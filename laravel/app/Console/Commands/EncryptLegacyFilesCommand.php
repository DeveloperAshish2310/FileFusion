<?php

namespace App\Console\Commands;

use App\Helpers\FileEncryptor;
use App\Models\FileModal;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class EncryptLegacyFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:encrypt-legacy {--dry-run : Only audit and report files without performing encryption}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan and migrate legacy unencrypted files to authenticated AES-256-GCM envelope encryption';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $this->info("==========================================================");
        $this->info("   FILE FUSION: LEGACY FILE ENCRYPTION MIGRATION TOOL    ");
        $this->info("==========================================================");

        if ($isDryRun) {
            $this->warn("MODE: DRY RUN (No changes will be written to disk/database)");
        }

        $files = FileModal::all();
        $totalFiles = $files->count();

        $alreadyEncrypted = 0;
        $missingFiles = 0;
        $toMigrate = [];

        $disk = 'local';

        $this->line("Scanning {$totalFiles} database records...");

        foreach ($files as $file) {
            $filePath = $file->path;
            $fullPath = Storage::disk($disk)->path($filePath);

            if (!file_exists($fullPath)) {
                $missingFiles++;
                continue;
            }

            if (FileEncryptor::isEncrypted($filePath, $disk)) {
                $alreadyEncrypted++;
            } else {
                $toMigrate[] = $file;
            }
        }

        $this->table(
            ['Category', 'Count'],
            [
                ['Total Records', $totalFiles],
                ['Already Encrypted (FF_ENC_V2)', $alreadyEncrypted],
                ['Legacy Plaintext Files', count($toMigrate)],
                ['Missing from Storage', $missingFiles],
            ]
        );

        if (count($toMigrate) === 0) {
            $this->info("✅ All files are already securely encrypted at rest! No migration needed.");
            return 0;
        }

        if ($isDryRun) {
            $this->info("Dry run complete. " . count($toMigrate) . " legacy files identified for encryption.");
            return 0;
        }

        if (!$this->confirm("Do you wish to proceed with encrypting " . count($toMigrate) . " files now?")) {
            $this->warn("Operation cancelled by user.");
            return 0;
        }

        $migratedCount = 0;
        $failedCount = 0;

        $bar = $this->output->createProgressBar(count($toMigrate));
        $bar->start();

        foreach ($toMigrate as $file) {
            try {
                $filePath = $file->path;
                $fullPath = Storage::disk($disk)->path($filePath);

                $origSha256 = hash_file('sha256', $fullPath);
                $tempEncFullPath = $fullPath . '.tmp_enc';

                // 1. Encrypt to temporary encrypted file
                $encMeta = FileEncryptor::encryptFile($fullPath, $tempEncFullPath);

                // 2. Verify decryption integrity before wiping original
                $decryptedContent = FileEncryptor::decryptFileToString($tempEncFullPath);
                $decryptedSha256 = hash('sha256', $decryptedContent);

                if ($decryptedSha256 !== $origSha256) {
                    throw new Exception("Integrity verification failed: SHA-256 mismatch after test decryption.");
                }

                // 3. Atomically overwrite plaintext with verified encrypted file
                rename($tempEncFullPath, $fullPath);

                $migratedCount++;
            } catch (Exception $e) {
                $failedCount++;
                $this->error("\nFailed to encrypt file ID {$file->id} ({$file->name}): " . $e->getMessage());
                if (isset($tempEncFullPath) && file_exists($tempEncFullPath)) {
                    @unlink($tempEncFullPath);
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("==========================================================");
        $this->info(" Migration Completed: {$migratedCount} successfully encrypted, {$failedCount} failed.");
        $this->info("==========================================================");

        return 0;
    }
}

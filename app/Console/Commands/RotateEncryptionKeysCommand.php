<?php

namespace App\Console\Commands;

use App\Services\KeyRotationService;
use Illuminate\Console\Command;

class RotateEncryptionKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:rotate-keys 
                            {--dry-run : Test decryption on all DB records and disk files without modifying data}
                            {--app-key : Rotate only database APP_KEY}
                            {--file-key : Rotate only disk files FILE_ENCRYPTION_KEY}
                            {--new-app-key= : Specify custom new APP_KEY (auto-generated if omitted)}
                            {--new-file-key= : Specify custom new FILE_ENCRYPTION_KEY (auto-generated if omitted)}
                            {--no-fallback : Do not preserve old keys in previous keys list}
                            {--force : Bypass confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotate Master Encryption Keys (APP_KEY and FILE_ENCRYPTION_KEY) and seamlessly re-encrypt database records and disk files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("===================================================================");
        $this->info("   FILE FUSION: MASTER ENCRYPTION KEY ROTATION & RE-ENCRYPTION     ");
        $this->info("===================================================================");

        $status = KeyRotationService::getCurrentKeyStatus();
        $this->line("• Active APP_KEY Fingerprint:     <comment>{$status['app_key_fingerprint']}</comment>");
        $this->line("• Active FILE_KEY Fingerprint:    <comment>{$status['file_key_fingerprint']}</comment>");
        $this->line("• Active File Key Version:        <comment>{$status['file_key_version']}</comment>");
        $this->line("• Previous App Keys In Chain:     <comment>{$status['app_previous_keys_count']}</comment>");
        $this->line("• Previous File Keys In Chain:    <comment>{$status['file_previous_keys_count']}</comment>");
        $this->newLine();

        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn("⚡ Running Pre-Flight Dry Run Verification...");
            $dryRun = KeyRotationService::runDryRun();

            $this->table(
                ['Resource / Area', 'Total Records', 'Successfully Decryptable', 'Decryption Failures'],
                [
                    ['Files (DB Names)', $dryRun['stats']['files_total'], $dryRun['stats']['files_decryptable'], $dryRun['stats']['files_failures']],
                    ['Categories (DB)', $dryRun['stats']['categories_total'], $dryRun['stats']['categories_decryptable'], $dryRun['stats']['categories_failures']],
                    ['Links (DB)', $dryRun['stats']['links_total'], $dryRun['stats']['links_decryptable'], $dryRun['stats']['links_failures']],
                    ['Passwords (DB Vault)', $dryRun['stats']['passwords_total'], $dryRun['stats']['passwords_decryptable'], $dryRun['stats']['passwords_failures']],
                    ['Disk Files (Storage)', $dryRun['stats']['disk_files_total'], $dryRun['stats']['disk_files_decryptable'], $dryRun['stats']['disk_files_failures']],
                ]
            );

            if ($dryRun['success']) {
                $this->info("✓ Dry Run SUCCESS: 100% of database records and envelope-encrypted disk files are healthy and decryptable!");
                $this->line("You can proceed with live key rotation safely.");
            } else {
                $this->error("✗ Dry Run WARNING: Found decryption errors:");
                foreach ($dryRun['errors'] as $err) {
                    $this->error("  - {$err}");
                }
            }

            return $dryRun['success'] ? Command::SUCCESS : Command::FAILURE;
        }

        // Determine what to rotate
        $rotateApp = true;
        $rotateFile = true;

        if ($this->option('app-key') && !$this->option('file-key')) {
            $rotateFile = false;
        } elseif ($this->option('file-key') && !$this->option('app-key')) {
            $rotateApp = false;
        }

        $newAppKey = $this->option('new-app-key');
        $newFileKey = $this->option('new-file-key');
        $keepPrevious = !$this->option('no-fallback');

        $this->warn("⚠️  Target Operations:");
        if ($rotateApp) $this->line("  [✓] Rotate APP_KEY and re-encrypt all database tables");
        if ($rotateFile) $this->line("  [✓] Rotate FILE_ENCRYPTION_KEY and re-encrypt all disk file envelope headers");
        if ($keepPrevious) $this->line("  [✓] Preserve current keys in APP_PREVIOUS_KEYS / FILE_PREVIOUS_KEYS for zero downtime");

        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to rotate encryption keys and re-encrypt database & files now?', false)) {
                $this->line('Operation cancelled by user.');
                return Command::SUCCESS;
            }
        }

        $this->info("⚡ Step 1/3: Running pre-flight dry-run check...");
        $dryRun = KeyRotationService::runDryRun();
        if (!$dryRun['success'] && !$this->option('force')) {
            $this->error("✗ Pre-flight check failed! Aborting rotation to prevent data corruption.");
            foreach ($dryRun['errors'] as $err) {
                $this->error("  - {$err}");
            }
            return Command::FAILURE;
        }
        $this->info("✓ Pre-flight check passed.");

        $this->info("⚡ Step 2/3: Executing database and storage file re-encryption...");
        try {
            $result = KeyRotationService::executeFullRotation([
                'rotate_app_key' => $rotateApp,
                'rotate_file_key' => $rotateFile,
                'new_app_key' => $newAppKey,
                'new_file_key' => $newFileKey,
                'keep_previous' => $keepPrevious,
                'force' => $this->option('force'),
            ]);

            if (!$result['success']) {
                $this->error("✗ Key rotation failed: {$result['message']}");
                return Command::FAILURE;
            }

            $this->info("⚡ Step 3/3: Re-encryption summary:");
            if (!empty($result['db_reencrypted']['stats'])) {
                $dbStats = $result['db_reencrypted']['stats'];
                $this->line("  • Files table rows updated:      {$dbStats['files_updated']}");
                $this->line("  • Categories table rows updated: {$dbStats['categories_updated']}");
                $this->line("  • Links table rows updated:      {$dbStats['links_updated']}");
                $this->line("  • Passwords table rows updated:  {$dbStats['passwords_updated']}");
            }

            if (!empty($result['files_reencrypted']['stats'])) {
                $fStats = $result['files_reencrypted']['stats'];
                $this->line("  • Disk files scanned:            {$fStats['scanned']}");
                $this->line("  • Envelope headers re-keyed:     {$fStats['encrypted_re_keyed']}");
                $this->line("  • Legacy unencrypted skipped:    {$fStats['legacy_skipped']}");
            }

            $this->newLine();
            $this->info("===================================================================");
            $this->info("✓ KEY ROTATION COMPLETED SUCCESSFULLY!");
            $this->info("• New APP_KEY:            " . KeyRotationService::maskKey($result['new_keys']['app_key']));
            $this->info("• New FILE_KEY:           " . KeyRotationService::maskKey($result['new_keys']['file_key']));
            $this->info("• New FILE_KEY_VERSION:   " . $result['new_keys']['file_key_version']);
            $this->info("• .env synchronized:      " . ($result['env_updated'] ? 'Yes' : 'Manual check recommended'));
            $this->info("===================================================================");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("✗ Error executing key rotation: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

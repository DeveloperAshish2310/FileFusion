<?php

namespace App\Console\Commands;

use App\Helpers\Encryptor;
use App\Models\FileModal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EncryptDbFileNamesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:encrypt-db-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt all plaintext file names in the database files table using AES-256 encryption';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("==========================================================");
        $this->info("   FILE FUSION: DATABASE FILE NAME ENCRYPTION MIGRATION   ");
        $this->info("==========================================================");

        $records = DB::table('files')->get();
        $total = count($records);
        $this->info("Found {$total} database records in 'files' table.");

        $encryptedCount = 0;
        $alreadyEncryptedCount = 0;

        foreach ($records as $record) {
            $rawName = $record->name;
            if (empty($rawName)) {
                continue;
            }

            // Check if already encrypted (Laravel Crypt payload usually starts with base64 json containing "iv")
            $isAlreadyEncrypted = false;
            if (str_contains($rawName, 'eyJpdiI6') || str_contains($rawName, 'eyJtYWMi')) {
                $isAlreadyEncrypted = true;
            }

            if ($isAlreadyEncrypted) {
                $alreadyEncryptedCount++;
            } else {
                $encryptedName = Encryptor::encrypt($rawName);
                DB::table('files')->where('id', $record->id)->update([
                    'name' => $encryptedName,
                ]);
                $encryptedCount++;
            }
        }

        $this->info("----------------------------------------------------------");
        $this->info("✓ Migration Complete!");
        $this->info("  - Total Files Processed: {$total}");
        $this->info("  - Files Newly Encrypted: {$encryptedCount}");
        $this->info("  - Files Already Encrypted: {$alreadyEncryptedCount}");
        $this->info("----------------------------------------------------------");

        return 0;
    }
}

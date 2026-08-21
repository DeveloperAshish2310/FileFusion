<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Helpers\Encryptor;
use Illuminate\Support\Facades\Schema;

class EncryptDatabase extends Command
{
    protected $signature = 'db:encrypt';
    protected $description = 'Encrypt all existing data in all tables';

    public function handle()
    {
        $dbName = env('DB_DATABASE');
        $tables = DB::select('SHOW TABLES');
        $keyName = "Tables_in_{$dbName}";

        foreach ($tables as $table) {
            $tableName = $table->$keyName;
            $columns = DB::getSchemaBuilder()->getColumnListing($tableName);

            $this->info("Encrypting table: {$tableName}");

            $rows = DB::table($tableName)->get();

            foreach ($rows as $row) {
                $update = [];
                foreach ($columns as $col) {
                    if (in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at'])) continue;
                    // if (Schema::getColumnType($tableName, $col) !== 'text') continue;

                    $value = $row->$col;
                    if (!empty($value)) {
                        $update[$col] = Encryptor::encrypt($value);
                    }
                }
                if (!empty($update)) {
                    DB::table($tableName)->where('id', $row->id)->update($update);
                }
            }
        }

        $this->info('✅ All existing data encrypted successfully!');
        return Command::SUCCESS;
    }
}

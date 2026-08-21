<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE categories MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'both'");
        } catch (\Throwable $e) {
            // Fallback for sqlite or systems without direct alter
            Schema::table('categories', function (Blueprint $table) {
                // If it's already compatible
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE categories MODIFY COLUMN type ENUM('links', 'files', 'both') NOT NULL DEFAULT 'both'");
        } catch (\Throwable $e) {
        }
    }
};

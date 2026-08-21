<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Storage quota in bytes (25GB = 26843545600 bytes)
            $table->bigInteger('storage_quota')->default(26843545600)->after('email_verified_at');
            // Current storage used in bytes
            $table->bigInteger('storage_used')->default(0)->after('storage_quota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['storage_quota', 'storage_used']);
        });
    }
};

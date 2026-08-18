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
            $table->unsignedInteger('hidden_files_session_lifetime')->default(1800)->after('vault_session_lifetime');
            $table->unsignedInteger('hidden_links_session_lifetime')->default(1800)->after('hidden_files_session_lifetime');
            $table->unsignedInteger('hidden_passwords_session_lifetime')->default(1800)->after('hidden_links_session_lifetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'hidden_files_session_lifetime',
                'hidden_links_session_lifetime',
                'hidden_passwords_session_lifetime'
            ]);
        });
    }
};

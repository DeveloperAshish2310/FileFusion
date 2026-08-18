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
            $table->unsignedInteger('vault_session_lifetime')->default(1800)->after('two_factor_enforce_password_reveal'); // default 30 mins in seconds
            $table->unsignedInteger('password_reveal_lifetime')->default(900)->after('vault_session_lifetime'); // default 15 mins in seconds
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['vault_session_lifetime', 'password_reveal_lifetime']);
        });
    }
};

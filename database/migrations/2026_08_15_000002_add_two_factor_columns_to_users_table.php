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
            if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('password');
            }
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            }
            if (!Schema::hasColumn('users', 'two_factor_type')) {
                $table->string('two_factor_type', 30)->default('authenticator')->after('two_factor_secret'); // 'authenticator', 'email', 'both'
            }
            if (!Schema::hasColumn('users', 'two_factor_enforce_login')) {
                $table->boolean('two_factor_enforce_login')->default(true)->after('two_factor_type');
            }
            if (!Schema::hasColumn('users', 'two_factor_enforce_vault')) {
                $table->boolean('two_factor_enforce_vault')->default(false)->after('two_factor_enforce_login');
            }
            if (!Schema::hasColumn('users', 'two_factor_enforce_password_reveal')) {
                $table->boolean('two_factor_enforce_password_reveal')->default(false)->after('two_factor_enforce_vault');
            }
            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_enforce_password_reveal');
            }
            if (!Schema::hasColumn('users', 'email_otp_code')) {
                $table->string('email_otp_code', 10)->nullable()->after('two_factor_recovery_codes');
            }
            if (!Schema::hasColumn('users', 'email_otp_expires_at')) {
                $table->timestamp('email_otp_expires_at')->nullable()->after('email_otp_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_enabled',
                'two_factor_secret',
                'two_factor_type',
                'two_factor_enforce_login',
                'two_factor_enforce_vault',
                'two_factor_enforce_password_reveal',
                'two_factor_recovery_codes',
                'email_otp_code',
                'email_otp_expires_at',
            ]);
        });
    }
};

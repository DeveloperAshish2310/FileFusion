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
        // First populate any null/empty role fields
        DB::table('users')->whereNull('role')->orWhere('role', '')->update(['role' => 'user']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('account_type')->default('2')->change(); // 2 = Regular User
            $table->string('role')->default('user')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_type')->default('1')->change();
            $table->string('role')->nullable()->change();
        });
    }
};

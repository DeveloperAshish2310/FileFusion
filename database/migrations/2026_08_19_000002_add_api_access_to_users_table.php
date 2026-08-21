<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'api_access_enabled')) {
                $table->boolean('api_access_enabled')->default(false)->after('items_per_page');
            }
        });

        // Automatically grant API access to existing Super Admins and Admins
        DB::table('users')
            ->whereIn('role', ['super_admin', 'admin'])
            ->update(['api_access_enabled' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'api_access_enabled')) {
                $table->dropColumn('api_access_enabled');
            }
        });
    }
};

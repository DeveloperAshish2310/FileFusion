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
        // 1. Expand Files Table Columns
        if (Schema::hasTable('files')) {
            Schema::table('files', function (Blueprint $table) {
                $table->text('name')->change();
                $table->text('path')->change();
                $table->text('thumbnail')->nullable()->change();
            });
        }

        // 2. Expand Links Table Columns
        if (Schema::hasTable('links')) {
            Schema::table('links', function (Blueprint $table) {
                $table->text('title')->change();
                $table->text('url')->change();
                $table->text('tags')->nullable()->change();
                $table->text('thumbnail')->nullable()->change();
            });
        }

        // 3. Expand Categories Table Columns
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->text('title')->change();
                $table->text('thumbnail')->nullable()->change();
            });
        }

        // 4. Expand Todo Collections & Tasks Columns
        if (Schema::hasTable('todo_collections')) {
            Schema::table('todo_collections', function (Blueprint $table) {
                $table->text('name')->change();
                $table->text('cover_image')->nullable()->change();
            });
        }

        if (Schema::hasTable('todo_tasks')) {
            Schema::table('todo_tasks', function (Blueprint $table) {
                $table->text('title')->change();
            });
        }

        if (Schema::hasTable('todo_steps')) {
            Schema::table('todo_steps', function (Blueprint $table) {
                $table->text('title')->change();
            });
        }

        // 5. Expand Users Table Vault Columns
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('vault_pass')->nullable()->change();
                $table->text('enc_key')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed as converting text back to varchar(255) risks data truncation
    }
};

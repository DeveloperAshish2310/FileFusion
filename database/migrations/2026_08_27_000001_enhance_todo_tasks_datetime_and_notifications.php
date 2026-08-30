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
        if (Schema::hasTable('todo_tasks')) {
            Schema::table('todo_tasks', function (Blueprint $table) {
                // Change due_date to dateTime if not already, or add due_datetime
                if (Schema::hasColumn('todo_tasks', 'due_date')) {
                    $table->dateTime('due_date')->nullable()->change();
                } else {
                    $table->dateTime('due_date')->nullable()->after('is_hidden');
                }

                if (!Schema::hasColumn('todo_tasks', 'last_notified_at')) {
                    $table->dateTime('last_notified_at')->nullable()->after('remind_at');
                }

                if (!Schema::hasColumn('todo_tasks', 'is_notified')) {
                    $table->boolean('is_notified')->default(false)->after('last_notified_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('todo_tasks')) {
            Schema::table('todo_tasks', function (Blueprint $table) {
                if (Schema::hasColumn('todo_tasks', 'last_notified_at')) {
                    $table->dropColumn('last_notified_at');
                }
                if (Schema::hasColumn('todo_tasks', 'is_notified')) {
                    $table->dropColumn('is_notified');
                }
            });
        }
    }
};

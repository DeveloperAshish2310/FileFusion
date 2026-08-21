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
        Schema::create('todo_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('color', 50)->default('#6366f1');
            $table->string('icon', 50)->default('list-todo');
            $table->string('cover_image')->nullable();
            $table->unsignedBigInteger('cover_file_id')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'is_hidden']);
        });

        Schema::create('todo_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('todo_collection_id')->nullable()->constrained('todo_collections')->nullOnDelete();
            $table->string('title');
            $table->longText('notes')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_pinned_to_dashboard')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->date('due_date')->nullable();
            $table->dateTime('remind_at')->nullable();
            $table->string('repeat_interval', 30)->default('none'); // none, daily, weekdays, weekly, monthly, yearly, custom
            $table->json('repeat_custom_days')->nullable();
            $table->json('attachments')->nullable(); // Array of file metadata [id, name, path, size, url]
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'is_completed']);
            $table->index(['user_id', 'is_starred']);
            $table->index(['user_id', 'is_pinned_to_dashboard']);
            $table->index(['user_id', 'due_date']);
        });

        Schema::create('todo_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('todo_task_id')->constrained('todo_tasks')->onDelete('cascade');
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['todo_task_id', 'is_completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todo_steps');
        Schema::dropIfExists('todo_tasks');
        Schema::dropIfExists('todo_collections');
    }
};

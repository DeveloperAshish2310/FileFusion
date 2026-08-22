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
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->text('title');
                $table->text('description')->nullable();
                $table->enum('type', ['links', 'files', 'both'])->default('both');
                $table->text('thumbnail')->nullable();
                $table->json('categories')->nullable(); // Store comma-separated categories as JSON
                $table->boolean('is_new')->default(true);
                $table->boolean('is_hidden')->default(false);
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['user_id', 'type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

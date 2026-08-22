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
        if (!Schema::hasTable('files')) {
            Schema::create('files', function (Blueprint $table) {
                $table->id();
                $table->text('name');
                $table->text('path');
                $table->unsignedBigInteger('size');
                $table->string('type', 150)->nullable();
                $table->timestamps();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('shared_id')->nullable();
                $table->text('thumbnail')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->boolean('is_hidden')->default(false);
                $table->boolean('is_starred')->default(false);
                $table->boolean('is_trashed')->default(false);
                $table->boolean('is_encrypted')->default(false);
                $table->boolean('is_shared')->default(false);
                $table->softDeletes();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                // $table->foreign('shared_id')->references('id')->on('shared_items')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};

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
        if (!Schema::hasTable('links')) {
            Schema::create('links', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('category_id')->nullable();
                $table->text('title');
                $table->text('url');
                $table->text('description')->nullable();
                $table->text('tags')->nullable();
                $table->boolean('is_hidden')->default(false);
                $table->text('thumbnail')->nullable();
                $table->boolean('is_starred')->default(false);
                $table->boolean('is_new')->default(false);
                $table->unsignedBigInteger('shared_id')->nullable();
                $table->boolean('is_encrypted')->default(false);
                $table->boolean('is_shared')->default(false);
                $table->date('expiry_date')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
                // $table->foreign('shared_id')->references('id')->on('shared_items')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};

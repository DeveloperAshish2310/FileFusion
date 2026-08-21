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
        // 1. Link Shares Table
        if (!Schema::hasTable('link_shares')) {
            Schema::create('link_shares', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('link_id');
                $table->unsignedBigInteger('user_id');
                $table->string('share_token', 64)->unique();
                $table->enum('share_type', ['private_user', 'public_link', 'anonymous_qr'])->default('public_link');
                $table->unsignedBigInteger('recipient_user_id')->nullable();
                $table->string('recipient_email')->nullable();
                $table->string('password')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->integer('max_clicks')->nullable();
                $table->integer('click_count')->default(0);
                $table->boolean('is_anonymous')->default(false);
                $table->timestamps();

                $table->foreign('link_id')->references('id')->on('links')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('recipient_user_id')->references('id')->on('users')->onDelete('set null');
                $table->index(['link_id', 'share_type']);
            });
        }

        // 2. Password Secret Shares Table
        if (!Schema::hasTable('password_shares')) {
            Schema::create('password_shares', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('password_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->string('share_token', 64)->unique();
                $table->enum('share_type', ['private_user', 'public_link', 'anonymous_qr'])->default('public_link');
                $table->text('encrypted_payload')->nullable();
                $table->unsignedBigInteger('recipient_user_id')->nullable();
                $table->string('recipient_email')->nullable();
                $table->string('password')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->integer('max_reveals')->default(1);
                $table->integer('reveal_count')->default(0);
                $table->boolean('burn_after_reading')->default(true);
                $table->boolean('is_anonymous')->default(true);
                $table->timestamps();

                $table->foreign('password_id')->references('id')->on('passwords')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('recipient_user_id')->references('id')->on('users')->onDelete('set null');
                $table->index(['password_id', 'share_type']);
            });
        }

        // 3. Category Bundle Shares Table
        if (!Schema::hasTable('category_shares')) {
            Schema::create('category_shares', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('user_id');
                $table->string('share_token', 64)->unique();
                $table->enum('share_type', ['private_user', 'public_link', 'anonymous_qr'])->default('public_link');
                $table->unsignedBigInteger('recipient_user_id')->nullable();
                $table->string('recipient_email')->nullable();
                $table->string('password')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->integer('max_views')->nullable();
                $table->integer('view_count')->default(0);
                $table->boolean('include_files')->default(true);
                $table->boolean('include_links')->default(true);
                $table->boolean('include_passwords')->default(false);
                $table->boolean('is_anonymous')->default(false);
                $table->timestamps();

                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('recipient_user_id')->references('id')->on('users')->onDelete('set null');
                $table->index(['category_id', 'share_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_shares');
        Schema::dropIfExists('password_shares');
        Schema::dropIfExists('link_shares');
    }
};

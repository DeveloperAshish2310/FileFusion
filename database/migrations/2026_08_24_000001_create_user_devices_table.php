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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('device_uuid', 100);
            $table->string('device_name', 150)->nullable();
            $table->string('platform', 30)->default('web'); // android, ios, web, pwa
            $table->string('push_type', 30)->default('vapid'); // fcm, vapid
            $table->text('push_token')->nullable(); // FCM Token or VAPID Subscription JSON
            $table->text('endpoint')->nullable(); // Web Push endpoint
            $table->text('public_key')->nullable(); // p256dh key
            $table->text('auth_token')->nullable(); // auth secret
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'device_uuid'], 'user_device_unique');
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};

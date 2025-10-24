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
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('email')->index();
            $table->string('ip_address', 45)->index(); // IPv6 support
            $table->text('user_agent')->nullable();
            $table->boolean('success')->default(false);
            $table->string('failed_reason')->nullable();
            $table->timestamp('attempted_at')->index();
            $table->timestamp('blocked_until')->nullable()->index();
            $table->string('device_fingerprint')->nullable()->index();
            $table->json('location_data')->nullable();
            $table->timestamps();

            // Composite indexes for efficient queries
            $table->index(['email', 'attempted_at']);
            $table->index(['ip_address', 'attempted_at']);
            $table->index(['user_id', 'attempted_at']);
            $table->index(['success', 'attempted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
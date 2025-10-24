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
        Schema::create('transaction_audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Transaction reference
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            
            // Action details
            $table->string('action'); // status_change, field_update, approval_requested, etc.
            $table->string('field_name')->nullable(); // For field updates
            
            // Values
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            
            // User who performed the action
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_role')->nullable();
            $table->string('user_name')->nullable();
            
            // Additional context
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            
            // Request details
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            // Timestamps
            $table->timestamp('created_at');
            
            // Indexes for performance
            $table->index(['transaction_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_audit_logs');
    }
};

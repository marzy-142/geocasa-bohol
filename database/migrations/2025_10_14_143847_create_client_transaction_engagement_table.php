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
        Schema::create('client_transaction_engagement', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            
            // Engagement metrics
            $table->enum('engagement_level', ['low', 'medium', 'high', 'very_high'])->default('medium');
            $table->integer('response_time_minutes')->nullable(); // Average response time in minutes
            $table->integer('login_count')->default(0);
            $table->timestamp('last_active')->nullable();
            $table->timestamp('last_message_sent')->nullable();
            $table->timestamp('last_document_uploaded')->nullable();
            
            // Communication preferences
            $table->json('preferences')->nullable(); // Preferred communication channels, times, etc.
            
            // Engagement history
            $table->json('interaction_history')->nullable(); // Track key interactions
            $table->integer('total_interactions')->default(0);
            $table->decimal('satisfaction_score', 3, 2)->nullable(); // 0.00 to 5.00
            
            $table->timestamps();
            
            // Indexes
            $table->index(['client_id', 'transaction_id']);
            $table->index(['engagement_level']);
            $table->index(['last_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_transaction_engagement');
    }
};
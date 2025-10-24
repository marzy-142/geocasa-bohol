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
         Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('inquiry_id')->nullable()->constrained()->onDelete('set null');
            
            // Transaction details
            $table->string('transaction_number')->unique();
            $table->enum('transaction_type', ['sale', 'rent', 'lease'])->default('sale');
            $table->decimal('offered_price', 15, 2);
            $table->decimal('final_price', 15, 2)->nullable();
            $table->decimal('commission_rate', 5, 4)->default(0.06); // 6% default
            $table->decimal('commission_amount', 15, 2)->nullable();
            
            // Status and timeline
            $table->enum('status', [
                'inquiry',
                'initial_contact',
                'property_viewing',
                'offer_made',
                'negotiation',
                'offer_accepted',
                'contract_signed',
                'due_diligence',
                'financing',
                'closing_preparation',
                'finalized',
                'cancelled'
            ])->default('inquiry');
            
            // Important dates
            $table->timestamp('inquiry_date');
            $table->timestamp('first_contact_date')->nullable();
            $table->timestamp('viewing_date')->nullable();
            $table->timestamp('offer_date')->nullable();
            $table->timestamp('acceptance_date')->nullable();
            $table->timestamp('contract_date')->nullable();
            $table->timestamp('closing_date')->nullable();
            $table->timestamp('finalized_date')->nullable();
            
            // Notes and documents
            $table->text('broker_notes')->nullable();
            $table->json('documents')->nullable(); // Array of document paths
            $table->json('status_history')->nullable(); // Track status changes
            
            $table->timestamps();
            
            // Indexes
            $table->index(['broker_id', 'status']);
            $table->index(['property_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index('status');
            $table->index('finalized_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

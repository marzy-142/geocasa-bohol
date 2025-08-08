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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            
            // Client preferences
            $table->decimal('budget_min', 15, 2)->nullable();
            $table->decimal('budget_max', 15, 2)->nullable();
            $table->string('preferred_location')->nullable();
            $table->decimal('preferred_area_min', 10, 2)->nullable();
            $table->decimal('preferred_area_max', 10, 2)->nullable();
            $table->json('preferred_features')->nullable();
            
            // Relationship
            $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');
            
            // Source tracking
            $table->enum('source', ['inquiry', 'manual', 'referral', 'website'])->default('manual');
            $table->text('notes')->nullable();
            
            // Status
            $table->enum('status', ['active', 'inactive', 'converted'])->default('active');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['broker_id', 'status']);
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};

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
        Schema::create('seller_requests', function (Blueprint $table) {
            $table->id();
            
            // Seller information
            $table->string('seller_name');
            $table->string('seller_email');
            $table->string('seller_phone')->nullable();
            $table->text('seller_address')->nullable();
            
            // Property information
            $table->string('property_title');
            $table->text('property_description');
            $table->decimal('asking_price', 15, 2);
            $table->decimal('property_area', 10, 2);
            $table->string('area_unit')->default('acres');
            $table->string('property_location');
            $table->string('property_address');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code')->nullable();
            
            // Optional details
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('property_type', ['residential', 'commercial', 'agricultural', 'industrial', 'recreational'])->default('residential');
            $table->json('features')->nullable();
            $table->json('uploaded_images')->nullable(); // Temporary image storage
            
            // Request status
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'listed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Assignment
            $table->foreignId('assigned_broker_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            
            // Conversion to property
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('listed_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['assigned_broker_id', 'status']);
            $table->index('seller_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_requests');
    }
};

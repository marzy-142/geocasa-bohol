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
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('address');

            // Property information
            $table->string('property_title');
            $table->text('property_description');
            $table->enum('property_type', [
                'residential_lot', 'commercial_lot', 'agricultural_land', 'industrial_lot',
                'beachfront', 'mountain_view', 'rice_field', 'coconut_plantation',
                'subdivision_lot', 'titled_land', 'tax_declared'
            ])->default('residential_lot');
            $table->decimal('asking_price', 15, 2);
            $table->string('city');
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('lot_area', 10, 2)->nullable();
            $table->json('features')->nullable();
            $table->json('uploaded_images');
            $table->json('property_documents')->nullable();
            $table->json('ownership_documents')->nullable();
            $table->string('preferred_contact_method');
            $table->string('availability')->nullable();
            $table->string('urgency');
            $table->text('additional_notes')->nullable();
            $table->boolean('marketing_consent')->default(false);
            $table->boolean('newsletter_consent')->default(false);
            $table->boolean('terms_accepted')->default(false);
            $table->string('status')->default('pending');
            $table->timestamps();
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

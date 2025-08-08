<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            
            // Land-specific property type
            $table->enum('type', [
                'residential_lot', 'agricultural_land', 'commercial_lot', 'industrial_lot',
                'beachfront', 'mountain_view', 'rice_field', 'coconut_plantation',
                'subdivision_lot', 'titled_land', 'tax_declared'
            ]);
            
            $table->enum('status', ['available', 'reserved', 'sold', 'under_negotiation', 'off_market'])
                  ->default('available');
            
            // Pricing for land (per sqm and total)
            $table->decimal('price_per_sqm', 10, 2);
            $table->decimal('total_price', 15, 2);
            
            // Location details (Bohol-specific)
            $table->string('address');
            $table->string('municipality');
            $table->string('barangay');
            
            // Land area measurements
            $table->decimal('lot_area_sqm', 12, 2);
            $table->decimal('lot_area_hectares', 8, 4)->nullable();
            
            // Legal documents
            $table->enum('title_type', ['titled', 'tax_declared', 'mother_title', 'cct'])->nullable();
            $table->string('title_number')->nullable();
            $table->string('tax_declaration_number')->nullable();
            
            // GPS coordinates
            $table->decimal('coordinates_lat', 10, 8)->nullable();
            $table->decimal('coordinates_lng', 11, 8)->nullable();
            
            // Utilities and access
            $table->boolean('road_access')->default(false);
            $table->boolean('water_source')->default(false);
            $table->boolean('electricity_available')->default(false);
            $table->boolean('internet_available')->default(false);
            
            // Additional details
            $table->json('nearby_landmarks')->nullable();
            $table->string('zoning_classification')->nullable();
            
            // Media
            $table->json('images')->nullable();
            $table->json('documents')->nullable(); // For title, tax dec, etc.
            
            $table->boolean('is_featured')->default(false);
            
            // Relationships
            $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');

            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index(['municipality', 'status']);
            $table->index(['type', 'status']);
            $table->index(['price_per_sqm', 'lot_area_sqm']);
            $table->index(['coordinates_lat', 'coordinates_lng']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};

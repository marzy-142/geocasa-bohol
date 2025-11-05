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
        Schema::table('properties', function (Blueprint $table) {
            // Make pricing fields nullable
            $table->decimal('price_per_sqm', 10, 2)->nullable()->change();
            $table->decimal('total_price', 15, 2)->nullable()->change();
            
            // Make location detail fields nullable (except municipality which is required)
            $table->string('address')->nullable()->change();
            $table->string('barangay')->nullable()->change();
            
            // Make land area measurements nullable
            $table->decimal('lot_area_sqm', 12, 2)->nullable()->change();
            
            // Make title number and zoning nullable (title_type is already nullable)
            $table->string('title_number')->nullable()->change();
            $table->string('zoning_classification')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Revert to NOT NULL (will fail if there are null values)
            $table->decimal('price_per_sqm', 10, 2)->nullable(false)->change();
            $table->decimal('total_price', 15, 2)->nullable(false)->change();
            $table->string('address')->nullable(false)->change();
            $table->string('barangay')->nullable(false)->change();
            $table->decimal('lot_area_sqm', 12, 2)->nullable(false)->change();
            $table->string('title_number')->nullable(false)->change();
            $table->string('zoning_classification')->nullable(false)->change();
        });
    }
};

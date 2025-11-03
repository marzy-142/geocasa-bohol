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
            // Make pricing and area fields required (NOT NULL)
            $table->decimal('price_per_sqm', 15, 2)->nullable(false)->change();
            $table->decimal('total_price', 15, 2)->nullable(false)->change();
            $table->decimal('lot_area_sqm', 10, 2)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Make fields nullable again
            $table->decimal('price_per_sqm', 15, 2)->nullable()->change();
            $table->decimal('total_price', 15, 2)->nullable()->change();
            $table->decimal('lot_area_sqm', 10, 2)->nullable()->change();
        });
    }
};

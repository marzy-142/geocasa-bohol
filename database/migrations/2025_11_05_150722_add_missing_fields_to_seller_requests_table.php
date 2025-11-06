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
        Schema::table('seller_requests', function (Blueprint $table) {
            $table->string('title_number', 100)->nullable()->after('title_type');
            $table->string('custom_property_type', 100)->nullable()->after('property_type');
            $table->decimal('coordinates_lat', 10, 8)->nullable()->after('barangay');
            $table->decimal('coordinates_lng', 11, 8)->nullable()->after('coordinates_lat');
            $table->string('nearby_landmarks', 500)->nullable()->after('coordinates_lng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_requests', function (Blueprint $table) {
            $table->dropColumn(['title_number', 'custom_property_type', 'coordinates_lat', 'coordinates_lng', 'nearby_landmarks']);
        });
    }
};

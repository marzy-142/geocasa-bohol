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
            $table->boolean('road_access')->default(false)->after('zoning_classification');
            $table->boolean('water_source')->default(false)->after('road_access');
            $table->boolean('electricity_available')->default(false)->after('water_source');
            $table->boolean('internet_available')->default(false)->after('electricity_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_requests', function (Blueprint $table) {
            $table->dropColumn(['road_access', 'water_source', 'electricity_available', 'internet_available']);
        });
    }
};

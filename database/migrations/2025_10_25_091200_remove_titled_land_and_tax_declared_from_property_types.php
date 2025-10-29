<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Update any existing records with titled_land or tax_declared to residential_lot
            DB::table('seller_requests')
                ->whereIn('property_type', ['titled_land', 'tax_declared'])
                ->update(['property_type' => 'residential_lot']);

            // Update the enum to remove titled_land and tax_declared
            DB::statement("ALTER TABLE seller_requests MODIFY COLUMN property_type ENUM(
                'residential_lot',
                'agricultural_land', 
                'commercial_lot',
                'industrial_lot',
                'beachfront',
                'mountain_view',
                'rice_field',
                'coconut_plantation',
                'subdivision_lot'
            ) DEFAULT 'residential_lot'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Add back titled_land and tax_declared
            DB::statement("ALTER TABLE seller_requests MODIFY COLUMN property_type ENUM(
                'residential_lot',
                'agricultural_land', 
                'commercial_lot',
                'industrial_lot',
                'beachfront',
                'mountain_view',
                'rice_field',
                'coconut_plantation',
                'subdivision_lot',
                'titled_land',
                'tax_declared'
            ) DEFAULT 'residential_lot'");
        }
    }
};

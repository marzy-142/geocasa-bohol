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
            // Step 1: Change column to VARCHAR temporarily to allow any values
            DB::statement("ALTER TABLE seller_requests MODIFY COLUMN property_type VARCHAR(50) DEFAULT 'residential_lot'");

            // Step 2: Update existing data to match new enum values
            DB::table('seller_requests')->where('property_type', 'residential')->update(['property_type' => 'residential_lot']);
            DB::table('seller_requests')->where('property_type', 'commercial')->update(['property_type' => 'commercial_lot']);
            DB::table('seller_requests')->where('property_type', 'agricultural')->update(['property_type' => 'agricultural_land']);
            DB::table('seller_requests')->where('property_type', 'industrial')->update(['property_type' => 'industrial_lot']);
            DB::table('seller_requests')->where('property_type', 'recreational')->update(['property_type' => 'beachfront']);

            // Step 3: Change back to ENUM with new values
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
        } else {
            // For SQLite and other DBs, skip column type changes (not supported)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Revert to the old enum values
            DB::statement("ALTER TABLE seller_requests MODIFY COLUMN property_type ENUM(
                'residential',
                'commercial',
                'agricultural',
                'industrial',
                'recreational'
            ) DEFAULT 'residential'");
        } else {
            // For SQLite and other DBs, skip column type changes
        }
    }
};
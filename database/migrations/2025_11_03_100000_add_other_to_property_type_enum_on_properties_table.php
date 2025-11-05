<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Expand the enum to include 'other' while preserving existing values
            DB::statement("ALTER TABLE `properties` MODIFY COLUMN `type` ENUM(
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
                'tax_declared',
                'other'
            ) NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Any rows with 'other' must be mapped to a supported type before shrinking enum
            DB::table('properties')->where('type', 'other')->update(['type' => 'residential_lot']);

            DB::statement("ALTER TABLE `properties` MODIFY COLUMN `type` ENUM(
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
            ) NOT NULL");
        }
    }
};

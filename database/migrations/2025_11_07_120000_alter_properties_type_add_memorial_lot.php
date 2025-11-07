<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the ENUM for properties.type to include 'memorial_lot'
        // Note: This assumes MySQL. Adjust if your database differs.
        DB::statement("ALTER TABLE `properties` 
            MODIFY COLUMN `type` ENUM(
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
                'memorial_lot'
            ) NOT NULL");
    }

    public function down(): void
    {
        // Revert the ENUM by removing 'memorial_lot'
        // WARNING: This will fail if any rows still have 'memorial_lot' value.
        DB::statement("ALTER TABLE `properties` 
            MODIFY COLUMN `type` ENUM(
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
};

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
        // Change property_type from ENUM to TEXT to support JSON arrays
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE seller_requests MODIFY COLUMN property_type TEXT NULL");
        } else {
            Schema::table('seller_requests', function (Blueprint $table) {
                $table->text('property_type')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to ENUM (optional - may lose data if values don't match)
        if (DB::getDriverName() === 'mysql') {
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
                'other'
            ) DEFAULT 'residential_lot'");
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Make some properties columns nullable or provide defaults to support auto-conversion
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `properties` 
                MODIFY `slug` varchar(255) NULL,
                MODIFY `price_per_sqm` decimal(10,2) NULL,
                MODIFY `total_price` decimal(15,2) NULL,
                MODIFY `address` varchar(255) NULL,
                MODIFY `municipality` varchar(255) NULL,
                MODIFY `barangay` varchar(255) NULL,
                MODIFY `lot_area_sqm` decimal(12,2) NULL");
        } // else: skip for SQLite
    }

    public function down()
    {
        // Revert to NOT NULL where reasonable (may fail if data exists); use cautiously
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `properties` 
                MODIFY `slug` varchar(255) NOT NULL,
                MODIFY `price_per_sqm` decimal(10,2) NOT NULL,
                MODIFY `total_price` decimal(15,2) NOT NULL,
                MODIFY `address` varchar(255) NOT NULL,
                MODIFY `municipality` varchar(255) NOT NULL,
                MODIFY `barangay` varchar(255) NOT NULL,
                MODIFY `lot_area_sqm` decimal(12,2) NOT NULL");
        } // else: skip for SQLite
    }
};

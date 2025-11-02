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
        // MySQL doesn't support adding values to ENUM directly, so we need to change the column
        DB::statement("ALTER TABLE `inquiries` MODIFY COLUMN `status` ENUM('new', 'contacted', 'scheduled', 'completed', 'in_transaction', 'closed') DEFAULT 'new'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values
        // Note: This will fail if any records have 'in_transaction' status
        DB::statement("ALTER TABLE `inquiries` MODIFY COLUMN `status` ENUM('new', 'contacted', 'scheduled', 'completed', 'closed') DEFAULT 'new'");
    }
};

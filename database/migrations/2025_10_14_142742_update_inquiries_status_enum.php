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
        // Update the status enum to include 'in transaction'
        DB::statement("ALTER TABLE inquiries MODIFY COLUMN status ENUM('new', 'contacted', 'scheduled', 'in transaction', 'completed', 'closed') DEFAULT 'new'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE inquiries MODIFY COLUMN status ENUM('new', 'contacted', 'scheduled', 'completed', 'closed') DEFAULT 'new'");
    }
};
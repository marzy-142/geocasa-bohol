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
        // SQLite doesn't support ENUM, it uses TEXT with CHECK constraints
        // MySQL supports ENUM natively
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE inquiries MODIFY COLUMN status ENUM('new', 'contacted', 'scheduled', 'in transaction', 'completed', 'closed') DEFAULT 'new'");
        } else {
            // For SQLite, we just need to ensure the column exists (it already does)
            // SQLite will accept any string value, Laravel validation handles the constraint
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // Revert back to original enum values
            DB::statement("ALTER TABLE inquiries MODIFY COLUMN status ENUM('new', 'contacted', 'scheduled', 'completed', 'closed') DEFAULT 'new'");
        }
    }
};
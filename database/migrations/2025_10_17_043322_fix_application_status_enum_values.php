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
        // Modify the application_status column to include missing ENUM values
        Schema::table('users', function (Blueprint $table) {
            // First, modify the column to add the missing ENUM values
            $table->enum('application_status', [
                'pending',
                'under_review', 
                'approved',
                'rejected',
                'prc_verification_failed',
                'inactive'
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original ENUM values
        Schema::table('users', function (Blueprint $table) {
            $table->enum('application_status', [
                'pending',
                'under_review',
                'approved', 
                'rejected'
            ])->default('pending')->change();
        });
    }
};

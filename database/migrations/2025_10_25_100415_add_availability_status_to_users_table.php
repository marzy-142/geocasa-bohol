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
        Schema::table('users', function (Blueprint $table) {
            // Broker availability status
            $table->enum('availability_status', ['available', 'limited', 'unavailable'])
                  ->default('available')
                  ->after('prc_verified')
                  ->comment('Broker availability for new assignments');
            
            // Maximum concurrent assignments (optional limit)
            $table->integer('max_concurrent_requests')
                  ->default(5)
                  ->after('availability_status')
                  ->comment('Maximum number of concurrent seller requests');
            
            // Auto-unavailable toggle
            $table->boolean('auto_manage_availability')
                  ->default(true)
                  ->after('max_concurrent_requests')
                  ->comment('Automatically update availability based on workload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'availability_status',
                'max_concurrent_requests',
                'auto_manage_availability'
            ]);
        });
    }
};

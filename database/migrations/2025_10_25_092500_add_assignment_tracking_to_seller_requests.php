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
        Schema::table('seller_requests', function (Blueprint $table) {
            // Track how broker was assigned
            $table->enum('assignment_method', ['auto', 'manual', 'admin'])
                  ->default('auto')
                  ->after('assigned_broker_id')
                  ->comment('How the broker was assigned: auto (algorithm), manual (seller choice), admin (admin assigned)');
            
            // Track when assignment happened
            $table->timestamp('assigned_at')->nullable()->after('assignment_method');
            
            // Track if seller wants to choose broker
            $table->boolean('wants_broker_selection')->default(false)->after('assigned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_requests', function (Blueprint $table) {
            $table->dropColumn(['assignment_method', 'assigned_at', 'wants_broker_selection']);
        });
    }
};

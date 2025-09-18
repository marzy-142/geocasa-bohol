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
        Schema::table('inquiries', function (Blueprint $table) {
            // Add assigned_broker_id to track which broker is handling the inquiry
            // This is separate from the property's broker_id to preserve original ownership
            $table->foreignId('assigned_broker_id')->nullable()->after('client_id')
                  ->constrained('users')->onDelete('set null');
            
            // Add index for efficient queries
            $table->index(['assigned_broker_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropForeign(['assigned_broker_id']);
            $table->dropIndex(['assigned_broker_id', 'status']);
            $table->dropColumn('assigned_broker_id');
        });
    }
};

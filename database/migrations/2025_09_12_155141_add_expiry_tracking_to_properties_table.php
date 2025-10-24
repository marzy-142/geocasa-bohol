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
        Schema::table('properties', function (Blueprint $table) {
            $table->timestamp('last_updated_at')->nullable()->after('updated_at');
            $table->timestamp('expiry_date')->nullable()->after('last_updated_at');
            $table->timestamp('reminder_sent_at')->nullable()->after('expiry_date');
            $table->boolean('renewal_required')->default(false)->after('reminder_sent_at');
            
            // Add index for performance on expiry queries
            $table->index(['expiry_date', 'status']);
            $table->index(['reminder_sent_at', 'expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['expiry_date', 'status']);
            $table->dropIndex(['reminder_sent_at', 'expiry_date']);
            
            $table->dropColumn([
                'last_updated_at',
                'expiry_date', 
                'reminder_sent_at',
                'renewal_required'
            ]);
        });
    }
};

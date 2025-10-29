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
        // Remove from seller_requests table
        if (Schema::hasColumn('seller_requests', 'preferred_contact_method')) {
            Schema::table('seller_requests', function (Blueprint $table) {
                $table->dropColumn('preferred_contact_method');
            });
        }
        
        // Remove from inquiries table
        if (Schema::hasColumn('inquiries', 'preferred_contact_method')) {
            Schema::table('inquiries', function (Blueprint $table) {
                $table->dropColumn('preferred_contact_method');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back to seller_requests table
        Schema::table('seller_requests', function (Blueprint $table) {
            $table->string('preferred_contact_method')->nullable()->after('contact_phone');
        });
        
        // Add back to inquiries table
        Schema::table('inquiries', function (Blueprint $table) {
            $table->string('preferred_contact_method')->nullable()->after('phone');
        });
    }
};

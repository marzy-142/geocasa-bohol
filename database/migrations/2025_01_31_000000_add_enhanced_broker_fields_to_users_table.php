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
            // PRC License expiration date
            $table->date('prc_license_expiration')->nullable()->after('prc_id');
            
            // Business Details (Optional)
            $table->string('brokerage_firm_name')->nullable()->after('company_address');
            $table->text('office_address')->nullable()->after('brokerage_firm_name');
            $table->string('office_contact_number')->nullable()->after('office_address');
            
            // Agreement checkboxes (already exist but ensuring they're properly set)
            // terms_accepted and privacy_policy_accepted already exist from previous migration
            
            // Additional verification fields
            $table->boolean('information_certified')->default(false)->after('privacy_policy_accepted');
            $table->boolean('prc_verification_consent')->default(false)->after('information_certified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'prc_license_expiration',
                'brokerage_firm_name',
                'office_address', 
                'office_contact_number',
                'information_certified',
                'prc_verification_consent'
            ]);
        });
    }
};
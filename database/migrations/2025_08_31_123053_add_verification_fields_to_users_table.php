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
            // Verification status fields
            $table->boolean('prc_verified')->default(false)->after('prc_id_file');
            $table->text('prc_verification_notes')->nullable()->after('prc_verified');
            $table->boolean('business_permit_verified')->default(false)->after('business_permit_file');
            $table->text('business_permit_verification_notes')->nullable()->after('business_permit_verified');
            
            // Admin notes for approval/rejection
            $table->text('admin_notes')->nullable()->after('rejection_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'prc_verified',
                'prc_verification_notes',
                'business_permit_verified',
                'business_permit_verification_notes',
                'admin_notes'
            ]);
        });
    }
};

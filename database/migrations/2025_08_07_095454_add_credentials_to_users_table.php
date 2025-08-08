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
            // Professional credentials
            $table->string('prc_id')->nullable()->after('role');
            $table->string('prc_id_file')->nullable()->after('prc_id');
            $table->string('business_permit')->nullable()->after('prc_id_file');
            $table->string('business_permit_file')->nullable()->after('business_permit');
            $table->text('additional_documents')->nullable()->after('business_permit_file'); // JSON field for other docs
            
            // Application status
            $table->enum('application_status', ['pending', 'under_review', 'approved', 'rejected'])
                  ->default('pending')->after('is_approved');
            $table->text('rejection_reason')->nullable()->after('application_status');
            $table->timestamp('submitted_at')->nullable()->after('rejection_reason');
            $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'prc_id', 'prc_id_file', 'business_permit', 'business_permit_file',
                'additional_documents', 'application_status', 'rejection_reason',
                'submitted_at', 'reviewed_at'
            ]);
        });
    }
};

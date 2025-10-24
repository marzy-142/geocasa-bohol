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
            // PRC Verification fields - only add if they don't exist
            if (!Schema::hasColumn('users', 'prc_verification_status')) {
                $table->string('prc_verification_status')->nullable();
            }
            if (!Schema::hasColumn('users', 'prc_verification_result')) {
                $table->json('prc_verification_result')->nullable();
            }
            if (!Schema::hasColumn('users', 'prc_verified_at')) {
                $table->timestamp('prc_verified_at')->nullable();
            }
            
            // Enhanced application status tracking - only add if they don't exist
            if (!Schema::hasColumn('users', 'application_status')) {
                $table->string('application_status')->default('pending');
            }
            if (!Schema::hasColumn('users', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            
            // Add indexes if they don't exist
            if (!Schema::hasIndex('users', ['application_status', 'submitted_at'])) {
                $table->index(['application_status', 'submitted_at']);
            }
            if (!Schema::hasIndex('users', ['prc_verification_status'])) {
                $table->index('prc_verification_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['application_status', 'submitted_at']);
            $table->dropIndex(['prc_verification_status']);
            
            $table->dropColumn([
                'prc_verification_status',
                'prc_verification_result',
                'prc_verified_at',
                'application_status',
                'submitted_at'
            ]);
        });
    }
};

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
        Schema::table('transactions', function (Blueprint $table) {
            // Client-centric tracking fields
            if (!Schema::hasColumn('transactions', 'client_approvals')) {
                $table->json('client_approvals')->nullable()->after('broker_notes');
            }
            if (!Schema::hasColumn('transactions', 'client_feedback')) {
                $table->json('client_feedback')->nullable()->after('client_approvals');
            }
            if (!Schema::hasColumn('transactions', 'client_last_viewed')) {
                $table->timestamp('client_last_viewed')->nullable()->after('client_feedback');
            }
            if (!Schema::hasColumn('transactions', 'client_satisfaction')) {
                $table->enum('client_satisfaction', ['pending', 'satisfied', 'dissatisfied'])->nullable()->after('client_last_viewed');
            }
            if (!Schema::hasColumn('transactions', 'client_notes')) {
                $table->text('client_notes')->nullable()->after('client_satisfaction');
            }
            
            // Enhanced status tracking for client involvement
            if (!Schema::hasColumn('transactions', 'client_engagement_score')) {
                $table->integer('client_engagement_score')->default(0)->after('client_notes');
            }
            if (!Schema::hasColumn('transactions', 'requires_client_action')) {
                $table->boolean('requires_client_action')->default(false)->after('client_engagement_score');
            }
            if (!Schema::hasColumn('transactions', 'client_action_deadline')) {
                $table->timestamp('client_action_deadline')->nullable()->after('requires_client_action');
            }
            
            // Add indexes for performance
            if (!Schema::hasIndex('transactions', ['requires_client_action'])) {
                $table->index('requires_client_action');
            }
            if (!Schema::hasIndex('transactions', ['client_action_deadline'])) {
                $table->index('client_action_deadline');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['requires_client_action']);
            $table->dropIndex(['client_action_deadline']);
            
            $table->dropColumn([
                'client_approvals',
                'client_feedback', 
                'client_last_viewed',
                'client_satisfaction',
                'client_notes',
                'client_engagement_score',
                'requires_client_action',
                'client_action_deadline'
            ]);
        });
    }
};
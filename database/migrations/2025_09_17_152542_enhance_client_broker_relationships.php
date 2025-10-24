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
        Schema::table('clients', function (Blueprint $table) {
            // Only add columns if they don't already exist
            if (!Schema::hasColumn('clients', 'relationship_type')) {
                $table->enum('relationship_type', ['primary', 'secondary', 'inquiry_specific'])
                      ->default('primary')
                      ->after('broker_id');
            }
            if (!Schema::hasColumn('clients', 'relationship_established_at')) {
                $table->timestamp('relationship_established_at')
                      ->nullable()
                      ->after('relationship_type');
            }
            if (!Schema::hasColumn('clients', 'relationship_metadata')) {
                $table->json('relationship_metadata')
                      ->nullable()
                      ->after('relationship_established_at');
            }
            if (!Schema::hasColumn('clients', 'assignment_reason')) {
                $table->string('assignment_reason')
                      ->nullable()
                      ->after('relationship_metadata');
            }
        });

        Schema::table('inquiries', function (Blueprint $table) {
            // Only add columns if they don't already exist
            if (!Schema::hasColumn('inquiries', 'assignment_context')) {
                $table->enum('assignment_context', ['property_broker', 'auto_assigned', 'manual_assigned', 'escalated'])
                      ->default('property_broker')
                      ->after('assigned_broker_id');
            }
            if (!Schema::hasColumn('inquiries', 'assignment_timestamp')) {
                $table->timestamp('assignment_timestamp')
                      ->nullable()
                      ->after('assignment_context');
            }
            if (!Schema::hasColumn('inquiries', 'assignment_metadata')) {
                $table->json('assignment_metadata')
                      ->nullable()
                      ->after('assignment_timestamp');
            }
        });

        // Create broker-client relationship history table (only if it doesn't exist)
        if (!Schema::hasTable('broker_client_relationships')) {
            Schema::create('broker_client_relationships', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->onDelete('cascade');
                $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');
                $table->enum('relationship_type', ['primary', 'secondary', 'inquiry_specific']);
                $table->enum('assignment_method', ['auto', 'manual', 'escalated', 'reassigned']);
                $table->string('assignment_reason')->nullable();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('assigned_at');
                $table->timestamp('ended_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['client_id', 'broker_id']);
                $table->index(['broker_id', 'relationship_type']);
                $table->index('assigned_at');
            });
        }

        // Create communication workflow tracking table (only if it doesn't exist)
        if (!Schema::hasTable('communication_workflows')) {
            Schema::create('communication_workflows', function (Blueprint $table) {
                $table->id();
                $table->morphs('workflowable'); // inquiry_id or transaction_id
                $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('client_id')->constrained()->onDelete('cascade');
                $table->enum('workflow_type', ['inquiry_response', 'escalation', 'follow_up', 'transaction_update']);
                $table->enum('status', ['pending', 'in_progress', 'completed', 'escalated', 'cancelled']);
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->json('workflow_data')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['broker_id', 'status']);
                $table->index('scheduled_at');
            });
        }

        // Note: broker_performance_metrics table is created in inquiry_monitoring_tables migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: broker_performance_metrics table is managed by inquiry_monitoring_tables migration
        Schema::dropIfExists('communication_workflows');
        Schema::dropIfExists('broker_client_relationships');
        
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn([
                'assignment_context',
                'assignment_timestamp',
                'assignment_metadata'
            ]);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'relationship_type',
                'relationship_established_at',
                'relationship_metadata',
                'assignment_reason'
            ]);
        });
    }
};

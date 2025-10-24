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
        // Basic inquiry metrics table
        Schema::create('inquiry_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('metric_type');
            $table->json('data');
            $table->timestamps();
            
            $table->index(['date', 'metric_type']);
        });

        // Inquiry status history
        Schema::create('inquiry_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inquiry_id');
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
            
            // Add foreign key constraints after column definitions
            $table->foreign('inquiry_id')->references('id')->on('inquiries')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['inquiry_id', 'changed_at']);
            $table->index(['new_status', 'changed_at']);
        });

        // Duplicate prevention logs
        Schema::create('duplicate_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('duplicate_type'); // 'exact_match', 'similar_content', 'frequency_limit'
            $table->text('original_message')->nullable();
            $table->text('duplicate_message')->nullable();
            $table->string('ip_address')->nullable();
            $table->unsignedBigInteger('property_id')->nullable();
            $table->timestamps();
            
            // Add foreign key constraint after column definition
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('set null');
            
            $table->index(['email', 'created_at']);
            $table->index(['duplicate_type', 'created_at']);
        });

        // Broker performance metrics
        Schema::create('broker_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('broker_id');
            $table->date('date');
            $table->integer('inquiries_assigned')->default(0);
            $table->integer('inquiries_contacted')->default(0);
            $table->integer('inquiries_completed')->default(0);
            $table->decimal('avg_response_hours', 8, 2)->nullable();
            $table->decimal('conversion_rate', 5, 2)->nullable();
            $table->decimal('workload_score', 8, 2)->default(0);
            $table->timestamps();
            
            // Add foreign key constraint after column definition
            $table->foreign('broker_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['broker_id', 'date']);
            $table->index(['date', 'conversion_rate']);
        });

        // System health logs
        Schema::create('system_health_logs', function (Blueprint $table) {
            $table->id();
            $table->string('component');
            $table->string('status'); // 'healthy', 'warning', 'critical'
            $table->json('metrics');
            $table->text('message')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
            
            $table->index(['component', 'checked_at']);
            $table->index(['status', 'checked_at']);
        });

        // Notification logs
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('notification_type');
            $table->string('recipient_type');
            $table->unsignedBigInteger('recipient_id');
            $table->unsignedBigInteger('inquiry_id')->nullable();
            $table->json('channels');
            $table->string('status'); // 'sent', 'failed', 'queued'
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            // Add foreign key constraint after column definition
            $table->foreign('inquiry_id')->references('id')->on('inquiries')->onDelete('set null');
            
            $table->index(['notification_type', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('system_health_logs');
        Schema::dropIfExists('broker_performance_metrics');
        Schema::dropIfExists('duplicate_logs');
        Schema::dropIfExists('inquiry_status_history');
        Schema::dropIfExists('inquiry_metrics');
    }
};
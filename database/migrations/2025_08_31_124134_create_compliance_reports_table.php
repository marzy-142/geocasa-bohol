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
        Schema::create('compliance_reports', function (Blueprint $table) {
            $table->id();
            $table->string('reportable_type'); // Property, Inquiry, User, etc.
            $table->unsignedBigInteger('reportable_id');
            $table->enum('report_type', ['spam', 'inappropriate_content', 'fake_listing', 'suspicious_activity', 'policy_violation', 'other']);
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['pending', 'under_review', 'resolved', 'dismissed']);
            $table->text('description');
            $table->json('evidence')->nullable(); // Screenshots, URLs, etc.
            $table->unsignedBigInteger('reported_by')->nullable(); // User who reported
            $table->unsignedBigInteger('assigned_to')->nullable(); // Admin assigned to review
            $table->text('admin_notes')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('reported_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['reportable_type', 'reportable_id']);
            $table->index(['status', 'severity']);
            $table->index(['reported_at']);
            
            // Foreign keys
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_reports');
    }
};

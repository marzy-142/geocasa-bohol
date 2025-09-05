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
        Schema::create('investigation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compliance_report_id')->constrained()->onDelete('cascade');
            $table->foreignId('investigator_id')->constrained('users')->onDelete('cascade');
            $table->string('action_type'); // 'evidence_collected', 'interview_conducted', 'status_changed', 'note_added'
            $table->text('description');
            $table->json('metadata')->nullable(); // Additional data like interview notes, evidence details
            $table->json('evidence_files')->nullable(); // File paths or URLs to evidence
            $table->timestamp('action_taken_at');
            $table->timestamps();
            
            $table->index(['compliance_report_id', 'action_taken_at']);
            $table->index('investigator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investigation_logs');
    }
};
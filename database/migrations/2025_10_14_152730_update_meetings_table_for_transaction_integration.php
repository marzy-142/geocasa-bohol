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
        Schema::table('meetings', function (Blueprint $table) {
            // Add transaction relationship
            if (!Schema::hasColumn('meetings', 'transaction_id')) {
                $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('cascade')->after('id');
            }

            // Add meeting type
            if (!Schema::hasColumn('meetings', 'type')) {
                $table->enum('type', ['property_viewing', 'contract_review', 'closing_meeting', 'consultation', 'other'])
                      ->default('consultation')
                      ->after('transaction_id');
            }

            // Add scheduled_at field (more precise than scheduled_date)
            if (!Schema::hasColumn('meetings', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('scheduled_date');
            }

            // Add reminder settings
            if (!Schema::hasColumn('meetings', 'reminder_minutes')) {
                $table->integer('reminder_minutes')->default(60)->after('status');
            }

            // Add attendees array
            if (!Schema::hasColumn('meetings', 'attendees')) {
                $table->json('attendees')->nullable()->after('reminder_minutes');
            }

            // Add cancellation fields
            if (!Schema::hasColumn('meetings', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('attendees');
            }
            if (!Schema::hasColumn('meetings', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            }
            if (!Schema::hasColumn('meetings', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null')->after('cancellation_reason');
            }

            // Add completion field
            if (!Schema::hasColumn('meetings', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('cancelled_by');
            }

            // Add indexes for performance
            if (!Schema::hasIndex('meetings', ['transaction_id', 'status'])) {
                $table->index(['transaction_id', 'status']);
            }
            if (!Schema::hasIndex('meetings', ['client_id', 'scheduled_at'])) {
                $table->index(['client_id', 'scheduled_at']);
            }
            if (!Schema::hasIndex('meetings', ['broker_id', 'scheduled_at'])) {
                $table->index(['broker_id', 'scheduled_at']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            // Drop indexes first
            if (Schema::hasIndex('meetings', ['transaction_id', 'status'])) {
                $table->dropIndex(['transaction_id', 'status']);
            }
            if (Schema::hasIndex('meetings', ['client_id', 'scheduled_at'])) {
                $table->dropIndex(['client_id', 'scheduled_at']);
            }
            if (Schema::hasIndex('meetings', ['broker_id', 'scheduled_at'])) {
                $table->dropIndex(['broker_id', 'scheduled_at']);
            }

            // Drop columns
            if (Schema::hasColumn('meetings', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
            if (Schema::hasColumn('meetings', 'cancelled_by')) {
                $table->dropColumn('cancelled_by');
            }
            if (Schema::hasColumn('meetings', 'cancellation_reason')) {
                $table->dropColumn('cancellation_reason');
            }
            if (Schema::hasColumn('meetings', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }
            if (Schema::hasColumn('meetings', 'attendees')) {
                $table->dropColumn('attendees');
            }
            if (Schema::hasColumn('meetings', 'reminder_minutes')) {
                $table->dropColumn('reminder_minutes');
            }
            if (Schema::hasColumn('meetings', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
            if (Schema::hasColumn('meetings', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('meetings', 'transaction_id')) {
                $table->dropColumn('transaction_id');
            }
        });
    }
};
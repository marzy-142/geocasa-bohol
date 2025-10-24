<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the status enum to include client-centric statuses
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM(
            'inquiry',
            'initial_contact', 
            'property_viewing',
            'offer_made',
            'negotiation',
            'offer_accepted',
            'contract_signed',
            'due_diligence',
            'financing',
            'closing_preparation',
            'finalized',
            'cancelled',
            'client_approval_pending',
            'client_review_required',
            'client_rejected',
            'client_approved',
            'offer_client_review',
            'contract_review',
            'document_collection',
            'client_final_approval'
        ) DEFAULT 'inquiry'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM(
            'inquiry',
            'initial_contact',
            'property_viewing',
            'offer_made',
            'negotiation',
            'offer_accepted',
            'contract_signed',
            'due_diligence',
            'financing',
            'closing_preparation',
            'finalized',
            'cancelled'
        ) DEFAULT 'inquiry'");
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Update status enum to include 'pending' and 'archived'
            $table->enum('status', [
                'available', 
                'pending',      // New: Offer accepted, deal in progress
                'sold',         // Property sold, show for 90 days
                'archived',     // New: Removed from public view after 90 days
                'reserved',     // Legacy: Keep for backward compatibility
                'under_negotiation', 
                'off_market'
            ])->default('available')->change();
            
            // Add tracking timestamps only if they don't exist
            if (!Schema::hasColumn('properties', 'pending_at')) {
                $table->timestamp('pending_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('properties', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('sold_at');
            }
        });
        
        // Add index separately to avoid conflicts
        if (!Schema::hasIndex('properties', 'properties_status_sold_at_index')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->index(['status', 'sold_at']);
            });
        }
    }

    public function down(): void
    {
        // Make rollback idempotent: only change/drop what exists
        // Revert status enum (attempt; if it fails, let it bubble up as it's a structural concern)
        Schema::table('properties', function (Blueprint $table) {
            $table->enum('status', [
                'available', 
                'reserved', 
                'sold', 
                'under_negotiation', 
                'off_market'
            ])->default('available')->change();
        });

        // Drop new columns if they exist
        if (Schema::hasColumn('properties', 'pending_at') || Schema::hasColumn('properties', 'archived_at')) {
            Schema::table('properties', function (Blueprint $table) {
                if (Schema::hasColumn('properties', 'pending_at')) {
                    $table->dropColumn('pending_at');
                }
                if (Schema::hasColumn('properties', 'archived_at')) {
                    $table->dropColumn('archived_at');
                }
            });
        }

        // Drop index if present. dropIndex can throw if the index name/columns don't exist,
        // so wrap in try/catch to keep rollback safe.
        try {
            Schema::table('properties', function (Blueprint $table) {
                // Use the generated index name if available, otherwise drop by columns
                // Attempt dropping by the conventional name first
                $table->dropIndex('properties_status_sold_at_index');
            });
        } catch (\Exception $e) {
            try {
                Schema::table('properties', function (Blueprint $table) {
                    $table->dropIndex(['status', 'sold_at']);
                });
            } catch (\Exception $e) {
                // ignore: index already removed or never existed
            }
        }
    }
};

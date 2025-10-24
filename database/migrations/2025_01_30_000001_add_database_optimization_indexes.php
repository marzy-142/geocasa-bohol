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
        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            // Skip all indexes - they already exist from previous migrations:
            // - email: users_email_unique and users_email_index
            // - phone: users_phone_index  
            // - role combinations: users_role_is_approved_index, users_role_application_status_index
            // - created_at: users_created_at_index
            // - suspended_at: users_suspended_at_index
        });

        // Add indexes to properties table (additional to existing ones)
        Schema::table('properties', function (Blueprint $table) {
            // Skip all indexes - they already exist from create table or foreign key constraints:
            // - broker_id: foreign key constraint creates this automatically
            // - municipality + status: already exists from create table
            // - type + status: already exists from create table  
            // - price_per_sqm + lot_area_sqm: already exists from create table
            // - coordinates: already exists from create table
        });

        // Add indexes to clients table
        Schema::table('clients', function (Blueprint $table) {
            // Skip all indexes - they already exist from previous migrations:
            // - email: already exists
            // - broker_id + status: already exists
            // - broker_id: foreign key constraint creates this automatically
            // - status + created_at: already exists
            // - phone: already exists
            // - created_at: already exists
        });

        // Add indexes to inquiries table
        Schema::table('inquiries', function (Blueprint $table) {
            // Skip all indexes - they already exist from create table:
            // - property_id + status: already exists
            // - email + created_at: already exists
            // - status: already exists
            // - property_id: foreign key constraint creates this automatically
            // - client_id: foreign key constraint creates this automatically
        });

        // Add indexes to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            // Skip all indexes - they already exist from create table:
            // - broker_id + status: already exists
            // - property_id + status: already exists
            // - client_id + status: already exists
            // - status: already exists
            // - finalized_date: already exists
            // - foreign key constraints create indexes automatically
        });

        // Add indexes to seller_requests table
        Schema::table('seller_requests', function (Blueprint $table) {
            // Skip all indexes - they already exist from create table:
            // - status + created_at: already exists
            // - assigned_broker_id + status: already exists
            // - seller_email: already exists
            // - foreign key constraints create indexes automatically
        });

        // Add indexes to conversations table
        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->index(['type', 'is_archived']); // For conversation filtering
                $table->index(['is_archived', 'last_message_at']); // For active conversations by activity
                $table->index('created_at'); // For sorting
            });
        }

        // Add indexes to messages table (additional to existing ones)
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->index(['type', 'created_at']); // For message type filtering
                $table->index(['is_edited', 'created_at']); // For edited messages
            });
        }

        // Add indexes to sessions table for better session management
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->index('ip_address'); // For IP-based queries
                $table->index(['user_id', 'last_activity']); // For user session tracking
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['role']);
            $table->dropIndex(['role', 'is_approved']);
            $table->dropIndex(['role', 'application_status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['suspended_at']);
        });

        // Drop indexes from properties table
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['broker_id']);
            $table->dropIndex(['broker_id', 'created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['municipality', 'type']);
            $table->dropIndex(['price_per_sqm', 'status']);
            $table->dropIndex(['is_featured', 'status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
        });

        // Drop indexes from clients table
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['broker_id', 'created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['created_at']);
        });

        // Drop indexes from inquiries table
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['inquiry_type', 'status']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['contacted_at']);
            $table->dropIndex(['scheduled_at']);
        });

        // Drop indexes from transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['broker_id', 'created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['broker_id', 'status', 'created_at']);
            $table->dropIndex(['transaction_number']);
            $table->dropIndex(['inquiry_date']);
            $table->dropIndex(['closing_date']);
            $table->dropIndex(['created_at']);
        });

        // Drop indexes from seller_requests table
        Schema::table('seller_requests', function (Blueprint $table) {
            $table->dropIndex(['seller_email']);
            $table->dropIndex(['seller_phone']);
            $table->dropIndex(['assigned_broker_id', 'created_at']);
            $table->dropIndex(['reviewed_by', 'reviewed_at']);
            $table->dropIndex(['property_type']);
            $table->dropIndex(['city']);
            $table->dropIndex(['created_at']);
        });

        // Drop indexes from conversations table
        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropIndex(['type', 'is_archived']);
                $table->dropIndex(['is_archived', 'last_message_at']);
                $table->dropIndex(['created_at']);
            });
        }

        // Drop indexes from messages table
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropIndex(['type', 'created_at']);
                $table->dropIndex(['is_edited', 'created_at']);
            });
        }

        // Drop indexes from sessions table
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropIndex(['ip_address']);
                $table->dropIndex(['user_id', 'last_activity']);
            });
        }
    }
};
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
        // Add composite indexes for complex queries
        
        Schema::table('properties', function (Blueprint $table) {
            // Skip properties_broker_status_index - already exists
            // Skip properties_status_created_index - already exists  
            // Skip properties_municipality_type_index - already exists
            // Skip properties_price_status_index - already exists
            // All composite indexes for properties table already exist
        });
        
        Schema::table('inquiries', function (Blueprint $table) {
            // Skip inquiries_property_status_index - already exists
            // Skip inquiries_status_created_index - already exists
            // Skip inquiries_client_status_index - already exists
            // All composite indexes for inquiries table already exist
        });
        
        Schema::table('transactions', function (Blueprint $table) {
            // Skip transactions_broker_status_index - already exists
            // Skip transactions_property_status_index - already exists
            // Skip transactions_status_created_index - already exists
            // Skip transactions_broker_commission_index - already exists
            // All composite indexes for transactions table already exist
        });
        
        Schema::table('clients', function (Blueprint $table) {
            // Skip clients_broker_status_index - already exists
            // Skip clients_status_created_index - already exists
            // All composite indexes for clients table already exist
        });
        
        Schema::table('seller_requests', function (Blueprint $table) {
            // Skip seller_requests_status_created_index - already exists
            // Skip location index - barangay column doesn't exist in seller_requests table
            // All composite indexes for seller_requests table already exist or are not applicable
        });
        
        Schema::table('conversations', function (Blueprint $table) {
            // Skip property_id index - conversations table doesn't have property_id column
            // Skip conversations_inquiry_updated_index - already exists
            // All composite indexes for conversations table already exist
        });
        
        Schema::table('messages', function (Blueprint $table) {
            // Skip messages_conversation_created_index - already exists as ['conversation_id', 'created_at']
            // Skip user index - table uses sender_id, and ['sender_id', 'created_at'] already exists
            // All composite indexes for messages table already exist
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Skip users_role_application_status_index - already exists
            // Add missing composite index for role + created_at for registration analytics
            $table->index(['role', 'created_at'], 'users_role_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('properties_broker_status_index');
            $table->dropIndex('properties_status_created_index');
            $table->dropIndex('properties_municipality_type_index');
            $table->dropIndex('properties_price_status_index');
        });
        
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropIndex('inquiries_property_status_index');
            $table->dropIndex('inquiries_status_created_index');
            $table->dropIndex('inquiries_client_status_index');
        });
        
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_broker_status_index');
            $table->dropIndex('transactions_property_status_index');
            $table->dropIndex('transactions_status_created_index');
            $table->dropIndex('transactions_broker_commission_index');
        });
        
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('clients_broker_status_index');
            $table->dropIndex('clients_status_created_index');
        });
        
        Schema::table('seller_requests', function (Blueprint $table) {
            $table->dropIndex('seller_requests_status_created_index');
            $table->dropIndex('seller_requests_location_index');
        });
        
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('conversations_property_updated_index');
        });
        
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_conversation_created_index');
            $table->dropIndex('messages_user_created_index');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_status_index');
            $table->dropIndex('users_role_created_index');
        });
    }
};
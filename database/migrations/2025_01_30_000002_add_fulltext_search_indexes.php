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
        // Add full-text search indexes for better search performance
        
        // Skip properties table - search_index already exists
        // Skip users table - search_index already exists
        // Skip clients table - search_index already exists
        // Skip inquiries table - search_index already exists
        // Skip transactions table - search_index already exists
        
        // Seller requests table - for seller request search
        if (Schema::hasTable('seller_requests')) {
            DB::statement('ALTER TABLE seller_requests ADD FULLTEXT search_index (seller_name, seller_email, property_description, city)');
        }
        
        // Messages table - for message content search
        if (Schema::hasTable('messages')) {
            DB::statement('ALTER TABLE messages ADD FULLTEXT search_index (content)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop full-text search indexes
        
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropIndex('search_index');
            });
        }
        
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('search_index');
            });
        }
        
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropIndex('search_index');
            });
        }
        
        if (Schema::hasTable('inquiries')) {
            Schema::table('inquiries', function (Blueprint $table) {
                $table->dropIndex('search_index');
            });
        }
        
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('search_index');
            });
        }
        
        if (Schema::hasTable('seller_requests')) {
            Schema::table('seller_requests', function (Blueprint $table) {
                $table->dropIndex('search_index');
            });
        }
        
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropIndex('search_index');
            });
        }
    }
};
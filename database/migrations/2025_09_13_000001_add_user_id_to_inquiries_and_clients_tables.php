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
        // Add user_id to inquiries table
        Schema::table('inquiries', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('client_id')->constrained()->onDelete('set null');
            $table->index(['user_id', 'created_at']);
        });

        // Add user_id to clients table
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('broker_id')->constrained()->onDelete('set null');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropColumn('user_id');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropColumn('user_id');
        });
    }
};
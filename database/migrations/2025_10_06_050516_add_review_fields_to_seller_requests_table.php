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
        Schema::table('seller_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('assigned_broker_id');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('admin_notes')->nullable()->after('reviewed_at');
            $table->text('rejection_reason')->nullable()->after('admin_notes');
            
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_requests', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['reviewed_by', 'reviewed_at', 'admin_notes', 'rejection_reason']);
        });
    }
};
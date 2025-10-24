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
        Schema::table('inquiries', function (Blueprint $table) {
            $table->boolean('is_flagged')->default(false)->after('responded_at');
            $table->string('flag_reason')->nullable()->after('is_flagged');
            $table->timestamp('flagged_at')->nullable()->after('flag_reason');
            $table->foreignId('flagged_by')->nullable()->constrained('users')->onDelete('set null')->after('flagged_at');
            
            // Add index for flagged inquiries
            $table->index('is_flagged');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropIndex(['is_flagged']);
            $table->dropForeign(['flagged_by']);
            $table->dropColumn(['is_flagged', 'flag_reason', 'flagged_at', 'flagged_by']);
        });
    }
};


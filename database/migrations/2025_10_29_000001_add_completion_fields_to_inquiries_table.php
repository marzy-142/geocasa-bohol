<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            // Keep it lightweight and nullable; enforce semantics in app validation
            if (!Schema::hasColumn('inquiries', 'completion_outcome')) {
                // Laravel 11 supports enum; fallback could be string if needed
                if (method_exists($table, 'enum')) {
                    $table->enum('completion_outcome', ['won','lost','no_response','other'])->nullable()->after('status');
                } else {
                    $table->string('completion_outcome', 32)->nullable()->after('status');
                }
            }
            if (!Schema::hasColumn('inquiries', 'completion_reason')) {
                $table->string('completion_reason', 255)->nullable()->after('completion_outcome');
            }
            if (!Schema::hasColumn('inquiries', 'completion_notes')) {
                $table->text('completion_notes')->nullable()->after('completion_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            if (Schema::hasColumn('inquiries', 'completion_notes')) {
                $table->dropColumn('completion_notes');
            }
            if (Schema::hasColumn('inquiries', 'completion_reason')) {
                $table->dropColumn('completion_reason');
            }
            if (Schema::hasColumn('inquiries', 'completion_outcome')) {
                $table->dropColumn('completion_outcome');
            }
        });
    }
};

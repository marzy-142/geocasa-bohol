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
            $table->string('title_type')->nullable()->after('lot_area');
            $table->string('zoning_classification')->nullable()->after('title_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_requests', function (Blueprint $table) {
            $table->dropColumn(['title_type', 'zoning_classification']);
        });
    }
};

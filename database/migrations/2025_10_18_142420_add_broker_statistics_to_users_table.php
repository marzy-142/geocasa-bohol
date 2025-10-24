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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('finalized_transactions_count')->default(0)->after('privacy_policy_accepted');
            $table->decimal('total_commission_earned', 15, 2)->default(0)->after('finalized_transactions_count');
            $table->timestamp('last_sale_date')->nullable()->after('total_commission_earned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['finalized_transactions_count', 'total_commission_earned', 'last_sale_date']);
        });
    }
};
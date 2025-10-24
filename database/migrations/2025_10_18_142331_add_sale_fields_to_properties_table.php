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
        Schema::table('properties', function (Blueprint $table) {
            $table->timestamp('sold_at')->nullable()->after('updated_at');
            $table->decimal('sold_price', 15, 2)->nullable()->after('sold_at');
            $table->foreignId('sold_to_client_id')->nullable()->constrained('clients')->onDelete('set null')->after('sold_price');
            $table->foreignId('sold_via_transaction_id')->nullable()->constrained('transactions')->onDelete('set null')->after('sold_to_client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['sold_to_client_id']);
            $table->dropForeign(['sold_via_transaction_id']);
            $table->dropColumn(['sold_at', 'sold_price', 'sold_to_client_id', 'sold_via_transaction_id']);
        });
    }
};
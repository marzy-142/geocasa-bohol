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
        Schema::table('clients', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['broker_id']);
            
            // Make broker_id nullable and recreate the foreign key
            $table->foreignId('broker_id')->nullable()->change();
            $table->foreign('broker_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Drop the nullable foreign key
            $table->dropForeign(['broker_id']);
            
            // Recreate as non-nullable (original state)
            $table->foreignId('broker_id')->nullable(false)->change();
            $table->foreign('broker_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};

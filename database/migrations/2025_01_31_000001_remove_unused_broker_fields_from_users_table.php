<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove unused fields that are not reflected in frontend
            $table->dropColumn([
                'specialization',
                'company_address', // Using separate address fields instead
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restore fields if needed
            $table->string('specialization')->nullable()->after('years_experience');
            $table->text('company_address')->nullable()->after('postal_code');
        });
    }
};
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
            // Contact information
            $table->string('phone')->nullable()->after('email');
            
            // Address fields
            $table->text('address')->nullable()->after('phone');
            $table->string('city')->nullable()->after('address');
            $table->string('province')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('province');
            
            // Broker-specific fields
            $table->text('company_address')->nullable()->after('postal_code');
            $table->integer('years_experience')->nullable()->after('company_address');
            $table->string('specialization')->nullable()->after('years_experience');
            
            // Agreement fields
            $table->boolean('terms_accepted')->default(false)->after('specialization');
            $table->boolean('privacy_policy_accepted')->default(false)->after('terms_accepted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'address', 'city', 'province', 'postal_code',
                'company_address', 'years_experience', 'specialization',
                'terms_accepted', 'privacy_policy_accepted'
            ]);
        });
    }
};
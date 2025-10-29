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
            // Only add fields that don't already exist
            // phone and address already exist from broker profile migration
            // bio already exists
            $table->string('avatar')->nullable()->after('bio');
            $table->json('notification_preferences')->nullable()->after('avatar');
            $table->json('privacy_settings')->nullable()->after('notification_preferences');
            $table->boolean('is_active')->default(true)->after('privacy_settings');
            $table->timestamp('deactivated_at')->nullable()->after('is_active');
            $table->string('deactivation_reason', 500)->nullable()->after('deactivated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar',
                'notification_preferences',
                'privacy_settings',
                'is_active',
                'deactivated_at',
                'deactivation_reason',
            ]);
        });
    }
};

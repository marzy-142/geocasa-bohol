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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Email notification preferences
            $table->boolean('email_new_inquiry')->default(true);
            $table->boolean('email_transaction_updates')->default(true);
            $table->boolean('email_messages')->default(true);
            $table->boolean('email_seller_requests')->default(true);
            $table->enum('email_frequency', ['immediate', 'hourly', 'daily', 'weekly'])->default('immediate');
            
            // SMS notification preferences
            $table->string('phone_number')->nullable();
            $table->boolean('sms_urgent_inquiries')->default(false);
            $table->boolean('sms_transaction_milestones')->default(false);
            
            // Browser notification preferences
            $table->boolean('browser_notifications')->default(false);
            
            // Quiet hours
            $table->time('quiet_hours_start')->default('22:00:00');
            $table->time('quiet_hours_end')->default('08:00:00');
            
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};

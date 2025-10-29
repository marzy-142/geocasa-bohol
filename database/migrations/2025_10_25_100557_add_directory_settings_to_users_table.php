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
            // Public directory visibility
            $table->boolean('show_in_directory')
                  ->default(true)
                  ->after('auto_manage_availability')
                  ->comment('Show broker in public directory');
            
            // Contact preferences
            $table->boolean('show_email_in_directory')
                  ->default(false)
                  ->after('show_in_directory')
                  ->comment('Display email publicly');
            
            $table->boolean('show_phone_in_directory')
                  ->default(true)
                  ->after('show_email_in_directory')
                  ->comment('Display phone publicly');
            
            $table->boolean('accept_directory_inquiries')
                  ->default(true)
                  ->after('show_phone_in_directory')
                  ->comment('Accept inquiries via directory contact form');
            
            // Profile enhancements
            $table->text('bio')
                  ->nullable()
                  ->after('accept_directory_inquiries')
                  ->comment('Broker biography for public profile');
            
            $table->json('specializations')
                  ->nullable()
                  ->after('bio')
                  ->comment('Broker specializations (residential, commercial, etc.)');
            
            $table->json('service_areas')
                  ->nullable()
                  ->after('specializations')
                  ->comment('Cities/municipalities served');
            
            $table->string('profile_image')
                  ->nullable()
                  ->after('service_areas')
                  ->comment('Broker profile photo');
            
            $table->string('website')
                  ->nullable()
                  ->after('profile_image')
                  ->comment('Broker/firm website');
            
            $table->string('facebook')
                  ->nullable()
                  ->after('website')
                  ->comment('Facebook profile/page');
            
            $table->string('linkedin')
                  ->nullable()
                  ->after('facebook')
                  ->comment('LinkedIn profile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'show_in_directory',
                'show_email_in_directory',
                'show_phone_in_directory',
                'accept_directory_inquiries',
                'bio',
                'specializations',
                'service_areas',
                'profile_image',
                'website',
                'facebook',
                'linkedin'
            ]);
        });
    }
};

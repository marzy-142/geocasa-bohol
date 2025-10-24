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
            // Add client_id column for linking to clients table
            $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->onDelete('cascade');
            
            // Add additional fields that were in the controller but not in the model
            $table->string('municipality')->nullable()->after('city');
            $table->string('barangay')->nullable()->after('municipality');
            $table->decimal('floor_area', 10, 2)->nullable()->after('lot_area');
            $table->integer('bedrooms')->nullable()->after('floor_area');
            $table->integer('bathrooms')->nullable()->after('bedrooms');
            $table->decimal('price_expectation', 15, 2)->nullable()->after('asking_price');
            $table->text('description')->nullable()->after('property_description');
            $table->string('contact_name')->nullable()->after('name');
            $table->string('contact_email')->nullable()->after('email');
            $table->string('contact_phone')->nullable()->after('phone');
            $table->json('images')->nullable()->after('uploaded_images');
            $table->json('documents')->nullable()->after('property_documents');
            $table->timestamp('submission_date')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_requests', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn([
                'client_id',
                'municipality',
                'barangay',
                'floor_area',
                'bedrooms',
                'bathrooms',
                'price_expectation',
                'description',
                'contact_name',
                'contact_email',
                'contact_phone',
                'images',
                'documents',
                'submission_date',
            ]);
        });
    }
};

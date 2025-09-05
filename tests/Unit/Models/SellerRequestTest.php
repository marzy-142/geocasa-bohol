<?php

namespace Tests\Unit\Models;

use App\Models\SellerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_seller_request_has_fillable_attributes(): void
    {
        $fillable = [
            'name', 'email', 'phone', 'property_title', 'property_description',
            'property_type', 'listing_type', 'asking_price', 'address', 'city',
            'province', 'lot_area_sqm', 'floor_area_sqm', 'bedrooms', 'bathrooms',
            'parking_spaces', 'year_built', 'property_features', 'images',
            'documents', 'preferred_contact_method', 'best_time_to_contact',
            'urgency', 'additional_notes', 'marketing_preferences', 'terms_accepted',
            'status', 'admin_notes', 'processed_at', 'processed_by'
        ];

        $sellerRequest = new SellerRequest();
        $this->assertEquals($fillable, $sellerRequest->getFillable());
    }

    public function test_seller_request_casts_attributes_correctly(): void
    {
        $sellerRequest = new SellerRequest();
        $casts = $sellerRequest->getCasts();

        $this->assertEquals('array', $casts['property_features']);
        $this->assertEquals('array', $casts['images']);
        $this->assertEquals('array', $casts['documents']);
        $this->assertEquals('array', $casts['marketing_preferences']);
        $this->assertEquals('boolean', $casts['terms_accepted']);
        $this->assertEquals('datetime', $casts['processed_at']);
        $this->assertEquals('decimal:2', $casts['asking_price']);
    }

    public function test_seller_request_can_be_created_with_required_attributes(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '09123456789',
            'property_title' => 'Beautiful Beachfront Lot',
            'property_description' => 'A stunning beachfront property',
            'property_type' => 'residential_lot',
            'listing_type' => 'sale',
            'asking_price' => 5000000.00,
            'address' => '123 Beach Road',
            'city' => 'Panglao',
            'province' => 'Bohol',
            'lot_area_sqm' => 1000,
            'preferred_contact_method' => 'email',
            'urgency' => 'medium',
            'terms_accepted' => true,
            'status' => 'pending'
        ];

        $sellerRequest = SellerRequest::create($requestData);

        $this->assertInstanceOf(SellerRequest::class, $sellerRequest);
        $this->assertEquals('John Doe', $sellerRequest->name);
        $this->assertEquals('john@example.com', $sellerRequest->email);
        $this->assertEquals('Beautiful Beachfront Lot', $sellerRequest->property_title);
        $this->assertEquals('residential_lot', $sellerRequest->property_type);
        $this->assertEquals('sale', $sellerRequest->listing_type);
        $this->assertEquals(5000000.00, $sellerRequest->asking_price);
        $this->assertEquals('pending', $sellerRequest->status);
        $this->assertTrue($sellerRequest->terms_accepted);
    }

    public function test_seller_request_property_features_are_cast_to_array(): void
    {
        $features = ['swimming_pool', 'garden', 'garage', 'security_system'];
        $sellerRequest = SellerRequest::factory()->create(['property_features' => $features]);

        $this->assertIsArray($sellerRequest->property_features);
        $this->assertEquals($features, $sellerRequest->property_features);
    }

    public function test_seller_request_images_are_cast_to_array(): void
    {
        $images = ['image1.jpg', 'image2.jpg', 'image3.jpg'];
        $sellerRequest = SellerRequest::factory()->create(['images' => $images]);

        $this->assertIsArray($sellerRequest->images);
        $this->assertEquals($images, $sellerRequest->images);
    }

    public function test_seller_request_documents_are_cast_to_array(): void
    {
        $documents = ['title_deed.pdf', 'tax_declaration.pdf'];
        $sellerRequest = SellerRequest::factory()->create(['documents' => $documents]);

        $this->assertIsArray($sellerRequest->documents);
        $this->assertEquals($documents, $sellerRequest->documents);
    }

    public function test_seller_request_marketing_preferences_are_cast_to_array(): void
    {
        $preferences = ['online_listing', 'social_media', 'print_ads'];
        $sellerRequest = SellerRequest::factory()->create(['marketing_preferences' => $preferences]);

        $this->assertIsArray($sellerRequest->marketing_preferences);
        $this->assertEquals($preferences, $sellerRequest->marketing_preferences);
    }

    public function test_seller_request_terms_accepted_is_cast_to_boolean(): void
    {
        $sellerRequest = SellerRequest::factory()->create(['terms_accepted' => true]);

        $this->assertIsBool($sellerRequest->terms_accepted);
        $this->assertTrue($sellerRequest->terms_accepted);
    }

    public function test_seller_request_asking_price_is_cast_to_decimal(): void
    {
        $sellerRequest = SellerRequest::factory()->create(['asking_price' => 2500000.50]);

        $this->assertEquals('2500000.50', $sellerRequest->asking_price);
    }

    public function test_seller_request_processed_at_is_cast_to_datetime(): void
    {
        $processedAt = now();
        $sellerRequest = SellerRequest::factory()->create(['processed_at' => $processedAt]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $sellerRequest->processed_at);
        $this->assertEquals($processedAt->format('Y-m-d H:i:s'), $sellerRequest->processed_at->format('Y-m-d H:i:s'));
    }

    public function test_seller_request_can_be_created_with_all_attributes(): void
    {
        $requestData = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '09987654321',
            'property_title' => 'Modern House and Lot',
            'property_description' => 'A modern 3-bedroom house with lot',
            'property_type' => 'house_and_lot',
            'listing_type' => 'sale',
            'asking_price' => 8500000.00,
            'address' => '456 Subdivision Road',
            'city' => 'Tagbilaran',
            'province' => 'Bohol',
            'lot_area_sqm' => 300,
            'floor_area_sqm' => 150,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'parking_spaces' => 2,
            'year_built' => 2020,
            'property_features' => ['swimming_pool', 'garden', 'garage'],
            'images' => ['house1.jpg', 'house2.jpg'],
            'documents' => ['title.pdf', 'tax_dec.pdf'],
            'preferred_contact_method' => 'phone',
            'best_time_to_contact' => 'morning',
            'urgency' => 'high',
            'additional_notes' => 'Urgent sale needed',
            'marketing_preferences' => ['online_listing', 'social_media'],
            'terms_accepted' => true,
            'status' => 'pending'
        ];

        $sellerRequest = SellerRequest::create($requestData);

        $this->assertInstanceOf(SellerRequest::class, $sellerRequest);
        $this->assertEquals('Jane Smith', $sellerRequest->name);
        $this->assertEquals('Modern House and Lot', $sellerRequest->property_title);
        $this->assertEquals('house_and_lot', $sellerRequest->property_type);
        $this->assertEquals(8500000.00, $sellerRequest->asking_price);
        $this->assertEquals(3, $sellerRequest->bedrooms);
        $this->assertEquals(2, $sellerRequest->bathrooms);
        $this->assertEquals('high', $sellerRequest->urgency);
        $this->assertIsArray($sellerRequest->property_features);
        $this->assertIsArray($sellerRequest->images);
        $this->assertIsArray($sellerRequest->documents);
        $this->assertIsArray($sellerRequest->marketing_preferences);
    }

    public function test_seller_request_factory_creates_valid_request(): void
    {
        $sellerRequest = SellerRequest::factory()->create();

        $this->assertInstanceOf(SellerRequest::class, $sellerRequest);
        $this->assertNotEmpty($sellerRequest->name);
        $this->assertNotEmpty($sellerRequest->email);
        $this->assertNotEmpty($sellerRequest->property_title);
        $this->assertNotEmpty($sellerRequest->property_type);
        $this->assertNotEmpty($sellerRequest->listing_type);
        $this->assertNotNull($sellerRequest->asking_price);
        $this->assertNotEmpty($sellerRequest->address);
        $this->assertNotEmpty($sellerRequest->city);
        $this->assertNotEmpty($sellerRequest->province);
        $this->assertNotEmpty($sellerRequest->preferred_contact_method);
        $this->assertNotEmpty($sellerRequest->urgency);
        $this->assertTrue($sellerRequest->terms_accepted);
        $this->assertEquals('pending', $sellerRequest->status);
    }

    public function test_seller_request_can_be_processed(): void
    {
        $sellerRequest = SellerRequest::factory()->create(['status' => 'pending']);
        $processedAt = now();
        $processedBy = 1; // Admin user ID

        $sellerRequest->update([
            'status' => 'approved',
            'processed_at' => $processedAt,
            'processed_by' => $processedBy,
            'admin_notes' => 'Request approved and property listing created'
        ]);

        $this->assertEquals('approved', $sellerRequest->status);
        $this->assertNotNull($sellerRequest->processed_at);
        $this->assertEquals($processedBy, $sellerRequest->processed_by);
        $this->assertEquals('Request approved and property listing created', $sellerRequest->admin_notes);
    }

    public function test_seller_request_can_be_rejected(): void
    {
        $sellerRequest = SellerRequest::factory()->create(['status' => 'pending']);

        $sellerRequest->update([
            'status' => 'rejected',
            'processed_at' => now(),
            'processed_by' => 1,
            'admin_notes' => 'Incomplete documentation provided'
        ]);

        $this->assertEquals('rejected', $sellerRequest->status);
        $this->assertNotNull($sellerRequest->processed_at);
        $this->assertEquals('Incomplete documentation provided', $sellerRequest->admin_notes);
    }

    public function test_seller_request_soft_deletes(): void
    {
        $sellerRequest = SellerRequest::factory()->create();
        $requestId = $sellerRequest->id;

        $sellerRequest->delete();

        $this->assertSoftDeleted('seller_requests', ['id' => $requestId]);
        $this->assertNull(SellerRequest::find($requestId));
        $this->assertNotNull(SellerRequest::withTrashed()->find($requestId));
    }
}
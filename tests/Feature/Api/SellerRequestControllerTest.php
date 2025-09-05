<?php

namespace Tests\Feature\Api;

use App\Models\SellerRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SellerRequestControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
        Storage::fake('public');
    }

    public function test_can_create_seller_request_with_required_fields(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '09123456789',
            'property_title' => 'Beautiful Beachfront Lot',
            'property_description' => 'A stunning beachfront property with amazing views',
            'property_type' => 'residential_lot',
            'listing_type' => 'sale',
            'asking_price' => 5000000,
            'address' => '123 Beach Road',
            'city' => 'Panglao',
            'province' => 'Bohol',
            'lot_area_sqm' => 1000,
            'preferred_contact_method' => 'email',
            'urgency' => 'medium',
            'terms_accepted' => true
        ];

        $response = $this->postJson('/api/v1/seller-requests', $requestData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id',
                         'name',
                         'email',
                         'phone',
                         'property_title',
                         'property_description',
                         'property_type',
                         'listing_type',
                         'asking_price',
                         'address',
                         'city',
                         'province',
                         'status',
                         'created_at',
                         'updated_at'
                     ]
                 ]);

        $this->assertDatabaseHas('seller_requests', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'property_title' => 'Beautiful Beachfront Lot',
            'status' => 'pending'
        ]);
    }

    public function test_can_create_seller_request_with_file_uploads(): void
    {
        $image1 = UploadedFile::fake()->image('property1.jpg', 800, 600);
        $image2 = UploadedFile::fake()->image('property2.jpg', 800, 600);
        $document = UploadedFile::fake()->create('title_deed.pdf', 1024, 'application/pdf');

        $requestData = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '09987654321',
            'property_title' => 'Mountain View Lot',
            'property_description' => 'Peaceful mountain view property',
            'property_type' => 'residential_lot',
            'listing_type' => 'sale',
            'asking_price' => 3000000,
            'address' => '456 Mountain Road',
            'city' => 'Tagbilaran',
            'province' => 'Bohol',
            'lot_area_sqm' => 800,
            'preferred_contact_method' => 'phone',
            'urgency' => 'high',
            'terms_accepted' => true,
            'images' => [$image1, $image2],
            'documents' => [$document]
        ];

        $response = $this->postJson('/api/v1/seller-requests', $requestData);

        $response->assertStatus(201);

        $sellerRequest = SellerRequest::where('email', 'jane@example.com')->first();
        $this->assertNotNull($sellerRequest);
        $this->assertNotEmpty($sellerRequest->images);
        $this->assertNotEmpty($sellerRequest->documents);
    }

    public function test_validation_fails_for_missing_required_fields(): void
    {
        $response = $this->postJson('/api/v1/seller-requests', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'name',
                     'property_title',
                     'listing_type',
                     'address',
                     'city',
                     'province',
                     'preferred_contact_method',
                     'urgency',
                     'terms_accepted'
                 ]);
    }

    public function test_validation_fails_for_invalid_email(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'property_title' => 'Test Property',
            'listing_type' => 'sale',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'preferred_contact_method' => 'email',
            'urgency' => 'medium',
            'terms_accepted' => true
        ];

        $response = $this->postJson('/api/v1/seller-requests', $requestData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_validation_fails_for_invalid_phone_format(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'phone' => '123',
            'property_title' => 'Test Property',
            'listing_type' => 'sale',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'preferred_contact_method' => 'phone',
            'urgency' => 'medium',
            'terms_accepted' => true
        ];

        $response = $this->postJson('/api/v1/seller-requests', $requestData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['phone']);
    }

    public function test_validation_fails_for_invalid_property_type(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'property_title' => 'Test Property',
            'property_type' => 'invalid_type',
            'listing_type' => 'sale',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'preferred_contact_method' => 'email',
            'urgency' => 'medium',
            'terms_accepted' => true
        ];

        $response = $this->postJson('/api/v1/seller-requests', $requestData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['property_type']);
    }

    public function test_validation_fails_for_invalid_listing_type(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'property_title' => 'Test Property',
            'listing_type' => 'invalid_listing',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'preferred_contact_method' => 'email',
            'urgency' => 'medium',
            'terms_accepted' => true
        ];

        $response = $this->postJson('/api/v1/seller-requests', $requestData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['listing_type']);
    }

    public function test_validation_fails_for_terms_not_accepted(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'property_title' => 'Test Property',
            'listing_type' => 'sale',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'preferred_contact_method' => 'email',
            'urgency' => 'medium',
            'terms_accepted' => false
        ];

        $response = $this->postJson('/api/v1/seller-requests', $requestData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['terms_accepted']);
    }

    public function test_can_handle_large_file_uploads(): void
    {
        // Test with maximum allowed file sizes
        $largeImage = UploadedFile::fake()->image('large_property.jpg', 2000, 2000)->size(5000); // 5MB
        $largeDocument = UploadedFile::fake()->create('large_document.pdf', 10000, 'application/pdf'); // 10MB

        $requestData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'property_title' => 'Test Property',
            'listing_type' => 'sale',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'Test Province',
            'preferred_contact_method' => 'email',
            'urgency' => 'medium',
            'terms_accepted' => true,
            'images' => [$largeImage],
            'documents' => [$largeDocument]
        ];

        $response = $this->postJson('/api/v1/seller-requests', $requestData);

        $response->assertStatus(201);
    }

    public function test_authenticated_user_can_view_own_seller_requests(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        SellerRequest::factory()->count(3)->create(['email' => $user->email]);
        SellerRequest::factory()->count(2)->create(); // Other user's requests

        $response = $this->getJson('/api/v1/seller-requests');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => [
                             'id',
                             'name',
                             'email',
                             'property_title',
                             'status',
                             'created_at'
                         ]
                     ]
                 ]);

        // Should only return user's own requests
        $this->assertEquals(3, count($response->json('data')));
    }
}
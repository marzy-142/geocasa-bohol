<?php

namespace Tests\Feature\Broker;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrokerControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
        Storage::fake('public');
    }

    public function test_approved_broker_can_access_dashboard(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        // Create test data for broker dashboard
        Property::factory()->count(5)->create(['broker_id' => $broker->id]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        Inquiry::factory()->count(3)->create(['property_id' => $property->id]);
        Transaction::factory()->count(2)->create([
            'broker_id' => $broker->id,
            'property_id' => $property->id
        ]);

        $response = $this->actingAs($broker)->get('/broker/dashboard');

        $response->assertStatus(200)
                 ->assertViewIs('broker.dashboard')
                 ->assertViewHas(['totalProperties', 'totalInquiries', 'totalTransactions', 'recentInquiries']);

        $viewData = $response->viewData();
        $this->assertEquals(6, $viewData['totalProperties']); // 5 + 1
        $this->assertEquals(3, $viewData['totalInquiries']);
        $this->assertEquals(2, $viewData['totalTransactions']);
    }

    public function test_unapproved_broker_cannot_access_dashboard(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => false,
            'application_status' => 'pending'
        ]);

        $response = $this->actingAs($broker)->get('/broker/dashboard');

        $response->assertStatus(403);
    }

    public function test_broker_can_view_own_properties(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $otherBroker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        Property::factory()->count(5)->create(['broker_id' => $broker->id]);
        Property::factory()->count(3)->create(['broker_id' => $otherBroker->id]);

        $response = $this->actingAs($broker)->get('/broker/properties');

        $response->assertStatus(200)
                 ->assertViewIs('broker.properties.index')
                 ->assertViewHas('properties');

        $properties = $response->viewData('properties');
        $this->assertCount(5, $properties);
        
        foreach ($properties as $property) {
            $this->assertEquals($broker->id, $property->broker_id);
        }
    }

    public function test_broker_can_create_property(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        $propertyData = [
            'title' => 'Beautiful Beach House',
            'description' => 'A stunning beachfront property with amazing views.',
            'property_type' => 'house',
            'listing_type' => 'sale',
            'price' => 5000000,
            'location' => 'Panglao Island, Bohol',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'lot_area' => 500,
            'floor_area' => 200,
            'road_access' => true,
            'water_source' => true,
            'electricity' => true,
            'internet' => true,
            'status' => 'available'
        ];

        $response = $this->actingAs($broker)->post('/broker/properties', $propertyData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('properties', [
            'broker_id' => $broker->id,
            'title' => 'Beautiful Beach House',
            'property_type' => 'house',
            'listing_type' => 'sale',
            'price' => 5000000,
            'status' => 'available'
        ]);
    }

    public function test_broker_can_create_property_with_images(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        $propertyData = [
            'title' => 'Modern Condo Unit',
            'description' => 'A modern condominium unit in the city center.',
            'property_type' => 'condo',
            'listing_type' => 'rent',
            'price' => 25000,
            'location' => 'Tagbilaran City, Bohol',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'images' => [
                UploadedFile::fake()->image('property1.jpg'),
                UploadedFile::fake()->image('property2.jpg')
            ]
        ];

        $response = $this->actingAs($broker)->post('/broker/properties', $propertyData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $property = Property::where('title', 'Modern Condo Unit')->first();
        $this->assertNotNull($property);
        $this->assertCount(2, $property->images);
        
        foreach ($property->images as $image) {
            Storage::disk('public')->assertExists($image);
        }
    }

    public function test_broker_can_update_own_property(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'title' => 'Original Title'
        ]);

        $updateData = [
            'title' => 'Updated Property Title',
            'description' => 'Updated description',
            'price' => 6000000,
            'status' => 'sold'
        ];

        $response = $this->actingAs($broker)
                         ->put("/broker/properties/{$property->id}", $updateData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => 'Updated Property Title',
            'price' => 6000000,
            'status' => 'sold'
        ]);
    }

    public function test_broker_cannot_update_other_broker_property(): void
    {
        $broker1 = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $broker2 = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker2->id]);

        $updateData = [
            'title' => 'Unauthorized Update',
            'price' => 1000000
        ];

        $response = $this->actingAs($broker1)
                         ->put("/broker/properties/{$property->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_broker_can_delete_own_property(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $response = $this->actingAs($broker)
                         ->delete("/broker/properties/{$property->id}");

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_broker_can_view_property_inquiries(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        Inquiry::factory()->count(4)->create([
            'property_id' => $property->id,
            'client_id' => $client->id
        ]);

        // Create inquiries for other properties
        Inquiry::factory()->count(2)->create();

        $response = $this->actingAs($broker)->get('/broker/inquiries');

        $response->assertStatus(200)
                 ->assertViewIs('broker.inquiries.index')
                 ->assertViewHas('inquiries');

        $inquiries = $response->viewData('inquiries');
        $this->assertCount(4, $inquiries);
        
        foreach ($inquiries as $inquiry) {
            $this->assertEquals($broker->id, $inquiry->property->broker_id);
        }
    }

    public function test_broker_can_respond_to_inquiry(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        $inquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'status' => 'pending'
        ]);

        $responseData = [
            'response' => 'Thank you for your interest. I would be happy to schedule a viewing.',
            'status' => 'responded'
        ];

        $response = $this->actingAs($broker)
                         ->put("/broker/inquiries/{$inquiry->id}", $responseData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'id' => $inquiry->id,
            'response' => 'Thank you for your interest. I would be happy to schedule a viewing.',
            'status' => 'responded'
        ]);
    }

    public function test_broker_can_view_own_transactions(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        Transaction::factory()->count(3)->create([
            'broker_id' => $broker->id,
            'client_id' => $client->id,
            'property_id' => $property->id
        ]);

        // Create transactions for other brokers
        Transaction::factory()->count(2)->create();

        $response = $this->actingAs($broker)->get('/broker/transactions');

        $response->assertStatus(200)
                 ->assertViewIs('broker.transactions.index')
                 ->assertViewHas('transactions');

        $transactions = $response->viewData('transactions');
        $this->assertCount(3, $transactions);
        
        foreach ($transactions as $transaction) {
            $this->assertEquals($broker->id, $transaction->broker_id);
        }
    }

    public function test_broker_can_create_transaction(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'price' => 5000000
        ]);

        $transactionData = [
            'property_id' => $property->id,
            'client_id' => $client->id,
            'transaction_type' => 'sale',
            'amount' => 5000000,
            'commission_rate' => 5.0,
            'status' => 'pending'
        ];

        $response = $this->actingAs($broker)->post('/broker/transactions', $transactionData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'broker_id' => $broker->id,
            'client_id' => $client->id,
            'property_id' => $property->id,
            'transaction_type' => 'sale',
            'amount' => 5000000,
            'commission_rate' => 5.0
        ]);
    }

    public function test_broker_can_update_profile(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        $updateData = [
            'name' => 'Updated Broker Name',
            'email' => 'updated.broker@example.com',
            'phone' => '+639987654321',
            'address' => '456 Updated Street, Tagbilaran City',
            'license_number' => 'LIC-2024-001',
            'years_experience' => 10
        ];

        $response = $this->actingAs($broker)
                         ->put('/broker/profile', $updateData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $broker->id,
            'name' => 'Updated Broker Name',
            'email' => 'updated.broker@example.com',
            'phone' => '+639987654321'
        ]);
    }

    public function test_broker_can_filter_properties_by_status(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        Property::factory()->count(3)->create([
            'broker_id' => $broker->id,
            'status' => 'available'
        ]);
        Property::factory()->count(2)->create([
            'broker_id' => $broker->id,
            'status' => 'sold'
        ]);

        $response = $this->actingAs($broker)->get('/broker/properties?status=available');

        $response->assertStatus(200);
        $properties = $response->viewData('properties');
        $this->assertCount(3, $properties);
        
        foreach ($properties as $property) {
            $this->assertEquals('available', $property->status);
        }
    }

    public function test_broker_can_search_properties(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        Property::factory()->create([
            'broker_id' => $broker->id,
            'title' => 'Beach House Paradise'
        ]);
        Property::factory()->create([
            'broker_id' => $broker->id,
            'title' => 'Mountain View Condo'
        ]);

        $response = $this->actingAs($broker)->get('/broker/properties?search=Beach');

        $response->assertStatus(200);
        $properties = $response->viewData('properties');
        $this->assertCount(1, $properties);
        $this->assertEquals('Beach House Paradise', $properties->first()->title);
    }

    public function test_broker_property_validation_fails_for_missing_fields(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        $response = $this->actingAs($broker)->post('/broker/properties', []);

        $response->assertSessionHasErrors([
            'title',
            'description',
            'property_type',
            'listing_type',
            'price',
            'location'
        ]);
    }

    public function test_broker_can_upload_property_documents(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        $propertyData = [
            'title' => 'Property with Documents',
            'description' => 'A property with legal documents.',
            'property_type' => 'house',
            'listing_type' => 'sale',
            'price' => 3000000,
            'location' => 'Bohol, Philippines',
            'documents' => [
                UploadedFile::fake()->create('title_deed.pdf', 1000, 'application/pdf'),
                UploadedFile::fake()->create('tax_declaration.pdf', 800, 'application/pdf')
            ]
        ];

        $response = $this->actingAs($broker)->post('/broker/properties', $propertyData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $property = Property::where('title', 'Property with Documents')->first();
        $this->assertNotNull($property);
        $this->assertCount(2, $property->documents);
        
        foreach ($property->documents as $document) {
            Storage::disk('public')->assertExists($document);
        }
    }

    public function test_non_broker_cannot_access_broker_routes(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $admin = User::factory()->create(['role' => 'admin']);

        // Test client access
        $response = $this->actingAs($client)->get('/broker/dashboard');
        $response->assertStatus(403);

        // Test admin access
        $response = $this->actingAs($admin)->get('/broker/properties');
        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_broker_routes(): void
    {
        $response = $this->get('/broker/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/broker/properties');
        $response->assertRedirect('/login');

        $response = $this->get('/broker/inquiries');
        $response->assertRedirect('/login');
    }
}
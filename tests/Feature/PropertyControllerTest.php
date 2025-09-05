<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
        Storage::fake('public');
    }

    public function test_guest_can_view_properties_index(): void
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

        $response = $this->get('/browse-properties');

        $response->assertStatus(200)
                 ->assertViewIs('properties.index')
                 ->assertViewHas('properties');
    }

    public function test_guest_can_view_single_property(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'status' => 'available'
        ]);

        $response = $this->get("/properties/{$property->id}");

        $response->assertStatus(200)
                 ->assertViewIs('properties.show')
                 ->assertViewHas('property', $property);
    }

    public function test_guest_cannot_view_unavailable_property(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'status' => 'sold'
        ]);

        $response = $this->get("/properties/{$property->id}");

        $response->assertStatus(404);
    }

    public function test_broker_can_view_properties_dashboard(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        Property::factory()->count(5)->create(['broker_id' => $broker->id]);

        $response = $this->actingAs($broker)->get('/broker/properties');

        $response->assertStatus(200)
                 ->assertViewIs('broker.properties.index')
                 ->assertViewHas('properties');
    }

    public function test_broker_can_create_property(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        $image = UploadedFile::fake()->image('property.jpg', 800, 600);
        $document = UploadedFile::fake()->create('deed.pdf', 1024, 'application/pdf');

        $propertyData = [
            'title' => 'Test Property',
            'type' => 'residential_lot',
            'status' => 'available',
            'description' => 'A beautiful test property',
            'address' => '123 Test Street',
            'municipality' => 'Tagbilaran City',
            'barangay' => 'Test Barangay',
            'lot_area_sqm' => 1000,
            'price_per_sqm' => 5000,
            'total_price' => 5000000,
            'road_access' => true,
            'water_source' => true,
            'electricity_available' => true,
            'internet_available' => false,
            'is_featured' => false,
            'images' => [$image],
            'documents' => [$document]
        ];

        $response = $this->actingAs($broker)->post('/broker/properties', $propertyData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('properties', [
            'title' => 'Test Property',
            'broker_id' => $broker->id,
            'status' => 'available'
        ]);
    }

    public function test_broker_can_update_property(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $updateData = [
            'title' => 'Updated Property Title',
            'type' => $property->type,
            'status' => 'available',
            'description' => 'Updated description',
            'address' => $property->address,
            'municipality' => $property->municipality,
            'barangay' => $property->barangay,
            'lot_area_sqm' => $property->lot_area_sqm,
            'price_per_sqm' => 6000,
            'total_price' => 6000000,
            'road_access' => $property->road_access,
            'water_source' => $property->water_source,
            'electricity_available' => $property->electricity_available,
            'internet_available' => $property->internet_available,
            'is_featured' => true
        ];

        $response = $this->actingAs($broker)
                         ->put("/broker/properties/{$property->id}", $updateData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => 'Updated Property Title',
            'price_per_sqm' => 6000,
            'total_price' => 6000000,
            'is_featured' => true
        ]);
    }

    public function test_broker_can_delete_property(): void
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

    public function test_broker_cannot_access_other_broker_property(): void
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

        $response = $this->actingAs($broker1)
                         ->get("/broker/properties/{$property->id}/edit");

        $response->assertStatus(403);
    }

    public function test_client_cannot_access_broker_property_management(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client)->get('/broker/properties');

        $response->assertStatus(403);
    }

    public function test_unapproved_broker_cannot_create_property(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => false,
            'application_status' => 'pending'
        ]);

        $response = $this->actingAs($broker)->get('/broker/properties/create');

        $response->assertStatus(403);
    }

    public function test_property_search_works_correctly(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        Property::factory()->create([
            'broker_id' => $broker->id,
            'title' => 'Beachfront Property in Panglao',
            'municipality' => 'Panglao',
            'status' => 'available'
        ]);

        Property::factory()->create([
            'broker_id' => $broker->id,
            'title' => 'Mountain View Lot',
            'municipality' => 'Tagbilaran',
            'status' => 'available'
        ]);

        $response = $this->get('/browse-properties?search=Panglao');

        $response->assertStatus(200)
                 ->assertSee('Beachfront Property in Panglao')
                 ->assertDontSee('Mountain View Lot');
    }

    public function test_property_type_filter_works_correctly(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        Property::factory()->create([
            'broker_id' => $broker->id,
            'type' => 'residential_lot',
            'status' => 'available'
        ]);

        Property::factory()->create([
            'broker_id' => $broker->id,
            'type' => 'house_and_lot',
            'status' => 'available'
        ]);

        $response = $this->get('/browse-properties?type=residential_lot');

        $response->assertStatus(200);
        
        // Check that only residential_lot properties are shown
        $properties = $response->viewData('properties');
        foreach ($properties as $property) {
            $this->assertEquals('residential_lot', $property->type);
        }
    }

    public function test_property_price_range_filter_works_correctly(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        Property::factory()->create([
            'broker_id' => $broker->id,
            'total_price' => 2000000,
            'status' => 'available'
        ]);

        Property::factory()->create([
            'broker_id' => $broker->id,
            'total_price' => 8000000,
            'status' => 'available'
        ]);

        $response = $this->get('/browse-properties?min_price=1000000&max_price=5000000');

        $response->assertStatus(200);
        
        // Check that only properties within price range are shown
        $properties = $response->viewData('properties');
        foreach ($properties as $property) {
            $this->assertGreaterThanOrEqual(1000000, $property->total_price);
            $this->assertLessThanOrEqual(5000000, $property->total_price);
        }
    }

    public function test_featured_properties_are_displayed_first(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        $regularProperty = Property::factory()->create([
            'broker_id' => $broker->id,
            'is_featured' => false,
            'status' => 'available',
            'created_at' => now()->subDays(1)
        ]);

        $featuredProperty = Property::factory()->create([
            'broker_id' => $broker->id,
            'is_featured' => true,
            'status' => 'available',
            'created_at' => now()->subDays(2)
        ]);

        $response = $this->get('/browse-properties');

        $response->assertStatus(200);
        
        $properties = $response->viewData('properties');
        $firstProperty = $properties->first();
        
        // Featured property should appear first despite being older
        $this->assertEquals($featuredProperty->id, $firstProperty->id);
        $this->assertTrue($firstProperty->is_featured);
    }
}
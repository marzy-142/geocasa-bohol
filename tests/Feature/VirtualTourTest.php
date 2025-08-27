<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VirtualTourTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_property_can_be_created_with_virtual_tour(): void
    {
        /** @var User $broker */
        $broker = User::factory()->create([
            'role' => 'broker', 
            'application_status' => 'approved',
            'is_approved' => true,
            'email_verified_at' => now()
        ]);
        
        $virtualTourImage = UploadedFile::fake()->create('360-tour.jpg', 1024, 'image/jpeg');
        
        $response = $this->actingAs($broker)->post('/broker/properties', [
            'title' => 'Test Property with Virtual Tour',
            'type' => 'residential_lot',
            'status' => 'available',
            'description' => 'Test property description',
            'address' => 'Test Address',
            'municipality' => 'Tagbilaran City',
            'barangay' => 'Test Barangay',
            'lot_area_sqm' => 1000,
            'price_per_sqm' => 5000,
            'total_price' => 5000000,
            'road_access' => true,
            'water_source' => true,
            'electricity_available' => true,
            'internet_available' => false,
            // Remove has_virtual_tour - let controller set it automatically
            'virtual_tour_images' => [$virtualTourImage],
            'gis_data' => json_encode(['lat' => 9.6496, 'lng' => 123.8547]),
            'tour_hotspots' => json_encode([
                ['x' => 50, 'y' => 30, 'title' => 'Living Room', 'description' => 'Spacious living area']
            ])
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('properties', [
            'title' => 'Test Property with Virtual Tour',
            'has_virtual_tour' => true
        ]);
    }

    public function test_virtual_tour_filter_works_correctly(): void
    {
        /** @var User $broker */
        $broker = User::factory()->create([
            'role' => 'broker', 
            'application_status' => 'approved',
            'is_approved' => true
        ]);
        
        $propertyWithTour = Property::factory()->create([
            'has_virtual_tour' => true,
            'status' => 'available',
            'broker_id' => $broker->id
        ]);
        
        $propertyWithoutTour = Property::factory()->create([
            'has_virtual_tour' => false,
            'status' => 'available',
            'broker_id' => $broker->id
        ]);

        $response = $this->get('/browse-properties?virtual_tour=1');

        $response->assertStatus(200);
        $response->assertSee($propertyWithTour->title);
        $response->assertDontSee($propertyWithoutTour->title);
    }

    public function test_property_detail_shows_virtual_tour_when_available(): void
    {
        /** @var User $broker */
        $broker = User::factory()->create([
            'role' => 'broker', 
            'application_status' => 'approved',
            'is_approved' => true
        ]);
        
        $property = Property::factory()->create([
            'has_virtual_tour' => true,
            'virtual_tour_images' => ['tours/sample-360.jpg'], // Store as array, not JSON
            'status' => 'available',
            'broker_id' => $broker->id
        ]);

        $response = $this->get('/browse-properties/' . $property->slug);

        $response->assertStatus(200);
        // Check for the presence of virtual tour component or data
        $response->assertSee('virtual_tour_images');
    }

    public function test_virtual_tour_images_are_validated_correctly(): void
    {
        /** @var User $broker */
        $broker = User::factory()->create([
            'role' => 'broker', 
            'application_status' => 'approved',
            'is_approved' => true,
            'email_verified_at' => now()
        ]);
        
        $invalidFile = UploadedFile::fake()->create('invalid.txt', 100, 'text/plain');
        
        $response = $this->actingAs($broker)->post('/broker/properties', [
            'title' => 'Test Property',
            'type' => 'residential_lot',
            'description' => 'Test description',
            'address' => 'Test Address',
            'municipality' => 'Tagbilaran City',
            'barangay' => 'Test Barangay',
            'lot_area_sqm' => 1000,
            'price_per_sqm' => 5000,
            'has_virtual_tour' => true, // Add this to trigger validation
            'virtual_tour_images' => [$invalidFile]
        ]);

        $response->assertSessionHasErrors('virtual_tour_images.0');
    }

    public function test_virtual_tour_can_be_updated(): void
    {
        /** @var User $broker */
        $broker = User::factory()->create([
            'role' => 'broker', 
            'application_status' => 'approved',
            'is_approved' => true,
            'email_verified_at' => now()
        ]);
        
        $property = Property::factory()->create([
            'has_virtual_tour' => false,
            'broker_id' => $broker->id
        ]);
        
        $newTourImage = UploadedFile::fake()->create('new-tour.jpg', 1024, 'image/jpeg');
        
        // Fix: Use correct URL path - remove '/broker' prefix
        $response = $this->actingAs($broker)->put('/properties/' . $property->slug, [
            'title' => $property->title,
            'type' => $property->type,
            'status' => $property->status,
            'description' => $property->description,
            'address' => $property->address,
            'municipality' => $property->municipality,
            'barangay' => $property->barangay,
            'lot_area_sqm' => $property->lot_area_sqm,
            'price_per_sqm' => $property->price_per_sqm,
            'total_price' => $property->total_price,
            // Add required boolean fields
            'road_access' => $property->road_access ?? true,
            'water_source' => $property->water_source ?? true,
            'electricity_available' => $property->electricity_available ?? true,
            'internet_available' => $property->internet_available ?? false,
            // Remove has_virtual_tour - let controller set it automatically
            'new_virtual_tour_images' => [$newTourImage]
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'has_virtual_tour' => true
        ]);
    }
}
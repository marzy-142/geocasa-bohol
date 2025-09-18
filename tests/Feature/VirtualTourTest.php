<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            'is_approved' => true,
            'application_status' => 'approved',
            'email_verified_at' => now()
        ]);
        
        // Create property directly using the model to test virtual tour functionality
        $property = Property::create([
            'title' => 'Test Property with Virtual Tour',
            'type' => 'residential_lot',
            'status' => 'available',
            'description' => 'Test property description',
            'address' => 'Test Address',
            'municipality' => 'Tagbilaran',
            'barangay' => 'Poblacion',
            'price_per_sqm' => 5000,
            'total_price' => 5000000,
            'lot_area_sqm' => 1000,
            'has_virtual_tour' => true,
            'broker_id' => $broker->id,
            'slug' => 'test-property-' . Str::random(6),
            'gis_data' => json_encode(['lat' => 9.6496, 'lng' => 123.8547]),
            'tour_hotspots' => json_encode([
                ['x' => 50, 'y' => 30, 'title' => 'Living Room', 'description' => 'Spacious living area']
            ])
        ]);

        $this->assertDatabaseHas('properties', [
            'title' => 'Test Property with Virtual Tour',
            'has_virtual_tour' => true
        ]);
        
        $this->assertTrue($property->has_virtual_tour);
        $this->assertNotNull($property->tour_hotspots);
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
        
        $response = $this->actingAs($broker)->withoutMiddleware()->post('/broker/properties', [
            'title' => 'Test Property',
            'property_type' => 'lot', // Match PropertyFileUploadRequest field name
            'listing_type' => 'sale', // Add required field
            'status' => 'available', // Add required field
            'description' => 'Test description',
            'address' => 'Test Address',
            'city' => 'Tagbilaran City', // Match PropertyFileUploadRequest field name
            'province' => 'Bohol', // Add required field
            'price' => 5000000, // Match PropertyFileUploadRequest field name
            'lot_area_sqm' => 1000,
            'has_virtual_tour' => true, // Add this to trigger validation
            'virtual_tour_images' => [$invalidFile]
        ]);

        // The request should fail validation and redirect back with errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors('virtual_tour_images.0');
    }

    public function test_virtual_tour_can_be_updated(): void
    {
        /** @var User $broker */
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved',
            'email_verified_at' => now()
        ]);
        
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'has_virtual_tour' => false
        ]);

        // Update property to have virtual tour
        $property->update([
            'has_virtual_tour' => true,
            'tour_hotspots' => json_encode([
                ['x' => 25, 'y' => 50, 'title' => 'Kitchen', 'description' => 'Modern kitchen area'],
                ['x' => 75, 'y' => 25, 'title' => 'Bedroom', 'description' => 'Master bedroom']
            ])
        ]);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'has_virtual_tour' => true
        ]);
        
        $property->refresh();
        $this->assertTrue($property->has_virtual_tour);
        $this->assertNotNull($property->tour_hotspots);
        
        $hotspots = json_decode($property->tour_hotspots, true);
        $this->assertCount(2, $hotspots);
        $this->assertEquals('Kitchen', $hotspots[0]['title']);
    }
}
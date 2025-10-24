<?php

namespace Tests\Unit\Models;

use App\Models\Property;
use App\Models\User;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_property_has_fillable_attributes(): void
    {
        $fillable = [
            'title', 'type', 'status', 'description', 'address', 'municipality', 'barangay',
            'lot_area_sqm', 'floor_area_sqm', 'bedrooms', 'bathrooms', 'parking_spaces',
            'price_per_sqm', 'total_price', 'road_access', 'water_source', 'electricity_available',
            'internet_available', 'is_featured', 'broker_id', 'images', 'documents',
            'has_virtual_tour', 'virtual_tour_images', 'gis_data', 'tour_hotspots'
        ];

        $property = new Property();
        $this->assertEquals($fillable, $property->getFillable());
    }

    public function test_property_casts_attributes_correctly(): void
    {
        $property = new Property();
        $casts = $property->getCasts();

        $this->assertEquals('array', $casts['images']);
        $this->assertEquals('array', $casts['documents']);
        $this->assertEquals('array', $casts['virtual_tour_images']);
        $this->assertEquals('array', $casts['gis_data']);
        $this->assertEquals('array', $casts['tour_hotspots']);
        $this->assertEquals('boolean', $casts['road_access']);
        $this->assertEquals('boolean', $casts['water_source']);
        $this->assertEquals('boolean', $casts['electricity_available']);
        $this->assertEquals('boolean', $casts['internet_available']);
        $this->assertEquals('boolean', $casts['is_featured']);
        $this->assertEquals('boolean', $casts['has_virtual_tour']);
    }

    public function test_property_belongs_to_broker(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $this->assertInstanceOf(User::class, $property->broker);
        $this->assertEquals($broker->id, $property->broker->id);
        $this->assertEquals('broker', $property->broker->role);
    }

    public function test_property_has_many_inquiries(): void
    {
        $property = Property::factory()->create();
        $inquiry1 = Inquiry::factory()->create(['property_id' => $property->id]);
        $inquiry2 = Inquiry::factory()->create(['property_id' => $property->id]);

        $this->assertCount(2, $property->inquiries);
        $this->assertTrue($property->inquiries->contains($inquiry1));
        $this->assertTrue($property->inquiries->contains($inquiry2));
    }

    public function test_property_images_are_cast_to_array(): void
    {
        $images = ['image1.jpg', 'image2.jpg', 'image3.jpg'];
        $property = Property::factory()->create(['images' => $images]);

        $this->assertIsArray($property->images);
        $this->assertEquals($images, $property->images);
    }

    public function test_property_documents_are_cast_to_array(): void
    {
        $documents = ['doc1.pdf', 'doc2.pdf'];
        $property = Property::factory()->create(['documents' => $documents]);

        $this->assertIsArray($property->documents);
        $this->assertEquals($documents, $property->documents);
    }

    public function test_property_virtual_tour_images_are_cast_to_array(): void
    {
        $virtualTourImages = ['360_1.jpg', '360_2.jpg'];
        $property = Property::factory()->create(['virtual_tour_images' => $virtualTourImages]);

        $this->assertIsArray($property->virtual_tour_images);
        $this->assertEquals($virtualTourImages, $property->virtual_tour_images);
    }

    public function test_property_gis_data_is_cast_to_array(): void
    {
        $gisData = ['lat' => 9.6496, 'lng' => 123.8547, 'zoom' => 15];
        $property = Property::factory()->create(['gis_data' => $gisData]);

        $this->assertIsArray($property->gis_data);
        $this->assertEquals($gisData, $property->gis_data);
    }

    public function test_property_tour_hotspots_are_cast_to_array(): void
    {
        $hotspots = [
            ['x' => 50, 'y' => 30, 'title' => 'Living Room', 'description' => 'Spacious living area'],
            ['x' => 70, 'y' => 60, 'title' => 'Kitchen', 'description' => 'Modern kitchen']
        ];
        $property = Property::factory()->create(['tour_hotspots' => $hotspots]);

        $this->assertIsArray($property->tour_hotspots);
        $this->assertEquals($hotspots, $property->tour_hotspots);
    }

    public function test_property_boolean_attributes_are_cast_correctly(): void
    {
        $property = Property::factory()->create([
            'road_access' => true,
            'water_source' => false,
            'electricity_available' => true,
            'internet_available' => false,
            'is_featured' => true,
            'has_virtual_tour' => false
        ]);

        $this->assertIsBool($property->road_access);
        $this->assertIsBool($property->water_source);
        $this->assertIsBool($property->electricity_available);
        $this->assertIsBool($property->internet_available);
        $this->assertIsBool($property->is_featured);
        $this->assertIsBool($property->has_virtual_tour);

        $this->assertTrue($property->road_access);
        $this->assertFalse($property->water_source);
        $this->assertTrue($property->electricity_available);
        $this->assertFalse($property->internet_available);
        $this->assertTrue($property->is_featured);
        $this->assertFalse($property->has_virtual_tour);
    }

    public function test_property_can_be_created_with_all_attributes(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        
        $propertyData = [
            'title' => 'Beautiful Beachfront Property',
            'type' => 'residential_lot',
            'status' => 'available',
            'description' => 'A stunning beachfront property with amazing views',
            'address' => '123 Beach Road',
            'municipality' => 'Panglao',
            'barangay' => 'Alona',
            'lot_area_sqm' => 1000,
            'floor_area_sqm' => 200,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'parking_spaces' => 2,
            'price_per_sqm' => 5000,
            'total_price' => 5000000,
            'road_access' => true,
            'water_source' => true,
            'electricity_available' => true,
            'internet_available' => false,
            'is_featured' => true,
            'broker_id' => $broker->id,
            'images' => ['image1.jpg', 'image2.jpg'],
            'documents' => ['deed.pdf'],
            'has_virtual_tour' => true,
            'virtual_tour_images' => ['360_1.jpg'],
            'gis_data' => ['lat' => 9.6496, 'lng' => 123.8547],
            'tour_hotspots' => [['x' => 50, 'y' => 30, 'title' => 'Living Room']]
        ];

        $property = Property::create($propertyData);

        $this->assertInstanceOf(Property::class, $property);
        $this->assertEquals('Beautiful Beachfront Property', $property->title);
        $this->assertEquals('residential_lot', $property->type);
        $this->assertEquals('available', $property->status);
        $this->assertEquals($broker->id, $property->broker_id);
        $this->assertTrue($property->is_featured);
        $this->assertTrue($property->has_virtual_tour);
    }

    public function test_property_factory_creates_valid_property(): void
    {
        $property = Property::factory()->create();

        $this->assertInstanceOf(Property::class, $property);
        $this->assertNotEmpty($property->title);
        $this->assertNotEmpty($property->type);
        $this->assertNotEmpty($property->status);
        $this->assertNotNull($property->broker_id);
        $this->assertIsNumeric($property->total_price);
        $this->assertIsNumeric($property->lot_area_sqm);
    }

    public function test_property_soft_deletes(): void
    {
        $property = Property::factory()->create();
        $propertyId = $property->id;

        $property->delete();

        $this->assertSoftDeleted('properties', ['id' => $propertyId]);
        $this->assertNull(Property::find($propertyId));
        $this->assertNotNull(Property::withTrashed()->find($propertyId));
    }
}
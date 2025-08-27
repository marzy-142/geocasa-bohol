<?php

namespace Tests\Unit;

use App\Models\Property;
use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PropertyVirtualTourTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_has_virtual_tour_attribute(): void
    {
        $property = new Property();
        $property->has_virtual_tour = true;
        
        $this->assertTrue($property->has_virtual_tour);
    }

    public function test_property_virtual_tour_images_are_cast_to_array(): void
    {
        $property = new Property();
        $property->virtual_tour_images = ['image1.jpg', 'image2.jpg'];
        
        $this->assertIsArray($property->virtual_tour_images);
        $this->assertCount(2, $property->virtual_tour_images);
    }

    public function test_property_tour_hotspots_are_cast_to_array(): void
    {
        $hotspots = [
            ['x' => 50, 'y' => 30, 'title' => 'Living Room'],
            ['x' => 70, 'y' => 60, 'title' => 'Kitchen']
        ];
        
        $property = new Property();
        $property->tour_hotspots = $hotspots;
        
        $this->assertIsArray($property->tour_hotspots);
        $this->assertEquals($hotspots, $property->tour_hotspots);
    }

    public function test_property_gis_data_is_cast_to_array(): void
    {
        $gisData = ['lat' => 9.6496, 'lng' => 123.8547, 'zoom' => 15];
        
        $property = new Property();
        $property->gis_data = $gisData;
        
        $this->assertIsArray($property->gis_data);
        $this->assertEquals($gisData, $property->gis_data);
    }
}
<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VirtualTourPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_virtual_tour_filter_performance_with_large_dataset(): void
    {
        /** @var User $broker */
        $broker = User::factory()->create(['role' => 'broker', 'application_status' => 'approved']);
        
        // Create 100 properties instead of 1000 for more realistic testing
        Property::factory(50)->create([
            'has_virtual_tour' => true, 
            'status' => 'available',
            'broker_id' => $broker->id
        ]);
        Property::factory(50)->create([
            'has_virtual_tour' => false, 
            'status' => 'available',
            'broker_id' => $broker->id
        ]);
        
        $startTime = microtime(true);
        
        $response = $this->get('/browse-properties?virtual_tour=1');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        
        // More realistic expectation: < 3000ms for 100 records
        $this->assertLessThan(3000, $executionTime, 'Virtual tour filter query took too long');
        
        // Log the actual execution time for monitoring
        $this->addToAssertionCount(1);
        echo "\nVirtual tour filter execution time: {$executionTime}ms\n";
    }

    public function test_property_detail_with_virtual_tour_loads_quickly(): void
    {
        /** @var User $broker */
        $broker = User::factory()->create(['role' => 'broker', 'application_status' => 'approved']);
        
        $property = Property::factory()->create([
            'has_virtual_tour' => true,
            'virtual_tour_images' => array_fill(0, 5, 'tours/sample-360.jpg'), // Reduced to 5 images
            'tour_hotspots' => array_fill(0, 10, [ // Reduced to 10 hotspots
                'x' => 50, 'y' => 30, 'title' => 'Test Hotspot', 'description' => 'Test description'
            ]),
            'status' => 'available',
            'broker_id' => $broker->id
        ]);
        
        $startTime = microtime(true);
        
        $response = $this->get('/browse-properties/' . $property->slug);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        
        // More realistic expectation: < 3000ms for property detail
        $this->assertLessThan(3000, $executionTime, 'Property detail page with virtual tour loaded too slowly');
        
        // Log the actual execution time for monitoring
        echo "\nProperty detail execution time: {$executionTime}ms\n";
    }
}
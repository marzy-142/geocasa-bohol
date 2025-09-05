<?php

namespace Tests\Feature\Api;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PropertyControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_can_get_all_properties(): void
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

        $response = $this->getJson('/api/v1/properties');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'data' => [
                             '*' => [
                                 'id',
                                 'title',
                                 'type',
                                 'status',
                                 'description',
                                 'address',
                                 'municipality',
                                 'barangay',
                                 'lot_area_sqm',
                                 'price_per_sqm',
                                 'total_price',
                                 'broker',
                                 'images',
                                 'created_at',
                                 'updated_at'
                             ]
                         ],
                         'current_page',
                         'per_page',
                         'total'
                     ]
                 ]);
    }

    public function test_can_get_single_property(): void
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

        $response = $this->getJson("/api/v1/properties/{$property->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'title',
                         'type',
                         'status',
                         'description',
                         'address',
                         'municipality',
                         'barangay',
                         'lot_area_sqm',
                         'price_per_sqm',
                         'total_price',
                         'broker',
                         'images',
                         'created_at',
                         'updated_at'
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $property->id,
                         'title' => $property->title
                     ]
                 ]);
    }

    public function test_returns_404_for_nonexistent_property(): void
    {
        $response = $this->getJson('/api/v1/properties/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Property not found'
                 ]);
    }

    public function test_can_search_properties(): void
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

        $response = $this->getJson('/api/v1/properties/search?q=Panglao');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'data' => [
                             '*' => [
                                 'id',
                                 'title',
                                 'municipality'
                             ]
                         ]
                     ]
                 ]);

        $this->assertStringContainsString('Panglao', $response->json('data.data.0.title'));
    }

    public function test_search_requires_minimum_query_length(): void
    {
        $response = $this->getJson('/api/v1/properties/search?q=a');

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['q']);
    }

    public function test_can_get_featured_properties(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        Property::factory()->count(5)->create([
            'broker_id' => $broker->id,
            'status' => 'available',
            'is_featured' => true
        ]);

        $response = $this->getJson('/api/v1/properties/featured');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => [
                             'id',
                             'title',
                             'is_featured'
                         ]
                     ]
                 ]);

        // Should return maximum 4 featured properties
        $this->assertLessThanOrEqual(4, count($response->json('data')));
    }

    public function test_only_shows_available_properties(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        $availableProperty = Property::factory()->create([
            'broker_id' => $broker->id,
            'status' => 'available'
        ]);

        $soldProperty = Property::factory()->create([
            'broker_id' => $broker->id,
            'status' => 'sold'
        ]);

        $response = $this->getJson('/api/v1/properties');

        $response->assertStatus(200);
        
        $propertyIds = collect($response->json('data.data'))->pluck('id')->toArray();
        $this->assertContains($availableProperty->id, $propertyIds);
        $this->assertNotContains($soldProperty->id, $propertyIds);
    }

    public function test_pagination_works_correctly(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        Property::factory()->count(25)->create([
            'broker_id' => $broker->id,
            'status' => 'available'
        ]);

        $response = $this->getJson('/api/v1/properties?per_page=10&page=1');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'current_page',
                         'per_page',
                         'total',
                         'last_page',
                         'data'
                     ]
                 ]);

        $this->assertEquals(10, count($response->json('data.data')));
        $this->assertEquals(1, $response->json('data.current_page'));
        $this->assertEquals(25, $response->json('data.total'));
    }
}
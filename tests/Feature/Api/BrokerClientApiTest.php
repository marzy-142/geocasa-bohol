<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\{User, Client, Property, Inquiry};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class BrokerClientApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function api_returns_broker_clients()
    {
        $broker = User::factory()->broker()->create();
        Sanctum::actingAs($broker);
        
        $clients = Client::factory()->count(3)->create(['broker_id' => $broker->id]);
        
        $response = $this->getJson('/api/broker/clients');
        
        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data')
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name', 'email', 'phone', 'created_at']
                     ]
                 ]);
    }

    /** @test */
    public function api_creates_inquiry_response()
    {
        $broker = User::factory()->broker()->create();
        $inquiry = Inquiry::factory()->create();
        Sanctum::actingAs($broker);
        
        $response = $this->putJson("/api/inquiries/{$inquiry->id}/respond", [
            'response' => 'Thank you for your inquiry.',
            'status' => 'responded'
        ]);
        
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Response sent successfully',
                     'data' => [
                         'status' => 'responded'
                     ]
                 ]);
    }
}
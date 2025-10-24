<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User, Client, Property, Inquiry, Transaction, Conversation};
use Illuminate\Foundation\Testing\RefreshDatabase;

class BrokerClientInteractionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function broker_can_view_assigned_clients()
    {
        $broker = User::factory()->broker()->create();
        $clients = Client::factory()->count(3)->create(['broker_id' => $broker->id]);
        
        $response = $this->actingAs($broker)->get('/broker/clients');
        
        $response->assertStatus(200)
                 ->assertViewHas('clients')
                 ->assertSee($clients->first()->name);
    }

    /** @test */
    public function broker_can_respond_to_client_inquiry()
    {
        $broker = User::factory()->broker()->create();
        $client = User::factory()->client()->create();
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        $inquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'status' => 'pending'
        ]);
        
        $response = $this->actingAs($broker)
                         ->put("/broker/inquiries/{$inquiry->id}", [
                             'response' => 'Thank you for your interest.',
                             'status' => 'responded'
                         ]);
        
        $response->assertRedirect()
                 ->assertSessionHas('success');
        
        $this->assertDatabaseHas('inquiries', [
            'id' => $inquiry->id,
            'status' => 'responded',
            'response' => 'Thank you for your interest.'
        ]);
    }

    /** @test */
    public function client_can_create_inquiry_for_property()
    {
        $client = User::factory()->client()->create();
        $broker = User::factory()->broker()->create();
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        
        $response = $this->actingAs($client)
                         ->post('/inquiries', [
                             'property_id' => $property->id,
                             'message' => 'I am interested in this property.',
                             'inquiry_type' => 'viewing'
                         ]);
        
        $response->assertRedirect()
                 ->assertSessionHas('success');
        
        $this->assertDatabaseHas('inquiries', [
            'property_id' => $property->id,
            'client_id' => $client->id,
            'message' => 'I am interested in this property.'
        ]);
    }
}
<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\{User, Client, Property, Inquiry, Transaction, Conversation, Message};
use Illuminate\Foundation\Testing\RefreshDatabase;

class BrokerClientRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function broker_can_have_multiple_clients()
    {
        $broker = User::factory()->broker()->create();
        $clients = Client::factory()->count(3)->create(['broker_id' => $broker->id]);
        
        $this->assertCount(3, $broker->clients);
        $this->assertEquals($broker->id, $clients->first()->broker_id);
    }

    /** @test */
    public function client_belongs_to_broker()
    {
        $broker = User::factory()->broker()->create();
        $client = Client::factory()->create(['broker_id' => $broker->id]);
        
        $this->assertInstanceOf(User::class, $client->broker);
        $this->assertEquals($broker->id, $client->broker->id);
    }

    /** @test */
    public function inquiry_creates_broker_client_connection()
    {
        $broker = User::factory()->broker()->create();
        $client = Client::factory()->create(['broker_id' => $broker->id]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        
        $inquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'client_id' => $client->id
        ]);
        
        $this->assertEquals($broker->id, $inquiry->property->broker->id);
        $this->assertEquals($client->id, $inquiry->client->id);
        $this->assertEquals($broker->id, $inquiry->client->broker->id);
    }
}
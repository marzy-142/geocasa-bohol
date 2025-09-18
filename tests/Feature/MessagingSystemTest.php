<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User, Conversation, Message, Inquiry, Transaction};
use Illuminate\Foundation\Testing\RefreshDatabase;

class MessagingSystemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function conversation_created_for_inquiry()
    {
        $broker = User::factory()->broker()->create();
        $client = User::factory()->client()->create();
        $property = \App\Models\Property::factory()->create(['broker_id' => $broker->id]);
        $inquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'client_id' => $client->id
        ]);
        
        $conversation = Conversation::createForInquiry($inquiry);
        
        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals('inquiry', $conversation->type);
        $this->assertEquals($inquiry->id, $conversation->inquiry_id);
        $this->assertContains($client->id, $conversation->participants);
        $this->assertContains($broker->id, $conversation->participants);
    }

    /** @test */
    public function broker_can_send_message_to_client()
    {
        $broker = User::factory()->broker()->create();
        $client = User::factory()->client()->create();
        $conversation = Conversation::factory()->create([
            'participants' => [$broker->id, $client->id]
        ]);
        
        $response = $this->actingAs($broker)
                         ->post("/conversations/{$conversation->id}/messages", [
                             'content' => 'Hello, I have reviewed your inquiry.'
                         ]);
        
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $broker->id,
            'content' => 'Hello, I have reviewed your inquiry.'
        ]);
    }

    /** @test */
    public function message_updates_conversation_timestamp()
    {
        $conversation = Conversation::factory()->create();
        $user = User::factory()->create();
        $originalTimestamp = $conversation->last_message_at;
        
        sleep(1); // Ensure timestamp difference
        
        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id
        ]);
        
        $conversation->refresh();
        $this->assertNotEquals($originalTimestamp, $conversation->last_message_at);
    }
}
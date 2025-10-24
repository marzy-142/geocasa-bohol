<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Conversation;
use App\Models\Transaction;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InquiryToTransactionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $broker;
    protected $client;
    protected $property;
    protected $inquiry;

    protected function setUp(): void
    {
        parent::setUp();

        // Create broker
        $this->broker = User::factory()->create([
            'role' => 'broker',
            'email' => 'broker@test.com',
        ]);

        // Create client user
        $clientUser = User::factory()->create([
            'role' => 'client',
            'email' => 'client@test.com',
        ]);

        // Create client record
        $this->client = Client::factory()->create([
            'user_id' => $clientUser->id,
            'broker_id' => $this->broker->id,
            'email' => $clientUser->email,
        ]);

        // Create property
        $this->property = Property::factory()->create([
            'broker_id' => $this->broker->id,
            'status' => 'available',
        ]);

        // Create inquiry
        $this->inquiry = Inquiry::create([
            'property_id' => $this->property->id,
            'client_id' => $this->client->id,
            'user_id' => $clientUser->id,
            'assigned_broker_id' => $this->broker->id,
            'name' => 'Test Client',
            'email' => 'client@test.com',
            'phone' => '1234567890',
            'message' => 'I am interested in this property',
            'inquiry_type' => 'purchase',
            'status' => 'new',
        ]);
    }

    /** @test */
    public function broker_can_accept_inquiry_and_create_transaction()
    {
        // Act as broker
        $response = $this->actingAs($this->broker)
            ->post(route('inquiries.accept', $this->inquiry));

        // Assert redirect with success message
        $response->assertRedirect(route('inquiries.show', $this->inquiry));
        $response->assertSessionHas('success');

        // Assert transaction was created
        $this->assertDatabaseHas('transactions', [
            'inquiry_id' => $this->inquiry->id,
            'property_id' => $this->property->id,
            'client_id' => $this->client->id,
            'broker_id' => $this->broker->id,
            'status' => 'initial_contact',
        ]);

        // Assert inquiry status updated
        $this->inquiry->refresh();
        $this->assertEquals('converted', $this->inquiry->status);

        // Assert transaction exists
        $this->assertNotNull($this->inquiry->transaction);
    }

    /** @test */
    public function conversation_transitions_when_inquiry_accepted()
    {
        // Create conversation for inquiry first
        $conversation = Conversation::createForInquiry($this->inquiry);

        // Accept inquiry
        $this->actingAs($this->broker)
            ->post(route('inquiries.accept', $this->inquiry));

        // Refresh conversation
        $conversation->refresh();

        // Assert conversation transitioned
        $this->assertEquals('transaction', $conversation->type);
        $this->assertEquals('transaction', $conversation->lifecycle_stage);
        $this->assertNotNull($conversation->transaction_id);
        $this->assertNotNull($conversation->transitioned_at);

        // Assert system message was created
        $systemMessage = $conversation->messages()
            ->where('type', 'system')
            ->latest()
            ->first();

        $this->assertNotNull($systemMessage);
        $this->assertStringContainsString('Transaction', $systemMessage->content);
    }

    /** @test */
    public function non_broker_cannot_accept_inquiry()
    {
        $otherUser = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($otherUser)
            ->post(route('inquiries.accept', $this->inquiry));

        $response->assertStatus(403);
    }

    /** @test */
    public function broker_cannot_accept_inquiry_not_assigned_to_them()
    {
        $otherBroker = User::factory()->create(['role' => 'broker']);

        $response = $this->actingAs($otherBroker)
            ->post(route('inquiries.accept', $this->inquiry));

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_accept_already_converted_inquiry()
    {
        // Create transaction first
        Transaction::create([
            'inquiry_id' => $this->inquiry->id,
            'property_id' => $this->property->id,
            'client_id' => $this->client->id,
            'broker_id' => $this->broker->id,
            'status' => 'initial_contact',
            'transaction_number' => 'TXN-TEST123',
        ]);

        // Try to accept again
        $response = $this->actingAs($this->broker)
            ->post(route('inquiries.accept', $this->inquiry));

        $response->assertSessionHas('error');
    }

    /** @test */
    public function conversation_metadata_updated_on_transition()
    {
        $conversation = Conversation::createForInquiry($this->inquiry);

        $this->actingAs($this->broker)
            ->post(route('inquiries.accept', $this->inquiry));

        $conversation->refresh();
        $transaction = $this->inquiry->transaction;

        $this->assertNotNull($conversation->metadata);
        $this->assertArrayHasKey('transaction_number', $conversation->metadata);
        $this->assertArrayHasKey('original_inquiry_id', $conversation->metadata);
        $this->assertEquals($transaction->transaction_number, $conversation->metadata['transaction_number']);
        $this->assertEquals($this->inquiry->id, $conversation->metadata['original_inquiry_id']);
    }
}

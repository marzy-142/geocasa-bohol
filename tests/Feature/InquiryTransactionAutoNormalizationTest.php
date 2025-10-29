<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Events\InquiryStatusUpdated;

class InquiryTransactionAutoNormalizationTest extends TestCase
{
    use RefreshDatabase;

    protected $broker;
    protected $client;
    protected $property;

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
    }

    /** @test */
    public function auto_normalizes_new_inquiry_on_transaction_creation()
    {
        Event::fake([InquiryStatusUpdated::class]);

        // Create a "new" inquiry with no response
        $inquiry = Inquiry::create([
            'property_id' => $this->property->id,
            'client_id' => $this->client->id,
            'user_id' => $this->client->user_id,
            'assigned_broker_id' => $this->broker->id,
            'name' => 'Test Client',
            'email' => 'client@test.com',
            'phone' => '1234567890',
            'message' => 'I am interested in this property',
            'inquiry_type' => 'general',
            'status' => 'new',
            'broker_response' => null,
            'responded_at' => null,
            'contacted_at' => null,
        ]);

        // Accept/create transaction
        $this->actingAs($this->broker)
            ->post(route('inquiries.accept', $inquiry));

        // Refresh inquiry
        $inquiry->refresh();

        // Assert auto-normalization happened
        $this->assertEquals('in transaction', $inquiry->status);
        $this->assertNotNull($inquiry->responded_at);
        $this->assertNotNull($inquiry->contacted_at);

        // Assert event was broadcast
        Event::assertDispatched(InquiryStatusUpdated::class, function ($event) use ($inquiry) {
            return $event->inquiry->id === $inquiry->id
                && $event->previousStatus === 'new'
                && $event->newStatus === 'in transaction';
        });

        // Assert transaction was created
        $this->assertDatabaseHas('transactions', [
            'inquiry_id' => $inquiry->id,
            'property_id' => $this->property->id,
            'client_id' => $this->client->id,
            'broker_id' => $this->broker->id,
        ]);
    }

    /** @test */
    public function auto_normalizes_contacted_inquiry_preserving_scheduled_at()
    {
        Event::fake([InquiryStatusUpdated::class]);

        $scheduledAt = now()->addDays(2);

        // Create a "scheduled" inquiry with timestamps
        $inquiry = Inquiry::create([
            'property_id' => $this->property->id,
            'client_id' => $this->client->id,
            'user_id' => $this->client->user_id,
            'assigned_broker_id' => $this->broker->id,
            'name' => 'Test Client',
            'email' => 'client@test.com',
            'phone' => '1234567890',
            'message' => 'I am interested',
            'inquiry_type' => 'general',
            'status' => 'scheduled',
            'broker_response' => 'Let us meet on Thursday.',
            'responded_at' => now()->subDay(),
            'contacted_at' => now()->subDays(2),
            'scheduled_at' => $scheduledAt,
        ]);

        // Accept/create transaction
        $this->actingAs($this->broker)
            ->post(route('inquiries.accept', $inquiry));

        $inquiry->refresh();

        // Assert status updated but timestamps preserved
        $this->assertEquals('in transaction', $inquiry->status);
        $this->assertNotNull($inquiry->responded_at);
        $this->assertNotNull($inquiry->contacted_at);
        $this->assertEquals($scheduledAt->toDateTimeString(), $inquiry->scheduled_at->toDateTimeString());

        // Assert broadcast happened
        Event::assertDispatched(InquiryStatusUpdated::class);
    }

    /** @test */
    public function prevents_duplicate_transaction_and_redirects()
    {
        // Create inquiry
        $inquiry = Inquiry::create([
            'property_id' => $this->property->id,
            'client_id' => $this->client->id,
            'user_id' => $this->client->user_id,
            'assigned_broker_id' => $this->broker->id,
            'name' => 'Test Client',
            'email' => 'client@test.com',
            'phone' => '1234567890',
            'message' => 'I am interested',
            'inquiry_type' => 'general',
            'status' => 'new',
        ]);

        // Create existing transaction
        $existingTransaction = Transaction::create([
            'inquiry_id' => $inquiry->id,
            'property_id' => $this->property->id,
            'client_id' => $this->client->id,
            'broker_id' => $this->broker->id,
            'status' => 'initial_contact', // Use a valid transaction status
            'transaction_number' => 'TXN-EXISTING',
            'offered_price' => $this->property->total_price ?? 0,
            'inquiry_date' => $inquiry->created_at,
        ]);

        // Try to accept again
        $response = $this->actingAs($this->broker)
            ->post(route('inquiries.accept', $inquiry));

        // Assert redirected to existing transaction instead of error
        $response->assertRedirect(route('transactions.show', $existingTransaction->id));
        $response->assertSessionHas('info');

        // Assert no duplicate transaction created
        $this->assertEquals(1, Transaction::where('inquiry_id', $inquiry->id)->count());
    }

    /** @test */
    public function sets_responded_at_if_null_on_transaction_create()
    {
        // Create inquiry with a response but no responded_at timestamp
        $inquiry = Inquiry::create([
            'property_id' => $this->property->id,
            'client_id' => $this->client->id,
            'user_id' => $this->client->user_id,
            'assigned_broker_id' => $this->broker->id,
            'name' => 'Test Client',
            'email' => 'client@test.com',
            'phone' => '1234567890',
            'message' => 'I am interested',
            'inquiry_type' => 'general',
            'status' => 'contacted',
            'broker_response' => 'Thanks for your interest!',
            'responded_at' => null,
        ]);

        $this->actingAs($this->broker)
            ->post(route('inquiries.accept', $inquiry));

        $inquiry->refresh();

        $this->assertNotNull($inquiry->responded_at);
    }
}

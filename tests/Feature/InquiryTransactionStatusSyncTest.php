<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User, Client, Property, Inquiry, Transaction};
use Illuminate\Foundation\Testing\RefreshDatabase;

class InquiryTransactionStatusSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_inquiry_status_tracks_transaction_progression()
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $client = Client::factory()->create();
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        $inquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'assigned_broker_id' => $broker->id,
            'status' => 'new',
        ]);

        // Create transaction linked to inquiry
        $transaction = Transaction::factory()->create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'broker_id' => $broker->id,
            'inquiry_id' => $inquiry->id,
            'status' => 'inquiry',
        ]);
        $inquiry->refresh();
        $this->assertEquals('in transaction', $inquiry->status);

        // Progress transaction status
        $transaction->update(['status' => 'offer_made']);
        $inquiry->refresh();
        $this->assertEquals('in transaction', $inquiry->status);

        $transaction->update(['status' => 'finalized']);
        $inquiry->refresh();
        $this->assertEquals('closed', $inquiry->status);

        $transaction2 = Transaction::factory()->create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'broker_id' => $broker->id,
            'inquiry_id' => $inquiry->id,
            'status' => 'inquiry',
        ]);
        $transaction2->update(['status' => 'cancelled']);
        $inquiry->refresh();
        $this->assertEquals('not converted', $inquiry->status);
    }
}

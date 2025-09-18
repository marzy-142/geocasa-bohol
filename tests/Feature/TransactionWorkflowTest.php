<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User, Client, Property, Transaction, Conversation};
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function broker_can_create_transaction_from_inquiry()
    {
        $broker = User::factory()->broker()->create();
        $client = User::factory()->client()->create();
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        
        $response = $this->actingAs($broker)
                         ->post('/broker/transactions', [
                             'property_id' => $property->id,
                             'client_id' => $client->id,
                             'transaction_type' => 'sale',
                             'amount' => 5000000,
                             'commission_rate' => 5.0
                         ]);
        
        $response->assertRedirect()
                 ->assertSessionHas('success');
        
        $transaction = Transaction::latest()->first();
        $this->assertEquals($broker->id, $transaction->broker_id);
        $this->assertEquals($client->id, $transaction->client_id);
        $this->assertNotNull($transaction->transaction_number);
    }

    /** @test */
    public function transaction_status_changes_are_logged()
    {
        $transaction = Transaction::factory()->create(['status' => 'pending']);
        
        $transaction->update(['status' => 'in_progress']);
        
        $this->assertNotEmpty($transaction->fresh()->status_history);
        $this->assertContains('pending', array_column($transaction->status_history, 'status'));
        $this->assertContains('in_progress', array_column($transaction->status_history, 'status'));
    }

    /** @test */
    public function conversation_created_for_transaction()
    {
        $transaction = Transaction::factory()->create();
        
        $conversation = Conversation::createForTransaction($transaction);
        
        $this->assertEquals('transaction', $conversation->type);
        $this->assertEquals($transaction->id, $conversation->transaction_id);
        $this->assertContains($transaction->client_id, $conversation->participants);
        $this->assertContains($transaction->broker_id, $conversation->participants);
    }
}
<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_broker_can_create_transaction(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'status' => 'available',
            'price' => 5000000
        ]);

        $transactionData = [
            'property_id' => $property->id,
            'client_id' => $client->id,
            'transaction_type' => 'sale',
            'amount' => 5000000,
            'commission_rate' => 5.0,
            'status' => 'pending'
        ];

        $response = $this->actingAs($broker)->post('/broker/transactions', $transactionData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'property_id' => $property->id,
            'client_id' => $client->id,
            'broker_id' => $broker->id,
            'transaction_type' => 'sale',
            'amount' => 5000000,
            'commission_rate' => 5.0,
            'status' => 'pending'
        ]);
    }

    public function test_broker_can_view_own_transactions(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        Transaction::factory()->count(3)->create([
            'broker_id' => $broker->id,
            'client_id' => $client->id,
            'property_id' => $property->id
        ]);

        // Create transactions for other brokers
        Transaction::factory()->count(2)->create();

        $response = $this->actingAs($broker)->get('/broker/transactions');

        $response->assertStatus(200)
                 ->assertViewIs('broker.transactions.index')
                 ->assertViewHas('transactions');

        $transactions = $response->viewData('transactions');
        $this->assertCount(3, $transactions);
        
        foreach ($transactions as $transaction) {
            $this->assertEquals($broker->id, $transaction->broker_id);
        }
    }

    public function test_client_can_view_own_transactions(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        Transaction::factory()->count(2)->create([
            'client_id' => $client->id,
            'broker_id' => $broker->id,
            'property_id' => $property->id
        ]);

        // Create transactions for other clients
        Transaction::factory()->count(3)->create();

        $response = $this->actingAs($client)->get('/client/transactions');

        $response->assertStatus(200)
                 ->assertViewIs('client.transactions.index')
                 ->assertViewHas('transactions');

        $transactions = $response->viewData('transactions');
        $this->assertCount(2, $transactions);
        
        foreach ($transactions as $transaction) {
            $this->assertEquals($client->id, $transaction->client_id);
        }
    }

    public function test_broker_can_update_transaction_status(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        $transaction = Transaction::factory()->create([
            'broker_id' => $broker->id,
            'client_id' => $client->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        $updateData = [
            'status' => 'completed',
            'completion_date' => now()->format('Y-m-d'),
            'notes' => 'Transaction completed successfully.'
        ];

        $response = $this->actingAs($broker)
                         ->put("/broker/transactions/{$transaction->id}", $updateData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'completed',
            'notes' => 'Transaction completed successfully.'
        ]);
    }

    public function test_broker_cannot_update_other_broker_transaction(): void
    {
        $broker1 = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $broker2 = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker2->id]);
        $transaction = Transaction::factory()->create([
            'broker_id' => $broker2->id,
            'client_id' => $client->id,
            'property_id' => $property->id
        ]);

        $updateData = [
            'status' => 'completed',
            'notes' => 'Unauthorized update'
        ];

        $response = $this->actingAs($broker1)
                         ->put("/broker/transactions/{$transaction->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_admin_can_view_all_transactions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        Transaction::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/transactions');

        $response->assertStatus(200)
                 ->assertViewIs('admin.transactions.index')
                 ->assertViewHas('transactions');

        $transactions = $response->viewData('transactions');
        $this->assertCount(5, $transactions);
    }

    public function test_transaction_validation_fails_for_missing_fields(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        $response = $this->actingAs($broker)->post('/broker/transactions', []);

        $response->assertSessionHasErrors([
            'property_id',
            'client_id',
            'transaction_type',
            'amount'
        ]);
    }

    public function test_transaction_validation_fails_for_invalid_property(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);

        $transactionData = [
            'property_id' => 999, // Non-existent property
            'client_id' => $client->id,
            'transaction_type' => 'sale',
            'amount' => 5000000
        ];

        $response = $this->actingAs($broker)->post('/broker/transactions', $transactionData);

        $response->assertSessionHasErrors(['property_id']);
    }

    public function test_transaction_validation_fails_for_invalid_client(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $transactionData = [
            'property_id' => $property->id,
            'client_id' => 999, // Non-existent client
            'transaction_type' => 'sale',
            'amount' => 5000000
        ];

        $response = $this->actingAs($broker)->post('/broker/transactions', $transactionData);

        $response->assertSessionHasErrors(['client_id']);
    }

    public function test_transaction_validation_fails_for_negative_amount(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $transactionData = [
            'property_id' => $property->id,
            'client_id' => $client->id,
            'transaction_type' => 'sale',
            'amount' => -1000 // Negative amount
        ];

        $response = $this->actingAs($broker)->post('/broker/transactions', $transactionData);

        $response->assertSessionHasErrors(['amount']);
    }

    public function test_transaction_can_calculate_commission(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $transactionData = [
            'property_id' => $property->id,
            'client_id' => $client->id,
            'transaction_type' => 'sale',
            'amount' => 5000000,
            'commission_rate' => 5.0
        ];

        $response = $this->actingAs($broker)->post('/broker/transactions', $transactionData);

        $response->assertRedirect();

        $transaction = Transaction::where('property_id', $property->id)->first();
        $expectedCommission = 5000000 * 0.05; // 5% of 5,000,000
        
        $this->assertEquals($expectedCommission, $transaction->commission_amount);
    }

    public function test_transaction_filters_by_status(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        Transaction::factory()->create([
            'broker_id' => $broker->id,
            'client_id' => $client->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);
        Transaction::factory()->create([
            'broker_id' => $broker->id,
            'client_id' => $client->id,
            'property_id' => $property->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($broker)->get('/broker/transactions?status=pending');

        $response->assertStatus(200);
        $transactions = $response->viewData('transactions');
        $this->assertCount(1, $transactions);
        $this->assertEquals('pending', $transactions->first()->status);
    }

    public function test_transaction_shows_property_and_client_details(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create([
            'role' => 'client',
            'name' => 'John Doe'
        ]);
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'title' => 'Beautiful Beach House'
        ]);
        $transaction = Transaction::factory()->create([
            'broker_id' => $broker->id,
            'client_id' => $client->id,
            'property_id' => $property->id
        ]);

        $response = $this->actingAs($broker)->get("/broker/transactions/{$transaction->id}");

        $response->assertStatus(200)
                 ->assertViewIs('broker.transactions.show')
                 ->assertViewHas('transaction', $transaction)
                 ->assertSee('Beautiful Beach House')
                 ->assertSee('John Doe');
    }

    public function test_guest_cannot_access_transactions(): void
    {
        $response = $this->get('/broker/transactions');
        $response->assertRedirect('/login');

        $response = $this->get('/client/transactions');
        $response->assertRedirect('/login');
    }

    public function test_client_cannot_access_broker_transactions(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client)->get('/broker/transactions');
        $response->assertStatus(403);
    }

    public function test_broker_cannot_access_admin_transactions(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);

        $response = $this->actingAs($broker)->get('/admin/transactions');
        $response->assertStatus(403);
    }
}
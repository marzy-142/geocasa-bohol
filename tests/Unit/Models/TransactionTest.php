<?php

namespace Tests\Unit\Models;

use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_transaction_has_correct_fillable_attributes(): void
    {
        $fillable = [
            'property_id',
            'client_id',
            'broker_id',
            'transaction_type',
            'amount',
            'commission_rate',
            'commission_amount',
            'status',
            'completion_date',
            'notes'
        ];

        $transaction = new Transaction();
        $this->assertEquals($fillable, $transaction->getFillable());
    }

    public function test_transaction_has_correct_casts(): void
    {
        $transaction = new Transaction();
        $casts = $transaction->getCasts();

        $this->assertArrayHasKey('amount', $casts);
        $this->assertEquals('decimal:2', $casts['amount']);
        
        $this->assertArrayHasKey('commission_rate', $casts);
        $this->assertEquals('decimal:2', $casts['commission_rate']);
        
        $this->assertArrayHasKey('commission_amount', $casts);
        $this->assertEquals('decimal:2', $casts['commission_amount']);
        
        $this->assertArrayHasKey('completion_date', $casts);
        $this->assertEquals('date', $casts['completion_date']);
    }

    public function test_transaction_belongs_to_property(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        $transaction = Transaction::factory()->create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'broker_id' => $broker->id
        ]);

        $this->assertInstanceOf(Property::class, $transaction->property);
        $this->assertEquals($property->id, $transaction->property->id);
    }

    public function test_transaction_belongs_to_client(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        $transaction = Transaction::factory()->create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'broker_id' => $broker->id
        ]);

        $this->assertInstanceOf(User::class, $transaction->client);
        $this->assertEquals($client->id, $transaction->client->id);
        $this->assertEquals('client', $transaction->client->role);
    }

    public function test_transaction_belongs_to_broker(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        $transaction = Transaction::factory()->create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'broker_id' => $broker->id
        ]);

        $this->assertInstanceOf(User::class, $transaction->broker);
        $this->assertEquals($broker->id, $transaction->broker->id);
        $this->assertEquals('broker', $transaction->broker->role);
    }

    public function test_transaction_can_be_created_with_required_attributes(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $transaction = Transaction::create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'broker_id' => $broker->id,
            'transaction_type' => 'sale',
            'amount' => 5000000.00,
            'status' => 'pending'
        ]);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals($property->id, $transaction->property_id);
        $this->assertEquals($client->id, $transaction->client_id);
        $this->assertEquals($broker->id, $transaction->broker_id);
        $this->assertEquals('sale', $transaction->transaction_type);
        $this->assertEquals(5000000.00, $transaction->amount);
        $this->assertEquals('pending', $transaction->status);
    }

    public function test_transaction_can_be_created_with_all_attributes(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $transaction = Transaction::create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'broker_id' => $broker->id,
            'transaction_type' => 'sale',
            'amount' => 5000000.00,
            'commission_rate' => 5.00,
            'commission_amount' => 250000.00,
            'status' => 'completed',
            'completion_date' => now()->format('Y-m-d'),
            'notes' => 'Transaction completed successfully.'
        ]);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals($property->id, $transaction->property_id);
        $this->assertEquals($client->id, $transaction->client_id);
        $this->assertEquals($broker->id, $transaction->broker_id);
        $this->assertEquals('sale', $transaction->transaction_type);
        $this->assertEquals(5000000.00, $transaction->amount);
        $this->assertEquals(5.00, $transaction->commission_rate);
        $this->assertEquals(250000.00, $transaction->commission_amount);
        $this->assertEquals('completed', $transaction->status);
        $this->assertNotNull($transaction->completion_date);
        $this->assertEquals('Transaction completed successfully.', $transaction->notes);
    }

    public function test_transaction_factory_creates_valid_transaction(): void
    {
        $transaction = Transaction::factory()->create();

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertNotNull($transaction->property_id);
        $this->assertNotNull($transaction->client_id);
        $this->assertNotNull($transaction->broker_id);
        $this->assertNotNull($transaction->transaction_type);
        $this->assertNotNull($transaction->amount);
        $this->assertNotNull($transaction->status);
        $this->assertInstanceOf(Property::class, $transaction->property);
        $this->assertInstanceOf(User::class, $transaction->client);
        $this->assertInstanceOf(User::class, $transaction->broker);
    }

    public function test_transaction_can_calculate_commission_amount(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $transaction = Transaction::create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'broker_id' => $broker->id,
            'transaction_type' => 'sale',
            'amount' => 5000000.00,
            'commission_rate' => 5.00,
            'status' => 'pending'
        ]);

        // Calculate commission: 5% of 5,000,000 = 250,000
        $expectedCommission = $transaction->amount * ($transaction->commission_rate / 100);
        $transaction->commission_amount = $expectedCommission;
        $transaction->save();

        $this->assertEquals(250000.00, $transaction->commission_amount);
    }

    public function test_transaction_can_be_marked_as_completed(): void
    {
        $transaction = Transaction::factory()->create(['status' => 'pending']);

        $transaction->update([
            'status' => 'completed',
            'completion_date' => now()->format('Y-m-d'),
            'notes' => 'Transaction completed successfully.'
        ]);

        $this->assertEquals('completed', $transaction->status);
        $this->assertNotNull($transaction->completion_date);
        $this->assertEquals('Transaction completed successfully.', $transaction->notes);
    }

    public function test_transaction_can_be_cancelled(): void
    {
        $transaction = Transaction::factory()->create(['status' => 'pending']);

        $transaction->update([
            'status' => 'cancelled',
            'notes' => 'Transaction cancelled by client.'
        ]);

        $this->assertEquals('cancelled', $transaction->status);
        $this->assertEquals('Transaction cancelled by client.', $transaction->notes);
    }

    public function test_transaction_supports_soft_deletes(): void
    {
        $transaction = Transaction::factory()->create();
        $transactionId = $transaction->id;

        $transaction->delete();

        $this->assertSoftDeleted('transactions', ['id' => $transactionId]);
        $this->assertNotNull($transaction->fresh()->deleted_at);
    }

    public function test_transaction_can_be_restored_after_soft_delete(): void
    {
        $transaction = Transaction::factory()->create();
        $transactionId = $transaction->id;

        $transaction->delete();
        $this->assertSoftDeleted('transactions', ['id' => $transactionId]);

        $transaction->restore();
        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'deleted_at' => null
        ]);
    }

    public function test_transaction_status_defaults_to_pending(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $transaction = Transaction::create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'broker_id' => $broker->id,
            'transaction_type' => 'sale',
            'amount' => 5000000.00
        ]);

        $this->assertEquals('pending', $transaction->status);
    }

    public function test_transaction_validates_transaction_type(): void
    {
        $validTypes = ['sale', 'rent', 'lease'];
        
        foreach ($validTypes as $type) {
            $transaction = Transaction::factory()->create([
                'transaction_type' => $type
            ]);
            
            $this->assertEquals($type, $transaction->transaction_type);
        }
    }

    public function test_transaction_validates_status_values(): void
    {
        $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        
        foreach ($validStatuses as $status) {
            $transaction = Transaction::factory()->create([
                'status' => $status
            ]);
            
            $this->assertEquals($status, $transaction->status);
        }
    }

    public function test_transaction_amount_is_positive(): void
    {
        $transaction = Transaction::factory()->create([
            'amount' => 5000000.00
        ]);

        $this->assertGreaterThan(0, $transaction->amount);
    }

    public function test_transaction_commission_rate_is_percentage(): void
    {
        $transaction = Transaction::factory()->create([
            'commission_rate' => 5.50
        ]);

        $this->assertGreaterThanOrEqual(0, $transaction->commission_rate);
        $this->assertLessThanOrEqual(100, $transaction->commission_rate);
    }

    public function test_transaction_decimal_precision(): void
    {
        $transaction = Transaction::factory()->create([
            'amount' => 5000000.99,
            'commission_rate' => 5.75,
            'commission_amount' => 287500.57
        ]);

        $this->assertEquals(5000000.99, $transaction->amount);
        $this->assertEquals(5.75, $transaction->commission_rate);
        $this->assertEquals(287500.57, $transaction->commission_amount);
    }
}
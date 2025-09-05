<?php

namespace Tests\Feature\Client;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_client_can_access_dashboard(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        // Create some test data for client dashboard
        Inquiry::factory()->count(3)->create([
            'client_id' => $client->id,
            'property_id' => $property->id
        ]);
        Transaction::factory()->count(2)->create([
            'client_id' => $client->id,
            'broker_id' => $broker->id,
            'property_id' => $property->id
        ]);

        $response = $this->actingAs($client)->get('/client/dashboard');

        $response->assertStatus(200)
                 ->assertViewIs('client.dashboard')
                 ->assertViewHas(['totalInquiries', 'totalTransactions', 'recentInquiries', 'recentTransactions']);

        $viewData = $response->viewData();
        $this->assertEquals(3, $viewData['totalInquiries']);
        $this->assertEquals(2, $viewData['totalTransactions']);
    }

    public function test_client_can_view_profile(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $response = $this->actingAs($client)->get('/client/profile');

        $response->assertStatus(200)
                 ->assertViewIs('client.profile')
                 ->assertViewHas('user', $client)
                 ->assertSee('John Doe')
                 ->assertSee('john@example.com');
    }

    public function test_client_can_update_profile(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $updateData = [
            'name' => 'John Updated',
            'email' => 'john.updated@example.com',
            'phone' => '+639123456789',
            'address' => '123 Updated Street, Tagbilaran City'
        ];

        $response = $this->actingAs($client)
                         ->put('/client/profile', $updateData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $client->id,
            'name' => 'John Updated',
            'email' => 'john.updated@example.com',
            'phone' => '+639123456789',
            'address' => '123 Updated Street, Tagbilaran City'
        ]);
    }

    public function test_client_profile_validation_fails_for_invalid_email(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $updateData = [
            'name' => 'John Doe',
            'email' => 'invalid-email', // Invalid email format
            'phone' => '+639123456789'
        ];

        $response = $this->actingAs($client)
                         ->put('/client/profile', $updateData);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_client_profile_validation_fails_for_duplicate_email(): void
    {
        $client1 = User::factory()->create(['role' => 'client']);
        $client2 = User::factory()->create([
            'role' => 'client',
            'email' => 'existing@example.com'
        ]);

        $updateData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com', // Email already exists
            'phone' => '+639123456789'
        ];

        $response = $this->actingAs($client1)
                         ->put('/client/profile', $updateData);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_client_can_view_own_inquiries(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        Inquiry::factory()->count(5)->create([
            'client_id' => $client->id,
            'property_id' => $property->id
        ]);

        // Create inquiries for other clients
        Inquiry::factory()->count(3)->create();

        $response = $this->actingAs($client)->get('/client/inquiries');

        $response->assertStatus(200)
                 ->assertViewIs('client.inquiries.index')
                 ->assertViewHas('inquiries');

        $inquiries = $response->viewData('inquiries');
        $this->assertCount(5, $inquiries);
        
        foreach ($inquiries as $inquiry) {
            $this->assertEquals($client->id, $inquiry->client_id);
        }
    }

    public function test_client_can_view_single_inquiry(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'title' => 'Beautiful Beach House'
        ]);
        $inquiry = Inquiry::factory()->create([
            'client_id' => $client->id,
            'property_id' => $property->id,
            'message' => 'I am interested in this property.'
        ]);

        $response = $this->actingAs($client)->get("/client/inquiries/{$inquiry->id}");

        $response->assertStatus(200)
                 ->assertViewIs('client.inquiries.show')
                 ->assertViewHas('inquiry', $inquiry)
                 ->assertSee('Beautiful Beach House')
                 ->assertSee('I am interested in this property.');
    }

    public function test_client_cannot_view_other_client_inquiry(): void
    {
        $client1 = User::factory()->create(['role' => 'client']);
        $client2 = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        $inquiry = Inquiry::factory()->create([
            'client_id' => $client2->id,
            'property_id' => $property->id
        ]);

        $response = $this->actingAs($client1)->get("/client/inquiries/{$inquiry->id}");

        $response->assertStatus(403);
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

        Transaction::factory()->count(3)->create([
            'client_id' => $client->id,
            'broker_id' => $broker->id,
            'property_id' => $property->id
        ]);

        // Create transactions for other clients
        Transaction::factory()->count(2)->create();

        $response = $this->actingAs($client)->get('/client/transactions');

        $response->assertStatus(200)
                 ->assertViewIs('client.transactions.index')
                 ->assertViewHas('transactions');

        $transactions = $response->viewData('transactions');
        $this->assertCount(3, $transactions);
        
        foreach ($transactions as $transaction) {
            $this->assertEquals($client->id, $transaction->client_id);
        }
    }

    public function test_client_can_view_single_transaction(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'title' => 'Luxury Condo Unit'
        ]);
        $transaction = Transaction::factory()->create([
            'client_id' => $client->id,
            'broker_id' => $broker->id,
            'property_id' => $property->id,
            'amount' => 5000000
        ]);

        $response = $this->actingAs($client)->get("/client/transactions/{$transaction->id}");

        $response->assertStatus(200)
                 ->assertViewIs('client.transactions.show')
                 ->assertViewHas('transaction', $transaction)
                 ->assertSee('Luxury Condo Unit')
                 ->assertSee('5,000,000');
    }

    public function test_client_cannot_view_other_client_transaction(): void
    {
        $client1 = User::factory()->create(['role' => 'client']);
        $client2 = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        $transaction = Transaction::factory()->create([
            'client_id' => $client2->id,
            'broker_id' => $broker->id,
            'property_id' => $property->id
        ]);

        $response = $this->actingAs($client1)->get("/client/transactions/{$transaction->id}");

        $response->assertStatus(403);
    }

    public function test_client_can_filter_inquiries_by_status(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        Inquiry::factory()->count(3)->create([
            'client_id' => $client->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);
        Inquiry::factory()->count(2)->create([
            'client_id' => $client->id,
            'property_id' => $property->id,
            'status' => 'responded'
        ]);

        $response = $this->actingAs($client)->get('/client/inquiries?status=pending');

        $response->assertStatus(200);
        $inquiries = $response->viewData('inquiries');
        $this->assertCount(3, $inquiries);
        
        foreach ($inquiries as $inquiry) {
            $this->assertEquals('pending', $inquiry->status);
        }
    }

    public function test_client_can_filter_transactions_by_status(): void
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
            'property_id' => $property->id,
            'status' => 'pending'
        ]);
        Transaction::factory()->count(1)->create([
            'client_id' => $client->id,
            'broker_id' => $broker->id,
            'property_id' => $property->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($client)->get('/client/transactions?status=pending');

        $response->assertStatus(200);
        $transactions = $response->viewData('transactions');
        $this->assertCount(2, $transactions);
        
        foreach ($transactions as $transaction) {
            $this->assertEquals('pending', $transaction->status);
        }
    }

    public function test_client_can_change_password(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'password' => bcrypt('oldpassword')
        ]);

        $passwordData = [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->actingAs($client)
                         ->put('/client/password', $passwordData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $client->refresh();
        $this->assertTrue(Hash::check('newpassword123', $client->password));
    }

    public function test_client_password_change_fails_with_wrong_current_password(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'password' => bcrypt('oldpassword')
        ]);

        $passwordData = [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->actingAs($client)
                         ->put('/client/password', $passwordData);

        $response->assertSessionHasErrors(['current_password']);
    }

    public function test_client_can_view_favorite_properties(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $properties = Property::factory()->count(3)->create(['broker_id' => $broker->id]);

        // Simulate adding properties to favorites (assuming a favorites relationship exists)
        foreach ($properties as $property) {
            $client->favoriteProperties()->attach($property->id);
        }

        $response = $this->actingAs($client)->get('/client/favorites');

        $response->assertStatus(200)
                 ->assertViewIs('client.favorites')
                 ->assertViewHas('favoriteProperties');

        $favoriteProperties = $response->viewData('favoriteProperties');
        $this->assertCount(3, $favoriteProperties);
    }

    public function test_non_client_cannot_access_client_routes(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $admin = User::factory()->create(['role' => 'admin']);

        // Test broker access
        $response = $this->actingAs($broker)->get('/client/dashboard');
        $response->assertStatus(403);

        // Test admin access
        $response = $this->actingAs($admin)->get('/client/dashboard');
        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_client_routes(): void
    {
        $response = $this->get('/client/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/client/profile');
        $response->assertRedirect('/login');

        $response = $this->get('/client/inquiries');
        $response->assertRedirect('/login');
    }
}
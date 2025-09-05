<?php

namespace Tests\Feature\Admin;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\SellerRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create some test data for dashboard statistics
        User::factory()->count(5)->create(['role' => 'broker']);
        User::factory()->count(10)->create(['role' => 'client']);
        Property::factory()->count(15)->create();
        Transaction::factory()->count(8)->create();
        Inquiry::factory()->count(12)->create();
        SellerRequest::factory()->count(6)->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200)
                 ->assertViewIs('admin.dashboard')
                 ->assertViewHas(['totalBrokers', 'totalClients', 'totalProperties', 'totalTransactions', 'totalInquiries', 'totalSellerRequests']);

        $viewData = $response->viewData();
        $this->assertEquals(5, $viewData['totalBrokers']);
        $this->assertEquals(10, $viewData['totalClients']);
        $this->assertEquals(15, $viewData['totalProperties']);
        $this->assertEquals(8, $viewData['totalTransactions']);
        $this->assertEquals(12, $viewData['totalInquiries']);
        $this->assertEquals(6, $viewData['totalSellerRequests']);
    }

    public function test_admin_can_view_all_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(5)->create(['role' => 'broker']);
        User::factory()->count(3)->create(['role' => 'client']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200)
                 ->assertViewIs('admin.users.index')
                 ->assertViewHas('users');

        $users = $response->viewData('users');
        // Total users should be 9 (5 brokers + 3 clients + 1 admin)
        $this->assertCount(9, $users);
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(5)->create(['role' => 'broker']);
        User::factory()->count(3)->create(['role' => 'client']);

        $response = $this->actingAs($admin)->get('/admin/users?role=broker');

        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertCount(5, $users);
        
        foreach ($users as $user) {
            $this->assertEquals('broker', $user->role);
        }
    }

    public function test_admin_can_approve_broker_application(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => false,
            'application_status' => 'pending'
        ]);

        $response = $this->actingAs($admin)
                         ->post("/admin/users/{$broker->id}/approve");

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $broker->id,
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
    }

    public function test_admin_can_reject_broker_application(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => false,
            'application_status' => 'pending'
        ]);

        $rejectionData = [
            'rejection_reason' => 'Incomplete documentation provided.'
        ];

        $response = $this->actingAs($admin)
                         ->post("/admin/users/{$broker->id}/reject", $rejectionData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $broker->id,
            'is_approved' => false,
            'application_status' => 'rejected'
        ]);
    }

    public function test_admin_can_suspend_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'broker']);

        $suspensionData = [
            'suspension_reason' => 'Violation of terms of service.'
        ];

        $response = $this->actingAs($admin)
                         ->post("/admin/users/{$user->id}/suspend", $suspensionData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id
        ]);
        
        $user->refresh();
        $this->assertNotNull($user->suspended_at);
    }

    public function test_admin_can_unsuspend_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'role' => 'broker',
            'suspended_at' => now()
        ]);

        $response = $this->actingAs($admin)
                         ->post("/admin/users/{$user->id}/unsuspend");

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'suspended_at' => null
        ]);
    }

    public function test_admin_can_view_all_properties(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Property::factory()->count(10)->create();

        $response = $this->actingAs($admin)->get('/admin/properties');

        $response->assertStatus(200)
                 ->assertViewIs('admin.properties.index')
                 ->assertViewHas('properties');

        $properties = $response->viewData('properties');
        $this->assertCount(10, $properties);
    }

    public function test_admin_can_filter_properties_by_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Property::factory()->count(5)->create(['status' => 'available']);
        Property::factory()->count(3)->create(['status' => 'sold']);
        Property::factory()->count(2)->create(['status' => 'pending']);

        $response = $this->actingAs($admin)->get('/admin/properties?status=available');

        $response->assertStatus(200);
        $properties = $response->viewData('properties');
        $this->assertCount(5, $properties);
        
        foreach ($properties as $property) {
            $this->assertEquals('available', $property->status);
        }
    }

    public function test_admin_can_delete_property(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $property = Property::factory()->create();

        $response = $this->actingAs($admin)
                         ->delete("/admin/properties/{$property->id}");

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_admin_can_view_all_transactions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Transaction::factory()->count(8)->create();

        $response = $this->actingAs($admin)->get('/admin/transactions');

        $response->assertStatus(200)
                 ->assertViewIs('admin.transactions.index')
                 ->assertViewHas('transactions');

        $transactions = $response->viewData('transactions');
        $this->assertCount(8, $transactions);
    }

    public function test_admin_can_view_system_analytics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create test data for analytics
        User::factory()->count(10)->create(['role' => 'broker', 'created_at' => now()->subDays(30)]);
        User::factory()->count(15)->create(['role' => 'client', 'created_at' => now()->subDays(15)]);
        Property::factory()->count(20)->create(['created_at' => now()->subDays(10)]);
        Transaction::factory()->count(5)->create(['created_at' => now()->subDays(5)]);

        $response = $this->actingAs($admin)->get('/admin/analytics');

        $response->assertStatus(200)
                 ->assertViewIs('admin.analytics')
                 ->assertViewHas(['userGrowth', 'propertyGrowth', 'transactionGrowth']);
    }

    public function test_admin_can_manage_seller_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        SellerRequest::factory()->count(5)->create(['status' => 'pending']);
        SellerRequest::factory()->count(3)->create(['status' => 'approved']);

        $response = $this->actingAs($admin)->get('/admin/seller-requests');

        $response->assertStatus(200)
                 ->assertViewIs('admin.seller-requests.index')
                 ->assertViewHas('sellerRequests');

        $sellerRequests = $response->viewData('sellerRequests');
        $this->assertCount(8, $sellerRequests);
    }

    public function test_admin_can_approve_seller_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sellerRequest = SellerRequest::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($admin)
                         ->post("/admin/seller-requests/{$sellerRequest->id}/approve");

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('seller_requests', [
            'id' => $sellerRequest->id,
            'status' => 'approved'
        ]);
    }

    public function test_admin_can_reject_seller_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sellerRequest = SellerRequest::factory()->create(['status' => 'pending']);

        $rejectionData = [
            'rejection_reason' => 'Insufficient property information provided.'
        ];

        $response = $this->actingAs($admin)
                         ->post("/admin/seller-requests/{$sellerRequest->id}/reject", $rejectionData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('seller_requests', [
            'id' => $sellerRequest->id,
            'status' => 'rejected'
        ]);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $client = User::factory()->create(['role' => 'client']);

        // Test broker access
        $response = $this->actingAs($broker)->get('/admin/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($broker)->get('/admin/users');
        $response->assertStatus(403);

        // Test client access
        $response = $this->actingAs($client)->get('/admin/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($client)->get('/admin/properties');
        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/admin/users');
        $response->assertRedirect('/login');

        $response = $this->get('/admin/properties');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_search_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'broker'
        ]);
        User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'role' => 'client'
        ]);

        $response = $this->actingAs($admin)->get('/admin/users?search=John');

        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertCount(1, $users);
        $this->assertEquals('John Doe', $users->first()->name);
    }

    public function test_admin_can_export_user_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/users/export');

        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_view_system_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/settings');

        $response->assertStatus(200)
                 ->assertViewIs('admin.settings')
                 ->assertViewHas('settings');
    }

    public function test_admin_can_update_system_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $settingsData = [
            'site_name' => 'GeoCasa Bohol Updated',
            'commission_rate' => 6.0,
            'max_property_images' => 15,
            'maintenance_mode' => false
        ];

        $response = $this->actingAs($admin)
                         ->put('/admin/settings', $settingsData);

        $response->assertRedirect()
                 ->assertSessionHas('success');
    }
}
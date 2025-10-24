<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Transaction;
use App\Models\NotificationPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_user_has_fillable_attributes(): void
    {
        $fillable = [
            'name', 'email', 'password', 'role', 'phone', 'address', 'municipality',
            'barangay', 'date_of_birth', 'gender', 'occupation',
            'prc_id', 'years_of_experience', 'specialization', 'bio',
            'profile_picture', 'application_status', 'is_approved', 'approval_date',
            'approved_by', 'rejection_reason', 'credentials', 'suspended_at',
            'suspension_reason', 'suspended_by'
        ];

        $user = new User();
        $this->assertEquals($fillable, $user->getFillable());
    }

    public function test_user_has_hidden_attributes(): void
    {
        $hidden = ['password', 'remember_token'];
        $user = new User();
        $this->assertEquals($hidden, $user->getHidden());
    }

    public function test_user_casts_attributes_correctly(): void
    {
        $user = new User();
        $casts = $user->getCasts();

        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('hashed', $casts['password']);
        $this->assertEquals('array', $casts['credentials']);
        $this->assertEquals('boolean', $casts['is_approved']);
        $this->assertEquals('datetime', $casts['approval_date']);
        $this->assertEquals('datetime', $casts['suspended_at']);
        $this->assertEquals('date', $casts['date_of_birth']);
    }

    public function test_user_can_be_created_with_basic_attributes(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'client'
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('client', $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_broker_user_has_many_properties(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $property1 = Property::factory()->create(['broker_id' => $broker->id]);
        $property2 = Property::factory()->create(['broker_id' => $broker->id]);

        $this->assertCount(2, $broker->properties);
        $this->assertTrue($broker->properties->contains($property1));
        $this->assertTrue($broker->properties->contains($property2));
    }

    public function test_user_has_many_inquiries(): void
    {
        $user = User::factory()->create();
        $inquiry1 = Inquiry::factory()->create(['client_id' => $user->id]);
        $inquiry2 = Inquiry::factory()->create(['client_id' => $user->id]);

        $this->assertCount(2, $user->inquiries);
        $this->assertTrue($user->inquiries->contains($inquiry1));
        $this->assertTrue($user->inquiries->contains($inquiry2));
    }

    public function test_user_has_many_transactions_as_client(): void
    {
        $user = User::factory()->create();
        $transaction1 = Transaction::factory()->create(['client_id' => $user->id]);
        $transaction2 = Transaction::factory()->create(['client_id' => $user->id]);

        $this->assertCount(2, $user->clientTransactions);
        $this->assertTrue($user->clientTransactions->contains($transaction1));
        $this->assertTrue($user->clientTransactions->contains($transaction2));
    }

    public function test_broker_has_many_transactions_as_broker(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $transaction1 = Transaction::factory()->create(['broker_id' => $broker->id]);
        $transaction2 = Transaction::factory()->create(['broker_id' => $broker->id]);

        $this->assertCount(2, $broker->brokerTransactions);
        $this->assertTrue($broker->brokerTransactions->contains($transaction1));
        $this->assertTrue($broker->brokerTransactions->contains($transaction2));
    }

    public function test_user_has_one_notification_preference(): void
    {
        $user = User::factory()->create();
        $preference = NotificationPreference::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(NotificationPreference::class, $user->notificationPreference);
        $this->assertEquals($preference->id, $user->notificationPreference->id);
    }

    public function test_user_credentials_are_cast_to_array(): void
    {
        $credentials = ['license.pdf', 'certificate.pdf'];
        $user = User::factory()->create(['credentials' => $credentials]);

        $this->assertIsArray($user->credentials);
        $this->assertEquals($credentials, $user->credentials);
    }

    public function test_user_boolean_attributes_are_cast_correctly(): void
    {
        $user = User::factory()->create([
            'is_approved' => true
        ]);

        $this->assertIsBool($user->is_approved);
        $this->assertTrue($user->is_approved);
    }

    public function test_user_date_attributes_are_cast_correctly(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => '1990-01-01',
            'approval_date' => now(),
            'suspended_at' => now()
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->date_of_birth);
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->approval_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->suspended_at);
    }

    public function test_user_factory_creates_valid_user(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotEmpty($user->name);
        $this->assertNotEmpty($user->email);
        $this->assertNotEmpty($user->password);
        $this->assertContains($user->role, ['client', 'broker', 'admin']);
    }

    public function test_broker_factory_creates_broker_user(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);

        $this->assertEquals('broker', $broker->role);
        $this->assertNotEmpty($broker->prc_id);
        $this->assertNotEmpty($broker->specialization);
    }

    public function test_admin_factory_creates_admin_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertEquals('admin', $admin->role);
        $this->assertTrue($admin->is_approved);
        $this->assertEquals('approved', $admin->application_status);
    }

    public function test_user_can_be_suspended(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $user->update([
            'suspended_at' => now(),
            'suspension_reason' => 'Violation of terms',
            'suspended_by' => $admin->id
        ]);

        $this->assertNotNull($user->suspended_at);
        $this->assertEquals('Violation of terms', $user->suspension_reason);
        $this->assertEquals($admin->id, $user->suspended_by);
    }

    public function test_broker_can_be_approved(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'application_status' => 'pending',
            'is_approved' => false
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $broker->update([
            'application_status' => 'approved',
            'is_approved' => true,
            'approval_date' => now(),
            'approved_by' => $admin->id
        ]);

        $this->assertTrue($broker->is_approved);
        $this->assertEquals('approved', $broker->application_status);
        $this->assertNotNull($broker->approval_date);
        $this->assertEquals($admin->id, $broker->approved_by);
    }

    public function test_broker_can_be_rejected(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'application_status' => 'pending',
            'is_approved' => false
        ]);

        $broker->update([
            'application_status' => 'rejected',
            'rejection_reason' => 'Incomplete documentation'
        ]);

        $this->assertFalse($broker->is_approved);
        $this->assertEquals('rejected', $broker->application_status);
        $this->assertEquals('Incomplete documentation', $broker->rejection_reason);
    }

    public function test_user_password_is_automatically_hashed(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'plaintext_password',
            'role' => 'client'
        ]);

        $this->assertNotEquals('plaintext_password', $user->password);
        $this->assertTrue(Hash::check('plaintext_password', $user->password));
    }

    public function test_user_soft_deletes(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        $this->assertSoftDeleted('users', ['id' => $userId]);
        $this->assertNull(User::find($userId));
        $this->assertNotNull(User::withTrashed()->find($userId));
    }
}
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_user_can_register(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'client'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'user' => [
                             'id',
                             'name',
                             'email',
                             'role',
                             'created_at',
                             'updated_at'
                         ],
                         'token'
                     ]
                 ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'client'
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'user' => [
                             'id',
                             'name',
                             'email',
                             'role'
                         ],
                         'token'
                     ]
                 ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Invalid credentials'
                 ]);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'name',
                         'email',
                         'role',
                         'created_at',
                         'updated_at'
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $user->id,
                         'name' => $user->name,
                         'email' => $user->email
                     ]
                 ]);
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.'
                 ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Logged out successfully'
                 ]);
    }

    public function test_registration_validation_fails_for_missing_fields(): void
    {
        $response = $this->postJson('/api/v1/register', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'name',
                     'email',
                     'password'
                 ]);
    }

    public function test_registration_validation_fails_for_invalid_email(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_validation_fails_for_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_validation_fails_for_password_mismatch(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_validation_fails_for_short_password(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123',
            'password_confirmation' => '123'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    public function test_login_validation_fails_for_missing_fields(): void
    {
        $response = $this->postJson('/api/v1/login', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'email',
                     'password'
                 ]);
    }

    public function test_broker_registration_sets_pending_status(): void
    {
        $userData = [
            'name' => 'Jane Broker',
            'email' => 'jane@broker.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'broker'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@broker.com',
            'role' => 'broker',
            'application_status' => 'pending',
            'is_approved' => false
        ]);
    }

    public function test_client_registration_sets_approved_status(): void
    {
        $userData = [
            'name' => 'John Client',
            'email' => 'john@client.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'client'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'john@client.com',
            'role' => 'client',
            'application_status' => 'approved',
            'is_approved' => true
        ]);
    }
}
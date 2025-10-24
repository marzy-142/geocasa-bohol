<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'role' => 'client',
        ]);

        // Should redirect to email verification notice
        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'client',
            'is_approved' => true,
            'application_status' => 'approved',
        ]);
    }

    public function test_registration_requires_strong_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'role' => 'client',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_registration_requires_email_verification(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'role' => 'client',
        ]);

        // Should not be logged in immediately
        $this->assertGuest();
        
        // Should redirect to verification notice
        $response->assertRedirect(route('verification.notice'));
        
        // Should have pending user in session
        $this->assertTrue(session()->has('pending_user_id'));
    }
}

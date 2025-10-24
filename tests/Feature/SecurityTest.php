<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\LoginAttempt;
use App\Models\MfaToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test enhanced password policy
     */
    public function test_password_policy_enforcement()
    {
        // Test weak password (should fail)
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'role' => 'client'
        ]);

        $response->assertSessionHasErrors(['password']);

        // Test strong password (should pass)
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
            'role' => 'client'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'test2@example.com']);
    }

    /**
     * Test account lockout after failed attempts
     */
    public function test_account_lockout_mechanism()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('StrongPass123!')
        ]);

        // Attempt login with wrong password 5 times
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword'
            ]);
        }

        // Check if login attempts are recorded
        $this->assertDatabaseHas('login_attempts', [
            'email' => 'test@example.com',
            'success' => false
        ]);

        // 6th attempt should be blocked
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test MFA token generation and verification
     */
    public function test_mfa_functionality()
    {
        $user = User::factory()->create(['role' => 'broker']);

        // Test MFA enablement
        $mfaService = app(\App\Services\MultiFactorAuthenticationService::class);
        $result = $mfaService->enableMfa($user, 'email');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('backup_codes', $result);

        // Test backup codes generation
        $backupCodes = $mfaService->getBackupCodes($user);
        $this->assertCount(10, $backupCodes);

        // Test MFA token verification
        $token = MfaToken::createToken($user->id, 'email', 10);
        $verifyResult = $mfaService->verifyToken($user, $token->token, 'email');

        $this->assertTrue($verifyResult['success']);
    }

    /**
     * Test enhanced authentication service
     */
    public function test_enhanced_authentication_service()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('StrongPass123!')
        ]);

        $authService = app(\App\Services\EnhancedAuthenticationService::class);
        
        // Test successful login
        $request = $this->app['request'];
        $result = $authService->attemptLogin([
            'email' => 'test@example.com',
            'password' => 'StrongPass123!',
            'remember' => false
        ], $request);

        $this->assertTrue($result['success']);
        $this->assertEquals($user->id, $result['user']->id);

        // Test failed login
        $result = $authService->attemptLogin([
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
            'remember' => false
        ], $request);

        $this->assertFalse($result['success']);
    }

    /**
     * Test device fingerprinting
     */
    public function test_device_fingerprinting()
    {
        $user = User::factory()->create();
        $request = $this->app['request'];

        $authService = app(\App\Services\EnhancedAuthenticationService::class);
        
        // Simulate login attempt
        try {
            $authService->attemptLogin([
                'email' => $user->email,
                'password' => 'wrongpassword',
                'remember' => false
            ], $request);
        } catch (\Exception $e) {
            // Expected to fail
        }

        // Check if login attempt was recorded with device fingerprint
        $attempt = LoginAttempt::where('email', $user->email)->first();
        $this->assertNotNull($attempt->device_fingerprint);
    }

    /**
     * Test suspicious activity detection
     */
    public function test_suspicious_activity_detection()
    {
        $user = User::factory()->create();

        // Create multiple failed login attempts
        for ($i = 0; $i < 10; $i++) {
            LoginAttempt::recordAttempt([
                'email' => $user->email,
                'ip_address' => '192.168.1.' . $i,
                'user_agent' => 'Test Browser ' . $i,
                'success' => false,
                'failed_reason' => 'Invalid password',
                'attempted_at' => now(),
            ]);
        }

        $authService = app(\App\Services\EnhancedAuthenticationService::class);
        $activity = $authService->getSuspiciousActivityReport($user);

        $this->assertGreaterThan(15, $activity['suspicious_score']);
    }

    /**
     * Test session security headers
     */
    public function test_security_headers()
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
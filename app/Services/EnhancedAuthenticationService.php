<?php

namespace App\Services;

use App\Models\User;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class EnhancedAuthenticationService
{
    const MAX_LOGIN_ATTEMPTS = 10; // Increased from 5 to 10
    const LOCKOUT_DURATION = 300; // Reduced from 15 minutes to 5 minutes
    const SUSPICIOUS_ACTIVITY_THRESHOLD = 20; // Increased from 10 to 20

    /**
     * Attempt user login with enhanced security
     */
    public function attemptLogin(array $credentials, Request $request): array
    {
        $email = $credentials['email'];
        $password = $credentials['password'];
        $remember = $credentials['remember'] ?? false;

        // Check if IP or email is blocked
        if (LoginAttempt::isIpBlocked($request->ip())) {
            throw ValidationException::withMessages([
                'email' => 'Your IP address has been temporarily blocked due to multiple failed login attempts. Please try again later.',
            ]);
        }

        if (LoginAttempt::isEmailBlocked($email)) {
            throw ValidationException::withMessages([
                'email' => 'This email address has been temporarily blocked due to multiple failed login attempts. Please try again later.',
            ]);
        }

        // Rate limiting check
        $this->ensureIsNotRateLimited($email, $request);

        // Device fingerprinting
        $deviceFingerprint = $this->generateDeviceFingerprint($request);

        // Location data (simplified)
        $locationData = $this->getLocationData($request);

        // Attempt authentication
        $user = User::where('email', $email)->first();
        $loginSuccessful = false;
        $failedReason = null;

        if ($user && Hash::check($password, $user->password)) {
            // Check if user account is suspended
            if ($user->is_suspended) {
                $failedReason = 'Account suspended';
            } elseif ($user->role === 'broker' && !$user->is_approved) {
                $failedReason = 'Account pending approval';
            } else {
                $loginSuccessful = true;
                
                // Login successful
                Auth::login($user, $remember);
                $request->session()->regenerate();

                // Clear rate limiting for this email
                RateLimiter::clear($this->throttleKey($email, $request));

                // Record successful attempt
                LoginAttempt::recordAttempt([
                    'user_id' => $user->id,
                    'email' => $email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'success' => true,
                    'device_fingerprint' => $deviceFingerprint,
                    'location_data' => $locationData,
                ]);

                Log::info('Successful login', [
                    'user_id' => $user->id,
                    'email' => $email,
                    'ip_address' => $request->ip(),
                    'device_fingerprint' => $deviceFingerprint,
                ]);

                return [
                    'success' => true,
                    'user' => $user,
                    'redirect_url' => $this->getRedirectUrl($user),
                ];
            }
        } else {
            $failedReason = $user ? 'Invalid password' : 'User not found';
        }

        // Login failed - record attempt
        LoginAttempt::recordAttempt([
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'success' => false,
            'failed_reason' => $failedReason,
            'device_fingerprint' => $deviceFingerprint,
            'location_data' => $locationData,
        ]);

        // Hit rate limiter
        RateLimiter::hit($this->throttleKey($email, $request));

        // Check for suspicious activity
        $this->checkForSuspiciousActivity($email, $request);

        // Check if we should block IP or email
        $this->checkAndApplyBlocking($email, $request);

        Log::warning('Failed login attempt', [
            'email' => $email,
            'ip_address' => $request->ip(),
            'reason' => $failedReason,
            'device_fingerprint' => $deviceFingerprint,
        ]);

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    /**
     * Register a new user with enhanced security
     */
    public function registerUser(array $data, Request $request): User
    {
        // Device fingerprinting for registration
        $deviceFingerprint = $this->generateDeviceFingerprint($request);
        $locationData = $this->getLocationData($request);

        // Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'client',
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'is_approved' => $data['role'] === 'broker' ? false : true,
            'application_status' => $data['role'] === 'broker' ? 'pending' : 'approved',
        ]);

        // Handle broker-specific data
        if ($data['role'] === 'broker') {
            $user->update([
                'prc_id' => $data['prc_id'] ?? null,
                'prc_license_expiration' => $data['prc_license_expiration'] ?? null,
                'city' => $data['city'] ?? null,
                'province' => $data['province'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'terms_accepted' => $data['terms_accepted'] ?? false,
                'privacy_policy_accepted' => $data['privacy_policy_accepted'] ?? false,
                'information_certified' => $data['information_certified'] ?? false,
                'prc_verification_consent' => $data['prc_verification_consent'] ?? false,
                'submitted_at' => now(),
            ]);
        }

        Log::info('User registered', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'ip_address' => $request->ip(),
            'device_fingerprint' => $deviceFingerprint,
        ]);

        return $user;
    }

    /**
     * Change user password with security checks
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        // Verify current password
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        // Check if new password is different from current
        if (Hash::check($newPassword, $user->password)) {
            return false;
        }

        // Update password
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Revoke all tokens for security
        $user->tokens()->delete();

        Log::info('Password changed', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return true;
    }

    /**
     * Check for suspicious activity
     */
    private function checkForSuspiciousActivity(string $email, Request $request): void
    {
        $recentAttempts = LoginAttempt::forEmail($email)
            ->recent(60) // Last hour
            ->count();

        if ($recentAttempts > self::SUSPICIOUS_ACTIVITY_THRESHOLD) {
            Log::alert('Suspicious login activity detected', [
                'email' => $email,
                'ip_address' => $request->ip(),
                'attempts_in_hour' => $recentAttempts,
            ]);

            // Could implement additional security measures here
            // such as sending alerts to admins, requiring additional verification, etc.
        }
    }

    /**
     * Check and apply blocking if necessary
     */
    private function checkAndApplyBlocking(string $email, Request $request): void
    {
        $failedAttemptsForEmail = LoginAttempt::getFailedAttemptsForEmail($email, 15);
        $failedAttemptsForIp = LoginAttempt::getFailedAttemptsForIp($request->ip(), 15);

        // Block email if too many failed attempts
        if ($failedAttemptsForEmail >= self::MAX_LOGIN_ATTEMPTS) {
            LoginAttempt::blockEmail($email, 15); // Block for 15 minutes instead of 1 hour
        }

        // Block IP if too many failed attempts
        if ($failedAttemptsForIp >= self::MAX_LOGIN_ATTEMPTS) {
            LoginAttempt::blockIp($request->ip(), 15); // Block for 15 minutes instead of 1 hour
        }
    }

    /**
     * Ensure request is not rate limited
     */
    private function ensureIsNotRateLimited(string $email, Request $request): void
    {
        $key = $this->throttleKey($email, $request);

        if (RateLimiter::tooManyAttempts($key, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }
    }

    /**
     * Generate throttle key
     */
    private function throttleKey(string $email, Request $request): string
    {
        return Str::lower($email) . '|' . $request->ip();
    }

    /**
     * Generate device fingerprint
     */
    private function generateDeviceFingerprint(Request $request): string
    {
        $data = [
            $request->userAgent(),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
        ];

        return hash('sha256', implode('|', array_filter($data)));
    }

    /**
     * Get location data from request
     */
    private function getLocationData(Request $request): array
    {
        // Simplified location data - in production, you might use a geolocation service
        return [
            'ip_address' => $request->ip(),
            'country' => null, // Could be determined by IP geolocation service
            'city' => null,
            'timezone' => null,
        ];
    }

    /**
     * Get redirect URL based on user role and status
     */
    private function getRedirectUrl(User $user): string
    {
        return match($user->role) {
            'admin' => route('admin.dashboard'),
            'broker' => match($user->application_status) {
                'rejected' => route('broker.rejected'),
                'approved' => route('broker.dashboard'),
                default => route('broker.pending-approval')
            },
            'client' => route('client.dashboard'),
            default => route('dashboard')
        };
    }

    /**
     * Logout user and clean up session
     */
    public function logout(Request $request): void
    {
        $user = Auth::user();
        
        if ($user) {
            Log::info('User logout', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Get user's suspicious activity report
     */
    public function getSuspiciousActivityReport(User $user): array
    {
        return LoginAttempt::getSuspiciousActivity($user->id);
    }

    /**
     * Check if user should be required to verify identity
     */
    public function requiresIdentityVerification(User $user): bool
    {
        $activity = $this->getSuspiciousActivityReport($user);
        return $activity['suspicious_score'] > 15;
    }
}




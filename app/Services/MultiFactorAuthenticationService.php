<?php

namespace App\Services;

use App\Models\User;
use App\Models\MfaToken;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MultiFactorAuthenticationService
{
    /**
     * Enable MFA for user
     */
    public function enableMfa(User $user, string $method = 'email'): array
    {
        // Create backup codes
        $backupCodes = $this->generateBackupCodes();
        
        // Store backup codes as MFA tokens
        foreach ($backupCodes as $code) {
            MfaToken::create([
                'user_id' => $user->id,
                'type' => 'backup',
                'token' => $code,
                'expires_at' => now()->addYears(1), // Backup codes don't expire for a year
                'metadata' => ['is_backup' => true],
            ]);
        }

        Log::info('MFA enabled for user', [
            'user_id' => $user->id,
            'method' => $method,
        ]);

        return [
            'success' => true,
            'backup_codes' => $backupCodes,
            'message' => 'Multi-factor authentication has been enabled successfully.',
        ];
    }

    /**
     * Disable MFA for user
     */
    public function disableMfa(User $user): array
    {
        // Remove all MFA tokens for user
        MfaToken::where('user_id', $user->id)->delete();

        Log::info('MFA disabled for user', [
            'user_id' => $user->id,
        ]);

        return [
            'success' => true,
            'message' => 'Multi-factor authentication has been disabled.',
        ];
    }

    /**
     * Send MFA token via email
     */
    public function sendEmailToken(User $user): array
    {
        $token = MfaToken::createToken($user->id, 'email', 10);

        try {
            // In a real implementation, you would send an email here
            // Mail::to($user->email)->send(new MfaTokenMail($token->token));
            
            Log::info('MFA email token sent', [
                'user_id' => $user->id,
                'token_id' => $token->id,
            ]);

            return [
                'success' => true,
                'message' => 'Verification code sent to your email address.',
                'expires_in' => 10, // minutes
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send MFA email token', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send verification code. Please try again.',
            ];
        }
    }

    /**
     * Send MFA token via SMS
     */
    public function sendSmsToken(User $user): array
    {
        $token = MfaToken::createToken($user->id, 'sms', 10);

        try {
            // In a real implementation, you would send an SMS here
            // SMS::to($user->phone)->send("Your verification code is: {$token->token}");
            
            Log::info('MFA SMS token sent', [
                'user_id' => $user->id,
                'token_id' => $token->id,
            ]);

            return [
                'success' => true,
                'message' => 'Verification code sent to your phone.',
                'expires_in' => 10, // minutes
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send MFA SMS token', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send verification code. Please try again.',
            ];
        }
    }

    /**
     * Verify MFA token
     */
    public function verifyToken(User $user, string $token, string $type = 'email'): array
    {
        $mfaToken = MfaToken::verifyToken($user->id, $type, $token);

        if ($mfaToken) {
            Log::info('MFA token verified successfully', [
                'user_id' => $user->id,
                'token_type' => $type,
                'token_id' => $mfaToken->id,
            ]);

            return [
                'success' => true,
                'message' => 'Verification successful.',
                'token' => $mfaToken,
            ];
        }

        Log::warning('MFA token verification failed', [
            'user_id' => $user->id,
            'token_type' => $type,
            'provided_token' => $token,
        ]);

        return [
            'success' => false,
            'message' => 'Invalid or expired verification code.',
        ];
    }

    /**
     * Verify backup code
     */
    public function verifyBackupCode(User $user, string $code): array
    {
        $mfaToken = MfaToken::verifyToken($user->id, 'backup', $code);

        if ($mfaToken) {
            Log::info('MFA backup code used', [
                'user_id' => $user->id,
                'token_id' => $mfaToken->id,
            ]);

            return [
                'success' => true,
                'message' => 'Backup code verified successfully.',
                'token' => $mfaToken,
            ];
        }

        Log::warning('MFA backup code verification failed', [
            'user_id' => $user->id,
            'provided_code' => $code,
        ]);

        return [
            'success' => false,
            'message' => 'Invalid backup code.',
        ];
    }

    /**
     * Check if user has MFA enabled
     */
    public function hasMfaEnabled(User $user): bool
    {
        return MfaToken::where('user_id', $user->id)
            ->where('type', 'backup')
            ->whereNull('used_at')
            ->exists();
    }

    /**
     * Get user's backup codes
     */
    public function getBackupCodes(User $user): array
    {
        return MfaToken::where('user_id', $user->id)
            ->where('type', 'backup')
            ->whereNull('used_at')
            ->pluck('token')
            ->toArray();
    }

    /**
     * Regenerate backup codes
     */
    public function regenerateBackupCodes(User $user): array
    {
        // Remove existing backup codes
        MfaToken::where('user_id', $user->id)
            ->where('type', 'backup')
            ->delete();

        // Generate new backup codes
        $backupCodes = $this->generateBackupCodes();
        
        foreach ($backupCodes as $code) {
            MfaToken::create([
                'user_id' => $user->id,
                'type' => 'backup',
                'token' => $code,
                'expires_at' => now()->addYears(1),
                'metadata' => ['is_backup' => true],
            ]);
        }

        Log::info('MFA backup codes regenerated', [
            'user_id' => $user->id,
        ]);

        return [
            'success' => true,
            'backup_codes' => $backupCodes,
            'message' => 'Backup codes have been regenerated successfully.',
        ];
    }

    /**
     * Check if MFA is required for user
     */
    public function isMfaRequired(User $user): bool
    {
        // Require MFA for admin and broker accounts
        return in_array($user->role, ['admin', 'broker']);
    }

    /**
     * Generate backup codes
     */
    private function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4))); // 8-character codes
        }
        
        return $codes;
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        $deletedCount = MfaToken::where('expires_at', '<', now())
            ->whereNull('used_at')
            ->delete();

        Log::info('Cleaned up expired MFA tokens', [
            'deleted_count' => $deletedCount,
        ]);

        return $deletedCount;
    }

    /**
     * Get MFA statistics for user
     */
    public function getMfaStats(User $user): array
    {
        $totalTokens = MfaToken::where('user_id', $user->id)->count();
        $activeTokens = MfaToken::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->count();
        $backupCodes = $this->getBackupCodes($user);

        return [
            'total_tokens' => $totalTokens,
            'active_tokens' => $activeTokens,
            'backup_codes_count' => count($backupCodes),
            'mfa_enabled' => $this->hasMfaEnabled($user),
            'mfa_required' => $this->isMfaRequired($user),
        ];
    }
}




<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'success',
        'failed_reason',
        'attempted_at',
        'blocked_until',
        'device_fingerprint',
        'location_data',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'attempted_at' => 'datetime',
            'blocked_until' => 'datetime',
            'location_data' => 'array',
        ];
    }

    /**
     * Get the user that owns the login attempt
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for failed attempts
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope for successful attempts
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope for attempts from specific IP
     */
    public function scopeFromIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Scope for attempts with specific email
     */
    public function scopeForEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Scope for recent attempts
     */
    public function scopeRecent($query, int $minutes = 60)
    {
        return $query->where('attempted_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Check if IP is currently blocked
     */
    public static function isIpBlocked(string $ip): bool
    {
        return self::fromIp($ip)
            ->where('blocked_until', '>', now())
            ->exists();
    }

    /**
     * Check if email is currently blocked
     */
    public static function isEmailBlocked(string $email): bool
    {
        return self::forEmail($email)
            ->where('blocked_until', '>', now())
            ->exists();
    }

    /**
     * Get failed attempt count for IP in time window
     */
    public static function getFailedAttemptsForIp(string $ip, int $minutes = 15): int
    {
        return self::fromIp($ip)
            ->failed()
            ->recent($minutes)
            ->count();
    }

    /**
     * Get failed attempt count for email in time window
     */
    public static function getFailedAttemptsForEmail(string $email, int $minutes = 15): int
    {
        return self::forEmail($email)
            ->failed()
            ->recent($minutes)
            ->count();
    }

    /**
     * Record a login attempt
     */
    public static function recordAttempt(array $data): self
    {
        return self::create([
            'user_id' => $data['user_id'] ?? null,
            'email' => $data['email'],
            'ip_address' => $data['ip_address'],
            'user_agent' => $data['user_agent'],
            'success' => $data['success'],
            'failed_reason' => $data['failed_reason'] ?? null,
            'attempted_at' => now(),
            'device_fingerprint' => $data['device_fingerprint'] ?? null,
            'location_data' => $data['location_data'] ?? null,
        ]);
    }

    /**
     * Block IP for specified duration
     */
    public static function blockIp(string $ip, int $minutes = 60): void
    {
        self::fromIp($ip)->update([
            'blocked_until' => now()->addMinutes($minutes)
        ]);
    }

    /**
     * Block email for specified duration
     */
    public static function blockEmail(string $email, int $minutes = 60): void
    {
        self::forEmail($email)->update([
            'blocked_until' => now()->addMinutes($minutes)
        ]);
    }

    /**
     * Get suspicious activity for user
     */
    public static function getSuspiciousActivity(int $userId, int $days = 30): array
    {
        $attempts = self::where('user_id', $userId)
            ->where('attempted_at', '>=', now()->subDays($days))
            ->orderBy('attempted_at', 'desc')
            ->get();

        $uniqueIps = $attempts->pluck('ip_address')->unique()->count();
        $uniqueUserAgents = $attempts->pluck('user_agent')->unique()->count();
        $failedAttempts = $attempts->where('success', false)->count();

        return [
            'total_attempts' => $attempts->count(),
            'failed_attempts' => $failedAttempts,
            'unique_ips' => $uniqueIps,
            'unique_user_agents' => $uniqueUserAgents,
            'suspicious_score' => ($uniqueIps * 2) + ($uniqueUserAgents * 1.5) + ($failedAttempts * 1),
        ];
    }
}




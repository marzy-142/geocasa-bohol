<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MfaToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'token',
        'expires_at',
        'used_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user that owns the MFA token
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for valid (not expired, not used) tokens
     */
    public function scopeValid($query)
    {
        return $query->whereNull('used_at')
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope for tokens by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if token is valid
     */
    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at > now();
    }

    /**
     * Mark token as used
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    /**
     * Generate a secure random token
     */
    public static function generateToken(int $length = 6): string
    {
        return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new MFA token
     */
    public static function createToken(int $userId, string $type, int $expiryMinutes = 10, array $metadata = []): self
    {
        // Invalidate any existing tokens of the same type for this user
        self::where('user_id', $userId)
            ->where('type', $type)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'token' => self::generateToken(),
            'expires_at' => now()->addMinutes($expiryMinutes),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Verify a token
     */
    public static function verifyToken(int $userId, string $type, string $token): ?self
    {
        $mfaToken = self::where('user_id', $userId)
            ->where('type', $type)
            ->where('token', $token)
            ->valid()
            ->first();

        if ($mfaToken) {
            $mfaToken->markAsUsed();
            return $mfaToken;
        }

        return null;
    }

    /**
     * Get active tokens for user
     */
    public static function getActiveTokens(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('user_id', $userId)
            ->valid()
            ->orderBy('expires_at', 'asc')
            ->get();
    }
}




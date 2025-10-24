<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientTransactionEngagement extends Model
{
    use HasFactory;

    protected $table = 'client_transaction_engagement';

    protected $fillable = [
        'transaction_id',
        'client_id',
        'engagement_level',
        'response_time_minutes',
        'login_count',
        'last_active',
        'last_message_sent',
        'last_document_uploaded',
        'preferences',
        'interaction_history',
        'total_interactions',
        'satisfaction_score',
    ];

    protected $casts = [
        'last_active' => 'datetime',
        'last_message_sent' => 'datetime',
        'last_document_uploaded' => 'datetime',
        'preferences' => 'array',
        'interaction_history' => 'array',
        'satisfaction_score' => 'decimal:2',
    ];

    // Relationships
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // Scopes
    public function scopeHighEngagement($query)
    {
        return $query->whereIn('engagement_level', ['high', 'very_high']);
    }

    public function scopeLowEngagement($query)
    {
        return $query->where('engagement_level', 'low');
    }

    public function scopeActive($query)
    {
        return $query->where('last_active', '>=', now()->subDays(7));
    }

    // Methods
    public function updateEngagementLevel(): void
    {
        $score = $this->calculateEngagementScore();
        
        $level = match (true) {
            $score >= 80 => 'very_high',
            $score >= 60 => 'high',
            $score >= 40 => 'medium',
            default => 'low'
        };

        $this->update(['engagement_level' => $level]);
    }

    public function calculateEngagementScore(): int
    {
        $score = 0;
        
        // Login frequency (30 points max)
        $score += min(30, $this->login_count * 2);
        
        // Response time (25 points max)
        if ($this->response_time_minutes) {
            $score += max(0, 25 - ($this->response_time_minutes / 60)); // Lower is better
        }
        
        // Recent activity (25 points max)
        if ($this->last_active && $this->last_active->diffInHours(now()) < 24) {
            $score += 25;
        } elseif ($this->last_active && $this->last_active->diffInHours(now()) < 72) {
            $score += 15;
        }
        
        // Message activity (20 points max)
        if ($this->last_message_sent && $this->last_message_sent->diffInHours(now()) < 48) {
            $score += 20;
        }

        return min(100, $score);
    }

    public function recordInteraction(string $type, array $data = []): void
    {
        $interactions = $this->interaction_history ?? [];
        $interactions[] = [
            'type' => $type,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];

        $this->update([
            'interaction_history' => $interactions,
            'total_interactions' => $this->total_interactions + 1,
            'last_active' => now(),
        ]);

        $this->updateEngagementLevel();
    }

    public function recordLogin(): void
    {
        $this->update([
            'login_count' => $this->login_count + 1,
            'last_active' => now(),
        ]);
        
        $this->updateEngagementLevel();
    }

    public function recordMessageSent(): void
    {
        $this->update(['last_message_sent' => now()]);
        $this->recordInteraction('message_sent');
    }

    public function recordDocumentUpload(): void
    {
        $this->update(['last_document_uploaded' => now()]);
        $this->recordInteraction('document_uploaded');
    }

    public function updateResponseTime(int $minutes): void
    {
        $currentAvg = $this->response_time_minutes ?? $minutes;
        $newAvg = ($currentAvg + $minutes) / 2;
        
        $this->update(['response_time_minutes' => round($newAvg, 2)]);
        $this->updateEngagementLevel();
    }

    public function setSatisfactionScore(float $score): void
    {
        $this->update(['satisfaction_score' => $score]);
    }

    // Accessors
    public function getEngagementLevelColorAttribute(): string
    {
        return match ($this->engagement_level) {
            'very_high' => 'green',
            'high' => 'blue',
            'medium' => 'yellow',
            'low' => 'red',
            default => 'gray'
        };
    }

    public function getResponseTimeFormattedAttribute(): string
    {
        if (!$this->response_time_minutes) {
            return 'No data';
        }

        if ($this->response_time_minutes < 60) {
            return $this->response_time_minutes . ' minutes';
        }

        $hours = floor($this->response_time_minutes / 60);
        $minutes = $this->response_time_minutes % 60;
        
        return $hours . 'h ' . $minutes . 'm';
    }

    public function getLastActiveFormattedAttribute(): string
    {
        return $this->last_active ? $this->last_active->diffForHumans() : 'Never';
    }
}
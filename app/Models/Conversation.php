<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'type',
        'inquiry_id',
        'transaction_id',
        'participants',
        'last_message_at',
        'is_archived',
        'metadata',
        'lifecycle_stage',
        'transitioned_at',
    ];

    protected $casts = [
        'participants' => 'array',
        'metadata' => 'array',
        'last_message_at' => 'datetime',
        'transitioned_at' => 'datetime',
        'is_archived' => 'boolean',
    ];

    /**
     * Get the inquiry associated with this conversation
     */
    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    /**
     * Get the transaction associated with this conversation
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get all messages in this conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    /**
     * Get the latest message in this conversation
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    /**
     * Get participants as User models
     */
    public function participantUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants', 'conversation_id', 'user_id');
    }

    /**
     * Alias for participantUsers for backward compatibility
     */
    public function participants(): BelongsToMany
    {
        return $this->participantUsers();
    }

    /**
     * Get participants from JSON field as User models (for backward compatibility)
     */
    public function getParticipantUsersFromJson()
    {
        return User::whereIn('id', $this->participants ?? [])->get();
    }

    /**
     * Check if a user is a participant in this conversation
     */
    public function hasParticipant(int $userId): bool
    {
        // Check JSON participants field first
        if (in_array($userId, $this->participants ?? [])) {
            return true;
        }

        // Fallback to pivot table for robustness
        return $this->participantUsers()->where('user_id', $userId)->exists();
    }

    /**
     * Add a participant to the conversation
     */
    public function addParticipant(int $userId): void
    {
        $participants = $this->participants ?? [];
        if (!in_array($userId, $participants)) {
            $participants[] = $userId;
            $this->update(['participants' => $participants]);
            
            // Also add to pivot table
            $this->participantUsers()->syncWithoutDetaching([$userId]);
        }
    }

    /**
     * Remove a participant from the conversation
     */
    public function removeParticipant(int $userId): void
    {
        $participants = $this->participants ?? [];
        $participants = array_values(array_filter($participants, fn($id) => $id !== $userId));
        $this->update(['participants' => $participants]);
        
        // Also remove from pivot table
        $this->participantUsers()->detach($userId);
    }

    /**
     * Get unread messages count for a specific user
     */
    public function getUnreadCountForUser(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Check if conversation has unread messages for a specific user
     */
    public function hasUnreadMessagesForUser(int $userId): bool
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->exists();
    }

    /**
     * Mark all messages as read for a specific user
     */
    public function markAsReadForUser(int $userId): void
    {
        $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Create a conversation for an inquiry
     */
    public static function createForInquiry(Inquiry $inquiry): self
    {
        // Only add valid user IDs as participants
        $userParticipants = [];

        // Add broker (always a user)
        if ($inquiry->property && $inquiry->property->broker_id) {
            $userParticipants[] = $inquiry->property->broker_id;
        }

        // Add client user if available
        if ($inquiry->client && $inquiry->client->user_id) {
            $userParticipants[] = $inquiry->client->user_id;
        }

        // Add direct user_id if set (for logged-in inquiries)
        if ($inquiry->user_id) {
            $userParticipants[] = $inquiry->user_id;
        }

        // Remove duplicates/nulls
        $userParticipants = array_values(array_unique(array_filter($userParticipants)));

        $conversation = self::create([
            'title' => "Inquiry: {$inquiry->property->title}",
            'type' => 'inquiry',
            'inquiry_id' => $inquiry->id,
            'participants' => $userParticipants,
        ]);

        // Sync only valid user IDs to pivot table
        if (!empty($userParticipants)) {
            $conversation->participantUsers()->attach($userParticipants);
        }

        return $conversation;
    }

    /**
     * Create a conversation for a transaction
     */
    public static function createForTransaction(Transaction $transaction): self
    {
        $participants = [$transaction->client_id, $transaction->broker_id];
        $participants = array_filter($participants); // Remove null values

        $conversation = self::create([
            'title' => "Transaction: {$transaction->property->title}",
            'type' => 'transaction',
            'transaction_id' => $transaction->id,
            'participants' => array_values($participants),
        ]);

        // Sync participants to pivot table
        if (!empty($participants)) {
            $conversation->participantUsers()->attach($participants);
        }

        return $conversation;
    }

    /**
     * Scope to get conversations for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            // Check both JSON participants field and pivot table
            $q->whereJsonContains('participants', $userId)
              ->orWhereHas('participantUsers', function ($subQuery) use ($userId) {
                  $subQuery->where('user_id', $userId);
              });
        });
    }

    /**
     * Scope to get active (non-archived) conversations
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Get the total message count for this conversation
     */
    public function getMessageCount(): int
    {
        return $this->messages()->count();
    }

    /**
     * Transition conversation from inquiry to transaction
     */
    public function transitionToTransaction(Transaction $transaction): void
    {
        $this->update([
            'type' => 'transaction',
            'transaction_id' => $transaction->id,
            'title' => "Transaction: {$transaction->property->title}",
            'lifecycle_stage' => 'transaction',
            'transitioned_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], [
                'transitioned_at' => now()->toIso8601String(),
                'original_inquiry_id' => $this->inquiry_id,
                'transaction_number' => $transaction->transaction_number,
                'transaction_status' => $transaction->status,
            ]),
        ]);

        // Add system message
        Message::createSystemMessage(
            $this->id,
            "🎉 Inquiry accepted! Transaction #{$transaction->transaction_number} has been created.",
            [
                'action' => 'inquiry_accepted',
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
            ]
        );
    }

    /**
     * Update lifecycle stage
     */
    public function updateLifecycleStage(string $stage): void
    {
        $this->update([
            'lifecycle_stage' => $stage,
            'metadata' => array_merge($this->metadata ?? [], [
                'last_stage_update' => now()->toIso8601String(),
                'previous_stage' => $this->lifecycle_stage,
            ]),
        ]);
    }

    /**
     * Add system message to conversation
     */
    public function addSystemMessage(string $content, array $metadata = []): Message
    {
        return Message::createSystemMessage($this->id, $content, $metadata);
    }

    /**
     * Update conversation metadata
     */
    public function updateMetadata(array $data): void
    {
        $this->update([
            'metadata' => array_merge($this->metadata ?? [], $data),
        ]);
    }

    /**
     * Check if conversation is in inquiry stage
     */
    public function isInquiryStage(): bool
    {
        return $this->lifecycle_stage === 'inquiry';
    }

    /**
     * Check if conversation is in transaction stage
     */
    public function isTransactionStage(): bool
    {
        return $this->lifecycle_stage === 'transaction';
    }

    /**
     * Get the current context (inquiry or transaction)
     */
    public function getCurrentContext()
    {
        if ($this->isTransactionStage() && $this->transaction) {
            return $this->transaction;
        }
        
        return $this->inquiry;
    }
}
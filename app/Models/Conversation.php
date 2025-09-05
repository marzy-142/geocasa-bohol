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
    ];

    protected $casts = [
        'participants' => 'array',
        'last_message_at' => 'datetime',
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
        return in_array($userId, $this->participants ?? []);
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
        $participants = [$inquiry->client_id ?? null, $inquiry->property->broker_id ?? null];
        $participants = array_filter($participants); // Remove null values

        return self::create([
            'title' => "Inquiry: {$inquiry->property->title}",
            'type' => 'inquiry',
            'inquiry_id' => $inquiry->id,
            'participants' => array_values($participants),
        ]);
    }

    /**
     * Create a conversation for a transaction
     */
    public static function createForTransaction(Transaction $transaction): self
    {
        $participants = [$transaction->client_id, $transaction->broker_id];
        $participants = array_filter($participants); // Remove null values

        return self::create([
            'title' => "Transaction: {$transaction->property->title}",
            'type' => 'transaction',
            'transaction_id' => $transaction->id,
            'participants' => array_values($participants),
        ]);
    }

    /**
     * Scope to get conversations for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->whereJsonContains('participants', $userId);
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
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'type',
        'attachments',
        'metadata',
        'read_at',
        'is_edited',
        'edited_at',
    ];

    // Message types
    const TYPE_TEXT = 'text';
    const TYPE_SYSTEM = 'system';
    const TYPE_INQUIRY_INITIAL = 'inquiry_initial';
    const TYPE_STATUS_CHANGE = 'status_change';
    const TYPE_DOCUMENT = 'document';
    const TYPE_MEETING_SCHEDULED = 'meeting_scheduled';

    protected $casts = [
        'attachments' => 'array',
        'metadata' => 'array',
        'read_at' => 'datetime',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    /**
     * Get the conversation this message belongs to
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user who sent this message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Check if the message has been read
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Mark the message as read
     */
    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Mark the message as unread
     */
    public function markAsUnread(): void
    {
        if ($this->isRead()) {
            $this->update(['read_at' => null]);
        }
    }

    /**
     * Check if the message has attachments
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * Get attachment URLs
     */
    public function getAttachmentUrls(): array
    {
        if (!$this->hasAttachments()) {
            return [];
        }

        return array_map(function ($attachment) {
            return [
                'name' => $attachment['name'] ?? 'Unknown',
                'url' => Storage::url($attachment['path']),
                'size' => $attachment['size'] ?? 0,
                'type' => $attachment['type'] ?? 'unknown',
            ];
        }, $this->attachments);
    }

    /**
     * Create a system message
     */
    public static function createSystemMessage(
        int $conversationId,
        string $content,
        array $metadata = []
    ): self {
        return self::create([
            'conversation_id' => $conversationId,
            'sender_id' => null, // System messages don't have a sender
            'content' => $content,
            'type' => 'system',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Edit the message content
     */
    public function editContent(string $newContent): void
    {
        $this->update([
            'content' => $newContent,
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }

    /**
     * Scope to get unread messages
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope to get read messages
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope to get messages by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get messages by type (alias for ofType)
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get messages from a specific sender
     */
    public function scopeFromSender($query, int $senderId)
    {
        return $query->where('sender_id', $senderId);
    }

    /**
     * Check if message is a system message
     */
    public function isSystemMessage(): bool
    {
        return $this->type === self::TYPE_SYSTEM;
    }

    /**
     * Check if message is a regular text message
     */
    public function isTextMessage(): bool
    {
        return $this->type === self::TYPE_TEXT;
    }

    /**
     * Get human-readable type label
     */
    public function getTypeLabel(): string
    {
        $labels = [
            self::TYPE_TEXT => 'Message',
            self::TYPE_SYSTEM => 'System',
            self::TYPE_INQUIRY_INITIAL => 'Initial Inquiry',
            self::TYPE_STATUS_CHANGE => 'Status Update',
            self::TYPE_DOCUMENT => 'Document',
            self::TYPE_MEETING_SCHEDULED => 'Meeting Scheduled',
        ];

        return $labels[$this->type] ?? 'Unknown';
    }

    /**
     * Boot method to update conversation's last_message_at
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($message) {
            $message->conversation->update([
                'last_message_at' => $message->created_at,
            ]);
        });
    }
}
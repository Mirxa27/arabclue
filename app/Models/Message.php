<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * Message Model - Conversation Message Management
 * 
 * Manages individual messages within conversations
 * 
 * @property int $id
 * @property int $conversation_id
 * @property int $sender_id
 * @property int $receiver_id
 * @property string $message
 * @property array|null $attachments
 * @property \Carbon\Carbon|null $read_at
 * @property bool $is_system_message
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Message extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_TEXT = 'text';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'receiver_id',
        'message',
        'attachments',
        'read_at',
        'is_system_message',
        'metadata'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'attachments' => 'array',
        'read_at' => 'datetime',
        'is_system_message' => 'boolean',
        'metadata' => 'array'
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'is_system_message' => false,
        'attachments' => '[]',
        'metadata' => '{}'
    ];

    /**
     * Model events
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($message) {
            // Update conversation's last message timestamp
            $message->conversation->updateLastMessage();
            
            // Clear message caches
            cache()->forget("conversation_messages_{$message->conversation_id}");
        });

        static::updated(function ($message) {
            if ($message->wasChanged('read_at')) {
                // Clear unread count caches
                cache()->forget("unread_messages_{$message->receiver_id}");
            }
        });
    }

    /**
     * Conversation relationship
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Sender relationship
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Receiver relationship
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read messages
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for system messages
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system_message', true);
    }

    /**
     * Scope for user messages
     */
    public function scopeUser($query)
    {
        return $query->where('is_system_message', false);
    }

    /**
     * Scope for messages by sender
     */
    public function scopeBySender($query, int $senderId)
    {
        return $query->where('sender_id', $senderId);
    }

    /**
     * Scope for messages by receiver
     */
    public function scopeByReceiver($query, int $receiverId)
    {
        return $query->where('receiver_id', $receiverId);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if message is read
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Check if message has attachments
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * Get formatted message content
     */
    public function getFormattedContentAttribute(): string
    {
        if ($this->is_system_message) {
            return "<em>{$this->message}</em>";
        }
        
        return nl2br(e($this->message));
    }

    /**
     * Get message excerpt
     */
    public function getExcerptAttribute(): string
    {
        return \Str::limit($this->message, 100);
    }

    /**
     * Create system message
     */
    public static function createSystemMessage(
        int $conversationId,
        string $message,
        array $metadata = []
    ): self {
        return static::create([
            'conversation_id' => $conversationId,
            'sender_id' => 1, // System user ID
            'receiver_id' => 1, // System user ID
            'message' => $message,
            'is_system_message' => true,
            'metadata' => $metadata,
            'read_at' => now()
        ]);
    }

    /**
     * Get unread count for user
     */
    public static function getUnreadCountForUser(int $userId): int
    {
        return cache()->remember("unread_messages_{$userId}", 300, function () use ($userId) {
            return static::where('receiver_id', $userId)
                ->whereNull('read_at')
                ->count();
        });
    }

    /**
     * Mark all messages as read for user
     */
    public static function markAllAsReadForUser(int $userId): int
    {
        $count = static::where('receiver_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        cache()->forget("unread_messages_{$userId}");
        
        return $count;
    }

    /**
     * Search messages
     */
    public static function search(string $query, int $userId, int $limit = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        return static::where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->where('message', 'LIKE', "%{$query}%")
            ->where('is_system_message', false)
            ->with(['conversation', 'sender', 'receiver'])
            ->orderByDesc('created_at')
            ->paginate($limit);
    }
}

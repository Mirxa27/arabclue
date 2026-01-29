<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Conversation Model - Host-Guest Communication
 * 
 * Manages conversations between hosts and guests for property inquiries
 * and booking-related communication
 * 
 * @property int $id
 * @property int $guest_id
 * @property int $host_id
 * @property int|null $property_id
 * @property int|null $booking_id
 * @property string $subject
 * @property string $type
 * @property string $status
 * @property \Carbon\Carbon|null $last_message_at
 * @property int|null $last_message_by
 * @property bool $guest_read
 * @property bool $host_read
 * @property bool $guest_archived
 * @property bool $host_archived
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Conversation types
     */
    const TYPE_INQUIRY = 'inquiry';
    const TYPE_BOOKING = 'booking';
    const TYPE_SUPPORT = 'support';
    const TYPE_GENERAL = 'general';

    /**
     * Conversation status
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';
    const STATUS_ARCHIVED = 'archived';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'guest_id',
        'host_id',
        'property_id',
        'booking_id',
        'subject',
        'type',
        'status',
        'last_message_at',
        'last_message_by',
        'guest_read',
        'host_read',
        'guest_archived',
        'host_archived',
        'metadata'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'last_message_at' => 'datetime',
        'guest_read' => 'boolean',
        'host_read' => 'boolean',
        'guest_archived' => 'boolean',
        'host_archived' => 'boolean',
        'metadata' => 'array'
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'type' => self::TYPE_GENERAL,
        'status' => self::STATUS_ACTIVE,
        'guest_read' => true,
        'host_read' => false,
        'guest_archived' => false,
        'host_archived' => false,
        'metadata' => '{}'
    ];

    /**
     * Model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($conversation) {
            $conversation->last_message_at = now();
        });

        static::saved(function ($conversation) {
            // Clear conversation caches
            cache()->forget("user_conversations_{$conversation->guest_id}");
            cache()->forget("user_conversations_{$conversation->host_id}");
        });
    }

    /**
     * Guest user relationship
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    /**
     * Host user relationship
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * Property relationship
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Booking relationship
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Messages in conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Latest message
     */
    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    /**
     * Get last message content
     */
    public function getLastMessageContentAttribute(): string
    {
        $lastMessage = $this->messages()->latest()->first();
        return $lastMessage ? $lastMessage->content : '';
    }

    /**
     * Get unread messages count for user
     */
    public function getUnreadCountForUser(int $userId): int
    {
        return $this->messages()
                   ->where('sender_id', '!=', $userId)
                   ->where('read_at', null)
                   ->count();
    }

    /**
     * Mark conversation as read for user
     */
    public function markAsReadForUser(int $userId): void
    {
        if ($userId === $this->guest_id) {
            $this->update(['guest_read' => true]);
        } elseif ($userId === $this->host_id) {
            $this->update(['host_read' => true]);
        }

        // Mark all unread messages as read
        $this->messages()
             ->where('sender_id', '!=', $userId)
             ->whereNull('read_at')
             ->update(['read_at' => now()]);
    }

    /**
     * Archive conversation for user
     */
    public function archiveForUser(int $userId): void
    {
        if ($userId === $this->guest_id) {
            $this->update(['guest_archived' => true]);
        } elseif ($userId === $this->host_id) {
            $this->update(['host_archived' => true]);
        }
    }

    /**
     * Unarchive conversation for user
     */
    public function unarchiveForUser(int $userId): void
    {
        if ($userId === $this->guest_id) {
            $this->update(['guest_archived' => false]);
        } elseif ($userId === $this->host_id) {
            $this->update(['host_archived' => false]);
        }
    }

    /**
     * Close conversation
     */
    public function close(): void
    {
        $this->update(['status' => self::STATUS_CLOSED]);
    }

    /**
     * Reopen conversation
     */
    public function reopen(): void
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Check if user is participant
     */
    public function hasParticipant(int $userId): bool
    {
        return $this->guest_id === $userId || $this->host_id === $userId;
    }

    /**
     * Get other participant
     */
    public function getOtherParticipant(int $userId): ?User
    {
        if ($this->guest_id === $userId) {
            return $this->host;
        } elseif ($this->host_id === $userId) {
            return $this->guest;
        }
        return null;
    }

    /**
     * Update last message info
     */
    public function updateLastMessage(Message $message): void
    {
        $this->update([
            'last_message_at' => $message->created_at,
            'last_message_by' => $message->sender_id,
            'guest_read' => $message->sender_id === $this->guest_id,
            'host_read' => $message->sender_id === $this->host_id
        ]);
    }

    /**
     * Scope for active conversations
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for conversations involving user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('guest_id', $userId)
              ->orWhere('host_id', $userId);
        });
    }

    /**
     * Scope for unarchived conversations for user
     */
    public function scopeNotArchivedForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where(function ($subQ) use ($userId) {
                $subQ->where('guest_id', $userId)
                     ->where('guest_archived', false);
            })->orWhere(function ($subQ) use ($userId) {
                $subQ->where('host_id', $userId)
                     ->where('host_archived', false);
            });
        });
    }

    /**
     * Scope for conversations with unread messages for user
     */
    public function scopeWithUnreadForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where(function ($subQ) use ($userId) {
                $subQ->where('guest_id', $userId)
                     ->where('guest_read', false);
            })->orWhere(function ($subQ) use ($userId) {
                $subQ->where('host_id', $userId)
                     ->where('host_read', false);
            });
        });
    }

    /**
     * Create conversation between users
     */
    public static function createBetweenUsers(
        int $guestId, 
        int $hostId, 
        array $options = []
    ): self {
        return static::create([
            'guest_id' => $guestId,
            'host_id' => $hostId,
            'property_id' => $options['property_id'] ?? null,
            'booking_id' => $options['booking_id'] ?? null,
            'subject' => $options['subject'] ?? 'Property Inquiry',
            'type' => $options['type'] ?? self::TYPE_INQUIRY,
            'metadata' => $options['metadata'] ?? []
        ]);
    }

    /**
     * Find or create conversation between users for property
     */
    public static function findOrCreateForProperty(
        int $guestId, 
        int $hostId, 
        int $propertyId
    ): self {
        $conversation = static::where('guest_id', $guestId)
                             ->where('host_id', $hostId)
                             ->where('property_id', $propertyId)
                             ->where('status', self::STATUS_ACTIVE)
                             ->first();

        if (!$conversation) {
            $property = Property::findOrFail($propertyId);
            $conversation = static::createBetweenUsers($guestId, $hostId, [
                'property_id' => $propertyId,
                'subject' => "Inquiry about {$property->title}",
                'type' => self::TYPE_INQUIRY
            ]);
        }

        return $conversation;
    }

    /**
     * Get conversation statistics for user
     */
    public static function getStatsForUser(int $userId): array
    {
        $conversations = static::forUser($userId)->get();
        
        return [
            'total' => $conversations->count(),
            'active' => $conversations->where('status', self::STATUS_ACTIVE)->count(),
            'unread' => $conversations->filter(function ($conv) use ($userId) {
                return $conv->getUnreadCountForUser($userId) > 0;
            })->count(),
            'archived' => $conversations->filter(function ($conv) use ($userId) {
                return $userId === $conv->guest_id ? $conv->guest_archived : $conv->host_archived;
            })->count(),
            'by_type' => $conversations->groupBy('type')->map->count()->toArray()
        ];
    }
}
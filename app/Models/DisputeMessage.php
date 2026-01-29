<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DisputeMessage Model - Dispute Communication Management
 * 
 * Manages messages within dispute resolution process
 * 
 * @property int $id
 * @property int $dispute_id
 * @property int $sender_id
 * @property string $sender_role
 * @property string $message
 * @property array|null $attachments
 * @property bool $is_internal
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class DisputeMessage extends Model
{
    use HasFactory;

    /**
     * Sender roles
     */
    const ROLE_GUEST = 'guest';
    const ROLE_HOST = 'host';
    const ROLE_ADMIN = 'admin';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'dispute_id',
        'sender_id',
        'sender_role',
        'message',
        'attachments',
        'is_internal'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'attachments' => 'array',
        'is_internal' => 'boolean'
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'is_internal' => false,
        'attachments' => '[]'
    ];

    /**
     * Dispute relationship
     */
    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    /**
     * Sender relationship
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Scope for internal messages
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Scope for public messages
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Scope for admin messages
     */
    public function scopeByAdmin($query)
    {
        return $query->where('sender_role', self::ROLE_ADMIN);
    }

    /**
     * Scope for guest messages
     */
    public function scopeByGuest($query)
    {
        return $query->where('sender_role', self::ROLE_GUEST);
    }

    /**
     * Scope for host messages
     */
    public function scopeByHost($query)
    {
        return $query->where('sender_role', self::ROLE_HOST);
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
        return nl2br(htmlspecialchars($this->message, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Create admin message
     */
    public static function createAdminMessage(
        int $disputeId,
        int $adminId,
        string $message,
        bool $isInternal = false,
        array $attachments = []
    ): self {
        return static::create([
            'dispute_id' => $disputeId,
            'sender_id' => $adminId,
            'sender_role' => self::ROLE_ADMIN,
            'message' => $message,
            'attachments' => $attachments,
            'is_internal' => $isInternal
        ]);
    }
}
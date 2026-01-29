<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SyncLog Model
 * 
 * Tracks synchronization activities between HabibiStay and external channels
 * 
 * @property int $id
 * @property int $user_id
 * @property int|null $channel_id
 * @property string $channel_name
 * @property int|null $property_id
 * @property string $action
 * @property string $status
 * @property string $message
 * @property array|null $details
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SyncLog extends Model
{
    use HasFactory;

    /**
     * Sync actions enumeration
     */
    const ACTION_CHANNEL_CONNECTED = 'channel_connected';
    const ACTION_CHANNEL_DISCONNECTED = 'channel_disconnected';
    const ACTION_CHANNEL_UPDATED = 'channel_updated';
    const ACTION_PROPERTY_SYNC = 'property_sync';
    const ACTION_AVAILABILITY_SYNC = 'availability_sync';
    const ACTION_PRICING_SYNC = 'pricing_sync';
    const ACTION_BOOKING_IMPORT = 'booking_import';
    const ACTION_BOOKING_EXPORT = 'booking_export';
    const ACTION_CALENDAR_SYNC = 'calendar_sync';
    const ACTION_FULL_SYNC = 'full_sync';

    /**
     * Sync status enumeration
     */
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_WARNING = 'warning';
    const STATUS_INFO = 'info';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'user_id',
        'channel_id',
        'channel_name',
        'property_id',
        'action',
        'status',
        'message',
        'details'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'details' => 'array'
    ];

    /**
     * Get the user that owns this sync log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the channel associated with this sync log
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Get the property associated with this sync log
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Scope for successful syncs
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Scope for failed syncs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_ERROR);
    }

    /**
     * Scope for warnings
     */
    public function scopeWarnings($query)
    {
        return $query->where('status', self::STATUS_WARNING);
    }

    /**
     * Scope for specific action
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific channel
     */
    public function scopeForChannel($query, int $channelId)
    {
        return $query->where('channel_id', $channelId);
    }

    /**
     * Scope for specific property
     */
    public function scopeForProperty($query, int $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    /**
     * Scope for today's logs
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for recent logs (last 24 hours)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }

    /**
     * Check if sync was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Check if sync failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    /**
     * Check if sync has warnings
     */
    public function hasWarnings(): bool
    {
        return $this->status === self::STATUS_WARNING;
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_SUCCESS => 'bg-green-100 text-green-800',
            self::STATUS_ERROR => 'bg-red-100 text-red-800',
            self::STATUS_WARNING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_INFO => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get status icon for UI
     */
    public function getStatusIcon(): string
    {
        return match($this->status) {
            self::STATUS_SUCCESS => '✓',
            self::STATUS_ERROR => '✗',
            self::STATUS_WARNING => '⚠',
            self::STATUS_INFO => 'ℹ',
            default => '•'
        };
    }

    /**
     * Get action display name
     */
    public function getActionDisplayName(): string
    {
        $names = [
            self::ACTION_CHANNEL_CONNECTED => 'Channel Connected',
            self::ACTION_CHANNEL_DISCONNECTED => 'Channel Disconnected',
            self::ACTION_CHANNEL_UPDATED => 'Channel Updated',
            self::ACTION_PROPERTY_SYNC => 'Property Sync',
            self::ACTION_AVAILABILITY_SYNC => 'Availability Sync',
            self::ACTION_PRICING_SYNC => 'Pricing Sync',
            self::ACTION_BOOKING_IMPORT => 'Booking Import',
            self::ACTION_BOOKING_EXPORT => 'Booking Export',
            self::ACTION_FULL_SYNC => 'Full Sync'
        ];

        return $names[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    /**
     * Create a success log entry
     */
    public static function logSuccess(
        int $userId,
        ?int $channelId,
        string $channelName,
        string $action,
        string $message,
        ?int $propertyId = null,
        ?array $details = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'channel_id' => $channelId,
            'channel_name' => $channelName,
            'property_id' => $propertyId,
            'action' => $action,
            'status' => self::STATUS_SUCCESS,
            'message' => $message,
            'details' => $details
        ]);
    }

    /**
     * Create an error log entry
     */
    public static function logError(
        int $userId,
        ?int $channelId,
        string $channelName,
        string $action,
        string $message,
        ?int $propertyId = null,
        ?array $details = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'channel_id' => $channelId,
            'channel_name' => $channelName,
            'property_id' => $propertyId,
            'action' => $action,
            'status' => self::STATUS_ERROR,
            'message' => $message,
            'details' => $details
        ]);
    }

    /**
     * Create a warning log entry
     */
    public static function logWarning(
        int $userId,
        ?int $channelId,
        string $channelName,
        string $action,
        string $message,
        ?int $propertyId = null,
        ?array $details = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'channel_id' => $channelId,
            'channel_name' => $channelName,
            'property_id' => $propertyId,
            'action' => $action,
            'status' => self::STATUS_WARNING,
            'message' => $message,
            'details' => $details
        ]);
    }

    /**
     * Create an info log entry
     */
    public static function logInfo(
        int $userId,
        ?int $channelId,
        string $channelName,
        string $action,
        string $message,
        ?int $propertyId = null,
        ?array $details = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'channel_id' => $channelId,
            'channel_name' => $channelName,
            'property_id' => $propertyId,
            'action' => $action,
            'status' => self::STATUS_INFO,
            'message' => $message,
            'details' => $details
        ]);
    }

    /**
     * Get available actions
     */
    public static function getAvailableActions(): array
    {
        return [
            self::ACTION_CHANNEL_CONNECTED => 'Channel Connected',
            self::ACTION_CHANNEL_DISCONNECTED => 'Channel Disconnected',
            self::ACTION_CHANNEL_UPDATED => 'Channel Updated',
            self::ACTION_PROPERTY_SYNC => 'Property Sync',
            self::ACTION_AVAILABILITY_SYNC => 'Availability Sync',
            self::ACTION_PRICING_SYNC => 'Pricing Sync',
            self::ACTION_BOOKING_IMPORT => 'Booking Import',
            self::ACTION_BOOKING_EXPORT => 'Booking Export',
            self::ACTION_FULL_SYNC => 'Full Sync'
        ];
    }

    /**
     * Get available statuses
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_SUCCESS => 'Success',
            self::STATUS_ERROR => 'Error',
            self::STATUS_WARNING => 'Warning',
            self::STATUS_INFO => 'Info'
        ];
    }
}

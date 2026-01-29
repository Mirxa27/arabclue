<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Channel Model
 * 
 * Represents external booking channels (Booking.com, Airbnb, etc.)
 * that hosts can connect to distribute their properties
 * 
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $name
 * @property string $api_key
 * @property string|null $api_secret
 * @property string $status
 * @property bool $auto_sync
 * @property array|null $settings
 * @property \Carbon\Carbon|null $last_sync_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Channel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Channel types enumeration
     */
    const TYPE_BOOKING = 'booking';
    const TYPE_AIRBNB = 'airbnb';
    const TYPE_EXPEDIA = 'expedia';
    const TYPE_AGODA = 'agoda';
    const TYPE_VRBO = 'vrbo';

    /**
     * Channel status enumeration
     */
    const STATUS_CONNECTED = 'connected';
    const STATUS_DISCONNECTED = 'disconnected';
    const STATUS_SYNCING = 'syncing';
    const STATUS_ERROR = 'error';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'user_id',
        'type',
        'name',
        'api_key',
        'api_secret',
        'status',
        'auto_sync',
        'settings',
        'last_sync_at'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'auto_sync' => 'boolean',
        'settings' => 'array',
        'last_sync_at' => 'datetime'
    ];

    /**
     * Hidden attributes
     */
    protected $hidden = [
        'api_key',
        'api_secret'
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'status' => self::STATUS_DISCONNECTED,
        'auto_sync' => true,
        'settings' => '{}'
    ];

    /**
     * Get the host that owns this channel
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the host that owns this channel (alias)
     */
    public function host(): BelongsTo
    {
        return $this->user();
    }

    /**
     * Get properties connected to this channel
     */
    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'channel_property')
            ->withPivot(['external_id', 'sync_status', 'last_synced_at'])
            ->withTimestamps();
    }

    /**
     * Get sync logs for this channel
     */
    public function syncLogs(): HasMany
    {
        return $this->hasMany(SyncLog::class);
    }

    /**
     * Scope for connected channels
     */
    public function scopeConnected($query)
    {
        return $query->where('status', self::STATUS_CONNECTED);
    }

    /**
     * Scope for auto-sync enabled channels
     */
    public function scopeAutoSync($query)
    {
        return $query->where('auto_sync', true);
    }

    /**
     * Scope for specific channel type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if channel is connected
     */
    public function isConnected(): bool
    {
        return $this->status === self::STATUS_CONNECTED;
    }

    /**
     * Check if channel is currently syncing
     */
    public function isSyncing(): bool
    {
        return $this->status === self::STATUS_SYNCING;
    }

    /**
     * Check if channel has errors
     */
    public function hasError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    /**
     * Check if auto-sync is enabled
     */
    public function isAutoSyncEnabled(): bool
    {
        return $this->auto_sync;
    }

    /**
     * Get decrypted API key
     */
    public function getDecryptedApiKey(): ?string
    {
        return $this->api_key ? decrypt($this->api_key) : null;
    }

    /**
     * Get decrypted API secret
     */
    public function getDecryptedApiSecret(): ?string
    {
        return $this->api_secret ? decrypt($this->api_secret) : null;
    }

    /**
     * Update sync status
     */
    public function updateSyncStatus(string $status): void
    {
        $this->update([
            'status' => $status,
            'last_sync_at' => now()
        ]);
    }

    /**
     * Mark as syncing
     */
    public function markAsSyncing(): void
    {
        $this->updateSyncStatus(self::STATUS_SYNCING);
    }

    /**
     * Mark as connected
     */
    public function markAsConnected(): void
    {
        $this->updateSyncStatus(self::STATUS_CONNECTED);
    }

    /**
     * Mark as error
     */
    public function markAsError(): void
    {
        $this->updateSyncStatus(self::STATUS_ERROR);
    }

    /**
     * Get channel display name
     */
    public function getDisplayName(): string
    {
        $names = [
            self::TYPE_BOOKING => 'Booking.com',
            self::TYPE_AIRBNB => 'Airbnb',
            self::TYPE_EXPEDIA => 'Expedia',
            self::TYPE_AGODA => 'Agoda',
            self::TYPE_VRBO => 'VRBO'
        ];

        return $names[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get channel icon/logo path
     */
    public function getIconPath(): string
    {
        return "/images/channels/{$this->type}.png";
    }

    /**
     * Get channel configuration
     */
    public function getConfiguration(): array
    {
        return array_merge([
            'auto_sync' => $this->auto_sync,
            'sync_interval' => 60, // minutes
            'sync_properties' => true,
            'sync_availability' => true,
            'sync_pricing' => true,
            'sync_bookings' => true
        ], $this->settings ?? []);
    }

    /**
     * Update channel configuration
     */
    public function updateConfiguration(array $config): void
    {
        $this->update([
            'settings' => array_merge($this->settings ?? [], $config)
        ]);
    }

    /**
     * Get properties count for this channel
     */
    public function getPropertiesCountAttribute(): int
    {
        return $this->properties()->count();
    }

    /**
     * Get last successful sync time
     */
    public function getLastSuccessfulSyncAttribute(): ?string
    {
        $lastSuccessfulLog = $this->syncLogs()
            ->where('status', 'success')
            ->latest()
            ->first();

        return $lastSuccessfulLog?->created_at?->diffForHumans();
    }

    /**
     * Get sync error count for today
     */
    public function getTodayErrorCountAttribute(): int
    {
        return $this->syncLogs()
            ->where('status', 'error')
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Check if channel needs attention (has recent errors)
     */
    public function needsAttention(): bool
    {
        return $this->today_error_count > 0 || $this->status === self::STATUS_ERROR;
    }

    /**
     * Get available channel types
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_BOOKING => 'Booking.com',
            self::TYPE_AIRBNB => 'Airbnb',
            self::TYPE_EXPEDIA => 'Expedia',
            self::TYPE_AGODA => 'Agoda',
            self::TYPE_VRBO => 'VRBO'
        ];
    }

    /**
     * Get available statuses
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_CONNECTED => 'Connected',
            self::STATUS_DISCONNECTED => 'Disconnected',
            self::STATUS_SYNCING => 'Syncing',
            self::STATUS_ERROR => 'Error'
        ];
    }
}

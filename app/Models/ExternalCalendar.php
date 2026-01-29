<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExternalCalendar Model - External iCal Feeds
 *
 * @property int $id
 * @property int $property_id
 * @property string $name
 * @property string $url
 * @property bool $auto_sync
 * @property string $sync_frequency
 * @property \Carbon\Carbon|null $last_synced_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ExternalCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'name',
        'url',
        'auto_sync',
        'sync_frequency',
        'last_synced_at'
    ];

    protected $casts = [
        'property_id' => 'integer',
        'auto_sync' => 'boolean',
        'last_synced_at' => 'datetime'
    ];

    /**
     * Get the property this calendar belongs to
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}

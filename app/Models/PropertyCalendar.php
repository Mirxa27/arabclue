<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * PropertyCalendar Model - Property Availability Management
 * 
 * Manages property availability and pricing calendar
 * 
 * @property int $id
 * @property int $property_id
 * @property \Carbon\Carbon $date
 * @property string $status
 * @property float|null $price
 * @property string|null $notes
 * @property int|null $booking_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PropertyCalendar extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'property_calendar';

    /**
     * Calendar status
     */
    const STATUS_AVAILABLE = 'available';
    const STATUS_BOOKED = 'booked';
    const STATUS_BLOCKED = 'blocked';
    const STATUS_MAINTENANCE = 'maintenance';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'property_id',
        'date',
        'status',
        'price',
        'notes',
        'booking_id',
        'source',
        'external_id',
        'title'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'date' => 'date',
        'price' => 'float'
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'status' => self::STATUS_AVAILABLE
    ];

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
     * Scope for available dates
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    /**
     * Scope for booked dates
     */
    public function scopeBooked($query)
    {
        return $query->where('status', self::STATUS_BOOKED);
    }

    /**
     * Scope for blocked dates
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', self::STATUS_BLOCKED);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Check if property is available for date range
     */
    public static function isAvailable(int $propertyId, Carbon $startDate, Carbon $endDate): bool
    {
        $unavailableDates = static::where('property_id', $propertyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', '!=', self::STATUS_AVAILABLE)
            ->count();

        return $unavailableDates === 0;
    }

    /**
     * Block dates for property
     */
    public static function blockDates(
        int $propertyId,
        Carbon $startDate,
        Carbon $endDate,
        string $reason = null
    ): int {
        $dates = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dates[] = [
                'property_id' => $propertyId,
                'date' => $current->format('Y-m-d'),
                'status' => self::STATUS_BLOCKED,
                'notes' => $reason,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            $current->addDay();
        }

        return static::upsert($dates, ['property_id', 'date'], ['status', 'notes', 'updated_at']);
    }

    /**
     * Unblock dates for property
     */
    public static function unblockDates(int $propertyId, Carbon $startDate, Carbon $endDate): int
    {
        return static::where('property_id', $propertyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', self::STATUS_BLOCKED)
            ->update(['status' => self::STATUS_AVAILABLE, 'notes' => null]);
    }

    /**
     * Set booking dates
     */
    public static function setBookingDates(
        int $propertyId,
        int $bookingId,
        Carbon $startDate,
        Carbon $endDate
    ): int {
        $dates = [];
        $current = $startDate->copy();

        while ($current->lt($endDate)) {
            $dates[] = [
                'property_id' => $propertyId,
                'date' => $current->format('Y-m-d'),
                'status' => self::STATUS_BOOKED,
                'booking_id' => $bookingId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            $current->addDay();
        }

        return static::upsert($dates, ['property_id', 'date'], ['status', 'booking_id', 'updated_at']);
    }

    /**
     * Clear booking dates
     */
    public static function clearBookingDates(int $bookingId): int
    {
        return static::where('booking_id', $bookingId)
            ->update([
                'status' => self::STATUS_AVAILABLE,
                'booking_id' => null
            ]);
    }

    /**
     * Get availability calendar for property
     */
    public static function getCalendar(
        int $propertyId,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        return static::where('property_id', $propertyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->keyBy(fn($item) => $item->date->format('Y-m-d'));
    }

    /**
     * Get occupancy rate for property
     */
    public static function getOccupancyRate(
        int $propertyId,
        Carbon $startDate,
        Carbon $endDate
    ): float {
        $totalDays = $startDate->diffInDays($endDate);
        $bookedDays = static::where('property_id', $propertyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', self::STATUS_BOOKED)
            ->count();

        return $totalDays > 0 ? ($bookedDays / $totalDays) * 100 : 0;
    }
}

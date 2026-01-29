<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_reference',
        'property_id',
        'user_id',
        'host_id',
        'check_in',
        'check_out',
        'guests',
        'adults',
        'children',
        'infants',
        'guest_details',
        'price_per_night',
        'total_nights',
        'accommodation_total',
        'cleaning_fee',
        'service_fee',
        'host_service_fee',
        'tax_amount',
        'discount_amount',
        'coupon_code',
        'total_amount',
        'currency',
        'exchange_rate',
        'status',
        'payment_status',
        'payment_method',
        'payment_intent_id',
        'payment_details',
        'paid_at',
        'special_requests',
        'host_message',
        'guest_message',
        'guest_agreed_to_rules',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'confirmed_at',
        'refund_amount',
        'refund_status',
        'checked_in_at',
        'checked_out_at',
        'check_in_details',
        'booked_via_sara',
        'sara_conversation_id'
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'payment_details' => 'array',
        'guest_details' => 'array',
        'check_in_details' => 'array',
        'guest_agreed_to_rules' => 'boolean',
        'booked_via_sara' => 'boolean',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'price_per_night' => 'float',
        'accommodation_total' => 'float',
        'cleaning_fee' => 'float',
        'service_fee' => 'float',
        'host_service_fee' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'exchange_rate' => 'float',
        'refund_amount' => 'float'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = static::generateBookingReference();
            }
        });
    }

    public static function generateBookingReference(): string
    {
        do {
            $reference = 'HB' . strtoupper(Str::random(8));
        } while (static::where('booking_reference', $reference)->exists());

        return $reference;
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function saraConversation(): BelongsTo
    {
        return $this->belongsTo(SaraConversation::class, 'sara_conversation_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'accepted']);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function canBeCancelled(): bool
    {
        return $this->isActive() && !$this->check_in->isPast();
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'accepted')
                    ->where('check_in', '>', now());
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'accepted', 'completed']);
    }

    /**
     * Provide backwards compatibility for tests expecting a `nights` attribute.
     */
    public function getNightsAttribute(): int
    {
        return $this->total_nights;
    }

    public function setNightsAttribute($value): void
    {
        $this->attributes['total_nights'] = $value;
    }
}
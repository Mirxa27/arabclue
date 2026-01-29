<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Coupon Model - Discount Management
 * 
 * Manages promotional codes, discounts, and special offers
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $description
 * @property string $type
 * @property float $value
 * @property float|null $minimum_amount
 * @property float|null $maximum_discount
 * @property int|null $usage_limit
 * @property int $used_count
 * @property int|null $user_limit
 * @property bool $is_active
 * @property \Carbon\Carbon|null $starts_at
 * @property \Carbon\Carbon|null $expires_at
 * @property array|null $applicable_to
 * @property array|null $restrictions
 * @property int|null $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Coupon types
     */
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED_AMOUNT = 'fixed_amount';
    const TYPE_FREE_NIGHT = 'free_night';
    const TYPE_FREE_CLEANING = 'free_cleaning';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_amount',
        'maximum_discount',
        'usage_limit',
        'used_count',
        'user_limit',
        'is_active',
        'starts_at',
        'expires_at',
        'applicable_to',
        'restrictions',
        'created_by'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'value' => 'float',
        'minimum_amount' => 'float',
        'maximum_discount' => 'float',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'applicable_to' => 'array',
        'restrictions' => 'array'
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'type' => self::TYPE_PERCENTAGE,
        'used_count' => 0,
        'user_limit' => 1,
        'is_active' => true,
        'applicable_to' => '{}',
        'restrictions' => '{}'
    ];

    /**
     * Model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($coupon) {
            if (empty($coupon->code)) {
                $coupon->code = static::generateUniqueCode();
            } else {
                $coupon->code = strtoupper($coupon->code);
            }
        });

        static::updating(function ($coupon) {
            if ($coupon->isDirty('code')) {
                $coupon->code = strtoupper($coupon->code);
            }
        });
    }

    /**
     * Creator relationship
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Coupon usages
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Bookings that used this coupon
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'coupon_code', 'code');
    }

    /**
     * Generate unique coupon code
     */
    public static function generateUniqueCode(int $length = 8): string
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Check if coupon is valid
     */
    public function isValid(): bool
    {
        return $this->is_active &&
               (!$this->starts_at || $this->starts_at->isPast()) &&
               (!$this->expires_at || $this->expires_at->isFuture()) &&
               (!$this->usage_limit || $this->used_count < $this->usage_limit);
    }

    /**
     * Check if coupon is valid for user
     */
    public function isValidForUser(int $userId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check user usage limit
        if ($this->user_limit) {
            $userUsageCount = $this->usages()
                                  ->where('user_id', $userId)
                                  ->count();
            
            if ($userUsageCount >= $this->user_limit) {
                return false;
            }
        }

        // Check user restrictions
        $restrictions = $this->restrictions ?? [];
        
        if (isset($restrictions['excluded_users']) && 
            in_array($userId, $restrictions['excluded_users'])) {
            return false;
        }

        if (isset($restrictions['included_users']) && 
            !in_array($userId, $restrictions['included_users'])) {
            return false;
        }

        return true;
    }

    /**
     * Check if coupon is applicable to booking
     */
    public function isApplicableToBooking(array $bookingDetails): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $applicable = $this->applicable_to ?? [];
        $restrictions = $this->restrictions ?? [];

        // Check minimum amount
        if ($this->minimum_amount && 
            $bookingDetails['subtotal'] < $this->minimum_amount) {
            return false;
        }

        // Check property types
        if (isset($applicable['property_types']) && 
            !in_array($bookingDetails['property_type'], $applicable['property_types'])) {
            return false;
        }

        // Check cities
        if (isset($applicable['cities']) && 
            !in_array($bookingDetails['city'], $applicable['cities'])) {
            return false;
        }

        // Check minimum nights
        if (isset($restrictions['minimum_nights']) && 
            $bookingDetails['nights'] < $restrictions['minimum_nights']) {
            return false;
        }

        // Check maximum nights
        if (isset($restrictions['maximum_nights']) && 
            $bookingDetails['nights'] > $restrictions['maximum_nights']) {
            return false;
        }

        // Check booking dates
        if (isset($restrictions['blackout_dates'])) {
            $checkIn = \Carbon\Carbon::parse($bookingDetails['check_in']);
            $checkOut = \Carbon\Carbon::parse($bookingDetails['check_out']);
            
            foreach ($restrictions['blackout_dates'] as $blackout) {
                $blackoutStart = \Carbon\Carbon::parse($blackout['start']);
                $blackoutEnd = \Carbon\Carbon::parse($blackout['end']);
                
                if ($checkIn->between($blackoutStart, $blackoutEnd) || 
                    $checkOut->between($blackoutStart, $blackoutEnd)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount(array $bookingDetails): float
    {
        if (!$this->isApplicableToBooking($bookingDetails)) {
            return 0;
        }

        $subtotal = $bookingDetails['subtotal'];
        $discount = 0;

        switch ($this->type) {
            case self::TYPE_PERCENTAGE:
                $discount = $subtotal * ($this->value / 100);
                break;
                
            case self::TYPE_FIXED_AMOUNT:
                $discount = $this->value;
                break;
                
            case self::TYPE_FREE_NIGHT:
                $pricePerNight = $bookingDetails['price_per_night'];
                $discount = min($this->value * $pricePerNight, $subtotal);
                break;
                
            case self::TYPE_FREE_CLEANING:
                $discount = min($bookingDetails['cleaning_fee'] ?? 0, $subtotal);
                break;
        }

        // Apply maximum discount limit
        if ($this->maximum_discount) {
            $discount = min($discount, $this->maximum_discount);
        }

        // Don't exceed the subtotal
        return min($discount, $subtotal);
    }

    /**
     * Apply coupon to booking
     */
    public function applyToBooking(int $userId, array $bookingDetails): array
    {
        if (!$this->isValidForUser($userId) || 
            !$this->isApplicableToBooking($bookingDetails)) {
            throw new \Exception('Coupon is not valid for this booking');
        }

        $discountAmount = $this->calculateDiscount($bookingDetails);

        // Create usage record
        $this->usages()->create([
            'user_id' => $userId,
            'booking_reference' => $bookingDetails['booking_reference'] ?? null,
            'discount_amount' => $discountAmount,
            'booking_details' => $bookingDetails
        ]);

        // Increment usage count
        $this->increment('used_count');

        return [
            'discount_amount' => $discountAmount,
            'discount_type' => $this->type,
            'discount_description' => $this->getDiscountDescription($discountAmount)
        ];
    }

    /**
     * Get discount description
     */
    public function getDiscountDescription(float $amount = null): string
    {
        switch ($this->type) {
            case self::TYPE_PERCENTAGE:
                return "{$this->value}% off";
                
            case self::TYPE_FIXED_AMOUNT:
                return "SAR {$this->value} off";
                
            case self::TYPE_FREE_NIGHT:
                $nights = $this->value == 1 ? 'night' : 'nights';
                return "{$this->value} free {$nights}";
                
            case self::TYPE_FREE_CLEANING:
                return "Free cleaning fee";
                
            default:
                return $amount ? "SAR {$amount} discount" : "Discount applied";
        }
    }

    /**
     * Get usage statistics
     */
    public function getUsageStats(): array
    {
        $usages = $this->usages()->with('user')->get();
        
        return [
            'total_uses' => $usages->count(),
            'unique_users' => $usages->unique('user_id')->count(),
            'total_discount_given' => $usages->sum('discount_amount'),
            'average_discount' => $usages->avg('discount_amount'),
            'remaining_uses' => $this->usage_limit ? 
                max(0, $this->usage_limit - $this->used_count) : 
                'unlimited',
            'usage_rate' => $this->usage_limit ? 
                round(($this->used_count / $this->usage_limit) * 100, 2) : 
                null
        ];
    }

    /**
     * Scope for active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for valid coupons (not expired, not used up)
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('starts_at')
                          ->orWhere('starts_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('usage_limit')
                          ->orWhereRaw('used_count < usage_limit');
                    });
    }

    /**
     * Scope for expired coupons
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope for used up coupons
     */
    public function scopeUsedUp($query)
    {
        return $query->whereNotNull('usage_limit')
                    ->whereRaw('used_count >= usage_limit');
    }

    /**
     * Find coupon by code
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', strtoupper($code))->first();
    }

    /**
     * Validate coupon code for booking
     */
    public static function validateForBooking(
        string $code, 
        int $userId, 
        array $bookingDetails
    ): array {
        $coupon = static::findByCode($code);
        
        if (!$coupon) {
            return [
                'valid' => false,
                'error' => 'Coupon code not found'
            ];
        }

        if (!$coupon->isValidForUser($userId)) {
            return [
                'valid' => false,
                'error' => 'Coupon is not valid for your account'
            ];
        }

        if (!$coupon->isApplicableToBooking($bookingDetails)) {
            return [
                'valid' => false,
                'error' => 'Coupon is not applicable to this booking'
            ];
        }

        $discountAmount = $coupon->calculateDiscount($bookingDetails);

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount_amount' => $discountAmount,
            'description' => $coupon->getDiscountDescription($discountAmount)
        ];
    }

    /**
     * Create promotional coupon
     */
    public static function createPromotional(array $details): self
    {
        return static::create([
            'code' => $details['code'] ?? static::generateUniqueCode(),
            'name' => $details['name'],
            'description' => $details['description'],
            'type' => $details['type'],
            'value' => $details['value'],
            'minimum_amount' => $details['minimum_amount'] ?? null,
            'maximum_discount' => $details['maximum_discount'] ?? null,
            'usage_limit' => $details['usage_limit'] ?? null,
            'user_limit' => $details['user_limit'] ?? 1,
            'starts_at' => $details['starts_at'] ?? now(),
            'expires_at' => $details['expires_at'] ?? null,
            'applicable_to' => $details['applicable_to'] ?? [],
            'restrictions' => $details['restrictions'] ?? [],
            'created_by' => $details['created_by'] ?? null
        ]);
    }
}
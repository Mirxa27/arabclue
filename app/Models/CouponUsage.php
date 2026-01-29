<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * CouponUsage Model - Coupon Usage Tracking
 * 
 * Tracks individual coupon usage instances
 * 
 * @property int $id
 * @property int $coupon_id
 * @property int $booking_id
 * @property int $user_id
 * @property float $discount_amount
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CouponUsage extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'coupon_id',
        'booking_id',
        'user_id',
        'discount_amount'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'discount_amount' => 'float'
    ];

    /**
     * Coupon relationship
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Booking relationship
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for user's coupon usages
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for coupon usages
     */
    public function scopeForCoupon($query, int $couponId)
    {
        return $query->where('coupon_id', $couponId);
    }

    /**
     * Get usage statistics for coupon
     */
    public static function getStatsForCoupon(int $couponId): array
    {
        $usages = static::where('coupon_id', $couponId)->get();
        
        return [
            'total_uses' => $usages->count(),
            'total_discount' => $usages->sum('discount_amount'),
            'average_discount' => $usages->avg('discount_amount'),
            'unique_users' => $usages->unique('user_id')->count(),
            'recent_uses' => $usages->where('created_at', '>=', Carbon::now()->subDays(30))->count()
        ];
    }

    /**
     * Get usage statistics for user
     */
    public static function getStatsForUser(int $userId): array
    {
        $usages = static::where('user_id', $userId)->get();
        
        return [
            'total_coupons_used' => $usages->count(),
            'total_savings' => $usages->sum('discount_amount'),
            'average_savings' => $usages->avg('discount_amount'),
            'recent_savings' => $usages->where('created_at', '>=', Carbon::now()->subDays(30))->sum('discount_amount')
        ];
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ReferralCredit Model - Referral Credit Tracking
 * 
 * Tracks credits awarded through referral system
 * 
 * @property int $id
 * @property int $referral_id
 * @property int $user_id
 * @property float $credit_amount
 * @property string $credit_type
 * @property string $description
 * @property string $status
 * @property float|null $used_amount
 * @property \Carbon\Carbon|null $used_at
 * @property string|null $used_for
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ReferralCredit extends Model
{
    use HasFactory;

    /**
     * Credit status
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_USED = 'used';
    const STATUS_PARTIALLY_USED = 'partially_used';
    const STATUS_EXPIRED = 'expired';

   /**
    * Credit type
    */
   const CREDIT_TYPE_REFERRER = 'referrer';
   const CREDIT_TYPE_REFERRED = 'referred';
   const TYPE_SIGNUP_REFERRER = 'signup_referrer';
   const TYPE_SIGNUP_REFERRED = 'signup_referred';
   const TYPE_BOOKING_REFERRER = 'booking_referrer';
   const TYPE_BOOKING_REFERRED = 'booking_referred';
   const TYPE_USED = 'used';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'referral_id',
        'user_id',
        'credit_amount',
        'credit_type',
        'description',
        'status',
        'used_amount',
        'used_at',
        'used_for',
        'expires_at'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'credit_amount' => 'float',
        'used_amount' => 'float',
        'used_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
        'used_amount' => 0
    ];

    /**
     * Referral relationship
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get remaining credit amount
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->credit_amount - ($this->used_amount ?? 0);
    }

    /**
     * Check if credit is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if credit is available for use
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_ACTIVE && 
               !$this->isExpired() && 
               $this->remaining_amount > 0;
    }

    /**
     * Use credit amount
     */
    public function useCredit(float $amount, string $purpose = null): bool
    {
        if (!$this->isAvailable() || $amount > $this->remaining_amount) {
            return false;
        }

        $newUsedAmount = ($this->used_amount ?? 0) + $amount;
        $newStatus = $newUsedAmount >= $this->credit_amount ? 
                    self::STATUS_USED : 
                    self::STATUS_PARTIALLY_USED;

        $this->update([
            'used_amount' => $newUsedAmount,
            'status' => $newStatus,
            'used_at' => now(),
            'used_for' => $purpose
        ]);

        return true;
    }

    /**
     * Expire credit
     */
    public function expire(): void
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Scope for active credits
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for available credits (active and not expired)
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    })
                    ->whereRaw('used_amount < credit_amount');
    }

    /**
     * Scope for expired credits
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope for user's credits
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get user's total available credits
     */
    public static function getTotalAvailableForUser(int $userId): float
    {
        return static::forUser($userId)
                    ->available()
                    ->get()
                    ->sum('remaining_amount');
    }

    /**
     * Use credits for user (uses oldest first)
     */
    public static function useCreditsForUser(
        int $userId, 
        float $amount, 
        string $purpose = null
    ): array {
        $credits = static::forUser($userId)
                         ->available()
                         ->orderBy('created_at', 'asc')
                         ->get();

        $remainingAmount = $amount;
        $usedCredits = [];

        foreach ($credits as $credit) {
            if ($remainingAmount <= 0) {
                break;
            }

            $useAmount = min($remainingAmount, $credit->remaining_amount);
            
            if ($credit->useCredit($useAmount, $purpose)) {
                $usedCredits[] = [
                    'credit_id' => $credit->id,
                    'amount_used' => $useAmount,
                    'remaining_in_credit' => $credit->remaining_amount
                ];
                
                $remainingAmount -= $useAmount;
            }
        }

        return [
            'total_used' => $amount - $remainingAmount,
            'remaining_needed' => $remainingAmount,
            'credits_used' => $usedCredits
        ];
    }

    /**
     * Get credit statistics for user
     */
    public static function getStatsForUser(int $userId): array
    {
        $credits = static::forUser($userId)->get();
        
        return [
            'total_earned' => $credits->sum('credit_amount'),
            'total_used' => $credits->sum('used_amount'),
            'total_available' => $credits->where('status', self::STATUS_ACTIVE)->sum('remaining_amount'),
            'expired_amount' => $credits->where('status', self::STATUS_EXPIRED)->sum(function ($credit) {
                return $credit->credit_amount - ($credit->used_amount ?? 0);
            }),
            'credits_count' => $credits->count(),
            'active_credits' => $credits->where('status', self::STATUS_ACTIVE)->count(),
            'expiring_soon' => $credits->where('status', self::STATUS_ACTIVE)
                                     ->where('expires_at', '>', now())
                                     ->where('expires_at', '<', now()->addDays(30))
                                     ->count()
        ];
    }
}

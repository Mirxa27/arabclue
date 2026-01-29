<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Referral Model - User Referral System
 * 
 * Manages user referrals, tracking, and rewards
 * 
 * @property int $id
 * @property int $referrer_id
 * @property int|null $referred_id
 * @property string $referral_code
 * @property string|null $referred_email
 * @property string $status
 * @property float|null $referrer_credit
 * @property float|null $referred_credit
 * @property string|null $referrer_credit_type
 * @property string|null $referred_credit_type
 * @property \Carbon\Carbon|null $signup_completed_at
 * @property \Carbon\Carbon|null $first_booking_completed_at
 * @property \Carbon\Carbon|null $credits_awarded_at
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Referral extends Model
{
    use HasFactory;

    /**
     * Referral status
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SIGNED_UP = 'signed_up';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CREDITED = 'credited';
    const STATUS_EXPIRED = 'expired';

    /**
     * Credit types
     */
    const CREDIT_TYPE_BOOKING = 'booking_credit';
    const CREDIT_TYPE_CASH = 'cash_credit';
    const CREDIT_TYPE_PERCENTAGE = 'percentage_discount';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'referrer_id',
        'referred_id',
        'referral_code',
        'referred_email',
        'status',
        'referrer_credit',
        'referred_credit',
        'referrer_credit_type',
        'referred_credit_type',
        'signup_completed_at',
        'first_booking_completed_at',
        'credits_awarded_at',
        'metadata'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'referrer_credit' => 'float',
        'referred_credit' => 'float',
        'signup_completed_at' => 'datetime',
        'first_booking_completed_at' => 'datetime',
        'credits_awarded_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'referrer_credit_type' => self::CREDIT_TYPE_BOOKING,
        'referred_credit_type' => self::CREDIT_TYPE_BOOKING,
        'metadata' => '{}'
    ];

    /**
     * Model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($referral) {
            if (empty($referral->referral_code)) {
                $referral->referral_code = static::generateReferralCode();
            }
        });

        static::updated(function ($referral) {
            // Send notifications on status changes
            if ($referral->wasChanged('status')) {
                $referral->sendStatusNotification();
            }
        });
    }

    /**
     * Generate unique referral code
     */
    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Referrer relationship
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Referred user relationship
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    /**
     * Credits awarded for this referral
     */
    public function credits(): HasMany
    {
        return $this->hasMany(ReferralCredit::class);
    }

    /**
     * Mark signup as completed
     */
    public function markSignupCompleted(int $referredUserId): void
    {
        $this->update([
            'referred_id' => $referredUserId,
            'status' => self::STATUS_SIGNED_UP,
            'signup_completed_at' => now()
        ]);
    }

    /**
     * Mark first booking as completed
     */
    public function markFirstBookingCompleted(): void
    {
        if ($this->status === self::STATUS_SIGNED_UP) {
            $this->update([
                'status' => self::STATUS_COMPLETED,
                'first_booking_completed_at' => now()
            ]);

            // Award credits
            $this->awardCredits();
        }
    }

    /**
     * Award credits to both users
     */
    public function awardCredits(): void
    {
        $settings = $this->getReferralSettings();
        
        // Award credit to referrer
        if ($this->referrer_credit && $this->referrer_credit > 0) {
            ReferralCredit::create([
                'referral_id' => $this->id,
                'user_id' => $this->referrer_id,
                'credit_amount' => $this->referrer_credit,
                'credit_type' => $this->referrer_credit_type,
                'description' => "Referral bonus for inviting {$this->referred->name}",
                'expires_at' => now()->addDays($settings['credit_expiry_days'] ?? 365)
            ]);
        }

        // Award credit to referred user
        if ($this->referred_credit && $this->referred_credit > 0) {
            ReferralCredit::create([
                'referral_id' => $this->id,
                'user_id' => $this->referred_id,
                'credit_amount' => $this->referred_credit,
                'credit_type' => $this->referred_credit_type,
                'description' => "Welcome bonus from {$this->referrer->name}'s referral",
                'expires_at' => now()->addDays($settings['credit_expiry_days'] ?? 365)
            ]);
        }

        $this->update([
            'status' => self::STATUS_CREDITED,
            'credits_awarded_at' => now()
        ]);
    }

    /**
     * Get referral settings
     */
    protected function getReferralSettings(): array
    {
        return cache()->remember('referral_settings', 3600, function () {
            return [
                'referrer_credit_amount' => 100, // SAR
                'referred_credit_amount' => 50,  // SAR
                'credit_expiry_days' => 365,
                'max_referrals_per_user' => 50,
                'require_first_booking' => true
            ];
        });
    }

    /**
     * Send status notification
     */
    protected function sendStatusNotification(): void
    {
        switch ($this->status) {
            case self::STATUS_SIGNED_UP:
                $this->referrer->sendPushNotification(
                    "Referral Update",
                    "{$this->referred->name} signed up using your referral!",
                    ['type' => 'referral_signup', 'referral_id' => $this->id]
                );
                break;

            case self::STATUS_COMPLETED:
                $this->referrer->sendPushNotification(
                    "Referral Completed",
                    "{$this->referred->name} completed their first booking!",
                    ['type' => 'referral_completed', 'referral_id' => $this->id]
                );
                break;

            case self::STATUS_CREDITED:
                $this->referrer->sendPushNotification(
                    "Referral Bonus",
                    "You've earned SAR {$this->referrer_credit} for your referral!",
                    ['type' => 'referral_credited', 'referral_id' => $this->id]
                );

                if ($this->referred) {
                    $this->referred->sendPushNotification(
                        "Welcome Bonus",
                        "You've received SAR {$this->referred_credit} welcome bonus!",
                        ['type' => 'welcome_bonus', 'referral_id' => $this->id]
                    );
                }
                break;
        }
    }

    /**
     * Check if referral is still valid
     */
    public function isValid(): bool
    {
        return $this->status !== self::STATUS_EXPIRED &&
               $this->created_at->gt(now()->subDays(30)); // 30 days validity
    }

    /**
     * Expire referral
     */
    public function expire(): void
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Get referral progress
     */
    public function getProgress(): array
    {
        return [
            'code' => $this->referral_code,
            'status' => $this->status,
            'steps' => [
                'sent' => true,
                'signed_up' => !is_null($this->signup_completed_at),
                'first_booking' => !is_null($this->first_booking_completed_at),
                'credited' => !is_null($this->credits_awarded_at)
            ],
            'referrer_credit' => $this->referrer_credit,
            'referred_credit' => $this->referred_credit,
            'referred_user' => $this->referred?->only(['name', 'email']),
            'created_at' => $this->created_at
        ];
    }

    /**
     * Scope for pending referrals
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for completed referrals
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for credited referrals
     */
    public function scopeCredited($query)
    {
        return $query->where('status', self::STATUS_CREDITED);
    }

    /**
     * Scope for referrals by user
     */
    public function scopeByReferrer($query, int $userId)
    {
        return $query->where('referrer_id', $userId);
    }

    /**
     * Create referral invitation
     */
    public static function createInvitation(
        int $referrerId, 
        string $email, 
        array $options = []
    ): self {
        $settings = app()->call([new static, 'getReferralSettings']);
        
        return static::create([
            'referrer_id' => $referrerId,
            'referred_email' => $email,
            'referrer_credit' => $options['referrer_credit'] ?? $settings['referrer_credit_amount'],
            'referred_credit' => $options['referred_credit'] ?? $settings['referred_credit_amount'],
            'referrer_credit_type' => $options['referrer_credit_type'] ?? self::CREDIT_TYPE_BOOKING,
            'referred_credit_type' => $options['referred_credit_type'] ?? self::CREDIT_TYPE_BOOKING,
            'metadata' => $options['metadata'] ?? []
        ]);
    }

    /**
     * Find referral by code
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('referral_code', strtoupper($code))
                    ->where('status', self::STATUS_PENDING)
                    ->first();
    }

    /**
     * Get user's referral statistics
     */
    public static function getStatsForUser(int $userId): array
    {
        $referrals = static::where('referrer_id', $userId)->get();
        
        return [
            'total_sent' => $referrals->count(),
            'signed_up' => $referrals->where('status', '!=', self::STATUS_PENDING)->count(),
            'completed' => $referrals->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_CREDITED])->count(),
            'total_earned' => $referrals->where('status', self::STATUS_CREDITED)->sum('referrer_credit'),
            'pending_earnings' => $referrals->where('status', self::STATUS_COMPLETED)->sum('referrer_credit'),
            'success_rate' => $referrals->count() > 0 ? 
                round(($referrals->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_CREDITED])->count() / $referrals->count()) * 100, 2) : 
                0
        ];
    }

    /**
     * Get platform referral statistics
     */
    public static function getPlatformStats(): array
    {
        $referrals = static::all();
        
        return [
            'total_referrals' => $referrals->count(),
            'successful_referrals' => $referrals->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_CREDITED])->count(),
            'total_credits_awarded' => $referrals->where('status', self::STATUS_CREDITED)->sum(function ($ref) {
                return $ref->referrer_credit + $ref->referred_credit;
            }),
            'top_referrers' => static::with('referrer')
                                    ->selectRaw('referrer_id, COUNT(*) as referral_count, SUM(referrer_credit) as total_earned')
                                    ->where('status', self::STATUS_CREDITED)
                                    ->groupBy('referrer_id')
                                    ->orderBy('referral_count', 'desc')
                                    ->limit(10)
                                    ->get(),
            'conversion_rate' => $referrals->count() > 0 ? 
                round(($referrals->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_CREDITED])->count() / $referrals->count()) * 100, 2) : 
                0
        ];
    }
}
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\AdminUiPreference; // Added this line
use App\Traits\HasProfileCompletion;
use App\Traits\HasAIPersonalization;
use App\Traits\HasMobileAppSupport;

/**
 * User Model - Advanced Authentication & Identity Management
 * 
 * Implements multi-channel authentication, OAuth integration,
 * AI-driven personalization, and mobile app support with
 * push notification capabilities
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $phone
 * @property string $role
 * @property string|null $avatar
 * @property string $language
 * @property string|null $bio
 * @property string $status
 * @property string|null $google_id
 * @property string|null $facebook_id
 * @property string|null $apple_id
 * @property string|null $fcm_token
 * @property string|null $apns_token
 * @property array|null $device_info
 * @property array|null $preferences
 * @property array|null $notification_settings
 * @property bool $two_factor_enabled
 * @property string|null $two_factor_secret
 * @property bool $identity_verified
 * @property \Carbon\Carbon|null $identity_verified_at
 * @property string|null $government_id
 * @property float|null $host_rating
 * @property float|null $guest_rating
 * @property int $total_bookings
 * @property int $total_listings
 * @property string|null $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    use HasProfileCompletion, HasAIPersonalization, HasMobileAppSupport;

    /**
     * User roles enumeration
     */
    const ROLE_GUEST = 'guest';
    const ROLE_HOST = 'host';
    const ROLE_ADMIN = 'admin';

    /**
     * User status enumeration
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'avatar',
        'language',
        'bio',
        'status',
        'google_id',
        'facebook_id',
        'apple_id',
        'fcm_token',
        'apns_token',
        'device_info',
        'preferences',
        'notification_settings',
        'two_factor_enabled',
        'identity_verified',
        'government_id'
    ];

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Hidden attributes
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'government_id'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'identity_verified_at' => 'datetime',
        'device_info' => 'array',
        'preferences' => 'array',
        'notification_settings' => 'array',
        'two_factor_enabled' => 'boolean',
        'identity_verified' => 'boolean',
        'host_rating' => 'float',
        'guest_rating' => 'float'
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'role' => self::ROLE_GUEST,
        'status' => self::STATUS_ACTIVE,
        'language' => 'en',
        'total_bookings' => 0,
        'total_listings' => 0,
        'preferences' => '{}',
        'notification_settings' => '{}'
    ];

    /**
     * Model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Set default notification settings
            if (empty($user->notification_settings)) {
                $user->notification_settings = [
                    'email' => [
                        'bookings' => true,
                        'messages' => true,
                        'reviews' => true,
                        'marketing' => false
                    ],
                    'push' => [
                        'bookings' => true,
                        'messages' => true,
                        'reviews' => true,
                        'reminders' => true
                    ],
                    'sms' => [
                        'bookings' => true,
                        'urgent' => true
                    ]
                ];
            }

            // Set default preferences
            if (empty($user->preferences)) {
                $user->preferences = [
                    'currency' => 'SAR',
                    'date_format' => 'd/m/Y',
                    'time_format' => '24h',
                    'measurement_unit' => 'metric',
                    'search_radius' => 50,
                    'instant_booking' => true
                ];
            }
        });

        static::updated(function ($user) {
            // Clear user-specific caches when updated
            cache()->forget("user_preferences_{$user->id}");
            cache()->forget("user_permissions_{$user->id}");
        });
    }

    /**
     * Properties owned by the user (as host)
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Bookings made by the user (as guest)
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Bookings received by the user (as host)
     */
    public function hostBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'host_id');
    }

    /**
     * Reviews received by the user (as host)
     */
    public function hostReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'host_id');
    }

    /**
     * Get count of unread messages for user
     */
    public function unreadMessagesCount(): int
    {
        return $this->hasMany(Message::class, 'recipient_id')
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Reviews written by the user
     */
    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Alias for reviews written by the user
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Reviews received as host
     */
    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'host_id');
    }

    /**
     * Messages sent by the user
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Messages received by the user
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * User's wishlist
     */
    public function wishlist(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * User's wishlists (alternative method name)
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * User's wishlist collections
     */
    public function wishlistCollections(): HasMany
    {
        return $this->hasMany(WishlistCollection::class);
    }

    /**
     * Sara AI conversations
     */
    public function saraConversations(): HasMany
    {
        return $this->hasMany(SaraConversation::class);
    }

    /**
     * Admin UI preferences
     */
    public function adminPreferences(): HasOne
    {
        return $this->hasOne(AdminUiPreference::class);
    }

    /**
     * User preferences relationship
     */
    public function preferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    /**
     * User activity tracking relationship
     */
    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }

    /**
     * Check if user is host
     */
    public function isHost(): bool
    {
        return in_array($this->role, [self::ROLE_HOST, self::ROLE_ADMIN]);
    }

    /**
     * Check if user can host properties
     */
    public function canHost(): bool
    {
        return $this->isHost() && 
               $this->status === self::STATUS_ACTIVE && 
               $this->identity_verified;
    }

    /**
     * Promote user to host
     */
    public function promoteToHost(): bool
    {
        if ($this->role === self::ROLE_GUEST && $this->identity_verified) {
            $this->role = self::ROLE_HOST;
            return $this->save();
        }
        return false;
    }

    /**
     * Get user's full avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return str_starts_with($this->avatar, 'http') 
                ? $this->avatar 
                : asset('storage/' . $this->avatar);
        }

        // Generate Gravatar URL as fallback
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=200";
    }

    /**
     * Calculate profile completion percentage
     */
    public function getProfileCompletionAttribute(): int
    {
        $requiredFields = ['name', 'email', 'phone', 'bio', 'avatar'];
        $completedFields = 0;

        foreach ($requiredFields as $field) {
            if (!empty($this->$field)) {
                $completedFields++;
            }
        }

        if ($this->email_verified_at) $completedFields++;
        if ($this->identity_verified) $completedFields++;

        return (int) (($completedFields / (count($requiredFields) + 2)) * 100);
    }

    /**
     * Get user's preferred currency
     */
    public function getPreferredCurrencyAttribute(): string
    {
        return $this->preferences['currency'] ?? 'SAR';
    }

    /**
     * Update device information for mobile app
     */
    public function updateDeviceInfo(array $deviceInfo): void
    {
        $this->update([
            'device_info' => array_merge($this->device_info ?? [], $deviceInfo),
            'fcm_token' => $deviceInfo['fcm_token'] ?? $this->fcm_token,
            'apns_token' => $deviceInfo['apns_token'] ?? $this->apns_token
        ]);
    }

    /**
     * Send push notification to user
     */
    public function sendPushNotification(string $title, string $body, array $data = []): bool
    {
        if ($this->fcm_token || $this->apns_token) {
            return app('push.notification')->send($this, $title, $body, $data);
        }
        return false;
    }

    /**
     * Get user's active Sara conversation
     */
    public function getActiveSaraConversation(): ?SaraConversation
    {
        return $this->saraConversations()
            ->where('status', 'active')
            ->latest()
            ->first();
    }

    /**
     * Create or get Sara conversation
     */
    public function getOrCreateSaraConversation(string $channel = 'web'): SaraConversation
    {
        $conversation = $this->getActiveSaraConversation();

        if (!$conversation || $conversation->last_activity_at->lt(now()->subHours(2))) {
            $conversation = $this->saraConversations()->create([
                'session_id' => uniqid('sara_'),
                'channel' => $channel,
                'context' => [
                    'user_name' => $this->name,
                    'user_preferences' => $this->preferences,
                    'user_language' => $this->language,
                    'user_role' => $this->role
                ],
                'last_activity_at' => now()
            ]);
        }

        return $conversation;
    }

    /**
     * Get personalized recommendations using AI
     */
    public function getPersonalizedRecommendations(int $limit = 6): \Illuminate\Support\Collection
    {
        return cache()->remember(
            "user_recommendations_{$this->id}",
            now()->addHours(6),
            function () use ($limit) {
                return app('ai.recommendation')->getForUser($this, $limit);
            }
        );
    }

    /**
     * Calculate user trust score
     */
    public function getTrustScoreAttribute(): float
    {
        $score = 0;

        // Email verification (20%)
        if ($this->email_verified_at) $score += 20;

        // Identity verification (30%)
        if ($this->identity_verified) $score += 30;

        // Profile completion (20%)
        $score += ($this->profile_completion * 0.2);

        // Reviews and ratings (20%)
        if ($this->isHost()) {
            $score += min(20, ($this->host_rating ?? 0) * 4);
        } else {
            $score += min(20, ($this->guest_rating ?? 0) * 4);
        }

        // Account age (10%)
        $accountAge = $this->created_at->diffInDays(now());
        $score += min(10, $accountAge / 36.5); // Max at 1 year

        return round($score, 1);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for verified users
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at')
                    ->where('identity_verified', true);
    }

    /**
     * Export user data for GDPR compliance
     */
    public function exportPersonalData(): array
    {
        return [
            'profile' => $this->only([
                'name', 'email', 'phone', 'bio', 'language',
                'created_at', 'email_verified_at'
            ]),
            'preferences' => $this->preferences,
            'bookings' => $this->bookings()->get(['id', 'property_id', 'check_in', 'check_out', 'total_amount']),
            'reviews' => $this->reviewsGiven()->get(['id', 'property_id', 'rating', 'comment']),
            'messages' => $this->sentMessages()->get(['id', 'receiver_id', 'message', 'created_at'])
        ];
    }
}

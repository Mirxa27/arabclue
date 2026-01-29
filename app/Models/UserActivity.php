<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * User Activity Log Model
 * 
 * Tracks user interactions, preferences, and booking patterns
 * to facilitate personalized recommendations and improved user experience
 * 
 * @property int $id
 * @property int $user_id
 * @property string $activity_type
 * @property string $description
 * @property array $metadata
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class UserActivity extends Model
{
    use SoftDeletes;

    protected $table = 'user_activities';

    protected $fillable = [
        'user_id',
        'activity_type',
        'description',
        'metadata',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    /**
     * Activity types constants
     */
    const SEARCH = 'search';
    const PROPERTY_VIEW = 'property_view';
    const BOOKING = 'booking';
    const WISHLIST = 'wishlist';
    const REVIEW = 'review';
    const ACCOUNT = 'account';
    const LOGIN = 'login';
    const PAYMENT = 'payment';
    const COMMUNICATION = 'communication';
    const USER_PREFERENCE = 'preference';

    /**
     * The user who performed this activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create activity record with current request data
     */
    public static function logActivity(
        int $userId, 
        string $activityType, 
        string $description, 
        array $metadata = []
    ): self {
        $request = request();
        
        return self::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User Preferences Model
 * 
 * Stores detailed user preferences that impact search results, 
 * recommendations, and overall user experience
 * 
 * @property int $id
 * @property int $user_id
 * @property string $category
 * @property string $key
 * @property mixed $value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'key',
        'value'
    ];

    protected $casts = [
        'value' => 'json'
    ];

    /**
     * The user who owns this preference
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Predefined preference categories
     */
    public static function categories(): array
    {
        return [
            'appearance',
            'notifications',
            'search',
            'currency',
            'accessibility',
            'privacy',
            'language',
            'communication',
            'payment',
            'booking',
            'hosting',
            'travel'
        ];
    }
}

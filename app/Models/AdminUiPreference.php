<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminUiPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'theme',
        'layout_density',
        'sidebar_behavior',
        'notification_preferences',
        // Add other preference fields as needed
    ];

    protected $casts = [
        'notification_preferences' => 'array',
    ];

    /**
     * Get the user that owns the admin UI preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'property_id',
        'user_id',
        'host_id',
        'rating',
        'cleanliness_rating',
        'communication_rating',
        'checkin_rating',
        'accuracy_rating',
        'location_rating',
        'value_rating',
        'comment',
        'highlighted_amenities',
        'improvement_suggestions',
        'sentiment_analysis',
        'sentiment_score',
        'extracted_keywords',
        'host_response',
        'host_responded_at',
        'is_verified',
        'is_featured',
        'is_hidden',
        'moderation_notes'
    ];

    protected $casts = [
        'rating' => 'float',
        'sentiment_score' => 'float',
        'highlighted_amenities' => 'array',
        'improvement_suggestions' => 'array',
        'sentiment_analysis' => 'array',
        'extracted_keywords' => 'array',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'is_hidden' => 'boolean',
        'host_responded_at' => 'datetime'
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false)
                    ->where('is_verified', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getOverallRatingAttribute(): float
    {
        $ratings = array_filter([
            $this->cleanliness_rating,
            $this->communication_rating,
            $this->checkin_rating,
            $this->accuracy_rating,
            $this->location_rating,
            $this->value_rating
        ]);

        return count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 1) : $this->rating;
    }
}
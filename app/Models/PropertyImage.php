<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'image_path',
        'thumbnail_path',
        'caption',
        'ai_tags',
        'ai_quality_score',
        'is_primary',
        'is_verified',
        'sort_order',
        'width',
        'height',
        'file_size'
    ];

    protected $casts = [
        'ai_tags' => 'array',
        'ai_quality_score' => 'array',
        'is_primary' => 'boolean',
        'is_verified' => 'boolean'
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail_path 
            ? asset('storage/' . $this->thumbnail_path)
            : $this->image_url;
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }
}
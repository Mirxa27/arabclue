<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * WishlistCollection Model - Groups of wishlisted properties
 * 
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property bool $is_private
 * @property Carbon\Carbon $created_at
 * @property Carbon\Carbon $updated_at
 * @property Carbon\Carbon|null $deleted_at
 */
class WishlistCollection extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'is_private',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_private' => 'boolean',
    ];

    /**
     * Get the user that owns this wishlist collection.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the wishlisted properties in this collection.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'collection_id');
    }

    /**
     * Get the count of items in this collection.
     */
    public function getItemCountAttribute(): int
    {
        return $this->wishlists()->count();
    }
    
    /**
     * Get featured image for the collection (the first property image)
     */
    public function getFeaturedImageAttribute()
    {
        $firstWishlist = $this->wishlists()->with('property.primaryImage')->first();
        
        if ($firstWishlist && $firstWishlist->property && $firstWishlist->property->primaryImage) {
            return $firstWishlist->property->primaryImage->image_url;
        }
        
        return null;
    }
}

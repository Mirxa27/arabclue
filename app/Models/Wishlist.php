<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Wishlist Model - User Property Wishlist Management
 * 
 * Manages user wishlists for saving favorite properties
 * 
 * @property int $id
 * @property int $user_id
 * @property int $property_id
 * @property int|null $collection_id
 * @property string|null $note
 * @property array|null $tags
 * @property bool $is_private
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Wishlist extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'user_id',
        'property_id',
        'collection_id',
        'note',
        'tags',
        'is_private'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'tags' => 'array',
        'is_private' => 'boolean'
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'is_private' => false
    ];
    
    /**
     * Get the user that owns this wishlist item
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the property that is wishlisted
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
    
    /**
     * Get the collection this wishlist belongs to
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(WishlistCollection::class, 'collection_id');
    }

    /**
     * Scope for public wishlists
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * Scope for private wishlists
     */
    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    /**
     * Scope for user's wishlists
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for property wishlists
     */
    public function scopeForProperty($query, int $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    /**
     * Add property to user's wishlist
     */
    public static function addToWishlist(int $userId, int $propertyId, array $options = []): self
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'property_id' => $propertyId
            ],
            [
                'note' => $options['note'] ?? null,
                'tags' => $options['tags'] ?? [],
                'is_private' => $options['is_private'] ?? false
            ]
        );
    }

    /**
     * Remove property from user's wishlist
     */
    public static function removeFromWishlist(int $userId, int $propertyId): bool
    {
        return static::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->delete();
    }

    /**
     * Check if property is in user's wishlist
     */
    public static function isInWishlist(int $userId, int $propertyId): bool
    {
        return static::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->exists();
    }

    /**
     * Get user's wishlist count
     */
    public static function getCountForUser(int $userId): int
    {
        return static::where('user_id', $userId)->count();
    }

    /**
     * Get popular properties from wishlists
     */
    public static function getPopularProperties(int $limit = 10): \Illuminate\Support\Collection
    {
        return static::select('property_id')
            ->selectRaw('COUNT(*) as wishlist_count')
            ->groupBy('property_id')
            ->orderByDesc('wishlist_count')
            ->limit($limit)
            ->with('property')
            ->get();
    }

    /**
     * Get wishlist statistics for user
     */
    public static function getStatsForUser(int $userId): array
    {
        $wishlists = static::where('user_id', $userId)->get();
        
        return [
            'total_properties' => $wishlists->count(),
            'private_count' => $wishlists->where('is_private', true)->count(),
            'public_count' => $wishlists->where('is_private', false)->count(),
            'tagged_count' => $wishlists->filter(fn($w) => !empty($w->tags))->count(),
            'most_used_tags' => $wishlists->pluck('tags')
                ->flatten()
                ->countBy()
                ->sortDesc()
                ->take(5)
                ->toArray()
        ];
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\HasSlug;
use App\Traits\HasGeolocation;
use App\Traits\HasDynamicPricing;
use App\Traits\HasAIOptimization;
use App\Traits\Searchable;
use App\Services\AI\AIService;

/**
 * Property Model - Advanced Real Estate Management
 * 
 * Implements comprehensive property management with geospatial features,
 * dynamic pricing, AI optimization, and multi-channel distribution
 * 
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property string $property_type
 * @property string $room_type
 * @property int $accommodates
 * @property int $bedrooms
 * @property int $beds
 * @property float $bathrooms
 * @property int|null $square_meters
 * @property float $price_per_night
 * @property float $cleaning_fee
 * @property float $service_fee_percentage
 * @property array|null $seasonal_pricing
 * @property array|null $length_of_stay_pricing
 * @property array|null $special_offers
 * @property string $address
 * @property string|null $address_line_2
 * @property string $city
 * @property string|null $state
 * @property string $country
 * @property string|null $postal_code
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $neighborhood
 * @property string $check_in_time
 * @property string $check_out_time
 * @property array|null $house_rules
 * @property string $cancellation_policy
 * @property bool $instant_booking
 * @property int $minimum_nights
 * @property int $maximum_nights
 * @property array|null $ai_generated_description
 * @property array|null $ai_suggested_amenities
 * @property float|null $ai_pricing_suggestion
 * @property bool $smart_pricing_enabled
 * @property bool $is_featured
 * @property string $status
 * @property \Carbon\Carbon|null $approved_at
 * @property int|null $approved_by
 * @property int $views
 * @property int $saves
 * @property int $shares
 * @property float|null $overall_rating
 * @property int $review_count
 * @property float|null $occupancy_rate
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property array|null $meta_keywords
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Property extends Model
{
    use HasFactory, SoftDeletes;
    use HasSlug, HasGeolocation, HasDynamicPricing, HasAIOptimization, Searchable;

    /**
     * Property types enumeration
     */
    const TYPE_HOUSE = 'house';
    const TYPE_APARTMENT = 'apartment';
    const TYPE_VILLA = 'villa';
    const TYPE_STUDIO = 'studio';
    const TYPE_ROOM = 'room';

    /**
     * Room types enumeration
     */
    const ROOM_ENTIRE_PLACE = 'entire_place';
    const ROOM_PRIVATE = 'private_room';
    const ROOM_SHARED = 'shared_room';

    /**
     * Property status enumeration
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'user_id', 'title', 'slug', 'description',
        'property_type', 'room_type', 'accommodates',
        'bedrooms', 'beds', 'bathrooms', 'square_meters',
        'price_per_night', 'cleaning_fee', 'service_fee_percentage',
        'seasonal_pricing', 'length_of_stay_pricing', 'special_offers',
        'address', 'address_line_2', 'city', 'state', 'country', 
        'postal_code', 'latitude', 'longitude', 'neighborhood',
        'check_in_time', 'check_out_time', 'house_rules',
        'cancellation_policy', 'instant_booking', 'minimum_nights', 'maximum_nights',
        'ai_generated_description', 'ai_suggested_amenities', 'ai_pricing_suggestion',
        'smart_pricing_enabled', 'is_featured', 'status',
        'meta_title', 'meta_description', 'meta_keywords'
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'seasonal_pricing' => 'array',
        'length_of_stay_pricing' => 'array',
        'special_offers' => 'array',
        'house_rules' => 'array',
        'ai_generated_description' => 'array',
        'ai_suggested_amenities' => 'array',
        'meta_keywords' => 'array',
        'instant_booking' => 'boolean',
        'smart_pricing_enabled' => 'boolean',
        'is_featured' => 'boolean',
        'bathrooms' => 'float',
        'price_per_night' => 'float',
        'cleaning_fee' => 'float',
        'service_fee_percentage' => 'float',
        'ai_pricing_suggestion' => 'float',
        'overall_rating' => 'float',
        'occupancy_rate' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'approved_at' => 'datetime'
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'property_type' => self::TYPE_APARTMENT,
        'room_type' => self::ROOM_ENTIRE_PLACE,
        'accommodates' => 2,
        'bedrooms' => 1,
        'beds' => 1,
        'bathrooms' => 1.0,
        'cleaning_fee' => 0,
        'service_fee_percentage' => 10.00,
        'check_in_time' => '15:00:00',
        'check_out_time' => '11:00:00',
        'cancellation_policy' => 'flexible',
        'instant_booking' => false,
        'minimum_nights' => 1,
        'maximum_nights' => 365,
        'smart_pricing_enabled' => false,
        'is_featured' => false,
        'status' => self::STATUS_DRAFT,
        'views' => 0,
        'saves' => 0,
        'shares' => 0,
        'review_count' => 0,
        'country' => 'Saudi Arabia'
    ];

    /**
     * Searchable fields for full-text search
     */
    protected $searchable = [
        'title',
        'description',
        'city',
        'neighborhood',
        'meta_description'
    ];

    /**
     * Model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($property) {
            if (empty($property->slug)) {
                $property->slug = Str::slug($property->title);
            }
            
            // Ensure unique slug
            $originalSlug = $property->slug;
            $count = 1;
            while (static::where('slug', $property->slug)->exists()) {
                $property->slug = $originalSlug . '-' . $count++;
            }
        });

        static::saving(function ($property) {
            // Update meta title if not set
            if (empty($property->meta_title)) {
                $property->meta_title = $property->title . ' - ' . $property->city;
            }
            
            // Update meta description if not set
            if (empty($property->meta_description)) {
                $property->meta_description = Str::limit(strip_tags($property->description), 155);
            }
            
            // Calculate occupancy rate if has bookings
            if ($property->exists && $property->bookings()->exists()) {
                $property->occupancy_rate = $property->calculateOccupancyRate();
            }
        });

        static::saved(function ($property) {
            // Clear property-related caches
            cache()->forget("property_{$property->id}");
            cache()->forget("property_slug_{$property->slug}");
            cache()->tags(['properties'])->flush();
        });
    }

    /**
     * Property owner relationship
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Property approver relationship
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Property bookings
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Property reviews
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get channels connected to this property
     */
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'channel_property')
            ->withPivot(['external_id', 'sync_status', 'last_synced_at'])
            ->withTimestamps();
    }

    /**
     * Property images
     */
    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }

    /**
     * Primary image
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(PropertyImage::class)->where('is_primary', true);
    }

    /**
     * Property amenities
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'property_amenities');
    }

    /**
     * Property calendar entries
     */
    public function calendar(): HasMany
    {
        return $this->hasMany(PropertyCalendar::class);
    }

    /**
     * Wishlisted by users
     */
    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }

    /**
     * Messages about this property
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get primary image URL
     */
    public function getPrimaryImageUrlAttribute(): string
    {
        if ($this->primaryImage) {
            return asset('storage/' . $this->primaryImage->image_path);
        }
        
        // Return placeholder image
        return asset('assets/images/property-placeholder.jpg');
    }

    /**
     * Get all image URLs
     */
    public function getImageUrlsAttribute(): array
    {
        return $this->images->map(function ($image) {
            return asset('storage/' . $image->image_path);
        })->toArray();
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price_per_night, 0) . ' SAR';
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->address_line_2,
            $this->neighborhood,
            $this->city,
            $this->state,
            $this->country
        ]);
        
        return implode(', ', $parts);
    }

    public function getCityImageUrl(string $city, string $country): string
    {
        // This would typically come from a city images database or service
        $cityImages = [
            'Riyadh' => '/assets/images/cities/riyadh.jpg',
            'Jeddah' => '/assets/images/cities/jeddah.jpg',
            'Al Khobar' => '/assets/images/cities/khobar.jpg',
            'Dammam' => '/assets/images/cities/dammam.jpg',
            'Mecca' => '/assets/images/cities/mecca.jpg',
            'Medina' => '/assets/images/cities/medina.jpg'
        ];

        return $cityImages[$city] ?? '/assets/images/cities/default.jpg';
    }

    /**
     * Check if property is available for dates
     */
    public function isAvailable(string $checkIn, string $checkOut): bool
    {
        return !$this->bookings()
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                          ->where('check_out', '>=', $checkOut);
                    });
            })
            ->whereIn('status', ['accepted', 'completed'])
            ->exists();
    }

    /**
     * Get unavailable dates for calendar
     */
    public function getUnavailableDates(): array
    {
        $dates = [];
        
        $bookings = $this->bookings()
            ->whereIn('status', ['accepted', 'completed'])
            ->where('check_out', '>=', now())
            ->get(['check_in', 'check_out']);
        
        foreach ($bookings as $booking) {
            $start = \Carbon\Carbon::parse($booking->check_in);
            $end = \Carbon\Carbon::parse($booking->check_out);
            
            while ($start->lte($end)) {
                $dates[] = $start->format('Y-m-d');
                $start->addDay();
            }
        }
        
        return array_unique($dates);
    }

    /**
     * Calculate dynamic price for specific date
     */
    public function calculatePriceForDate(string $date): float
    {
        $basePrice = $this->price_per_night;
        
        if (!$this->smart_pricing_enabled) {
            return $basePrice;
        }
        
        // Apply seasonal pricing
        if ($this->seasonal_pricing) {
            foreach ($this->seasonal_pricing as $season) {
                if ($date >= $season['start'] && $date <= $season['end']) {
                    if (isset($season['percentage'])) {
                        return $basePrice * (1 + $season['percentage'] / 100);
                    } elseif (isset($season['fixed_price'])) {
                        return $season['fixed_price'];
                    }
                }
            }
        }
        
        // Apply day of week pricing
        $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek;
        if (in_array($dayOfWeek, [5, 6])) { // Friday, Saturday
            return $basePrice * 1.2; // 20% weekend markup
        }
        
        // Apply demand-based pricing using AI
        if ($this->ai_pricing_suggestion) {
            return $this->ai_pricing_suggestion;
        }
        
        return $basePrice;
    }

    /**
     * Calculate total price for stay
     */
    public function calculateTotalPrice(string $checkIn, string $checkOut, int $guests = 2): array
    {
        $start = \Carbon\Carbon::parse($checkIn);
        $end = \Carbon\Carbon::parse($checkOut);
        $nights = $start->diffInDays($end);
        
        // Calculate accommodation cost
        $accommodationTotal = 0;
        $currentDate = $start->copy();
        
        while ($currentDate->lt($end)) {
            $accommodationTotal += $this->calculatePriceForDate($currentDate->format('Y-m-d'));
            $currentDate->addDay();
        }
        
        // Apply length of stay discount
        if ($this->length_of_stay_pricing) {
            foreach ($this->length_of_stay_pricing as $discount) {
                if ($nights >= $discount['min_nights']) {
                    $accommodationTotal *= (1 - $discount['percentage'] / 100);
                    break;
                }
            }
        }
        
        // Calculate fees
        $cleaningFee = $this->cleaning_fee;
        $serviceFee = $accommodationTotal * ($this->service_fee_percentage / 100);
        $hostServiceFee = $accommodationTotal * 0.03; // 3% host fee
        $taxes = ($accommodationTotal + $cleaningFee + $serviceFee) * 0.15; // 15% VAT
        
        $totalAmount = $accommodationTotal + $cleaningFee + $serviceFee + $taxes;
        
        return [
            'nights' => $nights,
            'price_per_night' => $accommodationTotal / $nights,
            'accommodation_total' => round($accommodationTotal, 2),
            'cleaning_fee' => round($cleaningFee, 2),
            'service_fee' => round($serviceFee, 2),
            'host_service_fee' => round($hostServiceFee, 2),
            'tax_amount' => round($taxes, 2),
            'total_amount' => round($totalAmount, 2),
            'currency' => 'SAR'
        ];
    }

    /**
     * Calculate occupancy rate
     */
    public function calculateOccupancyRate(int $days = 30): float
    {
        $endDate = now();
        $startDate = now()->subDays($days);
        
        $bookedNights = $this->bookings()
            ->whereIn('status', ['completed'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('check_in', [$startDate, $endDate])
                    ->orWhereBetween('check_out', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('check_in', '<=', $startDate)
                          ->where('check_out', '>=', $endDate);
                    });
            })
            ->get()
            ->sum(function ($booking) use ($startDate, $endDate) {
                $checkIn = max($booking->check_in, $startDate);
                $checkOut = min($booking->check_out, $endDate);
                return $checkIn->diffInDays($checkOut);
            });
        
        return round(($bookedNights / $days) * 100, 2);
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        $thirtyDaysAgo = now()->subDays(30);
        
        return [
            'views' => $this->views,
            'saves' => $this->saves,
            'conversion_rate' => $this->views > 0 
                ? round(($this->bookings()->count() / $this->views) * 100, 2) 
                : 0,
            'occupancy_rate' => $this->occupancy_rate ?? $this->calculateOccupancyRate(),
            'average_rating' => $this->overall_rating ?? 0,
            'total_reviews' => $this->review_count,
            'revenue_30_days' => $this->bookings()
                ->where('status', 'completed')
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->sum('total_amount'),
            'upcoming_bookings' => $this->bookings()
                ->whereIn('status', ['accepted'])
                ->where('check_in', '>', now())
                ->count()
        ];
    }

    /**
     * Update property with AI optimization
     */
    public function optimizeWithAI(): void
    {
        $aiService = app(AIService::class);
        
        // Generate optimized description
        if (empty($this->ai_generated_description)) {
            $response = $aiService->generateContent('property_description', [
                'property_type' => $this->property_type,
                'location' => $this->city,
                'amenities' => $this->amenities->pluck('name')->toArray()
            ], ['property' => $this->toArray()]);
            
            $this->ai_generated_description = $response['content'];
        }
        
        // Get pricing suggestion
        $pricingResponse = $aiService->generateContent('pricing_suggestion', [
            'property_type' => $this->property_type,
            'location' => $this->city,
            'bedrooms' => $this->bedrooms,
            'amenities_count' => $this->amenities->count(),
            'neighborhood_average' => $this->getNeighborhoodAveragePrice()
        ]);
        
        $this->ai_pricing_suggestion = $pricingResponse['content']['suggested_price'] ?? $this->price_per_night;
        
        // Get amenity suggestions
        $amenityResponse = $aiService->generateContent('amenity_suggestions', [
            'property_type' => $this->property_type,
            'target_market' => 'premium',
            'price_range' => $this->price_per_night,
            'current_amenities' => $this->amenities->pluck('name')->toArray()
        ]);
        
        $this->ai_suggested_amenities = $amenityResponse['content']['recommended'] ?? [];
        
        $this->save();
    }

    /**
     * Get neighborhood average price
     */
    protected function getNeighborhoodAveragePrice(): float
    {
        return static::where('city', $this->city)
            ->where('neighborhood', $this->neighborhood)
            ->where('property_type', $this->property_type)
            ->where('status', self::STATUS_ACTIVE)
            ->avg('price_per_night') ?? $this->price_per_night;
    }

    /**
     * Scope for active properties
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for featured properties
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for properties in a city
     */
    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope for available properties
     */
    public function scopeAvailableBetween($query, string $checkIn, string $checkOut)
    {
        return $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
            $q->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                          ->where('check_out', '>=', $checkOut);
                    });
            })->whereIn('status', ['accepted', 'completed']);
        });
    }

    /**
     * Get similar properties
     */
    public function getSimilarProperties(int $limit = 6): \Illuminate\Support\Collection
    {
        return static::where('id', '!=', $this->id)
            ->where('city', $this->city)
            ->where('property_type', $this->property_type)
            ->where('status', self::STATUS_ACTIVE)
            ->whereBetween('price_per_night', [
                $this->price_per_night * 0.7,
                $this->price_per_night * 1.3
            ])
            ->orderByRaw('ABS(accommodates - ?) ASC', [$this->accommodates])
            ->limit($limit)
            ->get();
    }

    /**
     * Increment view count
     */
    public function incrementViews(): void
    {
        $this->increment('views');
        
        // Track unique views in Redis/Cache
        $ip = request()->ip();
        $cacheKey = "property_view_{$this->id}_{$ip}";
        
        if (!cache()->has($cacheKey)) {
            cache()->put($cacheKey, true, now()->addHours(24));
        }
    }

    /**
     * Check if user can instant book
     */
    public function canInstantBook(User $user): bool
    {
        if (!$this->instant_booking) {
            return false;
        }
        
        // Check user verification status
        if (!$user->email_verified_at) {
            return false;
        }
        
        // Check user rating if has previous bookings
        if ($user->bookings()->completed()->exists() && $user->guest_rating < 4.0) {
            return false;
        }
        
        return true;
    }

    /**
     * Get SEO data
     */
    public function getSeoData(): array
    {
        return [
            'title' => $this->meta_title,
            'description' => $this->meta_description,
            'keywords' => implode(', ', $this->meta_keywords ?? []),
            'og:title' => $this->title,
            'og:description' => $this->meta_description,
            'og:image' => $this->primary_image_url,
            'og:type' => 'website',
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $this->title,
            'twitter:description' => $this->meta_description,
            'twitter:image' => $this->primary_image_url,
            'schema' => $this->getSchemaMarkup()
        ];
    }

    /**
     * Get schema.org markup
     */
    protected function getSchemaMarkup(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LodgingBusiness',
            'name' => $this->title,
            'description' => $this->description,
            'image' => $this->image_urls,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $this->address,
                'addressLocality' => $this->city,
                'addressRegion' => $this->state,
                'postalCode' => $this->postal_code,
                'addressCountry' => $this->country
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $this->overall_rating,
                'reviewCount' => $this->review_count
            ],
            'priceRange' => $this->formatted_price
        ];
    }
}

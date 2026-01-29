<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class PropertyService
{
    public function getFeaturedProperties(string $city = 'all', int $limit = 10)
    {
        $cacheKey = 'api_featured_properties_' . $city . '_limit_' . $limit;

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($city, $limit) {
            $query = Property::with(['primaryImage', 'amenities'])
                ->active()
                ->featured()
                ->withAvg('reviews', 'rating')
                ->withCount('reviews');

            if ($city !== 'all') {
                $query->inCity($city);
            }

            return $query->limit($limit)->get();
        });
    }

    public function getTwoFeaturedPropertiesForSara(string $city = 'all')
    {
        // Specifically for Sara, we fetch 2 properties.
        // We can use the more generic getFeaturedProperties method with a limit of 2.
        // This also benefits from the same caching logic if the city and limit match.
        return $this->getFeaturedProperties($city, 2);
    }

    public function getPropertyBySlug(string $slug)
    {
        return Cache::remember("api_property_{$slug}", now()->addMinutes(30), function () use ($slug) {
            return Property::with([
                'owner:id,name,avatar,host_rating,created_at',
                'images',
                'amenities',
                'reviews' => function ($query) {
                    $query->latest()->limit(5);
                },
                'reviews.user:id,name,avatar'
            ])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();
        });
    }

    public function getSimilarProperties(Property $property, int $limit = 4)
    {
        return $property->getSimilarProperties($limit);
    }

    public function isWishlisted(Property $property): bool
    {
        if (Auth::guard('sanctum')->check()) {
            return Wishlist::where('user_id', Auth::guard('sanctum')->id())
                ->where('property_id', $property->id)
                ->exists();
        }

        return false;
    }

    public function getAvailability(Property $property, string $startDate, string $endDate): array
    {
        $availability = [];
        $current = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        while ($current->lte($end)) {
            $date = $current->format('Y-m-d');
            $isAvailable = $property->isAvailable($date, $current->copy()->addDay()->format('Y-m-d'));
            $price = $property->calculatePriceForDate($date);

            $availability[] = [
                'date' => $date,
                'available' => $isAvailable,
                'price' => $price,
                'is_weekend' => in_array($current->dayOfWeek, [5, 6])
            ];

            $current->addDay();
        }

        return $availability;
    }

    public function getReviews(Property $property, int $perPage = 10)
    {
        return $property->reviews()
            ->with('user:id,name,avatar')
            ->latest()
            ->paginate($perPage);
    }

    public function getReviewStats(Property $property): array
    {
        return [
            'average_rating' => $property->overall_rating,
            'total_reviews' => $property->review_count,
            'rating_breakdown' => $property->reviews()
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray()
        ];
    }

    public function getNearbyProperties(Property $property, int $radius = 5, int $limit = 6)
    {
        if (!$property->latitude || !$property->longitude) {
            return collect();
        }

        return Property::active()
            ->where('id', '!=', $property->id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw("*, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance", [$property->latitude, $property->longitude, $property->latitude])
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->limit($limit)
            ->get();
    }

    /**
     * Create a new property.
     *
     * @param \App\Models\User $user
     * @param array $data
     * @return Property
     */
    public function createProperty(\App\Models\User $user, array $data): Property
    {
        // Basic implementation, assuming $data is validated
        // In a real scenario, this would involve more complex logic,
        // potentially including image handling, amenities, etc.,
        // similar to what's in HostController::createProperty
        $data['user_id'] = $user->id;
        if (isset($data['title']) && !isset($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']) . '-' . \Illuminate\Support\Str::random(6);
        }
        // Ensure all required fields for Property model are present or have defaults
        // This is a simplified placeholder
        return Property::create($data);
    }

    /**
     * Update an existing property.
     *
     * @param Property $property
     * @param array $data
     * @return Property
     */
    public function updateProperty(Property $property, array $data): Property
    {
        // Basic implementation, assuming $data is validated
        $property->update($data);
        // Handle amenities, images, etc. if necessary
        if (isset($data['amenities'])) {
            $property->amenities()->sync($data['amenities']);
        }
        return $property->fresh();
    }

    /**
     * Delete a property.
     *
     * @param Property $property
     * @return bool
     */
    public function deleteProperty(Property $property): bool
    {
        // Add any pre-deletion logic, e.g., checking for active bookings
        // This is a simplified placeholder
        return $property->delete();
    }
    public function updatePriceOptimizationSettings(Property $property, bool $enabled, ?float $minPrice, ?float $maxPrice, ?string $strategy): bool
    {
        $settings = [
            'enabled' => $enabled,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'strategy' => $strategy,
        ];

        return $property->update(['price_optimization_settings' => $settings]);
    }
}

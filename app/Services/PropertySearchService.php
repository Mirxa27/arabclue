<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Amenity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PropertySearchService
{
    protected int $defaultPerPage = 20;
    protected int $maxPerPage = 100;

    public function search(array $criteria): LengthAwarePaginator
    {
        $query = Property::with(['primaryImage', 'amenities'])
            ->active()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        // Apply search filters
        $this->applyLocationFilter($query, $criteria);
        $this->applyDateFilter($query, $criteria);
        $this->applyCapacityFilter($query, $criteria);
        $this->applyPriceFilter($query, $criteria);
        $this->applyPropertyTypeFilter($query, $criteria);
        $this->applyAmenitiesFilter($query, $criteria);
        $this->applyInstantBookingFilter($query, $criteria);
        $this->applyTextSearch($query, $criteria);
        $this->applyLocationRadius($query, $criteria);

        // Apply sorting
        $this->applySorting($query, $criteria);

        // Paginate results
        $perPage = min($criteria['per_page'] ?? $this->defaultPerPage, $this->maxPerPage);
        
        return $query->paginate($perPage);
    }

    public function getPopularDestinations(int $limit = 10): array
    {
        return Cache::remember('popular_destinations', now()->addHours(12), function () use ($limit) {
            return Property::select('city')
                ->selectRaw('COUNT(*) as property_count')
                ->selectRaw('AVG(price_per_night) as avg_price')
                ->active()
                ->groupBy('city')
                ->orderBy('property_count', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($city) {
                    return [
                        'name' => $city->city,
                        'property_count' => $city->property_count,
                        'avg_price' => round($city->avg_price),
                        'image' => $this->getCityImage($city->city)
                    ];
                })
                ->toArray();
        });
    }

    public function getFilters(): array
    {
        return Cache::remember('search_filters', now()->addHours(6), function () {
            return [
                'property_types' => $this->getPropertyTypes(),
                'price_ranges' => $this->getPriceRanges(),
                'amenities' => $this->getPopularAmenities(),
                'cities' => $this->getPopularCities()
            ];
        });
    }

    protected function applyLocationFilter(Builder $query, array $criteria): void
    {
        if (!empty($criteria['city'])) {
            $query->where('city', 'like', '%' . $criteria['city'] . '%');
        }

        if (!empty($criteria['neighborhood'])) {
            $query->where('neighborhood', 'like', '%' . $criteria['neighborhood'] . '%');
        }
    }

    protected function applyDateFilter(Builder $query, array $criteria): void
    {
        if (!empty($criteria['check_in']) && !empty($criteria['check_out'])) {
            $query->availableBetween($criteria['check_in'], $criteria['check_out']);
        }
    }

    protected function applyCapacityFilter(Builder $query, array $criteria): void
    {
        if (!empty($criteria['guests'])) {
            $query->where('accommodates', '>=', $criteria['guests']);
        }

        if (!empty($criteria['bedrooms'])) {
            $query->where('bedrooms', '>=', $criteria['bedrooms']);
        }

        if (!empty($criteria['bathrooms'])) {
            $query->where('bathrooms', '>=', $criteria['bathrooms']);
        }
    }

    protected function applyPriceFilter(Builder $query, array $criteria): void
    {
        if (!empty($criteria['price_min']) && is_numeric($criteria['price_min'])) {
            $query->where('price_per_night', '>=', $criteria['price_min']);
        }

        if (!empty($criteria['price_max']) && is_numeric($criteria['price_max'])) {
            $query->where('price_per_night', '<=', $criteria['price_max']);
        }
        
        // Additional price-related filters
        if (!empty($criteria['weekly_discount']) && $criteria['weekly_discount'] === true) {
            $query->whereNotNull('weekly_discount_percentage')
                  ->where('weekly_discount_percentage', '>', 0);
        }
        
        if (!empty($criteria['monthly_discount']) && $criteria['monthly_discount'] === true) {
            $query->whereNotNull('monthly_discount_percentage')
                  ->where('monthly_discount_percentage', '>', 0);
        }
    }

    protected function applyPropertyTypeFilter(Builder $query, array $criteria): void
    {
        if (!empty($criteria['property_type'])) {
            $query->where('property_type', $criteria['property_type']);
        }

        if (!empty($criteria['room_type'])) {
            $query->where('room_type', $criteria['room_type']);
        }
    }

    protected function applyAmenitiesFilter(Builder $query, array $criteria): void
    {
        if (!empty($criteria['amenities']) && is_array($criteria['amenities'])) {
            $amenityIds = $criteria['amenities'];
            
            // Check for required vs. desired amenities
            if (!empty($criteria['required_amenities']) && $criteria['required_amenities'] === true) {
                // All amenities must be present (AND condition)
                foreach ($amenityIds as $amenityId) {
                    $query->whereHas('amenities', function ($q) use ($amenityId) {
                        $q->where('amenities.id', $amenityId);
                    });
                }
            } else {
                // Any of the amenities is acceptable (OR condition with count)
                $minCount = !empty($criteria['min_amenities_count']) ? $criteria['min_amenities_count'] : 1;
                $query->whereHas('amenities', function ($q) use ($amenityIds) {
                    $q->whereIn('amenities.id', $amenityIds);
                }, '>=', $minCount);
            }
        }
        
        // Filter for specific amenity categories
        if (!empty($criteria['amenity_categories']) && is_array($criteria['amenity_categories'])) {
            $categories = $criteria['amenity_categories'];
            $query->whereHas('amenities', function ($q) use ($categories) {
                $q->whereIn('category', $categories);
            });
        }
    }

    protected function applyInstantBookingFilter(Builder $query, array $criteria): void
    {
        if (isset($criteria['instant_booking']) && $criteria['instant_booking']) {
            $query->where('instant_booking', true);
        }
    }

    protected function applyTextSearch(Builder $query, array $criteria): void
    {
        if (!empty($criteria['query'])) {
            $searchTerm = $criteria['query'];
            
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('city', 'like', "%{$searchTerm}%")
                  ->orWhere('neighborhood', 'like', "%{$searchTerm}%");
            });
        }
    }
    
    /**
     * Apply filters for accessibility features
     */
    protected function applyAccessibilityFilter(Builder $query, array $criteria): void
    {
        if (!empty($criteria['accessibility_features']) && is_array($criteria['accessibility_features'])) {
            $features = $criteria['accessibility_features'];
            
            $query->where(function ($q) use ($features) {
                foreach ($features as $feature) {
                    $q->orWhereJsonContains('accessibility_features', $feature);
                }
            });
        }
    }
    
    /**
     * Apply filters for sustainability features
     */
    protected function applySustainabilityFilter(Builder $query, array $criteria): void
    {
        if (!empty($criteria['sustainability']) && $criteria['sustainability'] === true) {
            $query->where('is_eco_friendly', true);
        }
    }
    
    /**
     * Apply filters for specific property features
     */
    protected function applyFeaturesFilter(Builder $query, array $criteria): void
    {
        // Pool filter
        if (!empty($criteria['has_pool']) && $criteria['has_pool'] === true) {
            $query->whereHas('amenities', function ($q) {
                $q->where('name', 'like', '%pool%');
            });
        }
        
        // View filter
        if (!empty($criteria['view_type'])) {
            $query->where('view_type', $criteria['view_type']);
        }
        
        // Pet-friendly filter
        if (!empty($criteria['pets_allowed']) && $criteria['pets_allowed'] === true) {
            $query->where('pets_allowed', true);
        }
    }

    protected function applyLocationRadius(Builder $query, array $criteria): void
    {
        if (!empty($criteria['lat']) && !empty($criteria['lng']) && !empty($criteria['radius'])) {
            $lat = $criteria['lat'];
            $lng = $criteria['lng'];
            $radius = $criteria['radius'];

            $query->selectRaw("*, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<=', $radius)
            ->orderBy('distance');
        }
    }

    protected function applySorting(Builder $query, array $criteria): void
    {
        $sortBy = $criteria['sort_by'] ?? 'newest';

        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price_per_night', 'asc');
                break;
            
            case 'price_desc':
                $query->orderBy('price_per_night', 'desc');
                break;
            
            case 'rating':
                $query->orderBy('reviews_avg_rating', 'desc')
                      ->orderBy('reviews_count', 'desc');
                break;
            
            case 'popular':
                $query->orderBy('views', 'desc')
                      ->orderBy('saves', 'desc');
                break;
            
            case 'distance':
                // Already ordered by distance in applyLocationRadius
                if (empty($criteria['lat']) || empty($criteria['lng'])) {
                    $query->orderBy('created_at', 'desc');
                }
                break;
            
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }

    protected function getPropertyTypes(): array
    {
        return [
            ['value' => 'apartment', 'label' => 'Apartment', 'count' => $this->getTypeCount('apartment')],
            ['value' => 'house', 'label' => 'House', 'count' => $this->getTypeCount('house')],
            ['value' => 'villa', 'label' => 'Villa', 'count' => $this->getTypeCount('villa')],
            ['value' => 'studio', 'label' => 'Studio', 'count' => $this->getTypeCount('studio')],
            ['value' => 'room', 'label' => 'Room', 'count' => $this->getTypeCount('room')]
        ];
    }

    protected function getPriceRanges(): array
    {
        $stats = Property::active()
            ->selectRaw('MIN(price_per_night) as min_price, MAX(price_per_night) as max_price, AVG(price_per_night) as avg_price')
            ->first();

        return [
            ['min' => 0, 'max' => 200, 'label' => 'Under 200 SAR'],
            ['min' => 200, 'max' => 500, 'label' => '200 - 500 SAR'],
            ['min' => 500, 'max' => 1000, 'label' => '500 - 1,000 SAR'],
            ['min' => 1000, 'max' => 2000, 'label' => '1,000 - 2,000 SAR'],
            ['min' => 2000, 'max' => null, 'label' => '2,000+ SAR'],
            ['min' => $stats->min_price ?? 0, 'max' => $stats->max_price ?? 5000, 'avg' => $stats->avg_price ?? 500]
        ];
    }

    protected function getPopularAmenities(): array
    {
        return Amenity::popular()
            ->withCount('properties')
            ->orderBy('properties_count', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($amenity) {
                return [
                    'id' => $amenity->id,
                    'name' => $amenity->name,
                    'icon' => $amenity->icon,
                    'category' => $amenity->category,
                    'count' => $amenity->properties_count
                ];
            })
            ->toArray();
    }

    protected function getPopularCities(): array
    {
        return Property::select('city')
            ->selectRaw('COUNT(*) as count')
            ->active()
            ->groupBy('city')
            ->orderBy('count', 'desc')
            ->limit(15)
            ->pluck('count', 'city')
            ->map(function ($count, $city) {
                return [
                    'name' => $city,
                    'count' => $count
                ];
            })
            ->values()
            ->toArray();
    }

    protected function getTypeCount(string $type): int
    {
        return Property::active()->where('property_type', $type)->count();
    }

    protected function getCityImage(string $city): string
    {
        $cityImages = [
            'Riyadh' => 'cities/riyadh.jpg',
            'Jeddah' => 'cities/jeddah.jpg',
            'Dammam' => 'cities/dammam.jpg',
            'Mecca' => 'cities/mecca.jpg',
            'Medina' => 'cities/medina.jpg',
            'Taif' => 'cities/taif.jpg',
            'Abha' => 'cities/abha.jpg',
            'Khobar' => 'cities/khobar.jpg'
        ];

        return asset('storage/' . ($cityImages[$city] ?? 'cities/default.jpg'));
    }

    public function getSimilarProperties(Property $property, int $limit = 6): \Illuminate\Support\Collection
    {
        return Property::active()
            ->where('id', '!=', $property->id)
            ->where('city', $property->city)
            ->where('property_type', $property->property_type)
            ->whereBetween('price_per_night', [
                $property->price_per_night * 0.7,
                $property->price_per_night * 1.3
            ])
            ->orderByRaw('ABS(accommodates - ?) ASC', [$property->accommodates])
            ->limit($limit)
            ->get();
    }

    public function getRecommendations(?int $userId = null, int $limit = 6): \Illuminate\Support\Collection
    {
        if ($userId) {
            return $this->getPersonalizedRecommendations($userId, $limit);
        }

        return $this->getGeneralRecommendations($limit);
    }

    protected function getPersonalizedRecommendations(int $userId, int $limit): \Illuminate\Support\Collection
    {
        // Get user's booking history and preferences
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            return $this->getGeneralRecommendations($limit);
        }

        $preferences = $user->preferences ?? [];
        $pastBookings = $user->bookings()->with('property')->completed()->latest()->limit(5)->get();

        $query = Property::active()->with(['primaryImage']);

        // Prefer similar properties to past bookings
        if ($pastBookings->isNotEmpty()) {
            $preferredCities = $pastBookings->pluck('property.city')->unique();
            $preferredTypes = $pastBookings->pluck('property.property_type')->unique();
            $avgPrice = $pastBookings->avg('property.price_per_night');

            $query->where(function ($q) use ($preferredCities, $preferredTypes, $avgPrice) {
                $q->whereIn('city', $preferredCities)
                  ->orWhereIn('property_type', $preferredTypes)
                  ->orWhereBetween('price_per_night', [$avgPrice * 0.8, $avgPrice * 1.2]);
            });
        }

        // Apply user preferences
        if (isset($preferences['preferred_price_range'])) {
            $query->whereBetween('price_per_night', $preferences['preferred_price_range']);
        }

        if (isset($preferences['preferred_amenities'])) {
            $query->whereHas('amenities', function ($q) use ($preferences) {
                $q->whereIn('amenities.id', $preferences['preferred_amenities']);
            });
        }

        return $query->featured()
                    ->orderBy('overall_rating', 'desc')
                    ->limit($limit)
                    ->get();
    }

    protected function getGeneralRecommendations(int $limit): \Illuminate\Support\Collection
    {
        return Property::active()
            ->with(['primaryImage'])
            ->featured()
            ->orderBy('overall_rating', 'desc')
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get();
    }
}

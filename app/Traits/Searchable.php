<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait Searchable
{
    /**
     * Perform full-text search on the model
     */
    public static function search(string $query, array $columns = null): Builder
    {
        $instance = new static;
        $columns = $columns ?? $instance->getSearchableColumns();
        
        return $instance->newQuery()->where(function ($queryBuilder) use ($query, $columns, $instance) {
            $keywords = $instance->parseSearchQuery($query);
            
            foreach ($keywords as $keyword) {
                $queryBuilder->where(function ($subQuery) use ($keyword, $columns) {
                    foreach ($columns as $column) {
                        $subQuery->orWhere($column, 'LIKE', "%{$keyword}%");
                    }
                });
            }
        });
    }

    /**
     * Advanced search with filters and sorting
     */
    public static function advancedSearch(array $filters): Builder
    {
        $instance = new static;
        $query = $instance->newQuery();

        // Text search
        if (!empty($filters['q'])) {
            $query = $instance->applyTextSearch($query, $filters['q']);
        }

        // Location search
        if (!empty($filters['location'])) {
            $query = $instance->applyLocationSearch($query, $filters['location']);
        }

        // Price range
        if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
            $query = $instance->applyPriceFilter($query, $filters);
        }

        // Date availability
        if (!empty($filters['check_in']) && !empty($filters['check_out'])) {
            $query = $instance->applyAvailabilityFilter($query, $filters);
        }

        // Guest count
        if (!empty($filters['guests'])) {
            $query = $instance->applyGuestFilter($query, $filters['guests']);
        }

        // Amenities
        if (!empty($filters['amenities'])) {
            $query = $instance->applyAmenityFilter($query, $filters['amenities']);
        }

        // Property type
        if (!empty($filters['property_type'])) {
            $query = $instance->applyPropertyTypeFilter($query, $filters['property_type']);
        }

        // Rating filter
        if (!empty($filters['min_rating'])) {
            $query = $instance->applyRatingFilter($query, $filters['min_rating']);
        }

        // Sorting
        if (!empty($filters['sort'])) {
            $query = $instance->applySorting($query, $filters['sort']);
        } else {
            $query = $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    /**
     * Get searchable columns for the model
     */
    protected function getSearchableColumns(): array
    {
        return property_exists($this, 'searchable') 
            ? $this->searchable 
            : ['title', 'description'];
    }

    /**
     * Parse search query into keywords
     */
    protected function parseSearchQuery(string $query): array
    {
        // Remove special characters and split by spaces
        $cleaned = preg_replace('/[^\w\s]/', ' ', $query);
        $keywords = array_filter(explode(' ', $cleaned));
        
        // Remove common stop words
        $stopWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        $keywords = array_diff($keywords, $stopWords);
        
        // Remove keywords shorter than 2 characters
        return array_filter($keywords, fn($word) => strlen($word) >= 2);
    }

    /**
     * Apply text search to query
     */
    protected function applyTextSearch(Builder $query, string $searchText): Builder
    {
        $columns = $this->getSearchableColumns();
        $keywords = $this->parseSearchQuery($searchText);
        
        return $query->where(function ($subQuery) use ($keywords, $columns) {
            foreach ($keywords as $keyword) {
                $subQuery->where(function ($keywordQuery) use ($keyword, $columns) {
                    foreach ($columns as $column) {
                        $keywordQuery->orWhere($column, 'LIKE', "%{$keyword}%");
                    }
                });
            }
        });
    }

    /**
     * Apply location search to query
     */
    protected function applyLocationSearch(Builder $query, array $location): Builder
    {
        // Search by coordinates and radius
        if (isset($location['lat'], $location['lng'], $location['radius'])) {
            return $query->whereHas('location', function ($locationQuery) use ($location) {
                $locationQuery->withinRadius(
                    $location['lat'],
                    $location['lng'],
                    $location['radius']
                );
            });
        }
        
        // Search by city/region name
        if (isset($location['city'])) {
            $query->where(function ($subQuery) use ($location) {
                $subQuery->where('city', 'LIKE', "%{$location['city']}%")
                        ->orWhere('state', 'LIKE', "%{$location['city']}%")
                        ->orWhere('country', 'LIKE', "%{$location['city']}%");
            });
        }
        
        return $query;
    }

    /**
     * Apply price filter to query
     */
    protected function applyPriceFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['price_min'])) {
            $query->where('price_per_night', '>=', $filters['price_min']);
        }
        
        if (isset($filters['price_max'])) {
            $query->where('price_per_night', '<=', $filters['price_max']);
        }
        
        return $query;
    }

    /**
     * Apply availability filter to query
     */
    protected function applyAvailabilityFilter(Builder $query, array $filters): Builder
    {
        $checkIn = $filters['check_in'];
        $checkOut = $filters['check_out'];
        
        return $query->whereDoesntHave('bookings', function ($bookingQuery) use ($checkIn, $checkOut) {
            $bookingQuery->where('status', '!=', 'cancelled')
                        ->where(function ($dateQuery) use ($checkIn, $checkOut) {
                            $dateQuery->whereBetween('check_in', [$checkIn, $checkOut])
                                     ->orWhereBetween('check_out', [$checkIn, $checkOut])
                                     ->orWhere(function ($overlapQuery) use ($checkIn, $checkOut) {
                                         $overlapQuery->where('check_in', '<=', $checkIn)
                                                     ->where('check_out', '>=', $checkOut);
                                     });
                        });
        });
    }

    /**
     * Apply guest count filter to query
     */
    protected function applyGuestFilter(Builder $query, int $guests): Builder
    {
        return $query->where('max_guests', '>=', $guests);
    }

    /**
     * Apply amenity filter to query
     */
    protected function applyAmenityFilter(Builder $query, array $amenities): Builder
    {
        return $query->whereHas('amenities', function ($amenityQuery) use ($amenities) {
            $amenityQuery->whereIn('name', $amenities);
        }, '>=', count($amenities));
    }

    /**
     * Apply property type filter to query
     */
    protected function applyPropertyTypeFilter(Builder $query, array $types): Builder
    {
        return $query->whereIn('property_type', $types);
    }

    /**
     * Apply rating filter to query
     */
    protected function applyRatingFilter(Builder $query, float $minRating): Builder
    {
        return $query->whereHas('reviews', function ($reviewQuery) use ($minRating) {
            $reviewQuery->selectRaw('AVG(rating) as avg_rating')
                       ->havingRaw('AVG(rating) >= ?', [$minRating]);
        });
    }

    /**
     * Apply sorting to query
     */
    protected function applySorting(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'price_low' => $query->orderBy('price_per_night', 'asc'),
            'price_high' => $query->orderBy('price_per_night', 'desc'),
            'rating' => $query->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating'),
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            'popular' => $query->withCount('bookings')->orderByDesc('bookings_count'),
            'distance' => $query->orderBy('distance'), // Requires location context
            default => $query->orderBy('created_at', 'desc')
        };
    }

    /**
     * Get search suggestions based on query
     */
    public static function getSearchSuggestions(string $query, int $limit = 5): array
    {
        $instance = new static;
        $columns = $instance->getSearchableColumns();
        
        $suggestions = [];
        
        foreach ($columns as $column) {
            $results = $instance->newQuery()
                ->select($column)
                ->where($column, 'LIKE', "%{$query}%")
                ->distinct()
                ->limit($limit)
                ->pluck($column)
                ->toArray();
                
            $suggestions = array_merge($suggestions, $results);
        }
        
        // Remove duplicates and limit results
        $suggestions = array_unique($suggestions);
        return array_slice($suggestions, 0, $limit);
    }

    /**
     * Get popular search terms
     */
    public static function getPopularSearchTerms(int $limit = 10): array
    {
        // This would typically come from a search analytics table
        // For now, return common property-related terms
        return [
            'apartment',
            'villa',
            'beach',
            'city center',
            'pool',
            'wifi',
            'parking',
            'kitchen',
            'balcony',
            'garden'
        ];
    }

    /**
     * Build search index for better performance
     */
    public function buildSearchIndex(): void
    {
        $searchableData = [];
        
        foreach ($this->getSearchableColumns() as $column) {
            if (!empty($this->{$column})) {
                $searchableData[] = $this->{$column};
            }
        }
        
        // Create search index entry
        $searchIndex = implode(' ', $searchableData);
        $searchIndex = strtolower($searchIndex);
        $searchIndex = preg_replace('/[^\w\s]/', ' ', $searchIndex);
        
        $this->update(['search_index' => $searchIndex]);
    }

    /**
     * Search using pre-built index
     */
    public static function indexSearch(string $query): Builder
    {
        $instance = new static;
        $keywords = $instance->parseSearchQuery($query);
        
        return $instance->newQuery()->where(function ($queryBuilder) use ($keywords) {
            foreach ($keywords as $keyword) {
                $queryBuilder->where('search_index', 'LIKE', "%{$keyword}%");
            }
        });
    }

    /**
     * Get search filters for the model
     */
    public static function getAvailableFilters(): array
    {
        return [
            'price_range' => [
                'type' => 'range',
                'min' => 'price_min',
                'max' => 'price_max',
                'label' => 'Price per night'
            ],
            'property_type' => [
                'type' => 'multi_select',
                'options' => ['apartment', 'house', 'villa', 'studio', 'loft'],
                'label' => 'Property Type'
            ],
            'amenities' => [
                'type' => 'multi_select',
                'options' => ['wifi', 'pool', 'parking', 'kitchen', 'gym'],
                'label' => 'Amenities'
            ],
            'guests' => [
                'type' => 'number',
                'min' => 1,
                'max' => 20,
                'label' => 'Number of Guests'
            ],
            'rating' => [
                'type' => 'select',
                'options' => [4.5, 4.0, 3.5, 3.0],
                'label' => 'Minimum Rating'
            ]
        ];
    }

    /**
     * Scope for search functionality
     */
    public function scopeSearch($query, string $searchTerm)
    {
        return $this->search($searchTerm);
    }

    /**
     * Scope for advanced search functionality
     */
    public function scopeAdvancedSearch($query, array $filters)
    {
        return $this->advancedSearch($filters);
    }
}

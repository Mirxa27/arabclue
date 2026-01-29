<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Amenity;
use Illuminate\Support\Facades\Cache;

class SearchService
{
    public function getSuggestions(string $query, string $type = 'all', int $limit = 10)
    {
        if (strlen($query) < 2) {
            return [];
        }

        $cacheKey = "search_suggestions_{$type}_{$query}_{$limit}";

        return Cache::remember($cacheKey, 300, function () use ($query, $type, $limit) {
            $results = [];

            if ($type === 'all' || $type === 'cities') {
                $results['cities'] = $this->getCitySuggestions($query, $limit);
            }

            if ($type === 'all' || $type === 'properties') {
                $results['properties'] = $this->getPropertySuggestions($query, $limit);
            }

            if ($type === 'all' || $type === 'amenities') {
                $results['amenities'] = $this->getAmenitySuggestions($query, $limit);
            }

            if ($type === 'all') {
                return $this->combineSuggestions($results, $limit);
            }

            return $results;
        });
    }

    public function getPopularCities(string $country = null, int $limit = 20)
    {
        $cacheKey = "popular_cities_{$country}_{$limit}";

        return Cache::remember($cacheKey, 3600, function () use ($country, $limit) {
            $query = Property::select('city', 'country')
                ->selectRaw('COUNT(*) as properties_count')
                ->selectRaw('AVG(overall_rating) as average_rating')
                ->where('status', Property::STATUS_ACTIVE)
                ->groupBy('city', 'country')
                ->orderByDesc('properties_count');

            if ($country) {
                $query->where('country', $country);
            }

            return $query->limit($limit)->get()->map(function ($city) {
                return [
                    'name' => $city->city,
                    'country' => $city->country,
                    'properties_count' => $city->properties_count,
                    'average_rating' => round($city->average_rating, 1),
                    'image_url' => $this->getCityImageUrl($city->city, $city->country)
                ];
            });
        });
    }

    public function getNeighborhoods(string $city, int $limit = 20)
    {
        $cacheKey = "neighborhoods_{$city}_{$limit}";

        return Cache::remember($cacheKey, 3600, function () use ($city, $limit) {
            return Property::select('neighborhood')
                ->selectRaw('COUNT(*) as properties_count')
                ->selectRaw('AVG(price_per_night) as average_price')
                ->selectRaw('AVG(overall_rating) as average_rating')
                ->where('city', $city)
                ->where('status', Property::STATUS_ACTIVE)
                ->whereNotNull('neighborhood')
                ->groupBy('neighborhood')
                ->orderByDesc('properties_count')
                ->limit($limit)
                ->get()
                ->map(function ($neighborhood) {
                    return [
                        'name' => $neighborhood->neighborhood,
                        'properties_count' => $neighborhood->properties_count,
                        'average_price' => round($neighborhood->average_price, 2),
                        'average_rating' => round($neighborhood->average_rating, 1)
                    ];
                });
        });
    }

    public function getPopularAmenities(string $category = null, int $limit = 50)
    {
        $cacheKey = "popular_amenities_{$category}_{$limit}";

        return Cache::remember($cacheKey, 3600, function () use ($category, $limit) {
            $query = Amenity::withCount('properties')
                ->orderByDesc('properties_count');

            if ($category) {
                $query->where('category', $category);
            }

            return $query->limit($limit)->get()->map(function ($amenity) {
                return [
                    'id' => $amenity->id,
                    'name' => $amenity->name,
                    'slug' => $amenity->slug,
                    'icon' => $amenity->icon,
                    'category' => $amenity->category,
                    'properties_count' => $amenity->properties_count
                ];
            });
        });
    }

    public function getTrendingSearches(int $limit = 10)
    {
        return Cache::remember('trending_searches', 1800, function () use ($limit) {
            // This would typically come from search analytics
            return [
                ['query' => 'Riyadh', 'count' => 1250, 'type' => 'city'],
                ['query' => 'Jeddah', 'count' => 980, 'type' => 'city'],
                ['query' => 'Al Khobar', 'count' => 750, 'type' => 'city'],
                ['query' => 'Villa', 'count' => 650, 'type' => 'property_type'],
                ['query' => 'Swimming Pool', 'count' => 580, 'type' => 'amenity'],
                ['query' => 'Apartment', 'count' => 520, 'type' => 'property_type'],
                ['query' => 'WiFi', 'count' => 480, 'type' => 'amenity'],
                ['query' => 'King Fahd District', 'count' => 420, 'type' => 'neighborhood'],
                ['query' => 'Olaya', 'count' => 380, 'type' => 'neighborhood'],
                ['query' => 'Parking', 'count' => 350, 'type' => 'amenity']
            ];
        });
    }

    protected function getCitySuggestions(string $query, int $limit): array
    {
        return Property::select('city', 'country')
            ->selectRaw('COUNT(*) as properties_count')
            ->where('city', 'LIKE', "%{$query}%")
            ->where('status', Property::STATUS_ACTIVE)
            ->groupBy('city', 'country')
            ->orderByDesc('properties_count')
            ->limit($limit)
            ->get()
            ->map(function ($city) {
                return [
                    'name' => $city->city,
                    'country' => $city->country,
                    'properties_count' => $city->properties_count
                ];
            })
            ->toArray();
    }

    protected function getPropertySuggestions(string $query, int $limit): array
    {
        return Property::select('id', 'title', 'slug', 'city', 'country', 'price_per_night')
            ->where('title', 'LIKE', "%{$query}%")
            ->where('status', Property::STATUS_ACTIVE)
            ->orderByDesc('views')
            ->limit($limit)
            ->get()
            ->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'slug' => $property->slug,
                    'city' => $property->city,
                    'country' => $property->country,
                    'formatted_price' => $property->formatted_price,
                    'primary_image_url' => $property->primary_image_url
                ];
            })
            ->toArray();
    }

    protected function getAmenitySuggestions(string $query, int $limit): array
    {
        return Amenity::select('id', 'name', 'slug', 'icon', 'category')
            ->where('name', 'LIKE', "%{$query}%")
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(function ($amenity) {
                return [
                    'id' => $amenity->id,
                    'name' => $amenity->name,
                    'slug' => $amenity->slug,
                    'icon' => $amenity->icon,
                    'category' => $amenity->category
                ];
            })
            ->toArray();
    }

    protected function combineSuggestions(array $results, int $limit): array
    {
        $combined = [];

        foreach ($results['cities'] ?? [] as $city) {
            $combined[] = [
                'type' => 'city',
                'title' => $city['name'],
                'subtitle' => $city['country'],
                'value' => $city['name'],
                'properties_count' => $city['properties_count']
            ];
        }

        foreach ($results['properties'] ?? [] as $property) {
            $combined[] = [
                'type' => 'property',
                'title' => $property['title'],
                'subtitle' => $property['city'] . ', ' . $property['country'],
                'value' => $property['slug'],
                'image' => $property['primary_image_url'],
                'price' => $property['formatted_price']
            ];
        }

        foreach ($results['amenities'] ?? [] as $amenity) {
            $combined[] = [
                'type' => 'amenity',
                'title' => $amenity['name'],
                'subtitle' => 'Amenity',
                'value' => $amenity['slug'],
                'icon' => $amenity['icon']
            ];
        }

        return array_slice($combined, 0, $limit);
    }

    protected function getCityImageUrl(string $city, string $country): string
    {
        return (new Property())->getCityImageUrl($city, $country);
    }
}

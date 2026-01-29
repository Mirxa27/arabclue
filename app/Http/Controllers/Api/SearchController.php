<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PropertySearchService;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Search API Controller - Property Search and Suggestions
 * 
 * Provides search functionality for properties, cities, and amenities
 * with intelligent suggestions and filtering capabilities
 * 
 * @package App\Http\Controllers\Api
 * @version 1.0.0
 */
class SearchController extends Controller
{
    protected PropertySearchService $propertySearchService;
    protected SearchService $searchService;

    public function __construct(PropertySearchService $propertySearchService, SearchService $searchService)
    {
        $this->propertySearchService = $propertySearchService;
        $this->searchService = $searchService;
    }

    /**
     * Get search suggestions based on query
     */
    public function suggestions(Request $request): JsonResponse
    {
        $suggestions = $this->searchService->getSuggestions(
            $request->get('q', ''),
            $request->get('type', 'all'),
            min($request->get('limit', 10), 20)
        );

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }

    /**
     * Get popular cities
     */
    public function cities(Request $request): JsonResponse
    {
        $cities = $this->searchService->getPopularCities(
            $request->get('country'),
            min($request->get('limit', 20), 50)
        );

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    /**
     * Get neighborhoods for a city
     */
    public function neighborhoods(Request $request): JsonResponse
    {
        $city = $request->get('city');
        if (!$city) {
            return response()->json(['error' => true, 'message' => 'City parameter is required'], 422);
        }

        $neighborhoods = $this->searchService->getNeighborhoods(
            $city,
            min($request->get('limit', 20), 50)
        );

        return response()->json([
            'success' => true,
            'data' => $neighborhoods
        ]);
    }

    /**
     * Get popular amenities
     */
    public function amenities(Request $request): JsonResponse
    {
        $amenities = $this->searchService->getPopularAmenities(
            $request->get('category'),
            min($request->get('limit', 50), 100)
        );

        return response()->json([
            'success' => true,
            'data' => $amenities
        ]);
    }

    /**
     * Advanced property search
     */
    public function search(Request $request, $unexpected = null): JsonResponse
    {
        if ($unexpected !== null) {
            \Illuminate\Support\Facades\Log::warning('SearchController@search called with an unexpected second parameter.', ['param' => $unexpected]);
        }
        try {
            $filters = $request->only([
                'location', 'check_in', 'check_out', 'guests',
                'property_type', 'room_type', 'price_min', 'price_max',
                'amenities', 'instant_booking', 'rating_min',
                'bedrooms', 'bathrooms', 'neighborhood'
            ]);

            $page = $request->get('page', 1);
            $perPage = min($request->get('per_page', 20), 50);
            $sortBy = $request->get('sort_by', 'relevance');

            $criteria = array_merge($filters, [
                'page' => $page,
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'user_id' => $request->user()?->id
            ]);

            $results = $this->propertySearchService->search($criteria);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trending searches
     */
    public function trending(Request $request): JsonResponse
    {
        $trending = $this->searchService->getTrendingSearches(
            min($request->get('limit', 10), 20)
        );

        return response()->json([
            'success' => true,
            'data' => $trending
        ]);
    }
}

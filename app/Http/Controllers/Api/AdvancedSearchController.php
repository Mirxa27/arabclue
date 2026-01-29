<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PropertySearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdvancedSearchController extends Controller
{
    protected PropertySearchService $searchService;

    public function __construct(PropertySearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Advanced search with multiple filter options
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        // Validate basic search parameters
        $validator = Validator::make($request->all(), [
            'check_in' => 'nullable|date|after_or_equal:today',
            'check_out' => 'nullable|date|after:check_in',
            'guests' => 'nullable|integer|min:1',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|gt:price_min',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|numeric|min:0',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Prepare search criteria
        $criteria = [
            // Location filters
            'city' => $request->input('city'),
            'neighborhood' => $request->input('neighborhood'),
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
            'radius' => $request->input('radius'),
            
            // Date and capacity filters
            'check_in' => $request->input('check_in'),
            'check_out' => $request->input('check_out'),
            'guests' => $request->input('guests'),
            'bedrooms' => $request->input('bedrooms'),
            'bathrooms' => $request->input('bathrooms'),
            
            // Price filters
            'price_min' => $request->input('price_min'),
            'price_max' => $request->input('price_max'),
            'weekly_discount' => $request->boolean('weekly_discount'),
            'monthly_discount' => $request->boolean('monthly_discount'),
            
            // Property type filters
            'property_type' => $request->input('property_type'),
            'room_type' => $request->input('room_type'),
            
            // Amenity filters
            'amenities' => $request->input('amenities'),
            'required_amenities' => $request->boolean('required_amenities'),
            'min_amenities_count' => $request->input('min_amenities_count'),
            'amenity_categories' => $request->input('amenity_categories'),
            
            // Additional feature filters
            'instant_booking' => $request->boolean('instant_booking'),
            'accessibility_features' => $request->input('accessibility_features'),
            'sustainability' => $request->boolean('sustainability'),
            'has_pool' => $request->boolean('has_pool'),
            'view_type' => $request->input('view_type'),
            'pets_allowed' => $request->boolean('pets_allowed'),
            
            // Sorting and pagination
            'sort_by' => $request->input('sort_by', 'recommended'),
            'per_page' => $request->input('per_page', 20),
            
            // Search query
            'query' => $request->input('query'),
        ];

        // Perform search with advanced filters
        $results = $this->searchService->search($criteria);

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * Get available filter options for advanced search
     *
     * @return JsonResponse
     */
    public function getFilterOptions(): JsonResponse
    {
        $filters = $this->searchService->getFilters();
        
        return response()->json([
            'success' => true,
            'data' => $filters
        ]);
    }
}

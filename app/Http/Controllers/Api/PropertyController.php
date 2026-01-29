<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Wishlist;
use App\Services\PropertyService;
use App\Services\PropertySearchService;
use App\Http\Requests\PropertySearchRequest;
use App\Http\Requests\PropertyAvailabilityRequest;
use App\Http\Requests\CalculatePriceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * API Property Controller - Mobile-Optimized Property Management
 * 
 * Provides RESTful API endpoints for property browsing, searching,
 * and management optimized for mobile app and PWA consumption
 * 
 * @package App\Http\Controllers\Api
 * @version 1.0.0
 */
class PropertyController extends Controller
{
    protected PropertyService $propertyService;
    protected PropertySearchService $searchService;

    public function __construct(PropertyService $propertyService, PropertySearchService $searchService)
    {
        $this->propertyService = $propertyService;
        $this->searchService = $searchService;
        $this->middleware('auth:sanctum')->except(['featured', 'search', 'show', 'availability', 'calculatePrice', 'reviews', 'nearby']);
    }
    
    /**
     * Get featured properties for home screen
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function featured(Request $request): JsonResponse
    {
        $properties = $this->propertyService->getFeaturedProperties($request->input('city', 'all'));

        return $this->successResponse([
            'properties' => $this->transformProperties($properties)
        ]);
    }
    
    /**
     * Search properties with filters
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function search(PropertySearchRequest $request): JsonResponse
    {
        $results = $this->searchService->search($request->validated());

        return $this->paginatedResponse($results, 'Properties retrieved successfully');
    }
    
    /**
     * Get property details
     * 
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        $property = $this->propertyService->getPropertyBySlug($slug);

        dispatch(fn () => $property->incrementViews())->afterResponse();

        $similarProperties = $this->propertyService->getSimilarProperties($property);
        $isWishlisted = $this->propertyService->isWishlisted($property);

        return $this->successResponse([
            'property' => $this->transformPropertyDetail($property, $isWishlisted),
            'similar_properties' => $this->transformProperties($similarProperties),
            'unavailable_dates' => $property->getUnavailableDates()
        ]);
    }
    
    /**
     * Get property availability calendar
     * 
     * @param Property $property
     * @param Request $request
     * @return JsonResponse
     */
    public function availability(Property $property, PropertyAvailabilityRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $availability = $this->propertyService->getAvailability($property, $validated['start_date'], $validated['end_date']);

        return $this->successResponse([
            'property_id' => $property->id,
            'availability' => $availability
        ]);
    }
    
    /**
     * Calculate booking price
     * 
     * @param Property $property
     * @param Request $request
     * @return JsonResponse
     */
    public function calculatePrice(Property $property, CalculatePriceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $pricing = $property->calculateTotalPrice(
            $validated['check_in'],
            $validated['check_out'],
            $validated['guests']
        );

        // Apply coupon if provided
        if ($validated['coupon_code'] ?? null) {
            // Coupon logic here
        }

        return $this->successResponse([
            'pricing' => $pricing,
            'available' => $property->isAvailable($validated['check_in'], $validated['check_out'])
        ]);
    }
    
    /**
     * Toggle property wishlist status
     * 
     * @param Property $property
     * @return JsonResponse
     */
    public function toggleWishlist(Property $property): JsonResponse
    {
        $user = auth('sanctum')->user();
        
        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('property_id', $property->id)
            ->first();
        
        if ($wishlist) {
            $wishlist->delete();
            $action = 'removed';
        } else {
            Wishlist::create([
                'user_id' => $user->id,
                'property_id' => $property->id
            ]);
            $action = 'added';
        }
        
        // Update property saves count
        $property->saves = Wishlist::where('property_id', $property->id)->count();
        $property->save();
        
        return $this->successResponse([
            'action' => $action,
            'is_wishlisted' => $action === 'added'
        ], "Property {$action} to wishlist");
    }
    
    /**
     * Get user's wishlist
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getWishlist(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $wishlist = Wishlist::with(['property' => function ($query) {
            $query->with(['primaryImage'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews');
        }])
        ->where('user_id', $user->id)
        ->latest()
        ->paginate($request->input('per_page', 20));

        $transformed = $wishlist->getCollection()->transform(function ($item) {
            return $this->transformProperty($item->property);
        });

        return $this->paginatedResponse($wishlist->setCollection($transformed), 'Wishlist retrieved successfully');
    }

    /**
     * Clear user's entire wishlist
     *
     * @return JsonResponse
     */
    public function clearWishlist(): JsonResponse
    {
        $user = auth('sanctum')->user();

        $deletedCount = Wishlist::where('user_id', $user->id)->delete();

        return $this->successResponse([
            'deleted_count' => $deletedCount
        ], 'Wishlist cleared successfully');
    }
    
    /**
     * Get property reviews
     * 
     * @param Property $property
     * @param Request $request
     * @return JsonResponse
     */
    public function reviews(Property $property, Request $request): JsonResponse
    {
        $reviews = $this->propertyService->getReviews($property, $request->input('per_page', 10));
        $stats = $this->propertyService->getReviewStats($property);

        return $this->successResponse([
            'stats' => $stats,
            'reviews' => $reviews->items(),
            'pagination' => [
                'total' => $reviews->total(),
                'per_page' => $reviews->perPage(),
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage()
            ]
        ]);
    }
    
    /**
     * Get nearby properties
     * 
     * @param Property $property
     * @return JsonResponse
     */
    public function nearby(Property $property): JsonResponse
    {
        $nearbyProperties = $this->propertyService->getNearbyProperties($property);

        return $this->successResponse([
            'properties' => $this->transformProperties($nearbyProperties)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Authorization handled by middleware
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'property_type_id' => 'required|exists:property_types,id',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'price_per_night' => 'required|numeric|min:0',
            'accommodates' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:0',
            'beds' => 'required|integer|min:1',
            'bathrooms' => 'required|numeric|min:0',
            'amenities' => 'array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        $property = $this->propertyService->createProperty($request->user(), $validated);

        return $this->successResponse($this->transformPropertyDetail($property), 'Property created successfully', 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property): JsonResponse
    {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'property_type_id' => 'sometimes|required|exists:property_types,id',
            'address' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'country' => 'sometimes|required|string|max:100',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'price_per_night' => 'sometimes|required|numeric|min:0',
            'accommodates' => 'sometimes|required|integer|min:1',
            'bedrooms' => 'sometimes|required|integer|min:0',
            'beds' => 'sometimes|required|integer|min:1',
            'bathrooms' => 'sometimes|required|numeric|min:0',
            'amenities' => 'sometimes|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        $property = $this->propertyService->updateProperty($property, $validated);

        return $this->successResponse($this->transformPropertyDetail($property), 'Property updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property): JsonResponse
    {
        $this->authorize('delete', $property);

        $this->propertyService->deleteProperty($property);

        return $this->successResponse(null, 'Property deleted successfully');
    }
    
    /**
     * Transform properties for API response
     * 
     * @param \Illuminate\Support\Collection $properties
     * @return array
     */
    protected function transformProperties($properties): array
    {
        return $properties->map(function ($property) {
            return $this->transformProperty($property);
        })->toArray();
    }
    
    /**
     * Transform single property for API response
     * 
     * @param Property $property
     * @return array
     */
    protected function transformProperty(Property $property): array
    {
        return [
            'id' => $property->id,
            'slug' => $property->slug,
            'title' => $property->title,
            'type' => $property->property_type,
            'image' => $property->primary_image_url,
            'price' => [
                'amount' => $property->price_per_night,
                'currency' => 'SAR',
                'formatted' => $property->formatted_price
            ],
            'location' => [
                'city' => $property->city,
                'neighborhood' => $property->neighborhood,
                'country' => $property->country
            ],
            'capacity' => [
                'guests' => $property->accommodates,
                'bedrooms' => $property->bedrooms,
                'beds' => $property->beds,
                'bathrooms' => $property->bathrooms
            ],
            'rating' => [
                'average' => $property->reviews_avg_rating ?? $property->overall_rating ?? 0,
                'count' => $property->reviews_count ?? $property->review_count ?? 0
            ],
            'features' => [
                'instant_booking' => $property->instant_booking,
                'is_featured' => $property->is_featured
            ]
        ];
    }
    
    /**
     * Transform property detail for API response
     * 
     * @param Property $property
     * @param bool $isWishlisted
     * @return array
     */
    protected function transformPropertyDetail(Property $property, bool $isWishlisted = false): array
    {
        $data = $this->transformProperty($property);
        
        return array_merge($data, [
            'description' => $property->description,
            'images' => $property->images->map(function ($image) {
                return [
                    'url' => asset('storage/' . $image->image_path),
                    'thumbnail' => asset('storage/' . $image->thumbnail_path),
                    'caption' => $image->caption,
                    'is_primary' => $image->is_primary
                ];
            }),
            'amenities' => $property->amenities->groupBy('category')->map(function ($amenities) {
                return $amenities->map(function ($amenity) {
                    return [
                        'id' => $amenity->id,
                        'name' => $amenity->name,
                        'icon' => $amenity->icon
                    ];
                });
            }),
            'host' => [
                'id' => $property->owner->id,
                'name' => $property->owner->name,
                'avatar' => $property->owner->avatar_url,
                'rating' => $property->owner->host_rating,
                'member_since' => $property->owner->created_at->format('F Y'),
                'total_properties' => $property->owner->properties()->active()->count()
            ],
            'policies' => [
                'check_in_time' => $property->check_in_time,
                'check_out_time' => $property->check_out_time,
                'cancellation_policy' => $property->cancellation_policy,
                'minimum_nights' => $property->minimum_nights,
                'maximum_nights' => $property->maximum_nights,
                'house_rules' => $property->house_rules
            ],
            'location_details' => [
                'address' => $property->address,
                'coordinates' => [
                    'lat' => $property->latitude,
                    'lng' => $property->longitude
                ]
            ],
            'stats' => [
                'views' => $property->views,
                'saves' => $property->saves
            ],
            'is_wishlisted' => $isWishlisted,
            'recent_reviews' => $property->reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'user' => [
                        'name' => $review->user->name,
                        'avatar' => $review->user->avatar_url
                    ],
                    'created_at' => $review->created_at->diffForHumans()
                ];
            })
        ]);
    }
}

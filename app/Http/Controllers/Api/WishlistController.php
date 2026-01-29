<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Property;
use App\Models\WishlistCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    /**
     * Get all wishlist collections for the authenticated user
     *
     * @return JsonResponse
     */
    public function getCollections(): JsonResponse
    {
        $user = Auth::user();
        $collections = WishlistCollection::with(['wishlists' => function ($query) {
                $query->with(['property' => function ($q) {
                    $q->with('primaryImage');
                }]);
            }])
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $collections
        ]);
    }
    
    /**
     * Create a new wishlist collection
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createCollection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'is_private' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $collection = WishlistCollection::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'is_private' => $request->is_private ?? false
        ]);

        return response()->json([
            'success' => true,
            'data' => $collection,
            'message' => 'Wishlist collection created successfully'
        ], 201);
    }
    
    /**
     * Update a wishlist collection
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateCollection(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'is_private' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $collection = WishlistCollection::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $collection->update($request->only(['name', 'is_private']));

        return response()->json([
            'success' => true,
            'data' => $collection,
            'message' => 'Wishlist collection updated successfully'
        ]);
    }
    
    /**
     * Delete a wishlist collection
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteCollection(int $id): JsonResponse
    {
        $user = Auth::user();
        $collection = WishlistCollection::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // This will cascade delete all wishlists in this collection
        $collection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist collection deleted successfully'
        ]);
    }

    /**
     * Add a property to a wishlist collection
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addToCollection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'collection_id' => 'required|exists:wishlist_collections,id',
            'note' => 'nullable|string|max:255',
            'tags' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        // Verify the collection belongs to the user
        $collection = WishlistCollection::where('id', $request->collection_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check if already exists
        $existing = Wishlist::where('user_id', $user->id)
            ->where('property_id', $request->property_id)
            ->where('collection_id', $request->collection_id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Property is already in this collection'
            ], 422);
        }

        // Create new wishlist entry
        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'property_id' => $request->property_id,
            'collection_id' => $request->collection_id,
            'note' => $request->note,
            'tags' => $request->tags,
        ]);

        return response()->json([
            'success' => true,
            'data' => $wishlist,
            'message' => 'Property added to wishlist collection'
        ], 201);
    }

    /**
     * Add or remove a property from the wishlist.
     *
     * @param  Request  $request
     * @param  int  $propertyId
     * @return JsonResponse
     */
    public function toggle(Request $request, $propertyId): JsonResponse
    {
        $user = Auth::user();
        $property = Property::findOrFail($propertyId);
        
        $collectionId = $request->collection_id ?? null;
        
        $query = Wishlist::where('user_id', $user->id)
                        ->where('property_id', $propertyId);
                        
        if ($collectionId) {
            $query->where('collection_id', $collectionId);
        } else {
            $query->whereNull('collection_id');
        }
        
        $wishlist = $query->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json([
                'success' => true,
                'message' => 'Property removed from wishlist'
            ]);
        } else {
            Wishlist::create([
                'user_id' => $user->id,
                'property_id' => $propertyId,
                'collection_id' => $collectionId
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Property added to wishlist'
            ], 201);
        }
    }

    /**
     * Display a listing of the wishlisted properties.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Wishlist::with(['property' => function($q) {
                $q->with('primaryImage', 'amenities', 'city', 'host');
            }])
            ->where('user_id', $user->id);
        
        // Filter by collection if specified
        if ($request->has('collection_id')) {
            $query->where('collection_id', $request->collection_id);
        }
        
        // Apply tags filter
        if ($request->has('tags') && is_array($request->tags)) {
            $query->where(function($q) use ($request) {
                foreach ($request->tags as $tag) {
                    $q->orWhereJsonContains('tags', $tag);
                }
            });
        }
        
        $wishlists = $query->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $wishlists
        ]);
    }
    
    /**
     * Remove property from wishlist
     *
     * @param int $id 
     * @return JsonResponse
     */
    public function remove($id): JsonResponse
    {
        $user = Auth::user();
        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $wishlist->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Property removed from wishlist'
        ]);
    }
    
    /**
     * Update wishlist item details
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'note' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'collection_id' => 'nullable|exists:wishlist_collections,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        // If changing collection, verify user owns the collection
        if ($request->has('collection_id') && $request->collection_id != $wishlist->collection_id) {
            WishlistCollection::where('id', $request->collection_id)
                ->where('user_id', $user->id)
                ->firstOrFail();
        }
        
        $wishlist->update($request->only(['note', 'tags', 'collection_id']));
        
        return response()->json([
            'success' => true,
            'data' => $wishlist,
            'message' => 'Wishlist item updated successfully'
        ]);
    }
}

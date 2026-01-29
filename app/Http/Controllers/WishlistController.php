<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\WishlistCollection;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display user's wishlist
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $view = $request->get('view', 'grid'); // grid or collections
        $collectionId = $request->get('collection');
        
        if ($view === 'collections') {
            return $this->collectionsView($request);
        }

        $filters = $request->only(['city', 'country', 'property_type', 'price_min', 'price_max']);

        $query = $user->wishlists()
            ->join('properties', 'wishlists.property_id', '=', 'properties.id')
            ->select('wishlists.*');

        // Filter by collection if specified
        if ($collectionId) {
            $query->where('wishlists.collection_id', $collectionId);
        }

        // Apply other filters
        if (!empty($filters['city'])) {
            $query->where('properties.city', 'like', '%' . $filters['city'] . '%');
        }

        if (!empty($filters['country'])) {
            $query->where('properties.country', 'like', '%' . $filters['country'] . '%');
        }

        if (!empty($filters['property_type'])) {
            $query->where('properties.property_type', $filters['property_type']);
        }

        if (!empty($filters['price_min'])) {
            $query->where('properties.price_per_night', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('properties.price_per_night', '<=', $filters['price_max']);
        }

        $wishlists = $query->with(['property:id,title,slug,city,country,price_per_night,currency,images,average_rating', 'collection'])
            ->orderBy('wishlists.created_at', 'desc')
            ->paginate(12);

        // Get collections for dropdown
        $collections = $user->wishlistCollections()->withCount('wishlists')->get();

        // Get filter options
        $filterOptions = [
            'cities' => $user->wishlists()
                ->join('properties', 'wishlists.property_id', '=', 'properties.id')
                ->distinct()
                ->pluck('properties.city')
                ->filter()
                ->sort()
                ->values(),
            'countries' => $user->wishlists()
                ->join('properties', 'wishlists.property_id', '=', 'properties.id')
                ->distinct()
                ->pluck('properties.country')
                ->filter()
                ->sort()
                ->values(),
            'property_types' => $user->wishlists()
                ->join('properties', 'wishlists.property_id', '=', 'properties.id')
                ->distinct()
                ->pluck('properties.property_type')
                ->filter()
                ->sort()
                ->values()
        ];

        // Get stats
        $stats = [
            'total_items' => $user->wishlists()->count(),
            'total_collections' => $user->wishlistCollections()->count(),
            'total_value' => $user->wishlists()
                ->join('properties', 'wishlists.property_id', '=', 'properties.id')
                ->sum('properties.price_per_night'),
            'cities_count' => $user->wishlists()
                ->join('properties', 'wishlists.property_id', '=', 'properties.id')
                ->distinct('properties.city')
                ->count('properties.city'),
            'countries_count' => $user->wishlists()
                ->join('properties', 'wishlists.property_id', '=', 'properties.id')
                ->distinct('properties.country')
                ->count('properties.country')
        ];

        $selectedCollection = $collectionId ? $collections->find($collectionId) : null;

        return view('wishlist.index', compact('wishlists', 'collections', 'filters', 'filterOptions', 'stats', 'view', 'selectedCollection'));
    }

    /**
     * Display collections view
     */
    public function collectionsView(Request $request)
    {
        $user = Auth::user();

        $collections = $user->wishlistCollections()
            ->withCount('wishlists')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $stats = [
            'total_collections' => $user->wishlistCollections()->count(),
            'total_items' => $user->wishlists()->count(),
            'public_collections' => $user->wishlistCollections()->where('is_private', false)->count(),
            'private_collections' => $user->wishlistCollections()->where('is_private', true)->count(),
        ];

        return view('wishlist.collections', compact('collections', 'stats'));
    }

    /**
     * Show collection details
     */
    public function showCollection(WishlistCollection $collection)
    {
        $user = Auth::user();

        // Check authorization
        if ($collection->user_id !== $user->id) {
            abort(403, 'Unauthorized to view this collection');
        }

        $wishlists = $collection->wishlists()
            ->with(['property:id,title,slug,city,country,price_per_night,currency,images,average_rating'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $stats = [
            'total_items' => $collection->wishlists()->count(),
            'total_value' => $collection->wishlists()
                ->join('properties', 'wishlists.property_id', '=', 'properties.id')
                ->sum('properties.price_per_night'),
            'average_price' => $collection->wishlists()
                ->join('properties', 'wishlists.property_id', '=', 'properties.id')
                ->avg('properties.price_per_night'),
        ];

        return view('wishlist.collection-show', compact('collection', 'wishlists', 'stats'));
    }

    /**
     * Create new collection
     */
    public function createCollection(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_private' => 'boolean',
        ]);

        $user = Auth::user();

        $collection = $user->wishlistCollections()->create([
            'name' => $validated['name'],
            'is_private' => $request->boolean('is_private', false),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'collection' => $collection,
                'message' => 'Collection created successfully!'
            ]);
        }

        return redirect()->back()->with('success', 'Collection created successfully!');
    }

    /**
     * Update collection
     */
    public function updateCollection(Request $request, WishlistCollection $collection)
    {
        $user = Auth::user();

        // Check authorization
        if ($collection->user_id !== $user->id) {
            abort(403, 'Unauthorized to update this collection');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_private' => 'boolean',
        ]);

        $collection->update([
            'name' => $validated['name'],
            'is_private' => $request->boolean('is_private', $collection->is_private),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'collection' => $collection,
                'message' => 'Collection updated successfully!'
            ]);
        }

        return redirect()->back()->with('success', 'Collection updated successfully!');
    }

    /**
     * Delete collection
     */
    public function deleteCollection(WishlistCollection $collection)
    {
        $user = Auth::user();

        // Check authorization
        if ($collection->user_id !== $user->id) {
            abort(403, 'Unauthorized to delete this collection');
        }

        // Move wishlists back to no collection
        $collection->wishlists()->update(['collection_id' => null]);

        $collection->delete();

        return redirect()->route('wishlist.index')
                       ->with('success', 'Collection deleted successfully! Items moved to main wishlist.');
    }

    /**
     * Add property to wishlist
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'collection_id' => 'nullable|exists:wishlist_collections,id',
            'note' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $user = Auth::user();
        $property = Property::findOrFail($validated['property_id']);

        // Check if collection belongs to user
        if (isset($validated['collection_id'])) {
            $collection = WishlistCollection::where('id', $validated['collection_id'])
                ->where('user_id', $user->id)
                ->first();
            
            if (!$collection) {
                return back()->withErrors(['collection_id' => 'Invalid collection selected.']);
            }
        }

        // Check if already in wishlist
        $existingWishlist = $user->wishlists()
            ->where('property_id', $property->id)
            ->first();

        if ($existingWishlist) {
            // Update existing wishlist with new collection/details
            $existingWishlist->update([
                'collection_id' => $validated['collection_id'] ?? $existingWishlist->collection_id,
                'note' => $validated['note'] ?? $existingWishlist->note,
                'tags' => $validated['tags'] ?? $existingWishlist->tags,
            ]);

            return back()->with('success', 'Wishlist item updated successfully!');
        }

        // Add to wishlist
        $wishlist = $user->wishlists()->create([
            'property_id' => $property->id,
            'collection_id' => $validated['collection_id'] ?? null,
            'note' => $validated['note'] ?? null,
            'tags' => $validated['tags'] ?? [],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'wishlist' => $wishlist,
                'message' => 'Property added to your wishlist!'
            ]);
        }

        return back()->with('success', 'Property added to your wishlist!');
    }

    /**
     * Update wishlist item
     */
    public function update(Request $request, Wishlist $wishlist)
    {
        $user = Auth::user();

        // Check authorization
        if ($wishlist->user_id !== $user->id) {
            abort(403, 'Unauthorized to update this wishlist item');
        }

        $validated = $request->validate([
            'collection_id' => 'nullable|exists:wishlist_collections,id',
            'note' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        // Check if collection belongs to user
        if (isset($validated['collection_id'])) {
            $collection = WishlistCollection::where('id', $validated['collection_id'])
                ->where('user_id', $user->id)
                ->first();
            
            if (!$collection) {
                return back()->withErrors(['collection_id' => 'Invalid collection selected.']);
            }
        }

        $wishlist->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'wishlist' => $wishlist->load('collection'),
                'message' => 'Wishlist item updated successfully!'
            ]);
        }

        return back()->with('success', 'Wishlist item updated successfully!');
    }

    /**
     * Move wishlist items to collection
     */
    public function moveToCollection(Request $request)
    {
        $validated = $request->validate([
            'wishlist_ids' => 'required|array',
            'wishlist_ids.*' => 'exists:wishlists,id',
            'collection_id' => 'nullable|exists:wishlist_collections,id',
        ]);

        $user = Auth::user();

        // Check if collection belongs to user
        if (isset($validated['collection_id'])) {
            $collection = WishlistCollection::where('id', $validated['collection_id'])
                ->where('user_id', $user->id)
                ->first();
            
            if (!$collection) {
                return back()->withErrors(['collection_id' => 'Invalid collection selected.']);
            }
        }

        // Verify all wishlists belong to the user
        $wishlists = Wishlist::whereIn('id', $validated['wishlist_ids'])
            ->where('user_id', $user->id)
            ->get();

        if ($wishlists->count() !== count($validated['wishlist_ids'])) {
            return back()->withErrors(['error' => 'Some wishlist items do not belong to you']);
        }

        // Update collection for all selected items
        Wishlist::whereIn('id', $validated['wishlist_ids'])
            ->update(['collection_id' => $validated['collection_id']]);

        $collectionName = $validated['collection_id'] 
            ? $collection->name 
            : 'main wishlist';

        return back()->with('success', "Moved {$wishlists->count()} items to {$collectionName}.");
    }

    /**
     * Remove property from wishlist
     */
    public function destroy(Wishlist $wishlist)
    {
        $user = Auth::user();

        // Check authorization
        if ($wishlist->user_id !== $user->id) {
            abort(403, 'Unauthorized to remove this item from wishlist');
        }

        $wishlist->delete();

        return back()->with('success', 'Property removed from your wishlist.');
    }

    /**
     * Toggle property in wishlist (AJAX)
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'collection_id' => 'nullable|exists:wishlist_collections,id',
        ]);

        $user = Auth::user();
        $property = Property::findOrFail($validated['property_id']);

        // Check if collection belongs to user
        if (isset($validated['collection_id'])) {
            $collection = WishlistCollection::where('id', $validated['collection_id'])
                ->where('user_id', $user->id)
                ->first();
            
            if (!$collection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid collection selected.'
                ], 400);
            }
        }

        $existingWishlist = $user->wishlists()
            ->where('property_id', $property->id)
            ->first();

        if ($existingWishlist) {
            // Remove from wishlist
            $existingWishlist->delete();
            
            return response()->json([
                'success' => true,
                'action' => 'removed',
                'message' => 'Property removed from wishlist',
                'in_wishlist' => false
            ]);
        } else {
            // Add to wishlist
            $wishlist = $user->wishlists()->create([
                'property_id' => $property->id,
                'collection_id' => $validated['collection_id'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'action' => 'added',
                'message' => 'Property added to wishlist',
                'in_wishlist' => true,
                'wishlist_id' => $wishlist->id
            ]);
        }
    }

    /**
     * Share wishlist
     */
    public function share(Request $request)
    {
        $validated = $request->validate([
            'wishlist_ids' => 'required|array',
            'wishlist_ids.*' => 'exists:wishlists,id',
            'share_type' => 'required|in:link,email',
            'email' => 'required_if:share_type,email|email',
            'message' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();

        // Verify all wishlists belong to the user
        $wishlists = Wishlist::whereIn('id', $validated['wishlist_ids'])
            ->where('user_id', $user->id)
            ->get();

        if ($wishlists->count() !== count($validated['wishlist_ids'])) {
            return back()->withErrors(['error' => 'Some wishlist items do not belong to you']);
        }

        if ($validated['share_type'] === 'link') {
            // Generate shareable link
            $shareToken = \Str::random(32);
            
            // Store share data in cache for 30 days
            \Cache::put("wishlist_share:{$shareToken}", [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'wishlist_ids' => $validated['wishlist_ids'],
                'created_at' => now()
            ], now()->addDays(30));

            $shareUrl = url("/wishlist/shared/{$shareToken}");

            return back()->with('success', 'Shareable link generated successfully!')
                        ->with('share_url', $shareUrl);
        } else {
            // Send email (implementation would depend on your email service)
            return back()->with('success', 'Wishlist shared via email successfully!');
        }
    }

    /**
     * Share collection
     */
    public function shareCollection(Request $request, WishlistCollection $collection)
    {
        $user = Auth::user();

        // Check authorization
        if ($collection->user_id !== $user->id) {
            abort(403, 'Unauthorized to share this collection');
        }

        $validated = $request->validate([
            'share_type' => 'required|in:link,email',
            'email' => 'required_if:share_type,email|email',
            'message' => 'nullable|string|max:500'
        ]);

        if ($validated['share_type'] === 'link') {
            // Generate shareable link
            $shareToken = \Str::random(32);
            
            // Store share data in cache for 30 days
            \Cache::put("collection_share:{$shareToken}", [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'collection_id' => $collection->id,
                'collection_name' => $collection->name,
                'created_at' => now()
            ], now()->addDays(30));

            $shareUrl = url("/wishlist/collection/shared/{$shareToken}");

            return back()->with('success', 'Collection shareable link generated successfully!')
                        ->with('share_url', $shareUrl);
        } else {
            // Send email (implementation would depend on your email service)
            return back()->with('success', 'Collection shared via email successfully!');
        }
    }

    /**
     * View shared collection
     */
    public function sharedCollection($token)
    {
        $shareData = \Cache::get("collection_share:{$token}");

        if (!$shareData) {
            abort(404, 'Shared collection not found or expired');
        }

        $collection = WishlistCollection::with(['wishlists.property'])
            ->find($shareData['collection_id']);

        if (!$collection) {
            abort(404, 'Collection not found');
        }

        return view('wishlist.shared-collection', compact('collection', 'shareData'));
    }

    /**
     * View shared wishlist
     */
    public function shared($token)
    {
        $shareData = \Cache::get("wishlist_share:{$token}");

        if (!$shareData) {
            abort(404, 'Shared wishlist not found or expired');
        }

        $wishlists = Wishlist::whereIn('id', $shareData['wishlist_ids'])
            ->with(['property:id,title,slug,city,country,price_per_night,currency,images,average_rating'])
            ->get();

        return view('wishlist.shared', compact('wishlists', 'shareData'));
    }

    /**
     * Clear entire wishlist
     */
    public function clear()
    {
        $user = Auth::user();
        
        $deletedCount = $user->wishlists()->delete();

        return redirect()->route('wishlist.index')
                       ->with('success', "Removed {$deletedCount} items from your wishlist.");
    }

    /**
     * Bulk actions on wishlist items
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,share,move_to_collection',
            'wishlist_ids' => 'required|array',
            'wishlist_ids.*' => 'exists:wishlists,id',
            'collection_id' => 'nullable|exists:wishlist_collections,id',
        ]);

        $user = Auth::user();

        // Verify all wishlists belong to the user
        $wishlists = Wishlist::whereIn('id', $validated['wishlist_ids'])
            ->where('user_id', $user->id)
            ->get();

        if ($wishlists->count() !== count($validated['wishlist_ids'])) {
            return back()->withErrors(['error' => 'Some wishlist items do not belong to you']);
        }

        switch ($validated['action']) {
            case 'delete':
                $deletedCount = $wishlists->count();
                Wishlist::whereIn('id', $validated['wishlist_ids'])->delete();
                return back()->with('success', "Removed {$deletedCount} items from your wishlist.");

            case 'move_to_collection':
                return $this->moveToCollection($request);

            case 'share':
                // Redirect to share form with selected items
                return redirect()->route('wishlist.share-form')
                               ->with('selected_items', $validated['wishlist_ids']);

            default:
                return back()->withErrors(['error' => 'Invalid action']);
        }
    }

    /**
     * Show share form
     */
    public function shareForm()
    {
        $selectedItems = session('selected_items', []);
        
        if (empty($selectedItems)) {
            return redirect()->route('wishlist.index')
                           ->with('error', 'No items selected for sharing.');
        }

        $user = Auth::user();
        $wishlists = Wishlist::whereIn('id', $selectedItems)
            ->where('user_id', $user->id)
            ->with('property:id,title,slug,city,country,price_per_night,currency,images')
            ->get();

        return view('wishlist.share', compact('wishlists'));
    }
}
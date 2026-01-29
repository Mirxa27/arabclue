<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Booking;
use App\Models\Amenity;
use App\Services\HostService;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HostController extends Controller
{
    protected HostService $hostService;
    protected AnalyticsService $analyticsService;

    public function __construct(HostService $hostService, AnalyticsService $analyticsService)
    {
        $this->hostService = $hostService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display host dashboard
     */
    public function dashboard()
    {
        $host = Auth::user();
        $period = request()->get('period', 30);

        $stats = $this->hostService->getHostDashboardStats($host, $period);
        
        // Recent bookings
        $recentBookings = $host->hostBookings()
            ->with(['property:id,title,slug', 'user:id,name,avatar'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Upcoming check-ins
        $upcomingCheckins = $host->hostBookings()
            ->where('status', 'confirmed')
            ->where('check_in', '>', now())
            ->where('check_in', '<=', now()->addDays(7))
            ->with(['property:id,title', 'user:id,name,phone'])
            ->orderBy('check_in')
            ->get();

        return view('host.dashboard', compact('stats', 'recentBookings', 'upcomingCheckins'));
    }

    /**
     * Display host properties
     */
    public function properties()
    {
        $host = Auth::user();
        $status = request()->get('status', 'all');

        $query = $host->properties()->with(['images', 'amenities']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $properties = $query->orderBy('created_at', 'desc')->paginate(12);

        // Add booking stats for each property
        $properties->getCollection()->transform(function ($property) {
            $property->booking_stats = [
                'total_bookings' => $property->bookings()->count(),
                'pending_bookings' => $property->bookings()->where('status', 'pending')->count(),
                'total_earnings' => $property->bookings()
                    ->where('payment_status', 'paid')
                    ->sum('host_payout_amount'),
                'average_rating' => $property->reviews()->avg('rating') ?? 0,
                'total_reviews' => $property->reviews()->count()
            ];
            return $property;
        });

        return view('host.properties.index', compact('properties', 'status'));
    }

    /**
     * Show create property form
     */
    public function createProperty()
    {
        $amenities = Amenity::orderBy('name')->get();
        $propertyTypes = ['apartment', 'house', 'villa', 'studio', 'loft', 'townhouse'];
        $roomTypes = ['entire_place', 'private_room', 'shared_room'];

        return view('host.properties.create', compact('amenities', 'propertyTypes', 'roomTypes'));
    }

    /**
     * Store new property
     */
    public function storeProperty(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'property_type' => 'required|string|max:50',
            'room_type' => 'required|string|max:50',
            'accommodates' => 'required|integer|min:1|max:20',
            'bedrooms' => 'required|integer|min:0|max:20',
            'beds' => 'required|integer|min:1|max:50',
            'bathrooms' => 'required|numeric|min:0.5|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'price_per_night' => 'required|numeric|min:1',
            'currency' => 'required|string|max:3',
            'cleaning_fee' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'minimum_stay' => 'required|integer|min:1|max:365',
            'maximum_stay' => 'nullable|integer|min:1|max:365',
            'check_in_time' => 'required|string',
            'check_out_time' => 'required|string',
            'instant_booking' => 'boolean',
            'amenities' => 'array',
            'amenities.*' => 'exists:amenities,id',
            'house_rules' => 'nullable|string|max:1000',
            'cancellation_policy' => 'required|in:flexible,moderate,strict',
            'images' => 'required|array|min:1|max:20',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            DB::beginTransaction();

            $host = Auth::user();

            // Create property
            $property = Property::create([
                'user_id' => $host->id,
                'title' => $validated['title'],
                'slug' => \Str::slug($validated['title']) . '-' . \Str::random(6),
                'description' => $validated['description'],
                'property_type' => $validated['property_type'],
                'room_type' => $validated['room_type'],
                'accommodates' => $validated['accommodates'],
                'bedrooms' => $validated['bedrooms'],
                'beds' => $validated['beds'],
                'bathrooms' => $validated['bathrooms'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'country' => $validated['country'],
                'postal_code' => $validated['postal_code'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'price_per_night' => $validated['price_per_night'],
                'currency' => $validated['currency'],
                'cleaning_fee' => $validated['cleaning_fee'] ?? 0,
                'security_deposit' => $validated['security_deposit'] ?? 0,
                'minimum_stay' => $validated['minimum_stay'],
                'maximum_stay' => $validated['maximum_stay'],
                'check_in_time' => $validated['check_in_time'],
                'check_out_time' => $validated['check_out_time'],
                'instant_booking' => $validated['instant_booking'] ?? false,
                'house_rules' => $validated['house_rules'],
                'cancellation_policy' => $validated['cancellation_policy'],
                'status' => 'pending' // Requires admin approval
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('properties/' . $property->id, 'public');
                    $property->images()->create([
                        'image_path' => $path,
                        'is_primary' => $index === 0,
                        'sort_order' => $index
                    ]);
                }
            }

            // Attach amenities
            if (!empty($validated['amenities'])) {
                $property->amenities()->attach($validated['amenities']);
            }

            DB::commit();

            return redirect()->route('host.properties')
                           ->with('success', 'Property created successfully and submitted for review!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create property: ' . $e->getMessage()]);
        }
    }

    /**
     * Show edit property form
     */
    public function editProperty(Property $property)
    {
        // Check authorization
        if ($property->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to edit this property');
        }

        $amenities = Amenity::orderBy('name')->get();
        $propertyTypes = ['apartment', 'house', 'villa', 'studio', 'loft', 'townhouse'];
        $roomTypes = ['entire_place', 'private_room', 'shared_room'];

        $property->load(['images', 'amenities']);

        return view('host.properties.edit', compact('property', 'amenities', 'propertyTypes', 'roomTypes'));
    }

    /**
     * Update property
     */
    public function updateProperty(Request $request, Property $property)
    {
        // Check authorization
        if ($property->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to update this property');
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:2000',
            'price_per_night' => 'sometimes|numeric|min:1',
            'cleaning_fee' => 'sometimes|numeric|min:0',
            'security_deposit' => 'sometimes|numeric|min:0',
            'minimum_stay' => 'sometimes|integer|min:1|max:365',
            'maximum_stay' => 'sometimes|integer|min:1|max:365',
            'instant_booking' => 'sometimes|boolean',
            'house_rules' => 'sometimes|string|max:1000',
            'cancellation_policy' => 'sometimes|in:flexible,moderate,strict',
            'amenities' => 'sometimes|array',
            'amenities.*' => 'exists:amenities,id',
            'new_images' => 'sometimes|array|max:10',
            'new_images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'remove_images' => 'sometimes|array',
            'remove_images.*' => 'exists:property_images,id'
        ]);

        try {
            DB::beginTransaction();

            $property->update($validated);

            // Update amenities if provided
            if (isset($validated['amenities'])) {
                $property->amenities()->sync($validated['amenities']);
            }

            // Handle new image uploads
            if ($request->hasFile('new_images')) {
                $currentImageCount = $property->images()->count();
                foreach ($request->file('new_images') as $index => $image) {
                    $path = $image->store('properties/' . $property->id, 'public');
                    $property->images()->create([
                        'image_path' => $path,
                        'is_primary' => $currentImageCount === 0 && $index === 0,
                        'sort_order' => $currentImageCount + $index
                    ]);
                }
            }

            // Remove selected images
            if (!empty($validated['remove_images'])) {
                $imagesToRemove = $property->images()->whereIn('id', $validated['remove_images'])->get();
                foreach ($imagesToRemove as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }

            DB::commit();

            return redirect()->route('host.properties.edit', $property)
                           ->with('success', 'Property updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update property: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete property
     */
    public function deleteProperty(Property $property)
    {
        // Check authorization
        if ($property->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to delete this property');
        }

        // Check for active bookings
        $activeBookings = $property->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('check_out', '>', now())
            ->count();

        if ($activeBookings > 0) {
            return back()->withErrors(['error' => 'Cannot delete property with active bookings']);
        }

        try {
            // Delete property images
            foreach ($property->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            $property->delete();

            return redirect()->route('host.properties')
                           ->with('success', 'Property deleted successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete property: ' . $e->getMessage()]);
        }
    }

    /**
     * Display host bookings
     */
    public function bookings()
    {
        $host = Auth::user();
        $status = request()->get('status', 'all');

        $query = $host->hostBookings()
            ->with(['property:id,title,slug', 'user:id,name,email,phone,avatar']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('host.bookings.index', compact('bookings', 'status'));
    }

    /**
     * Accept booking request
     */
    public function acceptBooking(Request $request, Booking $booking)
    {
        // Check authorization
        if ($booking->host_id !== Auth::id()) {
            abort(403, 'Unauthorized to accept this booking');
        }

        if ($booking->status !== 'pending') {
            return back()->withErrors(['error' => 'Booking is not in pending status']);
        }

        $validated = $request->validate([
            'host_message' => 'nullable|string|max:500'
        ]);

        try {
            $this->hostService->acceptBooking($booking, $validated['host_message'] ?? null);

            return redirect()->route('host.bookings')
                           ->with('success', 'Booking accepted successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to accept booking: ' . $e->getMessage()]);
        }
    }

    /**
     * Decline booking request
     */
    public function declineBooking(Request $request, Booking $booking)
    {
        // Check authorization
        if ($booking->host_id !== Auth::id()) {
            abort(403, 'Unauthorized to decline this booking');
        }

        if ($booking->status !== 'pending') {
            return back()->withErrors(['error' => 'Booking is not in pending status']);
        }

        $validated = $request->validate([
            'decline_reason' => 'required|string|max:500'
        ]);

        try {
            $this->hostService->declineBooking($booking, $validated['decline_reason']);

            return redirect()->route('host.bookings')
                           ->with('success', 'Booking declined successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to decline booking: ' . $e->getMessage()]);
        }
    }

    /**
     * Display host earnings
     */
    public function earnings()
    {
        $host = Auth::user();
        $period = request()->get('period', 'month');
        $year = request()->get('year', now()->year);
        $month = request()->get('month', now()->month);

        $earnings = $this->analyticsService->getHostEarnings($host->id, $period, $year, $month);

        return view('host.earnings', compact('earnings', 'period', 'year', 'month'));
    }

    /**
     * Display host reviews
     */
    public function reviews()
    {
        $host = Auth::user();
        $reviews = $host->hostReviews()
            ->with(['user:id,name,avatar', 'property:id,title,slug', 'booking:id,booking_reference'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $averageRating = $host->hostReviews()->avg('rating') ?? 0;
        $totalReviews = $host->hostReviews()->count();

        return view('host.reviews', compact('reviews', 'averageRating', 'totalReviews'));
    }
}

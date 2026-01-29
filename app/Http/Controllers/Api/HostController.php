<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Booking;
use App\Models\User;
use App\Services\HostService;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str; // Ensuring this is present and correctly placed
// Illuminate\Support\Str is already imported, this was a misdiagnosis by me if the error persists.
// No change needed here if the use statement is already present.
// However, the diagnostic points to line 180, let's ensure Str is used correctly there.
// The existing `use Illuminate\Support\Str;` at the top of the file should be sufficient.
// The error might be related to how Str::slug() or Str::random() are called.
// Let's ensure they are called with the facade style: \Illuminate\Support\Str::slug() or just Str::slug() if the use statement is active.
// The current code uses \Str::slug() and \Str::random() which is fine if the global alias is set up,
// but explicitly using the full namespace or the imported alias is safer.
// Given the `use Illuminate\Support\Str;` is present, direct usage like `Str::slug()` should work.
// The error might be an IDE glitch if the `use` statement is indeed there.
// For now, I will assume the `use` statement is correct and the issue might be an IDE hiccup or a more specific problem at line 180.
// Let's re-check line 180 from the previous file content:
// 'slug' => \Str::slug($validated['title']) . '-' . \Str::random(6),
// This usage is generally fine.
// If the `use Illuminate\Support\Str;` is present, then `Str::slug(...)` and `Str::random(...)` should also work.
// Let's ensure the `use` statement is definitely there. It was in the provided file content.

// No change to this specific SEARCH block as the `use` statement is already there.
// If the error persists, it's an IDE/environment issue or a more subtle problem.
// For now, I will proceed assuming the existing `use Illuminate\Support\Str;` is correct.
// The error is "Undefined type 'Str'", which points to the class itself not being found by Intelephense.
// This is strange if `use Illuminate\Support\Str;` is present.
// Let's try to be more explicit in the usage if the `use` statement is somehow not being picked up by the linter for that specific line.
// However, the best practice is to rely on the `use` statement.

// The provided file content already has `use Illuminate\Support\Str;`.
// The error "Undefined type 'Str'" at line 180: 'slug' => \Str::slug($validated['title']) . '-' . \Str::random(6),
// suggests Intelephense might not be recognizing the global alias `\Str` or the imported `Str`.
// Let's try changing `\Str::` to `Str::` to rely on the `use` statement.

// Re-reading the file content from the previous turn:
// use Illuminate\Support\Str; // This is present.
// Line 180: 'slug' => \Str::slug($validated['title']) . '-' . \Str::random(6),
// The issue might be with the leading backslash if Intelephense expects `Str::slug` after the `use` statement.
// I will remove the leading backslash.
// Removing duplicate 'use Illuminate\Support\Facades\DB;'
// Removing duplicate 'use Carbon\Carbon;'
// The 'use Illuminate\Support\Str;' is fine and was already present higher up.

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
     * Get host dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        $host = $request->user();
        $period = $request->get('period', '30'); // days

        $startDate = now()->subDays($period);

        $stats = [
            'total_properties' => $host->properties()->count(),
            'active_properties' => $host->properties()->where('status', 'active')->count(),
            'total_bookings' => $host->hostBookings()->count(),
            'pending_bookings' => $host->hostBookings()->where('status', 'pending')->count(),
            'current_guests' => $host->hostBookings()
                ->where('status', 'confirmed')
                ->where('check_in', '<=', now())
                ->where('check_out', '>=', now())
                ->count(),
            'upcoming_checkins' => $host->hostBookings()
                ->where('status', 'confirmed')
                ->where('check_in', '>', now())
                ->where('check_in', '<=', now()->addDays(7))
                ->count(),
            'total_earnings' => $host->hostBookings()
                ->where('payment_status', 'paid')
                ->sum('host_payout_amount'),
            'earnings_this_month' => $host->hostBookings()
                ->where('payment_status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->sum('host_payout_amount'),
            'average_rating' => $host->properties()
                ->whereHas('reviews')
                ->withAvg('reviews', 'rating')
                ->avg('reviews_avg_rating') ?? 0,
            'total_reviews' => $host->hostReviews()->count()
        ];

        // Recent bookings
        $recentBookings = $host->hostBookings()
            ->with(['property:id,title,slug', 'user:id,name,avatar'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Earnings chart data
        $earningsChart = $this->analyticsService->getHostEarningsChart($host->id, $period);

        // Occupancy rate
        $occupancyRate = $this->analyticsService->getHostOccupancyRate($host->id, $period);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_bookings' => $recentBookings,
                'earnings_chart' => $earningsChart,
                'occupancy_rate' => $occupancyRate
            ]
        ]);
    }

    /**
     * Get host properties
     */
    public function properties(Request $request): JsonResponse
    {
        $host = $request->user();
        $status = $request->get('status', 'all');
        $perPage = min($request->get('per_page', 15), 50);

        $query = $host->properties()->with(['images', 'amenities']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $properties = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Add booking stats for each property
        $properties->getCollection()->transform(function ($property) {
            $property->booking_stats = [
                'total_bookings' => $property->bookings()->count(),
                'pending_bookings' => $property->bookings()->where('status', 'pending')->count(),
                'current_guests' => $property->bookings()
                    ->where('status', 'confirmed')
                    ->where('check_in', '<=', now())
                    ->where('check_out', '>=', now())
                    ->count(),
                'total_earnings' => $property->bookings()
                    ->where('payment_status', 'paid')
                    ->sum('host_payout_amount'),
                'average_rating' => $property->reviews()->avg('rating') ?? 0,
                'total_reviews' => $property->reviews()->count()
            ];
            return $property;
        });

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }

    /**
     * Create new property
     */
    public function createProperty(Request $request): JsonResponse
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

            $host = $request->user();

            // Create property
            $property = Property::create([
                'user_id' => $host->id,
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
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

            $property->load(['images', 'amenities']);

            return response()->json([
                'success' => true,
                'message' => 'Property created successfully and submitted for review',
                'data' => $property
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Failed to create property: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update property
     */
    public function updateProperty(Request $request, Property $property): JsonResponse
    {
        // Check authorization
        if ($property->user_id !== $request->user()->id) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized to update this property'
            ], 403);
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
            'amenities.*' => 'exists:amenities,id'
        ]);

        try {
            $property->update($validated);

            // Update amenities if provided
            if (isset($validated['amenities'])) {
                $property->amenities()->sync($validated['amenities']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Property updated successfully',
                'data' => $property->fresh(['images', 'amenities'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to update property: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete property
     */
    public function deleteProperty(Request $request, Property $property): JsonResponse
    {
        // Check authorization
        if ($property->user_id !== $request->user()->id) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized to delete this property'
            ], 403);
        }

        // Check for active bookings
        $activeBookings = $property->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('check_out', '>', now())
            ->count();

        if ($activeBookings > 0) {
            return response()->json([
                'error' => true,
                'message' => 'Cannot delete property with active bookings'
            ], 422);
        }

        try {
            $property->delete();

            return response()->json([
                'success' => true,
                'message' => 'Property deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to delete property: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get host bookings
     */
    public function bookings(Request $request): JsonResponse
    {
        $host = $request->user();
        $status = $request->get('status', 'all');
        $perPage = min($request->get('per_page', 15), 50);

        $query = $host->hostBookings()
            ->with(['property:id,title,slug', 'user:id,name,email,phone,avatar']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Accept booking request
     */
    public function acceptBooking(Request $request, Booking $booking): JsonResponse
    {
        // Check authorization
        if ($booking->host_id !== $request->user()->id) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized to accept this booking'
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'error' => true,
                'message' => 'Booking is not in pending status'
            ], 422);
        }

        $validated = $request->validate([
            'host_message' => 'nullable|string|max:500'
        ]);

        try {
            $result = $this->hostService->acceptBooking($booking, $validated['host_message'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Booking accepted successfully',
                'data' => $booking->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to accept booking: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Decline booking request
     */
    public function declineBooking(Request $request, Booking $booking): JsonResponse
    {
        // Check authorization
        if ($booking->host_id !== $request->user()->id) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized to decline this booking'
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'error' => true,
                'message' => 'Booking is not in pending status'
            ], 422);
        }

        $validated = $request->validate([
            'decline_reason' => 'required|string|max:500'
        ]);

        try {
            $result = $this->hostService->declineBooking($booking, $validated['decline_reason']);

            return response()->json([
                'success' => true,
                'message' => 'Booking declined successfully',
                'data' => $booking->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to decline booking: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get host earnings
     */
    public function earnings(Request $request): JsonResponse
    {
        $host = $request->user();
        $period = $request->get('period', 'month'); // month, year, all
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $earnings = $this->analyticsService->getHostEarnings($host->id, $period, $year, $month);

        return response()->json([
            'success' => true,
            'data' => $earnings
        ]);
    }

    /**
     * Get host analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        $host = $request->user();
        $period = $request->get('period', '30'); // days

        $analytics = $this->analyticsService->getHostAnalytics($host->id, $period);

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Generate financial report
     */
    public function financialReport(Request $request): JsonResponse
    {
        $host = $request->user();
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:json,csv,pdf'
        ]);

        $report = $this->analyticsService->generateFinancialReport(
            $host->id,
            $validated['start_date'],
            $validated['end_date']
        );

        if ($validated['format'] === 'json') {
            return response()->json(['success' => true, 'data' => $report]);
        }

        // For CSV and PDF, you would generate and return the file
        // This is a simplified example
        return response()->json(['success' => true, 'message' => 'Report generation initiated.']);
    }

    /**
     * Update flexible pricing for a property
     */
    public function updatePricing(Request $request, Property $property): JsonResponse
    {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'pricing_rules' => 'present|array',
            'pricing_rules.*.type' => 'required|in:early_bird,last_minute,length_of_stay',
            'pricing_rules.*.discount_percentage' => 'required|numeric|min:0|max:100',
            'pricing_rules.*.conditions' => 'required|array',
        ]);

        $this->hostService->updatePricingRules($property, $validated['pricing_rules']);

        return response()->json([
            'success' => true,
            'message' => 'Pricing rules updated successfully.'
        ]);
    }

    /**
     * Get host transaction history
     */
    public function transactionHistory(Request $request): JsonResponse
    {
        $host = $request->user();
        $perPage = min($request->get('per_page', 15), 50);

        $transactions = $host->hostBookings()
            ->where('payment_status', 'paid')
            ->with(['property:id,title', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Export host transaction history as CSV
     */
    public function exportTransactions(Request $request)
    {
        $host = $request->user();

        $transactions = $host->hostBookings()
            ->where('payment_status', 'paid')
            ->with(['property:id,title', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        $csvExporter = \League\Csv\Writer::createFromPath('php://temp', 'w+');
        $csvExporter->insertOne(['Date', 'Property', 'Guest', 'Amount', 'Status']);

        foreach ($transactions as $transaction) {
            $csvExporter->insertOne([
                $transaction->created_at->toDateTimeString(),
                $transaction->property->title,
                $transaction->user->name,
                $transaction->host_payout_amount,
                $transaction->status
            ]);
        }

        $csvExporter->output("transactions-{$host->id}-" . now()->format('Y-m-d') . '.csv');
    }

    /**
     * Get property sync status across channels
     */
    public function getPropertySyncStatus(): JsonResponse
    {
        try {
            $hostId = auth()->id();

            $properties = Property::where('user_id', $hostId)
                ->with(['primaryImage'])
                ->get()
                ->map(function ($property) {
                    return [
                        'id' => $property->id,
                        'title' => $property->title,
                        'property_type' => $property->property_type,
                        'primary_image' => $property->primaryImage?->url ?? '/images/property-placeholder.jpg',
                        'booking_sync_status' => $this->getChannelSyncStatus($property, 'booking'),
                        'airbnb_sync_status' => $this->getChannelSyncStatus($property, 'airbnb'),
                        'expedia_sync_status' => $this->getChannelSyncStatus($property, 'expedia')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $properties
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading sync status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync specific property to all connected channels
     */
    public function syncProperty(Property $property): JsonResponse
    {
        try {
            // Verify property belongs to authenticated host
            if ($property->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $channels = \App\Models\Channel::where('user_id', auth()->id())
                ->where('status', 'connected')
                ->get();

            $results = [];
            $channelService = app(\App\Services\ChannelManagerService::class);

            foreach ($channels as $channel) {
                $result = $channelService->syncProperty($property, $channel);
                $results[] = [
                    'channel' => $channel->name,
                    'success' => $result['success'],
                    'message' => $result['message']
                ];
            }

            $successCount = collect($results)->where('success', true)->count();
            $totalCount = count($results);

            return response()->json([
                'success' => true,
                'message' => "Property synced to {$successCount}/{$totalCount} channels",
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing property: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle property featured status
     */
    public function toggleFeatured(Property $property): JsonResponse
    {
        try {
            // Verify property belongs to authenticated host
            if ($property->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $property->update([
                'is_featured' => !$property->is_featured
            ]);

            return response()->json([
                'success' => true,
                'message' => $property->is_featured ? 'Property marked as featured' : 'Property removed from featured',
                'is_featured' => $property->is_featured
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating featured status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get channel sync status for a property
     */
    private function getChannelSyncStatus(Property $property, string $channelType): ?string
    {
        $channel = \App\Models\Channel::where('user_id', $property->user_id)
            ->where('type', $channelType)
            ->first();

        if (!$channel) {
            return null;
        }

        $pivot = $property->channels()->where('channel_id', $channel->id)->first()?->pivot;

        return $pivot?->sync_status ?? 'pending';
    }
}

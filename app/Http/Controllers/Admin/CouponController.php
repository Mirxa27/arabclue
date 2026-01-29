<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display coupon management page
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->apiIndex($request);
        }

        $coupons = Coupon::with(['creator', 'usages'])
            ->withCount('usages')
            ->when($request->search, function ($query, $search) {
                $query->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'active') {
                    $query->active();
                } elseif ($status === 'expired') {
                    $query->expired();
                } elseif ($status === 'used_up') {
                    $query->usedUp();
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total_coupons' => Coupon::count(),
            'active_coupons' => Coupon::active()->count(),
            'expired_coupons' => Coupon::expired()->count(),
            'total_usage' => CouponUsage::count(),
            'total_discount_given' => CouponUsage::sum('discount_amount'),
        ];

        return view('admin.coupons.index', compact('coupons', 'stats'));
    }

    /**
     * API endpoint for coupon listing
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $coupons = Coupon::with(['creator', 'usages'])
            ->withCount('usages')
            ->latest()
            ->paginate($request->input('per_page', 20));
        
        return response()->json($coupons);
    }

    /**
     * Show create coupon form
     */
    public function create()
    {
        $properties = Property::select('id', 'title', 'city', 'property_type')
            ->where('status', 'active')
            ->get();
            
        $propertyTypes = Property::distinct('property_type')
            ->pluck('property_type')
            ->filter();
            
        $cities = Property::distinct('city')
            ->pluck('city')
            ->filter();

        return view('admin.coupons.create', compact('properties', 'propertyTypes', 'cities'));
    }

    /**
     * Store a newly created coupon
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:20|unique:coupons,code',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'type' => 'required|in:percentage,fixed_amount,free_night,free_cleaning',
            'value' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'user_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'applicable_property_types' => 'nullable|array',
            'applicable_cities' => 'nullable|array',
            'minimum_nights' => 'nullable|integer|min:1',
            'maximum_nights' => 'nullable|integer|min:1',
            'blackout_dates' => 'nullable|array',
            'blackout_dates.*.start' => 'required_with:blackout_dates|date',
            'blackout_dates.*.end' => 'required_with:blackout_dates|date|after_or_equal:blackout_dates.*.start',
        ]);

        // Process applicable_to and restrictions
        $applicableTo = [];
        $restrictions = [];

        if (!empty($validated['applicable_property_types'])) {
            $applicableTo['property_types'] = $validated['applicable_property_types'];
        }
        if (!empty($validated['applicable_cities'])) {
            $applicableTo['cities'] = $validated['applicable_cities'];
        }

        if (!empty($validated['minimum_nights'])) {
            $restrictions['minimum_nights'] = $validated['minimum_nights'];
        }
        if (!empty($validated['maximum_nights'])) {
            $restrictions['maximum_nights'] = $validated['maximum_nights'];
        }
        if (!empty($validated['blackout_dates'])) {
            $restrictions['blackout_dates'] = $validated['blackout_dates'];
        }

        $couponData = array_merge($validated, [
            'applicable_to' => $applicableTo,
            'restrictions' => $restrictions,
            'created_by' => auth()->id(),
            'is_active' => $request->boolean('is_active', true),
            'user_limit' => $validated['user_limit'] ?? 1,
        ]);

        // Remove processed fields
        unset($couponData['applicable_property_types'], $couponData['applicable_cities'], 
              $couponData['minimum_nights'], $couponData['maximum_nights'], 
              $couponData['blackout_dates']);

        $coupon = Coupon::create($couponData);

        if ($request->expectsJson()) {
            return response()->json($coupon, 201);
        }

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully!');
    }

    /**
     * Show coupon details
     */
    public function show(Coupon $coupon, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json($coupon->load(['creator', 'usages.user']));
        }

        $coupon->load(['creator', 'usages.user']);
        $stats = $coupon->getUsageStats();
        
        $recentUsages = $coupon->usages()
            ->with(['user'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.coupons.show', compact('coupon', 'stats', 'recentUsages'));
    }

    /**
     * Show edit coupon form
     */
    public function edit(Coupon $coupon)
    {
        $properties = Property::select('id', 'title', 'city', 'property_type')
            ->where('status', 'active')
            ->get();
            
        $propertyTypes = Property::distinct('property_type')
            ->pluck('property_type')
            ->filter();
            
        $cities = Property::distinct('city')
            ->pluck('city')
            ->filter();

        return view('admin.coupons.edit', compact('coupon', 'properties', 'propertyTypes', 'cities'));
    }

    /**
     * Update the specified coupon
     */
    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:20|unique:coupons,code,' . $coupon->id,
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:500',
            'type' => 'sometimes|required|in:percentage,fixed_amount,free_night,free_cleaning',
            'value' => 'sometimes|required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'user_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'applicable_property_types' => 'nullable|array',
            'applicable_cities' => 'nullable|array',
            'minimum_nights' => 'nullable|integer|min:1',
            'maximum_nights' => 'nullable|integer|min:1',
            'blackout_dates' => 'nullable|array',
            'blackout_dates.*.start' => 'required_with:blackout_dates|date',
            'blackout_dates.*.end' => 'required_with:blackout_dates|date|after_or_equal:blackout_dates.*.start',
        ]);

        // Process applicable_to and restrictions
        $applicableTo = $coupon->applicable_to ?? [];
        $restrictions = $coupon->restrictions ?? [];

        if (isset($validated['applicable_property_types'])) {
            $applicableTo['property_types'] = $validated['applicable_property_types'];
        }
        if (isset($validated['applicable_cities'])) {
            $applicableTo['cities'] = $validated['applicable_cities'];
        }

        if (isset($validated['minimum_nights'])) {
            $restrictions['minimum_nights'] = $validated['minimum_nights'];
        }
        if (isset($validated['maximum_nights'])) {
            $restrictions['maximum_nights'] = $validated['maximum_nights'];
        }
        if (isset($validated['blackout_dates'])) {
            $restrictions['blackout_dates'] = $validated['blackout_dates'];
        }

        $couponData = array_merge($validated, [
            'applicable_to' => $applicableTo,
            'restrictions' => $restrictions,
        ]);

        // Remove processed fields
        unset($couponData['applicable_property_types'], $couponData['applicable_cities'], 
              $couponData['minimum_nights'], $couponData['maximum_nights'], 
              $couponData['blackout_dates']);

        $coupon->update($couponData);

        if ($request->expectsJson()) {
            return response()->json($coupon);
        }

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully!');
    }

    /**
     * Remove the specified coupon
     */
    public function destroy(Coupon $coupon, Request $request)
    {
        $coupon->delete();

        if ($request->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully!');
    }

    /**
     * Toggle coupon status
     */
    public function toggleStatus(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $coupon->is_active,
            'message' => 'Coupon status updated successfully'
        ]);
    }

    /**
     * Generate bulk coupons
     */
    public function generateBulk(Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:1000',
            'name_prefix' => 'required|string|max:50',
            'type' => 'required|in:percentage,fixed_amount,free_night,free_cleaning',
            'value' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:1',
            'user_limit' => 'nullable|integer|min:1',
        ]);

        $coupons = [];
        $quantity = $validated['quantity'];
        
        for ($i = 1; $i <= $quantity; $i++) {
            $coupons[] = [
                'code' => Coupon::generateUniqueCode(),
                'name' => $validated['name_prefix'] . ' #' . $i,
                'description' => 'Bulk generated coupon',
                'type' => $validated['type'],
                'value' => $validated['value'],
                'expires_at' => $validated['expires_at'] ?? null,
                'usage_limit' => $validated['usage_limit'] ?? null,
                'user_limit' => $validated['user_limit'] ?? 1,
                'is_active' => true,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Coupon::insert($coupons);

        return response()->json([
            'success' => true,
            'message' => "Successfully generated {$quantity} coupons",
            'quantity' => $quantity
        ]);
    }

    /**
     * Export coupons
     */
    public function export(Request $request)
    {
        $coupons = Coupon::with(['creator', 'usages'])
            ->withCount('usages')
            ->get();

        $filename = 'coupons_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($coupons) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Code', 'Name', 'Type', 'Value', 'Usage Count', 
                'Usage Limit', 'Status', 'Created By', 'Created At', 'Expires At'
            ]);

            // Data
            foreach ($coupons as $coupon) {
                fputcsv($file, [
                    $coupon->code,
                    $coupon->name,
                    $coupon->type,
                    $coupon->value,
                    $coupon->usages_count,
                    $coupon->usage_limit ?? 'Unlimited',
                    $coupon->is_active ? 'Active' : 'Inactive',
                    $coupon->creator->name ?? 'System',
                    $coupon->created_at->format('Y-m-d H:i:s'),
                    $coupon->expires_at ? $coupon->expires_at->format('Y-m-d H:i:s') : 'No expiry'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Validate coupon code
     */
    public function validateCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'booking_details' => 'required|array',
        ]);

        try {
            $result = Coupon::validateForBooking(
                $validated['code'],
                $validated['user_id'],
                $validated['booking_details']
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'error' => 'Error validating coupon: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get coupon usage analytics
     */
    public function analytics(Request $request)
    {
        $period = $request->input('period', '30'); // days
        $startDate = now()->subDays($period);

        $analytics = [
            'total_usage' => CouponUsage::where('created_at', '>=', $startDate)->count(),
            'total_discount' => CouponUsage::where('created_at', '>=', $startDate)->sum('discount_amount'),
            'unique_users' => CouponUsage::where('created_at', '>=', $startDate)
                ->distinct('user_id')
                ->count(),
            'top_coupons' => Coupon::withCount(['usages' => function ($query) use ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                }])
                ->orderBy('usages_count', 'desc')
                ->limit(10)
                ->get(['id', 'code', 'name', 'type', 'value']),
            'usage_by_day' => CouponUsage::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(discount_amount) as total_discount')
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return response()->json($analytics);
    }
}
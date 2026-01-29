<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Services\HostAnalyticsService;
use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HostDashboardController extends Controller
{
    protected HostAnalyticsService $analyticsService;
    protected PropertyService $propertyService;

    public function __construct(
        HostAnalyticsService $analyticsService,
        PropertyService $propertyService
    ) {
        $this->analyticsService = $analyticsService;
        $this->propertyService = $propertyService;
    }

    /**
     * Get dashboard overview data
     *
     * @return JsonResponse
     */
    public function getOverview(): JsonResponse
    {
        $user = Auth::user();
        
        $result = $this->analyticsService->getHostDashboardOverview($user->id);
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * Get booking analytics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBookingAnalytics(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $period = $request->input('period', '30days');
        $propertyId = $request->input('property_id');
        
        $result = $this->analyticsService->getBookingAnalytics($user->id, $period, $propertyId);
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * Get revenue analytics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRevenueAnalytics(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $period = $request->input('period', '30days');
        $propertyId = $request->input('property_id');
        
        $result = $this->analyticsService->getRevenueAnalytics($user->id, $period, $propertyId);
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * Get occupancy analytics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getOccupancyAnalytics(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $period = $request->input('period', '30days');
        $propertyId = $request->input('property_id');
        
        $result = $this->analyticsService->getOccupancyAnalytics($user->id, $period, $propertyId);
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * Get calendar view for all properties
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCalendarView(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $propertyId = $request->input('property_id');
        
        $result = $this->analyticsService->getCalendarView($user->id, $startDate, $endDate, $propertyId);
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * Get property performance comparison
     *
     * @return JsonResponse
     */
    public function getPropertyComparison(): JsonResponse
    {
        $user = Auth::user();
        
        $result = $this->analyticsService->getPropertyPerformanceComparison($user->id);
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * Get guest analytics
     *
     * @return JsonResponse
     */
    public function getGuestAnalytics(): JsonResponse
    {
        $user = Auth::user();
        
        $result = $this->analyticsService->getGuestAnalytics($user->id);
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * Get review analytics
     *
     * @return JsonResponse
     */
    public function getReviewAnalytics(): JsonResponse
    {
        $user = Auth::user();
        
        $result = $this->analyticsService->getReviewAnalytics($user->id);
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * Get AI-powered recommendations
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $user = Auth::user();
        $propertyId = $request->input('property_id');
        
        $result = $this->analyticsService->getAIRecommendations($user->id, $propertyId);
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * Update price optimization settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePriceOptimization(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'enabled' => 'required|boolean',
            'min_price' => 'required_if:enabled,true|numeric|min:0',
            'max_price' => 'required_if:enabled,true|numeric|gt:min_price',
            'strategy' => 'required_if:enabled,true|string|in:conservative,balanced,aggressive'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $propertyId = $request->input('property_id');
        
        // Verify property belongs to this host
        $property = Property::where('id', $propertyId)
            ->where('user_id', $user->id)
            ->firstOrFail();
            
        $result = $this->propertyService->updatePriceOptimizationSettings(
            $property,
            $request->input('enabled'),
            $request->input('min_price'),
            $request->input('max_price'),
            $request->input('strategy')
        );
        
        return response()->json([
            'success' => $result,
            'message' => $result ? 'Price optimization settings updated' : 'Failed to update settings'
        ]);
    }
    
    /**
     * Get transaction history
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTransactionHistory(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $propertyId = $request->input('property_id');
        $type = $request->input('type');
        $perPage = $request->input('per_page', 15);
        
        $result = $this->analyticsService->getTransactionHistory(
            $user->id, 
            $startDate, 
            $endDate, 
            $propertyId,
            $type,
            $perPage
        );
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * Get financial summary
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFinancialSummary(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $period = $request->input('period', 'this_month');
        
        $result = $this->analyticsService->getFinancialSummary($user->id, $period);
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * Generate and download financial report
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateFinancialReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|string|in:csv,pdf'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $format = $request->input('format');
        
        $result = $this->analyticsService->generateFinancialReport(
            $user->id,
            $startDate,
            $endDate,
            $format
        );
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => [
                    'download_url' => $result['download_url']
                ],
                'message' => 'Report generated successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 500);
        }
    }
}

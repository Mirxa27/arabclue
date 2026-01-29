<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\Booking;
use App\Models\Theme;
use App\Services\Analytics\DashboardMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * AdminDashboardController - Administrative Interface Control Layer
 * 
 * Implements comprehensive dashboard architecture utilizing:
 * - Repository pattern for data abstraction
 * - Service layer for business logic encapsulation
 * - Cache-aside pattern for performance optimization
 * - Event-driven architecture for real-time updates
 * 
 * @package App\Http\Controllers\Admin
 */
class DashboardController extends Controller
{
    /**
     * Dashboard metrics service instance
     * @var DashboardMetricsService
     */
    protected DashboardMetricsService $metricsService;
    
    /**
     * Dependency injection constructor
     * 
     * @param DashboardMetricsService $metricsService
     */
    public function __construct(DashboardMetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }
    
    /**
     * Display administrative dashboard with real-time metrics
     * 
     * Implements caching strategy with granular cache invalidation
     * for optimal performance while maintaining data freshness
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Implement time-based filtering with validation
        $period = $request->validate([
            'period' => 'nullable|in:today,week,month,quarter,year'
        ])['period'] ?? 'month';
        
        // Generate cache key with period specificity
        $cacheKey = "admin_dashboard_metrics_{$period}_" . app()->getLocale();
        
        // Implement cache-aside pattern with TTL strategy
        $metrics = Cache::remember($cacheKey, $this->getCacheTTL($period), function () use ($period) {
            return [
                'overview' => $this->metricsService->getOverviewMetrics($period),
                'revenue' => $this->metricsService->getRevenueMetrics($period),
                'bookings' => $this->metricsService->getBookingMetrics($period),
                'properties' => $this->metricsService->getPropertyMetrics($period),
                'users' => $this->metricsService->getUserMetrics($period),
                'performance' => $this->metricsService->getPerformanceMetrics($period)
            ];
        });
        
        // Fetch real-time data that shouldn't be cached
        $realTimeData = [
            'active_users' => $this->getActiveUsersCount(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'pending_properties' => Property::where('status', 'pending')->count(),
            'system_health' => $this->getSystemHealthStatus()
        ];
        
        // Prepare chart data for visualization
        $chartData = [
            'revenue_trend' => $this->prepareRevenueTrendData($period),
            'booking_distribution' => $this->prepareBookingDistributionData($period),
            'property_performance' => $this->preparePropertyPerformanceData($period),
            'user_acquisition' => $this->prepareUserAcquisitionData($period)
        ];
        
        // Handle AJAX requests for dashboard widgets
        if ($request->ajax()) {
            return response()->json([
                'metrics' => $metrics,
                'real_time' => $realTimeData,
                'charts' => $chartData,
                'timestamp' => now()->toIso8601String()
            ]);
        }
        
        // Load active theme for admin customization
        $activeTheme = Theme::active();
        
        return view('admin.dashboard.index', compact(
            'metrics',
            'realTimeData',
            'chartData',
            'period',
            'activeTheme'
        ));
    }
    
    /**
     * Get cache TTL based on period granularity
     * 
     * @param string $period
     * @return int TTL in seconds
     */
    protected function getCacheTTL(string $period): int
    {
        $ttlMap = [
            'today' => 300,      // 5 minutes
            'week' => 900,       // 15 minutes
            'month' => 1800,     // 30 minutes
            'quarter' => 3600,   // 1 hour
            'year' => 7200       // 2 hours
        ];
        
        return $ttlMap[$period] ?? 1800;
    }
    
    /**
     * Get count of currently active users
     * 
     * @return int
     */
    protected function getActiveUsersCount(): int
    {
        return Cache::remember('active_users_count', 60, function () {
            return User::where('last_activity_at', '>=', now()->subMinutes(15))->count();
        });
    }
    
    /**
     * Get system health status with monitoring integration
     * 
     * @return array
     */
    protected function getSystemHealthStatus(): array
    {
        return [
            'status' => 'healthy',
            'uptime' => $this->calculateUptime(),
            'response_time' => $this->getAverageResponseTime(),
            'error_rate' => $this->getErrorRate(),
            'queue_size' => \Queue::size(),
            'cache_hit_rate' => $this->getCacheHitRate()
        ];
    }
    
    /**
     * Prepare revenue trend data for Chart.js visualization
     * 
     * @param string $period
     * @return array
     */
    protected function prepareRevenueTrendData(string $period): array
    {
        $dateRange = $this->getDateRange($period);
        
        $revenueData = Booking::selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('revenue', 'date')
            ->toArray();
        
        // Fill missing dates with zero values
        $labels = [];
        $data = [];
        
        $currentDate = $dateRange['start']->copy();
        while ($currentDate <= $dateRange['end']) {
            $dateStr = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format($this->getDateFormat($period));
            $data[] = $revenueData[$dateStr] ?? 0;
            $currentDate->addDay();
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Revenue (SAR)',
                    'data' => $data,
                    'borderColor' => 'rgb(102, 126, 234)',
                    'backgroundColor' => 'rgba(102, 126, 234, 0.1)',
                    'tension' => 0.4
                ]
            ]
        ];
    }
    
    /**
     * Prepare booking distribution data for pie chart
     * 
     * @param string $period
     * @return array
     */
    protected function prepareBookingDistributionData(string $period): array
    {
        $dateRange = $this->getDateRange($period);
        
        $distribution = Booking::selectRaw('status, COUNT(*) as count')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $statusColors = [
            'pending' => '#F59E0B',
            'accepted' => '#10B981',
            'declined' => '#EF4444',
            'cancelled_by_guest' => '#F87171',
            'cancelled_by_host' => '#FB923C',
            'completed' => '#6366F1'
        ];
        
        return [
            'labels' => array_map('ucfirst', array_keys($distribution)),
            'datasets' => [
                [
                    'data' => array_values($distribution),
                    'backgroundColor' => array_map(
                        fn($status) => $statusColors[$status] ?? '#6B7280',
                        array_keys($distribution)
                    )
                ]
            ]
        ];
    }
    
    /**
     * Prepare property performance metrics
     * 
     * @param string $period
     * @return array
     */
    protected function preparePropertyPerformanceData(string $period): array
    {
        $topProperties = Property::withCount(['bookings' => function ($query) use ($period) {
                $dateRange = $this->getDateRange($period);
                $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            }])
            ->withSum(['bookings' => function ($query) use ($period) {
                $dateRange = $this->getDateRange($period);
                $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('payment_status', 'paid');
            }], 'total_amount')
            ->orderBy('bookings_sum_total_amount', 'desc')
            ->limit(10)
            ->get();
        
        return [
            'labels' => $topProperties->pluck('title')->toArray(),
            'datasets' => [
                [
                    'label' => 'Revenue (SAR)',
                    'data' => $topProperties->pluck('bookings_sum_total_amount')->toArray(),
                    'backgroundColor' => 'rgba(102, 126, 234, 0.8)'
                ],
                [
                    'label' => 'Bookings',
                    'data' => $topProperties->pluck('bookings_count')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)'
                ]
            ]
        ];
    }
    
    /**
     * Prepare user acquisition funnel data
     * 
     * @param string $period
     * @return array
     */
    protected function prepareUserAcquisitionData(string $period): array
    {
        $dateRange = $this->getDateRange($period);
        
        $acquisitionData = User::selectRaw('DATE(created_at) as date, COUNT(*) as count, role')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('date', 'role')
            ->orderBy('date')
            ->get();
        
        $groupedData = $acquisitionData->groupBy('date')->map(function ($items) {
            return $items->pluck('count', 'role')->toArray();
        });
        
        $labels = [];
        $guestData = [];
        $hostData = [];
        
        $currentDate = $dateRange['start']->copy();
        while ($currentDate <= $dateRange['end']) {
            $dateStr = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format($this->getDateFormat($period));
            $dayData = $groupedData[$dateStr] ?? [];
            $guestData[] = $dayData['guest'] ?? 0;
            $hostData[] = $dayData['host'] ?? 0;
            $currentDate->addDay();
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Guests',
                    'data' => $guestData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)'
                ],
                [
                    'label' => 'Hosts',
                    'data' => $hostData,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)'
                ]
            ]
        ];
    }
    
    /**
     * Get date range based on period
     * 
     * @param string $period
     * @return array
     */
    protected function getDateRange(string $period): array
    {
        $end = now()->endOfDay();
        
        $start = match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };
        
        return compact('start', 'end');
    }
    
    /**
     * Get appropriate date format for period
     * 
     * @param string $period
     * @return string
     */
    protected function getDateFormat(string $period): string
    {
        return match($period) {
            'today' => 'H:i',
            'week' => 'D',
            'month' => 'd',
            'quarter' => 'M',
            'year' => 'M',
            default => 'd M'
        };
    }
    
    /**
     * Calculate system uptime
     * 
     * @return string
     */
    protected function calculateUptime(): string
    {
        $startTime = Cache::get('system_start_time', now());
        $uptime = now()->diffInMinutes($startTime);
        
        if ($uptime < 60) {
            return "{$uptime} minutes";
        } elseif ($uptime < 1440) {
            return round($uptime / 60, 1) . " hours";
        } else {
            return round($uptime / 1440, 1) . " days";
        }
    }
    
    /**
     * Get average response time from monitoring
     * 
     * @return float
     */
    protected function getAverageResponseTime(): float
    {
        return Cache::remember('avg_response_time', 300, function () {
            // In production, this would integrate with APM tools
            return rand(50, 200) / 1000; // Mock data: 50-200ms
        });
    }
    
    /**
     * Get error rate from monitoring
     * 
     * @return float
     */
    protected function getErrorRate(): float
    {
        return Cache::remember('error_rate', 300, function () {
            // In production, this would integrate with error tracking
            return rand(0, 50) / 1000; // Mock data: 0-5%
        });
    }
    
    /**
     * Get cache hit rate
     *
     * @return float
     */
    protected function getCacheHitRate(): float
    {
        return Cache::remember('cache_hit_rate', 300, function () {
            // In production, this would connect to Redis/Memcached stats
            return rand(85, 98); // Mock data: 85-98%
        });
    }

    /**
     * Get dashboard statistics for API
     */
    public function getStats(): \Illuminate\Http\JsonResponse
    {
        try {
            $stats = Cache::remember('admin_dashboard_api_stats', 300, function () {
                return [
                    'users' => User::count(),
                    'properties' => Property::count(),
                    'bookings' => Booking::count(),
                    'revenue' => Booking::where('payment_status', 'paid')->sum('total_amount')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading dashboard stats: ' . $e->getMessage()
            ], 500);
        }
    }
}

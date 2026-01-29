<?php

namespace App\Services;

use App\Models\User;
use App\Models\Property;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get host earnings chart data
     */
    public function getHostEarningsChart(int $hostId, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        $earnings = Booking::where('host_id', $hostId)
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(host_payout_amount) as earnings')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartData = [];
        $current = $startDate->copy();
        
        while ($current->lte(now())) {
            $dateStr = $current->toDateString();
            $earning = $earnings->firstWhere('date', $dateStr);
            
            $chartData[] = [
                'date' => $dateStr,
                'earnings' => $earning ? (float) $earning->earnings : 0
            ];
            
            $current->addDay();
        }

        return $chartData;
    }

    /**
     * Get host occupancy rate
     */
    public function getHostOccupancyRate(int $hostId, int $days = 30): array
    {
        $host = User::findOrFail($hostId);
        $startDate = now()->subDays($days);

        // Get all properties for the host
        $properties = $host->properties()->where('status', 'active')->get();
        
        if ($properties->isEmpty()) {
            return ['rate' => 0, 'booked_nights' => 0, 'available_nights' => 0];
        }

        $totalAvailableNights = 0;
        $totalBookedNights = 0;

        foreach ($properties as $property) {
            // Calculate available nights (excluding blocked dates)
            $availableNights = $days;
            
            // Calculate booked nights
            $bookedNights = Booking::where('property_id', $property->id)
                ->where('status', 'confirmed')
                ->where(function($query) use ($startDate) {
                    $query->where('check_in', '>=', $startDate)
                          ->orWhere('check_out', '>=', $startDate);
                })
                // Correct way to sum a raw expression
                ->selectRaw('SUM(julianday(LEAST(check_out, NOW())) - julianday(GREATEST(check_in, ?))) as total_booked_duration', [$startDate->toDateString()])
                ->value('total_booked_duration') ?? 0;

            $totalAvailableNights += $availableNights;
            $totalBookedNights += (int)$bookedNights; // Cast to int as value() can return null
        }

        $occupancyRate = $totalAvailableNights > 0 ? ($totalBookedNights / $totalAvailableNights) * 100 : 0;

        return [
            'rate' => round($occupancyRate, 2),
            'booked_nights' => $totalBookedNights,
            'available_nights' => $totalAvailableNights
        ];
    }

    /**
     * Get host earnings breakdown
     */
    public function getHostEarnings(int $hostId, string $period = 'month', int $year = null, int $month = null): array
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $query = Booking::where('host_id', $hostId)
            ->where('payment_status', 'paid');

        switch ($period) {
            case 'month':
                $query->whereYear('created_at', $year)
                      ->whereMonth('created_at', $month);
                break;
            case 'year':
                $query->whereYear('created_at', $year);
                break;
            case 'all':
                // No additional filters
                break;
        }

        $bookings = $query->get();

        $totalEarnings = $bookings->sum('host_payout_amount');
        $totalBookings = $bookings->count();
        $averageBookingValue = $totalBookings > 0 ? $totalEarnings / $totalBookings : 0;

        // Breakdown by property
        $propertyBreakdown = $bookings->groupBy('property_id')->map(function ($propertyBookings) {
            $property = $propertyBookings->first()->property;
            return [
                'property_id' => $property->id,
                'property_title' => $property->title,
                'earnings' => $propertyBookings->sum('host_payout_amount'),
                'bookings' => $propertyBookings->count(),
                'average_value' => $propertyBookings->avg('host_payout_amount')
            ];
        })->values();

        // Monthly breakdown for the year
        $monthlyBreakdown = [];
        if ($period === 'year') {
            for ($m = 1; $m <= 12; $m++) {
                $monthEarnings = $bookings->filter(function ($booking) use ($m) {
                    return Carbon::parse($booking->created_at)->month === $m;
                })->sum('host_payout_amount');

                $monthlyBreakdown[] = [
                    'month' => $m,
                    'month_name' => Carbon::create()->month($m)->format('F'),
                    'earnings' => $monthEarnings
                ];
            }
        }

        return [
            'total_earnings' => $totalEarnings,
            'total_bookings' => $totalBookings,
            'average_booking_value' => round($averageBookingValue, 2),
            'property_breakdown' => $propertyBreakdown,
            'monthly_breakdown' => $monthlyBreakdown,
            'period' => $period,
            'year' => $year,
            'month' => $month
        ];
    }

    /**
     * Get comprehensive host analytics
     */
    public function getHostAnalytics(int $hostId, int $days = 30): array
    {
        $host = User::findOrFail($hostId);
        $startDate = now()->subDays($days);

        // Basic stats
        $totalProperties = $host->properties()->count();
        $activeProperties = $host->properties()->where('status', 'active')->count();
        
        // Booking stats
        $totalBookings = $host->hostBookings()->count();
        $periodBookings = $host->hostBookings()->where('created_at', '>=', $startDate)->count();
        $confirmedBookings = $host->hostBookings()->where('status', 'confirmed')->count();
        $pendingBookings = $host->hostBookings()->where('status', 'pending')->count();

        // Revenue stats
        $totalRevenue = $host->hostBookings()->where('payment_status', 'paid')->sum('host_payout_amount');
        $periodRevenue = $host->hostBookings()
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->sum('host_payout_amount');

        // Review stats
        $totalReviews = $host->hostReviews()->count();
        $averageRating = $host->hostReviews()->avg('rating') ?? 0;
        $recentReviews = $host->hostReviews()
            ->where('created_at', '>=', $startDate)
            ->count();

        // Performance metrics
        $responseRate = $this->calculateResponseRate($hostId, $days);
        $acceptanceRate = $this->calculateAcceptanceRate($hostId, $days);
        $occupancyRate = $this->getHostOccupancyRate($hostId, $days);

        // Top performing properties
        $topProperties = $host->properties()
            ->withCount(['bookings as total_bookings'])
            ->withSum(['bookings as total_earnings' => function($query) {
                $query->where('payment_status', 'paid');
            }], 'host_payout_amount')
            ->withAvg('reviews', 'rating')
            ->orderBy('total_earnings', 'desc')
            ->limit(5)
            ->get();

        // Booking trends (last 12 months)
        $bookingTrends = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthBookings = $host->hostBookings()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $bookingTrends[] = [
                'month' => $date->format('M Y'),
                'bookings' => $monthBookings
            ];
        }

        // Revenue trends (last 12 months)
        $revenueTrends = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthRevenue = $host->hostBookings()
                ->where('payment_status', 'paid')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('host_payout_amount');

            $revenueTrends[] = [
                'month' => $date->format('M Y'),
                'revenue' => $monthRevenue
            ];
        }

        return [
            'overview' => [
                'total_properties' => $totalProperties,
                'active_properties' => $activeProperties,
                'total_bookings' => $totalBookings,
                'period_bookings' => $periodBookings,
                'confirmed_bookings' => $confirmedBookings,
                'pending_bookings' => $pendingBookings,
                'total_revenue' => $totalRevenue,
                'period_revenue' => $periodRevenue,
                'total_reviews' => $totalReviews,
                'average_rating' => round($averageRating, 2),
                'recent_reviews' => $recentReviews
            ],
            'performance' => [
                'response_rate' => $responseRate,
                'acceptance_rate' => $acceptanceRate,
                'occupancy_rate' => $occupancyRate['rate']
            ],
            'top_properties' => $topProperties,
            'trends' => [
                'bookings' => $bookingTrends,
                'revenue' => $revenueTrends
            ]
        ];
    }

    /**
     * Calculate response rate
     */
    protected function calculateResponseRate(int $hostId, int $days = 30): float
    {
        $startDate = now()->subDays($days);
        
        $totalInquiries = Booking::where('host_id', $hostId)
            ->where('created_at', '>=', $startDate)
            ->count();

        if ($totalInquiries === 0) return 100;

        $respondedInquiries = Booking::where('host_id', $hostId)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('host_message')
            ->count();

        return round(($respondedInquiries / $totalInquiries) * 100, 2);
    }

    /**
     * Calculate acceptance rate
     */
    protected function calculateAcceptanceRate(int $hostId, int $days = 30): float
    {
        $startDate = now()->subDays($days);
        
        $totalRequests = Booking::where('host_id', $hostId)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', ['pending', 'confirmed', 'declined'])
            ->count();

        if ($totalRequests === 0) return 100;

        $acceptedRequests = Booking::where('host_id', $hostId)
            ->where('created_at', '>=', $startDate)
            ->where('status', 'confirmed')
            ->count();

        return round(($acceptedRequests / $totalRequests) * 100, 2);
    }

    /**
     * Get platform-wide analytics (for admin)
     */
    public function getPlatformAnalytics(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'users' => [
                'total' => User::count(),
                'new_period' => User::where('created_at', '>=', $startDate)->count(),
                'verified' => User::where('identity_verified', true)->count(),
                'hosts' => User::whereHas('properties')->count()
            ],
            'properties' => [
                'total' => Property::count(),
                'active' => Property::where('status', 'active')->count(),
                'pending' => Property::where('status', 'pending')->count(),
                'new_period' => Property::where('created_at', '>=', $startDate)->count()
            ],
            'bookings' => [
                'total' => Booking::count(),
                'period' => Booking::where('created_at', '>=', $startDate)->count(),
                'confirmed' => Booking::where('status', 'confirmed')->count(),
                'pending' => Booking::where('status', 'pending')->count(),
                'cancelled' => Booking::where('status', 'cancelled')->count()
            ],
            'revenue' => [
                'total' => Booking::where('payment_status', 'paid')->sum('total_amount'),
                'period' => Booking::where('payment_status', 'paid')
                    ->where('created_at', '>=', $startDate)
                    ->sum('total_amount'),
                'platform_fees' => Booking::where('payment_status', 'paid')->sum('service_fee')
            ],
            'reviews' => [
                'total' => Review::count(),
                'period' => Review::where('created_at', '>=', $startDate)->count(),
                'average_rating' => Review::avg('rating') ?? 0
            ]
        ];
    }

    public function generateFinancialReport(int $hostId, string $startDate, string $endDate): array
    {
        $bookings = Booking::where('host_id', $hostId)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('property')
            ->get();

        $totalEarnings = $bookings->sum('host_payout_amount');
        $totalBookings = $bookings->count();

        $propertyBreakdown = $bookings->groupBy('property_id')->map(function ($items) {
            $property = $items->first()->property;
            return [
                'property_id' => $property->id,
                'title' => $property->title,
                'bookings' => $items->count(),
                'earnings' => $items->sum('host_payout_amount'),
            ];
        })->values();

        return [
            'host_id' => $hostId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_earnings' => $totalEarnings,
            'total_bookings' => $totalBookings,
            'property_breakdown' => $propertyBreakdown
        ];
    }
}

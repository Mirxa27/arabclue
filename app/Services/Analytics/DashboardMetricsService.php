<?php

namespace App\Services\Analytics;

use App\Models\User;
use App\Models\Property;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Dashboard Metrics Service
 * 
 * Provides analytics and metrics for admin dashboard
 */
class DashboardMetricsService
{
    /**
     * Get overview metrics
     */
    public function getOverviewMetrics(string $period): array
    {
        return [
            'total_users' => User::count(),
            'total_properties' => Property::count(),
            'total_bookings' => Booking::count(),
            'total_revenue' => Booking::where('payment_status', 'paid')->sum('total_amount')
        ];
    }

    /**
     * Get revenue metrics
     */
    public function getRevenueMetrics(string $period): array
    {
        $dateRange = $this->getDateRange($period);
        
        return [
            'total_revenue' => Booking::where('payment_status', 'paid')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->sum('total_amount'),
            'commission_earned' => Booking::where('payment_status', 'paid')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->sum('platform_fee'),
            'average_booking_value' => Booking::where('payment_status', 'paid')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->avg('total_amount') ?? 0
        ];
    }

    /**
     * Get booking metrics
     */
    public function getBookingMetrics(string $period): array
    {
        $dateRange = $this->getDateRange($period);
        
        return [
            'total_bookings' => Booking::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count(),
            'pending_bookings' => Booking::where('status', 'pending')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count(),
            'cancelled_bookings' => Booking::whereIn('status', ['cancelled_by_guest', 'cancelled_by_host'])
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count()
        ];
    }

    /**
     * Get property metrics
     */
    public function getPropertyMetrics(string $period): array
    {
        $dateRange = $this->getDateRange($period);
        
        return [
            'total_properties' => Property::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count(),
            'active_properties' => Property::where('status', 'active')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count(),
            'pending_properties' => Property::where('status', 'pending')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count(),
            'average_rating' => Property::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->avg('overall_rating') ?? 0
        ];
    }

    /**
     * Get user metrics
     */
    public function getUserMetrics(string $period): array
    {
        $dateRange = $this->getDateRange($period);
        
        return [
            'total_users' => User::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count(),
            'new_guests' => User::where('role', 'guest')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count(),
            'new_hosts' => User::where('role', 'host')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count(),
            'verified_users' => User::whereNotNull('email_verified_at')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count()
        ];
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(string $period): array
    {
        return [
            'occupancy_rate' => $this->calculateOccupancyRate($period),
            'average_stay_duration' => $this->calculateAverageStayDuration($period),
            'conversion_rate' => $this->calculateConversionRate($period),
            'repeat_booking_rate' => $this->calculateRepeatBookingRate($period)
        ];
    }

    /**
     * Calculate occupancy rate
     */
    private function calculateOccupancyRate(string $period): float
    {
        $dateRange = $this->getDateRange($period);
        $totalDays = $dateRange['start']->diffInDays($dateRange['end']);
        $totalProperties = Property::where('status', 'active')->count();
        $totalAvailableDays = $totalDays * $totalProperties;

        // Use Carbon for cross-database compatibility
        $bookings = Booking::where('status', 'confirmed')
            ->whereBetween('check_in', [$dateRange['start'], $dateRange['end']])
            ->select('check_in', 'check_out')
            ->get();

        $bookedDays = $bookings->sum(function ($booking) {
            return \Carbon\Carbon::parse($booking->check_out)->diffInDays(\Carbon\Carbon::parse($booking->check_in));
        });

        return $totalAvailableDays > 0 ? round(($bookedDays / $totalAvailableDays) * 100, 2) : 0;
    }

    /**
     * Calculate average stay duration
     */
    private function calculateAverageStayDuration(string $period): float
    {
        $dateRange = $this->getDateRange($period);
        
        // Use Carbon for cross-database compatibility
        $bookings = Booking::where('status', 'confirmed')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select('check_in', 'check_out')
            ->get();

        if ($bookings->isEmpty()) {
            return 0;
        }

        $totalDuration = $bookings->sum(function ($booking) {
            return \Carbon\Carbon::parse($booking->check_out)->diffInDays(\Carbon\Carbon::parse($booking->check_in));
        });

        return round($totalDuration / $bookings->count(), 2);
    }

    /**
     * Calculate conversion rate
     */
    private function calculateConversionRate(string $period): float
    {
        $dateRange = $this->getDateRange($period);
        
        $totalInquiries = Booking::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count();
        $confirmedBookings = Booking::where('status', 'confirmed')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();
        
        return $totalInquiries > 0 ? round(($confirmedBookings / $totalInquiries) * 100, 2) : 0;
    }

    /**
     * Calculate repeat booking rate
     */
    private function calculateRepeatBookingRate(string $period): float
    {
        $dateRange = $this->getDateRange($period);
        
        $totalUsers = User::whereHas('bookings', function ($query) use ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        })->count();
        
        $repeatUsers = User::whereHas('bookings', function ($query) use ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }, '>', 1)->count();
        
        return $totalUsers > 0 ? round(($repeatUsers / $totalUsers) * 100, 2) : 0;
    }

    /**
     * Get date range based on period
     */
    private function getDateRange(string $period): array
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
}

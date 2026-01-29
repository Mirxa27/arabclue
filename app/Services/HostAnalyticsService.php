<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Property;
use App\Models\PropertyCalendar;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HostAnalyticsService
{
    /**
     * Get host dashboard overview data
     *
     * @param int $userId
     * @return array
     */
    public function getHostDashboardOverview(int $userId): array
    {
        // Cache key that's unique to the user
        $cacheKey = "host_dashboard_overview_{$userId}";
        
        // Cache for 6 hours
        return Cache::remember($cacheKey, 21600, function () use ($userId) {
            // Get all properties for this host
            $properties = Property::where('user_id', $userId)->get();
            $propertyIds = $properties->pluck('id')->toArray();
            
            // Get key metrics
            $totalBookings = Booking::whereIn('property_id', $propertyIds)->count();
            $confirmedBookings = Booking::whereIn('property_id', $propertyIds)
                ->whereIn('status', ['confirmed', 'completed'])
                ->count();
            $totalRevenue = Booking::whereIn('property_id', $propertyIds)
                ->whereIn('status', ['confirmed', 'completed'])
                ->sum('total_price');
            $pendingBookings = Booking::whereIn('property_id', $propertyIds)
                ->where('status', 'pending')
                ->count();
                
            // Get recent booking stats (last 30 days)
            $recentBookings = Booking::whereIn('property_id', $propertyIds)
                ->where('created_at', '>=', now()->subDays(30))
                ->count();
            $recentRevenue = Booking::whereIn('property_id', $propertyIds)
                ->whereIn('status', ['confirmed', 'completed'])
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('total_price');
                
            // Get average rating
            $avgRating = Review::whereIn('property_id', $propertyIds)->avg('rating') ?? 0;
            $reviewCount = Review::whereIn('property_id', $propertyIds)->count();
            
            // Get upcoming bookings
            $upcomingBookings = Booking::whereIn('property_id', $propertyIds)
                ->whereIn('status', ['confirmed'])
                ->where('check_in', '>=', now())
                ->orderBy('check_in')
                ->limit(5)
                ->with(['property:id,title,city', 'user:id,name,email,avatar'])
                ->get();
                
            // Get recent reviews
            $recentReviews = Review::whereIn('property_id', $propertyIds)
                ->orderByDesc('created_at')
                ->limit(3)
                ->with(['property:id,title', 'user:id,name,avatar'])
                ->get();
                
            // Get revenue by property
            $propertyRevenue = Booking::whereIn('property_id', $propertyIds)
                ->whereIn('status', ['confirmed', 'completed'])
                ->select('property_id', DB::raw('SUM(total_price) as total_revenue'), DB::raw('COUNT(*) as booking_count'))
                ->groupBy('property_id')
                ->get()
                ->keyBy('property_id');
                
            $propertyStats = $properties->map(function ($property) use ($propertyRevenue) {
                $stats = $propertyRevenue->get($property->id);
                
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'city' => $property->city,
                    'image' => $property->primary_image,
                    'revenue' => $stats ? $stats->total_revenue : 0,
                    'bookings' => $stats ? $stats->booking_count : 0
                ];
            })->sortByDesc('revenue')->values();
            
            // Calculate occupancy rate
            $occupancyRate = $this->calculateOccupancyRate($propertyIds);
            
            // Calculate review sentiment
            $reviewSentiment = $this->calculateReviewSentiment($propertyIds);
            
            return [
                'total_properties' => count($propertyIds),
                'total_bookings' => $totalBookings,
                'confirmed_bookings' => $confirmedBookings,
                'pending_bookings' => $pendingBookings,
                'total_revenue' => $totalRevenue,
                'avg_rating' => round($avgRating, 1),
                'review_count' => $reviewCount,
                'occupancy_rate' => $occupancyRate,
                'recent_bookings' => $recentBookings,
                'recent_revenue' => $recentRevenue,
                'upcoming_bookings' => $upcomingBookings,
                'recent_reviews' => $recentReviews,
                'property_stats' => $propertyStats,
                'review_sentiment' => $reviewSentiment
            ];
        });
    }
    
    /**
     * Get booking analytics
     *
     * @param int $userId
     * @param string $period
     * @param int|null $propertyId
     * @return array
     */
    public function getBookingAnalytics(int $userId, string $period = '30days', ?int $propertyId = null): array
    {
        // Determine date range based on period
        $datePeriod = $this->getDatePeriodRange($period);
        $startDate = $datePeriod['start'];
        $endDate = $datePeriod['end'];
        
        // Build base query
        $query = Booking::whereHas('property', function (Builder $query) use ($userId) {
            $query->where('user_id', $userId);
        });
        
        // Apply property filter if provided
        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }
        
        // Apply date range filter
        $query->whereBetween('created_at', [$startDate, $endDate]);
        
        // Get booking statistics
        $totalBookings = $query->count();
        $confirmedBookings = (clone $query)->whereIn('status', ['confirmed', 'completed'])->count();
        $cancelledBookings = (clone $query)->where('status', 'cancelled')->count();
        
        // Get daily bookings data
        $dailyBookings = (clone $query)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status IN ("confirmed", "completed") THEN 1 ELSE 0 END) as confirmed'),
                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Format data for chart
        $labels = [];
        $totalSeries = [];
        $confirmedSeries = [];
        $cancelledSeries = [];
        
        $currentDate = Carbon::parse($startDate);
        $lastDate = Carbon::parse($endDate);
        
        while ($currentDate->lte($lastDate)) {
            $dateString = $currentDate->format('Y-m-d');
            $dayData = $dailyBookings->firstWhere('date', $dateString);
            
            $labels[] = $currentDate->format('M d');
            $totalSeries[] = $dayData ? (int)$dayData->total : 0;
            $confirmedSeries[] = $dayData ? (int)$dayData->confirmed : 0;
            $cancelledSeries[] = $dayData ? (int)$dayData->cancelled : 0;
            
            $currentDate->addDay();
        }
        
        // Calculate conversion rate
        $conversionRate = $totalBookings > 0 ? round(($confirmedBookings / $totalBookings) * 100, 2) : 0;
        
        // Get top booking sources if available in the data
        $bookingSources = (clone $query)
            ->whereNotNull('source')
            ->select('source', DB::raw('COUNT(*) as count'))
            ->groupBy('source')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
            
        // Guest demographics if available
        $guestDemographics = (clone $query)
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->whereNotNull('users.country')
            ->select('users.country', DB::raw('COUNT(*) as count'))
            ->groupBy('users.country')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
            
        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'metrics' => [
                'total_bookings' => $totalBookings,
                'confirmed_bookings' => $confirmedBookings,
                'cancelled_bookings' => $cancelledBookings,
                'conversion_rate' => $conversionRate
            ],
            'chart' => [
                'labels' => $labels,
                'series' => [
                    [
                        'name' => 'Total Bookings',
                        'data' => $totalSeries
                    ],
                    [
                        'name' => 'Confirmed',
                        'data' => $confirmedSeries
                    ],
                    [
                        'name' => 'Cancelled',
                        'data' => $cancelledSeries
                    ]
                ]
            ],
            'booking_sources' => $bookingSources,
            'guest_demographics' => $guestDemographics
        ];
    }
    
    /**
     * Get revenue analytics
     *
     * @param int $userId
     * @param string $period
     * @param int|null $propertyId
     * @return array
     */
    public function getRevenueAnalytics(int $userId, string $period = '30days', ?int $propertyId = null): array
    {
        // Determine date range
        $datePeriod = $this->getDatePeriodRange($period);
        $startDate = $datePeriod['start'];
        $endDate = $datePeriod['end'];
        
        // Build base query
        $query = Booking::whereHas('property', function (Builder $query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->whereIn('status', ['confirmed', 'completed']);
        
        // Apply property filter
        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }
        
        // Apply date range
        $query->whereBetween('created_at', [$startDate, $endDate]);
        
        // Get total revenue
        $totalRevenue = $query->sum('total_price');
        $serviceFeesTotal = $query->sum('service_fee');
        $cleaningFeesTotal = $query->sum('cleaning_fee');
        $netRevenue = $totalRevenue - $serviceFeesTotal;
        
        // Get daily revenue
        $dailyRevenue = (clone $query)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('SUM(service_fee) as service_fees'),
                DB::raw('SUM(cleaning_fee) as cleaning_fees')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Format for chart
        $labels = [];
        $revenueSeries = [];
        $serviceFeeSeries = [];
        $netRevenueSeries = [];
        
        $currentDate = Carbon::parse($startDate);
        $lastDate = Carbon::parse($endDate);
        
        while ($currentDate->lte($lastDate)) {
            $dateString = $currentDate->format('Y-m-d');
            $dayData = $dailyRevenue->firstWhere('date', $dateString);
            
            $labels[] = $currentDate->format('M d');
            $revenue = $dayData ? (float)$dayData->revenue : 0;
            $serviceFee = $dayData ? (float)$dayData->service_fees : 0;
            
            $revenueSeries[] = $revenue;
            $serviceFeeSeries[] = $serviceFee;
            $netRevenueSeries[] = $revenue - $serviceFee;
            
            $currentDate->addDay();
        }
        
        // Get revenue by property
        $propertyRevenue = [];
        
        if (!$propertyId) {
            $propertyRevenue = (clone $query)
                ->select(
                    'property_id',
                    DB::raw('SUM(total_price) as revenue'),
                    DB::raw('COUNT(*) as bookings')
                )
                ->groupBy('property_id')
                ->with('property:id,title,city')
                ->get()
                ->map(function ($item) {
                    return [
                        'property_id' => $item->property_id,
                        'property_name' => $item->property->title,
                        'city' => $item->property->city,
                        'revenue' => $item->revenue,
                        'bookings' => $item->bookings
                    ];
                })
                ->sortByDesc('revenue')
                ->values()
                ->toArray();
        }
        
        // Calculate average booking value
        $avgBookingValue = $query->count() > 0 ? $totalRevenue / $query->count() : 0;
        
        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'metrics' => [
                'total_revenue' => $totalRevenue,
                'net_revenue' => $netRevenue,
                'service_fees' => $serviceFeesTotal,
                'cleaning_fees' => $cleaningFeesTotal,
                'avg_booking_value' => $avgBookingValue,
                'bookings_count' => $query->count()
            ],
            'chart' => [
                'labels' => $labels,
                'series' => [
                    [
                        'name' => 'Total Revenue',
                        'data' => $revenueSeries
                    ],
                    [
                        'name' => 'Net Revenue',
                        'data' => $netRevenueSeries
                    ],
                    [
                        'name' => 'Service Fees',
                        'data' => $serviceFeeSeries
                    ]
                ]
            ],
            'property_revenue' => $propertyRevenue
        ];
    }
    
    /**
     * Get occupancy analytics
     *
     * @param int $userId
     * @param string $period
     * @param int|null $propertyId
     * @return array
     */
    public function getOccupancyAnalytics(int $userId, string $period = '30days', ?int $propertyId = null): array
    {
        // Determine date range
        $datePeriod = $this->getDatePeriodRange($period);
        $startDate = $datePeriod['start'];
        $endDate = $datePeriod['end'];
        
        // Get all properties for this host
        $propertiesQuery = Property::where('user_id', $userId);
        
        if ($propertyId) {
            $propertiesQuery->where('id', $propertyId);
        }
        
        $properties = $propertiesQuery->get();
        $propertyIds = $properties->pluck('id')->toArray();
        
        // Calculate occupancy for each property
        $propertyOccupancy = [];
        $totalDaysAvailable = 0;
        $totalDaysBooked = 0;
        
        foreach ($properties as $property) {
            $propertyStartDate = max($startDate, $property->created_at);
            $daysInPeriod = $propertyStartDate->diffInDays($endDate) + 1;
            $totalDaysAvailable += $daysInPeriod;
            
            // Count booked days
            $bookedDays = Booking::where('property_id', $property->id)
                ->whereIn('status', ['confirmed', 'completed'])
                ->where(function($query) use ($propertyStartDate, $endDate) {
                    $query->whereBetween('check_in', [$propertyStartDate, $endDate])
                          ->orWhereBetween('check_out', [$propertyStartDate, $endDate])
                          ->orWhere(function($q) use ($propertyStartDate, $endDate) {
                              $q->where('check_in', '<', $propertyStartDate)
                                ->where('check_out', '>', $endDate);
                          });
                })
                ->get()
                ->reduce(function ($carry, $booking) use ($propertyStartDate, $endDate) {
                    $checkIn = max(Carbon::parse($booking->check_in), $propertyStartDate);
                    $checkOut = min(Carbon::parse($booking->check_out), $endDate);
                    return $carry + $checkIn->diffInDays($checkOut);
                }, 0);
                
            $totalDaysBooked += $bookedDays;
            
            $occupancyRate = $daysInPeriod > 0 ? ($bookedDays / $daysInPeriod) * 100 : 0;
            
            $propertyOccupancy[] = [
                'property_id' => $property->id,
                'property_name' => $property->title,
                'days_available' => $daysInPeriod,
                'days_booked' => $bookedDays,
                'occupancy_rate' => round($occupancyRate, 2)
            ];
        }
        
        // Calculate overall occupancy rate
        $overallOccupancyRate = $totalDaysAvailable > 0 ? ($totalDaysBooked / $totalDaysAvailable) * 100 : 0;
        
        // Get monthly occupancy trend
        $occupancyTrend = [];
        $currentMonth = Carbon::parse($startDate)->startOfMonth();
        $endMonth = Carbon::parse($endDate)->startOfMonth();
        
        while ($currentMonth->lte($endMonth)) {
            $monthStart = $currentMonth->copy()->startOfMonth();
            $monthEnd = $currentMonth->copy()->endOfMonth();
            
            // Calculate occupancy for this month
            $daysInMonth = $monthStart->daysInMonth;
            $totalMonthlyAvailable = $daysInMonth * count($propertyIds);
            
            $monthlyBooked = Booking::whereIn('property_id', $propertyIds)
                ->whereIn('status', ['confirmed', 'completed'])
                ->where(function($query) use ($monthStart, $monthEnd) {
                    $query->whereBetween('check_in', [$monthStart, $monthEnd])
                          ->orWhereBetween('check_out', [$monthStart, $monthEnd])
                          ->orWhere(function($q) use ($monthStart, $monthEnd) {
                              $q->where('check_in', '<', $monthStart)
                                ->where('check_out', '>', $monthEnd);
                          });
                })
                ->get()
                ->reduce(function ($carry, $booking) use ($monthStart, $monthEnd) {
                    $checkIn = max(Carbon::parse($booking->check_in), $monthStart);
                    $checkOut = min(Carbon::parse($booking->check_out), $monthEnd);
                    return $carry + $checkIn->diffInDays($checkOut);
                }, 0);
                
            $monthlyRate = $totalMonthlyAvailable > 0 ? ($monthlyBooked / $totalMonthlyAvailable) * 100 : 0;
            
            $occupancyTrend[] = [
                'month' => $currentMonth->format('M Y'),
                'occupancy_rate' => round($monthlyRate, 2)
            ];
            
            $currentMonth->addMonth();
        }
        
        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'overall_occupancy_rate' => round($overallOccupancyRate, 2),
            'total_days_available' => $totalDaysAvailable,
            'total_days_booked' => $totalDaysBooked,
            'property_occupancy' => $propertyOccupancy,
            'monthly_trend' => $occupancyTrend
        ];
    }
    
    /**
     * Generate a date range based on period string
     *
     * @param string $period
     * @return array
     */
    private function getDatePeriodRange(string $period): array
    {
        $now = now();
        
        switch ($period) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            case 'yesterday':
                return [
                    'start' => $now->copy()->subDay()->startOfDay(),
                    'end' => $now->copy()->subDay()->endOfDay()
                ];
            case '7days':
                return [
                    'start' => $now->copy()->subDays(6)->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            case '30days':
                return [
                    'start' => $now->copy()->subDays(29)->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            case 'this_month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            case 'last_month':
                return [
                    'start' => $now->copy()->subMonth()->startOfMonth(),
                    'end' => $now->copy()->subMonth()->endOfMonth()
                ];
            case 'this_year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear()
                ];
            case 'last_year':
                return [
                    'start' => $now->copy()->subYear()->startOfYear(),
                    'end' => $now->copy()->subYear()->endOfYear()
                ];
            case 'all_time':
                return [
                    'start' => Carbon::parse('2020-01-01'),
                    'end' => $now->copy()
                ];
            default:
                return [
                    'start' => $now->copy()->subDays(29)->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
        }
    }
    
    /**
     * Calculate occupancy rate for properties
     *
     * @param array $propertyIds
     * @param int $days
     * @return float
     */
    private function calculateOccupancyRate(array $propertyIds, int $days = 30): float
    {
        if (empty($propertyIds)) {
            return 0;
        }
        
        $startDate = now()->subDays($days);
        $endDate = now();
        
        // Total available days (properties * days)
        $totalAvailableDays = count($propertyIds) * $days;
        
        // Count booked days
        $bookedDays = Booking::whereIn('property_id', $propertyIds)
            ->whereIn('status', ['confirmed', 'completed'])
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('check_in', [$startDate, $endDate])
                      ->orWhereBetween('check_out', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('check_in', '<', $startDate)
                            ->where('check_out', '>', $endDate);
                      });
            })
            ->get()
            ->reduce(function ($carry, $booking) use ($startDate, $endDate) {
                $checkIn = max(Carbon::parse($booking->check_in), $startDate);
                $checkOut = min(Carbon::parse($booking->check_out), $endDate);
                return $carry + $checkIn->diffInDays($checkOut);
            }, 0);
            
        // Calculate occupancy rate
        return $totalAvailableDays > 0 ? round(($bookedDays / $totalAvailableDays) * 100, 2) : 0;
    }
    
    /**
     * Calculate review sentiment
     *
     * @param array $propertyIds
     * @param int $recentCount
     * @return array
     */
    private function calculateReviewSentiment(array $propertyIds, int $recentCount = 10): array
    {
        $reviews = Review::whereIn('property_id', $propertyIds)
            ->orderByDesc('created_at')
            ->limit($recentCount)
            ->get();
            
        $total = $reviews->count();
        
        if ($total === 0) {
            return [
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0
            ];
        }
        
        $positive = $reviews->where('rating', '>=', 4)->count();
        $negative = $reviews->where('rating', '<=', 2)->count();
        $neutral = $total - $positive - $negative;
        
        return [
            'positive' => round(($positive / $total) * 100),
            'neutral' => round(($neutral / $total) * 100),
            'negative' => round(($negative / $total) * 100)
        ];
    }
    
    /**
     * Get calendar view for all properties
     *
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @param int|null $propertyId
     * @return array
     */
    public function getCalendarView(int $userId, string $startDate, string $endDate, ?int $propertyId = null): array
    {
        // Parse dates
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Get properties
        $propertiesQuery = Property::where('user_id', $userId);
        
        if ($propertyId) {
            $propertiesQuery->where('id', $propertyId);
        }
        
        $properties = $propertiesQuery->get(['id', 'title', 'city']);
        $propertyIds = $properties->pluck('id')->toArray();
        
        // Get bookings in date range
        $bookings = Booking::whereIn('property_id', $propertyIds)
            ->where(function($query) use ($start, $end) {
                $query->whereBetween('check_in', [$start, $end])
                      ->orWhereBetween('check_out', [$start, $end])
                      ->orWhere(function($q) use ($start, $end) {
                          $q->where('check_in', '<', $start)
                            ->where('check_out', '>', $end);
                      });
            })
            ->with(['user:id,name,email'])
            ->get();
            
        // Get blocked dates from calendar
        $blockedDates = PropertyCalendar::whereIn('property_id', $propertyIds)
            ->where('is_available', false)
            ->where(function($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                      ->orWhereBetween('end_date', [$start, $end])
                      ->orWhere(function($q) use ($start, $end) {
                          $q->where('start_date', '<', $start)
                            ->where('end_date', '>', $end);
                      });
            })
            ->get();
            
        // Format events for calendar view
        $events = [];
        
        // Add bookings as events
        foreach ($bookings as $booking) {
            $events[] = [
                'id' => 'booking_' . $booking->id,
                'title' => "Booking #{$booking->id} - {$booking->user->name}",
                'start' => $booking->check_in,
                'end' => $booking->check_out,
                'property_id' => $booking->property_id,
                'property_name' => $booking->property->title,
                'type' => 'booking',
                'status' => $booking->status,
                'guest' => [
                    'name' => $booking->user->name,
                    'email' => $booking->user->email
                ],
                'amount' => $booking->total_price,
                'guests' => $booking->guests
            ];
        }
        
        // Add blocked dates as events
        foreach ($blockedDates as $block) {
            $events[] = [
                'id' => 'block_' . $block->id,
                'title' => $block->title ?? 'Blocked',
                'start' => $block->start_date,
                'end' => $block->end_date,
                'property_id' => $block->property_id,
                'property_name' => $block->property->title,
                'type' => 'blocked',
                'notes' => $block->notes,
                'source' => $block->source ?? 'manual'
            ];
        }
        
        return [
            'properties' => $properties,
            'events' => $events,
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ]
        ];
    }
    
    /**
     * Get property performance comparison
     *
     * @param int $userId
     * @return array
     */
    public function getPropertyPerformanceComparison(int $userId): array
    {
        $properties = Property::where('user_id', $userId)
            ->withCount(['bookings' => function ($query) {
                $query->whereIn('status', ['confirmed', 'completed']);
            }])
            ->withSum(['bookings' => function ($query) {
                $query->whereIn('status', ['confirmed', 'completed']);
            }], 'total_price')
            ->withAvg('reviews', 'rating')
            ->get(['id', 'title', 'city', 'price_per_night', 'created_at']);
            
        $performanceData = [];
        
        foreach ($properties as $property) {
            // Calculate average daily rate
            $adr = 0;
            if ($property->bookings_count > 0) {
                $totalNights = Booking::where('property_id', $property->id)
                    ->whereIn('status', ['confirmed', 'completed'])
                    ->get()
                    ->reduce(function ($carry, $booking) {
                        return $carry + Carbon::parse($booking->check_in)->diffInDays(Carbon::parse($booking->check_out));
                    }, 0);
                
                $adr = $totalNights > 0 ? $property->bookings_sum_total_price / $totalNights : $property->price_per_night;
            }
            
            // Calculate days on platform
            $daysOnPlatform = $property->created_at->diffInDays(now()) + 1;
            
            // Calculate bookings per month
            $bookingsPerMonth = $daysOnPlatform > 30 ? ($property->bookings_count / ($daysOnPlatform / 30)) : $property->bookings_count;
            
            // Calculate revenue per day
            $revenuePerDay = $daysOnPlatform > 0 ? $property->bookings_sum_total_price / $daysOnPlatform : 0;
            
            $performanceData[] = [
                'property_id' => $property->id,
                'property_name' => $property->title,
                'city' => $property->city,
                'bookings_count' => $property->bookings_count,
                'total_revenue' => $property->bookings_sum_total_price,
                'average_rating' => round($property->reviews_avg_rating ?? 0, 1),
                'price_per_night' => $property->price_per_night,
                'average_daily_rate' => round($adr, 2),
                'bookings_per_month' => round($bookingsPerMonth, 1),
                'revenue_per_day' => round($revenuePerDay, 2),
                'days_on_platform' => $daysOnPlatform
            ];
        }
        
        // Sort data for better comparison
        usort($performanceData, function($a, $b) {
            return $b['total_revenue'] - $a['total_revenue'];
        });
        
        // Calculate average performance metrics
        $avgTotalRevenue = array_sum(array_column($performanceData, 'total_revenue')) / max(1, count($performanceData));
        $avgBookingsCount = array_sum(array_column($performanceData, 'bookings_count')) / max(1, count($performanceData));
        $avgRating = array_sum(array_column($performanceData, 'average_rating')) / max(1, count($performanceData));
        $avgBookingsPerMonth = array_sum(array_column($performanceData, 'bookings_per_month')) / max(1, count($performanceData));
        
        return [
            'properties_data' => $performanceData,
            'averages' => [
                'avg_revenue' => round($avgTotalRevenue, 2),
                'avg_bookings' => round($avgBookingsCount, 1),
                'avg_rating' => round($avgRating, 1),
                'avg_bookings_per_month' => round($avgBookingsPerMonth, 1)
            ]
        ];
    }
    
    /**
     * Get AI-powered recommendations for a property
     *
     * @param int $userId
     * @param int|null $propertyId
     * @return array
     */
    public function getAIRecommendations(int $userId, ?int $propertyId = null): array
    {
        // Determine which property to analyze
        $propertyQuery = Property::where('user_id', $userId);
        
        if ($propertyId) {
            $propertyQuery->where('id', $propertyId);
        } else {
            // If no specific property, analyze the one with the most potential for improvement
            $propertyQuery->withCount('bookings')
                ->orderBy('bookings_count', 'asc')
                ->limit(1);
        }
        
        $property = $propertyQuery->first();
        
        if (!$property) {
            return [
                'success' => false,
                'message' => 'No property found for analysis'
            ];
        }
        
        // Sample recommendations (in a real app, this would use AI analysis)
        $recommendations = [
            [
                'type' => 'pricing',
                'recommendation' => 'Consider increasing your base price by 10-15% on weekends based on market analysis',
                'reason' => 'Properties in your area have 30% higher rates on weekends with similar occupancy rates',
                'potential_impact' => 'High',
                'action_url' => '/host/properties/' . $property->id . '/pricing'
            ],
            [
                'type' => 'amenities',
                'recommendation' => 'Adding Wi-Fi and a workspace could attract more business travelers',
                'reason' => 'Properties with these amenities see 25% more weekday bookings in your area',
                'potential_impact' => 'Medium',
                'action_url' => '/host/properties/' . $property->id . '/amenities'
            ],
            [
                'type' => 'photos',
                'recommendation' => 'Add more photos highlighting the kitchen and outdoor areas',
                'reason' => 'Listings with 20+ photos and clear kitchen shots receive more bookings',
                'potential_impact' => 'Medium',
                'action_url' => '/host/properties/' . $property->id . '/photos'
            ],
            [
                'type' => 'description',
                'recommendation' => 'Update your description to highlight proximity to popular landmarks',
                'reason' => 'Guests frequently search for properties near major attractions in your area',
                'potential_impact' => 'Low',
                'action_url' => '/host/properties/' . $property->id . '/description'
            ],
            [
                'type' => 'availability',
                'recommendation' => 'Consider offering longer minimum stays (3+ nights) for better efficiency',
                'reason' => 'Your property has higher turnover costs than average',
                'potential_impact' => 'Medium',
                'action_url' => '/host/properties/' . $property->id . '/policies'
            ]
        ];
        
        return [
            'success' => true,
            'property' => [
                'id' => $property->id,
                'title' => $property->title,
                'city' => $property->city
            ],
            'recommendations' => $recommendations
        ];
    }
    public function getGuestAnalytics(int $userId): array
    {
        // Placeholder implementation
        return ['new_vs_repeat_guests' => ['new' => 0, 'repeat' => 0]];
    }

    public function getReviewAnalytics(int $userId): array
    {
        // Placeholder implementation
        return ['average_rating' => 0, 'total_reviews' => 0];
    }

    public function getTransactionHistory(int $userId, ?string $startDate, ?string $endDate, ?int $propertyId, ?string $type, int $perPage): array
    {
        // Placeholder implementation
        return ['transactions' => [], 'total' => 0, 'per_page' => $perPage];
    }

    public function getFinancialSummary(int $userId, string $period): array
    {
        // Placeholder implementation
        return ['total_revenue' => 0, 'total_expenses' => 0, 'net_profit' => 0];
    }

    public function generateFinancialReport(int $userId, string $startDate, string $endDate, string $format): array
    {
        // Placeholder implementation
        return ['success' => false, 'message' => 'Not implemented'];
    }
}

<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Referral;
use App\Models\SaraConversation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminAnalyticsService
{
    /**
     * Get daily stats for a specific day
     *
     * @param Carbon $date
     * @return array
     */
    public function getDailyStats(Carbon $date): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        return [
            'bookings_count' => Booking::whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
            'revenue' => Booking::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->whereIn('status', ['confirmed', 'completed'])
                ->sum('total_price'),
            'new_users' => User::whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
            'new_properties' => Property::whereBetween('created_at', [$startOfDay, $endOfDay])->count(),
        ];
    }
    
    /**
     * Get revenue chart data
     *
     * @param int $days
     * @return array
     */
    public function getRevenueChart(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();
        
        // Cache the results for an hour to improve performance
        return Cache::remember("admin_revenue_chart_{$days}", 3600, function () use ($startDate, $endDate, $days) {
            $data = Booking::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(case when status = "completed" then total_price else 0 end) as completed_revenue'),
                DB::raw('SUM(case when status = "confirmed" then total_price else 0 end) as confirmed_revenue')
            )
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            $labels = [];
            $completedSeries = [];
            $confirmedSeries = [];
            
            for ($i = 0; $i <= $days; $i++) {
                $date = $startDate->copy()->addDays($i)->format('Y-m-d');
                $labels[] = $startDate->copy()->addDays($i)->format('M d');
                
                $dayData = $data->firstWhere('date', $date);
                $completedSeries[] = $dayData ? (float)$dayData->completed_revenue : 0;
                $confirmedSeries[] = $dayData ? (float)$dayData->confirmed_revenue : 0;
            }
            
            return [
                'labels' => $labels,
                'series' => [
                    [
                        'name' => 'Completed',
                        'data' => $completedSeries
                    ],
                    [
                        'name' => 'Confirmed',
                        'data' => $confirmedSeries
                    ]
                ]
            ];
        });
    }
    
    /**
     * Get booking chart data
     *
     * @param int $days
     * @return array
     */
    public function getBookingChart(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();
        
        // Cache the results for an hour
        return Cache::remember("admin_booking_chart_{$days}", 3600, function () use ($startDate, $endDate, $days) {
            $data = Booking::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as bookings_count'),
                DB::raw('SUM(case when status = "confirmed" or status = "completed" then 1 else 0 end) as confirmed_count'),
                DB::raw('SUM(case when status = "cancelled" then 1 else 0 end) as cancelled_count')
            )
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            $labels = [];
            $totalSeries = [];
            $confirmedSeries = [];
            $cancelledSeries = [];
            
            for ($i = 0; $i <= $days; $i++) {
                $date = $startDate->copy()->addDays($i)->format('Y-m-d');
                $labels[] = $startDate->copy()->addDays($i)->format('M d');
                
                $dayData = $data->firstWhere('date', $date);
                $totalSeries[] = $dayData ? (int)$dayData->bookings_count : 0;
                $confirmedSeries[] = $dayData ? (int)$dayData->confirmed_count : 0;
                $cancelledSeries[] = $dayData ? (int)$dayData->cancelled_count : 0;
            }
            
            return [
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
            ];
        });
    }
    
    /**
     * Get user growth chart data
     *
     * @param int $days
     * @return array
     */
    public function getUserGrowthChart(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();
        
        // Cache the results for an hour
        return Cache::remember("admin_user_growth_chart_{$days}", 3600, function () use ($startDate, $endDate, $days) {
            $dailyUsers = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as users_count'),
                DB::raw('SUM(case when user_type = "guest" then 1 else 0 end) as guest_count'),
                DB::raw('SUM(case when user_type = "host" then 1 else 0 end) as host_count')
            )
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();
                
            $cumulativeUsers = User::select(
                DB::raw('COUNT(*) as total')
            )
                ->where('created_at', '<', $startDate)
                ->first();
                
            $initialCount = $cumulativeUsers ? $cumulativeUsers->total : 0;
            
            $labels = [];
            $totalSeries = [];
            $guestSeries = [];
            $hostSeries = [];
            $cumulativeSeries = [];
            
            $runningTotal = $initialCount;
            
            for ($i = 0; $i <= $days; $i++) {
                $date = $startDate->copy()->addDays($i)->format('Y-m-d');
                $labels[] = $startDate->copy()->addDays($i)->format('M d');
                
                $dayData = $dailyUsers->firstWhere('date', $date);
                $newUsers = $dayData ? (int)$dayData->users_count : 0;
                $guestUsers = $dayData ? (int)$dayData->guest_count : 0;
                $hostUsers = $dayData ? (int)$dayData->host_count : 0;
                
                $totalSeries[] = $newUsers;
                $guestSeries[] = $guestUsers;
                $hostSeries[] = $hostUsers;
                
                $runningTotal += $newUsers;
                $cumulativeSeries[] = $runningTotal;
            }
            
            return [
                'labels' => $labels,
                'daily_series' => [
                    [
                        'name' => 'Total New Users',
                        'data' => $totalSeries
                    ],
                    [
                        'name' => 'New Guests',
                        'data' => $guestSeries
                    ],
                    [
                        'name' => 'New Hosts',
                        'data' => $hostSeries
                    ]
                ],
                'cumulative_series' => [
                    [
                        'name' => 'Total Users',
                        'data' => $cumulativeSeries
                    ]
                ]
            ];
        });
    }
    
    /**
     * Get Sara AI analytics
     *
     * @return array
     */
    public function getSaraAnalytics(): array
    {
        // Cache for an hour
        return Cache::remember('admin_sara_analytics', 3600, function () {
            $totalConversations = SaraConversation::count();
            $activeToday = SaraConversation::where('updated_at', '>=', now()->startOfDay())->count();
            
            $monthlyConversations = SaraConversation::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
                
            // Get conversation metrics
            $avgLength = DB::table('conversation_metrics')
                ->avg('messages_count');
                
            $avgResponseTime = DB::table('conversation_metrics')
                ->avg('avg_response_time');
                
            $sentimentAnalysis = DB::table('conversation_metrics')
                ->select(
                    DB::raw('COUNT(case when sentiment = "positive" then 1 else null end) as positive'),
                    DB::raw('COUNT(case when sentiment = "neutral" then 1 else null end) as neutral'),
                    DB::raw('COUNT(case when sentiment = "negative" then 1 else null end) as negative')
                )
                ->first();
                
            $topIntents = DB::table('conversation_metrics')
                ->select('primary_intent', DB::raw('COUNT(*) as count'))
                ->whereNotNull('primary_intent')
                ->groupBy('primary_intent')
                ->orderByDesc('count')
                ->limit(5)
                ->get();
                
            return [
                'total_conversations' => $totalConversations,
                'active_today' => $activeToday,
                'conversations_trend' => $monthlyConversations,
                'avg_conversation_length' => round($avgLength, 1) ?? 0,
                'avg_response_time' => round($avgResponseTime, 2) ?? 0,
                'sentiment_analysis' => $sentimentAnalysis ? [
                    'positive' => (int) $sentimentAnalysis->positive,
                    'neutral' => (int) $sentimentAnalysis->neutral,
                    'negative' => (int) $sentimentAnalysis->negative
                ] : [
                    'positive' => 0,
                    'neutral' => 0,
                    'negative' => 0
                ],
                'top_intents' => $topIntents
            ];
        });
    }
    
    /**
     * Get referral program analytics
     *
     * @return array
     */
    public function getReferralAnalytics(): array
    {
        // Cache for an hour
        return Cache::remember('admin_referral_analytics', 3600, function () {
            $totalReferrals = Referral::count();
            $pendingReferrals = Referral::where('status', Referral::STATUS_PENDING)->count();
            $completedReferrals = Referral::where('status', Referral::STATUS_COMPLETED)->count();
            $creditedReferrals = Referral::where('status', Referral::STATUS_CREDITED)->count();
            
            $referrerCredits = Referral::where('status', Referral::STATUS_CREDITED)
                ->sum('referrer_credit');
                
            $referredCredits = Referral::where('status', Referral::STATUS_CREDITED)
                ->sum('referred_credit');
                
            $signupConversions = Referral::whereNotNull('signup_completed_at')->count();
            $bookingConversions = Referral::whereNotNull('first_booking_completed_at')->count();
            
            $conversionRate = $totalReferrals > 0 
                ? round(($signupConversions / $totalReferrals) * 100, 2) 
                : 0;
                
            $bookingRate = $signupConversions > 0 
                ? round(($bookingConversions / $signupConversions) * 100, 2) 
                : 0;
                
            $monthlyReferrals = Referral::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
                
            $topReferrers = User::join('referrals', 'users.id', '=', 'referrals.referrer_id')
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    DB::raw('COUNT(*) as referrals_count')
                )
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderByDesc('referrals_count')
                ->limit(10)
                ->get();
                
            return [
                'total_referrals' => $totalReferrals,
                'pending_referrals' => $pendingReferrals,
                'completed_referrals' => $completedReferrals,
                'credited_referrals' => $creditedReferrals,
                'referrer_credits' => $referrerCredits,
                'referred_credits' => $referredCredits,
                'signup_conversions' => $signupConversions,
                'booking_conversions' => $bookingConversions,
                'conversion_rate' => $conversionRate,
                'booking_rate' => $bookingRate,
                'monthly_trend' => $monthlyReferrals,
                'top_referrers' => $topReferrers
            ];
        });
    }
    
    /**
     * Get booking map data
     *
     * @return array
     */
    public function getBookingMapData(): array
    {
        // Cache for an hour
        return Cache::remember('admin_booking_map_data', 3600, function () {
            $propertyLocationData = Property::select(
                'id', 
                'title', 
                'city', 
                'country', 
                'latitude', 
                'longitude',
                DB::raw('(SELECT COUNT(*) FROM bookings WHERE bookings.property_id = properties.id) as booking_count')
            )
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->having('booking_count', '>', 0)
                ->get();
                
            $countryStats = Property::select(
                'country',
                DB::raw('COUNT(*) as property_count'),
                DB::raw('(SELECT COUNT(*) FROM bookings JOIN properties as p ON p.id = bookings.property_id WHERE p.country = properties.country) as booking_count')
            )
                ->groupBy('country')
                ->orderByDesc('booking_count')
                ->get();
                
            $cityStats = Property::select(
                'city',
                'country',
                DB::raw('COUNT(*) as property_count'),
                DB::raw('(SELECT COUNT(*) FROM bookings JOIN properties as p ON p.id = bookings.property_id WHERE p.city = properties.city) as booking_count')
            )
                ->groupBy('city', 'country')
                ->orderByDesc('booking_count')
                ->limit(10)
                ->get();
                
            return [
                'property_locations' => $propertyLocationData,
                'country_stats' => $countryStats,
                'city_stats' => $cityStats
            ];
        });
    }
    
    /**
     * Run application health checks
     *
     * @return array
     */
    public function runHealthChecks(): array
    {
        $checks = [];
        
        // Check database connection
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'ok',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $checks['database'] = [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
            Log::error('Health check: Database connection failed', ['error' => $e->getMessage()]);
        }
        
        // Check email configuration
        if (!empty(config('mail.mailers.smtp.username'))) {
            $checks['email'] = [
                'status' => 'ok',
                'message' => 'Email configuration detected'
            ];
        } else {
            $checks['email'] = [
                'status' => 'warning',
                'message' => 'Email not configured'
            ];
        }
        
        // Check storage access
        try {
            $disk = config('filesystems.default');
            $path = 'health-check-' . time() . '.txt';
            \Storage::disk($disk)->put($path, 'Health check');
            \Storage::disk($disk)->delete($path);
            
            $checks['storage'] = [
                'status' => 'ok',
                'message' => 'Storage system operational (' . $disk . ')'
            ];
        } catch (\Exception $e) {
            $checks['storage'] = [
                'status' => 'error',
                'message' => 'Storage system error: ' . $e->getMessage()
            ];
            Log::error('Health check: Storage system error', ['error' => $e->getMessage()]);
        }
        
        // Check queue system
        try {
            $queueConnection = config('queue.default');
            $checks['queue'] = [
                'status' => 'ok',
                'message' => 'Queue system configured (' . $queueConnection . ')'
            ];
        } catch (\Exception $e) {
            $checks['queue'] = [
                'status' => 'warning',
                'message' => 'Queue system check failed: ' . $e->getMessage()
            ];
        }
        
        // Check recent bookings
        $recentBooking = Booking::orderByDesc('created_at')->first();
        if ($recentBooking && $recentBooking->created_at->gt(now()->subDays(7))) {
            $checks['recent_activity'] = [
                'status' => 'ok',
                'message' => 'Recent booking activity detected'
            ];
        } else {
            $checks['recent_activity'] = [
                'status' => 'warning',
                'message' => 'No recent booking activity'
            ];
        }
        
        return [
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
            'overall_status' => in_array('error', array_column($checks, 'status')) ? 'error' : 
                (in_array('warning', array_column($checks, 'status')) ? 'warning' : 'ok')
        ];
    }
}

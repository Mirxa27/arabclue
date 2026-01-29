<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmailAnalyticsService
{
    /**
     * Track email sent event
     */
    public function trackEmailSent(string $emailType, User $user, array $metadata = []): void
    {
        try {
            DB::table('email_analytics')->insert([
                'user_id' => $user->id,
                'email_type' => $emailType,
                'event_type' => 'sent',
                'metadata' => json_encode($metadata),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update daily stats cache
            $this->updateDailyStats($emailType, 'sent');
            
        } catch (\Exception $e) {
            Log::error('Failed to track email sent', [
                'email_type' => $emailType,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Track email opened event
     */
    public function trackEmailOpened(string $trackingId): void
    {
        try {
            $analytics = DB::table('email_analytics')
                ->where('tracking_id', $trackingId)
                ->first();

            if ($analytics) {
                DB::table('email_analytics')
                    ->where('id', $analytics->id)
                    ->update([
                        'opened_at' => now(),
                        'updated_at' => now(),
                    ]);

                $this->updateDailyStats($analytics->email_type, 'opened');
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to track email opened', [
                'tracking_id' => $trackingId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Track email clicked event
     */
    public function trackEmailClicked(string $trackingId, string $linkUrl): void
    {
        try {
            $analytics = DB::table('email_analytics')
                ->where('tracking_id', $trackingId)
                ->first();

            if ($analytics) {
                DB::table('email_analytics')
                    ->where('id', $analytics->id)
                    ->update([
                        'clicked_at' => now(),
                        'clicked_url' => $linkUrl,
                        'updated_at' => now(),
                    ]);

                $this->updateDailyStats($analytics->email_type, 'clicked');
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to track email clicked', [
                'tracking_id' => $trackingId,
                'link_url' => $linkUrl,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get email performance metrics
     */
    public function getEmailMetrics(string $emailType = null, int $days = 30): array
    {
        $query = DB::table('email_analytics')
            ->where('created_at', '>=', now()->subDays($days));

        if ($emailType) {
            $query->where('email_type', $emailType);
        }

        $analytics = $query->get();

        $totalSent = $analytics->count();
        $totalOpened = $analytics->whereNotNull('opened_at')->count();
        $totalClicked = $analytics->whereNotNull('clicked_at')->count();

        return [
            'total_sent' => $totalSent,
            'total_opened' => $totalOpened,
            'total_clicked' => $totalClicked,
            'open_rate' => $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 2) : 0,
            'click_rate' => $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 2) : 0,
            'click_through_rate' => $totalOpened > 0 ? round(($totalClicked / $totalOpened) * 100, 2) : 0,
        ];
    }

    /**
     * Get email performance by type
     */
    public function getMetricsByType(int $days = 30): array
    {
        $emailTypes = DB::table('email_analytics')
            ->where('created_at', '>=', now()->subDays($days))
            ->distinct()
            ->pluck('email_type');

        $metrics = [];
        foreach ($emailTypes as $type) {
            $metrics[$type] = $this->getEmailMetrics($type, $days);
        }

        return $metrics;
    }

    /**
     * Get daily email statistics
     */
    public function getDailyStats(int $days = 30): array
    {
        $stats = DB::table('email_analytics')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as sent'),
                DB::raw('COUNT(opened_at) as opened'),
                DB::raw('COUNT(clicked_at) as clicked')
            )
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return $stats->map(function ($stat) {
            return [
                'date' => $stat->date,
                'sent' => $stat->sent,
                'opened' => $stat->opened,
                'clicked' => $stat->clicked,
                'open_rate' => $stat->sent > 0 ? round(($stat->opened / $stat->sent) * 100, 2) : 0,
                'click_rate' => $stat->sent > 0 ? round(($stat->clicked / $stat->sent) * 100, 2) : 0,
            ];
        })->toArray();
    }

    /**
     * Get user engagement metrics
     */
    public function getUserEngagementMetrics(User $user, int $days = 30): array
    {
        $analytics = DB::table('email_analytics')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $totalSent = $analytics->count();
        $totalOpened = $analytics->whereNotNull('opened_at')->count();
        $totalClicked = $analytics->whereNotNull('clicked_at')->count();

        return [
            'total_emails_received' => $totalSent,
            'total_emails_opened' => $totalOpened,
            'total_links_clicked' => $totalClicked,
            'engagement_score' => $this->calculateEngagementScore($analytics),
            'preferred_email_types' => $this->getPreferredEmailTypes($analytics),
            'last_engagement' => $analytics->whereNotNull('opened_at')->max('opened_at'),
        ];
    }

    /**
     * Calculate user engagement score
     */
    private function calculateEngagementScore($analytics): int
    {
        if ($analytics->isEmpty()) {
            return 0;
        }

        $totalSent = $analytics->count();
        $totalOpened = $analytics->whereNotNull('opened_at')->count();
        $totalClicked = $analytics->whereNotNull('clicked_at')->count();

        // Weighted scoring: opens = 1 point, clicks = 3 points
        $score = ($totalOpened * 1) + ($totalClicked * 3);
        $maxPossibleScore = $totalSent * 4; // Max if all emails were clicked

        return $maxPossibleScore > 0 ? round(($score / $maxPossibleScore) * 100) : 0;
    }

    /**
     * Get user's preferred email types based on engagement
     */
    private function getPreferredEmailTypes($analytics): array
    {
        return $analytics
            ->whereNotNull('opened_at')
            ->groupBy('email_type')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(3)
            ->keys()
            ->toArray();
    }

    /**
     * Update daily statistics cache
     */
    private function updateDailyStats(string $emailType, string $eventType): void
    {
        $cacheKey = "email_stats:" . now()->format('Y-m-d') . ":{$emailType}:{$eventType}";
        Cache::increment($cacheKey, 1);
        Cache::expire($cacheKey, 86400 * 7); // Keep for 7 days
    }

    /**
     * Get top performing email campaigns
     */
    public function getTopPerformingCampaigns(int $limit = 10): array
    {
        return DB::table('email_analytics')
            ->select(
                'email_type',
                DB::raw('COUNT(*) as total_sent'),
                DB::raw('COUNT(opened_at) as total_opened'),
                DB::raw('COUNT(clicked_at) as total_clicked'),
                DB::raw('ROUND((COUNT(opened_at) / COUNT(*)) * 100, 2) as open_rate'),
                DB::raw('ROUND((COUNT(clicked_at) / COUNT(*)) * 100, 2) as click_rate')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('email_type')
            ->having('total_sent', '>=', 10) // Minimum 10 emails sent
            ->orderBy('open_rate', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get overall email statistics
     */
    public function getEmailStats(): array
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            'today' => [
                'sent' => $this->getEmailCount($today),
                'opened' => $this->getEmailCount($today, 'opened'),
                'clicked' => $this->getEmailCount($today, 'clicked'),
            ],
            'yesterday' => [
                'sent' => $this->getEmailCount($yesterday, null, $today),
                'opened' => $this->getEmailCount($yesterday, 'opened', $today),
                'clicked' => $this->getEmailCount($yesterday, 'clicked', $today),
            ],
            'this_week' => [
                'sent' => $this->getEmailCount($thisWeek),
                'opened' => $this->getEmailCount($thisWeek, 'opened'),
                'clicked' => $this->getEmailCount($thisWeek, 'clicked'),
            ],
            'this_month' => [
                'sent' => $this->getEmailCount($thisMonth),
                'opened' => $this->getEmailCount($thisMonth, 'opened'),
                'clicked' => $this->getEmailCount($thisMonth, 'clicked'),
            ],
            'total' => [
                'sent' => $this->getEmailCount(),
                'opened' => $this->getEmailCount(null, 'opened'),
                'clicked' => $this->getEmailCount(null, 'clicked'),
            ],
        ];
    }

    /**
     * Get email count for specific period and type
     */
    private function getEmailCount($startDate = null, $type = null, $endDate = null): int
    {
        $query = DB::table('email_analytics');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<', $endDate);
        }

        switch ($type) {
            case 'opened':
                $query->whereNotNull('opened_at');
                break;
            case 'clicked':
                $query->whereNotNull('clicked_at');
                break;
        }

        return $query->count();
    }

    /**
     * Get analytics data for admin dashboard
     */
    public function getAnalytics(int $days = 30): array
    {
        return [
            'overview' => $this->getEmailMetrics(null, $days),
            'by_type' => $this->getMetricsByType($days),
            'daily_stats' => $this->getDailyStats($days),
            'top_campaigns' => $this->getTopPerformingCampaigns(),
            'recent_activity' => $this->getRecentActivity(),
        ];
    }

    /**
     * Get recent email activity
     */
    private function getRecentActivity(int $limit = 20): array
    {
        return DB::table('email_analytics')
            ->select('email_type', 'created_at', 'opened_at', 'clicked_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Generate email performance report
     */
    public function generatePerformanceReport(int $days = 30): array
    {
        return [
            'overview' => $this->getEmailMetrics(null, $days),
            'by_type' => $this->getMetricsByType($days),
            'daily_stats' => $this->getDailyStats($days),
            'top_campaigns' => $this->getTopPerformingCampaigns(),
            'report_period' => [
                'start_date' => now()->subDays($days)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
                'days' => $days
            ]
        ];
    }
}

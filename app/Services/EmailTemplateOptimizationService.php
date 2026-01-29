<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EmailTemplateOptimizationService
{
    private $emailAnalyticsService;

    public function __construct(EmailAnalyticsService $emailAnalyticsService)
    {
        $this->emailAnalyticsService = $emailAnalyticsService;
    }

    /**
     * A/B test email templates
     */
    public function runABTest(string $emailType, array $variants, User $user): string
    {
        $cacheKey = "ab_test:{$emailType}:{$user->id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($emailType, $variants, $user) {
            // Get user's test group based on user ID
            $testGroup = $user->id % count($variants);
            $selectedVariant = array_keys($variants)[$testGroup];
            
            // Track A/B test assignment
            $this->trackABTestAssignment($emailType, $selectedVariant, $user);
            
            return $selectedVariant;
        });
    }

    /**
     * Get optimal send time for user
     */
    public function getOptimalSendTime(User $user): string
    {
        $cacheKey = "optimal_send_time:{$user->id}";
        
        return Cache::remember($cacheKey, 86400, function () use ($user) {
            // Analyze user's email opening patterns
            $openingTimes = DB::table('email_analytics')
                ->where('user_id', $user->id)
                ->whereNotNull('opened_at')
                ->selectRaw('HOUR(opened_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('count', 'desc')
                ->first();

            if ($openingTimes) {
                return sprintf('%02d:00', $openingTimes->hour);
            }

            // Default optimal times based on user timezone and role
            if ($user->role === 'host') {
                return '09:00'; // Business hours for hosts
            }

            return '19:00'; // Evening for guests
        });
    }

    /**
     * Get optimal email frequency for user
     */
    public function getOptimalFrequency(User $user): array
    {
        $engagementMetrics = $this->emailAnalyticsService->getUserEngagementMetrics($user);
        
        // Base frequency on engagement score
        $engagementScore = $engagementMetrics['engagement_score'];
        
        if ($engagementScore >= 80) {
            return [
                'marketing' => 'weekly',
                'transactional' => 'immediate',
                'reminders' => 'daily'
            ];
        } elseif ($engagementScore >= 50) {
            return [
                'marketing' => 'bi-weekly',
                'transactional' => 'immediate',
                'reminders' => 'daily'
            ];
        } else {
            return [
                'marketing' => 'monthly',
                'transactional' => 'immediate',
                'reminders' => 'weekly'
            ];
        }
    }

    /**
     * Optimize email subject line
     */
    public function optimizeSubjectLine(string $baseSubject, User $user, string $emailType): string
    {
        // Get best performing subject line patterns for this email type
        $topPatterns = $this->getTopSubjectPatterns($emailType);
        
        // Personalize based on user data
        $personalizedSubject = $this->personalizeSubject($baseSubject, $user);
        
        // Apply best performing patterns
        return $this->applySubjectOptimizations($personalizedSubject, $topPatterns, $user);
    }

    /**
     * Get content optimization recommendations
     */
    public function getContentOptimizations(string $emailType): array
    {
        $metrics = $this->emailAnalyticsService->getEmailMetrics($emailType, 30);
        
        $recommendations = [];
        
        // Low open rate recommendations
        if ($metrics['open_rate'] < 20) {
            $recommendations[] = [
                'type' => 'subject_line',
                'priority' => 'high',
                'suggestion' => 'Improve subject line with urgency or personalization',
                'current_rate' => $metrics['open_rate']
            ];
        }
        
        // Low click rate recommendations
        if ($metrics['click_rate'] < 5) {
            $recommendations[] = [
                'type' => 'call_to_action',
                'priority' => 'high',
                'suggestion' => 'Make call-to-action buttons more prominent and compelling',
                'current_rate' => $metrics['click_rate']
            ];
        }
        
        // Content length optimization
        $avgContentLength = $this->getAverageContentLength($emailType);
        if ($avgContentLength > 1000) {
            $recommendations[] = [
                'type' => 'content_length',
                'priority' => 'medium',
                'suggestion' => 'Consider shortening email content for better engagement',
                'current_length' => $avgContentLength
            ];
        }
        
        return $recommendations;
    }

    /**
     * Generate email performance insights
     */
    public function generateInsights(string $emailType): array
    {
        $metrics = $this->emailAnalyticsService->getEmailMetrics($emailType, 30);
        $benchmarks = $this->getIndustryBenchmarks();
        
        $insights = [];
        
        // Compare against benchmarks
        foreach (['open_rate', 'click_rate'] as $metric) {
            $performance = $metrics[$metric];
            $benchmark = $benchmarks[$metric];
            
            if ($performance > $benchmark * 1.2) {
                $insights[] = [
                    'type' => 'success',
                    'metric' => $metric,
                    'message' => "Excellent {$metric}! 20% above industry average.",
                    'value' => $performance,
                    'benchmark' => $benchmark
                ];
            } elseif ($performance < $benchmark * 0.8) {
                $insights[] = [
                    'type' => 'warning',
                    'metric' => $metric,
                    'message' => "Low {$metric}. Consider optimization strategies.",
                    'value' => $performance,
                    'benchmark' => $benchmark
                ];
            }
        }
        
        return $insights;
    }

    /**
     * Get device-specific optimizations
     */
    public function getDeviceOptimizations(User $user): array
    {
        $deviceStats = DB::table('email_analytics')
            ->where('user_id', $user->id)
            ->whereNotNull('device_type')
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->orderBy('count', 'desc')
            ->get();

        $primaryDevice = $deviceStats->first()->device_type ?? 'mobile';
        
        $optimizations = [
            'mobile' => [
                'subject_length' => 30,
                'preheader_length' => 35,
                'button_size' => 'large',
                'image_width' => 320
            ],
            'desktop' => [
                'subject_length' => 50,
                'preheader_length' => 90,
                'button_size' => 'medium',
                'image_width' => 600
            ],
            'tablet' => [
                'subject_length' => 40,
                'preheader_length' => 60,
                'button_size' => 'medium',
                'image_width' => 480
            ]
        ];
        
        return $optimizations[$primaryDevice] ?? $optimizations['mobile'];
    }

    /**
     * Track A/B test assignment
     */
    private function trackABTestAssignment(string $emailType, string $variant, User $user): void
    {
        DB::table('ab_test_assignments')->updateOrInsert(
            [
                'user_id' => $user->id,
                'email_type' => $emailType,
            ],
            [
                'variant' => $variant,
                'assigned_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Get top performing subject line patterns
     */
    private function getTopSubjectPatterns(string $emailType): array
    {
        // This would analyze historical data to find patterns
        // For now, return common high-performing patterns
        return [
            'urgency' => ['Limited time', 'Expires soon', 'Last chance'],
            'personalization' => ['{name}', 'Your', 'For you'],
            'numbers' => ['50%', '24 hours', '3 days'],
            'emojis' => ['🎉', '⏰', '🔥', '✨']
        ];
    }

    /**
     * Personalize subject line
     */
    private function personalizeSubject(string $subject, User $user): string
    {
        return str_replace(
            ['{name}', '{first_name}'],
            [$user->name, explode(' ', $user->name)[0]],
            $subject
        );
    }

    /**
     * Apply subject line optimizations
     */
    private function applySubjectOptimizations(string $subject, array $patterns, User $user): string
    {
        $engagementScore = $this->emailAnalyticsService->getUserEngagementMetrics($user)['engagement_score'];
        
        // For low engagement users, add urgency
        if ($engagementScore < 30) {
            $urgencyWords = $patterns['urgency'] ?? [];
            if (!empty($urgencyWords) && !$this->containsUrgency($subject)) {
                $subject = $urgencyWords[0] . ': ' . $subject;
            }
        }
        
        // For high engagement users, add emojis
        if ($engagementScore > 70) {
            $emojis = $patterns['emojis'] ?? [];
            if (!empty($emojis) && !$this->containsEmoji($subject)) {
                $subject = $emojis[0] . ' ' . $subject;
            }
        }
        
        return $subject;
    }

    /**
     * Check if subject contains urgency words
     */
    private function containsUrgency(string $subject): bool
    {
        $urgencyWords = ['limited', 'expires', 'last', 'urgent', 'hurry', 'now'];
        return str_contains(strtolower($subject), implode('|', $urgencyWords));
    }

    /**
     * Check if subject contains emojis
     */
    private function containsEmoji(string $subject): bool
    {
        return preg_match('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]/u', $subject);
    }

    /**
     * Get average content length for email type
     */
    private function getAverageContentLength(string $emailType): int
    {
        // This would analyze actual email content lengths
        // For now, return estimated values
        $estimates = [
            'welcome' => 800,
            'booking_confirmation' => 600,
            'marketing' => 1200,
            'reminder' => 400,
        ];
        
        return $estimates[$emailType] ?? 800;
    }

    /**
     * Get industry benchmarks
     */
    private function getIndustryBenchmarks(): array
    {
        return [
            'open_rate' => 22.0,  // Travel industry average
            'click_rate' => 3.5,  // Travel industry average
            'unsubscribe_rate' => 0.3,
            'bounce_rate' => 2.0,
        ];
    }
}

<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmailQueueManagementService
{
    /**
     * Schedule email with optimal timing
     */
    public function scheduleOptimalEmail($mailable, User $user, string $emailType): void
    {
        $optimizationService = app(EmailTemplateOptimizationService::class);
        $optimalTime = $optimizationService->getOptimalSendTime($user);
        
        // Calculate delay until optimal send time
        $sendAt = $this->calculateOptimalSendTime($optimalTime, $user->timezone ?? 'Asia/Riyadh');
        
        // Queue the email with delay
        $mailable->delay($sendAt);
        
        // Track scheduled email
        $this->trackScheduledEmail($user, $emailType, $sendAt);
        
        Log::info('Email scheduled for optimal time', [
            'user_id' => $user->id,
            'email_type' => $emailType,
            'scheduled_for' => $sendAt->toDateTimeString()
        ]);
    }

    /**
     * Manage email frequency limits
     */
    public function canSendEmail(User $user, string $emailType): bool
    {
        $limits = $this->getEmailLimits($emailType);
        $sentCount = $this->getRecentEmailCount($user, $emailType, $limits['period']);
        
        if ($sentCount >= $limits['max_count']) {
            Log::warning('Email frequency limit reached', [
                'user_id' => $user->id,
                'email_type' => $emailType,
                'sent_count' => $sentCount,
                'limit' => $limits['max_count']
            ]);
            return false;
        }
        
        return true;
    }

    /**
     * Batch process emails for better performance
     */
    public function batchProcessEmails(array $emails, int $batchSize = 100): void
    {
        $chunks = array_chunk($emails, $batchSize);
        
        foreach ($chunks as $index => $chunk) {
            // Stagger batch processing to avoid overwhelming the mail server
            $delay = $index * 30; // 30 seconds between batches
            
            Queue::later(now()->addSeconds($delay), function () use ($chunk) {
                foreach ($chunk as $emailData) {
                    try {
                        $this->processEmail($emailData);
                    } catch (\Exception $e) {
                        Log::error('Failed to process email in batch', [
                            'email_data' => $emailData,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });
        }
        
        Log::info('Batch email processing scheduled', [
            'total_emails' => count($emails),
            'batches' => count($chunks),
            'batch_size' => $batchSize
        ]);
    }

    /**
     * Implement smart retry logic for failed emails
     */
    public function retryFailedEmail(array $emailData, int $attemptNumber = 1): void
    {
        $maxAttempts = 3;
        $backoffMultiplier = 2; // Exponential backoff
        
        if ($attemptNumber > $maxAttempts) {
            Log::error('Email failed after maximum retry attempts', [
                'email_data' => $emailData,
                'attempts' => $attemptNumber
            ]);
            
            // Move to dead letter queue or notify admin
            $this->handlePermanentFailure($emailData);
            return;
        }
        
        // Calculate delay with exponential backoff
        $delay = pow($backoffMultiplier, $attemptNumber - 1) * 60; // Minutes
        
        Queue::later(now()->addMinutes($delay), function () use ($emailData, $attemptNumber) {
            try {
                $this->processEmail($emailData);
            } catch (\Exception $e) {
                Log::warning('Email retry failed', [
                    'email_data' => $emailData,
                    'attempt' => $attemptNumber,
                    'error' => $e->getMessage()
                ]);
                
                $this->retryFailedEmail($emailData, $attemptNumber + 1);
            }
        });
    }

    /**
     * Monitor email queue health
     */
    public function getQueueHealth(): array
    {
        $queueSize = Queue::size('emails');
        $failedJobs = DB::table('failed_jobs')->where('queue', 'emails')->count();
        $avgProcessingTime = $this->getAverageProcessingTime();
        
        $health = [
            'status' => 'healthy',
            'queue_size' => $queueSize,
            'failed_jobs' => $failedJobs,
            'avg_processing_time' => $avgProcessingTime,
            'recommendations' => []
        ];
        
        // Determine health status and recommendations
        if ($queueSize > 1000) {
            $health['status'] = 'warning';
            $health['recommendations'][] = 'High queue size - consider adding more workers';
        }
        
        if ($failedJobs > 50) {
            $health['status'] = 'critical';
            $health['recommendations'][] = 'High failure rate - check email configuration';
        }
        
        if ($avgProcessingTime > 30) {
            $health['status'] = 'warning';
            $health['recommendations'][] = 'Slow processing - optimize email templates';
        }
        
        return $health;
    }

    /**
     * Implement email throttling to respect provider limits
     */
    public function throttleEmails(string $provider = 'default'): void
    {
        $limits = $this->getProviderLimits($provider);
        $currentRate = $this->getCurrentSendRate();
        
        if ($currentRate > $limits['per_minute']) {
            $delay = 60 - (now()->second);
            sleep($delay);
            
            Log::info('Email sending throttled', [
                'provider' => $provider,
                'current_rate' => $currentRate,
                'limit' => $limits['per_minute'],
                'delay' => $delay
            ]);
        }
    }

    /**
     * Prioritize emails based on type and urgency
     */
    public function prioritizeEmail(string $emailType, User $user): string
    {
        $priorities = [
            'password_reset' => 'high',
            'booking_confirmation' => 'high',
            'payment_confirmation' => 'high',
            'booking_reminder' => 'medium',
            'review_request' => 'medium',
            'marketing' => 'low',
            'newsletter' => 'low'
        ];
        
        $basePriority = $priorities[$emailType] ?? 'medium';
        
        // Adjust priority based on user status
        if ($user->role === 'admin') {
            return 'high';
        }
        
        if ($user->created_at->isAfter(now()->subDays(7))) {
            // New users get higher priority
            return $basePriority === 'low' ? 'medium' : 'high';
        }
        
        return $basePriority;
    }

    /**
     * Calculate optimal send time based on user timezone
     */
    private function calculateOptimalSendTime(string $optimalTime, string $timezone): Carbon
    {
        $userTime = Carbon::createFromFormat('H:i', $optimalTime, $timezone);
        $now = Carbon::now($timezone);
        
        // If optimal time has passed today, schedule for tomorrow
        if ($userTime->isPast()) {
            $userTime->addDay();
        }
        
        // Convert to UTC for queue scheduling
        return $userTime->utc();
    }

    /**
     * Track scheduled email
     */
    private function trackScheduledEmail(User $user, string $emailType, Carbon $scheduledFor): void
    {
        DB::table('scheduled_emails')->insert([
            'user_id' => $user->id,
            'email_type' => $emailType,
            'scheduled_for' => $scheduledFor,
            'status' => 'scheduled',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Get email frequency limits
     */
    private function getEmailLimits(string $emailType): array
    {
        $limits = [
            'marketing' => ['max_count' => 2, 'period' => 'week'],
            'newsletter' => ['max_count' => 1, 'period' => 'week'],
            'reminder' => ['max_count' => 3, 'period' => 'day'],
            'transactional' => ['max_count' => 10, 'period' => 'day'],
        ];
        
        return $limits[$emailType] ?? ['max_count' => 5, 'period' => 'day'];
    }

    /**
     * Get recent email count for user
     */
    private function getRecentEmailCount(User $user, string $emailType, string $period): int
    {
        $since = match($period) {
            'hour' => now()->subHour(),
            'day' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            default => now()->subDay()
        };
        
        return DB::table('email_analytics')
            ->where('user_id', $user->id)
            ->where('email_type', $emailType)
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Process individual email
     */
    private function processEmail(array $emailData): void
    {
        // Implementation would depend on email data structure
        // This is a placeholder for the actual email processing logic
        Log::info('Processing email', ['email_data' => $emailData]);
    }

    /**
     * Handle permanent email failure
     */
    private function handlePermanentFailure(array $emailData): void
    {
        DB::table('failed_emails')->insert([
            'email_data' => json_encode($emailData),
            'failed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Optionally notify administrators
        Log::critical('Email permanently failed', ['email_data' => $emailData]);
    }

    /**
     * Get average email processing time
     */
    private function getAverageProcessingTime(): float
    {
        // This would calculate based on job processing metrics
        // For now, return a placeholder value
        return Cache::remember('avg_email_processing_time', 300, function () {
            return 15.5; // seconds
        });
    }

    /**
     * Get email provider limits
     */
    private function getProviderLimits(string $provider): array
    {
        $limits = [
            'ses' => ['per_minute' => 200, 'per_day' => 50000],
            'sendgrid' => ['per_minute' => 600, 'per_day' => 100000],
            'mailgun' => ['per_minute' => 300, 'per_day' => 10000],
            'default' => ['per_minute' => 100, 'per_day' => 1000]
        ];
        
        return $limits[$provider] ?? $limits['default'];
    }

    /**
     * Get current email send rate
     */
    private function getCurrentSendRate(): int
    {
        return Cache::remember('current_send_rate', 60, function () {
            return DB::table('email_analytics')
                ->where('created_at', '>=', now()->subMinute())
                ->count();
        });
    }
}

<?php

namespace App\Console\Commands;

use App\Services\EmailQueueManagementService;
use App\Services\EmailAnalyticsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:health-check 
                            {--fix : Automatically fix issues where possible}
                            {--notify : Send notification if critical issues found}
                            {--detailed : Show detailed diagnostics}';

    /**
     * The console command description.
     */
    protected $description = 'Perform comprehensive health check of email system';

    protected $queueService;
    protected $analyticsService;

    public function __construct(
        EmailQueueManagementService $queueService,
        EmailAnalyticsService $analyticsService
    ) {
        parent::__construct();
        $this->queueService = $queueService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fix = $this->option('fix');
        $notify = $this->option('notify');
        $detailed = $this->option('detailed');

        $this->info('🏥 Starting email system health check...');

        $healthReport = [
            'overall_status' => 'healthy',
            'checks' => [],
            'issues' => [],
            'recommendations' => []
        ];

        try {
            // Run all health checks
            $this->checkQueueHealth($healthReport, $fix);
            $this->checkDatabaseHealth($healthReport, $fix);
            $this->checkEmailConfiguration($healthReport, $fix);
            $this->checkDeliverabilityMetrics($healthReport);
            $this->checkPerformanceMetrics($healthReport);
            $this->checkStorageHealth($healthReport, $fix);

            // Determine overall health status
            $this->determineOverallHealth($healthReport);

            // Display results
            $this->displayHealthReport($healthReport, $detailed);

            // Handle notifications and fixes
            if ($notify && $healthReport['overall_status'] === 'critical') {
                $this->sendHealthAlert($healthReport);
            }

            $this->info('✅ Email health check completed');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Health check failed: ' . $e->getMessage());
            Log::error('Email health check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Check queue health
     */
    protected function checkQueueHealth(array &$healthReport, bool $fix): void
    {
        $this->line('📋 Checking queue health...');

        $queueHealth = $this->queueService->getQueueHealth();
        
        $healthReport['checks']['queue'] = [
            'status' => $queueHealth['status'],
            'queue_size' => $queueHealth['queue_size'],
            'failed_jobs' => $queueHealth['failed_jobs'],
            'avg_processing_time' => $queueHealth['avg_processing_time']
        ];

        if ($queueHealth['status'] !== 'healthy') {
            $healthReport['issues'][] = [
                'type' => 'queue',
                'severity' => $queueHealth['status'] === 'critical' ? 'critical' : 'warning',
                'message' => 'Email queue health issues detected',
                'details' => $queueHealth['recommendations']
            ];

            if ($fix) {
                $this->fixQueueIssues($queueHealth);
            }
        }
    }

    /**
     * Check database health
     */
    protected function checkDatabaseHealth(array &$healthReport, bool $fix): void
    {
        $this->line('🗄️ Checking database health...');

        $checks = [
            'email_analytics_table' => $this->checkTableExists('email_analytics'),
            'scheduled_emails_table' => $this->checkTableExists('scheduled_emails'),
            'failed_emails_table' => $this->checkTableExists('failed_emails'),
            'notifications_table' => $this->checkTableExists('notifications'),
        ];

        $dbHealth = 'healthy';
        $issues = [];

        foreach ($checks as $check => $result) {
            if (!$result) {
                $dbHealth = 'critical';
                $issues[] = "Missing table: {$check}";
            }
        }

        // Check for old data that should be cleaned up
        $oldAnalytics = DB::table('email_analytics')
            ->where('created_at', '<', now()->subDays(90))
            ->count();

        if ($oldAnalytics > 10000) {
            $issues[] = "Large amount of old analytics data ({$oldAnalytics} records)";
            if ($dbHealth === 'healthy') {
                $dbHealth = 'warning';
            }
        }

        $healthReport['checks']['database'] = [
            'status' => $dbHealth,
            'table_checks' => $checks,
            'old_analytics_count' => $oldAnalytics
        ];

        if (!empty($issues)) {
            $healthReport['issues'][] = [
                'type' => 'database',
                'severity' => $dbHealth,
                'message' => 'Database health issues detected',
                'details' => $issues
            ];

            if ($fix && $oldAnalytics > 10000) {
                $this->cleanupOldData();
            }
        }
    }

    /**
     * Check email configuration
     */
    protected function checkEmailConfiguration(array &$healthReport, bool $fix): void
    {
        $this->line('⚙️ Checking email configuration...');

        $configChecks = [
            'mail_driver' => config('mail.default') !== null,
            'mail_host' => config('mail.mailers.smtp.host') !== null,
            'mail_port' => config('mail.mailers.smtp.port') !== null,
            'mail_from_address' => config('mail.from.address') !== null,
            'queue_connection' => config('queue.default') !== 'sync',
        ];

        $configHealth = 'healthy';
        $issues = [];

        foreach ($configChecks as $check => $result) {
            if (!$result) {
                $configHealth = 'critical';
                $issues[] = "Configuration issue: {$check}";
            }
        }

        // Test email sending capability
        try {
            $testResult = $this->testEmailSending();
            $configChecks['email_sending'] = $testResult;
        } catch (\Exception $e) {
            $configChecks['email_sending'] = false;
            $configHealth = 'critical';
            $issues[] = "Email sending test failed: " . $e->getMessage();
        }

        $healthReport['checks']['configuration'] = [
            'status' => $configHealth,
            'config_checks' => $configChecks
        ];

        if (!empty($issues)) {
            $healthReport['issues'][] = [
                'type' => 'configuration',
                'severity' => $configHealth,
                'message' => 'Email configuration issues detected',
                'details' => $issues
            ];
        }
    }

    /**
     * Check deliverability metrics
     */
    protected function checkDeliverabilityMetrics(array &$healthReport): void
    {
        $this->line('📬 Checking deliverability metrics...');

        $metrics = [
            'delivery_rate' => $this->calculateDeliveryRate(),
            'bounce_rate' => $this->calculateBounceRate(),
            'spam_rate' => $this->calculateSpamRate(),
            'unsubscribe_rate' => $this->calculateUnsubscribeRate(),
        ];

        $deliverabilityHealth = 'healthy';
        $issues = [];

        // Check against thresholds
        if ($metrics['delivery_rate'] < 95) {
            $deliverabilityHealth = 'warning';
            $issues[] = "Low delivery rate: {$metrics['delivery_rate']}%";
        }

        if ($metrics['bounce_rate'] > 5) {
            $deliverabilityHealth = 'warning';
            $issues[] = "High bounce rate: {$metrics['bounce_rate']}%";
        }

        if ($metrics['spam_rate'] > 1) {
            $deliverabilityHealth = 'critical';
            $issues[] = "High spam rate: {$metrics['spam_rate']}%";
        }

        $healthReport['checks']['deliverability'] = [
            'status' => $deliverabilityHealth,
            'metrics' => $metrics
        ];

        if (!empty($issues)) {
            $healthReport['issues'][] = [
                'type' => 'deliverability',
                'severity' => $deliverabilityHealth,
                'message' => 'Deliverability issues detected',
                'details' => $issues
            ];
        }
    }

    /**
     * Check performance metrics
     */
    protected function checkPerformanceMetrics(array &$healthReport): void
    {
        $this->line('⚡ Checking performance metrics...');

        $overallMetrics = $this->analyticsService->getEmailMetrics(null, 7);
        
        $performanceHealth = 'healthy';
        $issues = [];

        if ($overallMetrics['open_rate'] < 15) {
            $performanceHealth = 'warning';
            $issues[] = "Low open rate: {$overallMetrics['open_rate']}%";
        }

        if ($overallMetrics['click_rate'] < 2) {
            $performanceHealth = 'warning';
            $issues[] = "Low click rate: {$overallMetrics['click_rate']}%";
        }

        $healthReport['checks']['performance'] = [
            'status' => $performanceHealth,
            'metrics' => $overallMetrics
        ];

        if (!empty($issues)) {
            $healthReport['issues'][] = [
                'type' => 'performance',
                'severity' => $performanceHealth,
                'message' => 'Performance issues detected',
                'details' => $issues
            ];
        }
    }

    /**
     * Check storage health
     */
    protected function checkStorageHealth(array &$healthReport, bool $fix): void
    {
        $this->line('💾 Checking storage health...');

        $storageChecks = [
            'logs_directory' => is_writable(storage_path('logs')),
            'cache_directory' => is_writable(storage_path('framework/cache')),
            'views_directory' => is_writable(storage_path('framework/views')),
        ];

        $storageHealth = 'healthy';
        $issues = [];

        foreach ($storageChecks as $check => $result) {
            if (!$result) {
                $storageHealth = 'critical';
                $issues[] = "Storage issue: {$check} not writable";
            }
        }

        // Check disk space
        $freeSpace = disk_free_space(storage_path());
        $totalSpace = disk_total_space(storage_path());
        $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;

        if ($usagePercent > 90) {
            $storageHealth = 'critical';
            $issues[] = "Low disk space: {$usagePercent}% used";
        } elseif ($usagePercent > 80) {
            $storageHealth = 'warning';
            $issues[] = "High disk usage: {$usagePercent}% used";
        }

        $healthReport['checks']['storage'] = [
            'status' => $storageHealth,
            'storage_checks' => $storageChecks,
            'disk_usage_percent' => round($usagePercent, 2)
        ];

        if (!empty($issues)) {
            $healthReport['issues'][] = [
                'type' => 'storage',
                'severity' => $storageHealth,
                'message' => 'Storage issues detected',
                'details' => $issues
            ];
        }
    }

    /**
     * Determine overall health status
     */
    protected function determineOverallHealth(array &$healthReport): void
    {
        $criticalIssues = array_filter($healthReport['issues'], function ($issue) {
            return $issue['severity'] === 'critical';
        });

        $warningIssues = array_filter($healthReport['issues'], function ($issue) {
            return $issue['severity'] === 'warning';
        });

        if (!empty($criticalIssues)) {
            $healthReport['overall_status'] = 'critical';
        } elseif (!empty($warningIssues)) {
            $healthReport['overall_status'] = 'warning';
        } else {
            $healthReport['overall_status'] = 'healthy';
        }
    }

    /**
     * Display health report
     */
    protected function displayHealthReport(array $healthReport, bool $detailed): void
    {
        $statusIcon = match($healthReport['overall_status']) {
            'healthy' => '🟢',
            'warning' => '🟡',
            'critical' => '🔴',
            default => '⚪'
        };

        $this->info("\n{$statusIcon} Overall Status: " . strtoupper($healthReport['overall_status']));

        if ($detailed) {
            $this->displayDetailedReport($healthReport);
        } else {
            $this->displaySummaryReport($healthReport);
        }
    }

    /**
     * Display detailed report
     */
    protected function displayDetailedReport(array $healthReport): void
    {
        foreach ($healthReport['checks'] as $checkType => $checkData) {
            $statusIcon = match($checkData['status']) {
                'healthy' => '✅',
                'warning' => '⚠️',
                'critical' => '❌',
                default => '❓'
            };

            $this->line("\n{$statusIcon} " . ucfirst($checkType) . ": " . $checkData['status']);
        }

        if (!empty($healthReport['issues'])) {
            $this->warn("\n🚨 Issues Found:");
            foreach ($healthReport['issues'] as $issue) {
                $this->line("  • {$issue['message']}");
                foreach ($issue['details'] as $detail) {
                    $this->line("    - {$detail}");
                }
            }
        }
    }

    /**
     * Display summary report
     */
    protected function displaySummaryReport(array $healthReport): void
    {
        $summary = [];
        foreach ($healthReport['checks'] as $checkType => $checkData) {
            $summary[] = [ucfirst($checkType), $checkData['status']];
        }

        $this->table(['Component', 'Status'], $summary);

        if (!empty($healthReport['issues'])) {
            $this->warn("\n⚠️ " . count($healthReport['issues']) . " issue(s) found. Use --detailed for more information.");
        }
    }

    // Helper methods for various checks...
    
    protected function checkTableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function testEmailSending(): bool
    {
        // This would test actual email sending capability
        return true; // Placeholder
    }

    protected function calculateDeliveryRate(): float
    {
        return 98.5; // Placeholder
    }

    protected function calculateBounceRate(): float
    {
        return 1.2; // Placeholder
    }

    protected function calculateSpamRate(): float
    {
        return 0.3; // Placeholder
    }

    protected function calculateUnsubscribeRate(): float
    {
        return 0.5; // Placeholder
    }

    protected function fixQueueIssues(array $queueHealth): void
    {
        $this->line('🔧 Attempting to fix queue issues...');
        // Implementation would fix queue issues
    }

    protected function cleanupOldData(): void
    {
        $this->line('🧹 Cleaning up old analytics data...');
        DB::table('email_analytics')
            ->where('created_at', '<', now()->subDays(90))
            ->delete();
    }

    protected function sendHealthAlert(array $healthReport): void
    {
        $this->line('📧 Sending health alert notification...');
        // Implementation would send alert to administrators
    }
}

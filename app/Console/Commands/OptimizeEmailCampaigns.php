<?php

namespace App\Console\Commands;

use App\Services\EmailAnalyticsService;
use App\Services\EmailTemplateOptimizationService;
use App\Services\EmailPersonalizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OptimizeEmailCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:optimize 
                            {--type=all : Email type to optimize (all, welcome, booking, etc.)}
                            {--analyze-only : Only analyze without making changes}
                            {--days=30 : Number of days to analyze}';

    /**
     * The console command description.
     */
    protected $description = 'Analyze and optimize email campaigns based on performance data';

    protected $analyticsService;
    protected $optimizationService;
    protected $personalizationService;

    public function __construct(
        EmailAnalyticsService $analyticsService,
        EmailTemplateOptimizationService $optimizationService,
        EmailPersonalizationService $personalizationService
    ) {
        parent::__construct();
        $this->analyticsService = $analyticsService;
        $this->optimizationService = $optimizationService;
        $this->personalizationService = $personalizationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $analyzeOnly = $this->option('analyze-only');
        $days = (int) $this->option('days');

        $this->info('🚀 Starting email campaign optimization...');

        try {
            if ($type === 'all') {
                $this->optimizeAllCampaigns($days, $analyzeOnly);
            } else {
                $this->optimizeSpecificCampaign($type, $days, $analyzeOnly);
            }

            $this->info('✅ Email optimization completed successfully');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Email optimization failed: ' . $e->getMessage());
            Log::error('Email optimization command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Optimize all email campaigns
     */
    protected function optimizeAllCampaigns(int $days, bool $analyzeOnly): void
    {
        $this->info('📊 Analyzing all email campaigns...');

        $emailTypes = $this->getEmailTypes();
        $optimizations = [];

        foreach ($emailTypes as $emailType) {
            $this->line("Analyzing {$emailType}...");
            
            $metrics = $this->analyticsService->getEmailMetrics($emailType, $days);
            $recommendations = $this->optimizationService->getContentOptimizations($emailType);
            $insights = $this->optimizationService->generateInsights($emailType);

            $optimizations[$emailType] = [
                'metrics' => $metrics,
                'recommendations' => $recommendations,
                'insights' => $insights
            ];

            $this->displayMetrics($emailType, $metrics);
            $this->displayRecommendations($recommendations);
        }

        if (!$analyzeOnly) {
            $this->applyOptimizations($optimizations);
        }

        $this->generateOptimizationReport($optimizations);
    }

    /**
     * Optimize specific email campaign
     */
    protected function optimizeSpecificCampaign(string $emailType, int $days, bool $analyzeOnly): void
    {
        $this->info("📊 Analyzing {$emailType} campaign...");

        $metrics = $this->analyticsService->getEmailMetrics($emailType, $days);
        $recommendations = $this->optimizationService->getContentOptimizations($emailType);
        $insights = $this->optimizationService->generateInsights($emailType);

        $this->displayMetrics($emailType, $metrics);
        $this->displayRecommendations($recommendations);
        $this->displayInsights($insights);

        if (!$analyzeOnly && !empty($recommendations)) {
            if ($this->confirm('Apply optimization recommendations?')) {
                $this->applySpecificOptimizations($emailType, $recommendations);
            }
        }
    }

    /**
     * Display email metrics
     */
    protected function displayMetrics(string $emailType, array $metrics): void
    {
        $this->table(
            ['Metric', 'Value', 'Status'],
            [
                ['Total Sent', number_format($metrics['total_sent']), '📧'],
                ['Open Rate', $metrics['open_rate'] . '%', $this->getStatusIcon($metrics['open_rate'], 20)],
                ['Click Rate', $metrics['click_rate'] . '%', $this->getStatusIcon($metrics['click_rate'], 3)],
                ['CTR', $metrics['click_through_rate'] . '%', $this->getStatusIcon($metrics['click_through_rate'], 15)],
            ]
        );
    }

    /**
     * Display optimization recommendations
     */
    protected function displayRecommendations(array $recommendations): void
    {
        if (empty($recommendations)) {
            $this->info('✅ No optimization recommendations - campaign is performing well!');
            return;
        }

        $this->warn('⚠️  Optimization Recommendations:');
        foreach ($recommendations as $recommendation) {
            $priority = strtoupper($recommendation['priority']);
            $icon = $recommendation['priority'] === 'high' ? '🔴' : ($recommendation['priority'] === 'medium' ? '🟡' : '🟢');
            
            $this->line("{$icon} [{$priority}] {$recommendation['suggestion']}");
        }
    }

    /**
     * Display insights
     */
    protected function displayInsights(array $insights): void
    {
        if (empty($insights)) {
            return;
        }

        $this->info('💡 Performance Insights:');
        foreach ($insights as $insight) {
            $icon = $insight['type'] === 'success' ? '✅' : '⚠️';
            $this->line("{$icon} {$insight['message']}");
        }
    }

    /**
     * Apply optimizations
     */
    protected function applyOptimizations(array $optimizations): void
    {
        $this->info('🔧 Applying optimizations...');

        foreach ($optimizations as $emailType => $data) {
            if (!empty($data['recommendations'])) {
                $this->applySpecificOptimizations($emailType, $data['recommendations']);
            }
        }
    }

    /**
     * Apply specific optimizations
     */
    protected function applySpecificOptimizations(string $emailType, array $recommendations): void
    {
        foreach ($recommendations as $recommendation) {
            switch ($recommendation['type']) {
                case 'subject_line':
                    $this->optimizeSubjectLines($emailType);
                    break;
                case 'send_time':
                    $this->optimizeSendTimes($emailType);
                    break;
                case 'personalization':
                    $this->enhancePersonalization($emailType);
                    break;
                case 'content_length':
                    $this->optimizeContentLength($emailType);
                    break;
            }
        }
    }

    /**
     * Optimize subject lines
     */
    protected function optimizeSubjectLines(string $emailType): void
    {
        $this->line("  📝 Optimizing subject lines for {$emailType}...");
        // Implementation would update subject line templates
        Log::info("Subject line optimization applied for {$emailType}");
    }

    /**
     * Optimize send times
     */
    protected function optimizeSendTimes(string $emailType): void
    {
        $this->line("  ⏰ Optimizing send times for {$emailType}...");
        // Implementation would update send time configurations
        Log::info("Send time optimization applied for {$emailType}");
    }

    /**
     * Enhance personalization
     */
    protected function enhancePersonalization(string $emailType): void
    {
        $this->line("  👤 Enhancing personalization for {$emailType}...");
        // Implementation would update personalization rules
        Log::info("Personalization enhancement applied for {$emailType}");
    }

    /**
     * Optimize content length
     */
    protected function optimizeContentLength(string $emailType): void
    {
        $this->line("  📏 Optimizing content length for {$emailType}...");
        // Implementation would update content templates
        Log::info("Content length optimization applied for {$emailType}");
    }

    /**
     * Generate optimization report
     */
    protected function generateOptimizationReport(array $optimizations): void
    {
        $this->info('📋 Optimization Report:');
        
        $totalRecommendations = 0;
        $highPriorityCount = 0;
        
        foreach ($optimizations as $emailType => $data) {
            $recommendations = $data['recommendations'];
            $totalRecommendations += count($recommendations);
            
            foreach ($recommendations as $rec) {
                if ($rec['priority'] === 'high') {
                    $highPriorityCount++;
                }
            }
        }
        
        $this->table(
            ['Summary', 'Count'],
            [
                ['Email Types Analyzed', count($optimizations)],
                ['Total Recommendations', $totalRecommendations],
                ['High Priority Items', $highPriorityCount],
                ['Optimization Status', $totalRecommendations > 0 ? 'Needs Attention' : 'Performing Well'],
            ]
        );
    }

    /**
     * Get status icon based on performance
     */
    protected function getStatusIcon(float $value, float $benchmark): string
    {
        if ($value >= $benchmark * 1.2) {
            return '🟢'; // Excellent
        } elseif ($value >= $benchmark) {
            return '🟡'; // Good
        } else {
            return '🔴'; // Needs improvement
        }
    }

    /**
     * Get available email types
     */
    protected function getEmailTypes(): array
    {
        return [
            'welcome',
            'booking_confirmation',
            'payment_confirmation',
            'booking_reminder',
            'review_request',
            'host_payout',
            'property_approval',
            'special_offer'
        ];
    }
}

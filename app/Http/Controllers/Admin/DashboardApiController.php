<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use App\Services\AdminAnalyticsService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    protected AdminAnalyticsService $analyticsService;
    protected SettingsService $settingsService;

    public function __construct(AdminAnalyticsService $analyticsService, SettingsService $settingsService)
    {
        $this->analyticsService = $analyticsService;
        $this->settingsService = $settingsService;
    }

    /**
     * Get dashboard overview statistics
     * 
     * @return JsonResponse
     */
    public function getOverview(): JsonResponse
    {
        // Get today's stats
        $todayStats = $this->analyticsService->getDailyStats(now());
        
        // Get metrics
        $metrics = [
            'users_count' => User::count(),
            'properties_count' => Property::count(),
            'bookings_count' => Booking::count(),
            'active_properties' => Property::active()->count(),
            'total_revenue' => Booking::where('status', 'completed')->sum('total_price'),
            'today_bookings' => $todayStats['bookings_count'],
            'today_revenue' => $todayStats['revenue'],
            'today_new_users' => $todayStats['new_users'],
            'today_new_properties' => $todayStats['new_properties']
        ];
        
        // Get charts data
        $revenue = $this->analyticsService->getRevenueChart();
        $bookings = $this->analyticsService->getBookingChart();
        $userGrowth = $this->analyticsService->getUserGrowthChart();
        
        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => $metrics,
                'charts' => [
                    'revenue' => $revenue,
                    'bookings' => $bookings,
                    'user_growth' => $userGrowth
                ]
            ]
        ]);
    }
    
    /**
     * Get system configuration status
     * 
     * @return JsonResponse
     */
    public function getSystemStatus(): JsonResponse
    {
        // Get system settings
        $settings = $this->settingsService->getAllSettings();
        
        // Check essential configurations
        $configChecks = [
            'payment_gateways' => [
                'paypal' => !empty(config('services.paypal.client_id')),
                'stripe' => !empty(config('services.stripe.key')),
                'myfatoorah' => !empty(config('services.myfatoorah.api_key'))
            ],
            'ai_services' => [
                'openai' => !empty(config('openai.api_key')),
                'gemini' => !empty(config('ai.gemini_api_key'))
            ],
            'email' => [
                'configured' => !empty(config('mail.mailers.smtp.username')),
                'from_address' => config('mail.from.address')
            ],
            'sms' => [
                'twilio' => !empty(config('services.twilio.sid')),
                'vonage' => !empty(config('services.vonage.key'))
            ],
            'storage' => [
                'driver' => config('filesystems.default'),
                's3_configured' => !empty(config('filesystems.disks.s3.key'))
            ],
            'pusher' => [
                'configured' => !empty(config('broadcasting.connections.pusher.key')),
                'app_id' => config('broadcasting.connections.pusher.app_id')
            ]
        ];
        
        // Get server information
        $serverInfo = [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database' => DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION),
            'timezone' => config('app.timezone'),
            'environment' => app()->environment()
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                'config_checks' => $configChecks,
                'server_info' => $serverInfo,
                'settings' => $settings
            ]
        ]);
    }
    
    /**
     * Get Sara AI statistics
     * 
     * @return JsonResponse
     */
    public function getSaraStats(): JsonResponse
    {
        $stats = $this->analyticsService->getSaraAnalytics();
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Get referral program statistics
     * 
     * @return JsonResponse
     */
    public function getReferralStats(): JsonResponse
    {
        $stats = $this->analyticsService->getReferralAnalytics();
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Get data for administrative map
     * 
     * @return JsonResponse
     */
    public function getMapData(): JsonResponse
    {
        $mapData = $this->analyticsService->getBookingMapData();
        
        return response()->json([
            'success' => true,
            'data' => $mapData
        ]);
    }
    
    /**
     * Get application health check
     * 
     * @return JsonResponse
     */
    public function getHealthCheck(): JsonResponse
    {
        // Run health checks
        $checks = $this->analyticsService->runHealthChecks();
        
        return response()->json([
            'success' => true,
            'data' => $checks
        ]);
    }
}

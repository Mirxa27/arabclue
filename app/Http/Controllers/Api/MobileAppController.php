<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Mobile App API Controller
 * 
 * Handles mobile-specific functionality and API extensions
 */
class MobileAppController extends Controller
{
    /**
     * Update device information for mobile app
     */
    public function updateDeviceInfo(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $validated = $request->validate([
                'device_type' => 'required|string|in:ios,android,huawei',
                'os_version' => 'required|string',
                'app_version' => 'required|string',
                'fcm_token' => 'nullable|string',
                'apns_token' => 'nullable|string',
                'push_enabled' => 'nullable|boolean',
                'language' => 'nullable|string',
                'timezone' => 'nullable|string'
            ]);
            
            $user->updateDeviceInfo($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Device information updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update device info', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update device information',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get app configuration for mobile
     */
    public function getAppConfig(): JsonResponse
    {
        $config = [
            'version' => config('app.version', '1.0.0'),
            'min_version' => [
                'ios' => '1.0.0',
                'android' => '1.0.0',
                'huawei' => '1.0.0'
            ],
            'force_update' => false,
            'maintenance' => app()->isDownForMaintenance(),
            'features' => [
                'sara_chatbot' => true,
                'sara_voice' => true,
                'instant_booking' => true,
                'social_login' => true,
                'push_notifications' => true,
                'biometric_auth' => true,
                'offline_mode' => true,
                'dark_mode' => true,
                'wishlists' => true,
                'referrals' => true,
                'advanced_search' => true
            ],
            'api' => [
                'base_url' => config('app.url') . '/api/v1',
                'timeout' => 30000,
                'image_quality' => 'high',
                'cache_ttl' => 3600
            ],
            'urls' => [
                'terms' => url('/terms'),
                'privacy' => url('/privacy'),
                'support' => url('/support'),
                'faq' => url('/faq'),
                'about' => url('/about')
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => $config
        ]);
    }
    
    /**
     * Push notification test endpoint (for development)
     */
    public function testPushNotification(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->hasPushTokens()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No push tokens registered for this user'
                ], 400);
            }
            
            $result = $user->sendPushNotification(
                'Test Notification',
                'This is a test notification from HabibiStay',
                [
                    'type' => 'test',
                    'timestamp' => now()->toISOString()
                ]
            );
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test notification sent successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send test notification'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Push notification test failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error sending push notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get available notification channels and preferences
     */
    public function getNotificationPreferences(Request $request): JsonResponse
    {
        $user = $request->user();
        $notificationSettings = $user->notification_settings ?? [];
        
        $availableChannels = [
            'email' => [
                'available' => true,
                'verified' => !is_null($user->email_verified_at)
            ],
            'push' => [
                'available' => $user->hasPushTokens(),
                'enabled' => $user->hasPushNotificationsEnabled()
            ],
            'sms' => [
                'available' => !empty($user->phone),
                'verified' => $user->phone_verified ?? false
            ]
        ];
        
        $notificationTypes = [
            'bookings' => [
                'name' => 'Booking updates',
                'description' => 'Updates about your bookings and trips',
                'critical' => true
            ],
            'messages' => [
                'name' => 'Messages',
                'description' => 'New messages from hosts and guests',
                'critical' => false
            ],
            'reviews' => [
                'name' => 'Reviews',
                'description' => 'New reviews for your properties or from your guests',
                'critical' => false
            ],
            'reminders' => [
                'name' => 'Reminders',
                'description' => 'Reminders about check-ins, check-outs, and payments',
                'critical' => true
            ],
            'marketing' => [
                'name' => 'Marketing',
                'description' => 'Promotions and special offers',
                'critical' => false
            ],
            'urgent' => [
                'name' => 'Urgent alerts',
                'description' => 'Critical urgent notifications',
                'critical' => true
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                'channels' => $availableChannels,
                'notification_types' => $notificationTypes,
                'current_preferences' => $notificationSettings
            ]
        ]);
    }
    
    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $validated = $request->validate([
                'channel' => 'required|string|in:email,push,sms',
                'preferences' => 'required|array'
            ]);
            
            $channel = $validated['channel'];
            $preferences = $validated['preferences'];
            
            $notificationSettings = $user->notification_settings ?? [
                'email' => [],
                'push' => [],
                'sms' => []
            ];
            
            // Update the specific channel preferences
            $notificationSettings[$channel] = $preferences;
            
            $user->update([
                'notification_settings' => $notificationSettings
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification preferences updated successfully',
                'data' => $notificationSettings
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update notification preferences', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification preferences',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

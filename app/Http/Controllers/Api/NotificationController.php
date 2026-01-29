<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user's notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = $user->notifications();
        
        // Filter by read/unread status
        if ($request->has('unread_only') && $request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }
        
        // Filter by notification type
        if ($request->has('type')) {
            $query->where('data->type', $request->type);
        }
        
        // Pagination
        $perPage = min($request->get('per_page', 15), 50);
        $notifications = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => $user->unreadNotifications()->count(),
            ]
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->find($notificationId);
        
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        
        $user->unreadNotifications()->update(['read_at' => now()]);
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(string $notificationId): JsonResponse
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->find($notificationId);
        
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * Get notification statistics
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();
        
        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'read' => $user->readNotifications()->count(),
            'by_type' => $user->notifications()
                ->selectRaw('JSON_EXTRACT(data, "$.type") as type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'recent_activity' => $user->notifications()
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->count()
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'boolean',
            'push' => 'boolean',
            'sms' => 'boolean',
            'marketing' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $currentSettings = $user->notification_settings ?? [];
        
        $newSettings = array_merge($currentSettings, $request->only([
            'email', 'push', 'sms', 'marketing'
        ]));
        
        $user->update(['notification_settings' => $newSettings]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated',
            'data' => $newSettings
        ]);
    }

    /**
     * Get notification preferences
     */
    public function getPreferences(): JsonResponse
    {
        $user = Auth::user();
        
        $defaultSettings = [
            'email' => true,
            'push' => true,
            'sms' => false,
            'marketing' => true,
        ];
        
        $settings = array_merge($defaultSettings, $user->notification_settings ?? []);
        
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Update FCM token for push notifications
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $user->update(['fcm_token' => $request->fcm_token]);
        
        return response()->json([
            'success' => true,
            'message' => 'FCM token updated successfully'
        ]);
    }

    /**
     * Remove FCM token (logout from push notifications)
     */
    public function removeFcmToken(): JsonResponse
    {
        $user = Auth::user();
        $user->update(['fcm_token' => null]);
        
        return response()->json([
            'success' => true,
            'message' => 'FCM token removed successfully'
        ]);
    }

    /**
     * Test notification (for development/testing)
     */
    public function testNotification(Request $request): JsonResponse
    {
        if (!app()->environment(['local', 'staging'])) {
            return response()->json([
                'success' => false,
                'message' => 'Test notifications only available in development environments'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:welcome,booking_reminder,payout',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        switch ($request->type) {
            case 'welcome':
                $this->notificationService->sendWelcomeNotification($user);
                break;
                
            case 'booking_reminder':
                // Create a fake booking for testing
                $booking = new \App\Models\Booking([
                    'id' => 999,
                    'check_in' => now()->addDay(),
                    'check_out' => now()->addDays(3),
                ]);
                $booking->user = $user;
                $booking->property = new \App\Models\Property(['title' => 'Test Property']);
                
                $this->notificationService->sendBookingReminderNotification($booking, 'check_in');
                break;
                
            case 'payout':
                // Create a fake booking for testing
                $booking = new \App\Models\Booking(['id' => 999]);
                $booking->property = new \App\Models\Property(['title' => 'Test Property']);
                $booking->property->user = $user;
                
                $this->notificationService->sendHostPayoutNotification($booking, 500.00);
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Test notification sent successfully'
        ]);
    }
}

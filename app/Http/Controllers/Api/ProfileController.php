<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\UserPreference;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Http\Requests\ChangePasswordRequest;
use App\Services\UserProfileService;

class ProfileController extends Controller
{
    protected $profileService;
    
    /**
     * Constructor
     */
    public function __construct(UserProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Get user profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $profileData = $this->profileService->getCompleteProfile($user);
        
        return response()->json([
            'success' => true,
            'data' => $profileData
        ]);
    }

    /**
     * Get user preferences
     */
    public function getPreferences(Request $request): JsonResponse
    {
        $user = $request->user();
        $preferences = $this->profileService->getCategorizedPreferences($user);
        
        return response()->json([
            'success' => true,
            'data' => $preferences
        ]);
    }
    
    /**
     * Update user preference
     */
    public function updatePreference(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'category' => 'required|string|max:50',
            'key' => 'required|string|max:100',
            'value' => 'required'
        ]);
        
        $preference = $this->profileService->updatePreference(
            $user,
            $validated['category'],
            $validated['key'],
            $validated['value']
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Preference updated successfully',
            'data' => $preference
        ]);
    }
    
    /**
     * Get activity history
     */
    public function getActivityHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);
        
        $activities = UserActivity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
            
        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }
    
    /**
     * Upload identity verification document
     */
    public function uploadVerificationDocument(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'document_type' => 'required|string|in:passport,id_card,drivers_license,residence_permit',
            'document' => 'required|file|max:10240|mimes:jpeg,jpg,png,pdf'
        ]);
        
        $path = $this->profileService->uploadDocument(
            $user, 
            $request->file('document'),
            $validated['document_type']
        );
        
        // Mark as pending verification
        $user->update(['identity_verification_status' => 'pending']);
        
        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully and pending verification',
            'data' => [
                'document_type' => $validated['document_type'],
                'path' => $path
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'language' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100'
        ]);

        try {
            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'error' => true,
                'message' => 'The provided password does not match your current password.'
            ], 422);
        }

        try {
            $user->update([
                'password' => Hash::make($validated['new_password'])
            ]);

            // Revoke all other tokens
            $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to change password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user account
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $user = $request->user();

            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar updated successfully',
                'data' => [
                    'avatar_url' => Storage::url($path)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to update avatar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify user identity
     */
    public function verifyIdentity(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document_type' => 'required|in:passport,national_id,driving_license',
            'document_number' => 'required|string|max:50',
            'document_front' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'document_back' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'selfie' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            $user = $request->user();

            // Store documents
            $frontPath = $request->file('document_front')->store('identity/documents', 'private');
            $selfiePath = $request->file('selfie')->store('identity/selfies', 'private');
            
            $backPath = null;
            if ($request->hasFile('document_back')) {
                $backPath = $request->file('document_back')->store('identity/documents', 'private');
            }

            // Update user verification status
            $user->update([
                'identity_verified' => false, // Will be verified by admin
                'identity_verification_status' => 'pending',
                'identity_documents' => [
                    'type' => $validated['document_type'],
                    'number' => $validated['document_number'],
                    'front_path' => $frontPath,
                    'back_path' => $backPath,
                    'selfie_path' => $selfiePath,
                    'submitted_at' => now()
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Identity verification documents submitted successfully. We will review them within 24-48 hours.',
                'data' => [
                    'verification_status' => 'pending'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to submit identity verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'language' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'marketing_emails' => 'boolean',
            'booking_notifications' => 'boolean',
            'message_notifications' => 'boolean',
            'review_notifications' => 'boolean',
            'promotion_notifications' => 'boolean'
        ]);

        try {
            $user = $request->user();
            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Preferences updated successfully',
                'data' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to update preferences: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotificationSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'marketing_emails' => 'boolean',
            'booking_notifications' => 'boolean',
            'message_notifications' => 'boolean',
            'review_notifications' => 'boolean',
            'promotion_notifications' => 'boolean'
        ]);

        try {
            $user = $request->user();
            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Notification settings updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to update notification settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update device information
     */
    public function updateDevice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_token' => 'required|string',
            'device_type' => 'required|in:ios,android,web',
            'device_name' => 'nullable|string|max:100',
            'app_version' => 'nullable|string|max:20'
        ]);

        try {
            $user = $request->user();
            
            // Update or create device record
            $user->update([
                'device_token' => $validated['device_token'],
                'device_type' => $validated['device_type'],
                'device_name' => $validated['device_name'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'last_active_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device information updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to update device information: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user notifications
     */
    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min($request->get('per_page', 20), 50);

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(Request $request, $notificationId): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($notificationId);
        
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete notification
     */
    public function deleteNotification(Request $request, $notificationId): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($notificationId);
        
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * Delete user account
     */
    public function delete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string',
            'reason' => 'nullable|string|max:500'
        ]);

        $user = $request->user();

        // Verify password
        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'error' => true,
                'message' => 'Invalid password'
            ], 422);
        }

        try {
            // Check for active bookings
            $activeBookings = $user->bookings()
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('check_out', '>', now())
                ->count();

            if ($activeBookings > 0) {
                return response()->json([
                    'error' => true,
                    'message' => 'Cannot delete account with active bookings. Please complete or cancel your bookings first.'
                ], 422);
            }

            // Soft delete user
            $user->update([
                'deleted_reason' => $validated['reason'] ?? 'User requested deletion',
                'deleted_at' => now()
            ]);

            // Revoke all tokens
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to delete account: ' . $e->getMessage()
            ], 500);
        }
    }
}

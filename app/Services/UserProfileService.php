<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPreference;
use App\Models\UserActivity;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

/**
 * User Profile Service
 * 
 * Handles comprehensive user profile management including:
 * - Detailed user preferences
 * - Activity tracking and history
 * - Profile analytics and completion tracking
 * - User verification and documentation
 */
class UserProfileService
{
    /**
     * Get comprehensive user profile data
     */
    public function getCompleteProfile(User $user): array
    {
        return Cache::remember("user_complete_profile_{$user->id}", 60, function() use ($user) {
            // Load relationships
            $user->load([
                'wishlists.property:id,title,slug,city,price_per_night,images',
                'bookings' => function ($query) {
                    $query->latest()->limit(5);
                },
                'properties' => function ($query) {
                    $query->select('id', 'user_id', 'title', 'slug', 'status', 'price_per_night', 'images')
                          ->where('status', 'active');
                },
                'reviews:id,user_id,booking_id,rating,comment,created_at'
            ]);

            // Get profile completion data
            $profileCompletion = [
                'percentage' => $user->calculateProfileCompletion(),
                'missing_fields' => $user->getMissingProfileFields()
            ];

            // Get activity statistics
            $activityStats = $this->getUserActivityStats($user);

            // Get preferences by category
            $preferences = $this->getCategorizedPreferences($user);

            // Get roles and permissions
            $userRoles = [
                'is_host' => $user->role === User::ROLE_HOST,
                'is_guest' => $user->role === User::ROLE_GUEST || $user->role === User::ROLE_HOST, // Hosts can also be guests
                'is_admin' => $user->role === User::ROLE_ADMIN,
                'can_host' => $user->canHost()
            ];

            // Return complete profile data
            return [
                'user' => $user,
                'profile_completion' => $profileCompletion,
                'statistics' => $activityStats,
                'preferences' => $preferences,
                'roles' => $userRoles,
                'verification_status' => [
                    'email_verified' => !is_null($user->email_verified_at),
                    'phone_verified' => !empty($user->phone) && $user->phone_verified_at,
                    'identity_verified' => $user->identity_verified,
                    'verification_documents' => $this->getVerificationDocuments($user)
                ]
            ];
        });
    }

    /**
     * Get user preference hierarchy by category
     */
    public function getCategorizedPreferences(User $user): array
    {
        $preferences = UserPreference::where('user_id', $user->id)
            ->get()
            ->groupBy('category')
            ->map(function ($items) {
                return $items->mapWithKeys(function ($item) {
                    return [$item->key => $item->value];
                });
            })
            ->toArray();

        // Add any default preferences from the user model
        if ($user->preferences) {
            foreach ($user->preferences as $key => $value) {
                $category = $this->determineCategoryForKey($key);
                if (!isset($preferences[$category])) {
                    $preferences[$category] = [];
                }
                if (!isset($preferences[$category][$key])) {
                    $preferences[$category][$key] = $value;
                }
            }
        }

        return $preferences;
    }

    /**
     * Update user preference
     */
    public function updatePreference(User $user, string $category, string $key, $value): UserPreference
    {
        $preference = UserPreference::updateOrCreate(
            ['user_id' => $user->id, 'category' => $category, 'key' => $key],
            ['value' => $value]
        );

        // Also update nested array in user.preferences if it exists
        if ($user->preferences && is_array($user->preferences)) {
            $preferences = $user->preferences;
            $preferences[$key] = $value;
            $user->update(['preferences' => $preferences]);
        }

        // Log the preference change
        UserActivity::logActivity(
            $user->id,
            UserActivity::USER_PREFERENCE,
            "Updated preference: {$category}.{$key}",
            ['category' => $category, 'key' => $key]
        );

        // Clear cached profile
        Cache::forget("user_complete_profile_{$user->id}");
        
        return $preference;
    }

    /**
     * Upload and store user document
     */
    public function uploadDocument(User $user, UploadedFile $file, string $documentType): string
    {
        $path = "users/{$user->id}/documents/{$documentType}";
        $filename = $file->hashName();

        $storedPath = Storage::disk('private')->putFileAs($path, $file, $filename);

        // Log document upload
        UserActivity::logActivity(
            $user->id,
            UserActivity::ACCOUNT,
            "Uploaded document: {$documentType}",
            ['document_type' => $documentType, 'filename' => $filename]
        );

        return $storedPath;
    }

    /**
     * Get user's uploaded verification documents
     */
    protected function getVerificationDocuments(User $user): array
    {
        $disk = Storage::disk('private');
        $basePath = "users/{$user->id}/documents";

        if (!$disk->exists($basePath)) {
            return [];
        }

        $allFiles = $disk->allFiles($basePath);
        $documents = [];

        foreach ($allFiles as $file) {
            // Exclude hidden files (e.g., .DS_Store)
            if (strpos(basename($file), '.') === 0) {
                continue;
            }

            $filename = basename($file);
            $parentDir = basename(dirname($file));
            
            // The document type is the parent directory name.
            // If the parent is 'documents', it's a file at the root of the user's doc folder.
            $documentType = ($parentDir !== 'documents') ? $parentDir : 'other';

            $documents[] = [
                'type' => $documentType,
                'filename' => $filename,
                'uploaded_at' => $disk->lastModified($file),
                'path' => $file
            ];
        }

        return $documents;
    }

    /**
     * Get user activity statistics
     */
    protected function getUserActivityStats(User $user): array
    {
        $now = Carbon::now();
        
        // Get booking stats
        $bookings = $user->bookings;
        $completedBookings = $bookings->where('status', 'completed')->count();
        
        // Get reviews stats
        $reviews = Review::where('user_id', $user->id)->get();
        $averageRating = $reviews->avg('rating') ?? 0;
        
        // Get recent activity
        $recentActivity = UserActivity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return [
            'total_bookings' => $bookings->count(),
            'completed_bookings' => $completedBookings,
            'total_nights_stayed' => $this->calculateTotalNightsStayed($bookings),
            'average_stay_length' => $this->calculateAverageStayLength($bookings),
            'favorite_destinations' => $this->getFavoriteDestinations($bookings),
            'reviews_given' => $reviews->count(),
            'average_rating_given' => round($averageRating, 1),
            'member_since' => $user->created_at->format('F Y'),
            'days_as_member' => $user->created_at->diffInDays($now),
            'recent_activity' => $recentActivity,
        ];
    }

    /**
     * Calculate total nights stayed
     */
    protected function calculateTotalNightsStayed($bookings): int
    {
        return $bookings->where('status', 'completed')
            ->sum(function ($booking) {
                return Carbon::parse($booking->check_in)
                    ->diffInDays(Carbon::parse($booking->check_out));
            });
    }

    /**
     * Calculate average stay length
     */
    protected function calculateAverageStayLength($bookings): float
    {
        $completedBookings = $bookings->where('status', 'completed');
        
        if ($completedBookings->isEmpty()) {
            return 0;
        }
        
        $totalNights = $completedBookings->sum(function ($booking) {
            return Carbon::parse($booking->check_in)
                ->diffInDays(Carbon::parse($booking->check_out));
        });
        
        return round($totalNights / $completedBookings->count(), 1);
    }

    /**
     * Get favorite destinations
     */
    protected function getFavoriteDestinations($bookings): array
    {
        return $bookings->where('status', 'completed')
            ->groupBy('property.city')
            ->map(function ($cityBookings) {
                return $cityBookings->count();
            })
            ->sortDesc()
            ->take(3)
            ->toArray();
    }

    /**
     * Determine appropriate category for preference key
     */
    protected function determineCategoryForKey(string $key): string
    {
        $categoryMappings = [
            'currency' => 'currency',
            'language' => 'language',
            'date_format' => 'appearance',
            'time_format' => 'appearance',
            'theme' => 'appearance',
            'search_radius' => 'search',
            'instant_booking' => 'booking',
            'push_' => 'notifications',
            'email_' => 'notifications',
            'sms_' => 'notifications',
            'accessibility_' => 'accessibility',
            'payment_' => 'payment'
        ];

        foreach ($categoryMappings as $prefix => $category) {
            if (strpos($key, $prefix) === 0) {
                return $category;
            }
        }

        return 'other';
    }
}

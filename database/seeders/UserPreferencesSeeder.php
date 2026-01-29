<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserPreference;
use App\Models\User;

class UserPreferencesSeeder extends Seeder
{
    /**
     * Seed the user preferences table with default values
     */
    public function run(): void
    {
        // Define common preference categories and defaults
        $defaultPreferences = [
            'appearance' => [
                'theme' => 'light',
                'date_format' => 'd/m/Y',
                'time_format' => '24h',
                'language' => 'en',
                'currency' => 'SAR'
            ],
            'notifications' => [
                'email_bookings' => true,
                'email_messages' => true,
                'email_reviews' => true,
                'email_marketing' => false,
                'push_bookings' => true,
                'push_messages' => true,
                'push_reviews' => true,
                'push_reminders' => true,
                'sms_bookings' => true,
                'sms_urgent' => true
            ],
            'search' => [
                'search_radius' => 50,
                'search_sort' => 'recommended',
                'search_filters_expanded' => true,
                'recent_searches_count' => 5
            ],
            'accessibility' => [
                'accessibility_larger_text' => false,
                'accessibility_high_contrast' => false,
                'accessibility_reduced_motion' => false,
                'accessibility_screen_reader' => false
            ],
            'privacy' => [
                'privacy_profile_visibility' => 'registered',
                'privacy_show_activity' => true,
                'privacy_share_data' => false
            ],
            'booking' => [
                'instant_booking' => true,
                'show_price_breakdown' => true,
                'currency_display' => 'code'
            ]
        ];
        
        // Get all users
        $users = User::all();
        
        foreach ($users as $user) {
            foreach ($defaultPreferences as $category => $preferences) {
                foreach ($preferences as $key => $value) {
                    UserPreference::create([
                        'user_id' => $user->id,
                        'category' => $category,
                        'key' => $key,
                        'value' => $value
                    ]);
                }
            }
            
            // Add specific preferences based on role
            if ($user->role === User::ROLE_HOST) {
                $this->addHostPreferences($user);
            }
            
            if ($user->role === User::ROLE_ADMIN) {
                $this->addAdminPreferences($user);
            }
        }
    }
    
    /**
     * Add host-specific preferences
     */
    private function addHostPreferences(User $user): void
    {
        $hostPreferences = [
            'hosting' => [
                'auto_approve_bookings' => false,
                'instant_booking_enabled' => true,
                'minimum_notice' => 1,
                'maximum_notice' => 365,
                'minimum_stay' => 1,
                'maximum_stay' => 30,
                'calendar_sync_frequency' => 'hourly',
                'default_check_in_time' => '15:00',
                'default_check_out_time' => '11:00'
            ]
        ];
        
        foreach ($hostPreferences as $category => $preferences) {
            foreach ($preferences as $key => $value) {
                UserPreference::create([
                    'user_id' => $user->id,
                    'category' => $category,
                    'key' => $key,
                    'value' => $value
                ]);
            }
        }
    }
    
    /**
     * Add admin-specific preferences
     */
    private function addAdminPreferences(User $user): void
    {
        $adminPreferences = [
            'admin' => [
                'show_system_stats' => true,
                'auto_approve_properties' => false,
                'notification_frequency' => 'immediate',
                'dashboard_layout' => 'detailed',
                'system_alerts' => true
            ]
        ];
        
        foreach ($adminPreferences as $category => $preferences) {
            foreach ($preferences as $key => $value) {
                UserPreference::create([
                    'user_id' => $user->id,
                    'category' => $category,
                    'key' => $key,
                    'value' => $value
                ]);
            }
        }
    }
}

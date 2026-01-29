<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        $adminExists = User::where('email', 'admin@habibistay.com')->exists();
        
        if (!$adminExists) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@habibistay.com',
                'password' => Hash::make('admin123'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'language' => 'en',
                'preferences' => [
                    'dashboard_layout' => 'default',
                    'notifications' => true,
                    'dark_mode' => false
                ],
                'notification_settings' => [
                    'email_notifications' => true,
                    'sms_notifications' => false,
                    'push_notifications' => true
                ]
            ]);
            
            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: admin@habibistay.com');
            $this->command->info('Password: admin123');
        } else {
            $this->command->info('Admin user already exists!');
        }
        
        // Also create a test host user for testing host functions
        $hostExists = User::where('email', 'host@habibistay.com')->exists();
        
        if (!$hostExists) {
            User::create([
                'name' => 'Test Host',
                'email' => 'host@habibistay.com',
                'password' => Hash::make('host123'),
                'role' => User::ROLE_HOST,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'language' => 'en',
                'bio' => 'Experienced property host with multiple listings',
                'host_rating' => 4.8,
                'total_listings' => 3,
                'preferences' => [
                    'dashboard_layout' => 'default',
                    'notifications' => true,
                    'dark_mode' => false
                ],
                'notification_settings' => [
                    'email_notifications' => true,
                    'sms_notifications' => true,
                    'push_notifications' => true
                ]
            ]);
            
            $this->command->info('Host user created successfully!');
            $this->command->info('Email: host@habibistay.com');
            $this->command->info('Password: host123');
        } else {
            $this->command->info('Host user already exists!');
        }
        
        // Create a regular guest user for testing
        $guestExists = User::where('email', 'guest@habibistay.com')->exists();
        
        if (!$guestExists) {
            User::create([
                'name' => 'Test Guest',
                'email' => 'guest@habibistay.com',
                'password' => Hash::make('guest123'),
                'role' => User::ROLE_GUEST,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'language' => 'en',
                'bio' => 'Frequent traveler looking for unique stays',
                'guest_rating' => 4.9,
                'total_bookings' => 12,
                'preferences' => [
                    'dashboard_layout' => 'default',
                    'notifications' => true,
                    'dark_mode' => false
                ],
                'notification_settings' => [
                    'email_notifications' => true,
                    'sms_notifications' => false,
                    'push_notifications' => true
                ]
            ]);
            
            $this->command->info('Guest user created successfully!');
            $this->command->info('Email: guest@habibistay.com');
            $this->command->info('Password: guest123');
        } else {
            $this->command->info('Guest user already exists!');
        }
    }
}

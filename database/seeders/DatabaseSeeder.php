<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@habibistay.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        // Create a test host user
        User::create([
            'name' => 'Test Host',
            'email' => 'host@habibistay.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'role' => 'host',
        ]);

        // Create a test regular user
        User::create([
            'name' => 'Test User',
            'email' => 'user@habibistay.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'role' => 'guest',
        ]);
        
        // Seed user preferences
        $this->call([
            UserPreferencesSeeder::class,
        ]);
    }
}
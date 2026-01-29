<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'phone' => fake()->phoneNumber(),
            'role' => 'guest',
            'language' => 'en',
            'bio' => fake()->paragraph(),
            'status' => 'active',
            'preferences' => [
                'currency' => 'SAR',
                'notifications' => [
                    'email' => true,
                    'push' => true,
                    'sms' => false
                ]
            ],
            'notification_settings' => [
                'email' => true,
                'push' => true,
                'sms' => false,
                'marketing' => true
            ],
            'two_factor_enabled' => false,
            'identity_verified' => false,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is a host.
     */
    public function host(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'host',
            'identity_verified' => true,
            'identity_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'identity_verified' => true,
            'identity_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the user is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'identity_verified' => true,
            'identity_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the user has 2FA enabled.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt('JDDK4U6G3BJLEZ7Y'),
        ]);
    }
}

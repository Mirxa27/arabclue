<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);
        
        return [
            'user_id' => User::factory()->host(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(3, true),
            'property_type' => fake()->randomElement(['apartment', 'house', 'villa', 'studio', 'room']),
            'room_type' => fake()->randomElement(['entire_place', 'private_room', 'shared_room']),
            'accommodates' => fake()->numberBetween(1, 8),
            'bedrooms' => fake()->numberBetween(1, 4),
            'beds' => fake()->numberBetween(1, 6),
            'bathrooms' => fake()->randomFloat(1, 1, 3),
            'square_meters' => fake()->numberBetween(30, 200),
            'price_per_night' => fake()->randomFloat(2, 100, 1000),
            'cleaning_fee' => fake()->randomFloat(2, 50, 200),
            'service_fee_percentage' => 10.00,
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Riyadh', 'Jeddah', 'Dammam', 'Mecca', 'Medina']),
            'state' => fake()->randomElement(['Riyadh Province', 'Makkah Province', 'Eastern Province']),
            'country' => 'Saudi Arabia',
            'postal_code' => fake()->postcode(),
            'latitude' => fake()->latitude(24.0, 25.0),
            'longitude' => fake()->longitude(46.0, 47.0),
            'neighborhood' => fake()->citySuffix(),
            'check_in_time' => '15:00:00',
            'check_out_time' => '11:00:00',
            'house_rules' => [
                'No smoking',
                'No pets',
                'No parties or events',
                'Check-in after 3:00 PM',
                'Check-out before 11:00 AM'
            ],
            'cancellation_policy' => fake()->randomElement(['flexible', 'moderate', 'strict']),
            'instant_booking' => fake()->boolean(30),
            'minimum_nights' => fake()->numberBetween(1, 7),
            'maximum_nights' => fake()->numberBetween(30, 365),
            'smart_pricing_enabled' => fake()->boolean(20),
            'is_featured' => fake()->boolean(10),
            'status' => 'active',
            'views' => fake()->numberBetween(0, 1000),
            'saves' => fake()->numberBetween(0, 100),
            'shares' => fake()->numberBetween(0, 50),
            'review_count' => fake()->numberBetween(0, 50),
            'overall_rating' => fake()->randomFloat(2, 3.0, 5.0),
            'occupancy_rate' => fake()->randomFloat(2, 0.3, 0.9),
            'meta_title' => $title,
            'meta_description' => fake()->sentence(10),
            'meta_keywords' => fake()->words(5),
            'approved_at' => now(),
        ];
    }

    /**
     * Indicate that the property is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the property is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the property is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the property is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the property has instant booking.
     */
    public function instantBooking(): static
    {
        return $this->state(fn (array $attributes) => [
            'instant_booking' => true,
        ]);
    }

    /**
     * Indicate that the property is in Riyadh.
     */
    public function inRiyadh(): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => 'Riyadh',
            'state' => 'Riyadh Province',
            'latitude' => fake()->latitude(24.6, 24.8),
            'longitude' => fake()->longitude(46.6, 46.8),
        ]);
    }
}

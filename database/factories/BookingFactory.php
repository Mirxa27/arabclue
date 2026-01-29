<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $property = Property::factory()->create();
        $hostId = $property->user_id;
        $checkIn = fake()->dateTimeBetween('now', '+30 days');
        $checkOut = fake()->dateTimeBetween($checkIn, $checkIn->format('Y-m-d') . ' +7 days');
        $totalNights = $checkIn->diff($checkOut)->days;
        
        $subtotal = $property->price_per_night * $totalNights;
        $cleaningFee = $property->cleaning_fee;
        $serviceFee = $subtotal * ($property->service_fee_percentage / 100);
        $totalAmount = $subtotal + $cleaningFee + $serviceFee;

        return [
            'user_id' => User::factory(),
            'host_id' => $hostId,
            'property_id' => $property->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => fake()->numberBetween(1, $property->accommodates),
            'total_nights' => $totalNights,
            'subtotal' => $subtotal,
            'cleaning_fee' => $cleaningFee,
            'service_fee' => $serviceFee,
            'total_amount' => $totalAmount,
            'currency' => 'SAR',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => fake()->randomElement(['credit_card', 'debit_card', 'bank_transfer']),
            'payment_intent_id' => 'pi_' . fake()->uuid(),
            'special_requests' => fake()->optional()->sentence(),
            'guest_details' => [
                'adults' => fake()->numberBetween(1, 4),
                'children' => fake()->numberBetween(0, 2),
                'infants' => fake()->numberBetween(0, 1),
            ],
            'cancellation_reason' => null,
            'cancelled_at' => null,
            'confirmed_at' => now(),
        ];
    }

    /**
     * Indicate that the booking is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
            'confirmed_at' => null,
        ]);
    }

    /**
     * Indicate that the booking is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancellation_reason' => fake()->sentence(),
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Indicate that the booking is completed.
     */
    public function completed(): static
    {
        $checkOut = fake()->dateTimeBetween('-30 days', '-1 day');
        $checkIn = fake()->dateTimeBetween('-37 days', $checkOut);
        
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ]);
    }

    /**
     * Indicate that the booking is currently active (guest is staying).
     */
    public function active(): static
    {
        $checkIn = fake()->dateTimeBetween('-3 days', 'now');
        $checkOut = fake()->dateTimeBetween('now', '+7 days');
        
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ]);
    }

    /**
     * Indicate that the booking payment failed.
     */
    public function paymentFailed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'failed',
            'confirmed_at' => null,
        ]);
    }

    /**
     * Indicate that the booking is for a specific property.
     */
    public function forProperty(Property $property): static
    {
        return $this->state(fn (array $attributes) => [
            'property_id' => $property->id,
        ]);
    }

    /**
     * Indicate that the booking is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}

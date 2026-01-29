<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use App\Notifications\NewBookingNotification;
use App\Notifications\PaymentConfirmationNotification;
use App\Notifications\BookingCancelledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_create_booking()
    {
        Notification::fake();
        
        $guest = User::factory()->create();
        $property = Property::factory()->create(['status' => 'active']);

        $response = $this->actingAs($guest)->post('/api/bookings', [
            'property_id' => $property->id,
            'check_in' => '2024-06-01',
            'check_out' => '2024-06-04',
            'guests' => 2,
            'guest_details' => [
                'adults' => 2,
                'children' => 0,
                'infants' => 0,
            ],
            'special_requests' => 'Late check-in please',
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('bookings', [
            'user_id' => $guest->id,
            'property_id' => $property->id,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $booking = Booking::where('user_id', $guest->id)->first();
        
        // Assert notification was sent to host
        Notification::assertSentTo($property->user, NewBookingNotification::class);
    }

    public function test_booking_prevents_double_booking()
    {
        $guest = User::factory()->create();
        $property = Property::factory()->create(['status' => 'active']);
        
        // Create existing booking
        Booking::factory()->create([
            'property_id' => $property->id,
            'check_in' => '2024-06-01',
            'check_out' => '2024-06-05',
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($guest)->post('/api/bookings', [
            'property_id' => $property->id,
            'check_in' => '2024-06-03',
            'check_out' => '2024-06-07',
            'guests' => 2,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['check_in']);
    }

    public function test_booking_payment_confirmation()
    {
        Notification::fake();
        
        $booking = Booking::factory()->pending()->create();

        $response = $this->post("/api/bookings/{$booking->id}/confirm-payment", [
            'payment_intent_id' => 'pi_test_123456',
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_intent_id' => 'pi_test_123456',
        ]);

        $booking->refresh();
        $this->assertNotNull($booking->confirmed_at);

        // Assert payment confirmation notification was sent
        Notification::assertSentTo($booking->user, PaymentConfirmationNotification::class);
    }

    public function test_guest_can_cancel_booking()
    {
        Notification::fake();
        
        $booking = Booking::factory()->create(['status' => 'confirmed']);

        $response = $this->actingAs($booking->user)->post("/api/bookings/{$booking->id}/cancel", [
            'cancellation_reason' => 'Change of plans',
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
            'cancellation_reason' => 'Change of plans',
        ]);

        $booking->refresh();
        $this->assertNotNull($booking->cancelled_at);

        // Assert cancellation notifications were sent
        Notification::assertSentTo($booking->user, BookingCancelledNotification::class);
        Notification::assertSentTo($booking->property->user, BookingCancelledNotification::class);
    }

    public function test_host_can_cancel_booking()
    {
        Notification::fake();
        
        $booking = Booking::factory()->create(['status' => 'confirmed']);

        $response = $this->actingAs($booking->property->user)->post("/api/bookings/{$booking->id}/cancel", [
            'cancellation_reason' => 'Property maintenance required',
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
            'cancellation_reason' => 'Property maintenance required',
        ]);

        // Assert cancellation notifications were sent
        Notification::assertSentTo($booking->user, BookingCancelledNotification::class);
    }

    public function test_booking_pricing_calculation()
    {
        $property = Property::factory()->create([
            'price_per_night' => 250.00,
            'cleaning_fee' => 75.00,
            'service_fee_percentage' => 12.00,
        ]);

        $guest = User::factory()->create();

        $response = $this->actingAs($guest)->post('/api/bookings', [
            'property_id' => $property->id,
            'check_in' => '2024-06-01',
            'check_out' => '2024-06-05', // 4 nights
            'guests' => 3,
        ]);

        $response->assertStatus(201);
        
        $booking = Booking::where('user_id', $guest->id)->first();
        
        $this->assertEquals(4, $booking->nights);
        $this->assertEquals(1000.00, $booking->subtotal); // 4 * 250
        $this->assertEquals(75.00, $booking->cleaning_fee);
        $this->assertEquals(120.00, $booking->service_fee); // 12% of 1000
        $this->assertEquals(1195.00, $booking->total_amount);
    }

    public function test_booking_guest_limit_validation()
    {
        $property = Property::factory()->create([
            'accommodates' => 4,
            'status' => 'active',
        ]);

        $guest = User::factory()->create();

        $response = $this->actingAs($guest)->post('/api/bookings', [
            'property_id' => $property->id,
            'check_in' => '2024-06-01',
            'check_out' => '2024-06-04',
            'guests' => 6, // Exceeds property capacity
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['guests']);
    }

    public function test_booking_minimum_nights_validation()
    {
        $property = Property::factory()->create([
            'minimum_nights' => 3,
            'status' => 'active',
        ]);

        $guest = User::factory()->create();

        $response = $this->actingAs($guest)->post('/api/bookings', [
            'property_id' => $property->id,
            'check_in' => '2024-06-01',
            'check_out' => '2024-06-02', // Only 1 night
            'guests' => 2,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['check_out']);
    }

    public function test_booking_status_transitions()
    {
        $booking = Booking::factory()->pending()->create();

        // Test pending -> confirmed
        $booking->update(['status' => 'confirmed', 'payment_status' => 'paid']);
        $this->assertEquals('confirmed', $booking->status);

        // Test confirmed -> cancelled
        $booking->update(['status' => 'cancelled']);
        $this->assertEquals('cancelled', $booking->status);

        // Test that cancelled booking cannot be confirmed again
        $booking->update(['status' => 'confirmed']);
        $this->assertEquals('cancelled', $booking->fresh()->status);
    }

    public function test_guest_can_view_own_bookings()
    {
        $guest = User::factory()->create();
        $otherGuest = User::factory()->create();
        
        $guestBooking = Booking::factory()->create(['user_id' => $guest->id]);
        $otherBooking = Booking::factory()->create(['user_id' => $otherGuest->id]);

        $response = $this->actingAs($guest)->get('/api/bookings');

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $guestBooking->id]);
        $response->assertJsonMissing(['id' => $otherBooking->id]);
    }

    public function test_host_can_view_property_bookings()
    {
        $host = User::factory()->host()->create();
        $otherHost = User::factory()->host()->create();
        
        $hostProperty = Property::factory()->create(['user_id' => $host->id]);
        $otherProperty = Property::factory()->create(['user_id' => $otherHost->id]);
        
        $hostBooking = Booking::factory()->create(['property_id' => $hostProperty->id]);
        $otherBooking = Booking::factory()->create(['property_id' => $otherProperty->id]);

        $response = $this->actingAs($host)->get('/api/host/bookings');

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $hostBooking->id]);
        $response->assertJsonMissing(['id' => $otherBooking->id]);
    }
}

<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use App\Exceptions\BookingException;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookingService
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Generate unique booking reference
     */
    public function generateBookingReference(): string
    {
        do {
            $reference = 'HB' . strtoupper(substr(uniqid(), -8));
        } while (Booking::where('booking_reference', $reference)->exists());

        return $reference;
    }

    /**
     * Apply coupon to booking
     */
    public function applyCoupon(string $couponCode, float $totalAmount): array
    {
        $coupon = \App\Models\Coupon::where('code', $couponCode)
            ->where('status', 'active')
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->first();

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Invalid or expired coupon code'];
        }

        // Check usage limits
        if ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) {
            return ['valid' => false, 'message' => 'Coupon usage limit exceeded'];
        }

        // Check minimum amount
        if ($coupon->minimum_amount && $totalAmount < $coupon->minimum_amount) {
            return ['valid' => false, 'message' => "Minimum booking amount of {$coupon->minimum_amount} required"];
        }

        // Calculate discount
        $discount = 0;
        if ($coupon->type === 'percentage') {
            $discount = ($totalAmount * $coupon->value) / 100;
            if ($coupon->maximum_discount && $discount > $coupon->maximum_discount) {
                $discount = $coupon->maximum_discount;
            }
        } else {
            $discount = min($coupon->value, $totalAmount);
        }

        return [
            'valid' => true,
            'discount' => $discount,
            'coupon' => $coupon
        ];
    }
    public function createBooking(array $bookingData): Booking
    {
        return DB::transaction(function () use ($bookingData) {
            $property = Property::findOrFail($bookingData['property_id']);
            $guest = User::findOrFail($bookingData['user_id']);

            // Validate availability
            if (!$this->checkAvailability($property, $bookingData['check_in'], $bookingData['check_out'])) {
                throw new BookingException('Property is not available for the selected dates');
            }

            // Validate guest capacity
            if ($property->accommodates < $bookingData['guests']) {
                throw new BookingException('Property cannot accommodate the requested number of guests');
            }

            // Calculate pricing
            $pricing = $this->calculatePricing(
                $property,
                $bookingData['check_in'],
                $bookingData['check_out'],
                $bookingData['guests']
            );

            // Create booking
            $booking = Booking::create([
                'property_id' => $property->id,
                'user_id' => $guest->id,
                'host_id' => $property->user_id,
                'check_in' => $bookingData['check_in'],
                'check_out' => $bookingData['check_out'],
                'guests' => $bookingData['guests'],
                'adults' => $bookingData['adults'] ?? $bookingData['guests'],
                'children' => $bookingData['children'] ?? 0,
                'infants' => $bookingData['infants'] ?? 0,
                'price_per_night' => $pricing['price_per_night'],
                'total_nights' => $pricing['nights'],
                'accommodation_total' => $pricing['accommodation_total'],
                'cleaning_fee' => $pricing['cleaning_fee'],
                'service_fee' => $pricing['service_fee'],
                'host_service_fee' => $pricing['host_service_fee'],
                'tax_amount' => $pricing['tax_amount'],
                'total_amount' => $pricing['total_amount'],
                'currency' => 'SAR',
                'status' => $property->instant_booking && $guest->canInstantBook($property) ? 'accepted' : 'pending',
                'payment_status' => 'pending',
                'special_requests' => $bookingData['special_requests'] ?? null,
                'guest_agreed_to_rules' => $bookingData['guest_agreed_to_rules'] ?? false,
                'booked_via_sara' => $bookingData['booked_via_sara'] ?? false,
                'sara_conversation_id' => $bookingData['sara_conversation_id'] ?? null
            ]);

            // Send notifications
            $this->sendBookingNotifications($booking);

            return $booking;
        });
    }

    public function checkAvailability(Property $property, string $checkIn, string $checkOut): bool
    {
        return $property->isAvailable($checkIn, $checkOut);
    }

    public function calculatePricing(Property $property, string $checkIn, string $checkOut, int $guests = 2): array
    {
        return $property->calculateTotalPrice($checkIn, $checkOut, $guests);
    }

    public function confirmBooking(Booking $booking, array $paymentData): Booking
    {
        return DB::transaction(function () use ($booking, $paymentData) {
            if ($booking->status !== 'pending' && $booking->status !== 'accepted') {
                throw new BookingException('Booking cannot be confirmed in current status');
            }

            // Process payment
            $paymentResult = $this->processPayment($booking, $paymentData);

            if (!$paymentResult['success']) {
                throw new BookingException('Payment failed: ' . $paymentResult['message']);
            }

            // Update booking
            $booking->update([
                'status' => 'accepted',
                'payment_status' => 'paid',
                'payment_method' => $paymentData['method'],
                'transaction_id' => $paymentResult['transaction_id'],
                'payment_details' => $paymentResult['details'],
                'paid_at' => now()
            ]);

            // Send confirmation notifications
            $this->sendBookingConfirmation($booking);

            return $booking;
        });
    }

    public function cancelBooking(Booking $booking, ?User $cancelledBy = null, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($booking, $cancelledBy, $reason) {
            if (!$booking->canBeCancelled()) {
                throw new BookingException('Booking cannot be cancelled');
            }

            $refundAmount = $this->calculateRefund($booking);
            $cancellationStatus = $cancelledBy->id === $booking->user_id ? 'cancelled_by_guest' : 'cancelled_by_host';

            $booking->update([
                'status' => $cancellationStatus,
                'cancelled_by' => $cancelledBy?->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'refund_amount' => $refundAmount,
                'refund_status' => $refundAmount > 0 ? 'pending' : null
            ]);

            // Process refund if applicable
            if ($refundAmount > 0) {
                $this->processRefund($booking);
            }

            // Send cancellation notifications
            $this->sendCancellationNotifications($booking);

            return $booking;
        });
    }

    public function acceptBooking(Booking $booking): Booking
    {
        if ($booking->status !== 'pending') {
            throw new BookingException('Only pending bookings can be accepted');
        }

        $booking->update(['status' => 'accepted']);

        // Send acceptance notification to guest
        $this->sendBookingAcceptance($booking);

        return $booking;
    }

    public function declineBooking(Booking $booking, ?string $reason = null): Booking
    {
        if ($booking->status !== 'pending') {
            throw new BookingException('Only pending bookings can be declined');
        }

        $booking->update([
            'status' => 'declined',
            'host_message' => $reason
        ]);

        // Process refund if payment was made
        if ($booking->payment_status === 'paid') {
            $this->processRefund($booking);
        }

        // Send decline notification to guest
        $this->sendBookingDecline($booking);

        return $booking;
    }

    public function checkIn(Booking $booking, array $checkInData = []): Booking
    {
        if ($booking->status !== 'accepted' || $booking->check_in->isAfter(now())) {
            throw new BookingException('Booking cannot be checked in');
        }

        $booking->update([
            'checked_in_at' => now(),
            'check_in_details' => $checkInData
        ]);

        return $booking;
    }

    public function checkOut(Booking $booking): Booking
    {
        if (!$booking->checked_in_at || $booking->checked_out_at) {
            throw new BookingException('Invalid check-out request');
        }

        $booking->update([
            'checked_out_at' => now(),
            'status' => 'completed'
        ]);

        // Send review request
        $this->sendReviewRequest($booking);

        return $booking;
    }

    protected function processPayment(Booking $booking, array $paymentData): array
    {
        $provider = $paymentData['provider'] ?? $this->paymentService->getBestProviderForBooking($booking);
        
        return $this->paymentService->processPayment($booking, $provider, $paymentData);
    }

    public function createPaymentIntent(Booking $booking, array $options = []): array
    {
        return $this->paymentService->createPaymentIntent($booking, $options);
    }

    public function getAvailablePaymentMethods(Booking $booking): array
    {
        return $this->paymentService->getAvailableProviders();
    }

    protected function calculateRefund(Booking $booking): float
    {
        if ($booking->payment_status !== 'paid') {
            return 0;
        }

        $now = now();
        $checkIn = Carbon::parse($booking->check_in);
        $hoursUntilCheckIn = $now->diffInHours($checkIn);

        // Get cancellation policy
        $policy = $booking->property->cancellation_policy;

        switch ($policy) {
            case 'flexible':
                return $hoursUntilCheckIn >= 24 ? $booking->total_amount : 0;
            
            case 'moderate':
                if ($hoursUntilCheckIn >= 120) { // 5 days
                    return $booking->total_amount;
                } elseif ($hoursUntilCheckIn >= 24) {
                    return $booking->total_amount * 0.5;
                }
                return 0;
            
            case 'strict':
                if ($hoursUntilCheckIn >= 168) { // 7 days
                    return $booking->total_amount * 0.5;
                }
                return 0;
            
            case 'super_strict':
                if ($hoursUntilCheckIn >= 720) { // 30 days
                    return $booking->total_amount * 0.5;
                }
                return 0;
            
            default:
                return 0;
        }
    }

    protected function processRefund(Booking $booking): void
    {
        $booking->update(['refund_status' => 'processing']);

        // Use the payment service to process refund
        $provider = $booking->payment_details['method'] ?? 'myfatoorah';
        
        try {
            $result = $this->paymentService->processRefund(
                $booking, 
                $provider, 
                $booking->refund_amount,
                $booking->cancellation_reason
            );

            if ($result['success']) {
                $booking->update([
                    'refund_status' => 'completed',
                    'refund_transaction_id' => $result['refund_id'] ?? null
                ]);
            } else {
                $booking->update(['refund_status' => 'failed']);
            }
        } catch (\Exception $e) {
            $booking->update(['refund_status' => 'failed']);
            Log::error('Refund processing failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function sendBookingNotifications(Booking $booking): void
    {
        // Send to guest
        dispatch(function () use ($booking) {
            // Mail::to($booking->guest)->send(new BookingCreated($booking));
        })->afterResponse();

        // Send to host
        dispatch(function () use ($booking) {
            // Mail::to($booking->host)->send(new NewBookingRequest($booking));
        })->afterResponse();
    }

    protected function sendBookingConfirmation(Booking $booking): void
    {
        dispatch(function () use ($booking) {
            // Mail::to($booking->guest)->send(new BookingConfirmed($booking));
            // Mail::to($booking->host)->send(new BookingConfirmedHost($booking));
        })->afterResponse();
    }

    protected function sendCancellationNotifications(Booking $booking): void
    {
        dispatch(function () use ($booking) {
            // Mail::to($booking->guest)->send(new BookingCancelled($booking));
            // Mail::to($booking->host)->send(new BookingCancelledHost($booking));
        })->afterResponse();
    }

    protected function sendBookingAcceptance(Booking $booking): void
    {
        dispatch(function () use ($booking) {
            // Mail::to($booking->guest)->send(new BookingAccepted($booking));
        })->afterResponse();
    }

    protected function sendBookingDecline(Booking $booking): void
    {
        dispatch(function () use ($booking) {
            // Mail::to($booking->guest)->send(new BookingDeclined($booking));
        })->afterResponse();
    }

    protected function sendReviewRequest(Booking $booking): void
    {
        dispatch(function () use ($booking) {
            // Mail::to($booking->guest)->send(new ReviewRequest($booking));
        })->delay(now()->addHours(24));
    }

    public function getUpcomingCheckIns(User $host, int $days = 7): \Illuminate\Support\Collection
    {
        return Booking::where('host_id', $host->id)
            ->where('status', 'accepted')
            ->whereBetween('check_in', [now(), now()->addDays($days)])
            ->with(['guest', 'property'])
            ->orderBy('check_in')
            ->get();
    }

    public function getUpcomingCheckOuts(User $host, int $days = 7): \Illuminate\Support\Collection
    {
        return Booking::where('host_id', $host->id)
            ->where('status', 'accepted')
            ->whereNotNull('checked_in_at')
            ->whereNull('checked_out_at')
            ->whereBetween('check_out', [now(), now()->addDays($days)])
            ->with(['guest', 'property'])
            ->orderBy('check_out')
            ->get();
    }

    public function getBookingStats(User $user, string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth()
        };

        $query = Booking::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate);

        return [
            'total_bookings' => $query->count(),
            'confirmed_bookings' => $query->where('status', 'accepted')->count(),
            'cancelled_bookings' => $query->whereIn('status', ['cancelled_by_guest', 'cancelled_by_host'])->count(),
            'total_spent' => $query->where('payment_status', 'paid')->sum('total_amount'),
            'upcoming_trips' => $user->bookings()->upcoming()->count(),
            'past_trips' => $user->bookings()->completed()->count()
        ];
    }
}
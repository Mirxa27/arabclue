<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class HostService
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Accept a booking request
     */
    public function acceptBooking(Booking $booking, ?string $hostMessage = null): array
    {
        if ($booking->status !== 'pending') {
            throw new \Exception('Only pending bookings can be accepted');
        }

        DB::beginTransaction();

        try {
            // Update booking status
            $booking->update([
                'status' => 'confirmed',
                'host_message' => $hostMessage,
                'accepted_at' => now()
            ]);

            // Send notification to guest
            $this->sendBookingAcceptedNotification($booking);

            // Block calendar dates
            $this->blockCalendarDates($booking);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Booking accepted successfully',
                'booking' => $booking->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Decline a booking request
     */
    public function declineBooking(Booking $booking, string $reason): array
    {
        if ($booking->status !== 'pending') {
            throw new \Exception('Only pending bookings can be declined');
        }

        DB::beginTransaction();

        try {
            // Update booking status
            $booking->update([
                'status' => 'declined',
                'host_message' => $reason,
                'declined_at' => now()
            ]);

            // Process refund if payment was already made
            if ($booking->payment_status === 'paid') {
                $refundAmount = $booking->total_amount;
                $booking->update([
                    'refund_amount' => $refundAmount,
                    'refund_status' => 'pending'
                ]);

                // Process the refund
                $this->processRefund($booking);
            }

            // Send notification to guest
            $this->sendBookingDeclinedNotification($booking);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Booking declined successfully',
                'booking' => $booking->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get host dashboard statistics
     */
    public function getHostDashboardStats(User $host, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'total_properties' => $host->properties()->count(),
            'active_properties' => $host->properties()->where('status', 'active')->count(),
            'pending_properties' => $host->properties()->where('status', 'pending')->count(),
            'total_bookings' => $host->hostBookings()->count(),
            'pending_bookings' => $host->hostBookings()->where('status', 'pending')->count(),
            'confirmed_bookings' => $host->hostBookings()->where('status', 'confirmed')->count(),
            'completed_bookings' => $host->hostBookings()->where('status', 'completed')->count(),
            'current_guests' => $this->getCurrentGuests($host),
            'upcoming_checkins' => $this->getUpcomingCheckins($host, 7),
            'upcoming_checkouts' => $this->getUpcomingCheckouts($host, 7),
            'total_earnings' => $this->getTotalEarnings($host),
            'earnings_this_month' => $this->getEarningsThisMonth($host),
            'earnings_period' => $this->getEarningsPeriod($host, $startDate),
            'average_rating' => $this->getAverageRating($host),
            'total_reviews' => $this->getTotalReviews($host),
            'occupancy_rate' => $this->getOccupancyRate($host, $days),
            'response_rate' => $this->getResponseRate($host, $days),
            'acceptance_rate' => $this->getAcceptanceRate($host, $days)
        ];
    }

    /**
     * Get current guests for host
     */
    protected function getCurrentGuests(User $host): int
    {
        return $host->hostBookings()
            ->where('status', 'confirmed')
            ->where('check_in', '<=', now())
            ->where('check_out', '>=', now())
            ->count();
    }

    /**
     * Get upcoming check-ins
     */
    protected function getUpcomingCheckins(User $host, int $days = 7): int
    {
        return $host->hostBookings()
            ->where('status', 'confirmed')
            ->where('check_in', '>', now())
            ->where('check_in', '<=', now()->addDays($days))
            ->count();
    }

    /**
     * Get upcoming check-outs
     */
    protected function getUpcomingCheckouts(User $host, int $days = 7): int
    {
        return $host->hostBookings()
            ->where('status', 'confirmed')
            ->where('check_out', '>', now())
            ->where('check_out', '<=', now()->addDays($days))
            ->count();
    }

    /**
     * Get total earnings for host
     */
    protected function getTotalEarnings(User $host): float
    {
        return $host->hostBookings()
            ->where('payment_status', 'paid')
            ->sum('host_payout_amount') ?? 0;
    }

    /**
     * Get earnings for current month
     */
    protected function getEarningsThisMonth(User $host): float
    {
        return $host->hostBookings()
            ->where('payment_status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('host_payout_amount') ?? 0;
    }

    /**
     * Get earnings for specific period
     */
    protected function getEarningsPeriod(User $host, Carbon $startDate): float
    {
        return $host->hostBookings()
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->sum('host_payout_amount') ?? 0;
    }

    /**
     * Get average rating for host
     */
    protected function getAverageRating(User $host): float
    {
        return $host->hostReviews()->avg('rating') ?? 0;
    }

    /**
     * Get total reviews for host
     */
    protected function getTotalReviews(User $host): int
    {
        return $host->hostReviews()->count();
    }

    /**
     * Calculate occupancy rate
     */
    protected function getOccupancyRate(User $host, int $days = 30): float
    {
        $totalDays = $host->properties()->sum('accommodates') * $days;
        if ($totalDays === 0) return 0;

        $bookedDays = $host->hostBookings()
            ->where('status', 'confirmed')
            ->where('check_in', '>=', now()->subDays($days))
            ->sum(DB::raw('julianday(check_out) - julianday(check_in)'));

        return ($bookedDays / $totalDays) * 100;
    }

    /**
     * Calculate response rate
     */
    protected function getResponseRate(User $host, int $days = 30): float
    {
        $totalInquiries = $host->hostBookings()
            ->where('created_at', '>=', now()->subDays($days))
            ->count();

        if ($totalInquiries === 0) return 100;

        $respondedInquiries = $host->hostBookings()
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('host_message')
            ->count();

        return ($respondedInquiries / $totalInquiries) * 100;
    }

    /**
     * Calculate acceptance rate
     */
    protected function getAcceptanceRate(User $host, int $days = 30): float
    {
        $totalRequests = $host->hostBookings()
            ->where('created_at', '>=', now()->subDays($days))
            ->whereIn('status', ['pending', 'confirmed', 'declined'])
            ->count();

        if ($totalRequests === 0) return 100;

        $acceptedRequests = $host->hostBookings()
            ->where('created_at', '>=', now()->subDays($days))
            ->where('status', 'confirmed')
            ->count();

        return ($acceptedRequests / $totalRequests) * 100;
    }

    /**
     * Block calendar dates for booking
     */
    protected function blockCalendarDates(Booking $booking): void
    {
        $property = $booking->property;
        $checkIn = Carbon::parse($booking->check_in);
        $checkOut = Carbon::parse($booking->check_out);

        $dates = [];
        $current = $checkIn->copy();
        
        while ($current->lt($checkOut)) {
            $dates[] = [
                'property_id' => $property->id,
                'date' => $current->toDateString(),
                'status' => 'booked',
                'booking_id' => $booking->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
            $current->addDay();
        }

        if (!empty($dates)) {
            DB::table('property_calendar')->insert($dates);
        }
    }

    /**
     * Process refund for declined booking
     */
    protected function processRefund(Booking $booking): void
    {
        // This would integrate with the payment service
        // For now, just mark as processed
        $booking->update(['refund_status' => 'processed']);
    }

    /**
     * Send booking accepted notification
     */
    protected function sendBookingAcceptedNotification(Booking $booking): void
    {
        // Send email to guest
        dispatch(new \App\Jobs\SendBookingAccepted($booking));
        
        // Send push notification
        dispatch(new \App\Jobs\SendPushNotification($booking->user, [
            'title' => 'Booking Accepted!',
            'body' => "Your booking request at {$booking->property->title} has been accepted by the host.",
            'data' => ['booking_id' => $booking->id, 'type' => 'booking_accepted']
        ]));
    }

    /**
     * Send booking declined notification
     */
    protected function sendBookingDeclinedNotification(Booking $booking): void
    {
        // Send email to guest
        dispatch(new \App\Jobs\SendBookingDeclined($booking));
        
        // Send push notification
        dispatch(new \App\Jobs\SendPushNotification($booking->user, [
            'title' => 'Booking Request Declined',
            'body' => "Your booking request at {$booking->property->title} was declined by the host.",
            'data' => ['booking_id' => $booking->id, 'type' => 'booking_declined']
        ]));
    }

    /**
     * Get host performance metrics
     */
    public function getHostPerformanceMetrics(User $host, int $days = 30): array
    {
        return [
            'response_time' => $this->getAverageResponseTime($host, $days),
            'response_rate' => $this->getResponseRate($host, $days),
            'acceptance_rate' => $this->getAcceptanceRate($host, $days),
            'cancellation_rate' => $this->getCancellationRate($host, $days),
            'overall_rating' => $this->getAverageRating($host),
            'review_count' => $this->getTotalReviews($host)
        ];
    }

    /**
     * Get average response time in hours
     */
    protected function getAverageResponseTime(User $host, int $days = 30): float
    {
        // This would calculate based on message timestamps
        // For now, return a placeholder
        return 2.5; // 2.5 hours average
    }

    /**
     * Calculate cancellation rate
     */
    protected function getCancellationRate(User $host, int $days = 30): float
    {
        $totalBookings = $host->hostBookings()
            ->where('created_at', '>=', now()->subDays($days))
            ->count();

        if ($totalBookings === 0) return 0;

        $cancelledBookings = $host->hostBookings()
            ->where('created_at', '>=', now()->subDays($days))
            ->where('status', 'cancelled')
            ->count();

        return ($cancelledBookings / $totalBookings) * 100;
    }

    public function updatePricingRules(Property $property, array $rules): void
    {
        $property->update([
            'smart_pricing_enabled' => $rules['smart_pricing_enabled'] ?? $property->smart_pricing_enabled,
            'seasonal_pricing' => $rules['seasonal_pricing'] ?? $property->seasonal_pricing,
            'length_of_stay_pricing' => $rules['length_of_stay_pricing'] ?? $property->length_of_stay_pricing,
            'special_offers' => $rules['special_offers'] ?? $property->special_offers,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use App\Services\BookingService;
use App\Services\Payment\PaymentService;
use App\Notifications\NewBookingNotification;
use App\Notifications\BookingCancelledNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    protected BookingService $bookingService;
    protected PaymentService $paymentService;

    public function __construct(BookingService $bookingService, PaymentService $paymentService)
    {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
    }

    /**
     * Get user's bookings
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->get('status', 'all');
        $perPage = min($request->get('per_page', 15), 50);

        $query = Booking::with(['property:id,title,slug,city,country,images', 'host:id,name,avatar'])
            ->where('user_id', $user->id);

        // Filter by status
        switch ($status) {
            case 'pending':
                $query->where('status', 'pending');
                break;
            case 'confirmed':
                $query->where('status', 'confirmed');
                break;
            case 'current':
                $query->where('status', 'confirmed')
                      ->where('check_in', '<=', now())
                      ->where('check_out', '>=', now());
                break;
            case 'upcoming':
                $query->where('status', 'confirmed')
                      ->where('check_in', '>', now());
                break;
            case 'previous':
                $query->where('status', 'completed')
                      ->orWhere(function($q) {
                          $q->where('status', 'confirmed')
                            ->where('check_out', '<', now());
                      });
                break;
            case 'cancelled':
                $query->where('status', 'cancelled');
                break;
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Create a new booking
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'infants' => 'nullable|integer|min:0',
            'special_requests' => 'nullable|string|max:1000',
            'coupon_code' => 'nullable|string|max:50',
            'payment_method' => 'required|in:paypal,myfatoorah',
            'guest_agreed_to_rules' => 'required|boolean|accepted'
        ]);

        try {
            DB::beginTransaction();

            $property = Property::findOrFail($validated['property_id']);
            $user = $request->user();

            // Check if property is available
            if (!$property->isAvailable($validated['check_in'], $validated['check_out'], $validated['guests'])) {
                return response()->json([
                    'error' => true,
                    'message' => 'Property is not available for the selected dates'
                ], 422);
            }

            // Calculate pricing
            $pricing = $property->calculateTotalPrice(
                $validated['check_in'],
                $validated['check_out'],
                $validated['guests']
            );

            // Apply coupon if provided
            if (!empty($validated['coupon_code'])) {
                $couponResult = $this->bookingService->applyCoupon($validated['coupon_code'], $pricing['total_amount']);
                if ($couponResult['valid']) {
                    $pricing['discount_amount'] = $couponResult['discount'];
                    $pricing['total_amount'] -= $couponResult['discount'];
                }
            }

            // Create booking
            $booking = Booking::create([
                'booking_reference' => $this->bookingService->generateBookingReference(),
                'property_id' => $property->id,
                'user_id' => $user->id,
                'host_id' => $property->user_id,
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'guests' => $validated['guests'],
                'adults' => $validated['adults'],
                'children' => $validated['children'] ?? 0,
                'infants' => $validated['infants'] ?? 0,
                'price_per_night' => $pricing['price_per_night'],
                'total_nights' => $pricing['total_nights'],
                'accommodation_total' => $pricing['accommodation_total'],
                'cleaning_fee' => $pricing['cleaning_fee'],
                'service_fee' => $pricing['service_fee'],
                'tax_amount' => $pricing['tax_amount'],
                'discount_amount' => $pricing['discount_amount'] ?? 0,
                'coupon_code' => $validated['coupon_code'] ?? null,
                'total_amount' => $pricing['total_amount'],
                'currency' => $pricing['currency'],
                'status' => $property->instant_booking ? 'confirmed' : 'pending',
                'payment_status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'special_requests' => $validated['special_requests'],
                'guest_agreed_to_rules' => $validated['guest_agreed_to_rules']
            ]);

            // Notify host
            $property->owner->notify(new NewBookingNotification($booking));

            DB::commit();

            // Load relationships for response
            $booking->load(['property:id,title,slug,city,country,images', 'host:id,name,email,phone']);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => [
                    'booking' => $booking,
                    'next_step' => 'payment'
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Failed to create booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific booking details
     */
    public function show(Request $request, Booking $booking): JsonResponse
    {
        // Check authorization
        if ($booking->user_id !== $request->user()->id && $booking->host_id !== $request->user()->id) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access to booking'
            ], 403);
        }

        $booking->load([
            'property:id,title,slug,description,city,country,address,images,amenities,house_rules',
            'host:id,name,email,phone,avatar',
            'user:id,name,email,phone,avatar'
        ]);

        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        // Check authorization
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized to cancel this booking'
            ], 403);
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500'
        ]);

        try {
            $result = $this->bookingService->cancelBooking($booking, $validated['cancellation_reason'], 'guest');

            // Notify host
            $booking->host->notify(new BookingCancelledNotification($booking));

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'data' => [
                    'booking' => $booking->fresh(),
                    'refund_amount' => $result['refund_amount'],
                    'refund_policy' => $result['refund_policy']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to cancel booking: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Submit review for completed booking
     */
    public function submitReview(Request $request, Booking $booking): JsonResponse
    {
        // Check authorization and booking status
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized to review this booking'
            ], 403);
        }

        if ($booking->status !== 'completed' && $booking->check_out > now()) {
            return response()->json([
                'error' => true,
                'message' => 'Can only review completed bookings'
            ], 422);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'cleanliness_rating' => 'required|integer|min:1|max:5',
            'accuracy_rating' => 'required|integer|min:1|max:5',
            'checkin_rating' => 'required|integer|min:1|max:5',
            'communication_rating' => 'required|integer|min:1|max:5',
            'location_rating' => 'required|integer|min:1|max:5',
            'value_rating' => 'required|integer|min:1|max:5'
        ]);

        try {
            $review = Review::create([
                'booking_id' => $booking->id,
                'property_id' => $booking->property_id,
                'user_id' => $booking->user_id,
                'host_id' => $booking->host_id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'cleanliness_rating' => $validated['cleanliness_rating'],
                'accuracy_rating' => $validated['accuracy_rating'],
                'checkin_rating' => $validated['checkin_rating'],
                'communication_rating' => $validated['communication_rating'],
                'location_rating' => $validated['location_rating'],
                'value_rating' => $validated['value_rating'],
                'status' => 'published'
            ]);

            // Update booking status
            $booking->update(['review_submitted' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully',
                'data' => $review
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to submit review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking invoice
     */
    public function invoice(Request $request, Booking $booking): JsonResponse
    {
        // Check authorization
        if ($booking->user_id !== $request->user()->id && $booking->host_id !== $request->user()->id) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized access to booking invoice'
            ], 403);
        }

        $booking->load([
            'property:id,title,address,city,country',
            'user:id,name,email',
            'host:id,name,email'
        ]);

        $invoice = [
            'booking_reference' => $booking->booking_reference,
            'booking_details' => $booking,
            'invoice_date' => $booking->created_at,
            'payment_date' => $booking->paid_at,
            'breakdown' => [
                'accommodation' => $booking->accommodation_total,
                'cleaning_fee' => $booking->cleaning_fee,
                'service_fee' => $booking->service_fee,
                'tax_amount' => $booking->tax_amount,
                'discount' => $booking->discount_amount,
                'total' => $booking->total_amount
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $invoice
        ]);
    }
}

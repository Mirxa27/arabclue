<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Display user's bookings
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status', 'all');

        $query = Booking::where('user_id', $user->id)->with(['property:id,title,slug,city,country,images']);

        // Filter by status
        switch ($status) {
            case 'upcoming':
                $query->where('status', 'confirmed')
                      ->where('check_in', '>', now());
                break;
            case 'current':
                $query->where('status', 'confirmed')
                      ->where('check_in', '<=', now())
                      ->where('check_out', '>=', now());
                break;
            case 'past':
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

        $bookings = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('bookings.index', compact('bookings', 'status'));
    }

    /**
     * Store a new booking
     */
    public function store(Request $request)
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
            'guest_agreed_to_rules' => 'required|accepted'
        ]);

        try {
            $property = Property::findOrFail($validated['property_id']);
            
            // Check availability
            if (!$property->isAvailable($validated['check_in'], $validated['check_out'], $validated['guests'])) {
                return back()->withErrors(['dates' => 'Property is not available for the selected dates.']);
            }

            // Create booking
            $booking = $this->bookingService->createBooking([
                'property_id' => $property->id,
                'user_id' => Auth::id(),
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'guests' => $validated['guests'],
                'adults' => $validated['adults'],
                'children' => $validated['children'] ?? 0,
                'infants' => $validated['infants'] ?? 0,
                'special_requests' => $validated['special_requests'],
                'guest_agreed_to_rules' => true
            ]);

            return redirect()->route('bookings.show', $booking)
                           ->with('success', 'Booking request submitted successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create booking: ' . $e->getMessage()]);
        }
    }

    /**
     * Display specific booking
     */
    public function show(Booking $booking)
    {
        // Check authorization
        if ($booking->user_id !== Auth::id() && $booking->host_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking');
        }

        $booking->load([
            'property:id,title,slug,description,city,country,address,images,amenities,house_rules',
            'host:id,name,email,phone,avatar',
            'user:id,name,email,phone,avatar'
        ]);

        return view('bookings.show', compact('booking'));
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, Booking $booking)
    {
        // Check authorization
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to cancel this booking');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500'
        ]);

        try {
            $this->bookingService->cancelBooking(
                $booking, 
                Auth::user(), 
                $validated['cancellation_reason']
            );

            return redirect()->route('bookings.show', $booking)
                           ->with('success', 'Booking cancelled successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to cancel booking: ' . $e->getMessage()]);
        }
    }

    /**
     * Show booking confirmation page
     */
    public function confirmation(Booking $booking)
    {
        // Check authorization
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking');
        }

        $booking->load([
            'property:id,title,slug,city,country,address,images',
            'host:id,name,email,phone'
        ]);

        return view('bookings.confirmation', compact('booking'));
    }

    /**
     * Show booking payment page
     */
    public function payment(Booking $booking)
    {
        // Check authorization
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking');
        }

        if ($booking->payment_status === 'paid') {
            return redirect()->route('bookings.confirmation', $booking);
        }

        $booking->load(['property:id,title,city,country,images']);

        $paymentMethods = $this->bookingService->getAvailablePaymentMethods($booking);

        return view('bookings.payment', compact('booking', 'paymentMethods'));
    }

    /**
     * Process booking payment
     */
    public function processPayment(Request $request, Booking $booking)
    {
        // Check authorization
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:paypal,myfatoorah',
            'payment_data' => 'required|array'
        ]);

        try {
            $result = $this->bookingService->confirmBooking($booking, [
                'provider' => $validated['payment_method'],
                'method' => $validated['payment_method'],
                ...$validated['payment_data']
            ]);

            if ($result['success']) {
                return redirect()->route('bookings.confirmation', $booking)
                               ->with('success', 'Payment processed successfully!');
            } else {
                return back()->withErrors(['payment' => 'Payment failed: ' . $result['message']]);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['payment' => 'Payment processing failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Download booking invoice
     */
    public function invoice(Booking $booking)
    {
        // Check authorization
        if ($booking->user_id !== Auth::id() && $booking->host_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking invoice');
        }

        $booking->load([
            'property:id,title,address,city,country',
            'user:id,name,email',
            'host:id,name,email'
        ]);

        return view('bookings.invoice', compact('booking'));
    }

    /**
     * Submit review for booking
     */
    public function submitReview(Request $request, Booking $booking)
    {
        // Check authorization
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to review this booking');
        }

        if ($booking->status !== 'completed' && $booking->check_out > now()) {
            return back()->withErrors(['error' => 'Can only review completed bookings']);
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
            $review = \App\Models\Review::create([
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

            // Update booking
            $booking->update(['review_submitted' => true]);

            return redirect()->route('bookings.show', $booking)
                           ->with('success', 'Review submitted successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to submit review: ' . $e->getMessage()]);
        }
    }

    /**
     * Show review form
     */
    public function reviewForm(Booking $booking)
    {
        // Check authorization
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to review this booking');
        }

        if ($booking->status !== 'completed' && $booking->check_out > now()) {
            abort(422, 'Can only review completed bookings');
        }

        if ($booking->review_submitted) {
            return redirect()->route('bookings.show', $booking)
                           ->with('info', 'You have already submitted a review for this booking.');
        }

        $booking->load(['property:id,title,images', 'host:id,name,avatar']);

        return view('bookings.review', compact('booking'));
    }
}

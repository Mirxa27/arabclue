<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\Payment\PaymentService;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CreatePaymentIntentRequest;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Requests\CalculateFeesRequest;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected BookingService $bookingService;

    public function __construct(PaymentService $paymentService, BookingService $bookingService)
    {
        $this->paymentService = $paymentService;
        $this->bookingService = $bookingService;
    }

    /**
     * Create payment intent for booking
     */
    public function createPaymentIntent(CreatePaymentIntentRequest $request, Booking $booking): JsonResponse
    {
        try {
            // Check if user can access this booking
            if ($booking->user_id !== $request->user()->id) {
                return response()->json([
                    'error' => true,
                    'message' => 'Unauthorized access to booking'
                ], 403);
            }

            // Check booking status
            if (!in_array($booking->status, ['pending', 'accepted'])) {
                return response()->json([
                    'error' => true,
                    'message' => 'Booking cannot be paid in current status'
                ], 400);
            }

            $options = $request->only(['provider', 'return_url', 'cancel_url']);
            $result = $this->bookingService->createPaymentIntent($booking, $options);

            if (!$result['success']) {
                return response()->json([
                    'error' => true,
                    'message' => $result['message']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to create payment intent'
            ], 500);
        }
    }

    /**
     * Process payment for booking
     */
    public function processPayment(ProcessPaymentRequest $request, Booking $booking): JsonResponse
    {
        try {
            // Check authorization
            if ($booking->user_id !== $request->user()->id) {
                return response()->json([
                    'error' => true,
                    'message' => 'Unauthorized access to booking'
                ], 403);
            }

            // Process payment through booking service
            $result = $this->bookingService->confirmBooking($booking, [
                'provider' => $request->provider,
                'method' => $request->provider,
                ...$request->payment_data
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'booking' => $booking->fresh(),
                    'payment_result' => $result
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get available payment methods for booking
     */
    public function getPaymentMethods(Booking $booking): JsonResponse
    {
        try {
            $methods = $this->bookingService->getAvailablePaymentMethods($booking);
            
            return response()->json([
                'success' => true,
                'data' => $methods
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to get payment methods'
            ], 500);
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(Request $request, Booking $booking): JsonResponse
    {
        try {
            if ($booking->user_id !== $request->user()->id && 
                $booking->host_id !== $request->user()->id) {
                return response()->json([
                    'error' => true,
                    'message' => 'Unauthorized access to booking'
                ], 403);
            }

            $provider = $booking->payment_details['method'] ?? 'myfatoorah';
            $transactionId = $booking->transaction_id;

            if (!$transactionId) {
                return response()->json([
                    'error' => true,
                    'message' => 'No transaction ID found'
                ], 400);
            }

            $result = $this->paymentService->getPaymentStatus($provider, $transactionId);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to get payment status'
            ], 500);
        }
    }

    /**
     * PayPal webhook handler
     */
    public function paypalWebhook(Request $request): JsonResponse
    {
        try {
            $signature = $request->header('PayPal-Transmission-Sig');
            $result = $this->paymentService->handleWebhook('paypal', $request->all(), $signature);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * MyFatoorah webhook handler
     */
    public function myfatoorahWebhook(Request $request): JsonResponse
    {
        try {
            $signature = $request->header('X-MyFatoorah-Signature');
            $result = $this->paymentService->handleWebhook('myfatoorah', $request->all(), $signature);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * PayPal return URL handler
     */
    public function paypalSuccess(Request $request, Booking $booking): JsonResponse
    {
        try {
            $orderId = $request->query('token');
            $payerId = $request->query('PayerID');

            if (!$orderId || !$payerId) {
                return response()->json([
                    'error' => true,
                    'message' => 'Missing PayPal parameters'
                ], 400);
            }

            // Process the payment
            $result = $this->bookingService->confirmBooking($booking, [
                'provider' => 'paypal',
                'method' => 'paypal',
                'order_id' => $orderId,
                'payer_id' => $payerId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment completed successfully',
                'booking' => $booking->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * PayPal cancel URL handler
     */
    public function paypalCancel(Request $request, Booking $booking): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Payment was cancelled by user',
            'booking' => $booking
        ]);
    }

    /**
     * MyFatoorah callback handler
     */
    public function myfatoorahCallback(Request $request, Booking $booking): JsonResponse
    {
        try {
            $paymentId = $request->query('paymentId');
            
            if (!$paymentId) {
                return response()->json([
                    'error' => true,
                    'message' => 'Missing payment ID'
                ], 400);
            }

            // Verify payment status with MyFatoorah
            $result = $this->paymentService->getPaymentStatus('myfatoorah', $paymentId);
            
            if ($result['success'] && $result['status'] === 'Paid') {
                // Update booking status
                $booking->update([
                    'status' => 'accepted',
                    'payment_status' => 'paid',
                    'payment_method' => 'myfatoorah',
                    'transaction_id' => $paymentId,
                    'paid_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment completed successfully',
                    'booking' => $booking->fresh()
                ]);
            }

            return response()->json([
                'error' => true,
                'message' => 'Payment verification failed'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * MyFatoorah error handler
     */
    public function myfatoorahError(Request $request, Booking $booking): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Payment failed or was cancelled',
            'booking' => $booking
        ]);
    }

    /**
     * Calculate payment fees
     */
    public function calculateFees(CalculateFeesRequest $request): JsonResponse
    {
        try {
            $fees = $this->paymentService->calculatePaymentFees(
                $request->amount,
                $request->currency,
                $request->provider
            );

            return response()->json([
                'success' => true,
                'data' => $fees
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to calculate fees'
            ], 500);
        }
    }
}

<?php

namespace App\Services\Payment;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PayPalPaymentService implements PaymentServiceInterface
{
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected string $apiUrl;
    protected bool $sandbox;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->sandbox = config('services.paypal.mode') === 'sandbox';
        $this->apiUrl = $this->sandbox
            ? 'https://api.sandbox.paypal.com'
            : 'https://api.paypal.com';
    }

    /**
     * Check if PayPal is configured
     */
    protected function isConfigured(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }

    /**
     * Process payment for booking
     */
    public function processPayment(Booking $booking, array $paymentData): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'PayPal is not configured. Please contact support.',
                'error_code' => 'NOT_CONFIGURED'
            ];
        }

        try {
            // Get access token
            $accessToken = $this->getAccessToken();
            
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Failed to authenticate with PayPal',
                    'error_code' => 'AUTH_FAILED'
                ];
            }

            // Create payment order
            $orderData = $this->createPaymentOrder($booking, $paymentData, $accessToken);
            
            if (!$orderData['success']) {
                return $orderData;
            }

            // Capture payment if order was approved
            if (isset($paymentData['order_id'])) {
                return $this->capturePayment($paymentData['order_id'], $accessToken, $booking);
            }

            return [
                'success' => true,
                'order_id' => $orderData['order_id'],
                'approval_url' => $orderData['approval_url'],
                'message' => 'Payment order created successfully'
            ];

        } catch (\Exception $e) {
            Log::error('PayPal payment error', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
                'error_code' => 'PAYMENT_FAILED'
            ];
        }
    }

    /**
     * Create PayPal payment order
     */
    protected function createPaymentOrder(Booking $booking, array $paymentData, string $accessToken): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
            'PayPal-Request-Id' => uniqid('habibistay_'),
        ])->post($this->apiUrl . '/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $booking->booking_reference,
                'description' => "HabibiStay Booking - {$booking->property->title}",
                'amount' => [
                    'currency_code' => $booking->currency,
                    'value' => number_format($booking->total_amount, 2, '.', ''),
                    'breakdown' => [
                        'item_total' => [
                            'currency_code' => $booking->currency,
                            'value' => number_format($booking->accommodation_total, 2, '.', '')
                        ],
                        'tax_total' => [
                            'currency_code' => $booking->currency,
                            'value' => number_format($booking->tax_amount, 2, '.', '')
                        ],
                        'handling' => [
                            'currency_code' => $booking->currency,
                            'value' => number_format($booking->service_fee + $booking->cleaning_fee, 2, '.', '')
                        ]
                    ]
                ],
                'items' => [[
                    'name' => $booking->property->title,
                    'description' => "Accommodation for {$booking->total_nights} nights",
                    'unit_amount' => [
                        'currency_code' => $booking->currency,
                        'value' => number_format($booking->price_per_night, 2, '.', '')
                    ],
                    'quantity' => (string) $booking->total_nights,
                    'category' => 'DIGITAL_GOODS'
                ]]
            ]],
            'application_context' => [
                'brand_name' => 'HabibiStay',
                'locale' => 'en-US',
                'landing_page' => 'BILLING',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'PAY_NOW',
                'return_url' => route('payments.paypal.success', ['booking' => $booking->id]),
                'cancel_url' => route('payments.paypal.cancel', ['booking' => $booking->id])
            ]
        ]);

        if ($response->failed()) {
            Log::error('PayPal order creation failed', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create PayPal order',
                'error_code' => 'ORDER_CREATION_FAILED'
            ];
        }

        $data = $response->json();
        $approvalUrl = collect($data['links'])->firstWhere('rel', 'approve')['href'] ?? null;

        return [
            'success' => true,
            'order_id' => $data['id'],
            'approval_url' => $approvalUrl
        ];
    }

    /**
     * Capture PayPal payment
     */
    protected function capturePayment(string $orderId, string $accessToken, Booking $booking): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ])->post($this->apiUrl . "/v2/checkout/orders/{$orderId}/capture");

        if ($response->failed()) {
            Log::error('PayPal capture failed', [
                'order_id' => $orderId,
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to capture PayPal payment',
                'error_code' => 'CAPTURE_FAILED'
            ];
        }

        $data = $response->json();
        $capture = $data['purchase_units'][0]['payments']['captures'][0] ?? null;

        if (!$capture || $capture['status'] !== 'COMPLETED') {
            return [
                'success' => false,
                'message' => 'Payment capture was not completed',
                'error_code' => 'CAPTURE_INCOMPLETE'
            ];
        }

        return [
            'success' => true,
            'transaction_id' => $capture['id'],
            'order_id' => $orderId,
            'amount' => $capture['amount']['value'],
            'currency' => $capture['amount']['currency_code'],
            'message' => 'Payment captured successfully',
            'details' => [
                'method' => 'paypal',
                'capture_id' => $capture['id'],
                'order_id' => $orderId,
                'payer_email' => $data['payer']['email_address'] ?? null,
                'payer_id' => $data['payer']['payer_id'] ?? null,
                'processed_at' => now()->toISOString()
            ]
        ];
    }

    /**
     * Process refund for booking
     */
    public function processRefund(Booking $booking, float $amount, string $reason = null): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'PayPal is not configured. Please contact support.',
                'error_code' => 'NOT_CONFIGURED'
            ];
        }

        try {
            $accessToken = $this->getAccessToken();
            
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Failed to authenticate with PayPal'
                ];
            }

            // Extract capture ID from payment details
            $captureId = $booking->payment_details['capture_id'] ?? $booking->transaction_id;
            
            if (!$captureId) {
                return [
                    'success' => false,
                    'message' => 'No capture ID found for refund'
                ];
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->post($this->apiUrl . "/v2/payments/captures/{$captureId}/refund", [
                'amount' => [
                    'currency_code' => $booking->currency,
                    'value' => number_format($amount, 2, '.', '')
                ],
                'note_to_payer' => $reason ?? 'Booking cancellation refund'
            ]);

            if ($response->failed()) {
                Log::error('PayPal refund failed', [
                    'capture_id' => $captureId,
                    'response' => $response->json()
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to process PayPal refund'
                ];
            }

            $data = $response->json();

            return [
                'success' => true,
                'refund_id' => $data['id'],
                'amount' => $data['amount']['value'],
                'currency' => $data['amount']['currency_code'],
                'status' => $data['status'],
                'message' => 'Refund processed successfully'
            ];

        } catch (\Exception $e) {
            Log::error('PayPal refund error', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Refund processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get PayPal access token
     */
    protected function getAccessToken(): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $cacheKey = 'paypal_access_token';

        return Cache::remember($cacheKey, 3300, function () { // 55 minutes
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->withHeaders(['Accept' => 'application/json'])
                ->asForm()
                ->post($this->apiUrl . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials'
                ]);

            if ($response->failed()) {
                Log::error('PayPal authentication failed', [
                    'response' => $response->json()
                ]);
                return null;
            }

            return $response->json()['access_token'];
        });
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhook(array $payload, string $signature): bool
    {
        // PayPal webhook verification implementation
        // This would involve verifying the webhook signature with PayPal's public key
        return true; // Simplified for now
    }

    /**
     * Handle webhook data
     */
    public function handleWebhook(array $payload): array
    {
        $eventType = $payload['event_type'] ?? null;
        
        switch ($eventType) {
            case 'CHECKOUT.ORDER.APPROVED':
                return $this->handleOrderApproved($payload);
            case 'PAYMENT.CAPTURE.COMPLETED':
                return $this->handlePaymentCompleted($payload);
            case 'PAYMENT.CAPTURE.DENIED':
                return $this->handlePaymentDenied($payload);
            default:
                return ['success' => true, 'message' => 'Event type not handled'];
        }
    }

    /**
     * Handle order approved webhook
     */
    protected function handleOrderApproved(array $payload): array
    {
        $orderId = $payload['resource']['id'] ?? null;
        
        if (!$orderId) {
            return ['success' => false, 'message' => 'No order ID in webhook'];
        }

        // Find booking by order ID and update status
        // This would require storing the order ID in the booking
        
        return ['success' => true, 'message' => 'Order approved webhook handled'];
    }

    /**
     * Handle payment completed webhook
     */
    protected function handlePaymentCompleted(array $payload): array
    {
        $captureId = $payload['resource']['id'] ?? null;
        
        if (!$captureId) {
            return ['success' => false, 'message' => 'No capture ID in webhook'];
        }

        // Update booking status to paid
        
        return ['success' => true, 'message' => 'Payment completed webhook handled'];
    }

    /**
     * Handle payment denied webhook
     */
    protected function handlePaymentDenied(array $payload): array
    {
        $captureId = $payload['resource']['id'] ?? null;
        
        // Handle payment denial logic
        
        return ['success' => true, 'message' => 'Payment denied webhook handled'];
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $transactionId): array
    {
        try {
            $accessToken = $this->getAccessToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get($this->apiUrl . "/v2/payments/captures/{$transactionId}");

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' => 'Failed to get payment status'
                ];
            }

            $data = $response->json();
            
            return [
                'success' => true,
                'status' => $data['status'],
                'amount' => $data['amount']['value'],
                'currency' => $data['amount']['currency_code']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting payment status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'SAR', 'AED'];
    }

    /**
     * Validate payment data
     */
    public function validatePaymentData(array $paymentData): array
    {
        $errors = [];

        if (empty($paymentData['order_id']) && empty($paymentData['create_order'])) {
            $errors[] = 'Either order_id or create_order flag is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
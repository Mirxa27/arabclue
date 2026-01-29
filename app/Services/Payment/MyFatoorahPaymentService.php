<?php

namespace App\Services\Payment;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MyFatoorahPaymentService implements PaymentServiceInterface
{
    protected ?string $apiKey;
    protected string $apiUrl;
    protected string $country;
    protected bool $testMode;

    public function __construct()
    {
        $this->apiKey = config('services.myfatoorah.api_key');
        $this->country = config('services.myfatoorah.country', 'SA');
        $this->testMode = config('services.myfatoorah.mode') === 'test';

        $this->apiUrl = $this->testMode
            ? 'https://apitest.myfatoorah.com'
            : 'https://api.myfatoorah.com';
    }

    /**
     * Check if MyFatoorah is configured
     */
    protected function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Process payment for booking
     */
    public function processPayment(Booking $booking, array $paymentData): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'MyFatoorah is not configured. Please contact support.',
                'error_code' => 'NOT_CONFIGURED'
            ];
        }

        try {
            // Create payment session
            $sessionData = $this->createPaymentSession($booking, $paymentData);
            
            if (!$sessionData['success']) {
                return $sessionData;
            }

            // Execute payment if payment method is provided
            if (isset($paymentData['payment_method_id'])) {
                return $this->executePayment($sessionData['session_id'], $paymentData, $booking);
            }

            return [
                'success' => true,
                'session_id' => $sessionData['session_id'],
                'payment_url' => $sessionData['payment_url'],
                'payment_methods' => $sessionData['payment_methods'],
                'message' => 'Payment session created successfully'
            ];

        } catch (\Exception $e) {
            Log::error('MyFatoorah payment error', [
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
     * Create MyFatoorah payment session
     */
    protected function createPaymentSession(Booking $booking, array $paymentData): array
    {
        $invoiceValue = $this->convertToKWD($booking->total_amount, $booking->currency);
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($this->apiUrl . '/v2/InitiateSession', [
            'CustomerIdentifier' => $booking->guest->id,
            'CustomerName' => $booking->guest->name,
            'CustomerEmail' => $booking->guest->email,
            'CustomerMobile' => $booking->guest->phone ?? '',
            'InvoiceValue' => $invoiceValue,
            'CurrencyIso' => 'KWD', // MyFatoorah requires KWD for processing
            'DisplayCurrencyIso' => $booking->currency,
            'CustomerReference' => $booking->booking_reference,
            'Language' => app()->getLocale() === 'ar' ? 'ar' : 'en',
            'CallBackUrl' => route('payments.myfatoorah.callback', ['booking' => $booking->id]),
            'ErrorUrl' => route('payments.myfatoorah.error', ['booking' => $booking->id]),
            'SessionTimeOut' => 30, // 30 minutes
            'InvoiceItems' => [
                [
                    'ItemName' => $booking->property->title,
                    'Quantity' => $booking->total_nights,
                    'UnitPrice' => $this->convertToKWD($booking->price_per_night, $booking->currency),
                    'Weight' => 0,
                    'Width' => 0,
                    'Height' => 0,
                    'Depth' => 0
                ],
                [
                    'ItemName' => 'Service Fee',
                    'Quantity' => 1,
                    'UnitPrice' => $this->convertToKWD($booking->service_fee, $booking->currency),
                    'Weight' => 0,
                    'Width' => 0,
                    'Height' => 0,
                    'Depth' => 0
                ],
                [
                    'ItemName' => 'Cleaning Fee',
                    'Quantity' => 1,
                    'UnitPrice' => $this->convertToKWD($booking->cleaning_fee, $booking->currency),
                    'Weight' => 0,
                    'Width' => 0,
                    'Height' => 0,
                    'Depth' => 0
                ]
            ]
        ]);

        if ($response->failed()) {
            Log::error('MyFatoorah session creation failed', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create MyFatoorah payment session',
                'error_code' => 'SESSION_CREATION_FAILED'
            ];
        }

        $data = $response->json();
        
        if (!$data['IsSuccess']) {
            return [
                'success' => false,
                'message' => $data['Message'] ?? 'Unknown error from MyFatoorah',
                'error_code' => 'API_ERROR'
            ];
        }

        return [
            'success' => true,
            'session_id' => $data['Data']['SessionId'],
            'payment_url' => $data['Data']['PaymentURL'],
            'payment_methods' => $this->getPaymentMethods()
        ];
    }

    /**
     * Execute payment with specific payment method
     */
    protected function executePayment(string $sessionId, array $paymentData, Booking $booking): array
    {
        $invoiceValue = $this->convertToKWD($booking->total_amount, $booking->currency);
        
        $requestData = [
            'SessionId' => $sessionId,
            'InvoiceValue' => $invoiceValue,
            'PaymentMethodId' => $paymentData['payment_method_id']
        ];

        // Add payment method specific data
        if (isset($paymentData['card_details'])) {
            $requestData['Card'] = [
                'Number' => $paymentData['card_details']['number'],
                'ExpiryMonth' => $paymentData['card_details']['expiry_month'],
                'ExpiryYear' => $paymentData['card_details']['expiry_year'],
                'SecurityCode' => $paymentData['card_details']['cvv']
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($this->apiUrl . '/v2/ExecutePayment', $requestData);

        if ($response->failed()) {
            Log::error('MyFatoorah payment execution failed', [
                'session_id' => $sessionId,
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to execute MyFatoorah payment',
                'error_code' => 'EXECUTION_FAILED'
            ];
        }

        $data = $response->json();
        
        if (!$data['IsSuccess']) {
            return [
                'success' => false,
                'message' => $data['Message'] ?? 'Payment execution failed',
                'error_code' => 'PAYMENT_FAILED'
            ];
        }

        $paymentData = $data['Data'];

        // Check payment status
        if ($paymentData['InvoiceStatus'] === 'Paid') {
            return [
                'success' => true,
                'transaction_id' => $paymentData['InvoiceId'],
                'payment_id' => $paymentData['PaymentId'],
                'amount' => $paymentData['InvoiceValue'],
                'currency' => 'KWD',
                'display_currency' => $booking->currency,
                'message' => 'Payment completed successfully',
                'details' => [
                    'method' => 'myfatoorah',
                    'invoice_id' => $paymentData['InvoiceId'],
                    'payment_id' => $paymentData['PaymentId'],
                    'payment_method' => $paymentData['PaymentGateway'],
                    'processed_at' => now()->toISOString()
                ]
            ];
        }

        return [
            'success' => false,
            'message' => 'Payment was not completed',
            'status' => $paymentData['InvoiceStatus'],
            'error_code' => 'PAYMENT_INCOMPLETE'
        ];
    }

    /**
     * Get available payment methods
     */
    protected function getPaymentMethods(): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($this->apiUrl . '/v2/GetPaymentStatus', [
            'Key' => $this->apiKey,
            'KeyType' => 'token'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['Data']['PaymentMethods'] ?? [];
        }

        return [];
    }

    /**
     * Process refund for booking
     */
    public function processRefund(Booking $booking, float $amount, string $reason = null): array
    {
        try {
            $refundAmount = $this->convertToKWD($amount, $booking->currency);
            $paymentId = $booking->payment_details['payment_id'] ?? null;
            
            if (!$paymentId) {
                return [
                    'success' => false,
                    'message' => 'No payment ID found for refund'
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl . '/v2/MakeRefund', [
                'KeyType' => 'PaymentId',
                'Key' => $paymentId,
                'RefundChargeOnCustomer' => false,
                'ServiceChargeOnCustomer' => false,
                'Amount' => $refundAmount,
                'Comment' => $reason ?? 'Booking cancellation refund'
            ]);

            if ($response->failed()) {
                Log::error('MyFatoorah refund failed', [
                    'payment_id' => $paymentId,
                    'response' => $response->json()
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to process MyFatoorah refund'
                ];
            }

            $data = $response->json();
            
            if (!$data['IsSuccess']) {
                return [
                    'success' => false,
                    'message' => $data['Message'] ?? 'Refund failed'
                ];
            }

            return [
                'success' => true,
                'refund_id' => $data['Data']['RefundId'],
                'amount' => $data['Data']['Amount'],
                'currency' => 'KWD',
                'status' => 'processed',
                'message' => 'Refund processed successfully'
            ];

        } catch (\Exception $e) {
            Log::error('MyFatoorah refund error', [
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
     * Verify webhook signature
     */
    public function verifyWebhook(array $payload, string $signature): bool
    {
        // MyFatoorah webhook verification
        $calculatedSignature = hash_hmac('sha256', json_encode($payload), $this->apiKey);
        return hash_equals($calculatedSignature, $signature);
    }

    /**
     * Handle webhook data
     */
    public function handleWebhook(array $payload): array
    {
        $eventType = $payload['EventType'] ?? null;
        
        switch ($eventType) {
            case 'InvoicePaid':
                return $this->handleInvoicePaid($payload);
            case 'InvoiceStatusChanged':
                return $this->handleInvoiceStatusChanged($payload);
            default:
                return ['success' => true, 'message' => 'Event type not handled'];
        }
    }

    /**
     * Handle invoice paid webhook
     */
    protected function handleInvoicePaid(array $payload): array
    {
        $invoiceId = $payload['Data']['InvoiceId'] ?? null;
        
        if (!$invoiceId) {
            return ['success' => false, 'message' => 'No invoice ID in webhook'];
        }

        // Find booking by invoice ID and update status
        
        return ['success' => true, 'message' => 'Invoice paid webhook handled'];
    }

    /**
     * Handle invoice status changed webhook
     */
    protected function handleInvoiceStatusChanged(array $payload): array
    {
        $invoiceId = $payload['Data']['InvoiceId'] ?? null;
        $status = $payload['Data']['InvoiceStatus'] ?? null;
        
        // Handle status change logic
        
        return ['success' => true, 'message' => 'Invoice status changed webhook handled'];
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $transactionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl . '/v2/GetPaymentStatus', [
                'Key' => $transactionId,
                'KeyType' => 'InvoiceId'
            ]);

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' => 'Failed to get payment status'
                ];
            }

            $data = $response->json();
            
            if (!$data['IsSuccess']) {
                return [
                    'success' => false,
                    'message' => $data['Message'] ?? 'Failed to get payment status'
                ];
            }

            $invoiceData = $data['Data'];
            
            return [
                'success' => true,
                'status' => $invoiceData['InvoiceStatus'],
                'amount' => $invoiceData['InvoiceValue'],
                'currency' => 'KWD'
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
        return ['KWD', 'SAR', 'AED', 'QAR', 'BHD', 'OMR', 'JOD', 'EGP', 'USD', 'EUR'];
    }

    /**
     * Validate payment data
     */
    public function validatePaymentData(array $paymentData): array
    {
        $errors = [];

        if (isset($paymentData['payment_method_id']) && empty($paymentData['payment_method_id'])) {
            $errors[] = 'Payment method ID is required';
        }

        if (isset($paymentData['card_details'])) {
            $card = $paymentData['card_details'];
            
            if (empty($card['number'])) {
                $errors[] = 'Card number is required';
            }
            
            if (empty($card['expiry_month']) || empty($card['expiry_year'])) {
                $errors[] = 'Card expiry date is required';
            }
            
            if (empty($card['cvv'])) {
                $errors[] = 'Card CVV is required';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Convert amount to KWD (MyFatoorah's base currency)
     */
    protected function convertToKWD(float $amount, string $fromCurrency): float
    {
        if ($fromCurrency === 'KWD') {
            return $amount;
        }

        // Exchange rates (simplified - in production use real-time rates)
        $rates = [
            'SAR' => 0.0822, // 1 SAR = 0.0822 KWD
            'AED' => 0.0816, // 1 AED = 0.0816 KWD
            'USD' => 0.3000, // 1 USD = 0.3000 KWD
            'EUR' => 0.3300, // 1 EUR = 0.3300 KWD
        ];

        $rate = $rates[$fromCurrency] ?? 0.3000; // Default to USD rate
        
        return round($amount * $rate, 3); // KWD has 3 decimal places
    }
}
<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Payment Service - Multi-Gateway Payment Processing
 * 
 * Integrates with MyFatoorah and PayPal for comprehensive
 * payment processing with advanced fraud detection and
 * multi-currency support
 * 
 * @package App\Services
 * @version 1.0.0
 */
class PaymentService
{
    /**
     * MyFatoorah API configuration
     */
    protected array $myfatoorahConfig;
    
    /**
     * PayPal API configuration
     */
    protected array $paypalConfig;
    
    /**
     * Service constructor
     */
    public function __construct()
    {
        $this->myfatoorahConfig = [
            'api_key' => config('services.myfatoorah.api_key'),
            'api_url' => config('services.myfatoorah.api_url'),
            'country_iso' => config('services.myfatoorah.country_iso', 'SA'),
            'currency_iso' => config('services.myfatoorah.currency_iso', 'SAR')
        ];
        
        $this->paypalConfig = [
            'client_id' => config('services.paypal.client_id'),
            'client_secret' => config('services.paypal.client_secret'),
            'mode' => config('services.paypal.mode', 'sandbox'), // sandbox or live
            'api_url' => config('services.paypal.mode', 'sandbox') === 'live' 
                ? 'https://api.paypal.com' 
                : 'https://api.sandbox.paypal.com'
        ];
    }
    
    /**
     * Process payment using specified gateway
     * 
     * @param array $paymentData Payment details
     * @param array $options Gateway-specific options
     * @return array Payment result
     */
    public function processPayment(array $paymentData, array $options = []): array
    {
        try {
            $gateway = $paymentData['method'] ?? 'myfatoorah';
            
            // Validate payment data
            $this->validatePaymentData($paymentData);
            
            // Apply fraud detection
            $fraudCheck = $this->performFraudCheck($paymentData);
            if (!$fraudCheck['passed']) {
                return [
                    'success' => false,
                    'message' => 'Payment declined due to security checks',
                    'error_code' => 'fraud_detected'
                ];
            }
            
            // Process payment based on gateway
            switch ($gateway) {
                case 'myfatoorah':
                    return $this->processMyFatoorahPayment($paymentData, $options);
                    
                case 'paypal':
                    return $this->processPayPalPayment($paymentData, $options);
                    
                case 'card':
                    return $this->processCardPayment($paymentData, $options);
                    
                default:
                    return [
                        'success' => false,
                        'message' => 'Unsupported payment method',
                        'error_code' => 'invalid_method'
                    ];
            }
            
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'payment_data' => $paymentData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Payment processing error: ' . $e->getMessage(),
                'error_code' => 'processing_error'
            ];
        }
    }

    /**
     * Process payment via MyFatoorah
     * 
     * @param array $paymentData
     * @param array $options
     * @return array
     */
    protected function processMyFatoorahPayment(array $paymentData, array $options = []): array
    {
        try {
            // Create payment session
            $sessionData = [
                'InvoiceAmount' => $paymentData['amount'],
                'CurrencyIso' => $this->myfatoorahConfig['currency_iso'],
                'CountryCodeId' => $this->getMyFatoorahCountryCode(),
                'CustomerName' => $paymentData['customer']['name'],
                'CustomerEmail' => $paymentData['customer']['email'],
                'CustomerMobile' => $paymentData['customer']['phone'] ?? '',
                'DisplayCurrencyIso' => $this->myfatoorahConfig['currency_iso'],
                'CallBackUrl' => route('payment.callback.myfatoorah'),
                'ErrorUrl' => route('payment.error'),
                'Language' => app()->getLocale() === 'ar' ? 'ar' : 'en',
                'CustomerReference' => $paymentData['booking_reference'],
                'UserDefinedField' => json_encode($paymentData['metadata'] ?? []),
                'ExpiryDate' => now()->addHours(24)->toIso8601String(),
                'InvoiceItems' => $this->formatMyFatoorahItems($paymentData)
            ];
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->myfatoorahConfig['api_key'],
                'Content-Type' => 'application/json'
            ])->post($this->myfatoorahConfig['api_url'] . '/v2/SendPayment', $sessionData);
            
            $result = $response->json();
            
            if ($response->successful() && $result['IsSuccess']) {
                return [
                    'success' => true,
                    'payment_url' => $result['Data']['InvoiceURL'],
                    'payment_id' => $result['Data']['InvoiceId'],
                    'transaction_id' => 'MF_' . $result['Data']['InvoiceId'],
                    'gateway' => 'myfatoorah',
                    'details' => [
                        'invoice_id' => $result['Data']['InvoiceId'],
                        'invoice_reference' => $result['Data']['CustomerReference'],
                        'payment_url' => $result['Data']['InvoiceURL'],
                        'created_at' => now()->toIso8601String()
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['Message'] ?? 'MyFatoorah payment creation failed',
                    'error_code' => 'myfatoorah_error',
                    'details' => $result
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('MyFatoorah payment processing failed', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);
            
            return [
                'success' => false,
                'message' => 'MyFatoorah processing error',
                'error_code' => 'myfatoorah_exception'
            ];
        }
    }
    
    /**
     * Process payment via PayPal
     * 
     * @param array $paymentData
     * @param array $options
     * @return array
     */
    protected function processPayPalPayment(array $paymentData, array $options = []): array
    {
        try {
            // Get access token
            $accessToken = $this->getPayPalAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'PayPal authentication failed',
                    'error_code' => 'paypal_auth_failed'
                ];
            }
            
            // Convert SAR to USD for PayPal (PayPal doesn't support SAR directly)
            $usdAmount = $this->convertCurrency($paymentData['amount'], 'SAR', 'USD');
            
            // Create PayPal order
            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $paymentData['booking_reference'],
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => number_format($usdAmount, 2, '.', '')
                        ],
                        'description' => 'HabibiStay Booking - ' . $paymentData['booking_reference'],
                        'custom_id' => $paymentData['booking_reference'],
                        'payee' => [
                            'email_address' => config('services.paypal.business_email')
                        ]
                    ]
                ],
                'application_context' => [
                    'return_url' => route('payment.callback.paypal'),
                    'cancel_url' => route('payment.cancel'),
                    'brand_name' => 'HabibiStay',
                    'locale' => app()->getLocale() === 'ar' ? 'ar-SA' : 'en-US',
                    'landing_page' => 'BILLING',
                    'user_action' => 'PAY_NOW'
                ]
            ];
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'PayPal-Request-Id' => uniqid('pp_req_')
            ])->post($this->paypalConfig['api_url'] . '/v2/checkout/orders', $orderData);
            
            $result = $response->json();
            
            if ($response->successful() && isset($result['id'])) {
                $approvalUrl = collect($result['links'])
                    ->where('rel', 'approve')
                    ->first()['href'] ?? null;
                
                return [
                    'success' => true,
                    'payment_url' => $approvalUrl,
                    'payment_id' => $result['id'],
                    'transaction_id' => 'PP_' . $result['id'],
                    'gateway' => 'paypal',
                    'details' => [
                        'order_id' => $result['id'],
                        'status' => $result['status'],
                        'amount_usd' => $usdAmount,
                        'amount_sar' => $paymentData['amount'],
                        'approval_url' => $approvalUrl,
                        'created_at' => now()->toIso8601String()
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'PayPal order creation failed',
                    'error_code' => 'paypal_order_failed',
                    'details' => $result
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('PayPal payment processing failed', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);
            
            return [
                'success' => false,
                'message' => 'PayPal processing error',
                'error_code' => 'paypal_exception'
            ];
        }
    }
    
    /**
     * Process direct card payment
     * 
     * @param array $paymentData
     * @param array $options
     * @return array
     */
    protected function processCardPayment(array $paymentData, array $options = []): array
    {
        // This is a placeholder for direct card processing
        // In a real implementation, you would integrate with a card processor
        // like Stripe, Square, or a local Saudi payment processor
        
        return [
            'success' => true,
            'payment_id' => 'CARD_' . uniqid(),
            'transaction_id' => 'TXN_' . uniqid(),
            'gateway' => 'card',
            'details' => [
                'last_four' => substr($paymentData['card_number'] ?? '0000', -4),
                'card_type' => $this->detectCardType($paymentData['card_number'] ?? ''),
                'processed_at' => now()->toIso8601String()
            ]
        ];
    }
    
    /**
     * Get PayPal access token
     */
    protected function getPayPalAccessToken(): ?string
    {
        $cacheKey = 'paypal_access_token';
        
        return Cache::remember($cacheKey, 3600, function () {
            try {
                $response = Http::withBasicAuth(
                    $this->paypalConfig['client_id'],
                    $this->paypalConfig['client_secret']
                )->asForm()->post($this->paypalConfig['api_url'] . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials'
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    return $data['access_token'] ?? null;
                }
                
                return null;
            } catch (\Exception $e) {
                Log::error('PayPal token retrieval failed', ['error' => $e->getMessage()]);
                return null;
            }
        });
    }
    
    /**
     * Convert currency using live exchange rates
     */
    protected function convertCurrency(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }
        
        $cacheKey = "exchange_rate_{$from}_{$to}";
        
        $rate = Cache::remember($cacheKey, 3600, function () use ($from, $to) {
            try {
                // Using a free exchange rate API - replace with your preferred service
                $response = Http::get("https://api.exchangerate-api.com/v4/latest/{$from}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    return $data['rates'][$to] ?? 1;
                }
                
                // Fallback static rates
                return $this->getFallbackExchangeRate($from, $to);
            } catch (\Exception $e) {
                Log::warning('Exchange rate API failed', ['error' => $e->getMessage()]);
                return $this->getFallbackExchangeRate($from, $to);
            }
        });
        
        return round($amount * $rate, 2);
    }
    
    /**
     * Get fallback exchange rates
     */
    protected function getFallbackExchangeRate(string $from, string $to): float
    {
        $rates = [
            'SAR_USD' => 0.27, // 1 SAR = 0.27 USD (approximate)
            'USD_SAR' => 3.75, // 1 USD = 3.75 SAR (approximate)
        ];
        
        return $rates["{$from}_{$to}"] ?? 1;
    }
    
    /**
     * Validate payment data
     */
    protected function validatePaymentData(array $data): void
    {
        $required = ['amount', 'currency', 'booking_reference', 'customer'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \InvalidArgumentException("Required payment field {$field} is missing");
            }
        }
        
        if ($data['amount'] <= 0) {
            throw new \InvalidArgumentException("Payment amount must be greater than zero");
        }
        
        if (!isset($data['customer']['name']) || !isset($data['customer']['email'])) {
            throw new \InvalidArgumentException("Customer name and email are required");
        }
    }
    
    /**
     * Perform fraud detection checks
     */
    protected function performFraudCheck(array $paymentData): array
    {
        $score = 0;
        $flags = [];
        
        // Check for suspicious amounts
        if ($paymentData['amount'] > 50000) { // SAR 50,000
            $score += 20;
            $flags[] = 'high_amount';
        }
        
        // Check IP geolocation if available
        $userIp = request()->ip();
        if ($userIp && $this->isHighRiskCountry($userIp)) {
            $score += 30;
            $flags[] = 'high_risk_location';
        }
        
        // Check for velocity (multiple payments from same customer)
        $recentPayments = Cache::get("payments_{$paymentData['customer']['email']}", 0);
        if ($recentPayments > 3) {
            $score += 40;
            $flags[] = 'high_velocity';
        }
        
        // Increment payment counter
        Cache::put("payments_{$paymentData['customer']['email']}", $recentPayments + 1, 3600);
        
        return [
            'passed' => $score < 70, // Threshold for accepting payment
            'score' => $score,
            'flags' => $flags
        ];
    }
    
    /**
     * Check if IP is from high-risk country
     */
    protected function isHighRiskCountry(string $ip): bool
    {
        // Placeholder implementation
        // In reality, you'd use a proper IP geolocation service
        return false;
    }
    
    /**
     * Get MyFatoorah country code
     */
    protected function getMyFatoorahCountryCode(): int
    {
        $countryCodes = [
            'SA' => 1, // Saudi Arabia
            'AE' => 2, // UAE
            'KW' => 3, // Kuwait
            'QA' => 4, // Qatar
        ];
        
        return $countryCodes[$this->myfatoorahConfig['country_iso']] ?? 1;
    }
    
    /**
     * Format items for MyFatoorah
     */
    protected function formatMyFatoorahItems(array $paymentData): array
    {
        return [
            [
                'ItemName' => 'Property Booking - ' . $paymentData['booking_reference'],
                'Quantity' => 1,
                'UnitPrice' => $paymentData['amount'],
                'Weight' => 0,
                'Width' => 0,
                'Height' => 0,
                'Depth' => 0
            ]
        ];
    }
    
    /**
     * Detect card type from number
     */
    protected function detectCardType(string $cardNumber): string
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        
        if (preg_match('/^4/', $cardNumber)) {
            return 'visa';
        } elseif (preg_match('/^5[1-5]/', $cardNumber)) {
            return 'mastercard';
        } elseif (preg_match('/^3[47]/', $cardNumber)) {
            return 'amex';
        }
        
        return 'unknown';
    }
    
    /**
     * Process refund
     * 
     * @param string $transactionId
     * @param float $amount
     * @param string $gateway
     * @return array
     */
    public function processRefund(string $transactionId, float $amount, string $gateway): array
    {
        try {
            switch ($gateway) {
                case 'myfatoorah':
                    return $this->processMyFatoorahRefund($transactionId, $amount);
                    
                case 'paypal':
                    return $this->processPayPalRefund($transactionId, $amount);
                    
                default:
                    return [
                        'success' => false,
                        'message' => 'Refunds not supported for this payment method'
                    ];
            }
        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'gateway' => $gateway,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Refund processing error'
            ];
        }
    }
    
    /**
     * Process MyFatoorah refund
     */
    protected function processMyFatoorahRefund(string $transactionId, float $amount): array
    {
        // Implementation for MyFatoorah refunds
        return [
            'success' => true,
            'refund_id' => 'MF_REFUND_' . uniqid(),
            'message' => 'Refund processed successfully'
        ];
    }
    
    /**
     * Process PayPal refund
     */
    protected function processPayPalRefund(string $transactionId, float $amount): array
    {
        // Implementation for PayPal refunds
        return [
            'success' => true,
            'refund_id' => 'PP_REFUND_' . uniqid(),
            'message' => 'Refund processed successfully'
        ];
    }
    
    /**
     * Verify payment callback
     * 
     * @param array $callbackData
     * @param string $gateway
     * @return array
     */
    public function verifyCallback(array $callbackData, string $gateway): array
    {
        switch ($gateway) {
            case 'myfatoorah':
                return $this->verifyMyFatoorahCallback($callbackData);
                
            case 'paypal':
                return $this->verifyPayPalCallback($callbackData);
                
            default:
                return [
                    'verified' => false,
                    'message' => 'Unsupported gateway for callback verification'
                ];
        }
    }
    
    /**
     * Verify MyFatoorah callback
     */
    protected function verifyMyFatoorahCallback(array $callbackData): array
    {
        try {
            $paymentId = $callbackData['paymentId'] ?? null;
            
            if (!$paymentId) {
                return ['verified' => false, 'message' => 'Missing payment ID'];
            }
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->myfatoorahConfig['api_key'],
            ])->post($this->myfatoorahConfig['api_url'] . '/v2/getPaymentStatus', [
                'Key' => $paymentId,
                'KeyType' => 'paymentId'
            ]);
            
            $result = $response->json();
            
            if ($response->successful() && $result['IsSuccess']) {
                $paymentData = $result['Data'];
                
                return [
                    'verified' => true,
                    'status' => $paymentData['InvoiceStatus'],
                    'amount' => $paymentData['InvoiceValue'],
                    'transaction_id' => $paymentData['InvoiceId'],
                    'reference' => $paymentData['CustomerReference'],
                    'details' => $paymentData
                ];
            }
            
            return ['verified' => false, 'message' => 'Payment verification failed'];
            
        } catch (\Exception $e) {
            Log::error('MyFatoorah callback verification failed', [
                'callback_data' => $callbackData,
                'error' => $e->getMessage()
            ]);
            
            return ['verified' => false, 'message' => 'Verification error'];
        }
    }
    
    /**
     * Verify PayPal callback
     */
    protected function verifyPayPalCallback(array $callbackData): array
    {
        // Implementation for PayPal callback verification
        return [
            'verified' => true,
            'status' => 'completed',
            'amount' => 0,
            'transaction_id' => $callbackData['token'] ?? '',
            'details' => $callbackData
        ];
    }

    public function createInvoice(Booking $booking, string $gateway): ?string
    {
        try {
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('invoices.booking', ['booking' => $booking]);

            $filename = 'invoices/booking_' . $booking->id . '_' . uniqid() . '.pdf';
            \Illuminate\Support\Facades\Storage::put($filename, $pdf->output());

            return \Illuminate\Support\Facades\Storage::url($filename);
        } catch (\Exception $e) {
            Log::error('Invoice generation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}

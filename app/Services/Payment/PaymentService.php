<?php

namespace App\Services\Payment;

use App\Models\Booking;
use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected array $providers;

    public function __construct()
    {
        $this->providers = [
            'paypal' => app(PayPalPaymentService::class),
            'myfatoorah' => app(MyFatoorahPaymentService::class),
        ];
    }

    /**
     * Process payment using specified provider
     */
    public function processPayment(
        Booking $booking, 
        string $provider, 
        array $paymentData
    ): array {
        try {
            $service = $this->getProvider($provider);
            
            // Validate payment data
            $validation = $service->validatePaymentData($paymentData);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Invalid payment data',
                    'errors' => $validation['errors']
                ];
            }

            // Process payment
            $result = $service->processPayment($booking, $paymentData);
            
            // Log payment attempt
            Log::info('Payment attempt', [
                'booking_id' => $booking->id,
                'provider' => $provider,
                'success' => $result['success'],
                'transaction_id' => $result['transaction_id'] ?? null
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Payment processing error', [
                'booking_id' => $booking->id,
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new PaymentException(
                'Payment processing failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Process refund using specified provider
     */
    public function processRefund(
        Booking $booking, 
        string $provider, 
        float $amount, 
        string $reason = null
    ): array {
        try {
            $service = $this->getProvider($provider);
            
            $result = $service->processRefund($booking, $amount, $reason);
            
            // Log refund attempt
            Log::info('Refund attempt', [
                'booking_id' => $booking->id,
                'provider' => $provider,
                'amount' => $amount,
                'success' => $result['success'],
                'refund_id' => $result['refund_id'] ?? null
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Refund processing error', [
                'booking_id' => $booking->id,
                'provider' => $provider,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            throw new PaymentException(
                'Refund processing failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Handle webhook from payment provider
     */
    public function handleWebhook(string $provider, array $payload, string $signature = null): array
    {
        try {
            $service = $this->getProvider($provider);
            
            // Verify webhook signature if provided
            if ($signature && !$service->verifyWebhook($payload, $signature)) {
                return [
                    'success' => false,
                    'message' => 'Invalid webhook signature'
                ];
            }

            $result = $service->handleWebhook($payload);
            
            Log::info('Webhook processed', [
                'provider' => $provider,
                'event_type' => $payload['event_type'] ?? $payload['EventType'] ?? 'unknown',
                'success' => $result['success']
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'message' => 'Webhook processing failed'
            ];
        }
    }

    /**
     * Get payment status from provider
     */
    public function getPaymentStatus(string $provider, string $transactionId): array
    {
        try {
            $service = $this->getProvider($provider);
            return $service->getPaymentStatus($transactionId);

        } catch (\Exception $e) {
            Log::error('Payment status check error', [
                'provider' => $provider,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to check payment status'
            ];
        }
    }

    /**
     * Get supported currencies for provider
     */
    public function getSupportedCurrencies(string $provider): array
    {
        try {
            $service = $this->getProvider($provider);
            return $service->getSupportedCurrencies();

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get all available payment providers
     */
    public function getAvailableProviders(): array
    {
        return [
            'paypal' => [
                'name' => 'PayPal',
                'currencies' => $this->getSupportedCurrencies('paypal'),
                'methods' => ['paypal_account', 'credit_card'],
                'logo' => asset('images/payment/paypal-logo.png')
            ],
            'myfatoorah' => [
                'name' => 'MyFatoorah',
                'currencies' => $this->getSupportedCurrencies('myfatoorah'),
                'methods' => ['credit_card', 'debit_card', 'knet', 'apple_pay', 'google_pay'],
                'logo' => asset('images/payment/myfatoorah-logo.png')
            ]
        ];
    }

    /**
     * Determine best payment provider for booking
     */
    public function getBestProviderForBooking(Booking $booking): string
    {
        $currency = $booking->currency;
        $amount = $booking->total_amount;
        $country = $booking->guest->preferences['country'] ?? 'SA';

        // Prefer MyFatoorah for Middle East currencies and countries
        if (in_array($currency, ['SAR', 'AED', 'QAR', 'KWD', 'BHD', 'OMR']) || 
            in_array($country, ['SA', 'AE', 'QA', 'KW', 'BH', 'OM'])) {
            return 'myfatoorah';
        }

        // Prefer PayPal for international currencies
        if (in_array($currency, ['USD', 'EUR', 'GBP', 'CAD', 'AUD'])) {
            return 'paypal';
        }

        // Default to MyFatoorah for Saudi market
        return 'myfatoorah';
    }

    /**
     * Create payment intent for booking
     */
    public function createPaymentIntent(Booking $booking, array $options = []): array
    {
        $provider = $options['provider'] ?? $this->getBestProviderForBooking($booking);
        
        try {
            $service = $this->getProvider($provider);
            
            // Prepare payment data for intent creation
            $paymentData = [
                'create_order' => true,
                'return_url' => route('bookings.payment.success', $booking),
                'cancel_url' => route('bookings.payment.cancel', $booking),
            ];

            $result = $service->processPayment($booking, $paymentData);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'provider' => $provider,
                    'payment_intent' => $result,
                    'booking_id' => $booking->id
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Failed to create payment intent'
            ];

        } catch (\Exception $e) {
            Log::error('Payment intent creation error', [
                'booking_id' => $booking->id,
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create payment intent'
            ];
        }
    }

    /**
     * Calculate payment fees for different providers
     */
    public function calculatePaymentFees(float $amount, string $currency, string $provider): array
    {
        switch ($provider) {
            case 'paypal':
                // PayPal fees: 2.9% + fixed fee
                $percentage = 2.9;
                $fixedFee = match($currency) {
                    'SAR' => 1.20,
                    'USD' => 0.30,
                    'EUR' => 0.35,
                    default => 0.30
                };
                break;

            case 'myfatoorah':
                // MyFatoorah fees: 2.5% for local cards, 3.5% for international
                $percentage = 2.5; // Simplified
                $fixedFee = 0;
                break;

            default:
                $percentage = 3.0;
                $fixedFee = 0;
        }

        $percentageFee = ($amount * $percentage) / 100;
        $totalFee = $percentageFee + $fixedFee;

        return [
            'percentage' => $percentage,
            'percentage_fee' => round($percentageFee, 2),
            'fixed_fee' => $fixedFee,
            'total_fee' => round($totalFee, 2),
            'amount_with_fees' => round($amount + $totalFee, 2)
        ];
    }

    /**
     * Get payment provider instance
     */
    protected function getProvider(string $provider): PaymentServiceInterface
    {
        if (!isset($this->providers[$provider])) {
            throw new PaymentException("Unsupported payment provider: {$provider}");
        }

        return $this->providers[$provider];
    }

    /**
     * Validate provider configuration
     */
    public function validateProviderConfig(string $provider): array
    {
        $errors = [];

        switch ($provider) {
            case 'paypal':
                if (!config('services.paypal.client_id')) {
                    $errors[] = 'PayPal client ID not configured';
                }
                if (!config('services.paypal.client_secret')) {
                    $errors[] = 'PayPal client secret not configured';
                }
                break;

            case 'myfatoorah':
                if (!config('services.myfatoorah.api_key')) {
                    $errors[] = 'MyFatoorah API key not configured';
                }
                break;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
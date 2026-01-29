<?php

namespace App\Services\Payment;

use App\Models\Booking;

interface PaymentServiceInterface
{
    /**
     * Process payment for booking
     */
    public function processPayment(Booking $booking, array $paymentData): array;

    /**
     * Process refund for booking
     */
    public function processRefund(Booking $booking, float $amount, string $reason = null): array;

    /**
     * Verify webhook signature
     */
    public function verifyWebhook(array $payload, string $signature): bool;

    /**
     * Handle webhook data
     */
    public function handleWebhook(array $payload): array;

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $transactionId): array;

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array;

    /**
     * Validate payment data
     */
    public function validatePaymentData(array $paymentData): array;
}
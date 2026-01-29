<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Payment Model
 * 
 * Represents payment transactions for bookings
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_id',
        'user_id',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'gateway_transaction_id',
        'amount',
        'currency',
        'status',
        'gateway_response',
        'refund_amount',
        'refund_reason',
        'refunded_at',
        'processed_at',
        'failed_at',
        'failure_reason',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'metadata' => 'array',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime'
    ];

    protected $dates = [
        'processed_at',
        'failed_at',
        'refunded_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Payment statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    // Payment methods
    const METHOD_CREDIT_CARD = 'credit_card';
    const METHOD_DEBIT_CARD = 'debit_card';
    const METHOD_PAYPAL = 'paypal';
    const METHOD_APPLE_PAY = 'apple_pay';
    const METHOD_GOOGLE_PAY = 'google_pay';
    const METHOD_BANK_TRANSFER = 'bank_transfer';

    // Payment gateways
    const GATEWAY_PAYPAL = 'paypal';
    const GATEWAY_MYFATOORAH = 'myfatoorah';
    const GATEWAY_STRIPE = 'stripe';

    /**
     * Get the booking that owns the payment
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user that made the payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for refunded payments
     */
    public function scopeRefunded($query)
    {
        return $query->whereIn('status', [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if payment is refunded
     */
    public function isRefunded(): bool
    {
        return in_array($this->status, [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    /**
     * Check if payment can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->isSuccessful() && !$this->isRefunded();
    }

    /**
     * Get remaining refundable amount
     */
    public function getRefundableAmount(): float
    {
        if (!$this->canBeRefunded()) {
            return 0;
        }

        return $this->amount - ($this->refund_amount ?? 0);
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(string $gatewayTransactionId = null, array $gatewayResponse = []): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'gateway_transaction_id' => $gatewayTransactionId,
            'gateway_response' => $gatewayResponse,
            'processed_at' => now()
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(string $reason = null, array $gatewayResponse = []): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failure_reason' => $reason,
            'gateway_response' => $gatewayResponse,
            'failed_at' => now()
        ]);
    }

    /**
     * Process refund
     */
    public function processRefund(float $amount, string $reason = null): void
    {
        $currentRefundAmount = $this->refund_amount ?? 0;
        $newRefundAmount = $currentRefundAmount + $amount;

        $status = $newRefundAmount >= $this->amount 
            ? self::STATUS_REFUNDED 
            : self::STATUS_PARTIALLY_REFUNDED;

        $this->update([
            'status' => $status,
            'refund_amount' => $newRefundAmount,
            'refund_reason' => $reason,
            'refunded_at' => now()
        ]);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Get formatted refund amount
     */
    public function getFormattedRefundAmountAttribute(): string
    {
        if (!$this->refund_amount) {
            return '0.00 ' . strtoupper($this->currency);
        }

        return number_format($this->refund_amount, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Get payment method display name
     */
    public function getPaymentMethodDisplayAttribute(): string
    {
        return match($this->payment_method) {
            self::METHOD_CREDIT_CARD => 'Credit Card',
            self::METHOD_DEBIT_CARD => 'Debit Card',
            self::METHOD_PAYPAL => 'PayPal',
            self::METHOD_APPLE_PAY => 'Apple Pay',
            self::METHOD_GOOGLE_PAY => 'Google Pay',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            default => ucfirst(str_replace('_', ' ', $this->payment_method))
        };
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REFUNDED => 'Refunded',
            self::STATUS_PARTIALLY_REFUNDED => 'Partially Refunded',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_COMPLETED => 'green',
            self::STATUS_PENDING, self::STATUS_PROCESSING => 'yellow',
            self::STATUS_FAILED, self::STATUS_CANCELLED => 'red',
            self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED => 'blue',
            default => 'gray'
        };
    }
}

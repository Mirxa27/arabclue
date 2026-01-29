<?php

namespace App\Exceptions;

use Exception;

class PaymentException extends Exception
{
    protected string $errorCode;
    protected array $context;

    public function __construct(
        string $message = 'Payment processing failed',
        int $code = 0,
        Exception $previous = null,
        string $errorCode = 'PAYMENT_ERROR',
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    /**
     * Get error code
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get context data
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Convert to array for API responses
     */
    public function toArray(): array
    {
        return [
            'error' => true,
            'message' => $this->getMessage(),
            'error_code' => $this->errorCode,
            'context' => $this->context
        ];
    }
}
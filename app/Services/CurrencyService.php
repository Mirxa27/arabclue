<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    protected array $supportedCurrencies = ['SAR', 'USD', 'KWD'];
    protected array $defaultRates = [
        'SAR_TO_USD' => 0.2667,
        'USD_TO_SAR' => 3.75,
        'SAR_TO_KWD' => 0.0822,
        'KWD_TO_SAR' => 12.16,
    ];

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return $this->supportedCurrencies;
    }

    /**
     * Get all conversion rates
     */
    public function getAllRates(): array
    {
        $rates = [];
        foreach ($this->defaultRates as $key => $defaultRate) {
            $rates[$key] = Cache::get("currency_rate_{$key}", $defaultRate);
        }
        return $rates;
    }

    /**
     * Get conversion rate between two currencies
     */
    public function getConversionRate(string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $rateKey = $fromCurrency . '_TO_' . $toCurrency;
        
        if (isset($this->defaultRates[$rateKey])) {
            return Cache::get("currency_rate_{$rateKey}", $this->defaultRates[$rateKey]);
        }

        // Try inverse rate
        $inverseKey = $toCurrency . '_TO_' . $fromCurrency;
        if (isset($this->defaultRates[$inverseKey])) {
            $inverseRate = Cache::get("currency_rate_{$inverseKey}", $this->defaultRates[$inverseKey]);
            return 1 / $inverseRate;
        }

        Log::warning("Currency conversion rate not found", [
            'from' => $fromCurrency,
            'to' => $toCurrency
        ]);

        return 1.0; // Fallback
    }

    /**
     * Update conversion rate
     */
    public function updateConversionRate(string $fromCurrency, string $toCurrency, float $rate): bool
    {
        $rateKey = $fromCurrency . '_TO_' . $toCurrency;
        
        try {
            Cache::put("currency_rate_{$rateKey}", $rate, now()->addDays(30));
            
            // Update inverse rate
            $inverseKey = $toCurrency . '_TO_' . $fromCurrency;
            $inverseRate = 1 / $rate;
            Cache::put("currency_rate_{$inverseKey}", $inverseRate, now()->addDays(30));
            
            Log::info("Currency rate updated", [
                'rate_key' => $rateKey,
                'rate' => $rate,
                'inverse_key' => $inverseKey,
                'inverse_rate' => $inverseRate
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to update currency rate", [
                'rate_key' => $rateKey,
                'rate' => $rate,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Convert amount between currencies
     */
    public function convertAmount(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $rate = $this->getConversionRate($fromCurrency, $toCurrency);
        return $amount * $rate;
    }

    /**
     * Convert SAR to USD (most common conversion for PayPal)
     */
    public function convertSarToUsd(float $sarAmount): float
    {
        return $this->convertAmount($sarAmount, 'SAR', 'USD');
    }

    /**
     * Convert USD to SAR
     */
    public function convertUsdToSar(float $usdAmount): float
    {
        return $this->convertAmount($usdAmount, 'USD', 'SAR');
    }

    /**
     * Format price with currency symbol
     */
    public function formatPrice(float $amount, string $currency = 'SAR'): string
    {
        switch ($currency) {
            case 'SAR':
                return 'SAR ' . number_format($amount, 2);
            case 'USD':
                return '$' . number_format($amount, 2);
            case 'KWD':
                return 'KWD ' . number_format($amount, 3);
            default:
                return $currency . ' ' . number_format($amount, 2);
        }
    }

    /**
     * Sync rates from an array
     */
    public function syncRates(array $rates): array
    {
        $results = [];
        
        foreach ($rates as $rateKey => $rateValue) {
            if (strpos($rateKey, '_TO_') !== false) {
                $parts = explode('_TO_', $rateKey);
                if (count($parts) === 2) {
                    $fromCurrency = $parts[0];
                    $toCurrency = $parts[1];
                    
                    $success = $this->updateConversionRate($fromCurrency, $toCurrency, (float) $rateValue);
                    $results[$rateKey] = $success;
                }
            }
        }
        
        return $results;
    }
}
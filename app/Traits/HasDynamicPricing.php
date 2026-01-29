<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasDynamicPricing
{
    /**
     * Calculate dynamic price for given dates
     */
    public function calculateDynamicPrice(Carbon $checkIn, Carbon $checkOut, int $guests = 1): array
    {
        $basePrice = $this->price_per_night;
        $totalDays = $checkIn->diffInDays($checkOut);
        
        if ($totalDays <= 0) {
            return ['error' => 'Invalid date range'];
        }

        $priceBreakdown = [
            'base_price' => $basePrice,
            'total_nights' => $totalDays,
            'subtotal' => $basePrice * $totalDays,
            'adjustments' => [],
            'fees' => [],
            'total' => 0
        ];

        // Apply seasonal pricing
        $seasonalAdjustment = $this->getSeasonalAdjustment($checkIn, $checkOut);
        if ($seasonalAdjustment !== 0) {
            $adjustment = $priceBreakdown['subtotal'] * ($seasonalAdjustment / 100);
            $priceBreakdown['adjustments']['seasonal'] = [
                'percentage' => $seasonalAdjustment,
                'amount' => $adjustment,
                'description' => $seasonalAdjustment > 0 ? 'Peak season surcharge' : 'Off-season discount'
            ];
            $priceBreakdown['subtotal'] += $adjustment;
        }

        // Apply demand-based pricing
        $demandMultiplier = $this->getDemandMultiplier($checkIn, $checkOut);
        if ($demandMultiplier !== 1.0) {
            $adjustment = $priceBreakdown['subtotal'] * ($demandMultiplier - 1);
            $priceBreakdown['adjustments']['demand'] = [
                'multiplier' => $demandMultiplier,
                'amount' => $adjustment,
                'description' => $demandMultiplier > 1 ? 'High demand surcharge' : 'Low demand discount'
            ];
            $priceBreakdown['subtotal'] += $adjustment;
        }

        // Apply length of stay discounts
        $lengthDiscount = $this->getLengthOfStayDiscount($totalDays);
        if ($lengthDiscount > 0) {
            $discount = $priceBreakdown['subtotal'] * ($lengthDiscount / 100);
            $priceBreakdown['adjustments']['length_discount'] = [
                'percentage' => -$lengthDiscount,
                'amount' => -$discount,
                'description' => $this->getLengthDiscountDescription($totalDays)
            ];
            $priceBreakdown['subtotal'] -= $discount;
        }

        // Apply guest count pricing
        $guestAdjustment = $this->getGuestCountAdjustment($guests);
        if ($guestAdjustment !== 0) {
            $adjustment = $guestAdjustment * $totalDays;
            $priceBreakdown['adjustments']['extra_guests'] = [
                'per_night' => $guestAdjustment,
                'amount' => $adjustment,
                'description' => "Additional fee for {$guests} guests"
            ];
            $priceBreakdown['subtotal'] += $adjustment;
        }

        // Add fees
        $priceBreakdown['fees'] = $this->calculateFees($priceBreakdown['subtotal'], $totalDays, $guests);
        
        $totalFees = array_sum(array_column($priceBreakdown['fees'], 'amount'));
        $priceBreakdown['total'] = $priceBreakdown['subtotal'] + $totalFees;

        return $priceBreakdown;
    }

    /**
     * Get seasonal pricing adjustment percentage
     */
    protected function getSeasonalAdjustment(Carbon $checkIn, Carbon $checkOut): float
    {
        $pricingRules = $this->pricing_rules ?? [];
        $seasonalRules = $pricingRules['seasonal'] ?? [];
        
        if (empty($seasonalRules)) {
            return 0;
        }

        // Check each day in the stay period
        $totalAdjustment = 0;
        $totalDays = 0;
        
        $current = $checkIn->copy();
        while ($current->lt($checkOut)) {
            $month = $current->month;
            $dayOfYear = $current->dayOfYear;
            
            $adjustment = 0;
            foreach ($seasonalRules as $rule) {
                if ($this->dateMatchesRule($current, $rule)) {
                    $adjustment = max($adjustment, $rule['adjustment'] ?? 0);
                }
            }
            
            $totalAdjustment += $adjustment;
            $totalDays++;
            $current->addDay();
        }
        
        return $totalDays > 0 ? $totalAdjustment / $totalDays : 0;
    }

    /**
     * Get demand-based pricing multiplier
     */
    protected function getDemandMultiplier(Carbon $checkIn, Carbon $checkOut): float
    {
        $pricingRules = $this->pricing_rules ?? [];
        $demandRules = $pricingRules['demand'] ?? [];
        
        if (empty($demandRules)) {
            return 1.0;
        }

        // Check booking density for the period
        $bookingCount = $this->bookings()
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                      ->orWhereBetween('check_out', [$checkIn, $checkOut])
                      ->orWhere(function ($q) use ($checkIn, $checkOut) {
                          $q->where('check_in', '<=', $checkIn)
                            ->where('check_out', '>=', $checkOut);
                      });
            })
            ->count();

        // Apply demand multiplier based on booking density
        foreach ($demandRules as $rule) {
            if ($bookingCount >= ($rule['min_bookings'] ?? 0)) {
                return $rule['multiplier'] ?? 1.0;
            }
        }
        
        return 1.0;
    }

    /**
     * Get length of stay discount percentage
     */
    protected function getLengthOfStayDiscount(int $nights): float
    {
        $pricingRules = $this->pricing_rules ?? [];
        $lengthDiscounts = $pricingRules['length_discounts'] ?? [];
        
        $discount = 0;
        foreach ($lengthDiscounts as $rule) {
            $minNights = $rule['min_nights'] ?? 0;
            if ($nights >= $minNights) {
                $discount = max($discount, $rule['discount'] ?? 0);
            }
        }
        
        return $discount;
    }

    /**
     * Get guest count adjustment
     */
    protected function getGuestCountAdjustment(int $guests): float
    {
        $maxGuests = $this->max_guests ?? 1;
        $extraGuestFee = $this->extra_guest_fee ?? 0;
        
        if ($guests > $maxGuests) {
            return ($guests - $maxGuests) * $extraGuestFee;
        }
        
        return 0;
    }

    /**
     * Calculate additional fees
     */
    protected function calculateFees(float $subtotal, int $nights, int $guests): array
    {
        $fees = [];
        
        // Cleaning fee
        if ($this->cleaning_fee > 0) {
            $fees['cleaning'] = [
                'name' => 'Cleaning fee',
                'amount' => $this->cleaning_fee,
                'type' => 'fixed'
            ];
        }
        
        // Service fee
        $serviceFeePercent = config('app.service_fee_percent', 3);
        if ($serviceFeePercent > 0) {
            $serviceFee = $subtotal * ($serviceFeePercent / 100);
            $fees['service'] = [
                'name' => 'Service fee',
                'amount' => $serviceFee,
                'type' => 'percentage',
                'percentage' => $serviceFeePercent
            ];
        }
        
        // Security deposit (if required)
        if ($this->security_deposit > 0) {
            $fees['security_deposit'] = [
                'name' => 'Security deposit',
                'amount' => $this->security_deposit,
                'type' => 'deposit',
                'refundable' => true
            ];
        }
        
        return $fees;
    }

    /**
     * Check if date matches pricing rule
     */
    protected function dateMatchesRule(Carbon $date, array $rule): bool
    {
        if (isset($rule['start_date']) && isset($rule['end_date'])) {
            return $date->between(
                Carbon::parse($rule['start_date']),
                Carbon::parse($rule['end_date'])
            );
        }
        
        if (isset($rule['months'])) {
            return in_array($date->month, $rule['months']);
        }
        
        if (isset($rule['day_of_week'])) {
            return in_array($date->dayOfWeek, $rule['day_of_week']);
        }
        
        return false;
    }

    /**
     * Get length discount description
     */
    protected function getLengthDiscountDescription(int $nights): string
    {
        return match (true) {
            $nights >= 28 => 'Monthly stay discount',
            $nights >= 7 => 'Weekly stay discount',
            $nights >= 3 => 'Extended stay discount',
            default => 'Length of stay discount'
        };
    }

    /**
     * Update pricing rules
     */
    public function updatePricingRules(array $rules): void
    {
        $currentRules = $this->pricing_rules ?? [];
        $updatedRules = array_merge($currentRules, $rules);
        
        $this->update(['pricing_rules' => $updatedRules]);
    }

    /**
     * Get available pricing strategies
     */
    public function getAvailablePricingStrategies(): array
    {
        return [
            'seasonal' => 'Seasonal pricing adjustments',
            'demand' => 'Demand-based pricing',
            'length_discounts' => 'Length of stay discounts',
            'last_minute' => 'Last minute pricing',
            'early_bird' => 'Early booking discounts'
        ];
    }
}
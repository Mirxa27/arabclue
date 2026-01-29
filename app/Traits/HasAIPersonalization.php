<?php

namespace App\Traits;

trait HasAIPersonalization
{
    /**
     * Get AI personalization data
     */
    public function getAIPersonalizationData(): array
    {
        return [
            'user_id' => $this->id,
            'preferences' => $this->preferences ?? [],
            'search_history' => $this->getSearchHistory(),
            'booking_patterns' => $this->getBookingPatterns(),
            'interaction_style' => $this->getInteractionStyle(),
            'language_preference' => $this->language ?? 'en'
        ];
    }

    /**
     * Update AI learning data
     */
    public function updateAILearning(array $data): void
    {
        $currentPreferences = $this->preferences ?? [];
        $aiLearning = $currentPreferences['ai_learning'] ?? [];
        
        // Update interaction patterns
        if (isset($data['interaction'])) {
            $aiLearning['interactions'][] = [
                'type' => $data['interaction']['type'],
                'context' => $data['interaction']['context'],
                'timestamp' => now()->toISOString(),
                'satisfaction' => $data['interaction']['satisfaction'] ?? null
            ];
            
            // Keep only last 100 interactions
            $aiLearning['interactions'] = array_slice($aiLearning['interactions'], -100);
        }
        
        // Update preferences
        if (isset($data['preferences'])) {
            $aiLearning['preferences'] = array_merge(
                $aiLearning['preferences'] ?? [],
                $data['preferences']
            );
        }
        
        // Update communication style
        if (isset($data['communication_style'])) {
            $aiLearning['communication_style'] = $data['communication_style'];
        }
        
        $currentPreferences['ai_learning'] = $aiLearning;
        $this->update(['preferences' => $currentPreferences]);
    }

    /**
     * Get personalized recommendations
     */
    public function getPersonalizedRecommendations(string $type = 'properties', int $limit = 6): array
    {
        $personalizationData = $this->getAIPersonalizationData();
        
        // This would integrate with your AI recommendation service
        return [
            'type' => $type,
            'recommendations' => [],
            'reasoning' => 'Based on your preferences and booking history',
            'confidence' => 0.8
        ];
    }

    /**
     * Get search history patterns
     */
    protected function getSearchHistory(): array
    {
        $preferences = $this->preferences ?? [];
        return $preferences['search_history'] ?? [];
    }

    /**
     * Get booking patterns
     */
    protected function getBookingPatterns(): array
    {
        // Analyze user's booking history for patterns
        $bookings = $this->bookings()->completed()->get();
        
        $patterns = [
            'preferred_price_range' => $this->calculatePriceRange($bookings),
            'preferred_amenities' => $this->calculatePreferredAmenities($bookings),
            'booking_lead_time' => $this->calculateBookingLeadTime($bookings),
            'stay_duration' => $this->calculateStayDuration($bookings),
            'seasonal_preferences' => $this->calculateSeasonalPreferences($bookings)
        ];
        
        return $patterns;
    }

    /**
     * Get interaction style preferences
     */
    protected function getInteractionStyle(): array
    {
        $preferences = $this->preferences ?? [];
        $aiLearning = $preferences['ai_learning'] ?? [];
        
        return [
            'communication_style' => $aiLearning['communication_style'] ?? 'friendly',
            'response_length' => $aiLearning['response_length'] ?? 'medium',
            'detail_level' => $aiLearning['detail_level'] ?? 'balanced',
            'formality' => $aiLearning['formality'] ?? 'casual'
        ];
    }

    /**
     * Calculate preferred price range from bookings
     */
    protected function calculatePriceRange($bookings): array
    {
        if ($bookings->isEmpty()) {
            return ['min' => 0, 'max' => 1000, 'preferred' => 300];
        }
        
        $prices = $bookings->pluck('price_per_night')->sort();
        
        return [
            'min' => $prices->first(),
            'max' => $prices->last(),
            'preferred' => $prices->median(),
            'average' => $prices->average()
        ];
    }

    /**
     * Calculate preferred amenities from bookings
     */
    protected function calculatePreferredAmenities($bookings): array
    {
        // This would analyze amenities from booked properties
        return [];
    }

    /**
     * Calculate booking lead time patterns
     */
    protected function calculateBookingLeadTime($bookings): array
    {
        $leadTimes = $bookings->map(function ($booking) {
            return $booking->created_at->diffInDays($booking->check_in);
        });
        
        if ($leadTimes->isEmpty()) {
            return ['average' => 14, 'pattern' => 'medium_planner'];
        }
        
        $average = $leadTimes->average();
        
        $pattern = match (true) {
            $average <= 3 => 'last_minute',
            $average <= 14 => 'short_planner',
            $average <= 30 => 'medium_planner',
            default => 'advance_planner'
        };
        
        return [
            'average' => round($average),
            'pattern' => $pattern
        ];
    }

    /**
     * Calculate stay duration patterns
     */
    protected function calculateStayDuration($bookings): array
    {
        $durations = $bookings->map(function ($booking) {
            return $booking->check_out->diffInDays($booking->check_in);
        });
        
        if ($durations->isEmpty()) {
            return ['average' => 3, 'pattern' => 'short_stay'];
        }
        
        $average = $durations->average();
        
        $pattern = match (true) {
            $average <= 2 => 'very_short',
            $average <= 5 => 'short_stay',
            $average <= 10 => 'medium_stay',
            default => 'long_stay'
        };
        
        return [
            'average' => round($average),
            'pattern' => $pattern
        ];
    }

    /**
     * Calculate seasonal preferences
     */
    protected function calculateSeasonalPreferences($bookings): array
    {
        $seasonalData = $bookings->groupBy(function ($booking) {
            return $booking->check_in->format('n'); // Month number
        });
        
        $preferences = [];
        foreach ($seasonalData as $month => $monthBookings) {
            $season = $this->getSeasonFromMonth((int)$month);
            $preferences[$season] = ($preferences[$season] ?? 0) + $monthBookings->count();
        }
        
        return $preferences;
    }

    /**
     * Get season from month number
     */
    protected function getSeasonFromMonth(int $month): string
    {
        return match (true) {
            in_array($month, [12, 1, 2]) => 'winter',
            in_array($month, [3, 4, 5]) => 'spring',
            in_array($month, [6, 7, 8]) => 'summer',
            default => 'autumn'
        };
    }
}
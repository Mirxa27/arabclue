<?php

namespace App\Traits;

trait HasAIOptimization
{
    /**
     * Get AI optimization data for the property
     */
    public function getAIOptimizationData(): array
    {
        return [
            'property_id' => $this->id,
            'pricing_optimization' => $this->getPricingOptimizationData(),
            'content_optimization' => $this->getContentOptimizationData(),
            'booking_optimization' => $this->getBookingOptimizationData(),
            'performance_metrics' => $this->getPerformanceMetrics()
        ];
    }

    /**
     * Apply AI-generated optimizations
     */
    public function applyAIOptimizations(array $optimizations): void
    {
        foreach ($optimizations as $type => $data) {
            match ($type) {
                'pricing' => $this->applyPricingOptimizations($data),
                'content' => $this->applyContentOptimizations($data),
                'amenities' => $this->applyAmenityOptimizations($data),
                'availability' => $this->applyAvailabilityOptimizations($data),
                default => null
            };
        }

        // Update optimization timestamp
        $this->update(['ai_optimized_at' => now()]);
    }

    /**
     * Get pricing optimization recommendations
     */
    protected function getPricingOptimizationData(): array
    {
        $bookings = $this->bookings()->completed()->get();
        $views = $this->getViewsData();
        
        return [
            'current_price' => $this->price_per_night,
            'occupancy_rate' => $this->calculateOccupancyRate(),
            'average_daily_rate' => $this->calculateAverageDailyRate($bookings),
            'conversion_rate' => $this->calculateConversionRate($views, $bookings),
            'competitor_analysis' => $this->getCompetitorPricingData(),
            'seasonal_trends' => $this->getSeasonalTrends($bookings)
        ];
    }

    /**
     * Get content optimization data
     */
    protected function getContentOptimizationData(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'amenities' => $this->amenities()->pluck('name')->toArray(),
            'photos_count' => $this->images()->count(),
            'review_keywords' => $this->extractReviewKeywords(),
            'search_ranking' => $this->getSearchRankingData(),
            'engagement_metrics' => $this->getEngagementMetrics()
        ];
    }

    /**
     * Get booking optimization data
     */
    protected function getBookingOptimizationData(): array
    {
        return [
            'booking_patterns' => $this->getBookingPatterns(),
            'cancellation_rate' => $this->calculateCancellationRate(),
            'guest_satisfaction' => $this->calculateGuestSatisfaction(),
            'response_time' => $this->calculateAverageResponseTime(),
            'booking_lead_time' => $this->calculateAverageBookingLeadTime()
        ];
    }

    /**
     * Apply pricing optimizations
     */
    protected function applyPricingOptimizations(array $data): void
    {
        if (isset($data['recommended_price']) && $data['confidence'] > 0.8) {
            $newPrice = $data['recommended_price'];
            $this->update(['price_per_night' => $newPrice]);
        }

        if (isset($data['dynamic_pricing_rules'])) {
            $this->updatePricingRules($data['dynamic_pricing_rules']);
        }
    }

    /**
     * Apply content optimizations
     */
    protected function applyContentOptimizations(array $data): void
    {
        $updates = [];

        if (isset($data['optimized_title']) && $data['title_confidence'] > 0.7) {
            $updates['title'] = $data['optimized_title'];
        }

        if (isset($data['optimized_description']) && $data['description_confidence'] > 0.7) {
            $updates['description'] = $data['optimized_description'];
        }

        if (!empty($updates)) {
            $this->update($updates);
        }
    }

    /**
     * Apply amenity optimizations
     */
    protected function applyAmenityOptimizations(array $data): void
    {
        if (isset($data['recommended_amenities'])) {
            foreach ($data['recommended_amenities'] as $amenityData) {
                if ($amenityData['confidence'] > 0.8) {
                    // Add recommended amenity
                    $this->amenities()->firstOrCreate(
                        ['name' => $amenityData['name']]
                    );
                }
            }
        }
    }

    /**
     * Calculate occupancy rate
     */
    protected function calculateOccupancyRate(): float
    {
        $totalDays = 365; // Last year
        $bookedDays = $this->bookings()
            ->where('check_in', '>=', now()->subYear())
            ->where('status', 'confirmed')
            ->get()
            ->sum(function ($booking) {
                return $booking->check_out->diffInDays($booking->check_in);
            });

        return $totalDays > 0 ? ($bookedDays / $totalDays) * 100 : 0;
    }

    /**
     * Calculate average daily rate
     */
    protected function calculateAverageDailyRate($bookings): float
    {
        if ($bookings->isEmpty()) {
            return $this->price_per_night;
        }

        return $bookings->average('price_per_night');
    }

    /**
     * Calculate conversion rate
     */
    protected function calculateConversionRate(array $views, $bookings): float
    {
        $totalViews = $views['total'] ?? 0;
        $totalBookings = $bookings->count();

        return $totalViews > 0 ? ($totalBookings / $totalViews) * 100 : 0;
    }

    /**
     * Get competitor pricing data
     */
    protected function getCompetitorPricingData(): array
    {
        // Find similar properties in the area
        $competitors = static::where('id', '!=', $this->id)
            ->where('city', $this->city)
            ->where('property_type', $this->property_type)
            ->whereBetween('max_guests', [$this->max_guests - 2, $this->max_guests + 2])
            ->limit(10)
            ->get();

        if ($competitors->isEmpty()) {
            return [];
        }

        return [
            'average_price' => $competitors->average('price_per_night'),
            'min_price' => $competitors->min('price_per_night'),
            'max_price' => $competitors->max('price_per_night'),
            'market_position' => $this->calculateMarketPosition($competitors)
        ];
    }

    /**
     * Calculate market position
     */
    protected function calculateMarketPosition($competitors): string
    {
        $averagePrice = $competitors->average('price_per_night');
        $myPrice = $this->price_per_night;

        return match (true) {
            $myPrice < $averagePrice * 0.8 => 'budget',
            $myPrice > $averagePrice * 1.2 => 'premium',
            default => 'mid_range'
        };
    }

    /**
     * Extract review keywords
     */
    protected function extractReviewKeywords(): array
    {
        $reviews = $this->reviews()->where('rating', '>=', 4)->get();
        $keywords = [];

        foreach ($reviews as $review) {
            // Simple keyword extraction (in real implementation, use NLP)
            $words = str_word_count(strtolower($review->comment), 1);
            $positiveWords = array_intersect($words, [
                'clean', 'beautiful', 'amazing', 'perfect', 'excellent',
                'comfortable', 'spacious', 'quiet', 'convenient', 'helpful'
            ]);
            $keywords = array_merge($keywords, $positiveWords);
        }

        return array_count_values($keywords);
    }

    /**
     * Get views data (placeholder)
     */
    protected function getViewsData(): array
    {
        // This would integrate with analytics service
        return ['total' => 100, 'unique' => 80];
    }

    /**
     * Get seasonal trends
     */
    protected function getSeasonalTrends($bookings): array
    {
        $seasonal = $bookings->groupBy(function ($booking) {
            return $booking->check_in->format('n'); // Month
        });

        $trends = [];
        foreach ($seasonal as $month => $monthBookings) {
            $trends[$month] = [
                'bookings' => $monthBookings->count(),
                'average_price' => $monthBookings->average('price_per_night'),
                'occupancy' => $monthBookings->count() > 0 ? 'high' : 'low'
            ];
        }

        return $trends;
    }

    /**
     * Get performance metrics
     */
    protected function getPerformanceMetrics(): array
    {
        return [
            'listing_score' => $this->calculateListingScore(),
            'search_visibility' => $this->calculateSearchVisibility(),
            'guest_satisfaction' => $this->calculateGuestSatisfaction(),
            'revenue_performance' => $this->calculateRevenuePerformance()
        ];
    }

    /**
     * Calculate listing score
     */
    protected function calculateListingScore(): int
    {
        $score = 0;
        
        // Photos (max 20 points)
        $photoCount = $this->images()->count();
        $score += min(20, $photoCount * 2);
        
        // Description length (max 15 points)
        $descriptionLength = strlen($this->description ?? '');
        $score += min(15, $descriptionLength / 100);
        
        // Amenities (max 25 points)
        $amenityCount = $this->amenities()->count();
        $score += min(25, $amenityCount * 2);
        
        // Reviews (max 25 points)
        $reviewScore = $this->reviews()->average('rating') ?? 0;
        $score += $reviewScore * 5;
        
        // Verification (max 15 points)
        if ($this->verified) $score += 15;
        
        return min(100, round($score));
    }

    /**
     * Calculate search visibility
     */
    protected function calculateSearchVisibility(): float
    {
        // Placeholder - would integrate with search analytics
        return 65.5;
    }

    /**
     * Calculate guest satisfaction
     */
    protected function calculateGuestSatisfaction(): float
    {
        return $this->reviews()->average('rating') ?? 0;
    }

    /**
     * Calculate revenue performance
     */
    protected function calculateRevenuePerformance(): array
    {
        $lastMonthRevenue = $this->bookings()
            ->where('check_in', '>=', now()->subMonth())
            ->where('status', 'confirmed')
            ->sum('total_amount');

        $previousMonthRevenue = $this->bookings()
            ->whereBetween('check_in', [now()->subMonths(2), now()->subMonth()])
            ->where('status', 'confirmed')
            ->sum('total_amount');

        $growth = $previousMonthRevenue > 0 
            ? (($lastMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100
            : 0;

        return [
            'last_month' => $lastMonthRevenue,
            'previous_month' => $previousMonthRevenue,
            'growth_percentage' => round($growth, 2)
        ];
    }

    /**
     * Get booking patterns
     */
    protected function getBookingPatterns(): array
    {
        $bookings = $this->bookings()->completed()->get();
        
        return [
            'average_stay_duration' => $bookings->average(function ($booking) {
                return $booking->check_out->diffInDays($booking->check_in);
            }),
            'peak_booking_days' => $this->getPeakBookingDays($bookings),
            'guest_return_rate' => $this->calculateGuestReturnRate($bookings)
        ];
    }

    /**
     * Get peak booking days
     */
    protected function getPeakBookingDays($bookings): array
    {
        $dayOfWeek = $bookings->groupBy(function ($booking) {
            return $booking->check_in->dayOfWeek;
        });

        return $dayOfWeek->map->count()->sortDesc()->take(3)->keys()->toArray();
    }

    /**
     * Calculate guest return rate
     */
    protected function calculateGuestReturnRate($bookings): float
    {
        $totalGuests = $bookings->pluck('guest_id')->unique()->count();
        $returningGuests = $bookings->groupBy('guest_id')
            ->filter(function ($guestBookings) {
                return $guestBookings->count() > 1;
            })->count();

        return $totalGuests > 0 ? ($returningGuests / $totalGuests) * 100 : 0;
    }

    /**
     * Calculate cancellation rate
     */
    protected function calculateCancellationRate(): float
    {
        $totalBookings = $this->bookings()->count();
        $cancelledBookings = $this->bookings()->where('status', 'cancelled')->count();

        return $totalBookings > 0 ? ($cancelledBookings / $totalBookings) * 100 : 0;
    }

    /**
     * Calculate average response time
     */
    protected function calculateAverageResponseTime(): float
    {
        // This would calculate host response time to messages
        return 2.5; // hours (placeholder)
    }

    /**
     * Calculate average booking lead time
     */
    protected function calculateAverageBookingLeadTime(): float
    {
        $bookings = $this->bookings()->get();
        
        $leadTimes = $bookings->map(function ($booking) {
            return $booking->created_at->diffInDays($booking->check_in);
        });

        return $leadTimes->average() ?? 14;
    }

    /**
     * Get engagement metrics
     */
    protected function getEngagementMetrics(): array
    {
        return [
            'saves_count' => $this->wishlists()->count(),
            'shares_count' => 0, // Would track social shares
            'inquiry_rate' => $this->calculateInquiryRate(),
            'photo_views' => $this->images()->sum('view_count') ?? 0
        ];
    }

    /**
     * Calculate inquiry rate
     */
    protected function calculateInquiryRate(): float
    {
        // This would calculate inquiries vs views
        return 5.2; // percentage (placeholder)
    }
}
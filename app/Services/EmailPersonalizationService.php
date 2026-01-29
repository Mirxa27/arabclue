<?php

namespace App\Services;

use App\Models\User;
use App\Models\Property;
use App\Models\Booking;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class EmailPersonalizationService
{
    /**
     * Get personalized property recommendations for user
     */
    public function getPersonalizedRecommendations(User $user, int $limit = 3): Collection
    {
        $cacheKey = "user_recommendations:{$user->id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($user, $limit) {
            // Get user's booking history and preferences
            $userBookings = $user->bookings()->with('property')->get();
            $preferredCities = $userBookings->pluck('property.city')->unique();
            $preferredPropertyTypes = $userBookings->pluck('property.property_type')->unique();
            $avgPriceRange = $userBookings->avg('total_amount') ?? 500;

            // Build recommendation query
            $query = Property::where('status', 'active')
                ->where('is_featured', true);

            // Prefer cities user has booked before
            if ($preferredCities->isNotEmpty()) {
                $query->whereIn('city', $preferredCities->toArray());
            }

            // Prefer property types user has booked before
            if ($preferredPropertyTypes->isNotEmpty()) {
                $query->whereIn('property_type', $preferredPropertyTypes->toArray());
            }

            // Price range based on user's history
            $query->whereBetween('price_per_night', [
                $avgPriceRange * 0.7, // 30% below average
                $avgPriceRange * 1.5  // 50% above average
            ]);

            return $query->orderBy('overall_rating', 'desc')
                ->orderBy('review_count', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get personalized greeting based on user data
     */
    public function getPersonalizedGreeting(User $user): string
    {
        $hour = now()->hour;
        $timeGreeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        
        // Check if user has recent bookings
        $recentBooking = $user->bookings()
            ->where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->first();

        if ($recentBooking) {
            return "{$timeGreeting}, {$user->name}! We hope you enjoyed your recent stay.";
        }

        // Check if user is a frequent traveler
        $bookingCount = $user->bookings()->count();
        if ($bookingCount >= 5) {
            return "{$timeGreeting}, {$user->name}! Thank you for being a valued HabibiStay traveler.";
        }

        // Check if user is a host
        if ($user->role === 'host') {
            return "{$timeGreeting}, {$user->name}! Thank you for being an amazing host.";
        }

        return "{$timeGreeting}, {$user->name}!";
    }

    /**
     * Get user's travel preferences
     */
    public function getUserTravelPreferences(User $user): array
    {
        $bookings = $user->bookings()->with('property')->get();
        
        if ($bookings->isEmpty()) {
            return $this->getDefaultPreferences();
        }

        return [
            'preferred_cities' => $bookings->pluck('property.city')
                ->countBy()
                ->sortDesc()
                ->take(3)
                ->keys()
                ->toArray(),
            
            'preferred_property_types' => $bookings->pluck('property.property_type')
                ->countBy()
                ->sortDesc()
                ->take(2)
                ->keys()
                ->toArray(),
            
            'average_stay_duration' => round($bookings->avg('nights')),
            
            'preferred_price_range' => [
                'min' => $bookings->min('total_amount'),
                'max' => $bookings->max('total_amount'),
                'avg' => round($bookings->avg('total_amount'))
            ],
            
            'booking_frequency' => $this->calculateBookingFrequency($bookings),
            
            'seasonal_preferences' => $this->getSeasonalPreferences($bookings),
            
            'group_size_preference' => round($bookings->avg('guests')),
        ];
    }

    /**
     * Get personalized email subject line
     */
    public function getPersonalizedSubject(string $baseSubject, User $user, array $context = []): string
    {
        $preferences = $this->getUserTravelPreferences($user);
        
        // Add location if user has preferred cities
        if (!empty($preferences['preferred_cities']) && isset($context['property'])) {
            $property = $context['property'];
            if (in_array($property->city, $preferences['preferred_cities'])) {
                return $baseSubject . " in {$property->city}";
            }
        }

        // Add urgency for frequent travelers
        if ($preferences['booking_frequency'] === 'frequent') {
            return "🔥 " . $baseSubject . " - Limited Time";
        }

        // Add personal touch for loyal customers
        $bookingCount = $user->bookings()->count();
        if ($bookingCount >= 10) {
            return $baseSubject . " - VIP Member Exclusive";
        }

        return $baseSubject;
    }

    /**
     * Get personalized content recommendations
     */
    public function getPersonalizedContent(User $user): array
    {
        $preferences = $this->getUserTravelPreferences($user);
        
        return [
            'recommended_destinations' => $this->getRecommendedDestinations($preferences),
            'travel_tips' => $this->getTravelTips($preferences),
            'seasonal_offers' => $this->getSeasonalOffers($preferences),
            'loyalty_benefits' => $this->getLoyaltyBenefits($user),
        ];
    }

    /**
     * Calculate booking frequency
     */
    private function calculateBookingFrequency(Collection $bookings): string
    {
        if ($bookings->isEmpty()) {
            return 'new';
        }

        $daysSinceFirst = $bookings->min('created_at')->diffInDays(now());
        $bookingsPerMonth = ($bookings->count() / max($daysSinceFirst / 30, 1));

        if ($bookingsPerMonth >= 2) {
            return 'frequent';
        } elseif ($bookingsPerMonth >= 0.5) {
            return 'regular';
        } else {
            return 'occasional';
        }
    }

    /**
     * Get seasonal booking preferences
     */
    private function getSeasonalPreferences(Collection $bookings): array
    {
        $seasonalData = $bookings->groupBy(function ($booking) {
            $month = $booking->check_in->month;
            if (in_array($month, [12, 1, 2])) return 'winter';
            if (in_array($month, [3, 4, 5])) return 'spring';
            if (in_array($month, [6, 7, 8])) return 'summer';
            return 'autumn';
        });

        return $seasonalData->map->count()->sortDesc()->toArray();
    }

    /**
     * Get default preferences for new users
     */
    private function getDefaultPreferences(): array
    {
        return [
            'preferred_cities' => ['Riyadh', 'Jeddah', 'Dammam'],
            'preferred_property_types' => ['apartment', 'house'],
            'average_stay_duration' => 2,
            'preferred_price_range' => ['min' => 100, 'max' => 500, 'avg' => 250],
            'booking_frequency' => 'new',
            'seasonal_preferences' => [],
            'group_size_preference' => 2,
        ];
    }

    /**
     * Get recommended destinations based on preferences
     */
    private function getRecommendedDestinations(array $preferences): array
    {
        // Logic to recommend destinations based on user preferences
        $destinations = [
            'Riyadh' => 'Explore the capital\'s modern attractions and traditional souks',
            'Jeddah' => 'Discover the historic charm of the Red Sea coast',
            'AlUla' => 'Experience ancient history and stunning landscapes',
            'Taif' => 'Enjoy the cool mountain climate and rose gardens',
            'Abha' => 'Relax in the beautiful Asir Mountains',
        ];

        // Filter out cities user has already visited frequently
        $visitedCities = $preferences['preferred_cities'] ?? [];
        return array_diff_key($destinations, array_flip($visitedCities));
    }

    /**
     * Get travel tips based on user preferences
     */
    private function getTravelTips(array $preferences): array
    {
        $tips = [];
        
        if (in_array('frequent', [$preferences['booking_frequency']])) {
            $tips[] = 'Book in advance for better rates and availability';
            $tips[] = 'Consider our loyalty program for exclusive benefits';
        }

        if (($preferences['group_size_preference'] ?? 2) > 4) {
            $tips[] = 'Look for properties with multiple bedrooms for group stays';
            $tips[] = 'Check for group discounts on longer stays';
        }

        return $tips;
    }

    /**
     * Get seasonal offers based on preferences
     */
    private function getSeasonalOffers(array $preferences): array
    {
        $currentSeason = $this->getCurrentSeason();
        $offers = [];

        // Seasonal offer logic based on user preferences
        if (isset($preferences['seasonal_preferences'][$currentSeason])) {
            $offers[] = "Special {$currentSeason} rates available";
        }

        return $offers;
    }

    /**
     * Get loyalty benefits for user
     */
    private function getLoyaltyBenefits(User $user): array
    {
        $bookingCount = $user->bookings()->count();
        $benefits = [];

        if ($bookingCount >= 10) {
            $benefits[] = 'VIP customer support';
            $benefits[] = 'Early access to new properties';
            $benefits[] = 'Exclusive member discounts';
        } elseif ($bookingCount >= 5) {
            $benefits[] = 'Priority booking support';
            $benefits[] = 'Member-only deals';
        }

        return $benefits;
    }

    /**
     * Get current season
     */
    private function getCurrentSeason(): string
    {
        $month = now()->month;
        if (in_array($month, [12, 1, 2])) return 'winter';
        if (in_array($month, [3, 4, 5])) return 'spring';
        if (in_array($month, [6, 7, 8])) return 'summer';
        return 'autumn';
    }
}

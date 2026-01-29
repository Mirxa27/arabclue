<?php

namespace App\Services\AI;

use App\Models\Property;
use App\Models\SaraConversation;

/**
 * Additional methods for SaraChatbotService to handle button-driven interface
 */
trait SaraChatbotServiceExtensions
{
    /**
     * Handle popular destinations
     */
    protected function handlePopularDestinations(SaraConversation $conversation, array $entities): array
    {
        // Get popular properties in Riyadh
        $popularProperties = Property::where('status', 'active')
            ->where('city', 'LIKE', '%Riyadh%')
            ->where('average_rating', '>=', 4.0)
            ->orderBy('bookings_count', 'desc')
            ->orderBy('average_rating', 'desc')
            ->limit(4)
            ->get();
        
        if ($popularProperties->isEmpty()) {
            return [
                'message' => "Let me show you some great properties in Riyadh!",
                'suggested_actions' => [
                    ['type' => 'search_property', 'label' => '🔍 Search All Properties', 'data' => []],
                    ['type' => 'start_over', 'label' => '🔄 Start Over', 'data' => []]
                ]
            ];
        }
        
        $message = "⭐ **Popular Properties in Riyadh:**\n\n";
        
        foreach ($popularProperties as $index => $property) {
            $message .= ($index + 1) . ". **{$property->title}**\n";
            $message .= "📍 {$property->city}\n";
            $message .= "💰 {$property->price_per_night} SAR/night\n";
            $message .= "⭐ {$property->average_rating}/5 rating\n\n";
        }
        
        return [
            'message' => $message,
            'data' => [
                'properties' => $popularProperties->map(function ($property) {
                    return [
                        'id' => $property->id,
                        'title' => $property->title,
                        'image' => $property->main_image_url,
                        'price' => $property->price_per_night,
                        'rating' => $property->average_rating,
                        'location' => $property->city,
                        'actions' => [
                            ['type' => 'view_property_details', 'label' => '👁️ View Details', 'data' => ['property_id' => $property->id]],
                            ['type' => 'book_property', 'label' => '📅 Book Now', 'data' => ['property_id' => $property->id]]
                        ]
                    ];
                })->toArray()
            ],
            'suggested_actions' => [
                ['type' => 'search_property', 'label' => '🔍 Search More', 'data' => []],
                ['type' => 'start_over', 'label' => '🔄 Start Over', 'data' => []]
            ]
        ];
    }
    
    /**
     * Handle user login
     */
    protected function handleUserLogin(SaraConversation $conversation, array $entities): array
    {
        return [
            'message' => "To access your account and manage bookings, please log in or create a new account.",
            'data' => [
                'login_url' => route('login'),
                'register_url' => route('register')
            ],
            'suggested_actions' => [
                ['type' => 'open_url', 'label' => '👤 Login', 'data' => ['url' => route('login')]],
                ['type' => 'open_url', 'label' => '📝 Register', 'data' => ['url' => route('register')]],
                ['type' => 'search_property', 'label' => '🔍 Browse as Guest', 'data' => []],
                ['type' => 'start_over', 'label' => '🔄 Start Over', 'data' => []]
            ]
        ];
    }
    
    /**
     * Handle help and support
     */
    protected function handleHelpSupport(SaraConversation $conversation, array $entities): array
    {
        $message = "❓ **How can I help you?**\n\n";
        $message .= "I can assist you with:\n";
        $message .= "• Finding and booking properties\n";
        $message .= "• Answering questions about accommodations\n";
        $message .= "• Managing your bookings\n";
        $message .= "• Providing local recommendations\n";
        $message .= "• Payment and booking support\n\n";
        $message .= "What would you like help with?";
        
        return [
            'message' => $message,
            'suggested_actions' => [
                ['type' => 'search_property', 'label' => '🔍 Find Properties', 'data' => []],
                ['type' => 'view_my_bookings', 'label' => '📅 My Bookings', 'data' => []],
                ['type' => 'show_popular_destinations', 'label' => '⭐ Popular Places', 'data' => []],
                ['type' => 'contact_support', 'label' => '📞 Contact Support', 'data' => []]
            ]
        ];
    }
    
    /**
     * Handle start over
     */
    protected function handleStartOver(SaraConversation $conversation, array $entities): array
    {
        // Clear conversation context
        $conversation->update(['context' => []]);
        
        $hour = now()->hour;
        $greeting = match(true) {
            $hour < 12 => 'Good morning',
            $hour < 18 => 'Good afternoon',
            default => 'Good evening'
        };
        
        $message = "{$greeting}! Let's start fresh. I'm Sara, your AI assistant at HabibiStay. How can I help you find the perfect accommodation today?";
        
        return [
            'message' => $message,
            'suggested_actions' => [
                ['type' => 'search_property', 'label' => '🔍 Search Properties', 'data' => []],
                ['type' => 'show_popular_destinations', 'label' => '⭐ Popular in Riyadh', 'data' => []],
                ['type' => 'view_my_bookings', 'label' => '📅 My Bookings', 'data' => []],
                ['type' => 'help_support', 'label' => '❓ Help & Support', 'data' => []]
            ]
        ];
    }
}

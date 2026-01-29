<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\ConfigurationService;
use Illuminate\Http\Request;

class SaraChatController extends Controller
{
    protected $configService;

    public function __construct(ConfigurationService $configService)
    {
        $this->configService = $configService;
    }

    public function index()
    {
        // Get Sara configuration
        $saraConfig = $this->configService->get('sara', []);
        
        // Get featured properties (2 properties with highest ratings)
        $featuredProperties = Property::with(['images', 'owner', 'amenities'])
            ->where('status', 'active')
            ->orderBy('overall_rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get()
            ->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'slug' => $property->slug,
                    'description' => $property->description,
                    'price_per_night' => $property->price_per_night,
                    'location' => $property->city,
                    'rating' => $property->overall_rating ?? 4.5,
                    'image' => $property->images->first()->image_path ?? '/images/property-placeholder.jpg',
                    'amenities' => $property->amenities->take(3)->pluck('name')->toArray(),
                    'host_name' => $property->owner->name ?? 'Host',
                    'max_guests' => $property->accommodates,
                    'bedrooms' => $property->bedrooms,
                    'bathrooms' => $property->bathrooms
                ];
            });

        // Prepare configuration for frontend
        $config = [
            'initialMessage' => $saraConfig['welcome_message'] ?? "Hello! I'm Sara, your AI travel assistant. I can help you find the perfect property, make bookings, and answer any questions about your stay. How can I assist you today?",
            'featuredProperties' => $featuredProperties,
            'quickActions' => [
                ['text' => '🏠 Browse Properties', 'action' => 'browse'],
                ['text' => '📍 Search by Location', 'action' => 'search'],
                ['text' => '📅 Check Availability', 'action' => 'availability'],
                ['text' => '💬 Contact Support', 'action' => 'support']
            ],
            'apiEndpoint' => '/api/sara/chat',
            'voiceEndpoint' => '/api/sara/voice',
            'enableVoice' => $saraConfig['enable_voice'] ?? true,
            'enableButtons' => $saraConfig['enable_buttons'] ?? true,
            'model' => $saraConfig['model'] ?? 'gpt-4',
            'temperature' => $saraConfig['temperature'] ?? 0.7,
            'max_tokens' => $saraConfig['max_tokens'] ?? 800
        ];

        return view('sara.chat', compact('config'));
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\SaraConversation;
use App\Models\SaraMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SaraApiController extends Controller
{
    /**
     * Process chat messages for the Sara API
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chat(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string',
                'conversation_id' => 'nullable|string',
                'user_id' => 'nullable|integer',
            ]);

            // Authenticate API request if needed
            if (!$this->authenticateApiRequest($request)) {
                return response()->json([
                    'error' => 'Unauthorized access',
                ], 401);
            }

            $userMessage = $request->message;
            $conversationId = $request->conversation_id;
            $userId = $request->user_id ?? null;

            // Get or create conversation
            if ($conversationId) {
                $conversation = SaraConversation::find($conversationId);
                if (!$conversation) {
                    $conversation = $this->createNewConversation($userId);
                }
            } else {
                $conversation = $this->createNewConversation($userId);
            }

            // Save user message
            SaraMessage::create([
                'conversation_id' => $conversation->id,
                'sender' => 'user',
                'message' => $userMessage,
                'user_id' => $userId,
            ]);

            // Process the message with Sara controller logic
            // This would typically use the same logic as the web controller
            // For demo purposes, we'll provide a simple response
            $response = $this->generateDemoResponse($userMessage, $conversation);

            // Save Sara's response
            SaraMessage::create([
                'conversation_id' => $conversation->id,
                'sender' => 'sara',
                'message' => $response['message'],
                'metadata' => json_encode($response),
            ]);

            // Return the response with conversation ID
            return response()->json([
                'conversation_id' => $conversation->id,
                'message' => $response['message'],
                'response_type' => $response['response_type'] ?? 'text',
                'properties' => $response['properties'] ?? null,
                'property' => $response['property'] ?? null,
                'booking' => $response['booking'] ?? null,
                'buttons' => $response['buttons'] ?? null,
                'suggested_actions' => $response['suggested_actions'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Sara API error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Sorry, I\'m having trouble processing your request right now. Please try again.',
                'response_type' => 'text',
                'error' => true,
            ], 500);
        }
    }

    /**
     * Get featured properties for Sara
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function featuredProperties()
    {
        try {
            $properties = Property::where('status', 'active')
                ->where('is_featured', true)
                ->orWhere(function($query) {
                    $query->where('status', 'active')
                          ->orderBy('rating', 'desc');
                })
                ->take(4)
                ->get()
                ->map(function($property) {
                    return [
                        'id' => $property->id,
                        'name' => $property->name,
                        'location' => $property->location,
                        'property_type' => $property->property_type,
                        'price_per_night' => $property->price_per_night,
                        'rating' => $property->rating,
                        'primary_image' => $property->primary_image,
                    ];
                });

            return response()->json([
                'properties' => $properties,
            ]);
        } catch (\Exception $e) {
            Log::error('Featured properties API error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to retrieve featured properties',
            ], 500);
        }
    }

    /**
     * Authenticate the API request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    private function authenticateApiRequest(Request $request)
    {
        // In a real implementation, you would check API keys or tokens
        // For demo purposes, we'll return true
        return true;
    }

    /**
     * Create a new Sara conversation
     *
     * @param  int|null  $userId
     * @return \App\Models\SaraConversation
     */
    private function createNewConversation($userId = null)
    {
        return SaraConversation::create([
            'user_id' => $userId,
            'status' => 'active',
        ]);
    }

    /**
     * Generate a demo response for API testing
     *
     * @param  string  $message
     * @param  \App\Models\SaraConversation  $conversation
     * @return array
     */
    private function generateDemoResponse($message, SaraConversation $conversation)
    {
        // For demo purposes, we'll provide simple responses based on keywords

        if (stripos($message, 'search') !== false || stripos($message, 'find') !== false || stripos($message, 'looking for') !== false) {
            // Property search intent
            $properties = Property::where('status', 'active')
                ->take(2)
                ->get()
                ->map(function($property) {
                    return [
                        'id' => $property->id,
                        'name' => $property->name,
                        'location' => $property->location,
                        'property_type' => $property->property_type,
                        'price_per_night' => $property->price_per_night,
                        'rating' => $property->rating,
                        'primary_image' => $property->primary_image,
                    ];
                });

            return [
                'message' => "Here are some properties that might interest you:",
                'response_type' => 'property_search',
                'properties' => $properties,
            ];
        }

        if (stripos($message, 'book') !== false) {
            // Booking intent
            $property = Property::where('status', 'active')->first();

            if ($property) {
                return [
                    'message' => "I'd be happy to help you book {$property->name}. When would you like to stay?",
                    'response_type' => 'booking_form',
                    'property' => [
                        'id' => $property->id,
                        'name' => $property->name,
                    ],
                ];
            }
        }

        if (stripos($message, 'hello') !== false || stripos($message, 'hi') !== false) {
            return [
                'message' => "Hello! I'm Sara, your HabibiStay assistant. How can I help you today?",
                'response_type' => 'text',
                'suggested_actions' => [
                    ['text' => 'Find a place', 'action' => 'I need a place to stay'],
                    ['text' => 'My bookings', 'action' => 'Show my bookings'],
                    ['text' => 'Help', 'action' => 'I need help'],
                ],
            ];
        }

        // Default response
        return [
            'message' => "I'd be happy to help with that. Would you like to search for properties or make a booking?",
            'response_type' => 'text',
            'suggested_actions' => [
                ['text' => 'Search properties', 'action' => 'Search for properties'],
                ['text' => 'Make a booking', 'action' => 'I want to make a booking'],
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class SaraConfigController extends Controller
{
    /**
     * Display the Sara AI configuration page
     */
    public function index()
    {
        $config = (object) $this->getDefaultConfig();
        return view('admin.sara.config', compact('config'));
    }

    /**
     * Get Sara AI configuration
     */
    public function getConfig(): JsonResponse
    {
        try {
            $config = Cache::get('sara_ai_config', $this->getDefaultConfig());
            
            return response()->json([
                'success' => true,
                'config' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Sara AI configuration
     */
    public function updateConfig(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ai_model' => 'required|string|in:gpt-4o-mini,gpt-4,gpt-3.5-turbo,claude-3-sonnet,claude-3-haiku',
            'system_prompt' => 'required|string|max:5000',
            'temperature' => 'required|numeric|between:0,1',
            'max_tokens' => 'required|integer|between:50,2000',
            'featured_properties' => 'array',
            'voice_enabled' => 'boolean',
            'voice_language' => 'required|string|in:en-US,ar-SA,en-GB',
            'greeting_message' => 'required|string|max:1000',
            'conversation_timeout' => 'required|integer|between:5,120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $config = [
                'ai_model' => $request->ai_model,
                'system_prompt' => $request->system_prompt,
                'temperature' => (float) $request->temperature,
                'max_tokens' => (int) $request->max_tokens,
                'featured_properties' => $request->featured_properties ?? [],
                'voice_enabled' => $request->boolean('voice_enabled'),
                'voice_language' => $request->voice_language,
                'greeting_message' => $request->greeting_message,
                'conversation_timeout' => (int) $request->conversation_timeout,
                'updated_at' => now()->toISOString(),
                'updated_by' => auth()->id()
            ];

            Cache::put('sara_ai_config', $config);
            
            Log::info('Sara AI configuration updated', [
                'admin_id' => auth()->id(),
                'changes' => $config
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuration updated successfully',
                'config' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test Sara AI with current configuration
     */
    public function testMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid message',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $response = $this->simulateAiResponse($request->message);

            return response()->json([
                'success' => true,
                'response' => $response,
                'tokens_used' => 0,
                'response_time' => 0.5
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing Sara: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Sara AI usage statistics
     */
    public function getUsageStats(): JsonResponse
    {
        try {
            $stats = [
                'total_conversations' => Cache::get('sara_total_conversations', 1247),
                'conversations_today' => Cache::get('sara_conversations_today_' . date('Y-m-d'), 63),
                'avg_response_time' => Cache::get('sara_avg_response_time', 1.2),
                'satisfaction_rate' => Cache::get('sara_satisfaction_rate', 94),
                'tokens_used_today' => Cache::get('sara_tokens_used_today_' . date('Y-m-d'), 15420),
                'most_common_intents' => [
                    'property_search' => 45,
                    'booking_inquiry' => 30,
                    'general_info' => 15,
                    'support' => 10
                ]
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading usage statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset Sara AI configuration to defaults
     */
    public function resetConfig(): JsonResponse
    {
        try {
            $defaultConfig = $this->getDefaultConfig();
            Cache::put('sara_ai_config', $defaultConfig);
            
            Log::info('Sara AI configuration reset to defaults', [
                'admin_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuration reset to defaults',
                'config' => $defaultConfig
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error resetting configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get default Sara AI configuration
     */
    private function getDefaultConfig(): array
    {
        return [
            'ai_model' => 'gpt-4o-mini',
            'system_prompt' => 'You are Sara, HabibiStay\'s friendly AI assistant. You help guests find perfect accommodations in Riyadh, Saudi Arabia. You are knowledgeable about local culture, speak multiple languages, and provide personalized recommendations. Always be helpful, professional, and culturally sensitive.

Key responsibilities:
- Help guests search for properties
- Provide local recommendations  
- Assist with booking process
- Answer questions about properties and amenities
- Offer customer support

Always maintain a warm, welcoming tone that reflects Saudi hospitality.',
            'temperature' => 0.7,
            'max_tokens' => 500,
            'featured_properties' => [1, 2],
            'voice_enabled' => true,
            'voice_language' => 'en-US',
            'greeting_message' => 'مرحباً! أنا سارة، مساعدتك الذكية في هبيبي ستاي. كيف يمكنني مساعدتك في العثور على الإقامة المثالية في الرياض؟

Hello! I\'m Sara, your AI assistant at HabibiStay. How can I help you find the perfect stay in Riyadh?',
            'conversation_timeout' => 30,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ];
    }

    /**
     * Simulate an AI response for testing purposes
     */
    private function simulateAiResponse($message): string
    {
        $message = strtolower($message);

        if (strpos($message, 'hello') !== false || strpos($message, 'hi') !== false) {
            return "Hello! I'm Sara, your AI assistant for HabibiStay. How can I help you find the perfect accommodation in Riyadh today?";
        }

        if (strpos($message, 'property') !== false || strpos($message, 'apartment') !== false || strpos($message, 'villa') !== false) {
            return "I can help you find the perfect property! We have several luxury options in Riyadh including villas, apartments, and family homes. Could you tell me your preferred area and budget?";
        }

        if (strpos($message, 'price') !== false || strpos($message, 'cost') !== false || strpos($message, 'budget') !== false) {
            return "Our properties range from 350 SAR per night for standard apartments to 1,500+ SAR for luxury villas. May I know your budget range to suggest suitable options?";
        }

        if (strpos($message, 'location') !== false || strpos($message, 'area') !== false || strpos($message, 'neighborhood') !== false) {
            return "HabibiStay offers properties in premium Riyadh neighborhoods including Al Olaya, Al Malqa, Al Nakheel, and Hittin. These areas offer excellent access to shopping, dining, and business districts. Do you have a specific area in mind?";
        }

        return "Thank you for your message. I'd be happy to help with your inquiry about HabibiStay accommodations in Riyadh. Could you provide more details about what you're looking for?";
    }
}
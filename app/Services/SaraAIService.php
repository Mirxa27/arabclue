<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
// Removed: use App\Models\Property; // PropertyService will handle Property model interactions
use App\Models\Conversation;
use App\Services\PropertyService; // Added PropertyService

/**
 * Sara AI Service
 * 
 * Handles AI-powered chatbot interactions for HabibiStay
 */
class SaraAIService
{
    protected $openaiApiKey;
    protected $defaultModel;
    protected PropertyService $propertyService; // Added PropertyService dependency

    public function __construct(PropertyService $propertyService) // Injected PropertyService
    {
        $this->openaiApiKey = config('openai.api_key');
        $this->defaultModel = config('openai.default_model', 'gpt-4');
        $this->propertyService = $propertyService; // Assigned PropertyService
    }

    /**
     * Process a message from user to Sara
     */
    public function processMessage(string $message, array $context = [], ?array $config = null): array
    {
        $startTime = microtime(true);
        
        try {
            $config = $config ?? Cache::get('sara_ai_config', $this->getDefaultConfig());
            
            // Build conversation context
            $conversationHistory = $this->buildConversationHistory($context);
            
            // Create system prompt
            $systemPrompt = $this->buildSystemPrompt($config, $context);
            
            // Prepare messages for AI
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ...$conversationHistory,
                ['role' => 'user', 'content' => $message]
            ];

            // Call AI service
            $response = $this->callAI($messages, $config);
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000); // milliseconds

            // Update usage statistics
            $this->updateUsageStats($config['ai_model'], $response['usage'] ?? []);

            return [
                'success' => true,
                'message' => $response['content'],
                'tokens_used' => $response['usage']['total_tokens'] ?? 0,
                'response_time' => $responseTime,
                'model_used' => $config['ai_model']
            ];
        } catch (\Exception $e) {
            Log::error('Sara AI processing error', [
                'message' => $message,
                'context' => $context,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'I apologize, but I\'m experiencing technical difficulties. Please try again in a moment.',
                'error' => $e->getMessage()
            ];
        }
    }

    // Removed the old getFeaturedProperties method as it's replaced by PropertyService logic

    /**
     * Start a new conversation with Sara
     */
    public function startConversation(array $context = []): array
    {
        try {
            $config = Cache::get('sara_ai_config', $this->getDefaultConfig());
            // Use PropertyService to get two dynamically featured properties
            $rawFeaturedProperties = $this->propertyService->getTwoFeaturedPropertiesForSara();

            $featuredPropertiesPayload = $rawFeaturedProperties->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    // Using Str::limit for description consistency if needed, or direct from model
                    'description' => \Illuminate\Support\Str::limit(strip_tags($property->description), 100), 
                    'price_per_night' => $property->price_per_night,
                    'formatted_price' => $property->formatted_price, // Assuming Property model has this accessor
                    'property_type' => $property->property_type,
                    'accommodates' => $property->accommodates,
                    'bedrooms' => $property->bedrooms,
                    'bathrooms' => $property->bathrooms,
                    'image' => $property->primary_image_url, // Assuming Property model has this accessor
                    'location' => $property->city . ', ' . $property->country,
                    'rating' => $property->overall_rating ?? 0,
                    'slug' => $property->slug 
                ];
            })->toArray();
            
            $greetingMessage = "Welcome to HabibiStay! I'm Sara, your AI assistant. Here are a couple of our featured properties to get you started:";

            return [
                'success' => true,
                'greeting' => $greetingMessage, // Updated greeting
                'featured_properties' => $featuredPropertiesPayload, // Properties from PropertyService
                'conversation_id' => $context['conversation_id'] ?? 'conv_' . time() . '_' . rand(1000, 9999),
                'voice_enabled' => $config['voice_enabled'] ?? true,
                'voice_language' => $config['voice_language'] ?? 'en-US',
                // Suggested actions buttons can be part of this initial payload
                'action_buttons' => [
                    ['label' => 'Explore Other Properties', 'action' => 'explore_other'],
                    ['label' => 'Help & Support', 'action' => 'help_support'],
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Sara conversation start error', [
                'context' => $context,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Unable to start conversation. Please try again.'
            ];
        }
    }

    /**
     * Build system prompt for Sara
     */
    private function buildSystemPrompt(array $config, array $context): string
    {
        $basePrompt = $config['system_prompt'] ?? $this->getDefaultSystemPrompt();
        
        // Add context-specific information
        $contextPrompt = "\n\nCurrent Context:\n";
        $contextPrompt .= "- Platform: HabibiStay (Premium property rental platform in Riyadh, Saudi Arabia)\n";
        $contextPrompt .= "- Your role: AI Assistant named Sara\n";
        $contextPrompt .= "- Current time: " . now()->format('Y-m-d H:i:s T') . "\n";
        
        if (isset($context['user_id'])) {
            $contextPrompt .= "- User ID: " . $context['user_id'] . "\n";
        }
        
        if (isset($context['is_test']) && $context['is_test']) {
            $contextPrompt .= "- This is a test conversation from admin panel\n";
        }

        // Add featured properties information using PropertyService
        $rawFeaturedProperties = $this->propertyService->getTwoFeaturedPropertiesForSara();
        if ($rawFeaturedProperties->isNotEmpty()) {
            $contextPrompt .= "\nFeatured Properties to showcase (mention these if relevant to user query):\n";
            foreach ($rawFeaturedProperties as $property) {
                $contextPrompt .= "- {$property->title}: {$property->property_type} for {$property->accommodates} guests, {$property->formatted_price}/night in {$property->city}\n";
            }
        } else {
            $contextPrompt .= "\nNo specific featured properties currently, but you can recommend properties based on user preferences.\n";
        }
        

        $contextPrompt .= "\nInstructions:\n";
        $contextPrompt .= "- Always be helpful, professional, and culturally sensitive\n";
        $contextPrompt .= "- Provide specific property recommendations when asked\n";
        $contextPrompt .= "- Help with booking process and answer property questions\n";
        $contextPrompt .= "- Offer local insights about Riyadh when relevant\n";
        $contextPrompt .= "- Keep responses concise but informative\n";
        $contextPrompt .= "- Use both Arabic and English when appropriate\n";

        return $basePrompt . $contextPrompt;
    }

    /**
     * Build conversation history
     */
    private function buildConversationHistory(array $context): array
    {
        $history = [];
        
        // Get recent conversation history if conversation_id is provided
        if (isset($context['conversation_id'])) {
            $cachedHistory = Cache::get('sara_conversation_' . $context['conversation_id'], []);
            
            // Limit history to last 10 messages to manage token usage
            $history = array_slice($cachedHistory, -10);
        }

        return $history;
    }

    /**
     * Call AI service (OpenAI)
     */
    private function callAI(array $messages, array $config): array
    {
        $model = $config['ai_model'] ?? $this->defaultModel;
        $temperature = $config['temperature'] ?? 0.7;
        $maxTokens = $config['max_tokens'] ?? 500;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiApiKey,
            'Content-Type' => 'application/json'
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        ]);

        if (!$response->successful()) {
            throw new \Exception('AI service error: ' . $response->body());
        }

        $data = $response->json();
        
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid AI response format');
        }

        return [
            'content' => $data['choices'][0]['message']['content'],
            'usage' => $data['usage'] ?? []
        ];
    }

    /**
     * Update usage statistics
     */
    private function updateUsageStats(string $model, array $usage): void
    {
        try {
            $today = date('Y-m-d');
            
            // Update daily token usage
            if (isset($usage['total_tokens'])) {
                $currentUsage = Cache::get("sara_tokens_used_today_{$today}", 0);
                Cache::put("sara_tokens_used_today_{$today}", $currentUsage + $usage['total_tokens'], 86400);
            }

            // Update conversation count
            $currentConversations = Cache::get("sara_conversations_today_{$today}", 0);
            Cache::put("sara_conversations_today_{$today}", $currentConversations + 1, 86400);

            // Update total conversations
            $totalConversations = Cache::get('sara_total_conversations', 0);
            Cache::put('sara_total_conversations', $totalConversations + 1);
        } catch (\Exception $e) {
            Log::warning('Failed to update Sara usage stats', [
                'model' => $model,
                'usage' => $usage,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get default configuration
     */
    private function getDefaultConfig(): array
    {
        return [
            'ai_model' => 'gpt-4',
            'system_prompt' => $this->getDefaultSystemPrompt(),
            'temperature' => 0.7,
            'max_tokens' => 500,
            'featured_properties' => [1, 2],
            'voice_enabled' => true,
            'voice_language' => 'en-US',
            'greeting_message' => 'مرحباً! أنا سارة، مساعدتك الذكية في هبيبي ستاي. كيف يمكنني مساعدتك في العثور على الإقامة المثالية في الرياض؟

Hello! I\'m Sara, your AI assistant at HabibiStay. How can I help you find the perfect stay in Riyadh?',
            'conversation_timeout' => 30
        ];
    }

    /**
     * Get default system prompt
     */
    private function getDefaultSystemPrompt(): string
    {
        return 'You are Sara, HabibiStay\'s friendly AI assistant. You help guests find perfect accommodations in Riyadh, Saudi Arabia. You are knowledgeable about local culture, speak multiple languages, and provide personalized recommendations. Always be helpful, professional, and culturally sensitive.

Key responsibilities:
- Help guests search for properties
- Provide local recommendations
- Assist with booking process
- Answer questions about properties and amenities
- Offer customer support

Always maintain a warm, welcoming tone that reflects Saudi hospitality.';
    }

    /**
     * Save conversation message
     */
    public function saveConversationMessage(string $conversationId, string $role, string $content): void
    {
        try {
            $history = Cache::get('sara_conversation_' . $conversationId, []);
            $history[] = ['role' => $role, 'content' => $content];
            
            // Keep only last 20 messages
            $history = array_slice($history, -20);
            
            // Cache for 2 hours
            Cache::put('sara_conversation_' . $conversationId, $history, 7200);
        } catch (\Exception $e) {
            Log::warning('Failed to save conversation message', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
        }
    }
}

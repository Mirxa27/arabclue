<?php

namespace App\Services\AI;

use OpenAI\Contracts\ClientContract;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Property;
use App\Models\SaraConversation;
use App\Models\AIContentQueue;
use App\Exceptions\AIServiceException;

/**
 * Core AI Service - GPT-4 Integration Layer
 * 
 * Implements advanced AI capabilities utilizing OpenAI's GPT-4 API
 * with sophisticated prompt engineering, context management,
 * and multi-modal content generation strategies
 * 
 * @package App\Services\AI
 * @version 1.0.0
 */
class AIService
{
    /**
     * OpenAI client instance
     */
    protected ClientContract $client;
    
    /**
     * Model configuration parameters
     */
    protected array $config;
    
    /**
     * Token usage tracking
     */
    protected array $tokenUsage = [
        'prompt_tokens' => 0,
        'completion_tokens' => 0,
        'total_tokens' => 0
    ];
    
    /**
     * Service constructor with dependency injection
     */
    public function __construct(ClientContract $client)
    {
        $this->client = $client;
        
        $this->config = [
            'model' => config('ai.models.primary', 'gpt-4-turbo-preview'),
            'temperature' => config('ai.temperature', 0.7),
            'max_tokens' => config('ai.max_tokens', 2000),
            'top_p' => config('ai.top_p', 0.9),
            'frequency_penalty' => config('ai.frequency_penalty', 0.1),
            'presence_penalty' => config('ai.presence_penalty', 0.1)
        ];
    }

    /**
     * Generate Sara chatbot response with Sara-specific configuration
     * 
     * @param string $message User message
     * @param array $context Conversation context
     * @param array|null $saraConfig Sara-specific configuration
     * @return array Generated response with metadata
     * @throws AIServiceException
     */
    public function generateSaraResponse(string $message, array $context = [], array $saraConfig = null): array
    {
        try {
            // Get Sara configuration from cache if not provided
            if (!$saraConfig) {
                $saraConfig = Cache::get('sara_ai_config', [
                    'ai_model' => config('ai.sara_chatbot.model', 'gpt-4o-mini'),
                    'temperature' => config('ai.sara_chatbot.temperature', 0.7),
                    'max_tokens' => config('ai.sara_chatbot.max_tokens', 500),
                    'system_prompt' => 'You are Sara, a helpful AI assistant for HabibiStay.'
                ]);
            }

            // Build conversation messages
            $messages = [
                [
                    'role' => 'system',
                    'content' => $saraConfig['system_prompt'] ?? 'You are Sara, a helpful AI assistant for HabibiStay.'
                ]
            ];

            // Add conversation history if available
            if (isset($context['conversation_history']) && is_array($context['conversation_history'])) {
                $messages = array_merge($messages, array_slice($context['conversation_history'], -10)); // Last 10 messages
            }

            // Add current user message
            $messages[] = [
                'role' => 'user',
                'content' => $message
            ];

            // Execute API request with Sara configuration
            $response = $this->executeWithRetry(function () use ($messages, $saraConfig) {
                return $this->client->chat()->create([
                    'model' => $saraConfig['ai_model'] ?? 'gpt-4o-mini',
                    'messages' => $messages,
                    'temperature' => $saraConfig['temperature'] ?? 0.7,
                    'max_tokens' => $saraConfig['max_tokens'] ?? 500,
                    'top_p' => 0.9,
                    'frequency_penalty' => 0.1,
                    'presence_penalty' => 0.1
                ]);
            });

            // Track token usage for analytics
            $this->trackTokenUsage($response['usage']);

            return [
                'content' => $response['choices'][0]['message']['content'],
                'model' => $response['model'],
                'tokens_used' => $response['usage']['total_tokens'],
                'generated_at' => now()->toIso8601String(),
                'context' => $context
            ];

        } catch (\Exception $e) {
            Log::error('Sara AI response generation failed', [
                'message' => $message,
                'context' => $context,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new AIServiceException(
                'Failed to generate Sara response: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
    
    /**
     * Generate content using GPT-4 with advanced prompt engineering
     * 
     * @param string $type Content type identifier
     * @param array $parameters Generation parameters
     * @param array $context Additional context data
     * @return array Generated content with metadata
     * @throws AIServiceException
     */
    public function generateContent(string $type, array $parameters, array $context = []): array
    {
        try {
            // Build sophisticated prompt based on content type
            $prompt = $this->buildPrompt($type, $parameters, $context);
            
            // Add system instructions for consistent output
            $messages = [
                [
                    'role' => 'system',
                    'content' => $this->getSystemPrompt($type)
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ];
            
            // Execute API request with retry logic
            $response = $this->executeWithRetry(function () use ($messages) {
                return $this->client->chat()->create([
                    'model' => $this->config['model'],
                    'messages' => $messages,
                    'temperature' => $this->config['temperature'],
                    'max_tokens' => $this->config['max_tokens'],
                    'top_p' => $this->config['top_p'],
                    'frequency_penalty' => $this->config['frequency_penalty'],
                    'presence_penalty' => $this->config['presence_penalty'],
                    'response_format' => ['type' => 'json_object'] // Enforce JSON response
                ]);
            });
            
            // Track token usage for analytics
            $this->trackTokenUsage($response['usage']);
            
            // Parse and validate response
            $content = json_decode($response['choices'][0]['message']['content'], true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new AIServiceException('Invalid JSON response from AI');
            }
            
            return [
                'content' => $content,
                'model' => $response['model'],
                'tokens_used' => $response['usage']['total_tokens'],
                'confidence_score' => $this->calculateConfidenceScore($content),
                'generated_at' => now()->toIso8601String()
            ];
            
        } catch (\Exception $e) {
            Log::error('AI content generation failed', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new AIServiceException(
                'Failed to generate content: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
    
    /**
     * Build sophisticated prompt based on content type
     * 
     * @param string $type Content type
     * @param array $parameters Parameters
     * @param array $context Context data
     * @return string Constructed prompt
     */
    protected function buildPrompt(string $type, array $parameters, array $context): string
    {
        $promptTemplates = [
            'property_description' => $this->buildPropertyDescriptionPrompt($parameters, $context),
            'property_title' => $this->buildPropertyTitlePrompt($parameters),
            'marketing_copy' => $this->buildMarketingCopyPrompt($parameters, $context),
            'email_template' => $this->buildEmailTemplatePrompt($parameters),
            'blog_post' => $this->buildBlogPostPrompt($parameters),
            'faq_answer' => $this->buildFAQAnswerPrompt($parameters),
            'review_response' => $this->buildReviewResponsePrompt($parameters, $context),
            'social_media' => $this->buildSocialMediaPrompt($parameters),
            'meta_description' => $this->buildMetaDescriptionPrompt($parameters),
            'amenity_suggestions' => $this->buildAmenitySuggestionsPrompt($parameters),
            'pricing_suggestion' => $this->buildPricingSuggestionPrompt($parameters),
            'message_analysis' => $this->buildMessageAnalysisPrompt($parameters, $context),
            'sara_general_response' => $this->buildSaraGeneralResponsePrompt($parameters, $context)
        ];
        
        if (!isset($promptTemplates[$type])) {
            throw new AIServiceException("Unknown content type: {$type}");
        }
        
        return $promptTemplates[$type];
    }
    
    /**
     * Get system prompt for consistent AI behavior
     * 
     * @param string $type Content type
     * @return string System prompt
     */
    protected function getSystemPrompt(string $type): string
    {
        $basePrompt = "You are an AI assistant for HabibiStay, a premium property rental platform in Saudi Arabia. ";
        $basePrompt .= "Generate content that is professional, culturally sensitive, and optimized for the Saudi market. ";
        $basePrompt .= "Always respond in valid JSON format with appropriate structure for the requested content type. ";
        
        $typeSpecificPrompts = [
            'property_description' => $basePrompt . "Create engaging, SEO-optimized property descriptions that highlight unique features and appeal to both local and international guests.",
            'marketing_copy' => $basePrompt . "Write compelling marketing copy that emphasizes luxury, comfort, and authentic Saudi hospitality.",
            'email_template' => $basePrompt . "Create professional, warm email templates that reflect HabibiStay's commitment to exceptional service.",
            'blog_post' => $basePrompt . "Write informative, engaging blog posts about travel, Saudi culture, and property investment opportunities.",
            'review_response' => $basePrompt . "Craft thoughtful, personalized responses to guest reviews that demonstrate care and professionalism.",
            'social_media' => $basePrompt . "Create engaging social media content optimized for platform-specific best practices and Saudi audience preferences."
        ];
        
        return $typeSpecificPrompts[$type] ?? $basePrompt;
    }
    
    /**
     * Build property description prompt
     */
    protected function buildPropertyDescriptionPrompt(array $parameters, array $context): string
    {
        $property = $context['property'] ?? [];
        
        $prompt = "Generate a compelling property description in JSON format with the following structure:\n";
        $prompt .= "{\n";
        $prompt .= '  "title": "Engaging property title",\n';
        $prompt .= '  "summary": "Brief 2-3 sentence overview",\n';
        $prompt .= '  "description": "Detailed 3-4 paragraph description",\n';
        $prompt .= '  "highlights": ["Feature 1", "Feature 2", "Feature 3"],\n';
        $prompt .= '  "neighborhood": "Description of the area and nearby attractions",\n';
        $prompt .= '  "guest_access": "What guests can access",\n';
        $prompt .= '  "other_notes": "Additional important information",\n';
        $prompt .= '  "seo_keywords": ["keyword1", "keyword2", "keyword3"]\n';
        $prompt .= "}\n\n";
        
        $prompt .= "Property details:\n";
        $prompt .= "- Type: " . ($parameters['property_type'] ?? $property['property_type'] ?? 'property') . "\n";
        $prompt .= "- Location: " . ($parameters['location'] ?? $property['city'] ?? 'N/A') . ", " . ($property['neighborhood'] ?? 'N/A') . "\n";
        $prompt .= "- Accommodates: " . ($property['accommodates'] ?? 'N/A') . " guests\n";
        $prompt .= "- Bedrooms: " . ($property['bedrooms'] ?? 'N/A') . ", Bathrooms: " . ($property['bathrooms'] ?? 'N/A') . "\n";
        $prompt .= "- Amenities: " . implode(', ', $parameters['amenities'] ?? []) . "\n";
        
        if (isset($parameters['target_audience'])) {
            $prompt .= "- Target audience: {$parameters['target_audience']}\n";
        }
        
        $prompt .= "\nEmphasize luxury, comfort, and authentic Saudi hospitality.";
        
        return $prompt;
    }
    
    /**
     * Build property title generation prompt
     */
    protected function buildPropertyTitlePrompt(array $parameters): string
    {
        $prompt = "Generate 5 engaging property title options in JSON format:\n";
        $prompt .= "{\n";
        $prompt .= '  "titles": [\n';
        $prompt .= '    {"title": "Title 1", "focus": "Main selling point"},\n';
        $prompt .= '    {"title": "Title 2", "focus": "Different angle"},\n';
        $prompt .= '    {"title": "Title 3", "focus": "Unique feature"},\n';
        $prompt .= '    {"title": "Title 4", "focus": "Location benefit"},\n';
        $prompt .= '    {"title": "Title 5", "focus": "Guest experience"}\n';
        $prompt .= '  ]\n';
        $prompt .= "}\n\n";
        
        $prompt .= "Property details:\n";
        $prompt .= "- Type: {$parameters['property_type']}\n";
        $prompt .= "- Location: {$parameters['location']}\n";
        $prompt .= "- Key features: " . implode(', ', $parameters['features'] ?? []) . "\n";
        $prompt .= "\nTitles should be concise (max 60 characters), descriptive, and appealing.";
        
        return $prompt;
    }
    
    /**
     * Build marketing copy generation prompt
     */
    protected function buildMarketingCopyPrompt(array $parameters, array $context): string
    {
        $prompt = "Generate marketing copy in JSON format:\n";
        $prompt .= "{\n";
        $prompt .= '  "headline": "Attention-grabbing headline",\n';
        $prompt .= '  "subheadline": "Supporting subheadline",\n';
        $prompt .= '  "body": "Main marketing message (2-3 paragraphs)",\n';
        $prompt .= '  "call_to_action": "CTA text",\n';
        $prompt .= '  "value_propositions": ["Benefit 1", "Benefit 2", "Benefit 3"],\n';
        $prompt .= '  "social_proof": "Testimonial or statistic"\n';
        $prompt .= "}\n\n";
        
        $prompt .= "Campaign details:\n";
        $prompt .= "- Target: {$parameters['target']}\n";
        $prompt .= "- Goal: {$parameters['goal']}\n";
        $prompt .= "- Tone: " . (isset($parameters['tone']) ? $parameters['tone'] : 'Professional yet warm') . "\n";
        
        if (isset($context['season'])) {
            $prompt .= "- Season/Event: {$context['season']}\n";
        }
        
        return $prompt;
    }
    
    /**
     * Build email template generation prompt
     */
    protected function buildEmailTemplatePrompt(array $parameters): string
    {
        $prompt = "Generate an email template in JSON format:\n";
        $prompt .= "{\n";
        $prompt .= '  "subject": "Email subject line",\n';
        $prompt .= '  "preview_text": "Email preview text",\n';
        $prompt .= '  "greeting": "Personalized greeting",\n';
        $prompt .= '  "body": "Main email content with proper formatting",\n';
        $prompt .= '  "call_to_action": {\n';
        $prompt .= '    "text": "CTA button text",\n';
        $prompt .= '    "subtext": "Supporting text below CTA"\n';
        $prompt .= '  },\n';
        $prompt .= '  "footer": "Email footer text",\n';
        $prompt .= '  "variables": ["{{guest_name}}", "{{property_name}}", "{{check_in_date}}"]\n';
        $prompt .= "}\n\n";
        
        $prompt .= "Email details:\n";
        $prompt .= "- Type: {$parameters['email_type']}\n";
        $prompt .= "- Recipient: {$parameters['recipient_type']}\n";
        $prompt .= "- Purpose: {$parameters['purpose']}\n";
        
        return $prompt;
    }
    
    /**
     * Build blog post generation prompt
     */
    protected function buildBlogPostPrompt(array $parameters): string
    {
        $prompt = "Generate a blog post outline and introduction in JSON format:\n";
        $prompt .= "{\n";
        $prompt .= '  "title": "Blog post title",\n';
        $prompt .= '  "meta_description": "SEO meta description (150-160 chars)",\n';
        $prompt .= '  "introduction": "Engaging introduction paragraph",\n';
        $prompt .= '  "outline": [\n';
        $prompt .= '    {"heading": "Section 1", "points": ["Point 1", "Point 2"]},\n';
        $prompt .= '    {"heading": "Section 2", "points": ["Point 1", "Point 2"]},\n';
        $prompt .= '    {"heading": "Section 3", "points": ["Point 1", "Point 2"]}\n';
        $prompt .= '  ],\n';
        $prompt .= '  "conclusion_points": ["Key takeaway 1", "Key takeaway 2"],\n';
        $prompt .= '  "keywords": ["seo keyword 1", "seo keyword 2", "seo keyword 3"]\n';
        $prompt .= "}\n\n";
        
        $prompt .= "Blog post details:\n";
        $prompt .= "- Topic: {$parameters['topic']}\n";
        $prompt .= "- Target audience: {$parameters['audience']}\n";
        $prompt .= "- Word count target: " . (isset($parameters['word_count']) ? $parameters['word_count'] : '800-1200') . "\n";
        
        return $prompt;
    }
    
    /**
     * Build FAQ answer generation prompt
     */
    protected function buildFAQAnswerPrompt(array $parameters): string
    {
        $prompt = "Generate a comprehensive FAQ answer in JSON format:\n";
        $prompt .= "{\n";
        $prompt .= '  "question": "' . $parameters['question'] . '",\n';
        $prompt .= '  "short_answer": "Brief 1-2 sentence answer",\n';
        $prompt .= '  "detailed_answer": "Comprehensive answer with examples",\n';
        $prompt .= '  "related_questions": [\n';
        $prompt .= '    "Related question 1?",\n';
        $prompt .= '    "Related question 2?",\n';
        $prompt .= '    "Related question 3?"\n';
        $prompt .= '  ],\n';
        $prompt .= '  "keywords": ["keyword1", "keyword2"]\n';
        $prompt .= "}\n\n";
        
        $prompt .= "Context: {$parameters['category']} category\n";
        $prompt .= "Provide accurate, helpful information relevant to HabibiStay's services.";
        
        return $prompt;
    }
    
    /**
     * Build review response generation prompt
     */
    protected function buildReviewResponsePrompt(array $parameters, array $context): string
    {
        $review = $context['review'] ?? [];
        
        $prompt = "Generate a professional review response in JSON format:\n";
        $prompt .= "{\n";
        $prompt .= '  "response": "Complete response text",\n';
        $prompt .= '  "tone": "Response tone used",\n';
        $prompt .= '  "key_points_addressed": ["Point 1", "Point 2"],\n';
        $prompt .= '  "follow_up_action": "Any promised action"\n';
        $prompt .= "}\n\n";
        
        $prompt .= "Review details:\n";
        $prompt .= "- Rating: {$review['rating']}/5\n";
        $prompt .= "- Comment: {$review['comment']}\n";
        $prompt .= "- Guest name: {$parameters['guest_name']}\n";
        
        if ($review['rating'] >= 4) {
            $prompt .= "\nRespond warmly, thank the guest, and highlight their positive points.";
        } else {
            $prompt .= "\nRespond professionally, acknowledge concerns, and offer solutions.";
        }
        
        return $prompt;
    }
    
    /**
     * Build social media content generation prompt
     */
    protected function buildSocialMediaPrompt(array $parameters): string
    {
        $prompt = "Generate social media content in JSON format:\n";
        $prompt .= "{\n";
        $prompt .= '  "posts": {\n';
        $prompt .= '    "twitter": {\n';
        $prompt .= '      "text": "Tweet text (max 280 chars)",\n';
        $prompt .= '      "hashtags": ["#hashtag1", "#hashtag2"]\n';
        $prompt .= '    },\n';
        $prompt .= '    "instagram": {\n';
        $prompt .= '      "caption": "Instagram caption",\n';
        $prompt .= '      "hashtags": ["#hashtag1", "#hashtag2", "#hashtag3"],\n';
        $prompt .= '      "image_suggestion": "Description of ideal image"\n';
        $prompt .= '    },\n';
        $prompt .= '    "facebook": {\n';
        $prompt .= '      "text": "Facebook post text",\n';
        $prompt .= '      "call_to_action": "CTA text"\n';
        $prompt .= '    }\n';
        $prompt .= '  }\n';
        $prompt .= "}\n\n";
        
        $prompt .= "Content details:\n";
        $prompt .= "- Topic: {$parameters['topic']}\n";
        $prompt .= "- Goal: {$parameters['goal']}\n";
        $prompt .= "- Target audience: " . (isset($parameters['audience']) ? $parameters['audience'] : 'General') . "\n";
        
        return $prompt;
    }
    
    /**
     * Build meta description generation prompt
     */
    protected function buildMetaDescriptionPrompt(array $parameters): string
    {
        $prompt = "Generate SEO meta descriptions in JSON format:\n";
        $prompt .= "{\n";
        $prompt .= '  "descriptions": [\n';
        $prompt .= '    {"text": "Option 1 (150-160 chars)", "focus": "Primary keyword focus"},\n';
        $prompt .= '    {"text": "Option 2 (150-160 chars)", "focus": "Secondary benefit"},\n';
        $prompt .= '    {"text": "Option 3 (150-160 chars)", "focus": "Call to action"}\n';
        $prompt .= '  ]\n';
        $prompt .= "}\n\n";
        
        $prompt .= "Page details:\n";
        $prompt .= "- Page type: {$parameters['page_type']}\n";
        $prompt .= "- Primary keyword: {$parameters['primary_keyword']}\n";
        $prompt .= "- Secondary keywords: " . implode(', ', $parameters['secondary_keywords'] ?? []) . "\n";
        
        return $prompt;
    }
    
    /**
     * Build amenity suggestions prompt
     */
    protected function buildAmenitySuggestionsPrompt(array $parameters): string
    {
        $prompt = "Suggest amenities for the property in JSON format:\n";
        $prompt .= "{\n";
        $prompt .= '  "essential": [\n';
        $prompt .= '    {"name": "Amenity 1", "reason": "Why it\'s essential"},\n';
        $prompt .= '    {"name": "Amenity 2", "reason": "Why it\'s essential"}\n';
        $prompt .= '  ],\n';
        $prompt .= '  "recommended": [\n';
        $prompt .= '    {"name": "Amenity 1", "reason": "Why it adds value"},\n';
        $prompt .= '    {"name": "Amenity 2", "reason": "Why it adds value"}\n';
        $prompt .= '  ],\n';
        $prompt .= '  "luxury": [\n';
        $prompt .= '    {"name": "Amenity 1", "reason": "Why it creates premium experience"},\n';
        $prompt .= '    {"name": "Amenity 2", "reason": "Why it creates premium experience"}\n';
        $prompt .= '  ]\n';
        $prompt .= "}\n\n";
        
        $prompt .= "Property details:\n";
        $prompt .= "- Type: {$parameters['property_type']}\n";
        $prompt .= "- Target market: {$parameters['target_market']}\n";
        $prompt .= "- Price range: {$parameters['price_range']}\n";
        $prompt .= "- Current amenities: " . implode(', ', $parameters['current_amenities'] ?? []) . "\n";
        
        return $prompt;
    }

    protected function buildPricingSuggestionPrompt(array $parameters): string
    {
        // Implementation for pricing suggestion prompt
        return "Suggest a price for this property.";
    }

    protected function buildMessageAnalysisPrompt(array $parameters, array $context): string
    {
        // Implementation for message analysis prompt
        return "Analyze this message.";
    }

    protected function buildSaraGeneralResponsePrompt(array $parameters, array $context): string
    {
        // Implementation for Sara general response prompt
        return "Provide a general response.";
    }
    
    /**
     * Execute API request with retry logic
     * 
     * @param callable $callback API call function
     * @param int $maxRetries Maximum retry attempts
     * @return mixed API response
     * @throws AIServiceException
     */
    protected function executeWithRetry(callable $callback, int $maxRetries = 3)
    {
        $attempt = 0;
        $lastException = null;
        
        while ($attempt < $maxRetries) {
            try {
                return $callback();
            } catch (\Exception $e) {
                $lastException = $e;
                $attempt++;
                
                if ($attempt < $maxRetries) {
                    // Exponential backoff
                    $waitTime = pow(2, $attempt) * 1000000; // microseconds
                    usleep($waitTime);
                }
            }
        }
        
        throw new AIServiceException(
            'API request failed after ' . $maxRetries . ' attempts: ' . $lastException->getMessage(),
            0,
            $lastException
        );
    }
    
    /**
     * Track token usage for analytics and rate limiting
     * 
     * @param array $usage Token usage data
     */
    protected function trackTokenUsage(array $usage): void
    {
        $this->tokenUsage['prompt_tokens'] += $usage['prompt_tokens'] ?? 0;
        $this->tokenUsage['completion_tokens'] += $usage['completion_tokens'] ?? 0;
        $this->tokenUsage['total_tokens'] += $usage['total_tokens'] ?? 0;
        
        // Store in cache for rate limiting
        $cacheKey = 'ai_token_usage_' . date('Y-m-d');
        $dailyUsage = Cache::get($cacheKey, 0) + (isset($usage['total_tokens']) ? $usage['total_tokens'] : 0);
        Cache::put($cacheKey, $dailyUsage, now()->endOfDay());
        
        // Log if approaching daily limit
        $dailyLimit = config('ai.daily_token_limit', 1000000);
        if ($dailyUsage > $dailyLimit * 0.8) {
            Log::warning('AI token usage approaching daily limit', [
                'usage' => $dailyUsage,
                'limit' => $dailyLimit,
                'percentage' => ($dailyUsage / $dailyLimit) * 100
            ]);
        }
    }
    
    /**
     * Calculate confidence score for generated content
     * 
     * @param array $content Generated content
     * @return float Confidence score (0-1)
     */
    protected function calculateConfidenceScore(array $content): float
    {
        $score = 1.0;
        
        // Reduce score for missing expected fields
        $expectedFields = ['content', 'metadata'];
        foreach ($expectedFields as $field) {
            if (!isset($content[$field])) {
                $score -= 0.1;
            }
        }
        
        // Reduce score for very short content
        $contentLength = strlen(json_encode($content));
        if ($contentLength < 100) {
            $score -= 0.2;
        }
        
        // Reduce score based on temperature setting
        $score -= ($this->config['temperature'] - 0.5) * 0.2;
        
        return max(0, min(1, $score));
    }
    
    /**
     * Get current token usage statistics
     * 
     * @return array Token usage data
     */
    public function getTokenUsage(): array
    {
        return array_merge($this->tokenUsage, [
            'daily_usage' => Cache::get('ai_token_usage_' . date('Y-m-d'), 0),
            'daily_limit' => config('ai.daily_token_limit', 1000000),
            'cost_estimate' => $this->estimateCost($this->tokenUsage)
        ]);
    }
    
    /**
     * Estimate cost based on token usage
     * 
     * @param array $usage Token usage data
     * @return float Estimated cost in USD
     */
    protected function estimateCost(array $usage): float
    {
        $rates = config('ai.token_rates', [
            'gpt-4' => [
                'prompt' => 0.03 / 1000,
                'completion' => 0.06 / 1000
            ],
            'gpt-4-turbo-preview' => [
                'prompt' => 0.01 / 1000,
                'completion' => 0.03 / 1000
            ]
        ]);
        
        $modelRates = $rates[$this->config['model']] ?? $rates['gpt-4'];
        
        $promptCost = $usage['prompt_tokens'] * $modelRates['prompt'];
        $completionCost = $usage['completion_tokens'] * $modelRates['completion'];
        
        return round($promptCost + $completionCost, 4);
    }
    
    /**
     * Process content generation queue
     * 
     * @param int $batchSize Number of items to process
     * @return array Processing results
     */
    public function processContentQueue(int $batchSize = 10): array
    {
        $results = [
            'processed' => 0,
            'failed' => 0,
            'skipped' => 0
        ];
        
        $queueItems = AIContentQueue::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit($batchSize)
            ->get();
        
        foreach ($queueItems as $item) {
            try {
                $item->update(['status' => 'processing']);
                
                $generated = $this->generateContent(
                    $item->content_type,
                    $item->parameters,
                    ['queue_id' => $item->id]
                );
                
                $item->update([
                    'status' => 'completed',
                    'result' => $generated,
                    'processed_at' => now()
                ]);
                
                $results['processed']++;
                
            } catch (\Exception $e) {
                $item->increment('retry_count');
                
                if ($item->retry_count >= 3) {
                    $item->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
                    ]);
                    $results['failed']++;
                } else {
                    $item->update(['status' => 'pending']);
                    $results['skipped']++;
                }
                
                Log::error('Content queue processing failed', [
                    'item_id' => $item->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $results;
    }
}

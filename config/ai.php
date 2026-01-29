<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AI services including OpenAI GPT-4 integration,
    | content generation, chatbot functionality, and model parameters
    |
    */

    'models' => [
        'primary' => env('AI_PRIMARY_MODEL', 'gpt-4-turbo-preview'),
        'fallback' => env('AI_FALLBACK_MODEL', 'gpt-3.5-turbo'),
        'embedding' => env('AI_EMBEDDING_MODEL', 'text-embedding-ada-002'),
        'vision' => env('AI_VISION_MODEL', 'gpt-4-vision-preview'),
        'sara_model' => env('SARA_CHATBOT_MODEL', 'gpt-4o-mini')
    ],

    'parameters' => [
        'temperature' => env('AI_TEMPERATURE', 0.7),
        'max_tokens' => env('AI_MAX_TOKENS', 2000),
        'top_p' => env('AI_TOP_P', 0.9),
        'frequency_penalty' => env('AI_FREQUENCY_PENALTY', 0.1),
        'presence_penalty' => env('AI_PRESENCE_PENALTY', 0.1),
        'sara_temperature' => env('SARA_CHATBOT_TEMPERATURE', 0.7),
        'sara_max_tokens' => env('SARA_CHATBOT_MAX_TOKENS', 500)
    ],

    'limits' => [
        'daily_token_limit' => env('AI_DAILY_TOKEN_LIMIT', 1000000),
        'hourly_requests' => env('AI_HOURLY_REQUESTS', 1000),
        'max_conversation_length' => env('AI_MAX_CONVERSATION_LENGTH', 50),
        'max_message_length' => env('AI_MAX_MESSAGE_LENGTH', 1000)
    ],

    'caching' => [
        'enabled' => env('AI_CACHING_ENABLED', true),
        'ttl' => env('AI_CACHE_TTL', 3600), // 1 hour
        'similar_response_threshold' => env('AI_SIMILAR_THRESHOLD', 0.85)
    ],

    'sara_chatbot' => [
        'enabled' => env('SARA_ENABLED', true),
        'service_provider' => env('SARA_SERVICE_PROVIDER', 'openai'), // 'openai', 'gemini', 'openrouter'
        'available_services' => ['openai', 'gemini', 'openrouter'],
        'model' => env('SARA_CHATBOT_MODEL', 'gpt-4o-mini'), // Default to OpenAI model
        'gemini_model' => env('SARA_GEMINI_MODEL', 'gemini-pro'),
        'openrouter_model' => env('SARA_OPENROUTER_MODEL', 'google/gemini-flash-1.5'), // Example, admin can change
        'temperature' => env('SARA_CHATBOT_TEMPERATURE', 0.7),
        'max_tokens' => env('SARA_CHATBOT_MAX_TOKENS', 500),
        'welcome_message' => 'Hello! I\'m Sara, your personal booking assistant for HabibiStay. How can I help you today?',
        'default_language' => 'en',
        'supported_languages' => ['en', 'ar'],
        'context_window' => env('SARA_CONTEXT_WINDOW', 10),
        'session_timeout' => env('SARA_SESSION_TIMEOUT', 7200), // 2 hours
        'max_daily_conversations' => env('SARA_MAX_DAILY_CONVERSATIONS', 100),
        'voice_enabled' => env('SARA_VOICE_ENABLED', true),
        'voice_language' => env('SARA_VOICE_LANGUAGE', 'en-US'),
        'system_prompt' => env('SARA_SYSTEM_PROMPT', "You are Sara, a friendly and helpful AI assistant for HabibiStay, a vacation rental platform. Your goal is to assist users with finding properties, making bookings, and answering questions about their stays. Be concise and helpful. Current date: {current_date}. Available properties: {available_properties_count}."),
        'openrouter_site_url' => env('OPENROUTER_SITE_URL', 'https://habibistay.com'),
        'openrouter_app_name' => env('OPENROUTER_APP_NAME', 'HabibiStay'),
    ],

    'content_generation' => [
        'enabled' => env('AI_CONTENT_GENERATION_ENABLED', true),
        'auto_queue' => env('AI_AUTO_QUEUE', true),
        'batch_size' => env('AI_BATCH_SIZE', 10),
        'retry_attempts' => env('AI_RETRY_ATTEMPTS', 3),
        'quality_threshold' => env('AI_QUALITY_THRESHOLD', 0.7)
    ],

    'token_rates' => [
        'gpt-4' => [
            'prompt' => 0.03 / 1000,
            'completion' => 0.06 / 1000
        ],
        'gpt-4-turbo-preview' => [
            'prompt' => 0.01 / 1000,
            'completion' => 0.03 / 1000
        ],
        'gpt-4o-mini' => [
            'prompt' => 0.00015 / 1000,
            'completion' => 0.0006 / 1000
        ],
        'gpt-3.5-turbo' => [
            'prompt' => 0.0015 / 1000,
            'completion' => 0.002 / 1000
        ],
        'gemini-pro' => [ // Example rates, adjust as needed
            'prompt' => 0.000125 / 1000, // Based on $0.125/1M input tokens for 128K context
            'completion' => 0.000375 / 1000 // Based on $0.375/1M output tokens for 128K context
        ],
        // OpenRouter rates vary by model, this is a placeholder
        // Actual cost calculation for OpenRouter will need to fetch model-specific rates
        // or use a general approximation if specific model rates aren't available.
        'openrouter/google/gemini-flash-1.5' => [ // Example, will vary
            'prompt' => 0.00006 / 1000, // Example rate
            'completion' => 0.00012 / 1000 // Example rate
        ]
    ],

    'monitoring' => [
        'log_requests' => env('AI_LOG_REQUESTS', true),
        'track_performance' => env('AI_TRACK_PERFORMANCE', true),
        'alert_on_errors' => env('AI_ALERT_ON_ERRORS', true),
        'cost_alerts' => [
            'daily_threshold' => env('AI_DAILY_COST_THRESHOLD', 100), // USD
            'monthly_threshold' => env('AI_MONTHLY_COST_THRESHOLD', 2000) // USD
        ]
    ],

    'security' => [
        'input_sanitization' => true,
        'output_filtering' => true,
        'rate_limiting' => true,
        'content_moderation' => env('AI_CONTENT_MODERATION', true),
        'blocked_patterns' => [
            'personal_info_request',
            'inappropriate_content',
            'off_topic_requests'
        ]
    ],

    'features' => [
        'multilingual_support' => true,
        'sentiment_analysis' => true,
        'intent_recognition' => true,
        'entity_extraction' => true,
        'conversation_memory' => true,
        'context_awareness' => true,
        'learning_mode' => env('AI_LEARNING_MODE', false)
    ]
];

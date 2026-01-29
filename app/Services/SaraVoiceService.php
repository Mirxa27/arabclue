<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Sara Voice Streaming Service
 * 
 * Handles real-time voice interactions with Sara AI assistant
 * including text-to-speech and speech-to-text functionality
 */
class SaraVoiceService
{
    protected $openaiApiKey;
    protected $elevenLabsApiKey;
    protected $defaultVoiceId;
    protected $defaultModel;
    protected $saraAIService;
    
    public function __construct(SaraAIService $saraAIService)
    {
        $this->openaiApiKey = config('openai.api_key');
        $this->elevenLabsApiKey = config('services.elevenlabs.api_key');
        $this->defaultVoiceId = config('services.elevenlabs.default_voice_id', 'EXAVITQu4vr4xnSDxMaL');
        $this->defaultModel = config('openai.speech_model', 'tts-1');
        $this->saraAIService = $saraAIService;
    }

    /**
     * Convert text to speech for streaming
     */
    public function textToSpeech(string $text, ?string $voiceId = null, array $options = []): array
    {
        $voiceId = $voiceId ?? $this->defaultVoiceId;
        
        // Determine which TTS provider to use
        $provider = $options['provider'] ?? 'elevenlabs'; // elevenlabs or openai
        
        try {
            if ($provider === 'elevenlabs') {
                return $this->elevenLabsTextToSpeech($text, $voiceId, $options);
            } else {
                return $this->openaiTextToSpeech($text, $options);
            }
        } catch (\Exception $e) {
            Log::error('Text-to-speech conversion failed', [
                'error' => $e->getMessage(),
                'provider' => $provider
            ]);
            
            // Try fallback provider if primary fails
            if ($provider === 'elevenlabs') {
                Log::info('Falling back to OpenAI TTS');
                return $this->openaiTextToSpeech($text, $options);
            }
            
            throw $e;
        }
    }
    
    /**
     * Convert speech to text from audio stream
     */
    public function speechToText(string $audioContent, string $fileType = 'webm'): string
    {
        try {
            // Generate temporary file path
            $tempFilePath = storage_path('tmp/audio_' . Str::uuid() . '.' . $fileType);
            
            // Ensure directory exists
            if (!file_exists(dirname($tempFilePath))) {
                mkdir(dirname($tempFilePath), 0755, true);
            }
            
            // Write binary audio content to file
            file_put_contents($tempFilePath, $audioContent);
            
            // Send to OpenAI Whisper API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
            ])->attach(
                'file', 
                file_get_contents($tempFilePath), 
                'audio.' . $fileType
            )->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
            ]);
            
            // Delete temporary file
            @unlink($tempFilePath);
            
            if ($response->successful()) {
                $result = $response->json();
                return $result['text'] ?? '';
            }
            
            Log::error('Speech-to-text conversion failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            throw new \Exception('Speech-to-text conversion failed: ' . $response->body());
            
        } catch (\Exception $e) {
            Log::error('Speech-to-text processing failed', [
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Process a voice message from user to Sara and get voice response
     */
    public function processSpeechInteraction(string $audioContent, string $fileType, User $user): array
    {
        // Step 1: Convert speech to text
        $userMessage = $this->speechToText($audioContent, $fileType);
        
        // Step 2: Get response from Sara AI
        $context = [
            'user_id' => $user->id,
            'conversation_id' => session('conversation_id'),
            'interaction_type' => 'voice'
        ];
        
        $saraResponse = $this->saraAIService->processMessage($userMessage, $context);
        
        // Step 3: Convert Sara's response to speech
        $voiceId = $user->preferences['sara_voice_id'] ?? $this->defaultVoiceId;
        $voiceOptions = [
            'model' => $user->preferences['tts_model'] ?? $this->defaultModel,
            'voice_settings' => [
                'stability' => 0.7,
                'similarity_boost' => 0.5,
                'style' => 0.15,
                'use_speaker_boost' => true
            ]
        ];
        
        $speechData = $this->textToSpeech($saraResponse['response'], $voiceId, $voiceOptions);
        
        // Return the complete interaction
        return [
            'user_message' => $userMessage,
            'ai_response' => $saraResponse['response'],
            'audio_url' => $speechData['audio_url'],
            'audio_base64' => $speechData['audio_base64'] ?? null,
            'conversation_id' => $saraResponse['conversation_id']
        ];
    }
    
    /**
     * Use ElevenLabs for text-to-speech (premium quality)
     */
    protected function elevenLabsTextToSpeech(string $text, string $voiceId, array $options = []): array
    {
        $apiUrl = "https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}/stream";
        
        $response = Http::withHeaders([
            'Accept' => 'audio/mpeg',
            'xi-api-key' => $this->elevenLabsApiKey,
            'Content-Type' => 'application/json'
        ])->post($apiUrl, [
            'text' => $text,
            'model_id' => $options['model'] ?? 'eleven_monolingual_v1',
            'voice_settings' => $options['voice_settings'] ?? [
                'stability' => 0.5,
                'similarity_boost' => 0.75
            ]
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('ElevenLabs API request failed: ' . $response->body());
        }
        
        // Save the audio file
        $fileName = 'sara_voice_' . time() . '_' . Str::random(10) . '.mp3';
        $filePath = 'public/audio/' . $fileName;
        
        Storage::put($filePath, $response->body());
        
        return [
            'audio_url' => Storage::url($filePath),
            'audio_base64' => base64_encode($response->body()),
            'provider' => 'elevenlabs'
        ];
    }
    
    /**
     * Use OpenAI for text-to-speech (fallback)
     */
    protected function openaiTextToSpeech(string $text, array $options = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiApiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.openai.com/v1/audio/speech', [
            'model' => $options['model'] ?? $this->defaultModel,
            'input' => $text,
            'voice' => $options['voice'] ?? 'nova',
            'response_format' => 'mp3'
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('OpenAI TTS API request failed: ' . $response->body());
        }
        
        // Save the audio file
        $fileName = 'sara_voice_' . time() . '_' . Str::random(10) . '.mp3';
        $filePath = 'public/audio/' . $fileName;
        
        Storage::put($filePath, $response->body());
        
        return [
            'audio_url' => Storage::url($filePath),
            'provider' => 'openai'
        ];
    }

    /**
     * Generate audio stream for real-time playback
     * This method provides streaming audio for immediate playback
     */
    public function generateAudioStream(string $text, array $options = []): \Generator
    {
        $provider = $options['provider'] ?? 'elevenlabs';
        $voiceId = $options['voice_id'] ?? $this->defaultVoiceId;
        
        try {
            if ($provider === 'elevenlabs') {
                yield from $this->elevenLabsAudioStream($text, $voiceId, $options);
            } else {
                yield from $this->openaiAudioStream($text, $options);
            }
        } catch (\Exception $e) {
            Log::error('Audio stream generation failed', [
                'error' => $e->getMessage(),
                'provider' => $provider
            ]);
            
            // Fallback to other provider
            if ($provider === 'elevenlabs') {
                yield from $this->openaiAudioStream($text, $options);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Stream audio from ElevenLabs API
     */
    protected function elevenLabsAudioStream(string $text, string $voiceId, array $options = []): \Generator
    {
        $apiUrl = "https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}/stream";
        
        $client = new \GuzzleHttp\Client();
        $response = $client->post($apiUrl, [
            'headers' => [
                'Accept' => 'audio/mpeg',
                'xi-api-key' => $this->elevenLabsApiKey,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'text' => $text,
                'model_id' => $options['model'] ?? 'eleven_monolingual_v1',
                'voice_settings' => $options['voice_settings'] ?? [
                    'stability' => 0.5,
                    'similarity_boost' => 0.75
                ]
            ],
            'stream' => true
        ]);
        
        $body = $response->getBody();
        
        while (!$body->eof()) {
            yield $body->read(1024); // Read in 1KB chunks
        }
    }

    /**
     * Stream audio from OpenAI API
     */
    protected function openaiAudioStream(string $text, array $options = []): \Generator
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://api.openai.com/v1/audio/speech', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'model' => $options['model'] ?? $this->defaultModel,
                'input' => $text,
                'voice' => $options['voice'] ?? 'nova',
                'response_format' => 'mp3'
            ],
            'stream' => true
        ]);
        
        $body = $response->getBody();
        
        while (!$body->eof()) {
            yield $body->read(1024); // Read in 1KB chunks
        }
    }

    /**
     * Process real-time voice conversation with streaming response
     */
    public function processVoiceStreamInteraction(string $audioContent, string $fileType, User $user): array
    {
        // Step 1: Convert speech to text
        $userMessage = $this->speechToText($audioContent, $fileType);
        
        // Step 2: Get response from Sara AI
        $context = [
            'user_id' => $user->id,
            'conversation_id' => session('conversation_id'),
            'interaction_type' => 'voice_stream'
        ];
        
        $saraResponse = $this->saraAIService->processMessage($userMessage, $context);
        
        // Step 3: Generate streaming audio response
        $voiceId = $user->preferences['sara_voice_id'] ?? $this->defaultVoiceId;
        $streamOptions = [
            'provider' => $user->preferences['tts_provider'] ?? 'elevenlabs',
            'voice_id' => $voiceId,
            'model' => $user->preferences['tts_model'] ?? 'eleven_monolingual_v1',
            'voice_settings' => [
                'stability' => 0.7,
                'similarity_boost' => 0.5,
                'style' => 0.15,
                'use_speaker_boost' => true
            ]
        ];
        
        // Create a unique stream ID for this interaction
        $streamId = 'sara_stream_' . time() . '_' . Str::random(10);
        
        return [
            'user_message' => $userMessage,
            'ai_response' => $saraResponse['response'],
            'stream_id' => $streamId,
            'stream_options' => $streamOptions,
            'conversation_id' => $saraResponse['conversation_id'],
            'stream_ready' => true
        ];
    }

    /**
     * Get available voices for Sara
     */
    public function getAvailableVoices(): array
    {
        $voices = [
            // ElevenLabs Voices
            'elevenlabs' => [
                [
                    'id' => 'EXAVITQu4vr4xnSDxMaL',
                    'name' => 'Bella',
                    'description' => 'Warm and friendly female voice',
                    'gender' => 'female',
                    'accent' => 'american'
                ],
                [
                    'id' => '21m00Tcm4TlvDq8ikWAM',
                    'name' => 'Rachel',
                    'description' => 'Professional and clear female voice',
                    'gender' => 'female',
                    'accent' => 'american'
                ],
                [
                    'id' => 'AZnzlk1XvdvUeBnXmlld',
                    'name' => 'Domi',
                    'description' => 'Confident and articulate female voice',
                    'gender' => 'female',
                    'accent' => 'american'
                ]
            ],
            // OpenAI Voices
            'openai' => [
                [
                    'id' => 'nova',
                    'name' => 'Nova',
                    'description' => 'Energetic and friendly',
                    'gender' => 'female',
                    'accent' => 'american'
                ],
                [
                    'id' => 'shimmer',
                    'name' => 'Shimmer',
                    'description' => 'Warm and expressive',
                    'gender' => 'female',
                    'accent' => 'american'
                ],
                [
                    'id' => 'echo',
                    'name' => 'Echo',
                    'description' => 'Professional and clear',
                    'gender' => 'male',
                    'accent' => 'american'
                ]
            ]
        ];
        
        return $voices;
    }

    /**
     * Cache audio for frequently used responses
     */
    public function getCachedAudio(string $text, array $options = []): ?array
    {
        $cacheKey = 'sara_audio_' . md5($text . serialize($options));
        return Cache::get($cacheKey);
    }

    /**
     * Store audio in cache for reuse
     */
    public function cacheAudio(string $text, array $options, array $audioData): void
    {
        $cacheKey = 'sara_audio_' . md5($text . serialize($options));
        Cache::put($cacheKey, $audioData, now()->addHours(24)); // Cache for 24 hours
    }

    /**
     * Process voice input from user
     */
    public function processVoiceInput($audioFile, User $user, array $options = []): array
    {
        try {
            // Convert audio to text using speech-to-text
            $fileType = $options['fileType'] ?? 'webm';
            $transcription = $this->speechToText($audioFile, $fileType);

            if (empty($transcription)) {
                return [
                    'success' => false,
                    'error' => 'Failed to transcribe audio'
                ];
            }

            // Process the text with Sara AI
            $context = [
                'user_id' => $user->id,
                'conversation_id' => $options['conversation_id'] ?? session('conversation_id'),
            ];
            $response = $this->saraAIService->processMessage($transcription, $context);

            // Convert response to speech
            $audioResponse = $this->textToSpeech($response['response'], null, $options);

            return [
                'success' => true,
                'transcription' => $transcription,
                'response_text' => $response['response'],
                'audio_response' => $audioResponse,
                'conversation_id' => $response['conversation_id'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Voice processing error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Voice processing failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Synthesize speech from text (alias for textToSpeech)
     */
    public function synthesizeSpeech(string $text, ?string $voiceId = null, array $options = []): array
    {
        return $this->textToSpeech($text, $voiceId, $options);
    }
}

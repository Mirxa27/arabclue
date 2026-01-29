<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SaraVoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * Sara Voice Controller
 * 
 * Handles real-time voice interactions with Sara AI assistant
 */
class SaraVoiceController extends Controller
{
    protected $voiceService;
    
    public function __construct(SaraVoiceService $voiceService)
    {
        $this->voiceService = $voiceService;
    }
    
    /**
     * Process live voice input and return voice response
     */
    public function processVoiceInput(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'audio' => 'required',
                'file_type' => 'required|string|in:webm,mp3,wav,ogg'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $user = $request->user();
            $audioContent = base64_decode($request->input('audio'));
            $fileType = $request->input('file_type');
            
            // Process speech interaction
            $result = $this->voiceService->processSpeechInteraction(
                $audioContent,
                $fileType,
                $user
            );
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('Voice processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process voice input',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Convert text to speech
     */
    public function textToSpeech(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'text' => 'required|string',
                'voice_id' => 'nullable|string',
                'provider' => 'nullable|string|in:elevenlabs,openai'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $user = $request->user();
            $text = $request->input('text');
            $voiceId = $request->input('voice_id');
            
            $options = [
                'provider' => $request->input('provider', 'elevenlabs')
            ];
            
            // Process text-to-speech
            $result = $this->voiceService->textToSpeech($text, $voiceId, $options);
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('Text-to-speech error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to convert text to speech',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get available voice options
     */
    public function getAvailableVoices(): JsonResponse
    {
        try {
            // You can extend this to fetch actual voices from ElevenLabs or other providers
            $voices = [
                [
                    'id' => 'EXAVITQu4vr4xnSDxMaL',
                    'name' => 'Sara (Default)',
                    'provider' => 'elevenlabs',
                    'description' => 'The default Sara AI assistant voice'
                ],
                [
                    'id' => 'nova',
                    'name' => 'Nova',
                    'provider' => 'openai',
                    'description' => 'Clear and warm female voice'
                ],
                [
                    'id' => 'alloy',
                    'name' => 'Alloy',
                    'provider' => 'openai',
                    'description' => 'Versatile neutral voice'
                ],
                [
                    'id' => 'echo',
                    'name' => 'Echo',
                    'provider' => 'openai',
                    'description' => 'Soft and balanced voice'
                ],
                [
                    'id' => 'MF3mGyEYCl7XYWbV9V6O',
                    'name' => 'Elli',
                    'provider' => 'elevenlabs',
                    'description' => 'Enthusiastic and engaging female voice'
                ]
            ];
            
            return response()->json([
                'success' => true,
                'data' => $voices
            ]);
            
        } catch (\Exception $e) {
            Log::error('Voice options error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available voices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stream audio response in real-time
     */
    public function streamAudio(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'text' => 'required|string',
                'voice_id' => 'nullable|string',
                'provider' => 'nullable|string|in:elevenlabs,openai'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $text = $request->input('text');
            $voiceId = $request->input('voice_id');
            $provider = $request->input('provider', 'elevenlabs');
            
            $options = [
                'provider' => $provider,
                'voice_id' => $voiceId
            ];
            
            return response()->stream(function() use ($text, $options) {
                foreach ($this->voiceService->generateAudioStream($text, $options) as $chunk) {
                    echo $chunk;
                    ob_flush();
                    flush();
                }
            }, 200, [
                'Content-Type' => 'audio/mpeg',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Audio streaming error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to stream audio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process voice input with streaming response
     */
    public function processVoiceStream(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'audio' => 'required',
                'file_type' => 'required|string|in:webm,mp3,wav,ogg'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $user = $request->user();
            $audioContent = base64_decode($request->input('audio'));
            $fileType = $request->input('file_type');
            
            // Process voice stream interaction
            $result = $this->voiceService->processVoiceStreamInteraction(
                $audioContent,
                $fileType,
                $user
            );
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('Voice stream processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process voice stream',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stream audio by stream ID
     */
    public function getStreamAudio(Request $request, string $streamId)
    {
        try {
            $text = $request->input('text');
            $options = $request->input('options', []);
            
            if (!$text) {
                return response()->json([
                    'success' => false,
                    'message' => 'Text parameter is required'
                ], 400);
            }
            
            return response()->stream(function() use ($text, $options) {
                foreach ($this->voiceService->generateAudioStream($text, $options) as $chunk) {
                    echo $chunk;
                    ob_flush();
                    flush();
                }
            }, 200, [
                'Content-Type' => 'audio/mpeg',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
                'Access-Control-Allow-Origin' => '*'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Stream audio error', [
                'error' => $e->getMessage(),
                'stream_id' => $streamId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stream audio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available voices with detailed information
     */
    public function getVoices(): JsonResponse
    {
        try {
            $voices = $this->voiceService->getAvailableVoices();
            
            return response()->json([
                'success' => true,
                'data' => $voices
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get voices error', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get available voices',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

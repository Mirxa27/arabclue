<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SaraVoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Mockery;

class SaraVoiceTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $voiceServiceMock;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Mock the SaraVoiceService
        $this->voiceServiceMock = Mockery::mock(SaraVoiceService::class);
        $this->app->instance(SaraVoiceService::class, $this->voiceServiceMock);
    }

    /** @test */
    public function user_can_get_available_voices()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/sara-voice/voices');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'provider',
                        'description'
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_convert_text_to_speech()
    {
        // Set up mock response
        $this->voiceServiceMock
            ->shouldReceive('textToSpeech')
            ->once()
            ->with('Hello from Sara!', 'EXAVITQu4vr4xnSDxMaL', ['provider' => 'elevenlabs'])
            ->andReturn([
                'audio_url' => 'https://storage.habibistay.com/audio/test.mp3',
                'provider' => 'elevenlabs'
            ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sara-voice/text-to-speech', [
                'text' => 'Hello from Sara!',
                'voice_id' => 'EXAVITQu4vr4xnSDxMaL',
                'provider' => 'elevenlabs'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'audio_url' => 'https://storage.habibistay.com/audio/test.mp3',
                    'provider' => 'elevenlabs'
                ]
            ]);
    }

    /** @test */
    public function user_can_process_voice_input()
    {
        // Setup mock for the voice processing service
        $this->voiceServiceMock
            ->shouldReceive('processSpeechInteraction')
            ->once()
            ->andReturn([
                'user_message' => 'What is the weather today?',
                'ai_response' => 'I\'m sorry, I don\'t have access to current weather information.',
                'audio_url' => 'https://storage.habibistay.com/audio/response.mp3',
                'conversation_id' => 'test-conversation-123'
            ]);

        // Sample base64 audio - this would normally be actual audio data
        $audioBase64 = base64_encode('test audio data');

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sara-voice/process', [
                'audio' => $audioBase64,
                'file_type' => 'webm'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user_message',
                    'ai_response',
                    'audio_url',
                    'conversation_id'
                ]
            ]);
    }

    /** @test */
    public function validation_fails_with_missing_data()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sara-voice/process', [
                // Missing audio data
                'file_type' => 'webm'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['audio']);
    }
    
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

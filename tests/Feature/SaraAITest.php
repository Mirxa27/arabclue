<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\User;
use App\Models\SaraConversation;
use Illuminate\Support\Str;

class SaraAITest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the Sara API endpoint responds correctly.
     *
     * @return void
     */
    public function test_sara_api_endpoint_responds_correctly()
    {
        $response = $this->postJson('/api/v1/sara/start', ['channel' => 'web']);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'conversation' => [
                        'id',
                        'session_id',
                    ],
                    'response' => [
                        'content',
                    ]
                ]
            ]);
    }

    /**
     * Test that the Sara AI completes a request successfully.
     *
     * @return void
     */
    public function test_sara_ai_completes_request_successfully()
    {
        $testConversationId = Str::uuid()->toString();
        $conversation = SaraConversation::create([
            'conversation_id' => $testConversationId, // Add UUID conversation_id
            'session_id' => 'test_session',
            'channel' => 'web',
            'status' => 'active',
            'last_activity_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/sara/message', [
            'conversation_id' => $conversation->id, // Use the database ID instead
            'message' => 'Hello, I am looking for a property in Riyadh'
        ]);


        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'response' => [
                        'content',
                        'intent',
                        'suggested_actions'
                    ]
                ]
            ]);
    }

    /**
     * Test that the Sara fallback mechanism works.
     *
     * @return void
     */
    public function test_sara_fallback_mechanism_works()
    {
        // Simulate AI service failure
        Http::fake([
            'api.openai.com/*' => Http::response(null, 500)
        ]);

        $testFallbackConversationId = Str::uuid()->toString();
        $conversation = SaraConversation::create([
            'conversation_id' => $testFallbackConversationId, // Add UUID conversation_id
            'session_id' => 'test_session_fallback',
            'channel' => 'web',
            'status' => 'active',
            'last_activity_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/sara/message', [
            'conversation_id' => $conversation->id, // Use the database ID instead
            'message' => 'I need help with my booking'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.response.intent', 'help_support');
    }

    /**
     * Test that the Sara integration is functional.
     *
     * @return void
     */
    public function test_sara_integration_is_functional()
    {
        $startResponse = $this->postJson('/api/v1/sara/start', ['channel' => 'web']);
        $conversationId = $startResponse->json('data.conversation.id');

        $messageResponse = $this->postJson('/api/v1/sara/message', [
            'conversation_id' => $conversationId,
            'message' => 'Show me popular destinations'
        ]);

        $messageResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        $actionResponse = $this->postJson('/api/v1/sara/action', [
            'conversation_id' => $conversationId,
            'action_type' => 'show_popular_destinations',
            'action_data' => []
        ]);

        $actionResponse->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'response' => [
                        'content'
                    ]
                ]
            ]);
    }
}
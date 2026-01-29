<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Property;
use OpenAI\Contracts\ClientContract as OpenAIClientContract;
use Mockery;

class FullFlowTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        // Mock the OpenAI client
        $this->mock(OpenAIClientContract::class, function ($mock) {
            $mock->shouldReceive('chat->create')->andReturn([
                'id' => 'chatcmpl-xxxxxxxxxxxxxxxxxxxxxxx',
                'object' => 'chat.completion',
                'created' => time(),
                'model' => 'gpt-4-turbo-preview',
                'choices' => [
                    [
                        'index' => 0,
                        'message' => [
                            'role' => 'assistant',
                            'content' => json_encode([
                                'type' => 'property_search_results',
                                'data' => ['properties' => [['id' => 1, 'title' => 'Test Property']]]
                            ]),
                        ],
                        'finish_reason' => 'stop',
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 50,
                    'completion_tokens' => 50,
                    'total_tokens' => 100,
                ],
            ]);
        });
    }

    /** @test */
    public function a_user_can_search_for_a_property_and_book_it_through_sara_chatbot()
    {
        // 1. Create a user and a property
        $user = User::factory()->create();
        $property = Property::factory()->create();

        // 2. Start a conversation with Sara
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/sara/start');
        $response->assertStatus(200);
        $conversationId = $response->json('data.conversation.id');

        // 3. Search for a property
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/sara/action', [
            'conversation_id' => $conversationId,
            'action' => [
                'type' => 'search_property',
                'data' => [
                    'location' => $property->city,
                ],
            ],
        ]);
        $response->assertStatus(200);

        // 4. Select a property
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/sara/action', [
            'conversation_id' => $conversationId,
            'action' => [
                'type' => 'book_property',
                'data' => [
                    'property_id' => $property->id,
                ],
            ],
        ]);
        $response->assertStatus(200);

        // 5. Provide booking details
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/sara/action', [
            'conversation_id' => $conversationId,
            'action' => [
                'type' => 'book_property',
                'data' => [
                    'check_in' => now()->addDay()->format('Y-m-d'),
                    'check_out' => now()->addDays(3)->format('Y-m-d'),
                    'guests' => 2,
                ],
            ],
        ]);
        $response->assertStatus(200);

        // 6. Confirm the booking
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/sara/action', [
            'conversation_id' => $conversationId,
            'action' => [
                'type' => 'confirm_booking',
                'data' => [],
            ],
        ]);
        $response->assertStatus(200);
        $response->assertJsonPath('data.response.data.payment_link', fn ($link) => !empty($link));
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /** @test */
    public function user_can_view_profile_with_enhanced_data()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'profile_completion',
                    'statistics',
                    'preferences',
                    'roles',
                    'verification_status'
                ]
            ]);
    }

    /** @test */
    public function user_can_update_preferences()
    {
        $preference = [
            'category' => 'appearance',
            'key' => 'theme',
            'value' => 'dark'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/profile/preferences', $preference);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user_id' => $this->user->id,
                    'category' => 'appearance',
                    'key' => 'theme',
                    'value' => 'dark'
                ]
            ]);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $this->user->id,
            'category' => 'appearance',
            'key' => 'theme',
        ]);
    }

    /** @test */
    public function user_can_upload_verification_document()
    {
        Storage::fake('private');

        $file = UploadedFile::fake()->image('id-card.jpg');

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/profile/verify/document', [
                'document_type' => 'id_card',
                'document' => $file
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'document_type' => 'id_card'
                ]
            ]);

        // Path format: users/{user_id}/documents/{document_type}/*
        $path = "users/{$this->user->id}/documents/id_card";
        Storage::disk('private')->assertExists($path . '/' . $file->hashName());
    }

    /** @test */
    public function user_can_view_activity_history()
    {
        // Create some test activities
        $this->user->activities()->create([
            'activity_type' => 'search',
            'description' => 'Searched for properties in Dubai',
            'metadata' => ['query' => 'Dubai', 'filters' => ['guests' => 2]]
        ]);

        $this->user->activities()->create([
            'activity_type' => 'booking',
            'description' => 'Made a booking',
            'metadata' => ['property_id' => 1, 'dates' => ['2025-06-10', '2025-06-15']]
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/profile/activity');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'activity_type',
                            'description',
                            'metadata',
                            'created_at'
                        ]
                    ]
                ]
            ]);
    }
}

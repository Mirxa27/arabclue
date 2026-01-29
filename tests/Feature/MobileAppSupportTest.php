<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class MobileAppSupportTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'email' => 'mobileuser@example.com',
            'device_info' => [
                'device_type' => 'ios',
                'os' => 'iOS',
                'os_version' => '16.0',
                'app_version' => '1.0.0',
                'push_enabled' => true
            ],
            'notification_settings' => [
                'push' => [
                    'bookings' => true,
                    'messages' => true,
                    'reviews' => true,
                    'reminders' => false
                ]
            ]
        ]);
    }

    /** @test */
    public function can_get_app_configuration()
    {
        $response = $this->getJson('/api/v1/mobile/config');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'version',
                    'min_version',
                    'force_update',
                    'maintenance',
                    'features',
                    'api',
                    'urls'
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_update_device_info()
    {
        $deviceInfo = [
            'device_type' => 'android',
            'os_version' => '12',
            'app_version' => '1.1.0',
            'push_enabled' => true,
            'fcm_token' => 'test-fcm-token-123',
            'language' => 'ar',
            'timezone' => 'Asia/Riyadh'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/mobile/device', $deviceInfo);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        // Fetch the updated user and check the device info
        $this->user->refresh();
        
        $this->assertEquals('android', Arr::get($this->user->device_info, 'device_type'));
        $this->assertEquals('12', Arr::get($this->user->device_info, 'os_version'));
        $this->assertEquals('1.1.0', Arr::get($this->user->device_info, 'app_version'));
        $this->assertEquals('test-fcm-token-123', $this->user->fcm_token);
    }

    /** @test */
    public function user_can_get_notification_preferences()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/mobile/notifications/preferences');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'channels',
                    'notification_types',
                    'current_preferences'
                ]
            ]);

        // Check that the response contains the user's current preferences
        $response->assertJson([
            'data' => [
                'current_preferences' => [
                    'push' => [
                        'bookings' => true,
                        'messages' => true,
                        'reviews' => true,
                        'reminders' => false
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function user_can_update_notification_preferences()
    {
        $preferences = [
            'channel' => 'push',
            'preferences' => [
                'bookings' => true,
                'messages' => false,
                'reviews' => true,
                'reminders' => true,
                'marketing' => false
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/mobile/notifications/preferences', $preferences);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'push' => [
                        'bookings' => true,
                        'messages' => false,
                        'reviews' => true,
                        'reminders' => true,
                        'marketing' => false
                    ]
                ]
            ]);

        // Verify the database was updated correctly
        $this->user->refresh();
        $this->assertEquals(false, $this->user->notification_settings['push']['messages']);
        $this->assertEquals(true, $this->user->notification_settings['push']['reminders']);
    }

    /** @test */
    public function unauthorized_user_cannot_access_mobile_endpoints()
    {
        $response = $this->getJson('/api/v1/mobile/notifications/preferences');
        
        $response->assertStatus(401);
    }
}

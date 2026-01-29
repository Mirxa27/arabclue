<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdminConfigurationTest extends TestCase
{
    use RefreshDatabase;

    private $adminUser;
    private $regularUser;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);
        
        // Create regular user
        $this->regularUser = User::factory()->create([
            'email' => 'user@example.com',
            'role' => 'guest',
        ]);

        // Mock config settings
        Config::set('app.name', 'HabibiStay Test');
        Config::set('mail.from.name', 'HabibiStay Support');
    }

    /** @test */
    public function admin_can_view_configuration_categories()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/configuration/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description'
                    ]
                ]
            ]);
    }

    /** @test */
    public function admin_can_view_configuration_by_category()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/configuration/category/app');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'category',
                    'settings'
                ]
            ])
            ->assertJson([
                'data' => [
                    'category' => 'app'
                ]
            ]);
    }

    /** @test */
    public function admin_can_update_configuration_settings()
    {
        $settings = [
            'category' => 'app',
            'settings' => [
                [
                    'key' => 'name',
                    'value' => 'HabibiStay Updated'
                ]
            ]
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/admin/configuration/update', $settings);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'category' => 'app',
                    'updated' => ['name']
                ]
            ]);
    }

    /** @test */
    public function admin_can_view_environment_variables()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/configuration/env');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'key',
                        'value',
                        'is_sensitive',
                        'restart_required'
                    ]
                ]
            ]);
    }

    /** @test */
    public function regular_user_cannot_access_configuration()
    {
        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/v1/admin/configuration/categories');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_environment_variables()
    {
        // This test assumes a mock file system
        // In a real scenario, you'd need to mock the file system operations
        
        $variables = [
            'variables' => [
                [
                    'key' => 'APP_NAME',
                    'value' => 'HabibiStay Production'
                ]
            ]
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/admin/configuration/env/update', $variables);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'updated' => ['APP_NAME']
                ]
            ]);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_admin_can_login()
    {
        // Create an admin user
        $admin = User::factory()->admin()->create([
            'password' => bcrypt($password = 'password'),
        ]);

        // Simulate a POST request to the admin login endpoint
        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => $password,
        ]);

        // Assert that the response is a successful redirect to the admin dashboard
        $response->assertRedirect('/admin');

        // Assert that the admin user is authenticated
        $this->assertAuthenticatedAs($admin);
    }

    /** @test */
    public function test_non_admin_user_is_redirected_from_admin_area()
    {
        // Create a regular user
        $user = User::factory()->create();

        // Act as this authenticated user
        $this->actingAs($user);

        // Simulate a GET request to an admin-only route
        $response = $this->get('/admin/dashboard');

        // Assert that the response status is a redirect (HTTP 302)
        $response->assertStatus(302);
    }
}
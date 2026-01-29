<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that it can create and retrieve users.
     *
     * @return void
     */
    public function test_it_can_create_and_retrieve_users()
    {
        // Create a specific number of users
        $userCount = 5;
        User::factory()->count($userCount)->create();

        // Assert that the total number of users in the database matches the number created
        $this->assertDatabaseCount('users', $userCount);
    }
}
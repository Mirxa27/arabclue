<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Manually run migrations
        Artisan::call('migrate');

        // Seed basic data if needed
        // $this->seed();
    }

    /**
     * Create a user for testing
     */
    protected function createUser(array $attributes = [])
    {
        return \App\Models\User::factory()->create($attributes);
    }

    /**
     * Create a property for testing
     */
    protected function createProperty(array $attributes = [])
    {
        return \App\Models\Property::factory()->create($attributes);
    }

    /**
     * Create a booking for testing
     */
    protected function createBooking(array $attributes = [])
    {
        return \App\Models\Booking::factory()->create($attributes);
    }

    /**
     * Authenticate a user for testing
     */
    protected function actingAsUser($user = null)
    {
        $user = $user ?: $this->createUser();
        return $this->actingAs($user);
    }

    /**
     * Authenticate as a host for testing
     */
    protected function actingAsHost($user = null)
    {
        $user = $user ?: $this->createUser(['role' => 'host']);
        return $this->actingAs($user);
    }

    /**
     * Authenticate as an admin for testing
     */
    protected function actingAsAdmin($user = null)
    {
        $user = $user ?: $this->createUser(['role' => 'admin']);
        return $this->actingAs($user);
    }

    /**
     * Assert that a notification was sent
     */
    protected function assertNotificationSent($notifiable, $notification)
    {
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,
            'type' => $notification,
        ]);
    }

    /**
     * Assert that an email was sent
     */
    protected function assertEmailSent($mailable)
    {
        \Illuminate\Support\Facades\Mail::assertSent($mailable);
    }
}

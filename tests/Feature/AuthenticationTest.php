<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        Notification::fake();
        
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        
        // Assert welcome notification was sent
        Notification::assertSentTo($user, WelcomeNotification::class);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_email_verification_required()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/email/verify');
    }

    public function test_user_can_verify_email()
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/dashboard');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_password_reset_email_sent()
    {
        Mail::fake();
        
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');
        Mail::assertSent(\Illuminate\Auth\Notifications\ResetPassword::class);
    }

    public function test_password_can_be_reset()
    {
        $user = User::factory()->create();
        
        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect('/login');
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_two_factor_authentication_setup()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/user/two-factor-authentication');

        $response->assertStatus(200);
        $this->assertTrue($user->fresh()->two_factor_enabled);
        $this->assertNotNull($user->fresh()->two_factor_secret);
    }

    public function test_two_factor_authentication_verification()
    {
        $user = User::factory()->withTwoFactor()->create();
        
        // Mock the 2FA code verification
        $this->mock(\PragmaRX\Google2FA\Google2FA::class)
            ->shouldReceive('verifyKey')
            ->andReturn(true);

        $response = $this->actingAs($user)->post('/user/confirmed-two-factor-authentication', [
            'code' => '123456',
        ]);

        $response->assertStatus(200);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Notifications\PropertyApprovalNotification;
use App\Notifications\PaymentConfirmationNotification;
use App\Notifications\ReviewRequestNotification;
use App\Mail\WelcomeEmail;
use App\Mail\PropertyApprovalEmail;
use App\Mail\PaymentConfirmationEmail;
use App\Mail\ReviewRequestEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_notification_sent_on_registration()
    {
        Notification::fake();
        Mail::fake();

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'john@example.com')->first();

        // Assert welcome notification was sent
        Notification::assertSentTo($user, WelcomeNotification::class);
        
        // Assert welcome email was sent
        Mail::assertSent(WelcomeEmail::class, function ($mail) use ($user) {
            return $mail->user->id === $user->id;
        });
    }

    public function test_property_approval_notification()
    {
        Notification::fake();
        Mail::fake();

        $host = User::factory()->host()->create();
        $property = Property::factory()->pending()->create(['user_id' => $host->id]);

        // Send approval notification
        $host->notify(new PropertyApprovalNotification($property, true));

        // Assert notification was sent
        Notification::assertSentTo($host, PropertyApprovalNotification::class);
        
        // Assert email was sent
        Mail::assertSent(PropertyApprovalEmail::class, function ($mail) use ($property) {
            return $mail->property->id === $property->id && $mail->approved === true;
        });
    }

    public function test_property_rejection_notification()
    {
        Notification::fake();
        Mail::fake();

        $host = User::factory()->host()->create();
        $property = Property::factory()->pending()->create(['user_id' => $host->id]);

        // Send rejection notification
        $host->notify(new PropertyApprovalNotification($property, false));

        // Assert notification was sent
        Notification::assertSentTo($host, PropertyApprovalNotification::class);
        
        // Assert email was sent
        Mail::assertSent(PropertyApprovalEmail::class, function ($mail) use ($property) {
            return $mail->property->id === $property->id && $mail->approved === false;
        });
    }

    public function test_payment_confirmation_notification()
    {
        Notification::fake();
        Mail::fake();

        $booking = Booking::factory()->create(['status' => 'confirmed']);

        // Send payment confirmation notification
        $booking->user->notify(new PaymentConfirmationNotification($booking));

        // Assert notification was sent
        Notification::assertSentTo($booking->user, PaymentConfirmationNotification::class);
        
        // Assert email was sent
        Mail::assertSent(PaymentConfirmationEmail::class, function ($mail) use ($booking) {
            return $mail->booking->id === $booking->id;
        });
    }

    public function test_review_request_notification()
    {
        Notification::fake();
        Mail::fake();

        $booking = Booking::factory()->completed()->create();

        // Send review request notification
        $booking->user->notify(new ReviewRequestNotification($booking));

        // Assert notification was sent
        Notification::assertSentTo($booking->user, ReviewRequestNotification::class);
        
        // Assert email was sent
        Mail::assertSent(ReviewRequestEmail::class, function ($mail) use ($booking) {
            return $mail->booking->id === $booking->id;
        });
    }

    public function test_notification_preferences_respected()
    {
        Notification::fake();

        // User with email notifications disabled
        $user = User::factory()->create([
            'notification_settings' => [
                'email' => false,
                'push' => true,
                'sms' => false,
            ]
        ]);

        $user->notify(new WelcomeNotification($user));

        // Should only send database notification, not email
        Notification::assertSentTo($user, WelcomeNotification::class, function ($notification, $channels) {
            return in_array('database', $channels) && !in_array('mail', $channels);
        });
    }

    public function test_push_notification_sent_when_fcm_token_exists()
    {
        Notification::fake();

        $user = User::factory()->create([
            'fcm_token' => 'test_fcm_token_123',
            'notification_settings' => [
                'email' => true,
                'push' => true,
            ]
        ]);

        $user->notify(new WelcomeNotification($user));

        // Should send both database and FCM notifications
        Notification::assertSentTo($user, WelcomeNotification::class, function ($notification, $channels) {
            return in_array('database', $channels) && in_array('fcm', $channels);
        });
    }

    public function test_notification_data_structure()
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create();

        $notification = new PaymentConfirmationNotification($booking);
        $data = $notification->toArray($user);

        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('action_url', $data);
        $this->assertArrayHasKey('action_text', $data);
        $this->assertArrayHasKey('icon', $data);
        $this->assertArrayHasKey('color', $data);
        
        $this->assertEquals('payment_confirmed', $data['type']);
        $this->assertEquals($booking->id, $data['booking_id']);
    }

    public function test_fcm_notification_data_structure()
    {
        $user = User::factory()->create(['fcm_token' => 'test_token']);
        $booking = Booking::factory()->create();

        $notification = new PaymentConfirmationNotification($booking);
        $fcmData = $notification->toFcm($user);

        $this->assertArrayHasKey('title', $fcmData);
        $this->assertArrayHasKey('body', $fcmData);
        $this->assertArrayHasKey('data', $fcmData);
        
        $this->assertArrayHasKey('type', $fcmData['data']);
        $this->assertArrayHasKey('booking_id', $fcmData['data']);
        $this->assertArrayHasKey('action', $fcmData['data']);
        $this->assertArrayHasKey('url', $fcmData['data']);
    }

    public function test_notification_database_storage()
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create();

        $user->notify(new PaymentConfirmationNotification($booking));

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => PaymentConfirmationNotification::class,
        ]);

        $notification = $user->notifications()->first();
        $data = $notification->data;

        $this->assertEquals('payment_confirmed', $data['type']);
        $this->assertEquals($booking->id, $data['booking_id']);
    }

    public function test_notification_marking_as_read()
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create();

        $user->notify(new PaymentConfirmationNotification($booking));

        $notification = $user->unreadNotifications()->first();
        $this->assertNull($notification->read_at);

        // Mark as read
        $response = $this->actingAs($user)->patch("/api/notifications/{$notification->id}/read");
        
        $response->assertStatus(200);
        
        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_bulk_notification_marking()
    {
        $user = User::factory()->create();
        
        // Create multiple notifications
        $user->notify(new WelcomeNotification($user));
        $booking = Booking::factory()->create(['user_id' => $user->id]);
        $user->notify(new PaymentConfirmationNotification($booking));

        $this->assertEquals(2, $user->unreadNotifications()->count());

        // Mark all as read
        $response = $this->actingAs($user)->patch('/api/notifications/mark-all-read');
        
        $response->assertStatus(200);
        
        $this->assertEquals(0, $user->unreadNotifications()->count());
    }
}

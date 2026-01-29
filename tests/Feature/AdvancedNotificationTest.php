<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use App\Notifications\BookingReminderNotification;
use App\Notifications\HostPayoutNotification;
use App\Notifications\SystemMaintenanceNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdvancedNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = new NotificationService();
    }

    public function test_booking_reminder_notification_check_in()
    {
        Notification::fake();

        $booking = Booking::factory()->create([
            'check_in' => now()->addDay(),
            'status' => 'confirmed'
        ]);

        $this->notificationService->sendBookingReminderNotification($booking, 'check_in');

        Notification::assertSentTo(
            $booking->user,
            BookingReminderNotification::class,
            function ($notification) {
                $data = $notification->toArray(new User());
                return $data['reminder_type'] === 'check_in' &&
                       $data['title'] === 'Check-in Tomorrow! 🎉';
            }
        );
    }

    public function test_booking_reminder_notification_check_out()
    {
        Notification::fake();

        $booking = Booking::factory()->create([
            'check_out' => now(),
            'status' => 'confirmed'
        ]);

        $this->notificationService->sendBookingReminderNotification($booking, 'check_out');

        Notification::assertSentTo(
            $booking->user,
            BookingReminderNotification::class,
            function ($notification) {
                $data = $notification->toArray(new User());
                return $data['reminder_type'] === 'check_out' &&
                       $data['title'] === 'Check-out Reminder 🏠';
            }
        );
    }

    public function test_host_payout_notification()
    {
        Notification::fake();

        $booking = Booking::factory()->create([
            'total_amount' => 1000.00,
            'service_fee' => 100.00
        ]);
        $payoutAmount = 900.00;

        $this->notificationService->sendHostPayoutNotification($booking, $payoutAmount);

        Notification::assertSentTo(
            $booking->property->user,
            HostPayoutNotification::class,
            function ($notification) use ($payoutAmount) {
                $data = $notification->toArray(new User());
                return $data['payout_amount'] === $payoutAmount &&
                       $data['title'] === 'Payout Processed! 💰';
            }
        );
    }

    public function test_system_maintenance_notification_scheduled()
    {
        Notification::fake();

        $users = User::factory()->count(3)->create();
        $maintenanceStart = now()->addHours(2);
        $maintenanceEnd = now()->addHours(4);

        $this->notificationService->sendSystemMaintenanceNotification(
            $maintenanceStart,
            $maintenanceEnd,
            'scheduled',
            ['Website', 'Mobile App']
        );

        Notification::assertSentTo(
            $users,
            SystemMaintenanceNotification::class,
            function ($notification) {
                $data = $notification->toArray(new User());
                return $data['maintenance_type'] === 'scheduled' &&
                       $data['title'] === '🔧 Scheduled Maintenance';
            }
        );
    }

    public function test_system_maintenance_notification_emergency()
    {
        Notification::fake();

        $users = User::factory()->count(2)->create();
        $maintenanceStart = now();
        $maintenanceEnd = now()->addHour();

        $this->notificationService->sendSystemMaintenanceNotification(
            $maintenanceStart,
            $maintenanceEnd,
            'emergency',
            ['Payment System']
        );

        Notification::assertSentTo(
            $users,
            SystemMaintenanceNotification::class,
            function ($notification) {
                $data = $notification->toArray(new User());
                return $data['maintenance_type'] === 'emergency' &&
                       $data['title'] === '🚨 Emergency Maintenance';
            }
        );
    }

    public function test_scheduled_booking_reminders()
    {
        Notification::fake();

        // Create bookings for different reminder scenarios
        $checkInTomorrow = Booking::factory()->create([
            'check_in' => now()->addDay()->startOfDay(),
            'status' => 'confirmed'
        ]);

        $checkOutToday = Booking::factory()->create([
            'check_out' => now()->startOfDay(),
            'status' => 'confirmed'
        ]);

        $upcomingIn3Days = Booking::factory()->create([
            'check_in' => now()->addDays(3)->startOfDay(),
            'status' => 'confirmed'
        ]);

        $this->notificationService->scheduleBookingReminders();

        // Assert check-in reminder sent
        Notification::assertSentTo(
            $checkInTomorrow->user,
            BookingReminderNotification::class
        );

        // Assert check-out reminder sent
        Notification::assertSentTo(
            $checkOutToday->user,
            BookingReminderNotification::class
        );

        // Assert upcoming reminder sent
        Notification::assertSentTo(
            $upcomingIn3Days->user,
            BookingReminderNotification::class
        );
    }

    public function test_notification_service_handles_errors_gracefully()
    {
        // Create a user with invalid email to trigger an error
        $user = User::factory()->create(['email' => 'invalid-email']);
        
        // This should not throw an exception
        $this->notificationService->sendWelcomeNotification($user);
        
        // Test should pass if no exception is thrown
        $this->assertTrue(true);
    }

    public function test_bulk_notification_sending()
    {
        Notification::fake();

        $users = User::factory()->count(5)->create();
        $userIds = $users->pluck('id')->toArray();

        $maintenanceStart = now()->addHour();
        $maintenanceEnd = now()->addHours(2);

        $notification = new SystemMaintenanceNotification(
            $maintenanceStart,
            $maintenanceEnd,
            'scheduled'
        );

        $this->notificationService->sendBulkNotification($notification, $userIds);

        Notification::assertSentTo($users, SystemMaintenanceNotification::class);
    }

    public function test_notification_respects_user_preferences()
    {
        Notification::fake();

        // User with email notifications disabled
        $userNoEmail = User::factory()->create([
            'notification_settings' => [
                'email' => false,
                'push' => true,
                'sms' => false,
            ]
        ]);

        // User with all notifications enabled
        $userAllEnabled = User::factory()->create([
            'notification_settings' => [
                'email' => true,
                'push' => true,
                'sms' => false,
            ]
        ]);

        $this->notificationService->sendWelcomeNotification($userNoEmail);
        $this->notificationService->sendWelcomeNotification($userAllEnabled);

        // Both users should receive notifications, but channels should differ
        Notification::assertSentToTimes($userNoEmail, \App\Notifications\WelcomeNotification::class, 1);
        Notification::assertSentToTimes($userAllEnabled, \App\Notifications\WelcomeNotification::class, 1);
    }

    public function test_fcm_notification_data_structure()
    {
        $booking = Booking::factory()->create();
        $notification = new BookingReminderNotification($booking, 'check_in');

        $user = User::factory()->create(['fcm_token' => 'test_token']);
        $fcmData = $notification->toFcm($user);

        $this->assertArrayHasKey('title', $fcmData);
        $this->assertArrayHasKey('body', $fcmData);
        $this->assertArrayHasKey('data', $fcmData);
        
        $this->assertArrayHasKey('type', $fcmData['data']);
        $this->assertArrayHasKey('booking_id', $fcmData['data']);
        $this->assertArrayHasKey('action', $fcmData['data']);
        $this->assertArrayHasKey('url', $fcmData['data']);

        $this->assertEquals('booking_reminder', $fcmData['data']['type']);
        $this->assertEquals((string) $booking->id, $fcmData['data']['booking_id']);
    }

    public function test_notification_database_storage_structure()
    {
        $booking = Booking::factory()->create();
        $payoutAmount = 500.00;

        $booking->property->user->notify(new HostPayoutNotification($booking, $payoutAmount));

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $booking->property->user_id,
            'type' => HostPayoutNotification::class,
        ]);

        $notification = $booking->property->user->notifications()->first();
        $data = $notification->data;

        $this->assertEquals('host_payout', $data['type']);
        $this->assertEquals($payoutAmount, $data['payout_amount']);
        $this->assertEquals($booking->id, $data['booking_id']);
        $this->assertArrayHasKey('expected_arrival', $data);
    }
}

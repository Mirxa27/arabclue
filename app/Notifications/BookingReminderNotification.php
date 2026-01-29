<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Mail\BookingReminderEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;
    protected $reminderType;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, string $reminderType = 'check_in')
    {
        $this->booking = $booking;
        $this->reminderType = $reminderType;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = ['database'];
        
        // Add mail channel if user wants email notifications
        if ($notifiable->notification_settings['email'] ?? true) {
            $channels[] = 'mail';
        }

        // Add push notification if user has FCM token
        if ($notifiable->fcm_token) {
            $channels[] = 'fcm';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): BookingReminderEmail
    {
        return new BookingReminderEmail($this->booking, $this->reminderType);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $messages = [
            'check_in' => "Your check-in at {$this->booking->property->title} is tomorrow!",
            'check_out' => "Don't forget to check out of {$this->booking->property->title} today",
            'upcoming' => "Your stay at {$this->booking->property->title} is coming up soon",
        ];

        $icons = [
            'check_in' => 'fas fa-door-open',
            'check_out' => 'fas fa-door-closed',
            'upcoming' => 'fas fa-calendar-check',
        ];

        $colors = [
            'check_in' => 'green',
            'check_out' => 'yellow',
            'upcoming' => 'blue',
        ];

        return [
            'type' => 'booking_reminder',
            'reminder_type' => $this->reminderType,
            'title' => $this->reminderType === 'check_in' ? 'Check-in Tomorrow! 🎉' : 
                      ($this->reminderType === 'check_out' ? 'Check-out Reminder 🏠' : 'Upcoming Stay 📅'),
            'message' => $messages[$this->reminderType] ?? 'Booking reminder',
            'action_url' => url('/bookings/' . $this->booking->id),
            'action_text' => 'View Booking',
            'icon' => $icons[$this->reminderType] ?? 'fas fa-calendar',
            'color' => $colors[$this->reminderType] ?? 'blue',
            'booking_id' => $this->booking->id,
            'property_title' => $this->booking->property->title,
            'check_in_date' => $this->booking->check_in->toDateString(),
            'check_out_date' => $this->booking->check_out->toDateString(),
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm($notifiable): array
    {
        $titles = [
            'check_in' => 'Check-in Tomorrow! 🎉',
            'check_out' => 'Check-out Reminder 🏠',
            'upcoming' => 'Upcoming Stay 📅',
        ];

        $bodies = [
            'check_in' => "Your stay at {$this->booking->property->title} begins tomorrow. Get ready for an amazing experience!",
            'check_out' => "Don't forget to check out of {$this->booking->property->title} by {$this->booking->property->check_out_time}",
            'upcoming' => "Your stay at {$this->booking->property->title} is coming up on {$this->booking->check_in->format('M d')}",
        ];

        return [
            'title' => $titles[$this->reminderType] ?? 'Booking Reminder',
            'body' => $bodies[$this->reminderType] ?? 'You have a booking reminder',
            'data' => [
                'type' => 'booking_reminder',
                'reminder_type' => $this->reminderType,
                'booking_id' => (string) $this->booking->id,
                'action' => 'open_booking',
                'url' => '/bookings/' . $this->booking->id
            ]
        ];
    }
}

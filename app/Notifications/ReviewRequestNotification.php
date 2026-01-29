<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Mail\ReviewRequestEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReviewRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
    public function toMail($notifiable): ReviewRequestEmail
    {
        return new ReviewRequestEmail($this->booking);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'review_request',
            'title' => 'How was your stay? ⭐',
            'message' => "Please share your experience at {$this->booking->property->title}",
            'action_url' => url('/bookings/' . $this->booking->id . '/review'),
            'action_text' => 'Write Review',
            'icon' => 'fas fa-star',
            'color' => 'yellow',
            'booking_id' => $this->booking->id,
            'property_title' => $this->booking->property->title
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm($notifiable): array
    {
        return [
            'title' => 'How was your stay? ⭐',
            'body' => "Share your experience at {$this->booking->property->title} and help other travelers!",
            'data' => [
                'type' => 'review_request',
                'booking_id' => (string) $this->booking->id,
                'action' => 'write_review',
                'url' => '/bookings/' . $this->booking->id . '/review'
            ]
        ];
    }
}

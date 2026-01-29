<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Mail\PaymentConfirmationEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaymentConfirmationNotification extends Notification implements ShouldQueue
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
    public function toMail($notifiable): PaymentConfirmationEmail
    {
        return new PaymentConfirmationEmail($this->booking);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'payment_confirmed',
            'title' => 'Payment Confirmed! 💳',
            'message' => "Your payment for booking #{$this->booking->id} has been confirmed.",
            'action_url' => url('/bookings/' . $this->booking->id),
            'action_text' => 'View Booking',
            'icon' => 'fas fa-credit-card',
            'color' => 'green',
            'booking_id' => $this->booking->id,
            'amount' => $this->booking->total_amount,
            'property_title' => $this->booking->property->title
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm($notifiable): array
    {
        return [
            'title' => 'Payment Confirmed! 💳',
            'body' => "Your booking for {$this->booking->property->title} is confirmed. Check-in: {$this->booking->check_in->format('M d')}",
            'data' => [
                'type' => 'payment_confirmed',
                'booking_id' => (string) $this->booking->id,
                'action' => 'open_booking',
                'url' => '/bookings/' . $this->booking->id
            ]
        ];
    }
}

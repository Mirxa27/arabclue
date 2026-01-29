<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Mail\HostPayoutEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class HostPayoutNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;
    protected $payoutAmount;
    protected $payoutDate;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, float $payoutAmount, $payoutDate = null)
    {
        $this->booking = $booking;
        $this->payoutAmount = $payoutAmount;
        $this->payoutDate = $payoutDate ?? now();
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
    public function toMail($notifiable): HostPayoutEmail
    {
        return new HostPayoutEmail($this->booking, $this->payoutAmount, $this->payoutDate);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'host_payout',
            'title' => 'Payout Processed! 💰',
            'message' => "Your payout of SAR " . number_format($this->payoutAmount, 2) . " has been processed",
            'action_url' => url('/host/earnings'),
            'action_text' => 'View Earnings',
            'icon' => 'fas fa-money-bill-wave',
            'color' => 'green',
            'booking_id' => $this->booking->id,
            'property_title' => $this->booking->property->title,
            'payout_amount' => $this->payoutAmount,
            'payout_date' => $this->payoutDate->toDateString(),
            'expected_arrival' => $this->payoutDate->addBusinessDays(3)->toDateString(),
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm($notifiable): array
    {
        return [
            'title' => 'Payout Processed! 💰',
            'body' => "SAR " . number_format($this->payoutAmount, 2) . " is on its way to your account. Expected arrival in 1-3 business days.",
            'data' => [
                'type' => 'host_payout',
                'booking_id' => (string) $this->booking->id,
                'payout_amount' => (string) $this->payoutAmount,
                'action' => 'open_earnings',
                'url' => '/host/earnings'
            ]
        ];
    }
}

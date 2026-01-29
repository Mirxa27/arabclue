<?php

namespace App\Notifications;

use App\Models\User;
use App\Mail\WelcomeEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
    public function toMail($notifiable): WelcomeEmail
    {
        return new WelcomeEmail($this->user);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'welcome',
            'title' => 'Welcome to HabibiStay!',
            'message' => 'Welcome to HabibiStay! We\'re excited to have you join our community.',
            'action_url' => url('/dashboard'),
            'action_text' => 'Get Started',
            'icon' => 'fas fa-heart',
            'color' => 'purple'
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm($notifiable): array
    {
        return [
            'title' => 'Welcome to HabibiStay! 🎉',
            'body' => 'Start exploring amazing properties and exceptional stays.',
            'data' => [
                'type' => 'welcome',
                'action' => 'open_app',
                'url' => '/dashboard'
            ]
        ];
    }
}

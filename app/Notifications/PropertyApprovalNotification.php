<?php

namespace App\Notifications;

use App\Models\Property;
use App\Mail\PropertyApprovalEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PropertyApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $property;
    protected $approved;

    /**
     * Create a new notification instance.
     */
    public function __construct(Property $property, bool $approved = true)
    {
        $this->property = $property;
        $this->approved = $approved;
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
    public function toMail($notifiable): PropertyApprovalEmail
    {
        return new PropertyApprovalEmail($this->property, $this->approved);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        if ($this->approved) {
            return [
                'type' => 'property_approved',
                'title' => 'Property Approved! 🎉',
                'message' => "Your property '{$this->property->title}' has been approved and is now live!",
                'action_url' => url('/host/properties/' . $this->property->id),
                'action_text' => 'View Property',
                'icon' => 'fas fa-check-circle',
                'color' => 'green',
                'property_id' => $this->property->id
            ];
        } else {
            return [
                'type' => 'property_rejected',
                'title' => 'Property Needs Attention',
                'message' => "Your property '{$this->property->title}' needs some updates before approval.",
                'action_url' => url('/host/properties/' . $this->property->id . '/edit'),
                'action_text' => 'Update Property',
                'icon' => 'fas fa-exclamation-triangle',
                'color' => 'yellow',
                'property_id' => $this->property->id
            ];
        }
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm($notifiable): array
    {
        if ($this->approved) {
            return [
                'title' => 'Property Approved! 🎉',
                'body' => "Your property '{$this->property->title}' is now live and ready for bookings!",
                'data' => [
                    'type' => 'property_approved',
                    'property_id' => (string) $this->property->id,
                    'action' => 'open_property',
                    'url' => '/host/properties/' . $this->property->id
                ]
            ];
        } else {
            return [
                'title' => 'Property Needs Updates',
                'body' => "Your property '{$this->property->title}' needs some attention before approval.",
                'data' => [
                    'type' => 'property_rejected',
                    'property_id' => (string) $this->property->id,
                    'action' => 'edit_property',
                    'url' => '/host/properties/' . $this->property->id . '/edit'
                ]
            ];
        }
    }
}

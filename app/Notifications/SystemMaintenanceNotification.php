<?php

namespace App\Notifications;

use App\Mail\SystemMaintenanceEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SystemMaintenanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $maintenanceStart;
    protected $maintenanceEnd;
    protected $maintenanceType;
    protected $affectedServices;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        $maintenanceStart,
        $maintenanceEnd,
        string $maintenanceType = 'scheduled',
        array $affectedServices = []
    ) {
        $this->maintenanceStart = $maintenanceStart;
        $this->maintenanceEnd = $maintenanceEnd;
        $this->maintenanceType = $maintenanceType;
        $this->affectedServices = $affectedServices;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = ['database'];
        
        // Always send email for maintenance notifications
        $channels[] = 'mail';

        // Add push notification if user has FCM token
        if ($notifiable->fcm_token) {
            $channels[] = 'fcm';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): SystemMaintenanceEmail
    {
        return new SystemMaintenanceEmail(
            $this->maintenanceStart,
            $this->maintenanceEnd,
            $this->maintenanceType,
            $this->affectedServices
        );
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $title = $this->maintenanceType === 'emergency' 
            ? '🚨 Emergency Maintenance'
            : '🔧 Scheduled Maintenance';

        $message = $this->maintenanceType === 'emergency'
            ? 'Emergency maintenance is in progress. Some services may be temporarily unavailable.'
            : 'Scheduled maintenance will occur on ' . $this->maintenanceStart->format('M d, Y \a\t g:i A');

        return [
            'type' => 'system_maintenance',
            'maintenance_type' => $this->maintenanceType,
            'title' => $title,
            'message' => $message,
            'action_url' => url('/status'),
            'action_text' => 'Check Status',
            'icon' => $this->maintenanceType === 'emergency' ? 'fas fa-exclamation-triangle' : 'fas fa-tools',
            'color' => $this->maintenanceType === 'emergency' ? 'red' : 'blue',
            'maintenance_start' => $this->maintenanceStart->toISOString(),
            'maintenance_end' => $this->maintenanceEnd->toISOString(),
            'affected_services' => $this->affectedServices,
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm($notifiable): array
    {
        $title = $this->maintenanceType === 'emergency' 
            ? '🚨 Emergency Maintenance'
            : '🔧 Scheduled Maintenance';

        $body = $this->maintenanceType === 'emergency'
            ? 'We\'re working to resolve a critical issue. Some services may be temporarily unavailable.'
            : 'Maintenance scheduled for ' . $this->maintenanceStart->format('M d \a\t g:i A') . '. Check our status page for updates.';

        return [
            'title' => $title,
            'body' => $body,
            'data' => [
                'type' => 'system_maintenance',
                'maintenance_type' => $this->maintenanceType,
                'action' => 'open_status',
                'url' => '/status'
            ]
        ];
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SystemMaintenanceEmail extends Mailable 
{
    use Queueable, SerializesModels;

    public $maintenanceStart;
    public $maintenanceEnd;
    public $maintenanceType;
    public $affectedServices;

    /**
     * Create a new message instance.
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
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->maintenanceType === 'emergency' 
            ? 'Emergency Maintenance Notice - HabibiStay'
            : 'Scheduled Maintenance Notice - HabibiStay';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.system-maintenance',
            with: [
                'maintenanceStart' => $this->maintenanceStart,
                'maintenanceEnd' => $this->maintenanceEnd,
                'maintenanceType' => $this->maintenanceType,
                'affectedServices' => $this->affectedServices,
                'statusPageUrl' => url('/status'),
                'supportUrl' => url('/support'),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

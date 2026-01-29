<?php

namespace App\Mail;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PropertyApprovalEmail extends Mailable 
{
    use Queueable, SerializesModels;

    public $property;
    public $approved;

    /**
     * Create a new message instance.
     */
    public function __construct(Property $property, bool $approved = true)
    {
        $this->property = $property;
        $this->approved = $approved;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->approved 
            ? 'Property Approved - ' . $this->property->title
            : 'Property Needs Attention - ' . $this->property->title;

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = $this->approved ? 'emails.property-approved' : 'emails.property-rejected';
        
        return new Content(
            view: $view,
            with: [
                'property' => $this->property,
                'host' => $this->property->user,
                'propertyUrl' => url('/host/properties/' . $this->property->id),
                'dashboardUrl' => url('/host/dashboard'),
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

<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReferralInvitation extends Mailable 
{
    use Queueable, SerializesModels;

    public User $referrer;
    public string $referralLink;
    public ?string $customMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(User $referrer, string $referralLink, ?string $customMessage = null)
    {
        $this->referrer = $referrer;
        $this->referralLink = $referralLink;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->referrer->name} invited you to join HabibiStay"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.referral-invitation',
            with: [
                'referrer' => $this->referrer,
                'referralLink' => $this->referralLink,
                'customMessage' => $this->customMessage,
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

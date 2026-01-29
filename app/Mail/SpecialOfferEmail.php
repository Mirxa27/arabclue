<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SpecialOfferEmail extends Mailable 
{
    use Queueable, SerializesModels;

    public $user;
    public $offerTitle;
    public $offerDescription;
    public $discountPercentage;
    public $validUntil;
    public $promoCode;
    public $featuredProperties;

    /**
     * Create a new message instance.
     */
    public function __construct(
        User $user,
        string $offerTitle,
        string $offerDescription,
        int $discountPercentage,
        $validUntil,
        string $promoCode = null,
        $featuredProperties = null
    ) {
        $this->user = $user;
        $this->offerTitle = $offerTitle;
        $this->offerDescription = $offerDescription;
        $this->discountPercentage = $discountPercentage;
        $this->validUntil = $validUntil;
        $this->promoCode = $promoCode;
        $this->featuredProperties = $featuredProperties ?? collect();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->offerTitle . ' - Save ' . $this->discountPercentage . '%!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.special-offer',
            with: [
                'user' => $this->user,
                'offerTitle' => $this->offerTitle,
                'offerDescription' => $this->offerDescription,
                'discountPercentage' => $this->discountPercentage,
                'validUntil' => $this->validUntil,
                'promoCode' => $this->promoCode,
                'featuredProperties' => $this->featuredProperties,
                'browseUrl' => url('/properties'),
                'unsubscribeUrl' => url('/unsubscribe/' . $this->user->id),
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

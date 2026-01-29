<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HostPayoutEmail extends Mailable 
{
    use Queueable, SerializesModels;

    public $booking;
    public $payoutAmount;
    public $payoutDate;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, float $payoutAmount, $payoutDate = null)
    {
        $this->booking = $booking;
        $this->payoutAmount = $payoutAmount;
        $this->payoutDate = $payoutDate ?? now();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payout Processed - SAR ' . number_format($this->payoutAmount, 2),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.host-payout',
            with: [
                'booking' => $this->booking,
                'host' => $this->booking->property->user,
                'property' => $this->booking->property,
                'guest' => $this->booking->user,
                'payoutAmount' => $this->payoutAmount,
                'payoutDate' => $this->payoutDate,
                'bookingUrl' => url('/host/bookings/' . $this->booking->id),
                'earningsUrl' => url('/host/earnings'),
                'taxDocumentUrl' => url('/host/tax-documents'),
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

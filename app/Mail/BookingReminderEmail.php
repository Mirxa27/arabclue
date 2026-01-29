<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingReminderEmail extends Mailable 
{
    use Queueable, SerializesModels;

    public $booking;
    public $reminderType;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, string $reminderType = 'check_in')
    {
        $this->booking = $booking;
        $this->reminderType = $reminderType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'check_in' => 'Your check-in is tomorrow - ' . $this->booking->property->title,
            'check_out' => 'Check-out reminder - ' . $this->booking->property->title,
            'upcoming' => 'Your stay is coming up - ' . $this->booking->property->title,
        ];

        return new Envelope(
            subject: $subjects[$this->reminderType] ?? 'Booking Reminder',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-reminder',
            with: [
                'booking' => $this->booking,
                'guest' => $this->booking->user,
                'property' => $this->booking->property,
                'host' => $this->booking->property->user,
                'reminderType' => $this->reminderType,
                'bookingUrl' => url('/bookings/' . $this->booking->id),
                'propertyUrl' => url('/properties/' . $this->booking->property->slug),
                'hostContactUrl' => url('/conversations/with/' . $this->booking->property->user->id),
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

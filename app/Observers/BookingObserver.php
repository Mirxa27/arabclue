<?php

namespace App\Observers;

use App\Models\Booking;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class BookingObserver
{
    /**
     * Handle the Booking "creating" event.
     * 
     * Automatically generate a stable UUID for iCal export compatibility.
     */
    public function creating(Booking $booking): void
    {
        // Ensure every booking gets a unique, stable UID for iCal exports
        if (empty($booking->uid)) {
            $booking->uid = (string) Str::uuid();
        }
        
        // Set default source if not specified
        if (empty($booking->source)) {
            $booking->source = 'direct';
        }
    }

    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        // Log booking creation for audit trail
        Log::info("Booking created", [
            'booking_id' => $booking->id,
            'property_id' => $booking->property_id,
            'source' => $booking->source,
            'uid' => $booking->uid
        ]);
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        // If dates changed, log for calendar sync purposes
        if ($booking->isDirty(['check_in', 'check_out'])) {
            Log::info("Booking dates updated", [
                'booking_id' => $booking->id,
                'property_id' => $booking->property_id,
                'old_check_in' => $booking->getOriginal('check_in'),
                'new_check_in' => $booking->check_in,
                'old_check_out' => $booking->getOriginal('check_out'),
                'new_check_out' => $booking->check_out,
            ]);
        }
    }

    /**
     * Handle the Booking "deleted" event.
     */
    public function deleted(Booking $booking): void
    {
        Log::info("Booking deleted", [
            'booking_id' => $booking->id,
            'property_id' => $booking->property_id,
            'source' => $booking->source,
            'uid' => $booking->uid
        ]);
    }
}

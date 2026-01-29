<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create a dispute for the booking.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function createDispute(User $user, Booking $booking)
    {
        return ($user->id === $booking->user_id || $user->id === $booking->host_id) && $booking->created_at->diffInDays(now()) <= 14;
    }
}

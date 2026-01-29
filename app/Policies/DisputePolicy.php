<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\Dispute;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DisputePolicy
{
    use HandlesAuthorization;

    public function createDispute(User $user, Booking $booking)
    {
        return $user->id === $booking->user_id || $user->id === $booking->host_id;
    }

    public function view(User $user, Dispute $dispute)
    {
        return $user->id === $dispute->booking->user_id || $user->id === $dispute->booking->host_id || $user->isAdmin();
    }

    public function reply(User $user, Dispute $dispute)
    {
        return $user->id === $dispute->booking->user_id || $user->id === $dispute->booking->host_id || $user->isAdmin();
    }
}

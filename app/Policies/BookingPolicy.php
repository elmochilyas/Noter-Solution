<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Client;
use App\Models\User;

class BookingPolicy
{
    public function view(User|Client $actor, Booking $booking): bool
    {
        if ($actor instanceof User) {
            return true;
        }

        return $actor->id === $booking->client_id;
    }

    public function create(User|Client $actor): bool
    {
        return true;
    }

    public function update(User $user, Booking $booking): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function cancel(User|Client $actor, Booking $booking): bool
    {
        $status = BookingStatus::tryFrom($booking->status);
        if ($status && $status->isTerminal()) {
            return false;
        }

        if ($actor instanceof Client) {
            return $actor->id === $booking->client_id
                && $booking->starts_at->isAfter(now()->addHours(2));
        }

        return $actor->isOwner() || $actor->isAssistant();
    }

    public function complete(User $user, Booking $booking): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function markNoShow(User $user, Booking $booking): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function viewNotes(User $user, Booking $booking): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function refund(User $user, Booking $booking): bool
    {
        return $user->isOwner();
    }

    public function markCashSucceeded(User $user, Booking $booking): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }
}

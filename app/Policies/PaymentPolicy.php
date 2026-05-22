<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User|Client $actor, Payment $payment): bool
    {
        if ($actor instanceof User) {
            return true;
        }

        return $actor->id === $payment->booking->client_id;
    }

    public function refund(User $user, Payment $payment): bool
    {
        return $user->isOwner();
    }
}

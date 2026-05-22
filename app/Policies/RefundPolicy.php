<?php

namespace App\Policies;

use App\Models\Refund;
use App\Models\User;

class RefundPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function view(User $user, Refund $refund): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function create(User $user): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function approve(User $user, Refund $refund): bool
    {
        return $user->isOwner();
    }

    public function reject(User $user, Refund $refund): bool
    {
        return $user->isOwner();
    }
}

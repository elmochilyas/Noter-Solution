<?php

namespace App\Policies;

use App\Models\AvailabilityException;
use App\Models\User;

class AvailabilityExceptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function view(User $user, AvailabilityException $exception): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function create(User $user): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function update(User $user, AvailabilityException $exception): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function delete(User $user, AvailabilityException $exception): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }
}

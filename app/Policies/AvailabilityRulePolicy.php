<?php

namespace App\Policies;

use App\Models\AvailabilityRule;
use App\Models\User;

class AvailabilityRulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function view(User $user, AvailabilityRule $rule): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function create(User $user): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function update(User $user, AvailabilityRule $rule): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function delete(User $user, AvailabilityRule $rule): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }
}

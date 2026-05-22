<?php

namespace App\Policies;

use App\Models\User;

class ConsultationPlanPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function delete(User $user): bool
    {
        return $user->isOwner();
    }
}

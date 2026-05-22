<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

class ContactMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function view(User $user, ContactMessage $contactMessage): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function update(User $user, ContactMessage $contactMessage): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function delete(User $user, ContactMessage $contactMessage): bool
    {
        return $user->isOwner();
    }
}

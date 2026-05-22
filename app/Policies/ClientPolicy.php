<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function view(User $user, Client $client): bool
    {
        return true;
    }

    public function update(User $user, Client $client): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->isOwner();
    }
}

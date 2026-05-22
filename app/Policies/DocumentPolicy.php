<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function view(User|Client $actor, Document $document): bool
    {
        if ($actor instanceof User) {
            return true;
        }

        return $actor->id === $document->client_id;
    }

    public function create(Client $client): bool
    {
        return true;
    }

    public function delete(User|Client $actor, Document $document): bool
    {
        if ($actor instanceof User) {
            return $actor->isOwner() || $actor->isAssistant();
        }

        return $actor->id === $document->client_id;
    }
}

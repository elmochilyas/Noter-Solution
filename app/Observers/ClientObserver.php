<?php

namespace App\Observers;

use App\Models\ChatbotConversation;
use App\Models\Client;

class ClientObserver
{
    public function deleting(Client $client): void
    {
        ChatbotConversation::where('client_id', $client->id)
            ->update([
                'client_id' => null,
                'metadata' => null,
            ]);
    }
}

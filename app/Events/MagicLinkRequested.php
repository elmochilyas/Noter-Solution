<?php

namespace App\Events;

use App\Models\Client;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MagicLinkRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Client $client,
        public string $signedUrl,
        public ?string $intendedUrl = null,
    ) {}
}

<?php

namespace App\Events;

use App\Models\Document;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentUploaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Document $document) {}
}

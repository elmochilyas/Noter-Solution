<?php

namespace App\Events;

use App\Models\Receipt;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReceiptGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Receipt $receipt) {}
}

<?php

namespace App\Events;

use App\Models\Refund;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefundIssued
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Refund $refund) {}
}

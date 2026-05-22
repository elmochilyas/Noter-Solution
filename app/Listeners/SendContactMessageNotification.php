<?php

namespace App\Listeners;

use App\Events\ContactMessageReceived;
use App\Models\User;
use App\Notifications\ContactMessageNotification;
use Illuminate\Support\Facades\Notification;

class SendContactMessageNotification
{
    public function handle(ContactMessageReceived $event): void
    {
        $users = User::role(['owner', 'assistant'])->get();

        Notification::send($users, new ContactMessageNotification($event->contactMessage));
    }
}

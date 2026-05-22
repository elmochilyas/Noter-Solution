<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthActivity
{
    public function handleLogin(Login $event): void
    {
        activity()
            ->causedBy($event->user)
            ->withProperties(['guard' => $event->guard])
            ->log($event->guard === 'client' ? 'client_login' : 'admin_login');
    }

    public function handleLogout(Logout $event): void
    {
        activity()
            ->causedBy($event->user)
            ->withProperties(['guard' => $event->guard])
            ->log($event->guard === 'client' ? 'client_logout' : 'admin_logout');
    }

    public function subscribe(): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
        ];
    }
}

<?php

namespace App\Providers;

use App\Events\ContactMessageReceived;
use App\Listeners\LogAuthActivity;
use App\Listeners\SendContactMessageNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogAuthActivity::class.'@handleLogin',
        ],
        Logout::class => [
            LogAuthActivity::class.'@handleLogout',
        ],
        ContactMessageReceived::class => [
            SendContactMessageNotification::class,
        ],
    ];
}

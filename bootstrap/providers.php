<?php

use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\PulseServiceProvider;

return [
    AppServiceProvider::class,
    EventServiceProvider::class,
    AdminPanelProvider::class,
    FortifyServiceProvider::class,
    PulseServiceProvider::class,
];

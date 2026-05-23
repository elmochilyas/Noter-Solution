<?php

return [
    'barryvdh/laravel-dompdf' => [
        'aliases' => [
            'PDF' => 'Barryvdh\\DomPDF\\Facade\\Pdf',
            'Pdf' => 'Barryvdh\\DomPDF\\Facade\\Pdf',
        ],
        'providers' => [
            0 => 'Barryvdh\\DomPDF\\ServiceProvider',
        ],
    ],
    'blade-ui-kit/blade-heroicons' => [
        'providers' => [
            0 => 'BladeUI\\Heroicons\\BladeHeroiconsServiceProvider',
        ],
    ],
    'blade-ui-kit/blade-icons' => [
        'providers' => [
            0 => 'BladeUI\\Icons\\BladeIconsServiceProvider',
        ],
    ],
    'filament/actions' => [
        'providers' => [
            0 => 'Filament\\Actions\\ActionsServiceProvider',
        ],
    ],
    'filament/filament' => [
        'providers' => [
            0 => 'Filament\\FilamentServiceProvider',
        ],
    ],
    'filament/forms' => [
        'providers' => [
            0 => 'Filament\\Forms\\FormsServiceProvider',
        ],
    ],
    'filament/infolists' => [
        'providers' => [
            0 => 'Filament\\Infolists\\InfolistsServiceProvider',
        ],
    ],
    'filament/notifications' => [
        'providers' => [
            0 => 'Filament\\Notifications\\NotificationsServiceProvider',
        ],
    ],
    'filament/query-builder' => [
        'providers' => [
            0 => 'Filament\\QueryBuilder\\QueryBuilderServiceProvider',
        ],
    ],
    'filament/schemas' => [
        'providers' => [
            0 => 'Filament\\Schemas\\SchemasServiceProvider',
        ],
    ],
    'filament/support' => [
        'providers' => [
            0 => 'Filament\\Support\\SupportServiceProvider',
        ],
    ],
    'filament/tables' => [
        'providers' => [
            0 => 'Filament\\Tables\\TablesServiceProvider',
        ],
    ],
    'filament/widgets' => [
        'providers' => [
            0 => 'Filament\\Widgets\\WidgetsServiceProvider',
        ],
    ],
    'flowframe/laravel-trend' => [
        'aliases' => [
            'Trend' => 'Flowframe\\Trend\\TrendFacade',
        ],
        'providers' => [
            0 => 'Flowframe\\Trend\\TrendServiceProvider',
        ],
    ],
    'kirschbaum-development/eloquent-power-joins' => [
        'providers' => [
            0 => 'Kirschbaum\\PowerJoins\\PowerJoinsServiceProvider',
        ],
    ],
    'laravel/dusk' => [
        'providers' => [
            0 => 'Laravel\\Dusk\\DuskServiceProvider',
        ],
    ],
    'laravel/fortify' => [
        'providers' => [
            0 => 'Laravel\\Fortify\\FortifyServiceProvider',
        ],
    ],
    'laravel/pail' => [
        'providers' => [
            0 => 'Laravel\\Pail\\PailServiceProvider',
        ],
    ],
    'laravel/pao' => [
        'providers' => [
            0 => 'Laravel\\Pao\\Laravel\\ServiceProvider',
        ],
    ],
    'laravel/passkeys' => [
        'providers' => [
            0 => 'Laravel\\Passkeys\\PasskeysServiceProvider',
        ],
    ],
    'laravel/pulse' => [
        'aliases' => [
            'Pulse' => 'Laravel\\Pulse\\Facades\\Pulse',
        ],
        'providers' => [
            0 => 'Laravel\\Pulse\\PulseServiceProvider',
        ],
    ],
    'laravel/sentinel' => [
        'providers' => [
            0 => 'Laravel\\Sentinel\\SentinelServiceProvider',
        ],
    ],
    'laravel/tinker' => [
        'providers' => [
            0 => 'Laravel\\Tinker\\TinkerServiceProvider',
        ],
    ],
    'livewire/livewire' => [
        'aliases' => [
            'Livewire' => 'Livewire\\Livewire',
        ],
        'providers' => [
            0 => 'Livewire\\LivewireServiceProvider',
        ],
    ],
    'nesbot/carbon' => [
        'providers' => [
            0 => 'Carbon\\Laravel\\ServiceProvider',
        ],
    ],
    'nunomaduro/collision' => [
        'providers' => [
            0 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
        ],
    ],
    'nunomaduro/termwind' => [
        'providers' => [
            0 => 'Termwind\\Laravel\\TermwindServiceProvider',
        ],
    ],
    'pragmarx/google2fa-laravel' => [
        'aliases' => [
            'Google2FA' => 'PragmaRX\\Google2FALaravel\\Facade',
        ],
        'providers' => [
            0 => 'PragmaRX\\Google2FALaravel\\ServiceProvider',
        ],
    ],
    'ryangjchandler/blade-capture-directive' => [
        'aliases' => [
            'BladeCaptureDirective' => 'RyanChandler\\BladeCaptureDirective\\Facades\\BladeCaptureDirective',
        ],
        'providers' => [
            0 => 'RyanChandler\\BladeCaptureDirective\\BladeCaptureDirectiveServiceProvider',
        ],
    ],
    'sentry/sentry-laravel' => [
        'aliases' => [
            'Sentry' => 'Sentry\\Laravel\\Facade',
        ],
        'providers' => [
            0 => 'Sentry\\Laravel\\ServiceProvider',
            1 => 'Sentry\\Laravel\\Tracing\\ServiceProvider',
        ],
    ],
    'spatie/laravel-activitylog' => [
        'providers' => [
            0 => 'Spatie\\Activitylog\\ActivitylogServiceProvider',
        ],
    ],
    'spatie/laravel-permission' => [
        'providers' => [
            0 => 'Spatie\\Permission\\PermissionServiceProvider',
        ],
    ],
];

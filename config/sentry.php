<?php

use App\Exceptions\Sentry\BeforeSendHandler;
use Sentry\Event;
use Sentry\EventHint;

return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),

    'release' => env('SENTRY_RELEASE', trim(exec('git log --pretty="%h" -n1 HEAD') ?: 'unknown')),

    'environment' => env('APP_ENV'),

    'sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),

    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),

    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.0),

    'send_default_pii' => false,

    'http_ssl_verify_peer' => true,

    'enable_tracing' => env('SENTRY_TRACES_SAMPLE_RATE', 0) > 0,

    'before_send' => function (Event $event, ?EventHint $hint): ?Event {
        $handler = new BeforeSendHandler;

        return $handler($event, $hint);
    },
];

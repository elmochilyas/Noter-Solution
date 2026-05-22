<?php

use Laravel\Pulse\Recorders;

return [
    'enabled' => env('PULSE_ENABLED', true),

    'domain' => env('PULSE_DOMAIN', 'localhost'),

    'path' => env('PULSE_PATH', 'pulse'),

    'dashboard' => [
        'timezone' => 'Africa/Casablanca',
    ],

    'storage' => [
        'driver' => env('PULSE_STORAGE_DRIVER', 'database'),

        'database' => [
            'connection' => env('PULSE_DB_CONNECTION'),
            'chunK' => env('PULSE_DB_CHUNK', 200),
        ],
    ],

    'recorders' => [
        Recorders\CacheInteractions::class => [
            'enabled' => env('PULSE_CACHE_INTERACTIONS_ENABLED', false),
        ],

        Recorders\Exceptions::class => [
            'enabled' => env('PULSE_EXCEPTIONS_ENABLED', true),
            'sample_rate' => env('PULSE_EXCEPTIONS_SAMPLE_RATE', 1),
            'location' => env('PULSE_EXCEPTIONS_LOCATION', true),
            'ignore' => [
                // '/^Symfony\\\\Component\\\\HttpKernel\\\\Exception\\\\/',
            ],
        ],

        Recorders\Queues::class => [
            'enabled' => env('PULSE_QUEUES_ENABLED', true),
            'sample_rate' => env('PULSE_QUEUES_SAMPLE_RATE', 1),
            'ignore' => [
                // '/^Illuminate\\\\Mail\\\\SendQueuedMailable/',
            ],
        ],

        Recorders\SlowJobs::class => [
            'enabled' => env('PULSE_SLOW_JOBS_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_JOBS_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_JOBS_THRESHOLD', 1000),
            'location' => env('PULSE_SLOW_JOBS_LOCATION', true),
            'max_job_length' => env('PULSE_SLOW_JOBS_MAX_LENGTH', 0),
        ],

        Recorders\SlowOutgoingRequests::class => [
            'enabled' => env('PULSE_SLOW_OUTGOING_REQUESTS_ENABLED', false),
            'sample_rate' => env('PULSE_SLOW_OUTGOING_REQUESTS_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_OUTGOING_REQUESTS_THRESHOLD', 1000),
            'location' => env('PULSE_SLOW_OUTGOING_REQUESTS_LOCATION', true),
            'max_job_length' => env('PULSE_SLOW_OUTGOING_REQUESTS_MAX_LENGTH', 0),
            'ignore' => [
                // '#^http://localhost#',
            ],
        ],

        Recorders\SlowQueries::class => [
            'enabled' => env('PULSE_SLOW_QUERIES_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_QUERIES_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_QUERIES_THRESHOLD', 100),
            'location' => env('PULSE_SLOW_QUERIES_LOCATION', true),
            'max_query_length' => env('PULSE_SLOW_QUERIES_MAX_LENGTH', 0),
        ],

        Recorders\SlowRequests::class => [
            'enabled' => env('PULSE_SLOW_REQUESTS_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_REQUESTS_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_REQUESTS_THRESHOLD', 100),
            'location' => env('PULSE_SLOW_REQUESTS_LOCATION', true),
            'ignore' => [
                // '#^/pulse$#',
                '#^/up$#',
                '#^/telescope#',
                '#^/horizon#',
                '#^/admin/horizon#',
            ],
        ],

        Recorders\UserSessions::class => [
            'enabled' => env('PULSE_USER_SESSIONS_ENABLED', false),
        ],
    ],

];

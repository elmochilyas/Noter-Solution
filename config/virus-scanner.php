<?php

use App\Infrastructure\VirusScanner\ClamavScanner;
use App\Infrastructure\VirusScanner\NullScanner;

return [
    'default' => env('VIRUS_SCANNER_DRIVER', 'null'),

    'drivers' => [
        'null' => NullScanner::class,
        'clamav' => ClamavScanner::class,
    ],

    'clamav' => [
        'host' => env('CLAMAV_HOST', '127.0.0.1'),
        'port' => (int) env('CLAMAV_PORT', 3310),
        'timeout' => (float) env('CLAMAV_TIMEOUT', 30.0),
    ],
];

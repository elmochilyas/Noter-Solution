<?php

namespace App\Domain\Services\VirusScanner;

enum ScanStatus: string
{
    case CLEAN = 'clean';
    case INFECTED = 'infected';
    case ERROR = 'error';
}

final readonly class ScanResult
{
    public function __construct(
        public ScanStatus $status,
        public string $message = '',
    ) {}
}

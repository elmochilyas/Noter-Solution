<?php

namespace App\Infrastructure\VirusScanner;

use App\Domain\Services\VirusScanner\Contracts\VirusScanner;
use App\Domain\Services\VirusScanner\ScanResult;
use App\Domain\Services\VirusScanner\ScanStatus;

final class NullScanner implements VirusScanner
{
    public function scan(string $filePath): ScanResult
    {
        return new ScanResult(ScanStatus::CLEAN);
    }

    public function name(): string
    {
        return 'null';
    }
}

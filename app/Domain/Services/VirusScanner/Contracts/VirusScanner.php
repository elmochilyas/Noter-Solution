<?php

namespace App\Domain\Services\VirusScanner\Contracts;

use App\Domain\Services\VirusScanner\ScanResult;

interface VirusScanner
{
    public function scan(string $filePath): ScanResult;

    public function name(): string;
}

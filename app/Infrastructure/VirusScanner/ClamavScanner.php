<?php

namespace App\Infrastructure\VirusScanner;

use App\Domain\Services\VirusScanner\Contracts\VirusScanner;
use App\Domain\Services\VirusScanner\ScanResult;
use App\Domain\Services\VirusScanner\ScanStatus;
use Socket;

final class ClamavScanner implements VirusScanner
{
    private const PREFIX = 'z';

    private const CHUNK_SIZE = 8192;

    public function __construct(
        private readonly string $host = '127.0.0.1',
        private readonly int $port = 3310,
        private readonly float $timeout = 30.0,
    ) {}

    public function scan(string $filePath): ScanResult
    {
        if (! file_exists($filePath)) {
            return new ScanResult(ScanStatus::ERROR, 'File not found');
        }

        $socket = $this->connect();

        if ($socket === false) {
            return new ScanResult(ScanStatus::ERROR, 'Cannot connect to ClamAV daemon');
        }

        try {
            $this->sendHandshake($socket);

            $result = $this->sendFile($socket, $filePath);

            @socket_close($socket);

            if (str_contains($result, 'OK')) {
                return new ScanResult(ScanStatus::CLEAN);
            }

            if (str_contains($result, 'FOUND')) {
                return new ScanResult(ScanStatus::INFECTED, trim($result));
            }

            return new ScanResult(ScanStatus::ERROR, "Unexpected response: {$result}");
        } catch (\Throwable $e) {
            if (isset($socket) && $socket !== false) {
                @socket_close($socket);
            }

            return new ScanResult(ScanStatus::ERROR, $e->getMessage());
        }
    }

    public function name(): string
    {
        return 'clamav';
    }

    private function connect(): Socket|false
    {
        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($socket === false) {
            return false;
        }

        @socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, [
            'sec' => (int) $this->timeout,
            'usec' => (int) (($this->timeout - (int) $this->timeout) * 1_000_000),
        ]);

        @socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, [
            'sec' => (int) $this->timeout,
            'usec' => (int) (($this->timeout - (int) $this->timeout) * 1_000_000),
        ]);

        if (! @socket_connect($socket, $this->host, $this->port)) {
            @socket_close($socket);

            return false;
        }

        return $socket;
    }

    private function sendHandshake(Socket $socket): void
    {
        @socket_write($socket, "zINSTREAM\0");
    }

    private function sendFile(Socket $socket, string $filePath): string
    {
        $handle = @fopen($filePath, 'rb');

        if ($handle === false) {
            throw new \RuntimeException("Cannot open file for scanning: {$filePath}");
        }

        try {
            while (! feof($handle)) {
                $chunk = fread($handle, self::CHUNK_SIZE);

                if ($chunk === false || $chunk === '') {
                    break;
                }

                $size = pack('N', strlen($chunk));
                @socket_write($socket, $size);
                @socket_write($socket, $chunk);
            }

            @socket_write($socket, pack('N', 0));

            $response = '';
            while ($chunk = @socket_read($socket, self::CHUNK_SIZE, PHP_BINARY_READ)) {
                if ($chunk === false || $chunk === '') {
                    break;
                }

                $response .= $chunk;
            }

            return $response;
        } finally {
            fclose($handle);
        }
    }
}

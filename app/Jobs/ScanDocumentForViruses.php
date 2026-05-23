<?php

namespace App\Jobs;

use App\Domain\Services\VirusScanner\Contracts\VirusScanner;
use App\Domain\Services\VirusScanner\ScanStatus;
use App\Enums\DocumentScanStatus;
use App\Models\Document;
use App\Models\User;
use App\Notifications\DocumentInfected;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ScanDocumentForViruses implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public Document $document,
    ) {
        $this->onQueue('default');
    }

    public function handle(VirusScanner $scanner): void
    {
        $localPath = $this->downloadToTemp($this->document);

        if ($localPath === null) {
            $this->document->update([
                'scan_status' => DocumentScanStatus::PENDING->value,
            ]);

            return;
        }

        try {
            $result = $scanner->scan($localPath);

            match ($result->status) {
                ScanStatus::CLEAN => $this->markClean(),
                ScanStatus::INFECTED => $this->handleInfected($result->message),
                ScanStatus::ERROR => $this->handleError($result->message),
            };
        } catch (Exception $e) {
            $this->handleError($e->getMessage());
        } finally {
            if (file_exists($localPath)) {
                @unlink($localPath);
            }
        }
    }

    private function downloadToTemp(Document $document): ?string
    {
        $disk = Storage::disk(config('filesystems.default'));

        if (! $disk->exists($document->storage_path)) {
            Log::warning('Document not found for virus scan', [
                'document_id' => $document->id,
                'path' => $document->storage_path,
            ]);

            return null;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'virus_scan_');
        $contents = $disk->get($document->storage_path);

        if ($contents === null) {
            return null;
        }

        file_put_contents($tempPath, $contents);

        return $tempPath;
    }

    private function markClean(): void
    {
        $this->document->update([
            'scan_status' => DocumentScanStatus::CLEAN->value,
            'scanned_at' => now(),
        ]);

        Log::info('Document virus scan clean', [
            'document_id' => $this->document->id,
        ]);
    }

    private function handleInfected(string $message): void
    {
        $storagePath = $this->document->storage_path;

        Storage::disk(config('filesystems.default'))->delete($storagePath);

        $this->document->update([
            'scan_status' => DocumentScanStatus::INFECTED->value,
            'scanned_at' => now(),
        ]);

        activity('document')
            ->performedOn($this->document)
            ->withProperties(['scan_result' => $message])
            ->log('document_infected_deleted');

        $admin = User::where('email', 'sana.bouhamidi@gmail.com')->first();

        if ($admin !== null) {
            $admin->notify(new DocumentInfected($this->document));
        }

        Log::warning('Infected document detected and deleted', [
            'document_id' => $this->document->id,
            'scan_result' => $message,
        ]);
    }

    private function handleError(string $message): void
    {
        $this->document->update([
            'scan_status' => DocumentScanStatus::PENDING->value,
        ]);

        Log::error('Virus scan failed for document', [
            'document_id' => $this->document->id,
            'error' => $message,
        ]);
    }
}

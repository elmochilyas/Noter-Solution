<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class GenerateClientDataExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Client $client,
        public User $requestedBy,
    ) {}

    public function handle(): void
    {
        try {
            $uuid = (string) Str::uuid();
            $tmpDir = storage_path("app/tmp/exports/{$uuid}");
            mkdir($tmpDir, 0755, true);

            $this->generateSummaryPdf($tmpDir);
            $this->generateCsv($tmpDir);
            $this->copyDocuments($tmpDir);

            $zipPath = storage_path("app/tmp/exports/client-{$this->client->id}-{$uuid}.zip");
            $zip = new ZipArchive;

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($tmpDir, \RecursiveDirectoryIterator::SKIP_DOTS)
                );

                foreach ($files as $file) {
                    $relativePath = substr($file->getPathname(), strlen($tmpDir) + 1);
                    $zip->addFile($file->getRealPath(), $relativePath);
                }

                $zip->close();
            }

            $this->cleanupTemp($tmpDir);

            activity()
                ->causedBy($this->requestedBy)
                ->performedOn($this->client)
                ->withProperties(['export_path' => $zipPath])
                ->log('client_data_export_generated');

            Log::info('Client data export generated', [
                'client_id' => $this->client->id,
                'requested_by' => $this->requestedBy->id,
                'path' => $zipPath,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to generate client data export', [
                'client_id' => $this->client->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function generateSummaryPdf(string $dir): void
    {
        $html = view('pdf.client-data-summary', [
            'client' => $this->client,
            'bookings' => $this->client->bookings()->with(['plan', 'payment', 'receipt'])->get(),
        ])->render();

        file_put_contents($dir.'/resume-donnees.html', $html);
    }

    private function generateCsv(string $dir): void
    {
        $bookings = $this->client->bookings()->with(['plan', 'payment', 'receipt'])->get();

        $handle = fopen($dir.'/rendez-vous.csv', 'w');
        fputcsv($handle, ['Reference', 'Date', 'Formule', 'Format', 'Statut', 'Montant', 'Paiement', 'Recu']);

        foreach ($bookings as $booking) {
            fputcsv($handle, [
                $booking->reference,
                $booking->starts_at->format('Y-m-d H:i'),
                $booking->plan?->name ?? '',
                $booking->format,
                $booking->status,
                $booking->total_centimes ? number_format($booking->total_centimes / 100, 2).' MAD' : '',
                $booking->payment?->status ?? '',
                $booking->receipt?->number ?? '',
            ]);
        }

        fclose($handle);
    }

    private function copyDocuments(string $dir): void
    {
        $docsDir = $dir.'/documents';
        mkdir($docsDir, 0755, true);

        foreach ($this->client->documents as $document) {
            if (Storage::exists($document->storage_path)) {
                $ext = pathinfo($document->original_filename, PATHINFO_EXTENSION);
                $destPath = $docsDir.'/'.$document->uuid.'.'.$ext;
                copy(Storage::path($document->storage_path), $destPath);
            }
        }
    }

    private function cleanupTemp(string $dir): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($dir);
    }
}

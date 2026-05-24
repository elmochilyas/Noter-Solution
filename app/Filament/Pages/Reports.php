<?php

namespace App\Filament\Pages;

use App\Models\Receipt;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class Reports extends Page
{
    protected static ?string $navigationLabel = 'Rapports';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    public ?array $data = [];

    public function mount(): void
    {
        $this->view = 'filament.pages.reports';
        $this->form->fill([
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to' => now()->endOfMonth()->format('Y-m-d'),
        ]);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Système';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date_from')->label('Du')->required(),
                DatePicker::make('date_to')->label('Au')->required(),
            ])
            ->statePath('data');
    }

    public function exportCsv(): void
    {
        $state = $this->form->getState();
        $receipts = Receipt::whereBetween('issued_at', [$state['date_from'], $state['date_to']])
            ->with('booking.client')
            ->get();

        if ($receipts->isEmpty()) {
            Notification::make()->title('Aucun reçu dans cette période')->warning()->send();

            return;
        }

        $filename = 'recus-'.$state['date_from'].'-'.$state['date_to'].'.csv';
        $path = 'exports/'.$filename;
        Storage::makeDirectory('exports');

        $handle = fopen(Storage::path($path), 'w');
        fputcsv($handle, ['Numéro', 'Client', 'Email', 'Montant', 'TVA', 'Date']);

        foreach ($receipts as $receipt) {
            fputcsv($handle, [
                $receipt->number,
                $receipt->booking?->client?->full_name,
                $receipt->booking?->client?->email,
                number_format($receipt->amount_centimes / 100, 2, ',', ' '),
                number_format($receipt->vat_centimes / 100, 2, ',', ' '),
                $receipt->issued_at?->format('Y-m-d'),
            ]);
        }

        fclose($handle);

        Notification::make()
            ->title('Export CSV prêt')
            ->body('Fichier: '.$filename)
            ->success()
            ->send();
    }

    public function exportPdfZip(): void
    {
        $state = $this->form->getState();
        $receipts = Receipt::whereBetween('issued_at', [$state['date_from'], $state['date_to']])->get();

        if ($receipts->isEmpty()) {
            Notification::make()->title('Aucun reçu dans cette période')->warning()->send();

            return;
        }

        $zipName = 'recus-'.now()->format('YmdHis').'.zip';
        $zipPath = Storage::path('exports/'.$zipName);
        Storage::makeDirectory('exports');

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            Notification::make()->title('Erreur lors de la création du ZIP')->danger()->send();

            return;
        }

        $receiptsDisk = Storage::disk('receipts');

        foreach ($receipts as $receipt) {
            if ($receiptsDisk->exists($receipt->storage_path)) {
                $contents = $receiptsDisk->get($receipt->storage_path);
                $zip->addFromString("recu-{$receipt->number}.pdf", $contents);
            }
        }

        $zip->close();

        Notification::make()
            ->title('Export ZIP prêt: '.$zipName)
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_csv')
                ->label('Exporter CSV')
                ->action('exportCsv'),
            Action::make('export_pdf_zip')
                ->label('Exporter PDF (ZIP)')
                ->color('warning')
                ->action('exportPdfZip'),
        ];
    }
}

<?php

namespace App\Filament\Resources\ReceiptResource\Pages;

use App\Filament\Resources\ReceiptResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewReceipt extends ViewRecord
{
    protected static string $resource = ReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Télécharger le PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn ($record) => route('admin.downloads.receipt', $record))
                ->openUrlInNewTab(),
        ];
    }
}

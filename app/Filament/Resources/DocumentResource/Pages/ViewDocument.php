<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Télécharger')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn ($record) => route('admin.downloads.document', $record))
                ->openUrlInNewTab(),
        ];
    }
}

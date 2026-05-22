<?php

namespace App\Filament\Resources\ConsultationPlanResource\Pages;

use App\Filament\Resources\ConsultationPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConsultationPlans extends ListRecords
{
    protected static string $resource = ConsultationPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

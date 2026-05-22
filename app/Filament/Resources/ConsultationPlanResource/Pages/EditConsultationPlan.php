<?php

namespace App\Filament\Resources\ConsultationPlanResource\Pages;

use App\Filament\Resources\ConsultationPlanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditConsultationPlan extends EditRecord
{
    protected static string $resource = ConsultationPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

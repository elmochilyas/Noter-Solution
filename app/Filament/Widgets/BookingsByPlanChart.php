<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BookingsByPlanChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Rendez-vous par formule';

    protected function getData(): array
    {
        $plans = DB::table('bookings')
            ->join('consultation_plans', 'bookings.consultation_plan_id', '=', 'consultation_plans.id')
            ->selectRaw('consultation_plans.slug, COUNT(*) as total')
            ->groupBy('consultation_plans.slug')
            ->pluck('total', 'slug');

        return [
            'datasets' => [
                [
                    'label' => 'Formule',
                    'data' => $plans->values(),
                ],
            ],
            'labels' => $plans->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}

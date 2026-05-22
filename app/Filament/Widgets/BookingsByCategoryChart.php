<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class BookingsByCategoryChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Rendez-vous par catégorie';

    protected function getData(): array
    {
        $categories = Booking::selectRaw('service_category, COUNT(*) as total')
            ->groupBy('service_category')
            ->pluck('total', 'service_category');

        return [
            'datasets' => [
                [
                    'label' => 'Catégorie',
                    'data' => $categories->values(),
                ],
            ],
            'labels' => $categories->keys()->map(fn ($key) => __("booking.category.{$key}"))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}

<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BookingsByCategoryChart;
use App\Filament\Widgets\BookingsByPlanChart;
use App\Filament\Widgets\BookingsPerDayChart;
use App\Filament\Widgets\BookingStatsOverview;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\SystemHealthWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Tableau de bord';

    public function getWidgets(): array
    {
        return [
            BookingStatsOverview::class,
            QuickActionsWidget::class,
            BookingsPerDayChart::class,
            BookingsByCategoryChart::class,
            BookingsByPlanChart::class,
            SystemHealthWidget::class,
        ];
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-home';
    }
}

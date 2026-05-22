<?php

namespace App\Filament\Widgets;

use App\Models\NotificationLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemHealthWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $last24h = now()->subDay();

        $totalNotifications = NotificationLog::where('created_at', '>=', $last24h)->count();
        $failedNotifications = NotificationLog::where('created_at', '>=', $last24h)->where('status', 'failed')->count();
        $successRate = $totalNotifications > 0
            ? round((1 - $failedNotifications / $totalNotifications) * 100, 1).'%'
            : 'N/A';

        return [
            Stat::make('Taux succès notifications (24h)', $successRate)
                ->description($totalNotifications.' notifications au total')
                ->color($failedNotifications === 0 ? 'success' : 'danger'),

            Stat::make('Échecs notifications (24h)', $failedNotifications)
                ->description('Sur '.$totalNotifications.' envois')
                ->color($failedNotifications === 0 ? 'success' : 'danger'),
        ];
    }
}

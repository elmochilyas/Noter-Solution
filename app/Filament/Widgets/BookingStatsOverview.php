<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $now = now();
        $weekStart = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();

        return [
            Stat::make('Rendez-vous cette semaine', Booking::whereBetween('starts_at', [$weekStart, $now->copy()->endOfWeek()])->count())
                ->description('Tous statuts confondus')
                ->color('info'),

            Stat::make('Rendez-vous ce mois', Booking::whereMonth('starts_at', $now->month)->whereYear('starts_at', $now->year)->count())
                ->description('Tous statuts confondus')
                ->color('info'),

            Stat::make('Revenus cette semaine', number_format(
                Payment::where('status', 'succeeded')->whereBetween('paid_at', [$weekStart, $now->copy()->endOfWeek()])->sum('amount_centimes') / 100, 2, ',', ' ').' MAD'
            )->description('Paiements réussis')
                ->color('success'),

            Stat::make('Revenus ce mois', number_format(
                Payment::where('status', 'succeeded')->whereBetween('paid_at', [$monthStart, $now->copy()->endOfMonth()])->sum('amount_centimes') / 100, 2, ',', ' ').' MAD'
            )->description('Paiements réussis')
                ->color('success'),

            Stat::make('Paiements en attente', Payment::where('status', 'pending')->count())
                ->description('En attente de confirmation')
                ->color('warning'),

            Stat::make('Rendez-vous actifs (7j)', Booking::whereIn('status', ['confirmed'])
                ->whereBetween('starts_at', [$now, $now->copy()->addDays(7)])
                ->count()
            )->description('Confirmés dans les 7 prochains jours')
                ->color('success'),
        ];
    }
}

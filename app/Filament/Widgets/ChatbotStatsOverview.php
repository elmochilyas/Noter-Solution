<?php

namespace App\Filament\Widgets;

use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChatbotStatsOverview extends BaseWidget
{
    protected static ?int $sort = 7;

    protected function getStats(): array
    {
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        $conversationsThisWeek = ChatbotConversation::where('started_at', '>=', $weekStart)->count();
        $totalConversations = ChatbotConversation::where('started_at', '>=', $monthStart)->count();
        $escalated = ChatbotConversation::where('intent_resolved', 'escalated')
            ->where('started_at', '>=', $monthStart)
            ->count();
        $booked = ChatbotConversation::where('intent_resolved', 'booked')
            ->where('started_at', '>=', $monthStart)
            ->count();

        $deflected = ChatbotConversation::whereIn('intent_resolved', ['info_only', 'faq_query'])
            ->where('started_at', '>=', $monthStart)
            ->count();

        $escalationRate = $totalConversations > 0
            ? round(($escalated / $totalConversations) * 100, 1).'%'
            : '0%';

        $deflectionRate = $totalConversations > 0
            ? round(($deflected / $totalConversations) * 100, 1).'%'
            : '0%';

        $bookingRate = $totalConversations > 0
            ? round(($booked / $totalConversations) * 100, 1).'%'
            : '0%';

        $totalTokens = ChatbotMessage::where('created_at', '>=', $monthStart)
            ->get()
            ->sum(fn (ChatbotMessage $msg) => ($msg->tokens_in ?? 0) + ($msg->tokens_out ?? 0));

        $costPerMillion = 0.50;
        $monthlyCost = round(($totalTokens / 1_000_000) * $costPerMillion, 4);

        return [
            Stat::make('Conversations / semaine', $conversationsThisWeek)
                ->description('Depuis lundi')
                ->color('info'),

            Stat::make('Taux de résolution', $deflectionRate)
                ->description('Sans escalade ni abandon')
                ->color('success'),

            Stat::make('Taux d\'escalade', $escalationRate)
                ->description("{$escalated} escalades")
                ->color($escalated > 5 ? 'danger' : 'warning'),

            Stat::make('Conversion réservation', $bookingRate)
                ->description("{$booked} via triage")
                ->color('success'),

            Stat::make('Coût mensuel', number_format($monthlyCost, 2, ',', ' ').' MAD')
                ->description(($totalTokens / 1_000_000).'M tokens')
                ->color('gray'),
        ];
    }
}

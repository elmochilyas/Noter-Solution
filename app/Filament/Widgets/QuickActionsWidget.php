<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\Refund;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected string $view = 'filament.widgets.quick-actions';

    protected static ?int $sort = 5;

    protected function getViewData(): array
    {
        return [
            'todayBookings' => Booking::whereDate('starts_at', today())->count(),
            'pendingRefunds' => Refund::where('status', 'requested')->count(),
            'unhandledMessages' => ContactMessage::where('is_handled', false)->count(),
        ];
    }
}

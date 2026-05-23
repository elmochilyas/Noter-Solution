<?php

use App\Jobs\PurgeExpiredBookingHolds;
use App\Models\ChatbotConversation;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new PurgeExpiredBookingHolds)->everyFiveMinutes();

// Purge chatbot conversations older than 18 months
Schedule::call(function () {
    ChatbotConversation::where('started_at', '<', now()->subMonths(18))
        ->each(function (ChatbotConversation $conversation) {
            $conversation->messages()->delete();
            $conversation->delete();
        });
})->daily();

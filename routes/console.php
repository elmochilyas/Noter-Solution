<?php

use App\Jobs\PurgeArchivedChatbotConversations;
use App\Jobs\PurgeExpiredBookingHolds;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new PurgeExpiredBookingHolds)->everyFiveMinutes();

// Purge ended chatbot conversations after 30 days (configurable via chatbot.archive_days)
Schedule::job(new PurgeArchivedChatbotConversations)->daily();

<?php

namespace App\Jobs;

use App\Models\ChatbotConversation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PurgeArchivedChatbotConversations implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $cutoff = now()->subDays((int) config('chatbot.archive_days', 30));

        ChatbotConversation::whereNotNull('ended_at')
            ->where('ended_at', '<', $cutoff)
            ->each(function (ChatbotConversation $conversation) {
                $conversation->messages()->delete();
                $conversation->delete();
            });
    }
}

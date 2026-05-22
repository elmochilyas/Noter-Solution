<?php

namespace App\Policies;

use App\Models\ChatbotConversation;
use App\Models\User;

class ChatbotConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function view(User $user, ChatbotConversation $conversation): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }

    public function update(User $user, ChatbotConversation $conversation): bool
    {
        return $user->isOwner() || $user->isAssistant();
    }
}

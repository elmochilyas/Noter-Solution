<?php

namespace App\Domain\Services\Chatbot;

use App\Models\ChatbotConversation;

final class EscalationHandler
{
    private const PHONE = '0666120661';

    private const WHATSAPP_NUMBER = '212666120661';

    public function buildResponse(ChatbotConversation $conversation, ?string $summary = null): array
    {
        $whatsappLink = $this->whatsappDeepLink($summary);

        return [
            'type' => 'escalation',
            'phone' => self::PHONE,
            'whatsapp_link' => $whatsappLink,
            'booking_link' => "/{$conversation->locale}/book",
            'summary_opt_in' => $summary === null,
        ];
    }

    public function shouldEscalate(string $message, string $locale): bool
    {
        $keywords = $locale === 'ar'
            ? ['وكيل', 'بشر', 'تواصل', 'اتصال', 'هاتف', 'شكوى', 'مساعدة']
            : ['agent', 'humain', 'parler', 'téléphone', 'whatsapp', 'aide', 'urgence'];

        $normalized = mb_strtolower(trim($message));

        foreach ($keywords as $word) {
            if (str_contains($normalized, $word)) {
                return true;
            }
        }

        return false;
    }

    public function detectLoop(array $messages): bool
    {
        $userMessages = array_values(array_filter($messages, fn ($msg) => ($msg['role'] ?? '') === 'user'));

        if (count($userMessages) < 2) {
            return false;
        }

        $lastTwo = array_slice($userMessages, -2);

        $user1 = $lastTwo[0]['content'] ?? '';
        $user2 = $lastTwo[1]['content'] ?? '';

        similar_text($user1, $user2, $percent);

        return $percent > 85;
    }

    private function whatsappDeepLink(?string $summary = null): string
    {
        $text = 'Bonjour, je souhaite parler à Maître Bouhamidi.';

        if ($summary) {
            $text .= "\n\nRésumé de ma conversation: {$summary}";
        }

        return 'https://wa.me/'.self::WHATSAPP_NUMBER.'?text='.urlencode($text);
    }
}

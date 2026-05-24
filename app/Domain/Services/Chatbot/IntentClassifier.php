<?php

namespace App\Domain\Services\Chatbot;

use App\Domain\Services\Chatbot\Contracts\LlmClient;
use App\Enums\ChatbotIntent;

final class IntentClassifier
{
    private const KEYWORDS = [
        'ar' => [
            'greeting' => ['السلام', 'مرحبا', 'اهلا', 'صباح', 'مساء', 'hi', 'hello', 'bonjour'],
            'booking' => ['حجز', 'موعد', 'مقابلة', 'أريد', 'ابدأ', 'تحديد', 'booking', 'rdv', 'rendez-vous', 'réservation'],
            'pricing' => ['سعر', 'ثمن', 'كم', 'تكلفة', 'مصاريف', 'أجرة', 'prix', 'tarif', 'combien', 'coût', 'pay'],
            'escalation' => ['وكيل', 'بشر', 'تواصل', 'اتصال', 'رقم', 'هاتف', 'واتساب', 'شكوى', 'agent', 'humain', 'parler', 'téléphoner', 'whatsapp', 'réclamation', 'urgence'],
            'out_of_scope' => ['محام', 'قضية', 'دعوى', 'حكم', 'قانون', 'avocat', 'procès', 'tribunal', 'juge', 'loi pénale', 'pénal', 'divorce judiciaire'],
        ],
        'fr' => [
            'greeting' => ['bonjour', 'salut', 'bonsoir', 'coucou', 'hello', 'hi', 'salem'],
            'booking' => ['rendez-vous', 'rdv', 'réserver', 'réservation', 'booking', 'prendre', 'planifier', 'consultation'],
            'pricing' => ['prix', 'tarif', 'combien', 'coût', 'frais', 'payer', 'pay', 'facture'],
            'escalation' => ['agent', 'humain', 'parler', 'téléphone', 'whatsapp', 'réclamation', 'urgence', 'problème', 'plainte'],
            'out_of_scope' => ['avocat', 'procès', 'tribunal', 'juge', 'droit pénal', 'pénal', 'jugement', 'plainte pénale', 'droit du travail', 'droit commercial'],
        ],
    ];

    public function __construct(
        private readonly ?LlmClient $llm = null,
    ) {}

    public function classify(string $message, string $locale): ChatbotIntent
    {
        $normalized = mb_strtolower(trim($message));
        $locale = $locale === 'ar' ? 'ar' : 'fr';

        $keywords = self::KEYWORDS[$locale] ?? self::KEYWORDS['fr'];

        // Out of scope check first (domain terms that are NOT notary-related)
        foreach ($keywords['out_of_scope'] as $word) {
            if ($this->containsWord($normalized, $word)) {
                return ChatbotIntent::OUT_OF_SCOPE;
            }
        }

        // Greeting
        foreach ($keywords['greeting'] as $word) {
            if ($this->containsWord($normalized, $word)) {
                return ChatbotIntent::GREETING;
            }
        }

        // Escalation
        foreach ($keywords['escalation'] as $word) {
            if ($this->containsWord($normalized, $word)) {
                return ChatbotIntent::ESCALATION;
            }
        }

        // Pricing
        foreach ($keywords['pricing'] as $word) {
            if ($this->containsWord($normalized, $word)) {
                return ChatbotIntent::PRICING_QUERY;
            }
        }

        // Booking
        foreach ($keywords['booking'] as $word) {
            if ($this->containsWord($normalized, $word)) {
                return ChatbotIntent::BOOKING_INTENT;
            }
        }

        // Question words
        $questionWords = $locale === 'ar'
            ? ['ما', 'هل', 'كيف', 'متى', 'أين', 'لماذا', 'من']
            : ['que', 'quoi', 'comment', 'pourquoi', 'est-ce', 'qu\'est', 'qui', 'où', 'quand'];

        foreach ($questionWords as $word) {
            if (str_contains($normalized, $word)) {
                return ChatbotIntent::FAQ_QUERY;
            }
        }

        // Tier-2 LLM fallback for ambiguous messages
        if ($this->llm && $this->isAmbiguous($normalized)) {
            return $this->classifyWithLLM($message, $locale);
        }

        return ChatbotIntent::FAQ_QUERY;
    }

    private function isAmbiguous(string $message): bool
    {
        $length = mb_strlen($message);

        return $length > 10 && $length < 120 && ! str_contains($message, '?');
    }

    private function classifyWithLLM(string $message, string $locale): ChatbotIntent
    {
        try {
            $prompt = 'You are an intent classifier for a notary office chatbot. '
                .'Classify the following user message into exactly one intent. '
                ."Respond with ONLY the intent name, no explanation.\n\n"
                ."Intents:\n"
                ."- FAQ_QUERY: Questions about notary documents, procedures, marriage, inheritance, real estate, etc.\n"
                ."- BOOKING_INTENT: User wants to schedule a consultation or appointment\n"
                ."- PRICING_QUERY: User asks about fees, costs, or pricing\n"
                ."- ESCALATION: User wants to speak to a human, is frustrated, or has an urgent issue\n"
                ."- OUT_OF_SCOPE: Question about criminal law, litigation, labor law, or other non-notary topics\n"
                ."- GREETING: Simple greeting, no question asked\n\n"
                ."Message: {$message}";

            $result = $this->llm->generate($prompt, [], 20, 0.1);
            $result = trim(mb_strtoupper($result));

            return match (true) {
                str_contains($result, 'BOOKING') => ChatbotIntent::BOOKING_INTENT,
                str_contains($result, 'PRICING') => ChatbotIntent::PRICING_QUERY,
                str_contains($result, 'ESCALATION') => ChatbotIntent::ESCALATION,
                str_contains($result, 'OUT_OF_SCOPE') => ChatbotIntent::OUT_OF_SCOPE,
                str_contains($result, 'GREETING') => ChatbotIntent::GREETING,
                default => ChatbotIntent::FAQ_QUERY,
            };
        } catch (\Throwable) {
            return ChatbotIntent::FAQ_QUERY;
        }
    }

    private function containsWord(string $haystack, string $needle): bool
    {
        if (str_contains($needle, ' ')) {
            return str_contains($haystack, $needle);
        }

        return preg_match('/\b'.preg_quote($needle, '/').'/u', $haystack) === 1;
    }
}

<?php

namespace App\Domain\Chatbot;

final readonly class ChatbotResponse
{
    public function __construct(
        public string $answer,
        public array $suggestions = [],
        public ?PlanRecommendation $recommendedPlan = null,
        public bool $escalate = false,
        public bool $outOfScope = false,
    ) {}

    public static function fallback(string $locale = 'fr'): self
    {
        $messages = [
            'fr' => 'Désolé, je n\'ai pas bien compris. Veuillez reformuler votre question ou contactez-nous au 05 28 38 07 19.',
            'ar' => 'عذراً، لم أفهم جيداً. يرجى إعادة صياغة سؤالك أو الاتصال بنا على 05 28 38 07 19.',
        ];

        return new self(
            answer: $messages[$locale] ?? $messages['fr'],
            suggestions: [
                'fr' => ['Reformuler', 'Parler à quelqu\'un'],
                'ar' => ['إعادة الصياغة', 'التحدث مع شخص'],
            ][$locale] ?? ['Reformuler', 'Parler à quelqu\'un'],
            escalate: false,
            outOfScope: false,
        );
    }

    public static function unavailable(string $locale = 'fr'): self
    {
        $messages = [
            'fr' => 'Notre assistant est temporairement indisponible. Vous pouvez nous contacter au 05 28 38 07 19 ou par WhatsApp.',
            'ar' => 'المساعد غير متاح حالياً. يمكنكم الاتصال بنا على 05 28 38 07 19 أو عبر واتساب.',
        ];

        return new self(
            answer: $messages[$locale] ?? $messages['fr'],
            suggestions: [],
            escalate: true,
        );
    }

    public function toArray(): array
    {
        return [
            'answer' => $this->answer,
            'suggestions' => $this->suggestions,
            'recommended_plan' => $this->recommendedPlan ? [
                'slug' => $this->recommendedPlan->slug,
                'category' => $this->recommendedPlan->category,
                'format' => $this->recommendedPlan->format,
                'reason' => $this->recommendedPlan->reason,
            ] : null,
            'escalate' => $this->escalate,
            'out_of_scope' => $this->outOfScope,
        ];
    }
}

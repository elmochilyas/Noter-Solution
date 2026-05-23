<?php

namespace App\Domain\Services\Chatbot;

use App\Domain\Services\Chatbot\Contracts\LlmClient;
use App\Enums\ChatbotIntent;
use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Models\ContactMessage;
use App\Models\Faq;
use Generator;
use Illuminate\Support\Str;

final class ChatbotService
{
    private const SYSTEM_PROMPT_KEY = 'chatbot.system_prompt';

    private const STRICTER_SYSTEM_PROMPT_KEY = 'chatbot.system_prompt_stricter';

    private const HISTORY_LIMIT = 6;

    private const MAX_INPUT_LENGTH = 500;

    private const CEREBRAS_COST_PER_TOKEN = 0.0000005;

    public function __construct(
        private readonly LlmClient $llm,
        private readonly IntentClassifier $classifier,
        private readonly TriageFlow $triage,
        private readonly EscalationHandler $escalation,
        private readonly OutputFilter $filter,
    ) {}

    public function startConversation(string $sessionId, string $locale): ChatbotConversation
    {
        $existing = ChatbotConversation::where('session_id', $sessionId)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if ($existing) {
            return $existing;
        }

        return ChatbotConversation::create([
            'uuid' => (string) Str::uuid(),
            'session_id' => $sessionId,
            'locale' => $locale,
            'metadata' => TriageFlow::initialMetadata(),
            'intent_resolved' => 'info_only',
            'started_at' => now(),
            'last_message_at' => now(),
        ]);
    }

    public function respondTo(ChatbotConversation $conversation, string $userMessage): Generator
    {
        $userMessage = mb_substr(trim($userMessage), 0, self::MAX_INPUT_LENGTH);

        if ($userMessage === '') {
            yield __('chatbot.empty_message');

            return;
        }

        $locale = $conversation->locale;

        $this->recordMessage($conversation, 'user', $userMessage);

        $intent = $this->classifier->classify($userMessage, $locale);

        if ($intent === ChatbotIntent::OUT_OF_SCOPE) {
            $response = __('chatbot.out_of_scope');
            $this->recordMessage($conversation, 'assistant', $response);
            $conversation->intent_resolved = 'out_of_scope';
            $conversation->last_message_at = now();
            $conversation->save();
            yield $response;

            return;
        }

        if ($intent === ChatbotIntent::ESCALATION) {
            $response = $this->handleEscalation($conversation);
            yield $response;

            return;
        }

        if ($intent === ChatbotIntent::GREETING) {
            $response = __('chatbot.greeting_response');
            $this->recordMessage($conversation, 'assistant', $response);
            yield $response;

            return;
        }

        $history = $this->getConversationHistory($conversation);

        if ($this->escalation->detectLoop($history)) {
            $response = $this->handleEscalation($conversation);
            yield $response;

            return;
        }

        $faqs = $this->retrieveFaqs($userMessage, $locale);
        $faqContext = $this->buildFaqContext($faqs);

        $metadata = $conversation->metadata ?? [];

        // Handle active triage flow
        if (($metadata['triage_state'] ?? 'idle') === 'active') {
            $step = $metadata['triage_step'] ?? 'category';
            $next = $this->triage->processStep($step, $userMessage, $metadata);

            $conversation->metadata = $metadata;

            if ($next === null) {
                $conversation->intent_resolved = 'booked';
                $conversation->save();

                yield $this->buildRecommendation($metadata, $locale);

                return;
            }

            $conversation->save();
            yield $next;

            return;
        }

        if ($intent === ChatbotIntent::BOOKING_INTENT) {
            $response = $this->triage->start($metadata);
            $conversation->metadata = $metadata;
            $conversation->save();

            $this->recordMessage($conversation, 'assistant', $response);
            yield $response;

            return;
        }

        // Main LLM generation with output filtering
        $systemPrompt = __(self::SYSTEM_PROMPT_KEY, [], $locale);
        $llmMessages = $this->buildLlmMessages($history, $userMessage, $faqContext);

        $rawResponse = $this->llm->generate($systemPrompt, $llmMessages);

        // 2+ violations → escalate immediately
        if ($this->filter->filter($rawResponse) === 'ESCALATE') {
            $cleaned = $this->filter->clean($rawResponse);
            yield $cleaned."\n\n".__('chatbot.escalation_suggestion');

            return;
        }

        // 1 violation → regenerate once with stricter prompt
        if ($this->filter->hasViolations($rawResponse)) {
            $stricterSystemPrompt = __(self::STRICTER_SYSTEM_PROMPT_KEY, [], $locale);
            $regenerated = $this->llm->generate($stricterSystemPrompt, $llmMessages, 400, 0.2);

            if ($this->filter->filter($regenerated) === 'ESCALATE') {
                $cleaned = $this->filter->clean($regenerated);
                yield $cleaned."\n\n".__('chatbot.escalation_suggestion');

                return;
            }

            if ($this->filter->hasViolations($regenerated)) {
                $cleaned = $this->filter->clean($regenerated);
                yield $cleaned."\n\n".__('chatbot.escalation_suggestion');

                return;
            }

            $this->recordMessage($conversation, 'assistant', $regenerated, $faqs->pluck('id')->toArray());
            $conversation->last_message_at = now();
            $conversation->save();
            yield $regenerated;

            return;
        }

        $this->recordMessage($conversation, 'assistant', $rawResponse, $faqs->pluck('id')->toArray());
        $conversation->last_message_at = now();
        $conversation->save();
        yield $rawResponse;
    }

    public function escalateToHuman(ChatbotConversation $conversation, ?string $summary = null): array
    {
        $escalation = $this->escalation->buildResponse($conversation, $summary);

        if ($summary) {
            ContactMessage::create([
                'name' => 'Chatbot - '.($conversation->client?->full_name ?? 'Anonyme'),
                'email' => $conversation->client?->email ?? 'chatbot@noter.ma',
                'subject' => 'Résumé de conversation chatbot #'.$conversation->uuid,
                'message' => "Résumé généré automatiquement:\n\n{$summary}\n\n---\nConversation complète dans Filament.",
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'is_handled' => false,
            ]);
        }

        $conversation->intent_resolved = 'escalated';
        $conversation->ended_at = now();
        $conversation->save();

        $phone = $escalation['phone'];
        $whatsapp = $escalation['whatsapp_link'];

        $response = __('chatbot.escalation_message', [
            'phone' => $phone,
            'whatsapp_link' => $whatsapp,
            'booking_link' => $escalation['booking_link'],
        ]);

        $this->recordMessage($conversation, 'assistant', $response);

        return [
            'response' => $response,
            'escalation' => $escalation,
        ];
    }

    public function getMonthlyCost(): float
    {
        $monthStart = now()->startOfMonth();

        $totalTokens = ChatbotMessage::where('created_at', '>=', $monthStart)
            ->get()
            ->sum(fn (ChatbotMessage $msg) => ($msg->tokens_in ?? 0) + ($msg->tokens_out ?? 0));

        return round($totalTokens * self::CEREBRAS_COST_PER_TOKEN, 4);
    }

    public function isBudgetExhausted(): bool
    {
        $budget = (float) config('services.cerebras.monthly_budget', 5.0);
        $spent = $this->getMonthlyCost();

        return $spent >= $budget;
    }

    public function isBudgetWarning(): bool
    {
        $budget = (float) config('services.cerebras.monthly_budget', 5.0);
        $spent = $this->getMonthlyCost();

        return $budget > 0 && ($spent / $budget) >= 0.8;
    }

    private function handleEscalation(ChatbotConversation $conversation): string
    {
        $result = $this->escalateToHuman($conversation);

        return $result['response'];
    }

    private function retrieveFaqs(string $query, string $locale): iterable
    {
        return Faq::where('is_published', true)
            ->where("question_translations->{$locale}", 'like', '%'.$query.'%')
            ->orWhere("answer_translations->{$locale}", 'like', '%'.$query.'%')
            ->orderBy('display_order')
            ->limit(5)
            ->get();
    }

    private function buildFaqContext(iterable $faqs): string
    {
        $lines = [];

        foreach ($faqs as $faq) {
            $translations = is_string($faq->question_translations)
                ? json_decode($faq->question_translations, true)
                : $faq->question_translations;

            $answerTranslations = is_string($faq->answer_translations)
                ? json_decode($faq->answer_translations, true)
                : $faq->answer_translations;

            $question = is_array($translations) ? ($translations[app()->getLocale()] ?? '') : '';
            $answer = is_array($answerTranslations) ? ($answerTranslations[app()->getLocale()] ?? '') : '';

            if ($question && $answer) {
                $lines[] = "Q: {$question}\nA: {$answer}";
            }
        }

        return $lines ? "\n\n<contexte FAQ>\n".implode("\n\n", $lines)."\n</contexte>" : '';
    }

    private function buildLlmMessages(array $history, string $userMessage, string $faqContext): array
    {
        $messages = [];

        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $content = $userMessage;
        if ($faqContext) {
            $content .= $faqContext;
        }

        $messages[] = ['role' => 'user', 'content' => $content];

        return $messages;
    }

    private function getConversationHistory(ChatbotConversation $conversation): array
    {
        return ChatbotMessage::where('conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->latest()
            ->take(self::HISTORY_LIMIT)
            ->get()
            ->reverse()
            ->map(fn (ChatbotMessage $msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->values()
            ->toArray();
    }

    private function recordMessage(
        ChatbotConversation $conversation,
        string $role,
        string $content,
        array $retrievedFaqIds = [],
    ): ChatbotMessage {
        return ChatbotMessage::create([
            'conversation_id' => $conversation->id,
            'role' => $role,
            'content' => $content,
            'retrieved_faq_ids' => $retrievedFaqIds,
            'tokens_in' => $role === 'user' ? $this->llm->countTokens($content) : null,
            'tokens_out' => $role === 'assistant' ? $this->llm->countTokens($content) : null,
            'latency_ms' => null,
            'created_at' => now(),
        ]);
    }

    private function buildRecommendation(array $metadata, string $locale): string
    {
        $url = TriageFlow::buildBookingUrl($metadata, $locale);
        $categoryLabels = [
            'family' => __('chatbot.category_family', [], $locale),
            'real_estate' => __('chatbot.category_real_estate', [], $locale),
            'financial' => __('chatbot.category_financial', [], $locale),
            'contracts' => __('chatbot.category_contracts', [], $locale),
            'other' => __('chatbot.category_other', [], $locale),
        ];

        $categoryLabel = $categoryLabels[$metadata['category'] ?? 'other'] ?? '';

        $formatLabel = $metadata['format'] === 'video'
            ? __('chatbot.format_video', [], $locale)
            : __('chatbot.format_in_person', [], $locale);

        $lines = [
            __('chatbot.recommendation_header', [], $locale),
            '',
            __('chatbot.recommendation_category', ['category' => $categoryLabel], $locale),
            __('chatbot.recommendation_format', ['format' => $formatLabel], $locale),
            '',
            '<a href="'.e($url).'" class="btn-primary">'.__('chatbot.recommendation_book_button', [], $locale).'</a>',
        ];

        return implode("\n", $lines);
    }
}

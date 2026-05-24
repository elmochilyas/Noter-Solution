<?php

namespace App\Domain\Services\Chatbot;

use App\Domain\Chatbot\ChatbotResponse;
use App\Domain\Chatbot\LlmRequest;
use App\Domain\Chatbot\LlmResponse;
use App\Domain\Chatbot\PlanRecommendation;
use App\Domain\Services\Chatbot\Contracts\LlmClient;
use App\Enums\ChatbotIntent;
use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Models\ConsultationPlan;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Services\Chatbot\ChatbotResponseParser;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Sentry;

final class ChatbotService
{
    private const SYSTEM_PROMPT_KEY = 'chatbot.system_prompt';

    private const STRICTER_SYSTEM_PROMPT_KEY = 'chatbot.system_prompt_stricter';

    private const HISTORY_TOKEN_BUDGET = 1500;

    private const MAX_INPUT_LENGTH = 500;

    private const TRIAGE_ABANDON_PROMPT_SUFFIX = "\n\nNOTE : L'utilisateur était en train de remplir un formulaire de prise de rendez-vous mais a changé d'avis et a posé une question libre. Ignore le formulaire, réponds à sa question normalement.";

    public function __construct(
        private readonly LlmClient $llm,
        private readonly IntentClassifier $classifier,
        private readonly TriageFlow $triage,
        private readonly EscalationHandler $escalation,
        private readonly OutputFilter $filter,
        private readonly ChatbotResponseParser $parser,
        private readonly ChipFilter $chipFilter,
        private readonly RepetitionGuard $repetitionGuard,
    ) {}

    public function startConversation(string $sessionId, string $locale): ChatbotConversation
    {
        $existing = ChatbotConversation::where('session_id', $sessionId)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if ($existing) {
            $timeoutMinutes = (int) config('chatbot.idle_timeout_minutes', 15);

            if ($existing->last_message_at && $existing->last_message_at->lessThan(now()->subMinutes($timeoutMinutes))) {
                $existing->ended_at = now();
                $existing->save();
            } else {
                return $existing;
            }
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

    public function respondTo(ChatbotConversation $conversation, string $userMessage): ChatbotResponse
    {
        $timeoutMinutes = (int) config('chatbot.idle_timeout_minutes', 15);

        if ($conversation->last_message_at && $conversation->last_message_at->lessThan(now()->subMinutes($timeoutMinutes))) {
            $conversation->ended_at = now();
            $conversation->save();

            return new ChatbotResponse(
                answer: __('chatbot.session_expired', [], $conversation->locale),
            );
        }

        $userMessage = mb_substr(trim($userMessage), 0, self::MAX_INPUT_LENGTH);

        if ($userMessage === '') {
            return new ChatbotResponse(
                answer: __('chatbot.empty_message', [], $conversation->locale),
            );
        }

        $locale = $conversation->locale;

        $this->recordMessage($conversation, 'user', $userMessage);

        $intent = $this->classifier->classify($userMessage, $locale);

        if ($intent === ChatbotIntent::OUT_OF_SCOPE) {

            $response = new ChatbotResponse(
                answer: __('chatbot.out_of_scope', [], $locale),
                outOfScope: true,
            );
            $this->recordMessage($conversation, 'assistant', $response->answer);
            $conversation->intent_resolved = 'out_of_scope';
            $conversation->last_message_at = now();
            $conversation->save();

            return $response;
        }

        if ($intent === ChatbotIntent::ESCALATION) {
            $result = $this->escalateToHuman($conversation);

            return new ChatbotResponse(
                answer: $result['response'],
                escalate: true,
            );
        }

        if ($intent === ChatbotIntent::GREETING) {
            $response = new ChatbotResponse(
                answer: __('chatbot.greeting_response', [], $locale),
                suggestions: config('chatbot.greeting_chips.'.$locale, []),
            );
            $this->recordMessage($conversation, 'assistant', $response->answer);
            $conversation->last_message_at = now();
            $conversation->save();

            return $response;
        }

        $history = $this->getConversationHistory($conversation);

        if ($this->escalation->detectLoop($history)) {
            $result = $this->escalateToHuman($conversation);

            return new ChatbotResponse(
                answer: $result['response'],
                escalate: true,
            );
        }

        $faqs = $this->retrieveFaqs($userMessage, $locale);
        $faqContext = $this->buildFaqContext($faqs, $locale);

        $metadata = $conversation->metadata ?? [];
        $triageAbandoned = false;

        if (($metadata['triage_state'] ?? 'idle') === 'active') {
            $metadata['triage_state'] = 'idle';
            $metadata['triage_step'] = null;
            $conversation->metadata = $metadata;
            $conversation->save();
            $triageAbandoned = true;
        }

        if ($intent === ChatbotIntent::BOOKING_INTENT) {
            $response = $this->triage->start($metadata);
            $conversation->metadata = $metadata;
            $conversation->save();

            $this->recordMessage($conversation, 'assistant', $response);

            return new ChatbotResponse(
                answer: $response,
                suggestions: $this->triageSuggestions('category', $locale),
            );
        }

        // Main LLM generation with structured response
        return $this->generateStructuredResponse($conversation, $history, $userMessage, $faqContext, $faqs, $locale, $triageAbandoned);
    }

    public function handleTriageChipClick(ChatbotConversation $conversation, string $chipValue): ChatbotResponse
    {
        $metadata = $conversation->metadata;

        if (($metadata['triage_state'] ?? 'idle') !== 'active') {
            throw new \RuntimeException('handleTriageChipClick called when triage is not active');
        }

        $this->recordMessage($conversation, 'user', $this->translateChipLabel($chipValue, $conversation->locale));

        $next = $this->triage->processStep($metadata['triage_step'] ?? 'category', $chipValue, $metadata);

        $conversation->metadata = $metadata;
        $conversation->last_message_at = now();

        if ($next === null) {
            $conversation->intent_resolved = 'booked';
            $conversation->save();

            return $this->buildRecommendationResponse($metadata, $conversation->locale);
        }

        $conversation->save();

        return new ChatbotResponse(
            answer: $next,
            suggestions: $this->triageSuggestions($metadata['triage_step'], $conversation->locale),
        );
    }

    public function escalateToHuman(ChatbotConversation $conversation, ?string $summary = null): array
    {
        $escalation = $this->escalation->buildResponse($conversation);

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
        ], $conversation->locale);

        $this->recordMessage($conversation, 'assistant', $response);

        return [
            'response' => $response,
            'escalation' => $escalation,
        ];
    }

    public function getMonthlyCost(): float
    {
        $monthStart = now()->startOfMonth();

        $totalInput = ChatbotMessage::where('created_at', '>=', $monthStart)
            ->where('role', 'assistant')
            ->sum('tokens_in');

        $totalOutput = ChatbotMessage::where('created_at', '>=', $monthStart)
            ->where('role', 'assistant')
            ->sum('tokens_out');

        $inputCost = ($totalInput / 1_000_000) * config('chatbot.pricing.input_per_million', 0.35);
        $outputCost = ($totalOutput / 1_000_000) * config('chatbot.pricing.output_per_million', 0.75);

        return round($inputCost + $outputCost, 4);
    }

    public function isBudgetExhausted(): bool
    {
        $budget = (float) config('chatbot.pricing.monthly_budget', 5.0);

        return $this->getMonthlyCost() >= $budget;
    }

    public function isBudgetWarning(): bool
    {
        $budget = (float) config('chatbot.pricing.monthly_budget', 5.0);

        return $budget > 0 && ($this->getMonthlyCost() / $budget) >= 0.8;
    }

    private function generateStructuredResponse(
        ChatbotConversation $conversation,
        array $history,
        string $userMessage,
        string $faqContext,
        iterable $faqs,
        string $locale,
        bool $triageAbandoned = false,
    ): ChatbotResponse {
        $systemPrompt = $this->buildSystemPrompt($locale);
        if ($triageAbandoned) {
            $systemPrompt .= self::TRIAGE_ABANDON_PROMPT_SUFFIX;
        }
        $llmMessages = $this->buildLlmMessages($history, $userMessage, $faqContext);

        try {
            [$parsed, $llmResponse] = $this->generateWithRetry(
                systemPrompt: $systemPrompt,
                llmMessages: $llmMessages,
                locale: $locale,
                conversationId: $conversation->id,
                maxTokens: 800,
                temperature: 0.3,
                isRegeneration: false,
            );

            $allowedAmounts = $this->filter->getAllowedAmounts();
            $violations = $this->filter->violationCount($parsed->answer, $locale, $allowedAmounts);

            if ($violations >= 2) {
                Log::warning('Chatbot output filter: 2+ violations, escalate', [
                    'conversation_id' => $conversation->id,
                ]);

                $cleaned = $this->filter->clean($parsed->answer, $allowedAmounts);
                $parsed = new ChatbotResponse(
                    answer: $cleaned."\n\n".__('chatbot.escalation_suggestion', [], $locale),
                    escalate: true,
                );

                $this->recordMessage($conversation, 'assistant', $parsed->answer, $faqs->pluck('id')->toArray(), $llmResponse);

                return $parsed;
            }

            if ($violations >= 1) {
                Log::info('Chatbot output filter: 1 violation, regenerating', [
                    'conversation_id' => $conversation->id,
                ]);

                [$parsed, $llmResponse] = $this->generateWithRetry(
                    systemPrompt: $this->buildStricterPrompt($locale),
                    llmMessages: $llmMessages,
                    locale: $locale,
                    conversationId: $conversation->id,
                    maxTokens: 400,
                    temperature: 0.2,
                    isRegeneration: true,
                );

                $reViolations = $this->filter->violationCount($parsed->answer, $locale, $allowedAmounts);

                if ($reViolations > 0) {
                    $cleaned = $this->filter->clean($parsed->answer, $allowedAmounts);
                    $parsed = new ChatbotResponse(
                        answer: $cleaned."\n\n".__('chatbot.escalation_suggestion', [], $locale),
                        escalate: true,
                    );

                    $this->recordMessage($conversation, 'assistant', $parsed->answer, $faqs->pluck('id')->toArray(), $llmResponse);

                    return $parsed;
                }
            }

            // Apply chip filter for anti-redundancy
            $priorUserMessages = $this->getPriorUserMessages($conversation);
            $priorSuggestions = $this->getPriorSuggestions($conversation);
            $recentAnswers = $this->getRecentAssistantAnswers($conversation);

            $filteredSuggestions = $this->chipFilter->filter(
                suggestions: $parsed->suggestions,
                priorUserMessages: $priorUserMessages,
                priorSuggestions: $priorSuggestions,
                recentAssistantAnswers: $recentAnswers,
                locale: $locale,
            );

            // Apply repetition guard
            $recentAssistantAnswers = $this->getRecentAssistantAnswers($conversation, 3);
            $repetitionVerdict = $this->repetitionGuard->check($parsed->answer, $recentAssistantAnswers);

            $regenerationAttempts = 0;
            while ($repetitionVerdict === RepetitionVerdict::REGENERATE && $regenerationAttempts < 2) {
                $regenerationAttempts++;
                Log::info('Chatbot repetition guard: regenerating', [
                    'conversation_id' => $conversation->id,
                    'attempt' => $regenerationAttempts,
                ]);

                $stricterSystemPrompt = $this->buildStricterPrompt($locale);

                [$parsed, $llmResponse] = $this->generateWithRetry(
                    systemPrompt: $stricterSystemPrompt,
                    llmMessages: $llmMessages,
                    locale: $locale,
                    conversationId: $conversation->id,
                    maxTokens: 400,
                    temperature: 0.2,
                    isRegeneration: true,
                );

                $repetitionVerdict = $this->repetitionGuard->check($parsed->answer, $recentAssistantAnswers);
            }

            if ($repetitionVerdict === RepetitionVerdict::FALLBACK || ($regenerationAttempts >= 2 && $repetitionVerdict === RepetitionVerdict::REGENERATE)) {
                Log::warning('Chatbot repetition guard: fallback activated', [
                    'conversation_id' => $conversation->id,
                ]);

                $parsed = new ChatbotResponse(
                    answer: __('chatbot.repetition_fallback', [], $locale),
                    suggestions: [],
                    recommendedPlan: null,
                    escalate: true,
                );
            } else {
                $parsed = new ChatbotResponse(
                    answer: $parsed->answer,
                    suggestions: $filteredSuggestions,
                    recommendedPlan: $parsed->recommendedPlan,
                    escalate: $parsed->escalate,
                    outOfScope: $parsed->outOfScope,
                );
            }

            if ($parsed->outOfScope) {
                $conversation->intent_resolved = 'out_of_scope';
            }

            if ($parsed->recommendedPlan !== null) {
                $plan = ConsultationPlan::where('slug', $parsed->recommendedPlan->slug)->first();

                if ($plan) {
                    $conversation->intent_resolved = 'booked';
                }
            }

            $this->recordMessage(
                $conversation,
                'assistant',
                $parsed->answer,
                $faqs->pluck('id')->toArray(),
                $llmResponse,
            );

            $conversation->last_message_at = now();
            $conversation->save();
        } catch (\Throwable $e) {
            Log::error('Chatbot generation failed', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);

            if (class_exists('\Sentry')) {
                Sentry\captureException($e);
            }

            return ChatbotResponse::fallback($locale);
        }

        return $parsed;
    }

    private function generateWithRetry(
        string $systemPrompt,
        array $llmMessages,
        string $locale,
        int $conversationId,
        int $maxTokens = 800,
        float $temperature = 0.3,
        bool $isRegeneration = false,
    ): array {
        $request = new LlmRequest(
            system: $systemPrompt,
            messages: $llmMessages,
            maxTokens: $maxTokens,
            temperature: $temperature,
            responseFormat: 'json_object',
        );

        $llmResponse = $this->llm->complete($request);
        $parsed = $this->parser->parse($llmResponse->content, $locale, (string) $conversationId);

        return [$parsed, $llmResponse];
    }

    private function retrieveFaqs(string $query, string $locale): iterable
    {
        return Faq::where('is_published', true)
            ->where(function ($q) use ($locale, $query) {
                $q->where("question_translations->{$locale}", 'like', '%'.$query.'%')
                    ->orWhere("answer_translations->{$locale}", 'like', '%'.$query.'%');
            })
            ->orderBy('display_order')
            ->limit(5)
            ->get();
    }

    private function buildFaqContext(iterable $faqs, string $locale): string
    {
        $lines = [];

        foreach ($faqs as $faq) {
            $translations = is_string($faq->question_translations)
                ? json_decode($faq->question_translations, true)
                : $faq->question_translations;

            $answerTranslations = is_string($faq->answer_translations)
                ? json_decode($faq->answer_translations, true)
                : $faq->answer_translations;

            $question = is_array($translations) ? ($translations[$locale] ?? '') : '';
            $answer = is_array($answerTranslations) ? ($answerTranslations[$locale] ?? '') : '';

            if ($question && $answer) {
                $lines[] = "Q: {$question}\nA: {$answer}";
            }
        }

        return $lines ? "\n\n<context>\n".implode("\n\n", $lines)."\n</context>" : '';
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

    private function buildSystemPrompt(string $locale): string
    {
        return __(self::SYSTEM_PROMPT_KEY, [], $locale);
    }

    private function buildStricterPrompt(string $locale): string
    {
        return __(self::STRICTER_SYSTEM_PROMPT_KEY, [], $locale);
    }

    private function getConversationHistory(ChatbotConversation $conversation): array
    {
        $messages = ChatbotMessage::where('conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->get();

        $history = [];
        $tokenCount = 0;

        foreach ($messages->reverse() as $msg) {
            $tokens = $this->llm->countTokens($msg->content);
            if ($tokenCount + $tokens > self::HISTORY_TOKEN_BUDGET) {
                break;
            }
            $history[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
            $tokenCount += $tokens;
        }

        // Remove the most recent message (current user turn) — it will be
        // appended last by buildLlmMessages() with the FAQ context block.
        if (! empty($history)) {
            array_shift($history);
        }

        return array_reverse($history);
    }

    private function getPriorUserMessages(ChatbotConversation $conversation): array
    {
        return ChatbotMessage::where('conversation_id', $conversation->id)
            ->where('role', 'user')
            ->orderBy('created_at')
            ->pluck('content')
            ->toArray();
    }

    private function getPriorSuggestions(ChatbotConversation $conversation): array
    {
        return ChatbotMessage::where('conversation_id', $conversation->id)
            ->where('role', 'assistant')
            ->orderBy('created_at')
            ->pluck('content')
            ->toArray();
    }

    private function getRecentAssistantAnswers(ChatbotConversation $conversation, int $count = 3): array
    {
        return ChatbotMessage::where('conversation_id', $conversation->id)
            ->where('role', 'assistant')
            ->orderBy('created_at', 'desc')
            ->take($count)
            ->pluck('content')
            ->toArray();
    }

    private function translateChipLabel(string $value, string $locale): string
    {
        $categoryKey = 'chatbot.category_'.$value;
        if (Lang::has($categoryKey, $locale)) {
            return __($categoryKey, [], $locale);
        }
        $triageKey = 'chatbot.triage_'.$value;
        if (Lang::has($triageKey, $locale)) {
            return __($triageKey, [], $locale);
        }

        return $value;
    }

    private function recordMessage(
        ChatbotConversation $conversation,
        string $role,
        string $content,
        array $retrievedFaqIds = [],
        ?LlmResponse $llmResponse = null,
    ): ChatbotMessage {
        return ChatbotMessage::create([
            'conversation_id' => $conversation->id,
            'role' => $role,
            'content' => $content,
            'retrieved_faq_ids' => $retrievedFaqIds,
            'tokens_in' => $llmResponse?->tokensIn,
            'tokens_out' => $llmResponse?->tokensOut,
            'latency_ms' => $llmResponse?->latencyMs,
            'created_at' => now(),
        ]);
    }

    private function triageSuggestions(string $step, string $locale): array
    {
        return match ($step) {
            'category' => ['family', 'real_estate', 'financial', 'contracts', 'other'],
            'has_documents' => ['yes', 'no'],
            'format' => ['in_person', 'video', 'indifferent'],
            'urgency' => ['this_week', 'this_month', 'flexible'],
            default => [],
        };
    }

    private function buildRecommendationResponse(array $metadata, string $locale): ChatbotResponse
    {
        $url = TriageFlow::buildBookingUrl($metadata, $locale);
        $category = $metadata['category'] ?? 'other';

        $planSlug = match ($category) {
            'family' => 'standard-online',
            'real_estate' => 'in-office',
            'financial' => 'standard-online',
            'contracts' => 'standard-online',
            default => 'free-orientation',
        };

        $format = $metadata['format'] === 'video' ? 'online' : 'in_office';

        try {
            $planRec = new PlanRecommendation(
                slug: $planSlug,
                category: $category,
                format: $format,
                reason: __('chatbot.recommendation_reason', [], $locale),
            );
        } catch (\InvalidArgumentException) {
            $planRec = null;
        }

        return new ChatbotResponse(
            answer: __('chatbot.recommendation_header', [], $locale),
            suggestions: [],
            recommendedPlan: $planRec,
        );
    }
}

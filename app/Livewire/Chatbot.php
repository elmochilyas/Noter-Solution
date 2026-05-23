<?php

namespace App\Livewire;

use App\Domain\Services\Chatbot\ChatbotService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Livewire\Component;

class Chatbot extends Component
{
    private const DISCLAIMER_COOKIE = 'chatbot_disclaimer';

    private const DISCLAIMER_DURATION = 90;

    public bool $open = false;

    public bool $disclaimerAccepted = false;

    public string $input = '';

    public array $messages = [];

    public array $suggestions = [];

    public bool $isTyping = false;

    public ?string $error = null;

    protected $conversation = null;

    protected ChatbotService $chatbotService;

    public function boot(ChatbotService $chatbotService): void
    {
        $this->chatbotService = $chatbotService;
    }

    public function mount(): void
    {
        $this->suggestions = $this->defaultSuggestions();

        // Check 90-day cookie for disclaimer
        if (Cookie::has(self::DISCLAIMER_COOKIE) || session()->has('chatbot_disclaimer_accepted')) {
            $this->disclaimerAccepted = true;
            $this->ensureConversation();
        }
    }

    public function acceptDisclaimer(): void
    {
        $this->disclaimerAccepted = true;

        // Set 90-day cookie
        Cookie::queue(self::DISCLAIMER_COOKIE, true, self::DISCLAIMER_DURATION * 24 * 60);

        session()->put('chatbot_disclaimer_accepted', true);
        $this->ensureConversation();
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
        $this->error = null;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function send(): void
    {
        $this->validateInput();

        if ($this->input === '' || $this->isTyping) {
            return;
        }

        // Rate limiting check
        if ($this->isRateLimited()) {
            $this->error = __('chatbot.rate_limit_exceeded');
            $this->input = '';

            return;
        }

        $userMessage = $this->input;
        $this->input = '';
        $this->error = null;

        // Track message for rate limiting
        $this->trackRateLimit();

        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'created_at' => now()->toIso8601String(),
        ];

        $this->isTyping = true;
        $this->suggestions = [];

        try {
            $this->ensureConversation();

            // Detect mid-conversation language switch
            $this->detectLanguageSwitch($userMessage);

            // Check budget before calling LLM
            if ($this->chatbotService->isBudgetExhausted()) {
                $this->isTyping = false;
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => __('chatbot.service_unavailable'),
                    'created_at' => now()->toIso8601String(),
                ];

                return;
            }

            $response = $this->chatbotService->respondTo($this->conversation, $userMessage);

            $fullResponse = '';

            foreach ($response as $chunk) {
                $fullResponse .= $chunk;

                // Check for client disconnect between chunks
                if (connection_aborted()) {
                    break;
                }
            }

            $this->isTyping = false;

            if ($fullResponse !== '') {
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => $fullResponse,
                    'created_at' => now()->toIso8601String(),
                ];

                $this->suggestions = $this->contextualSuggestions($fullResponse);
            }
        } catch (\Throwable $e) {
            $this->isTyping = false;

            if (config('app.debug')) {
                $this->error = __('chatbot.error_fallback').' '.$e->getMessage();
            } else {
                $this->error = __('chatbot.error_fallback');
                \Sentry\captureException($e);
            }

            $this->messages[] = [
                'role' => 'assistant',
                'content' => __('chatbot.error_fallback_contact'),
                'created_at' => now()->toIso8601String(),
            ];
        }
    }

    public function sendSuggestion(string $suggestion): void
    {
        $this->input = $suggestion;
        $this->send();
    }

    public function render()
    {
        return view('livewire.chatbot');
    }

    private function detectLanguageSwitch(string $message): void
    {
        if (! $this->conversation) {
            return;
        }

        $currentLocale = $this->conversation->locale;
        $arabicChars = preg_match('/[\x{0600}-\x{06FF}]/u', $message);
        $latinChars = preg_match('/[a-zA-Z]/', $message);

        $detected = 'fr';
        if ($arabicChars > $latinChars) {
            $detected = 'ar';
        } elseif ($latinChars > $arabicChars && preg_match('/[a-zA-Z]{4,}/', $message)) {
            $detected = 'fr';
        } else {
            return;
        }

        // Only switch after 2+ messages in the new language
        if ($detected !== $currentLocale) {
            $userMessages = collect($this->messages)->where('role', 'user')->values();
            $recentUserMessages = $userMessages->slice(-2);

            $arabicCount = 0;
            $latinCount = 0;

            foreach ($recentUserMessages as $msg) {
                $content = $msg['content'] ?? '';
                if (preg_match('/[\x{0600}-\x{06FF}]/u', $content)) {
                    $arabicCount++;
                } elseif (preg_match('/[a-zA-Z]{4,}/', $content)) {
                    $latinCount++;
                }
            }

            if (($detected === 'ar' && $arabicCount >= 2) || ($detected === 'fr' && $latinCount >= 2)) {
                $this->conversation->locale = $detected;
                $this->conversation->save();

                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => $detected === 'ar'
                        ? __('chatbot.language_switch_ar')
                        : __('chatbot.language_switch_fr'),
                    'created_at' => now()->toIso8601String(),
                ];
            }
        }
    }

    private function ensureConversation(): void
    {
        if ($this->conversation) {
            return;
        }

        $sessionId = session()->getId();
        $locale = app()->getLocale();

        $this->conversation = $this->chatbotService->startConversation($sessionId, $locale);

        if (empty($this->messages)) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => __('chatbot.initial_message'),
                'created_at' => now()->toIso8601String(),
            ];
        }
    }

    private function validateInput(): void
    {
        $this->input = mb_substr(trim($this->input ?? ''), 0, 500);
    }

    private function defaultSuggestions(): array
    {
        return [
            __('chatbot.suggestion_documents'),
            __('chatbot.suggestion_booking'),
            __('chatbot.suggestion_pricing'),
        ];
    }

    private function contextualSuggestions(string $response): array
    {
        $suggestions = [];

        if (str_contains($response, '?')) {
            $suggestions[] = __('chatbot.suggestion_more_details');
        }

        $suggestions[] = __('chatbot.suggestion_documents');
        $suggestions[] = __('chatbot.suggestion_booking');
        $suggestions[] = __('chatbot.suggestion_speak_to_human');

        return array_slice($suggestions, 0, 4);
    }

    private function isRateLimited(): bool
    {
        $sessionKey = 'chatbot_rate_session:'.session()->getId();
        $ipKey = 'chatbot_rate_ip:'.request()->ip();

        $sessionCount = (int) Cache::get($sessionKey, 0);
        $ipCount = (int) Cache::get($ipKey, 0);

        if ($sessionCount >= 30) {
            return true;
        }

        if ($ipCount >= 100) {
            return true;
        }

        return false;
    }

    private function trackRateLimit(): void
    {
        $sessionKey = 'chatbot_rate_session:'.session()->getId();
        $ipKey = 'chatbot_rate_ip:'.request()->ip();

        Cache::increment($sessionKey, 1, 60);
        Cache::increment($ipKey, 1, 1440);
    }
}

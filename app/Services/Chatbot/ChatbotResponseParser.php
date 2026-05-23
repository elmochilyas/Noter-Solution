<?php

namespace App\Services\Chatbot;

use App\Domain\Chatbot\ChatbotResponse;
use App\Domain\Chatbot\PlanRecommendation;
use Illuminate\Support\Facades\Log;
use Sentry;
use Sentry\Severity;

final class ChatbotResponseParser
{
    public function parse(string $rawJson, string $locale = 'fr'): ChatbotResponse
    {
        $data = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Chatbot JSON parse failed', [
                'error' => json_last_error_msg(),
                'raw' => mb_substr($rawJson, 0, 500),
            ]);

            if (class_exists('\Sentry')) {
                Sentry\captureMessage('Chatbot JSON parse failure', Severity::warning());
            }

            return ChatbotResponse::fallback($locale);
        }

        $answer = is_string($data['answer'] ?? null) ? trim($data['answer']) : '';

        if ($answer === '') {
            return ChatbotResponse::fallback($locale);
        }

        $suggestions = $this->parseSuggestions($data['suggestions'] ?? []);
        $recommendedPlan = $this->parseRecommendedPlan($data['recommended_plan'] ?? null);
        $escalate = (bool) ($data['escalate'] ?? false);
        $outOfScope = (bool) ($data['out_of_scope'] ?? false);

        return new ChatbotResponse(
            answer: $answer,
            suggestions: $suggestions,
            recommendedPlan: $recommendedPlan,
            escalate: $escalate,
            outOfScope: $outOfScope,
        );
    }

    private function parseSuggestions(array $suggestions): array
    {
        $result = [];

        foreach ($suggestions as $suggestion) {
            if (is_string($suggestion) && trim($suggestion) !== '') {
                $result[] = trim($suggestion);
            }

            if (count($result) >= 4) {
                break;
            }
        }

        return $result;
    }

    private function parseRecommendedPlan(?array $data): ?PlanRecommendation
    {
        if ($data === null || empty($data)) {
            return null;
        }

        try {
            return PlanRecommendation::fromArray($data);
        } catch (\InvalidArgumentException $e) {
            Log::warning('Invalid plan recommendation in chatbot response', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return null;
        }
    }
}

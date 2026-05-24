<?php

namespace App\Services\Chatbot;

use App\Domain\Chatbot\ChatbotResponse;
use App\Domain\Chatbot\PlanRecommendation;
use Illuminate\Support\Facades\Log;
use Sentry;
use Sentry\Severity;
use Sentry\State\Scope;

final class ChatbotResponseParser
{
    public function parse(string $rawJson, string $locale = 'fr', ?string $conversationId = null): ChatbotResponse
    {
        $cleaned = $this->extractJson($rawJson);

        if ($cleaned === null) {
            $this->logFailure($rawJson, 'No JSON object found in response', $conversationId);

            return ChatbotResponse::fallback($locale);
        }

        $data = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logFailure($cleaned, json_last_error_msg(), $conversationId);

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

    /**
     * Extract the first balanced JSON object from a string.
     * Handles markdown fences (```json ... ```), leading/trailing prose,
     * multiple JSON objects, and trailing commas.
     */
    private function extractJson(string $raw): ?string
    {
        // Strip markdown code fences
        $cleaned = preg_replace('/^\s*```(?:json)?\s*/im', '', $raw);
        $cleaned = preg_replace('/\s*```\s*$/im', '', $cleaned);

        // Remove leading non-JSON prose: look for the first '{'
        $firstBrace = mb_strpos($cleaned, '{');
        if ($firstBrace === false) {
            return null;
        }
        $cleaned = mb_substr($cleaned, $firstBrace);

        // Remove trailing non-JSON prose after the matching '}'
        $depth = 0;
        $inString = false;
        $escape = false;
        $length = mb_strlen($cleaned);

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($cleaned, $i, 1);

            if ($escape) {
                $escape = false;

                continue;
            }

            if ($char === '\\' && $inString) {
                $escape = true;

                continue;
            }

            if ($char === '"') {
                $inString = ! $inString;

                continue;
            }

            if ($inString) {
                continue;
            }

            if ($char === '{') {
                $depth++;

                continue;
            }

            if ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    return mb_substr($cleaned, 0, $i + 1);
                }
            }
        }

        return null;
    }

    private function logFailure(string $raw, string $error, ?string $conversationId): void
    {
        Log::warning('Chatbot JSON parse failed', [
            'error' => $error,
            'raw' => mb_substr($raw, 0, 500),
            'conversation_id' => $conversationId,
        ]);

        if (class_exists('\Sentry')) {
            if (method_exists('\Sentry', 'configureScope')) {
                \Sentry\configureScope(function (Scope $scope) use ($conversationId): void {
                    if ($conversationId) {
                        $scope->setTag('conversation_id', $conversationId);
                    }
                });
            }

            Sentry\captureMessage(
                'Chatbot JSON parse failure: '.$error,
                Severity::warning(),
            );
        }
    }

    private function parseSuggestions(array $suggestions): array
    {
        $result = [];

        foreach ($suggestions as $suggestion) {
            if (! is_string($suggestion) || trim($suggestion) === '') {
                continue;
            }

            $trimmed = trim($suggestion);

            $result[] = $trimmed;

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

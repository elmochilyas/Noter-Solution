<?php

namespace App\Domain\Services\Chatbot;

use Illuminate\Support\Facades\Log;

final class ChipFilter
{
    private const MIN_WORDS = 3;

    private const MAX_WORDS = 10;

    private const MAX_CHIPS = 4;

    /**
     * French second-person patterns (bot asking user — reverse direction).
     */
    private const FR_REVERSE_PATTERNS = [
        '/^Avez-vous\b/i',
        '/^Quel\s+(est\s+)?(votre|ton)\b/i',
        '/^Quelle\s+(est\s+)?(votre|ton)\b/i',
        '/^Quels\s+(sont\s+)?(vos|tes)\b/i',
        '/^Quelles\s+(sont\s+)?(vos|tes)\b/i',
        '/^Quand\s+(souhaitez|voulez|pouvez)-vous\b/i',
        '/^Que\s+(voulez|souhaitez|cherchez)-vous\b/i',
        '/^Comment\s+(puis-je|pourrais-je)\s+vous\b/i',
        '/\b(vous|tu)\s+(souhaitez|voulez|cherchez|avez besoin|êtes)/i',
        '/^Parlez-moi\b/i',
        '/^Dites-moi\b/i',
    ];

    /**
     * Arabic second-person patterns.
     */
    private const AR_REVERSE_PATTERNS = [
        '/^(هل\s+)?(لديك|عندك|تريد|تبحث|تحتاج)\b/u',
        '/^(ما\s+)?(هو|هي)\s+(الموضوع|المجال|السبب)\b/u',
        '/\b(تريد|تبحث|تحتاج|ترغب)\s+(أن|في)\b/u',
        '/^(كم\s+)?(تريد|تود)\s+أن\b/u',
    ];

    public function filter(
        array $suggestions,
        array $priorUserMessages,
        array $priorSuggestions,
        array $recentAssistantAnswers,
        string $locale,
    ): array {
        $filtered = [];
        $rejectedCount = 0;

        foreach ($suggestions as $suggestion) {
            if (! is_string($suggestion) || trim($suggestion) === '') {
                $rejectedCount++;

                continue;
            }

            $trimmed = trim($suggestion);

            if ($this->isReverseDirection($trimmed, $locale)) {
                Log::info('ChipFilter: dropped (reverse direction)', ['chip' => $trimmed]);
                $rejectedCount++;

                continue;
            }

            if ($this->isTooShortOrLong($trimmed)) {
                Log::info('ChipFilter: dropped (length)', ['chip' => $trimmed]);
                $rejectedCount++;

                continue;
            }

            if ($this->wasAskedByUser($trimmed, $priorUserMessages)) {
                Log::info('ChipFilter: dropped (asked by user)', ['chip' => $trimmed]);
                $rejectedCount++;

                continue;
            }

            if ($this->wasSuggestedBefore($trimmed, $priorSuggestions)) {
                Log::info('ChipFilter: dropped (prior suggestion)', ['chip' => $trimmed]);
                $rejectedCount++;

                continue;
            }

            if ($this->isAnswerableFromRecentAnswer($trimmed, $recentAssistantAnswers)) {
                Log::info('ChipFilter: dropped (recently answerable)', ['chip' => $trimmed]);
                $rejectedCount++;

                continue;
            }

            $filtered[] = $trimmed;

            if (count($filtered) >= self::MAX_CHIPS) {
                break;
            }
        }

        $total = count($suggestions);
        if ($total > 0) {
            $rejectionRate = $rejectedCount / $total;
            if ($rejectionRate > 0.5) {
                Log::warning('ChipFilter: high rejection rate', [
                    'rejected' => $rejectedCount,
                    'total' => $total,
                    'rate' => $rejectionRate,
                ]);
            }
        }

        return $filtered;
    }

    private function isReverseDirection(string $suggestion, string $locale): bool
    {
        $patterns = $locale === 'ar' ? self::AR_REVERSE_PATTERNS : self::FR_REVERSE_PATTERNS;

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $suggestion)) {
                return true;
            }
        }

        return false;
    }

    private function isTooShortOrLong(string $suggestion): bool
    {
        $wordCount = count(preg_split('/\s+/u', $suggestion));

        return $wordCount < self::MIN_WORDS || $wordCount > self::MAX_WORDS;
    }

    private function wasAskedByUser(string $suggestion, array $priorUserMessages): bool
    {
        $normalized = $this->normalize($suggestion);

        foreach ($priorUserMessages as $userMsg) {
            if ($this->normalize($userMsg) === $normalized) {
                return true;
            }
        }

        return false;
    }

    private function wasSuggestedBefore(string $suggestion, array $priorSuggestions): bool
    {
        $normalized = $this->normalize($suggestion);

        foreach ($priorSuggestions as $prior) {
            if ($this->normalize($prior) === $normalized) {
                return true;
            }
        }

        return false;
    }

    private function isAnswerableFromRecentAnswer(string $suggestion, array $recentAssistantAnswers): bool
    {
        $normalized = $this->normalize($suggestion);

        foreach ($recentAssistantAnswers as $answer) {
            $answerNormalized = $this->normalize($answer);
            // Check if the suggestion's core question words appear in the recent answer
            $questionWords = $this->extractSignificantWords($normalized);
            $matchCount = 0;

            foreach ($questionWords as $word) {
                if (mb_strlen($word) <= 2) {
                    continue;
                }
                if (str_contains($answerNormalized, $word)) {
                    $matchCount++;
                }
            }

            if (count($questionWords) > 0 && $matchCount >= count($questionWords) * 0.5) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $text): string
    {
        return trim(mb_strtolower(preg_replace('/\s+/', ' ', $text)));
    }

    private function extractSignificantWords(string $text): array
    {
        $words = preg_split('/[\s\p{P}]+/u', $text);

        return array_values(array_filter($words, fn (string $w) => mb_strlen($w) > 2));
    }
}

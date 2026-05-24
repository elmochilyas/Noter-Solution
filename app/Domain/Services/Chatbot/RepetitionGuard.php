<?php

namespace App\Domain\Services\Chatbot;

use Illuminate\Support\Facades\Log;

enum RepetitionVerdict
{
    case OK;
    case REGENERATE;
    case FALLBACK;
}

final class RepetitionGuard
{
    private const SIMILARITY_THRESHOLD = 0.7;

    private const MAX_REGENERATIONS = 2;

    public function check(string $newAnswer, array $previousAssistantAnswers): RepetitionVerdict
    {
        if (empty($previousAssistantAnswers)) {
            return RepetitionVerdict::OK;
        }

        $maxSimilarity = 0;

        foreach ($previousAssistantAnswers as $prev) {
            $similarity = $this->computeSimilarity($newAnswer, $prev);
            $maxSimilarity = max($maxSimilarity, $similarity);
        }

        if ($maxSimilarity > self::SIMILARITY_THRESHOLD) {
            Log::info('RepetitionGuard: similarity above threshold', [
                'similarity' => round($maxSimilarity, 4),
                'threshold' => self::SIMILARITY_THRESHOLD,
            ]);

            return RepetitionVerdict::REGENERATE;
        }

        return RepetitionVerdict::OK;
    }

    /**
     * Compute character-level 3-gram (shingle) cosine similarity.
     */
    private function computeSimilarity(string $a, string $b): float
    {
        $shinglesA = $this->shingles(mb_strtolower(trim($a)), 3);
        $shinglesB = $this->shingles(mb_strtolower(trim($b)), 3);

        if (empty($shinglesA) || empty($shinglesB)) {
            return 0;
        }

        $intersection = array_intersect($shinglesA, $shinglesB);
        $union = array_unique(array_merge($shinglesA, $shinglesB));

        if (count($union) === 0) {
            return 0;
        }

        return count($intersection) / count($union);
    }

    private function shingles(string $text, int $k): array
    {
        $shingles = [];
        $len = mb_strlen($text);

        for ($i = 0; $i <= $len - $k; $i++) {
            $shingles[] = mb_substr($text, $i, $k);
        }

        return array_unique($shingles);
    }
}

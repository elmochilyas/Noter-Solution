<?php

namespace App\Domain\Services\Chatbot\Contracts;

use Generator;

interface LlmClient
{
    public function generate(
        string $systemPrompt,
        array $messages,
        int $maxTokens = 600,
        float $temperature = 0.3,
    ): string;

    public function generateStreamed(
        string $systemPrompt,
        array $messages,
        int $maxTokens = 600,
        float $temperature = 0.3,
    ): Generator;

    public function countTokens(string $text): int;
}

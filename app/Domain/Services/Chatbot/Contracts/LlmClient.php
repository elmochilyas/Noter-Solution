<?php

namespace App\Domain\Services\Chatbot\Contracts;

use App\Domain\Chatbot\LlmRequest;
use App\Domain\Chatbot\LlmResponse;
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

    public function complete(LlmRequest $request): LlmResponse;

    public function name(): string;
}

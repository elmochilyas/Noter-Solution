<?php

namespace App\Domain\Chatbot;

final readonly class LlmResponse
{
    public function __construct(
        public string $content,
        public int $tokensIn,
        public int $tokensOut,
        public int $latencyMs,
        public string $model,
    ) {}
}

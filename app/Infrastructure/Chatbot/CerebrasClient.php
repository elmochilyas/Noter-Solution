<?php

namespace App\Infrastructure\Chatbot;

use App\Domain\Services\Chatbot\Contracts\LlmClient;
use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class CerebrasClient implements LlmClient
{
    private const MAX_TOKENS_CEILING = 4_000;

    private const SYSTEM_OVERHEAD = 200;

    private const MAX_RETRIES = 1;

    private readonly string $apiKey;

    private readonly string $model;

    private readonly string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.cerebras.api_key');
        $this->model = config('services.cerebras.model', 'gpt-oss-120b');
        $this->baseUrl = config('services.cerebras.base_url', 'https://api.cerebras.ai/v1');
    }

    public function generate(
        string $systemPrompt,
        array $messages,
        int $maxTokens = 600,
        float $temperature = 0.3,
    ): string {
        $maxTokens = $this->enforceTokenBudget($systemPrompt, $messages, $maxTokens);

        $payload = $this->buildPayload($systemPrompt, $messages, $maxTokens, $temperature, false);

        $attempts = 0;

        while ($attempts <= self::MAX_RETRIES) {
            try {
                $response = Http::timeout(15)
                    ->withToken($this->apiKey)
                    ->post("{$this->baseUrl}/chat/completions", $payload);

                if ($response->successful()) {
                    $data = $response->json();

                    return $data['choices'][0]['message']['content'] ?? '';
                }

                if ($response->status() < 500 || $attempts >= self::MAX_RETRIES) {
                    Log::error('Cerebras API error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    throw new \RuntimeException('Cerebras API request failed: '.$response->body());
                }

                $attempts++;
                Log::warning('Retrying Cerebras API', ['attempt' => $attempts, 'status' => $response->status()]);
            } catch (ConnectionException $e) {
                if ($attempts >= self::MAX_RETRIES) {
                    throw new \RuntimeException('Cerebras API connection failed after retries: '.$e->getMessage());
                }

                $attempts++;
                Log::warning('Retrying Cerebras API connection', ['attempt' => $attempts]);
            }
        }

        throw new \RuntimeException('Cerebras API request failed after max retries');
    }

    public function generateStreamed(
        string $systemPrompt,
        array $messages,
        int $maxTokens = 600,
        float $temperature = 0.3,
    ): Generator {
        $maxTokens = $this->enforceTokenBudget($systemPrompt, $messages, $maxTokens);

        $payload = $this->buildPayload($systemPrompt, $messages, $maxTokens, $temperature, true);

        $response = Http::timeout(20)
            ->withToken($this->apiKey)
            ->withHeaders(['Accept' => 'text/event-stream'])
            ->post("{$this->baseUrl}/chat/completions", $payload);

        if ($response->failed()) {
            Log::error('Cerebras streaming error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Cerebras streaming request failed');
        }

        $body = $response->body();
        $lines = explode("\n", $body);

        foreach ($lines as $line) {
            if (connection_aborted()) {
                Log::info('Client disconnected, stopping stream processing');
                break;
            }

            $line = trim($line);

            if ($line === '' || $line === 'data: [DONE]') {
                continue;
            }

            if (! str_starts_with($line, 'data: ')) {
                continue;
            }

            $json = substr($line, 6);
            $chunk = json_decode($json, true);

            if (! $chunk || ! isset($chunk['choices'][0]['delta']['content'])) {
                continue;
            }

            yield $chunk['choices'][0]['delta']['content'];
        }
    }

    public function countTokens(string $text): int
    {
        $length = mb_strlen($text);

        return (int) ceil($length / 4);
    }

    private function enforceTokenBudget(string $systemPrompt, array $messages, int $maxTokens): int
    {
        $systemTokens = $this->countTokens($systemPrompt);
        $historyTokens = 0;

        foreach ($messages as $msg) {
            $historyTokens += $this->countTokens($msg['content'] ?? '');
        }

        $totalNeeded = $systemTokens + $historyTokens + $maxTokens + self::SYSTEM_OVERHEAD;

        if ($totalNeeded > self::MAX_TOKENS_CEILING) {
            $available = self::MAX_TOKENS_CEILING - $systemTokens - $historyTokens - self::SYSTEM_OVERHEAD;
            $maxTokens = max(100, min($maxTokens, $available));
        }

        return $maxTokens;
    }

    private function buildPayload(
        string $systemPrompt,
        array $messages,
        int $maxTokens,
        float $temperature,
        bool $stream,
    ): array {
        $formatted = [];

        if ($systemPrompt !== '') {
            $formatted[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        foreach ($messages as $msg) {
            $formatted[] = [
                'role' => $msg['role'] ?? 'user',
                'content' => $msg['content'] ?? '',
            ];
        }

        return [
            'model' => $this->model,
            'messages' => $formatted,
            'max_completion_tokens' => $maxTokens,
            'temperature' => $temperature,
            'stream' => $stream,
        ];
    }
}

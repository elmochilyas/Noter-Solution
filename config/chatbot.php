<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chatbot Configuration
    |--------------------------------------------------------------------------
    */

    // Session idle timeout (minutes without interaction)
    'idle_timeout_minutes' => env('CHATBOT_IDLE_TIMEOUT_MINUTES', 15),

    // Days before archiving inactive conversations
    'archive_days' => env('CHATBOT_ARCHIVE_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | LLM Provider — Cerebras
    |--------------------------------------------------------------------------
    */

    'provider' => [
        'api_key' => env('CEREBRAS_API_KEY'),
        'model' => env('CEREBRAS_MODEL', 'gpt-oss-120b'),
        'base_url' => 'https://api.cerebras.ai/v1',
        'max_tokens' => (int) env('CEREBRAS_MAX_TOKENS', 600),
        'temperature' => (float) env('CEREBRAS_TEMPERATURE', 0.3),
        'timeout' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing (Cerebras gpt-oss-120b)
    |--------------------------------------------------------------------------
    | Per-million-token costs. Used for cost tracking and budget cap.
    | Free tier: 1M tokens/day, no credit card required.
    */

    'pricing' => [
        'input_per_million' => 0.35,   // USD per 1M input tokens
        'output_per_million' => 0.75,  // USD per 1M output tokens
        'monthly_budget' => (float) env('CEREBRAS_MONTHLY_BUDGET', 5.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Greeting suggestion chips (seed set — first message only)
    |--------------------------------------------------------------------------
    | These are the only static chips in the system.
    | All subsequent turns use dynamically generated chips from the LLM.
    */

    'greeting_chips' => [
        'fr' => [
            'Quels documents pour un mariage ?',
            'Prendre un rendez-vous',
            'Combien ça coûte ?',
        ],
        'ar' => [
            'ما هي وثائق الزواج؟',
            'حجز موعد',
            'كم التكلفة؟',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Few-shot examples
    |--------------------------------------------------------------------------
    | Now embedded directly in system prompts in resources/lang/{locale}/chatbot.php.
    | Retained only for reference; not loaded at runtime.
    */

    'few_shot_examples' => [],
];

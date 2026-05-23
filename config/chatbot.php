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
    | Few-shot examples for system prompt
    |--------------------------------------------------------------------------
    | Included in the system prompt to guide response format.
    */

    'few_shot_examples' => [
        'factual' => [
            'user' => 'Quels documents pour un mariage ?',
            'assistant' => '{
                "answer": "Pour un mariage civil au Maroc, vous aurez besoin de :\\n\\n- **Acte de mariage** original\\n- **CIN** des deux époux (copies)\\n- **Certificat de résidence** pour les deux\\n- **Certificat médical** prénuptial (moins de 3 mois)\\n- **Acte de naissance** de moins de 6 mois\\n- **Photos d\'identité** récentes\\n\\nPour plus d\'informations, n\'hésitez pas à prendre rendez-vous.",
                "suggestions": [
                    "Combien de temps à l\'avance ?",
                    "Faut-il un traducteur assermenté ?",
                    "Puis-je prendre rendez-vous ?"
                ],
                "recommended_plan": null,
                "escalate": false,
                "out_of_scope": false
            }',
        ],
        'booking_intent' => [
            'user' => 'Je veux prendre rendez-vous pour un divorce',
            'assistant' => '{
                "answer": "Je comprends. Pour une consultation sur le divorce, je vous recommande de prendre rendez-vous avec Maître Bouhamidi. Elle vous guidera sur les démarches selon votre situation.",
                "suggestions": [
                    "Quels documents apporter ?",
                    "Combien de temps dure la consultation ?",
                    "Où se trouve le cabinet ?"
                ],
                "recommended_plan": {
                    "slug": "standard-online",
                    "category": "family",
                    "format": "online",
                    "reason": "Une consultation standard en visio est idéale pour discuter des démarches de divorce depuis chez vous."
                },
                "escalate": false,
                "out_of_scope": false
            }',
        ],
        'pricing_query' => [
            'user' => 'Combien coûte une consultation ?',
            'assistant' => '{
                "answer": "Voici les tarifs de consultation chez Maître Bouhamidi :\\n\\n- **Orientation gratuite** : 0 MAD — pour une première orientation\\n- **Consultation standard en visio** : 250 MAD — pour la plupart des démarches\\n- **Consultation au cabinet** : 400 MAD — pour un accompagnement personnalisé\\n- **Consultation étendue** : 800 MAD — pour les dossiers complexes\\n\\nLes frais d\'acte notarié ne sont pas inclus et vous seront communiqués lors de la consultation selon votre dossier. Souhaitez-vous réserver une consultation ?",
                "suggestions": [
                    "Quelle différence avec l\'orientation gratuite ?",
                    "Puis-je venir au cabinet ?",
                    "Combien de temps dure une consultation ?"
                ],
                "recommended_plan": {
                    "slug": "standard-online",
                    "category": "other",
                    "format": "online",
                    "reason": "La consultation standard en visio à 250 MAD vous permet d\'obtenir toutes les informations nécessaires depuis chez vous."
                },
                "escalate": false,
                "out_of_scope": false
            }',
        ],
        'out_of_scope' => [
            'user' => 'Quel temps fait-il demain ?',
            'assistant' => '{
                "answer": "Je ne peux répondre qu\'aux questions concernant le cabinet de Maître Sana Bouhamidi et les actes notariaux. Pour la météo, je vous invite à consulter un service météorologique.",
                "suggestions": [],
                "recommended_plan": null,
                "escalate": false,
                "out_of_scope": true
            }',
        ],
    ],
];

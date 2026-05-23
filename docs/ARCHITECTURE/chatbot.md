# Chatbot Architecture

## What it does

A bilingual (AR/FR) assistant that:

1. Answers frequently-asked questions from a curated FAQ corpus.
2. Triages users wanting a consultation toward the right plan.
3. Escalates to a human (WhatsApp / phone) when out of scope or when the user asks.
4. Never gives legal advice. Always reminds it's informational.

## High-level flow

```
User message → Pre-processing → Intent classification → Branch
                                                        │
                          ┌─────────────────────────────┼─────────────────────────────┐
                          ▼                             ▼                             ▼
                  FAQ retrieval (RAG)           Triage form                  Escalation
                          │                             │                             │
                          ▼                             ▼                             ▼
                  Cerebras API call            Recommendation card           Hand-off message
                  with retrieved context       + booking button              + WhatsApp link
                  (non-streaming JSON)
                          │
                          ▼
                  Parse structured JSON response
                  (answer, suggestions, recommended_plan)
                          │
                          ▼
                  Render UI: answer text
                  + optional plan card
                  + dynamic suggestion chips
                          │
                          ▼
                  Persist ChatbotMessage with tokens, latency, FAQ IDs
```

## Components

| Component | Class | Responsibility |
|---|---|---|---|
| Conversation manager | `ChatbotService` | Orchestrates everything |
| Intent classifier | `IntentClassifier` | First-pass categorization |
| FAQ retriever | `FaqRetriever` | Keyword search over FAQ translations |
| LLM client (interface) | `LlmClient` (contract), `CerebrasClient` (impl) | HTTP calls to Cerebras API (OpenAI-compatible) |
| Triage flow | `TriageFlow` | Multi-step Q&A to recommend a plan |
| Escalation handler | `EscalationHandler` | Generates WhatsApp deep-link + notifies admin |
| Output filter | `OutputFilter` | Post-generation regex check for forbidden patterns |
| Conversation logger | `ChatbotMessage` model (inline) | Persists messages synchronously |

## Conversation persistence

- A `ChatbotConversation` row created on first message.
- Identified by browser session ID (anonymous) or client ID (logged in).
- All messages stored in `chatbot_messages`.
- Persistence is async — the user response is not blocked on DB writes.

## Intent classification

First step on every user message: lightweight intent classification. Two-tier approach:

### Tier 1 — keyword + heuristic (fast, deterministic)

```php
class IntentClassifier
{
    public function classify(string $message, Locale $locale): ChatbotIntent
    {
        $normalized = mb_strtolower(trim($message));

        if ($this->isGreeting($normalized, $locale)) return ChatbotIntent::GREETING;
        if ($this->mentionsPricing($normalized, $locale)) return ChatbotIntent::PRICING_QUERY;
        if ($this->mentionsBooking($normalized, $locale)) return ChatbotIntent::BOOKING_INTENT;
        if ($this->mentionsEscalation($normalized, $locale)) return ChatbotIntent::ESCALATION;
        if ($this->isOutOfScope($normalized, $locale)) return ChatbotIntent::OUT_OF_SCOPE;

        // Fall through: treat as FAQ query
        return ChatbotIntent::FAQ_QUERY;
    }
}
```

### Tier 2 — LLM classification (if Tier 1 returns FAQ_QUERY)

If a message contains a question word but no clear category, we let the LLM classify it as part of the response generation prompt (single round-trip).

## RAG for FAQ answers

### Embedding pipeline

- Each FAQ entry has `question_translations` and `answer_translations` in `fr` and `ar`.
- We embed `question + " " + answer` per language using **Voyage AI** (`voyage-3-lite`, 1024 dims).
- Embeddings stored in `faqs.embedding_fr` and `faqs.embedding_ar` (`vector(1024)`).
- Re-embedded automatically on save via a model observer (or queued job for batch updates).
- HNSW index on each embedding column.

### Retrieval

```php
class FaqRetriever
{
    public function retrieve(string $query, Locale $locale, int $k = 5, float $minScore = 0.6): Collection
    {
        $vector = $this->embed($query, $locale);

        $column = $locale === Locale::AR ? 'embedding_ar' : 'embedding_fr';

        return Faq::query()
            ->where('is_published', true)
            ->selectRaw("*, 1 - ({$column} <=> ?::vector) as score", [$this->toVectorString($vector)])
            ->orderByDesc('score')
            ->limit($k)
            ->get()
            ->filter(fn ($faq) => $faq->score >= $minScore);
    }
}
```

### Why Voyage over OpenAI for embeddings

- Better multilingual quality, especially for Arabic.
- Cheaper.
- Smaller dimensions (1024 vs OpenAI 1536) — smaller index, faster search.

### Why pgvector over a dedicated vector DB

- ~200 FAQ entries — embarrassingly small for any vector DB.
- Same database = same backups, same access control, same migrations.
- HNSW index in Postgres is fast enough at this scale (<10ms).

## LLM generation

### Provider

- **Cerebras** via the OpenAI-compatible HTTP API (`https://api.cerebras.ai/v1/chat/completions`).
- Model: `gpt-oss-120b` (OpenAI GPT OSS, 120B params, ~3000 tok/s, 131K context window).
- Non-streaming JSON responses (structured output). The application sends a single request and receives the complete JSON response.
- Free tier: 1,000,000 tokens/day (no credit card required).
- Cost: $0.35/1M input tokens; $0.75/1M output tokens (itemized). For cost tracking see "Cost monitoring" below.

### System prompt

The system prompt is the most important piece. Maintained in `resources/lang/{locale}/chatbot.php` so it's translatable and editable from Filament.

Key elements:
- Identity: "You are the assistant for Maître Sana Bouhamidi's notary practice in Agadir."
- Scope: "Only answer questions about notarial services in Morocco."
- Tone: formal, calm, professional.
- Languages: respond in the same language as the user.
- Format: return a JSON object matching the structured response schema (see above). The `answer` field may contain light Markdown (**bold**, line breaks, bullet lists).
- Guardrails:
  - Never give legal advice — only general information.
  - Never quote fees in the `answer` text. Plan prices are inserted by the application from the database.
  - Never include a `recommended_plan` unless the user has expressed concrete booking intent.
  - Recommend booking a consultation for specific cases.
  - Recommend WhatsApp / phone for urgent matters.
- Populate `suggestions` dynamically based on the conversation flow — not from a fixed list.
- Forbid superlative / comparative claims in any field.
- Disclaimer line appended to every substantive answer.

### Message structure sent to Cerebras

```json
{
  "model": "gpt-oss-120b",
  "max_completion_tokens": 600,
  "temperature": 0.3,
  "response_format": { "type": "json_object" },
  "messages": [
    { "role": "system", "content": "<system prompt for resolved locale>" },
    { "role": "user", "content": "Quels documents pour un divorce ?" },
    { "role": "assistant", "content": "<structured response JSON from previous turn>" },
    {
      "role": "user",
      "content": "Combien de temps ça prend ?\n\n<context from retrieved FAQ entries — clearly labeled>"
    }
  ]
}
```

The retrieved FAQ context is appended to the user message as a context block, not as a separate role.

### Token budget

- System prompt: ~600 tokens.
- Retrieved context: up to 5 FAQ entries, ~1000 tokens total.
- Conversation history: last 6 messages, ~800 tokens.
- Response: max 600 tokens.
- Per call ceiling: ~3000 tokens.

### Structured response schema

The LLM is instructed to return a JSON object matching this contract:

```json
{
  "answer": "string — main text, max ~120 words, user's locale. Light Markdown: **bold**, line breaks, bullet lists (- ...). No headings, no hyperlinks.",
  "suggestions": [
    "string — short follow-up question chip, max 6 words, user's locale. 2–4 items, contextually relevant, no duplicates."
  ],
  "recommended_plan": {
    "slug": "free-orientation | standard-online | in-office | extended",
    "category": "family | real_estate | financial | contracts",
    "format": "online | in_office",
    "reason": "one sentence explaining why this plan fits, user's locale"
  } | null,
  "escalate": false | true,
  "out_of_scope": false | true
}
```

Rules:
- `answer` is the only required field. Others are optional.
- `recommended_plan` must be `null` unless the user has expressed concrete booking intent or is asking about pricing/process for a specific matter.
- `escalate: true` only when the user explicitly asks for a human or the bot's confidence is very low.
- `out_of_scope: true` when the question is outside notarial services in Morocco.
- Never include a price/amount in `answer` or `reason` — prices come from the plan card.
- Suggestions must be questions the user might ask next, given the current answer.

### Response handling
- The UI shows a typing indicator (three brass dots with staggered `animate-pulse` delays) during the round-trip (typically ~600ms–2s on Cerebras).
- The response is parsed from JSON into a `ChatbotResponse` value object.
- If JSON parsing fails, a fallback `ChatbotResponse` with a generic apology and escalation chips is returned. A Sentry warning is fired.
- On browser disconnect or timeout, the upstream request is cancelled and the user sees an error message.

### Rate limiting

- 30 messages per session per hour.
- 100 messages per IP per day.
- After hitting the limit: friendly message asking to use phone / WhatsApp.

### Cost monitoring

- Per-message token usage logged (`tokens_in`, `tokens_out`, `latency_ms`).
- Weekly cost report visible in admin dashboard.
- Hard monthly budget cap configurable in `.env` — if exceeded, chatbot falls back to "Service temporairement indisponible, contactez-nous par WhatsApp."

## Triage flow

When intent classifier returns `BOOKING_INTENT`, the bot switches to a guided form rather than open chat:

```
Step 1: "What is your matter about?"
        Options: Famille / Immobilier / Financier / Contrats / Autre
                 (clickable chips)

Step 2: "Do you already have all your documents?"
        Options: Oui / Non / Je ne sais pas

Step 3: "Do you prefer in-person or video?"
        Options: En personne / En vidéo / Indifférent

Step 4: "How urgent is it?"
        Options: Cette semaine / Ce mois / Flexible

Recommendation card:
  - Plan name + price + duration
  - "Réserver ce créneau" button → links to /book?plan=<slug>&category=<cat>&format=<fmt>
```

Implemented as a structured state machine in `TriageFlow`. State persisted in the conversation row's `metadata` JSON column.

The user can break out of the triage at any time by typing free text — that returns to general Q&A.

## Escalation

Triggered when:
- User says "human", "real person", "WhatsApp", "agent", "speak to someone", or AR equivalents.
- Intent classifier returns `ESCALATION`.
- The bot's confidence is low (retrieval scores all below 0.5).
- The same question is asked twice in a row (loop detection).

Escalation message includes:
- A WhatsApp deep-link: `https://wa.me/212666120661?text=<pre-filled-message>`
- The practice phone number.
- A "Send my conversation summary?" button (optional, with consent).

If the user opts to send the summary:
- A short summary (LLM-generated) is added to the contact form.
- Sent to the admin as a `ContactMessageReceived` event.

## Out-of-scope responses

For requests outside the notarial domain (general legal advice, unrelated topics, harmful requests):

```
"Je ne peux répondre qu'aux questions concernant le cabinet de Maître
Sana Bouhamidi et les actes notariaux. Pour [topic], je vous invite à
[redirect]."
```

The bot never refuses rudely. It redirects politely.

## Safety guardrails

Implemented at multiple layers:

1. **System prompt** — strongly worded constraints.
2. **Input filter** — rate limit + max message length (500 chars).
3. **Output filter** — post-generation check: if response contains forbidden patterns (specific legal advice phrases, fee amounts not in context), regenerate with stricter prompt; if still bad, escalate.
4. **Conversation review** — admin can flag bad responses; flagged conversations feed into prompt improvement.

## Failure modes

| Failure | Behavior |
|---|---|---|
| Cerebras API timeout | Show fallback message, suggest WhatsApp |
| Cerebras API rate limited | Same fallback |
| Cerebras quota exhausted | Same fallback |
| Output filter triggered | Filtered response shown + escalation suggestion |
| Browser disconnects | Server cancels upstream request |
| JSON parse failure | Fallback response + Sentry warning |

All failures logged to Sentry with the conversation ID for review.

## Admin review tools

Filament page `/admin/chatbot`:

- List of recent conversations with intent + outcome (resolved / escalated / abandoned / booked).
- View full transcript.
- Actions per conversation:
  - "Promote question to FAQ" — opens a new FAQ entry pre-filled with the user question.
  - "Mark as resolved"
  - "Flag for review"
- Filter by intent, outcome, language, date.

## FAQ management

Owner and assistant can:

- Add / edit FAQ entries (question + answer in both languages).
- Re-categorize.
- Publish / unpublish.
- Re-trigger embedding via a button.

On save:
- A queued job `ReembedFaq` is dispatched.
- Updated FAQ visible on `/faq` and in chatbot retrieval immediately.

## Privacy

- Conversations are tied to a browser session (anonymous) or to a Client (logged-in).
- The chatbot disclaimer banner notes that conversations are logged for service improvement.
- Conversation retention: 18 months (see `database-schema.md` data retention).
- Right to erasure: a Client requesting deletion has their conversations anonymized (session_id and client_id cleared).
- No PII intentionally sent to the LLM — the system prompt warns the user not to share personal info, and we don't pass user identity to the LLM.

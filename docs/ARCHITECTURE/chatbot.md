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
|---|---|---|---|---|
| Conversation manager | `ChatbotService` | Orchestrates everything |
| Intent classifier | `IntentClassifier` | First-pass categorization |
| FAQ retriever | `FaqRetriever` | Keyword search over FAQ translations |
| LLM client (interface) | `LlmClient` (contract), `CerebrasClient` (impl) | HTTP calls to Cerebras API (OpenAI-compatible) |
| Triage flow | `TriageFlow` | Multi-step Q&A to recommend a plan |
| Escalation handler | `EscalationHandler` | Generates WhatsApp deep-link + notifies admin |
| Output filter | `OutputFilter` | Post-generation regex check for forbidden patterns |
| Chip filter | `ChipFilter` | Server-side anti-redundancy rules for suggestion chips |
| Repetition guard | `RepetitionGuard` | Detects and regenerates responses similar to recent turns |
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

The prompt is structured in 8 ordered sections:
1. **Identity** — one sentence: who the assistant is and whose practice it represents.
2. **Scope** (positive) — what the assistant DOES (inform, explain, present pricing, recommend, answer FAQ).
3. **Out of scope** (negative) — what it refrains from (act fees, legal advice, off-domain topics, drafting, outcome promises).
4. **Tone** — calm, factual, respectful. No exclamation marks, no emoji except 📞✉️ for contacts. No marketing language. No superlatives.
5. **Conversation principles** — the strategic heart: read history, match question shape (different answer forms for different intents), be brief, don't repeat across turns.
6. **Response schema** — explicit field-by-field JSON rules with length limits, formatting, and when to populate `recommended_plan`.
7. **Few-shot examples** — 8 multi-turn conversation examples embedded directly in the prompt.
8. **Guardrails** — JSON-only output, currency format, language rules.

### Message structure sent to Cerebras

```json
{
  "model": "gpt-oss-120b",
  "max_completion_tokens": 800,
  "temperature": 0.3,
  "response_format": { "type": "json_object" },
  "messages": [
    { "role": "system", "content": "<system prompt for resolved locale>" },
    { "role": "user", "content": "<prior user turn 1>" },
    { "role": "assistant", "content": "<prior assistant turn 1>" },
    { "role": "user", "content": "<prior user turn 2>" },
    { "role": "assistant", "content": "<prior assistant turn 2>" },
    { "role": "user", "content": "<prior user turn 3>" },
    { "role": "assistant", "content": "<prior assistant turn 3>" },
    {
      "role": "user",
      "content": "<current user message>\n\n<context>Retrieved FAQ entries (question + answer)</context>"
    }
  ]
}
```

The retrieved FAQ context is appended to the user message as a `<context>` block, not as a separate role.

The conversation history array (**`$history`** in `getConversationHistory()`) excludes the current user message — it contains only turns prior to the current one. The current message is appended last by `buildLlmMessages()`, so it appears exactly once in the request array.

Assistant turn content in history is the plain text `.answer` field of the stored `ChatbotMessage`, not the full JSON response envelope or the LLM's raw JSON output. This prevents the model from seeing its own structured JSON in prior turns.

### Token budget

| Component | Target tokens | Notes |
|---|---|---|
| System prompt | 1000–1200 | Structured sections + 8 multi-turn few-shot examples |
| FAQ context | Up to 800 | Up to 5 FAQ entries, truncated to fit |
| Conversation history | ~1500 | Token-budgeted (not turn-counted). Accumulates from most recent backwards until 1500 tokens reached. |
| Response (`max_completion_tokens`) | 800 | ~200 for JSON structure + ~600 for answer |
| **Per-call ceiling** | **~4300** | Well within Cerebras 131K window |

### History budget algorithm

The conversation history is built by walking messages from most recent backwards, accumulating tokens until the 1500-token budget is exhausted.

1. All messages for the conversation are loaded from `chatbot_messages`, ordered chronologically by `created_at`.
2. Messages are iterated in reverse (most recent first), accumulating token count via `LlmmClient::countTokens()` (character count / 4 approximation).
3. Once the accumulated token count exceeds `HISTORY_TOKEN_BUDGET` (1500), older messages are dropped.
4. The most recent message (the current user turn, just recorded by `recordMessage()`) is removed from the history array — it will be appended last by `buildLlmMessages()` with the FAQ `<context>` block.
5. The remaining messages are reversed back to chronological order.

This ensures the current user message appears exactly once in the final request array, and that the token budget limits total history length (not turn count). Assistant content in history is the `.answer` text only, never the raw JSON response.

### Chip filter (ChipFilter)

Server-side post-generation filter that enforces suggestion chip relevance and anti-redundancy:

Rules applied in order:
1. **Reverse-direction check** — drops chips phrased from the bot's perspective (second person). Patterns for FR and AR.
2. **Length gate** — rejects < 3 words or > 10 words.
3. **Prior-user-message check** — rejects chips matching any prior user message in the conversation.
4. **Prior-suggestion check** — rejects chips matching any prior assistant suggestion.
5. **Recency check** — rejects chips whose answer is substantially present in the bot's last 3 turns (checked via keyword overlap).
6. **Diversity** — caps at 4 chips, logs high rejection rates (>50%) to Sentry.

### Repetition guard (RepetitionGuard)

Detects when the LLM produces an answer too similar to recent turns:

- Computes character-level 3-gram (shingle) similarity between the new answer and each of the previous 3 assistant answers.
- Threshold: > 0.7 similarity → `REGENERATE`.
- Regeneration uses the stricter prompt with an anti-repetition suffix.
- After 2 failed attempts → `FALLBACK` with a meta-response: "Pouvez-vous préciser ce que vous souhaitez savoir ?"

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
- `answer` (required): 2–4 sentences, max ~100 words. Light Markdown: **bold**, line breaks, bullet lists (-). No headings, no hyperlinks. No act fees. No superlatives. No promises.
- `suggestions` (optional, 2–4 items): Short questions (3–10 words) the USER would ask next. First person. Must NOT: duplicate prior user questions, duplicate prior suggestions, be answerable from the bot's last 3 turns, or be generic ("En savoir plus").
- `recommended_plan`: populate when user asks about pricing, plan selection, or shows booking intent. Default: `standard-online` unless user expresses in-office preference (`in-office`) or complex need (`extended`). `null` for general questions.
- `escalate: true` — only when the user explicitly asks to speak to a human.
- `out_of_scope: true` — only when the question is outside notarial services in Morocco.
- Consultation prices may be quoted in `answer` (0/250/400/800 MAD). Authentication act fees are NEVER quoted.
- Suggestions are additionally filtered server-side by ChipFilter (see above).

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

**Chip click routing:** When triage is active, chip clicks are handled directly by `ChatbotService::handleTriageChipClick()`, which bypasses the `respondTo()` pipeline entirely. The LLM is never called for chip clicks during active triage. The chip click goes directly to `TriageFlow::processStep()`, which advances the state machine and returns the next question (or the final recommendation).

**Free-form text during triage:** If the user types free-form text (not a chip click) while triage is active, `respondTo()` detects the active triage state, abandons it by resetting `triage_state` to `idle`, and falls through to the LLM generation path. The system prompt is augmented with a note that the triage was abandoned, instructing the model to answer the free-form question directly. Chip clicks sent after triage completion (state `completed`) are handled by the standard free-form LLM path.

**State persistence:** Triage state is persisted in the conversation's `metadata` JSON column on every state change. The Livewire component re-loads the conversation from the database on each request (protected property, not serialized), ensuring state survives page navigation.

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

1. **System prompt** — strongly worded constraints with multi-turn few-shot examples.
2. **Input filter** — rate limit + max message length (500 chars).
3. **Output filter** — post-generation check: if response contains forbidden patterns (specific legal advice phrases, unauthorized fee amounts), regenerate with stricter prompt; if still bad, escalate.
4. **Chip filter** — server-side anti-redundancy on suggestion chips (see above).
5. **Repetition guard** — server-side anti-repetition with regeneration path (see above).
6. **Conversation review** — admin can flag bad responses; flagged conversations feed into prompt improvement.

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

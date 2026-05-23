# Session Log: Cerebras Migration & Structured Responses

**Date:** 2026-05-23
**Duration:** Single session (full implementation)

## Summary

Migrated chatbot from streaming Anthropic/Claude to non-streaming Cerebras (`gpt-oss-120b`) with structured JSON responses. Added dynamic suggestion chips, plan cards, escalation panels, and output filter regeneration.

## Changes

### Documentation (commit `389793a`)
- `docs/ARCHITECTURE/chatbot.md`: New flow diagram, structured response schema, non-streaming, updated system prompt
- `docs/FEATURES/chatbot.md`: Dynamic chips, plan card anatomy, 19 acceptance criteria
- `docs/COMPLIANCE/loi-09-08.md`: Cerebras cross-border (US), P1 data residency flag
- `docs/PHASES/06-chatbot.md`: Non-streaming/structured output tasks
- `.env.example`: Cerebras as LLM provider
- Replaced `rotate-anthropic-key.md` → `rotate-cerebras-api-key.md`
- Cleaned 15+ doc files of Anthropic/Claude references

### Provider configuration (`0244c6e`)
- `config/chatbot.php`: Cerebras pricing ($0.35 input, $0.75 output per 1M), greeting chips, few-shot examples

### Domain & Infrastructure (`5b61a05`)
- **New VOs:** `LlmRequest` (system, messages, maxTokens, temperature, responseFormat), `LlmResponse` (content, tokensIn, tokensOut, latencyMs, model), `PlanRecommendation` (slug, category, reason, `toBookingUrl()`)
- **New structured response VO:** `ChatbotResponse` (answer, suggestions, recommendedPlan, escalate, outOfScope, fallback/unavailable factories)
- **New parser:** `ChatbotResponseParser` (JSON parse with Sentry warning on failure)
- **Updated `LlmClient` interface:** `complete()` and `name()` methods
- **Updated `CerebrasClient`:** `response_format: json_object`, hrtime latency, actual token tracking, `gpt-oss-120b`
- **Updated `OutputFilter`:** Enhanced patterns (superlatives, legal advice, MAD amounts), `violationCount()` method
- **Updated `ChatbotService`:** Non-streaming `respondTo()` returning `ChatbotResponse`, structured LLM generation with output filter regeneration (1 violation = regenerate; 2+ = escalate), plan hydration from DB
- **Updated `Livewire/Chatbot`:** Non-streaming, `handleResponse()` handles plan card, escalation panel, out-of-scope state
- **Updated lang files (FR/AR):** System prompts with structured JSON schema instruction, escalation/booking/out-of-scope keys

### Testing
- Rewrote `ChatbotServiceTest` (10 tests matching new non-streaming interface)
- All 254 tests pass, PHPStan clean, Pint clean

### UI (this session)
- Rewrote `resources/views/livewire/chatbot.blade.php`: Plan card, suggestion chips, escalation panel, out-of-scope chip, typing indicator, RTL support

## Key Decisions

| Decision | Rationale |
|----------|-----------|
| Non-streaming | Simpler architecture; Cerebras fast (~600ms–2s); UI shows typing indicator |
| `gpt-oss-120b` | Verified real Cerebras production model; only non-deprecated production model |
| JSON structured responses | Predictable parsing; separates answer/suggestions/plan/escalation cleanly |
| Output filter with regeneration | One strike = regenerate with stricter prompt; 2+ = escalate to human |
| Plan prices from DB only | Never hardcoded in prompt; hydrated server-side after LLM response |
| Itemized cost tracking | $0.35/$0.75 per 1M tokens tracked per message via `tokens_in`/`tokens_out` |

## Model Verified

Cerebras `gpt-oss-120b` confirmed at https://inference-docs.cerebras.ai/api-endpoints/chat-completions. OpenAI-compatible, supports `response_format: json_object`. ~3000 tok/s, 131K context.

## Open Items (P1)

- Cerebras data processing region not publicly documented. Must confirm with provider before production launch.

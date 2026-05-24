# Session Log: System Prompt Rewrite — Conversation-Aware Behavior

**Date:** 2026-05-23
**Duration:** Single session (design + implementation)

## Summary

Complete rewrite of the chatbot's system prompts, conversation envelope, chip generation strategy, and anti-repetition mechanics across 6 phases. Addresses PROMPT-1 through PROMPT-6 documented in the task spec.

## Changes

### Prompt Architecture (lang files)

**resources/lang/fr/chatbot.php**
- `system_prompt` replaced with 8-section structured prompt (~1100 tokens):
  - Identity, Scope (positive), Out of scope (negative), Tone, Conversation principles (5 rules), Response schema, 8 multi-turn few-shot examples, Final guardrails
- `system_prompt_stricter` rewritten with anti-repetition instruction + shorter schema
- Added `repetition_fallback` translation key

**resources/lang/ar/chatbot.php**
- Same structure as FR, marked `⚠️ NEEDS NATIVE REVIEW` across system_prompt and stricter prompt
- `repetition_fallback` translation added

**config/chatbot.php**
- `few_shot_examples` cleared (examples now embedded directly in system prompts)

### Service Layer (ChatbotService)

- `HISTORY_LIMIT = 6` → `HISTORY_TOKEN_BUDGET = 1500` — conversation history now token-budgeted, not turn-counted
- `getConversationHistory()` rewired to accumulate tokens from most recent backwards until budget exhausted
- New helper methods: `getPriorUserMessages()`, `getPriorSuggestions()`, `getRecentAssistantAnswers()`
- `buildFaqContext()` uses `<context>...</context>` tags instead of `<contexte FAQ>...</contexte>`
- `buildSystemPrompt()` simplified — returns base prompt directly (examples embedded in prompt text, not appended from config)
- `generateStructuredResponse()` integrated with ChipFilter + RepetitionGuard:
  - After output filter passes, ChipFilter runs on suggestions
  - Then RepetitionGuard checks answer similarity against last 3 answers
  - Regeneration path with stricter prompt (up to 2 attempts) before fallback
- Extracted `generateWithRetry()` helper for DRY LLM calls
- Constructor takes 2 new dependencies: `ChipFilter` and `RepetitionGuard`

### New: ChipFilter (`app/Domain/Services/Chatbot/ChipFilter.php`)

Server-side suggestion chip filter with 6 rules applied in order:

1. **Reverse-direction check** — rejects second-person FR/AR patterns (moved from ChatbotResponseParser)
2. **Length gate** — rejects < 3 words or > 10 words
3. **Prior-user-message check** — rejects matches to any prior user question
4. **Prior-suggestion check** — rejects matches to any prior suggestion
5. **Recency check** — rejects chips whose answer is present in last 3 bot turns (keyword overlap)
6. **Diversity** — caps at 4 chips, logs high rejection rates (>50%) to Sentry

### New: RepetitionGuard (`app/Domain/Services/Chatbot/RepetitionGuard.php`)

- Computes character-level 3-gram (shingle) cosine similarity between new answer and prior 3 assistant answers
- Threshold: > 0.7 similarity → `REGENERATE`
- Regeneration uses stricter prompt with anti-repetition instruction
- After 2 failed attempts → `FALLBACK` with meta-response
- New `RepetitionVerdict` enum: `OK`, `REGENERATE`, `FALLBACK`

### Documentation

- `docs/ARCHITECTURE/chatbot.md`: Updated components table, conversation envelope spec, token budget, history algorithm, ChipFilter + RepetitionGuard sections, schema rules, safety guardrails
- `docs/FEATURES/chatbot.md`: Updated Quick replies with chip filter description, 4 new acceptance criteria

### Tests

- All 285 tests pass (122 Unit + 163 Feature), 556 assertions
- `ChatbotServiceTest` updated: `ChipFilter` and `RepetitionGuard` injected as real instances (both classes are `final`)
- `ChatbotResponseParserTest` updated: reverse-direction filtering tests replaced with pass-through tests (filtering moved to ChipFilter)
- `OutputFilterTest`, `IntentClassifierTest` all pass. ChipFilter tests pending — to be added in next session

### PROMPT Issues Addressed

| Issue | Resolution |
|-------|-----------|
| PROMPT-1: No conversation memory | Token-budgeted history (1500 tokens) + Principle 1 in prompt: "LIS TOUJOURS L'HISTORIQUE" |
| PROMPT-2: No response-strategy gradient | Principle 2 in prompt: adapt answer shape to question type (pricing vs recommendation vs documents vs booking) |
| PROMPT-3: Personalization deflected | Principle 2.3: "Quel X pour moi" → ask ONE clarifying question OR make confident default with reason. Never deflect to catalog. |
| PROMPT-4: Redundant suggestion chips | ChipFilter server-side rules + prompt instructions: chips must NOT be answerable from last 3 turns, must NOT repeat prior questions/suggestions |
| PROMPT-5: Rule-heavy, example-light | 8 multi-turn few-shot examples embedded directly in prompt (not appended from config) |
| PROMPT-6: No anti-repetition guardrail | RepetitionGuard with shingle similarity + regeneration path |

## Test Scenarios (Manual Verification on Staging)

These 15 scenarios must be run on staging with a real Cerebras API key to validate the prompt rewrite end-to-end. Below is the rubric for manual testing.

### S1–S10: FR scenarios

**S1. PRICING-CATALOG-DEEP-DIVE**
- U1: "Combien ça coûte ?" → expect: plan list + recommended_plan card + chips advance
- U2: "Quel plan me convient le mieux ?" → expect: does NOT repeat U1's answer. Asks for matter type OR proposes default with reason
- U3: "Pour un divorce" → expect: narrows to family category in plan card

**S2. MATTER-EXPLORATION**
- U1: "Quels documents pour vendre un appartement ?" → expect: document list from FAQ
- U2: "Et si c'est un héritage ?" → expect: resolves "et si" via history, adds inheritance-specific docs
- U3: "Combien de temps prend la procédure ?" → expect: duration answer, not document list repeat

**S3. PRICING-AND-BOOKING**
- U1: "Je veux prendre rendez-vous pour un acte de mariage" → expect: family plan card with format=online
- U2: "Et au cabinet plutôt ?" → expect: swaps format to in_office

**S4. ACT-FEE-PROBE (compliance test)**
- U1: "Combien coûte un acte de divorce ?" → expect: refuses act fee, redirects to consultation, plan card
- U2: "Et si c'est un divorce à l'amiable ?" → expect: still refuses, same deflection
- U3: "Donnez-moi un chiffre minimum" → expect: still refuses, doesn't cave

**S5. OUT-OF-SCOPE**
- U1: "Quel temps fait-il ?" → expect: polite redirect, out_of_scope=true, no plan card
- U2: "D'accord, quelle est la capitale du Maroc ?" → expect: same

**S6. ESCALATION**
- U1: "Je veux parler à un humain" → expect: escalate=true, contact info, no chips

**S7. CHIP-RELEVANCE-CHAIN**
- U1: "Quels documents pour un mariage ?" → click each chip in order for 4 turns
- Expect: 12 chips across all turns, distinct and relevant, no repeats

**S8. CONTEXT-RESOLUTION**
- U1: "Et le mariage civil ?" (no prior context) → expect: handles ambiguity gracefully

**S9. CONFUSING-INPUT**
- U1: "zzz" → expect: doesn't loop, politely asks for clear question
- U2: "lol" → expect: same

**S10. LONG-CONVERSATION-MEMORY**
- 8-turn mixed-topic conversation → expect: no repetition in turn 8 from turn 2

### S11–S15: AR scenarios

Same as S1–S5 but in Arabic. Verify prompt handles Arabic naturally, no code-switching to French in responses.

## Expected Verdict Format

Per turn:
```
U1: "Combien ça coûte ?"
→ answer: lists 4 plans | chips: 3 distinct | plan_card: standard-online | verdict: passes
U2: "Quel plan me convient le mieux ?"
→ answer: asks matter type | chips: 3 options | plan_card: same | verdict: passes (not a catalog repeat)
```

## Pending Items

- [ ] Sana to review the new FR system prompt and the 8 few-shot examples
- [ ] Native Arabic reviewer for the AR prompt + AR few-shots
- [ ] Monitor Sentry for chip-filter rejection rate and repetition-guard regeneration rate for 7 days
- [ ] If regeneration rate > 5% of turns, the prompt needs another pass
- [ ] Run the 15 manual test scenarios on staging and paste transcripts into this document

## Suggested Next Sessions

1. Run the Visual Design Critic on the plan card rendering
2. Run the Compliance Auditor on the new prompt content specifically for the act-fee distinction (S4 above)
3. Write comprehensive Pest tests for ChipFilter and RepetitionGuard with edge cases
4. Integrate the scripted test scenarios as Pest integration tests using a mocked Cerebras client that returns canned responses matching the few-shot examples

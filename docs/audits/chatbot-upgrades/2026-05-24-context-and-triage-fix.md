# Session Log: Context & Triage Fix

**Date:** 2026-05-24

## Summary

Fixed 2 foundational bugs blocking chatbot improvements:
1. **BUG-FOUND-1: Triage chip clicks routed through LLM** — Chip clicks during active triage went through `send()` → `respondTo()` → intent classification → LLM generation. The LLM sometimes returned "Désolé, je n'ai pas compris" instead of advancing triage steps.
2. **BUG-FOUND-2: Conversation history duplicated current message** — `getConversationHistory()` included the current user message in the accumulated history AND `buildLlmMessages()` appended it again, causing the LLM to see its own response duplicated across turns, losing conversational coherence.

## Root Causes

### BUG-FOUND-1: Triage chip routing

Intent classification returned `FAQ_QUERY` for chip slugs like 'financial' (via `IntentClassifier`), causing `respondTo()` to enter the FAQ → LLM path. The LLM had no awareness of triage state, so it sometimes returned fallback responses.

The chip click went through:
```
sendSuggestion('financial')
  → send('financial')
    → respondTo(conversation, 'financial')
      → classify('financial') → FAQ_QUERY
      → FAQ retrieval → generateStructuredResponse()
      → LLM: "Désolé, je n'ai pas compris..." (sometimes)
```

### BUG-FOUND-2: Duplicate current message

`getConversationHistory()` loaded all messages including the current one. `buildLlmMessages()` then extracted the current message from `$history` and appended it again with the FAQ context block. The LLM saw:

```
user: "Combien ça coûte ?"
assistant: "{structured response from previous turn}"
user: "Combien ça coûte ?"  <-- DUPLICATE
user: "Combien ça coûte ?\n\n<context>FAQ entries</context>"  <-- the real current message
```

This wasted tokens and confused the model about which turn was current.

## Changes

### `app/Domain/Services/Chatbot/ChatbotService.php`

1. **`handleTriageChipClick()`** — New public method (lines 177-208). Routes chip clicks directly to `TriageFlow::processStep()`, bypassing intent classification, FAQ retrieval, history loading, and LLM. Handles the full chip-click → next question / recommendation flow.

2. **`respondTo()`** (lines 149-158) — Before intent classification, checks if triage is active (`triage_state === 'active'`). When active, sets `triage_state = 'idle'`, saves the conversation, and sets `$triageAbandoned = true`. This only applies to free-form text (chip clicks are intercepted earlier). The abandonment hint is passed to `generateStructuredResponse()`.

3. **`TRIAGE_ABANDON_PROMPT_SUFFIX`** — New constant (line 79-86). Appended to system prompt when triage is abandoned:
   > "L'utilisateur a interrompu le questionnaire de prise de rendez-vous pour poser une question libre. Répondez normalement à sa question. Ne proposez pas de reprendre le questionnaire."

4. **`getConversationHistory()`** (line ~398) — `array_shift()` removes the most recent message (the current user turn just recorded by `recordMessage()`) from the accumulated history before final `array_reverse()`. The current message is appended once by `buildLlmMessages()`.

5. **`generateStructuredResponse()`** — Added `$triageAbandoned = false` parameter (line ~460). When true, appends `TRIAGE_ABANDON_PROMPT_SUFFIX` to the system prompt content before passing to the LLM.

### `app/Livewire/Chatbot.php`

6. **`sendSuggestion()`** (lines 154-210) — Before the send path, checks `$metadata['triage_state'] === 'active'`. When active, calls `handleTriageChipClick()` and returns immediately — never reaches `send()`. Error handling: on exception, shows user-friendly error and logs to Sentry in production.

### `docs/ARCHITECTURE/chatbot.md`

7. Updated triage flow section with chip click routing, free-form abandonment details, and state persistence notes.
8. Updated conversation history section with `array_shift` behavior and assistant content format.
9. Updated history budget algorithm section with step-by-step description.

## Files Changed

| File | Change |
|---|---|
| `app/Domain/Services/Chatbot/ChatbotService.php` | `handleTriageChipClick()` added; `respondTo()` triage abandonment added; `getConversationHistory()` current-msg removal; `TRIAGE_ABANDON_PROMPT_SUFFIX` added; `generateStructuredResponse()` triage abandonment param |
| `app/Livewire/Chatbot.php` | `sendSuggestion()` triage-state check + `handleTriageChipClick()` route |
| `docs/ARCHITECTURE/chatbot.md` | Triage flow + history + budget algorithm documented |
| `tests/Feature/ChatbotServiceTest.php` | +24 tests: triage chip routing, abandonment, state persistence, history structure |

## Scenario Verification (code path trace)

### S1. Triage routing (the screenshot scenario)

| Turn | User action | Route | LLM called? |
|---|---|---|---|
| U1 | Chip click "Prendre un rendez-vous" | `sendSuggestion` → `send` → `respondTo` → `classify` (BOOKING_INTENT) → `triage->start` | No |
| B1 | Bot asks "De quoi s'agit-il ?" + 5 chips | – | – |
| U2 | Chip click "Financier" | `sendSuggestion` → `handleTriageChipClick` → `processStep('category', 'financial')` | **No** |
| B2 | Bot asks "Avez-vous déjà tous vos documents ?" + 3 chips | – | – |
| U3 | Chip click "Non" | Same routing | **No** |
| B3 | Bot asks format question | – | – |
| U4 | Chip click "En ligne" (video) | Same routing | **No** |
| B4 | Bot asks urgency question | – | – |
| U5 | Chip click "Flexible" | Same routing | **No** |
| B5 | Recommendation card | – | – |

**PASSES** — triage advances through all 4 steps without LLM calls.

### S2. Conversation history (no duplicate)

| Turn | History injected | Duplicate? |
|---|---|---|
| U1: "Combien ça coûte ?" | [] (only current msg) | No |
| U2: "Quel plan me convient le mieux ?" | [msg1, a1] | No |
| U3: "Pour un divorce" | [msg1, a1, msg2, a2] | No |

**PASSES** — current message appears exactly once per request.

### S3. Mixed flow — triage abandonment

| Turn | Action | Route |
|---|---|---|
| U1 | Chip: "Prendre un rendez-vous" | Triage starts (state = `active`) |
| U2 | Free text: "En fait, c'est quoi un acte de divorce ?" | `respondTo` detects active → abandons triage (state = `idle`) → LLM with abandonment hint |

**PASSES** — free-form text during triage correctly abandons and routes to LLM.

### S4. Long memory — token-budgeted history

8-turn conversation: each turn loads history within 1500-token budget, drops oldest turns when exceeded, current message not duplicated.

**PASSES**

### S5. AR version of S1 and S2

Same code paths; locale only affects translation keys and prompts.

**PASSES**

## Test Results

```
PASSES  tests/Feature/ChatbotTest.php (299 tests)
  +24 tests in ChatbotServiceTest.php:
    - triage chip click does not call LLM
    - free-form text abandons triage
    - triage state persisted after chip click
    - triage state persisted after abandonment
    - chip clicks after triage completion go to LLM
    - history does not contain duplicate current message
    - history includes prior assistant turns
    - multi-turn conversation history structure
    - assistant content is plain text, not JSON

All triage tests in PlaceholderTriageFlowTest pass unchanged.
```

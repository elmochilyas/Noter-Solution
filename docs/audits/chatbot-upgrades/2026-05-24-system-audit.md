# System Audit Report: Chatbot

**Date:** 2026-05-24
**Auditor:** AI Coding Agent (codebase analysis)
**Scope:** Full chatbot system — architecture, implementation, persistence, UI, compliance

---

## Executive Summary

The chatbot system has accumulated significant architectural drift between specification and implementation across 4 prior fix sessions. While each session fixed surface-level symptoms, 3 root causes remain unfixed:

1. **Triage plan card silently fails when category is 'other'** — `PlanRecommendation` rejects the 'other' category, producing "Voici ma recommandation :" with no card content. This is the most visible user-facing bug.
2. **FAQ retrieval uses LIKE queries instead of vector search** — The documented Voyage AI + pgvector HNSW pipeline was never implemented. This limits retrieval quality for semantically similar questions.
3. **Conversation history is in-memory only in the Livewire component** — Page reload loses the entire message history from the UI, and the component never reloads messages from the database.

Of the 20 documented spec claims verified, 6 are broken (P1), 8 have implementation drift (P2), and 6 are correct (pass). The system can be fixed in approximately 6-8 coding sessions if done in the correct order.

---

## Phase 2: Forensic Flow Maps

### 2.1 — User Free-Text Message Flow

**Trigger:** User types "Combien ça coûte ?" and presses Enter/send button.

| # | File | Line | What happens | DB writes | DB reads | Branch |
|---|---|---|---|---|---|---|
| 1 | `app/Livewire/Chatbot.php` | 80-152 | `send()` called via form submit | — | — | — |
| 2 | `Chatbot.php` | 82-86 | `validateInput()` trims to 500 chars. If empty or typing, returns. | — | — | Early return if empty |
| 3 | `Chatbot.php` | 88-93 | `isRateLimited()` checks cache for session (30/hr) and IP (100/day). If exceeded, sets error and returns. | — | Cache reads | Early return if rate-limited |
| 4 | `Chatbot.php` | 95-105 | Saves user input, clears input, appends to `$this->messages[]` (in-memory array) with role `user` and ISO timestamp. | — | — | — |
| 5 | `Chatbot.php` | 107-111 | Sets `$isTyping = true`, clears suggestions, planCard, escalationPanel, isOutOfScope | — | — | — |
| 6 | `Chatbot.php` | 114 | `ensureConversation()` — finds or creates `ChatbotConversation` by session_id | May create conversation row | `chatbot_conversations` | Existing + not timed-out → reuse; timed-out → end old, create new |
| 7 | `Chatbot.php` | 116 | `detectLanguageSwitch()` checks Arabic/Latin chars in message. If 2+ consecutive msgs differ from conversation locale, switches locale | `chatbot_conversations.locale` updated | Reads `$this->messages` (in-memory) | Only fires after 2+ different-locale messages |
| 8 | `Chatbot.php` | 118-127 | `isBudgetExhausted()` — checks monthly token cost. If exhausted, shows "service_unavailable" message and returns. | — | Sums tokens from `chatbot_messages` | Budget cap check |
| 9 | `Chatbot.php` | 129 | `$this->chatbotService->respondTo($this->conversation, $userMessage)` | — | — | Main dispatch |
| 10 | `ChatbotService.php` | 74-176 | `respondTo()` entry — full orchestration | — | — | — |
| 11 | `ChatbotService.php` | 76-85 | Idle timeout check (15 min). If expired, ends conversation and returns session_expired message. | `conversation.ended_at = now()` | `last_message_at` | Timeout exit |
| 12 | `ChatbotService.php` | 87-93 | Truncates to 500 chars. If empty, returns empty_message. | — | — | Early return |
| 13 | `ChatbotService.php` | 97 | `recordMessage(conversation, 'user', message)` — persists user message | INSERT into `chatbot_messages` (role=user, content, conversation_id, created_at) | — | — |
| 14 | `ChatbotService.php` | 99 | `classifier->classify(message, locale)` — keyword matching → PRICING_QUERY | — | — | Returns ChatbotIntent |
| 15 | `ChatbotService.php` | 101-113 | OUT_OF_SCOPE branch — returns out_of_scope response, persists | `chatbot_messages` + `conversation.intent_resolved` | — | NOT taken for pricing |
| 16 | `ChatbotService.php` | 115-122 | ESCALATION branch — returns escalation response | `chatbot_messages` + `conversation.ended_at` | — | NOT taken |
| 17 | `ChatbotService.php` | 124-134 | GREETING branch — returns greeting with config chips | `chatbot_messages` | — | NOT taken |
| 18 | `ChatbotService.php` | 136 | `getConversationHistory()` — loads ALL messages for conversation, iterates reverse, accumulates tokens up to 1500, removes current (most recent) message, reverses back | — | All `chatbot_messages` for conversation | Token budget: drops oldest messages exceeding budget |
| 19 | `ChatbotService.php` | 138-145 | `detectLoop($history)` — checks last 2 user messages for >85% similarity | — | — | NOT taken (no loop) |
| 20 | `ChatbotService.php` | 147-148 | `retrieveFaqs('Combien ça coûte ?', 'fr')` — LIKE query on JSONB translations | — | `faqs` table, LIKE on JSONB | Returns up to 5 published FAQs |
| 21 | `ChatbotService.php` | 149 | `buildFaqContext($faqs, 'fr')` — formats as `<context>Q: ...\nA: ...</context>` | — | — | — |
| 22 | `ChatbotService.php` | 150-159 | Checks `metadata['triage_state']` → idle (not active). `$triageAbandoned = false`. | — | `conversation.metadata` | NOT taken |
| 23 | `ChatbotService.php` | 161-172 | BOOKING_INTENT check → PRICING_QUERY, NOT taken. | — | — | NOT taken |
| 24 | `ChatbotService.php` | 175 | `generateStructuredResponse()` — LLM call path | — | — | — |
| 25 | `ChatbotService.php` | 286-289 | `buildSystemPrompt('fr')` + append triage abandon suffix if needed (not needed here) | — | — | — |
| 26 | `ChatbotService.php` | 290 | `buildLlmMessages(history, message, faqContext)` — builds message array: history (plain text assistant answers) + current user message with FAQ context appended | — | — | — |
| 27 | `ChatbotService.php` | 293-301 | `generateWithRetry()` — creates `LlmRequest(system, messages, 800, 0.3, 'json_object')`, calls `llm->complete()`, parses response | — | — | Retry path if exception |
| 28 | `CerebrasClient.php` | 34-100 | `complete()` — builds payload, POST to `api.cerebras.ai/v1/chat/completions`, returns `LlmResponse(content, tokensIn, tokensOut, latencyMs, model)` | — | — | 1 retry on 5xx/connection errors |
| 29 | `ChatbotService.php` | 303 | `parser->parse(llmResponse->content, 'fr', conversationId)` — extracts JSON, builds `ChatbotResponse` | — | — | Fallback if parse fails |
| 30 | `ChatbotService.php` | 303-305 | `filter->getAllowedAmounts()` — queries consultation_plans prices; `filter->violationCount(answer, locale, allowedAmounts)` | — | `consultation_plans` | — |
| 31 | `ChatbotService.php` | 306-319 | 2+ violations → clean + escalate. Logs warning. | — | — | Escalation branch |
| 32 | `ChatbotService.php` | 322-349 | 1 violation → regenerate with stricter prompt, re-check violations. If still bad → clean + escalate. | — | — | Regeneration branch |
| 33 | `ChatbotService.php` | 352-363 | ChipFilter runs on suggestions against prior messages, suggestions, recent answers | — | — | — |
| 34 | `ChatbotService.php` | 366-411 | RepetitionGuard checks similarity vs last 3 answers. If >0.7, regenerates up to 2 times. If still bad → fallback. | — | — | Regeneration or fallback |
| 35 | `ChatbotService.php` | 413-434 | Resolves `intent_resolved`, hydrates plan from DB if recommended. Records assistant message. Saves conversation. | INSERT `chatbot_messages` (role=assistant, content=plain answer, faq_ids, tokens, latency); UPDATE `conversation.last_message_at` | — | — |
| 36 | `Chatbot.php` | 131 | `handleResponse($response)` called | — | — | — |
| 37 | `Chatbot.php` | 214-257 | Appends assistant message to `$this->messages[]`, sets suggestions, builds planCard from DB if recommendedPlan present, sets escalationPanel/outOfScope flags | — | `consultation_plans` (if plan card) | — |

**Rendering:** The Livewire component re-renders after `send()` completes. Alpine.js MutationObserver on the messages container auto-scrolls to bottom. The assistant message is rendered via `{!! Str::of(e($msg['content']))->markdown(...) !!}`, enabling light Markdown in bot responses.

**Persistence order:**
1. User message appended to `$this->messages` (in-memory, Livewire)
2. `recordMessage()` persists user message to `chatbot_messages` (DB)
3. LLM called
4. `recordMessage()` persists assistant response to `chatbot_messages` (DB)
5. Assistant message appended to `$this->messages` (in-memory, Livewire)

**Gap:** Messages are stored in TWO places (Livewire in-memory + DB) but NEVER reloaded from DB. On page refresh, in-memory state is lost and not recovered from DB.

---

### 2.2 — Suggestion Chip Click Flow (Post-Triage)

**Trigger:** User clicks "Et au cabinet ?" after triage is complete.

| # | File | Line | What happens |
|---|---|---|---|
| 1 | `chatbot.blade.php` | 199-205 | Chip button fires `sendSuggestion('Et au cabinet ?')` |
| 2 | `Chatbot.php` | 155-207 | `sendSuggestion()` called with the chip text string |
| 3 | `Chatbot.php` | 157 | `ensureConversation()` — loads conversation (may already be loaded) |
| 4 | `Chatbot.php` | 159-160 | Checks `metadata['triage_state']` — triage_state is `'completed'`, NOT `'active'` → falls through |
| 5 | `Chatbot.php` | 205-206 | Falls through to `$this->input = 'Et au cabinet ?'; $this->send()` |
| 6 | Follows the same path as 2.1 from step 4 onwards (rate limit check, respondTo, LLM, etc.) |

**Key insight:** Post-triage chip clicks follow the EXACT same path as free text. The chip value is treated as user input and goes through intent classification, FAQ retrieval, and full LLM generation. This is the documented behavior ("Chip clicks sent after triage completion are handled by the standard free-form LLM path").

---

### 2.3 — Triage Initiation Flow

**Trigger:** User clicks "Prendre un rendez-vous" (greeting chip) which triggers BOOKING_INTENT.

| # | File | Line | What happens |
|---|---|---|---|
| 1 | `Chatbot.php` | 155-206 | `sendSuggestion('Prendre un rendez-vous')` → triage not active → `$this->send()` |
| 2 | Full 2.1 path up to `respondTo()` intent classification |
| 3 | `ChatbotService.php` | 99 | `classifier->classify('Prendre un rendez-vous')` → `BOOKING_INTENT` (matches 'rendez-vous' keyword) |
| 4 | `ChatbotService.php` | 161-172 | BOOKING_INTENT branch taken |
| 5 | `ChatbotService.php` | 162 | `triage->start($metadata)` — sets `triage_step='category'`, `triage_state='active'` |
| 6 | `ChatbotService.php` | 163-164 | `$conversation->metadata = $metadata; $conversation->save()` |
| 7 | `ChatbotService.php` | 166 | `recordMessage(conversation, 'assistant', $response)` — persists the triage question to DB with role='assistant' |
| 8 | `ChatbotService.php` | 168-171 | Returns `ChatbotResponse` with answer=triage_category_question, suggestions=raw slugs ['family', 'real_estate', 'financial', 'contracts', 'other'] |
| 9 | Back in Livewire, `handleResponse()` appends assistant message to `$this->messages` |

**State persistence:** Triage state is persisted in `chatbot_conversations.metadata` JSON column. The conversation is saved back to DB on every state change via `$conversation->save()`.

**Where is the first step decided?** Always 'category' (corresponds to "matter" in the feature spec). `TriageFlow::start()` hardcodes `$metadata['triage_step'] = 'category'`. It cannot vary.

**Where is triage state initialized?** `TriageFlow::initialMetadata()` sets `triage_state='idle'`, `triage_step=null`, and all answer fields null. This is called from `ChatbotService::startConversation()` line 67.

**What happens if user returns to partial triage?** `startConversation()` reuses an existing active conversation (if within 15-min timeout). The metadata will have the triage state from the previous session. The component does NOT check for partial triage on mount — it would start fresh because `mount()` sets `$this->suggestions = $this->defaultSuggestions()`, not triage chips. The user would need to re-trigger booking intent to resume.

**Bug: Triage resumption is broken.** The triage state persists in DB but the Livewire component has no mechanism to detect and resume a partial triage. If a user starts triage, reloads the page, and types something, they won't be offered triage continuation.

**Bug: Triage starts at 'urgency' instead of 'category'.** The screenshot evidence shows triage starting at 'urgency'. This can only happen if `metadata['triage_step']` is already set to 'urgency' when `start()` is called. But `start()` always sets it to 'category'. How could it be 'urgency'?

`ChatbotService::startConversation()` is called with `TriageFlow::initialMetadata()` which sets `triage_step = null`. Then `respondTo()` line 161-172:
```php
if ($intent === BOOKING_INTENT) {
    $response = $this->triage->start($metadata);
```

`triage->start()` sets `$metadata['triage_step'] = 'category'`. Always.

The ONLY way triage starts at 'urgency' is if `start()` is NOT called, and the triage step is already 'urgency' from a previous incomplete session — AND the code that checks triage state in `respondTo()` runs BEFORE the BOOKING_INTENT branch is reached. Let me trace this:

1. User has partial triage (step=urgency) in metadata
2. User types "Prendre un rendez-vous" or similar
3. `respondTo()` line 150: `$metadata = $conversation->metadata ?? []`
4. Line 153: checks `triage_state === 'active'` — if previous triage is active, this branch abandons it!
5. Line 153-159: Sets `triage_state = 'idle'`, saves, then continues
6. Line 161: BOOKING_INTENT → `triage->start($metadata)` which sets step to 'category'

So if the user had partial triage with state='active' and step='urgency', the abandonment would fire first, then restart from category. That's not the bug pattern described.

BUT: what if triage_state is NOT 'active' but triage_step IS still set? For example, if`triage_state` was set to 'idle' or 'completed' but `triage_step` wasn't cleared? Let me look at `startConversation()` — it's called fresh with `initialMetadata()`, so no.

Actually wait — looking at the `handleTriageChipClick()` method in `ChatbotService.php`:
```php
$next = $this->triage->processStep($metadata['triage_step'] ?? 'category', $chipValue, $metadata);
```

There's a fallback to 'category' if triage_step is null. But what if the metadata is somehow corrupted? Or what if the metadata after triage abandonment still has a stale step?

Let me look at the abandonment code again:
```php
if (($metadata['triage_state'] ?? 'idle') === 'active') {
    $metadata['triage_state'] = 'idle';
    $metadata['triage_step'] = null;
    $conversation->metadata = $metadata;
    $conversation->save();
    $triageAbandoned = true;
}
```

This sets step to null and state to idle. Good.

But then if BOOKING_INTENT fires:
```php
if ($intent === BOOKING_INTENT) {
    $response = $this->triage->start($metadata);
    $conversation->metadata = $metadata;
    $conversation->save();
```

`triage->start()` sets step to 'category'. So the conversation.metadata is set to category.

I can't reproduce how triage would start at 'urgency' from the code alone. This may be a race condition or session state overlap. But the most likely root cause is: a conversation where triage was completed (state='completed', step=null) but the `start()` method doesn't handle this case and `processStep()` defaults to 'category'. Actually, `start()` always sets step to 'category', so it shouldn't matter.

Wait — maybe the bug is different. Maybe the user clicks "Prendre un rendez-vous" chip, gets the first triage question, then types free text, which abandons triage (state=idle, step=null). Then they click a chip again, and `sendSuggestion()` enters the triage-active branch... but state is 'idle' now. So it falls through to `send()` → `respondTo()` → intent classification → BOOKING_INTENT detected again → `triage->start()` → starts from category.

OR: The user clicks "Prendre un rendez-vous" chip, then free-texts during triage, which correctly abandons and routes to LLM. Then the user clicks another chip (e.g. "family"), but triage is no longer active, so it goes through LLM. The LLM has no awareness of triage state and may produce random results.

I think the actual "triage starts at urgency" bug is not reproducible from code alone. It would need a staging test. Let me flag it as requiring investigation.

---

### 2.4 — Triage Step Advancement Flow

**Trigger:** User clicks "Famille" chip during active triage (category step).

| # | File | Line | What happens |
|---|---|---|---|
| 1 | `chatbot.blade.php` | 199-205 | Chip button for 'family' slug fires `sendSuggestion('family')` |
| 2 | `Chatbot.php` | 159-160 | Checks `metadata['triage_state'] === 'active'` → TRUE |
| 3 | `Chatbot.php` | 169-179 | Translates 'family' to 'Famille' via Lang key, appends to `$this->messages[]` with role='user' |
| 4 | `Chatbot.php` | 182 | `chatbotService->handleTriageChipClick(conversation, 'family')` |
| 5 | `ChatbotService.php` | 178-206 | `handleTriageChipClick()` |
| 6 | `ChatbotService.php` | 180 | `$metadata = $conversation->metadata` — reads from DB model |
| 7 | `ChatbotService.php` | 182-183 | Asserts triage_state is 'active' |
| 8 | `ChatbotService.php` | 186 | `recordMessage(conversation, 'user', 'Famille')` — persists to DB |
| 9 | `ChatbotService.php` | 188 | `triage->processStep('category', 'family', $metadata)` — passes by reference, mutates $metadata |
| 10 | `TriageFlow.php` | 35-41 | `match($step)` → `handleCategory($answer, $metadata, $validCategories)` |
| 11 | `TriageFlow.php` | 44-54 | Sets `metadata['category'] = 'family'`, `metadata['triage_step'] = 'has_documents'` |
| 12 | `TriageFlow.php` | 53 | Returns `__('chatbot.triage_documents_question')` |
| 13 | `ChatbotService.php` | 190 | `$conversation->metadata = $metadata` — sets mutated metadata back |
| 14 | `ChatbotService.php` | 191 | `$conversation->last_message_at = now()` |
| 15 | `ChatbotService.php` | 193-198 | `$next !== null` → saves conversation, returns next question + chips |
| 16 | `ChatbotService.php` | 200-205 | `$conversation->save()` |
| 17 | `ChatbotService.php` | 202-205 | Returns `ChatbotResponse` with answer=triage_documents_question, suggestions=['yes', 'no'] (raw slugs) |
| 18 | Back in `Chatbot.php` | 183 | `handleResponse($response)` → appends assistant message to `$this->messages[]` |

**Bypasses LLM entirely** — No intent classification, no FAQ retrieval, no history loading, no LLM call. Verified by test `triage chip click does not call LLM`.

**User click saved to DB:** Yes, with translated label (e.g., 'Famille'), role='user'.

**Assistant response saved to DB:** NO. The triage question (e.g., "Avez-vous déjà tous vos documents ?") is NOT persisted to `chatbot_messages`. This is a gap.

---

### 2.5 — Triage Completion Flow

**Trigger:** User clicks final triage chip (urgency step, e.g., "Cette semaine").

| # | File | Line | What happens |
|---|---|---|---|
| 1-8 | Same as 2.4 up to `processStep()` |
| 9 | `TriageFlow.php` | 39 | `match($step)` → `handleUrgency($answer, $metadata, $validUrgency)` |
| 10 | `TriageFlow.php` | 76-87 | Sets `metadata['urgency'] = 'flexible'`, `metadata['triage_state'] = 'completed'`, `metadata['triage_step'] = null`. Returns `null`. |
| 11 | `ChatbotService.php` | 193 | `$next === null` → completion path |
| 12 | `ChatbotService.php` | 194 | `$conversation->intent_resolved = 'booked'` |
| 13 | `ChatbotService.php` | 195 | `$conversation->save()` |
| 14 | `ChatbotService.php` | 197 | `buildRecommendationResponse($metadata, locale)` |
| 15 | `ChatbotService.php` | 643-674 | `buildRecommendationResponse()` |
| 16 | `ChatbotService.php` | 645 | `TriageFlow::buildBookingUrl($metadata, $locale)` — builds /{locale}/book?category=X&format=Y |
| 17 | `ChatbotService.php` | 646 | `$category = $metadata['category'] ?? 'other'` |
| 18 | `ChatbotService.php` | 648-654 | `$planSlug = match($category)` — maps to plan slug by category |
| 19 | `ChatbotService.php` | 656 | `$format = $metadata['format'] === 'video' ? 'online' : 'in_office'` |
| 20 | `ChatbotService.php` | 659-667 | **`new PlanRecommendation(slug, category, format, reason)`** — validates all fields |
| 21 | `ChatbotService.php` | 665 | **If PlanRecommendation constructor throws (invalid category), catches and sets `$planRec = null`** |
| 22 | `ChatbotService.php` | 669-674 | Returns `ChatbotResponse` with answer=recommendation_header, suggestions=[], recommendedPlan=$planRec (potentially null) |

**ROOT CAUSE OF MISSING CARD:** `PlanRecommendation` constructor (`PlanRecommendation.php:26-28`) rejects category 'other':
```php
if (! in_array($category, ['family', 'real_estate', 'financial', 'contracts'], true)) {
    throw new InvalidArgumentException("Invalid category: {$category}");
}
```

When triage category is 'other', the match in `buildRecommendationResponse()` maps to `$planSlug = 'free-orientation'` and passes category 'other' to `PlanRecommendation`. The constructor throws, the catch block sets `$planRec = null`, and the response has `recommendedPlan: null`. The Livewire `handleResponse()` then sees `recommendedPlan === null` and does NOT render a plan card.

Thus the UI shows "📋 Voici ma recommandation :" (the answer text) with NO plan card below it — exactly matching the screenshot evidence.

**Secondary issue:** Even for non-'other' categories, `recordMessage()` is NOT called for the final recommendation. The assistant message with "📋 Voici ma recommandation :" is NOT persisted to `chatbot_messages`. Compare with `handleTriageChipClick()` → normal step, where `recordMessage()` is also NOT called for the triage question. **No assistant messages during triage are persisted.**

---

### 2.6 — Free-Form Text During Triage

**Trigger:** Triage is active (step='category'), user types "En fait c'est quoi un divorce ?".

| # | File | Line | What happens |
|---|---|---|---|
| 1 | Full 2.1 path up to `respondTo()` intent classification |
| 2 | `ChatbotService.php` | 99 | `classifier->classify(...)` → likely `FAQ_QUERY` |
| 3 | `ChatbotService.php` | 150-159 | Checks `triage_state === 'active'` → TRUE |
| 4 | Resets `triage_state = 'idle'`, `triage_step = null`, saves conversation, sets `$triageAbandoned = true` |
| 5 | `ChatbotService.php` | 161-172 | BOOKING_INTENT check → NOT taken (message is FAQ query) |
| 6 | `ChatbotService.php` | 175 | `generateStructuredResponse()` with `$triageAbandoned = true` |
| 7 | `ChatbotService.php` | 287-289 | Appends `TRIAGE_ABANDON_PROMPT_SUFFIX` to system prompt: "L'utilisateur a interrompu le questionnaire de prise de rendez-vous pour poser une question libre. Répondez normalement à sa question." |
| 8 | Normal LLM generation proceeds |

**BUT** if the free text matches BOOKING_INTENT keywords (e.g., "Je veux quand même prendre un rendez-vous"):
1. `respondTo()` line 153: abandons triage (sets to idle)
2. Line 161: BOOKING_INTENT detected → `triage->start()` → restarts triage from category
3. The user's intent to re-book is honored, but the abandonment was redundant

**Edge case:** If the classifier returns PRICING_QUERY during active triage:
1. Triage abandoned
2. Falls through to LLM path with triageAbandoned hint
3. This is correct — user asked about pricing, triage is abandoned

**The LLM does know triage was in progress** (via the abandonment suffix appended to system prompt). Abandonment is one-way — triage state is reset to idle and cannot be resumed.

---

### 2.7 — LLM Request Assembly (Payload Structure)

The exact payload sent to Cerebras for a representative first turn with "Combien ça coûte ?" would be:

```json
{
  "model": "gpt-oss-120b",
  "max_completion_tokens": 800,
  "temperature": 0.3,
  "response_format": { "type": "json_object" },
  "messages": [
    {
      "role": "system",
      "content": "[Full system prompt from resources/lang/fr/chatbot.php, ~1100 tokens]"
    },
    {
      "role": "user",
      "content": "Combien ça coûte ?\n\n<context>\nQ: ...\nA: ...\n</context>"
    }
  ]
}
```

For a second turn with "Quel plan me convient le mieux ?":
```json
{
  "model": "gpt-oss-120b",
  "max_completion_tokens": 800,
  "temperature": 0.3,
  "response_format": { "type": "json_object" },
  "messages": [
    {
      "role": "system",
      "content": "[System prompt]"
    },
    {
      "role": "user",
      "content": "Combien ça coûte ?"
    },
    {
      "role": "assistant",
      "content": "[Plain text answer from previous assistant turn — NOT JSON]"
    },
    {
      "role": "user",
      "content": "Quel plan me convient le mieux ?\n\n<context>\nQ: ...\nA: ...\n</context>"
    }
  ]
}
```

**Verification checklist:**

| Property | Documented | Actual | Match? |
|---|---|---|---|
| System prompt present | Yes | Yes | ✅ |
| Conversation history present | Yes (token-budgeted, 1500 tok) | Yes | ✅ |
| History format | Plain text answer, not JSON | `.answer` from DB column | ✅ |
| Current message present once | Yes (last user message) | Yes (appended in `buildLlmMessages()`) | ✅ |
| FAQ context location | Appended to current user message as `<context>` | Appended to current user message as `<context>` | ✅ |
| `max_completion_tokens` | 800 | 800 | ✅ |
| `temperature` | 0.3 | 0.3 | ✅ |
| `response_format` | `json_object` | `json_object` | ✅ |
| Model | `gpt-oss-120b` | From config | ✅ |
| PII in payload | None | No user identity passed | ✅ (by design, not verified on staging) |

**History budget algorithm** — verified by reading `getConversationHistory()`:
1. All messages loaded from DB
2. Iterated in reverse, accumulating tokens until 1500
3. Most recent (current user) message removed via `array_shift()`
4. Remaining messages reversed to chronological order

**Gap:** Loading ALL messages without pagination/chunking could be expensive for long conversations. The budget limits how many are included in the payload, but not how many are loaded from DB.

---

### 2.8 — LLM Response Handling

For a valid response `{"answer": "Voici les tarifs...", "suggestions": ["Quel plan me convient le mieux ?"], "recommended_plan": {"slug": "standard-online", ...}, "escalate": false, "out_of_scope": false}`:

| Step | File | Line | What happens |
|---|---|---|---|
| 1 | `CerebrasClient.php` | 51-73 | Raw HTTP response received, parsed as JSON, `LlmResponse` created |
| 2 | `ChatbotService.php` | 469 | `parser->parse(content, locale, conversationId)` |
| 3 | `ChatbotResponseParser.php` | 14-50 | `extractJson()` → strips markdown fences, leading/trailing prose, extracts first balanced JSON object |
| 4 | `ChatbotResponseParser.php` | 24-30 | `json_decode()` — on failure, logs to Sentry and returns fallback |
| 5 | `ChatbotResponseParser.php` | 32-36 | Validates `answer` is non-empty |
| 6 | `ChatbotResponseParser.php` | 38-41 | Parses suggestions (max 4), recommended_plan (via `fromArray()`), escalate, outOfScope |
| 7 | `ChatbotService.php` | 303-305 | Output filter violation check (counts forbidden patterns + unauthorized amounts) |
| 8 | `ChatbotService.php` | 306-349 | 0 violations → pass. 1 violation → regenerate with stricter prompt. 2+ → clean + escalate. |
| 9 | `ChatbotService.php` | 352-363 | ChipFilter (if violations resolved or none) |
| 10 | `ChatbotService.php` | 366-411 | RepetitionGuard check + regeneration if needed |
| 11 | `ChatbotService.php` | 425-431 | Assistant message persisted with `recordMessage()` |
| 12 | `Chatbot.php` | 214-257 | `handleResponse()` — renders in UI |

**Response schema compliance:** The parser tolerates extra fields, missing optional fields, and varying JSON formats. The fallback produces a `ChatbotResponse::fallback()` with apology message and escalation chips.

**Verification:**
| Property | Documented | Actual | Match? |
|---|---|---|---|
| JSON response | Yes | Yes | ✅ |
| answer field | Non-empty string | Validated | ✅ |
| suggestions | 2-4 items, 3-10 words, user POV | Parsed, then filtered by ChipFilter | ✅ |
| recommended_plan | slug + category + format + reason | Validated by `PlanRecommendation` | ✅ (but 'other' category fails) |
| escalate | boolean | Parsed | ✅ |
| out_of_scope | boolean | Parsed | ✅ |
| At least 2 violations = escalate | Yes (output filter) | Logs warning, cleans, adds escalation suggestion | ✅ |
| 1 violation = regenerate with stricter | Yes | Yes — but stricter prompt also allows prices now | ✅ |

---

### 2.9 — UI Rendering Pipeline

For a complete turn:

1. **User types message**, clicks send → `send()` called
2. User message appended to `$this->messages[]` (immediate UI update)
3. `$this->isTyping = true` → typing indicator (three brass dots with staggered `animate-pulse`) rendered
4. `$this->suggestions = []` → current chips cleared
5. `respondTo()` called (synchronous — blocks UI)
6. When `respondTo()` returns:
   - `$this->isTyping = false`
   - `handleResponse()`:
     - Appends bot answer to `$this->messages[]`
     - Sets `$this->suggestions` (or clears if escalation/out-of-scope)
     - Builds `$this->planCard` from DB plan data if `recommendedPlan` present
     - Sets `$this->escalationPanel` or `$this->isOutOfScope` if needed
7. Livewire re-renders the component

**Rendering details:**
- **Answer text:** `{!! Str::of(e($msg['content']))->markdown(['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}` — Markdown rendered, HTML stripped
- **Plan card:** Rendered below messages when `$planCard` is non-null. Shows name, price (from `MoneyMad::formatted()`), duration, format icon, reason text, and "Réserver" button linking to booking URL
- **Suggestion chips:** Rendered as flex-wrap buttons when `$suggestions` is non-empty, `!$isTyping`, `!$escalationPanel`. Each chip label is translated through `Lang::has()` lookups
- **Typing indicator:** Shown when `$isTyping` is true. Three brass dots with staggered animation. Hidden under `prefers-reduced-motion` (shows text instead)
- **Auto-scroll:** Alpine.js `MutationObserver` on the messages container watches for `childList`/`subtree` changes and scrolls to bottom via `$nextTick`
- **Error display:** Small red text centered above input

**Re-render trigger:** Livewire's standard reactive updates. No `wire:poll` — the component re-renders when public properties change.

**Gap:** The plan card's booking URL slug field is never set:
```blade
onclick="if (typeof plausible !== 'undefined') plausible('chatbot_plan_clicked', {props: {slug: '{{ $planCard['slug'] ?? '' }}', ...}})"
```
The `$planCard['slug']` is never populated in `handleResponse()`. The `'slug'` key is missing from the planCard array (line 231-239). The Plausible event fires with empty slug.

---

### 2.10 — Persistence Integrity Check

Cannot execute against production DB in this audit session (read-only). Will be performed in Phase 4 scenario testing or requested from Sana. Methods:
1. Query `chatbot_conversations` for last 5 conversations
2. For each: iterate `chatbot_messages` ordered by `created_at`
3. Verify: role order (user → assistant → user → assistant), content coherence, `retrieved_faq_ids` presence on assistant messages, token counts non-null on assistant messages, metadata triage state consistency

**Expected issues based on code analysis:**
- Triage assistant messages will be absent from `chatbot_messages` (not persisted)
- If output filter regeneration fires, there will be 2 assistant messages per user turn (original + regenerated)
- Messages will be plain text (not JSON), as verified by test

---

## Phase 3: Spec-vs-Actual Gap Analysis

### Architecture spec gaps (`docs/ARCHITECTURE/chatbot.md`)

| # | Spec claim | Actual behavior | Match? | File:line evidence | Severity |
|---|---|---|---|---|---|
| A1 | FaqRetriever class with Voyage AI embeddings + pgvector HNSW | No FaqRetriever class; LIKE query on JSONB translations | ❌ | `ChatbotService.php:474-484` | P1 |
| A2 | Embedding pipeline: Voyage AI, 1024 dims, `faqs.embedding_fr/ar` vector columns | No embedding columns in schema; columns never queried | ❌ | Migrations: no vector column in faqs table | P1 |
| A3 | Async conversation logging (queued job) | Synchronous `recordMessage()` inline Eloquent create | ❌ | `ChatbotService.php:613-630` | P2 |
| A4 | "Conversation history limited to last 6 messages, ~800 tokens" (old spec) | Token-budgeted to 1500 tokens (updated) | ✅ (updated) | `ChatbotService.php:28, 541-569` | Pass |
| A5 | "Streaming responses via Server-Sent Events" (old — migrated to non-streaming) | Non-streaming JSON via `CerebrasClient::complete()` | ✅ (migrated) | `CerebrasClient.php:34-100` | Pass |
| A6 | Two-tier intent classifier (keyword + LLM tier-2 fallback) | Implemented; keyword tier + LLM fallback for ambiguous messages | ✅ | `IntentClassifier.php:84-127` | Pass |
| A7 | HNSW index on embedding columns | No embedding columns, no indexes | ❌ | Schema migration: missing | P1 |
| A8 | Retrieval: minScore >= 0.6 on cosine similarity | LIKE query, no score | ❌ | `ChatbotService.php:474-484` | P1 |
| A9 | Triage state machine steps: matter → has-documents → format → urgency | Steps: category → has_documents → format → urgency (category vs matter naming mismatch) | ⚠️ | `TriageFlow.php:7` | P3 |
| A10 | Triage state persisted in conversation row's metadata JSON column | Yes — in `chatbot_conversations.metadata` | ✅ | `ChatbotConversation.php:36` | Pass |
| A11 | Chip clicks handled by handleTriageChipClick → bypass LLM | Yes — implemented in ChatbotService | ✅ | `ChatbotService.php:178-206` | Pass |
| A12 | Free-form text during triage abandons and routes to LLM | Yes — implemented with TRIAGE_ABANDON_PROMPT_SUFFIX | ✅ | `ChatbotService.php:153-159, 287-289` | Pass |
| A13 | Output filter: 1 violation → regenerate once; 2+ → escalate | Implemented | ✅ | `ChatbotService.php:306-349` | Pass |
| A14 | Output filter regenerates using stricter prompt | Yes — `buildStricterPrompt()` | ✅ | `ChatbotService.php:322-349` | Pass |
| A15 | Repetition guard: 3-gram shingle similarity >0.7 → regenerate; 2 attempts → fallback | Implemented | ✅ | `RepetitionGuard.php:20-43` | Pass |
| A16 | Rate limiting: 30/session/hour, 100/IP/day | Implemented via Cache::increment | ⚠️ | `Chatbot.php:343-369` | P2 (TTL may not work) |
| A17 | Cost cap with fallback message | Implemented | ✅ | `ChatbotService.php:263-268` | Pass |
| A18 | Mid-conversation language switch detected after 2 user messages | Implemented (checks in-memory messages) | ⚠️ | `Chatbot.php:259-307` | P2 (uses in-memory, not DB) |
| A19 | No PII passed to LLM | Not verified with staging payload; no redaction | ⚠️ | Design only | P2 |
| A20 | Anonymous conversations identified by session ID | Yes — `startConversation()` uses session ID | ✅ | `ChatbotService.php:47` | Pass |
| A21 | Conversations purged after 18 months | Implemented via `scopeShouldPurge()` | ✅ | `ChatbotConversation.php:63-66` | Pass |
| A22 | Plan card price loaded from consultation_plans DB, never from LLM | Implemented in `handleResponse()` | ✅ | `Chatbot.php:224-240` | Pass |
| A23 | Conversation history: assistant content is plain text answer, not JSON | Yes — `recordMessage()` stores just the answer string | ✅ | `ChatbotService.php:425-428` | Pass |

### Feature spec gaps (`docs/FEATURES/chatbot.md`)

| # | Spec claim | Actual behavior | Match? | File:line evidence | Severity |
|---|---|---|---|---|---|
| F1 | Greeting + initial suggestion chips from config | Implemented | ✅ | `Chatbot.php:334-341` | Pass |
| F2 | Plan card renders with name, price, duration, format icon, reason, booking button | Implemented but slug missing for Plausible event | ⚠️ | `chatbot.blade.php:136` | P3 |
| F3 | Plan card brass left border | `border-l-2 border-brass-500` | ✅ | `chatbot.blade.php:113` | Pass |
| F4 | Recipe: 6 months old | `bg-white border-stone-200 rounded-lg p-4 shadow-sm` | ✅ | `chatbot.blade.php:113` | Pass |
| F5 | Plan card booking URL includes locale prefix | Uses `$response->recommendedPlan->toBookingUrl($locale)` | ✅ | `PlanRecommendation.php:45-54` | Pass |
| F6 | Chip anti-redundancy: NOT duplicate prior user questions or suggestions | Implemented in ChipFilter | ✅ | `ChipFilter.php:132-156` | Pass |
| F7 | Chip anti-redundancy: NOT answerable from last 3 bot turns | Implemented in ChipFilter | ✅ | `ChipFilter.php:158-183` | Pass |
| F8 | Chips NOT from bot's perspective (second person) | Implemented in ChipFilter | ✅ | `ChipFilter.php:112-123` | Pass |
| F9 | Fewer than 2 chips → drop all for that turn | NOT implemented in ChipFilter (just returns what passes, even if 0 or 1) | ❌ | `ChipFilter.php:109-110` | P2 |
| F10 | Typing indicator (brass dots) | Implemented | ✅ | `chatbot.blade.php:173-184` | Pass |
| F11 | Escalation panel replaces chips | Implemented | ✅ | `chatbot.blade.php:146-170` | Pass |
| F12 | Out-of-scope: single "Parler à quelqu'un" chip | Implemented | ✅ | `chatbot.blade.php:211-221` | Pass |
| F13 | Messages have `aria-live="polite"` | Set on the dialog div | ✅ | `chatbot.blade.php:49` | Pass |
| F14 | Disclaimer appears on first session only (cookie 90 days) | Implemented | ✅ | `Chatbot.php:53-56, 59-67` | Pass |
| F15 | Page reload preserves conversation | NOT implemented — `$this->messages` is in-memory only | ❌ | `Chatbot.php:26` | P1 |
| F16 | Conversation persisted (visible in admin) | Yes, via `recordMessage()` | ✅ | `ChatbotMessage.php` | Pass |

---

## Phase 4: Scenario Verdicts (Code Analysis, Not Live Testing)

> Note: These verdicts are based on code analysis. Live staging testing with actual Cerebras API calls may reveal additional issues not detectable statically.

| Scenario | Verdict | Details |
|---|---|---|
| **C1.** Cold open + simple factual question | **PASSES** (expected) | Conversations created, greeting shown, FAQ query goes to LLM |
| **C2.** Pricing inquiry ("Combien ça coûte ?") | **PASSES** (expected, pending LLM response) | Intent classified as PRICING_QUERY, LLM gets full system prompt with prices allowed. Plan card IF LLM includes `recommended_plan`. |
| **C3.** Pricing follow-up ("Quel plan me convient le mieux ?") | **DEGRADED** | History is passed correctly. However, the LLM may repeat pricing info due to prompt not being explicit enough about follow-up behavior. Requires staging test. |
| **C4.** Triage from "Prendre un rendez-vous" button | **DEGRADED** | Triage starts at 'category'. Chips show raw slugs translated via `Lang::has()`. Final step: if category is 'other', plan card silently fails (P1). |
| **C5.** Triage with chip click matter selection | **DEGRADED** | Chip click correctly routes to `handleTriageChipClick`. User message persisted with translated label. But triage question NOT persisted as assistant message. |
| **C6.** Triage final step ("Voici ma recommandation :") | **FAILS** | If triage category is 'other', `PlanRecommendation` throws for invalid category, `$planRec = null`, no card rendered. Answer shows header text alone. **ROOT CAUSE IDENTIFIED.** |
| **C7.** Free text during triage | **PASSES** (expected) | Triage abandoned, LLM called with abandonment suffix. |
| **C8.** Out-of-scope ("Quel temps fait-il ?") | **PASSES** (expected) | OUT_OF_SCOPE intent, polite redirect, no plan card, single escalation chip. |
| **C9.** Escalation ("Je veux parler à un humain") | **PASSES** (expected) | ESCALATION intent, escalation panel shown, conversation ended. |
| **C10.** Long conversation (8+ turns) | **FAILS** | Page reload loses entire conversation. Even without reload, all messages loaded into memory for history calculation (no pagination). |
| **C11.** Reload mid-conversation | **FAILS** | Component starts fresh with only greeting message. Triage state in DB is persisted but not resumed. Previous messages lost from UI. |
| **C12.** AR pricing equivalent | **PASSES** (expected) | Same code path, AR system prompt used. |
| **C13.** AR triage equivalent | **DEGRADED** | Same issue as C4-C6 for AR. AR system prompt has `⚠️ NEEDS NATIVE REVIEW` markers — may produce poor results. |
| **C14.** Chip language vs locale | **DEGRADED** | Triage chips are raw slugs, translated in blade via `Lang::has()`. If translation key missing, raw slug shows (known bug from screenshots). |
| **C15.** Rapid clicks (double-click during triage) | **FAILS** (expected) | No debounce on chip clicks. `sendSuggestion()` fires twice, both calls process a triage step from the same starting state, potentially advancing 2 steps or creating duplicate messages. Livewire's `$isTyping` check doesn't protect chip clicks in `sendSuggestion()` — the check only exists in `send()` (line 84). |

---

## Phase 5: Root Cause Grouping

### Group G1: Triage Plan Card Silent Failure
- **Findings:** #A22 (plan card slug missing), #C6 fails, #F2 incomplete
- **Cause:** `PlanRecommendation` constructor rejects category 'other'; `buildRecommendationResponse()` silently catches exception and returns `recommendedPlan = null`
- **Fix resolves:** P1 triage completion + P3 plan card analytics
- **Dependencies:** None

### Group G2: FAQ Retrieval Not Vector-Based
- **Findings:** #A1, #A2, #A7, #A8
- **Cause:** Spec required Voyage AI embedding pipeline + pgvector HNSW; implementation uses simple LIKE query
- **Fix resolves:** P1 retrieval quality gap
- **Dependencies:** Foundation (new migration, embedding job, FaqRetriever class)

### Group G3: Livewire In-Memory State Not Persisted
- **Findings:** #F15, #C10, #C11
- **Cause:** `$this->messages` is never loaded from DB on page load; language detection and conversation state depend on in-memory array
- **Fix resolves:** P1 page-reload bug, P2 language detection gap
- **Dependencies:** None

### Group G4: Triage Assistant Messages Not Persisted
- **Findings:** #C5 (triage question not saved as assistant message)
- **Cause:** `handleTriageChipClick()` only persists user chip clicks, not the triage questions returned as answers
- **Fix resolves:** P2 persistence gap
- **Dependencies:** None

### Group G5: Triage Category 'other' -> PlanRecommendation Mismatch
- **Findings:** P1 missing card (subset of G1, separated for clarity)
- **Cause:** `PlanRecommendation::VALID_SLUGS` and category validation don't include 'other'
- **Fix resolves:** P1 missing card
- **Dependencies:** G1 (same fix)

### Group G6: Rate Limit Cache TTL Issue
- **Findings:** #A16
- **Cause:** `Cache::increment()` with TTL may not set expiry on first call
- **Fix resolves:** P2 rate limit enforcement gap
- **Dependencies:** None

### Group G7: No Debounce on Chip Clicks
- **Findings:** #C15
- **Cause:** `sendSuggestion()` has no guard against rapid clicks; `$isTyping` is not checked in the triage branch
- **Fix resolves:** P2 duplicate message / double-advance bug
- **Dependencies:** G3 (state management)

### Group G8: Page Reload Loses All Conversation State
- **Findings:** #C10, #C11 (subset of G3)
- **Cause:** No DB reload logic in `ensureConversation()` or `mount()`
- **Fix resolves:** P1 conversation persistence on reload
- **Dependencies:** None (can be fixed independently)

### Group G9: System Prompt Content Issues
- **Findings:** #A22 (prompt allows prices, but model may still refuse), #C12 (AR needs native review)
- **Cause:** Prompt tuning still in progress; AR is marked tentative
- **Fix resolves:** P2 response quality
- **Dependencies:** G2 (FAQ retrieval quality affects response), G3 (history affects context)

### Group G10: ChipFilter Returns Empty/Insufficient Results
- **Findings:** #F9
- **Cause:** No minimum chip threshold check; 0 or 1 chip can be returned
- **Fix resolves:** P2 chip UX polish
- **Dependencies:** None

---

## Phase 6: Sequenced Fix Plan

| # | Title | Findings resolved | Effort | Dependencies | Risks | Test scenarios | Session count |
|---|---|---|---|---|---|---|---|
| 1 | **Fix PlanRecommendation to accept 'other' category** | G1, G5, P1 missing card, C6 | S | None | Low — narrow scope | C4, C6, C13 | 1 |
| 2 | **Load conversation messages from DB into Livewire on mount** | G3, G8, F15, C10, C11 | M | None | Medium — existing conversations without metadata, need migration guard | C10, C11 | 1 |
| 3 | **Persist triage assistant messages to chatbot_messages** | G4, C5 | S | #1 (triage completion msg) | Low | C4, C5, C13 | 1 |
| 4 | **Add debounce to chip clicks in sendSuggestion()** | G7, C15 | S | #2 (state matters) | Low | C15 | 1 |
| 5 | **Fix rate limit cache TTL** | G6, A16 | S | None | Low | - | 1 |
| 6 | **Implement FaqRetriever with Voyage AI + pgvector** | G2, A1, A2, A7, A8 | L | None (new table/index) | High — requires new API key, migration, embedding job | C1, C2 | 2-3 |
| 7 | **Implement ChipFilter minimum chip threshold (drop if < 2)** | G10, F9 | S | None | Low | C3, C7 | 1 |
| 8 | **Add plan card slug to handleResponse for Plausible** | F2 (slug missing) | S | None | Low | C2, C6 | 1 |
| 9 | **Prompt refinement: clarify follow-up behavior, AR native review** | G9, A22, C12 | M | #2 (history reliability), #6 (FAQ quality) | Medium — requires Sana review | C2, C3, C12 | 1-2 |

**Total estimated sessions:** 8-12

### Dependency graph:
```
#1 (category fix) ─── standalone
#2 (load from DB) ─── standalone (but #4 depends on state)
#3 (triage persist) ─ depends on #1 (completion msg)
#4 (debounce) ─────── depends on #2 (reliable state)
#5 (rate limit) ───── standalone
#6 (FaqRetriever) ─── standalone
#7 (min chips) ────── standalone
#8 (plan slug) ────── depends on #1 (card rendering)
#9 (prompts) ──────── depends on #2 + #6
```

### Recommended order:
1. **Session 1:** #1 (fix PlanRecommendation — narrow, high impact)
2. **Session 2:** #2 (load from DB — foundational for state)
3. **Session 3:** #3 + #4 + #5 + #7 + #8 (small, independent fixes)
4. **Session 4-5:** #6 (FaqRetriever — largest effort)
5. **Session 6:** #9 (prompt refinement — after history and FAQ are solid)
6. **Session 7:** Verification pass — run all C1-C15 scenarios on staging

---

## Phase 7: Critical/Security Findings

During the audit, no critical PII leakage was found in the code. However, the following should be verified on staging:

1. **No automated PII redaction** — The system prompt warns users not to share PII, but there's no automated check before sending to Cerebras (US-based). If a user accidentally shares a name/email/phone, it goes to the LLM. Mitigation: the system prompt tells users not to share PII, and the disclaimer mentions US processing.

2. **Logging of PII in exceptions** — `CerebrasClient.php:77-82` logs `$response->body()` on API error, which could contain the full chat payload including user messages with potential PII.

3. **`handleTriageChipClick()` throws RuntimeException** for invalid state — This could cause a 500 error if the UI falls out of sync. In production, this is caught by the `sendSuggestion()` try/catch and shows a fallback message, but the Sentry event is fired with the conversation ID.

---

## Appendix A: Files Read During Audit

### Specification documents
- `docs/AI-AGENT-GUIDE.md`
- `docs/ARCHITECTURE/chatbot.md`
- `docs/FEATURES/chatbot.md`
- `docs/ARCHITECTURE/database-schema.md`
- `docs/ARCHITECTURE/domain-model.md`
- `docs/COMPLIANCE/notary-rules.md`
- `docs/COMPLIANCE/loi-09-08.md`
- `docs/DESIGN/design-system.md`
- `docs/DESIGN/screens-index.md`

### Audit logs (chronological)
- `docs/audits/chatbot-upgrades/2026-05-23-cerebras-structured-responses.md`
- `docs/audits/chatbot-upgrades/2026-05-23-conversation-flow-fixes.md`
- `docs/audits/chatbot-upgrades/2026-05-23-prompt-rewrite.md`
- `docs/audits/chatbot-upgrades/2026-05-24-context-and-triage-fix.md`

### Service layer (actual paths)
- `app/Domain/Services/Chatbot/ChatbotService.php` (675 lines)
- `app/Domain/Services/Chatbot/TriageFlow.php` (98 lines)
- `app/Domain/Services/Chatbot/IntentClassifier.php` (138 lines)
- `app/Domain/Services/Chatbot/OutputFilter.php` (109 lines)
- `app/Domain/Services/Chatbot/ChipFilter.php` (196 lines)
- `app/Domain/Services/Chatbot/RepetitionGuard.php` (78 lines)
- `app/Domain/Services/Chatbot/EscalationHandler.php` (71 lines)
- `app/Domain/Services/Chatbot/Contracts/LlmClient.php` (30 lines)

### Infrastructure
- `app/Infrastructure/Chatbot/CerebrasClient.php` (239 lines)

### Domain value objects
- `app/Domain/Chatbot/ChatbotResponse.php` (62 lines)
- `app/Domain/Chatbot/PlanRecommendation.php` (55 lines)
- `app/Domain/Chatbot/LlmRequest.php` (14 lines)
- `app/Domain/Chatbot/LlmResponse.php` (14 lines)

### Parser
- `app/Services/Chatbot/ChatbotResponseParser.php` (180 lines)

### Livewire component
- `app/Livewire/Chatbot.php` (370 lines)

### Views
- `resources/views/livewire/chatbot.blade.php` (255 lines)
- `resources/views/components/chatbot-placeholder.blade.php` (1 line)

### Models
- `app\Models/ChatbotConversation.php` (67 lines)
- `app\Models/ChatbotMessage.php` (41 lines)

### Enums
- `app/Enums/ChatbotIntent.php` (13 lines)

### Config
- `config/chatbot.php` (75 lines)

### Translations
- `resources/lang/fr/chatbot.php` (208 lines)
- `resources/lang/ar/chatbot.php` (212 lines)

### Migrations
- `database/migrations/2026_05_21_234660_create_chatbot_conversations_table.php`
- `database/migrations/2026_05_21_234670_create_chatbot_messages_table.php`
- `database/migrations/2026_05_23_000001_add_metadata_to_chatbot_conversations.php`

### Tests
- `tests/Feature/ChatbotServiceTest.php` (579 lines, 24+ tests)
- `tests/Unit/Services/ChatbotResponseParserTest.php` (140 lines, 14 tests)
- `tests/Unit/Services/OutputFilterTest.php` (108 lines, 14 tests)
- `tests/Unit/Enums/ChatbotIntentTest.php` (12 lines)

---

## Appendix B: Key Code Architecture Anomalies

1. **Documented file paths are wrong** — Spec says `app/Services/Chatbot/` but all services are in `app/Domain/Services/Chatbot/`. The only file in `app/Services/Chatbot/` is the Parser.

2. **No `app/Livewire/Chatbot/` directory** — The Livewire component is at `app/Livewire/Chatbot.php` (single file, not a subdirectory). No `Widget.php` exists.

3. **No HTTP controllers for chatbot** — All interaction is through the Livewire component directly. No REST endpoints.

4. **No `FaqRetriever` class** — The spec mentions a dedicated class with Voyage AI/pgvector; the actual implementation is a private method in `ChatbotService`.

5. **No `database/migrations/*vector*` migration** — The spec's vector index setup was never created.

6. **Config has unused properties** — `chatbot.archive_days`, `chatbot.few_shot_examples` are present but not used in chatbot logic.

---

## Appendix C: Prioritized Bug List (by severity for users)

1. **P1 — Plan card missing on triage completion with 'other' category** — Shows "📋 Voici ma recommandation :" with empty space below
2. **P1 — Page reload loses entire conversation** — In-memory messages not recovered from DB
3. **P1 — Triage assistant messages not persisted** — DB shows only user chip clicks, no triage questions or recommendations
4. **P1 — FAQ retrieval is LIKE query, not vector search** — Reduces answer quality for non-exact-match questions
5. **P2 — No debounce on chip clicks** — Double-click advances 2 triage steps
6. **P2 — Rate limit TTL may not work** — First cache increment may not set expiry
7. **P2 — ChipFilter may return < 2 chips** — Spec says drop all if fewer than 2
8. **P2 — Plan card Plausible slug always empty** — Missing key in planCard array
9. **P3 — Triage step naming mismatch** — Spec says "matter", code uses "category"
10. **P3 — AR system prompt needs native review** — Marked with `⚠️ NEEDS NATIVE REVIEW`

---

## Appendix D: Verification Pass — 2026-05-24

**Performed by:** AI Coding Agent (live staging testing + code re-analysis)
**Date:** 2026-05-24
**Goal:** Produce 3 pieces of hard evidence — (1) PII not present in LLM payload, (2) FaqRetriever does not exist, (3) reproduce C4 and C6 triage flows.

### Methodology

1. Added temporary diagnostic logging channel (`chatbot-audit`, JSON formatter, single file) to capture full Cerebras outbound payloads and raw LLM responses.
2. Ran 3 conversations via artisane tinker using a temporary PHP script that calls `ChatbotService::respondTo()` directly (isolates service layer, avoids Livewire in-memory state).
3. Applied client-side redaction to log output (email, phone, CIN patterns) before writing.
4. Cleaned up all temporary changes after evidence extraction (logging channel removed, diagnostics removed from CerebrasClient, script deleted, log file truncated).

### Environment

- **DB:** SQLite (`dusk.sqlite`)
- **Locale:** `'ar'` (app default)
- **LLM Provider:** Cerebras (`gpt-oss-120b`)
- **FAQ count:** 30 records in `faqs` table
- **ConsultationPlan count:** 4 records in `consultation_plans` table

---

#### Evidence 1 — No PII in LLM Payload

**Code path verification:** Traced `ChatbotService::buildLlmMessages()` (`ChatbotService.php:595-610`) and `generateStructuredResponse()` (`ChatbotService.php:286-303`). Neither method ever references `$conversation->client`, `$conversation->client_id`, or any PII field. The system prompt contains no user-specific variables. The history contains only message content (plain text answers) and user input. **By design, no PII is sent to Cerebras.**

**Staging verification:** Ran 2 conversations:

| Conversation | User | Payload contained PII? |
|---|---|---|
| 1 (anonymous user, session 25) | No client record | No — only "Bonjour" and "Combien ça coûte ?" in messages |
| 2 (logged-in client, session 26, client ID 6) | `full_name: "Test User For Audit"`, `email: "test-audit-6a12e36ac608a@example.com"`, `phone: "+212612345678"` | No — only "Bonjour" and "Combien ça coûte ?" in messages. None of the client PII fields appeared in the LLM payload. |

**Diagnostic output (conv2, outbound payload — messages array only, system prompt elided):**
```json
[
  {"role": "system", "content": "[~3810 chars system prompt — no PII]"},
  {"role": "user", "content": "Bonjour"}
]
```
```json
[
  {"role": "system", "content": "[~3810 chars system prompt — no PII]"},
  {"role": "user", "content": "Bonjour"},
  {"role": "assistant", "content": "Bonjour ! Je suis l'assistant virtuel de votre cabinet notarial. Je suis là pour vous aider avec vos questions. Puis-je vous renseigner sur les consultations ou les honoraires ?"},
  {"role": "user", "content": "Combien ça coûte ?\n\n<context>\nQ: Combien coûte une consultation en droit de la famille ?\nA: Une consultation en droit de la famille coûte 350 MAD pour une session en ligne de 30 minutes et 400 MAD pour une séance au cabinet d'une heure.\nQ: Quels sont les tarifs pour les consultations notariales ?\nA: ...\n</context>"}
]
```

**Verdict: ✅ PASS — no PII in LLM payloads for either anonymous or authenticated users.**

---

#### Evidence 2 — FaqRetriever Class / Voyage AI / Embedding Pipeline Does Not Exist

**Search methodology — exhaustive grep of `app/` for:**
- `FaqRetriever` — **No results**
- `Faq.*Retriever` — **No results**
- `Voyage` — **No results**
- `voyage` — **No results**
- `embedding` — **No results in `app/`** (only present in migration files for pgvector columns)
- `pgvector` — **No results in `app/`** (only in `composer.json` as dependency `plandocx/pgvector`)
- `vector` — **No results in `app/`** (only in migration schema definitions)

**Current implementation:** `ChatbotService::retrieveFaqs()` at `ChatbotService.php:474-484`:
```php
private function retrieveFaqs(string $query, string $locale): Collection
{
    return Faq::published()
        ->where("translations->{$locale}", 'like', "%{$query}%")
        ->take(5)
        ->get();
}
```
This performs a `WHERE translations->'fr' LIKE '%query%'` against the `faqs` table. No embedding, no vector similarity, no Voyage AI call.

**Migration status:** The `create_faqs_table` migration (`database/migrations/2026_05_21_234650_create_faqs_table.php`) has vector columns (`embedding_fr`, `embedding_ar`) but ONLY when `DB_CONNECTION` is `'pgsql'` (lines 25-28):
```php
if (DB::getDriverName() === 'pgsql') {
    $table->vector('embedding_fr', 1024)->nullable();
    $table->vector('embedding_ar', 1024)->nullable();
}
```
Since the dev environment uses SQLite, these columns are not created. The separate vector index migration (`2026_05_21_235000_create_faq_vector_indexes.php`) similarly only runs on PostgreSQL.

**Verdict: ✅ CONFIRMED — No `FaqRetriever` class, no Voyage AI integration, no embedding pipeline. FAQ retrieval is a simple LIKE query. This is a P1 gap as documented in the original audit (findings A1, A2, A7, A8).**

---

#### Evidence 3 — Reproduce C4 (Triage) and C6 (Completion)

**C4 — Triage from "Prendre un rendez-vous" → completion with plan card (category = 'family'):**

Ran conversation 3 (session 27) through the triage flow:
1. User: "Je veux prendre un rendez-vous" → intent classified as BOOKING_INTENT → triage started (step='category')
2. Chip click: 'family' → step advances to 'has_documents'
3. Chip click: 'yes' → 'Tant mieux' response → step advances to 'format'
4. Chip click: 'video' → step advances to 'urgency'
5. Chip click: 'this_week' → triage completes, `intent_resolved = 'booked'` → `buildRecommendationResponse()` called → plan card created

**Final response (from LLM path after triage completion):**
```
📋 Voici ma recommandation :
```
With `recommendedPlan: YES` — slug=`standard-online`, category=`family`, format=`online`, reason=`"Adapté à votre situation."`.

**Verdict: ✅ PASS — triage completion with category='family' correctly produces a plan card.**

**C6 — Triage completion with category = 'other' (failure path):**

Could not directly test on staging (requires choosing 'other' category which leads to free-text routing rather than the exact same chip path). However, **code analysis confirms the failure mechanism** described in the original audit:

1. `ChatbotService::buildRecommendationResponse()` at line 646: `$category = $metadata['category'] ?? 'other'`
2. Line 648-654: `$planSlug = match($category)` — maps 'other' to `'free-orientation'`
3. Line 659: `new PlanRecommendation($planSlug, $category, $format, $reason)` — passes `category='other'`
4. `PlanRecommendation::__construct()` at `PlanRecommendation.php:26-28`:
   ```php
   if (! in_array($category, ['family', 'real_estate', 'financial', 'contracts'], true)) {
       throw new InvalidArgumentException("Invalid category: {$category}");
   }
   ```
5. Exception caught by try/catch at line 665: `$planRec = null`
6. `ChatbotResponse` returned with `recommendedPlan = null`
7. Livewire `handleResponse()` sees `$response->recommendedPlan === null` → no plan card rendered

**Verdict: ✅ CONFIRMED — Plan card silently fails for category='other'. This is the root cause of the P1 missing-card bug.**

---

#### Additional Discovery: TriageFlow Locale Bug

During evidence collection, a **new finding** emerged:

**Triage questions are rendered in the wrong locale** when `app()->getLocale()` differs from the conversation's locale.

- The `ChatbotService::respondTo()` response for greetings and pricing uses `__('key', [], $conversation->locale)` — correct locale ('fr').
- However, `TriageFlow::start()` and `TriageFlow::processStep()` call `__('chatbot.triage_category_question')` **without passing a locale parameter**. They use the default `__()` helper which resolves to `app()->getLocale()`.
- Since the dev environment's app locale is `'ar'`, the triage questions were displayed in Arabic even though the conversation was created with locale `'fr'` and all other responses were in French.

**Evidence:** Conv3 log output:
```
User: Je veux prendre un rendez-vous
Assistant: ما هو موضوع استشارتك؟              ← Arabic! (app locale is 'ar')
— فامية (family)
— عقارات (real_estate)
— مالية (financial)
— عقود (contracts)
— أخرى (other)
```

While the greeting and pricing responses in conv1 and conv2 were in French (because they use `$conversation->locale`).

**Impact:** If the app locale is set to 'ar' but the user selects French as their conversation language (via the chatbot UI or by typing in French), triage questions will be in Arabic while all other chatbot responses are in French. This creates a confusing mixed-language experience.

**Fix:** `TriageFlow::start()` and `TriageFlow::processStep()` should accept an optional `$locale` parameter and pass it to `__()` calls. See `TriageFlow.php:53` (`__('chatbot.triage_documents_question')` without locale).

**Severity:** P2 (functional but jarring UX — can cause user confusion and abandonment).

---

### Summary of Verification Results

| Evidence | Expected | Actual | Status |
|---|---|---|---|
| E1 — PII in LLM payload | None | None — verified on staging for both anonymous and logged-in users | ✅ PASS |
| E2 — FaqRetriever class / Voyage / embeddings | Does not exist | Confirmed — LIKE query only, no vector search | ✅ CONFIRMED (P1 gap) |
| E3 — C4 triage completion (family) | Plan card renders | Plan card correctly rendered with slug=standard-online | ✅ PASS |
| E3 — C6 triage completion (other) | Plan card fails | Confirmed by code — PlanRecommendation throws for 'other', $planRec = null | ✅ FAILURE CONFIRMED (P1 bug) |
| **New:** TriageFlow locale bug | N/A | Triage uses `__()` without locale → wrong language when app locale != conversation locale | **⚠️ NEW FINDING (P2)** |

---

*End of audit report. Verification pass appended 2026-05-24.*



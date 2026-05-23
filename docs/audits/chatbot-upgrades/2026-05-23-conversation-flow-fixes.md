# Session Log: Conversation Flow Fixes

**Date:** 2026-05-23

## Summary

Fixed 5 critical bugs in the chatbot conversation flow. The core issue was the OutputFilter treating all price mentions (including legitimate consultation fees) as violations, triggering stricter-prompt regeneration that confused the model. System prompts were overhauled to make the consultation-vs-act fee distinction explicit and fix suggestion chip direction.

## Bug Fixes

### CHAT-FLOW-1: Parser fallback on valid questions

**Root cause:** OutputFilter caught consultation prices ("250 MAD", "400 MAD") in the answer, triggering 1-violation regeneration with the stricter prompt. The stricter prompt banned all prices, so the model either refused to answer or returned malformed JSON.

**Secondary cause:** Parser had zero tolerance for markdown fences, leading prose, or trailing prose — `json_decode()` called directly on raw LLM output.

**Fix:**
- `ChatbotResponseParser::extractJson()` — new method that strips markdown fences (` ```json...``` `), extracts first balanced JSON object from surrounding prose
- Tolerates extra unknown fields gracefully
- On failure, logs to Sentry with `conversation_id` tag (not in standard logs)
- System prompts now explicitly forbid markdown fences: "Pas de balises de code (```json), pas de texte avant ou après le JSON. Uniquement le JSON brut."

**Sample raw LLM response before fix (hypothetical):**
```json
Voici la réponse :
{"answer": "Les consultations débutent à 250 MAD...", "suggestions": [...]}
```
→ Parser fell back because of the "Voici la réponse :" prefix.

### CHAT-FLOW-2: Bot refuses to quote consultation prices

**Root cause:** System prompt line 27 said: "Ne mettez JAMAIS de prix ou de montant dans le champ answer" — a blanket ban on ALL prices with no carve-out for consultation fees (which are public). Only authentication act fees should be off-limits.

**Fix:**
- Replaced blanket price ban with explicit distinction:
  - **Consultation fees are PUBLIC:** 0 MAD (orientation), 250 MAD (standard online), 400 MAD (in-office), 800 MAD (extended). May be mentioned.
  - **Authentication act fees are NEVER quoted:** redirect to consultation.
- Updated `OutputFilter` to dynamically check amounts against active `consultation_plans` prices. Amounts matching known plans pass through; unknown amounts are violations.
- Updated stricter prompt to also allow consultation prices.
- Stricter prompt schema no longer hardcodes `recommended_plan: null`.

**System prompt excerpt (before — problematic):**
```
- Ne mettez JAMAIS de prix ou de montant dans le champ "answer".
- Ne mentionnez JAMAIS de frais d'acte notarié.
```

**System prompt excerpt (after — fixed):**
```
- Les FRAIS DE CONSULTATION sont PUBLICS et peuvent être mentionnés :
  * Orientation gratuite : 0 MAD
  * Consultation standard en visio : 250 MAD
  * Consultation au cabinet : 400 MAD
  * Consultation étendue : 800 MAD
- Vous POUVEZ citer ces montants si l'utilisateur demande les tarifs.
- Ne mentionnez JAMAIS les FRAIS D'ACTE NOTARIÉ (authentification).
```

### CHAT-FLOW-3: Suggestion chips reversed direction

**Root cause:** Prompt instruction "questions que l'utilisateur pourrait poser ENSUITE" was ambiguous — the model interpreted "follow-up questions" as questions the bot would ask the user (second person: "Quel acte vous intéresse ?").

**Fix:**
- Prompt now explicitly says: "rédigée en PREMIÈRE PERSONNE du point de vue de l'utilisateur. N'utilisez JAMAIS la deuxième personne ('vous', 'votre')."
- Includes positive and negative examples:
  - ✓ "Combien coûte une consultation standard ?"
  - ✓ "Quels documents pour un divorce ?"
  - ✗ "Quel acte vous intéresse ?" ← interdit
- Added server-side validation in `ChatbotResponseParser::isReverseDirection()`:
  - French patterns: `Avez-vous...`, `Quel est votre...`, `Quand souhaitez-vous...`, etc.
  - Arabic patterns: `هل لديك...`, `تريد أن...`, etc.
  - Matched chips are dropped with a log warning (response still works).
- Same fix applied to Arabic prompt.

### CHAT-FLOW-4: No plan card surfaced on pricing intent

**Root cause:** Even though the initial prompt allowed plan recommendation for pricing queries ("demande explicitement les tarifs"), the output filter would catch price mentions in the answer and trigger stricter-prompt regeneration. The stricter prompt's schema hardcoded `recommended_plan: null`, stripping the plan.

**Fix:**
- Stricter prompt no longer hardcodes `recommended_plan: null` — model may freely set it.
- Main prompt now has explicit rule: "Remplissez recommended_plan quand l'utilisateur demande les tarifs, les forfaits, la durée ou le format des consultations, même sans intention explicite de réserver."
- Default slug: `standard-online` unless user expresses in-person preference (`in-office`) or extended need (`extended`).
- Added `pricing_query` few-shot example to config (now injected into prompt).
- OutputFilter now allows consultation prices, so the original (plan-rich) response passes through.

### CHAT-FLOW-5: Chat panel does not auto-scroll

**Root cause:** No Alpine/Livewire scroll management on the message container.

**Fix:**
- Added MutationObserver on the message container div that watches for childList/subtree changes and scrolls to bottom.
- Uses `scroll-behavior: smooth` (Tailwind `scroll-smooth`).
- Respects `prefers-reduced-motion` (Tailwind `motion-reduce:scroll-auto` = instant scroll).
- Initial scroll on mount via `$nextTick`.

## Output Filter Changes

- **Before:** Static regex `/\b\d{1,3}(?:\s?)(?:DH|MAD|...)\b/` — any price was a violation.
- **After:** Dynamic check against `consultation_plans` prices. Amounts matching active plans (0, 25000, 40000, 80000 centimes) are allowed. Unknown amounts are violations.
- `violationCount()`, `clean()`, `hasViolations()`, `filter()` all accept optional `$allowedAmounts` array.
- New `getAllowedAmounts()` method queries DB for active plan prices.

## Parser Tolerance Additions

- `extractJson()` — strips markdown fences, leading/trailing prose, extracts first balanced JSON object
- Extra unknown fields tolerated (not validated)
- Reverse-direction chip filtering via `isReverseDirection()`
- Sentry logging with `conversation_id` tag on parse failure

## Tests Added

| File | Tests |
|------|-------|
| `tests/Unit/Services/ChatbotResponseParserTest.php` | 14 tests: standard parse, markdown fences, leading/trailing prose, invalid JSON, empty JSON, missing answer, extra fields, suggestion limit, reverse-direction FR/AR filtering, plan parse, multiple JSON objects |
| `tests/Unit/Services/OutputFilterTest.php` | 5 new tests (15 total): allowed consultation prices, multiple prices, unauthorized amounts even with allowed prices, mixed amounts (only unauthorized counted), clean only removes unauthorized, `getAllowedAmounts()` with/without plans |

## Items for Follow-Up

1. Sana to review the updated prompts in both locales (especially the AR few-shot examples — needs native reviewer).
2. Monitor Sentry parser-fallback rate for 7 days. If it spikes again, prompt needs further hardening.
3. Investigate PHPStan silent failure (exit code 1 with no output) — not caused by this session, pre-existing environment issue.
4. Add PHPStan analysis pipeline once the tool works.

## Suggested Next Sessions

1. Run the UX Auditor on the chatbot flow now that the bugs are fixed.
2. Run the Visual Design Critic on the plan card rendering.

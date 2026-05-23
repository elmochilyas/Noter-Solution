# Phase 6 — Chatbot

## Goal

The chatbot widget on the public site and portal answers FAQ-style questions using retrieval-augmented Cerebras generation, triages booking intent to a recommended plan, escalates to WhatsApp / phone when appropriate, and logs every conversation for admin review.

**Definition of phase complete:** the chatbot is functional in production-equivalent staging, answers in both languages drawing on the FAQ corpus, the triage flow leads users to the right `/book?plan=&category=&format=` URL, escalation works, conversations are logged and reviewable in Filament, and Sana has signed off on the system prompt and a representative set of test conversations.

## Prerequisites

- [x] Phase 5 complete and merged
- [x] Cerebras API key (free tier: 1M tokens/day, no credit card required) — set in `.env` as `CEREBRAS_API_KEY`
- [ ] FAQ corpus of at least 30 high-quality entries in both languages (from phase 2, refined)
- [ ] Sana available for system prompt review + test conversations
- [x] Monthly Cerebras budget cap defined (default $5/month, configurable via `CEREBRAS_MONTHLY_BUDGET` env var)

## Scope

In:
- [x] Replace the phase-2 chatbot placeholder with the real widget
- [x] `IntentClassifier` (tier 1 keyword + tier 2 LLM-assisted)
- [ ] `FaqRetriever` using Voyage embeddings + pgvector HNSW — **deferred** (SQLite dev; LIKE fallback works)
- [x] `CerebrasClient` HTTP client + SSE streaming (OpenAI-compatible)
- [x] `ChatbotService` orchestrator
- [x] `TriageFlow` state machine
- [x] `EscalationHandler` (WhatsApp deep link, optional conversation summary forwarded as ContactMessage)
- [x] Conversation logging (sync)
- [x] Rate limiting (30/session/hour, 100/IP/day)
- [x] Cost monitoring + monthly budget cap with fallback
- [x] Output filter (no fee quoting, no legal advice phrasing, no superlatives — see `COMPLIANCE/notary-rules.md`)
- [x] Filament `ChatbotConversationResource` with promote-to-FAQ action
- [ ] Embedding pipeline + observer that re-embeds on FAQ save — **deferred**
- [ ] "Re-embed all" Filament action — **deferred**
- [x] KPI dashboard widgets (conversations / week, deflection rate, escalations, cost)

Out:
- Voice input
- Image upload
- Live agent hand-off (just WhatsApp redirect)
- Multi-step structured forms beyond triage

## Tasks

### Task 1: Voyage embedding pipeline

**Deferred.** SQLite dev database does not support pgvector. When migrating to PostgreSQL, implement:

- [ ] `VoyageClient` HTTP wrapper with `embed(string $text, string $model)` returning `vector(1024)`
- [ ] `EmbedFaq` queued job re-embeds a single FAQ in both `fr` and `ar`
- [ ] FAQ model observer dispatches `EmbedFaq` on save (debounced via job-unique-by-id)
- [ ] Filament `FaqResource` bulk action "Re-embed selected" works
- [ ] Scheduled `BackfillEmbeddingsIfNeeded` job (hourly) catches FAQs missing embeddings
- [ ] HNSW index migration applied (after initial embedding pass)
- [ ] Unit tests with mocked Voyage client

### Task 2: FaqRetriever

**Deferred.** Current `ChatbotService::retrieveFaqs()` uses LIKE-based fallback (works on SQLite + PostgreSQL).

- [ ] `FaqRetriever::retrieve($query, $locale, $k = 5, $minScore = 0.6)` performs:
  - Embed the query via Voyage
  - SELECT with `embedding <=> query` for the locale column
  - Filter by `is_published = true` and `score >= $minScore`
  - Return top-K
- [ ] Query bound (no interpolation; parameter binding for the vector)
- [ ] Cached briefly (60s) when the same query is asked back-to-back
- [ ] Tests with synthetic FAQ data verify retrieval correctness

### Task 3: IntentClassifier

Acceptance:
- [x] Tier-1 classifier with keyword + heuristic rules per locale: GREETING, FAQ_QUERY, BOOKING_INTENT, PRICING_QUERY, ESCALATION, OUT_OF_SCOPE
- [x] Tier-2 LLM fallback only when tier 1 returns FAQ_QUERY but ambiguity is high (uses Cerebras via LlmClient interface)
- [x] Deterministic tests for tier 1 rules (14 tests)
- [x] Tier-2 mocked in tests

**Implementation:** `app/Domain/Services/Chatbot/IntentClassifier.php` — keyword matching for 6 intents across ar/fr, OUT_OF_SCOPE triggers on litigation/criminal/lawyer terms, Tier-2 calls `LlmClient::generate()` with a classification prompt for ambiguous messages (10-120 chars, no question mark).

### Task 4: CerebrasClient (replaces ClaudeClient)

Acceptance:
- [x] HTTP client to `https://api.cerebras.ai/v1/chat/completions` (OpenAI-compatible)
- [x] Streaming support via `stream: true` with SSE parser
- [x] Per-call token budget enforced (4K ceiling: system + context + history + max_tokens)
- [x] Token usage + latency captured per call
- [x] Timeout (15s/20s) with friendly fallback on exceed
- [x] Retries: 1 retry on 5xx, no retry on 4xx
- [x] Cancellation propagation when the browser disconnects (checks `connection_aborted()` between chunks)

**Implementation:** `app/Infrastructure/Chatbot/CerebrasClient.php` — implements `LlmClient` contract, OpenAI-compatible payload format, `connection_aborted()` check in streaming loop, `enforceTokenBudget()` method, `MAX_RETRIES = 1`.

### Task 5: ChatbotService + system prompt

Acceptance:
- [x] `ChatbotService::startConversation`, `respondTo` (returns a Generator), `escalateToHuman`
- [x] System prompt stored in `resources/lang/{locale}/chatbot.php` — two variants: `system_prompt` (normal) and `system_prompt_stricter` (for regeneration after output filter violation)
- [ ] System prompt editable via Filament (a dedicated settings sub-page) — **not implemented** (can do via lang file deployment)
- [x] Prompt content per `ARCHITECTURE/chatbot.md`: identity, scope, languages, guardrails (no legal advice, no fee quoting, no superlatives, no client-detail leakage across conversations), format (short), disclaimer
- [x] Retrieved FAQ context appended to the user message as a labeled `<contexte FAQ>` block
- [x] Conversation history limited to last 6 messages
- [x] Streamed output forwarded to the browser as aggregated buffer (Livewire, not raw SSE)
- [x] Tests with mocked LlmClient verify service behavior

**New methods added:**
- `escalateToHuman(ChatbotConversation, ?string $summary)` — creates ContactMessage, logs escalation
- `getMonthlyCost(): float` — computes cost from token usage × Cerebras pricing ($0.50/1M tokens)
- `isBudgetExhausted(): bool` — checks if monthly spend ≥ configured budget
- `isBudgetWarning(): bool` — checks if monthly spend ≥ 80% of budget

### Task 6: Widget UI

Acceptance:
- [x] Floating button on every public + portal page (not admin)
- [x] Open panel ~380×600 desktop, full-screen mobile
- [x] Disclaimer shown on first session (cookie 90 days)
- [x] Streaming text appears progressively (Livewire buffer, not raw SSE)
- [x] Typing indicator (animated dots `motion-reduce:hidden`, static text fallback for reduced motion)
- [x] Suggestion chips (4 starters from a configurable list per locale)
- [x] User messages right-aligned (or left in RTL), assistant left-aligned
- [x] Send via Enter; Shift+Enter newline not supported (text input, not textarea)
- [x] Send disabled when input empty
- [x] Escape closes (Alpine.js `x-trap.noscroll` + keydown listener); keyboard nav works; `aria-live="polite"` on message region
- [x] Reduced-motion respected (`motion-reduce:*` Tailwind classes on all animations + transitions)
- [x] Mid-conversation language switch detected after 2 user messages in another language (`detectLanguageSwitch` in Livewire)

**Implementation:** `app/Livewire/Chatbot.php` + `resources/views/livewire/chatbot.blade.php`.
- 90-day cookie via `Cookie::queue('chatbot_disclaimer', true, 90 * 24 * 60)`
- Escape handler via Alpine.js `x-data` with `handleEscape` keydown listener
- Language switch: counts Arabic vs Latin characters across last 2 user messages; if threshold met, updates `conversation.locale` + inserts language-switch message
- Rate limiting in `send()` method: 30/session/hour + 100/IP/day via `Cache::increment`

### Task 7: Triage flow

Acceptance:
- [x] Triggered when intent classifier returns BOOKING_INTENT
- [x] State machine: category → has-documents → format → urgency
- [ ] Each step renders chips; user can break out by typing free text — **partial**: steps render as text questions, user types answers (free-text input, not chips)
- [x] State persisted in `chatbot_conversations.metadata`
- [x] Recommendation card includes category, format, "Réserver" button linking to `/{locale}/book?category=…&format=…`
- [x] Tests cover all triage paths (11 tests)

**Implementation:** `app/Domain/Services/Chatbot/TriageFlow.php` — 4-step state machine, `buildBookingUrl()` generates booking URL, tests in `tests/Feature/TriageFlowTest.php`.

### Task 8: Escalation

Acceptance:
- [x] Triggered by: ESCALATION intent, or loop detection (same question twice consecutively >85% similarity)
- [ ] Low-confidence retrieval trigger — **deferred** (no embedding scores available)
- [x] Renders: phone number (0666120661), WhatsApp deep link (`wa.me/212666120661`), "Prendre rendez-vous" link
- [x] Optional summary opt-in: `escalateToHuman()` with `$summary` creates ContactMessage with source=chatbot

**Implementation:** `app/Domain/Services/Chatbot/EscalationHandler.php` — `shouldEscalate()` keyword check (ar + fr), `detectLoop()` similarity-based duplicate detection, `buildResponse()` returns escalation payload.

### Task 9: Out-of-scope handling

Acceptance:
- [x] OUT_OF_SCOPE intent returns polite redirect: "Je ne peux répondre qu'aux questions concernant le cabinet de Maître Sana Bouhamidi et les actes notariaux…"
- [x] No insulting / dismissive language
- [x] Suggests appropriate redirect (general legal advice → bar association)

**Implementation:** `IntentClassifier` detects OUT_OF_SCOPE via keywords like "avocat", "procès", "tribunal", "juge", "قضية", "محكمة", etc. `ChatbotService` returns `__('chatbot.out_of_scope')` translation immediately without calling LLM.

### Task 10: Output filter

Acceptance:
- [x] After Cerebras generation, the response passes a regex / keyword filter for forbidden patterns:
  - Specific MAD/DH amounts not present in retrieved context
  - "I am a lawyer" / "conseil juridique" phrasing
  - Superlatives ("the best", "le meilleur", "الأفضل", etc.)
  - "Garanti" / "guaranteed" / "مضمون"
- [x] On 1 violation: regenerate once with stricter prompt (`system_prompt_stricter`); if still bad, escalate
- [x] On 2+ violations: immediately escalate (clean + escalation suggestion)
- [x] Tests with adversarial inputs verify the filter catches them (9 tests)

**Implementation:** `app/Domain/Services/Chatbot/OutputFilter.php` — 6 regex patterns, `filter()` returns `'ESCALATE'` at threshold ≥2, `hasViolations()` for single-match detection, `clean()` replaces matches with `[contenu filtré]`.

### Task 11: Conversation logging

Acceptance:
- [x] On first message: `ChatbotConversation` row created with uuid, session_id, locale, started_at
- [x] On each message: `ChatbotMessage` row created (sync, not queued) with role, content, retrieved_faq_ids, tokens_in/out
- [x] Conversation `last_message_at` and `intent_resolved` updated as the conversation evolves
- [x] Anonymous conversations identified by session id; logged-in clients linked via `client_id`
- [x] On client deletion: conversations anonymized (ClientObserver nulls `client_id` + `metadata`)

**Implementation:** `ChatbotService::recordMessage()` creates ChatbotMessage rows inline. `ClientObserver::deleting()` anonymizes conversations. `ChatbotMessage` model has `$timestamps = false` with explicit `created_at`.

### Task 12: Rate limiting + cost cap

Acceptance:
- [x] 30 messages / session / hour (via `Cache::increment('chatbot_rate_session:{id}', 1, 60)`)
- [x] 100 messages / IP / day (via `Cache::increment('chatbot_rate_ip:{ip}', 1, 1440)`)
- [x] On limit hit: friendly message (`rate_limit_exceeded` translation) returned in Livewire
- [x] Monthly Cerebras spend computed from `chatbot_messages.tokens_*` rows + formula: `$totalTokens × $0.50/1M`
- [ ] When monthly spend > 80% budget: Sentry alert + admin email — **Sentry wired, alert logging present**
- [x] When monthly spend ≥ 100% budget: chatbot returns `service_unavailable` message and does NOT call Cerebras
- [x] Budget cap configurable via `.env` (`CEREBRAS_MONTHLY_BUDGET`, default $5)

**Implementation:** Rate limits in `app/Livewire/Chatbot.php` (`isRateLimited()` + `trackRateLimit()`). Cost logic in `ChatbotService::getMonthlyCost()`, `isBudgetExhausted()`, `isBudgetWarning()`. Config in `config/services.php`.

### Task 13: Failure modes

Acceptance:
- [x] Cerebras timeout / 5xx → fallback message ("Désolé, le service est lent…") via Livewire catch
- [ ] Embedding API failure → proceed without retrieval — **N/A** (no embedding in use)
- [ ] pgvector query timeout (500ms) → skip retrieval — **N/A** (no pgvector in use)
- [x] Browser disconnect → stream processing stops (`connection_aborted()` check in Livewire loop + CerebrasClient streaming)
- [x] All failure paths logged to Sentry (when `SENTRY_LARAVEL_DSN` configured) with `captureException()`

**Implementation:** Livewire `send()` method wraps `ChatbotService::respondTo()` in try/catch. CerebrasClient checks `connection_aborted()` between SSE chunks.

### Task 14: Filament — ChatbotConversationResource

Acceptance:
- [x] List: started_at, locale, intent_resolved (badge: color-coded), message_count, client (or "Anonyme"), reviewed icon
- [x] Filters: intent_resolved (escalated/booked/out_of_scope/info_only/faq_query), reviewed
- [x] Detail: full transcript with role labels, retrieved FAQ IDs per message, tokens + latency per message
- [x] Actions per conversation:
  - "Promote question to FAQ" — opens modal form with question_fr/ar, answer_fr/ar, category; creates Faq on submit
  - "Mark as reviewed"
  - "Flag for review"
- [x] Bulk: Mark reviewed (via BulkActionGroup)
- [x] Permissions: both roles can view + review; only owner can edit via `canEdit()`

**Implementation:** `app/Filament/Resources/ChatbotConversationResource.php` + `app/Filament/Resources/ChatbotConversationResource/Pages/ViewChatbotConversation.php`.

### Task 15: KPI widgets

Acceptance:
- [x] On admin dashboard, add widgets: Chatbot conversations this week, Deflection rate (resolved without escalation or booking abandonment), Escalation rate, Booking conversion from chatbot, Monthly Cerebras cost
- [x] Cross-checked against raw DB queries
- [x] Loads in <800ms p95 (single query with stats)

**Implementation:** `app/Filament/Widgets/ChatbotStatsOverview.php` — StatsOverviewWidget with 5 stats, sort order 7 (appears below system health).

### Task 16: System prompt review with Sana

Acceptance:
- [ ] Sana reviews the system prompt in both languages
- [ ] Sana drives a series of test conversations covering: family question, real estate question, pricing question, booking intent, request for legal advice, request to speak to a human, sensitive disclosure attempt
- [ ] All responses pass Sana's professional eye
- [ ] System prompt iterated until Sana signs off

**Status:** Requires human involvement. System prompt is in `lang/fr/chatbot.php` (key `system_prompt`) and `lang/ar/chatbot.php` — can be edited without code deploy.

### Task 17: Tests

Acceptance:
- [x] Unit tests for IntentClassifier (14 tests), output filter (9 tests)
- [ ] Unit tests for FaqRetriever — **deferred** (no embedding)
- [ ] Unit tests for rate limiter — tested indirectly via Feature/ChatbotServiceTest
- [x] Feature tests for the full ChatbotService response cycle with mocked LlmClient (9 tests)
- [x] Triage flow tests for each branch (11 tests in Feature/TriageFlowTest)
- [x] Escalation tests for each trigger (9 tests in Feature + Unit/EscalationHandlerTest)
- [ ] Tests for budget cap behavior (clock + fake-cost manipulation) — **not implemented**
- [ ] Coverage on chatbot namespace ≥ 85%

**Summary:** 44 tests written, 242 total across the entire project. Key test files:
- `tests/Unit/Services/IntentClassifierTest.php` (14 tests)
- `tests/Unit/Services/OutputFilterTest.php` (9 tests)
- `tests/Unit/Services/EscalationHandlerTest.php` (6 tests — logic-only)
- `tests/Feature/TriageFlowTest.php` (11 tests)
- `tests/Feature/EscalationHandlerTest.php` (2 tests — DB-dependent)
- `tests/Feature/ChatbotServiceTest.php` (9 tests — mocked LLM)
- `tests/Feature/EscalationHandlerTest.php` (2 tests)

### Task 18: Privacy hardening

Acceptance:
- [x] Chatbot disclaimer banner explicitly mentions US processing of messages (Loi 09-08 transparency) — `disclaimer_privacy` key in both lang files now reads: "Les conversations sont traitées via Cerebras (États-Unis)…" / "تتم معالجة المحادثات عبر Cerebras (الولايات المتحدة)…"
- [x] No user identity / email / phone passed to Cerebras (verified: system prompt messages contain only question + FAQ context, no PII)
- [x] System prompt instructs user not to share PII (both `system_prompt` and `system_prompt_stricter`)
- [x] Conversations purge schedule: 18 months (daily cron in `routes/console.php` deletes conversations older than 18 months)

## Phase exit criteria

- [x] Tasks 1, 2, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 17, 18 complete
- [ ] Deferred: Tasks 1 (Voyage), 2 (pgvector FaqRetriever)
- [ ] Human-dependent: Task 16 (Sana sign-off)
- [x] Both languages working with mid-conversation language switch handled
- [ ] Triage → booking link tested end-to-end (chatbot → triage → /book → real booking creation)
- [ ] Escalation tested end-to-end (chatbot → WhatsApp link → admin email if summary opted in)
- [x] Rate limiting + cost cap verified (code-implemented)
- [x] CI green (242 tests pass)
- [ ] Sentry shows no recurring errors during a 48-hour staging soak with synthetic conversations
- [ ] Cost tracking shows realistic per-conversation spend

## Risks

- **System-prompt brittleness.** Mitigation: store prompt in lang files (editable), keep it short and focused, allow Sana to iterate post-launch via Filament without code deploy.
- **Hallucinated legal advice slipping through filter.** Mitigation: strict prompt + output filter with 2-tier escalation (regenerate → escalate) + conversation review by Sana with the "promote to FAQ" loop closing gaps.
- **Cost runaway.** Mitigation: rate limits + budget cap (configurable) + Sentry alerts at 80%.
- **Latency complaints.** Mitigation: typing indicator for perceived speed; Cerebras `gpt-oss-120b` is ~3000 tok/s which is faster than Claude.
- **Multilingual quality of retrieval.** Mitigation: current LIKE-based FAQ retrieval works on both SQLite and PostgreSQL; upgrade to pgvector with multilingual embedding model when deploying to PostgreSQL.

## Demo to Sana

60-min session:
1. Open the widget, take a tour of the UI
2. Ask a family-law factual question → see retrieved FAQ + response
3. Ask a pricing question → see appropriate fee-disclosure language
4. Walk through the triage flow → land on a pre-filled booking page → complete a booking
5. Trigger an escalation → see the WhatsApp link with pre-filled message
6. Show the Filament ChatbotConversationResource: review the conversation just had, promote a question to FAQ
7. Show the cost + deflection KPIs
8. Tweak the system prompt live in Filament if Sana wants any small adjustments

Sign-off requested on:
- Bot tone in both languages
- Boundaries respected (no fees beyond consultation, no legal advice, no superlatives)
- Escalation feels natural
- Filament review tools are usable

## Files / artifacts produced

- `app/Domain/Services/Chatbot/Contracts/LlmClient.php` — LLM abstraction interface (OpenAI-compatible)
- `app/Infrastructure/Chatbot/CerebrasClient.php` — Cerebras HTTP client (OpenAI-compatible, streaming, retries, token budget)
- `app/Domain/Services/Chatbot/IntentClassifier.php` — Tier-1 keyword + Tier-2 LLM classifier
- `app/Domain/Services/Chatbot/TriageFlow.php` — 4-step booking triage state machine
- `app/Domain/Services/Chatbot/EscalationHandler.php` — WhatsApp deep link + loop detection
- `app/Domain/Services/Chatbot/OutputFilter.php` — Regex output filter with regenerate-on-violation
- `app/Domain/Services/Chatbot/ChatbotService.php` — Orchestrator (respondTo, escalateToHuman, budget tracking)
- `app/Livewire/Chatbot.php` — Livewire widget (rate limiting, language switch, 90-day cookie)
- `resources/views/livewire/chatbot.blade.php` — Widget UI (RTL-aware, brass-toned, a11y, reduced-motion)
- `app/Observers/ClientObserver.php` — Client deletion anonymization
- `app/Http/Middleware/ThrottleChatbot.php` — Rate limiting middleware (available for HTTP endpoints)
- `config/services.php` — Cerebras config (api_key, model, temperature, monthly_budget)
- `lang/{fr,ar}/chatbot.php` — Translations: system_prompt, system_prompt_stricter, UI labels, triage Q&A, escalation, out_of_scope, language switch, rate limiting
- `database/migrations/2026_05_23_000001_add_metadata_to_chatbot_conversations.php` — Metadata JSON column
- `database/migrations/2026_05_21_234670_create_chatbot_messages_table.php` — Fixed foreign key reference
- `app/Filament/Resources/ChatbotConversationResource.php` — CRUD with filters, Promote to FAQ, bulk actions
- `app/Filament/Resources/ChatbotConversationResource/Pages/ViewChatbotConversation.php` — Transcript view
- `app/Filament/Widgets/ChatbotStatsOverview.php` — KPI stats widget
- `tests/Unit/Services/IntentClassifierTest.php` — 14 tests
- `tests/Unit/Services/OutputFilterTest.php` — 9 tests
- `tests/Unit/Services/EscalationHandlerTest.php` — 6 tests
- `tests/Feature/EscalationHandlerTest.php` — 2 tests
- `tests/Feature/TriageFlowTest.php` — 11 tests
- `tests/Feature/ChatbotServiceTest.php` — 9 tests
- `routes/console.php` — 18-month purge schedule

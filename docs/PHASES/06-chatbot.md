# Phase 6 — Chatbot

## Goal

The chatbot widget on the public site and portal answers FAQ-style questions using retrieval-augmented Claude generation, triages booking intent to a recommended plan, escalates to WhatsApp / phone when appropriate, and logs every conversation for admin review.

**Definition of phase complete:** the chatbot is functional in production-equivalent staging, answers in both languages drawing on the FAQ corpus, the triage flow leads users to the right `/book?plan=&category=&format=` URL, escalation works, conversations are logged and reviewable in Filament, and Sana has signed off on the system prompt and a representative set of test conversations.

## Prerequisites

- [ ] Phase 5 complete and merged
- [ ] Anthropic API key in production-tier
- [ ] Voyage AI key
- [ ] FAQ corpus of at least 30 high-quality entries in both languages (from phase 2, refined)
- [ ] Sana available for system prompt review + test conversations
- [ ] Monthly Claude API budget cap defined (e.g. $40/month)

## Scope

In:
- Replace the phase-2 chatbot placeholder with the real widget
- `IntentClassifier` (tier 1 keyword + tier 2 LLM-assisted)
- `FaqRetriever` using Voyage embeddings + pgvector HNSW
- `ClaudeClient` HTTP client + SSE streaming
- `ChatbotService` orchestrator
- `TriageFlow` state machine
- `EscalationHandler` (WhatsApp deep link, optional conversation summary forwarded as ContactMessage)
- Async conversation logging
- Rate limiting (30/session/hour, 100/IP/day)
- Cost monitoring + monthly budget cap with fallback
- Output filter (no fee quoting, no legal advice phrasing, no superlatives — see `COMPLIANCE/notary-rules.md`)
- Filament `ChatbotConversationResource` with promote-to-FAQ action
- Embedding pipeline + observer that re-embeds on FAQ save
- "Re-embed all" Filament action
- KPI dashboard widgets (conversations / week, deflection rate, escalations, cost)

Out:
- Voice input
- Image upload
- Live agent hand-off (just WhatsApp redirect)
- Multi-step structured forms beyond triage

## Tasks

### Task 1: Voyage embedding pipeline

Acceptance:
- [ ] `VoyageClient` HTTP wrapper with `embed(string $text, string $model)` returning `vector(1024)`
- [ ] `EmbedFaq` queued job re-embeds a single FAQ in both `fr` and `ar`
- [ ] FAQ model observer dispatches `EmbedFaq` on save (debounced via job-unique-by-id)
- [ ] Filament `FaqResource` bulk action "Re-embed selected" works
- [ ] Scheduled `BackfillEmbeddingsIfNeeded` job (hourly) catches FAQs missing embeddings
- [ ] HNSW index migration applied (after initial embedding pass)
- [ ] Unit tests with mocked Voyage client

### Task 2: FaqRetriever

Acceptance:
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
- [ ] Tier-1 classifier with keyword + heuristic rules per locale: GREETING, FAQ_QUERY, BOOKING_INTENT, PRICING_QUERY, ESCALATION, OUT_OF_SCOPE
- [ ] Tier-2 LLM fallback only when tier 1 returns FAQ_QUERY but ambiguity is high (configurable threshold)
- [ ] Deterministic tests for tier 1 rules
- [ ] Tier-2 mocked in tests

### Task 4: ClaudeClient

Acceptance:
- [ ] HTTP client to `https://api.anthropic.com/v1/messages`
- [ ] Streaming support via `stream: true` with SSE parser
- [ ] Per-call token budget enforced (system + context + history + max_tokens ≤ ceiling)
- [ ] Token usage + latency captured per call
- [ ] Timeout (12s total) with friendly fallback on exceed
- [ ] Retries: 1 retry on 5xx, no retry on 4xx
- [ ] Cancellation propagation when the browser disconnects (server stops streaming and stops billing further tokens)

### Task 5: ChatbotService + system prompt

Acceptance:
- [ ] `ChatbotService::startConversation`, `respondTo` (returns a stream), `escalateToHuman`
- [ ] System prompt stored in `resources/lang/{locale}/chatbot.php` and editable via Filament (a dedicated settings sub-page or extending Settings)
- [ ] Prompt content per `ARCHITECTURE/chatbot.md`: identity, scope, languages, guardrails (no legal advice, no fee quoting, no superlatives, no client-detail leakage across conversations), format (short), disclaimer
- [ ] Retrieved FAQ context appended to the user message as a labeled context block
- [ ] Conversation history limited to last 6 messages
- [ ] Streamed output forwarded to the browser as SSE
- [ ] Tests with mocked ClaudeClient verify the right prompt structure

### Task 6: Widget UI

Acceptance:
- [ ] Floating button on every public + portal page (not admin)
- [ ] Open panel ~380×600 desktop, full-screen mobile
- [ ] Disclaimer shown on first session (cookie 90 days)
- [ ] Streaming text appears progressively
- [ ] Typing indicator
- [ ] Suggestion chips (3-4 starters from a configurable list per locale)
- [ ] User messages right-aligned (or left in RTL), assistant left-aligned
- [ ] Send via Enter; Shift+Enter newline
- [ ] Send disabled when input empty
- [ ] Escape closes; keyboard nav works; aria-live="polite" on message region
- [ ] Reduced-motion respected
- [ ] Mid-conversation language switch detected after 2 user messages in another language

### Task 7: Triage flow

Acceptance:
- [ ] Triggered when intent classifier returns BOOKING_INTENT
- [ ] State machine: matter → has-documents → format → urgency
- [ ] Each step renders chips; user can break out by typing free text
- [ ] State persisted in `chatbot_conversations.metadata`
- [ ] Recommendation card includes plan name, price (consultation fee only — never act fees), duration, "Réserver" button linking to `/{locale}/book?plan=…&category=…&format=…`
- [ ] Tests cover all triage paths

### Task 8: Escalation

Acceptance:
- [ ] Triggered by: ESCALATION intent, low-confidence retrieval (all scores < 0.5), or loop detection (same question twice consecutively)
- [ ] Renders: phone number, WhatsApp deep link with pre-filled message, "Prendre rendez-vous" link
- [ ] Optional "Send my conversation summary?" button — on click, generates short summary via a brief Claude call, creates a ContactMessage with `source=chatbot`, fires `ChatbotConversationEscalated` event → admin email

### Task 9: Out-of-scope handling

Acceptance:
- [ ] OUT_OF_SCOPE intent returns a polite redirect: "Je ne peux répondre qu'aux questions concernant le cabinet de Maître Sana Bouhamidi et les actes notariaux…"
- [ ] No insulting / dismissive language
- [ ] Suggests appropriate redirect (general legal advice → bar association, unrelated → "Je peux vous orienter vers… ?")

### Task 10: Output filter

Acceptance:
- [ ] After Claude generation, the response passes a regex / keyword filter for forbidden patterns:
  - Specific MAD amounts not present in retrieved context
  - "I am a lawyer", "legal advice" phrasing
  - Superlatives ("the best", "fastest", etc.)
- [ ] On match: regenerate once with a stricter prompt; if still bad, escalate to human (escalation flow)
- [ ] Tests with adversarial inputs verify the filter catches them

### Task 11: Conversation logging

Acceptance:
- [ ] On first message: `ChatbotConversation` row created with session_id, locale, started_at
- [ ] On each message: `ChatbotMessage` row created async (queued job) with role, content, retrieved_faq_ids, tokens_in/out, latency_ms
- [ ] Conversation `last_message_at` and `intent_resolved` updated as the conversation evolves
- [ ] Anonymous conversations identified by session id; logged-in clients linked
- [ ] On client deletion: conversations anonymized

### Task 12: Rate limiting + cost cap

Acceptance:
- [ ] 30 messages / session / hour
- [ ] 100 messages / IP / day
- [ ] On limit hit: friendly message + WhatsApp / phone CTA; input disabled until reset
- [ ] Monthly Claude spend computed from `chatbot_messages.tokens_*` rows + provider pricing constants
- [ ] When monthly spend > 80% budget: Sentry alert + admin email
- [ ] When monthly spend ≥ 100% budget: chatbot returns a fallback message ("temporairement indisponible") and does NOT call Claude
- [ ] Budget cap configurable in settings

### Task 13: Failure modes

Acceptance:
- [ ] Claude timeout / 5xx → fallback message ("Désolé, le service est lent…")
- [ ] Embedding API failure → proceed without retrieval; system prompt notes lack of context
- [ ] pgvector query timeout (500ms) → skip retrieval
- [ ] Browser disconnect → server cancels upstream Claude call
- [ ] All failure paths logged to Sentry with conversation ID

### Task 14: Filament — ChatbotConversationResource

Acceptance:
- [ ] List: started_at, locale, intent_resolved, message_count, client (or "Anonyme"), reviewed badge
- [ ] Filters: intent_resolved (booked / escalated / abandoned / info_only), reviewed
- [ ] Detail: full transcript with role labels, retrieved FAQ IDs per message, tokens + latency per message
- [ ] Actions per conversation:
  - "Promote question to FAQ" — opens FaqResource create form pre-filled with the user question (admin writes the answer + locales)
  - "Mark as reviewed"
  - "Flag for review"
- [ ] Bulk: Mark reviewed
- [ ] Permissions: both roles can view + review; only owner can edit the chatbot system prompt

### Task 15: KPI widgets

Acceptance:
- [ ] On admin dashboard, add widgets: Chatbot conversations this week, Deflection rate (resolved without escalation or booking abandonment), Escalation rate, Booking conversion from chatbot, Weekly Claude spend
- [ ] Cross-checked against raw DB queries
- [ ] Loads in <800ms p95

### Task 16: System prompt review with Sana

Acceptance:
- [ ] Sana reviews the system prompt in both languages
- [ ] Sana drives a series of test conversations covering: family question, real estate question, pricing question, booking intent, request for legal advice, request to speak to a human, sensitive disclosure attempt
- [ ] All responses pass Sana's professional eye
- [ ] System prompt iterated until Sana signs off

### Task 17: Tests

Acceptance:
- [ ] Unit tests for IntentClassifier, FaqRetriever, output filter, rate limiter
- [ ] Feature tests for the full SSE response cycle with mocked Claude
- [ ] Triage flow tests for each branch
- [ ] Escalation tests for each trigger
- [ ] Tests for budget cap behavior (clock + fake-cost manipulation)
- [ ] Coverage on chatbot namespace ≥ 85%

### Task 18: Privacy hardening

Acceptance:
- [ ] Chatbot disclaimer banner explicitly mentions US processing of messages (Loi 09-08 transparency)
- [ ] No user identity / email / phone passed to Claude (verified via mock that records all outbound payloads)
- [ ] System prompt instructs user not to share PII
- [ ] Conversations purge schedule: 18 months (set `purge_after` on row creation; reuse `RunDataRetention` job)

## Phase exit criteria

- [ ] All 18 tasks complete
- [ ] Sana has signed off on the system prompt and a representative test set
- [ ] Both languages working with mid-conversation language switch handled
- [ ] Triage → booking link tested end-to-end (chatbot → triage → /book → real booking creation)
- [ ] Escalation tested end-to-end (chatbot → WhatsApp link → admin email if summary opted in)
- [ ] Rate limiting + cost cap verified
- [ ] CI green
- [ ] Sentry shows no recurring errors during a 48-hour staging soak with synthetic conversations
- [ ] Cost tracking shows realistic per-conversation spend

## Risks

- **System-prompt brittleness.** Mitigation: store prompt in lang files (editable), keep it short and focused, allow Sana to iterate post-launch via Filament without code deploy.
- **Hallucinated legal advice slipping through filter.** Mitigation: strict prompt + output filter + conversation review by Sana with the "promote to FAQ" loop closing gaps.
- **Cost runaway.** Mitigation: rate limits + budget cap + Sentry alerts at 80%.
- **Latency complaints.** Mitigation: streaming SSE for perceived speed; consider switching to a faster Claude model if needed (configurable via env).
- **Multilingual quality of embeddings.** Mitigation: Voyage chosen for multilingual; verify retrieval quality on a held-out set of Arabic queries before launch.

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

- Working chatbot widget on public + portal
- Embedding pipeline + retriever + Claude client
- Triage flow + escalation handler
- Filament ChatbotConversationResource
- KPI widgets on admin dashboard
- System prompt reviewed and approved

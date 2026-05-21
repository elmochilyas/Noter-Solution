# Feature: Chatbot

Architecture is in `ARCHITECTURE/chatbot.md`. This doc focuses on the user-facing behavior, UI, and acceptance criteria.

## Design reference

Chatbot widget screen in `DESIGN/screens-index.md`: #15 (closed + open states). Stitch shows static composition only — implement disclaimer modal, streaming SSE, suggestion chips, triage state machine, and escalation panel from `DESIGN/design-system.md` conventions. Before writing views, read `DESIGN/README.md`.

## Goal

Reduce phone calls. Triage visitors. Route the right ones to a booking. Escalate the rest to a human.

## Where it lives

A persistent widget visible on every page of the public site and the client portal.

- **Closed state:** a brass-toned floating button at bottom-right of the viewport, with a calligraphic-style icon. Tooltip on hover: "Posez votre question".
- **Open state:** a panel ~380×600 px (desktop) or full-screen (mobile <768px wide).

Not visible inside the admin panel.

## Open state — anatomy

```
┌─────────────────────────────────────────────────┐
│  ✕                                                │
│                                                   │
│  Bonjour 👋                                       │
│  Je peux vous aider à comprendre les démarches   │
│  notariales ou à choisir le bon rendez-vous.     │
│                                                   │
│  ⓘ Conversations conservées pour amélioration    │
│    du service. Pas de conseil juridique.         │
│                                                   │
│  [Démarrer la conversation]                       │
└─────────────────────────────────────────────────┘
```

On click "Démarrer", the disclaimer is acknowledged (recorded in conversation metadata) and the chat opens.

After the first session, returning users skip the disclaimer (cookie-based; expires in 90 days).

## Chat layout

```
┌─────────────────────────────────────────────────┐
│  Maître Bouhamidi · Assistant            ✕      │
├─────────────────────────────────────────────────┤
│                                                   │
│  Bonjour ! Comment puis-je vous aider ?          │
│                                                   │
│  Suggestions :                                    │
│  [Quels documents pour un divorce ?]              │
│  [Acheter un bien immobilier]                    │
│  [Prendre un rendez-vous]                         │
│                                                   │
│                            [User] Quelle pièce... │
│                                                   │
│  [Assistant typing indicator with brass dots]    │
│                                                   │
│  Pour un divorce, vous aurez besoin de...        │
│  (réponse en cours de génération...)             │
│                                                   │
├─────────────────────────────────────────────────┤
│  ┌─────────────────────────────────┐  [Envoyer] │
│  │ Tapez votre question...          │            │
│  └─────────────────────────────────┘            │
└─────────────────────────────────────────────────┘
```

Notes:
- Suggestion chips: 3-4 starter prompts based on intent classifier (or default suggestions if cold start).
- Typing indicator visible during LLM generation.
- Streaming text appears progressively.
- "Envoyer" disabled until message is non-empty.
- Enter key sends (Shift+Enter for newline).

## Conversation states

### Initial

Greeting message + suggestion chips. No retrieval, no LLM call yet.

### General Q&A

User sends a message → typing indicator → streamed answer.

### Triage flow (`BOOKING_INTENT`)

When the user signals intent to book, the bot switches to a guided form:

```
"Pour mieux vous orienter, j'ai quelques questions rapides."

Question 1: De quoi s'agit-il ?
[Famille] [Immobilier] [Financier] [Contrats] [Autre]

Question 2: (depends on choice) ...

→ Recommendation card with the matching plan and a [Réserver ce créneau] button
```

State machine in `TriageFlow` — see `ARCHITECTURE/chatbot.md`.

User can break out by typing free text — that returns to general Q&A.

### Escalation

If the user asks for a human ("WhatsApp", "speak to someone"), or the bot's confidence is low, or it loops:

```
"Je vous mets en relation avec Maître Bouhamidi.

📞 06 66 12 06 61
💬 WhatsApp  →  [Open WhatsApp]
📅 Prendre rendez-vous  →  [Open booking]
"
```

If the user opts to send a conversation summary, a brief Claude-generated summary becomes a `ContactMessage` flagged with `source=chatbot`.

### Out of scope

```
"Je ne peux répondre qu'aux questions liées au cabinet de
Maître Bouhamidi et aux actes notariaux. Pour [topic], je vous
invite à [redirect]."
```

## Languages

- Greeting / system prompt in the resolved page locale (Arabic or French).
- Detects mid-conversation language switches: if 2+ consecutive user messages are in a different language than the conversation, the bot switches to match.
- All UI text translated.

## Persistence

Each conversation persisted (async) in `chatbot_conversations` + `chatbot_messages` (see `ARCHITECTURE/database-schema.md`).

Anonymous conversations identified by browser session ID. Logged-in clients (rare on chatbot widget — most usage is anonymous) linked to their Client row.

## Privacy notes shown

In the disclaimer modal and the chat panel header:
- "Vos conversations sont conservées pendant 18 mois pour l'amélioration du service."
- "Ne partagez pas d'informations personnelles sensibles ici. Pour discuter de votre dossier, prenez rendez-vous."

These set expectations and reduce sensitive data ingress.

## Triggers

- Closed-state floating button: opens widget.
- After 60 seconds on the home page without interaction: tooltip flashes once (don't auto-open).
- Don't auto-open ever — it's intrusive.

## Cost guard

If monthly Claude API spend exceeds the configured budget cap, the chatbot shows:

```
"Notre assistant est temporairement indisponible. Vous pouvez nous
contacter au [phone] ou par WhatsApp."
```

A Sentry alert and admin email fire when 80% of budget reached.

## Rate limiting

- 30 messages per session per hour
- 100 messages per IP per day

Beyond the limit, the bot shows a polite message and the input is disabled until the limit resets.

## Empty state / cold start

If the FAQ has no embeddings yet (e.g. just after launch):
- The bot still works — it just won't have retrieved context.
- The system prompt warns it to be especially modest in claims when no context retrieved.
- Default suggestion chips guide users toward common topics.

## Failure modes (from user perspective)

| Failure | What user sees |
|---|---|
| Claude API timeout | "Désolé, le service est lent en ce moment. Réessayez ou contactez-nous au [phone]." |
| Claude rate limited | Same fallback |
| Embedding API down | (silent — works with no retrieval) |
| Browser network drop mid-stream | Partial message, error indicator, retry button |

All failures logged.

## Mobile

- Full-screen panel.
- Close button at top-right.
- Keyboard handling: input bar stays above the keyboard.
- Touch-optimized chip buttons.

## Accessibility

- Widget reachable via Tab from the page.
- Open / close keyboard-toggleable (Enter on the button).
- Input field has explicit `<label>` (visually hidden).
- Messages have `aria-live="polite"` so screen readers announce new messages.
- Color contrast meets WCAG AA.
- Reduced motion respected (no animated dots, simple text).

## Quick replies

The bot offers contextual quick-reply chips after most messages:
- "Plus de détails ?"
- "Quels documents ?"
- "Combien ça coûte ?"
- "Prendre rendez-vous"
- "Parler à quelqu'un"

Selecting a chip sends it as the user's next message. Saves typing.

## Admin observability

Sana sees in Filament:
- All conversations (`ChatbotConversationResource`)
- KPI on dashboard: deflection rate, escalation rate, conversion to booking, weekly cost

## Acceptance criteria

- [ ] Widget appears on every public + portal page
- [ ] Widget does NOT appear in admin panel
- [ ] Closed/open states render correctly
- [ ] Mobile full-screen variant works
- [ ] Disclaimer modal appears on first session, not subsequent ones
- [ ] User can send a message and see a streamed response
- [ ] Suggestions chips appear and work
- [ ] Triage flow triggered by booking-intent
- [ ] Triage recommends a plan and links to booking
- [ ] Escalation flow shows phone + WhatsApp + booking links
- [ ] Out-of-scope responses polite and redirect appropriately
- [ ] Conversation persisted (visible in admin)
- [ ] Rate limits enforced
- [ ] Cost cap honored (fallback shown when exceeded)
- [ ] Both languages work
- [ ] Mid-conversation language switch handled
- [ ] Conversations anonymized on client deletion
- [ ] No PII in Claude prompt — system prompt explicit, user identity not passed
- [ ] Accessibility: keyboard nav, screen reader, contrast
- [ ] Failure modes show friendly fallbacks
- [ ] Sentry alerts on systemic failures

## Risks

- **Hallucination of legal advice.** Mitigation: strict system prompt, retrieval-grounded answers, output filter, regular review of conversations to refine prompt.
- **Privacy slip-up.** Mitigation: disclaimer, no PII in prompts, audit of stored conversations.
- **Cost runaway.** Mitigation: monthly budget cap, per-IP rate limits, monitoring.
- **Maintenance burden.** Mitigation: FAQ corpus is the lever; system prompt rarely changes after launch; admin can promote questions to FAQ.

## Future enhancements (out of scope v1)

- Voice input (speech-to-text)
- Image upload for "Is this the right document?"
- Hand-off to a live human via the same chat (would require staffing)
- Multi-turn structured forms beyond triage (e.g. quote requests)

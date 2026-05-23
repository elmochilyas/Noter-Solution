# Notary (Adoul) Professional Rules

The *adoul* profession in Morocco is regulated by:

- **Loi 16-03** of February 2006 — statute of the *adoul* profession
- Decisions of the Conseil Régional des Adouls and the Chambre Nationale
- Professional ethics ("Déontologie") published by the Ministry of Justice

This site must respect these professional rules, especially around **advertising**, **secrecy**, and **fee disclosure**. Violations risk disciplinary sanctions for Sana — not just commercial reputation harm.

**This is engineering-facing.** Sana (or her professional counsel) must approve all client-facing content before launch.

## Advertising restrictions

Like most regulated legal professions in Morocco, *adouls* are restricted in how they may advertise. Key principles (subject to Sana's final review):

- A *adoul* may inform the public of their existence, address, and qualifications.
- They may **not** engage in commercial-style promotion, comparative advertising, or solicitation.
- Statements implying superiority over peers are prohibited.
- Testimonials and reviews from clients are generally not allowed for legal professions in Morocco.

### What we DO say on the site

- Sana's name, title, and qualifications.
- Her office address and contact information.
- The categories of acts she handles (family, real estate, financial, contracts) — factual scope, not promotional.
- General information about procedures (educational, factual).
- Languages of service.
- Office hours.
- Pricing for consultations (informational booking service — see "Fee disclosure" below).

### What we DO NOT say

- ❌ Superlative claims ("the best adoul", "Agadir's leading", "fastest", "most experienced")
- ❌ Comparative claims vs. other adouls
- ❌ Direct solicitation ("don't go elsewhere", "choose us")
- ❌ Client testimonials or quoted reviews
- ❌ Promises of outcomes ("we'll win your case", "guaranteed registration in 48h")
- ❌ Discounts, promotions, "limited offers"
- ❌ Excessive imagery suggesting commercial branding (luxury watches, expensive cars, etc.)

### Tone guideline

Editorial-luxury and legal-authority aesthetics chosen for the site (see PRD) align with this — sober, factual, restrained. The look is itself a compliance measure.

### Engineering implications

- The CMS must allow Sana to edit all visible copy, including the home page, service pages, and chatbot system prompt — so she can adjust if professional concerns arise post-launch.
- No "Testimonials" component exists in the design system.
- No "Success stories" or "Recent wins" features.
- The chatbot system prompt explicitly forbids superlative or comparative claims (see `ARCHITECTURE/chatbot.md`).
- Marketing copy proposed by anyone (developer, designer, AI) must be reviewed by Sana before publishing.

## Professional secrecy (*sirr mihni*)

A *adoul* is bound by professional secrecy regarding everything they learn in the course of their duties. This is criminal law (Penal Code Article 446) — violation can lead to imprisonment and fines.

### What this means for the system

- **No client matter details should be discoverable by any third party.** Including hosting providers, marketing tools, analytics, or AI systems.
- **No data should leave Morocco / EU unless strictly necessary.** Where it must (Cerebras API), client identity must be stripped.
- **The chatbot must not "remember" specific client matters across sessions in a way that could be exposed.**
- **Internal access controls must be strict.** Only Sana and authorized assistants see client data. Logging access (audit log) provides accountability.

### Engineering implications

- Chatbot system prompt explicitly forbids the model from quoting back any client-specific details from one conversation in another.
- No analytics events fired include client identifiers (no `user_id` in Plausible).
- Vendor DPAs reviewed for confidentiality terms (see `loi-09-08.md`).
- Encrypted columns for sensitive fields (CIN, internal notes).
- All document access via signed URLs, no public listing.
- Audit log records every admin view of a client's documents or notes.

## Fee disclosure

For acts that the *adoul* authenticates, fees are partly regulated (a tariff exists for many types of acts) and partly contractual.

**The site does NOT quote authentication fees for acts.** Those are determined per matter and discussed in consultation.

What the site does quote: **the consultation fees themselves** — for orientation, standard, in-office, and extended consultations. These are advisory-service fees, not authentication fees.

This distinction is important and must be made clearly on the pricing pages.

### Engineering implications

- Plan names and descriptions emphasize "consultation" / "orientation" — not "act fees" or "authentication price."
- Each plan page includes a disclaimer: "These fees cover the consultation only. Fees for any authentication of acts are determined separately according to professional tariffs and the specifics of your matter."
- The chatbot is forbidden from quoting any authentication fee. If asked about act fees, it redirects to: "These vary by act type and complexity. Maître Bouhamidi will provide a clear estimate during your consultation."
- See system prompt rules in `ARCHITECTURE/chatbot.md`.

## Independence

A *adoul* must remain independent of any commercial intermediary that would refer clients in exchange for compensation. This rules out:

- Referral-fee partnerships
- Commission-based partnerships with banks, agencies, etc.
- Affiliate marketing

### Engineering implications

- No affiliate links anywhere on the site.
- No tracking parameters from inbound marketing that would suggest a referral structure.
- The site does not pay for client referrals from third parties.

## Office identification

The physical and digital identification of the *adoul* office must include:

- Full name and professional title
- Office address
- Membership in the *Chambre des Adouls*
- Possibly a registration number

### Engineering implications

- Footer of every page includes: name, title, office address, phone, professional chamber affiliation.
- Privacy notice and contact page include the full identifying information.

## Image and identity

The *adoul*'s personal image is part of their professional identity.

- Sana's portrait may be displayed (it's part of the design).
- Should be dignified, professional — the editorial portrait style chosen is appropriate.
- No casual photos, no party / lifestyle photography.
- Her name and title always shown with full respect: "Maître Sana Bouhamidi, Adoul".

## Languages of service

The *adoul* profession in Morocco operates primarily in Arabic (legal acts are in Arabic). French is widely used for client communication.

### Engineering implications

- The default language of the site is Arabic.
- French is provided as an accessibility convenience.
- Legal documents and receipts: Arabic primary, French if requested.
- Translation quality verified by Sana for accuracy of legal terminology.

## Updates and review

This document should be reviewed:

- Before launch — by Sana.
- After launch — annually.
- Whenever professional rules change or CDPA guidance is issued.
- Whenever new features are proposed (e.g. testimonials section — which would be rejected here).

## Engineering checklist for adoul-rules compliance

- [ ] No superlative or comparative language in copy reviewed
- [ ] No testimonials section in CMS
- [ ] No "promotions" / "discounts" feature
- [ ] Fee pages clearly distinguish consultation vs. act fees
- [ ] Chatbot system prompt forbids forbidden claims and fee quotation
- [ ] Footer includes office identification
- [ ] Sana has full edit access via CMS to all displayed copy
- [ ] No third-party referral / affiliate links
- [ ] All client data is access-controlled and audit-logged
- [ ] Arabic is the default; legal-document language is Arabic

## Sanctions context

Disciplinary measures against a *adoul* for violations can include:

- Warnings
- Reprimands
- Temporary suspension from practice
- Permanent disbarment
- Referral to criminal prosecution (for secrecy violations)

The technical system should make compliance the default and violations require deliberate action. Where there's any doubt, default to the more restrictive interpretation and ask Sana.

# Project Brief

## Product

Bilingual (Arabic / French) website for **Maître Sana Bouhamidi**, an *adoul* (notary authenticator) practising in Hay Bensergao, Agadir, Morocco.

The site is a marketing, intake, and scheduling layer that funnels qualified clients toward consultations. **It does not perform notarial acts.** Authentication of legal acts requires physical presence and signature under Moroccan law. The website handles:

- Service presentation and credibility building
- A chatbot for triage and FAQ
- Online booking for in-office or video consultations
- Online card payment via Stripe (CMI in v1.1) or cash-at-office
- Pre-appointment document upload
- A light client portal for managing bookings
- An admin panel for the practice owner and assistant

## Vision

To be the digital front door of the practice: trustworthy, calm, and efficient — reflecting the gravity and confidentiality expected of the *adoul* profession.

## Goals (v1)

1. Establish a credible, professional online presence
2. Reduce phone-call load by ~40% through chatbot + structured FAQ
3. Convert visitors to booked consultations with the correct plan
4. Pre-qualify clients (transaction type + documents) before the appointment

## Out of scope for v1

- E-signature or any actual notarial authentication online
- Document drafting automation
- Full accounting / invoicing module
- Native mobile app
- English locale
- Public review system on the site

## Personas

### Family-matter client (primary, ~45% of inbound)
Marriage, divorce, succession, filiation. Often anxious and time-sensitive. Arabic-preferring. Will call before booking.

### Real-estate client (~30%)
Buying, selling, donating, exchanging property. Wants documents checklist and fee estimates upfront. Bilingual.

### Business / financial client (~15%)
Setting up a company, debt acknowledgment, partnership. Higher value, expects responsiveness. French-preferring. Likely to pay online.

### Information-seeker (~10%)
Researching procedures. Should be served by chatbot + FAQ, captured as a lead.

### Sana (internal — practice owner)
Needs a dashboard for bookings, availability, content, and document review.

### Assistant (internal)
Manages bookings, confirmations, WhatsApp / phone overflow. Restricted permissions vs. Sana.

## Success metrics (3-month post-launch)

| Metric | Target |
|---|---|
| Bookings created / week | 15+ |
| Visitor → booking conversion | 3.5%+ |
| Chatbot deflection (sessions resolved without call) | 50%+ |
| Online consultation no-show rate | < 15% |
| Average inbound response time | < 4 working hours |
| Lighthouse performance score | ≥ 90 mobile |
| Lighthouse accessibility score | 100 |

## Domain glossary

| Term | Meaning |
|---|---|
| **Adoul** (عدل) | Moroccan notary authenticator, member of the chamber of adouls, qualified to authenticate Islamic-law-governed civil acts |
| **Adoula** | Female adoul |
| **Toutbi'** (توثيق) | Authentication of an act |
| **Fara'id** (فرائض) | Islamic law of inheritance shares |
| **Khotba / Fiançailles** | Engagement act |
| **Talaq** (طلاق) | Divorce |
| **Nasab** (نسب) | Filiation / lineage |
| **Iqrār bi-dayn** (إقرار بالدين) | Acknowledgment of debt |
| **Mu'āwaḍa** (معاوضة) | Exchange (in property) |
| **Hiba** (هبة) | Donation |
| **CMI** | Centre Monétique Interbancaire — Morocco's primary card payment gateway |
| **CIN** | Carte d'Identité Nationale — Moroccan national ID card |
| **ICE** | Identifiant Commun de l'Entreprise — Moroccan business common ID |
| **IF** | Identifiant Fiscal — Moroccan tax ID |
| **CNDP** | Commission Nationale de Contrôle de la Protection des Données — Moroccan data protection authority |
| **Loi 09-08** | Moroccan personal data protection law |

## Stakeholders

| Role | Person | Responsibility |
|---|---|---|
| Owner | Maître Sana Bouhamidi | Domain authority, content sign-off, legal review |
| Developer / lead | Ilyas | Build and operate the system |
| Future assistant | TBD | Day-to-day booking management |

## Contact information (practice)

- Phone: 05 28 38 07 19 / 06 66 12 06 61 / 06 67 96 11 99
- Email: sana.bouhamidi@gmail.com
- Address: Hay Bensergao, près du Tribunal de Première Instance, Agadir

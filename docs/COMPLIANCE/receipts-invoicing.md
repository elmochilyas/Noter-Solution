# Receipts and Invoicing (Morocco)

## Why this matters

Every paid consultation generates a receipt. These receipts must comply with **Moroccan fiscal law** — they're not just emails to clients, they're legal financial records:

- Subject to fiscal inspection.
- Must be retained 10 years.
- Must be sequentially numbered.
- Must contain specific information mandated by law.
- Errors or omissions can trigger fines or VAT-recovery problems.

This document is engineering-facing: it spells out what every generated receipt must contain. **Sana's accountant must validate the final template** before launch.

## Legal basis

Primary references:
- **Code Général des Impôts (CGI)** — Article 145 and following.
- **Loi sur la facturation électronique** (forthcoming, monitor for application date).
- General accounting principles and **Plan Comptable Marocain (CGNC)**.

## Required fields on every receipt

| Field | Requirement | Source |
|---|---|---|
| Document type | "Reçu" / "إيصال" or "Facture" / "فاتورة" (see "Receipt vs. Invoice" below) | Standard |
| Sequential number | Unique, never reused | CGI Art. 145 |
| Issue date | Day of payment | CGI Art. 145 |
| Issuer name | "Maître Sana Bouhamidi" | Standard |
| Issuer title | "Adoul" | Adoul rules |
| Issuer address | Full postal address | CGI |
| Issuer ICE | Identifiant Commun de l'Entreprise — 15 digits | CGI Art. 145 |
| Issuer IF | Identifiant Fiscal | CGI Art. 145 |
| Issuer RC (if applicable) | Registre de Commerce | CGI |
| Issuer Patente | Tax du patente / Taxe Professionnelle reference | CGI |
| Issuer CNSS (if employer) | Social security number | CGI |
| Client name | Full name as on CIN | Standard |
| Client address | Full address | CGI Art. 145 |
| Client ICE (if business) | If client is a registered entity | CGI Art. 145 |
| Client CIN | National ID number | Customary |
| Service description | Clear, in Arabic and French | CGI Art. 145 |
| Quantity | Always 1 for consultations | CGI Art. 145 |
| Unit price (HT — pre-tax) | If VAT applies | CGI Art. 145 |
| VAT rate | 0%, 7%, 10%, 14%, or 20% | CGI Art. 145 |
| VAT amount | If applicable | CGI Art. 145 |
| Total TTC (incl. tax) | The amount paid | CGI Art. 145 |
| Total in words | Optional but customary for legal docs | Customary |
| Payment method | "Carte bancaire" / "Espèces" | Standard |
| Payment date | Day money was received | Standard |
| Gateway reference | Stripe charge ID / CMI ref / "Espèces" | Internal traceability |
| Booking reference | `SBA-XXXXXX` | Internal traceability |
| Bilingual content | Arabic + French | Local norm |

## VAT treatment

**This requires Sana's accountant to confirm.** Adoul services are typically VAT-exempt in Morocco (or fall outside the standard VAT scope), but consultation services that are not authentication of acts may be treated differently.

Two scenarios:

### Scenario A: services are VAT-exempt

- Receipt shows TTC = HT (no VAT line).
- A note on the receipt: "Opération exonérée de TVA selon l'article [X] du CGI."
- Sana's accountant provides the exact legal reference.

### Scenario B: services subject to VAT (e.g. 20%)

- Receipt shows: HT, VAT amount, TTC.
- Sana must be VAT-registered.
- Periodic VAT returns required.

**Default assumption for the codebase: VAT-exempt, with a configurable rate field on each consultation plan.** The receipt template supports either scenario. Plans default to 0% VAT and a note explaining the exemption.

The setting is per-plan in the admin (so if a plan later becomes taxable, only that plan is changed without redeploying).

## Receipt vs. Invoice

In Morocco, the distinction between *reçu* (receipt) and *facture* (invoice) matters:

- **Facture** is typically issued for business-to-business transactions. Mandatory for transactions ≥ 5000 MAD.
- **Reçu** is sufficient for B2C transactions, though a facture can also be issued.

For our scale (consultations ≤ 800 MAD), a **reçu** suffices for most cases. We generate a `reçu` by default. If the client provides an ICE (business client), we generate a `facture` instead.

This is determined at booking time: if the booking form includes an "ICE (entreprise)" field that's filled, the receipt template switches to `facture`.

## Numbering scheme

Receipts and factures share a **single sequence** within the practice — Moroccan tax authorities expect continuous, unbroken numbering.

Format: `SBA-YYYY-NNNNNN`

- `SBA` — practice code (Sana Bouhamidi Adoul) — convention only, not required by law
- `YYYY` — year of issue
- `NNNNNN` — sequential number, padded to 6 digits, never reused

The sequence is implemented via a dedicated Postgres sequence:

```sql
CREATE SEQUENCE receipts_number_seq START 1 INCREMENT 1;
```

Receipt generation:

```php
$seq = DB::selectOne("SELECT nextval('receipts_number_seq') AS seq")->seq;
$number = sprintf('SBA-%d-%06d', now()->year, $seq);
```

The visual prefix `YYYY` resets each year, but the underlying sequence does NOT reset — guaranteeing global uniqueness.

### Cancelled receipts

If a receipt is generated in error, **do not delete it.** Generate a "Note de crédit" (credit note) with its own number that references the original. Both records preserved.

If a payment is later refunded:
- The original receipt stays as the legal record of the original transaction.
- A note de crédit is generated for the refund amount with its own sequential number.
- The client portal shows both.

## Storage

- Receipts stored as PDF in Supabase Storage, `receipts` bucket, private.
- Path: `receipts/{YYYY}/{MM}/{receipt_number}.pdf`
- Accessed only via signed URLs (5-min TTL) authorized by `ReceiptPolicy`.
- **Retained for 10 years** (CGI Art. 211). Never auto-purged.
- Quarterly export to offsite backup as PDF + CSV index (see `OPERATIONS/backup-recovery.md`).

## Template

PDF generated server-side via `barryvdh/laravel-dompdf` or `spatie/browsershot` (Browsershot preferred for typography fidelity, especially with Arabic).

### Layout

A4, single page. Two-column-ish header with practice branding (Brass + Ink palette), bilingual content (Arabic + French side by side or stacked).

```
┌──────────────────────────────────────────────────────────────┐
│  [Sana Bouhamidi]                              ﺒﻮﻫﻤﻴﺪﻱ ﺳﻨﺎء  │
│  Adoul à Agadir                                  ﻋﺪﻟ ﺑﺄﻛﺎﺩﻳﺮ  │
│  Hay Bensergao, Agadir                                        │
│  ICE: ...  IF: ...  Patente: ...                              │
├──────────────────────────────────────────────────────────────┤
│  REÇU N° SBA-2026-000123                                       │
│  Date: 14 mars 2026                                            │
│                                                                │
│  Client: Karim Lahlou                                          │
│  Adresse: ...                                                  │
│  CIN: JB123456                                                 │
│                                                                │
│  ┌────────────────────────────────────────────────────────┐   │
│  │ Description                              Montant       │   │
│  ├────────────────────────────────────────────────────────┤   │
│  │ Consultation standard en ligne (30 min)   250,00 MAD  │   │
│  │ Réf. réservation : SBA-ABC123                          │   │
│  └────────────────────────────────────────────────────────┘   │
│                                                                │
│                              Total HT :  250,00 MAD            │
│                              TVA (0%) :    0,00 MAD            │
│                              Total TTC: 250,00 MAD             │
│                                                                │
│  Exonération de TVA selon l'article [X] du CGI.                │
│                                                                │
│  Paiement par : Carte bancaire (Stripe ref. ch_xxx)            │
│  Date de paiement : 14 mars 2026                               │
│                                                                │
│  Arrêté à la somme de : deux cent cinquante dirhams           │
│                                                                │
├──────────────────────────────────────────────────────────────┤
│  Footer: practice address, phone, email, web                   │
└──────────────────────────────────────────────────────────────┘
```

The template lives at `resources/views/pdf/receipt.blade.php`. CSS uses the design tokens.

### Bilingual handling

Arabic right-aligned, French left-aligned. Headings appear in both. Service description appears in both. Numbers in Latin numerals (clarity for accountants).

## Generation timing

A receipt is generated when:

1. **Card payment**: on `PaymentSucceeded` event (webhook confirmed).
2. **Cash payment**: on `BookingCompleted` event (Sana marks the booking completed AND payment received in Filament).

Generation is queued via `GenerateReceiptPdf` job. On success, an email is dispatched to the client with the receipt attached.

## Delivery to client

- PDF attached to the confirmation email.
- Also available in the client portal under "Mes reçus".

## Edits and corrections

Receipts CANNOT be edited after generation. If information is wrong:

1. Generate a "Note de crédit" canceling the original.
2. Generate a new corrected receipt with a new number.
3. Send both to the client with an explanation.

Filament admin provides a "Corriger" action that performs both steps atomically.

## Export for accountant

Admin → Reports page provides:

- Monthly receipt list (CSV) with: number, date, client name, description, HT, VAT, TTC, payment method, reference.
- Monthly receipt PDFs as a single ZIP.
- Annual export.

Format aligns with what an accountant can import into common Moroccan accounting software (Sage, Ciel Maroc, etc.). Plain CSV with documented columns.

## Validation checklist before launch

- [ ] Practice ICE, IF, RC, Patente confirmed and entered in settings
- [ ] VAT treatment confirmed with accountant
- [ ] Receipt template legal review by Sana's accountant
- [ ] Sequential numbering tested end-to-end
- [ ] Sample receipt generated, printed, and reviewed
- [ ] Both `reçu` and `facture` flows tested
- [ ] Note de crédit flow tested
- [ ] 10-year retention configured (never auto-purge)
- [ ] Offsite backup configured
- [ ] CSV export tested with accountant's preferred format
- [ ] Bilingual rendering verified for Arabic numerals, alignment, fonts

## Risks if this is wrong

- **Fines** for non-compliant receipts during fiscal audit.
- **VAT recovery problems** if input/output VAT not properly tracked.
- **Client trust** if professional / regulated services issue informal-looking receipts.
- **Cash flow tracking** broken if numbering is unreliable.

This is one of the few areas where "good enough" is not good enough. The accountant signs off.

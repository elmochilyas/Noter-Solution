# Accessibility & Internationalization Standards

## Accessibility target

**WCAG 2.1 Level AA** across the entire site.

- Lighthouse accessibility score: **100** required.
- axe-core CI checks must pass with zero violations.
- Keyboard-only navigation through every flow must work without rat-traps.

## Keyboard navigation

- Every interactive element reachable by Tab in a logical order.
- Visible focus ring on every focusable element — 2px brass ring offset 2px from element.
- `Esc` closes any modal, drawer, or chatbot widget.
- `Enter` and `Space` activate buttons (both required for non-`<button>` elements with `role="button"`).
- Skip-to-content link at the top of every page.
- Trapped focus inside modals and drawers; restored on close.

## Semantic HTML

- One `<h1>` per page.
- Heading levels never skipped (no `<h1>` → `<h3>`).
- `<main>`, `<nav>`, `<header>`, `<footer>`, `<aside>` used correctly.
- Lists are `<ul>` / `<ol>`, not `<div>`s.
- Tables use `<th>` with `scope` and `<caption>`.
- Forms have `<label>` for every input. No placeholder-as-label.
- Use `<button>` for actions, `<a>` for navigation. Never `<div onclick>`.

## ARIA

- Add ARIA only when semantic HTML can't express the relationship.
- `aria-label` on icon-only buttons.
- `aria-live="polite"` on toast / status regions.
- `aria-live="assertive"` reserved for errors only.
- `aria-current="page"` on the active nav link.
- `aria-expanded` on accordion / disclosure toggles.
- `aria-describedby` linking form fields to their error messages.

## Color contrast

- Text on background: minimum 4.5:1 (body), 3:1 (large text ≥ 18.66px bold or 24px regular).
- UI components and graphical objects: minimum 3:1.
- Don't rely on color alone — always pair with an icon, text, or pattern.

Verified palette ratios (vs. Parchment `#F7F3EC`):
- Ink `#0E1B2C` — 14.2:1 ✓
- Brass `#B68A3E` — 3.6:1 (only large text or icon use)
- Stone 700 `#3A3A38` — 10.3:1 ✓
- Stone 500 `#6B6660` — 5.0:1 ✓ (body text)
- Stone 300 `#C9C3B8` — 1.5:1 (decorative only, never text)

## Forms

- Every input has an associated `<label>`. Floating labels are fine as long as the `for`/`id` link exists.
- Required fields marked with both `required` attribute and visible asterisk + `aria-required="true"`.
- Errors displayed inline, with `aria-describedby` linking the input to the error.
- Use `autocomplete` attributes (`name`, `email`, `tel`, `street-address`, etc.).
- Use `inputmode` to surface the right mobile keyboard (`numeric`, `tel`, `email`).
- Group related fields with `<fieldset>` + `<legend>`.

## Media

- Every `<img>` has `alt` text (empty `alt=""` for decorative only).
- Sana's portrait alt: `"Portrait de Maître Sana Bouhamidi, adoul authentifiée à Agadir"` (FR) / Arabic equivalent.
- Office photos: described.
- Decorative ornaments (zellige): `alt=""` + `role="presentation"`.
- Videos (if any) require captions and a transcript.

## Motion and animation

- Respect `prefers-reduced-motion: reduce` — disable scroll reveals, page transitions, decorative motion.
- Never auto-play video or audio.
- No flashing content (seizure risk).

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
```

## Internationalization

### Supported languages

- **Arabic (ar)** — primary, default for new visitors from `.ma` or with `Accept-Language: ar*`
- **French (fr)** — secondary

### Language detection priority

1. URL prefix (`/ar/...`, `/fr/...`)
2. Cookie `locale` (set when user toggles)
3. Browser `Accept-Language`
4. Default: `ar`

### URL structure

- Every page exists at both `/ar/...` and `/fr/...`.
- Root `/` redirects to the user's resolved locale.
- `hreflang` link tags in `<head>` for every page:
  ```html
  <link rel="alternate" hreflang="ar" href="https://sana-bouhamidi.ma/ar/services/famille" />
  <link rel="alternate" hreflang="fr" href="https://sana-bouhamidi.ma/fr/services/famille" />
  <link rel="alternate" hreflang="x-default" href="https://sana-bouhamidi.ma/ar/services/famille" />
  ```

### Translation files

```
resources/lang/
├── ar/
│   ├── auth.php
│   ├── booking.php
│   ├── chatbot.php
│   ├── common.php
│   ├── email.php
│   ├── faq.php
│   ├── nav.php
│   ├── notifications.php
│   ├── plans.php
│   ├── services.php
│   └── validation.php
└── fr/  (same files)
```

### Translation key conventions

- `feature.section.purpose` — three dot-separated parts.
- Always nest by feature.
- Use sentence-style keys, not technical-style:
  - Good: `booking.confirmation.title`
  - Bad: `booking_confirmation_title_h1_v2`
- Reuse common keys via `common.php`:
  - `common.actions.book` — "Prendre rendez-vous" / "احجز موعدًا"
  - `common.actions.cancel` — "Annuler" / "إلغاء"
  - `common.actions.continue` — "Continuer" / "متابعة"

### Database-stored content

Content that Sana edits via Filament (service pages, FAQ, plan descriptions, chatbot responses) is stored in DB using `spatie/laravel-translatable`:

```php
$service->setTranslations('title', [
    'ar' => 'التوثيق الأسري',
    'fr' => 'Authentification familiale',
]);
```

Filament forms have a language tab switcher for entering both translations.

### RTL handling

- The `<html>` tag has `dir="rtl"` and `lang="ar"` on Arabic pages, `dir="ltr"` and `lang="fr"` on French pages.
- Tailwind logical properties used everywhere:
  - `ms-` / `me-` instead of `ml-` / `mr-`
  - `ps-` / `pe-` instead of `pl-` / `pr-`
  - `start-` / `end-` instead of `left-` / `right-`
- `text-start` / `text-end` instead of `text-left` / `text-right`.
- Icons that have directional meaning (arrows, chevrons) flip via `rtl:scale-x-[-1]` or have language-specific variants.
- Use the `tailwindcss-rtl` plugin to automate most of this.

### Mixed-direction content

- Phone numbers, emails, URLs: keep LTR via `<bdi dir="ltr">` even inside RTL paragraphs.
- Numbers in financial/legal contexts: Latin numerals (`123` not `١٢٣`) for clarity and consistency with receipts and legal documents — this is intentional for this practice.
- Dates: Latin numerals everywhere. Month names use the resolved locale.

### Typography per language

| Use | French (LTR) | Arabic (RTL) |
|---|---|---|
| Display headings | Fraunces | Reem Kufi |
| Body / UI | Inter | IBM Plex Sans Arabic |

Loaded via `@font-face` with `font-display: swap`. Subsets matched to language (Latin Extended for FR, Arabic + Latin for AR).

### Pluralization

- French has 2 forms (singular, plural).
- Arabic has 6 forms (zero, one, two, few, many, other).
- Use Laravel's `trans_choice()` with proper pluralization rules:
  ```php
  // resources/lang/ar/booking.php
  'remaining' => '{0}لا توجد مقاعد متبقية|{1}مقعد واحد متبقي|{2}مقعدان متبقيان|[3,10]:count مقاعد متبقية|[11,*]:count مقعدًا متبقيًا',
  ```

### Dates and times

- Storage: UTC in DB.
- Display: `Africa/Casablanca` (UTC+1, no DST).
- Format via `Carbon::createFromFormat()` with locale-aware formatters:
  - French: `mardi 14 mars 2026 à 10:30`
  - Arabic: `الثلاثاء 14 مارس 2026 في الساعة 10:30`
- Times always 24-hour format (avoids confusion across locales).

### Numbers and currency

- Currency: MAD only.
- Format:
  - French: `250,00 MAD`
  - Arabic: `250.00 درهم`
- Use `\NumberFormatter` from intl:
  ```php
  $formatter = new \NumberFormatter('fr_MA', \NumberFormatter::CURRENCY);
  echo $formatter->formatCurrency(250, 'MAD');
  ```

### Language switcher behavior

- Visible in the header on every page.
- Preserves current page when switching (`/fr/services/famille` ↔ `/ar/services/famille`).
- Sets the `locale` cookie for 1 year.
- Updates `<html lang>` and `<html dir>` immediately.

### QA checks

- Every translation file must have the same key set in both languages — CI script verifies.
- No missing translation falls back to the key itself (red flag in dev mode); fall back to the other language on prod with an error log.
- Manual review of Arabic translations by a native speaker before launch.

## Testing accessibility & i18n

- `axe-core` runs in CI on every page (via Dusk).
- Manual screen reader test (NVDA on Windows, VoiceOver on macOS) on the booking flow before launch.
- Manual keyboard-only test on the booking flow before launch.
- RTL visual review of every page before launch.
- Native Arabic speaker reviews all displayed Arabic copy.

## Common pitfalls to avoid

- ❌ Hardcoded text in Blade — always `{{ __('booking.title') }}`.
- ❌ Hardcoded directional CSS (`ml-4`, `text-left`) — use logical (`ms-4`, `text-start`).
- ❌ Icons whose meaning depends on direction without RTL flip.
- ❌ Forgetting `lang` attribute on the `<html>` tag.
- ❌ Form labels not associated with inputs.
- ❌ Using color as the only error indicator.
- ❌ Tiny tap targets — minimum 44×44 px on touch surfaces.
- ❌ Untranslated alt text and aria labels.

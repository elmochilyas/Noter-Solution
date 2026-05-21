# Design System

Canonical tokens and component conventions for the Sana Bouhamidi site. **This is the source of truth for visual values.** Everything else (Stitch outputs, ad-hoc choices in PRs) is downstream.

## Aesthetic direction

Editorial-luxury × Legal-authority.

- Sober, restrained, confident.
- Reads like a private practice's letterhead — not like a tech startup or law-firm marketing site.
- Negative space is part of the design.
- One ornamental motif, used sparingly (Moroccan geometric / calligraphic accents).
- Never gimmicky, never aggressive, never bright.

## Color tokens

Stored in `tailwind.config.js` under `theme.extend.colors`.

| Token | Hex | Use |
|---|---|---|
| `ink` | `#0E1B2C` | Primary text, dark surfaces, headings |
| `parchment` | `#F7F3EC` | Page background |
| `brass-500` | `#B68A3E` | Brand accent, primary CTAs, links |
| `brass-600` | `#9A7430` | Brass hover / pressed |
| `brass-400` | `#CDA45B` | Brass tint, subtle highlights |
| `brass-100` | `#F2E8D4` | Brass background, very subtle |
| `stone-900` | `#1F1E1B` | Almost-black for high contrast |
| `stone-700` | `#3A3A38` | Strong body text |
| `stone-500` | `#6B6660` | Default body text on parchment (5.0:1) |
| `stone-300` | `#C9C3B8` | Decorative dividers (NOT text) |
| `stone-200` | `#E2DDD2` | Card borders, subtle dividers |
| `stone-100` | `#EFEAE0` | Hover backgrounds, very subtle |
| `success` | `#3B6B4A` | Confirmations, success states |
| `warning` | `#9A6A1B` | Warnings (uses brass-family tones) |
| `danger` | `#8C2A2A` | Errors, destructive actions |
| `info` | `#2C4A6B` | Informational accents |

### Contrast (vs. `parchment` background)

- `ink` 14.2:1 ✓
- `stone-700` 10.3:1 ✓
- `stone-500` 5.0:1 ✓ (body text)
- `brass-500` 3.6:1 (large text only or icons; never small body)
- `stone-300` 1.5:1 (decorative only)

Never use `brass-500` for body text. For "primary text on dark surface" use `parchment` on `ink`.

### Dark surfaces

Dark backgrounds use `ink` with text in `parchment` or `brass-400`. Cards on parchment use white surface (`#FFFFFF`) or `stone-100`.

## Typography

Two variable fonts per locale.

### French (LTR)

| Use | Font | Weight | Variation |
|---|---|---|---|
| Display | Fraunces | 500–700 | `wdth: 100, opsz: 144, soft: 0` (high contrast) |
| Body / UI | Inter | 400–600 | regular |

### Arabic (RTL)

| Use | Font | Weight |
|---|---|---|
| Display | Reem Kufi | 500–700 |
| Body / UI | IBM Plex Sans Arabic | 400–600 |

Fonts loaded via local `@font-face` with `font-display: swap`. Subset:
- FR: Latin Extended + minimum Arabic for fallback safety.
- AR: Arabic + basic Latin.

Preload only the current locale's display weight on each page (LCP optimization).

### Type scale (8-point grid)

Use Tailwind's `text-` utilities mapped to these:

| Token | px | line-height | Use |
|---|---|---|---|
| `text-xs` | 12 | 16 | Captions, legal small print |
| `text-sm` | 14 | 20 | Secondary text, table cells |
| `text-base` | 16 | 24 | Body default |
| `text-lg` | 18 | 28 | Lede, large body |
| `text-xl` | 20 | 28 | Card titles |
| `text-2xl` | 24 | 32 | Section subheads |
| `text-3xl` | 30 | 36 | Section headings |
| `text-4xl` | 36 | 44 | Page headings (H1 mobile) |
| `text-5xl` | 48 | 56 | Hero headings (H1 desktop) |
| `text-6xl` | 60 | 64 | Display only (rare) |

Display tier (H1, hero) uses Fraunces (FR) / Reem Kufi (AR).
Heading tier (H2-H4) uses the same display fonts but lower weight.
Body uses Inter / IBM Plex Sans Arabic.

### Line length

Body paragraphs cap at `max-w-prose` (~70ch). Arabic readability tolerates slightly longer.

## Spacing

8-point grid. Use Tailwind defaults (`p-2` = 8px, `p-4` = 16px, etc.).

Page horizontal padding:
- Mobile: `px-4` (16)
- Tablet: `px-6` (24)
- Desktop: `px-8` (32) with a max-width container `max-w-7xl mx-auto`.

Section vertical rhythm:
- Tight section: `py-12 md:py-16`
- Standard section: `py-16 md:py-24`
- Spacious section: `py-24 md:py-32`

## Borders and radii

| Token | Use |
|---|---|
| `rounded-none` | Default for blocks of text and editorial cards |
| `rounded-sm` (2px) | Subtle softening on small elements |
| `rounded-md` (6px) | Form inputs, buttons |
| `rounded-lg` (8px) | Cards, modals |
| `rounded-full` | Pills, avatars, icon buttons |

Default border: `border-stone-200`.
Strong border: `border-stone-700`.
Brass border (selected / featured): `border-brass-500`.

## Shadows

Editorial — restrained, never dramatic.

| Token | Use |
|---|---|
| `shadow-sm` | Subtle card lift |
| `shadow-md` | Sticky header on scroll |
| `shadow-lg` | Modals, dropdowns |
| `shadow-brass` | Custom: `0 0 0 1px rgba(182,138,62,0.3)` for focus rings on brass elements |

## Motion

Subtle, purposeful, never bouncy.

| Token | Duration | Easing |
|---|---|---|
| `transition-fast` | 120ms | `cubic-bezier(0.4, 0, 0.2, 1)` |
| `transition-normal` | 200ms | `cubic-bezier(0.4, 0, 0.2, 1)` |
| `transition-slow` | 320ms | `cubic-bezier(0.4, 0, 0.2, 1)` |

Honor `prefers-reduced-motion`. Disable scroll reveals and decorative motion under that preference.

## Breakpoints

Tailwind defaults:

| Token | Width |
|---|---|
| `sm` | 640px |
| `md` | 768px |
| `lg` | 1024px |
| `xl` | 1280px |
| `2xl` | 1536px |

Design mobile-first. Layouts are single-column under `md`, two-column at `md+`.

## Iconography

- **Lucide icons** (open source, MIT). Imported per-page, not as a bundle.
- Default size: 20px in body, 24px in headers, 16px in dense UI (tables, chips).
- Stroke width: 1.5px (slightly lighter than Lucide default 2px) — matches the editorial feel.
- Color: inherits from text; `brass-500` for accent icons.

## Components — conventions

### Buttons

Three tiers. Each tier has the same shape — only color/weight differ.

**Primary** — main CTA per screen, max one per visual region:
- Background `brass-500`, hover `brass-600`, text `parchment`.
- Padding: `px-6 py-3` (desktop) / `px-5 py-3` (mobile).
- Rounded `md`.
- Focus: `ring-2 ring-brass-500 ring-offset-2 ring-offset-parchment`.

**Secondary** — supporting action:
- Background transparent, border `ink`, text `ink`.
- Hover: background `ink`, text `parchment`.
- Same padding/radius as primary.

**Tertiary / text** — low-emphasis:
- No background, no border. Text `brass-500` with subtle underline on hover.
- Padding `px-2 py-1` for inline use.

**Destructive variant:** any tier with `danger` substituted for `brass`.

**Icon-only buttons:**
- 40×40 tap target on mobile (`size-10`).
- `aria-label` required.
- Rounded `full` for circular, `md` for square.

### Form fields

- Label above the input, `text-sm font-medium text-stone-700`, `mb-1.5`.
- Input: full-width, `h-11` (44px tap target), `rounded-md`, `border-stone-200`, `bg-white`, `px-3`, `text-base`.
- Focus: `ring-2 ring-brass-500 ring-offset-0 border-brass-500`.
- Error: `border-danger ring-danger`. Error message below, `text-sm text-danger`, with `aria-describedby` link.
- Required marker: visual asterisk + `aria-required="true"`.
- Placeholder: `text-stone-500`. Don't use placeholder as label.

### Cards

- Background `white` (on parchment) or `stone-100` for subtle nesting.
- Border `stone-200`, `rounded-lg`, `shadow-sm`.
- Internal padding `p-6` (desktop) / `p-5` (mobile).
- Recommended / featured cards: brass border `border-brass-500`, brass-tinted background `bg-brass-100`.

### Modals / drawers

- Backdrop `bg-ink/60 backdrop-blur-sm`.
- Modal `bg-parchment rounded-lg shadow-lg max-w-md mx-auto`.
- Mobile: bottom-sheet variant — full-width, `rounded-t-lg`, slides up from bottom.
- Trapped focus while open. Escape to close. Focus restored to trigger on close.

### Toasts / banners

- `bg-success/10 border-success text-success` for confirmations.
- `bg-danger/10 border-danger text-danger` for errors.
- `bg-info/10 border-info text-info` for info.
- Auto-dismiss after 5s; pause on hover.
- `aria-live="polite"` for info/success; `aria-live="assertive"` for errors.

### Tables

- Striping: alternating `bg-white` and `bg-stone-100`.
- Header: `bg-ink text-parchment` or `bg-stone-100 text-ink font-semibold`, sticky on scroll.
- Cells: `px-4 py-3`, `text-sm`.
- Mobile: collapse to stacked card view at `< md`.

## Ornaments

A single decorative motif: a thin brass-toned geometric divider inspired by Moroccan zellige and Islamic geometric patterns. Used:

- Once on the hero (subtle accent above or below the H1)
- As a section divider between major page sections (not between every block)
- On the receipt PDF header

**Never** use as a background pattern repeating across whole sections. Always as a small accent.

SVG asset path: `/public/images/ornaments/divider.svg`.

## Imagery

- **Portraits**: editorial, single subject, neutral background. Sana's portrait shot specifically for the site, not a casual photo.
- **Office photos**: composed, low contrast, no harsh lighting.
- **Decorative**: zellige textures, calligraphic marks — used sparingly as accents.
- **Forbidden**: stock photos of "professionals shaking hands", "diverse team", "business meeting", emoji-heavy graphics, cartoon illustrations.
- **All images**: WebP first, AVIF when supported, JPEG fallback. Sized via `srcset` for responsive delivery.

## Loading states

| Surface | Treatment |
|---|---|
| Button (server action in flight) | Replace label with spinner + keep dimensions stable |
| Card grid | Skeleton blocks with `animate-pulse` (or static under reduced motion) |
| Full-page load | Top progress bar in `brass-500` (Livewire built-in or custom) |
| Inline (e.g. search debouncing) | Subtle `bg-stone-100` flash + spinner icon |

## Empty states

Every list view defines an empty state:
- Modest icon
- Short heading
- One sentence explaining what would populate the view
- One CTA back to the action that would create data (e.g. "Prendre rendez-vous")

## Error states

| Type | Treatment |
|---|---|
| Form field error | Inline below field, `text-danger`, with `aria-describedby` |
| Page-level error | Toast banner at top, dismissible |
| Full-page error (4xx/5xx) | Centered, calm, with link back to home and contact info |
| Permission denied | Friendly, no scary stack traces in prod |

## Filament theme overrides

Filament's default theme is overridden to match these tokens. See `phases/01-foundation.md` task 8 + 12. Mapping:

- Filament `primary` → `brass-500`
- Filament `gray` → `stone-*` palette
- Filament `success`/`warning`/`danger`/`info` → tokens above
- Filament font → Inter (UI), Fraunces (accents on detail page headings only)

Dark mode disabled in v1.

## RTL adaptation

All component conventions above work in RTL with logical Tailwind utilities. Specifically:

- `ms-`, `me-`, `ps-`, `pe-`, `start-`, `end-`, `text-start`, `text-end` instead of LTR-only variants.
- Icons with directional meaning (arrows, chevrons) flip via `rtl:scale-x-[-1]` or use `lucide-react`'s direction-aware variants.
- Layout (flex direction, grid placement) inherits browser direction automatically.
- The `tailwindcss-rtl` plugin handles common automatic flips for legacy utility names where they're unavoidable.

## Component build path

Reusable components live in:

- `resources/views/components/` — Blade components (e.g. `<x-button>`, `<x-card>`, `<x-input>`, `<x-modal>`)
- `app/View/Components/` — corresponding PHP classes when logic is needed

The agent extracts a component when the same pattern appears more than twice. Component naming matches Blade conventions (`kebab-case`).

## Versioning

Changes to tokens or component conventions require:
1. Update this doc.
2. Update `tailwind.config.js` (and Filament theme overrides if applicable).
3. Open a follow-up to regression-test affected surfaces.
4. PR description must list affected surfaces.

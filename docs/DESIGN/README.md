# Design — How to Use This Folder

This folder contains the visual design assets for the Sana Bouhamidi website. Everything here is **input to the agent**, not the final code.

## What's here

```
DESIGN/
├── README.md              ← you are here
├── design-system.md       canonical tokens (color / type / spacing / motion / components)
├── stitch-prompts.md      the 27 Stitch prompts used to generate the screens
├── screens-index.md       lookup table: feature → Stitch prompt → HTML artifact → Blade path
└── stitch-output/
    ├── README.md          manifest of the 27 expected HTML files
    └── <screen>.html      Stitch-generated HTML mockups
```

## Source-of-truth hierarchy

When implementing UI, sources rank in this order:

1. **`DESIGN/design-system.md`** — tokens, components, conventions. **Authoritative for visual values.** If a Stitch output uses a different color, the design system wins.
2. **`stitch-output/<screen>.html`** — layout, hierarchy, content structure for the specific screen. **Authoritative for layout and content blocks.**
3. **`STANDARDS/accessibility-i18n.md`** — RTL, semantic HTML, ARIA, keyboard. **Authoritative for accessibility and i18n behavior.**
4. **`FEATURES/<feature>.md`** — interactions, states, validation, edge cases. **Authoritative for behavior.**

If two sources disagree, the higher-ranked one wins. Document the resolution in the PR description so the lower-ranked source can be updated next.

## How the agent uses Stitch outputs

**Stitch outputs are reference, not source.** Do not copy the HTML or inline CSS verbatim into the codebase.

For each UI task, the agent:

1. Opens `DESIGN/screens-index.md`, finds the row matching the route / feature / screen.
2. Reads the prompt in `stitch-prompts.md` to understand the intent.
3. Reads the HTML artifact in `stitch-output/<screen>.html` to see the resolved layout, copy structure, and component hierarchy.
4. Reads `design-system.md` for tokens and component conventions.
5. Writes a Blade view + Livewire component (or Filament resource) that:
   - Uses Tailwind utility classes mapped to the design tokens.
   - Uses **logical CSS properties** (`ms-`, `me-`, `ps-`, `pe-`, `start-`, `end-`, `text-start`, `text-end`) instead of the LTR-only utilities Stitch produces.
   - Uses translation keys from `resources/lang/{locale}/*.php` instead of the hardcoded copy in the HTML.
   - Wraps every interactive element in semantic HTML with proper labels, alt text, and ARIA.
   - Implements the states (hover, focus, disabled, loading, error, empty) the Stitch static mockup can't show.

## Important: Stitch outputs are LTR-only and French-only

Stitch generates left-to-right, French-language mockups. The agent **must** transform output into a bidirectional, bilingual implementation:

- Replace every directional utility (`ml-`, `mr-`, `pl-`, `pr-`, `text-left`, `text-right`, `left-`, `right-`) with its logical equivalent.
- Replace every literal string with a `__('feature.section.purpose')` call.
- Verify the Arabic version renders RTL with no broken layouts before declaring the task done.
- Icons that have directional meaning (arrows, chevrons) get RTL flipping via `rtl:scale-x-[-1]` or use language-specific variants.

See `STANDARDS/accessibility-i18n.md` for the full rules.

## When to follow Stitch strictly

- Overall page composition (hero, sections, footer relative positions)
- Component hierarchy and content blocks
- Visual rhythm and density
- Decorative ornaments and accents
- Typographic scale within a screen

## When the agent should deviate

- **Accessibility:** add what Stitch can't (ARIA, focus rings, skip links, keyboard interactions, screen-reader text).
- **Responsiveness:** Stitch often shows one breakpoint; the agent fills in the rest per `design-system.md` breakpoint conventions.
- **States:** loading, error, empty, disabled — the agent designs these from the system conventions, not Stitch.
- **RTL:** the agent **must** adapt for RTL; Stitch doesn't.
- **Brand-prohibited patterns:** if a Stitch output shows something that violates `COMPLIANCE/notary-rules.md` (testimonials, superlatives, "limited offers"), **drop it**, don't ship it. Flag in the PR.

## When there is no Stitch output for a screen

Some screens (error pages, edge-case modals, mobile menu, admin Filament internals) have no Stitch output. The agent designs them from scratch using `design-system.md` tokens and conventions. The agent should never block on missing Stitch outputs — only the 27 listed in `screens-index.md` are expected.

## Regenerating a Stitch output

If a screen needs to be redesigned:

1. Locate the prompt in `stitch-prompts.md` for the screen.
2. Edit the prompt if intent has changed.
3. Run it through Stitch.
4. Save the new HTML over the existing file in `stitch-output/`.
5. Update `screens-index.md` if mapping changed.
6. Update or open a task to rebuild the corresponding Blade view.

## What the agent does NOT do

- Copy Stitch HTML/CSS into the repo as-is.
- Use the inline styles or class names Stitch invents — translate to the design system's Tailwind utilities.
- Skip RTL adaptation because Stitch didn't include it.
- Skip translation key wiring because Stitch hardcoded French.
- Treat Stitch's typography and color choices as authoritative when they contradict `design-system.md`.

# Stitch Output Manifest

This folder is where the HTML files you generated from Stitch go.

## What to drop here

The 27 Stitch outputs, named per the table in `../screens-index.md`. Expected filenames:

```
01-home.html
02-about.html
03-services-overview.html
04-service-detail.html
05-consultation-plans.html
06-booking-slot.html
07-booking-info.html
08-booking-payment.html
09-booking-success.html
10-payment-failed.html
11-faq.html
12-contact.html
13-legal-template.html
14-404.html
15-chatbot-widget.html
16-portal-home.html
17-portal-booking-detail.html
18-admin-login.html
19-admin-dashboard.html
20-admin-calendar.html
21-admin-availability.html
22-admin-bookings-list.html
23-admin-clients.html
24-admin-content.html
25-admin-chatbot.html
26-admin-settings.html
```

> Note: the original Stitch prompt list has 27 entries (see `../stitch-prompts.md`). The numbering above stops at 26 because screen 14 in the prompt list is "404" and the admin section starts at 18. Use the index in `../screens-index.md` to confirm the right filename for any given prompt number — the index is the canonical mapping.

## What if I only have some of them

That's fine. Drop what you have. For any missing file, the agent will:
1. Read the prompt for that screen in `../stitch-prompts.md`.
2. Design the screen from the prompt + `../design-system.md` directly, without HTML reference.
3. Note in the PR description that no Stitch HTML was present for the screen.

You can always regenerate any missing screen later and drop it in — no migration needed.

## What if my filenames don't match

Match them. The agent looks them up by the exact path in `../screens-index.md`. If yours are named differently (e.g. `home-v3-final.html`), either:
- Rename to match, or
- Edit the `Stitch HTML` column in `../screens-index.md` to your filename

The first option is cleaner.

## What if I have multiple variants

Pick one canonical version per screen. Keep variants in a separate `archive/` folder if you want them — anything not at the top level of `stitch-output/` is ignored by the agent.

## Format expectations

- HTML files (Stitch's default export).
- Self-contained — inline styles or `<style>` blocks are fine.
- No external image URLs that require network access; if Stitch embeds Unsplash links etc., they're fine for visual reference but the implementation should use the project's own assets.
- LTR and French — see `../README.md` for how the agent handles RTL + Arabic.

## Currently in this folder

(Drop your files here. This folder is empty until you do.)

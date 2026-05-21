# Stitch Prompts — Sana Bouhamidi Notary Website

## How to use this file

For every screen you generate in Stitch, paste the **Base Style Block** first, then the **Screen Prompt** for that specific page. The base block carries the design system; the screen prompt carries the layout and content.

Generate the **French / LTR version** of every screen. The Arabic / RTL version is handled in code (mirrored layout, font swap) — no need to regenerate.

Iterate one screen at a time. Stitch produces better results with focused prompts than with multi-screen requests.

---

## Base Style Block (prepend to every prompt)

```
Design a screen for a Moroccan notary's professional website. The brand is "editorial luxury meets legal authority" — refined, confident, expensive-looking. Think Aesop, Patek Philippe, Sotheby's Realty. Generous whitespace, no gradients, no glassmorphism, no stock photography of handshakes or gavels.

Palette:
- Background: Parchment #F7F3EC
- Elevated surfaces: Ivory #FBF9F4
- Primary text and dark surfaces: Ink #0E1B2C
- Accent: Brass #B68A3E (used sparingly — once or twice per screen)
- Hairlines / borders: Stone #C9C3B8 (1px)
- Muted text: Stone #6B6660

Typography:
- Headings: Fraunces (serif, editorial), tighter letter-spacing -0.02em on display sizes
- Body and UI: Inter (sans-serif)
- Plenty of breathing room around headings

Layout:
- 1240px max content width with generous side padding
- 8-point spacing grid (8, 16, 24, 32, 48, 64, 96, 128)
- Section vertical rhythm: 96–128px on desktop

Components:
- Primary button: brass background #B68A3E, ivory text, 48px tall, 6px radius, no shadow
- Secondary button: 1px ink border, ink text, transparent background
- Cards: ivory bg, 1px stone border, 12px radius. Service cards have a 3px brass top border
- Forms: 48px input height, 6px radius, hairline stone borders, ink on focus with a 2px brass ring
- Icons: Lucide-style line icons, 1.5px stroke, ink color

Language: French. The site is bilingual FR/AR but design only the French LTR version here.
```

---

# Public pages

## 1. Home (`/`)

```
Build the home page for Sana Bouhamidi, a notary (adoul) in Agadir.

Sections in order:

1. Sticky header on parchment with a 1px stone bottom border on scroll. Left: serif wordmark "Sana Bouhamidi" with a thin brass underline beneath. Center: nav links (À propos · Services · Consultation · FAQ · Contact). Right: language switcher "FR ⌄ AR" as plain text, then a brass "Prendre rendez-vous" button.

2. Hero — full-width parchment, 120px top padding. Two columns. Left (60%): small uppercase eyebrow "Adoul authentifiée · Agadir" in brass. Large Fraunces headline "Authentification de vos actes juridiques, familiaux, immobiliers et financiers." Below it, a 2-line Inter sub-paragraph: "Cabinet de Maître Sana Bouhamidi à Bensergao, à proximité du Tribunal de Première Instance d'Agadir." Then two CTAs side by side: primary brass "Prendre rendez-vous" and secondary ink-outlined "Discuter avec l'assistant". Right (40%): an editorial portrait of the notary, soft natural light, neutral background, framed in a 12px radius card with a 1px stone border.

3. Trust strip — thin parchment section with 4 inline credentials separated by hairline vertical dividers: "Diplômée de l'Institut Supérieur de la Magistrature" · "Plus de 10 ans d'expérience" · "Cabinet à Bensergao, Agadir" · "Confidentialité garantie". Small brass line icons before each.

4. Services overview — eyebrow "Services" in small caps, then Fraunces heading "Nos domaines d'intervention". Below: a 2×2 grid of four service cards (ivory, 3px brass top border, 12px radius). Each has a small Lucide icon top-left, a Fraunces heading, two lines of description, and a "En savoir plus →" link in brass. Titles: "Authentification familiale", "Authentification immobilière", "Authentification financière", "Contrats particuliers".

5. Why choose us — heading "Pourquoi nous confier vos actes" in Fraunces. Four columns below, each with a large brass Fraunces numeral (01, 02, 03, 04), then a one-line title, then 2 lines of body. Topics: "Expertise & professionnalisme", "Respect du cadre légal", "Confidentialité absolue", "Précision et rapidité".

6. Booking CTA banner — full-width ink background, ivory text. Centered serif headline "Réservez votre consultation en quelques minutes", one supporting line, brass "Prendre rendez-vous" button.

7. Mini-FAQ — title "Questions fréquentes" in Fraunces. Five collapsible accordion items with hairline dividers between them. Each row: Fraunces question on the left, brass chevron on the right. Bottom: "Voir toutes les questions →" link in brass.

8. Footer — ink background, ivory text. Three columns: practice info (name, address, three phone numbers, email), quick links column, and a faint brass zellige line ornament. Bottom row: copyright, language switcher, optional social link.

Overall: the hero should feel calm, not crowded. Generous space is the design.
```

---

## 2. About (`/about`)

```
Design the About page for Sana Bouhamidi, an adoul in Agadir.

Sections:

1. Header (same as home).

2. Page intro — parchment, 96px top padding. Centered eyebrow "À propos" in brass small caps. Fraunces heading "Une pratique du droit fondée sur la rigueur et la confiance." Below, a single paragraph (max 720px width) introducing the notary.

3. Portrait + bio — two columns. Left (40%): large editorial portrait in a 12px radius card. Right (60%): Fraunces sub-heading "Parcours". A short biographical paragraph mentioning her graduation from l'Institut Supérieur de la Magistrature, her years of practice, her commitment to clients. Then Fraunces sub-heading "Domaines de compétence" followed by a list of 4 bullet items in Inter (each with a small brass line icon) — Famille, Immobilier, Finance, Contrats.

4. Values block — full-width ivory background. Heading "Engagements". Three columns, each with a brass line icon, a Fraunces title and two lines of body: "Confidentialité", "Conformité légale", "Disponibilité".

5. Office photos — three architectural photos of the office in a horizontal strip (no people in the frame), wide aspect ratio, 12px radius.

6. Credentials block — heading "Reconnaissance professionnelle". A small horizontal list of credentials in plain text with brass bullet markers (registration body, qualification, court affiliation).

7. CTA banner (same style as home).

8. Footer.
```

---

## 3. Services overview (`/services`)

```
Design the services index page.

Sections:

1. Header.

2. Page intro — eyebrow "Services" in brass small caps, Fraunces heading "Authentification de tous vos actes". A two-line paragraph below.

3. Services grid — four large rows, each a horizontal card spanning the content width. Each row alternates image side (left/right). Each row contains: a Fraunces heading, a 2-line description, a list of 3–4 transaction types as inline tags (small ink-outlined pills with rounded corners), and a brass "Découvrir →" link. Rows:
   - "Authentification familiale" — mariage, divorce, succession, filiation
   - "Authentification immobilière" — ventes, donations, échanges
   - "Authentification financière" — reconnaissance de dette, prêts, sociétés
   - "Contrats particuliers" — sociétés, associations, procurations

4. Process strip — heading "Comment se déroule une consultation". Three steps shown horizontally, each with a brass numeral (01/02/03), a Fraunces title, two lines of body: "Prise de rendez-vous", "Consultation et conseil", "Authentification de l'acte". Subtle hairline connector between steps.

5. Booking CTA banner.

6. Footer.
```

---

## 4. Service detail (template — for the 4 service pages)

```
Design a service detail page. Use "Authentification familiale" as the content example; the same layout serves the three other services.

Sections:

1. Header.

2. Breadcrumb in muted stone — "Accueil / Services / Authentification familiale".

3. Page hero — left (60%): eyebrow "Service" in brass small caps, Fraunces heading "Authentification familiale", a paragraph describing the scope. Right (40%): a sticky summary card (ivory, brass top border, 12px radius) containing "À partir de — 250 MAD", a duration line "Consultation 30–60 min", and a primary brass "Prendre rendez-vous" button.

4. Transactions covered — heading "Actes traités". A grid of 2 columns × 3 rows, each item being a small card with a Lucide icon, a Fraunces title and one line of body. Examples: "Mariage", "Divorce", "Succession et fara'id", "Déclarations de filiation", "Acte de répudiation", "Inventaire successoral".

5. Required documents — heading "Documents nécessaires". A checklist of 6–8 items with small brass checkmark icons. Below: a brass-outlined "Télécharger la liste (PDF) ↓" link.

6. Process — heading "Déroulement". Four steps in a horizontal flow with hairline connectors: "Prise de contact", "Préparation du dossier", "Consultation", "Authentification".

7. FAQ specific to this service — 4 accordion items.

8. CTA banner.

9. Footer.
```

---

## 5. Consultation plans (`/consultation`)

```
Design the consultation plans page.

Sections:

1. Header.

2. Page intro — eyebrow "Consultation" in brass, Fraunces heading "Choisissez la formule adaptée à votre situation." A two-line subtext.

3. Plans grid — four cards in a row (or 2×2 on smaller screens). Each card: ivory, 1px stone border, 12px radius. The recommended plan ("Consultation au cabinet") has a 3px brass top border and a small "Recommandée" brass tag in the top right.

Each card contains in this order:
   - Fraunces plan name
   - Price in large Fraunces: "0 MAD" / "250 MAD" / "400 MAD" / "800 MAD"
   - Duration line "10 min" / "30 min" / "45–60 min" / "90 min"
   - Format line "En ligne" / "En ligne" / "Au cabinet" / "Au cabinet ou en ligne"
   - A hairline divider
   - 4 bullet features with brass check icons
   - A primary brass "Réserver" button (or "Discuter d'abord" on the free plan)

Plan names:
   - "Orientation gratuite"
   - "Consultation standard en ligne"
   - "Consultation au cabinet"
   - "Étude approfondie de dossier"

4. "Help me choose" block — full-width ivory section. Centered Fraunces heading "Vous hésitez ?", a paragraph, and a brass "Discuter avec l'assistant" button that opens the chatbot.

5. Cancellation policy — small section, heading "Politique d'annulation" in Fraunces, a compact paragraph in muted stone explaining the rules (24h full refund, 50% within 24h, none within 2h).

6. Footer.
```

---

## 6. Booking — Step 1 (`/book` — calendar)

```
Design the first step of the booking flow: selecting a slot.

Layout:

1. Header (with a slim progress bar below it: 3 steps, "1. Créneau · 2. Informations · 3. Paiement", with step 1 active in brass).

2. Page title — eyebrow "Réservation", Fraunces heading "Choisissez votre créneau".

3. Two-column layout.
   - Left (65%): a calendar view. Top of the calendar: month navigation with brass arrows, selectable plan dropdown ("Consultation standard en ligne — 30 min"), and a format toggle (En ligne / Au cabinet) as a segmented control. Below: a weekly view showing 5 days with time slots as clickable pills (ivory, 1px stone border). Available slots are clickable; unavailable are muted with a strike-through style. The selected slot has a brass background and ivory text.
   - Right (35%): a sticky summary card (ivory, 12px radius, brass top border) showing the selected plan name, duration, price, selected date/time (or "Aucun créneau sélectionné"), and a primary brass "Continuer" button (disabled until a slot is picked). Below the button, a "Politique d'annulation" link in muted stone.

4. Footer (compact version, single row).
```

---

## 7. Booking — Step 2 (`/book/info` — client details)

```
Design the second step: client information and document upload.

Layout:

1. Header with progress bar (step 2 active).

2. Page title — Fraunces heading "Vos informations".

3. Two-column layout.
   - Left (65%): a form with the following fields, each 48px tall with hairline borders and floating labels:
     - Nom complet (text)
     - Téléphone (tel)
     - Email (email)
     - Type de transaction (select with the 4 service categories)
     - Brève description de votre besoin (textarea, 4 rows)
     - A consent checkbox: "J'accepte la politique de confidentialité"
     A subtle hairline divider, then a Fraunces sub-heading "Documents (facultatif)" and a drag-and-drop upload area — ivory dashed border, brass upload icon in the center, helper text "Glissez vos documents ici ou parcourir". Below the dropzone, uploaded files shown as small ivory chips with file name, size, and a remove (×) icon.
   - Right (35%): the same sticky summary card from step 1, now showing the chosen slot. Primary brass "Continuer" button (disabled until required fields are valid). Secondary ink-outlined "Retour" button below.

4. Footer (compact).
```

---

## 8. Booking — Step 3 (`/book/payment`)

```
Design the third step: payment.

Layout:

1. Header with progress bar (step 3 active).

2. Page title — Fraunces heading "Paiement".

3. Two-column layout.
   - Left (65%): two stacked payment option cards (ivory, 1px stone border, 12px radius). Each card is a radio-selectable block.
     - Card 1 selected by default: "Carte bancaire" with brass card icon, a short description "Paiement sécurisé via Stripe". When selected, the card expands to show a card-number input, two side-by-side inputs (expiration MM/AA and CVC), and a cardholder name input. A small line of trust signals at the bottom: "Paiement sécurisé · 3D Secure · Données chiffrées" with small brass icons.
     - Card 2: "Paiement au cabinet" with brass wallet icon, description "Réglez en espèces lors de votre rendez-vous au cabinet à Bensergao". Only available when the format is "Au cabinet" — otherwise greyed out with a tooltip.
   - Right (35%): the sticky summary card now showing slot, plan name, total amount in Fraunces, and a primary brass "Confirmer et payer" button. Below: small line "En réservant, vous acceptez nos conditions générales et notre politique d'annulation" with the two as brass links.

4. Footer (compact).
```

---

## 9. Booking confirmation (`/book/success`)

```
Design the confirmation page after a successful booking.

Layout:

1. Header (no progress bar).

2. Centered hero — 96px top padding. Large brass Lucide check-circle icon (line style). Fraunces heading "Votre rendez-vous est confirmé." A 1-line subtext.

3. Confirmation card — centered, max 640px wide, ivory, 12px radius, 1px stone border. Inside, vertically stacked rows separated by hairlines:
   - Plan name in Fraunces, price in muted stone aligned right
   - Date and time (e.g. "Mardi 14 mars 2026 — 10:30") with a small brass calendar icon
   - Format (e.g. "En ligne · Lien Jitsi envoyé par email") with a small brass icon
   - Reference number in monospace muted stone

4. Below the card: two CTAs side by side — primary brass "Ajouter à mon calendrier" and secondary ink-outlined "Voir mes rendez-vous".

5. Next-steps block — small section with three short tips and brass numeral markers: "Vérifiez votre boîte mail", "Préparez vos documents", "Contactez-nous en cas de besoin" — each with one line of body.

6. Footer.
```

---

## 10. Payment failed (`/book/failed`)

```
Same as the confirmation page, but:
- The brass icon is replaced with an ink alert-circle (line style)
- Heading: "Le paiement n'a pas abouti."
- Subtext explains the issue
- The confirmation card is replaced with an informational ivory block listing common reasons (insufficient funds, 3D Secure failed, card declined)
- CTAs: primary brass "Réessayer le paiement" and secondary ink-outlined "Choisir un autre moyen"
- Add a discreet "Besoin d'aide ?" line at the bottom with a brass phone link.
```

---

## 11. FAQ (`/faq`)

```
Design the FAQ page.

Sections:

1. Header.

2. Page intro — eyebrow "FAQ" in brass, Fraunces heading "Questions fréquentes." A two-line subtext.

3. Search bar — full-width within content area. 56px tall, ivory background, hairline stone border, 6px radius, brass search icon on the left, placeholder "Rechercher une question…", optional brass × to clear.

4. Two-column layout below the search.
   - Left (25%): a sticky vertical category list. Categories: "Toutes", "Famille", "Immobilier", "Financier", "Contrats", "Pratique". The active category has a brass left border (3px) and ink text; others are muted stone.
   - Right (75%): a vertical list of accordion items, each separated by a hairline. Each row: Fraunces question on the left, brass chevron right-aligned. Open state reveals 1–3 short paragraphs of Inter body with optional bullet points.

5. "Didn't find what you needed" block — full-width ivory section. Fraunces heading "Vous n'avez pas trouvé votre réponse ?", and three CTAs in a row: "Discuter avec l'assistant" (primary brass), "Nous appeler" (ink outline), "Nous écrire" (ghost).

6. Footer.
```

---

## 12. Contact (`/contact`)

```
Design the contact page.

Sections:

1. Header.

2. Page intro — eyebrow "Contact" in brass, Fraunces heading "Nous joindre."

3. Two-column layout (50/50).
   - Left: an ivory card with practice info, 12px radius, 1px stone border. Inside, four rows separated by hairlines, each with a brass line icon on the left:
     - Phone — three numbers "05 28 38 07 19 / 06 66 12 06 61 / 06 67 96 11 99"
     - Email — "sana.bouhamidi@gmail.com"
     - Address — "Hay Bensergao, près du Tribunal de Première Instance, Agadir"
     - Office hours — "Lun–Ven 09:00–13:00 · 15:00–18:30 · Sam 09:00–13:00"
   Below the card: a "WhatsApp" primary brass button with the brand mark in line-icon style.
   - Right: a short contact form. Fields — Nom, Email, Sujet (select), Message (textarea). Submit button primary brass "Envoyer".

4. Map — full-width embedded Google Map showing the office location. 480px tall, 12px radius corners. A small ivory floating card on the map with the office name and a brass "Itinéraire" link.

5. Footer.
```

---

## 13. Legal page template (`/legal/...`)

```
Design a legal text page — used for Mentions légales, Politique de confidentialité, Conditions générales.

Layout:

1. Header.

2. Page title block — eyebrow "Mentions légales" in brass, Fraunces heading. A muted stone line "Dernière mise à jour : 15 mars 2026."

3. Two-column layout.
   - Left (25%): a sticky table of contents listing section headings with hairline left border on hover, brass left border on active.
   - Right (75%): the legal content. Max width 720px. Fraunces section headings, Inter body in slightly larger size for readability. Use clear hierarchy: H2 for top-level sections, H3 for sub-sections. Lists use small brass bullets.

4. Footer.
```

---

## 14. 404 page

```
Design a 404 page.

Layout:

1. Header.

2. Full-height centered section on parchment.
   - A large Fraunces "404" in Ink, with a thin brass underline.
   - Below: Fraunces heading "Cette page est introuvable."
   - A two-line muted stone subtext.
   - Two CTAs in a row: primary brass "Retour à l'accueil" and secondary ink-outlined "Nous contacter".

3. Optional: a faint zellige line ornament in stone-300 sitting low on the section.

4. Footer.
```

---

# Chatbot widget (overlay component)

## 15. Chatbot — closed and open states

```
Design the chatbot widget as an overlay component.

Closed state:
A floating 56×56 circular launcher fixed at bottom-right (24px from edges). Ink background, ivory Lucide message-circle line icon centered. Soft shadow 0 8px 24px rgba(14,27,44,0.12). On hover, the circle expands to a pill showing the icon plus a Fraunces label "Assistant" in ivory.

Open state:
A panel 380px wide × 560px tall, anchored bottom-right with 24px offset. Ivory background, 12px radius, 1px stone border, subtle shadow.

Panel structure:
1. Header — 64px tall, parchment background with a 1px stone bottom border. Left: a small brass icon and Fraunces "Assistant" title in ink. Right: a small × close button in ink.
2. Conversation area — scrollable. Bot messages aligned left, ivory bubble with a 1px stone border, 12px radius, Fraunces avatar circle (brass background, "SB" monogram) on the left. User messages aligned right, ink background with ivory text, 12px radius, no avatar.
3. Quick-reply chips — when relevant, a horizontal scroll row of small ink-outlined pills (e.g. "Famille", "Immobilier", "Voir les tarifs", "Prendre rendez-vous").
4. Input — 56px tall, parchment background separated by a 1px stone top border. Hairline-bordered input on the left with placeholder "Posez votre question…", and a brass send button (arrow icon) on the right.
5. Bottom strip — a thin row with a muted stone disclaimer "Assistant informatif, ne remplace pas une consultation." On the right, a small brass "Parler à un humain →" link.
```

---

# Client portal

## 16. Client portal home (`/me`)

```
Design the client portal home, accessed via magic link.

Layout:

1. Header — same as public, but the "Prendre rendez-vous" button is replaced by an avatar circle (initials in brass on ivory) with a small dropdown for "Mes rendez-vous · Déconnexion".

2. Page intro — eyebrow "Mon espace" in brass, Fraunces heading "Bonjour, Karim." Below: a one-line muted stone subtext "Voici l'aperçu de vos rendez-vous."

3. Upcoming bookings — heading "À venir" in Fraunces. A vertical stack of booking cards (ivory, 12px radius, 1px stone border, brass top border on the next upcoming one). Each card has three regions in a horizontal row:
   - Left: a calendar block with the day number in Fraunces and the abbreviated month above it
   - Middle: plan name in Fraunces, format and time below in muted stone, a small status pill ("Confirmé" in soft green or "À régler au cabinet" in soft amber)
   - Right: actions — primary brass "Voir le détail" and ghost "Annuler" / "Reprogrammer"

4. Past bookings — heading "Historique". A more compact list of past bookings — a vertical table-like layout, each row with date, plan, status, and a small "Reçu PDF ↓" brass link.

5. Help block — small ivory card with Fraunces heading "Besoin d'aide ?", a short description, and a brass "Contacter le cabinet" link.

6. Footer.
```

---

## 17. Booking detail (client view, `/me/bookings/:id`)

```
Design the booking detail page in the client portal.

Layout:

1. Header (with avatar).

2. Breadcrumb — "Mon espace / Rendez-vous / #REF-12345".

3. Top section — two columns.
   - Left (60%): a large ivory card. Inside, vertically stacked rows separated by hairlines, each labeled in muted stone small-caps and showing the value in Fraunces or Inter:
     - Plan
     - Date et heure
     - Format (with the meeting link if online — brass underlined)
     - Lieu (with the office address if in-office)
     - Statut (status pill, brass border)
     - Montant et paiement (amount + payment status)
     - Référence
   - Right (40%): a stacked action panel.
     - Primary brass "Rejoindre la visio" (if online and within 1h) or "Itinéraire" (if in-office)
     - Secondary ink-outlined "Reprogrammer"
     - Ghost danger-text "Annuler"
     - Below, a small brass link "Télécharger le reçu PDF ↓"

4. Documents — Fraunces heading "Vos documents". A grid of small ivory cards, each showing a file icon, file name, size, and a remove button. A dashed dropzone at the end to add more, with the same upload component as the booking flow.

5. Chat history — Fraunces heading "Échange avec le cabinet". A compact conversation thread (read-only here, or with a small input at the bottom if interaction is allowed in v1).

6. Footer.
```

---

# Admin pages

## 18. Admin login (`/admin/login`)

```
Design the admin login screen.

Layout:

1. Full-screen split layout.
   - Left (50%): ink background. Centered ivory wordmark "Sana Bouhamidi" in Fraunces with a brass underline. Below it, in muted stone, "Espace administrateur." Optionally, a faint zellige line ornament at the bottom in stone-300.
   - Right (50%): parchment background. Centered form, max 360px wide.
     - Fraunces heading "Connexion"
     - Email field (48px, floating label)
     - Password field with a brass eye-toggle icon
     - "Mot de passe oublié ?" link in brass, right-aligned
     - Primary brass "Se connecter" full-width button
     - A horizontal hairline divider with "ou" centered
     - A 2FA code field (6 digits, monospace, larger) — appears after first step

No header or footer on this screen — keep it focused.
```

---

## 19. Admin dashboard (`/admin`)

```
Design the admin dashboard home.

Layout uses a persistent left sidebar across all admin screens.

Sidebar (240px, ivory background, 1px stone right border):
- At top: small wordmark "Sana Bouhamidi" with "Admin" tag.
- Nav items with Lucide icons, vertically stacked: Tableau de bord, Calendrier, Disponibilités, Rendez-vous, Clients, Contenu, Assistant, Paramètres.
- Active item has brass left border (3px) and ink text.
- At bottom: user card with avatar, name, role, and a logout icon.

Main area (parchment):
1. Top bar — title "Tableau de bord" in Fraunces, on the right a search input (compact) and a notification bell icon with brass dot.

2. KPI strip — four ivory cards in a row, each with a small muted-stone label, a large Fraunces number, and a tiny brass trend indicator. KPIs: "Rendez-vous aujourd'hui", "Rendez-vous cette semaine", "Taux de présence", "Revenu du mois".

3. Two-column layout below.
   - Left (60%): "Prochains rendez-vous" — a table with columns Date, Client, Plan, Format, Statut, Actions. Status as small pills. Action: brass "Voir" link.
   - Right (40%): "Conversations récentes de l'assistant" — a vertical list of compact chat previews (avatar, first message excerpt, time ago). Each is clickable.

4. Bottom section — "Activité" — a simple line/bar chart showing bookings per day over the last 30 days. Ink lines on parchment, brass dot on the current day.
```

---

## 20. Admin calendar (`/admin/calendar`)

```
Design the admin calendar.

Use the same sidebar and top bar as the dashboard. Title: "Calendrier".

Main area:
1. Toolbar — left: view switcher (Jour / Semaine / Mois) as a segmented control. Center: current period with brass arrows for navigation, plus a "Aujourd'hui" link. Right: filters (plan, format, statut) as small dropdowns.

2. Calendar grid — week view by default. 7 columns for days, time slots vertically. Bookings rendered as ivory blocks with a brass left border (3px), 6px radius, showing plan name and client name. Confirmed bookings have brass border; pending have stone-300 dashed border; cancelled have a struck-through muted look.

3. Right-side drawer (collapsible) — when a booking is clicked, slides in from the right. Shows full booking detail, client info, uploaded docs link, payment status, and quick actions: "Marquer comme terminé", "Marquer absent", "Annuler", "Reprogrammer", "Voir le client →".
```

---

## 21. Admin availability (`/admin/availability`)

```
Design the availability management page.

Title "Disponibilités" in Fraunces.

Two-tab layout:
- Tab 1 (default): "Horaires hebdomadaires" — a 7-row table, one per day. Each row has the day label, a toggle (closed/open), and two time-range inputs (morning slot, afternoon slot) when open. A subtle "Ajouter une plage" brass link to add more ranges.

- Tab 2: "Jours fériés et absences" — a vertical list of date-range entries (e.g. "Aïd al-Fitr — 30/03/2026 → 01/04/2026"). Each entry is an ivory row with start date, end date, optional label, and a small × to remove. A primary brass "Ajouter une période" button above the list.

Bottom of the page: a primary brass "Enregistrer les modifications" button, aligned right. Subtle muted stone "Modifications enregistrées il y a 2 min" appears after save.
```

---

## 22. Admin bookings list (`/admin/bookings`)

```
Design the bookings list page.

Title "Rendez-vous" in Fraunces.

Toolbar:
- Search input on the left (full-width-ish, compact)
- Filters in a row: date range, plan, statut, format, payment status — each as a small dropdown
- Right: "Exporter CSV ↓" brass link

Table:
- Columns: Date, Heure, Client, Plan, Format, Statut, Paiement, Actions
- Hairline row dividers, ivory row hover state
- Status and payment as small pills with appropriate colors (brass border, soft tones)
- Actions: brass "Voir" link per row, plus a kebab menu with quick actions

Pagination at the bottom: rows per page select, page numbers, brass active page.
```

---

## 23. Admin booking detail (`/admin/bookings/:id`)

```
Design the admin booking detail page.

Title in Fraunces: "Rendez-vous #REF-12345". Subtitle in muted stone: status + payment status pills inline.

Two-column layout:
- Left (65%):
  - "Client" card — ivory, with avatar, name in Fraunces, phone, email, address, and a brass "Voir le profil client →" link
  - "Rendez-vous" card — plan, date and time, format, link (if online), location (if in-office), reference
  - "Documents" card — list of uploaded files with download and remove actions, plus an upload zone
  - "Notes internes" card — a textarea (only visible to admin, never to client) with auto-save and a small muted timestamp

- Right (35%): a sticky action panel.
  - Primary brass "Marquer comme terminé"
  - Secondary "Reprogrammer"
  - Secondary "Annuler"
  - Brass link "Rembourser →" (opens a modal)
  - "Générer le reçu PDF" brass link
  - Below, a small "Historique" timeline showing creation, confirmation, payment, etc. with small brass dots and timestamps.
```

---

## 24. Admin clients (`/admin/clients`)

```
Design the clients directory.

Title "Clients" in Fraunces.

Toolbar:
- Search input (full-width)
- Filters: plan utilisé, dernière visite (date range)
- Right: total count "327 clients"

Table:
- Columns: Nom, Téléphone, Email, Nombre de rendez-vous, Dernier rendez-vous, Actions
- Hairline dividers, ivory hover
- Action column: brass "Profil →" link

Click on a row opens a right-side drawer showing the client profile — basic info, full booking history, uploaded documents across bookings, and notes.
```

---

## 25. Admin content management (`/admin/content`)

```
Design the content management page.

Title "Contenu" in Fraunces.

Top-level tabs: Services · FAQ · Plans · Assistant.

For the FAQ tab (most complex — use as the example):

Layout:
- Left sidebar (within the page, not the admin sidebar): list of categories (Famille, Immobilier, Financier, Contrats, Pratique). Brass left border on active.
- Right: list of FAQ entries within the active category. Each entry is an ivory card showing the question in Fraunces, a one-line preview of the answer, language tabs (FR / AR), and three actions: edit, duplicate, delete. A primary brass "Ajouter une question" button at the top.
- Edit state: question and answer are inline-editable, with a small toolbar for basic formatting (bold, italic, link, list). Below the editor, "Enregistrer" brass button and "Annuler" ghost button.

For the Services tab: similar structure, each of the 4 services has its own editable page with intro, transactions list, required documents, FAQ.

For the Plans tab: a card per consultation plan, each with editable fields (name FR, name AR, duration, price, format, included features list, recommended toggle).

For the Assistant tab: list of intents (rows), each with example questions and a configured response. Plus a "Bibliothèque" sub-section showing the FAQ entries the LLM can retrieve from.
```

---

## 26. Admin chatbot logs (`/admin/chatbot`)

```
Design the chatbot conversations review page.

Title "Conversations de l'assistant" in Fraunces.

Toolbar: date range, language filter, "Résolues / Escaladées" toggle, search.

Two-column layout:
- Left (35%): a vertical list of conversation previews. Each row shows the first user message (truncated), language flag (FR/AR), duration, a small brass tag if the conversation led to a booking, and the timestamp.
- Right (65%): the selected conversation transcript, rendered like the chatbot widget — bot messages on the left, user on the right. Sticky action bar at the top of the right pane with three buttons:
  - "Ajouter à la FAQ" (primary brass)
  - "Marquer comme résolu" (secondary)
  - "Signaler un problème" (ghost)

Empty state when no conversation is selected: a centered illustration (line art only, with a single brass accent) and "Sélectionnez une conversation à examiner."
```

---

## 27. Admin settings (`/admin/settings`)

```
Design the settings page.

Title "Paramètres" in Fraunces.

Left vertical sub-nav inside the page (not the admin sidebar): Cabinet, Facturation, Notifications, Paiements, Utilisateurs, Sécurité, Confidentialité.

For the "Cabinet" section (use as example):
- A form grouped into sections:
  - "Identité" — Nom du cabinet, Adresse, Téléphones (multi-row), Email
  - "Identifiants légaux" — ICE, IF, RC, Patente
  - "Heures d'ouverture" — link to availability page
  - "Logo et favicon" — uploaders
- Each section has a Fraunces heading and a "Enregistrer" primary brass button at the bottom of the section.

For the "Utilisateurs" section: a table of admin users (Sana, assistante) with role, last login, status, and actions. Primary brass "Inviter un utilisateur" button.

For the "Paiements" section: configuration cards for Stripe (active) and CMI (greyed out with "Configuration à venir" tag). Each card shows status, mode (test/prod), and a brass "Configurer →" link.

For the "Confidentialité" section: GDPR/Loi 09-08 controls — data retention periods (sliders or selects), document auto-purge delay, consent banner toggle.

All sections share the same form aesthetic: ivory cards on parchment, hairline dividers between groups, brass focus states.
```

---

# Tips for getting better results from Stitch

- **Generate desktop first, then ask for the mobile variant** of each screen — Stitch handles them better separately than as one request.
- **Re-prompt the base style block** if a screen drifts off-brand (too colorful, too rounded, gradients reappear) — Stitch tends to forget context after a few generations.
- **For repeated patterns** (service detail, legal pages, admin sub-pages), generate one and clone in code rather than re-generating each variant.
- **Ask for empty/loading/error states** of complex screens (calendar, bookings list, chatbot logs) as separate generations once the main state is right.
- **Skip Arabic versions in Stitch.** Generate French only; handle RTL/font-swap in the Next.js implementation with the same design tokens.

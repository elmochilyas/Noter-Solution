---
name: L'Étude Notariale
colors:
  surface: '#fff8ef'
  surface-dim: '#e0d9ce'
  surface-bright: '#fff8ef'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#faf3e7'
  surface-container: '#f4ede1'
  surface-container-high: '#eee7dc'
  surface-container-highest: '#e8e2d6'
  on-surface: '#1e1b14'
  on-surface-variant: '#44474c'
  inverse-surface: '#333028'
  inverse-on-surface: '#f7f0e4'
  outline: '#75777d'
  outline-variant: '#c5c6cd'
  surface-tint: '#535f73'
  primary: '#000000'
  on-primary: '#ffffff'
  primary-container: '#0f1c2d'
  on-primary-container: '#78859a'
  inverse-primary: '#bac7de'
  secondary: '#7d570d'
  on-secondary: '#ffffff'
  secondary-container: '#fcc977'
  on-secondary-container: '#775307'
  tertiary: '#000000'
  on-tertiary: '#ffffff'
  tertiary-container: '#1c1c18'
  on-tertiary-container: '#86847e'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d6e3fb'
  primary-fixed-dim: '#bac7de'
  on-primary-fixed: '#0f1c2d'
  on-primary-fixed-variant: '#3b475a'
  secondary-fixed: '#ffdead'
  secondary-fixed-dim: '#f0be6d'
  on-secondary-fixed: '#281900'
  on-secondary-fixed-variant: '#604100'
  tertiary-fixed: '#e6e2db'
  tertiary-fixed-dim: '#c9c6c0'
  on-tertiary-fixed: '#1c1c18'
  on-tertiary-fixed-variant: '#484742'
  background: '#fff8ef'
  on-background: '#1e1b14'
  surface-variant: '#e8e2d6'
typography:
  display-lg:
    fontFamily: ebGaramond
    fontSize: 64px
    fontWeight: '500'
    lineHeight: '1.1'
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: ebGaramond
    fontSize: 40px
    fontWeight: '500'
    lineHeight: '1.2'
    letterSpacing: -0.01em
  headline-lg:
    fontFamily: ebGaramond
    fontSize: 48px
    fontWeight: '500'
    lineHeight: '1.2'
    letterSpacing: -0.01em
  headline-md:
    fontFamily: ebGaramond
    fontSize: 32px
    fontWeight: '500'
    lineHeight: '1.3'
  body-lg:
    fontFamily: inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  label-md:
    fontFamily: inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: '1'
    letterSpacing: 0.05em
  caption:
    fontFamily: inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: '1.4'
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  max-width: 1240px
  base-unit: 8px
  section-v-rhythm-min: 96px
  section-v-rhythm-max: 128px
  gutter: 24px
  margin-mobile: 16px
---

## Brand & Style
This design system embodies "Editorial Luxury meets Legal Authority." It is tailored for a high-end Moroccan notary practice, where the reliability of the law meets the refined aesthetic of a heritage brand. 

The visual language is rooted in **Minimalism** with an editorial flair. It prioritizes clarity, stillness, and prestige. There are no shadows or gradients; depth is achieved through intentional layering of parchment and ivory tones. The presence of generous whitespace (breathing room) is a core design "asset," signaling confidence and high-value service. The aesthetic is tactile—mimicking high-quality stationery and legal vellum.

## Colors
The palette is inspired by traditional legal archives and Mediterranean luxury.
- **Ink (#0E1B2C):** Our primary voice. Used for all critical text and authoritative backgrounds. It replaces true black to provide a softer, more sophisticated contrast against the off-white tones.
- **Brass (#B68A3E):** Our signature accent. Used sparingly for calls to action and structural highlights to suggest quality and endurance.
- **Parchment & Ivory:** These form our "white space." Parchment is the foundation, while Ivory acts as the elevated surface for cards and interactive elements.
- **Stone:** Used for fine lines (hairlines) and secondary information to maintain a low-friction visual hierarchy.

## Typography
The typography strategy creates a tension between the classical authority of a serif and the modern efficiency of a sans-serif.

- **Headlines (ebGaramond):** Chosen for its graceful, historical proportions. Display sizes should use tighter letter-spacing (-0.02em) to evoke a modern editorial feel. 
- **Body & UI (Inter):** Used for all functional text, ensuring maximum legibility across digital devices. 
- **Labels:** Always set in Inter with increased tracking and uppercase styling to denote categorization and formal structure.

## Layout & Spacing
The layout follows a **Fixed Grid** model centered on a 1240px container. 

- **Vertical Rhythm:** Sections must be separated by a minimum of 96px on desktop to maintain the "luxury" feel of unhurried space. 
- **The 8-Point Grid:** All internal padding, margins, and component heights must be multiples of 8px.
- **Responsibility:** On tablet and mobile, the 12-column grid collapses to 4 columns. Section spacing reduces to 64px to maintain momentum while preventing excessive scrolling.

## Elevation & Depth
In this design system, depth is purely architectural rather than atmospheric. 

- **Tonal Layering:** We do not use shadows. Elevation is communicated by placing **Ivory** surfaces on top of **Parchment** backgrounds.
- **Hairlines:** Elements are defined by 1px **Stone** borders. This creates a crisp, "plotted" look reminiscent of architectural blueprints or high-end legal documents.
- **Brass Accents:** Vertical or horizontal 3px bars in Brass are used to draw the eye to "active" or "primary" containers, such as the current section or a featured service card.

## Shapes
The shape language is "Soft-Geometric." 

- **Standard Radius:** 6px for interactive elements (buttons, inputs). This is enough to feel modern and approachable without losing the "sharpness" associated with legal precision.
- **Container Radius:** 12px for cards and larger modules. This creates a clear visual distinction between "the page" and "the content blocks."

## Components
- **Primary Button:** 48px height. Background: **Brass**. Text: **Ivory**. 6px radius. No shadow. On hover, the background darkens slightly.
- **Secondary Button:** 48px height. 1px **Ink** border. Text: **Ink**. Transparent background. 6px radius.
- **Service Cards:** **Ivory** background with a 1px **Stone** border. 12px radius. Feature a signature **3px Brass top border** to denote premium categorization.
- **Inputs & Forms:** 48px height. 6px radius. 1px **Stone** border. On focus, the border changes to **Ink** and is accompanied by a 2px **Brass** outer ring (offset by 2px).
- **Icons:** Use Lucide-style line icons. Stroke weight: **1.5px**. Color: **Ink**. Icons should always be accompanied by labels to ensure formal clarity.
- **Dividers:** Horizontal rules should be 1px **Stone**, used to separate content within sections without breaking the vertical flow.
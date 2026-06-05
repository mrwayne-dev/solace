Here is the complete, exhaustive design analysis of **minta.framer.ai**:

---

# Minta – Design System Analysis
**Product:** Minta – Crypto & Fintech App Template
**Source URL:** minta.framer.ai
**Analysis Date:** April 18, 2026
**Analyst:** Senior UI/UX Design Analyst (AI)

---

## 1. VISUAL IDENTITY

### 1.1 Color Palette

| Role | Value | Notes |
|---|---|---|
| Page Background | `#000000` / `rgb(0,0,0)` | Pure black, applied globally to `<body>` |
| Surface / Card Background | `rgba(255,255,255,0.06)` | ~6% white alpha — primary glass card |
| Surface Elevated | `rgba(255,255,255,0.12)` | ~12% white alpha — slightly lifted surface |
| Nav Background | `rgba(250,250,250,0)` — transparent | Transparent by default; blends with hero image |
| Primary Text | `#ffffff` / `rgb(255,255,255)` | Pure white for headings, labels, UI text |
| Secondary Text | `#c2c2c2` / `rgb(194,194,194)` | Mid-gray for subtitles, body, muted labels |
| Muted Text | `#8a8a8a` / `rgb(138,138,138)` | Token `--token-4da943ea` — footer links, captions |
| Accent / Brand Orange | `#fa8039` (approx.) | Token `--token-5ce86d7b` — used in hero image light streak; brand warmth |
| CTA Button Background | `rgba(255,255,255,0.06)` | Same glass surface as cards |
| CTA Button Inset | `rgba(255,255,255,0.20)` | Inset box-shadow creating a top-edge highlight |
| Footer Background | `#000000` | Identical to page bg |

### 1.2 CSS Design Token Map

```
--token-5d3602f5  →  #c2c2c2   (secondary text)
--token-fa4dcb27  →  #ffffff   (primary text)
--token-df227308  →  #ffffff1f (rgba 255/255/255 @ 12%)
--token-37b21b3d  →  #0d0d0d   (near-black alt bg)
--token-34d58044  →  #0f0f0f   (slightly lighter near-black)
--token-5f5e41aa  →  #000000   (pure black)
--token-5210968f  →  #fff3     (rgba 255/255/255 @ ~19%)
--token-e47778e7  →  #fafafa00 (nav – fully transparent)
--token-b299cb02  →  #ffffff0f (rgba 255/255/255 @ ~6%)
--token-6d3132b9  →  #ffffff1f (rgba 255/255/255 @ 12%)
--token-5ce86d7b  →  #fa8039   (orange accent)
--token-4da943ea  →  #8a8a8a   (muted gray)
```

### 1.3 Border, Divider, and Shadow Styles

The site uses **no traditional dividers or `<hr>` elements**. Visual separation is achieved entirely through spatial rhythm, glass card contrast, and background imagery.

| Element | Shadow / Border |
|---|---|
| Primary CTA Button | `box-shadow: rgba(255,255,255,0.20) 0px 1.5px 0px 0px inset` |
| Feature Cards (large) | `box-shadow: rgba(255,255,255,0.20) 0px 1px 0px 0px inset` |
| FAQ Accordion Items | `box-shadow: rgba(255,255,255,0.12) 0px 1px 0px 0px inset` |
| App UI Mockup Card (heavy) | Multi-layer depth shadow: `rgba(0,0,0,0.19) -0.67px -0.67px 0.95px inset … rgba(0,0,0,0.06) -40px -40px 56.5px -3.75px` |
| Testimonial Cards | `box-shadow: rgba(255,255,255,0.20) 0px 1px 0px 0px inset` |
| Footer Subscribe Button | `box-shadow: rgba(255,255,255,0.20) 0px 1.5px 0px 0px inset` |

The inset top-edge highlight (`rgba(255,255,255,0.20) 0px 1.5px 0px 0px inset`) is the **single most recurring micro-detail** across the component system — it simulates a glass edge catching light and is applied consistently to interactive surfaces.

### 1.4 Overall Mood & Tone

**Dark Editorial / Premium Cinematic.** The design communicates luxury, focus, and technological confidence. It is minimal in the sense of having zero decorative chrome, yet it is rich in atmosphere through full-section photography, ambient orange light streaks, and layered depth. The tone is closer to a luxury fintech brand (e.g., Revolut Ultra) than a typical SaaS startup.

---

## 2. TYPOGRAPHY

### 2.1 Font Families

Three font families are used in a deliberate three-tier typographic system:

| Role | Font | Variant | Type |
|---|---|---|---|
| Display Headings & Sub-Headings | **Inter Display** | Regular (400), Medium (500) | Custom / Loaded font |
| Body Copy, Labels, UI Text | **Inter** | Regular (400), Medium (500) | Loaded font |
| Section Category Labels (uppercase) | **JetBrains Mono** | Regular (400) | Monospace / Loaded |
| Statistics / Counter Numbers | **Geist Mono** | Light (300) | Monospace / Loaded |

### 2.2 Full Type Scale

| Level | Font | Size | Weight | Color | Letter-Spacing | Line-Height | Usage |
|---|---|---|---|---|---|---|---|
| H1 — Hero Headline | Inter Display | 56px | 400 | `#ffffff` | -2.24px (-4%) | 67.2px (1.2) | Page hero title |
| H2 — Section Heading | Inter Display | 32px | 400 | `#ffffff` | -0.96px (-3%) | 35.2px (1.1) | All section headings |
| H2 — CTA Repeat Headline | Inter Display | (approx.) 32–40px | 400 | `#ffffff` | tight negative | tight | Bottom CTA heading |
| Logo / Wordmark | Inter Display | 20px | 500 | `#ffffff` | -0.4px | 24px | Nav logo |
| Section Sub-label (parallax) | Inter Display | 20px | 400 | `#ffffff` / `#c2c2c2` | -0.4px | 26px | Scroll-text animations |
| Body / Subheadline | Inter | 16px | 400 | `#c2c2c2` | normal | 22.4px (1.4) | Section subtitles, descriptions |
| Body / UI Text | Inter | 16px | 500 | `#ffffff` | normal | 19.2px (1.2) | Button labels, feature titles |
| Card Body Text | Inter | 16px | 400 | `#ffffff` | normal | 22.4px | Testimonial quotes |
| Feature Item Title | JetBrains Mono | 14px | 400 | `#ffffff` | -0.28px | 16.8px | Uppercase card labels |
| Section Category Label | JetBrains Mono | 14px | 400 | `#ffffff` | -0.28px | 16.8px | "WHAT MINTA DOES" etc. |
| Footer Label / Caption | Inter | 14px | 400–500 | `#c2c2c2` | normal | 18.2px | Stat labels, footer links |
| Form Label | Inter | 12px | 500 | `#c2c2c2` | normal | 14.4px | Email label |
| Stats Counter | Geist Mono | 40px | 300 | `#ffffff` | -0.8px to -1.6px | 44px | Hero stats ($12K+, 190+) |

### 2.3 Typographic Treatments

- **Negative letter-spacing is pervasive.** All display text tracks tightly inward: H1 at -2.24px, H2 at -0.96px, logo at -0.4px. This creates a condensed, premium editorial feel.
- **Section category labels** use JetBrains Mono at 14px with `text-transform: uppercase`. They function as structured metadata for each section.
- **Stats** use Geist Mono (light weight, 300) — a deliberate choice to signal data precision and contrast with the expressive Inter Display headings.
- **Body text is always `rgb(194,194,194)`** (secondary gray) for subtitles and descriptions, never full white, preserving a clear hierarchy between headline and support copy.
- **Font weight hierarchy is minimal:** Primarily 300 (stats), 400 (display headings, body), and 500 (UI labels, logo, buttons). No bold (700) or heavy (900) weights are used anywhere.

---

## 3. LAYOUT & SPACING

### 3.1 Layout System

- **Contained, centered column layout** with a hard `max-width: 1128px`
- Side padding of `24px` applied to all sections
- Sections fill 100vw horizontally in background (for full-bleed imagery) but content is constrained to `1128px`
- No sidebar; all layouts are single-column or 2-column grid depending on section

### 3.2 Section Structure & Spacing Rhythm

| Pattern | Value |
|---|---|
| Hero section top/bottom padding | `128px` vertical |
| Standard section padding | `80px` top/bottom |
| Section internal content gap | `40–48px` |
| Card inner padding | `24px` |
| FAQ item inner padding | `16px` |
| Grid gutter (2-col layouts) | `~40px` (derived from gap) |

All sections follow a consistent `section → [label] → H2 → subtitle → [content grid/cards]` hierarchy.

### 3.3 Grid Structure

- **2-column grid** is the dominant layout for feature cards, testimonials, and app mockups
- **4-column row** for stats counter section
- **Step cards** use a **2×2 grid** of 312×120px cards
- Testimonials use **2-column masonry-style scrolling column** with uniform card size (532×360px)

### 3.4 Whitespace Usage

Whitespace is used **generously and deliberately**. Long dark voids exist between sections as breathing room — the site treats silence as a design element, mirroring cinematic pacing. No section feels visually crowded.

---

## 4. COMPONENT DESIGN LANGUAGE

### 4.1 Navigation Bar

| Property | Value |
|---|---|
| Height | 64px |
| Width | 1128px (matches content max-width) |
| Background | Transparent — floats over hero image |
| Padding | 16px all sides |
| Alignment | Flex row, space-between |
| Navigation Links | Hidden behind hamburger menu only |
| CTA in Nav | Absent |
| Sticky Behavior | Non-sticky (scrolls with page) |

### 4.2 Buttons

| Property | Value |
|---|---|
| Shape | Pill / fully-rounded |
| Border Radius | 48px |
| Background | `rgba(255,255,255,0.06)` — glass |
| Inset Shadow | `rgba(255,255,255,0.20) 0px 1.5px 0px 0px inset` |
| Text Color | `#ffffff` |
| Font | Inter, 16px, weight 500 |
| Padding | 16px all sides |
| Border | None |

### 4.3 Cards

**A. Feature Card (Large)**

| Property | Value |
|---|---|
| Background | `rgba(255,255,255,0.06)` |
| Border Radius | 16px |
| Inset Shadow | `rgba(255,255,255,0.20) 0px 1px 0px 0px inset` |
| Image Handling | Full-bleed `object-fit: cover` |

**B. Testimonial / Quote Card**

| Property | Value |
|---|---|
| Background | `rgba(255,255,255,0.06)` |
| Border Radius | 16px |
| Inset Shadow | `rgba(255,255,255,0.20) 0px 1px 0px 0px inset` |
| Inner Padding | 24px |

**C. Step Card (Onboarding Flow)**

| Property | Value |
|---|---|
| Background | `rgba(255,255,255,0.06)` |
| Border Radius | 12px |
| Inner Padding | 24px |
| Content | Step number prefix ("01 ·") + title + description |

**D. FAQ Accordion Item**

| Property | Value |
|---|---|
| Background | `rgba(255,255,255,0.06)` |
| Border Radius | 12px |
| Inset Shadow | `rgba(255,255,255,0.12) 0px 1px 0px 0px inset` |
| Inner Padding | 16px |
| Toggle Icon | "+" right-aligned |

### 4.4 Input Fields / Forms

| Property | Value |
|---|---|
| Input Border Radius | 8px |
| Input Background | Transparent |
| Input Text Color | `#ffffff` |
| Font Size | 14px, Inter, weight 400 |
| Label Font | Inter 12px/500, `#c2c2c2` |

### 4.5 Section Labels

- Small icon + uppercase monospace text before each H2
- Font: JetBrains Mono, 14px, uppercase, letter-spacing -0.28px
- Container: Inline flex, `border-radius: 100px`, no background fill
- Example: `⊕ FEATURES`, `⊕ SECURITY`, `⊕ FAQ`

### 4.6 Icons

- Style: Outline / line icons, 1–1.5px stroke
- Size: ~18–20px
- Color: White

### 4.7 Dividers

- No `<hr>` or explicit dividers used anywhere
- Section separation via whitespace, background contrast, and glass cards only

---

## 5. IMAGERY & MEDIA

### 5.1 Treatment

- Full color photography, no grayscale or tinting
- Warm amber/orange cinematic tone with deep shadows
- Full-bleed section backgrounds as atmospheric canvases
- App mockups use `border-radius: 24px` with layered dark shadows
- No vector illustrations except Security shield SVG

---

## 6. MOTION & INTERACTION CUES

- **Scroll-scrub text reveals:** Hero headline repeats mid-page with fade-in-per-word animation
- **Counting stats animation:** Numbers count up from 0 on scroll entry
- **Card stagger reveals:** Cards enter from below on scroll
- **FAQ accordion:** Expand/collapse with "+" toggling to "−"

---

## 7. DESIGN PRINCIPLES INFERRED

Minta's design philosophy is rooted in **restraint amplified by atmosphere**. Every visual decision reduces noise while maximizing emotional weight — the pure black background is a deliberate stage that makes every glass card feel luminous by contrast. The type system enforces strict hierarchy through size, weight, and color temperature alone, avoiding decorative elements. The three-font system (Inter Display for editorial impact, Inter for functional clarity, JetBrains Mono for technical precision) communicates both human warmth and technical credibility. The site intentionally avoids the neon-green cyberpunk aesthetic common to crypto products, choosing the visual language of premium consumer finance instead. Motion serves the narrative through subtle counter animations and text reveals — interaction is implied rather than demanded. The result feels less like a product landing page and more like an editorial brand experience.

---

## 8. DO NOT REPLICATE LIST

1. **Minta wordmark and snowflake/radiant logo icon** — proprietary brand identity
2. **Hero hand-reaching-toward-light photography** — core to the "Step into the light" brand metaphor
3. **Minta physical card renders** — proprietary product renders with Minta branding
4. **Mobile app UI screenshots** — Minta's own product design IP
5. **"Step into the light. Your future is already waiting." copy** — Minta's proprietary creative positioning

---

*This document provides sufficient specification for a designer to reconstruct the visual system, component library, spacing rhythm, and design language of this site onto an entirely different product and brand context.*

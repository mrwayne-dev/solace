# PERFORMANCE AUDIT MODE
> Mode 1 of 3. Read `performance-agent.md` first for the severity rubric, safety tiers, and tooling reality check — those rules apply throughout this file.

**Goal of this mode:** produce a ranked, tagged fix plan. Stop at the plan. Do not implement.

---

## PHASE 0: BUDGETS & USER PROFILE

Before auditing, define what you are optimizing against. This prevents fixing the wrong things.

### Determine the user profile

- **Primary geography?** (Latency expectations: Lagos on 4G ≠ Frankfurt on fiber.)
- **Device class?** (Low-end Android changes INP targets dramatically vs. MacBook Pro.)
- **P75 connection?** (Check CrUX or analytics if available; otherwise ask the user.)

### Set budgets upfront

Use the realistic column unless the user requests aggressive. Budget is a ceiling for *initial* page load, not total app weight.

| Asset | Aggressive | Realistic |
|---|---|---|
| Total page weight | < 300KB | < 500KB |
| JS (initial, gzipped) | < 100KB | < 200KB |
| CSS (gzipped) | < 20KB | < 50KB |
| Hero image | < 100KB | < 200KB |
| Fonts total | < 50KB | < 100KB |
| HTML | < 20KB | < 50KB |

For SaaS dashboards or app shells, these budgets are too tight — set per-template budgets in monitor mode.

### Check if field data exists

- Google Search Console + CrUX for the URL? `[REQUIRES USER]` if no access.
- RUM in place? (Vercel Analytics, Datadog RUM, Sentry, web-vitals.js.)
- A recent PageSpeed Insights report?

If no field data exists: **lab and field can diverge by 30–50 points**. State this explicitly. All recommendations will carry `[REQUIRES USER]` for measurement until RUM is set up in monitor mode.

### Output

A one-paragraph budget summary: target user, budget tier (aggressive/realistic), field data availability. Then proceed to Phase 1.

---

## PHASE 1: STACK DETECTION

Read the codebase. Identify:

- **Project type:** static site / SPA / SSR / SSG / ISR / hybrid / MPA
- **Framework:** React / Vue / Svelte / Angular / vanilla / other
- **Meta-framework:** Next.js / Nuxt / SvelteKit / Remix / Astro / Laravel + Blade / other
- **Backend:** Node / PHP / Python / Go / other
- **Database:** Postgres / MySQL / MongoDB / SQLite / other
- **CSS:** Tailwind / Sass / CSS Modules / styled-components / plain CSS
- **Build tool:** Vite / Webpack / Rollup / Parcel / esbuild / other
- **Hosting/CDN:** Vercel / Netlify / Cloudflare / AWS / VPS / shared hosting

### Files to read for stack detection

- `package.json` — dependencies, scripts, build config
- `vite.config.*` / `webpack.config.*` / `next.config.*` / `nuxt.config.*`
- `composer.json` — PHP packages
- `tailwind.config.*` — purge config, content paths
- `.env*` — environment, API endpoints (do not log secrets)
- `/public` or `/dist` — what actually ships to the browser
- `.htaccess` / `nginx.conf` / `Caddyfile` — server-level config
- CI/CD pipeline files

**MPA flag:** If this is an MPA (Laravel, WordPress, plain PHP, Rails server-rendered), note it. This unlocks the **Speculation Rules API** audit in 2.9. SPAs use framework-level prefetching instead.

### Output

A one-paragraph stack summary, then Phase 2.

---

## PHASE 2: FULL AUDIT

For every finding: assign **severity** (per rubric in master), **safety tier** (per master), and **verification tag** (`[VERIFIED]` / `[INFERRED]` / `[REQUIRES USER]`).

Format every finding as:
```
[SEVERITY] [Tier N] [VERIFICATION] Issue summary
  Basis: how you know
  Fix: exact action
  Expected impact: ~Xms LCP / ~XKB / etc.
```

---

### 2.1 — JavaScript

**Bundle:**
- Total JS shipped on initial load (target: < 200KB gzipped).
- Tree-shaking configured and effective?
- Imports pulling whole libraries when only part is needed (`import _ from 'lodash'` vs `import debounce from 'lodash/debounce'`)?
- Heavy deps: moment.js (→ date-fns/dayjs), full lodash (→ lodash-es or native), full icon packs.
- Route-level code splitting via dynamic `import()`?
- Vendor chunks separated from app code?
- Duplicate deps (multiple versions of the same library in the lockfile)?
- Web Workers for heavy computation (parsing, hashing, image manipulation)?

**Render-blocking scripts:**
- `<script>` tags in `<head>` without `defer` or `async`?
- Third-party scripts (analytics, chat, ads) blocking the main thread?
- Inline scripts executing before paint?
- `type="module"` (auto-deferred in modern browsers)?

**Runtime / long tasks (> 50ms):**
- Sync expensive work in event handlers (sorting, filtering large arrays, DOM thrashing)?
- `console.log` calls in production builds (should be stripped)?
- Memory leaks from event listeners not removed on unmount?
- React: missing `memo` / `useCallback` / `useMemo` causing re-renders?
- `useEffect` with wrong deps causing render loops?
- `scheduler.yield()` / `scheduler.postTask()` used for long handlers?

**INP attribution — three-part breakdown:**
Every slow interaction has three components. Identify which is broken:
1. **Input delay** — time from user action to handler execution. Cause: other tasks blocking the thread.
2. **Processing time** — time inside the handler itself. Cause: too much sync work.
3. **Presentation delay** — time from handler completion to next paint. Cause: forced layout/style recalc.

Different sub-components have different fixes. Don't treat INP as one monolithic problem.

**Web Workers candidates:**
- Large dataset parsing, encryption/hashing, image manipulation, complex sorting.
- `SharedArrayBuffer` / Transferable objects for zero-copy Worker communication where supported.

**Framework-specific (only audit the one in use):**

*React / Next.js:* `getStaticProps`/`generateStaticParams` where possible? `next/image`, `next/font`? Server Components where no interactivity is needed? Stray `'use client'`? `startTransition` for non-urgent updates? `useDeferredValue` for expensive derived UI?

*Vue / Nuxt:* `defineAsyncComponent` for heavy components? `useLazyFetch` / `useLazyAsyncData`? Pinia stores loaded only when needed? `v-memo` on heavy lists?

*SvelteKit:* `load` returning only what the page needs? Heavy components lazy-loaded with `{#await import()}`? Streaming with deferred promises for non-critical data?

*Laravel + Blade + Alpine/Livewire:* `wire:init` vs inline loads? Alpine loaded only on pages that need it? Livewire polling minimized?

*Vanilla JS:* `defer` on scripts? DOM queries cached? Event listeners delegated?

---

### 2.2 — CSS

- Total CSS shipped (target: < 50KB critical path).
- Unused CSS? Tailwind `content` config covers all template paths? Purge running in production?
- Critical CSS (above-the-fold) inlined in `<head>`?
- Non-critical styles loaded async (`rel="preload" as="style"` + `onload`)?
- Animations triggering layout? **Animate `transform` and `opacity` only.**
- Overly complex selectors causing expensive style recalc?
- Render-blocking CSS for stylesheets only used on specific routes?
- `@import` inside CSS files (render-blocking — move to HTML `<link>`)?
- `content-visibility: auto` on off-screen, visually independent sections? *(Caution: never on the LCP container.)*
- `contain: layout style paint` on isolated components?
- `@layer` cascade order intentional?

---

### 2.3 — Images

Images are usually the biggest performance problem. Audit them first.

- **Format:** WebP or AVIF? Any PNG/JPG that should be WebP (30–80% smaller)? AVIF support is now widespread — use it as primary with WebP fallback.
- **Sizing:** Served at displayed size? A 2000px image in a 400px container is 5× wasted bytes.
- **Lazy loading:** Below-fold images have `loading="lazy"`? Hero/LCP image has `loading="eager"` and `fetchpriority="high"`?
- **Dimensions:** Explicit `width` and `height` to prevent CLS?
- **Compression:** Optimized? Check filesize vs. perceived quality.
- **Responsive:** `srcset` and `sizes` for different viewports?
- **CDN delivery** (vs origin)?
- **SVGs:** Inlined (faster, styleable) or `<img>` tags (extra request)?
- **CSS background images:** Could they be `<img>` for better priority control?

**Priority hints:**
- LCP image: `fetchpriority="high"`.
- Carousel slides 2+: `fetchpriority="low"`.
- Grid thumbnails (non-LCP): `fetchpriority="low"`.
- `loading="lazy"` images already get low priority — do not also set `fetchpriority="high"` (conflicting signals).

**LCP image specifically:**
- Identify the LCP element (usually hero image or largest above-fold element).
- Must be: preloaded, **not** lazy-loaded, `fetchpriority="high"`, correctly sized.
- LCP target: < 2.5s.
- Inline as base64 only for small heroes (< 10KB) — otherwise use a `<link rel="preload">` with `imagesrcset` + `imagesizes`.

---

### 2.4 — Fonts

Fonts are silent performance killers.

- Self-hosted vs. Google Fonts/Typekit? (Self-hosted = no third-party DNS lookup.)
- Preloaded? `<link rel="preload" as="font" type="font/woff2" crossorigin>`
- `font-display: swap` set? (Prevents invisible text during font load.)
- `font-display: optional` where acceptable? (Best perf — uses system font if web font isn't ready in time.)
- How many variants loaded? Each weight/style is a separate file.
- Variable font instead of multiple weight files?
- Unicode-range subsetting?
- `size-adjust` / `ascent-override` / `descent-override` on fallback to eliminate CLS from font swap?
- Target: zero CLS from font loading.

---

### 2.5 — Network & Server

**HTTP basics:**
- HTTP/2 or HTTP/3 enabled?
- Brotli compression? (15–26% better than gzip.)
- Cache-Control on static assets: `public, max-age=31536000, immutable` (only safe with content-hashed filenames).
- HTML cache strategy (no-cache, short-lived, or stale-while-revalidate)?
- CDN in front of origin?

**Early Hints (HTTP 103):** *[Tier 2 — server config required]*
- Server supports 103? (Cloudflare, Vercel, Nginx 1.26+.)
- Allows server to send `Link: rel=preload` headers before the full response is ready.
- Example: `Link: </css/app.css>; rel=preload; as=style, </fonts/main.woff2>; rel=preload; as=font; crossorigin`
- Expected gain: 50–150ms LCP reduction when TTFB > 200ms.

**TTFB (target: < 200ms):**
- Server response time?
- Server co-located with users vs. geographically distant?
- Database on a separate server, connection pooled?
- Common API responses cached (Redis / in-memory / Cloudflare)?
- N+1 queries (especially Eloquent — missing `with()` eager load)?

**Request count:**
- Total requests on page load.
- Eliminable requests (inline critical SVGs, combine small scripts).
- Deferrable requests (third-party widgets, analytics).
- API request waterfalls vs. parallel fetches?

**DNS / connection:**
- `<link rel="preconnect">` / `dns-prefetch` for critical third-party origins (browser caps useful at ~3)?
- Total third-party domains contacted on load? Each adds 100–300ms (DNS + TCP + TLS).

**bfcache eligibility:** *[Tier 1 — universally safe to fix]*
- Page eligible? bfcache restores instantly on back/forward — a free win.
- Killers to check:
  - `Cache-Control: no-store` on the HTML response (disqualifies the page)
  - `unload` event listeners (replace with `pagehide`)
  - Open IndexedDB connections not closed before navigation
  - Open WebSocket / `fetch` streams not aborted before navigation
  - `window.opener` references on popup pages
- Test: Chrome DevTools → Application → Back/forward cache → Test. *(`[REQUIRES USER]` if no browser access.)*

---

### 2.6 — Caching

**Browser caching:**
- Static assets: `Cache-Control: public, max-age=31536000, immutable` (with content hashing).
- HTML: `no-cache` or `max-age=0, must-revalidate`.
- API responses: appropriate `max-age` based on freshness needs.

**Application-level:**
- Expensive computation / DB query results cached?
- Session-independent renders cached at the edge (Cloudflare, Varnish, Nginx `proxy_cache`)?
- Redis / Memcached for DB results?

**Service Worker (if applicable):**
- Caching the app shell for offline / repeat visits?
- Stale-while-revalidate for changing content?
- Oversized SW caches adding to install time on first visit?

*Laravel:* `route:cache`, `config:cache`, `view:cache`? ORM result caching for heavy queries?

*Next.js:* `revalidate` set on `fetch` and route handlers? Server Component results cached with `unstable_cache`?

---

### 2.7 — Core Web Vitals

For each metric: identify the *primary cause* of any failure, not just the score.

**LCP (target: < 2.5s)** — break into sub-parts:
1. **TTFB** — server response time.
2. **Resource load delay** — time after TTFB before LCP resource starts loading.
3. **Resource load time** — time to download the LCP resource.
4. **Render delay** — time after load until LCP element renders.

Each sub-part has different fixes. State which sub-part dominates.

**INP (target: < 200ms)** — replaced FID in March 2024. Measures p98 interaction latency across the page session.
- Identify the slowest interaction (`PerformanceObserver` with `event` type).
- Break into input delay / processing time / presentation delay (per 2.1).
- Heavy work moved to Web Workers?
- `scheduler.yield()` in long handlers?

**CLS (target: < 0.1):**
- Images / iframes / ads have explicit dimensions?
- Fonts cause layout shift on load?
- Dynamically injected banners / cookies / carousels cause shift?
- Animations using `transform` only?
- LCP image has correct aspect-ratio placeholder?
- Late-loading embeds (YouTube, tweets) — `aspect-ratio` CSS + placeholder?

**FCP (target: < 1.8s):** render-blocking CSS or JS? Server response fast?

**TTFB (target: < 200ms):** see 2.5.

---

### 2.8 — Build Pipeline

- Production build minifying JS and CSS?
- Source maps disabled in production output? (Adds ~2× size if accidentally shipped.)
- `NODE_ENV=production` set? (Strips React devtools, debug code.)
- Assets content-hashed for long-term caching (`app.[hash].js`)?
- Tree-shaking enabled and effective?
- Brotli pre-compression at build time (`.br` files served by Nginx/Caddy directly)?
- Tailwind purge running?
- Build-time image optimization (sharp, imagemin) in CI?
- Build reproducible (same input → same hashes — critical for cache predictability)?

---

### 2.9 — Modern Browser APIs

These represent significant gains with minimal code. Frequently missed.

**Speculation Rules API (MPAs only):** *[Tier 3 — see safety rules below]*
- Project is an MPA? Then Speculation Rules can make navigation feel instant.
- `<script type="speculationrules">` block present?
- Most likely next pages prefetched or prerendered?

Recommended starting config (safe defaults):
```html
<script type="speculationrules">
{
  "prefetch": [
    { "where": { "href_matches": "/*" }, "eagerness": "moderate" }
  ]
}
</script>
```

Upgrade to prerender for **specific** high-confidence next pages (cart → checkout, product list → first product):
```html
<script type="speculationrules">
{
  "prerender": [
    { "where": { "selector_matches": "[data-prerender]" }, "eagerness": "moderate" }
  ],
  "prefetch": [
    { "where": { "href_matches": "/*" }, "eagerness": "conservative" }
  ]
}
</script>
```

**Eagerness:** `immediate` > `eager` > `moderate` (hover 200ms or pointerdown) > `conservative` (pointerdown only). Start with `moderate`/`conservative`.

**DO NOT prerender pages that:** fire analytics on load (double-counts), mutate state on load (add-to-cart, auth callbacks), require auth not yet cached, are logout / delete / payment confirmation.

Expected gain: 150–500ms LCP reduction for prerendered pages, instant navigation feel.
Browser support: Chromium. Degrades gracefully in Firefox/Safari.

**`content-visibility: auto`:** *[Tier 2]*
- Long-scrolling pages with many off-screen sections?
- Apply to below-fold sections with explicit `contain-intrinsic-size`:
```css
.article-section {
  content-visibility: auto;
  contain-intrinsic-size: auto 500px;
}
```
**Caution:** never apply to a section containing the LCP element.

**`scheduler.yield()` / `scheduler.postTask()`:** *[Tier 1]*
- Long sync operations in handlers (> 50ms)?
- Yield within loops:
```javascript
async function processItems(items) {
  for (let i = 0; i < items.length; i++) {
    process(items[i]);
    if (i % 50 === 0) {
      await (scheduler?.yield?.() ?? new Promise(r => setTimeout(r, 0)));
    }
  }
}
```

**Priority Hints (`fetchpriority`):** *[Tier 1]*
- `fetchpriority="high"` on LCP image / resource and critical `fetch()` calls.
- `fetchpriority="low"` on below-fold images and non-critical scripts.

**View Transitions API:** *[Tier 3 — has LCP cost]*
- Used for page transitions?
- **Performance warning:** adds 50–80ms LCP on mobile. Only worth it combined with Speculation Rules prerender, which eliminates the cost.
- For MPA cross-document: `@view-transition { navigation: auto; }`
- Wrap in `@media (prefers-reduced-motion: no-preference)`.
- Browser support: Chrome 111+, Firefox 144+, Safari 18+ (same-doc); Chrome 126+ (cross-doc).

---

### 2.10 — Third-Party Scripts

Third-party scripts are the single most common cause of INP regressions and LCP delays. Audit them explicitly.

**Inventory every third-party script.** For each:
- What does it do? (Analytics, chat, A/B, payments, embeds.)
- Is it actually used / generating value?
- When does it load? (Blocking, defer, async.)
- How much main thread time? (Performance tab → Bottom-Up → filter by domain. `[REQUIRES USER]` if no browser.)
- Blocking INP? (Scripts running during interactions.)

**Third-party budget:**
- No single script should add > 100ms to TBT.
- Total third-party JS < 50KB gzipped.
- Maximum 3–5 distinct third-party origins.

**Defer strategy by type:**

| Script type | Strategy |
|---|---|
| Analytics (GA4, Plausible) | `defer` + load after `DOMContentLoaded` |
| Chat (Intercom, Crisp) | Load on first scroll or first click |
| A/B testing | `async` — accept brief FOUC, do not block |
| Video (YouTube) | Load on `IntersectionObserver` trigger |
| Cookie banners | `defer` — they don't need to block render |
| Payments (Stripe, PayPal) | Load only on checkout, not globally |
| Social embeds | Replace with static screenshots + click-to-load |
| Hotjar / FullStory | After 3s delay or after `load` event |

**Facade pattern for heavy embeds:**
- YouTube iframes → `<img>` thumbnail + play button. Load real iframe on click.
- Chat widgets → static button. Load script when clicked.
- Expected gain: 200–600ms TBT reduction per heavy widget removed from initial load.

---

### 2.11 — RUM (Real User Monitoring)

Lab data measures a controlled scenario. Real users experience the site differently. Without RUM, you are optimizing blind.

**Check if RUM is in place:**
- `web-vitals` npm package installed and reporting to an endpoint?
- RUM provider configured? (Vercel Speed Insights, Datadog RUM, Sentry, SpeedCurve, DebugBear.)
- CrUX data for this origin? (PageSpeed Insights shows real-world p75.)

**If no RUM:** flag as **HIGH** with `[REQUIRES USER]` for current field state. Setup goes in `performance-monitor.md`.

**Field vs. Lab gap analysis (when both available):**
- CrUX LCP 4.5s but Lighthouse 2.1s → real-world conditions (slower devices, cold cache, slower network). Prioritize server and caching fixes.
- CrUX INP 450ms but Lighthouse shows no JS issues → third-party scripts (don't show in Lighthouse, dominate real interactions).

---

### 2.12 — Mobile & Adaptive Loading

- Lighthouse run on Mobile preset? (Desktop 95 can be Mobile 40.)
- Mid-range Android (not Pixel, not iPhone) score?
- `navigator.connection` / `navigator.hardwareConcurrency` used to adapt to low-end devices?
- Reduced-motion version of animations? (`@media (prefers-reduced-motion: reduce)`)
- Heavy features (3D, canvas, WebGL) gated behind capability checks?

**Adaptive loading example:** *[Tier 2 — verify Save-Data UX is acceptable]*
```javascript
const connection = navigator.connection;
const shouldLoadHeavy =
  !connection ||
  (connection.effectiveType === '4g' && !connection.saveData);

if (shouldLoadHeavy) {
  import('./heavy-feature.js');
}
```

---

## OUTPUT: AUDIT RESULTS

Output exactly this format. Do not add prose around it. Do not implement anything. **Stop after the plan.**

```
## PERFORMANCE AUDIT RESULTS

**Stack:** [detected]
**User profile:** [geography + device class]
**Field data (CrUX p75):** LCP ~Xs | CLS ~X | INP ~Xms | TTFB ~Xms
  [or: "No field data — REQUIRES USER to run PageSpeed Insights or set up RUM"]
**Lab data (Lighthouse mobile):** Score ~X | LCP ~Xs | CLS ~X | INP ~Xms | TBT ~Xms
  [or: "REQUIRES USER — no Lighthouse access"]
**Total JS (initial):** ~XKB gzipped [VERIFIED from build output / INFERRED from package.json]
**Total CSS:** ~XKB gzipped
**Total page weight:** ~XMB
**bfcache eligible:** Yes / No [reason] / REQUIRES USER
**Speculation Rules:** Yes / No / N/A (SPA)

---

### QUICK WINS — < 1 hour each, highest ROI
1. [SEVERITY] [Tier 1] [VERIFIED] Issue
   Basis: [how you know]
   Fix: [exact action]
   Expected impact: [metric + estimated number]
2. ...

### CRITICAL — must fix
[same format]

### HIGH — fix before launch / next release
[same format]

### MEDIUM — next sprint
[same format]

### LOW — nice to have
[same format]

### UNVERIFIED — needs measurement before classifying
1. [REQUIRES USER] Issue
   What you suspect: [...]
   What to collect: [exact tool / report / output]
2. ...

---

**Expected improvement after CRITICAL + HIGH fixes:**
LCP: Xs → Xs | CLS: X → X | INP: Xms → Xms | Initial weight: XMB → XKB

**Tier-3 items requiring explicit user approval:** [list, or "none"]

---

**Next step:** review this plan. When ready, say "implement" and the agent will open `performance-implement.md` and apply CRITICAL + QUICK WINS first, with diffs for each change.
```

---

*End of audit mode. Do not proceed to implementation without explicit user approval.*

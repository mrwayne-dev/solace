# PERFORMANCE IMPLEMENTATION MODE
> Mode 2 of 3. Read `performance-agent.md` first. This file is opened only after the audit plan has been produced and the user has explicitly approved implementation.

**Goal:** apply the fixes from the audit plan, one at a time, with verifiable diffs and honest impact reports.

---

## ORDER OF OPERATIONS

1. **QUICK WINS first** — they have the highest ROI per minute. Knock out everything < 1 hour before touching anything else.
2. Then **CRITICAL** items, in the order they appear in the plan.
3. After all CRITICAL items: re-test (or request the user re-test), and confirm the predicted gains before moving to HIGH.
4. **HIGH** items only after CRITICAL is verified to have moved metrics.
5. **MEDIUM and LOW** items go in a follow-up PR or sprint.

**Never batch unrelated changes** into one commit. One issue → one commit → one diff. This makes regression bisecting possible.

---

## PER-FIX WORKFLOW

For every fix:

1. **State what you're changing and why.**
   ```
   Fixing: [issue from audit plan]
   Severity: [CRITICAL/HIGH/MEDIUM/LOW]
   Tier: [1/2/3]
   Expected impact: [metric, magnitude, basis]
   ```
2. **Show the before/after diff.** Actual code, not pseudocode.
3. **Note tradeoffs and caveats.** For Tier 2 and Tier 3 items, this is mandatory.
4. **Verify if possible.** Run the build, check the file size, confirm the change took effect.
5. **Mark the item complete in the plan.** Move to the next.

**Stop and ask** before applying any Tier 3 fix. Walk the user through:
- The metric you expect to gain.
- The failure mode.
- How to roll back.

---

## PLAYBOOKS — apply only items the audit flagged

These are the standard techniques. When the audit flags an issue in one of these areas, use the corresponding playbook. Do not apply techniques the audit didn't flag — that's how scope creep destroys performance work.

---

### JavaScript

```
Code splitting:
- Route-level: dynamic import() per page/view
- Component-level: React.lazy / defineAsyncComponent / {#await import()}
- Conditional: load on IntersectionObserver, on user interaction, after idle

Tree shaking:
- ES module imports (import { x } from 'lib'), never require()
- "sideEffects": false in package.json where safe
- lodash → lodash-es or native equivalents

Bundle:
- Separate vendor chunk (changes less = better cache hit rate)
- modulepreload for critical chunks: <link rel="modulepreload" href="...">
- Externalizing libraries to a public CDN [Tier 2]: loses bundle integrity, can introduce edge bugs — only do this if you have specific reason and a fallback

Defer non-critical JS [Tier 1]:
- Analytics: load after DOMContentLoaded or 3s idle
- Chat widgets: load on first scroll / click
- Video players: load on IntersectionObserver
- All <script> tags: defer attribute

Dead code:
- Strip console.log in production (terser drop_console)
- process.env.NODE_ENV checks for dev-only code

Long tasks:
- scheduler.yield() inside loops > 50ms
- scheduler.postTask() for explicit priority
- Move heavy work (parsing, sorting, encryption) to Web Workers
- Cross-browser yield fallback:
    const yieldToMain = () =>
      scheduler?.yield?.() ?? new Promise(r => setTimeout(r, 0));

React INP:
- startTransition() for non-urgent state updates
- useDeferredValue() for expensive derived renders (search, filter)
- Virtualize long lists: react-window or TanStack Virtual
- useMemo() with stable deps for expensive computations
```

---

### Images

```
Format conversion [Tier 1]:
- JPG/PNG → WebP (sharp, imagemin, Squoosh)
- AVIF as primary with WebP fallback (~90% browser support):
    <picture>
      <source type="image/avif" srcset="image.avif">
      <source type="image/webp" srcset="image.webp">
      <img src="image.jpg" width="800" height="600" alt="...">
    </picture>

Sizing [Tier 1]:
- Generate multiple widths: 320, 640, 1280, 1920
- srcset + sizes: sizes="(max-width: 640px) 100vw, 50vw"
- Never serve larger than displayed

Compression targets:
- Hero: < 150KB aggressive, < 200KB realistic
- Card / thumbnail: < 50KB
- Icons: SVG

Loading [Tier 1]:
- LCP image: loading="eager" + fetchpriority="high" + preload in <head>
    <link rel="preload" as="image" href="/hero.webp" fetchpriority="high"
          imagesrcset="/hero-640.webp 640w, /hero-1280.webp 1280w"
          imagesizes="100vw">
- Below-fold: loading="lazy"
- All: explicit width + height

If Next.js: next/image handles all of this.
If Nuxt: @nuxt/image.
If plain HTML: the <picture> pattern above.
```

---

### CSS

```
Critical CSS [Tier 2 — adds build-time complexity]:
- Extract above-fold styles, inline in <head>
- Tools: critical, penthouse, critters (webpack plugin)

Non-critical CSS [Tier 1]:
- Load async:
    <link rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" href="/styles.css">
    <noscript><link rel="stylesheet" href="/styles.css"></noscript>

Tailwind:
- content array covers all template files
- Production build with NODE_ENV=production
- Verify production output is < 30KB

Animation [Tier 1]:
- ONLY animate transform and opacity (GPU compositing, no layout)
- NEVER animate width, height, top, left, margin, padding (triggers layout)
- will-change: transform sparingly on animated elements

content-visibility [Tier 2]:
.below-fold {
  content-visibility: auto;
  contain-intrinsic-size: auto 400px;
}
- NEVER on the section containing the LCP element

size-adjust to eliminate font CLS [Tier 1]:
@font-face {
  font-family: 'FallbackFont';
  src: local('Arial');
  size-adjust: 96%;
  ascent-override: 90%;
  descent-override: 22%;
}
```

---

### Fonts

```
Self-host [Tier 1] — fastest:
- Download font files
- Serve from your domain (eliminates third-party DNS)
- font-display: swap in @font-face

Preload [Tier 1]:
- <link rel="preload" as="font" type="font/woff2" crossorigin href="/fonts/main.woff2">
- Only the font used for above-fold text (1–2 max)

Reduce variants [Tier 1]:
- Load only weights/styles used
- Variable fonts cover all weights in one file:
    @font-face {
      font-family: 'Inter Variable';
      src: url('/fonts/inter-variable.woff2') format('woff2');
      font-weight: 100 900;
    }

Subsetting [Tier 2 — verify charset coverage]:
- Latin subset for English-only sites (30–40% size reduction)
- Tools: pyftsubset, fonttools

Eliminate font-swap CLS [Tier 1]:
- size-adjust + ascent-override + descent-override on fallback
```

---

### Server / Backend

```
HTTP compression [Tier 1] — Nginx:
  gzip on;
  gzip_types text/plain text/css application/javascript application/json;
  gzip_min_length 1024;
  brotli on;  # requires ngx_brotli
  brotli_types text/plain text/css application/javascript application/json;

Cache headers for static assets [Tier 1]:
  location ~* \.(js|css|png|jpg|jpeg|webp|avif|woff2|ico)$ {
    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable";
  }

Early Hints (HTTP 103) [Tier 2 — server config required]:
  # Nginx 1.26+:
  location / {
    add_header Link "</css/app.css>; rel=preload; as=style";
    add_header Link "</fonts/main.woff2>; rel=preload; as=font; crossorigin";
    proxy_pass http://app_server;
  }
  # Cloudflare: enable in Speed → Optimization → Early Hints

HTTP/2 / HTTP/3 [Tier 1]:
- Enable on Nginx / Caddy / Apache
- HTTP/3 (QUIC) automatic on Cloudflare

bfcache eligibility [Tier 1]:
- Remove Cache-Control: no-store on HTML responses
- Replace addEventListener('unload', ...) with 'pagehide'
- Audit IndexedDB / WebSocket cleanup on navigation

Cloudflare:
- Enable Brotli [Tier 1]
- Cache Rules for static routes [Tier 1]
- Polish for image optimization [Tier 1]
- Early Hints in dashboard [Tier 2]
- Rocket Loader [Tier 3 — defers JS unpredictably; can break some scripts; verify in staging]
- Mirage [Tier 2 — slow-connection lazy loading; rarely needed if you already do lazy loading correctly]
```

---

### Database / API

```
Query optimization [Tier 1]:
- EXPLAIN ANALYZE on slow queries
- Indexes on WHERE / JOIN / ORDER BY columns
- Never SELECT * — explicit columns
- Pagination everywhere — never unbounded result sets
- Covering indexes where possible

Laravel / Eloquent:
- Eager load: Model::with(['rel1', 'rel2'])->get()
- Detect N+1: Laravel Debugbar, Telescope
- Cache::remember('key', 3600, fn() => DB::query()...)
- chunk() for large datasets
- artisan route:cache, config:cache, view:cache in production
- Read replicas for read-heavy pages [Tier 2]

Caching:
- Redis for session, cache, queue
- Cache infrequently-changing DB results (categories, config, roles)
- stale-while-revalidate for non-critical data
- Cache full HTML for logged-out users

API:
- Sparse fieldsets — return only needed fields
- Paginate everything
- Gzip/Brotli responses
- ETags / Last-Modified for conditional GET (304s save bandwidth)
- Promise.all() — parallel, not sequential awaits
```

---

### Speculation Rules (MPA only) *[Tier 3 — read carefully]*

```
Determine speculation level per page type:

High-confidence next pages — prerender:
- Cart → Checkout
- Product listing → first product
- Homepage → most-visited inner page
- Paginated list → next page

<script type="speculationrules">
{
  "prerender": [
    { "where": { "selector_matches": "[data-prerender]" }, "eagerness": "moderate" }
  ]
}
</script>
<a href="/checkout" data-prerender>Checkout</a>

Broad prefetch for everything else:
<script type="speculationrules">
{
  "prefetch": [
    {
      "where": {
        "href_matches": "/*",
        "not": { "selector_matches": "[data-no-prefetch]" }
      },
      "eagerness": "conservative"
    }
  ]
}
</script>
<a href="/logout" data-no-prefetch>Logout</a>

Debug: DevTools → Application → Background services → Speculative loads

DO NOT speculate on:
- Pages that fire analytics on load (double-counts pageviews)
- Pages that mutate state on load (add-to-cart, auth callbacks)
- Pages requiring auth not yet cached
- Logout, delete, payment confirmation

Rollback: remove the <script type="speculationrules"> block. No other side effects.
```

---

### Rendering Strategy (by framework)

```
Next.js:
- Static pages (no user data): generateStaticParams + revalidate
- Dynamic but cacheable: SSR with cache headers or unstable_cache
- User-specific: CSR after hydration (keep shell static)
- Push as much as possible to the server, generate statically

SvelteKit:
- prerender = true for static pages
- +page.server.ts load() instead of fetch in components
- Streaming with deferred promises for non-critical data

Nuxt:
- useLazyFetch / useLazyAsyncData for non-critical
- Hybrid: static for marketing, SSR for app pages

Laravel + Inertia:
- Partial reloads — only request props you need
- Lazy-load Inertia props for below-fold data
- Cache Blade views for guest routes

Plain JS SPA:
- Prerender marketing pages statically
- Service worker for app shell
- Route-based bundle splitting

Plain MPA (PHP, etc.):
- Speculation Rules for instant navigation [Tier 3]
- Cache full HTML at CDN for logged-out users [Tier 1]
- Early Hints for critical CSS / fonts [Tier 2]
```

---

## COMMON TRAPS — read before applying any fix

These are real-world mistakes experienced engineers see. Verify each before declaring a fix complete.

**Trap 1 — Optimizing the wrong thing.**
Profile first. Don't assume images are the problem when JS is 4MB. Start with field data and the waterfall, not assumptions.

**Trap 2 — Lazy-loading the LCP image.**
`loading="lazy"` on the hero is a common mistake. Hero = `loading="eager"` + `fetchpriority="high"` + preload link in `<head>`.

**Trap 3 — Preloading everything.**
Too many `<link rel="preload">` defeats the purpose — browser prioritizes everything, so nothing is prioritized. LCP image and 1–2 critical fonts max.

**Trap 4 — Forgetting mobile.**
Lighthouse on Mobile preset, not Desktop. Real users on real connections. Desktop 100 can be Mobile 40.

**Trap 5 — Cache-busting without content hashing.**
Filename `app.js` with 1-year cache means users never get updates. Use `app.[hash].js`. Then the cache header is safe.

**Trap 6 — Unused Tailwind in production.**
Wrong purge config = 3MB of CSS shipped. Verify production CSS file size after build. Should be < 30KB.

**Trap 7 — Animating layout properties.**
`width`, `height`, `top`, `left`, `margin` trigger layout recalc every frame. Animate `transform` and `opacity` only.

**Trap 8 — Third-party scripts on the main thread.**
GTM, Intercom, Hotjar, Drift each add 300–500ms blocking time. Defer all, load only what's used. GTM alone can contain 10+ scripts that each block INP.

**Trap 9 — Breaking bfcache with `unload` listeners.**
A single `window.addEventListener('unload', ...)` anywhere disqualifies the page from bfcache. Replace every `unload` with `pagehide`. Back/forward goes from 3s reload to instant.

**Trap 10 — View Transitions without Speculation Rules.**
`@view-transition { navigation: auto; }` looks smooth but adds 50–80ms to LCP on mobile. Only worth it combined with Speculation Rules prerender, which eliminates the cost.

**Trap 11 — Over-speculating.**
`eagerness: "immediate"` on all pages wastes bandwidth and CPU, especially on mobile. Use `conservative` or `moderate`, scope prerender to 2–3 high-confidence pages.

**Trap 12 — Optimizing only to lab data.**
Lighthouse 90 doesn't mean real users have a good experience. Real users have slower phones, extensions, partial caches. Always check CrUX field data.

**Trap 13 — `content-visibility: auto` on the LCP section.**
Browser skips rendering "off-screen" content; LCP image never paints. Only apply to sections genuinely below the fold and guaranteed not to contain the LCP element.

**Trap 14 — Externalizing React/Vue to public CDN as a perf "win".**
Sounds clever; rarely a win in 2026. You give up bundle integrity, lose tree-shaking across boundaries, and add a separate origin (DNS + TLS + cache). Only do it if your bundler is genuinely a problem and you've measured a benefit.

**Trap 15 — Severity inflation.**
If everything is CRITICAL, nothing is. Apply the rubric in the master file: CRITICAL = ≥ 300ms or ≥ 0.1 CLS or ≥ 100KB.

---

## AFTER ALL FIXES IN A SEVERITY TIER ARE APPLIED

1. **Re-measure or request re-measurement.**
   - If you can run Lighthouse / build tools yourself: do it and report the new numbers.
   - If you cannot: ask the user to re-run PageSpeed Insights and share the result. Do not claim improvements you haven't verified.
2. **Compare against the predicted gains** in the audit plan. If the actual gain is far off (less than half or more than double the prediction), explain why before continuing.
3. **Then move to the next severity tier**, or to monitor mode if everything is done.

---

*End of implement mode. When the user is ready to lock in regression prevention, open `performance-monitor.md`.*

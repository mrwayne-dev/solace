# PERFORMANCE MONITOR MODE
> Mode 3 of 3. Read `performance-agent.md` first. This file is opened after fixes are live, to prevent regression.

**Goal:** install the smallest amount of monitoring that will actually catch regressions in CI and in production. Without this, performance work decays within 4–8 weeks.

---

## MINIMUM VIABLE MONITORING (do all four)

### 1. `web-vitals` for field data — *[Tier 1]*

Install and report to *somewhere*. Even `console.log` is a valid first step — you need field data flowing.

```bash
npm install web-vitals
```

```javascript
// src/lib/vitals.js
import { onCLS, onINP, onLCP, onFCP, onTTFB } from 'web-vitals';

function sendToAnalytics({ name, value, id, delta, rating }) {
  // Replace with your analytics endpoint when ready.
  // Common targets: Vercel Speed Insights, Datadog RUM, Sentry,
  // Cloudflare Web Analytics, your own /vitals endpoint.
  console.log({ name, value, id, delta, rating });
}

onCLS(sendToAnalytics);
onINP(sendToAnalytics);
onLCP(sendToAnalytics);
onFCP(sendToAnalytics);
onTTFB(sendToAnalytics);
```

Wire this into the entry point (`main.tsx`, `app.js`, `_app.tsx`, `layout.tsx`, etc.).

### 2. Lighthouse CI — *[Tier 1]*

Block PRs that regress lab metrics beyond budget. Sample workflow:

```yaml
# .github/workflows/lighthouse.yml
name: Lighthouse CI
on: [pull_request]
jobs:
  lhci:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with: { node-version: 20 }
      - run: npm ci && npm run build
      - name: Run Lighthouse CI
        uses: treosh/lighthouse-ci-action@v11
        with:
          urls: |
            https://your-site.com/
            https://your-site.com/products
            https://your-site.com/checkout
          budgetPath: ./lighthouse-budgets.json
          uploadArtifacts: true
```

### 3. Bundle size diff per PR — *[Tier 1]*

Pick whichever fits your stack. All three block PRs that exceed the budget.

- **`size-limit`** — framework-agnostic, supports JS/CSS, posts a comment to the PR with diff vs. base branch.
- **`bundlesize`** — simpler, glob-based budgets.
- **Framework-native** — Next.js `@next/bundle-analyzer`, Nuxt `nuxi analyze`, Vite `rollup-plugin-visualizer`.

Example `size-limit` config:
```json
// .size-limit.json
[
  { "path": "dist/assets/*.js", "limit": "200 KB", "gzip": true },
  { "path": "dist/assets/*.css", "limit": "50 KB", "gzip": true }
]
```

### 4. Monthly field-data review — *assigned to a human*

The most-skipped step. CrUX data, RUM dashboard, or PageSpeed Insights — pick one, assign the review to a person, put it on a recurring calendar item. Without this, you'll ship green and silently regress.

---

## PER-ROUTE BUDGETS — the missing piece in v2

A site-wide budget is too crude. A marketing landing page and an authenticated dashboard have legitimately different needs. Define budgets per page type.

### Recommended template tiers

| Page type | JS (initial, gzipped) | CSS | LCP target | INP target | Notes |
|---|---|---|---|---|---|
| Marketing / landing | 100KB | 20KB | < 2.0s | < 200ms | Tightest budgets — these convert |
| Blog / content | 150KB | 30KB | < 2.5s | < 200ms | Some interactivity, mostly content |
| Product / catalog | 200KB | 40KB | < 2.5s | < 200ms | Image-heavy; budget headroom for images |
| App shell (auth'd dashboard) | 300KB | 50KB | < 2.5s | < 200ms | Real interactivity; richer client |
| Editor / IDE-like | 600KB | 80KB | < 3.0s | < 200ms | Genuinely needs the bytes |

Pick the tier per route. Don't use one site-wide budget — it forces you to choose between "too tight for the dashboard" and "too loose for the landing page."

### Lighthouse CI per-route budget example

```json
// lighthouse-budgets.json
[
  {
    "path": "/",
    "timings": [
      { "metric": "largest-contentful-paint", "budget": 2000 },
      { "metric": "interactive", "budget": 3500 }
    ],
    "resourceSizes": [
      { "resourceType": "script", "budget": 100 },
      { "resourceType": "stylesheet", "budget": 20 },
      { "resourceType": "total", "budget": 400 }
    ]
  },
  {
    "path": "/dashboard/*",
    "timings": [
      { "metric": "largest-contentful-paint", "budget": 2500 },
      { "metric": "interactive", "budget": 5000 }
    ],
    "resourceSizes": [
      { "resourceType": "script", "budget": 300 },
      { "resourceType": "stylesheet", "budget": 50 },
      { "resourceType": "total", "budget": 800 }
    ]
  }
]
```

---

## REGRESSION GOVERNANCE PLAYBOOK

Below is what "production-grade" performance governance looks like. Pick what fits your team size; the bare minimum is the four steps above.

### PR checks (block merge on regression)

1. **Lab metrics:** Lighthouse CI — fail if Performance score drops > 5 points or LCP/INP exceeds budget.
2. **Bundle size:** `size-limit` or equivalent — fail if any chunk exceeds budget.
3. **Visual regression** *(optional, Tier 2)*: Percy / Chromatic — catches CLS-causing layout changes.

### Production checks

1. **Synthetic monitoring:** DebugBear / SpeedCurve / Calibre — runs Lighthouse on a schedule from real geographies. Catches regressions that Lighthouse CI misses (e.g., third-party script changes).
2. **Real-user monitoring:** Vercel Speed Insights / Datadog RUM / Sentry / Cloudflare Web Analytics. Source of truth for p75 and p98.
3. **Alerting:** alert when CrUX/RUM p75 LCP exceeds 2.5s or INP exceeds 200ms for two consecutive days.

### Review cadence

| Cadence | Activity | Owner |
|---|---|---|
| Per-PR | Bundle diff, Lighthouse CI | Author + reviewer |
| Weekly | Synthetic dashboard skim | Frontend lead |
| Monthly | CrUX / RUM review, budget adjustments | Frontend lead |
| Per-release | Compare before/after p75 metrics | Whoever runs releases |
| Quarterly | Re-run full audit (audit mode) | Frontend lead |

---

## STACK-SPECIFIC RUM SETUP

### If on Cloudflare *[Tier 1]*
- Cloudflare Web Analytics — free, privacy-respecting, includes Core Web Vitals RUM.
- Enable in dashboard, drop the script tag in `<head>`.

### If on Vercel *[Tier 1]*
- Vercel Speed Insights — one line in your app, free tier available.
- `npm install @vercel/speed-insights` then add `<SpeedInsights />` to root layout.

### If on Netlify
- Netlify Analytics is server-side only (not CWV). Use `web-vitals` + your own endpoint, or Cloudflare in front for RUM.

### If self-hosted
- `web-vitals` → POST to your own `/api/vitals` endpoint → write to your DB or analytics system.
- Sample minimal endpoint:
```javascript
// pages/api/vitals.js (Next.js)
export default async function handler(req, res) {
  if (req.method !== 'POST') return res.status(405).end();
  const { name, value, id, rating, url } = req.body;
  await db.vitals.insert({ name, value, id, rating, url, timestamp: Date.now() });
  res.status(204).end();
}
```

---

## PRE-LAUNCH CHECKLIST

Run before promoting a release. This is the smallest checklist that catches the bulk of regressions.

**Build**
- [ ] Production build with `NODE_ENV=production`
- [ ] JS and CSS minified
- [ ] Source maps NOT included in production output
- [ ] Assets content-hashed
- [ ] Tailwind purged (production CSS file < 30KB)
- [ ] No `console.log` in production output
- [ ] Bundle analyzer shows no unexpected large chunks
- [ ] Brotli pre-compressed assets generated at build time

**Assets**
- [ ] All images WebP or AVIF (with fallbacks where needed)
- [ ] Hero/LCP image preloaded with `imagesrcset`
- [ ] All below-fold images have `loading="lazy"`
- [ ] All images have explicit `width` and `height`
- [ ] LCP image has `fetchpriority="high"`
- [ ] Fonts self-hosted
- [ ] Fonts have `font-display: swap` (or `optional` where appropriate)
- [ ] Only used font weights loaded
- [ ] Variable font used if multiple weights are needed

**Network**
- [ ] Brotli (or gzip) compression active — verify with `curl -H "Accept-Encoding: br" -I url`
- [ ] Static assets have 1-year cache headers (with content hashing)
- [ ] HTML has short or no-cache headers
- [ ] CDN in front of origin
- [ ] HTTP/2 (or HTTP/3) enabled
- [ ] Early Hints (103) configured for critical CSS/fonts (if server supports)
- [ ] `preconnect` for critical third-party origins (max 3)
- [ ] bfcache eligibility verified — no `unload` listeners, no `Cache-Control: no-store` on HTML

**Server**
- [ ] TTFB < 200ms from target user geography
- [ ] Database queries indexed
- [ ] No N+1 queries on main pages
- [ ] Cache layer (Redis / Memcached / edge) in place for hot paths
- [ ] No `unload` event listeners (replaced with `pagehide`)

**Modern APIs (if applicable to stack)**
- [ ] Speculation Rules added (MPA)
- [ ] `content-visibility: auto` on long below-fold sections (not on LCP container)
- [ ] `scheduler.yield()` in long event handlers
- [ ] Third-party scripts deferred or loaded on interaction

**Core Web Vitals (lab — Lighthouse mobile)**
- [ ] Performance score > 90
- [ ] LCP < 2.5s
- [ ] CLS < 0.1
- [ ] INP < 200ms
- [ ] No render-blocking resources flagged

**Monitoring**
- [ ] `web-vitals` library installed and reporting
- [ ] Lighthouse CI in build pipeline with per-route budgets
- [ ] Bundle size diff in CI
- [ ] Baseline CrUX captured before launch for comparison

---

## TARGET SCORES REFERENCE

Lighthouse, mobile, 4G throttled:

| Metric | Good | Needs Work | Poor |
|---|---|---|---|
| Performance Score | ≥ 90 | 50–89 | < 50 |
| LCP | < 2.5s | 2.5–4.0s | > 4.0s |
| FCP | < 1.8s | 1.8–3.0s | > 3.0s |
| INP | < 200ms | 200–500ms | > 500ms |
| CLS | < 0.1 | 0.1–0.25 | > 0.25 |
| TTFB | < 200ms | 200–600ms | > 600ms |
| Total Blocking Time | < 200ms | 200–600ms | > 600ms |

**Reminder:** field data (CrUX p75) matters more than lab data. If lab is green but CrUX is poor:
- Third-party scripts are the usual cause (not in Lighthouse).
- Real devices are slower than the emulated mid-tier.
- Real users have variable connections and partial caches.
- Audit third-party scripts and test on real mid-range Android.

---

## WHEN TO RE-RUN THE FULL AUDIT

Re-open `performance-audit.md` and run Phase 0 → 2 when:

- A major dependency upgrade lands (Next.js, React, framework upgrade).
- A major feature ships that changes the homepage, product page, or critical conversion flow.
- Field data (CrUX p75) regresses for two consecutive weeks.
- Quarterly cadence (whichever comes first).

---

*End of monitor mode. Performance work is never "done" — it's a maintenance practice. Keep the monthly review honest and the budgets in CI, and the rest follows.*

# SECURITY MONITOR MODE
> Mode 3 of 3. Read `security-agent.md` first. Opened after audit + implement, to lock in ongoing posture.

**Goal:** install the smallest amount of monitoring that actually catches new vulnerabilities and detects exploitation attempts. Without this, audits decay within weeks as new dependencies, new code, and new attack techniques accumulate.

---

## MINIMUM VIABLE SECURITY POSTURE (do all five)

### 1. Dependency vulnerability scanning in CI — *required*

```yaml
# .github/workflows/security.yml
name: Security
on: [pull_request, push]
jobs:
  audit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      # PHP
      - name: PHP audit
        if: hashFiles('composer.lock') != ''
        run: composer audit --abandoned=ignore
      # Node
      - name: Node audit
        if: hashFiles('package-lock.json') != ''
        run: npm audit --audit-level=high
      # Python
      - name: Python audit
        if: hashFiles('requirements.txt') != ''
        run: |
          pip install pip-audit
          pip-audit -r requirements.txt
```

Block merge on HIGH/CRITICAL CVEs. For HIGH-volume false positives, use a CVE allowlist file with **time-bound** entries — every allowlisted CVE has an expiry date.

### 2. Secret scanning in CI — *required*

```yaml
  secrets:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with: { fetch-depth: 0 }
      - uses: gitleaks/gitleaks-action@v2
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
```

Configure `.gitleaks.toml` with custom rules for your specific secret patterns (e.g., your internal API key prefix). The default rules catch most provider-issued secrets.

GitHub native: enable **Secret scanning** + **Push protection** in repo settings (free for public repos, GHAS for private).

### 3. Pre-commit hooks — *recommended*

Catch secrets before they hit Git history.

```yaml
# .pre-commit-config.yaml
repos:
  - repo: https://github.com/gitleaks/gitleaks
    rev: v8.18.0
    hooks:
      - id: gitleaks
  - repo: https://github.com/pre-commit/pre-commit-hooks
    rev: v4.5.0
    hooks:
      - id: detect-private-key
      - id: check-added-large-files
```

Install: `pre-commit install`. This runs locally before every commit.

### 4. Security event logging + alerting — *required*

The audit phase 11 list of must-log events is only useful if someone notices spikes. Set up at least one alert:

| Event | Alert threshold | Channel |
|---|---|---|
| Failed logins (per IP) | > 20 in 5 min | Slack / email |
| Failed logins (per account) | > 5 in 1 hour | Slack |
| Webhook signature verification failures | Any in 24h | Slack |
| 5xx error rate | > 1% for 5 min | PagerDuty |
| New admin account created | Any | Slack + email |
| Mass data export (> N records) | Any | Slack |

Tools that work for small teams:
- **Logtail / BetterStack** — log aggregation + alerting, generous free tier.
- **Sentry** — exceptions + custom events; alert on auth/payment failure exceptions.
- **Cloudflare Logpush + Workers** — for sites already on Cloudflare.
- **Datadog / New Relic** — full APM + security; pricier.

### 5. Quarterly re-audit + monthly check-in — *required*

- **Monthly**: skim CI security workflow runs for failed audits, check secret scanning hits.
- **Quarterly**: re-open `security-audit.md` and run Phase 0 → 13 from scratch. Compare to last audit's findings. New code = new attack surface.
- **After major releases**: targeted re-audit of changed areas (new auth flow, new payment integration, new file upload feature).

Without an assigned person and a calendar event, the quarterly review will not happen.

---

## RUNTIME PROTECTIONS

### Web Application Firewall (WAF)

If on Cloudflare, Vercel, or AWS, the platform-level WAF is the most cost-effective layer. Enable:
- **OWASP Core Rule Set** — generic injection / XSS patterns.
- **Rate limiting** — per-IP and per-route, tighter on auth endpoints.
- **Bot management** — Cloudflare Turnstile / Vercel Bot Protection on public auth forms.
- **Geo-blocking** — only if your user base is geographically scoped (e.g., Lymora's user base is Nigerian universities; blocking traffic from regions with no users reduces noise without losing signal).

### CSP report-uri / report-to

Once CSP is deployed, route violation reports somewhere you'll read:

```
Content-Security-Policy: ...; report-uri /csp-report; report-to csp-endpoint
Reporting-Endpoints: csp-endpoint="https://yourdomain.com/csp-report"
```

Set up a `/csp-report` endpoint that logs violations. Most violations are noise (browser extensions), but real attacks show up here. Filter aggressively, then alert on the rest.

### Subresource Integrity (SRI)

For any third-party `<script>` you cannot self-host:
```html
<script src="https://cdn.example.com/lib.js"
        integrity="sha384-Bx...="
        crossorigin="anonymous"></script>
```

If the CDN is compromised, the browser refuses the modified script.

### Database query monitoring

- **N+1 query alerts** — Laravel Telescope (in dev), Spatie laravel-query-detector, or APM-based detection. N+1 isn't strictly a security issue but can become a DoS amplifier.
- **Long-running queries** — alert on queries > 5s. Often indicates a missing `WHERE` clause or accidentally unbounded export.

---

## INCIDENT RESPONSE BASICS

If a real incident happens (leaked credential, suspected breach, anomalous access), having the runbook ready saves hours.

### Minimal runbook — keep this in `INCIDENT.md` or your wiki

**0. Stay calm. Don't `force-push --hard` anything in panic.**

**1. Contain (first 30 min)**
- Rotate the affected secret immediately (Stripe / Paystack / AWS dashboards).
- If suspected RCE: take the affected instance offline, snapshot disk for forensics.
- If suspected data exfiltration: revoke session tokens en masse (Laravel: `php artisan session:flush`).
- Preserve logs — don't restart the server before snapshotting if you can avoid it.

**2. Assess (next 2h)**
- What was exposed? For how long?
- Which users / records?
- Was there exfiltration evidence (large response sizes, anomalous IPs in access logs)?

**3. Notify (within 24–72h depending on jurisdiction)**
- Affected users (if PII or financial data is involved).
- Regulators (GDPR: 72h; NDPR for Nigeria: 72h to NDPC).
- Payment processors (Stripe / Paystack require notification of security incidents involving their data).

**4. Remediate**
- Patch the underlying vulnerability (audit + implement modes).
- Rotate any other secrets that may have been adjacent (defense in depth).
- Review logs for evidence of additional pivot points.

**5. Post-mortem (within 1 week)**
- What was the root cause?
- Why was it not caught (audit gap, monitoring gap, dependency gap)?
- What changes prevent the same failure?
- Update audit/implement/monitor playbooks accordingly.

---

## PRE-DEPLOYMENT SECURITY CHECKLIST

Run before every production deploy. The smallest checklist that catches the bulk of regressions.

**Secrets & credentials**
- [ ] `.env` in `.gitignore` and not tracked
- [ ] `.env.example` contains only placeholder values
- [ ] No hardcoded API keys / passwords / secrets in source
- [ ] gitleaks (or equivalent) passes in CI
- [ ] CI/CD uses secret env vars, not hardcoded YAML
- [ ] Test/live keys properly separated per environment

**Authentication**
- [ ] Passwords hashed with bcrypt cost ≥ 12 (or Argon2id)
- [ ] Login endpoint rate limited (≤ 10/min per IP)
- [ ] Password reset tokens: time-limited, single-use, hashed in DB
- [ ] Session cookies: HttpOnly, Secure, SameSite=Lax (or Strict for admin)
- [ ] Session regenerated on login

**Authorization**
- [ ] Every auth-required route has middleware
- [ ] Admin routes have role/permission middleware (not just auth)
- [ ] Every resource fetch verifies ownership (no IDOR)
- [ ] Mass assignment protected (`$fillable` allowlist or FormRequests)
- [ ] Sensitive fields (`role`, `is_admin`, `balance`) NOT in `$fillable`
- [ ] Server Actions / RPC endpoints have explicit auth checks

**Injection**
- [ ] No raw SQL with string interpolation — all queries parameterized
- [ ] No `{!! $var !!}` in Blade with user content
- [ ] No `innerHTML =` / `dangerouslySetInnerHTML` with user content
- [ ] File uploads: MIME + extension allowlist server-side
- [ ] Uploaded files stored outside web root

**Payment**
- [ ] Webhook signatures verified before processing
- [ ] Amount calculated server-side, never from request body
- [ ] Transaction verified via API before fulfillment
- [ ] Webhook handler is idempotent (duplicates safe)
- [ ] No test keys in production

**Misconfiguration**
- [ ] `APP_DEBUG=false` in production
- [ ] Dev tools (Telescope / Horizon / Debugbar) gated by auth
- [ ] HTTPS enforced, HTTP → HTTPS redirects
- [ ] Security headers present (verify at `securityheaders.com`)
- [ ] CSP uses nonce + `strict-dynamic` (not `'unsafe-inline'`)
- [ ] Server version disclosure disabled
- [ ] `.env` and `.git` blocked at web server

**Cryptography**
- [ ] No MD5 / SHA1 for security purposes
- [ ] Tokens generated with `random_bytes()` / `crypto.randomBytes()`
- [ ] `hash_equals()` / `crypto.timingSafeEqual()` for token comparison
- [ ] All sensitive API calls over HTTPS

**Dependencies**
- [ ] `composer audit` passes (no HIGH/CRITICAL)
- [ ] `npm audit --audit-level=high` passes
- [ ] Framework version supported (not EOL)
- [ ] Lockfile committed (`composer.lock`, `package-lock.json`)

**Logging**
- [ ] Auth events logged (login, logout, failures)
- [ ] Payment events logged
- [ ] Logs do NOT contain passwords, full tokens, full card numbers
- [ ] Logs shipped to external service (not only on disk)
- [ ] At least one alert configured (failed login spike, webhook signature failure)

**Error handling**
- [ ] Generic error messages to users (no stack traces)
- [ ] Exceptions logged server-side, not returned in responses
- [ ] No `==` for security comparisons in PHP

**Frontend (if applicable)**
- [ ] No JWTs / auth tokens in localStorage
- [ ] CSP configured with nonce + strict-dynamic
- [ ] No `NEXT_PUBLIC_*` / `VITE_*` containing secrets
- [ ] iframes have `sandbox` attribute
- [ ] Server Actions validate caller identity

---

## ALERTING THRESHOLDS REFERENCE

When tuning alerts, start with these defaults and adjust based on traffic:

| Signal | Quiet hours threshold | Peak hours threshold |
|---|---|---|
| Failed logins per IP | > 10 in 5 min | > 30 in 5 min |
| Failed logins per account | > 3 in 1h | > 5 in 1h |
| 401/403 response rate | > 5% for 5 min | > 10% for 5 min |
| 500 response rate | > 0.5% for 5 min | > 1% for 5 min |
| Webhook signature failures | Any in 1h | > 5 in 1h |
| New admin role assignments | Any | Any |
| Bulk data export | > 1000 rows in 1 query | > 10000 rows in 1 query |
| `NEXT_PUBLIC_*` env var added in PR | Any | Any |

---

## RE-AUDIT TRIGGERS

Reopen `security-audit.md` and run the full audit when:

- A new payment integration ships
- A new authentication flow ships (passwordless, SSO, magic links)
- A new user-uploaded data feature ships (file upload, rich text, profile imports)
- A major dependency upgrade lands (Laravel major version, Next.js major version, ORM upgrade)
- A CVE drops on a dependency you use
- An incident occurs (full re-audit post-remediation)
- Quarterly cadence (whichever comes first)

---

*End of monitor mode. Security is a practice, not a project — keep the CI checks honest and the quarterly review on the calendar, and the rest follows.*

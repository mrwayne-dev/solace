# SECURITY AUDIT MODE
> Mode 1 of 3. Read `security-agent.md` first for the severity rubric, tooling reality check, OWASP mapping, and confidentiality rule.

**Goal of this mode:** produce a ranked, scored, tagged list of findings. Stop at the list. Do not patch.

---

## PHASE 0: STACK DETECTION

Map the codebase before auditing. Do not assume.

### Languages

- PHP (version? Laravel version? plain PHP?)
- JavaScript / TypeScript (Node version? Express / Fastify / Hono / Next.js API routes?)
- Python (Django / Flask / FastAPI?)
- Go / Ruby on Rails / Java + Spring Boot / .NET / NestJS / other?
- Multiple languages (e.g., PHP backend + React frontend)?

### Frontend

- React / Next.js / Vue / Nuxt / SvelteKit / Vanilla JS
- Inline event handlers (`onclick`, `onload` in HTML)?
- Frontend rendering user-provided content?
- Templating engine (Blade / Twig / Jinja2 / EJS / Handlebars)?
- App Router (RSC) vs Pages Router for Next.js — affects hydration attack surface

### Auth

- Session-based (PHP sessions, Express sessions)
- Token-based (JWT, Laravel Sanctum / Passport, custom)
- OAuth / social login
- Third-party auth (Firebase Auth, Auth0, Clerk, Supabase, Cognito, NextAuth)

### Database

- Raw SQL queries?
- Query builder / ORM (Eloquent / Knex / Drizzle / Sequelize / Prisma / SQLAlchemy / ActiveRecord / Hibernate / EF Core)?
- Engine (MySQL / Postgres / MongoDB / SQLite / Redis)?

### Payments

- Stripe / Paystack / Flutterwave / PayPal / LemonSqueezy / Paddle / other?
- Webhook endpoints present?
- Custom amount logic (vs price-from-DB)?

### File uploads

- User-uploaded files at all?
- Storage (local disk / S3 / Cloudinary / DigitalOcean Spaces / Cloudflare R2)?

### External requests

- Server makes HTTP requests based on user-supplied URLs or data? (SSRF risk)
- Which third-party APIs?

### Infrastructure

- `.env` file present?
- `docker-compose.yml` — credentials in it?
- CI/CD config (`.github/workflows`, `.gitlab-ci.yml`, `Jenkinsfile`)?
- Web server config (`nginx.conf`, `.htaccess`, `Caddyfile`)?
- Lockfiles (`composer.lock`, `package-lock.json`, `yarn.lock`, `Pipfile.lock`)?

### Files to read

- `.env`, `.env.example`, `.env.production`, `.env.staging` — check `.example` for accidentally-real values
- `composer.json` / `package.json` / `requirements.txt` / `go.mod` / `Gemfile` / `pom.xml` — full dependency tree
- `config/` — hardcoded values?
- `routes/` / `api/` / route files — list every exposed endpoint
- `middleware/` — what auth/rate-limit middleware is applied where?
- `git log --oneline -30` — recent security commits hint at past exposures
- `.gitignore` — is `.env` actually listed?

### Output

A two-paragraph stack summary + a table of attack surfaces:

```
| Surface | Present? | Notes |
|---|---|---|
| Public web routes | Yes | `routes/web.php` — N routes, M with auth middleware |
| API routes | Yes | `routes/api.php` — N routes |
| Webhooks | Yes | Paystack at `/webhook/paystack` |
| File uploads | Yes | Stored in `storage/app/public/uploads/` (concerning — public) |
| External fetches | No | No user-controlled URL fetching detected |
| OAuth / SSO | No | Email/password only |
| Admin panel | Yes | `/admin/*`, role-gated |
```

Then proceed to Phase 1.

---

## PHASE 1: SECRETS & CREDENTIAL AUDIT [A02]

**Highest priority. Run first. A leaked secret is an immediate breach.**

### 1.1 — Hardcoded secrets in source

```bash
# Scan source for embedded credentials
grep -rn "sk_live_\|pk_live_\|sk_test_\|PAYSTACK_SECRET\|STRIPE_SECRET" . \
  --include="*.php" --include="*.js" --include="*.ts" --include="*.json" \
  --exclude-dir=node_modules --exclude-dir=vendor

grep -rn "password\s*=\s*['\"][^'\"]\|API_KEY\s*=\s*['\"]\|SECRET\s*=\s*['\"]" . \
  --include="*.php" --include="*.js" --include="*.ts"

grep -rn "-----BEGIN" . \
  --include="*.php" --include="*.js" --include="*.ts" --include="*.json" --include="*.pem"
```

For every match: real credential or placeholder (`your_key_here`)? In a Git-tracked file?

**Per-secret severity (use rubric in master):**
Real prod credential in source = `Score: 14` (Exploit 3 / Priv 3 / UI 3 / Blast 2–3 / Impact 3) → **CRITICAL**.

**Mask in output.** Report as `Stripe live key found in src/config/payment.php:23 (masked: sk_live_***)`. Never paste the full value.

### 1.2 — Git history scan

```bash
# Manual scan
git log --all -p | grep -iE "(api_key|secret|password|token|private_key|sk_live|sk_test|paystack_secret)\s*[=:]\s*['\"]?[A-Za-z0-9+/]{8,}"

# If gitleaks is available [REQUIRES USER if not installed]:
gitleaks detect --source . --verbose --no-banner
```

If `git log` is unavailable in your harness, mark this `[REQUIRES USER]` and ask them to run gitleaks and paste output.

**A historical secret exposure is a breach until rotated**, even if "removed" in a later commit. CRITICAL.

**Remediation:**
1. Rotate the secret with the provider (Stripe/Paystack/AWS dashboard).
2. Optionally clean history (coordinate with team; force-push required):
   ```bash
   git filter-repo --replace-text <(echo 'literal:OLD_SECRET==>REDACTED')
   ```

### 1.3 — Environment file exposure

```bash
cat .gitignore | grep -E "\.env|\.secret|config\.local"
git ls-files | grep "\.env"
diff -q .env .env.example 2>/dev/null
```

Checks:
- `.env` in `.gitignore`? If not → CRITICAL.
- `.env` actually tracked? (`git ls-files`)
- `.env.example` contains real values not placeholders?
- Backup variants (`.env.staging`, `.env.bak`, `.env.old`) tracked?

### 1.4 — Server-side credential exposure

**Laravel:**
- `APP_DEBUG=true` in production `.env` → CRITICAL (stack traces expose DB creds, file paths, all env vars)
- `APP_ENV=production` set?
- `APP_KEY` is real generated key, not empty/default?

**Web-accessible secrets check:** `[REQUIRES USER]` if no curl access
```bash
curl -sI https://yourdomain.com/.env
curl -sI https://yourdomain.com/.git/config
```
Either returning content = CRITICAL.

**Fix (Nginx) — block dotfile access:**
```nginx
location ~ /\. {
    deny all;
    return 404;
}
location ~* \.(env|git|gitignore|htaccess|log|sql|bak|zip)$ {
    deny all;
    return 404;
}
```

### 1.5 — CI/CD secret exposure

- Secrets stored as CI environment variables (safe) or hardcoded in workflow YAML (unsafe)?
- Workflow logs print env vars? (`echo $SECRET` in a run step is unsafe — even with masking, downstream tools can leak)
- Test files / fixtures with real test credentials committed?
- For matrix builds: secrets accidentally exposed to forks? (GitHub's `secrets.*` not available to fork PRs by default — verify)

---

## PHASE 2: AUTHENTICATION & SESSIONS [A07]

### 2.1 — Password storage

```bash
grep -rn "md5\|sha1\|SHA1\|MD5\|crypt(" . --include="*.php" --include="*.js"
grep -rn "password_hash\|bcrypt\|argon2\|Hash::make" . --include="*.php"
```

**Requirements:**
- bcrypt (cost ≥ 12), Argon2id, or scrypt. **Never** MD5/SHA1/SHA256/plaintext.
- Laravel: `Hash::make($password)` defaults to bcrypt cost 10 — bump to 12 in `config/hashing.php`.
- Node: `bcrypt.hash(password, 12)`.
- Password resets: time-limited (≤ 1h), single-use, hashed in DB.

**Severity examples:**
- Plaintext passwords stored = CRITICAL (Score 14: Exploit 3 / Priv 0 — DB compromise / UI 0 / Blast 3 / Impact 3 = with the DB-compromise prerequisite Priv ~1, total 10–14 depending on chain)
- MD5/SHA1 passwords = HIGH-CRITICAL depending on user count and reset path
- bcrypt cost < 10 = LOW (best-practice gap)

### 2.2 — Brute force & rate limiting

Endpoints that **must** be rate limited:
- `/login` (brute-force prevention)
- `/forgot-password` (enumeration prevention)
- `/register` (spam/abuse)
- `/api/*` (general abuse)
- `/verify-otp`, `/verify-2fa` (OTP enumeration)

**Laravel pattern:**
```php
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/login', [LoginController::class, 'store']);
    Route::post('/forgot-password', [PasswordController::class, 'store']);
});
```

Findings:
- No rate limit on login → CRITICAL (allows unlimited password guessing on real accounts)
- Login response distinguishes "user not found" vs "wrong password" → MEDIUM (enumeration)
- No account lockout after N failures → MEDIUM
- No CAPTCHA / hcaptcha / Turnstile on public auth → LOW (defense-in-depth gap)

### 2.3 — Session security

**Session-based:**
- Cookie attrs: `HttpOnly=true`, `Secure=true`, `SameSite=Lax` or `Strict`
- Session ID regenerated on login? (`session()->regenerate()` in Laravel)
- Sessions invalidated on logout (server-side, not just cookie deletion)
- Idle timeout configured (< 30 min for sensitive apps)

**JWT:**
- JWT secret strong (≥ 32 random chars)?
- `alg: none` accepted? CRITICAL — forges any token.
- `exp` claim set, short for access tokens (≤ 15 min)?
- JWTs in `localStorage` → HIGH (XSS turns into auth theft). Use `httpOnly` cookies.
- Refresh tokens rotated on each use?

### 2.4 — MFA

- MFA available for sensitive operations (admin, payment methods, data export)?
- TOTP replay protection (same code rejected on second use within window)?
- Account recovery path that bypasses MFA exists? If so, attacker can pivot through recovery — HIGH.

### 2.5 — OAuth / third-party

- `state` parameter validated on callback (CSRF on OAuth)?
- Redirect URI validated against an allowlist (open redirect via OAuth)?
- Tokens stored in cookies, not localStorage / URL?

---

## PHASE 3: AUTHORIZATION & ACCESS CONTROL [A01]

**A01 is the #1 OWASP category in 2025. Audit it like it matters.**

### 3.1 — Route-level authorization

```bash
# Laravel:
php artisan route:list --columns=method,uri,name,middleware
# If artisan unavailable [REQUIRES USER or fall back]:
grep -n "Route::" routes/*.php
```

Per route:
- Auth middleware (`auth`) on routes requiring login?
- Admin/management routes have role/permission middleware (not just `auth`)?
- Routes returning sensitive data without ownership check?

**IDOR pattern:**
```php
// VULNERABLE
Route::get('/orders/{id}', [OrderController::class, 'show']);

// Inside show($id) — no ownership check, returns Order::find($id)
// Any auth'd user reads any order by changing the ID

// SECURE
public function show(Order $order) {
    $this->authorize('view', $order);
    return $order;
}
```

### 3.2 — IDOR audit

```bash
grep -rn "find\|findOrFail\|where.*id" . --include="*.php" | grep "request\|param\|input"
```

For every match:
- Ownership check after fetch?
- User A can read/modify user B's data by changing an ID?
- UUIDs vs sequential ints? (UUIDs are not authorization, but they prevent enumeration)

IDOR exposing or modifying another user's data → CRITICAL (Exploit 3 / Priv 1 / UI 0 / Blast 3 / Impact 3 = 10+).

### 3.3 — Mass assignment

```bash
grep -rn "\$guarded\|\$fillable\|forceFill\|unguard\|request->all()" . --include="*.php"
```

- `$guarded = []` on a model = all fields mass-assignable → HIGH
- `$request->all()` directly into `Model::create()` → HIGH
- Privilege fields (`is_admin`, `role`, `balance`, `verified`, `email_verified_at`) in `$fillable` → CRITICAL

**Fix:**
```php
protected $fillable = ['name', 'email', 'address']; // no privilege fields
User::create($request->validated()); // FormRequest, allowlist
// or
User::create($request->only(['name', 'email', 'password']));
```

### 3.4 — Privilege escalation

- User can modify own `role`, `is_admin`, `permissions` via profile update?
- Admin functions checked at controller level, not just hidden in UI?
- Horizontal escalation (user A acting as user B via parameter manipulation)?

### 3.5 — API authorization

- API endpoints applying same authorization as web routes?
- Unauth'd access to non-public API endpoints?
- API keys scoped to minimum permission? (Mobile app key shouldn't have admin scope.)

---

## PHASE 4: INJECTION [A05]

### 4.1 — SQL injection

```bash
grep -rn "DB::select\|DB::statement\|whereRaw\|selectRaw\|orderByRaw" . --include="*.php"
grep -rn "query\|execute\|prepare" . --include="*.php" | grep "\$_\|input\|request"
```

```php
// VULNERABLE
DB::select("SELECT * FROM users WHERE email = '$email'");

// SECURE
DB::select("SELECT * FROM users WHERE email = ?", [$email]);
User::where('email', $email)->get();
```

**Column injection:** `User::orderBy($request->sort)` — user controls column name → HIGH.
Fix: `$request->validate(['sort' => 'in:name,created_at,price']);`

### 4.2 — Cross-site scripting (XSS)

```bash
grep -rn "{!!" . --include="*.blade.php"
grep -rn "innerHTML\|outerHTML\|document.write\|insertAdjacentHTML\|dangerouslySetInnerHTML" . \
  --include="*.js" --include="*.jsx" --include="*.tsx" --include="*.vue"
```

- `{!! $var !!}` in Blade with user content → HIGH
- `innerHTML = userInput` → CRITICAL
- `dangerouslySetInnerHTML={{ __html: userInput }}` → CRITICAL
- Reflected XSS via URL params → HIGH

**Fix for rich-text user HTML:**
```php
// Laravel: mews/purifier
$clean = clean($userInput);
```
```js
// JS: DOMPurify
const clean = DOMPurify.sanitize(userHTML);
```

CSP is defense-in-depth (Phase 7) — never the primary mitigation.

### 4.3 — Command injection

```bash
grep -rn "exec\|shell_exec\|system\|passthru\|popen\|proc_open\|\`" . --include="*.php"
grep -rn "child_process\|spawn\|execSync" . --include="*.js" --include="*.ts"
```

Any user input in a shell command without `escapeshellarg()` / equivalent → CRITICAL. Always prefer non-shell APIs.

### 4.4 — Server-side template injection (SSTI)

```bash
grep -rn "render\|compile\|eval\|template" . --include="*.php" --include="*.js" | grep "input\|request\|param\|user"
```

- `Blade::render($userInput)` with user-controlled template = full RCE → CRITICAL
- `ejs.render(userInput)`, `handlebars.compile(userInput)` → CRITICAL

### 4.5 — File upload injection

```bash
grep -rn "upload\|->store\|->move\|file('\|image('" . --include="*.php"
```

Per upload site:
- MIME type validated server-side (not just client)?
- Extension allowlist (not just `.php` — also `.phar`, `.php3`, `.php5`, `.phtml`, `.pht`)?
- Stored outside web root, or in dir with PHP execution disabled?
- Filename sanitized (path traversal: `../../etc/passwd`)?
- Max file size enforced?

```php
// SECURE Laravel pattern
$request->validate([
    'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
]);
$path = $request->file('file')->store('uploads', 'private'); // disk = 'private', outside public/
```

PHP execution allowed in upload dir = CRITICAL (RCE).

---

## PHASE 5: PAYMENT & FINANCIAL SECURITY

Payment bugs lose money and trust. Treat them as a category of their own.

### 5.1 — Webhook signature verification

```bash
grep -rn "webhook\|paystack\|stripe\|flutterwave" . --include="*.php" --include="*.js" --include="*.ts"
```

For every payment webhook:
- Signature verified **before** processing?
- Raw body used (parsed JSON fails — order/whitespace matters)?
- Webhook secret in env, not source?

**Paystack (Laravel):**
```php
public function webhook(Request $request) {
    $signature = $request->header('x-paystack-signature');
    $computed = hash_hmac('sha512', $request->getContent(), config('services.paystack.secret_key'));
    if (!hash_equals($computed, $signature)) {
        return response('Unauthorized', 401);
    }
    // process only after this passes
}
```

**Stripe (Laravel):**
```php
public function webhook(Request $request) {
    try {
        $event = \Stripe\Webhook::constructEvent(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            config('services.stripe.webhook_secret')
        );
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        return response('Invalid signature', 400);
    }
}
```

No signature verification → CRITICAL (Score 15 max — anyone can fabricate `payment.success`).

### 5.2 — Amount manipulation

The single biggest payment mistake: trusting `amount` from the client.

```php
// CRITICAL VULN
$amount = $request->input('amount'); // attacker sends 1
Paystack::initializeTransaction(['amount' => $amount]);

// SECURE
$order = Order::findOrFail($request->order_id);
$this->authorize('pay', $order);
$amount = $order->calculateTotalKobo(); // server-side, from DB
Paystack::initializeTransaction(['amount' => $amount, 'reference' => $order->reference]);
```

Reject zero / negative amounts explicitly.

### 5.3 — Verify before fulfilling

After payment redirect or webhook:
- Status verified via gateway API before granting access / shipping / activating?
- `reference` / `transaction_id` unique and tied to a specific order (replay protection)?
- Idempotency on order fulfillment?

```php
$resp = Paystack::getPaymentData($reference);
if ($resp['data']['status'] !== 'success') abort(402);
if ($resp['data']['amount'] < $expectedKobo) abort(402, 'Amount mismatch');
$order->markAsPaid(); // only now
```

### 5.4 — Idempotency

Most gateways retry on 5xx. Without idempotency, retries double-fulfill.

```php
if (WebhookEvent::where('event_id', $event->id)->exists()) {
    return response('Already processed', 200);
}
WebhookEvent::create(['event_id' => $event->id]);
// process once
```

### 5.5 — Key scope & separation

- Test/live keys never mixed (test key in prod, live key in dev)
- Keys scoped to minimum permission (read-only for dashboards)
- Secret key never in client code → CRITICAL if found in `NEXT_PUBLIC_*`, `VITE_*`, frontend bundle, or mobile app binary

---

## PHASE 6: SECURITY MISCONFIGURATION [A02]

A02 is OWASP #2 in 2025 — the most common real-world finding.

### 6.1 — Debug & error mode

```bash
grep -rn "APP_DEBUG\|debug=True\|DEBUG\s*=\s*True\|display_errors" .env config/ \
  --include="*.php" --include="*.env"
```

- `APP_DEBUG=true` in prod → CRITICAL (Laravel error pages leak DB creds, file paths, env vars)
- PHP `display_errors = On` in prod → CRITICAL
- Detailed framework error pages to end users → HIGH

```php
// Laravel — must default false
'debug' => env('APP_DEBUG', false),
```

### 6.2 — Exposed dev tools

- Laravel Telescope accessible without auth in prod (`/telescope`)?
- Horizon without auth (`/horizon`)?
- Debugbar visible in prod?
- Service provider gates (`Gate::define('viewTelescope', ...)`) configured?

### 6.3 — HTTP security headers

```bash
# [REQUIRES USER if no curl access]
curl -I https://yourdomain.com | grep -i "x-frame\|content-security\|strict-transport\|x-content-type\|referrer\|permissions"
```

| Header | Missing severity | Notes |
|---|---|---|
| `Strict-Transport-Security` | HIGH | MITM, protocol downgrade |
| `Content-Security-Policy` | HIGH | XSS amplification (see 6.3.1) |
| `X-Content-Type-Options: nosniff` | MEDIUM | MIME sniffing attacks |
| `X-Frame-Options` or CSP `frame-ancestors` | MEDIUM | Clickjacking |
| `Referrer-Policy` | LOW | Info leak |
| `Permissions-Policy` | LOW | Feature reduction |

**Nginx baseline:**
```nginx
add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'nonce-$cspNonce' 'strict-dynamic'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none';" always;
```

#### 6.3.1 — CSP nonce strategy (modern frontend)

`script-src 'self' 'unsafe-inline'` is not a CSP — it's a placebo. A real CSP uses **per-request nonces** + `'strict-dynamic'`:

```php
// Laravel middleware: generate nonce per request
$nonce = base64_encode(random_bytes(16));
view()->share('cspNonce', $nonce);
$response->header('Content-Security-Policy',
    "script-src 'self' 'nonce-$nonce' 'strict-dynamic'; object-src 'none'; base-uri 'self';");
```
```blade
<script nonce="{{ $cspNonce }}">/* inline script */</script>
```

```js
// Next.js (App Router): generate nonce in middleware.ts
import { NextResponse } from 'next/server';
export function middleware(request) {
  const nonce = Buffer.from(crypto.randomUUID()).toString('base64');
  const csp = `script-src 'self' 'nonce-${nonce}' 'strict-dynamic'; object-src 'none'; base-uri 'self';`;
  const response = NextResponse.next();
  response.headers.set('Content-Security-Policy', csp);
  response.headers.set('x-nonce', nonce);
  return response;
}
```

Findings:
- CSP using `'unsafe-inline'` for `script-src` without nonce/hash → HIGH
- CSP using `'unsafe-eval'` without justification → HIGH
- No CSP at all on a site with user-generated content → HIGH

### 6.4 — CORS

```bash
grep -rn "CORS\|cors\|Access-Control-Allow-Origin\|AllowedOrigins" . \
  --include="*.php" --include="*.js" --include="*.ts"
```

Red flags:
- `Access-Control-Allow-Origin: *` with `Access-Control-Allow-Credentials: true` → CRITICAL (some implementations allow this combination by reflecting origin)
- `*` for authenticated APIs → HIGH
- Origin validated by `str_contains` / regex with bypassable patterns (e.g., regex matching `evil-yourdomain.com`) → HIGH

**Laravel `config/cors.php`:**
```php
'allowed_origins' => [env('FRONTEND_URL', 'https://yourdomain.com')], // exact match
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
'allowed_headers' => ['Content-Type', 'Authorization'],
'supports_credentials' => true, // only if cookies/auth needed
```

### 6.5 — Server version disclosure

`Server: nginx/1.18.0` and `X-Powered-By: PHP/8.2.1` are LOW findings each (info leak), but worth removing:

```nginx
server_tokens off;
```
```ini
# php.ini
expose_php = Off
```

### 6.6 — CSRF protection

```bash
grep -rn "csrf\|VerifyCsrfToken\|csrf_token\|@csrf" . --include="*.php" --include="*.blade.php"
```

- `VerifyCsrfToken` in `web` middleware group? (Laravel default — check it wasn't removed.)
- State-changing routes excluded from CSRF without good reason?
- Sanctum SPA auth requires CSRF cookie — verify.

```blade
<form method="POST">@csrf ...</form>
```

---

## PHASE 7: CRYPTOGRAPHIC FAILURES [A04]

### 7.1 — Data in transit

- HTTPS enforced everywhere? Mixed content?
- HTTP → HTTPS redirect?
- HSTS configured (preloaded)?
- Any plain HTTP API calls in code?

### 7.2 — Data at rest

```bash
grep -rn "encrypt\|decrypt\|Crypt::\|openssl_encrypt" . --include="*.php"
grep -rn "password\|ssn\|credit.card\|bank.account\|national.id\|nin\|bvn" . \
  --include="*.php" --include="*.js"
```

- Passwords hashed (one-way), not encrypted (reversible)?
- Sensitive fields (card numbers, SSNs, NINs, BVNs) encrypted at column level?
- Encryption key stored separately from encrypted data? (Same repo = unencrypted in practice.)
- DB backups encrypted?

### 7.3 — Weak crypto

```bash
grep -rn "md5\|sha1\|des\|RC4\|base64_encode\|base64_decode" . --include="*.php" --include="*.js"
```

- `md5()` / `sha1()` for security purposes (passwords, tokens, signatures) → CRITICAL
- `base64_encode()` of sensitive data treated as "encrypted" → HIGH (encoding ≠ encryption)
- `rand()` / `mt_rand()` / `uniqid()` for security tokens → HIGH

```php
// SECURE
$token = bin2hex(random_bytes(32)); // 64 hex chars, cryptographically random
```

### 7.4 — Token security

For every security token (password reset, email verify, API token, magic link):
- Generated with `random_bytes()` / equivalent?
- Time-limited (≤ 1h for password reset)?
- Single-use (invalidated after first use)?
- Stored as hash in DB? (Breach of DB shouldn't grant valid tokens.)
- Compared with `hash_equals()` (constant-time, no timing attacks)?

---

## PHASE 8: ADDITIONAL INJECTION VECTORS

### 8.1 — SSRF [consolidated into A01:2025]

```bash
grep -rn "Http::get\|file_get_contents\|curl\|Guzzle\|fetch\|axios" . \
  --include="*.php" --include="*.js" | grep "request\|input\|param\|url"
```

User-controlled URL fetched by server (avatar URL, webhook callback URL, "import from URL")?
Attacker provides `http://169.254.169.254/` (AWS/cloud metadata), `http://localhost/admin` (internal services), or DNS-rebound hosts.

```php
$url = $request->input('url');
$parsed = parse_url($url);
if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) abort(422);
if (!in_array($parsed['host'], $allowedHosts)) abort(422);
$ip = gethostbyname($parsed['host']);
if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
    abort(422, 'Private/reserved IP not allowed');
}
```

### 8.2 — Path traversal

```bash
grep -rn "file_get_contents\|readfile\|include\|require\|fopen" . \
  --include="*.php" | grep "input\|request\|param\|GET\|POST"
```

`include($_GET['page'])` is one of the most-exploited PHP patterns ever → CRITICAL.

```php
$allowed = ['home', 'about', 'contact'];
if (!in_array($request->page, $allowed, true)) abort(404);
include "pages/{$request->page}.php";
```

---

## PHASE 9: SUPPLY CHAIN [A03]

A03 is **new to top 3 in OWASP:2025** — highest average exploit + impact scores.

### 9.1 — Dependency vuln scan

```bash
# Each may be [REQUIRES USER] depending on harness
composer audit                       # PHP
npm audit --audit-level=high         # Node
pip-audit                            # Python
bundle audit                         # Ruby
cargo audit                          # Rust
go list -json -deps ./... | nancy sleuth  # Go
```

If commands aren't runnable: read `composer.lock` / `package-lock.json` and cross-reference advisory feeds (`[INFERRED]` tag, recommend user run scan).

For each HIGH/CRITICAL CVE:
- CVE number + description
- Vulnerable code path actually reachable in this app?
- Patched version available?

**Add to CI:**
```yaml
- name: PHP audit
  run: composer audit --abandoned=ignore
- name: Node audit
  run: npm audit --audit-level=high
```

### 9.2 — EOL framework / runtime

- Laravel 9 and below = EOL (verify current LTS at audit time)
- PHP 8.0 and earlier = EOL
- Node 16 and earlier = EOL
- Python 3.8 and earlier = EOL

Running EOL = HIGH-CRITICAL depending on exposure.

### 9.3 — Package integrity

- `composer.lock` / `package-lock.json` / `yarn.lock` committed? (Lockfile must be present and committed.)
- `npm ci` in CI/CD (strict lockfile) vs `npm install`?
- `composer install --no-dev` in production (strip dev deps)?
- Any direct GitHub URLs as dependencies (`"react": "github:user/fork"`) — supply-chain risk → MEDIUM-HIGH.

---

## PHASE 10: FILE UPLOAD & STORAGE

### 10.1 — Direct file access

- Files stored in `/public/uploads/` with PHP execution enabled = RCE → CRITICAL
- Should be: `storage/app/private/...` served through a controller with auth check.

```php
Route::get('/files/{filename}', function($filename) {
    $path = storage_path('app/private/uploads/' . $filename);
    if (!file_exists($path)) abort(404);
    // ownership/authorization check here
    return response()->file($path);
})->middleware('auth');
```

### 10.2 — Filename handling

- Original filenames used as storage filenames? → Path traversal, overwrite attacks
- Always: random UUID for stored name, original name in DB only for display.

```php
$filename = Str::uuid() . '.' . $request->file('upload')->extension();
$request->file('upload')->storeAs('uploads', $filename, 'private');
```

---

## PHASE 11: LOGGING & MONITORING [A09]

### 11.1 — Required security log events

- Auth: every login (success + failure), logout, password change, password reset request
- Authorization: every 403 / unauthorized response
- Payment: every attempt, success, failure, refund
- Account: email change, password change, role change
- Admin: user create/delete, data export
- Suspicious: rate-limit hits, CSRF failures, signature verification failures

```php
Log::channel('security')->warning('Failed login attempt', [
    'email' => $request->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

### 11.2 — Must NOT be logged

- Passwords (even wrong attempts)
- Full credit card / CVV
- Full API keys / secrets / tokens
- Full JWTs
- Full national IDs / SSNs / BVNs

Mask: log first/last 4 chars max, hash, or omit.

### 11.3 — Log injection & storage

- User input logged without newline stripping → log forgery
- Logs only on disk (compromised server = lost evidence)? → Ship to external (Logtail, Papertrail, Datadog, CloudWatch).
- Alerting on security events (multiple failed logins, mass export, admin creation)?

---

## PHASE 12: ERROR HANDLING [A10 — new 2025]

### 12.1 — Fail closed

When something fails or hits an unexpected state, deny access by default.

```php
// VULNERABLE — exception swallowed, code continues
try { $user = getUserFromToken($token); }
catch (Exception $e) { /* continue */ }
if ($user->isAdmin()) { ... } // unpredictable state

// SECURE — explicit denial
try {
    $user = getUserFromToken($token);
    if (!$user) throw new AuthException('Invalid token');
} catch (Exception $e) {
    abort(401, 'Unauthorized');
}
```

Check for:
- `try/catch` swallowing exceptions before privileged code
- Auth checks bypassable by causing an exception
- Payment fulfillment continuing after gateway error
- Null coalescing on security-critical variables without explicit failure

### 12.2 — Error message leakage

- Raw exception messages in API responses → HIGH (file paths, DB structure, library versions exposed)
- SQL errors returned directly → CRITICAL (confirms SQLi, reveals schema)

```php
// VULNERABLE
return response()->json(['error' => $e->getMessage()], 500);

// SECURE
Log::error('DB error', ['exception' => $e]);
return response()->json(['error' => 'An error occurred'], 500);
```

### 12.3 — Type juggling (PHP especially)

```bash
grep -rn "==\s*\$\|==\s*['\"]" . --include="*.php" | grep -i "token\|secret\|password\|hash"
```

`"0" == false` is true, `"admin" == 0` is true (older PHP), `"1abc" == 1` is true. Use `===` and `hash_equals()` for security comparisons.

---

## PHASE 13: MODERN FRONTEND ATTACK SURFACE [NEW]

The 2026 frontend has attack surfaces v1 didn't cover. Audit explicitly.

### 13.1 — React Server Components / Next.js App Router

- Server Component imports something with side effects on the client? (Server-only secrets leaking via bundle?)
- Server Action accepts user input and performs privileged DB writes without authorization check? (Same IDOR concerns as API routes — server actions are RPC endpoints.)
- `'use server'` functions not validating caller identity?

```ts
// VULNERABLE — server action with no auth check
'use server';
export async function deletePost(id: number) {
  await db.posts.delete({ where: { id } });
}

// SECURE
'use server';
export async function deletePost(id: number) {
  const session = await auth();
  if (!session?.user) throw new Error('Unauthenticated');
  const post = await db.posts.findUnique({ where: { id } });
  if (post.authorId !== session.user.id) throw new Error('Forbidden');
  await db.posts.delete({ where: { id } });
}
```

### 13.2 — Hydration & SSR data leakage

- Sensitive data rendered into the initial HTML / `__NEXT_DATA__` that shouldn't reach the client (admin flags, internal IDs, full user records)?
- `getServerSideProps` returning full user object including `password_hash`, even if not displayed → HIGH (it's in the page source).

### 13.3 — `NEXT_PUBLIC_*` / `VITE_*` env leakage

```bash
grep -rn "NEXT_PUBLIC_\|VITE_\|REACT_APP_" . --include="*.ts" --include="*.tsx" --include="*.js" --include="*.env*"
```

Any secret key, DB credential, payment secret, JWT signing key prefixed with these = baked into the client bundle = public → CRITICAL.

### 13.4 — iframe sandboxing

- App embeds third-party content in iframes without `sandbox` attribute? (User-uploaded HTML, marketing iframes, legacy widgets.)
- Sandbox missing or too permissive (`sandbox="allow-scripts allow-same-origin"` defeats the purpose if same-origin)?

```html
<iframe src="..." sandbox="allow-scripts" referrerpolicy="no-referrer"></iframe>
```

### 13.5 — XS-Leaks (cross-origin information leakage)

- `Cross-Origin-Opener-Policy: same-origin` set on auth-relevant pages?
- `Cross-Origin-Resource-Policy: same-origin` on API responses?
- `Cross-Origin-Embedder-Policy: require-corp` if using SharedArrayBuffer / cross-origin isolation?

XS-Leaks abuse browser side channels (cache, frame counts, COOP) to infer auth state, presence of resources, etc. Most apps don't need full COOP/COEP, but auth pages should at least have COOP.

### 13.6 — Browser storage hygiene

- localStorage / sessionStorage used for auth tokens? → HIGH (covered in 2.3, restated here for completeness)
- IndexedDB storing sensitive data without encryption?
- Service Worker caching authenticated responses (could leak across users on shared devices)?

### 13.7 — `SameSite` cookie nuances

- Auth cookies with `SameSite=None` (required for cross-site contexts) — also have `Secure`?
- `SameSite=Lax` (default) — be aware top-level GET navigations still send the cookie. State-changing operations should never be GET.
- `SameSite=Strict` for the most sensitive cookies (admin session)?

### 13.8 — Edge runtime isolation (Next.js, Cloudflare Workers, Deno Deploy)

- Edge functions accessing secrets via env vars only (not bundled)?
- Edge KV / Durable Object / D1 access scoped per tenant?
- Edge runtime missing Node-specific protections (e.g., no `crypto.timingSafeEqual` in older edge runtimes — use Web Crypto)?

### 13.9 — Browser extension / userscript injection (defensive)

- App relies on DOM elements being unmodified for security checks? (Browser extensions inject freely; never trust the DOM as a security boundary.)
- Sensitive inputs (payment forms) inside iframes from PCI-compliant providers, so extensions can't read them directly?

---

## OUTPUT: AUDIT RESULTS

Output exactly this format. Stop after the list.

```
## SECURITY AUDIT RESULTS

**Stack:** [detected stack]
**Auth method:** [session / JWT / OAuth / etc.]
**Payment integrations:** [Stripe / Paystack / etc.]
**File uploads:** [yes/no + storage location]
**Total findings:** X CRITICAL, X HIGH, X MEDIUM, X LOW
**Verification breakdown:** X VERIFIED, X INFERRED, X REQUIRES USER

---

### CRITICAL — patch before next deploy

#### C1. [Finding title]
- **OWASP:** A0X
- **Score:** 14 (Exploit 3 / Priv 3 / UI 3 / Blast 2 / Impact 3)
- **Verification:** [VERIFIED] at app/Http/Controllers/OrderController.php:42
- **Description:** [what's wrong]
- **Attack:** [how an attacker exploits it]
- **Fix:** [exact code change or config change]

#### C2. ...

### HIGH — patch within 48h
[same format]

### MEDIUM — patch within current sprint
[same format]

### LOW — schedule
[same format]

### REQUIRES USER — needs verification before classifying
- [REQUIRES USER] [Suspected issue] — please run `[exact command]` and share output

---

**Attack chains identified:**
[List chained vulnerabilities, e.g., "L2 + M3 + H1 = unauthenticated admin takeover"]

**Estimated breach impact if CRITICAL items unresolved:**
[Concrete: "Attacker gains: live Paystack secret key, admin DB access, all customer PII"]

**Items requiring explicit business decision before fix:**
[e.g., "rotating prod Stripe keys requires coordination with finance"]

---

**Next step:** review findings. When ready, say "patch CRITICAL" or specify items, and the agent will open `security-implement.md`.
```

---

*End of audit mode. No patches without explicit user approval.*

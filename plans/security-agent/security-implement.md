# SECURITY IMPLEMENT MODE
> Mode 2 of 3. Read `security-agent.md` first. This file is opened only after the audit produced a findings list and the user has approved patching.

**Goal:** apply fixes from the audit, one finding at a time, with diffs and attack-vector explanations.

---

## ORDER OF OPERATIONS

1. **Rotate exposed secrets first.** Before any code changes — credentials, keys, tokens that were found leaked. This is non-negotiable. Code patches with the leaked secret still valid is closing a door while leaving the window open.
2. **CRITICAL findings**, in audit-list order.
3. **Re-audit or request re-audit** after CRITICAL is done — sometimes fixes introduce new issues (rate limiting added, but lockout policy now allows DoS).
4. **HIGH findings.**
5. **MEDIUM and LOW** in next sprint, separate PRs.

**Never batch.** One finding → one commit → one diff. Security regressions need to be bisectable.

---

## PER-FIX WORKFLOW

For every finding:

1. **State the finding.**
   ```
   Patching: [finding ID + title from audit]
   OWASP: A0X
   Severity: CRITICAL/HIGH/MEDIUM/LOW (Score: N)
   ```
2. **Show the vulnerable code.** Actual code from the repo, not pseudocode.
3. **Show the fixed code.** Diff format.
4. **Explain the attack vector.** What does this fix prevent? Be specific.
5. **Note regression risks.** Does the fix change behavior? Need a deploy ordering? Coordinate with other systems?
6. **Verify.** Run tests, lint, the relevant code path. If you can't run, ask user to verify.

**Do not patch the second issue until the first is approved.** The user reviews each diff before continuing.

### Special handling for credential rotation

When the fix is "rotate exposed secret":
1. Tell the user **exactly what to rotate and where** (Paystack dashboard URL, Stripe key rotation steps, AWS console path).
2. **Do not generate the new secret yourself.** Get it from the provider's dashboard or rotation flow.
3. Once rotated, update `.env` (locally) and CI/CD secret store.
4. Verify the old secret is invalidated (Paystack/Stripe dashboards show key history).
5. Then deploy the code that uses the new secret.
6. Optionally clean Git history afterward (force-push coordination).

---

## CORE PLAYBOOKS

### PHP / Laravel hardening

```
Minimum secure configuration:
- APP_DEBUG=false in production (verify in .env.production)
- APP_ENV=production
- Strong APP_KEY (php artisan key:generate, never committed)
- DB credentials separate per environment
- Session: HttpOnly=true, Secure=true, SameSite=Lax
  Set in config/session.php:
    'http_only' => true,
    'secure' => true,
    'same_site' => 'lax',
- Passwords: Hash::make() with cost ≥ 12
  config/hashing.php:
    'bcrypt' => ['rounds' => 12],

php.ini production:
  expose_php = Off
  display_errors = Off
  log_errors = On
  error_log = /var/log/php_errors.log
  allow_url_include = Off
  disable_functions = exec,passthru,shell_exec,system,proc_open,popen
  session.cookie_httponly = 1
  session.cookie_secure = 1
  session.cookie_samesite = "Lax"

Artisan caches (run after deploy):
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan event:cache

Block .env / .git at web server:
  See Phase 6 in audit mode.

Recommended packages:
  composer require spatie/laravel-permission   # RBAC
  composer require mews/purifier               # HTML sanitization
  composer require pragmarx/google2fa-laravel  # 2FA
  composer require spatie/laravel-csp          # CSP with nonce support
```

---

### Node.js / Express hardening

```
Required packages:
  npm install helmet express-rate-limit express-validator bcrypt cookie-parser

Helmet baseline (with CSP nonce):
  import helmet from 'helmet';
  import crypto from 'node:crypto';

  app.use((req, res, next) => {
    res.locals.cspNonce = crypto.randomBytes(16).toString('base64');
    next();
  });

  app.use(helmet({
    contentSecurityPolicy: {
      directives: {
        defaultSrc: ["'self'"],
        scriptSrc: ["'self'", (_req, res) => `'nonce-${res.locals.cspNonce}'`, "'strict-dynamic'"],
        objectSrc: ["'none'"],
        baseUri: ["'self'"],
        frameAncestors: ["'none'"],
        upgradeInsecureRequests: [],
      },
    },
    hsts: { maxAge: 63072000, includeSubDomains: true, preload: true },
  }));

Rate limiting:
  import rateLimit from 'express-rate-limit';
  const authLimiter = rateLimit({ windowMs: 60_000, max: 5 });
  app.post('/login', authLimiter, ...);

Input validation:
  body('email').isEmail().normalizeEmail(),
  body('amount').isInt({ min: 1, max: 1_000_000 }),
  body('password').isLength({ min: 8 })

Cookies:
  res.cookie('session', token, {
    httpOnly: true, secure: true, sameSite: 'lax',
    maxAge: 1000 * 60 * 60 * 8,
  });

Never:
  - JWT in localStorage — use httpOnly cookies
  - eval() with any user input
  - req.body straight to DB without validation
  - Log req.body in production (passwords, tokens)
```

---

### Next.js (App Router) frontend security

```
Token storage:
- NEVER localStorage / sessionStorage for auth tokens
- Use httpOnly cookies set by route handlers / server actions
- For SPA-only state: in-memory only, accept loss on refresh

Env vars:
- NEXT_PUBLIC_* are public — assume the value is on every user's screen
- Server-side env vars (no NEXT_PUBLIC_ prefix) for secrets

CSP with nonce (middleware.ts):
  import { NextResponse } from 'next/server';
  export function middleware(request) {
    const nonce = Buffer.from(crypto.randomUUID()).toString('base64');
    const csp = [
      "default-src 'self'",
      `script-src 'self' 'nonce-${nonce}' 'strict-dynamic'`,
      "style-src 'self' 'unsafe-inline'",
      "img-src 'self' data: https:",
      "font-src 'self'",
      "object-src 'none'",
      "base-uri 'self'",
      "form-action 'self'",
      "frame-ancestors 'none'",
    ].join('; ');
    const headers = new Headers(request.headers);
    headers.set('x-nonce', nonce);
    const response = NextResponse.next({ request: { headers } });
    response.headers.set('Content-Security-Policy', csp);
    response.headers.set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');
    response.headers.set('X-Content-Type-Options', 'nosniff');
    response.headers.set('Referrer-Policy', 'strict-origin-when-cross-origin');
    return response;
  }

  // In layout.tsx:
  import { headers } from 'next/headers';
  const nonce = headers().get('x-nonce');
  // Pass to <Script> components: <Script nonce={nonce} ... />

Server Actions:
  'use server';
  import { auth } from '@/lib/auth';

  export async function deletePost(id: number) {
    const session = await auth();
    if (!session?.user) throw new Error('Unauthenticated');
    const post = await db.posts.findUnique({ where: { id } });
    if (!post || post.authorId !== session.user.id) throw new Error('Forbidden');
    await db.posts.delete({ where: { id } });
  }

XSS prevention:
- Never dangerouslySetInnerHTML with user content
- If unavoidable, sanitize: DOMPurify.sanitize(html, { USE_PROFILES: { html: true } })
- Validate URL schemes for href: only http/https, no javascript:
```

---

## STACK-SPECIFIC PATTERNS (reference)

The audit and core playbooks use Laravel/Node as worked examples. Equivalent patterns for other stacks below.

### Django / Python

```python
# Settings (production)
DEBUG = False
ALLOWED_HOSTS = ['yourdomain.com']
SECURE_SSL_REDIRECT = True
SECURE_HSTS_SECONDS = 63072000
SECURE_HSTS_INCLUDE_SUBDOMAINS = True
SECURE_HSTS_PRELOAD = True
SESSION_COOKIE_SECURE = True
SESSION_COOKIE_HTTPONLY = True
SESSION_COOKIE_SAMESITE = 'Lax'
CSRF_COOKIE_SECURE = True
SECURE_REFERRER_POLICY = 'strict-origin-when-cross-origin'
X_FRAME_OPTIONS = 'DENY'
SECURE_CONTENT_TYPE_NOSNIFF = True

# Password hashing — use Argon2 (default in modern Django)
PASSWORD_HASHERS = [
    'django.contrib.auth.hashers.Argon2PasswordHasher',
    'django.contrib.auth.hashers.PBKDF2PasswordHasher',
]

# Authorization — object-level permissions
class OrderViewSet(viewsets.ModelViewSet):
    permission_classes = [IsAuthenticated, IsOwner]
    def get_queryset(self):
        return Order.objects.filter(user=self.request.user)  # ownership filter

# Mass assignment — Django doesn't have it the same way, but watch ModelForm fields:
class UserProfileForm(forms.ModelForm):
    class Meta:
        model = User
        fields = ['name', 'email']  # NOT __all__, NOT including is_staff/is_superuser

# Rate limiting — django-ratelimit
@ratelimit(key='ip', rate='5/m', method='POST')
def login_view(request): ...

# CSP — django-csp
CSP_DEFAULT_SRC = ("'self'",)
CSP_SCRIPT_SRC = ("'self'", "'nonce-{nonce}'", "'strict-dynamic'")
CSP_INCLUDE_NONCE_IN = ['script-src']
```

### Ruby on Rails

```ruby
# config/environments/production.rb
config.force_ssl = true
config.ssl_options = { hsts: { expires: 1.year, subdomains: true, preload: true } }

# Strong parameters (mass assignment defense)
def user_params
  params.require(:user).permit(:name, :email)  # NOT :role, :is_admin
end

# Authorization — Pundit
class OrderPolicy < ApplicationPolicy
  def show?
    record.user_id == user.id
  end
end
# In controller:
authorize @order, :show?

# Rack::Attack for rate limiting
Rack::Attack.throttle('login', limit: 5, period: 60) do |req|
  req.ip if req.path == '/login' && req.post?
end

# Session security — config/initializers/session_store.rb
Rails.application.config.session_store :cookie_store,
  key: '_app_session', httponly: true, secure: Rails.env.production?, same_site: :lax

# CSP — config/initializers/content_security_policy.rb
Rails.application.config.content_security_policy do |policy|
  policy.default_src :self
  policy.script_src :self, :strict_dynamic
  policy.object_src :none
end
Rails.application.config.content_security_policy_nonce_generator = ->(req) { SecureRandom.base64(16) }
```

### Spring Boot (Java)

```java
// SecurityConfig.java
@Configuration
@EnableWebSecurity
public class SecurityConfig {
    @Bean
    public SecurityFilterChain filterChain(HttpSecurity http) throws Exception {
        http
            .csrf(csrf -> csrf.csrfTokenRepository(CookieCsrfTokenRepository.withHttpOnlyFalse()))
            .headers(headers -> headers
                .httpStrictTransportSecurity(hsts -> hsts.maxAgeInSeconds(63072000).includeSubDomains(true).preload(true))
                .contentSecurityPolicy(csp -> csp.policyDirectives("default-src 'self'; object-src 'none';"))
                .frameOptions(frame -> frame.sameOrigin())
                .referrerPolicy(rp -> rp.policy(ReferrerPolicy.STRICT_ORIGIN_WHEN_CROSS_ORIGIN))
            )
            .authorizeHttpRequests(authz -> authz
                .requestMatchers("/admin/**").hasRole("ADMIN")
                .requestMatchers("/api/**").authenticated()
                .anyRequest().permitAll()
            )
            .sessionManagement(s -> s.sessionFixation(sf -> sf.newSession()))  // regenerate on login
            .build();
    }

    @Bean
    public PasswordEncoder passwordEncoder() {
        return new BCryptPasswordEncoder(12);
    }
}

// Method-level authorization
@PreAuthorize("#order.userId == authentication.principal.id")
public Order getOrder(Order order) { ... }
```

### Go (Gin / Echo)

```go
// Middleware for security headers
func SecurityHeaders() gin.HandlerFunc {
    return func(c *gin.Context) {
        c.Header("Strict-Transport-Security", "max-age=63072000; includeSubDomains; preload")
        c.Header("X-Content-Type-Options", "nosniff")
        c.Header("X-Frame-Options", "SAMEORIGIN")
        c.Header("Referrer-Policy", "strict-origin-when-cross-origin")
        c.Next()
    }
}

// Constant-time comparison (no timing attacks)
import "crypto/subtle"
if subtle.ConstantTimeCompare([]byte(provided), []byte(expected)) != 1 {
    c.AbortWithStatus(http.StatusUnauthorized)
}

// Password hashing — bcrypt
import "golang.org/x/crypto/bcrypt"
hash, _ := bcrypt.GenerateFromPassword([]byte(password), 12)

// SQL — always parameterized
db.QueryRow("SELECT id, email FROM users WHERE email = $1", email)
// NEVER fmt.Sprintf into the query

// Rate limiting — golang.org/x/time/rate or Redis-backed (for distributed)
```

### NestJS

```typescript
// main.ts
import helmet from 'helmet';
app.use(helmet({ /* same baseline as Express */ }));
app.useGlobalPipes(new ValidationPipe({ whitelist: true, forbidNonWhitelisted: true }));
// whitelist: strips properties not in the DTO — defends against mass assignment

// DTO with class-validator
class CreateUserDto {
  @IsEmail() email: string;
  @MinLength(8) password: string;
  @IsString() name: string;
  // No role, no isAdmin
}

// Guard for authorization
@Injectable()
export class OwnerGuard implements CanActivate {
  canActivate(ctx: ExecutionContext): boolean {
    const req = ctx.switchToHttp().getRequest();
    return req.params.userId === req.user.id;
  }
}

// Throttling
@Throttle({ default: { limit: 5, ttl: 60000 } })
@Post('login')
login() { ... }
```

### ASP.NET Core

```csharp
// Program.cs
app.UseHsts();
app.UseHttpsRedirection();
app.Use(async (ctx, next) => {
    ctx.Response.Headers.Append("X-Content-Type-Options", "nosniff");
    ctx.Response.Headers.Append("X-Frame-Options", "SAMEORIGIN");
    ctx.Response.Headers.Append("Referrer-Policy", "strict-origin-when-cross-origin");
    ctx.Response.Headers.Append("Content-Security-Policy",
        "default-src 'self'; object-src 'none'; frame-ancestors 'none';");
    await next();
});

// Anti-forgery (CSRF)
builder.Services.AddAntiforgery(o => o.HeaderName = "X-XSRF-TOKEN");

// Authorization policy (object-level)
[Authorize]
[HttpGet("orders/{id}")]
public async Task<IActionResult> GetOrder(int id) {
    var order = await _db.Orders.FindAsync(id);
    if (order == null) return NotFound();
    if (order.UserId != User.GetId()) return Forbid();
    return Ok(order);
}

// Identity password hasher uses PBKDF2 by default — switch to Argon2 via package or use ASP.NET Identity v8's improved hasher
```

---

## COMMON SECURITY TRAPS

**Trap 1 — "It's a private repo, so secrets are safe."**
Private repos get breached, leaked, accidentally made public, accessed by departing team members, and synced to backup services. Treat every secret in *any* repo as already public. Use environment variables and secret managers.

**Trap 2 — Trusting client-side validation.**
A React form's `required` attribute does not exist from the server's perspective. Every byte arriving at the server must be validated, sanitized, and authorized regardless of what the frontend enforces.

**Trap 3 — "Is logged in" instead of "is authorized."**
`auth()->check()` tells you a user is authenticated. It says nothing about whether they own the resource they're touching. Every fetch needs an ownership/permission check. This is the #1 OWASP category.

**Trap 4 — Webhook trust without signature verification.**
Anyone can POST JSON to your `/webhook/paystack`. Without HMAC verification, an attacker fabricates `payment.success` and you ship goods. Always verify before acting.

**Trap 5 — Client-controlled prices.**
The frontend sends `amount: 100`. That number means nothing. The server calculates from the database. Anything else is a financial vulnerability, not a UX preference.

**Trap 6 — "Deleting" a secret from Git.**
Removing a file or line in a new commit does not erase Git history. `git log -p` shows every change ever. Once a secret touches Git, **rotate it**. Period. Cleaning history is optional cosmetics — rotation is required.

**Trap 7 — Debug mode as a "temporary" setting.**
`APP_DEBUG=true` in production is one of the most common breach vectors. A single unhandled exception exposes DB credentials, file paths, and the entire `.env` to any visitor who triggers it.

**Trap 8 — `==` for security comparisons in PHP.**
PHP loose equality has decades of known type-juggling bugs. `"0" == false`, `"php" == 0`, `"1abc" == 1`. Use `===`, and `hash_equals()` for token comparison (constant-time, prevents timing attacks).

**Trap 9 — JWTs in localStorage.**
Any JS on the page — including injected third-party scripts and browser extensions — can read localStorage. One XSS = full auth theft. Use httpOnly cookies, which JS cannot access.

**Trap 10 — Mass assignment via `$request->all()`.**
A user can add any field to a POST. `User::create($request->all())` with `is_admin=1` makes them an admin. Use `$request->validated()` (FormRequest) or `$request->only([...])` with explicit allowlist.

**Trap 11 — Swallowed exceptions in auth flows.**
`try { $user = getUser(); } catch (Exception $e) { /* continue */ }` — exception caught, execution continues into privileged territory, application fails open. Exceptions in auth paths must result in hard denial.

**Trap 12 — CORS `*` with credentials.**
`Access-Control-Allow-Origin: *` + `Access-Control-Allow-Credentials: true` is an invalid combo, but some misconfigured servers reflect the request origin dynamically — allowing any site to make authenticated requests. Always exact-match against a fixed allowlist.

**Trap 13 — `NEXT_PUBLIC_*` for secrets.**
Anything prefixed `NEXT_PUBLIC_`, `VITE_`, or `REACT_APP_` is bundled into the client. Putting a Stripe secret key, DB credential, or JWT signing key behind these prefixes ships them to every user.

**Trap 14 — CSP with `'unsafe-inline'` and no nonce.**
`script-src 'self' 'unsafe-inline'` is a CSP that does nothing. Real CSP uses per-request nonces with `'strict-dynamic'`. `'unsafe-inline'` is an opt-out of XSS protection.

**Trap 15 — Rotating only the leaked key.**
If a CI/CD secret leaks, rotate **everything** that secret touched: webhook signing secrets, dependent API keys, and any data access tokens issued during the exposure window. Single-key rotation often misses pivot paths.

**Trap 16 — Server Actions without auth checks.**
`'use server'` functions in Next.js are effectively unauthenticated RPC endpoints from a security standpoint. The same authentication and authorization rules as API routes apply. The convenient call syntax does not imply convenient security.

---

## AFTER ALL CRITICAL FIXES ARE APPLIED

1. **Re-audit the affected paths** (or request user re-audit). Specifically: did the fix introduce a new issue? (Rate limit added → does it now allow account lockout DoS? CSP added → does it break legitimate scripts?)
2. **Verify in a staging environment** before production where possible.
3. **Confirm with provider dashboards** that rotated secrets show the rotation timestamp.
4. **Then proceed to HIGH severity items** or hand off to monitor mode.

---

*End of implement mode. To set up ongoing security posture, open `security-monitor.md`.*

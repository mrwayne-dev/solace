# HealthRunCare (HRC) — Developer Specification

> A technical specification of the HealthRunCare platform: what it is, how it is
> structured, how money flows through it, and how every subsystem works.
> Audience: developers picking up or extending this codebase.

---

## 1. Overview

**HealthRunCare** (brand: *HRC*; legacy framework references call it *Lymora*) is a
**healthcare‑themed investment, savings, and charity platform**. End users deposit money
into a wallet, then put that money to work through several "fund" products that pay
ROI over time, donate to healthcare charities, and withdraw funds back out. An admin
panel manages users, money movement, plans, and content.

Despite the healthcare branding on the public marketing site, the actual software is a
**fintech / yield platform**: it is a wallet + a set of fixed‑term investment products
+ a donation module, wrapped in a healthcare narrative. There is no real medical/clinical
functionality — "patients, doctors, pharmacies" exist only as marketing copy.

### The three faces of the app

| Surface | Audience | Entry route | Backed by |
|--------|----------|-------------|-----------|
| **Public site** | Anonymous visitors | `/`, `/about`, `/platform`, `/investment`, … | `pages/public/*` (static-ish marketing) |
| **User dashboard** | Logged‑in investors | `/dashboard`, `/dashboard.wallet`, … | `pages/user/*` + `api/backend/*` |
| **Admin panel** | Operators/admins | `/admin`, `/admin.users`, … | `pages/admin/*` + `api/admin/*` |

---

## 2. Technology Stack

| Layer | Technology |
|-------|-----------|
| Language | **PHP 8.3** (procedural, no framework) |
| Database | **MySQL 8.4**, InnoDB, `utf8mb4_unicode_ci`, accessed via **PDO** (prepared statements) |
| Web server | **Apache** with `mod_rewrite` (routing lives entirely in [.htaccess](.htaccess)) |
| Mail | **PHPMailer 6.11** (only Composer dependency) over SMTP |
| Payments | **NOWPayments** (crypto invoice + IPN webhook) |
| Front end | Server‑rendered PHP + **jQuery**, **Bootstrap**, `bootstrap-select`, Iconify/Unicons, `countTo` |
| Live chat | **Smartsupp** widget (embedded on public pages) |
| Local dev | **Laragon** (Windows) — see [run_cron.bat](run_cron.bat) paths |

There is **no build step, no bundler, no package.json**. JS/CSS are committed as
plain/minified files under `assets/`. PHP autoloading is Composer's (PHPMailer only).

---

## 3. Repository Layout

```
healthruncare/
├── index.php                 # Root entry → redirects to pages/public/index.php
├── .htaccess                 # All URL routing + security headers + caching
├── composer.json/.lock       # PHPMailer dependency
├── run_cron.bat              # Windows/Laragon cron launcher
│
├── config/
│   ├── env.php               # Environment + ALL secrets (DB, SMTP, NOWPayments)  ⚠
│   ├── constants.php         # App constants (APP_NAME, CURRENCY, SIMULATION_MODE…)
│   ├── database.php          # getPDO() connection factory
│   ├── roles.php             # Role constants + hasPermission() (largely unused/legacy)
│   └── certs/cacert.pem      # CA bundle for NOWPayments SSL
│
├── dbschema/
│   └── healthruncare_db.sql  # Full schema + seed data (source of truth for the DB)
│
├── api/                      # JSON endpoints (the backend)
│   ├── auth/                 # login/register/logout/forgot/reset  (user + admin_ variants)
│   ├── backend/              # User-facing controllers (wallet, dashboard, the 6 fund modules)
│   ├── admin/                # Admin controllers (users, funds, donations, process_*, settings)
│   ├── cron/                 # Scheduled ROI/maturity jobs (one per fund family)
│   ├── payments/             # NOWPayments: create_crypto_payment.php, now_webhook.php
│   └── utilities/            # helpers.php, email_temps.php (template library)
│
├── pages/                    # Server-rendered HTML views (.php)
│   ├── public/               # Marketing site
│   ├── user/                 # Logged-in dashboard views
│   └── admin/                # Admin views (+ admin/funds/ sub-views)
│
├── assets/                   # css/, js/, js/admin/, images/, fonts/, icon/, favicon/
├── uploads/                  # User-uploaded files (profiles/, contacts/)
├── logs/                     # email.log, wallet_debug.log
├── vendor/                   # Composer (PHPMailer)
└── txt/                      # Author's working notes (NOT runtime) — design scratchpad
```

> **Note on `txt/`**: these files (`details.txt`, `structure.txt`, etc.) are the original
> author's planning notes and pasted code snippets. They are *not* part of the running app
> but are useful as design intent (especially the wallet deposit/withdraw flow narrative in
> [txt/details.txt](txt/details.txt)).

---

## 4. Routing & Request Lifecycle

All routing is in [.htaccess](.htaccess) via `mod_rewrite`. There is **no front controller**;
each clean URL rewrites directly to a `.php` file.

### Environment detection
`.htaccess` sets `HRC_ENV` based on host: `localhost` → `dev`, anything else → `prod`.
In `prod` it forces HTTPS + non‑www (301). [config/env.php](config/env.php) reads
`$_SERVER['HRC_ENV']` to toggle error display.

### Route map (from `.htaccess`)

| URL pattern | Target | Notes |
|-------------|--------|-------|
| `/` | `pages/public/index.php` | Landing page |
| `/{about\|contact\|login\|register\|forgotpassword\|platform\|solutions\|whyhrc\|charity\|investment\|minfrastructure\|mdev\|trustfund\|holdlock}` | `pages/public/$1.php` | Public pages |
| `/api/...` | `api/...` | Pass‑through to JSON endpoints |
| `/dashboard` | `pages/user/dashboard.php` | User home |
| `/dashboard.{charity\|investment\|wallet\|trustfund\|infrastructure\|development\|holdlock\|transactions\|logout}` | `pages/user/$1.php` | User sub‑pages |
| `/admin` | `pages/admin/dashboard.php` | Admin home |
| `/admin.{dashboard\|users\|wallets\|donations\|funds\|transactions\|announcements\|login\|register\|forgotpassword\|settings}` | `pages/admin/$1.php` | Admin sub‑pages |
| `/admin.funds/{holdlock\|trustfund\|infrastructure\|maintenance}` | `pages/admin/funds/$1.php` | Per‑fund admin views |
| anything else | `pages/public/error.php` | Catch‑all 404 |

### Typical request lifecycle (logged‑in user action)
1. Browser loads a `pages/user/*.php` view (server‑rendered HTML; guards the session).
2. Page‑specific JS module (e.g. [assets/js/investment.js](assets/js/investment.js)) calls
   `fetchApi('/api/backend/<module>.php', { action: '...', ...payload })`.
3. `fetchApi()` (defined in [assets/js/api.js](assets/js/api.js)) POSTs JSON with
   `credentials: 'include'`, shows a global loader, parses JSON, returns `{status, message, data}`.
4. The PHP controller `session_start()`s, checks `$_SESSION['user_id']`, branches on `action`,
   runs PDO queries (often inside a transaction), sends emails, and returns a JSON envelope.
5. JS updates the DOM and shows a toast (`showToast()`).

**Security headers** (set in `.htaccess` for all responses): `X-Frame-Options: SAMEORIGIN`,
`X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`, HSTS, and a minimal
CSP (`upgrade-insecure-requests`). Static assets get long cache lifetimes; directory indexing
is disabled (`Options -Indexes`).

---

## 5. Configuration & Environment

| File | Responsibility | Key contents |
|------|----------------|--------------|
| [config/env.php](config/env.php) | Secrets + env flag | `ENV`/`APP_ENV`, `DB_*`, `SMTP_*`, `NOWPAY_*`, `ADMIN_CONTACT_EMAIL`, error reporting toggle |
| [config/constants.php](config/constants.php) | App constants | `APP_NAME`, `APP_URL`, `CURRENCY=USD`, `TIMEZONE=Europe/London`, `OTP_EXPIRY_MINUTES=10`, `SIMULATION_MODE=true`, `MAX_WITHDRAWAL_ATTEMPTS=3`, role constants, upload paths |
| [config/database.php](config/database.php) | `getPDO()` | Returns a configured PDO (exceptions on error, assoc fetch). Fails closed: prints error in dev, logs in prod, `exit()` |
| [config/roles.php](config/roles.php) | RBAC scaffold | `ROLE_USER / ROLE_SUPPORT_ADMIN / ROLE_SUPER_ADMIN` + `hasPermission()`. **Mostly legacy / not wired into the actual admin checks** (see §16) |

> ⚠ **Secrets are hard‑coded and committed** in `config/env.php` (DB password, SMTP password,
> NOWPayments API key + IPN secret). See §16 — this should be moved to real environment
> variables / a non‑committed file before any production use, and the exposed keys rotated.

`SIMULATION_MODE = true` is defined but acts mainly as a flag — most flows run "for real"
against the DB regardless; treat the platform's ROI/maturity logic as fully active.

---

## 6. Data Model

Database: `healthruncare_db` (the live deploy uses a cPanel‑prefixed name —
see `DB_NAME` in env). Engine InnoDB, charset `utf8mb4`. Source of truth:
[dbschema/healthruncare_db.sql](dbschema/healthruncare_db.sql).

### 6.1 Identity & access

| Table | Purpose | Notable columns |
|-------|---------|-----------------|
| `users` | Investor accounts | `name`, `full_name`, `email` (unique), `password` (bcrypt), `role` enum(`user`,`admin`), `status` enum(`active`,`disabled`), `profile_picture` |
| `admins` | Operator accounts (separate table) | `email` (unique), `password`, `role` enum(`super_admin`,`manager`,`support`), `status`, `last_login` |
| `password_resets` | OTP password reset | `user_id` FK, `otp`, `expires_at` (10‑min window) |
| `login_logs` | Audit of logins | `user_type` enum(`user`,`admin`), `ip`, `browser`, `location` (GeoIP) |

> **Users and admins are entirely separate tables and session namespaces.** A `users.role`
> column exists but admin access is governed by the `admins` table + `$_SESSION['admin_id']`.

### 6.2 Wallet & money movement

| Table | Purpose |
|-------|---------|
| `wallets` | One row per user. The financial ledger summary: `balance`, `total_deposited`, `total_withdrawn`, `total_donations`, `total_investments`, `holdlock_savings`, `total_earnings`, `pending_withdrawals`. Also caches `cash_mailing_address` / `wallet_deposit_address`. |
| `transactions` | Every money event. `type` (deposit/withdraw/investment/…), `method` enum(`secure_exchange`,`cash_mailing`,`wire_transfer`,`local_bank`,`wallet_address`,`wallet`,`system`), `details` JSON, `amount`, `reference` (unique), `status` enum(`pending`,`completed`,`failed`) |
| `bank_details` | Saved payout methods per user. `method` enum(`local_bank`,`wallet_address`), `details` JSON |
| `settings` | **Single‑row** global config (id=1): `cash_mailing_address`, `wallet_deposit_address` — the admin‑provided deposit destinations |

### 6.3 Financial products (each is a *plan catalog* + a *user holdings* table)

| Product | Plan catalog (admin‑managed) | User holdings | Key holdings columns |
|---------|------------------------------|---------------|----------------------|
| **Investment** | `investment_plans` | `investments` | `plan_name`, `amount`, `roi_percent`, `duration_days`, `maturity_date`, `roi_earned`, `status`(active/completed) |
| **HoldLock** (locked savings) | `holdlock_plans` | `holdlock` | `roi_percent`, `duration_days`, `penalty_percent`, `maturity_date`, `payout_option`(maturity/early), `status`(locked/unlock_pending/matured/unlocked_early/completed) |
| **TrustFund** | `trustfund_plans` | `trustfund` | `roi_percent`, `duration_days`, `penalty_percent`, `purpose`, `payout_option`(annual/maturity), `status`(active/matured/unlock_pending/unlocked_early/completed) |
| **Infrastructure** | `infrastructure_plans` + `infrastructure` (fundable projects) | `infrastructure_contributions` | `plan_id`, `project_id`, `amount`, `roi_earned`, `status`(active/matured/unlocked) |
| **Maintenance** | `maintenance_plans` | `maintenance` | `plan_id`, `frequency`(monthly/once), `next_payment_date`, `maturity_date`, `status`(active/matured/unlocked/expired) |
| **Charity** | `charities` (campaigns) | `charity_donations` | `charity_id`, `amount`, `reference` (no ROI — pure donation) |

### 6.4 Engagement

| Table | Purpose |
|-------|---------|
| `user_impacts` | Gamified impact metrics per user: `total_contributions`, `people_helped`, `impact_score`, `communities_helped`, `packages_funded` |

### 6.5 Relationships & integrity
- All user‑owned tables FK to `users(id)` **`ON DELETE CASCADE`** (deleting a user wipes
  their wallet, transactions, holdings, donations, resets).
- `charity_donations` FK both `users` and `charities`; `infrastructure_contributions` FK
  `users` and `infrastructure(project)`.
- Indexed on `user_id`, `maturity_date`, `status`, and `transactions.reference` (unique).
- The plan **catalogs are denormalized into the holdings rows** at purchase time (e.g.
  `investments.plan_name`, `roi_percent`, `duration_days` are copied), so editing a plan
  later does not retroactively change existing holdings.

---

## 7. Authentication & Sessions

Two parallel auth systems, both cookie sessions hardened with
`httponly`, `secure` (when HTTPS), `samesite=Strict`, 24h lifetime.

### User auth — `api/auth/`
| Endpoint | Method | Behaviour |
|----------|--------|-----------|
| `login.php` | POST JSON `{email,password}` | `password_verify` against `users`; blocks `disabled`; sets `$_SESSION['user_id'/'email'/'full_name']`; updates `last_login`; emails a **login alert** to user + **login notification** to admin; logs to `login_logs` with GeoIP + parsed browser. Returns `{data:{redirect:'/dashboard'}}` |
| `register.php` | POST JSON `{first_name,last_name,email,password}` | Validates, checks unique email, `password_hash`, inserts user, **creates a `wallets` row**, sets session, sends `welcome_user` email |
| `logout.php` | — | Destroys session |
| `forgotpassword.php` | POST `{email}` | Generates OTP, stores in `password_resets` (10‑min expiry), emails it |
| `resetpassword.php` | POST `{user_id,otp,new_password}` or `{...,verify_only:true}` | 3‑step flow: send → verify OTP → set new password |

### Admin auth — `api/auth/admin_*.php`
Mirror of the above against the `admins` table. Sets a **separate session namespace**:
`$_SESSION['admin_id'/'admin_email'/'admin_name'/'admin_logged_in']`. On login it emails an
`admin_login_alert` and logs the event.

The frontend [api.js](assets/js/api.js) auto‑selects the user vs admin endpoint via
`getAuthEndpoint()` (checks if the path contains `/admin`).

### Authorization guards
- **Pages** guard at the top: `pages/user/*` redirect if `!isset($_SESSION['user_id'])`;
  `pages/admin/*` redirect if `!isset($_SESSION['admin_id'])`.
- **APIs** return `401 JSON` if the relevant session var is missing.
- There is **no granular role enforcement** in practice — any authenticated admin can call
  any admin endpoint. The `admins.role` enum and `config/roles.php` are not enforced (§16).

---

## 8. The Wallet System — the heart of the app

The wallet is the single source of money. Everything (investing, donating, withdrawing) debits
or credits `wallets.balance`, and **every event is written to `transactions`**. Central
controller: [api/backend/wallet.php](api/backend/wallet.php). UI logic: the wallet/dashboard JS
in [assets/js/dashboard.js](assets/js/dashboard.js) and [pages/user/wallet.php](pages/user/wallet.php).

`wallet.php` accepts the `action` from form‑POST, GET, **or JSON body** (robust detection),
and dispatches via `switch`.

### 8.1 Wallet actions (`/api/backend/wallet.php`)

| `action` | Effect |
|----------|--------|
| `initiate_deposit` | Creates a `pending` deposit transaction. Branches by method (below). |
| `confirm_deposit_payment` | User clicks "I have paid" → marks `details.user_marked_paid` and emails admin. (Does **not** credit yet — admin must confirm for manual methods.) |
| `withdraw_request` | Validates balance, **debits `balance` + increments `pending_withdrawals`** atomically, creates a `pending` withdraw txn, emails user + admin (admin email includes formatted payout details). |
| `get_pending_deposits` | Lists the user's pending deposit txns (for the "pending actions" modal). |
| `get_wallet_summary` | **Recomputes `total_earnings`** by summing `roi_earned` across `investments`, `holdlock`, `trustfund`, `maintenance`, `infrastructure_contributions`, persists it, returns the full balance breakdown. |
| `get_deposit_details` | Returns the admin‑configured destination (`cash_mailing_address` / `wallet_deposit_address`) from `settings`. |

### 8.2 Deposit flows

**(a) Secure Exchange (crypto, automated):**
```
User enters amount → initiate_deposit(method=secure_exchange)
  → create pending txn
  → createCryptoPayment() → NOWPayments /v1/invoice  (api/payments/create_crypto_payment.php)
  → returns invoice_url → frontend redirects user to NOWPayments
User pays → NOWPayments calls now_webhook.php (IPN)
  → verify HMAC-SHA512 signature (canonical sorted-key JSON, NOWPAY_IPN_SECRET)
  → idempotency check (skip if already completed)
  → on success status: mark txn completed, credit balance + total_deposited (atomic)
  → email deposit_confirmed (user) + admin_payment_confirmed (admin)
```
The webhook treats `finished/confirmed/successful/paid/payment_received` as success states.

**(b) Manual (wire transfer / cash mailing):**
```
User enters amount → initiate_deposit(method=wire_transfer|cash_mailing)
  → create pending txn
  → email deposit_initiated (user) + admin_deposit_notification (admin)
Admin provides payment destination via settings (save_deposit_address.php)
  → user emailed deposit_details_provided
User opens "pending deposits" modal, sees address, pays externally, clicks "I have paid"
  → confirm_deposit_payment → emails admin (admin_payment_confirmed)
Admin verifies → process_deposit.php(action=complete)
  → mark completed, credit wallet, email deposit_confirmed
(or action=cancel → mark failed, email deposit_cancelled)
```

### 8.3 Withdrawal flow
Three methods: **`local_bank`** (country‑aware bank picker with IBAN/BIC/UK sort code),
**`wallet_address`** (coin + address), **`cash_mailing`** (free‑text mailing details).
```
withdraw_request → balance debited immediately, pending_withdrawals += amount, pending txn created
  → email withdrawal_initiated (user) + admin_withdrawal_notification (admin, with details)
Admin → process_withdrawal.php
  action=complete → mark txn completed, email withdrawal_approved  (money already deducted)
  action=cancel   → REFUND: balance += amount, pending_withdrawals -= amount,
                    mark txn failed, email withdrawal_declined (with reason)
```

The country→bank suggestion map (20 countries, ~15 banks each) is hard‑coded in
[assets/js/dashboard.js](assets/js/dashboard.js) (`bankSuggestions`).

---

## 9. Financial Products & Plans

There are **six product families**. Five pay ROI (Investment, HoldLock, TrustFund,
Infrastructure, Maintenance); one is donation‑only (Charity). Each ROI product follows the
same lifecycle:

```
Plan catalog (admin) → user "starts" a holding (wallet debited, holding row created 'active'/'locked')
   → cron accrues ROI periodically and credits wallet
   → at maturity_date: cron credits principal + ROI, marks completed
   → user may "unlock" early (penalty applied where configured)
```

### 9.1 Investment plans (seeded in `investment_plans`)
| Plan | ROI | Duration | Min–Max | Risk |
|------|-----|----------|---------|------|
| Healthy Future Bond Plan | 11% | 540d | $500–100k | Low |
| Wellness Growth Real Estate Plan | 16.5% | 730d | $5k–250k | Moderate |
| Health Innovation Venture Fund | 30% | 1095d | $10k–500k | High |
| Community Health Microfinance Plan | 9% | 360d | $300–20k | Low |
| Green Hospital Infrastructure Plan | 15% | 730d | $2k–200k | Moderate |
| Healthy Food Systems Plan | 13.5% | 540d | $1k–50k | Moderate |
| Digital Health Access Plan | 20% | 730d | $2k–100k | Mod–High |
| *testing plan* | 25.5% | 365d | $1–~10M | low (test row) |

### 9.2 HoldLock plans (locked savings, early‑unlock penalty)
| Plan | ROI | Lock | Min | Penalty |
|------|-----|------|-----|---------|
| Flexi Health Lock | 2% | 180d | $10k | 1% |
| Standard Lock & Grow | 7–9% | 365d | $20k | 1.5% |
| Executive LockPlus | 14–18% | 730d | $50k | 2% |
| Prestige Capital Hold | 25–30% | 1095d | $250k | 2.5% |
| Lifetime Reserve Lock | 6–8% | "perpetual" (36500d) | $1M | 1% |

### 9.3 TrustFund plans (long‑horizon trusts)
7 plans (e.g. *Child Education Growth* 25%/1095d, *Legacy Wealth Trust* 55%/1825d,
*Perpetual Legacy Trust* 11%/9999d). Payout `annual` or `maturity`.

### 9.4 Infrastructure (project‑backed)
6 plans (`infrastructure_plans`) financing fundable hospital projects (`infrastructure`):
ROI 9–29%, durations 365–1095d. Contributions tracked in `infrastructure_contributions`,
each tied to a `project_id`; projects have `goal_amount`/`raised_amount`/`status`(open/funded/complete).

### 9.5 Maintenance (servicing contracts)
5 plans (`maintenance_plans`), ROI 5.5–22%, with `monthly` vs `once` frequency and
`next_payment_date` tracking.

### 9.6 Charity (donations)
6 seeded campaigns (`charities`) with `goal_amount`/`raised_amount`. Users donate via
`api/backend/charity.php → make_donation` which debits the wallet, writes a
`charity_donations` row + transaction, and bumps `raised_amount` + `wallets.total_donations`.

### 9.7 Common backend controllers (`api/backend/<module>.php`)
Each fund module (`investment`, `holdlock`, `trustfund`, `infrastructure`, `maintenance`,
`charity`) exposes a consistent action set:

| Action | Purpose |
|--------|---------|
| `get_plans` | List catalog plans for the UI |
| `get_summary` | User's totals for this product |
| `get_active` | Active holdings |
| `get_matured` | Completed/matured holdings |
| `start_<product>` | Buy in: validate amount vs plan min/max, debit wallet (`FOR UPDATE` lock + transaction), create holding, write txn, email user + admin |
| `unlock` / `unlock_investment` | Early exit / claim; applies penalty where configured |

(Charity uses `get_campaigns` / `get_single_campaign` / `make_donation` instead.)

---

## 10. API Reference (by area)

All endpoints return a JSON envelope: `{ "status": "success"|"error", "message": "...", "data": {...} }`.
All authenticated endpoints check the session and return `401` if absent.

### 10.1 `api/auth/`
`login.php`, `register.php`, `logout.php`, `forgotpassword.php`, `resetpassword.php` and the
`admin_*` equivalents (see §7).

### 10.2 `api/backend/` (user, session = `user_id`)
| File | Responsibility |
|------|----------------|
| `dashboard.php` | Aggregated dashboard data (wallet snapshot, recent activity, pending count) |
| `wallet.php` | Deposits/withdrawals/summary (§8) |
| `investment.php`, `holdlock.php`, `trustfund.php`, `infrastructure.php`, `maintenance.php` | Fund modules (§9.7) |
| `charity.php` | Campaign listing + donations |
| `transactions.php` | User's transaction history |
| `card_usage.php` | Virtual "card" usage data for the dashboard card widget |
| `email.php` | `sendEmail()` wrapper + bootstraps the template library |

### 10.3 `api/admin/` (admin, session = `admin_id`)
| File | Responsibility |
|------|----------------|
| `dashboard.php` | Global metrics (revenue, donations, active investments, users), pending alerts, recent txns, chart percentages |
| `users.php` | `edit_user`, `delete_user`, `send_email` |
| `wallets.php` | `update_balance` — **manual balance adjustment** |
| `transactions.php` | Transaction management/listing |
| `donations.php` | Charity campaign CRUD (`add_campaign`, `edit_campaign`) |
| `funds.php`, `funds_holdlock.php`, `funds_trustfund.php`, `funds_infrastructure.php`, `funds_maintenance.php` | Plan/project CRUD per family (`add_plan`/`edit_plan`, `add_project`/`edit_project`/`toggle_status`, `update_task_status`) |
| `process_deposit.php` | `complete` / `cancel` a pending manual deposit (credits wallet on complete) |
| `process_withdrawal.php` | `complete` / `cancel` a withdrawal (refunds wallet on cancel) |
| `get_pending_deposits.php`, `get_pending_withdrawals.php` | Pending queues |
| `get_deposit_address.php`, `save_deposit_address.php` | Read/UPSERT the single‑row `settings` deposit destinations |
| `email.php` | Bulk/segmented email (`all`, `active`, `specific`, `investors`, `donors`) |

### 10.4 `api/payments/`
| File | Responsibility |
|------|----------------|
| `create_crypto_payment.php` | `createCryptoPayment()` — builds a NOWPayments `/v1/invoice` (price, callbacks to `now_webhook.php`, success/cancel URLs to `wallet.php`), attaches provider data to the txn, returns the invoice URL. Has a local‑dev SSL‑off retry. |
| `now_webhook.php` | IPN receiver: HMAC‑SHA512 signature verification, idempotent, credits wallet + emails on success |

### 10.5 `api/utilities/`
| File | Responsibility |
|------|----------------|
| `helpers.php` | `cleanInput()` (htmlspecialchars), `getLocationFromIP()` (ipapi.co, 2s timeout), `logLoginEvent()`, `getUserBrowser()` UA parser, `logAdminAction()` (writes to an `admin_logs` table — **note: that table is not in the committed schema**) |
| `email_temps.php` | `getEmailTemplates()` — the full HTML template library (~30 templates, themed) |

---

## 11. Cron / Automation

Located in `api/cron/`. Each fund family has its own job; all follow the same pattern:
connect, fetch active holdings joined to users, accrue periodic ROI, handle maturities
(credit principal + ROI to wallet, mark completed, write a txn, email the user), and append
to a per‑job `.log` file.

| Job | What it processes | Cadence (intended) |
|-----|-------------------|--------------------|
| `investment_cron.php` | `investments` — weekly ROI increment = `amount*roi%/(duration_days/7)`; matures on/after `maturity_date` | Weekly (or daily) |
| `holdlock_cron.php` | `holdlock` — matures locked funds, applies early‑unlock penalties, credits wallet | Every 12–24h |
| `trustfund_cron.php` | `trustfund` | Periodic |
| `infrastructure_cron.php` | `infrastructure_contributions` | Periodic |
| `maintenance_cron.php` | `maintenance` (monthly payouts) | Periodic |
| `update_charities.php` | Refreshes/normalizes charity campaign figures | Periodic |

**Maturity math example (investment):** final ROI = `amount * roi_percent/100`;
payout = principal + final ROI, credited to `balance` and `total_earnings`; a
`HRC-MATURE-*` transaction is written.

**Scheduling:** On Windows/Laragon use [run_cron.bat](run_cron.bat) (it currently runs only
`maintenance_cron.php` — adjust `SCRIPT_PATH` per job, or add Task Scheduler entries). On a
Linux host, register each via `crontab` (the header comments suggest the schedules).

> The cron jobs reference plan ROI/penalty values **both** from the DB holding rows and from
> hard‑coded fallback arrays inside the job (e.g. `holdlock_cron.php` has a `$plans` map).
> Keep these in sync with `*_plans` tables if you change plan economics.

---

## 12. Email Subsystem

Single sender: `sendEmail($params)` in [api/backend/email.php](api/backend/email.php), using
PHPMailer over SMTP (`SMTP_HOST=mail.spacemail.com`, port 465, SSL). Credentials in `env.php`.

**How it works:**
- `$params = ['to', 'template', 'variables', 'subject?', 'body?', 'debug?', 'cc_admin?', 'admin_template?']`.
- Looks up the template by key in `getEmailTemplates()` (falls back to `generic`).
- Replaces `{{placeholder}}` tokens from `variables`, **HTML‑escaping all values except**
  `details_html` and `message_body` (which are intentionally pre‑formatted HTML).
- Replaces global tokens: `{{year}}`, `{{app_name}}`, `{{support_email}}`, `{{website_url}}`.
- `debug:true` renders the email to the browser instead of sending.
- Logs every send/failure to [logs/email.log](logs/email.log).

**Template library** ([api/utilities/email_temps.php](api/utilities/email_temps.php)) — all wrapped
in a shared themed HTML shell (`$wrap()`), HRC green/cream palette. ~30 templates including:

`login_alert`, `admin_login_alert`, `admin_user_login_notification`, `welcome_user`,
`welcome_admin`, `deposit_initiated`, `admin_deposit_notification`, `deposit_details_provided`,
`deposit_confirmed`, `deposit_cancelled`, `admin_payment_confirmed`, `withdrawal_initiated`,
`admin_withdrawal_notification`, `withdrawal_approved`, `withdrawal_declined`,
`investment_confirmed`, `admin_investment_notification`, `weekly_investment_update`,
`investment_matured`, `holdlock_started`, `admin_holdlock_notification`, `holdlock_unlocked_early`,
… plus the analogous trustfund/infrastructure/maintenance notifications.

---

## 13. Front-End Architecture

Server‑rendered PHP views + per‑feature jQuery modules. **No SPA, no bundler.** Shared
behaviour (loader, toasts, modals, the API wrapper) lives in [assets/js/api.js](assets/js/api.js)
and [assets/js/dashboard.js](assets/js/dashboard.js).

### Shared front-end utilities (api.js)
- `fetchApi(endpoint, payload)` / `fetchApiGet(endpoint)` — JSON POST/GET wrappers with a global
  loader overlay and uniform error handling.
- `showToast(message, type, duration)` — toast notifications (success/error/info/warning).
- `displayMessage()` — inline form messages.
- Auth form handlers (login/register/forgot‑password 3‑step/logout) auto‑wired by element IDs.

### JS module map
| File | Drives |
|------|--------|
| `assets/js/dashboard.js` | Wallet, deposit/withdraw modals, pending deposits, bank picker, dashboard data load |
| `assets/js/investment.js`, `holdlock.js`, `trustfund.js`, `infrastructure.js`, `maintenance.js` | Each fund page (plan cards, start/unlock modals, active/matured tables) |
| `assets/js/charity.js` | Campaign listing + donation flow |
| `assets/js/transaction.js` | Transaction history table |
| `assets/js/main.js` | Public site interactions |
| `assets/js/admin/*.js` | One module per admin page (`admin.js`, `users.js`, `wallet.js`, `donations.js`, `funds*.js`, `transactions.js`, `utilities.js`) |
| `jquery.min.js`, `bootstrap*.js`, `countto.js` | Vendor libs |

### CSS
`assets/css/main.css` + `responsive.css` (public), `dashboard.css` (app), plus Bootstrap,
`bootstrap-select`, `animation.css`, icon/font CSS. A `min.css`/`min.js` folder holds minified
copies.

---

## 14. Public Marketing Site (`pages/public/`)

Static‑content PHP pages (no DB), SEO‑tagged, with the Smartsupp live‑chat widget embedded.
Pages: `index` (landing), `about`, `platform`, `solutions`, `whyhrc`, `charity`, `investment`,
`trustfund`, `holdlock`, `minfrastructure`, `mdev` (likely "maintenance/development"),
`contact`, plus `login`/`register`/`forgotpassword` entry forms (which post to `api/auth/`).
These market the product families described in §9 and funnel users into registration.

---

## 15. User Dashboard (`pages/user/`)

Guarded by `$_SESSION['user_id']`. Each view renders the shell + a JS module that hydrates it
from `api/backend/*`.

| View | Purpose |
|------|---------|
| `dashboard.php` | Home: wallet balance breakdown, recent wallet activity, a "virtual card" widget, impact stats. Renders display IDs like `HRC-INV-0001` from the user id. |
| `wallet.php` | Deposit / withdraw / pending deposits (the §8 flows) |
| `investment.php`, `holdlock.php`, `trustfund.php`, `infrastructure.php`, `development.php` | Browse plans, start holdings, view active/matured, unlock |
| `charity.php` | Browse campaigns, donate |
| `transactions.php` | Full transaction history |
| `logout.php` | Ends session |

---

## 16. Admin Panel (`pages/admin/`)

Guarded by `$_SESSION['admin_id']`. Backed by `api/admin/*`.

| View | Capability |
|------|-----------|
| `dashboard.php` | KPIs (revenue/donations/active investments/users), pending deposit & withdrawal alerts, recent transactions, distribution chart |
| `users.php` | View/edit/disable/delete users; email a user |
| `wallets.php` | **Manually adjust a user's wallet balance** |
| `transactions.php` | Browse/manage all transactions |
| `donations.php` | Create/edit charity campaigns |
| `funds.php` + `funds/{holdlock,trustfund,infrastructure,maintenance}.php` | Create/edit plans & infrastructure projects per family |
| `settings` | Configure the global deposit destinations (`settings` table) |
| `login.php`, `register.php`, `forgotpassword.php` | Admin auth |
| (`process_deposit`/`process_withdrawal` endpoints) | Approve/decline pending money movement |

Admins approve manual deposits, approve/decline withdrawals (with refund on decline), provide
deposit addresses, manage the plan catalogs, and run user/content administration. ROI accrual
and maturities are automated by cron, not the admin.

---

## 17. Security Considerations & Known Issues

This is a working/educational build. Before any production/real‑money use, address:

1. **Committed secrets** — `config/env.php` contains live DB, SMTP, and NOWPayments
   credentials in the repo. Move to real env vars / an untracked file and **rotate every
   exposed key** (DB password, SMTP password, NOWPayments API key + IPN secret). Likewise,
   `healthruncare.test*.pem` and `dbschema/*.sql` (with seeded password hashes) are committed.
2. **No role enforcement** — any authenticated admin can call any `api/admin/*` endpoint;
   `admins.role` and `config/roles.php` (`hasPermission`) are effectively unused. The role
   constant names also mismatch (`support_admin`/`super_admin` in code vs
   `super_admin`/`manager`/`support` in the `admins` enum).
3. **CSRF** — state‑changing endpoints rely only on the session cookie; there are no CSRF
   tokens. (`samesite=Strict` mitigates but is not a full defence.)
4. **CORS** — `api/admin/dashboard.php` sends `Access-Control-Allow-Origin: *`. Remove for an
   admin/credentialed endpoint.
5. **SQL** — queries use PDO prepared statements (good). A few places interpolate identifiers
   (`get_wallet_summary` loops a fixed table list; `save_deposit_address`/`get_deposit_details`
   pick from a whitelist) — keep those lists hard‑coded/whitelisted; never feed user input into
   a column/table name.
6. **`logAdminAction()` targets an `admin_logs` table that is not in the committed schema** —
   either create it or the helper silently logs an error.
7. **Money integrity** — wallet mutations correctly use transactions + `FOR UPDATE` in the
   fund "start" flows and the webhook; ensure all new money paths do the same. `get_wallet_summary`
   *overwrites* `total_earnings` from a recompute — be careful that ROI isn't double‑counted
   between cron credits to `balance`/`total_earnings` and this recompute.
8. **Cron auth** — cron scripts are plain PHP under the web root (`api/cron/*.php`); they have
   no auth guard, so they are reachable over HTTP. Restrict them (deny via `.htaccess`, move
   outside webroot, or require a CLI/secret token).
9. **`SIMULATION_MODE`** is defined but not consistently honoured — don't assume it makes the
   platform "safe" / non‑transacting.

---

## 18. Local Development Setup

1. **Server:** PHP 8.3 + MySQL 8.x + Apache with `mod_rewrite`, `mod_headers`, `mod_expires`
   (Laragon on Windows matches the author's setup; XAMPP works too). Serve the project root as
   the docroot so `.htaccess` routing resolves (`http://localhost/`).
2. **Database:** create the schema and import [dbschema/healthruncare_db.sql](dbschema/healthruncare_db.sql)
   (includes seed plans, charities, and a demo admin/user). Then set local `DB_*` in
   [config/env.php](config/env.php).
3. **Dependencies:** `composer install` (pulls PHPMailer into `vendor/`).
4. **Mail:** set `SMTP_*` to a real mailbox, or use `sendEmail([... 'debug'=>true])` to preview
   templates in‑browser without sending.
5. **Payments:** set `NOWPAY_*`; for the IPN webhook to reach localhost you need a tunnel
   (e.g. ngrok) and the public URL configured as the callback. The crypto path has a local
   SSL‑verify‑off fallback for dev.
6. **Cron:** run jobs manually (`php api/cron/investment_cron.php`) or wire Task Scheduler /
   crontab. Logs land next to each job and in `logs/`.
7. **Env flag:** `.htaccess` sets `HRC_ENV=dev` on `localhost`, enabling error display.

### Seeded demo accounts (from the SQL dump — change these)
- **User:** `aleruchi0987@gmail.com` (id 1)
- **Admin:** `aleruchi0987@gmail.com` (id 1, role `manager`)
- Passwords are bcrypt hashes in the dump; reset them locally.

---

## 19. Glossary

| Term | Meaning |
|------|---------|
| **HRC** | HealthRunCare (the platform) |
| **Lymora** | Legacy framework/brand name still referenced in some comments/templates |
| **Wallet** | A user's money account (`wallets` row); all products debit/credit it |
| **Plan** | A catalog product definition (`*_plans` table) |
| **Holding** | A user's instance of a plan (`investments`, `holdlock`, …) |
| **Secure Exchange** | The crypto deposit method (NOWPayments) |
| **HoldLock** | Locked‑savings product with early‑withdrawal penalty |
| **ROI accrual** | Periodic interest credited by cron |
| **Maturity** | End of a holding's term; principal + ROI credited |
| **IPN** | Instant Payment Notification — NOWPayments' signed webhook |
| **Reference** | Unique human/string ID per transaction (`HRC-DEP-…`, `HRC-WD-…`, `HRC-INV-…`, `HRC-ROI-…`, `HRC-MATURE-…`) |

---

*Generated from a full read of the codebase (schema, routing, API controllers, cron jobs,
payment integration, email subsystem, and views). Where this document and the code disagree,
the code is authoritative — keep this spec updated as the platform evolves.*

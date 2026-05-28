# Titan X Holdings — Developer Specification

> **Document Type:** Technical Specification  
> **Audience:** Developers building or extending the Titan X Holdings platform  
> **Source Project:** HealthRunCare (HRC) → VoltEdge Capital (VEC) → Titan X Holdings (TXH)  
> **Version:** 2.0 — Investment-focused scope with new features  

---

## 0. What Changed From Previous Spec

This spec supersedes the VoltEdge Capital developer spec. Key scope changes:

**Removed products:**
- `Impact Circuit` (charity/donations)
- `SteadyCharge` (maintenance yield)
- `VoltTrust` (trust fund)

**Retained products (renamed):**
- `VoltYield` → **X-Yield** (`investment`)
- `PowerLock` → **X-Lock** (`xlock`)
- `GridVentures` → **X-Grid** (`xgrid`)

**New products (net-new builds):**
- **X-Weekly** — weekly auto-invest program (`xweekly`)
- **X-Shares** — Tesla + Meta stock ownership module (`xshares`)
- **X-Rewards** — Tesla product catalogue at 40% discount (`xrewards`)

**Brand:**
- Company: `Titan X Holdings`
- Abbreviation: `TXH`
- Reference prefix: `TXH-`

---

## 1. Configuration Changes

### 1.1 `config/constants.php`

```php
define('APP_NAME',     'Titan X Holdings');
define('APP_SHORT',    'TXH');
define('APP_URL',      'https://titanxholdings.com');
define('APP_TAGLINE',  'Own More. Hold Stronger. Grow Faster.');
define('APP_AFFILIATE','Tesla');
define('CURRENCY',     'USD');
define('TIMEZONE',     'America/New_York');
define('OTP_EXPIRY_MINUTES', 10);
define('MAX_WITHDRAWAL_ATTEMPTS', 3);
// Remove SIMULATION_MODE from constants — move to env.php per environment
```

### 1.2 `config/env.php`

- `DB_NAME`: → `titanx_db`
- `HRC_ENV` / `VEC_ENV` → `TXH_ENV`
- Move all secrets to real environment variables (see §16)

### 1.3 `.htaccess`

```apache
# Replace env detection variable
SetEnvIf Host "localhost" TXH_ENV=dev
```

Update `config/env.php` to read `$_SERVER['TXH_ENV']`.

---

## 2. Database

### 2.1 Database name

```sql
CREATE DATABASE titanx_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Import from updated schema file: dbschema/titanx_db.sql
```

### 2.2 Tables to drop (removed products)

```sql
DROP TABLE IF EXISTS charities;
DROP TABLE IF EXISTS charity_donations;
DROP TABLE IF EXISTS maintenance_plans;
DROP TABLE IF EXISTS maintenance;
DROP TABLE IF EXISTS trustfund_plans;
DROP TABLE IF EXISTS trustfund;
```

Remove the corresponding `total_donations` column from `wallets` if not needed for any other product.

### 2.3 Tables to rename (retained products)

No structural table renames required. The table names (`investments`, `investment_plans`, `holdlock`, `holdlock_plans`, `infrastructure`, `infrastructure_plans`, `infrastructure_contributions`) remain as-is internally. Only user-facing display labels change.

### 2.4 New tables — X-Weekly

```sql
CREATE TABLE xweekly_programs (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         INT UNSIGNED NOT NULL,
  weekly_amount   DECIMAL(15,2) NOT NULL,
  total_invested  DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  total_earned    DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  roi_percent     DECIMAL(5,2) NOT NULL,
  status          ENUM('active','paused','cancelled') NOT NULL DEFAULT 'active',
  next_debit_date DATE NOT NULL,
  started_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_status (status),
  INDEX idx_next_debit (next_debit_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE xweekly_plans (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  plan_name       VARCHAR(120) NOT NULL,
  roi_percent     DECIMAL(5,2) NOT NULL,
  min_weekly      DECIMAL(15,2) NOT NULL DEFAULT 50.00,
  max_weekly      DECIMAL(15,2),
  description     TEXT,
  status          ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2.5 New tables — X-Shares

```sql
CREATE TABLE xshares_assets (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  asset_name      VARCHAR(120) NOT NULL,  -- e.g. "Tesla Stock"
  ticker          VARCHAR(10)  NOT NULL,  -- e.g. "TSLA"
  company         VARCHAR(120) NOT NULL,
  current_price   DECIMAL(15,4),          -- optional display value, admin-updated
  roi_percent     DECIMAL(5,2) NOT NULL,  -- platform yield on this asset
  payout_schedule ENUM('weekly','monthly','quarterly','maturity') NOT NULL DEFAULT 'monthly',
  duration_days   INT UNSIGNED,           -- optional lock on shares position
  min_amount      DECIMAL(15,2) NOT NULL DEFAULT 100.00,
  description     TEXT,
  status          ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE xshares_holdings (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         INT UNSIGNED NOT NULL,
  asset_id        INT UNSIGNED NOT NULL,
  amount          DECIMAL(15,2) NOT NULL,  -- USD invested
  entry_price     DECIMAL(15,4),           -- asset price at time of investment
  roi_earned      DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  maturity_date   DATE,
  payout_option   ENUM('periodic','maturity') NOT NULL DEFAULT 'periodic',
  status          ENUM('active','matured','unlocked') NOT NULL DEFAULT 'active',
  reference       VARCHAR(64) UNIQUE NOT NULL,
  started_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (asset_id) REFERENCES xshares_assets(id),
  INDEX idx_user_id (user_id),
  INDEX idx_status (status),
  INDEX idx_maturity (maturity_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2.6 New tables — X-Rewards

```sql
CREATE TABLE xrewards_products (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_name    VARCHAR(180) NOT NULL,
  description     TEXT,
  retail_price    DECIMAL(15,2) NOT NULL,
  reward_price    DECIMAL(15,2) NOT NULL,  -- 40% off retail
  discount_pct    DECIMAL(5,2) NOT NULL DEFAULT 40.00,
  image_path      VARCHAR(255),
  stock           INT DEFAULT NULL,        -- NULL = unlimited
  status          ENUM('active','inactive','out_of_stock') NOT NULL DEFAULT 'active',
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE xrewards_orders (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         INT UNSIGNED NOT NULL,
  product_id      INT UNSIGNED NOT NULL,
  quantity        INT UNSIGNED NOT NULL DEFAULT 1,
  unit_price      DECIMAL(15,2) NOT NULL,  -- reward_price at time of order
  total_price     DECIMAL(15,2) NOT NULL,
  shipping_details TEXT,                   -- JSON or free-text address
  status          ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  reference       VARCHAR(64) UNIQUE NOT NULL,  -- TXH-ORD-XXXXXX
  notes           TEXT,
  ordered_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES xrewards_products(id),
  INDEX idx_user_id (user_id),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2.7 `wallets` table — new columns

Add columns to track the two new earning products:

```sql
ALTER TABLE wallets
  ADD COLUMN xweekly_invested  DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER total_investments,
  ADD COLUMN xshares_invested  DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER xweekly_invested;
```

### 2.8 Seed data for X-Shares (initial)

```sql
INSERT INTO xshares_assets (asset_name, ticker, company, roi_percent, payout_schedule, min_amount, description) VALUES
('Tesla Stock',  'TSLA', 'Tesla, Inc.',         18.00, 'monthly', 500.00, 'Own a position in Tesla and earn from one of the world\'s most innovative companies.'),
('Meta Shares',  'META', 'Meta Platforms, Inc.', 14.00, 'monthly', 300.00, 'Own a position in Meta and earn from the global leader in social connectivity.');
```

### 2.9 Updated plan name seeds

Run the following to update retained products' plan names:

```sql
-- investment_plans
UPDATE investment_plans SET plan_name = 'Stable Arc Bond'            WHERE plan_name = 'Healthy Future Bond Plan';
UPDATE investment_plans SET plan_name = 'Growth Circuit Plan'        WHERE plan_name = 'Wellness Growth Real Estate Plan';
UPDATE investment_plans SET plan_name = 'Innovation Venture Fund'    WHERE plan_name = 'Health Innovation Venture Fund';
UPDATE investment_plans SET plan_name = 'Micro-Yield Community Plan' WHERE plan_name = 'Community Health Microfinance Plan';
UPDATE investment_plans SET plan_name = 'Grid Real Estate Plan'      WHERE plan_name = 'Green Hospital Infrastructure Plan';
UPDATE investment_plans SET plan_name = 'Sustainable Systems Plan'   WHERE plan_name = 'Healthy Food Systems Plan';
UPDATE investment_plans SET plan_name = 'Digital Frontier Fund'      WHERE plan_name = 'Digital Health Access Plan';

-- holdlock_plans
UPDATE holdlock_plans SET plan_name = 'Flexi Volt Lock'           WHERE plan_name = 'Flexi Health Lock';
UPDATE holdlock_plans SET plan_name = 'Standard Charge Lock'      WHERE plan_name = 'Standard Lock & Grow';
UPDATE holdlock_plans SET plan_name = 'Executive PowerLock Plus'  WHERE plan_name = 'Executive LockPlus';
UPDATE holdlock_plans SET plan_name = 'Prestige Capital Reserve'  WHERE plan_name = 'Prestige Capital Hold';
UPDATE holdlock_plans SET plan_name = 'Lifetime Arc Reserve'      WHERE plan_name = 'Lifetime Reserve Lock';

-- Migrate old reference IDs
UPDATE transactions SET reference = REPLACE(reference, 'HRC-', 'TXH-') WHERE reference LIKE 'HRC-%';
UPDATE transactions SET reference = REPLACE(reference, 'VEC-', 'TXH-') WHERE reference LIKE 'VEC-%';
```

---

## 3. Routing (`.htaccess`)

### 3.1 Full public route map

```apache
RewriteRule ^(about|contact|login|register|forgotpassword|platform|solutions|investment)$  pages/public/$1.php [L]
RewriteRule ^(xlock|xgrid|xweekly|xshares|xrewards|whytx)$  pages/public/$1.php [L]
```

### 3.2 Dashboard routes

```apache
RewriteRule ^dashboard\.(investment|xlock|xgrid|xweekly|xshares|xrewards|wallet|transactions|logout)$  pages/user/$1.php [L]
```

### 3.3 Admin routes

```apache
RewriteRule ^admin\.(dashboard|users|wallets|transactions|announcements|login|register|forgotpassword|settings)$  pages/admin/$1.php [L]
RewriteRule ^admin\.funds\.(investment|xlock|xgrid|xweekly|xshares)$   pages/admin/funds/$1.php [L]
RewriteRule ^admin\.rewards$                                             pages/admin/rewards.php [L]
```

### 3.4 Routes removed

Remove all rules for: `charity`, `holdlock`, `trustfund`, `minfrastructure`, `mdev`, `whyhrc`, `whyvec`, and all their dashboard/admin variants. Remove `dashboard.charity`, `dashboard.holdlock`, `dashboard.trustfund`, `dashboard.infrastructure`, `dashboard.development`.

---

## 4. File & Directory Structure

### 4.1 Files to delete (removed products)

```
pages/public/charity.php
pages/public/impact.php
pages/public/holdlock.php         ← was powerlock.php from previous rebrand
pages/public/trustfund.php
pages/public/volttrust.php
pages/public/minfrastructure.php
pages/public/mdev.php
pages/public/steadycharge.php
pages/user/charity.php
pages/user/impact.php
pages/user/holdlock.php
pages/user/powerlock.php
pages/user/trustfund.php
pages/user/volttrust.php
pages/user/development.php
pages/user/steadycharge.php
pages/admin/funds/holdlock.php
pages/admin/funds/powerlock.php
pages/admin/funds/trustfund.php
pages/admin/funds/volttrust.php
pages/admin/funds/maintenance.php
pages/admin/funds/steadycharge.php
pages/admin/donations.php
pages/admin/impact.php
api/backend/charity.php
api/backend/impact.php
api/backend/holdlock.php
api/backend/powerlock.php
api/backend/trustfund.php
api/backend/volttrust.php
api/backend/maintenance.php
api/backend/steadycharge.php
api/admin/donations.php
api/admin/impact.php
api/admin/funds_holdlock.php
api/admin/funds_powerlock.php
api/admin/funds_trustfund.php
api/admin/funds_volttrust.php
api/admin/funds_maintenance.php
api/admin/funds_steadycharge.php
api/cron/holdlock_cron.php
api/cron/powerlock_cron.php
api/cron/trustfund_cron.php
api/cron/volttrust_cron.php
api/cron/maintenance_cron.php
api/cron/steadycharge_cron.php
api/cron/update_charities.php
api/cron/update_impact.php
assets/js/charity.js
assets/js/impact.js
assets/js/holdlock.js
assets/js/powerlock.js
assets/js/trustfund.js
assets/js/volttrust.js
assets/js/maintenance.js
assets/js/steadycharge.js
assets/js/admin/donations.js
assets/js/admin/impact.js
```

### 4.2 Renamed files (retained products)

| Old | New |
|-----|-----|
| `pages/public/investment.php` | *(unchanged — X-Yield maps to `/investment`)* |
| `pages/public/gridventures.php` | `pages/public/xgrid.php` |
| `pages/public/powerlock.php` | `pages/public/xlock.php` |
| `pages/user/investment.php` | *(unchanged)* |
| `pages/user/gridventures.php` | `pages/user/xgrid.php` |
| `pages/user/powerlock.php` | `pages/user/xlock.php` |
| `pages/admin/funds/gridventures.php` | `pages/admin/funds/xgrid.php` |
| `pages/admin/funds/powerlock.php` | `pages/admin/funds/xlock.php` |
| `api/backend/gridventures.php` | `api/backend/xgrid.php` |
| `api/backend/powerlock.php` | `api/backend/xlock.php` |
| `api/admin/funds_gridventures.php` | `api/admin/funds_xgrid.php` |
| `api/admin/funds_powerlock.php` | `api/admin/funds_xlock.php` |
| `api/cron/gridventures_cron.php` | `api/cron/xgrid_cron.php` |
| `api/cron/powerlock_cron.php` | `api/cron/xlock_cron.php` |
| `assets/js/gridventures.js` | `assets/js/xgrid.js` |
| `assets/js/powerlock.js` | `assets/js/xlock.js` |

### 4.3 New files to create

```
pages/public/xweekly.php
pages/public/xshares.php
pages/public/xrewards.php
pages/public/whytx.php

pages/user/xweekly.php
pages/user/xshares.php
pages/user/xrewards.php

pages/admin/rewards.php
pages/admin/funds/xweekly.php
pages/admin/funds/xshares.php

api/backend/xweekly.php
api/backend/xshares.php
api/backend/xrewards.php

api/admin/funds_xweekly.php
api/admin/funds_xshares.php
api/admin/rewards.php

api/cron/xweekly_cron.php
api/cron/xshares_cron.php

assets/js/xweekly.js
assets/js/xshares.js
assets/js/xrewards.js
assets/js/admin/funds_xweekly.js
assets/js/admin/funds_xshares.js
assets/js/admin/rewards.js
```

---

## 5. New Backend Modules

### 5.1 `api/backend/xweekly.php`

Follows the same action-switch pattern as all other fund modules.

| Action | Behaviour |
|--------|-----------|
| `get_plans` | Return active `xweekly_plans` for display |
| `get_summary` | User's total weekly invested, total earned, active program count |
| `get_active` | User's active/paused `xweekly_programs` |
| `enrol` | Validate weekly_amount vs plan min/max; debit first week from wallet (`FOR UPDATE` lock); create `xweekly_programs` row; write `TXH-WKL-` transaction; email user + admin |
| `pause` | Set `status='paused'` on user's program; email confirmation |
| `resume` | Set `status='active'`; recalculate `next_debit_date`; email confirmation |
| `cancel` | Set `status='cancelled'`; no refund of amounts already invested |

**Weekly debit logic (cron-driven):**  
The cron job (not this controller) handles recurring debits. The `enrol` action only processes the first week's debit. All subsequent debits are handled by `xweekly_cron.php`.

### 5.2 `api/backend/xshares.php`

| Action | Behaviour |
|--------|-----------|
| `get_assets` | Return active `xshares_assets` (TSLA, META, etc.) |
| `get_summary` | User's total shares invested, total earned |
| `get_active` | User's active `xshares_holdings` |
| `get_matured` | User's matured/unlocked share holdings |
| `start_xshares` | Validate amount vs asset min; debit wallet; create `xshares_holdings` row; generate `TXH-SHR-` reference; write transaction; email user + admin |
| `unlock` | Early exit if duration_days set; credit wallet; mark status unlocked |

### 5.3 `api/backend/xrewards.php`

| Action | Behaviour |
|--------|-----------|
| `get_products` | Return active `xrewards_products` catalogue |
| `get_orders` | User's own orders with status |
| `place_order` | Validate product availability; debit wallet (reward_price × quantity); create `xrewards_orders` row; generate `TXH-ORD-` reference; write transaction; email user + admin |
| `cancel_order` | User can cancel if status is `pending`; refund wallet; update status |

> **Note:** Reward orders debit the wallet at the reward price. The platform acts as the fulfilment intermediary. No external e-commerce API required at this stage.

---

## 6. New Cron Jobs

### 6.1 `api/cron/xweekly_cron.php`

**Cadence:** Daily (runs every day; only processes programs where `next_debit_date <= CURDATE()`)

**Logic:**
```
1. SELECT active xweekly_programs WHERE next_debit_date <= CURDATE()
2. For each program:
   a. Check wallet balance >= weekly_amount (skip + log if insufficient)
   b. BEGIN TRANSACTION
   c. SELECT wallets FOR UPDATE
   d. Debit wallet balance by weekly_amount
   e. Credit total_investments and xweekly_invested on wallets
   f. Increment program total_invested
   g. Apply ROI accrual: roi_credit = weekly_amount * (roi_percent/100) / 52
   h. Credit roi_credit to wallet balance and program total_earned
   i. Write TXH-WKL-DEBIT transaction (debit) + TXH-ROI-WKL transaction (ROI credit)
   j. Update next_debit_date = next_debit_date + INTERVAL 7 DAY
   k. COMMIT
   l. Email weekly investment update to user
3. Log results to logs/xweekly_cron.log
```

### 6.2 `api/cron/xshares_cron.php`

**Cadence:** Per payout_schedule of each asset (daily check, processes due payouts)

**Logic:**
```
1. SELECT active xshares_holdings JOIN xshares_assets
2. For each holding where payout is due (based on payout_schedule + started_at):
   a. Calculate periodic ROI credit
   b. BEGIN TRANSACTION; wallet FOR UPDATE
   c. Credit ROI to wallet balance and holding roi_earned
   d. Write TXH-ROI-SHR transaction
   e. COMMIT; email user
3. Handle maturities (where maturity_date <= CURDATE()):
   a. Credit principal + full ROI to wallet
   b. Mark holding status = 'matured'
   c. Write TXH-MATURE-SHR transaction
   d. Email maturity notification
4. Log to logs/xshares_cron.log
```

---

## 7. Reference ID Format

All reference IDs use the `TXH-` prefix. Full format:

| Product / Event | Reference Format | Example |
|----------------|-----------------|---------|
| Deposit | `TXH-DEP-XXXXXX` | `TXH-DEP-000042` |
| Withdrawal | `TXH-WD-XXXXXX` | `TXH-WD-000017` |
| Investment (X-Yield) | `TXH-INV-XXXXXX` | `TXH-INV-000009` |
| X-Lock | `TXH-LOCK-XXXXXX` | `TXH-LOCK-000003` |
| X-Grid | `TXH-GRID-XXXXXX` | `TXH-GRID-000011` |
| X-Weekly enrol | `TXH-WKL-XXXXXX` | `TXH-WKL-000005` |
| X-Shares holding | `TXH-SHR-XXXXXX` | `TXH-SHR-000002` |
| X-Rewards order | `TXH-ORD-XXXXXX` | `TXH-ORD-000001` |
| ROI credit | `TXH-ROI-XXXXXX` | `TXH-ROI-000088` |
| Maturity payout | `TXH-MATURE-XXXXXX` | `TXH-MATURE-000006` |

Investor display ID (dashboard card): `TXH-INV-XXXX` (padded from user id).

---

## 8. Email Templates — New Additions

Add the following template keys to `api/utilities/email_temps.php`:

| Template Key | Trigger |
|-------------|---------|
| `xweekly_enrolled` | User enrols in X-Weekly program |
| `admin_xweekly_notification` | Admin alert on new enrolment |
| `xweekly_debit` | User's weekly debit processed |
| `xweekly_paused` | User pauses their program |
| `xweekly_cancelled` | User cancels their program |
| `xshares_started` | User starts an X-Shares holding |
| `admin_xshares_notification` | Admin alert on new share investment |
| `xshares_payout` | Periodic ROI payout on share holding |
| `xshares_matured` | Share holding matures |
| `xrewards_order_placed` | User places a rewards order |
| `admin_xrewards_order` | Admin alert on new order |
| `xrewards_order_confirmed` | Admin confirms order |
| `xrewards_order_shipped` | Admin marks order shipped |
| `xrewards_order_cancelled` | Order cancelled (with refund if applicable) |

### 8.1 Global string replacements in existing templates

| Find | Replace |
|------|---------|
| `HealthRunCare` | `Titan X Holdings` |
| `VoltEdge Capital` | `Titan X Holdings` |
| `HRC` / `VEC` (brand abbreviation) | `TXH` |
| `healthruncare.com` / `voltedgecapital.com` | `titanxholdings.com` |
| `HRC-` / `VEC-` (reference prefix) | `TXH-` |
| `Investment` (product label) | `X-Yield` |
| `HoldLock` / `PowerLock` | `X-Lock` |
| `Infrastructure` / `GridVentures` | `X-Grid` |

---

## 9. Frontend JS Endpoint Map

Update all `fetchApi()` calls in renamed/new JS files:

```javascript
// X-Yield (unchanged path):
fetchApi('/api/backend/investment.php', { action: 'get_plans' })

// X-Lock (renamed):
fetchApi('/api/backend/xlock.php', { action: 'get_plans' })

// X-Grid (renamed):
fetchApi('/api/backend/xgrid.php', { action: 'get_plans' })

// X-Weekly (new):
fetchApi('/api/backend/xweekly.php', { action: 'get_plans' })
fetchApi('/api/backend/xweekly.php', { action: 'enrol', weekly_amount: 100, plan_id: 1 })
fetchApi('/api/backend/xweekly.php', { action: 'pause', program_id: 3 })
fetchApi('/api/backend/xweekly.php', { action: 'resume', program_id: 3 })
fetchApi('/api/backend/xweekly.php', { action: 'cancel', program_id: 3 })

// X-Shares (new):
fetchApi('/api/backend/xshares.php', { action: 'get_assets' })
fetchApi('/api/backend/xshares.php', { action: 'start_xshares', asset_id: 1, amount: 1000 })

// X-Rewards (new):
fetchApi('/api/backend/xrewards.php', { action: 'get_products' })
fetchApi('/api/backend/xrewards.php', { action: 'place_order', product_id: 2, quantity: 1, shipping_details: '...' })
fetchApi('/api/backend/xrewards.php', { action: 'cancel_order', order_id: 5 })
```

---

## 10. `get_wallet_summary` Updates

In `api/backend/wallet.php`, the `get_wallet_summary` action currently recomputes `total_earnings` by summing `roi_earned` across the old product tables. Update to include the new products and exclude removed ones:

```php
// Tables to SUM roi_earned from:
$roi_tables = [
    'investments',               // X-Yield
    'holdlock',                  // X-Lock
    'infrastructure_contributions', // X-Grid
    'xweekly_programs',          // X-Weekly (use total_earned column)
    'xshares_holdings',          // X-Shares
    // REMOVED: trustfund, maintenance
];
```

Also update the dashboard wallet breakdown to display `xweekly_invested` and `xshares_invested` from the wallets table.

---

## 11. Admin Panel — New Modules

### 11.1 `api/admin/rewards.php`

| Action | Behaviour |
|--------|-----------|
| `get_products` | List all `xrewards_products` |
| `add_product` | Insert new product with auto-computed `reward_price` (retail * 0.60) |
| `edit_product` | Update product details |
| `toggle_status` | Active / inactive / out_of_stock |
| `get_orders` | All orders with user info, filterable by status |
| `update_order_status` | Move order through: `confirmed → shipped → delivered` |
| `cancel_order` | Admin cancel; refund user wallet; email user |

### 11.2 `api/admin/funds_xweekly.php`

| Action | Behaviour |
|--------|-----------|
| `add_plan` | Create new X-Weekly plan |
| `edit_plan` | Update plan |
| `get_programs` | List all user enrolments (for admin oversight) |
| `pause_program` | Admin-side pause |
| `cancel_program` | Admin-side cancel |

### 11.3 `api/admin/funds_xshares.php`

| Action | Behaviour |
|--------|-----------|
| `add_asset` | Create new share asset |
| `edit_asset` | Update asset details (including current_price for display) |
| `get_holdings` | All user share holdings across all assets |
| `toggle_asset` | Active / inactive |

---

## 12. Dashboard `wallet.php` — `user_impacts` Table

The `user_impacts` table tracked healthcare-specific metrics (`people_helped`, `communities_helped`, etc.). For Titan X Holdings:

**Option A (recommended):** Repurpose the table for investment milestones:
```sql
ALTER TABLE user_impacts
  CHANGE COLUMN people_helped     active_products  INT DEFAULT 0,
  CHANGE COLUMN communities_helped plans_matured   INT DEFAULT 0,
  CHANGE COLUMN packages_funded   total_entries    INT DEFAULT 0;
-- Rename or keep total_contributions and impact_score as-is
```

**Option B:** Drop `user_impacts` entirely if no gamification feature is required in this scope.

---

## 13. Design System

### 13.1 CSS Variables

```css
:root {
  --txh-bg:          #0A0A0A;
  --txh-surface:     #141414;
  --txh-border:      #252525;
  --txh-silver:      #C0C0C0;   /* primary brand accent */
  --txh-red:         #CC0000;   /* active/CTA accent */
  --txh-blue:        #4A9EFF;   /* data/charts */
  --txh-text:        #F5F5F5;
  --txh-muted:       #888888;
  --txh-success:     #22C55E;
  --txh-warning:     #F59E0B;
  --txh-danger:      #EF4444;

  --txh-font-display: 'Barlow Condensed', sans-serif;
  --txh-font-body:    'DM Sans', sans-serif;
  --txh-font-mono:    'IBM Plex Mono', monospace;
}
```

Add Google Fonts import at top of `assets/css/main.css`:
```css
@import url('https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=DM+Sans:wght@300;400;500&family=IBM+Plex+Mono:wght@400;500&display=swap');
```

### 13.2 Email palette

Light-mode emails (white background):
- Header bar: `#CC0000`
- CTA buttons: `#CC0000`
- Accent lines: `#C0C0C0`
- Body text: `#1A1A1A`
- Background: `#FFFFFF`
- App name in footer: `Titan X Holdings`
- Footer URL: `titanxholdings.com`

---

## 14. Post-Build Checklist

**Brand:**
- [ ] `config/constants.php` updated — APP_NAME, APP_SHORT, APP_URL
- [ ] `.htaccess` TXH_ENV variable updated
- [ ] All `HRC-` / `VEC-` references replaced with `TXH-`
- [ ] Logo, favicon, OG image replaced
- [ ] CSS variables updated; healthcare palette removed

**Removed products:**
- [ ] Charity, maintenance, trustfund tables dropped
- [ ] All related files deleted (see §4.1)
- [ ] Routes removed from `.htaccess`
- [ ] Nav items removed from dashboard and admin sidebar

**Retained products:**
- [ ] X-Yield (`/investment`) — unchanged, verified working
- [ ] X-Lock — files renamed, routes updated, JS endpoints updated
- [ ] X-Grid — files renamed, routes updated, JS endpoints updated

**New products:**
- [ ] `xweekly_programs` + `xweekly_plans` tables created
- [ ] `xshares_assets` + `xshares_holdings` tables created
- [ ] `xrewards_products` + `xrewards_orders` tables created
- [ ] `wallets` table updated with `xweekly_invested`, `xshares_invested` columns
- [ ] All new API backend files created and tested
- [ ] All new admin API files created and tested
- [ ] All new cron jobs created, tested manually, scheduled
- [ ] All new JS modules created, `fetchApi` paths correct
- [ ] All new email templates added and tested

**Financial integrity:**
- [ ] X-Weekly first debit uses `FOR UPDATE` wallet lock
- [ ] X-Shares investment uses `FOR UPDATE` wallet lock
- [ ] X-Rewards wallet debit uses `FOR UPDATE` wallet lock
- [ ] `get_wallet_summary` updated to include new products, exclude removed ones
- [ ] Cron ROI calculations verified against plan seed data

**Security:**
- [ ] `config/env.php` removed from version control, added to `.gitignore`
- [ ] All secrets rotated (DB, SMTP, NOWPayments)
- [ ] `api/cron/` blocked from HTTP access via `.htaccess`
- [ ] Admin CORS wildcard removed from `api/admin/dashboard.php`
- [ ] `admin_logs` table created or `logAdminAction()` removed

---

## 15. Glossary

| Term | Meaning |
|------|---------|
| TXH | Titan X Holdings (brand abbreviation) |
| TXH_ENV | Environment variable (dev / prod) |
| X-Yield | Core fixed-term investment product (maps to `investments` table) |
| X-Lock | Locked savings product (maps to `holdlock` / `holdlock_plans` tables) |
| X-Grid | Infrastructure investment product (maps to `infrastructure_contributions` table) |
| X-Weekly | Weekly auto-invest program (maps to `xweekly_programs` table) |
| X-Shares | Stock/share ownership product (maps to `xshares_holdings` table) |
| X-Rewards | Tesla product rewards hub (maps to `xrewards_orders` table) |
| Holding | A user's active instance of any investment product |
| Maturity | End of a holding's term; principal + ROI credited to wallet |
| Wallet | Single money account per user; all products debit/credit it |
| IPN | Instant Payment Notification — NOWPayments signed webhook |

---

*This document is authoritative for the Titan X Holdings build. Where the code and this spec diverge after implementation, the code is authoritative — update this document as the platform evolves.*

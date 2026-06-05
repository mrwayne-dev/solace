-- ============================================================
-- TitanXHoldings — Database Creation Script
-- File: dbschema/titanx_create.sql
-- Generated: 2026-05-31
--
-- Idempotent: drops & recreates all TXH tables and seeds the
-- catalog rows the platform needs to render its dashboards.
-- Safe to run on a fresh DB or to reset a stale one.
--
-- Usage:
--   mysql -u root -p < dbschema/titanx_create.sql
-- or:
--   mysql -u root -p titanx_db < dbschema/titanx_create.sql
--   (after manually creating the database)
-- ============================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
SET time_zone = '+00:00';

-- ============================================================
-- DATABASE
-- ============================================================
CREATE DATABASE IF NOT EXISTS `titanx_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `titanx_db`;

-- ============================================================
-- DROP LEGACY (HRC-era) TABLES IF PRESENT
-- ============================================================
DROP TABLE IF EXISTS `charity_donations`;
DROP TABLE IF EXISTS `charities`;
DROP TABLE IF EXISTS `trustfund`;
DROP TABLE IF EXISTS `trustfund_plans`;
DROP TABLE IF EXISTS `maintenance`;
DROP TABLE IF EXISTS `maintenance_plans`;

-- ============================================================
-- DROP TXH TABLES IN FK-SAFE ORDER (so re-runs are clean)
-- ============================================================
DROP TABLE IF EXISTS `xrewards_orders`;
DROP TABLE IF EXISTS `xrewards_products`;
DROP TABLE IF EXISTS `xshares_holdings`;
DROP TABLE IF EXISTS `xshares_assets`;
DROP TABLE IF EXISTS `xweekly_programs`;
DROP TABLE IF EXISTS `xweekly_plans`;
DROP TABLE IF EXISTS `infrastructure_contributions`;
DROP TABLE IF EXISTS `infrastructure_plans`;
DROP TABLE IF EXISTS `infrastructure`;
DROP TABLE IF EXISTS `investments`;
DROP TABLE IF EXISTS `investment_plans`;
DROP TABLE IF EXISTS `holdlock`;
DROP TABLE IF EXISTS `holdlock_plans`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `user_impacts`;
DROP TABLE IF EXISTS `announcements`;
DROP TABLE IF EXISTS `email_verifications`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `login_logs`;
DROP TABLE IF EXISTS `bank_details`;
DROP TABLE IF EXISTS `wallets`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `admins`;


-- ============================================================
-- CORE IDENTITY
-- ============================================================

CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `full_name` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `role` ENUM('user','admin') DEFAULT 'user',
  `status` ENUM('active','disabled') DEFAULT 'active',
  `profile_picture` VARCHAR(255) DEFAULT '/assets/images/avatar/default.png',
  `phone` VARCHAR(40) DEFAULT NULL,
  `country` VARCHAR(80) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `admins` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `full_name` VARCHAR(150) DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin','manager','support') DEFAULT 'manager',
  `status` ENUM('active','disabled') DEFAULT 'active',
  `profile_picture` VARCHAR(255) DEFAULT '/assets/images/avatar/admin_default.png',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_admins_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `settings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `cash_mailing_address` TEXT,
  `wallet_deposit_address` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- WALLETS & PAYMENTS
-- ============================================================

CREATE TABLE `wallets` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `balance` DECIMAL(12,2) DEFAULT 0.00,
  `total_deposited` DECIMAL(12,2) DEFAULT 0.00,
  `total_withdrawn` DECIMAL(12,2) DEFAULT 0.00,
  `total_investments` DECIMAL(12,2) DEFAULT 0.00,
  `xweekly_invested` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `xshares_invested` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `holdlock_savings` DECIMAL(12,2) DEFAULT 0.00,
  `total_earnings` DECIMAL(12,2) DEFAULT 0.00,
  `pending_withdrawals` DECIMAL(12,2) DEFAULT 0.00,
  `cash_mailing_address` TEXT,
  `wallet_deposit_address` TEXT,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_wallet` (`user_id`),
  CONSTRAINT `wallets_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `bank_details` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `method` ENUM('local_bank','wallet_address') NOT NULL,
  `details` JSON NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bank_user` (`user_id`),
  CONSTRAINT `bank_details_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `transactions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `method` ENUM('secure_exchange','cash_mailing','wire_transfer','local_bank','wallet_address','wallet','system') DEFAULT NULL,
  `details` JSON DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `reference` VARCHAR(100) NOT NULL,
  `status` ENUM('pending','completed','failed') DEFAULT 'completed',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_txn_reference` (`reference`),
  KEY `idx_user_transaction` (`user_id`),
  KEY `idx_txn_method` (`method`),
  CONSTRAINT `transactions_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- AUTH / SESSION
-- ============================================================

CREATE TABLE `password_resets` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `otp` VARCHAR(10) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reset_user` (`user_id`),
  CONSTRAINT `resets_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `email_verifications` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `otp` VARCHAR(10) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_verify_user` (`user_id`),
  CONSTRAINT `verify_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `login_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_type` ENUM('user','admin') NOT NULL,
  `user_id` INT NOT NULL,
  `ip` VARCHAR(100) DEFAULT NULL,
  `browser` VARCHAR(255) DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_login_user` (`user_type`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- ANNOUNCEMENTS (admin-managed dashboard updates)
-- ============================================================

CREATE TABLE `announcements` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `category` VARCHAR(50) DEFAULT 'general',
  `status` ENUM('published','draft') NOT NULL DEFAULT 'published',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- USER IMPACT METRICS (dashboard)
-- ============================================================

CREATE TABLE `user_impacts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `total_contributions` DECIMAL(12,2) DEFAULT 0.00,
  `people_helped` INT DEFAULT 0,
  `impact_score` DECIMAL(5,2) DEFAULT 0.00,
  `communities_helped` INT DEFAULT 0,
  `packages_funded` INT DEFAULT 0,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_impact_user` (`user_id`),
  CONSTRAINT `impact_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- X-LOCK  (fixed-term savings)
-- ============================================================

CREATE TABLE `holdlock_plans` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `purpose` TEXT,
  `min_amount` DECIMAL(15,2) NOT NULL,
  `max_amount` DECIMAL(15,2) DEFAULT NULL,
  `lock_period_text` VARCHAR(50) NOT NULL,
  `duration_days` INT DEFAULT NULL,
  `roi_range` VARCHAR(50) NOT NULL,
  `risk` VARCHAR(50) NOT NULL,
  `payout` VARCHAR(100) NOT NULL,
  `summary` TEXT NOT NULL,
  `icon` VARCHAR(50) NOT NULL,
  `color` VARCHAR(20) NOT NULL,
  `income_source` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `holdlock` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `plan_name` VARCHAR(100) DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `roi_percent` DECIMAL(5,2) DEFAULT 0.00,
  `duration_days` INT NOT NULL,
  `penalty_percent` DECIMAL(5,2) DEFAULT 1.50,
  `status` ENUM('locked','unlock_pending','matured','unlocked_early','completed') DEFAULT 'locked',
  `maturity_date` DATE DEFAULT NULL,
  `roi_earned` DECIMAL(12,2) DEFAULT 0.00,
  `penalty_applied` DECIMAL(12,2) DEFAULT 0.00,
  `payout_option` ENUM('maturity','early') DEFAULT 'maturity',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hold_user` (`user_id`),
  KEY `idx_hold_maturity` (`maturity_date`),
  CONSTRAINT `holdlock_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- X-YIELD  (fixed-duration investment plans)
-- ============================================================

CREATE TABLE `investment_plans` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `roi_percent` DECIMAL(5,2) NOT NULL,
  `duration_days` INT NOT NULL,
  `payout_option` VARCHAR(50) NOT NULL,
  `min_amount` DECIMAL(15,2) NOT NULL,
  `max_amount` DECIMAL(15,2) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `details` TEXT NOT NULL,
  `risk` VARCHAR(50) NOT NULL,
  `status` ENUM('active','hidden') NOT NULL DEFAULT 'active',
  `income` VARCHAR(255) NOT NULL,
  `summary` TEXT NOT NULL,
  `icon` VARCHAR(50) NOT NULL,
  `color` VARCHAR(20) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `investments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `plan_name` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `roi_percent` DECIMAL(5,2) DEFAULT 15.00,
  `duration_days` INT DEFAULT 30,
  `status` ENUM('active','completed') DEFAULT 'active',
  `maturity_date` DATE DEFAULT NULL,
  `roi_earned` DECIMAL(12,2) DEFAULT 0.00,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invest_user` (`user_id`),
  KEY `idx_invest_maturity` (`maturity_date`),
  CONSTRAINT `investments_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- X-GRID  (infrastructure co-investments)
-- ============================================================

CREATE TABLE `infrastructure` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `goal_amount` DECIMAL(12,2) DEFAULT 0.00,
  `raised_amount` DECIMAL(12,2) DEFAULT 0.00,
  `roi_percent` DECIMAL(5,2) DEFAULT 10.00,
  `status` ENUM('open','funded','complete') DEFAULT 'open',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_infra_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `infrastructure_plans` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `purpose` TEXT,
  `min_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `duration_days` INT NOT NULL DEFAULT 0,
  `roi_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `payout_option` VARCHAR(50) DEFAULT NULL,
  `risk_level` VARCHAR(50) DEFAULT NULL,
  `summary` TEXT,
  `color` VARCHAR(30) DEFAULT 'Green',
  `repayment_mode` VARCHAR(100) DEFAULT NULL,
  `icon` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `infrastructure_contributions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `plan_id` INT DEFAULT NULL,
  `user_id` INT NOT NULL,
  `project_id` INT DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `roi_earned` DECIMAL(12,2) DEFAULT 0.00,
  `status` ENUM('active','matured','unlocked') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_infra_user` (`user_id`),
  KEY `idx_infra_project` (`project_id`),
  CONSTRAINT `infra_contrib_fk_project` FOREIGN KEY (`project_id`) REFERENCES `infrastructure` (`id`) ON DELETE CASCADE,
  CONSTRAINT `infra_contrib_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- X-WEEKLY  (automated weekly contributions)
-- ============================================================

CREATE TABLE `xweekly_plans` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `plan_name` VARCHAR(120) NOT NULL,
  `roi_percent` DECIMAL(5,2) NOT NULL,
  `min_weekly` DECIMAL(15,2) NOT NULL DEFAULT 50.00,
  `max_weekly` DECIMAL(15,2) DEFAULT NULL,
  `description` TEXT,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `xweekly_programs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `weekly_amount` DECIMAL(15,2) NOT NULL,
  `total_invested` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `total_earned` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `roi_percent` DECIMAL(5,2) NOT NULL,
  `status` ENUM('active','paused','cancelled') NOT NULL DEFAULT 'active',
  `next_debit_date` DATE NOT NULL,
  `started_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_xweekly_user` (`user_id`),
  KEY `idx_xweekly_status` (`status`),
  KEY `idx_xweekly_next_debit` (`next_debit_date`),
  CONSTRAINT `xweekly_programs_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- X-SHARES  (fractional equity)
-- ============================================================

CREATE TABLE `xshares_assets` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `asset_name` VARCHAR(120) NOT NULL,
  `ticker` VARCHAR(10) NOT NULL,
  `company` VARCHAR(120) NOT NULL,
  `current_price` DECIMAL(15,4) DEFAULT NULL,
  `roi_percent` DECIMAL(5,2) NOT NULL,
  `payout_schedule` ENUM('weekly','monthly','quarterly','maturity') NOT NULL DEFAULT 'monthly',
  `duration_days` INT DEFAULT NULL,
  `min_amount` DECIMAL(15,2) NOT NULL DEFAULT 100.00,
  `description` TEXT,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_ticker` (`ticker`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `xshares_holdings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `asset_id` INT NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `entry_price` DECIMAL(15,4) DEFAULT NULL,
  `roi_earned` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `maturity_date` DATE DEFAULT NULL,
  `payout_option` ENUM('periodic','maturity') NOT NULL DEFAULT 'periodic',
  `status` ENUM('active','matured','unlocked') NOT NULL DEFAULT 'active',
  `reference` VARCHAR(64) NOT NULL,
  `started_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_xshares_reference` (`reference`),
  KEY `idx_xshares_asset` (`asset_id`),
  KEY `idx_xshares_user` (`user_id`),
  KEY `idx_xshares_status` (`status`),
  KEY `idx_xshares_maturity` (`maturity_date`),
  CONSTRAINT `xshares_holdings_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `xshares_holdings_fk_asset` FOREIGN KEY (`asset_id`) REFERENCES `xshares_assets` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- X-REWARDS  (loyalty catalog)
-- ============================================================

CREATE TABLE `xrewards_products` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `product_name` VARCHAR(180) NOT NULL,
  `description` TEXT,
  `retail_price` DECIMAL(15,2) NOT NULL,
  `reward_price` DECIMAL(15,2) NOT NULL,
  `discount_pct` DECIMAL(5,2) NOT NULL DEFAULT 40.00,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `stock` INT DEFAULT NULL,
  `status` ENUM('active','inactive','out_of_stock') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `xrewards_orders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(15,2) NOT NULL,
  `total_price` DECIMAL(15,2) NOT NULL,
  `shipping_details` TEXT,
  `status` ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `reference` VARCHAR(64) NOT NULL,
  `notes` TEXT,
  `ordered_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_xrewards_reference` (`reference`),
  KEY `idx_xrewards_product` (`product_id`),
  KEY `idx_xrewards_user` (`user_id`),
  KEY `idx_xrewards_status` (`status`),
  CONSTRAINT `xrewards_orders_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `xrewards_orders_fk_product` FOREIGN KEY (`product_id`) REFERENCES `xrewards_products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SEED DATA — settings (single row, blank deposit/mailing)
-- ============================================================

INSERT INTO `settings` (`id`,`cash_mailing_address`,`wallet_deposit_address`) VALUES
  (1, NULL, NULL);


-- ============================================================
-- SEED DATA — X-Lock plans (holdlock_plans)
-- ============================================================

INSERT INTO `holdlock_plans`
  (`name`,`purpose`,`min_amount`,`max_amount`,`lock_period_text`,`duration_days`,`roi_range`,`risk`,`payout`,`summary`,`icon`,`color`,`income_source`)
VALUES
  ('Flexi Lock','Short-term fixed-rate savings with full capital protection at maturity.',
   1000.00,500000.00,'180 days',180,'5.5–6.5%','Very Low','Lump sum at maturity',
   'A 180-day savings position priced above the high street. Capital is FSCS-protected and paid out automatically on maturity, with no early-withdrawal penalty inside the cooling-off window. Ideal for savers who want a known maturity date and a known rate.',
   'mdi:clock-outline','Green','Interbank money-market + UK gilts'),

  ('Standard Lock','One-year fixed-term savings with predictable income.',
   5000.00,500000.00,'12 months',365,'7.5–9.0%','Low','Annual or lump sum at maturity',
   'A balanced 12-month plan. Rate is fixed at enrolment and paid either annually or in full at maturity. The default option for first-time TXH savers looking to beat the high street by 3–4 percentage points without taking equity risk.',
   'mdi:calendar-check','Green','Diversified fixed-income portfolio'),

  ('Executive Lock','Two-year locked savings with elevated yield.',
   25000.00,1000000.00,'24 months',730,'11–14%','Moderate','Annual or lump sum at maturity',
   'A 24-month position with elevated yield, intended for capital you genuinely don''t need to touch. Returns settle to a UK current account at maturity or — optionally — in annual instalments. Capital is held in segregated accounts at a UK-regulated banking partner.',
   'mdi:briefcase-check','Blue','Mixed investment-grade credit + sovereign debt'),

  ('Prestige Reserve','Three-year long-term wealth plan for substantial balances.',
   100000.00,NULL,'36 months',1095,'18–22%','Moderate','Annual, bi-annual, or lump sum at maturity',
   'A premium fixed-term position rewarding patient capital with elevated yield. Operated under a structured-product framework with quarterly performance reporting. Suitable for high-balance investors building a long-duration core position.',
   'mdi:crown-outline','Orange','Structured-product portfolio'),

  ('Lifetime Reserve','Perpetual capital-preservation plan with lifetime income.',
   500000.00,NULL,'Lifetime (perpetual)',36500,'6–8% annual','Low','Annual or quarterly perpetual payout',
   'A perpetual capital-preservation vehicle. Income is paid quarterly or annually for the life of the plan; principal is returned in full on closure. Built for estates and family-office structures with multi-generational horizons.',
   'mdi:infinity','Green','Long-duration sovereign + index-linked');


-- ============================================================
-- SEED DATA — X-Yield plans (investment_plans)
-- ============================================================

INSERT INTO `investment_plans`
  (`title`,`roi_percent`,`duration_days`,`payout_option`,`min_amount`,`max_amount`,`description`,`details`,`risk`,`status`,`income`,`summary`,`icon`,`color`)
VALUES
  ('Starter Yield',8.50,180,'Lump sum at maturity',100.00,25000.00,
   'A 6-month entry plan with a fixed annualised rate.',
   'Designed for new TXH members building their first position. Rate is set at enrolment and paid in full at maturity.',
   'Low','active','Fixed-income portfolio',
   'Start small, lock in a rate above the high street, and watch the position settle automatically on the maturity date.',
   'mdi:chart-line','Green'),

  ('Growth Yield',13.50,365,'Bi-annual or lump sum at maturity',1000.00,150000.00,
   'A 12-month balanced plan combining fixed income and dividend equities.',
   'Mid-tier plan with optional bi-annual payouts. ROI is annualised — performance reports issued quarterly.',
   'Moderate','active','Diversified income portfolio',
   'A solid one-year position with a known rate and the option to take income twice a year instead of waiting for maturity.',
   'mdi:trending-up','Blue'),

  ('Compounder Plan',18.00,730,'Quarterly or at maturity',5000.00,500000.00,
   'A 24-month plan with elevated yield and optional quarterly distributions.',
   'Reflects the platform''s flagship growth vehicle. Returns are net of platform fees and reported quarterly.',
   'Moderate','active','Multi-strategy growth portfolio',
   'Two-year horizon, elevated yield, and the choice between drip-fed quarterly income or one lump sum on the maturity date.',
   'mdi:trending-up','Blue'),

  ('Apex Yield',25.00,1095,'Annual or lump sum at maturity',25000.00,2000000.00,
   'A 36-month long-duration plan for the highest available TXH yield.',
   'Higher-risk vehicle backed by structured products and growth equity. Capital at risk; performance disclosed before allocation.',
   'High','active','Structured products + growth equity',
   'Our highest-yield plan. Three years, elevated risk, and the option to take annual income or stack the full payout at maturity.',
   'mdi:rocket-launch','Orange');


-- ============================================================
-- SEED DATA — X-Grid plans (infrastructure_plans)
-- ============================================================

INSERT INTO `infrastructure_plans`
  (`name`,`purpose`,`min_amount`,`duration_days`,`roi_percent`,`payout_option`,`risk_level`,`summary`,`color`,`repayment_mode`,`icon`)
VALUES
  ('UK Solar Portfolio I',
   'Co-invest in a portfolio of operational UK solar generation sites with long-dated PPAs.',
   500.00,1095,11.50,'quarterly','Low',
   'A co-investment in operational UK solar assets with 15+ year power purchase agreements. Quarterly distributions backed by contracted revenues from regulated counterparties.',
   'Green','Quarterly distributions','mdi:solar-power'),

  ('Logistics Warehousing Fund',
   'Fractional position in a portfolio of last-mile logistics warehouses leased to UK retailers.',
   1000.00,1460,14.00,'quarterly','Low',
   'Income-producing warehousing assets leased to investment-grade UK retailers under long-dated leases. Returns are driven by contracted rents with annual RPI uplift.',
   'Blue','Quarterly distributions','mdi:warehouse'),

  ('Tier-III Data Centre Slot',
   'Capacity slot in a Tier-III data centre serving UK financial services clients.',
   2500.00,1825,16.00,'quarterly','Moderate',
   'A capacity-share position in a Tier-III data centre with five-year colocation contracts. Returns scale with utilisation; downside protected by minimum take-or-pay clauses.',
   'Blue','Quarterly distributions','mdi:server'),

  ('Wind Repower & Battery Co-Invest',
   'Co-invest in repowering existing UK onshore wind sites alongside grid-scale battery storage.',
   2500.00,1825,17.50,'semi-annual','Moderate',
   'Repowering older wind turbines with modern higher-yield equipment, paired with battery storage to capture peak grid pricing. Mixed contracted and merchant revenue.',
   'Green','Semi-annual distributions','mdi:wind-turbine'),

  ('Fibre-to-the-Premises Roll-Out',
   'Fractional position in an FTTP roll-out programme across rural UK communities.',
   1000.00,1460,15.00,'quarterly','Low',
   'Income-generating fibre network roll-out with government-backed subsidies. Returns are anchored in long-term ISP wholesale contracts.',
   'Green','Quarterly distributions','mdi:lan-connect'),

  ('Institutional Infrastructure Slot',
   'Open allocation slot in a portfolio normally reserved for institutional LPs.',
   25000.00,2190,19.00,'semi-annual','Moderate',
   'Higher-minimum allocation across a multi-asset infrastructure book that mirrors what UK pension funds hold. Diversified across power, transport, and digital infrastructure.',
   'Orange','Semi-annual distributions','mdi:office-building');


-- ============================================================
-- SEED DATA — X-Weekly plans
-- ============================================================

INSERT INTO `xweekly_plans` (`plan_name`,`roi_percent`,`min_weekly`,`max_weekly`,`description`,`status`) VALUES
  ('Starter Weekly',6.50,50.00,500.00,
   'A low-commitment weekly contribution plan for first-time investors. Pause, resume, or cancel anytime — no fees.',
   'active'),
  ('Steady Weekly',9.00,100.00,2000.00,
   'A balanced weekly plan. Contributions are deployed into a diversified income strategy with weekly compounding.',
   'active'),
  ('Compounder Weekly',12.50,250.00,5000.00,
   'A higher-yield weekly plan combining fixed income and dividend equity exposure. Best when sustained for 12+ months.',
   'active');


-- ============================================================
-- SEED DATA — X-Shares assets (TSLA + META)
-- ============================================================

INSERT INTO `xshares_assets`
  (`asset_name`,`ticker`,`company`,`current_price`,`roi_percent`,`payout_schedule`,`duration_days`,`min_amount`,`description`,`status`)
VALUES
  ('Tesla Stock','TSLA','Tesla, Inc.',
   NULL,18.00,'monthly',NULL,500.00,
   'Own a fractional position in Tesla and earn from one of the world''s most innovative companies.',
   'active'),

  ('Meta Shares','META','Meta Platforms, Inc.',
   NULL,14.00,'monthly',NULL,300.00,
   'Own a fractional position in Meta and earn from the global leader in social connectivity.',
   'active');


-- ============================================================
-- SEED DATA — X-Rewards starter catalog
-- ============================================================

INSERT INTO `xrewards_products`
  (`product_name`,`description`,`retail_price`,`reward_price`,`discount_pct`,`image_path`,`stock`,`status`)
VALUES
  ('Tesla Model 3 — Refundable Reservation','Reserve a Model 3 build slot at member pricing. Refundable.',
   1000.00,600.00,40.00,NULL,NULL,'active'),
  ('Tesla Wall Connector','Gen-3 Wall Connector for at-home EV charging. Member-priced.',
   480.00,288.00,40.00,NULL,50,'active'),
  ('Apple MacBook Pro 14"','M-series MacBook Pro at TXH member pricing.',
   2199.00,1319.40,40.00,NULL,25,'active'),
  ('Garmin Fenix 8','Premium multisport GPS smartwatch, member-priced.',
   1099.00,659.40,40.00,NULL,40,'active');


-- ============================================================
-- RESTORE SESSION DEFAULTS
-- ============================================================
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ============================================================
-- DONE
-- ============================================================
-- Next steps after running this script:
--   1. INSERT INTO admins  ... (create your bootstrap admin user)
--   2. INSERT INTO users   ... (or sign up via /register)
--   3. Verify by hitting   /admin.funds  /dashboard.xweekly  /dashboard.xshares
-- ============================================================

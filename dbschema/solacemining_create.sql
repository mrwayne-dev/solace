-- ============================================================
-- Solace Mining — Database Creation Script
-- File: dbschema/solacemining_create.sql
--
-- Idempotent: drops & recreates all Solace Mining tables and
-- seeds the catalog rows the platform needs (mining contract
-- tiers). Safe to run on a fresh DB or to reset a stale one.
--
-- Usage:
--   mysql -u root -p < dbschema/solacemining_create.sql
-- or:
--   mysql -u root -p solacemining_db < dbschema/solacemining_create.sql
--   (after manually creating the database)
-- ============================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- ============================================================
-- DATABASE
-- ============================================================
-- CREATE DATABASE IF NOT EXISTS `solacemining_db`
--   DEFAULT CHARACTER SET utf8mb4
--   COLLATE utf8mb4_unicode_ci;

-- USE `solacemining_db`;

-- ============================================================
-- DROP LEGACY TABLES (TitanX X-product era + HRC era) IF PRESENT
-- ============================================================
DROP TABLE IF EXISTS `charity_donations`;
DROP TABLE IF EXISTS `charities`;
DROP TABLE IF EXISTS `trustfund`;
DROP TABLE IF EXISTS `trustfund_plans`;
DROP TABLE IF EXISTS `maintenance`;
DROP TABLE IF EXISTS `maintenance_plans`;
DROP TABLE IF EXISTS `xrewards_orders`;
DROP TABLE IF EXISTS `xrewards_products`;
DROP TABLE IF EXISTS `xshares_holdings`;
DROP TABLE IF EXISTS `xshares_assets`;
DROP TABLE IF EXISTS `xweekly_programs`;
DROP TABLE IF EXISTS `xweekly_plans`;
DROP TABLE IF EXISTS `infrastructure_contributions`;
DROP TABLE IF EXISTS `infrastructure_plans`;
DROP TABLE IF EXISTS `infrastructure`;
DROP TABLE IF EXISTS `holdlock`;
DROP TABLE IF EXISTS `holdlock_plans`;
DROP TABLE IF EXISTS `user_impacts`;

-- ============================================================
-- DROP SOLACE MINING TABLES IN FK-SAFE ORDER (clean re-runs)
-- ============================================================
DROP TABLE IF EXISTS `referral_earnings`;
DROP TABLE IF EXISTS `referrals`;
DROP TABLE IF EXISTS `investments`;
DROP TABLE IF EXISTS `investment_plans`;
DROP TABLE IF EXISTS `transactions`;
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
  `referral_code` VARCHAR(20) DEFAULT NULL,
  `referred_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`),
  UNIQUE KEY `uniq_users_refcode` (`referral_code`),
  KEY `idx_users_referred_by` (`referred_by`),
  CONSTRAINT `users_fk_referrer` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
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
  `site_name` VARCHAR(120) DEFAULT 'Solace Mining',
  `support_email` VARCHAR(150) DEFAULT 'support@solacemining.com',
  `telegram_url` VARCHAR(255) DEFAULT NULL,
  `whatsapp_url` VARCHAR(255) DEFAULT NULL,
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
  `total_earnings` DECIMAL(12,2) DEFAULT 0.00,
  `referral_earnings` DECIMAL(12,2) DEFAULT 0.00,
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
-- MINING CONTRACT PLANS (tiered)
--   Daily profit accrues for `duration_days`; principal is
--   returned to the wallet on completion.
-- ============================================================

CREATE TABLE `investment_plans` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `min_amount` DECIMAL(15,2) NOT NULL,
  `max_amount` DECIMAL(15,2) DEFAULT NULL,        -- NULL = unlimited
  `daily_profit_percent` DECIMAL(5,2) NOT NULL,
  `duration_days` INT NOT NULL DEFAULT 5,
  `referral_commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
  `summary` TEXT,
  `icon` VARCHAR(50) NOT NULL DEFAULT 'mdi:pickaxe',
  `color` VARCHAR(20) NOT NULL DEFAULT 'Blue',
  `status` ENUM('active','hidden') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `investments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `plan_id` INT DEFAULT NULL,
  `plan_name` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `daily_profit_percent` DECIMAL(5,2) NOT NULL,
  `duration_days` INT NOT NULL DEFAULT 5,
  `days_paid` INT NOT NULL DEFAULT 0,
  `roi_earned` DECIMAL(12,2) DEFAULT 0.00,
  `status` ENUM('active','completed','cancelled') DEFAULT 'active',
  `start_date` DATE DEFAULT NULL,
  `maturity_date` DATE DEFAULT NULL,
  `last_payout_date` DATE DEFAULT NULL,
  `reference` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invest_user` (`user_id`),
  KEY `idx_invest_status` (`status`),
  KEY `idx_invest_maturity` (`maturity_date`),
  KEY `idx_invest_plan` (`plan_id`),
  CONSTRAINT `investments_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `investments_fk_plan` FOREIGN KEY (`plan_id`) REFERENCES `investment_plans` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- REFERRAL PROGRAM (10% commission)
-- ============================================================

CREATE TABLE `referrals` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `referrer_user_id` INT NOT NULL,
  `referred_user_id` INT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_referred_user` (`referred_user_id`),
  KEY `idx_referrer` (`referrer_user_id`),
  CONSTRAINT `referrals_fk_referrer` FOREIGN KEY (`referrer_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `referrals_fk_referred` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `referral_earnings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,                  -- the referrer who earns
  `referred_user_id` INT DEFAULT NULL,
  `source_investment_id` INT DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
  `status` ENUM('pending','credited') NOT NULL DEFAULT 'credited',
  `reference` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_refearn_user` (`user_id`),
  CONSTRAINT `refearn_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SEED DATA — settings (single row)
-- ============================================================

INSERT INTO `settings`
  (`id`,`site_name`,`support_email`,`telegram_url`,`whatsapp_url`,`cash_mailing_address`,`wallet_deposit_address`)
VALUES
  (1,'Solace Mining','support@solacemining.com',NULL,NULL,NULL,NULL);


-- ============================================================
-- SEED DATA — Mining contract tiers (investment_plans)
--   Source: plans/investment-platform-structure.txt
-- ============================================================

INSERT INTO `investment_plans`
  (`name`,`min_amount`,`max_amount`,`daily_profit_percent`,`duration_days`,`referral_commission_percent`,`summary`,`icon`,`color`,`status`)
VALUES
  ('Bronze',   100.00,   499.00,    4.00, 5, 10.00,
   'Entry-level mining contract. Allocate hashpower to our managed rigs and earn a fixed 4% daily for 5 days.',
   'mdi:pickaxe','Bronze','active'),

  ('Silver',   500.00,   2499.00,   6.00, 5, 10.00,
   'Step up your hashrate. Silver contracts return 6% daily across a 5-day cycle.',
   'mdi:hammer-wrench','Silver','active'),

  ('Gold',     2500.00,  4999.00,   8.00, 5, 10.00,
   'High-yield mining tier with priority rig allocation, paying 8% daily for 5 days.',
   'mdi:gold','Gold','active'),

  ('Platinum', 5000.00,  9999.00,  10.00, 5, 10.00,
   'Premium contract for serious miners. 10% daily for 5 days on dedicated capacity.',
   'mdi:diamond-stone','Platinum','active'),

  ('VIP',      10000.00, NULL,     12.00, 5, 10.00,
   'Our flagship contract. Unlimited allocation at the maximum 12% daily for 5 days.',
   'mdi:crown','VIP','active');


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
--   1. INSERT INTO admins ... (create your bootstrap admin user)
--   2. INSERT INTO users  ... (or sign up via /register)
--   3. Verify by hitting   /admin   /dashboard   /investment
-- ============================================================

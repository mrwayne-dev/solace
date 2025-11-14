
-- ===============================================================
--  HealthRunCare — Full Database Schema (Production Deployment)
-- ===============================================================
--  Version: 1.0
--  Compatible with MySQL 8.0+ / MariaDB 10.6+
-- ===============================================================

-- CREATE DATABASE IF NOT EXISTS `healthruncare_db`
--   CHARACTER SET utf8mb4
--   COLLATE utf8mb4_unicode_ci;

-- USE `healthruncare_db`;

-- SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- SET time_zone = "+00:00";

-- ===============================================================
--  TABLE: users
-- ===============================================================
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `full_name` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user','admin') DEFAULT 'user',
  `status` ENUM('active','disabled') DEFAULT 'active',
  `profile_picture` VARCHAR(255) DEFAULT '/assets/images/avatar/default.png',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================================
--  TABLE: admins
-- ===============================================================
CREATE TABLE `admins` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `full_name` VARCHAR(150) DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin','manager','support') DEFAULT 'manager',
  `status` ENUM('active','disabled') DEFAULT 'active',
  `profile_picture` VARCHAR(255) DEFAULT '/assets/images/avatar/admin_default.png',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================================
--  TABLE: wallets
-- ===============================================================
CREATE TABLE `wallets` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `balance` DECIMAL(12,2) DEFAULT 0.00,
  `total_deposited` DECIMAL(12,2) DEFAULT 0.00,
  `total_withdrawn` DECIMAL(12,2) DEFAULT 0.00,
  `total_donations` DECIMAL(12,2) DEFAULT 0.00,
  `total_investments` DECIMAL(12,2) DEFAULT 0.00,
  `holdlock_savings` DECIMAL(12,2) DEFAULT 0.00,
  `total_earnings` DECIMAL(12,2) DEFAULT 0.00,
  `pending_withdrawals` DECIMAL(12,2) DEFAULT 0.00,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_wallet` (`user_id`),
  CONSTRAINT `wallets_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================================
--  TABLE: bank_details
-- ===============================================================
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

-- ===============================================================
--  TABLE: charities
-- ===============================================================
CREATE TABLE `charities` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `organization` VARCHAR(200) DEFAULT NULL,
  `description` TEXT,
  `image` VARCHAR(255) DEFAULT '/assets/images/charity/placeholder.jpg',
  `goal_amount` DECIMAL(12,2) DEFAULT 0.00,
  `raised_amount` DECIMAL(12,2) DEFAULT 0.00,
  `status` ENUM('active','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_charity_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================================
--  TABLE: charity_donations
-- ===============================================================
CREATE TABLE `charity_donations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `charity_id` INT NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `reference` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_donation_user` (`user_id`),
  KEY `idx_donation_charity` (`charity_id`),
  CONSTRAINT `donation_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `donation_fk_charity` FOREIGN KEY (`charity_id`) REFERENCES `charities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================================
--  TABLE: investments
-- ===============================================================
CREATE TABLE `investments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `plan_name` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `roi_percent` DECIMAL(5,2) DEFAULT 0.00,
  `duration_days` INT DEFAULT 30,
  `status` ENUM('active','completed') DEFAULT 'active',
  `maturity_date` DATE DEFAULT NULL,
  `roi_earned` DECIMAL(12,2) DEFAULT 0.00,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invest_user` (`user_id`),
  CONSTRAINT `investments_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================================
--  TABLE: holdlock
-- ===============================================================
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
  CONSTRAINT `holdlock_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================================
--  TABLE: trustfund
-- ===============================================================
CREATE TABLE `trustfund` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `plan_name` VARCHAR(100) DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `roi_percent` DECIMAL(5,2) DEFAULT 0.00,
  `duration_days` INT DEFAULT 0,
  `penalty_percent` DECIMAL(5,2) DEFAULT 1.50,
  `purpose` VARCHAR(255) DEFAULT NULL,
  `maturity_date` DATE DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `roi_earned` DECIMAL(12,2) DEFAULT 0.00,
  `payout_option` ENUM('annual','maturity') DEFAULT 'maturity',
  `status` ENUM('active','matured','unlock_pending','unlocked_early','completed') DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_trust_user` (`user_id`),
  CONSTRAINT `trustfund_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================================
--  TABLE: maintenance
-- ===============================================================
CREATE TABLE `maintenance` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `plan_id` INT DEFAULT NULL,
  `user_id` INT NOT NULL,
  `plan_name` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `roi_earned` DECIMAL(12,2) DEFAULT 0.00,
  `frequency` ENUM('monthly','once') DEFAULT 'monthly',
  `status` ENUM('active','matured','unlocked','expired') DEFAULT 'active',
  `next_payment_date` DATE DEFAULT NULL,
  `maturity_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_maintenance_user` (`user_id`),
  CONSTRAINT `maintenance_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================================
--  TABLE: infrastructure & contributions
-- ===============================================================
CREATE TABLE `infrastructure` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `goal_amount` DECIMAL(12,2) DEFAULT 0.00,
  `raised_amount` DECIMAL(12,2) DEFAULT 0.00,
  `roi_percent` DECIMAL(5,2) DEFAULT 10.00,
  `status` ENUM('open','funded','complete') DEFAULT 'open',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `infrastructure_contributions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `plan_id` INT DEFAULT NULL,
  `user_id` INT NOT NULL,
  `project_id` INT DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `roi_earned` DECIMAL(12,2) DEFAULT 0.00,
  `status` ENUM('active','matured','unlocked') DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_infra_user` (`user_id`),
  CONSTRAINT `infra_contrib_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `infra_contrib_fk_project` FOREIGN KEY (`project_id`) REFERENCES `infrastructure` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================================
--  TABLE: transactions
-- ===============================================================
CREATE TABLE `transactions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `method` ENUM('secure_exchange','cash_mailing','wire_transfer','local_bank','wallet_address','wallet','system') DEFAULT NULL,
  `details` JSON DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `reference` VARCHAR(100) NOT NULL UNIQUE,
  `status` ENUM('pending','completed','failed') DEFAULT 'completed',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_transaction` (`user_id`),
  CONSTRAINT `transactions_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================================
--  TABLE: user_impacts
-- ===============================================================
CREATE TABLE `user_impacts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL UNIQUE,
  `total_contributions` DECIMAL(12,2) DEFAULT 0.00,
  `people_helped` INT DEFAULT 0,
  `impact_score` DECIMAL(5,2) DEFAULT 0.00,
  `communities_helped` INT DEFAULT 0,
  `packages_funded` INT DEFAULT 0,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `impact_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================================
--  TABLE: password_resets
-- ===============================================================
CREATE TABLE `password_resets` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `otp` VARCHAR(10) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `resets_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





-- ===============================================================
--  CHARITIES
-- ===============================================================
INSERT INTO `charities`
(`id`, `name`, `organization`, `description`, `image`, `goal_amount`, `raised_amount`, `status`, `created_at`)
VALUES
(7, 'Maternal Care Initiative', 'Africa Public Health Foundation (APHF)',
 'Expanding access to prenatal and postnatal care for mothers in underserved African regions.',
 '/assets/images/charity/maternal-care.jpg', 80000.00, 30000.00, 'active', NOW()),
(8, 'Clean Water for Health Program', 'The Global Fund',
 'Installing boreholes and filtration systems in rural health centers to reduce waterborne diseases.',
 '/assets/images/charity/clean-water.jpg', 60000.00, 3000.00, 'active', NOW()),
(9, 'Rural Health Outreach Network', 'PharmAccess Foundation',
 'Providing mobile clinics and telemedicine solutions to remote villages.',
 '/assets/images/charity/rural-outreach.jpg', 95000.00, 21000.00, 'active', NOW()),
(10, 'Child Immunization Drive', 'Against Malaria Foundation (AMF)',
 'Supporting mass vaccination programs to prevent common infectious diseases among children.',
 '/assets/images/charity/immunization.jpg', 50000.00, 36000.00, 'active', NOW()),
(11, 'Hospital Equipment Upgrade Fund', 'Transform Health Fund',
 'Providing critical diagnostic tools and life-saving machines to local health facilities.',
 '/assets/images/charity/equipment-upgrade.jpg', 120000.00, 129000.00, 'active', NOW()),
(12, 'Nutrition for Hope Program', 'Africa Humanitarian Action (AHA)',
 'Delivering essential food and supplements to children and elderly populations facing malnutrition.',
 '/assets/images/charity/nutrition.jpg', 45000.00, 28000.00, 'active', NOW());
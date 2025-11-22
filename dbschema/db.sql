-- ===========================================================
-- DATABASE: healthruncare_db
-- PURPOSE: Core schema for HealthRunCare Platform
-- AUTHOR: Mr Wayne (Generated from working dump)
-- ===========================================================

DROP DATABASE IF EXISTS `healthruncare_db`;
CREATE DATABASE `healthruncare_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `healthruncare_db`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ===== TABLE: users =====
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

-- ===== TABLE: admins =====
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

-- ===== TABLE: settings =====
CREATE TABLE `settings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `cash_mailing_address` TEXT,
  `wallet_deposit_address` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== TABLE: wallets =====
CREATE TABLE `wallets` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `balance` DECIMAL(12,2) DEFAULT '0.00',
  `total_deposited` DECIMAL(12,2) DEFAULT '0.00',
  `total_withdrawn` DECIMAL(12,2) DEFAULT '0.00',
  `total_donations` DECIMAL(12,2) DEFAULT '0.00',
  `total_investments` DECIMAL(12,2) DEFAULT '0.00',
  `holdlock_savings` DECIMAL(12,2) DEFAULT '0.00',
  `total_earnings` DECIMAL(12,2) DEFAULT '0.00',
  `pending_withdrawals` DECIMAL(12,2) DEFAULT '0.00',
  `cash_mailing_address` TEXT,
  `wallet_deposit_address` TEXT,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_wallet` (`user_id`),
  CONSTRAINT `wallets_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== TABLE: login_logs =====
CREATE TABLE `login_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_type` ENUM('user','admin') NOT NULL,
  `user_id` INT NOT NULL,
  `ip` VARCHAR(100) DEFAULT NULL,
  `browser` VARCHAR(255) DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== TABLE: bank_details =====
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

-- ===== TABLE: charities =====
CREATE TABLE `charities` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `organization` VARCHAR(200) DEFAULT NULL,
  `description` TEXT,
  `image` VARCHAR(255) DEFAULT '/assets/images/charity/placeholder.jpg',
  `goal_amount` DECIMAL(12,2) DEFAULT '0.00',
  `raised_amount` DECIMAL(12,2) DEFAULT '0.00',
  `status` ENUM('active','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_charity_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== TABLE: charity_donations =====
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

-- ===== TABLE: investment_plans =====
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
  `income` VARCHAR(255) NOT NULL,
  `summary` TEXT NOT NULL,
  `icon` VARCHAR(50) NOT NULL,
  `color` VARCHAR(20) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== TABLE: investments =====
CREATE TABLE `investments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `plan_name` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `roi_percent` DECIMAL(5,2) DEFAULT '15.00',
  `duration_days` INT DEFAULT '30',
  `status` ENUM('active','completed') DEFAULT 'active',
  `maturity_date` DATE DEFAULT NULL,
  `roi_earned` DECIMAL(12,2) DEFAULT '0.00',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invest_user` (`user_id`),
  KEY `idx_invest_maturity` (`maturity_date`),
  CONSTRAINT `investments_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== TABLE: transactions =====
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

-- ===== TABLE: user_impacts =====
CREATE TABLE `user_impacts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL UNIQUE,
  `total_contributions` DECIMAL(12,2) DEFAULT '0.00',
  `people_helped` INT DEFAULT '0',
  `impact_score` DECIMAL(5,2) DEFAULT '0.00',
  `communities_helped` INT DEFAULT '0',
  `packages_funded` INT DEFAULT '0',
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `impact_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== TABLE: maintenance =====
CREATE TABLE `maintenance` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `plan_id` INT DEFAULT NULL,
  `user_id` INT NOT NULL,
  `plan_name` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `roi_earned` DECIMAL(12,2) DEFAULT '0.00',
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

-- ===== TABLE: holdlock =====
CREATE TABLE `holdlock` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `plan_name` VARCHAR(100) DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `roi_percent` DECIMAL(5,2) DEFAULT '0.00',
  `duration_days` INT NOT NULL,
  `penalty_percent` DECIMAL(5,2) DEFAULT '1.50',
  `status` ENUM('locked','unlock_pending','matured','unlocked_early','completed') DEFAULT 'locked',
  `maturity_date` DATE DEFAULT NULL,
  `roi_earned` DECIMAL(12,2) DEFAULT '0.00',
  `penalty_applied` DECIMAL(12,2) DEFAULT '0.00',
  `payout_option` ENUM('maturity','early') DEFAULT 'maturity',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hold_user` (`user_id`),
  CONSTRAINT `holdlock_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== TABLE: infrastructure =====
CREATE TABLE `infrastructure` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `goal_amount` DECIMAL(12,2) DEFAULT '0.00',
  `raised_amount` DECIMAL(12,2) DEFAULT '0.00',
  `roi_percent` DECIMAL(5,2) DEFAULT '10.00',
  `status` ENUM('open','funded','complete') DEFAULT 'open',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_infra_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== TABLE: infrastructure_contributions =====
CREATE TABLE `infrastructure_contributions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `plan_id` INT DEFAULT NULL,
  `user_id` INT NOT NULL,
  `project_id` INT DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `roi_earned` DECIMAL(12,2) DEFAULT '0.00',
  `status` ENUM('active','matured','unlocked') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_infra_user` (`user_id`),
  KEY `idx_infra_project` (`project_id`),
  CONSTRAINT `infra_contrib_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `infra_contrib_fk_project` FOREIGN KEY (`project_id`) REFERENCES `infrastructure` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS trustfund_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    purpose VARCHAR(300) NOT NULL,
    min_amount DECIMAL(15,2) NOT NULL,
    max_amount DECIMAL(15,2) DEFAULT NULL, -- NULL means unlimited
    duration_days INT NOT NULL,
    roi_percent DECIMAL(10,2) NOT NULL,
    risk VARCHAR(50) NOT NULL,
    payout_option VARCHAR(100) NOT NULL,
    summary TEXT NOT NULL,
    icon VARCHAR(50) NOT NULL,
    color VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS infrastructure_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    purpose TEXT,
    min_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    duration_days INT NOT NULL DEFAULT 0,
    roi_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
    payout_option VARCHAR(50) DEFAULT NULL,
    risk_level VARCHAR(50) DEFAULT NULL,
    summary TEXT,
    color VARCHAR(30) DEFAULT 'Green',
    repayment_mode VARCHAR(100) DEFAULT NULL,
    icon VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS maintenance_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    purpose TEXT,
    min_amount DECIMAL(15,2) NOT NULL,
    max_amount DECIMAL(15,2) DEFAULT NULL,
    duration_days INT NOT NULL,
    roi_percent DECIMAL(5,2) NOT NULL,
    risk VARCHAR(50),
    payout VARCHAR(100),
    summary TEXT,
    color VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



ALTER TABLE `investment_plans`
ADD COLUMN `status` ENUM('active','hidden') NOT NULL DEFAULT 'active' AFTER `risk`;

  -- ===============================================================
  --  CHARITIES
  -- ===============================================================
  INSERT INTO `charities`
  (`id`, `name`, `organization`, `description`, `image`, `goal_amount`, `raised_amount`, `status`, `created_at`)
  VALUES
  (1, 'Maternal Care Initiative', 'Africa Public Health Foundation (APHF)',
  'Expanding access to prenatal and postnatal care for mothers in underserved African regions.',
  '/assets/images/charity/maternal-care.jpg', 80000.00, 30000.00, 'active', NOW()),
  (2, 'Clean Water for Health Program', 'The Global Fund',
  'Installing boreholes and filtration systems in rural health centers to reduce waterborne diseases.',
  '/assets/images/charity/clean-water.jpg', 60000.00, 3000.00, 'active', NOW()),
  (3, 'Rural Health Outreach Network', 'PharmAccess Foundation',
  'Providing mobile clinics and telemedicine solutions to remote villages.',
  '/assets/images/charity/rural-outreach.jpg', 95000.00, 21000.00, 'active', NOW()),
  (4, 'Child Immunization Drive', 'Against Malaria Foundation (AMF)',
  'Supporting mass vaccination programs to prevent common infectious diseases among children.',
  '/assets/images/charity/immunization.jpg', 50000.00, 36000.00, 'active', NOW()),
  (5, 'Hospital Equipment Upgrade Fund', 'Transform Health Fund',
  'Providing critical diagnostic tools and life-saving machines to local health facilities.',
  '/assets/images/charity/equipment-upgrade.jpg', 120000.00, 129000.00, 'active', NOW()),
  (6, 'Nutrition for Hope Program', 'Africa Humanitarian Action (AHA)',
  'Delivering essential food and supplements to children and elderly populations facing malnutrition.',
  '/assets/images/charity/nutrition.jpg', 45000.00, 28000.00, 'active', NOW());

  -- ===========================
-- 📦 INVESTMENT PLANS
-- ===========================
INSERT INTO `investment_plans` 
(id, title, roi_percent, duration_days, payout_option, min_amount, max_amount, description, details, risk, income, summary, icon, color) VALUES

(1, 'Healthy Future Bond Plan', 11.00, 540, 'Quarterly or at maturity', 500.00, 100000.00,
'Build Community Diagnostic Centers',
'Supporting local health screenings and medical supplies for underserved communities.',
'Low',
'Fees from diagnostic tests and partnerships with hospitals & insurance providers',
'Your money helps build diagnostic centers that earn from medical tests. As these centers generate consistent service income, you earn up to 12% in 18 months — safely and with social impact.',
'mdi:factory', 'Green'),

(2, 'Wellness Growth Real Estate Plan', 16.50, 730, 'Bi-annual or lump sum at maturity', 5000.00, 250000.00,
'Build and Lease Wellness & Rehabilitation Facilities',
'Your investment funds the construction of modern wellness centers leased to physiotherapy clinics, fitness brands, and recovery operators.',
'Moderate',
'Rental income from wellness centers and long-term lease agreements',
'You help build wellness facilities that lease to health operators. Rental payments provide steady income that\'s shared with you as up to 18% return in 2 years.',
'mdi:home-building', 'Blue'),

(3, 'Health Innovation Venture Fund', 30.00, 1095, 'At maturity (end of term)', 10000.00, 500000.00,
'Support High-Growth Health-Tech Startups',
'Your capital helps scale innovative startups working on medical devices, biotech research, and digital health technologies.',
'High',
'Equity profit from startup growth, technology licensing, and company buyouts',
'You back new health-tech companies. When they grow or get acquired, you share in their success — earning up to 35% within 3 years.',
'mdi:lightning-bolt', 'Orange'),

(4, 'Community Health Microfinance Plan', 9.00, 360, 'At maturity (end of 12 months)', 300.00, 20000.00,
'Empower Small Health Businesses',
'This plan provides microloans to rural pharmacies, small clinics, and health workers who repay with fair interest.',
'Low',
'Loan interest payments from local health entrepreneurs',
'Your investment gives small loans to trusted healthcare providers. They repay with interest, and you earn up to 10% in just one year — while supporting community care.',
'mdi:hand-extend', 'Green'),

(5, 'Green Hospital Infrastructure Plan', 15.00, 730, 'Annual or at maturity', 2000.00, 200000.00,
'Finance Eco-Friendly Hospital Upgrades',
'Your investment enables hospitals to install solar systems, energy-saving equipment, and water recycling units.',
'Moderate',
'Revenue-sharing from hospitals\' reduced energy costs and green subsidies',
'Hospitals save thousands on electricity and maintenance after green upgrades. Part of those savings is paid back to investors — giving you up to 16% return in 2 years.',
'mdi:leaf', 'Blue'),

(6, 'Healthy Food Systems Plan', 13.50, 540, 'Quarterly or at maturity', 1000.00, 50000.00,
'Strengthen Nutrition and Food Security',
'This plan funds farm-to-health programs and healthy meal suppliers for hospitals, schools, and wellness institutions.',
'Moderate',
'Profits from produce sales, supply contracts, and wholesale distribution partnerships',
'Your money supports healthy food producers who sell to hospitals and schools. As they make profits, you earn up to 15% in 18 months.',
'mdi:food', 'Blue'),

(7, 'Digital Health Access Plan', 20.00, 730, 'Annual or at maturity', 2000.00, 100000.00,
'Expand Online Health Platforms & Telemedicine',
'Invest in digital platforms offering remote doctor consultations, e-prescriptions, and mobile diagnostics.',
'Moderate to High',
'Subscription fees, teleconsultation charges, data partnerships, and health service commissions',
'You invest in the future of digital healthcare. As more users join and pay for services online, you earn up to 22% return in 2 years — while helping expand access to doctors worldwide.',
'mdi:phone', 'Orange');


INSERT INTO `holdlock_plans` 
(`id`, `name`, `purpose`, `min_amount`, `max_amount`, `lock_period_text`, `duration_days`, `roi_range`, `risk`, `payout`, `summary`, `icon`, `color`) VALUES
(1, 'Flexi Health Lock Plan',
 'A short-term plan designed for clients who want safe, quick returns while keeping their capital secure.',
 10000, 100000, '6 months', 180, '3–4%', 'Very Low', 'Full payout at maturity',
 'Ideal for clients seeking liquidity and short-term growth. Funds are safely held and paid out at the end of the term.',
 'mdi:clock-outline', 'Green'),

(2, 'Standard Lock & Grow Plan',
 'A one-year plan offering predictable and consistent growth with minimal risk.',
 20000, 300000, '12 months', 365, '7–9%', 'Low', 'Annual or full payout at maturity',
 'A balanced one-year growth plan for investors who prefer stability and moderate fixed returns.',
 'mdi:calendar-check', 'Green'),

(3, 'Executive LockPlus Plan',
 'A two-year plan for individuals and organizations seeking better returns from moderate-term investments.',
 50000, 500000, '24 months', 730, '14–18%', 'Moderate', 'Annual or full payout at maturity',
 'Perfect for mid- to high-level investors seeking strong, consistent growth over two years with minimal risk exposure.',
 'mdi:briefcase-check', 'Blue'),

(4, 'Prestige Capital Hold Plan',
 'A premium plan for investors with large capital who seek long-term, high-yield returns.',
 250000, NULL, '36 months', 1095, '25–30%', 'Moderate', 'Annual, bi-annual, or full payout at maturity',
 'A long-term, asset-secure investment option that rewards patience with premium returns and stable growth.',
 'mdi:crown-outline', 'Orange'),

(5, 'Lifetime Reserve Lock Plan',
 'A lifelong plan designed for wealth preservation and consistent annual income.',
 1000000, NULL, 'Lifetime (Perpetual)', 36500, '6–8% annual', 'Low',
 'Annual or quarterly lifetime payout',
 'An exclusive wealth preservation plan that guarantees lifetime income, ideal for estates, families, or organizations focused on long-term legacy.',
 'mdi:infinity', 'Green');


INSERT INTO trustfund_plans
(name, purpose, min_amount, max_amount, duration_days, roi_percent, risk, payout_option, summary, icon, color)
VALUES
('Child Education Growth Plan',
'Build a secure education fund with guaranteed growth.',
500, 50000, 1095, 25.0, 'Low', 'Annual or at maturity',
'A safe plan that builds education funds with secure returns.',
'mdi:school-outline', 'Green'),

('Legacy Wealth Trust Plan',
'Generate long-term generational wealth.',
500000, NULL, 1825, 55.0, 'Moderate', 'Annual or at maturity',
'A premium plan for multigenerational wealth growth.',
'mdi:family-tree', 'Blue'),

('Business Succession Trust Plan',
'Secure business growth or transition.',
5000, 500000, 1460, 48.0, 'Moderate to High', 'Annual or at maturity',
'Helps entrepreneurs expand while earning profitable returns.',
'mdi:briefcase-outline', 'Orange'),

('Medical Protection Trust Plan',
'Create a secure medical reserve with capital growth.',
300, 25000, 1095, 18.0, 'Low', 'Quarterly or at maturity',
'A health-focused savings option supporting emergency needs.',
'mdi:heart-pulse', 'Green'),

('Future Builders Business Plan',
'Support startups and young entrepreneurs.',
1000, 100000, 1460, 38.0, 'Moderate', 'Annual or at maturity',
'Capital invested into innovation projects with social impact.',
'mdi:rocket-outline', 'Blue'),

('Guardian Trust Income Plan',
'Steady annual income for beneficiaries.',
10000, 200000, 1825, 35.0, 'Low to Moderate', 'Annual income distribution',
'A reliable annual income-producing plan ideal for dependents.',
'mdi:shield-check-outline', 'Green'),

('Perpetual Legacy Trust Plan',
'Lifetime income with preserved principal.',
1000000, NULL, 9999, 11.0, 'Low', 'Annual or quarterly for life',
'An elite perpetual plan generating lifetime income.',
'mdi:infinity', 'Orange');


INSERT INTO infrastructure_plans 
(name, purpose, min_amount, duration_days, roi_percent, payout_option, risk_level, summary, color, repayment_mode, icon)
VALUES
('Basic Diagnostic Plan',
 'To support community and mid-level hospitals with portable ultrasound diagnostic systems for early disease detection.',
 10000, 365, 9.0, 'quarterly', 'Very Low',
 'Investors fund the purchase and setup of ultrasound systems. Clinics repay from diagnostic service fees, returning your full capital plus up to 10% profit within one year.',
 'Green', 'Quarterly payments over 12 months', 'mdi:office-building-outline'),

('Imaging Growth Plan',
 'To deploy digital X-ray imaging systems for regional hospitals and diagnostic centers.',
 20000, 540, 13.5, 'quarterly', 'Low',
 'Investors help hospitals acquire X-ray systems. Hospitals repay from patient scan revenue, and investors earn 12–15% total profit within 18 months.',
 'Green', 'Quarterly or semi-annual', 'mdi:bank-outline'),

('Advanced Radiology Plan',
 'To enable hospitals to install CT scanners and expand access to high-precision imaging services.',
 50000, 730, 17.5, 'monthly', 'Moderate',
 'Investors finance CT equipment. HealthRunCare manages contracts and collects hospital payments, ensuring full repayment plus up to 20% ROI over 24 months.',
 'Blue', 'Monthly or quarterly payments', 'mdi:chart-bar'),

('Dialysis Infrastructure Plan',
 'To expand kidney care capacity through the installation of dialysis centers and water treatment systems.',
 100000, 900, 20.0, 'quarterly', 'Moderate',
 'Your investment supports dialysis services in hospitals. Repayments come from patient treatment revenue, returning 18–22% profit over 30 months.',
 'Blue', 'Quarterly payments with inflation-adjusted escalation clause', 'mdi:water'),

('Complete Operating Room Equipment Plan',
 'To establish modern operating theatres equipped for advanced surgical operations in partner hospitals.',
 150000, 1095, 22.5, 'monthly', 'Moderate',
 'Investors finance complete operating room setups. Hospitals repay from surgical revenues, providing up to 25% profit over three years.',
 'Blue', 'Monthly or quarterly with partial early payment options', 'mdi:needle'),

('Hospital Diagnostic Wing Installation Plan',
 'To construct and equip an entire hospital diagnostic and imaging wing, combining MRI, CT, X-ray, ultrasound, and lab systems.',
 500000, 1095, 29.0, 'quarterly', 'Moderate-Low',
 'A high-value plan for institutional investors to fund full hospital diagnostic wings. Returns reach 30% over three years, backed by large-scale facility repayment contracts.',
 'Green', 'Quarterly or bi-annual', 'mdi:hospital-building');

INSERT INTO maintenance_plans (name, purpose, min_amount, max_amount, duration_days, roi_percent, risk, payout, summary, color)
VALUES
('Maintenance Support Starter Plan', 'Entry plan for basic healthcare maintenance support.', 10000, 50000, 270, 5.5, 'Very Low', 'Full payout at maturity', 'Modest maintenance-backed returns.', 'Green'),
('Standard Equipment Care Plan', 'One-year maintenance program for hospital devices.', 25000, 300000, 360, 9.0, 'Low', 'Annual or full payout at maturity', 'Steady returns supporting equipment upkeep.', 'Green'),
('Infrastructure Development Plan', 'Mid-term investment into major repair infrastructure.', 50000, 500000, 720, 16.5, 'Moderate', 'Annual or full payout at maturity', 'Upgrades and modernization for better service delivery.', 'Blue'),
('Premium Equipment Sustainability Plan', 'Top-tier maintenance pipeline for premium systems.', 250000, NULL, 1080, 22.0, 'Moderate', 'Multiple payout options', 'Long-term sustainability with high impact.', 'Blue'),
('Lifetime Equipment Trust Plan', 'Perpetual sustainability income for healthcare assets.', 1000000, NULL, 36500, 7.0, 'Low', 'Lifetime payouts', 'Legacy plan offering continuous profits.', 'Green');
  
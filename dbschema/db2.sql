--
-- Table structure for table `admins`
--
CREATE TABLE `admins` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('super_admin','manager','support') COLLATE utf8mb4_unicode_ci DEFAULT 'manager',
  `status` enum('active','disabled') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '/assets/images/avatar/admin_default.png',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `bank_details`
--
CREATE TABLE `bank_details` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `method` enum('local_bank','wallet_address') COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` json NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `charities`
--
CREATE TABLE `charities` (
  `id` int NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '/assets/images/charity/placeholder.jpg',
  `goal_amount` decimal(12,2) DEFAULT '0.00',
  `raised_amount` decimal(12,2) DEFAULT '0.00',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `charity_donations`
--
CREATE TABLE `charity_donations` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `charity_id` int NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `holdlock`
--
CREATE TABLE `holdlock` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `plan_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `roi_percent` decimal(5,2) DEFAULT '0.00',
  `duration_days` int NOT NULL,
  `penalty_percent` decimal(5,2) DEFAULT '1.50',
  `status` enum('locked','unlock_pending','matured','unlocked_early','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'locked',
  `maturity_date` date DEFAULT NULL,
  `roi_earned` decimal(12,2) DEFAULT '0.00',
  `penalty_applied` decimal(12,2) DEFAULT '0.00',
  `payout_option` enum('maturity','early') COLLATE utf8mb4_unicode_ci DEFAULT 'maturity',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `holdlock_plans`
--
CREATE TABLE `holdlock_plans` (
  `id` int NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci,
  `min_amount` decimal(15,2) NOT NULL,
  `max_amount` decimal(15,2) DEFAULT NULL,
  `lock_period_text` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_days` int DEFAULT NULL,
  `roi_range` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `risk` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payout` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `infrastructure`
--
CREATE TABLE `infrastructure` (
  `id` int NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `goal_amount` decimal(12,2) DEFAULT '0.00',
  `raised_amount` decimal(12,2) DEFAULT '0.00',
  `roi_percent` decimal(5,2) DEFAULT '10.00',
  `status` enum('open','funded','complete') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `infrastructure_contributions`
--
CREATE TABLE `infrastructure_contributions` (
  `id` int NOT NULL,
  `plan_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `project_id` int DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `roi_earned` decimal(12,2) DEFAULT '0.00',
  `status` enum('active','matured','unlocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `infrastructure_plans`
--
CREATE TABLE `infrastructure_plans` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci,
  `min_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `duration_days` int NOT NULL DEFAULT '0',
  `roi_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `payout_option` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `risk_level` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Green',
  `repayment_mode` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `investments`
--
CREATE TABLE `investments` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `plan_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `roi_percent` decimal(5,2) DEFAULT '15.00',
  `duration_days` int DEFAULT '30',
  `status` enum('active','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `maturity_date` date DEFAULT NULL,
  `roi_earned` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `investment_plans`
--
CREATE TABLE `investment_plans` (
  `id` int NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roi_percent` decimal(5,2) NOT NULL,
  `duration_days` int NOT NULL,
  `payout_option` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_amount` decimal(15,2) NOT NULL,
  `max_amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `risk` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','hidden') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `income` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `login_logs`
--
CREATE TABLE `login_logs` (
  `id` int NOT NULL,
  `user_type` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `ip` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `maintenance`
--
CREATE TABLE `maintenance` (
  `id` int NOT NULL,
  `plan_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `plan_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `roi_earned` decimal(12,2) DEFAULT '0.00',
  `frequency` enum('monthly','once') COLLATE utf8mb4_unicode_ci DEFAULT 'monthly',
  `status` enum('active','matured','unlocked','expired') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `next_payment_date` date DEFAULT NULL,
  `maturity_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `maintenance_plans`
--
CREATE TABLE `maintenance_plans` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci,
  `min_amount` decimal(15,2) NOT NULL,
  `max_amount` decimal(15,2) DEFAULT NULL,
  `duration_days` int DEFAULT NULL,
  `roi_percent` decimal(5,2) NOT NULL,
  `risk` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payout` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `password_resets`
--
CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `otp` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `settings`
--
CREATE TABLE `settings` (
  `id` int NOT NULL,
  `cash_mailing_address` text COLLATE utf8mb4_unicode_ci,
  `wallet_deposit_address` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `transactions`
--
CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` enum('secure_exchange','cash_mailing','wire_transfer','local_bank','wallet_address','wallet','system') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `details` json DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','completed','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `trustfund`
--
CREATE TABLE `trustfund` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `plan_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `roi_percent` decimal(5,2) DEFAULT '0.00',
  `duration_days` int DEFAULT '0',
  `penalty_percent` decimal(5,2) DEFAULT '1.50',
  `purpose` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maturity_date` date DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `roi_earned` decimal(12,2) DEFAULT '0.00',
  `payout_option` enum('annual','maturity') COLLATE utf8mb4_unicode_ci DEFAULT 'maturity',
  `status` enum('active','matured','unlock_pending','unlocked_early','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `trustfund_plans`
--
CREATE TABLE `trustfund_plans` (
  `id` int NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_amount` decimal(15,2) NOT NULL,
  `max_amount` decimal(15,2) DEFAULT NULL,
  `duration_days` int NOT NULL,
  `roi_percent` decimal(10,2) NOT NULL,
  `risk` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payout_option` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `status` enum('active','disabled') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '/assets/images/avatar/default.png',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `user_impacts`
--
CREATE TABLE `user_impacts` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `total_contributions` decimal(12,2) DEFAULT '0.00',
  `people_helped` int DEFAULT '0',
  `impact_score` decimal(5,2) DEFAULT '0.00',
  `communities_helped` int DEFAULT '0',
  `packages_funded` int DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `wallets`
--
CREATE TABLE `wallets` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `balance` decimal(12,2) DEFAULT '0.00',
  `total_deposited` decimal(12,2) DEFAULT '0.00',
  `total_withdrawn` decimal(12,2) DEFAULT '0.00',
  `total_donations` decimal(12,2) DEFAULT '0.00',
  `total_investments` decimal(12,2) DEFAULT '0.00',
  `holdlock_savings` decimal(12,2) DEFAULT '0.00',
  `total_earnings` decimal(12,2) DEFAULT '0.00',
  `pending_withdrawals` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `cash_mailing_address` text COLLATE utf8mb4_unicode_ci,
  `wallet_deposit_address` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `charities` (`id`, `name`, `organization`, `description`, `image`, `goal_amount`, `raised_amount`, `status`, `created_at`) VALUES
(1, 'Maternal Care Initiative', 'Africa Public Health Foundation (APHF)', 'Expanding access to prenatal and postnatal care for mothers in underserved African regions.', '/assets/images/charity/maternal-care.jpg', 80000.00, 60050.00, 'active', '2025-11-08 23:15:57'),
(2, 'Clean Water for Health Program', 'The Global Fund', 'Installing boreholes and filtration systems in rural health centers to reduce waterborne diseases.', '/assets/images/charity/clean-water.jpg', 60000.00, 30000.00, 'active', '2025-11-08 23:15:57'),
(3, 'Rural Health Outreach Network', 'PharmAccess Foundation', 'Providing mobile clinics and telemedicine solutions to remote villages.', '/assets/images/charity/rural-outreach.jpg', 95000.00, 80750.00, 'active', '2025-11-08 23:15:57'),
(4, 'Child Immunization Drive', 'Against Malaria Foundation (AMF)', 'Supporting mass vaccination programs to prevent common infectious diseases among children.', '/assets/images/charity/immunization.jpg', 50000.00, 15000.00, 'active', '2025-11-08 23:15:57'),
(5, 'Hospital Equipment Upgrade Fund', 'Transform Health Fund', 'Providing diagnostic tools and life-saving machines to health facilities.', '/assets/images/charity/equipment-upgrade.jpg', 120000.00, 10000.00, 'active', '2025-11-08 23:15:57'),
(6, 'Nutrition for Hope Program', 'Africa Humanitarian Action (AHA)', 'Delivering essential food and supplements to children and the elderly facing malnutrition.', '/assets/images/charity/nutrition.jpg', 45000.00, 18000.00, 'active', '2025-11-08 23:15:57');


INSERT INTO `holdlock_plans` (`id`, `name`, `purpose`, `min_amount`, `max_amount`, `lock_period_text`, `duration_days`, `roi_range`, `risk`, `payout`, `summary`, `icon`, `color`, `created_at`) VALUES
(1, 'Flexi Health Lock Plan', 'A short-term plan designed for clients who want safe, quick returns while keeping their capital secure.', 10000.00, 99999999.99, '180 days', 180, '2.00%', 'Very Low', 'Full payout at maturity', 'Ideal for clients seeking liquidity and short-term growth. Funds are safely held and paid out at the end of the term.', 'mdi:clock-outline', 'Green', '2025-11-22 13:42:57'),
(2, 'Standard Lock & Grow Plan', 'A one-year plan offering predictable and consistent growth with minimal risk.', 20000.00, 300000.00, '12 months', 365, '7–9%', 'Low', 'Annual or full payout at maturity', 'A balanced one-year growth plan for investors who prefer stability and moderate fixed returns.', 'mdi:calendar-check', 'Green', '2025-11-22 13:42:57'),
(3, 'Executive LockPlus Plan', 'A two-year plan for individuals and organizations seeking better returns from moderate-term investments.', 50000.00, 500000.00, '24 months', 730, '14–18%', 'Moderate', 'Annual or full payout at maturity', 'Perfect for mid- to high-level investors seeking strong, consistent growth over two years with minimal risk exposure.', 'mdi:briefcase-check', 'Blue', '2025-11-22 13:42:57'),
(4, 'Prestige Capital Hold Plan', 'A premium plan for investors with large capital who seek long-term, high-yield returns.', 250000.00, NULL, '36 months', 1095, '25–30%', 'Moderate', 'Annual, bi-annual, or full payout at maturity', 'A long-term, asset-secure investment option that rewards patience with premium returns and stable growth.', 'mdi:crown-outline', 'Orange', '2025-11-22 13:42:57'),
(5, 'Lifetime Reserve Lock Plan', 'A lifelong plan designed for wealth preservation and consistent annual income.', 1000000.00, NULL, 'Lifetime (Perpetual)', 36500, '6–8% annual', 'Low', 'Annual or quarterly lifetime payout', 'An exclusive wealth preservation plan that guarantees lifetime income, ideal for estates, families, or organizations focused on long-term legacy.', 'mdi:infinity', 'Green', '2025-11-22 13:42:57'),
(6, 'Testing Plan', 'HoldLock Plan for admin.', 30.00, 99999999.99, '20 days', 20, '1.00%', 'low', 'At maturity', 'A holdlock savings plan.', 'mdi:lock-outline', 'green', '2025-11-22 14:57:14');


INSERT INTO `infrastructure_plans` (`id`, `name`, `purpose`, `min_amount`, `duration_days`, `roi_percent`, `payout_option`, `risk_level`, `summary`, `color`, `repayment_mode`, `icon`, `created_at`, `updated_at`) VALUES
(1, 'Basic Diagnostic Plan', 'To support community and mid-level hospitals with portable ultrasound diagnostic systems for early disease detection.', 10000.00, 365, 9.00, 'quarterly', 'Very Low', 'Investors fund the purchase and setup of ultrasound systems. Clinics repay from diagnostic service fees, returning your full capital plus up to 10% profit within one year.', 'Green', 'Quarterly payments over 12 months', 'mdi:office-building-outline', '2025-11-22 18:38:18', NULL),
(2, 'Imaging Growth Plan', 'To deploy digital X-ray imaging systems for regional hospitals and diagnostic centers.', 20000.00, 540, 13.50, 'quarterly', 'Low', 'Investors help hospitals acquire X-ray systems. Hospitals repay from patient scan revenue, and investors earn 12–15% total profit within 18 months.', 'Green', 'Quarterly or semi-annual', 'mdi:bank-outline', '2025-11-22 18:38:18', NULL),
(3, 'Advanced Radiology Plan', 'To enable hospitals to install CT scanners and expand access to high-precision imaging services.', 50000.00, 730, 17.50, 'monthly', 'Moderate', 'Investors finance CT equipment. HealthRunCare manages contracts and collects hospital payments, ensuring full repayment plus up to 20% ROI over 24 months.', 'Blue', 'Monthly or quarterly payments', 'mdi:chart-bar', '2025-11-22 18:38:18', NULL),
(4, 'Dialysis Infrastructure Plan', 'To expand kidney care capacity through the installation of dialysis centers and water treatment systems.', 100000.00, 900, 20.00, 'quarterly', 'Moderate', 'Your investment supports dialysis services in hospitals. Repayments come from patient treatment revenue, returning 18–22% profit over 30 months.', 'Blue', 'Quarterly payments with inflation-adjusted escalation clause', 'mdi:water', '2025-11-22 18:38:18', NULL),
(5, 'Complete Operating Room Equipment Plan', 'To establish modern operating theatres equipped for advanced surgical operations in partner hospitals.', 150000.00, 1095, 22.50, 'monthly', 'Moderate', 'Investors finance complete operating room setups. Hospitals repay from surgical revenues, providing up to 25% profit over three years.', 'Blue', 'Monthly or quarterly with partial early payment options', 'mdi:needle', '2025-11-22 18:38:18', NULL),
(6, 'Hospital Diagnostic Wing Installation Plan', 'To construct and equip an entire hospital diagnostic and imaging wing, combining MRI, CT, X-ray, ultrasound, and lab systems.', 500000.00, 1095, 29.00, 'quarterly', 'Moderate-Low', 'A high-value plan for institutional investors to fund full hospital diagnostic wings. Returns reach 30% over three years, backed by large-scale facility repayment contracts.', 'Green', 'Quarterly or bi-annual', 'mdi:hospital-building', '2025-11-22 18:38:18', NULL),
(7, 'Testing', 'Testing', 40000.00, 365, 10.00, 'quarterly', 'Low', 'Plan summary.', 'Green', 'Quarterly payments', 'mdi:office-building-outline', '2025-11-22 21:11:51', NULL);


INSERT INTO `investment_plans` (`id`, `title`, `roi_percent`, `duration_days`, `payout_option`, `min_amount`, `max_amount`, `created_at`, `description`, `details`, `risk`, `status`, `income`, `summary`, `icon`, `color`) VALUES
(1, 'Healthy Future Bond Plan', 11.00, 540, 'Quarterly or at maturity', 500.00, 100000.00, '2025-11-22 12:22:52', 'Build Community Diagnostic Centers', 'Supporting local health screenings and medical supplies for underserved communities.', 'Low', 'active', 'Fees from diagnostic tests and partnerships with hospitals & insurance providers', 'Your money helps build diagnostic centers that earn from medical tests. As these centers generate consistent service income, you earn up to 12% in 18 months — safely and with social impact.', 'mdi:factory', 'Green'),
(2, 'Wellness Growth Real Estate Plan', 16.50, 730, 'Bi-annual or lump sum at maturity', 5000.00, 250000.00, '2025-11-22 12:22:52', 'Build and Lease Wellness & Rehabilitation Facilities', 'Your investment funds the construction of modern wellness centers leased to physiotherapy clinics, fitness brands, and recovery operators.', 'Moderate', 'active', 'Rental income from wellness centers and long-term lease agreements', 'You help build wellness facilities that lease to health operators. Rental payments provide steady income that\'s shared with you as up to 18% return in 2 years.', 'mdi:home-building', 'Blue'),
(3, 'Health Innovation Venture Fund', 30.00, 1095, 'At maturity (end of term)', 10000.00, 500000.00, '2025-11-22 12:22:52', 'Support High-Growth Health-Tech Startups', 'Your capital helps scale innovative startups working on medical devices, biotech research, and digital health technologies.', 'High', 'active', 'Equity profit from startup growth, technology licensing, and company buyouts', 'You back new health-tech companies. When they grow or get acquired, you share in their success — earning up to 35% within 3 years.', 'mdi:lightning-bolt', 'Orange'),
(4, 'Community Health Microfinance Plan', 9.00, 360, 'At maturity (end of 12 months)', 300.00, 20000.00, '2025-11-22 12:22:52', 'Empower Small Health Businesses', 'This plan provides microloans to rural pharmacies, small clinics, and health workers who repay with fair interest.', 'Low', 'active', 'Loan interest payments from local health entrepreneurs', 'Your investment gives small loans to trusted healthcare providers. They repay with interest, and you earn up to 10% in just one year — while supporting community care.', 'mdi:hand-extend', 'Green'),
(5, 'Green Hospital Infrastructure Plan', 15.00, 730, 'Annual or at maturity', 2000.00, 200000.00, '2025-11-22 12:22:52', 'Finance Eco-Friendly Hospital Upgrades', 'Your investment enables hospitals to install solar systems, energy-saving equipment, and water recycling units.', 'Moderate', 'active', 'Revenue-sharing from hospitals\' reduced energy costs and green subsidies', 'Hospitals save thousands on electricity and maintenance after green upgrades. Part of those savings is paid back to investors — giving you up to 16% return in 2 years.', 'mdi:leaf', 'Blue'),
(6, 'Healthy Food Systems Plan', 13.50, 540, 'Quarterly or at maturity', 1000.00, 50000.00, '2025-11-22 12:22:52', 'Strengthen Nutrition and Food Security', 'This plan funds farm-to-health programs and healthy meal suppliers for hospitals, schools, and wellness institutions.', 'Moderate', 'active', 'Profits from produce sales, supply contracts, and wholesale distribution partnerships', 'Your money supports healthy food producers who sell to hospitals and schools. As they make profits, you earn up to 15% in 18 months.', 'mdi:food', 'Blue'),
(7, 'Digital Health Access Plan', 20.00, 730, 'Annual or at maturity', 2000.00, 100000.00, '2025-11-22 12:22:52', 'Expand Online Health Platforms & Telemedicine', 'Invest in digital platforms offering remote doctor consultations, e-prescriptions, and mobile diagnostics.', 'Moderate to High', 'active', 'Subscription fees, teleconsultation charges, data partnerships, and health service commissions', 'You invest in the future of digital healthcare. As more users join and pay for services online, you earn up to 22% return in 2 years — while helping expand access to doctors worldwide.', 'mdi:phone', 'Orange'),
(8, 'testing plan', 25.50, 365, 'maturity', 1.00, 9999999.00, '2025-11-22 13:24:06', 'Investment plan description.', 'Detailed plan features.', 'low', 'active', 'General investment returns.', 'Summary of the plan.', 'mdi:chart-line', 'Blue');



INSERT INTO `maintenance_plans` (`id`, `name`, `purpose`, `min_amount`, `max_amount`, `duration_days`, `roi_percent`, `risk`, `payout`, `summary`, `color`, `created_at`) VALUES
(1, 'Maintenance Support Starter Plan', 'Entry plan for basic healthcare maintenance support.', 10000.00, 50000.00, 270, 5.50, 'Very Low', 'Full payout at maturity', 'Modest maintenance-backed returns.', 'Green', '2025-11-22 20:19:10'),
(2, 'Standard Equipment Care Plan', 'One-year maintenance program for hospital devices.', 25000.00, 300000.00, 360, 9.00, 'Low', 'Annual or full payout at maturity', 'Steady returns supporting equipment upkeep.', 'Green', '2025-11-22 20:19:10'),
(3, 'Infrastructure Development Plan', 'Mid-term investment into major repair infrastructure.', 50000.00, 500000.00, 720, 16.50, 'Moderate', 'Annual or full payout at maturity', 'Upgrades and modernization for better service delivery.', 'Blue', '2025-11-22 20:19:10'),
(4, 'Premium Equipment Sustainability Plan', 'Top-tier maintenance pipeline for premium systems.', 250000.00, NULL, 1080, 22.00, 'Moderate', 'Multiple payout options', 'Long-term sustainability with high impact.', 'Blue', '2025-11-22 20:19:10'),
(5, 'Lifetime Equipment Trust Plan', 'Perpetual sustainability income for healthcare assets.', 1000000.00, NULL, NULL, 7.00, 'Low', 'Lifetime payouts', 'Legacy plan offering continuous profits.', 'Green', '2025-11-22 20:19:10');


INSERT INTO `trustfund_plans` (`id`, `name`, `purpose`, `min_amount`, `max_amount`, `duration_days`, `roi_percent`, `risk`, `payout_option`, `summary`, `icon`, `color`, `created_at`) VALUES
(1, 'Child Education Growth Plan', 'Build a secure education fund with guaranteed growth.', 500.00, 50000.00, 1095, 25.00, 'Low', 'Annual or at maturity', 'A safe plan that builds education funds with secure returns.', 'mdi:school-outline', 'Green', '2025-11-22 14:15:26'),
(2, 'Legacy Wealth Trust Plan', 'Generate long-term generational wealth.', 500000.00, NULL, 1825, 55.00, 'Moderate', 'Annual or at maturity', 'A premium plan for multigenerational wealth growth.', 'mdi:family-tree', 'Blue', '2025-11-22 14:15:26'),
(3, 'Business Succession Trust Plan', 'Secure business growth or transition.', 5000.00, 500000.00, 1460, 48.00, 'Moderate to High', 'Annual or at maturity', 'Helps entrepreneurs expand while earning profitable returns.', 'mdi:briefcase-outline', 'Orange', '2025-11-22 14:15:26'),
(4, 'Medical Protection Trust Plan', 'Create a secure medical reserve with capital growth.', 300.00, 25000.00, 1095, 18.00, 'Low', 'Quarterly or at maturity', 'A health-focused savings option supporting emergency needs.', 'mdi:heart-pulse', 'Green', '2025-11-22 14:15:26'),
(5, 'Future Builders Business Plan', 'Support startups and young entrepreneurs.', 1000.00, 100000.00, 1460, 38.00, 'Moderate', 'Annual or at maturity', 'Capital invested into innovation projects with social impact.', 'mdi:rocket-outline', 'Blue', '2025-11-22 14:15:26'),
(6, 'Guardian Trust Income Plan', 'Steady annual income for beneficiaries.', 10000.00, 200000.00, 1825, 35.00, 'Low to Moderate', 'Annual income distribution', 'A reliable annual income-producing plan ideal for dependents.', 'mdi:shield-check-outline', 'Green', '2025-11-22 14:15:26'),
(7, 'Perpetual Legacy Trust Plan', 'Lifetime income with preserved principal.', 1000000.00, NULL, 9999, 11.00, 'Low', 'Annual or quarterly for life', 'An elite perpetual plan generating lifetime income.', 'mdi:infinity', 'Orange', '2025-11-22 14:15:26');



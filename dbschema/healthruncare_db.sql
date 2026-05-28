-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 24, 2025 at 07:01 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `healthruncare_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('super_admin','manager','support') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'manager',
  `status` enum('active','disabled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '/assets/images/avatar/admin_default.png',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `full_name`, `email`, `password`, `role`, `status`, `profile_picture`, `last_login`, `created_at`) VALUES
(1, 'mr wayne', 'mr wayne', 'aleruchi0987@gmail.com', '$2y$10$tssRL/rKOgDPKtFCs5w6MetlAM3fF2FBTMO0S4mkRD6iwjdkZHLKC', 'manager', 'active', '/assets/images/avatar/admin_default.png', '2025-11-24 07:16:30', '2025-11-12 15:22:41');

-- --------------------------------------------------------

--
-- Table structure for table `bank_details`
--

CREATE TABLE `bank_details` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `method` enum('local_bank','wallet_address') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` json NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `charities`
--

CREATE TABLE `charities` (
  `id` int NOT NULL,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '/assets/images/charity/placeholder.jpg',
  `goal_amount` decimal(12,2) DEFAULT '0.00',
  `raised_amount` decimal(12,2) DEFAULT '0.00',
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `charities`
--

INSERT INTO `charities` (`id`, `name`, `organization`, `description`, `image`, `goal_amount`, `raised_amount`, `status`, `created_at`) VALUES
(1, 'Maternal Care Initiative', 'Africa Public Health Foundation (APHF)', 'Expanding access to prenatal and postnatal care for mothers in underserved African regions.', '/assets/images/charity/maternal-care.png', 80000.00, 60050.00, 'active', '2025-11-08 23:15:57'),
(2, 'Clean Water for Health Program', 'The Global Fund', 'Installing boreholes and filtration systems in rural health centers to reduce waterborne diseases.', '/assets/images/charity/clean-water.png', 60000.00, 30000.00, 'active', '2025-11-08 23:15:57'),
(3, 'Rural Health Outreach Network', 'PharmAccess Foundation', 'Providing mobile clinics and telemedicine solutions to remote villages.', '/assets/images/charity/rural-outreach.png', 95000.00, 80750.00, 'active', '2025-11-08 23:15:57'),
(4, 'Child Immunization Drive', 'Against Malaria Foundation (AMF)', 'Supporting mass vaccination programs to prevent common infectious diseases among children.', '/assets/images/charity/immunization.png', 50000.00, 15000.00, 'active', '2025-11-08 23:15:57'),
(5, 'Hospital Equipment Upgrade Fund', 'Transform Health Fund', 'Providing diagnostic tools and life-saving machines to health facilities.', '/assets/images/charity/equipment-upgrade.png', 120000.00, 10000.00, 'active', '2025-11-08 23:15:57'),
(6, 'Nutrition for Hope Program', 'Africa Humanitarian Action (AHA)', 'Delivering essential food and supplements to children and the elderly facing malnutrition.', '/assets/images/charity/nutrition.png', 45000.00, 18000.00, 'active', '2025-11-08 23:15:57');

-- --------------------------------------------------------

--
-- Table structure for table `charity_donations`
--

CREATE TABLE `charity_donations` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `charity_id` int NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `holdlock`
--

CREATE TABLE `holdlock` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `plan_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `roi_percent` decimal(5,2) DEFAULT '0.00',
  `duration_days` int NOT NULL,
  `penalty_percent` decimal(5,2) DEFAULT '1.50',
  `status` enum('locked','unlock_pending','matured','unlocked_early','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'locked',
  `maturity_date` date DEFAULT NULL,
  `roi_earned` decimal(12,2) DEFAULT '0.00',
  `penalty_applied` decimal(12,2) DEFAULT '0.00',
  `payout_option` enum('maturity','early') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'maturity',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `holdlock_plans`
--

CREATE TABLE `holdlock_plans` (
  `id` int NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `min_amount` decimal(15,2) NOT NULL,
  `max_amount` decimal(15,2) DEFAULT NULL,
  `lock_period_text` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_days` int DEFAULT NULL,
  `roi_range` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `risk` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payout` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `holdlock_plans`
--

INSERT INTO `holdlock_plans` (`id`, `name`, `purpose`, `min_amount`, `max_amount`, `lock_period_text`, `duration_days`, `roi_range`, `risk`, `payout`, `summary`, `icon`, `color`, `created_at`) VALUES
(1, 'Flexi Health Lock Plan', 'A short-term plan designed for clients who want safe, quick returns while keeping their capital secure.', 10000.00, 99999999.99, '180 days', 180, '2.00%', 'Very Low', 'Full payout at maturity', 'Ideal for clients seeking liquidity and short-term growth. Funds are safely held and paid out at the end of the term. This option delivers competitive returns without locking you in for the long haul. It’s perfect for individuals or organizations that want reliable growth while maintaining access to their capital.', 'mdi:clock-outline', 'Green', '2025-11-22 13:42:57'),
(2, 'Standard Lock & Grow Plan', 'A one-year plan offering predictable and consistent growth with minimal risk.', 20000.00, 300000.00, '12 months', 365, '7–9%', 'Low', 'Annual or full payout at maturity', 'A balanced one-year growth plan for investors who prefer stability and moderate fixed returns. Designed to protect your capital while steadily increasing its value over time. It offers predictable earnings without the volatility of short-term market shifts. Ideal for individuals and organizations looking to grow funds responsibly, with reliable year-end payouts.', 'mdi:calendar-check', 'Green', '2025-11-22 13:42:57'),
(3, 'Executive LockPlus Plan', 'A two-year plan for individuals and organizations seeking better returns from moderate-term investments.', 50000.00, 500000.00, '24 months', 730, '14–18%', 'Moderate', 'Annual or full payout at maturity', 'Perfect for mid- to high-level investors seeking strong, consistent growth over two years with minimal risk exposure. This plan focuses on building wealth steadily through secure, well-managed allocations. It delivers higher returns than shorter-term options without sacrificing safety or predictability. Ideal for individuals and institutions looking to maximize gains over a longer horizon while keeping their capital protected.', 'mdi:briefcase-check', 'Blue', '2025-11-22 13:42:57'),
(4, 'Prestige Capital Hold Plan', 'A premium plan for investors with large capital who seek long-term, high-yield returns.', 250000.00, NULL, '36 months', 1095, '25–30%', 'Moderate', 'Annual, bi-annual, or full payout at maturity', 'A long-term, asset-secure investment option that rewards patience with premium returns and stable growth. Designed for investors who value strong protection of capital while building significant wealth over time. This plan leverages extended compounding benefits to deliver higher, more reliable earnings. Ideal for those who prioritize security yet still aim for ambitious growth targets.', 'mdi:crown-outline', 'Orange', '2025-11-22 13:42:57'),
(5, 'Lifetime Reserve Lock Plan', 'A lifelong plan designed for wealth preservation and consistent annual income.', 1000000.00, NULL, 'Lifetime (Perpetual)', 36500, '6–8% annual', 'Low', 'Annual or quarterly lifetime payout', 'An exclusive wealth preservation plan that guarantees lifetime income, ideal for estates, families, or organizations focused on long-term legacy. Built to protect high-value assets while delivering consistent financial strength across generations. This option ensures stable earnings, even in changing market conditions, while safeguarding the core value of your capital.', 'mdi:infinity', 'Green', '2025-11-22 13:42:57');

-- --------------------------------------------------------

--
-- Table structure for table `infrastructure`
--

CREATE TABLE `infrastructure` (
  `id` int NOT NULL,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `goal_amount` decimal(12,2) DEFAULT '0.00',
  `raised_amount` decimal(12,2) DEFAULT '0.00',
  `roi_percent` decimal(5,2) DEFAULT '10.00',
  `status` enum('open','funded','complete') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

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
  `status` enum('active','matured','unlocked') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `infrastructure_plans`
--

CREATE TABLE `infrastructure_plans` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `min_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `duration_days` int NOT NULL DEFAULT '0',
  `roi_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `payout_option` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `risk_level` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `color` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Green',
  `repayment_mode` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `infrastructure_plans`
--

INSERT INTO `infrastructure_plans` (`id`, `name`, `purpose`, `min_amount`, `duration_days`, `roi_percent`, `payout_option`, `risk_level`, `summary`, `color`, `repayment_mode`, `icon`, `created_at`, `updated_at`) VALUES
(1, 'Basic Diagnostic Plan', 'To support community and mid-level hospitals with portable ultrasound diagnostic systems for early disease detection.', 10000.00, 365, 9.00, 'quarterly', 'Very Low', 'A foundational infrastructure plan supporting community and mid-level hospitals with portable ultrasound systems for early detection. Investments directly equip clinics with diagnostic tools that generate continuous service revenue. Capital is repaid from ultrasound scan fees, ensuring predictable quarterly returns. Ideal for investors seeking stable, low-risk healthcare income anchored in essential medical services.', 'Green', 'Quarterly payments over 12 months', 'mdi:office-building-outline', '2025-11-22 18:38:18', '2025-11-23 15:22:06'),
(2, 'Imaging Growth Plan', 'To deploy digital X-ray imaging systems for regional hospitals and diagnostic centers.', 20000.00, 540, 13.50, 'quarterly', 'Low', 'This plan finances the installation of digital X-ray imaging systems in hospitals and diagnostic centers. Patient scan revenue powers structured repayments, creating reliable cash flow for investors. Designed to expand diagnostic access in underserved regions while yielding 12–15% total profit within 18 months. A strong option for low-risk investors supporting scalable healthcare growth with guaranteed demand.', 'Green', 'Quarterly or semi-annual', 'mdi:bank-outline', '2025-11-22 18:38:18', '2025-11-23 15:22:06'),
(3, 'Advanced Radiology Plan', 'To enable hospitals to install CT scanners and expand access to high-precision imaging services.', 50000.00, 730, 17.50, 'monthly', 'Moderate', 'A strategic infrastructure investment enabling hospitals to install CT scanners for advanced radiology diagnostics. HealthRunCare manages procurement, installation, and hospital repayment contracts to ensure secure returns. Investors receive up to 20% ROI over two years through dependable monthly or quarterly payments. A moderate-risk plan backed by strong medical service demand and critical imaging needs.', 'Blue', 'Monthly or quarterly payments', 'mdi:chart-bar', '2025-11-22 18:38:18', '2025-11-23 15:22:06'),
(4, 'Dialysis Infrastructure Plan', 'To expand kidney care capacity through the installation of dialysis centers and water treatment systems.', 100000.00, 900, 20.00, 'quarterly', 'Moderate', 'This plan expands kidney care capacity by funding dialysis center installations and water treatment systems. Hospitals repay using revenue generated from consistent patient treatment cycles. Investors earn 18–22% profit over 30 months, supported by strong market demand and rising chronic kidney disease care needs. A sustainable moderate-growth option driven by essential life-saving services.', 'Blue', 'Quarterly payments with inflation-adjusted escalation clause', 'mdi:water', '2025-11-22 18:38:18', '2025-11-23 15:22:06'),
(5, 'Complete Operating Room Equipment Plan', 'To establish modern operating theatres equipped for advanced surgical operations in partner hospitals.', 150000.00, 1095, 22.50, 'monthly', 'Moderate', 'A high-value investment that finances complete operating room equipment for advanced surgical procedures. Hospitals repay using surgical revenue streams while generating up to 25% profit over three years. The plan includes optional early payment features for increased liquidity and control. Ideal for investors seeking consistent returns from an essential, high-demand healthcare service sector.', 'Blue', 'Monthly or quarterly with partial early payment options', 'mdi:needle', '2025-11-22 18:38:18', '2025-11-23 15:22:06'),
(6, 'Hospital Diagnostic Wing Installation Plan', 'To construct and equip an entire hospital diagnostic and imaging wing, combining MRI, CT, X-ray, ultrasound, and lab systems.', 500000.00, 1095, 29.00, 'quarterly', 'Moderate-Low', 'An elite infrastructure plan for institutional investors funding full diagnostic wings, including MRI, CT, X-ray, ultrasound, and lab systems. Revenue from multi-department diagnostic services ensures robust repayment contracts. Investors earn up to 30% returns over three years, backed by large-scale, long-term facility agreements. A premium opportunity for substantial impact, high demand coverage, and strong capital growth.', 'Green', 'Quarterly or bi-annual', 'mdi:hospital-building', '2025-11-22 18:38:18', '2025-11-23 15:22:06');

-- --------------------------------------------------------

--
-- Table structure for table `investments`
--

CREATE TABLE `investments` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `plan_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `roi_percent` decimal(5,2) DEFAULT '15.00',
  `duration_days` int DEFAULT '30',
  `status` enum('active','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `maturity_date` date DEFAULT NULL,
  `roi_earned` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `investment_plans`
--

CREATE TABLE `investment_plans` (
  `id` int NOT NULL,
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `roi_percent` decimal(5,2) NOT NULL,
  `duration_days` int NOT NULL,
  `payout_option` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_amount` decimal(15,2) NOT NULL,
  `max_amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `risk` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','hidden') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `income` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `investment_plans`
--

INSERT INTO `investment_plans` (`id`, `title`, `roi_percent`, `duration_days`, `payout_option`, `min_amount`, `max_amount`, `created_at`, `description`, `details`, `risk`, `status`, `income`, `summary`, `icon`, `color`) VALUES
(1, 'Healthy Future Bond Plan', 11.00, 540, 'Quarterly or at maturity', 500.00, 100000.00, '2025-11-22 12:22:52', 'Build Community Diagnostic Centers', 'Supporting local health screenings and medical supplies for underserved communities.', 'Low', 'active', 'Fees from diagnostic tests and partnerships with hospitals & insurance providers', 'Your money helps build diagnostic centers that earn from medical tests. As these centers generate consistent service income, you earn up to 12% in 18 months — safely and with social impact.', 'mdi:factory', 'Green'),
(2, 'Wellness Growth Real Estate Plan', 16.50, 730, 'Bi-annual or lump sum at maturity', 5000.00, 250000.00, '2025-11-22 12:22:52', 'Build and Lease Wellness & Rehabilitation Facilities', 'Your investment funds the construction of modern wellness centers leased to physiotherapy clinics, fitness brands, and recovery operators.', 'Moderate', 'active', 'Rental income from wellness centers and long-term lease agreements', 'You help build wellness facilities that lease to health operators. Rental payments provide steady income that\'s shared with you as up to 18% return in 2 years.', 'mdi:home-building', 'Blue'),
(3, 'Health Innovation Venture Fund', 30.00, 1095, 'At maturity (end of term)', 10000.00, 500000.00, '2025-11-22 12:22:52', 'Support High-Growth Health-Tech Startups', 'Your capital helps scale innovative startups working on medical devices, biotech research, and digital health technologies.', 'High', 'active', 'Equity profit from startup growth, technology licensing, and company buyouts', 'You back new health-tech companies. When they grow or get acquired, you share in their success — earning up to 35% within 3 years.', 'mdi:lightning-bolt', 'Orange'),
(4, 'Community Health Microfinance Plan', 9.00, 360, 'At maturity (end of 12 months)', 300.00, 20000.00, '2025-11-22 12:22:52', 'Empower Small Health Businesses', 'This plan provides microloans to rural pharmacies, small clinics, and health workers who repay with fair interest.', 'Low', 'active', 'Loan interest payments from local health entrepreneurs', 'Your investment gives small loans to trusted healthcare providers. They repay with interest, and you earn up to 10% in just one year — while supporting community care.', 'mdi:hand-extend', 'Green'),
(5, 'Green Hospital Infrastructure Plan', 15.00, 730, 'Annual or at maturity', 2000.00, 200000.00, '2025-11-22 12:22:52', 'Finance Eco-Friendly Hospital Upgrades', 'Your investment enables hospitals to install solar systems, energy-saving equipment, and water recycling units.', 'Moderate', 'active', 'Revenue-sharing from hospitals\' reduced energy costs and green subsidies', 'Hospitals save thousands on electricity and maintenance after green upgrades. Part of those savings is paid back to investors — giving you up to 16% return in 2 years.', 'mdi:leaf', 'Blue'),
(6, 'Healthy Food Systems Plan', 13.50, 540, 'Quarterly or at maturity', 1000.00, 50000.00, '2025-11-22 12:22:52', 'Strengthen Nutrition and Food Security', 'This plan funds farm-to-health programs and healthy meal suppliers for hospitals, schools, and wellness institutions.', 'Moderate', 'active', 'Profits from produce sales, supply contracts, and wholesale distribution partnerships', 'Your money supports healthy food producers who sell to hospitals and schools. As they make profits, you earn up to 15% in 18 months.', 'mdi:food', 'Blue'),
(7, 'Digital Health Access Plan', 20.00, 730, 'Annual or at maturity', 2000.00, 100000.00, '2025-11-22 12:22:52', 'Expand Online Health Platforms & Telemedicine', 'Invest in digital platforms offering remote doctor consultations, e-prescriptions, and mobile diagnostics.', 'Moderate to High', 'active', 'Subscription fees, teleconsultation charges, data partnerships, and health service commissions', 'You invest in the future of digital healthcare. As more users join and pay for services online, you earn up to 22% return in 2 years — while helping expand access to doctors worldwide.', 'mdi:phone', 'Orange'),
(8, 'testing plan', 25.50, 365, 'maturity', 1.00, 9999999.00, '2025-11-22 13:24:06', 'Investment plan description.', 'Detailed plan features.', 'low', 'active', 'General investment returns.', 'Summary of the plan.', 'mdi:chart-line', 'Blue');

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int NOT NULL,
  `user_type` enum('user','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `ip` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

CREATE TABLE `maintenance` (
  `id` int NOT NULL,
  `plan_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `plan_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `roi_earned` decimal(12,2) DEFAULT '0.00',
  `frequency` enum('monthly','once') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'monthly',
  `status` enum('active','matured','unlocked','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `next_payment_date` date DEFAULT NULL,
  `maturity_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_plans`
--

CREATE TABLE `maintenance_plans` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `min_amount` decimal(15,2) NOT NULL,
  `max_amount` decimal(15,2) DEFAULT NULL,
  `duration_days` int DEFAULT NULL,
  `roi_percent` decimal(5,2) NOT NULL,
  `risk` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payout` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_plans`
--

INSERT INTO `maintenance_plans` (`id`, `name`, `purpose`, `min_amount`, `max_amount`, `duration_days`, `roi_percent`, `risk`, `payout`, `summary`, `color`, `created_at`) VALUES
(1, 'Maintenance Support Starter Plan', 'Entry plan for basic healthcare maintenance support.', 10000.00, 50000.00, 270, 5.50, 'Very Low', 'Full payout at maturity', 'A beginner-friendly investment that supports routine maintenance for essential healthcare devices and systems. Returns are generated from hospital service contracts that rely on continuous operational uptime. Ideal for low-risk investors seeking modest growth backed by predictable maintenance income. A secure way to participate in healthcare sustainability with guaranteed service demand.', 'Green', '2025-11-22 20:19:10'),
(2, 'Standard Equipment Care Plan', 'One-year maintenance program for hospital devices.', 25000.00, 300000.00, 360, 9.00, 'Low', 'Annual or full payout at maturity', 'A one-year maintenance investment that supports the longevity and efficiency of hospital diagnostic equipment. Healthcare facilities repay using funds allocated for technical servicing and calibration cycles, ensuring steady investor returns. Designed for low-risk growth tied directly to essential upkeep needs. A stable annual plan for investors supporting reliable healthcare operations.', 'Green', '2025-11-22 20:19:10'),
(3, 'Infrastructure Development Plan', 'Mid-term investment into major repair infrastructure.', 50000.00, 500000.00, 720, 16.50, 'Moderate', 'Annual or full payout at maturity', 'A mid-term investment designed to fund major repair and modernization projects across hospital infrastructure. Returns are backed by facility upgrade contracts tied to improved service capacity. Offers moderate growth for investors seeking impactful contributions to facility performance and safety. A strategic plan supporting high-value maintenance that enhances patient care delivery.', 'Blue', '2025-11-22 20:19:10'),
(4, 'Premium Equipment Sustainability Plan', 'Top-tier maintenance pipeline for premium systems.', 250000.00, NULL, 1080, 22.00, 'Moderate', 'Multiple payout options', 'A premium maintenance plan funding long-term sustainability of high-end diagnostic and treatment equipment. Hospitals commit to structured repayment cycles based on servicing and uptime requirements for advanced systems. Investors benefit from increased returns tied to equipment criticality and high operational demand. A strong option for those seeking moderate risk, high impact, and multi-payout flexibility.', 'Blue', '2025-11-22 20:19:10'),
(5, 'Lifetime Equipment Trust Plan', 'Perpetual sustainability income for healthcare assets.', 1000000.00, NULL, NULL, 7.00, 'Low', 'Lifetime payouts', 'An elite perpetual trust that generates lifetime maintenance income from healthcare asset servicing. Designed to safeguard equipment performance across generations, with continuous payouts funded by recurring maintenance contracts. Ideal for long-term investors, estates, or institutions seeking ongoing passive income. A legacy-centric plan that secures sustainable healthcare operations and enduring financial returns.', 'Green', '2025-11-22 20:19:10');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `otp` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `cash_mailing_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `wallet_deposit_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` enum('secure_exchange','cash_mailing','wire_transfer','local_bank','wallet_address','wallet','system') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `details` json DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','completed','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trustfund`
--

CREATE TABLE `trustfund` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `plan_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `roi_percent` decimal(5,2) DEFAULT '0.00',
  `duration_days` int DEFAULT '0',
  `penalty_percent` decimal(5,2) DEFAULT '1.50',
  `purpose` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maturity_date` date DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `roi_earned` decimal(12,2) DEFAULT '0.00',
  `payout_option` enum('annual','maturity') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'maturity',
  `status` enum('active','matured','unlock_pending','unlocked_early','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trustfund_plans`
--

CREATE TABLE `trustfund_plans` (
  `id` int NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `income_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_amount` decimal(15,2) NOT NULL,
  `max_amount` decimal(15,2) DEFAULT NULL,
  `duration_days` int NOT NULL,
  `roi_percent` decimal(10,2) NOT NULL,
  `risk` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payout_option` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trustfund_plans`
--

INSERT INTO `trustfund_plans` (`id`, `name`, `purpose`, `income_source`, `min_amount`, `max_amount`, `duration_days`, `roi_percent`, `risk`, `payout_option`, `summary`, `icon`, `color`, `created_at`) VALUES
(1, 'Child Education Growth Plan', 'Build a secure education fund with guaranteed growth.', 'Education bonds, community development funds, and micro-infrastructure projects', 500.00, 50000.00, 1095, 25.00, 'Low', 'Annual or at maturity', 'A secure education-focused savings plan that grows steadily over time. Designed to protect capital while building reliable funds for tuition, fees, and academic expenses. This trust provides guaranteed growth and helps parents and guardians plan early with confidence. Ideal for preparing long-term education goals without financial stress.', 'mdi:school-outline', 'Green', '2025-11-22 14:15:26'),
(2, 'Legacy Wealth Trust Plan', 'Generate long-term generational wealth.', 'Real estate development, healthcare partnerships, renewable energy assets, and long-term equity ventures', 500000.00, NULL, 1825, 55.00, 'Moderate', 'Annual or at maturity', 'A premium wealth preservation and growth trust designed for multigenerational impact. Built to protect and grow assets over many years, ensuring enduring financial security. Offers stable compounding returns ideal for inheritance planning. Perfect for families and estates seeking long-term financial empowerment with sustained legacy outcomes.', 'mdi:family-tree', 'Blue', '2025-11-22 14:15:26'),
(3, 'Business Succession Trust Plan', 'Secure business growth or transition.', 'SME expansion financing, business partnership equity, and scalable enterprise loan models', 5000.00, 500000.00, 1460, 48.00, 'Moderate to High', 'Annual or at maturity', 'A strategic business-focused trust for organizations planning expansion or succession. Provides consistent returns while supporting growth, transition, or ownership transfer. Designed to secure capital while maintaining profitability. Ideal for entrepreneurs looking to protect business assets while building long-term enterprise value.', 'mdi:briefcase-outline', 'Orange', '2025-11-22 14:15:26'),
(4, 'Medical Protection Trust Plan', 'Create a secure medical reserve with capital growth.', 'Health insurance reserve pools, medical leasing contracts, and healthcare infrastructure revenue', 300.00, 25000.00, 1095, 18.00, 'Low', 'Quarterly or at maturity', 'A specialized medical savings and protection trust that secures long-term emergency funds. Designed to grow your capital safely while ensuring healthcare preparedness. Offers accessible returns to support medical needs, emergencies, and family protection. A stable plan for anyone seeking security and wellness with financial peace of mind.', 'mdi:heart-pulse', 'Green', '2025-11-22 14:15:26'),
(5, 'Future Builders Business Plan', 'Support startups and young entrepreneurs.', 'Startup financing, tech incubation funds, and sustainability-driven venture investments', 1000.00, 100000.00, 1460, 38.00, 'Moderate', 'Annual or at maturity', 'A forward-focused investment trust for startups, young entrepreneurs, and innovation-driven ventures. Designed to fuel ideas while generating profitable returns for supporters. Balances growth, social impact, and sustainable business funding. Ideal for investors seeking meaningful financial growth that empowers the next generation of innovators.', 'mdi:rocket-outline', 'Blue', '2025-11-22 14:15:26'),
(6, 'Guardian Trust Income Plan', 'Steady annual income for beneficiaries.', 'Dividend portfolios, healthcare lease revenues, and infrastructure-backed income streams', 10000.00, 200000.00, 1825, 35.00, 'Low to Moderate', 'Annual income distribution', 'A dependable income trust providing guaranteed yearly payouts for beneficiaries. Protects principal while generating stable distribution to dependents, families, or long-term support needs. Ideal for individuals who want to secure financial care for others with predictable income. Built for reliability, safety, and consistent returns.', 'mdi:shield-check-outline', 'Green', '2025-11-22 14:15:26'),
(7, 'Perpetual Legacy Trust Plan', 'Lifetime income with preserved principal.', 'Real estate income, renewable energy royalties, hospital infrastructure revenue, and high-dividend equity portfolios', 1000000.00, NULL, 9999, 11.00, 'Low', 'Annual or quarterly for life', 'An elite wealth preservation trust that guarantees lifetime income while permanently protecting principal value. Designed for estates, family offices, and institutional planning. Generates continuous payouts across generations with low-risk protection. A premium legacy solution for enduring financial continuity, security, and long-term prosperity.', 'mdi:infinity', 'Orange', '2025-11-22 14:15:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `status` enum('active','disabled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '/assets/images/avatar/default.png',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `full_name`, `email`, `password`, `role`, `status`, `profile_picture`, `created_at`) VALUES
(1, 'mr wayne', 'mr wayne', 'aleruchi0987@gmail.com', '$2y$10$2QdOIhhezbZknHzwq5tKqeBsLE78JieSHe5DGTalWW.xTN37gxGie', 'user', 'active', '/assets/images/avatar/default.png', '2025-10-27 21:03:23');

-- --------------------------------------------------------

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
-- Dumping data for table `user_impacts`
--

INSERT INTO `user_impacts` (`id`, `user_id`, `total_contributions`, `people_helped`, `impact_score`, `communities_helped`, `packages_funded`, `updated_at`) VALUES
(1, 1, 0.00, 0, 0.00, 0, 0, '2025-11-24 07:01:19');

-- --------------------------------------------------------

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
  `cash_mailing_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `wallet_deposit_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `balance`, `total_deposited`, `total_withdrawn`, `total_donations`, `total_investments`, `holdlock_savings`, `total_earnings`, `pending_withdrawals`, `created_at`, `cash_mailing_address`, `wallet_deposit_address`) VALUES
(1, 1, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2025-11-24 07:01:19', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bank_details`
--
ALTER TABLE `bank_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bank_user` (`user_id`);

--
-- Indexes for table `charities`
--
ALTER TABLE `charities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_charity_status` (`status`);

--
-- Indexes for table `charity_donations`
--
ALTER TABLE `charity_donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_donation_user` (`user_id`),
  ADD KEY `idx_donation_charity` (`charity_id`);

--
-- Indexes for table `holdlock`
--
ALTER TABLE `holdlock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hold_user` (`user_id`),
  ADD KEY `idx_hold_maturity` (`maturity_date`);

--
-- Indexes for table `holdlock_plans`
--
ALTER TABLE `holdlock_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `infrastructure`
--
ALTER TABLE `infrastructure`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_infra_status` (`status`);

--
-- Indexes for table `infrastructure_contributions`
--
ALTER TABLE `infrastructure_contributions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_infra_user` (`user_id`),
  ADD KEY `idx_infra_project` (`project_id`);

--
-- Indexes for table `infrastructure_plans`
--
ALTER TABLE `infrastructure_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `investments`
--
ALTER TABLE `investments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invest_user` (`user_id`),
  ADD KEY `idx_invest_maturity` (`maturity_date`);

--
-- Indexes for table `investment_plans`
--
ALTER TABLE `investment_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_maintenance_user` (`user_id`);

--
-- Indexes for table `maintenance_plans`
--
ALTER TABLE `maintenance_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reset_user` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `idx_user_transaction` (`user_id`),
  ADD KEY `idx_txn_method` (`method`);

--
-- Indexes for table `trustfund`
--
ALTER TABLE `trustfund`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_trust_user` (`user_id`),
  ADD KEY `idx_trust_maturity` (`maturity_date`);

--
-- Indexes for table `trustfund_plans`
--
ALTER TABLE `trustfund_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_impacts`
--
ALTER TABLE `user_impacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_wallet` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bank_details`
--
ALTER TABLE `bank_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `charities`
--
ALTER TABLE `charities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `charity_donations`
--
ALTER TABLE `charity_donations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holdlock`
--
ALTER TABLE `holdlock`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holdlock_plans`
--
ALTER TABLE `holdlock_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `infrastructure`
--
ALTER TABLE `infrastructure`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `infrastructure_contributions`
--
ALTER TABLE `infrastructure_contributions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `infrastructure_plans`
--
ALTER TABLE `infrastructure_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `investments`
--
ALTER TABLE `investments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `investment_plans`
--
ALTER TABLE `investment_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_plans`
--
ALTER TABLE `maintenance_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trustfund`
--
ALTER TABLE `trustfund`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trustfund_plans`
--
ALTER TABLE `trustfund_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_impacts`
--
ALTER TABLE `user_impacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bank_details`
--
ALTER TABLE `bank_details`
  ADD CONSTRAINT `bank_details_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `charity_donations`
--
ALTER TABLE `charity_donations`
  ADD CONSTRAINT `donation_fk_charity` FOREIGN KEY (`charity_id`) REFERENCES `charities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donation_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `holdlock`
--
ALTER TABLE `holdlock`
  ADD CONSTRAINT `holdlock_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `infrastructure_contributions`
--
ALTER TABLE `infrastructure_contributions`
  ADD CONSTRAINT `infra_contrib_fk_project` FOREIGN KEY (`project_id`) REFERENCES `infrastructure` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `infra_contrib_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `investments`
--
ALTER TABLE `investments`
  ADD CONSTRAINT `investments_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD CONSTRAINT `maintenance_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `resets_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trustfund`
--
ALTER TABLE `trustfund`
  ADD CONSTRAINT `trustfund_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_impacts`
--
ALTER TABLE `user_impacts`
  ADD CONSTRAINT `impact_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

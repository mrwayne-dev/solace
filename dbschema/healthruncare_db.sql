-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 22, 2025 at 12:32 PM
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
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `full_name`, `email`, `password`, `role`, `status`, `profile_picture`, `last_login`, `created_at`) VALUES
(1, 'mr wayne', 'mr wayne', 'aleruchi0987@gmail.com', '$2y$10$tssRL/rKOgDPKtFCs5w6MetlAM3fF2FBTMO0S4mkRD6iwjdkZHLKC', 'manager', 'active', '/assets/images/avatar/admin_default.png', '2025-11-22 00:24:17', '2025-11-12 15:22:41');

-- --------------------------------------------------------

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

-- --------------------------------------------------------

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
-- Dumping data for table `charities`
--

INSERT INTO `charities` (`id`, `name`, `organization`, `description`, `image`, `goal_amount`, `raised_amount`, `status`, `created_at`) VALUES
(1, 'Maternal Care Initiative', 'Africa Public Health Foundation (APHF)', 'Expanding access to prenatal and postnatal care for mothers in underserved African regions.', '/assets/images/charity/maternal-care.jpg', 80000.00, 60050.00, 'active', '2025-11-08 23:15:57'),
(2, 'Clean Water for Health Program', 'The Global Fund', 'Installing boreholes and filtration systems in rural health centers to reduce waterborne diseases.', '/assets/images/charity/clean-water.jpg', 60000.00, 30000.00, 'active', '2025-11-08 23:15:57'),
(3, 'Rural Health Outreach Network', 'PharmAccess Foundation', 'Providing mobile clinics and telemedicine solutions to remote villages.', '/assets/images/charity/rural-outreach.jpg', 95000.00, 80750.00, 'active', '2025-11-08 23:15:57'),
(4, 'Child Immunization Drive', 'Against Malaria Foundation (AMF)', 'Supporting mass vaccination programs to prevent common infectious diseases among children.', '/assets/images/charity/immunization.jpg', 50000.00, 15000.00, 'active', '2025-11-08 23:15:57'),
(5, 'Hospital Equipment Upgrade Fund', 'Transform Health Fund', 'Providing diagnostic tools and life-saving machines to health facilities.', '/assets/images/charity/equipment-upgrade.jpg', 120000.00, 10000.00, 'active', '2025-11-08 23:15:57'),
(6, 'Nutrition for Hope Program', 'Africa Humanitarian Action (AHA)', 'Delivering essential food and supplements to children and the elderly facing malnutrition.', '/assets/images/charity/nutrition.jpg', 45000.00, 18000.00, 'active', '2025-11-08 23:15:57');

-- --------------------------------------------------------

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
-- Dumping data for table `charity_donations`
--

INSERT INTO `charity_donations` (`id`, `user_id`, `charity_id`, `amount`, `reference`, `created_at`) VALUES
(1, 1, 1, 20.00, 'HRC-DON-69132514CBF8C-7265', '2025-11-11 10:59:16'),
(2, 1, 1, 20.00, 'HRC-DON-69150EDB2C5F2-9564', '2025-11-12 21:48:59'),
(3, 1, 1, 10.00, 'HRC-DON-691B1183523E8-1453', '2025-11-17 11:13:55');

-- --------------------------------------------------------

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

-- --------------------------------------------------------

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
  `status` enum('active','matured','unlocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

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
-- Dumping data for table `investments`
--

INSERT INTO `investments` (`id`, `user_id`, `plan_name`, `amount`, `roi_percent`, `duration_days`, `status`, `maturity_date`, `roi_earned`, `created_at`) VALUES
(1, 1, 'Healthy Future Bond Plan', 500.00, 11.00, 540, 'active', '2027-05-03', 0.00, '2025-11-09 08:38:58'),
(2, 1, 'Healthy Future Bond Plan', 500.00, 11.00, 540, 'active', '2027-05-16', 0.00, '2025-11-22 11:06:56');

-- --------------------------------------------------------

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
  `income` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `investment_plans`
--

INSERT INTO `investment_plans` (`id`, `title`, `roi_percent`, `duration_days`, `payout_option`, `min_amount`, `max_amount`, `created_at`, `description`, `details`, `risk`, `income`, `summary`, `icon`, `color`) VALUES
(1, 'Healthy Future Bond Plan', 11.00, 540, 'Quarterly or at maturity', 500.00, 100000.00, '2025-11-22 12:22:52', 'Build Community Diagnostic Centers', 'Supporting local health screenings and medical supplies for underserved communities.', 'Low', 'Fees from diagnostic tests and partnerships with hospitals & insurance providers', 'Your money helps build diagnostic centers that earn from medical tests. As these centers generate consistent service income, you earn up to 12% in 18 months — safely and with social impact.', 'mdi:factory', 'Green'),
(2, 'Wellness Growth Real Estate Plan', 16.50, 730, 'Bi-annual or lump sum at maturity', 5000.00, 250000.00, '2025-11-22 12:22:52', 'Build and Lease Wellness & Rehabilitation Facilities', 'Your investment funds the construction of modern wellness centers leased to physiotherapy clinics, fitness brands, and recovery operators.', 'Moderate', 'Rental income from wellness centers and long-term lease agreements', 'You help build wellness facilities that lease to health operators. Rental payments provide steady income that\'s shared with you as up to 18% return in 2 years.', 'mdi:home-building', 'Blue'),
(3, 'Health Innovation Venture Fund', 30.00, 1095, 'At maturity (end of term)', 10000.00, 500000.00, '2025-11-22 12:22:52', 'Support High-Growth Health-Tech Startups', 'Your capital helps scale innovative startups working on medical devices, biotech research, and digital health technologies.', 'High', 'Equity profit from startup growth, technology licensing, and company buyouts', 'You back new health-tech companies. When they grow or get acquired, you share in their success — earning up to 35% within 3 years.', 'mdi:lightning-bolt', 'Orange'),
(4, 'Community Health Microfinance Plan', 9.00, 360, 'At maturity (end of 12 months)', 300.00, 20000.00, '2025-11-22 12:22:52', 'Empower Small Health Businesses', 'This plan provides microloans to rural pharmacies, small clinics, and health workers who repay with fair interest.', 'Low', 'Loan interest payments from local health entrepreneurs', 'Your investment gives small loans to trusted healthcare providers. They repay with interest, and you earn up to 10% in just one year — while supporting community care.', 'mdi:hand-extend', 'Green'),
(5, 'Green Hospital Infrastructure Plan', 15.00, 730, 'Annual or at maturity', 2000.00, 200000.00, '2025-11-22 12:22:52', 'Finance Eco-Friendly Hospital Upgrades', 'Your investment enables hospitals to install solar systems, energy-saving equipment, and water recycling units.', 'Moderate', 'Revenue-sharing from hospitals\' reduced energy costs and green subsidies', 'Hospitals save thousands on electricity and maintenance after green upgrades. Part of those savings is paid back to investors — giving you up to 16% return in 2 years.', 'mdi:leaf', 'Blue'),
(6, 'Healthy Food Systems Plan', 13.50, 540, 'Quarterly or at maturity', 1000.00, 50000.00, '2025-11-22 12:22:52', 'Strengthen Nutrition and Food Security', 'This plan funds farm-to-health programs and healthy meal suppliers for hospitals, schools, and wellness institutions.', 'Moderate', 'Profits from produce sales, supply contracts, and wholesale distribution partnerships', 'Your money supports healthy food producers who sell to hospitals and schools. As they make profits, you earn up to 15% in 18 months.', 'mdi:food', 'Blue'),
(7, 'Digital Health Access Plan', 20.00, 730, 'Annual or at maturity', 2000.00, 100000.00, '2025-11-22 12:22:52', 'Expand Online Health Platforms & Telemedicine', 'Invest in digital platforms offering remote doctor consultations, e-prescriptions, and mobile diagnostics.', 'Moderate to High', 'Subscription fees, teleconsultation charges, data partnerships, and health service commissions', 'You invest in the future of digital healthcare. As more users join and pay for services online, you earn up to 22% return in 2 years — while helping expand access to doctors worldwide.', 'mdi:phone', 'Orange');

-- --------------------------------------------------------

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
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`id`, `user_type`, `user_id`, `ip`, `browser`, `location`, `created_at`) VALUES
(1, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-12 20:46:31'),
(2, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-12 20:51:15'),
(3, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-12 20:51:42'),
(4, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-12 20:53:52'),
(5, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-12 20:54:09'),
(6, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-12 20:54:58'),
(7, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-12 20:58:53'),
(8, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-12 20:59:05'),
(9, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-12 21:07:16'),
(10, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-12 21:07:32'),
(11, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-12 21:12:05'),
(12, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-12 21:20:57'),
(13, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-12 21:26:16'),
(14, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-12 23:47:44'),
(15, 'admin', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-13 11:19:13'),
(16, 'admin', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-13 11:20:17'),
(17, 'user', 1, '127.0.0.1', 'Firefox 144.0 on Windows', 'Localhost / Internal Network', '2025-11-13 11:21:49'),
(18, 'user', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-15 01:17:56'),
(19, 'admin', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-15 01:54:27'),
(20, 'admin', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-16 10:58:34'),
(21, 'admin', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-16 23:51:26'),
(22, 'user', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-16 23:56:06'),
(23, 'admin', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-17 03:05:48'),
(24, 'user', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-17 12:33:13'),
(25, 'user', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-17 12:59:43'),
(26, 'admin', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-17 13:22:21'),
(27, 'admin', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-20 11:01:26'),
(28, 'admin', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-20 11:06:05'),
(29, 'admin', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-20 13:33:59'),
(30, 'user', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-20 22:14:51'),
(31, 'admin', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-21 00:21:06'),
(32, 'admin', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-21 11:34:31'),
(33, 'user', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-21 11:45:50'),
(34, 'user', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-21 11:46:03'),
(35, 'admin', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-21 11:50:59'),
(36, 'user', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-22 00:17:25'),
(37, 'user', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-22 00:19:08'),
(38, 'admin', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-22 00:19:56'),
(39, 'admin', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-22 00:24:22'),
(40, 'user', 1, '127.0.0.1', 'Firefox 145.0 on Windows', 'Localhost / Internal Network', '2025-11-22 03:15:30');

-- --------------------------------------------------------

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

-- --------------------------------------------------------

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

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `cash_mailing_address` text COLLATE utf8mb4_unicode_ci,
  `wallet_deposit_address` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `cash_mailing_address`, `wallet_deposit_address`) VALUES
(1, 'new address', NULL);

-- --------------------------------------------------------

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
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `type`, `method`, `details`, `amount`, `reference`, `status`, `created_at`) VALUES
(1, 1, 'deposit', 'secure_exchange', '{\"method\": \"secure_exchange\", \"provider\": \"nowpayments\", \"initiated_at\": \"2025-11-09 00:09:34\", \"provider_response\": {\"id\": \"4804426065\", \"source\": null, \"order_id\": \"HRC-DEP-690FDBBE365D5-6351\", \"token_id\": \"5490326139\", \"cancel_url\": \"https://healthruncare.com/pages/user/wallet.php?deposit=cancel&ref=HRC-DEP-690FDBBE365D5-6351\", \"created_at\": \"2025-11-09T00:09:39.512Z\", \"updated_at\": \"2025-11-09T00:09:39.512Z\", \"invoice_url\": \"https://nowpayments.io/payment/?iid=4804426065\", \"success_url\": \"https://healthruncare.com/pages/user/wallet.php?deposit=success&ref=HRC-DEP-690FDBBE365D5-6351\", \"pay_currency\": null, \"price_amount\": \"10000\", \"is_fixed_rate\": false, \"customer_email\": null, \"price_currency\": \"USD\", \"payout_currency\": null, \"ipn_callback_url\": \"https://healthruncare.com/api/payments/now_webhook.php\", \"collect_user_data\": false, \"order_description\": \"HealthRunCare deposit: HRC-DEP-690FDBBE365D5-6351\", \"partially_paid_url\": null, \"is_fee_paid_by_user\": false}, \"invoice_created_at\": \"2025-11-09 00:09:35\", \"created_invoice_url\": \"https://nowpayments.io/payment/?iid=4804426065\", \"provider_payment_id\": \"4804426065\"}', 10000.00, 'HRC-DEP-690FDBBE365D5-6351', 'pending', '2025-11-08 23:09:34'),
(2, 1, 'investment', NULL, '{\"plan_id\": 1, \"plan_name\": \"Healthy Future Bond Plan\", \"investment_id\": 1}', 500.00, 'HRC-INV-69106132121B1-1362', 'completed', '2025-11-09 08:38:58'),
(3, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 1}', 20.00, 'HRC-DON-69132514CBF8C-7265', 'completed', '2025-11-11 10:59:16'),
(4, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 1}', 20.00, 'HRC-DON-69150EDB2C5F2-9564', 'completed', '2025-11-12 21:48:59'),
(5, 1, 'trustfund', 'wallet_address', '{\"plan_name\": \"Child Education Growth Plan\", \"roi_percent\": 25, \"duration_days\": 1095, \"maturity_date\": \"2028-11-11\", \"penalty_percent\": 1.5}', 500.00, 'TRF-778EF18CBC', 'completed', '2025-11-12 22:50:19'),
(6, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 1}', 10.00, 'HRC-DON-691B1183523E8-1453', 'completed', '2025-11-17 11:13:55'),
(8, 1, 'investment', NULL, '{\"plan_id\": 1, \"plan_name\": \"Healthy Future Bond Plan\", \"investment_id\": 2}', 500.00, 'HRC-INV-6921A76016415-6643', 'completed', '2025-11-22 11:06:56');

-- --------------------------------------------------------

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
-- Dumping data for table `trustfund`
--

INSERT INTO `trustfund` (`id`, `user_id`, `plan_name`, `amount`, `roi_percent`, `duration_days`, `penalty_percent`, `purpose`, `maturity_date`, `updated_at`, `roi_earned`, `payout_option`, `status`, `created_at`) VALUES
(1, 1, 'Child Education Growth Plan', 500.00, 25.00, 1095, 1.50, NULL, '2028-11-11', NULL, 0.00, 'maturity', 'active', '2025-11-12 22:50:19');

-- --------------------------------------------------------

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
(1, 1, 2250.00, 5, 0.50, 0, 3, '2025-11-22 12:07:41');

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
  `cash_mailing_address` text COLLATE utf8mb4_unicode_ci,
  `wallet_deposit_address` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `balance`, `total_deposited`, `total_withdrawn`, `total_donations`, `total_investments`, `holdlock_savings`, `total_earnings`, `pending_withdrawals`, `created_at`, `cash_mailing_address`, `wallet_deposit_address`) VALUES
(1, 1, 9500.50, 1230.00, 0.00, 250.00, 2000.00, 0.00, 300.00, 0.00, '2025-11-08 23:15:57', NULL, 'momomomomo');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `holdlock`
--
ALTER TABLE `holdlock`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `investments`
--
ALTER TABLE `investments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `investment_plans`
--
ALTER TABLE `investment_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `trustfund`
--
ALTER TABLE `trustfund`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_impacts`
--
ALTER TABLE `user_impacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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

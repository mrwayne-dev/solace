-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 08, 2025 at 11:09 PM
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
(7, 'Maternal Care Initiative', 'Africa Public Health Foundation (APHF)', 'Expanding access to prenatal and postnatal care for mothers in underserved African regions.', '/assets/images/charity/maternal-care.jpg', 80000.00, 57773.00, 'active', '2025-11-01 21:19:10'),
(8, 'Clean Water for Health Program', 'The Global Fund', 'Installing boreholes and filtration systems in rural health centers to reduce waterborne diseases.', '/assets/images/charity/clean-water.jpg', 60000.00, 44520.00, 'active', '2025-11-01 21:19:10'),
(9, 'Rural Health Outreach Network', 'PharmAccess Foundation', 'Providing mobile clinics and telemedicine solutions to remote villages.', '/assets/images/charity/rural-outreach.jpg', 95000.00, 71000.00, 'active', '2025-11-01 21:19:10'),
(10, 'Child Immunization Drive', 'Against Malaria Foundation (AMF)', 'Supporting mass vaccination programs to prevent common infectious diseases among children.', '/assets/images/charity/immunization.jpg', 50000.00, 36000.00, 'active', '2025-11-01 21:19:10'),
(11, 'Hospital Equipment Upgrade Fund', 'Transform Health Fund', 'Providing critical diagnostic tools and life-saving machines to local health facilities.', '/assets/images/charity/equipment-upgrade.jpg', 120000.00, 129000.00, 'active', '2025-11-01 21:19:10'),
(12, 'Nutrition for Hope Program', 'Africa Humanitarian Action (AHA)', 'Delivering essential food and supplements to children and elderly populations facing malnutrition.', '/assets/images/charity/nutrition.jpg', 45000.00, 28000.00, 'active', '2025-11-01 21:19:10');

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
(1, 1, 7, 222.00, 'HRC-DON-6906928A0D2F7-4531', '2025-11-01 22:06:50'),
(2, 1, 7, 1000.00, 'HRC-DON-690692FCE1EBB-9453', '2025-11-01 22:08:44'),
(3, 1, 11, 19000.00, 'HRC-DON-690695A3074D3-5198', '2025-11-01 22:20:03'),
(4, 1, 11, 19000.00, 'HRC-DON-690695A3CE2B7-7959', '2025-11-01 22:20:03'),
(5, 1, 7, 500.00, 'HRC-DON-6906994FDFC9F-9904', '2025-11-01 22:35:43'),
(6, 1, 8, 500.00, 'HRC-DON-69069E8586B45-8448', '2025-11-01 22:57:57'),
(7, 1, 7, 1.00, 'HRC-DON-6906A61ABC00E-4652', '2025-11-01 23:30:18'),
(8, 1, 7, 999.00, 'HRC-DON-6906A7C3DEAD7-1724', '2025-11-01 23:37:23'),
(9, 1, 7, 1.00, 'HRC-DON-6906A923B2419-9108', '2025-11-01 23:43:15'),
(10, 1, 7, 1000.00, 'HRC-DON-6907133DB7862-4889', '2025-11-02 07:15:57'),
(11, 1, 7, 20.00, 'HRC-DON-6907276B0F214-5359', '2025-11-02 08:42:03'),
(12, 1, 8, 20.00, 'HRC-DON-690728217BB6B-9540', '2025-11-02 08:45:05'),
(13, 1, 8, 2000.00, 'HRC-DON-69076EC901C90-4052', '2025-11-02 13:46:33'),
(14, 1, 7, 30.00, 'HRC-DON-6909C71C096C7-6426', '2025-11-04 08:27:56');

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

--
-- Dumping data for table `holdlock`
--

INSERT INTO `holdlock` (`id`, `user_id`, `plan_name`, `amount`, `roi_percent`, `duration_days`, `penalty_percent`, `status`, `maturity_date`, `roi_earned`, `penalty_applied`, `payout_option`, `created_at`, `updated_at`) VALUES
(1, 1, 'Flexi Health Lock Plan', 10000.00, 3.50, 180, 1.50, 'locked', '2026-05-02', 9.72, 0.00, 'maturity', '2025-11-03 09:57:38', '2025-11-08 21:52:17'),
(2, 1, 'Flexi Health Lock Plan', 10000.00, 3.50, 180, 1.50, 'locked', '2026-05-02', 9.72, 0.00, 'maturity', '2025-11-03 09:58:00', '2025-11-08 21:52:17'),
(3, 1, 'Flexi Health Lock Plan', 10000.00, 3.50, 180, 1.50, 'locked', '2026-05-02', 9.72, 0.00, 'maturity', '2025-11-03 09:59:32', '2025-11-08 21:52:17'),
(4, 1, 'Flexi Health Lock Plan', 10000.00, 3.50, 180, 1.50, 'locked', '2026-05-02', 9.72, 0.00, 'maturity', '2025-11-03 10:00:37', '2025-11-08 21:52:17'),
(5, 1, 'Flexi Health Lock Plan', 10000.00, 3.50, 180, 1.50, 'locked', '2026-05-02', 7.78, 0.00, 'maturity', '2025-11-03 21:41:12', '2025-11-08 21:52:17'),
(6, 1, 'Flexi Health Lock Plan', 10000.00, 3.50, 180, 1.50, 'locked', '2026-05-02', 7.78, 0.00, 'maturity', '2025-11-03 21:51:24', '2025-11-08 21:52:17'),
(7, 1, 'Standard Lock & Grow Plan', 20000.00, 8.00, 365, 1.50, 'locked', '2026-11-03', 17.53, 0.00, 'maturity', '2025-11-03 21:57:38', '2025-11-08 21:52:17'),
(8, 1, 'Flexi Health Lock Plan', 10000.00, 3.50, 180, 1.50, 'locked', '2026-05-02', 7.78, 0.00, 'maturity', '2025-11-03 22:22:14', '2025-11-08 21:52:17'),
(9, 1, 'Flexi Health Lock Plan', 10000.00, 3.50, 180, 1.50, 'locked', '2026-05-02', 7.78, 0.00, 'maturity', '2025-11-03 22:28:20', '2025-11-08 21:52:17'),
(10, 1, 'Flexi Health Lock Plan', 10000.00, 3.50, 180, 1.50, 'locked', '2026-05-02', 7.78, 0.00, 'maturity', '2025-11-03 22:38:57', '2025-11-08 21:52:17'),
(11, 1, 'Flexi Health Lock Plan', 10000.00, 3.50, 180, 1.50, 'locked', '2026-05-03', 7.78, 0.00, 'maturity', '2025-11-04 09:30:00', '2025-11-08 21:52:17');

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

--
-- Dumping data for table `infrastructure_contributions`
--

INSERT INTO `infrastructure_contributions` (`id`, `plan_id`, `user_id`, `project_id`, `amount`, `roi_earned`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 10000.00, 0.00, 'active', '2025-11-07 08:17:09', '2025-11-07 09:17:09');

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
(1, 1, 'Community Health Microfinance Plan', 400.00, 9.00, 360, 'active', '2026-10-28', 1.40, '2025-11-02 21:45:47'),
(2, 1, 'Healthy Future Bond Plan', 10000.00, 11.00, 540, 'active', '2027-04-26', 14.26, '2025-11-02 21:53:49'),
(3, 1, 'Healthy Future Bond Plan', 500.00, 11.00, 540, 'active', '2027-04-27', 0.71, '2025-11-03 20:44:23'),
(4, 1, 'Healthy Future Bond Plan', 500.00, 11.00, 540, 'active', '2027-04-27', 0.71, '2025-11-03 21:29:52'),
(5, 1, 'Health Innovation Venture Fund', 10000.00, 30.00, 1095, 'active', '2028-11-03', 19.18, '2025-11-04 08:29:29');

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

--
-- Dumping data for table `maintenance`
--

INSERT INTO `maintenance` (`id`, `plan_id`, `user_id`, `plan_name`, `amount`, `roi_earned`, `frequency`, `status`, `next_payment_date`, `maturity_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Maintenance Support Starter Plan', 10000.00, 550.00, 'once', 'matured', NULL, '2026-08-04', '2025-01-12 22:48:46', '2025-11-08 22:48:54'),
(2, 1, 1, 'Maintenance Support Starter Plan', 10000.00, 0.00, 'once', 'active', NULL, '2026-08-04', '2025-11-07 16:51:16', '2025-11-07 17:51:16'),
(3, 1, 1, 'Maintenance Support Starter Plan', 10000.00, 0.00, 'once', 'active', NULL, '2026-08-04', '2025-11-07 17:07:11', '2025-11-07 18:07:11'),
(4, 1, 1, 'Maintenance Support Starter Plan', 10000.00, 0.00, 'once', 'active', NULL, '2026-08-04', '2025-11-07 17:13:25', '2025-11-07 18:13:25'),
(5, 1, 1, 'Maintenance Support Starter Plan', 10000.00, 0.00, 'once', 'active', NULL, '2026-08-04', '2025-11-07 17:20:29', '2025-11-07 18:20:29'),
(6, 1, 1, 'Maintenance Support Starter Plan', 10000.00, 0.00, 'once', 'active', NULL, '2026-08-04', '2025-11-07 17:32:50', '2025-11-07 18:32:50'),
(7, 1, 1, 'Maintenance Support Starter Plan', 10000.00, 0.00, 'once', 'active', NULL, '2026-08-04', '2025-11-07 17:38:33', '2025-11-07 18:38:33'),
(8, 1, 1, 'Maintenance Support Starter Plan', 10000.00, 0.00, 'once', 'active', NULL, '2026-08-04', '2025-11-07 19:48:44', '2025-11-07 20:48:44');

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
(30, 1, 'deposit', 'wire_transfer', '{\"method\": \"wire_transfer\", \"initiated_at\": \"2025-10-31 21:34:28\"}', 300.00, 'HRC-DEP-69052B640C0C5-6030', 'completed', '2025-10-31 20:34:28'),
(31, 1, 'withdraw', 'wallet_address', '{\"method\": \"wallet_address\", \"requested_at\": \"2025-10-31 22:16:41\", \"withdraw_details\": {\"coin\": \"btc\", \"address\": \"2222222222222222\"}}', 500.00, 'HRC-WD-6905354926B83-8543', 'failed', '2025-10-31 21:16:41'),
(32, 1, 'deposit', 'cash_mailing', '{\"method\": \"cash_mailing\", \"initiated_at\": \"2025-11-01 11:04:48\"}', 300.00, 'HRC-DEP-6905E95088026-1449', 'pending', '2025-11-01 10:04:48'),
(33, 1, 'withdraw', 'wallet_address', '{\"method\": \"wallet_address\", \"requested_at\": \"2025-11-01 12:04:06\", \"withdraw_details\": {\"coin\": \"eth\", \"address\": \"nwnidniedniendiede\"}}', 400.00, 'HRC-WD-6905F736D1184-7345', 'pending', '2025-11-01 11:04:06'),
(43, 1, 'deposit', 'wire_transfer', '{\"method\": \"wire_transfer\", \"initiated_at\": \"2025-11-01 13:43:10.000000\"}', 250.00, 'HRC-DEP-4543883385', 'completed', '2025-11-01 12:43:10'),
(44, 1, 'deposit', 'local_bank', '{\"method\": \"local_bank\", \"initiated_at\": \"2025-11-01 13:43:10.000000\"}', 600.00, 'HRC-DEP-5228508128', 'pending', '2025-11-01 12:43:10'),
(45, 1, 'withdraw', 'wallet_address', '{\"method\": \"wallet_address\", \"requested_at\": \"2025-11-01 13:43:10.000000\"}', 400.00, 'HRC-WD-2510890795', 'pending', '2025-11-01 12:43:10'),
(46, 1, 'withdraw', 'local_bank', '{\"method\": \"local_bank\", \"requested_at\": \"2025-11-01 13:43:10.000000\"}', 150.00, 'HRC-WD-6868929896', 'completed', '2025-11-01 12:43:10'),
(47, 1, 'donation', 'secure_exchange', '{\"method\": \"secure_exchange\", \"charity\": \"Save the Kids\", \"donated_at\": \"2025-11-01 13:43:10.000000\"}', 100.00, 'HRC-DON-6811977407', 'completed', '2025-11-01 12:43:10'),
(48, 1, 'investment', 'secure_exchange', '{\"plan\": \"Growth Booster\", \"method\": \"secure_exchange\", \"invested_at\": \"2025-11-01 13:43:10.000000\"}', 500.00, 'HRC-INV-3453097653', 'completed', '2025-11-01 12:43:10'),
(49, 1, 'holdlock', 'wallet_address', '{\"method\": \"wallet_address\", \"locked_at\": \"2025-11-01 13:43:10.000000\"}', 300.00, 'HRC-HL-6829556353', 'pending', '2025-11-01 12:43:10'),
(50, 1, 'trustfund', 'wire_transfer', '{\"method\": \"wire_transfer\", \"created_at\": \"2025-11-01 13:43:10.000000\"}', 700.00, 'HRC-TF-3788489115', 'pending', '2025-11-01 12:43:10'),
(51, 1, 'maintenance', 'secure_exchange', '{\"cycle\": \"monthly\", \"method\": \"secure_exchange\", \"created_at\": \"2025-11-01 13:43:10.000000\"}', 50.00, 'HRC-MT-8453776824', 'completed', '2025-11-01 12:43:10'),
(52, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 7}', 222.00, 'HRC-DON-6906928A0D2F7-4531', 'completed', '2025-11-01 22:06:50'),
(53, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 7}', 1000.00, 'HRC-DON-690692FCE1EBB-9453', 'completed', '2025-11-01 22:08:44'),
(54, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 11}', 19000.00, 'HRC-DON-690695A3074D3-5198', 'completed', '2025-11-01 22:20:03'),
(55, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 11}', 19000.00, 'HRC-DON-690695A3CE2B7-7959', 'completed', '2025-11-01 22:20:03'),
(56, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 7}', 500.00, 'HRC-DON-6906994FDFC9F-9904', 'completed', '2025-11-01 22:35:43'),
(57, 1, 'deposit', 'cash_mailing', '{\"method\": \"cash_mailing\", \"initiated_at\": \"2025-11-01 23:44:27\"}', 300.00, 'HRC-DEP-69069B5B43852-3403', 'pending', '2025-11-01 22:44:27'),
(58, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 8}', 500.00, 'HRC-DON-69069E8586B45-8448', 'completed', '2025-11-01 22:57:57'),
(59, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 7}', 1.00, 'HRC-DON-6906A61ABC00E-4652', 'completed', '2025-11-01 23:30:18'),
(60, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 7}', 999.00, 'HRC-DON-6906A7C3DEAD7-1724', 'completed', '2025-11-01 23:37:23'),
(61, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 7}', 1.00, 'HRC-DON-6906A923B2419-9108', 'completed', '2025-11-01 23:43:15'),
(62, 1, 'deposit', 'cash_mailing', '{\"method\": \"cash_mailing\", \"initiated_at\": \"2025-11-02 00:44:35\"}', 230.00, 'HRC-DEP-6906A973E5945-7593', 'pending', '2025-11-01 23:44:35'),
(63, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 7}', 1000.00, 'HRC-DON-6907133DB7862-4889', 'completed', '2025-11-02 07:15:57'),
(64, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 7}', 20.00, 'HRC-DON-6907276B0F214-5359', 'completed', '2025-11-02 08:42:03'),
(65, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 8}', 20.00, 'HRC-DON-690728217BB6B-9540', 'completed', '2025-11-02 08:45:05'),
(66, 1, 'deposit', 'cash_mailing', '{\"method\": \"cash_mailing\", \"initiated_at\": \"2025-11-02 14:40:56\"}', 222.00, 'HRC-DEP-69076D7833E87-5855', 'pending', '2025-11-02 13:40:56'),
(67, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 8}', 2000.00, 'HRC-DON-69076EC901C90-4052', 'completed', '2025-11-02 13:46:33'),
(68, 1, 'investment', NULL, '{\"plan_id\": 4, \"plan_name\": \"Community Health Microfinance Plan\", \"investment_id\": 1}', 400.00, 'HRC-INV-6907DF1B2E7DE-3585', 'completed', '2025-11-02 21:45:47'),
(69, 1, 'investment', NULL, '{\"plan_id\": 1, \"plan_name\": \"Healthy Future Bond Plan\", \"investment_id\": 2}', 10000.00, 'HRC-INV-6907E0FD02FEA-8400', 'completed', '2025-11-02 21:53:49'),
(70, 1, 'holdlock_start', 'wallet', '{\"roi\": \"3.5\", \"plan\": \"Flexi Health Lock Plan\", \"duration\": \"180\"}', 10000.00, 'HLD-6BE1ADB41A', 'completed', '2025-11-03 10:00:37'),
(71, 1, 'holdlock_start', 'wallet', '{\"roi\": \"3.5\", \"plan\": \"Flexi Health Lock Plan\", \"duration\": \"180\"}', 10000.00, 'HLD-3094588060', 'completed', '2025-11-03 21:41:12'),
(72, 1, 'investment', NULL, '{\"plan_id\": 1, \"plan_name\": \"Healthy Future Bond Plan\", \"investment_id\": 3}', 500.00, 'HRC-INV-69092237DF175-7521', 'completed', '2025-11-03 20:44:23'),
(73, 1, 'holdlock_start', 'wallet', '{\"roi\": \"3.5\", \"plan\": \"Flexi Health Lock Plan\", \"duration\": \"180\"}', 10000.00, 'HLD-CBE0317B7A', 'completed', '2025-11-03 21:51:24'),
(74, 1, 'holdlock_start', 'wallet', '{\"roi\": \"8\", \"plan\": \"Standard Lock & Grow Plan\", \"duration\": \"365\"}', 20000.00, 'HLD-1C41FA1432', 'completed', '2025-11-03 21:57:38'),
(75, 1, 'holdlock_start', 'wallet', '{\"roi\": \"3.5\", \"plan\": \"Flexi Health Lock Plan\", \"duration\": \"180\"}', 10000.00, 'HLD-A51D0FED71', 'completed', '2025-11-03 22:22:14'),
(76, 1, 'holdlock_start', 'wallet', '{\"roi\": \"3.5\", \"plan\": \"Flexi Health Lock Plan\", \"duration\": \"180\"}', 10000.00, 'HLD-9438CEC008', 'completed', '2025-11-03 22:28:20'),
(77, 1, 'investment', NULL, '{\"plan_id\": 1, \"plan_name\": \"Healthy Future Bond Plan\", \"investment_id\": 4}', 500.00, 'HRC-INV-69092CE0685CB-6905', 'completed', '2025-11-03 21:29:52'),
(78, 1, 'holdlock', 'wallet', '{\"plan\": \"Flexi Health Lock Plan\", \"subtype\": \"start\", \"roi_percent\": \"3.5\", \"duration_days\": \"180\", \"maturity_date\": \"2026-05-02\"}', 10000.00, 'HLD-16A96B06BB', 'completed', '2025-11-03 22:38:57'),
(79, 1, 'donation', NULL, '{\"note\": \"\", \"charity_id\": 7}', 30.00, 'HRC-DON-6909C71C096C7-6426', 'completed', '2025-11-04 08:27:56'),
(80, 1, 'investment', NULL, '{\"plan_id\": 3, \"plan_name\": \"Health Innovation Venture Fund\", \"investment_id\": 5}', 10000.00, 'HRC-INV-6909C779D53CC-3519', 'completed', '2025-11-04 08:29:29'),
(81, 1, 'holdlock', 'wallet', '{\"plan\": \"Flexi Health Lock Plan\", \"subtype\": \"start\", \"roi_percent\": \"3.5\", \"duration_days\": \"180\", \"maturity_date\": \"2026-05-03\"}', 10000.00, 'HLD-6C963DD8AA', 'completed', '2025-11-04 09:30:00'),
(82, 1, 'trustfund', 'wallet_address', '{\"plan_name\": \"Child Education Growth Plan\", \"roi_percent\": 25, \"duration_days\": 1095, \"maturity_date\": \"2028-11-03\", \"penalty_percent\": 1.5}', 500.00, 'TRF-26DBC1AD60', 'completed', '2025-11-04 22:11:17'),
(83, 1, 'trustfund', 'wallet_address', '{\"plan_name\": \"Child Education Growth Plan\", \"roi_percent\": 25, \"duration_days\": 1095, \"maturity_date\": \"2028-11-03\", \"penalty_percent\": 1.5}', 1000.00, 'TRF-F23608DE08', 'completed', '2025-11-04 22:13:13'),
(84, 1, 'trustfund', 'wallet_address', '{\"plan_name\": \"Child Education Growth Plan\", \"roi_percent\": 25, \"duration_days\": 1095, \"maturity_date\": \"2028-11-04\", \"penalty_percent\": 1.5}', 500.00, 'TRF-368837F52D', 'completed', '2025-11-05 11:09:58'),
(85, 1, 'trustfund', 'wallet_address', '{\"plan_name\": \"Child Education Growth Plan\", \"roi_percent\": 25, \"duration_days\": 1095, \"maturity_date\": \"2028-11-05\", \"penalty_percent\": 1.5}', 500.00, 'TRF-A51F7E30AB', 'completed', '2025-11-06 10:34:59'),
(86, 1, 'trustfund', 'wallet_address', '{\"plan_name\": \"Business Succession Trust Plan\", \"roi_percent\": 48, \"duration_days\": 1460, \"maturity_date\": \"2029-11-05\", \"penalty_percent\": 1.5}', 5000.00, 'TRF-32486D7B00', 'completed', '2025-11-06 11:04:14'),
(87, 1, 'deposit', 'secure_exchange', '{\"method\": \"secure_exchange\", \"provider\": \"nowpayments\", \"initiated_at\": \"2025-11-06 11:08:18\", \"provider_response\": {\"id\": \"6405616432\", \"source\": null, \"order_id\": \"HRC-DEP-690C81A2CA519-5866\", \"token_id\": \"5490326139\", \"cancel_url\": \"https://healthruncare.com/pages/user/wallet.php?deposit=cancel&ref=HRC-DEP-690C81A2CA519-5866\", \"created_at\": \"2025-11-06T11:08:21.888Z\", \"updated_at\": \"2025-11-06T11:08:21.888Z\", \"invoice_url\": \"https://nowpayments.io/payment/?iid=6405616432\", \"success_url\": \"https://healthruncare.com/pages/user/wallet.php?deposit=success&ref=HRC-DEP-690C81A2CA519-5866\", \"pay_currency\": null, \"price_amount\": \"500\", \"is_fixed_rate\": false, \"customer_email\": null, \"price_currency\": \"USD\", \"payout_currency\": null, \"ipn_callback_url\": \"https://healthruncare.com/api/payments/now_webhook.php\", \"collect_user_data\": false, \"order_description\": \"HealthRunCare deposit: HRC-DEP-690C81A2CA519-5866\", \"partially_paid_url\": null, \"is_fee_paid_by_user\": false}, \"invoice_created_at\": \"2025-11-06 11:08:19\", \"created_invoice_url\": \"https://nowpayments.io/payment/?iid=6405616432\", \"provider_payment_id\": \"6405616432\"}', 500.00, 'HRC-DEP-690C81A2CA519-5866', 'pending', '2025-11-06 10:08:18'),
(88, 1, 'deposit', 'secure_exchange', '{\"method\": \"secure_exchange\", \"provider\": \"nowpayments\", \"initiated_at\": \"2025-11-06 12:27:52\", \"provider_response\": {\"id\": \"6246598768\", \"source\": null, \"order_id\": \"HRC-DEP-690C9448A1E04-1686\", \"token_id\": \"5490326139\", \"cancel_url\": \"https://healthruncare.com/pages/user/wallet.php?deposit=cancel&ref=HRC-DEP-690C9448A1E04-1686\", \"created_at\": \"2025-11-06T12:27:55.334Z\", \"updated_at\": \"2025-11-06T12:27:55.334Z\", \"invoice_url\": \"https://nowpayments.io/payment/?iid=6246598768\", \"success_url\": \"https://healthruncare.com/pages/user/wallet.php?deposit=success&ref=HRC-DEP-690C9448A1E04-1686\", \"pay_currency\": null, \"price_amount\": \"400\", \"is_fixed_rate\": false, \"customer_email\": null, \"price_currency\": \"USD\", \"payout_currency\": null, \"ipn_callback_url\": \"https://healthruncare.com/api/payments/now_webhook.php\", \"collect_user_data\": false, \"order_description\": \"HealthRunCare deposit: HRC-DEP-690C9448A1E04-1686\", \"partially_paid_url\": null, \"is_fee_paid_by_user\": false}, \"invoice_created_at\": \"2025-11-06 12:27:53\", \"created_invoice_url\": \"https://nowpayments.io/payment/?iid=6246598768\", \"provider_payment_id\": \"6246598768\"}', 400.00, 'HRC-DEP-690C9448A1E04-1686', 'pending', '2025-11-06 11:27:52'),
(89, 1, 'deposit', 'secure_exchange', '{\"method\": \"secure_exchange\", \"provider\": \"nowpayments\", \"initiated_at\": \"2025-11-06 13:04:22\", \"provider_response\": {\"id\": \"5298167667\", \"source\": null, \"order_id\": \"HRC-DEP-690C9CD6B58F2-8065\", \"token_id\": \"5490326139\", \"cancel_url\": \"https://healthruncare.com/pages/user/wallet.php?deposit=cancel&ref=HRC-DEP-690C9CD6B58F2-8065\", \"created_at\": \"2025-11-06T13:04:25.584Z\", \"updated_at\": \"2025-11-06T13:04:25.584Z\", \"invoice_url\": \"https://nowpayments.io/payment/?iid=5298167667\", \"success_url\": \"https://healthruncare.com/pages/user/wallet.php?deposit=success&ref=HRC-DEP-690C9CD6B58F2-8065\", \"pay_currency\": null, \"price_amount\": \"2000\", \"is_fixed_rate\": false, \"customer_email\": null, \"price_currency\": \"USD\", \"payout_currency\": null, \"ipn_callback_url\": \"https://healthruncare.com/api/payments/now_webhook.php\", \"collect_user_data\": false, \"order_description\": \"HealthRunCare deposit: HRC-DEP-690C9CD6B58F2-8065\", \"partially_paid_url\": null, \"is_fee_paid_by_user\": false}, \"invoice_created_at\": \"2025-11-06 13:04:23\", \"created_invoice_url\": \"https://nowpayments.io/payment/?iid=5298167667\", \"provider_payment_id\": \"5298167667\"}', 2000.00, 'HRC-DEP-690C9CD6B58F2-8065', 'pending', '2025-11-06 12:04:22'),
(90, 1, 'infrastructure', 'wallet_address', '{\"plan_id\": 1, \"plan_name\": \"Basic Diagnostic Plan\", \"contrib_id\": \"1\"}', 10000.00, 'INF-11BC623EDB', 'completed', '2025-11-07 08:17:09'),
(91, 1, 'maintenance', 'wallet_address', '{\"plan_id\": 1, \"plan_name\": \"Maintenance Support Starter Plan\", \"contrib_id\": \"1\"}', 10000.00, 'MNT-3AE5A655FD', 'completed', '2025-11-07 16:50:44'),
(92, 1, 'maintenance', 'wallet_address', '{\"plan_id\": 1, \"plan_name\": \"Maintenance Support Starter Plan\", \"contrib_id\": \"2\"}', 10000.00, 'MNT-C201520145', 'completed', '2025-11-07 16:51:16'),
(93, 1, 'maintenance', 'wallet_address', '{\"plan_id\": 1, \"plan_name\": \"Maintenance Support Starter Plan\", \"contrib_id\": \"3\"}', 10000.00, 'MNT-9A92E404F3', 'completed', '2025-11-07 17:07:11'),
(94, 1, 'maintenance', 'wallet_address', '{\"plan_id\": 1, \"plan_name\": \"Maintenance Support Starter Plan\", \"contrib_id\": \"4\"}', 10000.00, 'MNT-818F0D5311', 'completed', '2025-11-07 17:13:25'),
(95, 1, 'maintenance', 'wallet_address', '{\"plan_id\": 1, \"plan_name\": \"Maintenance Support Starter Plan\", \"contrib_id\": \"5\"}', 10000.00, 'MNT-C6322E2A1E', 'completed', '2025-11-07 17:20:29'),
(96, 1, 'maintenance', 'wallet_address', '{\"plan_id\": 1, \"plan_name\": \"Maintenance Support Starter Plan\", \"contrib_id\": \"6\"}', 10000.00, 'MNT-DBDA02376A', 'completed', '2025-11-07 17:32:50'),
(97, 1, 'maintenance', 'wallet_address', '{\"plan_id\": 1, \"plan_name\": \"Maintenance Support Starter Plan\", \"contrib_id\": \"7\"}', 10000.00, 'MNT-8077343A7D', 'completed', '2025-11-07 17:38:33'),
(98, 1, 'maintenance', 'wallet_address', '{\"plan_id\": 1, \"plan_name\": \"Maintenance Support Starter Plan\", \"contrib_id\": \"8\"}', 10000.00, 'MNT-DA01ADA4D8', 'completed', '2025-11-07 19:48:44'),
(99, 1, 'deposit', 'cash_mailing', '{\"method\": \"cash_mailing\", \"initiated_at\": \"2025-11-07 21:34:48\"}', 1000.00, 'HRC-DEP-690E65F847F63-6084', 'pending', '2025-11-07 20:34:48'),
(100, 1, 'investment', NULL, '{\"weekly_roi\": 0.7, \"investment_id\": 1}', 0.70, 'HRC-ROI-690FB64AC7B4F', 'completed', '2025-11-08 20:29:46'),
(101, 1, 'investment', NULL, '{\"weekly_roi\": 0.7, \"investment_id\": 1}', 0.70, 'HRC-ROI-690FB69DB449A', 'completed', '2025-11-08 20:31:09'),
(102, 1, 'investment', NULL, '{\"weekly_roi\": 14.26, \"investment_id\": 2}', 14.26, 'HRC-ROI-690FB6A3298FE', 'completed', '2025-11-08 20:31:09'),
(103, 1, 'investment', NULL, '{\"weekly_roi\": 0.71, \"investment_id\": 3}', 0.71, 'HRC-ROI-690FB6A73EC60', 'completed', '2025-11-08 20:31:09'),
(104, 1, 'investment', NULL, '{\"weekly_roi\": 0.71, \"investment_id\": 4}', 0.71, 'HRC-ROI-690FB6AB51AB2', 'completed', '2025-11-08 20:31:09'),
(105, 1, 'investment', NULL, '{\"weekly_roi\": 19.18, \"investment_id\": 5}', 19.18, 'HRC-ROI-690FB6AF52B75', 'completed', '2025-11-08 20:31:09'),
(106, 1, 'trustfund', 'wallet_address', '{\"type\": \"trustfund_maturity\", \"trust_id\": 1, \"roi_earned\": 125, \"roi_percent\": 25, \"total_payout\": 625}', 625.00, 'TRF-MAT-A2619850E8', 'completed', '2025-11-08 22:17:29'),
(107, 1, 'trustfund', 'wallet_address', '{\"subtype\": \"maturity_unlock\", \"trust_id\": 1, \"roi_earned\": 125, \"total_payout\": 625, \"penalty_applied\": 0}', 625.00, 'TRF-UNL-E1CE9E0F', 'completed', '2025-11-08 22:39:52'),
(108, 1, 'maintenance', 'wallet_address', '{\"subtype\": \"maturity_unlock\", \"roi_earned\": 550, \"total_payout\": 10550, \"maintenance_id\": 1}', 10550.00, 'MNT-MAT-27592EE1', 'completed', '2025-11-08 22:48:54');

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
(1, 1, 'Child Education Growth Plan', 500.00, 25.00, 1095, 1.50, NULL, '2025-11-08', '2025-11-08 23:39:52', 125.00, 'maturity', 'completed', '2025-11-04 22:11:17'),
(2, 1, 'Child Education Growth Plan', 1000.00, 25.00, 1095, 1.50, NULL, '2028-11-03', NULL, 0.00, 'maturity', 'active', '2025-11-04 22:13:13'),
(3, 1, 'Child Education Growth Plan', 500.00, 25.00, 1095, 1.50, NULL, '2028-11-04', NULL, 0.00, 'maturity', 'active', '2025-11-05 11:09:58'),
(4, 1, 'Child Education Growth Plan', 500.00, 25.00, 1095, 1.50, NULL, '2028-11-05', NULL, 0.00, 'maturity', 'active', '2025-11-06 10:34:59'),
(5, 1, 'Business Succession Trust Plan', 5000.00, 48.00, 1460, 1.50, NULL, '2029-11-05', NULL, 0.00, 'maturity', 'active', '2025-11-06 11:04:14');

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
(1, 1, 272793.00, 96, 100.00, 4, 114, '2025-11-08 21:22:39');

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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `balance`, `total_deposited`, `total_withdrawn`, `total_donations`, `total_investments`, `holdlock_savings`, `total_earnings`, `pending_withdrawals`, `created_at`) VALUES
(1, 1, 823306.26, 1000000.00, 0.00, 44293.00, 108500.00, 120000.00, 814.35, 2000.00, '2025-10-27 21:03:23');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `bank_details`
--
ALTER TABLE `bank_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `charities`
--
ALTER TABLE `charities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `charity_donations`
--
ALTER TABLE `charity_donations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `holdlock`
--
ALTER TABLE `holdlock`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `infrastructure`
--
ALTER TABLE `infrastructure`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `infrastructure_contributions`
--
ALTER TABLE `infrastructure_contributions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `investments`
--
ALTER TABLE `investments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `trustfund`
--
ALTER TABLE `trustfund`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_impacts`
--
ALTER TABLE `user_impacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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

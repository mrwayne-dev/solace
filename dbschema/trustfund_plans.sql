-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 23, 2025 at 03:26 PM
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
-- Dumping data for table `trustfund_plans`
--

INSERT INTO `trustfund_plans` (`id`, `name`, `purpose`, `min_amount`, `max_amount`, `duration_days`, `roi_percent`, `risk`, `payout_option`, `summary`, `icon`, `color`, `created_at`) VALUES
(1, 'Child Education Growth Plan', 'Build a secure education fund with guaranteed growth.', 500.00, 50000.00, 1095, 25.00, 'Low', 'Annual or at maturity', 'A secure education-focused savings plan that grows steadily over time. Designed to protect capital while building reliable funds for tuition, fees, and academic expenses. This trust provides guaranteed growth and helps parents and guardians plan early with confidence. Ideal for preparing long-term education goals without financial stress.', 'mdi:school-outline', 'Green', '2025-11-22 14:15:26'),
(2, 'Legacy Wealth Trust Plan', 'Generate long-term generational wealth.', 500000.00, NULL, 1825, 55.00, 'Moderate', 'Annual or at maturity', 'A premium wealth preservation and growth trust designed for multigenerational impact. Built to protect and grow assets over many years, ensuring enduring financial security. Offers stable compounding returns ideal for inheritance planning. Perfect for families and estates seeking long-term financial empowerment with sustained legacy outcomes.', 'mdi:family-tree', 'Blue', '2025-11-22 14:15:26'),
(3, 'Business Succession Trust Plan', 'Secure business growth or transition.', 5000.00, 500000.00, 1460, 48.00, 'Moderate to High', 'Annual or at maturity', 'A strategic business-focused trust for organizations planning expansion or succession. Provides consistent returns while supporting growth, transition, or ownership transfer. Designed to secure capital while maintaining profitability. Ideal for entrepreneurs looking to protect business assets while building long-term enterprise value.', 'mdi:briefcase-outline', 'Orange', '2025-11-22 14:15:26'),
(4, 'Medical Protection Trust Plan', 'Create a secure medical reserve with capital growth.', 300.00, 25000.00, 1095, 18.00, 'Low', 'Quarterly or at maturity', 'A specialized medical savings and protection trust that secures long-term emergency funds. Designed to grow your capital safely while ensuring healthcare preparedness. Offers accessible returns to support medical needs, emergencies, and family protection. A stable plan for anyone seeking security and wellness with financial peace of mind.', 'mdi:heart-pulse', 'Green', '2025-11-22 14:15:26'),
(5, 'Future Builders Business Plan', 'Support startups and young entrepreneurs.', 1000.00, 100000.00, 1460, 38.00, 'Moderate', 'Annual or at maturity', 'A forward-focused investment trust for startups, young entrepreneurs, and innovation-driven ventures. Designed to fuel ideas while generating profitable returns for supporters. Balances growth, social impact, and sustainable business funding. Ideal for investors seeking meaningful financial growth that empowers the next generation of innovators.', 'mdi:rocket-outline', 'Blue', '2025-11-22 14:15:26'),
(6, 'Guardian Trust Income Plan', 'Steady annual income for beneficiaries.', 10000.00, 200000.00, 1825, 35.00, 'Low to Moderate', 'Annual income distribution', 'A dependable income trust providing guaranteed yearly payouts for beneficiaries. Protects principal while generating stable distribution to dependents, families, or long-term support needs. Ideal for individuals who want to secure financial care for others with predictable income. Built for reliability, safety, and consistent returns.', 'mdi:shield-check-outline', 'Green', '2025-11-22 14:15:26'),
(7, 'Perpetual Legacy Trust Plan', 'Lifetime income with preserved principal.', 1000000.00, NULL, 9999, 11.00, 'Low', 'Annual or quarterly for life', 'An elite wealth preservation trust that guarantees lifetime income while permanently protecting principal value. Designed for estates, family offices, and institutional planning. Generates continuous payouts across generations with low-risk protection. A premium legacy solution for enduring financial continuity, security, and long-term prosperity.', 'mdi:infinity', 'Orange', '2025-11-22 14:15:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `trustfund_plans`
--
ALTER TABLE `trustfund_plans`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `trustfund_plans`
--
ALTER TABLE `trustfund_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

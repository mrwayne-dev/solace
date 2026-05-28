
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('super_admin','manager','support') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'manager',
  `status` enum('active','disabled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '/assets/images/avatar/admin_default.png',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'mr wayne','mr wayne','aleruchi0987@gmail.com','$2y$10$tssRL/rKOgDPKtFCs5w6MetlAM3fF2FBTMO0S4mkRD6iwjdkZHLKC','manager','active','/assets/images/avatar/admin_default.png','2025-11-24 07:16:30','2025-11-12 15:22:41');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `bank_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `method` enum('local_bank','wallet_address') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` json NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bank_user` (`user_id`),
  CONSTRAINT `bank_details_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `bank_details` WRITE;
/*!40000 ALTER TABLE `bank_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_details` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `holdlock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `holdlock` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hold_user` (`user_id`),
  KEY `idx_hold_maturity` (`maturity_date`),
  CONSTRAINT `holdlock_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `holdlock` WRITE;
/*!40000 ALTER TABLE `holdlock` DISABLE KEYS */;
/*!40000 ALTER TABLE `holdlock` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `holdlock_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `holdlock_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `holdlock_plans` WRITE;
/*!40000 ALTER TABLE `holdlock_plans` DISABLE KEYS */;
INSERT INTO `holdlock_plans` VALUES (1,'Flexi Volt Lock','A short-term plan designed for clients who want safe, quick returns while keeping their capital secure.',10000.00,99999999.99,'180 days',180,'2.00%','Very Low','Full payout at maturity','Ideal for clients seeking liquidity and short-term growth. Funds are safely held and paid out at the end of the term. This option delivers competitive returns without locking you in for the long haul. It’s perfect for individuals or organizations that want reliable growth while maintaining access to their capital.','mdi:clock-outline','Green','2025-11-22 13:42:57'),(2,'Standard Charge Lock','A one-year plan offering predictable and consistent growth with minimal risk.',20000.00,300000.00,'12 months',365,'7–9%','Low','Annual or full payout at maturity','A balanced one-year growth plan for investors who prefer stability and moderate fixed returns. Designed to protect your capital while steadily increasing its value over time. It offers predictable earnings without the volatility of short-term market shifts. Ideal for individuals and organizations looking to grow funds responsibly, with reliable year-end payouts.','mdi:calendar-check','Green','2025-11-22 13:42:57'),(3,'Executive PowerLock Plus','A two-year plan for individuals and organizations seeking better returns from moderate-term investments.',50000.00,500000.00,'24 months',730,'14–18%','Moderate','Annual or full payout at maturity','Perfect for mid- to high-level investors seeking strong, consistent growth over two years with minimal risk exposure. This plan focuses on building wealth steadily through secure, well-managed allocations. It delivers higher returns than shorter-term options without sacrificing safety or predictability. Ideal for individuals and institutions looking to maximize gains over a longer horizon while keeping their capital protected.','mdi:briefcase-check','Blue','2025-11-22 13:42:57'),(4,'Prestige Capital Reserve','A premium plan for investors with large capital who seek long-term, high-yield returns.',250000.00,NULL,'36 months',1095,'25–30%','Moderate','Annual, bi-annual, or full payout at maturity','A long-term, asset-secure investment option that rewards patience with premium returns and stable growth. Designed for investors who value strong protection of capital while building significant wealth over time. This plan leverages extended compounding benefits to deliver higher, more reliable earnings. Ideal for those who prioritize security yet still aim for ambitious growth targets.','mdi:crown-outline','Orange','2025-11-22 13:42:57'),(5,'Lifetime Arc Reserve','A lifelong plan designed for wealth preservation and consistent annual income.',1000000.00,NULL,'Lifetime (Perpetual)',36500,'6–8% annual','Low','Annual or quarterly lifetime payout','An exclusive wealth preservation plan that guarantees lifetime income, ideal for estates, families, or organizations focused on long-term legacy. Built to protect high-value assets while delivering consistent financial strength across generations. This option ensures stable earnings, even in changing market conditions, while safeguarding the core value of your capital.','mdi:infinity','Green','2025-11-22 13:42:57');
/*!40000 ALTER TABLE `holdlock_plans` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `infrastructure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infrastructure` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `goal_amount` decimal(12,2) DEFAULT '0.00',
  `raised_amount` decimal(12,2) DEFAULT '0.00',
  `roi_percent` decimal(5,2) DEFAULT '10.00',
  `status` enum('open','funded','complete') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_infra_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `infrastructure` WRITE;
/*!40000 ALTER TABLE `infrastructure` DISABLE KEYS */;
/*!40000 ALTER TABLE `infrastructure` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `infrastructure_contributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infrastructure_contributions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plan_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `project_id` int DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `roi_earned` decimal(12,2) DEFAULT '0.00',
  `status` enum('active','matured','unlocked') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_infra_user` (`user_id`),
  KEY `idx_infra_project` (`project_id`),
  CONSTRAINT `infra_contrib_fk_project` FOREIGN KEY (`project_id`) REFERENCES `infrastructure` (`id`) ON DELETE CASCADE,
  CONSTRAINT `infra_contrib_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `infrastructure_contributions` WRITE;
/*!40000 ALTER TABLE `infrastructure_contributions` DISABLE KEYS */;
/*!40000 ALTER TABLE `infrastructure_contributions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `infrastructure_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infrastructure_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `infrastructure_plans` WRITE;
/*!40000 ALTER TABLE `infrastructure_plans` DISABLE KEYS */;
INSERT INTO `infrastructure_plans` VALUES (1,'Basic Diagnostic Plan','To support community and mid-level hospitals with portable ultrasound diagnostic systems for early disease detection.',10000.00,365,9.00,'quarterly','Very Low','A foundational infrastructure plan supporting community and mid-level hospitals with portable ultrasound systems for early detection. Investments directly equip clinics with diagnostic tools that generate continuous service revenue. Capital is repaid from ultrasound scan fees, ensuring predictable quarterly returns. Ideal for investors seeking stable, low-risk healthcare income anchored in essential medical services.','Green','Quarterly payments over 12 months','mdi:office-building-outline','2025-11-22 18:38:18','2025-11-23 15:22:06'),(2,'Imaging Growth Plan','To deploy digital X-ray imaging systems for regional hospitals and diagnostic centers.',20000.00,540,13.50,'quarterly','Low','This plan finances the installation of digital X-ray imaging systems in hospitals and diagnostic centers. Patient scan revenue powers structured repayments, creating reliable cash flow for investors. Designed to expand diagnostic access in underserved regions while yielding 12–15% total profit within 18 months. A strong option for low-risk investors supporting scalable healthcare growth with guaranteed demand.','Green','Quarterly or semi-annual','mdi:bank-outline','2025-11-22 18:38:18','2025-11-23 15:22:06'),(3,'Advanced Radiology Plan','To enable hospitals to install CT scanners and expand access to high-precision imaging services.',50000.00,730,17.50,'monthly','Moderate','A strategic infrastructure investment enabling hospitals to install CT scanners for advanced radiology diagnostics. HealthRunCare manages procurement, installation, and hospital repayment contracts to ensure secure returns. Investors receive up to 20% ROI over two years through dependable monthly or quarterly payments. A moderate-risk plan backed by strong medical service demand and critical imaging needs.','Blue','Monthly or quarterly payments','mdi:chart-bar','2025-11-22 18:38:18','2025-11-23 15:22:06'),(4,'Dialysis Infrastructure Plan','To expand kidney care capacity through the installation of dialysis centers and water treatment systems.',100000.00,900,20.00,'quarterly','Moderate','This plan expands kidney care capacity by funding dialysis center installations and water treatment systems. Hospitals repay using revenue generated from consistent patient treatment cycles. Investors earn 18–22% profit over 30 months, supported by strong market demand and rising chronic kidney disease care needs. A sustainable moderate-growth option driven by essential life-saving services.','Blue','Quarterly payments with inflation-adjusted escalation clause','mdi:water','2025-11-22 18:38:18','2025-11-23 15:22:06'),(5,'Complete Operating Room Equipment Plan','To establish modern operating theatres equipped for advanced surgical operations in partner hospitals.',150000.00,1095,22.50,'monthly','Moderate','A high-value investment that finances complete operating room equipment for advanced surgical procedures. Hospitals repay using surgical revenue streams while generating up to 25% profit over three years. The plan includes optional early payment features for increased liquidity and control. Ideal for investors seeking consistent returns from an essential, high-demand healthcare service sector.','Blue','Monthly or quarterly with partial early payment options','mdi:needle','2025-11-22 18:38:18','2025-11-23 15:22:06'),(6,'Hospital Diagnostic Wing Installation Plan','To construct and equip an entire hospital diagnostic and imaging wing, combining MRI, CT, X-ray, ultrasound, and lab systems.',500000.00,1095,29.00,'quarterly','Moderate-Low','An elite infrastructure plan for institutional investors funding full diagnostic wings, including MRI, CT, X-ray, ultrasound, and lab systems. Revenue from multi-department diagnostic services ensures robust repayment contracts. Investors earn up to 30% returns over three years, backed by large-scale, long-term facility agreements. A premium opportunity for substantial impact, high demand coverage, and strong capital growth.','Green','Quarterly or bi-annual','mdi:hospital-building','2025-11-22 18:38:18','2025-11-23 15:22:06');
/*!40000 ALTER TABLE `infrastructure_plans` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `investment_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `investment_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `investment_plans` WRITE;
/*!40000 ALTER TABLE `investment_plans` DISABLE KEYS */;
INSERT INTO `investment_plans` VALUES (1,'Stable Arc Bond',11.00,540,'Quarterly or at maturity',500.00,100000.00,'2025-11-22 12:22:52','Build Community Diagnostic Centers','Supporting local health screenings and medical supplies for underserved communities.','Low','active','Fees from diagnostic tests and partnerships with hospitals & insurance providers','Your money helps build diagnostic centers that earn from medical tests. As these centers generate consistent service income, you earn up to 12% in 18 months — safely and with social impact.','mdi:factory','Green'),(2,'Growth Circuit Plan',16.50,730,'Bi-annual or lump sum at maturity',5000.00,250000.00,'2025-11-22 12:22:52','Build and Lease Wellness & Rehabilitation Facilities','Your investment funds the construction of modern wellness centers leased to physiotherapy clinics, fitness brands, and recovery operators.','Moderate','active','Rental income from wellness centers and long-term lease agreements','You help build wellness facilities that lease to health operators. Rental payments provide steady income that\'s shared with you as up to 18% return in 2 years.','mdi:home-building','Blue'),(3,'Innovation Venture Fund',30.00,1095,'At maturity (end of term)',10000.00,500000.00,'2025-11-22 12:22:52','Support High-Growth Health-Tech Startups','Your capital helps scale innovative startups working on medical devices, biotech research, and digital health technologies.','High','active','Equity profit from startup growth, technology licensing, and company buyouts','You back new health-tech companies. When they grow or get acquired, you share in their success — earning up to 35% within 3 years.','mdi:lightning-bolt','Orange'),(4,'Micro-Yield Community Plan',9.00,360,'At maturity (end of 12 months)',300.00,20000.00,'2025-11-22 12:22:52','Empower Small Health Businesses','This plan provides microloans to rural pharmacies, small clinics, and health workers who repay with fair interest.','Low','active','Loan interest payments from local health entrepreneurs','Your investment gives small loans to trusted healthcare providers. They repay with interest, and you earn up to 10% in just one year — while supporting community care.','mdi:hand-extend','Green'),(5,'Grid Real Estate Plan',15.00,730,'Annual or at maturity',2000.00,200000.00,'2025-11-22 12:22:52','Finance Eco-Friendly Hospital Upgrades','Your investment enables hospitals to install solar systems, energy-saving equipment, and water recycling units.','Moderate','active','Revenue-sharing from hospitals\' reduced energy costs and green subsidies','Hospitals save thousands on electricity and maintenance after green upgrades. Part of those savings is paid back to investors — giving you up to 16% return in 2 years.','mdi:leaf','Blue'),(6,'Sustainable Systems Plan',13.50,540,'Quarterly or at maturity',1000.00,50000.00,'2025-11-22 12:22:52','Strengthen Nutrition and Food Security','This plan funds farm-to-health programs and healthy meal suppliers for hospitals, schools, and wellness institutions.','Moderate','active','Profits from produce sales, supply contracts, and wholesale distribution partnerships','Your money supports healthy food producers who sell to hospitals and schools. As they make profits, you earn up to 15% in 18 months.','mdi:food','Blue'),(7,'Digital Frontier Fund',20.00,730,'Annual or at maturity',2000.00,100000.00,'2025-11-22 12:22:52','Expand Online Health Platforms & Telemedicine','Invest in digital platforms offering remote doctor consultations, e-prescriptions, and mobile diagnostics.','Moderate to High','active','Subscription fees, teleconsultation charges, data partnerships, and health service commissions','You invest in the future of digital healthcare. As more users join and pay for services online, you earn up to 22% return in 2 years — while helping expand access to doctors worldwide.','mdi:phone','Orange'),(8,'testing plan',25.50,365,'maturity',1.00,9999999.00,'2025-11-22 13:24:06','Investment plan description.','Detailed plan features.','low','active','General investment returns.','Summary of the plan.','mdi:chart-line','Blue');
/*!40000 ALTER TABLE `investment_plans` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `investments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `investments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `plan_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `roi_percent` decimal(5,2) DEFAULT '15.00',
  `duration_days` int DEFAULT '30',
  `status` enum('active','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `maturity_date` date DEFAULT NULL,
  `roi_earned` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invest_user` (`user_id`),
  KEY `idx_invest_maturity` (`maturity_date`),
  CONSTRAINT `investments_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `investments` WRITE;
/*!40000 ALTER TABLE `investments` DISABLE KEYS */;
/*!40000 ALTER TABLE `investments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `login_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_type` enum('user','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `ip` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `login_logs` WRITE;
/*!40000 ALTER TABLE `login_logs` DISABLE KEYS */;
INSERT INTO `login_logs` VALUES (1,'user',1,'127.0.0.1','Firefox 147.0 on Windows','Localhost / Internal Network','2026-01-22 08:19:43'),(2,'user',1,'127.0.0.1','Firefox 147.0 on Windows','Localhost / Internal Network','2026-01-22 08:29:01');
/*!40000 ALTER TABLE `login_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `otp` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reset_user` (`user_id`),
  CONSTRAINT `resets_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cash_mailing_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `wallet_deposit_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` enum('secure_exchange','cash_mailing','wire_transfer','local_bank','wallet_address','wallet','system') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `details` json DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','completed','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `idx_user_transaction` (`user_id`),
  KEY `idx_txn_method` (`method`),
  CONSTRAINT `transactions_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,1,'deposit','secure_exchange','{\"method\": \"secure_exchange\", \"provider\": \"nowpayments\", \"initiated_at\": \"2026-01-22 07:21:01\", \"provider_response\": {\"id\": \"6205969903\", \"source\": null, \"order_id\": \"HRC-DEP-6971CFDD673AE-4138\", \"token_id\": \"5490326139\", \"cancel_url\": \"https://healthruncare.com/pages/user/wallet.php?deposit=cancel&ref=HRC-DEP-6971CFDD673AE-4138\", \"created_at\": \"2026-01-22T07:21:04.869Z\", \"updated_at\": \"2026-01-22T07:21:04.869Z\", \"invoice_url\": \"https://nowpayments.io/payment/?iid=6205969903\", \"success_url\": \"https://healthruncare.com/pages/user/wallet.php?deposit=success&ref=HRC-DEP-6971CFDD673AE-4138\", \"pay_currency\": null, \"price_amount\": \"500\", \"is_fixed_rate\": false, \"customer_email\": null, \"price_currency\": \"USD\", \"payout_currency\": null, \"ipn_callback_url\": \"https://healthruncare.com/api/payments/now_webhook.php\", \"collect_user_data\": false, \"order_description\": \"HealthRunCare deposit: HRC-DEP-6971CFDD673AE-4138\", \"partially_paid_url\": null, \"is_fee_paid_by_user\": false}, \"invoice_created_at\": \"2026-01-22 07:21:03\", \"created_invoice_url\": \"https://nowpayments.io/payment/?iid=6205969903\", \"provider_payment_id\": \"6205969903\"}',500.00,'TXH-DEP-6971CFDD673AE-4138','pending','2026-01-22 06:21:01'),(2,1,'deposit','secure_exchange','{\"method\": \"secure_exchange\", \"provider\": \"nowpayments\", \"initiated_at\": \"2026-01-22 07:31:40\", \"provider_response\": {\"id\": \"4778682004\", \"source\": null, \"order_id\": \"HRC-DEP-6971D25CA1793-9010\", \"token_id\": \"5490326139\", \"cancel_url\": \"https://healthruncare.com/pages/user/wallet.php?deposit=cancel&ref=HRC-DEP-6971D25CA1793-9010\", \"created_at\": \"2026-01-22T07:31:44.198Z\", \"updated_at\": \"2026-01-22T07:31:44.198Z\", \"invoice_url\": \"https://nowpayments.io/payment/?iid=4778682004\", \"success_url\": \"https://healthruncare.com/pages/user/wallet.php?deposit=success&ref=HRC-DEP-6971D25CA1793-9010\", \"pay_currency\": null, \"price_amount\": \"600\", \"is_fixed_rate\": false, \"customer_email\": null, \"price_currency\": \"USD\", \"payout_currency\": null, \"ipn_callback_url\": \"https://healthruncare.com/api/payments/now_webhook.php\", \"collect_user_data\": false, \"order_description\": \"HealthRunCare deposit: HRC-DEP-6971D25CA1793-9010\", \"partially_paid_url\": null, \"is_fee_paid_by_user\": false}, \"invoice_created_at\": \"2026-01-22 07:31:42\", \"created_invoice_url\": \"https://nowpayments.io/payment/?iid=4778682004\", \"provider_payment_id\": \"4778682004\"}',600.00,'TXH-DEP-6971D25CA1793-9010','pending','2026-01-22 06:31:40');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `user_impacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_impacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total_contributions` decimal(12,2) DEFAULT '0.00',
  `people_helped` int DEFAULT '0',
  `impact_score` decimal(5,2) DEFAULT '0.00',
  `communities_helped` int DEFAULT '0',
  `packages_funded` int DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `impact_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `user_impacts` WRITE;
/*!40000 ALTER TABLE `user_impacts` DISABLE KEYS */;
INSERT INTO `user_impacts` VALUES (1,1,0.00,0,0.00,0,0,'2025-11-24 07:01:19');
/*!40000 ALTER TABLE `user_impacts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `status` enum('active','disabled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '/assets/images/avatar/default.png',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'mr wayne','mr wayne','aleruchi0987@gmail.com','$2y$10$2QdOIhhezbZknHzwq5tKqeBsLE78JieSHe5DGTalWW.xTN37gxGie','user','active','/assets/images/avatar/default.png','2025-10-27 21:03:23');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `wallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wallets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `balance` decimal(12,2) DEFAULT '0.00',
  `total_deposited` decimal(12,2) DEFAULT '0.00',
  `total_withdrawn` decimal(12,2) DEFAULT '0.00',
  `total_investments` decimal(12,2) DEFAULT '0.00',
  `xweekly_invested` decimal(15,2) NOT NULL DEFAULT '0.00',
  `xshares_invested` decimal(15,2) NOT NULL DEFAULT '0.00',
  `holdlock_savings` decimal(12,2) DEFAULT '0.00',
  `total_earnings` decimal(12,2) DEFAULT '0.00',
  `pending_withdrawals` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `cash_mailing_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `wallet_deposit_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_user_wallet` (`user_id`),
  CONSTRAINT `wallets_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `wallets` WRITE;
/*!40000 ALTER TABLE `wallets` DISABLE KEYS */;
INSERT INTO `wallets` VALUES (1,1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'2025-11-24 07:01:19',NULL,NULL);
/*!40000 ALTER TABLE `wallets` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `xrewards_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `xrewards_orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(15,2) NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `shipping_details` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reference` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `ordered_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `product_id` (`product_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `xrewards_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `xrewards_orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `xrewards_products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `xrewards_orders` WRITE;
/*!40000 ALTER TABLE `xrewards_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `xrewards_orders` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `xrewards_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `xrewards_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `retail_price` decimal(15,2) NOT NULL,
  `reward_price` decimal(15,2) NOT NULL,
  `discount_pct` decimal(5,2) NOT NULL DEFAULT '40.00',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int DEFAULT NULL,
  `status` enum('active','inactive','out_of_stock') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `xrewards_products` WRITE;
/*!40000 ALTER TABLE `xrewards_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `xrewards_products` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `xshares_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `xshares_assets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ticker` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_price` decimal(15,4) DEFAULT NULL,
  `roi_percent` decimal(5,2) NOT NULL,
  `payout_schedule` enum('weekly','monthly','quarterly','maturity') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `duration_days` int DEFAULT NULL,
  `min_amount` decimal(15,2) NOT NULL DEFAULT '100.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `xshares_assets` WRITE;
/*!40000 ALTER TABLE `xshares_assets` DISABLE KEYS */;
INSERT INTO `xshares_assets` VALUES (1,'Tesla Stock','TSLA','Tesla, Inc.',NULL,18.00,'monthly',NULL,500.00,'Own a position in Tesla and earn from one of the world\'s most innovative companies.','active','2026-05-28 10:48:12'),(2,'Meta Shares','META','Meta Platforms, Inc.',NULL,14.00,'monthly',NULL,300.00,'Own a position in Meta and earn from the global leader in social connectivity.','active','2026-05-28 10:48:12');
/*!40000 ALTER TABLE `xshares_assets` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `xshares_holdings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `xshares_holdings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `asset_id` int NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `entry_price` decimal(15,4) DEFAULT NULL,
  `roi_earned` decimal(15,2) NOT NULL DEFAULT '0.00',
  `maturity_date` date DEFAULT NULL,
  `payout_option` enum('periodic','maturity') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'periodic',
  `status` enum('active','matured','unlocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `reference` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `started_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `asset_id` (`asset_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_maturity` (`maturity_date`),
  CONSTRAINT `xshares_holdings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `xshares_holdings_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `xshares_assets` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `xshares_holdings` WRITE;
/*!40000 ALTER TABLE `xshares_holdings` DISABLE KEYS */;
/*!40000 ALTER TABLE `xshares_holdings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `xweekly_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `xweekly_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roi_percent` decimal(5,2) NOT NULL,
  `min_weekly` decimal(15,2) NOT NULL DEFAULT '50.00',
  `max_weekly` decimal(15,2) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `xweekly_plans` WRITE;
/*!40000 ALTER TABLE `xweekly_plans` DISABLE KEYS */;
/*!40000 ALTER TABLE `xweekly_plans` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `xweekly_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `xweekly_programs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `weekly_amount` decimal(15,2) NOT NULL,
  `total_invested` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_earned` decimal(15,2) NOT NULL DEFAULT '0.00',
  `roi_percent` decimal(5,2) NOT NULL,
  `status` enum('active','paused','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `next_debit_date` date NOT NULL,
  `started_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_next_debit` (`next_debit_date`),
  CONSTRAINT `xweekly_programs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `xweekly_programs` WRITE;
/*!40000 ALTER TABLE `xweekly_programs` DISABLE KEYS */;
/*!40000 ALTER TABLE `xweekly_programs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


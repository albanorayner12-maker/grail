-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               12.3.2-MariaDB - MariaDB Server
-- Server OS:                    Win64
-- HeidiSQL Version:             12.17.0.7270
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for grail_db
CREATE DATABASE IF NOT EXISTS `grail_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci */;
USE `grail_db`;

-- Dumping structure for table grail_db.admins
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table grail_db.admins: ~1 rows (approximately)
INSERT INTO `admins` (`id`, `username`, `password_hash`, `name`, `created_at`) VALUES
	(1, 'admin', '$2y$10$wE99L1H6bT7Iu0HpxmYpPeXyE6GvU68vHmWqYx8R2A2V3K4M5mOeq', 'Derayner', '2026-07-25 19:39:33');

-- Dumping structure for table grail_db.grievances
CREATE TABLE IF NOT EXISTS `grievances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(255) DEFAULT 'Anonymous',
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `user_type` varchar(50) DEFAULT 'Other',
  `id_number` varchar(100) DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `incident_date` date NOT NULL,
  `priority` varchar(50) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `evidence` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `tracking_token` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_token` (`tracking_token`),
  KEY `idx_tracking` (`tracking_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table grail_db.grievances: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

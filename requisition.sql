-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 09, 2025 at 07:50 PM
-- Server version: 8.0.43-cll-lve
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gateweyc_requisition`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int NOT NULL,
  `requisition_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `old_value` text COLLATE utf8mb4_unicode_ci,
  `new_value` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 20:00:32'),
(2, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 20:11:27'),
(3, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 20:14:14'),
(4, NULL, 5, 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 21:08:48'),
(5, NULL, 6, 'user_updated', 'User account updated', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 21:09:01'),
(6, NULL, 5, 'user_updated', 'User account updated', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 21:12:12'),
(7, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 08:37:31'),
(8, NULL, 8, 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 10:00:26'),
(9, NULL, 8, 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 10:00:40'),
(10, NULL, 8, 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 10:01:02'),
(11, NULL, 8, 'user_updated', 'User account updated', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 13:01:40'),
(12, NULL, 8, 'user_updated', 'User account updated', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 13:05:27'),
(13, NULL, 8, 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 13:05:33'),
(14, NULL, 8, 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 13:06:55'),
(15, NULL, 8, 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 13:08:38'),
(16, NULL, 8, 'user_updated', 'User account updated', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 13:09:27'),
(17, NULL, 8, 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 13:09:41'),
(18, NULL, 8, 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 13:10:42'),
(19, NULL, 7, 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 13:11:50'),
(20, NULL, 10, 'user_created', 'User account created: nnanamadumere@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 13:20:44'),
(21, NULL, 10, 'user_password_changed', 'Password changed for user: nnanamadumere@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:02:23'),
(22, NULL, 1, 'backup_created', 'Database backup created: backup_requisition_2025-11-25_15-27-37.sql', NULL, NULL, '::1', NULL, '2025-11-25 14:27:37'),
(23, NULL, 1, 'backup_created', 'Database backup created: backup_requisition_2025-11-25_15-42-43.sql', NULL, NULL, '::1', NULL, '2025-11-25 14:42:43'),
(24, NULL, 1, 'backup_created', 'Database backup created: backup_requisition_2025-11-25_15-44-20.sql', NULL, NULL, '::1', NULL, '2025-11-25 14:44:20'),
(25, NULL, 1, 'backup_deleted', 'Database backup deleted: backup_requisition_2025-11-25_15-44-20.sql', NULL, NULL, '::1', NULL, '2025-11-25 14:44:24'),
(26, NULL, 1, 'backup_deleted', 'Database backup deleted: backup_requisition_2025-11-25_15-42-43.sql', NULL, NULL, '::1', NULL, '2025-11-25 14:44:26'),
(27, NULL, 1, 'backup_deleted', 'Database backup deleted: backup_requisition_2025-11-25_15-42-30.sql', NULL, NULL, '::1', NULL, '2025-11-25 14:44:28'),
(28, NULL, 1, 'backup_deleted', 'Database backup deleted: backup_requisition_2025-11-25_15-42-22.sql', NULL, NULL, '::1', NULL, '2025-11-25 14:45:07'),
(29, NULL, 1, 'backup_deleted', 'Database backup deleted: backup_requisition_2025-11-25_15-26-02.sql', NULL, NULL, '::1', NULL, '2025-11-25 14:45:09'),
(30, NULL, 1, 'backup_deleted', 'Database backup deleted: backup_requisition_2025-11-25_15-25-55.sql', NULL, NULL, '::1', NULL, '2025-11-25 14:45:11'),
(31, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 15:03:14'),
(32, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 15:04:06'),
(33, 1, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 15:19:00'),
(34, 2, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 15:21:40'),
(35, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 15:30:48'),
(36, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 15:31:00'),
(37, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 15:31:56'),
(38, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 15:32:13'),
(39, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:10:30'),
(40, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:11:17'),
(41, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:11:24'),
(42, NULL, 5, 'user_password_changed', 'Password changed for user: it.manager@gatewey.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:12:10'),
(43, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:12:23'),
(44, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:12:33'),
(45, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:45:38'),
(46, 2, 10, 'requisition_updated', 'Requisition resubmitted after revision', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:47:01'),
(47, 2, 10, 'attachment_uploaded', 'File uploaded: Screenshot 2025-10-17 104333.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:47:01'),
(48, 2, 5, 'document_viewed', 'Document viewed: Screenshot 2025-10-17 104333.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:50:38'),
(49, 2, 10, 'document_viewed', 'Document viewed: Screenshot 2025-10-17 104333.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:51:08'),
(50, 2, 10, 'requisition_updated', 'Requisition updated and saved as draft', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:51:32'),
(51, 2, 10, 'document_viewed', 'Document viewed: Screenshot 2025-10-17 104333.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:51:37'),
(52, 2, 10, 'requisition_updated', 'Requisition resubmitted after revision', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:51:57'),
(53, 2, 10, 'attachment_uploaded', 'File uploaded: Screenshot 2025-10-13 142437.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:51:57'),
(54, 2, 5, 'document_viewed', 'Document viewed: Screenshot 2025-10-13 142437.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:52:26'),
(55, 2, 5, 'document_viewed', 'Document viewed: Screenshot 2025-10-13 142437.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 19:52:31'),
(56, 2, 5, 'document_viewed', 'Document viewed: Screenshot 2025-10-13 142437.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:01:17'),
(57, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:02:16'),
(58, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:02:25'),
(59, NULL, 2, 'user_password_changed', 'Password changed for user: md@gatewey.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:02:55'),
(60, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:03:06'),
(61, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:03:10'),
(62, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:14:58'),
(63, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:15:08'),
(64, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:16:32'),
(65, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:16:50'),
(66, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:17:02'),
(67, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:17:07'),
(68, NULL, 3, 'user_password_changed', 'Password changed for user: finance.manager@gatewey.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:17:29'),
(69, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:17:37'),
(70, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:17:47'),
(71, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:25:32'),
(72, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:25:45'),
(73, NULL, 4, 'user_password_changed', 'Password changed for user: finance.member@gatewey.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:26:09'),
(74, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:26:17'),
(75, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:26:22'),
(76, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 08:43:59'),
(77, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 08:44:27'),
(78, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 08:44:33'),
(86, 2, 4, 'invoice_uploaded', 'File uploaded: NotebookLM Mind Map.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 09:26:09'),
(87, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 09:30:35'),
(88, 2, 10, 'document_viewed', 'Document viewed: NotebookLM Mind Map.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 09:30:49'),
(89, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 09:31:36'),
(90, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 09:31:58'),
(91, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 09:32:08'),
(92, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 09:32:22'),
(93, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 09:32:35'),
(94, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 09:32:41'),
(95, NULL, 4, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 10:36:02'),
(96, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 10:36:13'),
(97, 2, 10, 'document_viewed', 'Document viewed: NotebookLM Mind Map.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 10:58:47'),
(100, 2, 10, 'receipt_uploaded', 'File uploaded: Madumere Peter.jpg', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 11:05:53'),
(101, 2, 10, 'document_viewed', 'Document viewed: Madumere Peter.jpg', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 11:10:13'),
(102, 2, 10, 'document_viewed', 'Document viewed: NotebookLM Mind Map.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 11:10:19'),
(103, 2, 10, 'document_viewed', 'Document viewed: Screenshot 2025-10-13 142437.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 11:10:21'),
(104, 2, 10, 'document_viewed', 'Document viewed: Screenshot 2025-10-13 142437.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 11:10:24'),
(105, 3, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 11:14:21'),
(106, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:18:23'),
(107, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:28:28'),
(108, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:28:46'),
(109, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:37:55'),
(110, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:38:03'),
(111, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:38:54'),
(112, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:39:02'),
(113, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:39:15'),
(114, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:39:42'),
(115, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:47:28'),
(116, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:47:37'),
(117, NULL, 4, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:56:59'),
(118, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:57:15'),
(119, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:36:12'),
(120, 3, 10, 'requisition_updated', 'Requisition resubmitted after revision', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:47:49'),
(121, 3, 10, 'attachment_uploaded', 'File uploaded: NotebookLM Mind Map.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:47:49'),
(122, 3, 10, 'document_viewed', 'Document viewed: NotebookLM Mind Map.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:48:04'),
(123, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:48:39'),
(124, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:48:50'),
(125, 3, 10, 'document_viewed', 'Document viewed: NotebookLM Mind Map.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:56:15'),
(126, 3, 10, 'requisition_updated', 'Requisition resubmitted after revision', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:56:22'),
(127, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:21:05'),
(128, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:21:15'),
(129, 3, 10, 'document_viewed', 'Document viewed: NotebookLM Mind Map.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:38:10'),
(130, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:40:37'),
(131, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:40:46'),
(132, 3, 3, 'document_viewed', 'Document viewed: NotebookLM Mind Map.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:47:43'),
(133, 3, 10, 'requisition_updated', 'Requisition resubmitted after revision', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:49:20'),
(134, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:50:10'),
(135, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:50:21'),
(136, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:50:50'),
(137, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:50:56'),
(138, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:53:32'),
(139, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:53:40'),
(140, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:59:35'),
(141, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:59:42'),
(142, 3, 4, 'invoice_uploaded', 'File uploaded: c69e5b82-ecca-4c80-93c7-b07fd952cbd5.jpg', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:06:21'),
(143, 3, 10, 'document_viewed', 'Document viewed: c69e5b82-ecca-4c80-93c7-b07fd952cbd5.jpg', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:38:36'),
(144, 3, 10, 'receipt_uploaded', 'File uploaded: Screenshot 2025-10-13 154255.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:38:52'),
(145, 3, 10, 'document_viewed', 'Document viewed: Screenshot 2025-10-13 154255.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:39:15'),
(146, 3, 10, 'document_viewed', 'Document viewed: c69e5b82-ecca-4c80-93c7-b07fd952cbd5.jpg', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:39:19'),
(147, 3, 10, 'document_viewed', 'Document viewed: NotebookLM Mind Map.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:39:21'),
(148, NULL, 4, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:40:12'),
(149, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:40:22'),
(150, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:10:06'),
(151, 4, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:28:03'),
(152, 4, 10, 'attachment_uploaded', 'File uploaded: Screenshot 2025-10-18 185842.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:28:03'),
(153, 4, 10, 'document_viewed', 'Document viewed: Screenshot 2025-10-18 185842.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:28:14'),
(154, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:29:36'),
(155, 5, 5, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:30:48'),
(156, 4, 10, 'requisition_updated', 'Requisition resubmitted after revision', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:39:26'),
(157, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:48:10'),
(158, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:48:21'),
(159, 5, 5, 'requisition_cancelled', 'Requisition cancelled by requester', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:51:47'),
(160, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:53:37'),
(161, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:53:46'),
(162, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:56:04'),
(163, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:56:11'),
(164, 4, 4, 'invoice_uploaded', 'File uploaded: ZW_Full color logo2.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:58:28'),
(165, NULL, 4, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:59:32'),
(166, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 20:59:44'),
(167, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 21:16:19'),
(168, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 21:16:27'),
(169, 4, 10, 'document_viewed', 'Document viewed: ZW_Full color logo2.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 21:21:56'),
(170, 4, 10, 'receipt_uploaded', 'File uploaded: c69e5b82-ecca-4c80-93c7-b07fd952cbd5.jpg', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 21:22:08'),
(171, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 19:44:36'),
(172, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:40:32'),
(173, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:43:34'),
(174, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:43:50'),
(175, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 19:39:53'),
(176, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 11:57:35'),
(177, 6, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 12:01:42'),
(178, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 12:03:50'),
(179, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 12:03:57'),
(180, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 12:18:29'),
(181, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 12:18:42'),
(182, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 12:21:57'),
(183, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 12:22:04'),
(184, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 12:27:37'),
(185, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 12:27:47'),
(186, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 12:28:20'),
(187, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 12:28:24'),
(188, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 12:58:45'),
(189, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 13:07:31'),
(190, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 13:07:41'),
(191, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 13:18:16'),
(192, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 13:18:23'),
(193, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 13:25:04'),
(194, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 13:25:16'),
(195, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 13:30:35'),
(196, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 13:30:49'),
(197, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 13:40:54'),
(198, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 13:41:02'),
(199, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 17:37:21'),
(200, 7, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 17:44:52'),
(201, 8, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 18:10:05'),
(202, 9, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 18:24:05'),
(203, 10, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 18:26:29'),
(204, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 18:26:50'),
(205, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 18:27:00'),
(206, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 18:28:26'),
(207, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 18:28:37'),
(208, 9, 10, 'requisition_updated', 'Requisition resubmitted after revision', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 18:29:29'),
(209, 9, 10, 'attachment_uploaded', 'File uploaded: Screenshot 2025-10-15 152920.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 18:29:29'),
(210, 8, 10, 'requisition_updated', 'Requisition resubmitted after revision', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 18:29:57'),
(211, 7, 10, 'requisition_updated', 'Requisition resubmitted after revision', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 18:30:10'),
(212, 6, 10, 'requisition_updated', 'Requisition resubmitted after revision', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 18:30:32'),
(213, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 18:40:50'),
(214, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 18:40:59'),
(215, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 19:10:37'),
(216, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 19:10:45'),
(217, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 19:12:06'),
(218, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 19:35:24'),
(219, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 19:35:33'),
(220, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:10:02'),
(221, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:10:12'),
(222, 11, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:10:50'),
(223, 11, 10, 'attachment_uploaded', 'File uploaded: Screenshot 2025-10-15 152929.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:10:50'),
(224, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:11:02'),
(225, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:11:08'),
(226, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:11:56'),
(227, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:12:06'),
(228, 11, 10, 'requisition_updated', 'Requisition resubmitted after revision', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:12:27'),
(229, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:12:43'),
(230, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:12:48'),
(231, 11, 5, 'document_viewed', 'Document viewed: Screenshot 2025-10-15 152929.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:13:03'),
(232, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:13:29'),
(233, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:13:39'),
(234, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:14:03'),
(235, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:14:30'),
(236, 11, 2, 'document_viewed', 'Document viewed: Screenshot 2025-10-15 152929.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:14:43'),
(237, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:14:51'),
(238, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:14:58'),
(239, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:15:33');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(240, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:15:36'),
(241, 11, 4, 'invoice_uploaded', 'File uploaded: Screenshot 2025-10-13 154843.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:16:03'),
(242, NULL, 4, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:16:08'),
(243, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:16:18'),
(244, 11, 10, 'receipt_uploaded', 'File uploaded: Screenshot 2025-10-13 142437.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:16:48'),
(245, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:19:25'),
(246, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:19:37'),
(247, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:20:50'),
(248, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:21:00'),
(249, NULL, 4, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:21:51'),
(250, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:21:55'),
(251, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:22:34'),
(252, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:23:06'),
(253, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:23:21'),
(254, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:23:24'),
(255, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:23:43'),
(256, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:23:48'),
(257, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:26:43'),
(258, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:26:59'),
(259, 11, 10, 'document_viewed', 'Document viewed: Screenshot 2025-10-13 142437.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:36:15'),
(260, 11, 10, 'document_viewed', 'Document viewed: Screenshot 2025-10-13 142437.png', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:36:34'),
(261, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:37:22'),
(262, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:37:31'),
(263, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:37:38'),
(264, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:37:43'),
(265, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:37:55'),
(266, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:38:00'),
(267, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:38:15'),
(268, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:38:19'),
(269, NULL, 4, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:42:41'),
(270, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:42:57'),
(271, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:43:30'),
(272, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 20:44:10'),
(273, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 20:01:47'),
(274, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 20:05:11'),
(275, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 20:07:47'),
(276, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 20:08:20'),
(277, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 21:47:35'),
(278, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 21:47:48'),
(279, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 21:51:07'),
(280, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 21:51:24'),
(281, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 21:54:12'),
(282, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 21:54:26'),
(283, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 21:59:07'),
(284, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-04 21:59:24'),
(285, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-05 09:20:06'),
(286, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 09:13:55'),
(287, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 09:25:33'),
(288, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 09:26:08'),
(289, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-07 09:36:08'),
(290, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 09:51:49'),
(291, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 09:52:30'),
(292, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 09:52:45'),
(293, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 09:54:55'),
(294, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 09:55:01'),
(295, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 09:55:16'),
(296, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 09:55:27'),
(297, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 10:08:12'),
(298, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 10:08:25'),
(299, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 10:11:01'),
(300, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 10:11:23'),
(301, NULL, 4, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 10:14:17'),
(302, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 10:14:35'),
(303, 0, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 10:33:00'),
(304, 0, 10, 'requisition_created', 'Requisition saved as draft', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 10:33:35'),
(305, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 10:48:15'),
(306, 23, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 10:59:33'),
(307, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:02:59'),
(308, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:03:31'),
(309, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:17:53'),
(310, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:18:05'),
(311, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:21:54'),
(312, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:22:05'),
(313, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:22:57'),
(314, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:23:02'),
(315, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:23:43'),
(316, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:23:54'),
(317, 23, 4, 'invoice_uploaded', 'File uploaded: Screenshot 2025-10-14 205003.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:29:31'),
(318, NULL, 4, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:36:21'),
(319, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:36:40'),
(320, 23, 10, 'receipt_uploaded', 'File uploaded: Screenshot 2025-10-20 100631.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:42:02'),
(321, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:43:33'),
(322, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:43:44'),
(323, 23, 4, 'document_viewed', 'Document viewed: Screenshot 2025-10-20 100631.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:52:06'),
(324, 23, 4, 'document_viewed', 'Document viewed: Screenshot 2025-10-14 205003.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 11:52:11'),
(588, 23, 4, 'document_viewed', 'Document viewed: Screenshot 2025-10-20 100631.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 12:23:50'),
(589, 9, 4, 'invoice_uploaded', 'File uploaded: Screenshot 2025-10-13 142230.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 12:50:53'),
(590, NULL, 4, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 12:53:45'),
(591, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 12:54:00'),
(592, NULL, 11, 'user_created', 'User account created: peter@gamil.com', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 15:38:12'),
(593, NULL, 11, 'user_updated', 'User account updated', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:04:52'),
(594, NULL, 11, 'user_updated', 'User account updated', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:05:23'),
(595, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:05:45'),
(596, NULL, 11, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:05:58'),
(597, NULL, 11, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:12:42'),
(598, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:12:52'),
(599, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:17:53'),
(600, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:18:13'),
(601, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:48:31'),
(602, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:48:39'),
(603, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:49:59'),
(604, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:50:14'),
(605, NULL, 10, 'user_updated', 'User account updated', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:53:42'),
(606, NULL, 10, 'user_password_changed', 'Password changed for user: nnana@gmail.com', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:54:23'),
(607, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:54:48'),
(608, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:55:14'),
(609, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 16:57:17'),
(610, NULL, 12, 'user_created', 'User account created: mary@gmail.com', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 17:01:13'),
(611, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 17:06:03'),
(612, NULL, 12, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 17:06:19'),
(613, 24, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-07 18:56:26'),
(614, 25, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-07 19:05:01'),
(615, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-07 19:15:10'),
(616, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-07 19:26:21'),
(617, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-07 19:49:44'),
(618, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 20:06:39'),
(619, NULL, 12, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 20:06:51'),
(620, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 20:07:25'),
(621, 25, 12, 'invoice_uploaded', 'File uploaded: Screenshot 2025-10-15 231829.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 20:08:10'),
(622, 25, 10, 'document_viewed', 'Document viewed: Screenshot 2025-10-15 231829.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 20:08:30'),
(623, 25, 10, 'receipt_uploaded', 'File uploaded: Screenshot 2025-10-17 095526.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 20:08:49'),
(624, 25, 10, 'document_viewed', 'Document viewed: Screenshot 2025-10-17 095526.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 20:08:55'),
(625, NULL, 12, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 20:11:31'),
(626, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 20:11:41'),
(627, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 20:13:02'),
(628, NULL, 4, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 20:13:15'),
(629, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.89.69.242', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-08 07:37:07'),
(630, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '98.97.77.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-08 07:40:11'),
(631, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '98.97.77.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-08 08:04:51'),
(632, NULL, 12, 'user_logout', 'User logged out', NULL, NULL, '98.97.77.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 08:07:23'),
(633, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '98.97.77.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 08:07:31'),
(634, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '98.97.77.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 09:33:58'),
(635, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '98.97.77.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 09:34:06'),
(636, NULL, 4, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-08 10:04:22'),
(637, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-08 10:04:36'),
(638, 26, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 10:55:04'),
(639, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 10:55:46'),
(640, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 10:56:45'),
(641, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 11:01:14'),
(642, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 11:06:48'),
(643, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 11:12:09'),
(644, NULL, 12, 'user_login', 'User logged in successfully', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 11:12:20'),
(645, 26, 12, 'invoice_uploaded', 'File uploaded: CLEANING 21.01.2025.pdf', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 11:15:16'),
(646, NULL, 12, 'user_logout', 'User logged out', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 11:17:20'),
(647, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 11:17:26'),
(648, 26, 10, 'receipt_uploaded', 'File uploaded: CLEANING 21.01.2025.pdf', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 11:19:46'),
(649, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 11:24:05'),
(650, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '41.86.151.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 11:51:06'),
(651, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '41.86.151.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 11:57:59'),
(652, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '41.86.151.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 12:47:30'),
(653, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '41.86.151.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 12:47:36'),
(654, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 13:27:51'),
(655, 27, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 13:29:06'),
(656, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 13:29:23'),
(657, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 13:29:48'),
(658, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 13:29:59'),
(659, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.89.69.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 13:30:04'),
(660, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.90.97.223', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-12-08 13:51:37'),
(661, 26, 4, 'document_viewed', 'Document viewed: CLEANING 21.01.2025.pdf', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-08 14:55:29'),
(662, 26, 4, 'document_viewed', 'Document viewed: CLEANING 21.01.2025.pdf', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-08 14:55:47'),
(663, 26, 4, 'document_viewed', 'Document viewed: CLEANING 21.01.2025.pdf', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-08 14:56:01'),
(664, 28, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '143.105.174.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 08:50:32'),
(665, 28, 10, 'attachment_uploaded', 'File uploaded: Screenshot 2025-10-13 154255.png', NULL, NULL, '143.105.174.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 08:50:32'),
(666, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '143.105.174.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 08:50:46'),
(667, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '143.105.174.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 08:50:58'),
(668, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '143.105.174.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 08:51:31'),
(669, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '143.105.174.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 09:04:58'),
(670, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '143.105.174.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 09:05:08'),
(671, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '143.105.174.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 09:15:03'),
(672, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '143.105.174.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 09:15:26'),
(673, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '143.105.174.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 09:16:19'),
(674, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '143.105.174.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 15:47:34'),
(675, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '143.105.174.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 15:47:39');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int NOT NULL,
  `department_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `budget` decimal(15,2) DEFAULT '0.00',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `department_name`, `department_code`, `description`, `budget`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Information Technology', 'IT', 'Manages all technology infrastructure, software development, and IT support services', 5000000.00, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(2, 'Human Resources', 'HR', 'Handles recruitment, employee relations, training, and HR administration', 2000000.00, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(3, 'Finance & Accounting', 'FIN', 'Manages financial operations, accounting, budgeting, and financial reporting', 3000000.00, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(4, 'Marketing & Sales', 'MKT', 'Drives business growth through marketing campaigns, sales strategies, and customer acquisition', 4000000.00, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(5, 'Operations', 'OPS', 'Oversees day-to-day business operations, logistics, and process improvements', 3500000.00, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(6, 'Administration', 'ADM', 'Provides general administrative support and office management', 1500000.00, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(7, 'Biology', 'BY', 'Very Good Department', 0.00, 1, '2025-11-24 21:10:40', '2025-11-24 21:10:40'),
(9, 'Business Support', 'BST', 'provides essential administrative, operational, and logistical assistance to ensure a company runs smoothly, handling tasks like scheduling, data management, reporting, coordinating teams, and managing facilities, allowing core teams to focus on primary objectives and growth.', 0.00, 1, '2025-12-07 15:40:28', '2025-12-07 15:40:28');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `requisition_id` int DEFAULT NULL,
  `notification_type` enum('requisition_submitted','requisition_approved','requisition_rejected','requisition_paid','receipt_uploaded','action_required','requisition_cancelled') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `is_email_sent` tinyint(1) DEFAULT '0',
  `email_sent_at` timestamp NULL DEFAULT NULL,
  `email_error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_used` tinyint(1) DEFAULT '0',
  `used_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `is_used`, `used_at`, `ip_address`, `created_at`) VALUES
(1, 1, 'acd36621c44a8be7456df6bb8e7dce6ee4bc8ceaf24b6011a674c6b61914850e', '2025-11-24 15:56:58', 0, NULL, '::1', '2025-11-24 14:56:58'),
(2, 1, 'aa90ecfd98165fe432fa100ba0219077fcf19371e738892c62d820676d1c8268', '2025-11-24 15:57:37', 0, NULL, '::1', '2025-11-24 14:57:37');

-- --------------------------------------------------------

--
-- Table structure for table `requisitions`
--

CREATE TABLE `requisitions` (
  `id` int NOT NULL,
  `requisition_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `department_id` int NOT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Additional information or notes about the requisition',
  `total_amount` decimal(15,2) NOT NULL,
  `status` enum('draft','pending_line_manager','pending_md','pending_finance_manager','approved_for_payment','paid','completed','rejected','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `current_approver_id` int DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `rejected_by_id` int DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_notes` text COLLATE utf8mb4_unicode_ci,
  `receipt_uploaded` tinyint(1) DEFAULT '0',
  `receipt_uploaded_at` timestamp NULL DEFAULT NULL,
  `is_draft` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `paid_by` int DEFAULT NULL,
  `receipt_file_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_notes` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requisitions`
--

INSERT INTO `requisitions` (`id`, `requisition_number`, `user_id`, `department_id`, `purpose`, `description`, `total_amount`, `status`, `current_approver_id`, `rejection_reason`, `rejected_by_id`, `rejected_at`, `payment_date`, `payment_method`, `payment_reference`, `payment_notes`, `receipt_uploaded`, `receipt_uploaded_at`, `is_draft`, `created_at`, `updated_at`, `submitted_at`, `paid_by`, `receipt_file_path`, `receipt_file_name`, `receipt_notes`) VALUES
(1, 'REQ00001', 10, 1, 'for toilet', 'testing', 7000.00, 'pending_md', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-11-26 15:19:00', '2025-12-02 17:43:23', '2025-11-26 15:19:00', NULL, NULL, NULL, NULL),
(2, 'REQ00002', 10, 1, 'REQUISITION Definition; Meaning', NULL, 22000.00, 'completed', 4, NULL, NULL, NULL, '2025-11-27 09:26:09', 'Bank Transfer', 'torkks234ksiifks', '', 0, '2025-11-27 11:05:53', 0, '2025-11-26 15:21:40', '2025-11-27 11:05:53', '2025-11-26 19:51:57', 4, NULL, NULL, ''),
(3, 'REQ00003', 10, 1, 'Drink water', NULL, 9200.00, 'completed', 4, NULL, NULL, NULL, '2025-11-27 16:06:21', 'Credit Card', 'torkks234ksiifks11', 'this is been actioned', 0, '2025-11-27 16:38:52', 0, '2025-11-27 11:14:21', '2025-11-27 16:38:52', '2025-11-27 15:49:20', 4, NULL, NULL, ''),
(4, 'REQ00004', 10, 1, 'Diesel purchase', NULL, 500000.00, 'completed', 4, NULL, NULL, NULL, '2025-11-27 20:58:28', 'Mobile Payment', 'torkks234ksiifks1243', 'good', 0, '2025-11-27 21:22:08', 0, '2025-11-27 20:28:03', '2025-11-27 21:22:08', '2025-11-27 20:39:26', 4, NULL, NULL, ''),
(5, 'REQ00005', 5, 1, 'Business Travel', NULL, 180000.00, 'cancelled', 5, 'you go no where!!!', 2, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-11-27 20:30:48', '2025-11-27 20:51:47', '2025-11-27 20:30:48', NULL, NULL, NULL, NULL),
(6, 'REQ00006', 10, 1, 'Entertainment', 'now you see me', 64050.00, 'pending_line_manager', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-02 12:01:42', '2025-12-02 18:30:32', '2025-12-02 18:30:32', NULL, NULL, NULL, NULL),
(7, 'REQ00007', 10, 1, 'Miscellaneous', 'this is it', 500.00, 'pending_line_manager', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-02 17:44:52', '2025-12-02 18:30:10', '2025-12-02 18:30:10', NULL, NULL, NULL, NULL),
(8, 'REQ00008', 10, 1, 'Beverage', 'it has been added now', 500000.00, 'pending_md', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-02 18:10:05', '2025-12-07 11:16:49', '2025-12-02 18:29:57', NULL, NULL, NULL, NULL),
(9, 'REQ00009', 10, 1, 'Entertainment', 'it has been added now', 20000.00, 'paid', 4, NULL, NULL, NULL, '2025-12-07 12:50:53', 'Bank Transfer', 'torkks234ksiifks', '', 0, NULL, 0, '2025-12-02 18:24:05', '2025-12-07 12:50:53', '2025-12-02 18:29:29', 4, NULL, NULL, NULL),
(10, 'REQ00010', 10, 1, 'Mobile Telephone', 'data for all', 3999.99, 'pending_md', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-02 18:26:29', '2025-12-02 18:28:21', '2025-12-02 18:26:29', NULL, NULL, NULL, NULL),
(11, 'REQ00011', 10, 1, 'Medical Expenses', 'sfdsdsdsdsd', 22000.00, 'completed', 4, NULL, NULL, NULL, '2025-12-02 20:16:03', 'Bank Transfer', 'torkks234ksiifks', '', 0, '2025-12-02 20:16:48', 0, '2025-12-02 20:10:50', '2025-12-02 20:16:48', '2025-12-02 20:12:27', 4, NULL, NULL, ''),
(23, 'REQ00012', 10, 1, 'Assets', 'Computers for the Office', 765000.00, 'completed', 4, NULL, NULL, NULL, '2025-12-07 11:29:31', 'Bank Transfer', 'torkkss234ksiifks1243', '', 0, '2025-12-07 11:42:02', 0, '2025-12-07 10:59:33', '2025-12-07 11:42:02', '2025-12-07 10:59:33', 4, NULL, NULL, ''),
(24, 'REQ00024', 10, 1, 'Cleaning', 'cleaning items', 4800.00, 'pending_line_manager', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-07 18:56:26', '2025-12-07 18:56:26', '2025-12-07 18:56:26', NULL, NULL, NULL, NULL),
(25, 'REQ00025', 10, 1, 'Cleaning', 'Cleaning Items', 19600.00, 'completed', 4, NULL, NULL, NULL, '2025-12-07 20:08:10', 'Bank Transfer', 'torkks234ksiifkssss', '', 0, '2025-12-07 20:08:49', 0, '2025-12-07 19:05:01', '2025-12-07 20:08:49', '2025-12-07 19:05:01', 12, NULL, NULL, ''),
(26, 'REQ00026', 10, 1, 'R.M. Building', 'REPAIR OF TOILET DOOR', 9000.00, 'completed', 4, NULL, NULL, NULL, '2025-12-08 11:15:16', 'Bank Transfer', 'TNC', 'FIND RECEIPT FOR PAYMENT', 0, '2025-12-08 11:19:46', 0, '2025-12-08 10:55:04', '2025-12-08 11:19:46', '2025-12-08 10:55:04', 12, NULL, NULL, ''),
(27, 'REQ00027', 10, 1, 'Transportation', 'TRANSPORT TO VI', 20000.00, 'pending_line_manager', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-08 13:29:06', '2025-12-08 13:29:06', '2025-12-08 13:29:06', NULL, NULL, NULL, NULL),
(28, 'REQ00028', 10, 1, 'Assets', '', 5000.00, 'pending_line_manager', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-09 08:50:32', '2025-12-09 08:50:32', '2025-12-09 08:50:32', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `requisition_approvals`
--

CREATE TABLE `requisition_approvals` (
  `id` int NOT NULL,
  `requisition_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role_at_approval` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` enum('approved','rejected','returned') COLLATE utf8mb4_unicode_ci NOT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requisition_approvals`
--

INSERT INTO `requisition_approvals` (`id`, `requisition_id`, `user_id`, `role_at_approval`, `action`, `comments`, `ip_address`, `created_at`) VALUES
(1, 2, 5, 'Line Manager', 'rejected', 'dddddddddddddddddddddddddddddd', NULL, '2025-11-26 19:44:58'),
(2, 1, 5, 'Line Manager', 'approved', '', NULL, '2025-11-26 19:45:06'),
(3, 2, 5, 'Line Manager', 'rejected', 'add another better looking recept', NULL, '2025-11-26 19:51:02'),
(4, 2, 5, 'Line Manager', 'approved', '', NULL, '2025-11-26 20:15:22'),
(5, 2, 2, 'Managing Director', 'approved', '', NULL, '2025-11-26 20:16:03'),
(6, 2, 3, 'Finance Manager', 'approved', 'noted', NULL, '2025-11-26 20:23:52'),
(7, 3, 5, 'Line Manager', 'approved', '', NULL, '2025-11-27 13:38:47'),
(8, 3, 2, 'Managing Director', 'rejected', 'this is just a test', NULL, '2025-11-27 14:25:46'),
(9, 3, 5, 'Line Manager', 'rejected', 'this need more refinements', NULL, '2025-11-27 14:51:48'),
(10, 3, 5, 'Line Manager', 'approved', 'good to go for now', NULL, '2025-11-27 15:20:52'),
(11, 3, 2, 'Managing Director', 'approved', 'this is good to fly', NULL, '2025-11-27 15:38:38'),
(12, 3, 3, 'Finance Manager', 'rejected', 'the budget we have is not enough please revisit and cut cost', NULL, '2025-11-27 15:48:22'),
(13, 3, 5, 'Line Manager', 'approved', 'this is good now', NULL, '2025-11-27 15:50:34'),
(14, 3, 2, 'Managing Director', 'approved', 'looks okay', NULL, '2025-11-27 15:53:10'),
(15, 3, 3, 'Finance Manager', 'approved', 'good to fly we have the budget now', NULL, '2025-11-27 15:59:30'),
(16, 4, 5, 'Line Manager', 'rejected', 'reduce it to 500k', NULL, '2025-11-27 20:31:24'),
(17, 5, 2, 'Managing Director', 'rejected', 'you go no where!!!', NULL, '2025-11-27 20:51:08'),
(18, 4, 5, 'Line Manager', 'approved', '', NULL, '2025-11-27 20:51:24'),
(19, 4, 2, 'Managing Director', 'approved', '', NULL, '2025-11-27 20:52:29'),
(20, 4, 3, 'Finance Manager', 'approved', 'good to go', NULL, '2025-11-27 20:55:56'),
(21, 6, 5, 'Line Manager', 'rejected', 'update the description', NULL, '2025-12-02 18:27:32'),
(22, 7, 5, 'Line Manager', 'rejected', 'add description', NULL, '2025-12-02 18:27:46'),
(23, 8, 5, 'Line Manager', 'rejected', 'add description', NULL, '2025-12-02 18:28:00'),
(24, 9, 5, 'Line Manager', 'rejected', 'add description', NULL, '2025-12-02 18:28:09'),
(25, 10, 5, 'Line Manager', 'approved', 'good', NULL, '2025-12-02 18:28:21'),
(26, 11, 5, 'Line Manager', 'rejected', 'rejected for now', NULL, '2025-12-02 20:11:49'),
(27, 11, 5, 'Line Manager', 'approved', 'approved', NULL, '2025-12-02 20:13:13'),
(28, 11, 2, 'Managing Director', 'approved', '', NULL, '2025-12-02 20:14:47'),
(29, 11, 3, 'Finance Manager', 'approved', '', NULL, '2025-12-02 20:15:29'),
(30, 9, 5, 'Line Manager', 'approved', '', NULL, '2025-12-02 20:37:35'),
(31, 9, 2, 'Managing Director', 'approved', '', NULL, '2025-12-02 20:37:52'),
(32, 9, 3, 'Finance Manager', 'approved', '', NULL, '2025-12-02 20:38:06'),
(33, 8, 5, 'Line Manager', 'approved', '', NULL, '2025-12-07 11:16:49'),
(34, 23, 5, 'Line Manager', 'approved', '', NULL, '2025-12-07 11:17:02'),
(35, 23, 2, 'Managing Director', 'approved', 'good to fly', NULL, '2025-12-07 11:22:50'),
(36, 23, 3, 'Finance Manager', 'approved', 'good to fly', NULL, '2025-12-07 11:23:20'),
(65, 25, 5, 'Line Manager', 'approved', 'Please Go ahead to disburse', NULL, '2025-12-07 19:24:24'),
(66, 25, 2, 'Managing Director', 'approved', 'APPROVED FOR PAYMENT', NULL, '2025-12-07 19:35:42'),
(67, 25, 3, 'Finance Manager', 'approved', 'PAYMENT DISBURSED', NULL, '2025-12-07 19:48:29'),
(68, 26, 5, 'Line Manager', 'approved', 'KINDLY  APPROVE FOR PAYMENT', NULL, '2025-12-08 10:59:22'),
(69, 26, 2, 'Managing Director', 'approved', 'APPROVE', NULL, '2025-12-08 11:04:46'),
(70, 26, 3, 'Finance Manager', 'approved', 'PROCEED TO PAY', NULL, '2025-12-08 11:10:45');

-- --------------------------------------------------------

--
-- Table structure for table `requisition_categories`
--

CREATE TABLE `requisition_categories` (
  `id` int NOT NULL,
  `category_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `display_order` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requisition_categories`
--

INSERT INTO `requisition_categories` (`id`, `category_name`, `category_code`, `description`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Drink water', 'DRINK_WATER', 'Drinking water and related expenses', 1, 1, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(2, 'Entertainment', 'ENTERTAINMENT', 'Entertainment and hospitality expenses', 1, 2, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(3, 'Business Travel', 'BUSINESS_TRAVEL', 'Business travel and related costs', 1, 3, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(4, 'Transportation', 'TRANSPORTATION', 'Transportation and logistics', 1, 4, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(5, 'Internet facility', 'INTERNET', 'Internet and connectivity services', 1, 5, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(6, 'Cleaning', 'CLEANING', 'Cleaning services and supplies', 1, 6, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(7, 'Waste management', 'WASTE_MGT', 'Waste disposal and management', 1, 7, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(8, 'Electricity', 'ELECTRICITY', 'Electrical power and utilities', 1, 8, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(9, 'Beverage', 'BEVERAGE', 'Beverages and refreshments', 1, 9, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(10, 'Mobile Telephone', 'MOBILE_PHONE', 'Mobile phone and communication', 1, 10, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(11, 'Assets', 'ASSETS', 'Asset purchases and acquisitions', 1, 11, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(12, 'Diesel purchase', 'DIESEL', 'Diesel fuel purchases', 1, 12, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(13, 'Fuel purchase', 'FUEL', 'Fuel and petroleum products', 1, 13, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(14, 'Miscellaneous', 'MISCELLANEOUS', 'Other miscellaneous expenses', 1, 14, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(15, 'Subscriptions', 'SUBSCRIPTIONS', 'Service subscriptions and memberships', 1, 15, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(16, 'Tax', 'TAX', 'Tax payments and obligations', 1, 16, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(17, 'Paye', 'PAYE', 'PAYE tax remittances', 1, 17, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(18, 'R.M. Generator', 'RM_GENERATOR', 'Generator repairs and maintenance', 1, 18, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(19, 'R.M. Vehicle', 'RM_VEHICLE', 'Vehicle repairs and maintenance', 1, 19, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(20, 'R.M. Computer', 'RM_COMPUTER', 'Computer repairs and maintenance', 1, 20, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(21, 'R.M. Building', 'RM_BUILDING', 'Building repairs and maintenance', 1, 21, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(22, 'R.M. Office Equipment', 'RM_OFFICE_EQUIP', 'Office equipment repairs and maintenance', 1, 22, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(23, 'Salary and Wages', 'SALARY_WAGES', 'Employee salaries and wages', 1, 23, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(24, 'Security Cost', 'SECURITY', 'Security services and equipment', 1, 24, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(25, 'Bank Charge', 'BANK_CHARGE', 'Banking fees and charges', 1, 25, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(26, 'Medical Expenses', 'MEDICAL', 'Medical and health expenses', 1, 26, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(27, 'Loans', 'LOANS', 'Loan repayments and advances', 1, 27, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(28, 'Refund', 'REFUND', 'Refunds and reimbursements', 1, 28, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(29, 'Furniture and Fittings', 'FURNITURE', 'Furniture and office fittings', 1, 29, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(30, 'Management Expense', 'MANAGEMENT', 'Management and administrative expenses', 1, 30, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(31, 'Training', 'TRAINING', 'Training and development', 1, 31, '2025-11-27 20:22:18', '2025-11-27 20:22:18'),
(32, 'Postage and Delivery', 'POSTAGE', 'Postage and delivery services', 1, 32, '2025-11-27 20:22:18', '2025-11-27 20:22:18');

-- --------------------------------------------------------

--
-- Table structure for table `requisition_documents`
--

CREATE TABLE `requisition_documents` (
  `id` int NOT NULL,
  `requisition_id` int NOT NULL,
  `document_type` enum('attachment','invoice','receipt','proof_of_payment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` int NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requisition_documents`
--

INSERT INTO `requisition_documents` (`id`, `requisition_id`, `document_type`, `file_name`, `file_path`, `file_size`, `mime_type`, `uploaded_by`, `uploaded_at`) VALUES
(1, 2, 'attachment', 'Screenshot 2025-10-17 104333.png', 'C:\\xampp\\htdocs\\requisition/uploads/attachments/692759356b842_1764186421.png', 1163, 'image/png', 10, '2025-11-26 19:47:01'),
(2, 2, 'attachment', 'Screenshot 2025-10-13 142437.png', 'C:\\xampp\\htdocs\\requisition/uploads/attachments/69275a5dbe706_1764186717.png', 81966, 'image/png', 10, '2025-11-26 19:51:57'),
(10, 2, 'invoice', 'NotebookLM Mind Map.png', 'C:\\xampp\\htdocs\\requisition/uploads/invoices/69281931c852f_1764235569.png', 685314, 'image/png', 4, '2025-11-27 09:26:09'),
(13, 2, 'receipt', 'Madumere Peter.jpg', 'C:\\xampp\\htdocs\\requisition/uploads/receipts/6928309144876_1764241553.jpg', 714207, 'image/jpeg', 10, '2025-11-27 11:05:53'),
(14, 3, 'attachment', 'NotebookLM Mind Map.png', 'C:\\xampp\\htdocs\\requisition/uploads/attachments/6928649519896_1764254869.png', 685314, 'image/png', 10, '2025-11-27 14:47:49'),
(15, 3, 'invoice', 'c69e5b82-ecca-4c80-93c7-b07fd952cbd5.jpg', 'C:\\xampp\\htdocs\\requisition/uploads/invoices/692876fd2e52f_1764259581.jpg', 23507, 'image/jpeg', 4, '2025-11-27 16:06:21'),
(16, 3, 'receipt', 'Screenshot 2025-10-13 154255.png', 'C:\\xampp\\htdocs\\requisition/uploads/receipts/69287e9c48564_1764261532.png', 7853, 'image/png', 10, '2025-11-27 16:38:52'),
(17, 4, 'attachment', 'Screenshot 2025-10-18 185842.png', 'C:\\xampp\\htdocs\\requisition/uploads/attachments/6928b453b942a_1764275283.png', 32576, 'image/png', 10, '2025-11-27 20:28:03'),
(18, 4, 'invoice', 'ZW_Full color logo2.png', 'C:\\xampp\\htdocs\\requisition/uploads/invoices/6928bb74c0e3e_1764277108.png', 37750, 'image/png', 4, '2025-11-27 20:58:28'),
(19, 4, 'receipt', 'c69e5b82-ecca-4c80-93c7-b07fd952cbd5.jpg', 'C:\\xampp\\htdocs\\requisition/uploads/receipts/6928c100c50bc_1764278528.jpg', 23507, 'image/jpeg', 10, '2025-11-27 21:22:08'),
(20, 9, 'attachment', 'Screenshot 2025-10-15 152920.png', 'C:\\xampp\\htdocs\\requisition/uploads/attachments/692f300920a31_1764700169.png', 5774, 'image/png', 10, '2025-12-02 18:29:29'),
(21, 11, 'attachment', 'Screenshot 2025-10-15 152929.png', 'C:\\xampp\\htdocs\\requisition/uploads/attachments/692f47ca53cfe_1764706250.png', 10340, 'image/png', 10, '2025-12-02 20:10:50'),
(22, 11, 'invoice', 'Screenshot 2025-10-13 154843.png', 'C:\\xampp\\htdocs\\requisition/uploads/invoices/692f4903735c5_1764706563.png', 12869, 'image/png', 4, '2025-12-02 20:16:03'),
(23, 11, 'receipt', 'Screenshot 2025-10-13 142437.png', 'C:\\xampp\\htdocs\\requisition/uploads/receipts/692f49307183b_1764706608.png', 81966, 'image/png', 10, '2025-12-02 20:16:48'),
(24, 23, 'invoice', 'Screenshot 2025-10-14 205003.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/invoices/6935651baa2a5_1765106971.png', 6356, 'image/png', 4, '2025-12-07 11:29:31'),
(25, 23, 'receipt', 'Screenshot 2025-10-20 100631.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/receipts/6935680a3bdc9_1765107722.png', 19964, 'image/png', 10, '2025-12-07 11:42:02'),
(38, 9, 'invoice', 'Screenshot 2025-10-13 142230.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/invoices/6935782d1e13a_1765111853.png', 29842, 'image/png', 4, '2025-12-07 12:50:53'),
(39, 25, 'invoice', 'Screenshot 2025-10-15 231829.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/invoices/6935deaa37536_1765138090.png', 39735, 'image/png', 12, '2025-12-07 20:08:10'),
(40, 25, 'receipt', 'Screenshot 2025-10-17 095526.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/receipts/6935ded14eda5_1765138129.png', 46756, 'image/png', 10, '2025-12-07 20:08:49'),
(41, 26, 'invoice', 'CLEANING 21.01.2025.pdf', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/invoices/6936b34397beb_1765192515.pdf', 511431, 'application/pdf', 12, '2025-12-08 11:15:16'),
(42, 26, 'receipt', 'CLEANING 21.01.2025.pdf', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/receipts/6936b45206e1b_1765192786.pdf', 511431, 'application/pdf', 10, '2025-12-08 11:19:46'),
(43, 28, 'attachment', 'Screenshot 2025-10-13 154255.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/attachments/6937e2d8349bb_1765270232.png', 7853, 'image/png', 10, '2025-12-09 08:50:32');

-- --------------------------------------------------------

--
-- Table structure for table `requisition_items`
--

CREATE TABLE `requisition_items` (
  `id` int NOT NULL,
  `requisition_id` int NOT NULL,
  `item_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requisition_items`
--

INSERT INTO `requisition_items` (`id`, `requisition_id`, `item_description`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 'mop', 1, 2000.00, 2000.00, '2025-11-26 15:19:00', '2025-11-26 15:19:00'),
(2, 1, 'clearning agent', 5, 1000.00, 5000.00, '2025-11-26 15:19:00', '2025-11-26 15:19:00'),
(8, 2, 'testing 2', 1, 2000.00, 2000.00, '2025-11-26 19:51:57', '2025-11-26 19:51:57'),
(9, 2, 'item 2', 1, 20000.00, 20000.00, '2025-11-26 19:51:57', '2025-11-26 19:51:57'),
(15, 3, 'Water', 3, 2000.00, 6000.00, '2025-11-27 15:49:20', '2025-11-27 15:49:20'),
(16, 3, '2 water', 1, 3200.00, 3200.00, '2025-11-27 15:49:20', '2025-11-27 15:49:20'),
(18, 5, 'Flight Ticket', 1, 180000.00, 180000.00, '2025-11-27 20:30:48', '2025-11-27 20:30:48'),
(19, 4, 'Diesel', 1, 500000.00, 500000.00, '2025-11-27 20:39:26', '2025-11-27 20:39:26'),
(27, 10, 'MTN', 1, 3999.99, 3999.99, '2025-12-02 18:26:29', '2025-12-02 18:26:29'),
(28, 9, 'Dispenser', 1, 20000.00, 20000.00, '2025-12-02 18:29:29', '2025-12-02 18:29:29'),
(29, 8, 'Light', 1, 500000.00, 500000.00, '2025-12-02 18:29:57', '2025-12-02 18:29:57'),
(30, 7, 'Diesel', 1, 500.00, 500.00, '2025-12-02 18:30:10', '2025-12-02 18:30:10'),
(31, 6, 'Diesel', 1, 2000.00, 2000.00, '2025-12-02 18:30:32', '2025-12-02 18:30:32'),
(32, 6, 'cups', 500, 100.00, 50000.00, '2025-12-02 18:30:32', '2025-12-02 18:30:32'),
(33, 6, 'cake', 2, 5000.00, 10000.00, '2025-12-02 18:30:32', '2025-12-02 18:30:32'),
(34, 6, 'games', 1, 2050.00, 2050.00, '2025-12-02 18:30:32', '2025-12-02 18:30:32'),
(36, 11, 'paye', 11, 2000.00, 22000.00, '2025-12-02 20:12:27', '2025-12-02 20:12:27'),
(37, 23, 'Keyboard', 2, 20000.00, 40000.00, '2025-12-07 10:59:33', '2025-12-07 10:59:33'),
(38, 23, 'Mouse', 5, 15000.00, 75000.00, '2025-12-07 10:59:33', '2025-12-07 10:59:33'),
(39, 23, 'Monitor', 5, 30000.00, 150000.00, '2025-12-07 10:59:33', '2025-12-07 10:59:33'),
(40, 23, 'System Unit', 5, 100000.00, 500000.00, '2025-12-07 10:59:33', '2025-12-07 10:59:33'),
(58, 24, 'tissue', 1, 2000.00, 2000.00, '2025-12-07 18:56:26', '2025-12-07 18:56:26'),
(59, 24, 'air freshener', 4, 700.00, 2800.00, '2025-12-07 18:56:26', '2025-12-07 18:56:26'),
(60, 25, 'tissue', 2, 3000.00, 6000.00, '2025-12-07 19:05:01', '2025-12-07 19:05:01'),
(61, 25, 'air freshener', 3, 700.00, 2100.00, '2025-12-07 19:05:01', '2025-12-07 19:05:01'),
(62, 25, 'soap', 7, 500.00, 3500.00, '2025-12-07 19:05:01', '2025-12-07 19:05:01'),
(63, 25, 'wipes', 2, 4000.00, 8000.00, '2025-12-07 19:05:01', '2025-12-07 19:05:01'),
(64, 26, 'DOOR KEEY', 2, 2000.00, 4000.00, '2025-12-08 10:55:04', '2025-12-08 10:55:04'),
(65, 26, 'LABOUR', 1, 5000.00, 5000.00, '2025-12-08 10:55:04', '2025-12-08 10:55:04'),
(66, 27, 'TRANSPORT', 1, 20000.00, 20000.00, '2025-12-08 13:29:06', '2025-12-08 13:29:06'),
(67, 28, 'Keyboard', 10, 500.00, 5000.00, '2025-12-09 08:50:32', '2025-12-09 08:50:32');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `role_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `can_raise_requisition` tinyint(1) DEFAULT '1',
  `can_approve` tinyint(1) DEFAULT '0',
  `can_view_all` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `role_code`, `description`, `can_raise_requisition`, `can_approve`, `can_view_all`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'SUPER_ADMIN', 'System administrator with full access to all features. Can manage users, departments, and system settings. Cannot raise requisitions.', 0, 0, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(2, 'Managing Director', 'MD', 'Organization-wide oversight and approval authority. Can approve requisitions from Line Managers and Team Members. Can raise requisitions that go directly to Finance Manager. Can generate organization-wide reports.', 1, 1, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(3, 'Finance Manager', 'FINANCE_MGR', 'Reviews all approved requisitions before payment processing. Can reject requisitions back to previous approvers. Oversees payment processing. Can generate organization-wide reports.', 0, 1, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(4, 'Finance Member', 'FINANCE_MEM', 'Processes requisitions approved by Finance Manager. Uploads invoices and proof of payment. Marks requisitions as paid. Views receipts after payment.', 0, 0, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(5, 'Line Manager', 'LINE_MGR', 'Department manager with approval authority. Approves requisitions from team members. Can raise requisitions that require MD approval. Can generate department reports.', 1, 1, 0, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(6, 'Team Member', 'TEAM_MEM', 'Raises requisitions for department needs. Requisitions require Line Manager and MD approval. Can generate personal reports.', 1, 0, 0, '2025-11-24 13:55:52', '2025-11-24 13:55:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `role_id` int NOT NULL,
  `department_id` int DEFAULT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `department_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'System', 'Administrator', 'admin@gatewey.com', '$2y$12$1IrmI9xCl22.8kgpUGU39.GfUdhjb28eCzStjykQCEswAe6LktQsW', '+234 800 000 0000', 1, '2025-12-09 09:15:26', '2025-11-24 13:55:52', '2025-12-09 09:15:26'),
(2, 2, NULL, 'John', 'Okonkwo', 'md@gatewey.com', '$2y$10$0K90qD7ST5fvx/i/CPVwpu4vLAa5Uh55wwrA5KGJheqB.rF38yFT.', '+234 801 234 5678', 1, '2025-12-07 19:26:21', '2025-11-24 13:55:52', '2025-12-07 19:26:21'),
(3, 3, 3, 'Amina', 'Yusuf', 'finance.manager@gatewey.com', '$2y$10$5.0MP/wSe1ifUTKnN.FMI.yHftQDlPl6isCdPb28CEuiWyt0eKoaK', '+234 802 345 6789', 1, '2025-12-09 09:05:08', '2025-11-24 13:55:52', '2025-12-09 09:05:08'),
(4, 4, 3, 'Chidinma', 'Okafor', 'finance.member@gatewey.com', '$2y$10$Pgk6rsEvor7A.cwT5YRw0e/S0.nJSPX8yPS/46SZXrnuOOcl0Sb.u', '+234 803 456 7890', 1, '2025-12-07 20:11:41', '2025-11-24 13:55:52', '2025-12-07 20:11:41'),
(5, 5, 1, 'Emeka', 'Nwankwo', 'it.manager@gatewey.com', '$2y$10$aSMiTWqUPZ/TVAfUUHQl..lUjofotRg.nmgZzFOze4NHwdq5U0CGa', '+234 804 567 8901', 1, '2025-12-09 08:50:58', '2025-11-24 13:55:52', '2025-12-09 08:50:58'),
(6, 5, 4, 'Fatimaa', 'Abubakar', 'marketing.manager@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 805 678 9012', 1, NULL, '2025-11-24 13:55:52', '2025-11-24 21:09:01'),
(7, 6, 1, 'Oluwaseun', 'Adebayo', 'oluwaseun.adebayo@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 806 789 0123', 0, NULL, '2025-11-24 13:55:52', '2025-11-25 13:11:50'),
(8, 6, 4, 'Blessing2', 'Eze', 'blessing.eze@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 807 890 1234', 0, NULL, '2025-11-24 13:55:53', '2025-11-25 13:05:33'),
(9, 6, 2, 'Ibrahim', 'Mohammed', 'ibrahim.mohammed@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 808 901 2345', 1, NULL, '2025-11-24 13:55:53', '2025-11-24 13:55:53'),
(10, 6, 1, 'Peter', 'Madumere', 'nnana@gmail.com', '$2y$10$alx3vWs/tTlsnJBkef8wjuO6cf8VS.dH9F/XxXOoWCch3eZdju.V2', '09069225818', 1, '2025-12-09 15:47:39', '2025-11-25 13:20:44', '2025-12-09 15:47:39'),
(11, 5, 9, 'Peter', 'Madumere', 'peter@gamil.com', '$2y$10$GSktFIGpNSZO/KT6MHH/ye7kK5IACBVVkzHAGkoHqxnv3it/ccwWK', '+2349069225818', 1, '2025-12-07 16:05:58', '2025-12-07 15:38:12', '2025-12-07 16:05:58'),
(12, 4, 3, 'Mary', 'Madumere', 'mary@gmail.com', '$2y$10$JSpuQ9QMRtTD8sVFX1Jr0OJOYm7fQCgob2JDe0XxIhnsrB2se0NEi', '+234 816 035 3627', 1, '2025-12-08 11:12:20', '2025-12-07 17:01:13', '2025-12-08 11:12:20');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_active_users`
-- (See below for the actual view)
--
CREATE TABLE `v_active_users` (
`id` int
,`first_name` varchar(50)
,`last_name` varchar(50)
,`full_name` varchar(101)
,`email` varchar(100)
,`phone` varchar(20)
,`role_name` varchar(50)
,`role_code` varchar(20)
,`department_name` varchar(100)
,`department_code` varchar(20)
,`last_login` timestamp
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `v_pending_approvals`
--

CREATE TABLE `v_pending_approvals` (
  `id` int DEFAULT NULL,
  `requisition_number` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `purpose` text COLLATE utf8mb3_unicode_ci,
  `total_amount` decimal(15,2) DEFAULT NULL,
  `status` enum('draft','pending_line_manager','pending_md','pending_finance_manager','approved_for_payment','paid','completed','rejected','cancelled') COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `current_approver_id` int DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `requester_name` varchar(101) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department_name` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `days_pending` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `v_requisitions_summary`
--

CREATE TABLE `v_requisitions_summary` (
  `id` int DEFAULT NULL,
  `requisition_number` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `purpose` text COLLATE utf8mb3_unicode_ci,
  `total_amount` decimal(15,2) DEFAULT NULL,
  `status` enum('draft','pending_line_manager','pending_md','pending_finance_manager','approved_for_payment','paid','completed','rejected','cancelled') COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `is_draft` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `receipt_uploaded` tinyint(1) DEFAULT NULL,
  `requester_name` varchar(101) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `requester_email` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department_name` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department_code` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `current_approver_name` varchar(101) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb3_unicode_ci,
  `rejected_by_name` varchar(101) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_requisition` (`requisition_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_name` (`department_name`),
  ADD UNIQUE KEY `department_code` (`department_code`),
  ADD KEY `idx_dept_code` (`department_code`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `requisition_number` (`requisition_number`),
  ADD KEY `rejected_by_id` (`rejected_by_id`),
  ADD KEY `idx_requisition_number` (`requisition_number`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_department` (`department_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_current_approver` (`current_approver_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_submitted_at` (`submitted_at`),
  ADD KEY `fk_requisitions_paid_by` (`paid_by`);

--
-- Indexes for table `requisition_approvals`
--
ALTER TABLE `requisition_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_requisition` (`requisition_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `requisition_categories`
--
ALTER TABLE `requisition_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`category_name`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `requisition_documents`
--
ALTER TABLE `requisition_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_requisition` (`requisition_id`),
  ADD KEY `idx_document_type` (`document_type`),
  ADD KEY `idx_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `requisition_items`
--
ALTER TABLE `requisition_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_requisition` (`requisition_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`),
  ADD UNIQUE KEY `role_code` (`role_code`),
  ADD KEY `idx_role_code` (`role_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role_id`),
  ADD KEY `idx_department` (`department_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=676;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `requisitions`
--
ALTER TABLE `requisitions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `requisition_approvals`
--
ALTER TABLE `requisition_approvals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `requisition_categories`
--
ALTER TABLE `requisition_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `requisition_documents`
--
ALTER TABLE `requisition_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `requisition_items`
--
ALTER TABLE `requisition_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

-- --------------------------------------------------------

--
-- Structure for view `v_active_users`
--
DROP TABLE IF EXISTS `v_active_users`;

CREATE ALGORITHM=UNDEFINED DEFINER=`gateweyc`@`localhost` SQL SECURITY DEFINER VIEW `v_active_users`  AS SELECT `u`.`id` AS `id`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `full_name`, `u`.`email` AS `email`, `u`.`phone` AS `phone`, `r`.`role_name` AS `role_name`, `r`.`role_code` AS `role_code`, `d`.`department_name` AS `department_name`, `d`.`department_code` AS `department_code`, `u`.`last_login` AS `last_login`, `u`.`created_at` AS `created_at` FROM ((`users` `u` join `roles` `r` on((`u`.`role_id` = `r`.`id`))) left join `departments` `d` on((`u`.`department_id` = `d`.`id`))) WHERE (`u`.`is_active` = 1) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `audit_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD CONSTRAINT `fk_requisitions_paid_by` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `requisitions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requisitions_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `requisitions_ibfk_3` FOREIGN KEY (`current_approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requisitions_ibfk_4` FOREIGN KEY (`rejected_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `requisition_approvals`
--
ALTER TABLE `requisition_approvals`
  ADD CONSTRAINT `requisition_approvals_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requisition_approvals_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requisition_documents`
--
ALTER TABLE `requisition_documents`
  ADD CONSTRAINT `requisition_documents_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requisition_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requisition_items`
--
ALTER TABLE `requisition_items`
  ADD CONSTRAINT `requisition_items_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

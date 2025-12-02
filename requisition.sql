-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 07:02 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `requisition`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `requisition_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
(200, 7, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 17:44:52');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `department_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
(7, 'Biology', 'BY', 'Very Good Department', 0.00, 1, '2025-11-24 21:10:40', '2025-11-24 21:10:40');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `requisition_id` int(11) DEFAULT NULL,
  `notification_type` enum('requisition_submitted','requisition_approved','requisition_rejected','requisition_paid','receipt_uploaded','action_required','requisition_cancelled') NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_email_sent` tinyint(1) DEFAULT 0,
  `email_sent_at` timestamp NULL DEFAULT NULL,
  `email_error` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_used` tinyint(1) DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `id` int(11) NOT NULL,
  `requisition_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `purpose` text NOT NULL,
  `description` text DEFAULT NULL COMMENT 'Additional information or notes about the requisition',
  `total_amount` decimal(15,2) NOT NULL,
  `status` enum('draft','pending_line_manager','pending_md','pending_finance_manager','approved_for_payment','paid','completed','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `current_approver_id` int(11) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `rejected_by_id` int(11) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_notes` text DEFAULT NULL,
  `receipt_uploaded` tinyint(1) DEFAULT 0,
  `receipt_uploaded_at` timestamp NULL DEFAULT NULL,
  `is_draft` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `submitted_at` timestamp NULL DEFAULT NULL,
  `paid_by` int(11) DEFAULT NULL,
  `receipt_file_path` varchar(500) DEFAULT NULL,
  `receipt_file_name` varchar(255) DEFAULT NULL,
  `receipt_notes` text DEFAULT NULL
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
(6, 'REQ00006', 10, 1, 'Entertainment', 'testing also', 64050.00, 'pending_line_manager', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-02 12:01:42', '2025-12-02 17:43:32', '2025-12-02 12:01:42', NULL, NULL, NULL, NULL),
(7, 'REQ00007', 10, 1, 'Miscellaneous', NULL, 500.00, 'pending_line_manager', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-02 17:44:52', '2025-12-02 17:44:52', '2025-12-02 17:44:52', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `requisition_approvals`
--

CREATE TABLE `requisition_approvals` (
  `id` int(11) NOT NULL,
  `requisition_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_at_approval` varchar(50) NOT NULL,
  `action` enum('approved','rejected','returned') NOT NULL,
  `comments` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
(20, 4, 3, 'Finance Manager', 'approved', 'good to go', NULL, '2025-11-27 20:55:56');

-- --------------------------------------------------------

--
-- Table structure for table `requisition_categories`
--

CREATE TABLE `requisition_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
  `id` int(11) NOT NULL,
  `requisition_id` int(11) NOT NULL,
  `document_type` enum('attachment','invoice','receipt','proof_of_payment') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
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
(19, 4, 'receipt', 'c69e5b82-ecca-4c80-93c7-b07fd952cbd5.jpg', 'C:\\xampp\\htdocs\\requisition/uploads/receipts/6928c100c50bc_1764278528.jpg', 23507, 'image/jpeg', 10, '2025-11-27 21:22:08');

-- --------------------------------------------------------

--
-- Table structure for table `requisition_items`
--

CREATE TABLE `requisition_items` (
  `id` int(11) NOT NULL,
  `requisition_id` int(11) NOT NULL,
  `item_description` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
(20, 6, 'Diesel', 1, 2000.00, 2000.00, '2025-12-02 12:01:42', '2025-12-02 12:01:42'),
(21, 6, 'cups', 500, 100.00, 50000.00, '2025-12-02 12:01:42', '2025-12-02 12:01:42'),
(22, 6, 'cake', 2, 5000.00, 10000.00, '2025-12-02 12:01:42', '2025-12-02 12:01:42'),
(23, 6, 'games', 1, 2050.00, 2050.00, '2025-12-02 12:01:42', '2025-12-02 12:01:42'),
(24, 7, 'Diesel', 1, 500.00, 500.00, '2025-12-02 17:44:52', '2025-12-02 17:44:52');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `role_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `can_raise_requisition` tinyint(1) DEFAULT 1,
  `can_approve` tinyint(1) DEFAULT 0,
  `can_view_all` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `department_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'System', 'Administrator', 'admin@gatewey.com', '$2y$12$1IrmI9xCl22.8kgpUGU39.GfUdhjb28eCzStjykQCEswAe6LktQsW', '+234 800 000 0000', 1, '2025-12-01 09:40:32', '2025-11-24 13:55:52', '2025-12-01 09:40:32'),
(2, 2, NULL, 'John', 'Okonkwo', 'md@gatewey.com', '$2y$10$0K90qD7ST5fvx/i/CPVwpu4vLAa5Uh55wwrA5KGJheqB.rF38yFT.', '+234 801 234 5678', 1, '2025-11-27 21:16:27', '2025-11-24 13:55:52', '2025-11-27 21:16:27'),
(3, 3, 3, 'Amina', 'Yusuf', 'finance.manager@gatewey.com', '$2y$10$5.0MP/wSe1ifUTKnN.FMI.yHftQDlPl6isCdPb28CEuiWyt0eKoaK', '+234 802 345 6789', 1, '2025-12-02 13:25:16', '2025-11-24 13:55:52', '2025-12-02 13:25:16'),
(4, 4, 3, 'Chidinma', 'Okafor', 'finance.member@gatewey.com', '$2y$10$Pgk6rsEvor7A.cwT5YRw0e/S0.nJSPX8yPS/46SZXrnuOOcl0Sb.u', '+234 803 456 7890', 1, '2025-12-02 13:18:23', '2025-11-24 13:55:52', '2025-12-02 13:18:23'),
(5, 5, 1, 'Emeka', 'Nwankwo', 'it.manager@gatewey.com', '$2y$10$aSMiTWqUPZ/TVAfUUHQl..lUjofotRg.nmgZzFOze4NHwdq5U0CGa', '+234 804 567 8901', 1, '2025-12-02 13:41:02', '2025-11-24 13:55:52', '2025-12-02 13:41:02'),
(6, 5, 4, 'Fatimaa', 'Abubakar', 'marketing.manager@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 805 678 9012', 1, NULL, '2025-11-24 13:55:52', '2025-11-24 21:09:01'),
(7, 6, 1, 'Oluwaseun', 'Adebayo', 'oluwaseun.adebayo@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 806 789 0123', 0, NULL, '2025-11-24 13:55:52', '2025-11-25 13:11:50'),
(8, 6, 4, 'Blessing2', 'Eze', 'blessing.eze@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 807 890 1234', 0, NULL, '2025-11-24 13:55:53', '2025-11-25 13:05:33'),
(9, 6, 2, 'Ibrahim', 'Mohammed', 'ibrahim.mohammed@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 808 901 2345', 1, NULL, '2025-11-24 13:55:53', '2025-11-24 13:55:53'),
(10, 6, 1, 'Peter', 'Madumere', 'nnanamadumere@gmail.com', '$2y$10$2taMO2gOQnLm2aDRj2.S5O66lovhez672rUPdGMoheJguCkgG35Fy', 'admin@gatewey.com', 1, '2025-12-02 17:37:21', '2025-11-25 13:20:44', '2025-12-02 17:37:21');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_active_users`
-- (See below for the actual view)
--
CREATE TABLE `v_active_users` (
`id` int(11)
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
-- Stand-in structure for view `v_pending_approvals`
-- (See below for the actual view)
--
CREATE TABLE `v_pending_approvals` (
`id` int(11)
,`requisition_number` varchar(50)
,`purpose` text
,`total_amount` decimal(15,2)
,`status` enum('draft','pending_line_manager','pending_md','pending_finance_manager','approved_for_payment','paid','completed','rejected','cancelled')
,`current_approver_id` int(11)
,`submitted_at` timestamp
,`created_at` timestamp
,`requester_name` varchar(101)
,`department_name` varchar(100)
,`days_pending` int(7)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_requisitions_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_requisitions_summary` (
`id` int(11)
,`requisition_number` varchar(50)
,`purpose` text
,`total_amount` decimal(15,2)
,`status` enum('draft','pending_line_manager','pending_md','pending_finance_manager','approved_for_payment','paid','completed','rejected','cancelled')
,`is_draft` tinyint(1)
,`created_at` timestamp
,`submitted_at` timestamp
,`payment_date` timestamp
,`receipt_uploaded` tinyint(1)
,`requester_name` varchar(101)
,`requester_email` varchar(100)
,`department_name` varchar(100)
,`department_code` varchar(20)
,`current_approver_name` varchar(101)
,`rejection_reason` text
,`rejected_by_name` varchar(101)
,`rejected_at` timestamp
);

-- --------------------------------------------------------

--
-- Structure for view `v_active_users`
--
DROP TABLE IF EXISTS `v_active_users`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_active_users`  AS SELECT `u`.`id` AS `id`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `full_name`, `u`.`email` AS `email`, `u`.`phone` AS `phone`, `r`.`role_name` AS `role_name`, `r`.`role_code` AS `role_code`, `d`.`department_name` AS `department_name`, `d`.`department_code` AS `department_code`, `u`.`last_login` AS `last_login`, `u`.`created_at` AS `created_at` FROM ((`users` `u` join `roles` `r` on(`u`.`role_id` = `r`.`id`)) left join `departments` `d` on(`u`.`department_id` = `d`.`id`)) WHERE `u`.`is_active` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `v_pending_approvals`
--
DROP TABLE IF EXISTS `v_pending_approvals`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pending_approvals`  AS SELECT `r`.`id` AS `id`, `r`.`requisition_number` AS `requisition_number`, `r`.`purpose` AS `purpose`, `r`.`total_amount` AS `total_amount`, `r`.`status` AS `status`, `r`.`current_approver_id` AS `current_approver_id`, `r`.`submitted_at` AS `submitted_at`, `r`.`created_at` AS `created_at`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `requester_name`, `d`.`department_name` AS `department_name`, to_days(current_timestamp()) - to_days(`r`.`submitted_at`) AS `days_pending` FROM ((`requisitions` `r` join `users` `u` on(`r`.`user_id` = `u`.`id`)) join `departments` `d` on(`r`.`department_id` = `d`.`id`)) WHERE `r`.`current_approver_id` is not null AND `r`.`status` in ('pending_line_manager','pending_md','pending_finance_manager') ;

-- --------------------------------------------------------

--
-- Structure for view `v_requisitions_summary`
--
DROP TABLE IF EXISTS `v_requisitions_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_requisitions_summary`  AS SELECT `r`.`id` AS `id`, `r`.`requisition_number` AS `requisition_number`, `r`.`purpose` AS `purpose`, `r`.`total_amount` AS `total_amount`, `r`.`status` AS `status`, `r`.`is_draft` AS `is_draft`, `r`.`created_at` AS `created_at`, `r`.`submitted_at` AS `submitted_at`, `r`.`payment_date` AS `payment_date`, `r`.`receipt_uploaded` AS `receipt_uploaded`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `requester_name`, `u`.`email` AS `requester_email`, `d`.`department_name` AS `department_name`, `d`.`department_code` AS `department_code`, concat(`approver`.`first_name`,' ',`approver`.`last_name`) AS `current_approver_name`, `r`.`rejection_reason` AS `rejection_reason`, concat(`rejected_by`.`first_name`,' ',`rejected_by`.`last_name`) AS `rejected_by_name`, `r`.`rejected_at` AS `rejected_at` FROM ((((`requisitions` `r` join `users` `u` on(`r`.`user_id` = `u`.`id`)) join `departments` `d` on(`r`.`department_id` = `d`.`id`)) left join `users` `approver` on(`r`.`current_approver_id` = `approver`.`id`)) left join `users` `rejected_by` on(`r`.`rejected_by_id` = `rejected_by`.`id`)) ;

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
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_requisition` (`requisition_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_notification_type` (`notification_type`),
  ADD KEY `idx_created_at` (`created_at`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `requisitions`
--
ALTER TABLE `requisitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `requisition_approvals`
--
ALTER TABLE `requisition_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `requisition_categories`
--
ALTER TABLE `requisition_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `requisition_documents`
--
ALTER TABLE `requisition_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `requisition_items`
--
ALTER TABLE `requisition_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE;

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

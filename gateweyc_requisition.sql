-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 27, 2025 at 09:11 AM
-- Server version: 8.0.43-cll-lve
-- PHP Version: 8.4.16

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
(1, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:31:42'),
(2, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:31:53'),
(3, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:41:12'),
(4, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:41:26'),
(5, NULL, 3, 'budget_set', 'Budget of ₦500,000.00 set for period 2025-12-19 to 2026-03-18 (Department: 1)', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:42:15'),
(6, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:42:28'),
(7, NULL, 13, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:42:46'),
(8, 1, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:45:29'),
(9, 2, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:46:56'),
(10, 2, 13, 'attachment_uploaded', 'File uploaded: Screenshot 2025-10-13 142230.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:46:56'),
(11, 3, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:47:48'),
(12, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:48:30'),
(13, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:48:41'),
(14, NULL, 5, 'budget_allocated', 'Budget of ₦10,000.00 allocated to requisition #1 (Department: 1)', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:48:52'),
(15, NULL, 5, 'budget_allocated', 'Budget of ₦7,000.00 allocated to requisition #3 (Department: 1)', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:48:58'),
(16, NULL, 5, 'budget_allocated', 'Budget of ₦20,000.00 allocated to requisition #2 (Department: 1)', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:49:06'),
(17, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:49:27'),
(18, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:49:37'),
(19, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:50:39'),
(20, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:50:49'),
(21, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:51:28'),
(22, NULL, 12, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:51:35'),
(23, 3, 12, 'invoice_uploaded', 'File uploaded: Screenshot 2025-10-13 142230.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:51:54'),
(24, 2, 12, 'invoice_uploaded', 'File uploaded: Screenshot 2025-10-13 142230.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:52:09'),
(25, 1, 12, 'invoice_uploaded', 'File uploaded: Screenshot 2025-10-13 154255.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:52:21'),
(26, 3, 12, 'document_viewed', 'Document viewed: Screenshot 2025-10-13 142230.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:52:43'),
(27, NULL, 12, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:53:18'),
(28, NULL, 13, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:53:27'),
(29, 1, 13, 'receipt_uploaded', 'File uploaded: Screenshot 2025-10-13 154255.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:54:11'),
(30, 2, 13, 'receipt_uploaded', 'File uploaded: Screenshot 2025-10-13 154255.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:54:30'),
(31, 3, 13, 'receipt_uploaded', 'File uploaded: Screenshot 2025-10-13 154255.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:54:48'),
(32, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:11:42'),
(33, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:11:55'),
(34, NULL, 10, 'user_updated', 'User account updated', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:12:25'),
(35, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:12:39'),
(36, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:12:46'),
(37, NULL, 3, 'budget_set', 'Budget of ₦500,000.00 set for period 2025-12-19 to 2026-03-18 (Department: 9)', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:13:07'),
(38, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:13:48'),
(39, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-19 15:14:16'),
(40, NULL, 11, 'user_updated', 'User account updated', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-19 15:15:27'),
(41, NULL, 1, 'user_password_changed', 'Password changed for user: admin@gatewey.com', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-19 15:16:35'),
(42, NULL, 1, 'user_password_changed', 'Password changed for user: admin@gatewey.com', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-19 15:17:31'),
(43, NULL, 10, 'user_password_changed', 'Password changed for user: nnana@gmail.com', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-19 15:41:59'),
(44, NULL, 11, 'user_password_changed', 'Password changed for user: peter@gamil.com', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-19 15:42:56'),
(45, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:46:31'),
(46, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:47:23'),
(47, 4, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:48:22'),
(48, 5, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:49:01'),
(49, 6, 10, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:49:45'),
(50, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:49:50'),
(51, NULL, 11, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:50:04'),
(52, NULL, 11, 'budget_allocated', 'Budget of ₦2,000.00 allocated to requisition #6 (Department: 9)', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:50:16'),
(53, NULL, 11, 'budget_allocated', 'Budget of ₦2,300.00 allocated to requisition #5 (Department: 9)', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:50:22'),
(54, NULL, 11, 'budget_allocated', 'Budget of ₦4,000.00 allocated to requisition #4 (Department: 9)', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:50:27'),
(55, NULL, 11, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:50:33'),
(56, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:50:40'),
(57, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-19 15:51:04'),
(58, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:51:08'),
(59, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-19 15:51:24'),
(60, NULL, 12, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:51:27'),
(61, 6, 12, 'invoice_uploaded', 'File uploaded: Screenshot 2025-10-13 154255.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:52:09'),
(62, 5, 12, 'invoice_uploaded', 'File uploaded: Screenshot 2025-10-13 154255.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:52:23'),
(63, 4, 12, 'invoice_uploaded', 'File uploaded: Screenshot 2025-10-13 154255.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:52:36'),
(64, NULL, 12, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:52:41'),
(65, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:53:06'),
(66, 4, 10, 'receipt_uploaded', 'File uploaded: Screenshot 2025-10-13 154255.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:53:20'),
(67, 6, 10, 'receipt_uploaded', 'File uploaded: Screenshot 2025-10-13 154255.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:53:36'),
(68, 5, 10, 'receipt_uploaded', 'File uploaded: Screenshot 2025-10-13 154255.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:53:53'),
(69, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 19:51:08'),
(70, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 19:51:32'),
(71, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 19:51:45'),
(72, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 20:27:53'),
(73, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 20:28:05'),
(74, NULL, 5, 'user_updated', 'User account updated', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 08:34:44'),
(75, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 08:35:47'),
(76, NULL, 3, 'budget_updated', 'Budget updated. Changes: End: 2026-03-18 → 2026-03-19 (Department: 9)', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 08:39:53'),
(77, NULL, 3, 'budget_set', 'Budget of ₦2,000.00 set for period 2026-01-21 to 2026-04-20 (Department: 2)', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 08:45:34'),
(78, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 09:50:47'),
(79, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 12:10:30'),
(80, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 12:42:45'),
(81, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 12:42:55'),
(82, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 12:50:09'),
(83, NULL, 1, 'email_test', 'Email configuration test sent to admin@gatewey.com', NULL, NULL, '102.209.31.145', NULL, '2025-12-20 13:03:40'),
(84, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 21:49:45'),
(85, NULL, 1, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 21:49:55'),
(86, NULL, 1, 'backup_created', 'Database backup created: backup_gateweyc_requisition_2025-12-21_22-56-55.sql', NULL, NULL, '102.209.31.145', NULL, '2025-12-21 21:56:55'),
(87, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 22:02:28'),
(88, NULL, 13, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 22:02:37'),
(89, NULL, 10, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 22:08:03'),
(90, 7, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 22:19:53'),
(91, 7, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=7, Sent=2, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-21 22:19:53'),
(92, 8, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 22:29:19'),
(93, 8, 13, 'attachment_uploaded', 'File uploaded: Screenshot 2025-10-13 142230.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 22:29:22'),
(94, 8, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=8, Sent=2, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-21 22:29:22'),
(95, 9, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 22:34:05'),
(96, 9, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=9, Sent=2, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-21 22:34:05'),
(97, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 10:05:44'),
(98, 10, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 10:11:14'),
(99, 10, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=10, Sent=2, Failed=0', NULL, NULL, '102.88.113.171', NULL, '2025-12-22 10:11:14'),
(100, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 10:14:09'),
(101, NULL, 5, 'budget_allocated', 'Budget of ₦28,800.00 allocated to requisition #10 (Department: 1)', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 10:46:13'),
(102, 10, 5, 'requisition_approved', 'Requisition approved by Emekaa Nwankwo', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 10:46:13'),
(103, 10, 5, 'email_sent', 'Email notification sent: Type=requisition_approved, Requisition=10, Sent=2, Failed=0', NULL, NULL, '102.88.113.171', NULL, '2025-12-22 10:46:13'),
(104, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 10:51:06'),
(105, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 10:51:25'),
(106, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 11:12:52'),
(107, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 11:14:21'),
(108, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 11:14:33'),
(109, NULL, 3, 'budget_updated', 'Budget updated. Changes: Amount: ₦500,000.00 → ₦600,000.00 (Department: 1)', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 11:15:54'),
(110, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 11:16:02'),
(111, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 11:16:14'),
(112, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 11:18:03'),
(113, NULL, 12, 'user_logout', 'User logged out', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 11:30:43'),
(114, NULL, 4, 'user_logout', 'User logged out', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 11:35:46'),
(115, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.88.113.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 11:35:54'),
(116, 4, 3, 'document_viewed', 'Document viewed: Screenshot 2025-10-13 154255.png', NULL, NULL, '102.215.57.30', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 11:47:31'),
(117, 4, 3, 'document_viewed', 'Document viewed: Screenshot 2025-10-13 154255.png', NULL, NULL, '102.215.57.30', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-22 11:47:41'),
(118, NULL, 2, 'user_login', 'User logged in successfully', NULL, NULL, '129.222.206.63', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 09:28:50'),
(119, 10, 2, 'requisition_approved', 'Requisition approved by John Okonkwo - Comment: this is good to fly', NULL, NULL, '129.222.206.63', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 09:29:10'),
(120, 10, 2, 'email_sent', 'Email notification sent: Type=requisition_approved, Requisition=10, Sent=2, Failed=0', NULL, NULL, '129.222.206.63', NULL, '2025-12-23 09:29:10'),
(121, 10, 3, 'requisition_approved', 'Requisition approved by Amina Yusuf - Comment: Godspower, please Approve', NULL, NULL, '129.222.206.63', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 09:30:38'),
(122, 10, 3, 'email_sent', 'Email notification sent: Type=requisition_approved, Requisition=10, Sent=2, Failed=0', NULL, NULL, '129.222.206.63', NULL, '2025-12-23 09:30:38'),
(123, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '129.222.206.63', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-23 09:31:45'),
(124, NULL, 5, 'budget_allocated', 'Budget of ₦13,000.00 allocated to requisition #8 (Department: 1)', NULL, NULL, '129.222.206.63', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-23 09:32:57'),
(125, 8, 5, 'requisition_approved', 'Requisition approved by Emekaa Nwankwo', NULL, NULL, '129.222.206.63', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-23 09:32:57'),
(126, 8, 5, 'email_sent', 'Email notification sent: Type=requisition_approved, Requisition=8, Sent=2, Failed=0', NULL, NULL, '129.222.206.63', NULL, '2025-12-23 09:32:57'),
(127, 11, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.89.85.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 09:41:03'),
(128, 11, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=11, Sent=2, Failed=0', NULL, NULL, '102.89.85.151', NULL, '2025-12-23 09:41:03'),
(129, NULL, 2, 'budget_released', 'Budget of ₦13,000.00 released from requisition #8 - Released due to rejection by John Okonkwo (Department: 1)', NULL, NULL, '129.222.206.63', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 09:57:10'),
(130, 8, 2, 'requisition_rejected', 'Requisition rejected by John Okonkwo - Reason: this needs more information', NULL, NULL, '129.222.206.63', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 09:57:10'),
(131, 8, 2, 'email_sent', 'Email notification sent: Type=requisition_rejected, Requisition=8, Sent=1, Failed=0', NULL, NULL, '129.222.206.63', NULL, '2025-12-23 09:57:11'),
(132, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.89.85.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 10:20:05'),
(133, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.89.85.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 10:24:01'),
(134, NULL, 12, 'user_login', 'User logged in successfully', NULL, NULL, '102.89.85.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 10:24:19'),
(135, 10, 12, 'invoice_uploaded', 'File uploaded: Annotation 2025-04-17 114648.png', NULL, NULL, '102.89.85.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 10:26:27'),
(136, 10, 12, 'payment_processed', 'Payment processed - Method: Bank Transfer, Ref: erteresddfsd', NULL, NULL, '102.89.85.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 10:26:27'),
(137, 10, 12, 'email_sent', 'Email notification sent: Type=requisition_paid, Requisition=10, Sent=4, Failed=0', NULL, NULL, '102.89.85.151', NULL, '2025-12-23 10:26:28'),
(138, NULL, 12, 'user_logout', 'User logged out', NULL, NULL, '102.89.85.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 10:26:42'),
(139, 10, 13, 'document_viewed', 'Document viewed: Annotation 2025-04-17 114648.png', NULL, NULL, '102.89.85.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 10:42:04'),
(140, 8, 13, 'requisition_updated', 'Requisition resubmitted after revision', NULL, NULL, '102.215.57.96', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 11:11:19'),
(141, 8, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=8, Sent=2, Failed=0', NULL, NULL, '102.215.57.96', NULL, '2025-12-23 11:11:19'),
(142, NULL, 10, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 13:31:53'),
(143, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 21:19:35'),
(144, 12, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 21:26:39'),
(145, 12, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=12, Sent=2, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-23 21:26:39'),
(146, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 21:26:57'),
(147, NULL, 5, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 21:27:06'),
(148, NULL, 5, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 21:27:30'),
(149, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 21:28:38'),
(150, 12, 3, 'requisition_rejected', 'Requisition rejected by Amina Yusuf - Reason: this is the new flow rejection', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 21:29:11'),
(151, 12, 3, 'email_sent', 'Email notification sent: Type=requisition_rejected, Requisition=12, Sent=1, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-23 21:29:11'),
(152, 13, 3, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 21:33:32'),
(153, 13, 3, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=13, Sent=2, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-23 21:33:32'),
(154, 12, 13, 'requisition_updated', 'Requisition resubmitted after revision', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 08:57:25'),
(155, 12, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=12, Sent=2, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-24 08:57:25'),
(156, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 09:05:36'),
(157, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 09:05:46'),
(158, 12, 3, 'requisition_approved', 'Requisition approved by Amina Yusuf', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 09:06:03'),
(159, 12, 3, 'email_sent', 'Email notification sent: Type=requisition_approved, Requisition=12, Sent=2, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-24 09:06:03'),
(160, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 09:06:42'),
(161, NULL, 4, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 09:07:31'),
(162, NULL, 4, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 09:56:11'),
(163, 14, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 09:57:45'),
(164, 14, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=14, Sent=2, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-24 09:57:45'),
(165, 15, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 10:04:41'),
(166, 15, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=15, Sent=2, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-24 10:04:41'),
(167, 16, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 10:09:24'),
(168, 16, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=16, Sent=2, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-24 10:09:24'),
(169, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 10:29:00'),
(170, 16, 2, 'requisition_approved', 'Requisition approved by John Okonkwo', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 10:30:20'),
(171, 16, 2, 'email_sent', 'Email notification sent: Type=requisition_approved, Requisition=16, Sent=2, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-24 10:30:20'),
(172, NULL, 2, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 10:30:36'),
(173, NULL, 3, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 10:30:51'),
(174, 16, 3, 'requisition_approved', 'Requisition approved by Amina Yusuf', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 10:31:04'),
(175, 16, 3, 'email_sent', 'Email notification sent: Type=requisition_approved, Requisition=16, Sent=2, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-24 10:31:04'),
(176, NULL, 3, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 10:32:28'),
(177, NULL, 13, 'twofa_enabled', 'Two-factor authentication enabled', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:45:51'),
(178, NULL, 13, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:45:51'),
(179, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:46:18'),
(180, NULL, 13, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:46:41'),
(181, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:47:02'),
(182, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:50:28'),
(183, NULL, 13, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:50:46'),
(184, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:51:26'),
(185, NULL, 13, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:51:45'),
(186, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:51:55'),
(187, NULL, 13, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:52:15'),
(188, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:52:33'),
(189, NULL, 1, 'twofa_enabled', 'Two-factor authentication enabled', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:53:38'),
(190, NULL, 1, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:53:38'),
(191, NULL, 13, 'twofa_disabled', 'Two-factor authentication reset by admin (ID: 1)', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:54:23'),
(192, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:54:39'),
(193, NULL, 13, 'twofa_enabled', 'Two-factor authentication enabled', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:55:19'),
(194, NULL, 13, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:55:19'),
(195, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:59:01'),
(196, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:01:37'),
(197, NULL, 13, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:01:58'),
(198, NULL, 13, 'user_login', 'User logged in successfully', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:02:23'),
(199, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:02:31'),
(200, NULL, 1, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:02:56'),
(201, NULL, 13, 'twofa_disabled', 'Two-factor authentication reset by admin (ID: 1)', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:03:15'),
(202, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:03:20'),
(203, NULL, 13, 'twofa_enabled', 'Two-factor authentication enabled', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:03:57'),
(204, NULL, 13, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:03:57'),
(205, 17, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:11:26'),
(206, 17, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=17, Sent=0, Failed=2', NULL, NULL, '102.209.31.145', NULL, '2025-12-25 07:11:28'),
(207, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:19:26'),
(208, NULL, 1, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:20:10'),
(209, NULL, 1, 'twofa_disabled', 'Two-factor authentication reset by admin (ID: 1)', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:26:42'),
(210, NULL, 1, 'twofa_enabled', 'Two-factor authentication enabled', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:27:20'),
(211, NULL, 1, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:27:20'),
(212, NULL, 1, 'email_test', 'Email configuration test sent to madumerepeter.u@gmail.com', NULL, NULL, '102.209.31.145', NULL, '2025-12-25 07:29:50'),
(213, NULL, 1, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:31:18'),
(214, NULL, 13, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:31:50'),
(215, NULL, 13, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:32:07'),
(216, 18, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:32:48'),
(217, 18, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=18, Sent=2, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-25 07:32:49'),
(218, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:33:55'),
(219, NULL, 13, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2025-12-25 12:36:20');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(220, NULL, 1, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 12:54:05'),
(221, NULL, 1, 'help_updated', 'Updated help item ID: 1', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 12:55:27'),
(222, NULL, 1, 'help_created', 'Created help item: this is a test', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 12:58:52'),
(223, NULL, 1, 'help_deleted', 'Deleted help item: this is a test', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 12:59:23'),
(224, NULL, 1, 'help_updated', 'Updated help item ID: 1', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 13:03:21'),
(225, NULL, 1, 'help_updated', 'Updated help item ID: 1', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 13:04:53'),
(226, NULL, 1, 'help_updated', 'Updated help item ID: 1', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 13:35:59'),
(227, NULL, 1, 'help_created', 'Created help item: this is a test', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 13:39:03'),
(228, NULL, 1, 'help_created', 'Created help item: testing too again', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 13:39:48'),
(229, NULL, 1, 'help_created', 'Created help item: ssfgss', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 13:40:15'),
(230, NULL, 13, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 17:48:42'),
(231, 19, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 18:04:26'),
(232, 19, 13, 'attachment_uploaded', 'File uploaded: Screenshot 2025-10-13 154255.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 18:04:29'),
(233, 19, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=19, Sent=2, Failed=0', NULL, NULL, '102.209.31.145', NULL, '2025-12-25 18:04:30'),
(234, 19, 13, 'document_viewed', 'Document viewed: Screenshot 2025-10-13 154255.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 18:04:52'),
(235, 10, 13, 'document_viewed', 'Document viewed: Annotation 2025-04-17 114648.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 18:09:38'),
(236, 10, 13, 'receipt_uploaded', 'File uploaded: Screenshot 2025-10-13 142230.png', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 18:10:17'),
(237, 10, 13, 'receipt_uploaded', 'Receipt uploaded by requester', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 18:10:17'),
(238, NULL, 13, 'user_logout', 'User logged out', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 18:57:07'),
(239, NULL, 13, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 19:14:21'),
(240, 20, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 19:15:04'),
(241, 20, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=20, Sent=0, Failed=2', NULL, NULL, '102.209.31.145', NULL, '2025-12-25 19:15:06'),
(242, NULL, 1, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-26 13:57:34'),
(243, NULL, 18, 'user_created', 'User account created: anthony@gmail.com', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-26 14:24:31'),
(244, NULL, 13, 'user_login', 'User logged in successfully with 2FA', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-26 17:23:42'),
(245, 21, 13, 'requisition_submitted', 'Requisition submitted for approval', NULL, NULL, '102.209.31.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-26 17:24:20'),
(246, 21, 13, 'email_sent', 'Email notification sent: Type=requisition_submitted, Requisition=21, Sent=0, Failed=2', NULL, NULL, '102.209.31.145', NULL, '2025-12-26 17:24:23');

-- --------------------------------------------------------

--
-- Table structure for table `budget_allocations`
--

CREATE TABLE `budget_allocations` (
  `id` int NOT NULL,
  `budget_id` int NOT NULL,
  `requisition_id` int NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `allocation_type` enum('reserved','allocated','released') COLLATE utf8mb3_unicode_ci NOT NULL,
  `allocated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `released_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `budget_allocations`
--

INSERT INTO `budget_allocations` (`id`, `budget_id`, `requisition_id`, `amount`, `allocation_type`, `allocated_at`, `released_at`, `notes`) VALUES
(1, 1, 1, 10000.00, 'allocated', '2025-12-19 14:48:52', NULL, 'Allocated by Line Manager approval - Emeka Nwankwo'),
(2, 1, 3, 7000.00, 'allocated', '2025-12-19 14:48:58', NULL, 'Allocated by Line Manager approval - Emeka Nwankwo'),
(3, 1, 2, 20000.00, 'allocated', '2025-12-19 14:49:06', NULL, 'Allocated by Line Manager approval - Emeka Nwankwo'),
(4, 2, 6, 2000.00, 'allocated', '2025-12-19 15:50:16', NULL, 'Allocated by Line Manager approval - Peter Madumere'),
(5, 2, 5, 2300.00, 'allocated', '2025-12-19 15:50:22', NULL, 'Allocated by Line Manager approval - Peter Madumere'),
(6, 2, 4, 4000.00, 'allocated', '2025-12-19 15:50:27', NULL, 'Allocated by Line Manager approval - Peter Madumere'),
(7, 1, 10, 28800.00, 'allocated', '2025-12-22 10:46:13', NULL, 'Allocated by Line Manager approval - Emekaa Nwankwo'),
(8, 1, 8, 13000.00, 'released', '2025-12-23 09:32:57', '2025-12-23 09:57:10', 'Allocated by Line Manager approval - Emekaa Nwankwo - Released due to rejection by John Okonkwo');

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
(9, 'Business Support', 'BST', 'provides essential administrative, operational, and logistical assistance to ensure a company runs smoothly, handling tasks like scheduling, data management, reporting, coordinating teams, and managing facilities, allowing core teams to focus on primary objectives and growth.', 0.00, 1, '2025-12-07 15:40:28', '2025-12-07 15:40:28'),
(10, 'Executive Office', 'EXEC', NULL, 0.00, 1, '2025-12-10 13:14:16', '2025-12-10 13:14:16'),
(11, 'Information and Communication Technology(osogbo)', 'IT_O', '', 0.00, 1, '2025-12-16 11:28:04', '2025-12-16 11:28:04');

-- --------------------------------------------------------

--
-- Table structure for table `department_budgets`
--

CREATE TABLE `department_budgets` (
  `id` int NOT NULL,
  `department_id` int NOT NULL,
  `budget_amount` decimal(15,2) NOT NULL,
  `original_budget_amount` decimal(15,2) DEFAULT NULL,
  `allocated_amount` decimal(15,2) DEFAULT '0.00',
  `available_amount` decimal(15,2) NOT NULL,
  `duration_type` enum('quarterly','yearly','custom') COLLATE utf8mb3_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','expired','upcoming') COLLATE utf8mb3_unicode_ci DEFAULT 'active',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `department_budgets`
--

INSERT INTO `department_budgets` (`id`, `department_id`, `budget_amount`, `original_budget_amount`, `allocated_amount`, `available_amount`, `duration_type`, `start_date`, `end_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 600000.00, 500000.00, 65800.00, 534200.00, 'quarterly', '2025-12-19', '2026-03-18', 'active', 3, '2025-12-19 14:42:15', '2025-12-23 09:57:10'),
(2, 9, 500000.00, 500000.00, 8300.00, 491700.00, 'quarterly', '2025-12-19', '2026-03-19', 'active', 3, '2025-12-19 15:13:07', '2025-12-20 08:39:53'),
(3, 2, 2000.00, 2000.00, 0.00, 2000.00, 'quarterly', '2026-01-21', '2026-04-20', 'upcoming', 3, '2025-12-20 08:45:34', '2025-12-20 08:45:34');

-- --------------------------------------------------------

--
-- Table structure for table `help_support`
--

CREATE TABLE `help_support` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Title of the help article/tip',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Detailed description or writeup',
  `type` enum('tip','video','article') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tip' COMMENT 'Type of help content',
  `video_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'YouTube or video URL (for iframe embed)',
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Category for filtering (e.g., Requisitions, Approvals, Payments)',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'fa-info-circle' COMMENT 'FontAwesome icon class',
  `display_order` int NOT NULL DEFAULT '0' COMMENT 'Order for displaying items',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Whether this help item is active',
  `created_by` int UNSIGNED NOT NULL COMMENT 'User ID who created this (usually super admin)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Help and support articles, tips, and video tutorials';

--
-- Dumping data for table `help_support`
--

INSERT INTO `help_support` (`id`, `title`, `description`, `type`, `video_url`, `category`, `icon`, `display_order`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'How to Create a Requisition', 'Learn how to create a new requisition in the system. Fill in all required fields including purpose, description, category, and line items. Make sure to attach any supporting documents before submitting.', 'video', 'https://www.youtube.com/watch?v=-lp2Kt6RJkQ', 'Requisitions', 'fa-file-alt', 1, 1, 1, '2025-12-25 12:43:02', '2025-12-25 13:35:59'),
(2, 'Understanding Approval Workflow', 'Requisitions go through multiple approval levels based on the amount and department. Track your requisition status in real-time and receive notifications when action is required.', 'tip', NULL, 'Approvals', 'fa-check-circle', 2, 1, 1, '2025-12-25 12:43:02', '2025-12-25 12:43:02'),
(3, 'How to Track Your Requisition', 'View all your requisitions in the dashboard. Use filters to find specific requisitions by status, date range, or category. Click on any requisition to see detailed information and approval history.', 'article', NULL, 'Requisitions', 'fa-search', 3, 1, 1, '2025-12-25 12:43:02', '2025-12-25 12:43:02'),
(5, 'this is a test', '3dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd', 'tip', '', 'Payments', 'fa-info-circle', 0, 1, 1, '2025-12-25 13:39:03', '2025-12-25 13:39:03'),
(6, 'testing too again', 'sfffffffsssssssssssss', 'video', 'https://www.youtube.com/watch?v=-lp2Kt6RJkQ', 'Settings', 'fa-info-circle', 0, 1, 1, '2025-12-25 13:39:48', '2025-12-25 13:39:48'),
(7, 'ssfgss', 'sdsdsdsd', 'tip', '', 'Reports', 'fa-info-circle', 0, 1, 1, '2025-12-25 13:40:15', '2025-12-25 13:40:15');

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

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `requisition_id`, `notification_type`, `title`, `message`, `is_read`, `is_email_sent`, `email_sent_at`, `email_error`, `created_at`, `read_at`) VALUES
(0, 5, 1, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00001 has been submitted for approval. Amount: ₦10,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:45:29', NULL),
(0, 13, 1, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00001 has been submitted for approval. Amount: ₦10,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:45:29', NULL),
(0, 5, 2, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00002 has been submitted for approval. Amount: ₦20,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:46:56', NULL),
(0, 13, 2, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00002 has been submitted for approval. Amount: ₦20,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:46:56', NULL),
(0, 5, 3, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00003 has been submitted for approval. Amount: ₦7,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:47:48', NULL),
(0, 13, 3, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00003 has been submitted for approval. Amount: ₦7,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:47:48', NULL),
(0, 13, 1, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00001 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:48:52', NULL),
(0, 2, 1, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00001 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:48:52', NULL),
(0, 13, 3, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00003 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:48:58', NULL),
(0, 2, 3, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00003 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:48:58', NULL),
(0, 13, 2, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00002 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:49:06', NULL),
(0, 2, 2, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00002 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:49:06', NULL),
(0, 13, 3, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00003 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:50:01', NULL),
(0, 3, 3, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00003 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:50:01', NULL),
(0, 13, 2, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00002 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:50:08', NULL),
(0, 3, 2, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00002 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:50:08', NULL),
(0, 13, 1, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00001 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:50:16', NULL),
(0, 3, 1, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00001 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:50:16', NULL),
(0, 13, 3, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00003 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:51:01', NULL),
(0, 4, 3, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00003 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:51:01', NULL),
(0, 13, 2, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00002 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:51:07', NULL),
(0, 4, 2, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00002 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:51:07', NULL),
(0, 13, 1, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00001 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:51:13', NULL),
(0, 4, 1, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00001 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:51:13', NULL),
(0, 13, 3, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00003. Amount: ₦7,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:51:54', NULL),
(0, 3, 3, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00003. Amount: ₦7,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:51:54', NULL),
(0, 17, 3, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00003. Amount: ₦7,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:51:54', NULL),
(0, 16, 3, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00003. Amount: ₦7,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:51:54', NULL),
(0, 13, 2, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00002. Amount: ₦20,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:52:09', NULL),
(0, 3, 2, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00002. Amount: ₦20,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:52:09', NULL),
(0, 17, 2, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00002. Amount: ₦20,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:52:09', NULL),
(0, 16, 2, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00002. Amount: ₦20,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:52:09', NULL),
(0, 13, 1, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00001. Amount: ₦10,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:52:21', NULL),
(0, 3, 1, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00001. Amount: ₦10,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:52:21', NULL),
(0, 17, 1, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00001. Amount: ₦10,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:52:21', NULL),
(0, 16, 1, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00001. Amount: ₦10,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 14:52:21', NULL),
(0, 11, 4, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00004 has been submitted for approval. Amount: ₦4,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:48:22', NULL),
(0, 10, 4, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00004 has been submitted for approval. Amount: ₦4,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:48:23', NULL),
(0, 11, 5, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00005 has been submitted for approval. Amount: ₦2,300.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:49:01', NULL),
(0, 10, 5, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00005 has been submitted for approval. Amount: ₦2,300.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:49:01', NULL),
(0, 11, 6, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00006 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:49:45', NULL),
(0, 10, 6, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00006 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:49:45', NULL),
(0, 10, 6, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00006 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:50:16', NULL),
(0, 2, 6, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00006 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:50:16', NULL),
(0, 10, 5, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00005 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:50:22', NULL),
(0, 2, 5, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00005 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:50:22', NULL),
(0, 10, 4, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00004 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:50:27', NULL),
(0, 2, 4, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00004 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:50:27', NULL),
(0, 10, 6, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00006 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:50:50', NULL),
(0, 3, 6, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00006 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:50:50', NULL),
(0, 10, 5, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00005 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:50:55', NULL),
(0, 3, 5, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00005 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:50:55', NULL),
(0, 10, 4, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00004 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:51:00', NULL),
(0, 3, 4, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00004 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:51:00', NULL),
(0, 10, 6, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00006 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:51:37', NULL),
(0, 4, 6, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00006 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:51:37', NULL),
(0, 10, 5, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00005 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:51:45', NULL),
(0, 4, 5, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00005 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:51:45', NULL),
(0, 10, 4, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00004 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:51:51', NULL),
(0, 4, 4, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00004 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:51:51', NULL),
(0, 10, 6, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00006. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:52:09', NULL),
(0, 3, 6, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00006. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:52:09', NULL),
(0, 17, 6, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00006. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:52:09', NULL),
(0, 16, 6, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00006. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:52:09', NULL),
(0, 10, 5, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00005. Amount: ₦2,300.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:52:23', NULL),
(0, 3, 5, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00005. Amount: ₦2,300.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:52:23', NULL),
(0, 17, 5, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00005. Amount: ₦2,300.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:52:24', NULL),
(0, 16, 5, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00005. Amount: ₦2,300.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:52:24', NULL),
(0, 10, 4, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00004. Amount: ₦4,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:52:36', NULL),
(0, 3, 4, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00004. Amount: ₦4,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:52:36', NULL),
(0, 17, 4, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00004. Amount: ₦4,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:52:36', NULL),
(0, 16, 4, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00004. Amount: ₦4,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-19 15:52:36', NULL),
(0, 5, 7, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00007 has been submitted for approval. Amount: ₦1,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-21 22:19:53', NULL),
(0, 13, 7, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00007 has been submitted for approval. Amount: ₦1,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-21 22:19:53', NULL),
(0, 5, 8, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00008 has been submitted for approval. Amount: ₦13,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-21 22:29:22', NULL),
(0, 13, 8, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00008 has been submitted for approval. Amount: ₦13,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-21 22:29:22', NULL),
(0, 5, 9, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00009 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-21 22:34:05', NULL),
(0, 13, 9, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00009 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-21 22:34:05', NULL),
(0, 5, 10, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00010 has been submitted for approval. Amount: ₦28,800.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-22 10:11:14', NULL),
(0, 13, 10, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00010 has been submitted for approval. Amount: ₦28,800.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-22 10:11:14', NULL),
(0, 13, 10, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00010 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-22 10:46:13', NULL),
(0, 2, 10, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00010 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-22 10:46:13', NULL),
(0, 13, 10, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00010 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 09:29:10', NULL),
(0, 3, 10, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00010 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 09:29:10', NULL),
(0, 13, 10, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00010 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 09:30:38', NULL),
(0, 4, 10, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00010 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 09:30:38', NULL),
(0, 13, 8, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00008 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 09:32:57', NULL),
(0, 2, 8, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00008 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 09:32:57', NULL),
(0, 5, 11, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00011 has been submitted for approval. Amount: ₦13,500.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 09:41:03', NULL),
(0, 13, 11, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00011 has been submitted for approval. Amount: ₦13,500.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 09:41:03', NULL),
(0, 13, 8, 'requisition_rejected', '[GateWey Requisitions] Requisition Rejected', 'Requisition REQ00008 has been rejected. Please review and take action.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 09:57:10', NULL),
(0, 13, 10, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00010. Amount: ₦28,800.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 10:26:27', NULL),
(0, 3, 10, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00010. Amount: ₦28,800.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 10:26:27', NULL),
(0, 17, 10, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00010. Amount: ₦28,800.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 10:26:27', NULL),
(0, 16, 10, 'requisition_paid', '[GateWey Requisitions] Requisition Payment Processed', 'Payment has been processed for requisition REQ00010. Amount: ₦28,800.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 10:26:27', NULL),
(0, 5, 8, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00008 has been submitted for approval. Amount: ₦13,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 11:11:19', NULL),
(0, 13, 8, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00008 has been submitted for approval. Amount: ₦13,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 11:11:19', NULL),
(0, 3, 12, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00012 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 21:26:39', NULL),
(0, 13, 12, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00012 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 21:26:39', NULL),
(0, 13, 12, 'requisition_rejected', '[GateWey Requisitions] Requisition Rejected', 'Requisition REQ00012 has been rejected. Please review and take action.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 21:29:11', NULL),
(0, 2, 13, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00013 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 21:33:32', NULL),
(0, 3, 13, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00013 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-23 21:33:32', NULL),
(0, 3, 12, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00012 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-24 08:57:25', NULL),
(0, 13, 12, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00012 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-24 08:57:25', NULL),
(0, 13, 12, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00012 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-24 09:06:03', NULL),
(0, 4, 12, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00012 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-24 09:06:03', NULL),
(0, 2, 14, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00014 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-24 09:57:45', NULL),
(0, 13, 14, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00014 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-24 09:57:45', NULL),
(0, 2, 15, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00015 has been submitted for approval. Amount: ₦10,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-24 10:04:41', NULL),
(0, 13, 15, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00015 has been submitted for approval. Amount: ₦10,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-24 10:04:41', NULL),
(0, 2, 16, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00016 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-24 10:09:24', NULL),
(0, 13, 16, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00016 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-24 10:09:24', NULL),
(0, 13, 16, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00016 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-24 10:30:20', NULL),
(0, 3, 16, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00016 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-24 10:30:20', NULL),
(0, 13, 16, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00016 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-24 10:31:04', NULL),
(0, 4, 16, 'requisition_approved', '[GateWey Requisitions] Requisition Approved', 'Requisition REQ00016 has been approved and moved to the next stage.', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-24 10:31:04', NULL),
(0, 5, 17, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00017 has been submitted for approval. Amount: ₦1,999.01', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-25 07:11:26', NULL),
(0, 13, 17, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00017 has been submitted for approval. Amount: ₦1,999.01', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-25 07:11:27', NULL),
(0, 5, 18, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00018 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-25 07:32:48', NULL),
(0, 13, 18, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00018 has been submitted for approval. Amount: ₦2,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-25 07:32:49', NULL),
(0, 3, 19, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00019 has been submitted for approval. Amount: ₦172,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-25 18:04:29', NULL),
(0, 13, 19, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00019 has been submitted for approval. Amount: ₦172,000.00', 0, 0, '2025-12-25 18:04:30', 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-25 18:04:29', NULL);
INSERT INTO `notifications` (`id`, `user_id`, `requisition_id`, `notification_type`, `title`, `message`, `is_read`, `is_email_sent`, `email_sent_at`, `email_error`, `created_at`, `read_at`) VALUES
(0, 17, 20, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00020 has been submitted for approval. Amount: ₦2,000.00', 0, 0, NULL, 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-25 19:15:04', NULL),
(0, 13, 20, 'requisition_submitted', '[GateWey Requisitions] New Requisition Submitted', 'Requisition REQ00020 has been submitted for approval. Amount: ₦2,000.00', 0, 0, NULL, 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-25 19:15:05', NULL),
(0, 18, 21, 'requisition_submitted', '[Kadick Finance] New Requisition Submitted', 'Requisition REQ00021 has been submitted for approval. Amount: ₦2,000.00', 0, 0, NULL, 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-26 17:24:20', NULL),
(0, 13, 21, 'requisition_submitted', '[Kadick Finance] New Requisition Submitted', 'Requisition REQ00021 has been submitted for approval. Amount: ₦2,000.00', 0, 0, NULL, 'Email Error: SMTP Error: Could not connect to SMTP host. Failed to connect to server SMTP server error: Failed to connect to server SMTP code: 111 Additional SMTP info: Connection refused', '2025-12-26 17:24:22', NULL);

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
  `additional_info` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON data for account details (account_type, account_name, bank_name, account_number)',
  `total_amount` decimal(15,2) NOT NULL,
  `category_id` int DEFAULT NULL,
  `status` enum('draft','pending_line_manager','pending_md','pending_finance_manager','approved_for_payment','paid','completed','rejected','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `current_approver_id` int DEFAULT NULL,
  `selected_approver_id` int DEFAULT NULL COMMENT 'ID of the approver selected by requester (Line Manager, Executive, or Finance Manager)',
  `assigned_finance_member_id` int DEFAULT NULL COMMENT 'ID of Finance Member assigned by Finance Manager for payment processing (optional)',
  `assigned_by_id` int DEFAULT NULL COMMENT 'ID of user (Finance Manager) who assigned the Finance Member',
  `assigned_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp when Finance Member was assigned',
  `assignment_notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Optional notes from Finance Manager about the assignment',
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

INSERT INTO `requisitions` (`id`, `requisition_number`, `user_id`, `department_id`, `purpose`, `description`, `additional_info`, `total_amount`, `category_id`, `status`, `current_approver_id`, `selected_approver_id`, `assigned_finance_member_id`, `assigned_by_id`, `assigned_at`, `assignment_notes`, `rejection_reason`, `rejected_by_id`, `rejected_at`, `payment_date`, `payment_method`, `payment_reference`, `payment_notes`, `receipt_uploaded`, `receipt_uploaded_at`, `is_draft`, `created_at`, `updated_at`, `submitted_at`, `paid_by`, `receipt_file_path`, `receipt_file_name`, `receipt_notes`) VALUES
(1, 'REQ00001', 13, 1, 'Overtime', NULL, NULL, 10000.00, 13, 'completed', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-19 14:52:21', 'Bank Transfer', 'torkkss234ksiifks1243', '', 0, '2025-12-19 14:54:11', 0, '2025-12-19 14:45:29', '2025-12-19 19:52:49', '2025-12-19 14:45:29', 12, NULL, NULL, ''),
(2, 'REQ00002', 13, 1, 'Drinking Water', 'for the office', NULL, 20000.00, 17, 'completed', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-19 14:52:09', 'Bank Transfer', 'torkkss234ksiifks1243', '', 0, '2025-12-19 14:54:30', 0, '2025-12-19 14:46:56', '2025-12-19 14:54:30', '2025-12-19 14:46:56', 12, NULL, NULL, ''),
(3, 'REQ00003', 13, 1, 'Overtime', '', NULL, 7000.00, 13, 'completed', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-19 14:51:54', 'Bank Transfer', 'torkks234ksiifks1243', '', 0, '2025-12-19 14:54:48', 0, '2025-12-19 14:47:48', '2025-12-19 14:54:48', '2025-12-19 14:47:48', 12, NULL, NULL, ''),
(4, 'REQ00004', 10, 9, 'Cleaning', '', NULL, 4000.00, 18, 'completed', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-19 15:52:36', 'Check', 'torkks234ksiifks', '', 0, '2025-12-19 15:53:20', 0, '2025-12-19 15:48:22', '2025-12-19 15:53:20', '2025-12-19 15:48:22', 12, NULL, NULL, ''),
(5, 'REQ00005', 10, 9, 'Staff Uniform', '', NULL, 2300.00, 9, 'completed', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-19 15:52:23', 'Bank Transfer', 'torkks234ksiifks11', '', 0, '2025-12-19 15:53:53', 0, '2025-12-19 15:49:01', '2025-12-19 15:53:53', '2025-12-19 15:49:01', 12, NULL, NULL, ''),
(6, 'REQ00006', 10, 9, 'Staff Uniform', '', NULL, 2000.00, 9, 'completed', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-19 15:52:09', 'Credit Card', 'torkks234ksiifks', '', 0, '2025-12-19 15:53:36', 0, '2025-12-19 15:49:45', '2025-12-19 15:53:36', '2025-12-19 15:49:45', 12, NULL, NULL, ''),
(7, 'REQ00007', 13, 1, 'Electricity', '', NULL, 1000.00, 20, 'pending_line_manager', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-21 22:19:53', '2025-12-21 22:19:53', '2025-12-21 22:19:53', NULL, NULL, NULL, NULL),
(8, 'REQ00008', 13, 1, 'Salaries and Wages', 'this is for wages and what not', NULL, 13000.00, 2, 'pending_line_manager', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-21 22:29:19', '2025-12-23 11:11:19', '2025-12-23 11:11:19', NULL, NULL, NULL, NULL),
(9, 'REQ00009', 13, 1, 'Leave Bonus', '', NULL, 2000.00, 3, 'pending_line_manager', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-21 22:34:05', '2025-12-21 22:34:05', '2025-12-21 22:34:05', NULL, NULL, NULL, NULL),
(10, 'REQ00010', 13, 1, 'Drinking Water', '24 Cway for the month', NULL, 28800.00, 17, 'completed', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-23 10:26:27', 'Bank Transfer', 'erteresddfsd', '', 0, '2025-12-25 18:10:17', 0, '2025-12-22 10:11:14', '2025-12-25 18:10:17', '2025-12-22 10:11:14', 12, NULL, NULL, ''),
(11, 'REQ00011', 13, 1, 'Drinking Water', '', NULL, 13500.00, 17, 'pending_line_manager', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-23 09:41:03', '2025-12-23 09:41:03', '2025-12-23 09:41:03', NULL, NULL, NULL, NULL),
(12, 'REQ00012', 13, 1, 'Rent Expense', '', NULL, 2000.00, 15, 'approved_for_payment', 4, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-23 21:26:39', '2025-12-24 09:06:03', '2025-12-24 08:57:25', NULL, NULL, NULL, NULL),
(13, 'REQ00013', 3, 3, 'Employee Benefit Pension', '', NULL, 2000.00, 8, 'pending_md', 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-23 21:33:32', '2025-12-23 21:33:32', '2025-12-23 21:33:32', NULL, NULL, NULL, NULL),
(14, 'REQ00014', 13, 1, 'Employee Benefit Pension', '', NULL, 2000.00, 8, 'pending_md', 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-24 09:57:45', '2025-12-24 09:57:45', '2025-12-24 09:57:45', NULL, NULL, NULL, NULL),
(15, 'REQ00015', 13, 1, 'Security Costs', 'this is to pay the security guard', NULL, 10000.00, 19, 'pending_md', 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-24 10:04:41', '2025-12-24 10:04:41', '2025-12-24 10:04:41', NULL, NULL, NULL, NULL),
(16, 'REQ00016', 13, 1, 'Rates', 'this is just hourly rates', '{\"account_type\":\"vendor\",\"account_name\":\"Paul\",\"bank_name\":\"Fidelity\",\"account_number\":\"1234567890\"}', 2000.00, 16, 'approved_for_payment', 4, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-24 10:09:24', '2025-12-24 10:31:04', '2025-12-24 10:09:24', NULL, NULL, NULL, NULL),
(17, 'REQ00017', 13, 1, 'Electricity', '', '{\"account_type\":\"staff\",\"account_name\":\"Paul\",\"bank_name\":\"Fidelity\",\"account_number\":\"1234567890\"}', 1999.01, 20, 'pending_line_manager', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-25 07:11:26', '2025-12-25 07:11:26', '2025-12-25 07:11:26', NULL, NULL, NULL, NULL),
(18, 'REQ00018', 13, 1, 'Drinking Water', '', '{\"account_type\":\"staff\",\"account_name\":\"Paul\",\"bank_name\":\"Fidelity\",\"account_number\":\"1234567890\"}', 2000.00, 17, 'pending_line_manager', 5, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-25 07:32:48', '2025-12-25 07:32:48', '2025-12-25 07:32:48', NULL, NULL, NULL, NULL),
(19, 'REQ00019', 13, 1, 'Rent Expense', 'Optional', '{\"account_type\":\"staff\",\"account_name\":\"Peter Madumere\",\"bank_name\":\"First Bank\",\"account_number\":\"1234567890\"}', 172000.00, 15, 'pending_finance_manager', 3, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-25 18:04:26', '2025-12-25 18:04:26', '2025-12-25 18:04:26', NULL, NULL, NULL, NULL),
(20, 'REQ00020', 13, 1, 'Employee Benefit Pension', '', '{\"account_type\":\"vendor\",\"account_name\":\"Paul\",\"bank_name\":\"First Bank\",\"account_number\":\"1234567890\"}', 2000.00, 8, 'pending_finance_manager', 17, 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-25 19:15:04', '2025-12-25 19:15:04', '2025-12-25 19:15:04', NULL, NULL, NULL, NULL),
(21, 'REQ00021', 13, 1, 'FACILITIES COSTS', '', '{\"account_type\":\"staff\",\"account_name\":\"Peter Madumere\",\"bank_name\":\"First Bank\",\"account_number\":\"1234567890\"}', 2000.00, NULL, 'pending_md', 18, 18, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, '2025-12-26 17:24:20', '2025-12-26 17:24:20', '2025-12-26 17:24:20', NULL, NULL, NULL, NULL);

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
(1, 1, 5, 'Line Manager', 'approved', '', NULL, '2025-12-19 14:48:52'),
(2, 3, 5, 'Line Manager', 'approved', '', NULL, '2025-12-19 14:48:58'),
(3, 2, 5, 'Line Manager', 'approved', '', NULL, '2025-12-19 14:49:06'),
(4, 3, 2, 'Managing Director', 'approved', '', NULL, '2025-12-19 14:50:01'),
(5, 2, 2, 'Managing Director', 'approved', '', NULL, '2025-12-19 14:50:08'),
(6, 1, 2, 'Managing Director', 'approved', '', NULL, '2025-12-19 14:50:15'),
(7, 3, 3, 'Finance Manager', 'approved', '', NULL, '2025-12-19 14:51:01'),
(8, 2, 3, 'Finance Manager', 'approved', '', NULL, '2025-12-19 14:51:07'),
(9, 1, 3, 'Finance Manager', 'approved', '', NULL, '2025-12-19 14:51:13'),
(10, 6, 11, 'Line Manager', 'approved', '', NULL, '2025-12-19 15:50:16'),
(11, 5, 11, 'Line Manager', 'approved', '', NULL, '2025-12-19 15:50:22'),
(12, 4, 11, 'Line Manager', 'approved', '', NULL, '2025-12-19 15:50:27'),
(13, 6, 2, 'Managing Director', 'approved', '', NULL, '2025-12-19 15:50:50'),
(14, 5, 2, 'Managing Director', 'approved', '', NULL, '2025-12-19 15:50:55'),
(15, 4, 2, 'Managing Director', 'approved', '', NULL, '2025-12-19 15:51:00'),
(16, 6, 3, 'Finance Manager', 'approved', '', NULL, '2025-12-19 15:51:37'),
(17, 5, 3, 'Finance Manager', 'approved', '', NULL, '2025-12-19 15:51:45'),
(18, 4, 3, 'Finance Manager', 'approved', '', NULL, '2025-12-19 15:51:51'),
(19, 10, 5, 'Line Manager', 'approved', '', NULL, '2025-12-22 10:46:13'),
(20, 10, 2, 'Managing Director', 'approved', 'this is good to fly', NULL, '2025-12-23 09:29:10'),
(21, 10, 3, 'Finance Manager', 'approved', 'Godspower, please Approve', NULL, '2025-12-23 09:30:38'),
(22, 8, 5, 'Line Manager', 'approved', '', NULL, '2025-12-23 09:32:57'),
(23, 8, 2, 'Managing Director', 'rejected', 'this needs more information', NULL, '2025-12-23 09:57:10'),
(24, 12, 3, 'Finance Manager', 'rejected', 'this is the new flow rejection', NULL, '2025-12-23 21:29:11'),
(25, 12, 3, 'Finance Manager', 'approved', '', NULL, '2025-12-24 09:06:03'),
(26, 16, 2, 'Managing Director', 'approved', '', NULL, '2025-12-24 10:30:20'),
(27, 16, 3, 'Finance Manager', 'approved', '', NULL, '2025-12-24 10:31:04');

-- --------------------------------------------------------

--
-- Table structure for table `requisition_categories`
--

CREATE TABLE `requisition_categories` (
  `id` int NOT NULL,
  `parent_id` int DEFAULT NULL,
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

INSERT INTO `requisition_categories` (`id`, `parent_id`, `category_name`, `category_code`, `description`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, NULL, 'STAFF COSTS', 'STAFF_COSTS', '', 1, 0, '2025-12-19 14:33:15', '2025-12-19 14:33:15'),
(2, 1, 'Salaries and Wages', 'Salaries and Wages', '', 1, 0, '2025-12-19 14:33:32', '2025-12-19 14:33:59'),
(3, 1, 'Leave Bonus', 'Leave Bonus', '', 1, 0, '2025-12-19 14:34:28', '2025-12-19 14:34:52'),
(4, 1, 'Health Insurance Premium', 'Health Insurance Premium', '', 1, 0, '2025-12-19 14:35:06', '2025-12-19 14:35:06'),
(5, 1, 'Bonus Sharing', 'Bonus Sharing', '', 1, 0, '2025-12-19 14:35:28', '2025-12-19 14:35:28'),
(6, 1, 'Health Insurance Employee Contribution', 'Health Insurance Employee Contribution', '', 1, 0, '2025-12-19 14:35:51', '2025-12-19 14:35:51'),
(7, 1, 'Medical Expenses', 'Medical Expenses', '', 1, 0, '2025-12-19 14:36:09', '2025-12-19 14:36:09'),
(8, 1, 'Employee Benefit Pension', 'Employee Benefit Pension', '', 1, 0, '2025-12-19 14:36:32', '2025-12-19 14:36:32'),
(9, 1, 'Staff Uniform', 'Staff Uniform', '', 1, 0, '2025-12-19 14:36:46', '2025-12-19 14:36:46'),
(10, 1, 'Managers Rent', 'Managers Rent', '', 1, 0, '2025-12-19 14:37:00', '2025-12-19 14:37:32'),
(11, 1, 'Staff Training', 'Staff Training', '', 1, 0, '2025-12-19 14:37:47', '2025-12-19 14:37:47'),
(12, 1, 'long service award', 'long service award', '', 1, 0, '2025-12-19 14:38:14', '2025-12-19 14:38:14'),
(13, 1, 'Overtime', 'Overtime', '', 1, 0, '2025-12-19 14:38:26', '2025-12-19 14:38:26'),
(14, NULL, 'FACILITIES COSTS', 'FACILITIES COSTS', '', 1, 0, '2025-12-19 14:38:53', '2025-12-19 14:38:53'),
(15, 14, 'Rent Expense', 'Rent Expense', '', 1, 0, '2025-12-19 14:39:12', '2025-12-19 14:39:12'),
(16, 14, 'Rates', 'Rates', '', 1, 0, '2025-12-19 14:39:29', '2025-12-19 14:39:29'),
(17, 14, 'Drinking Water', 'Drinking Water', '', 1, 0, '2025-12-19 14:39:45', '2025-12-19 14:39:45'),
(18, 14, 'Cleaning', 'Cleaning', '', 1, 0, '2025-12-19 14:39:59', '2025-12-19 14:39:59'),
(19, 14, 'Security Costs', 'Security Costs', '', 1, 0, '2025-12-19 14:40:13', '2025-12-19 14:40:13'),
(20, 14, 'Electricity', 'Electricity', '', 1, 0, '2025-12-19 14:40:31', '2025-12-19 14:40:31');

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
(1, 2, 'attachment', 'Screenshot 2025-10-13 142230.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/attachments/6945656029a68_1766155616.png', 29842, 'image/png', 13, '2025-12-19 14:46:56'),
(2, 3, 'invoice', 'Screenshot 2025-10-13 142230.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/invoices/6945668a36a50_1766155914.png', 29842, 'image/png', 12, '2025-12-19 14:51:54'),
(3, 2, 'invoice', 'Screenshot 2025-10-13 142230.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/invoices/694566991c24d_1766155929.png', 29842, 'image/png', 12, '2025-12-19 14:52:09'),
(4, 1, 'invoice', 'Screenshot 2025-10-13 154255.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/invoices/694566a58dafa_1766155941.png', 7853, 'image/png', 12, '2025-12-19 14:52:21'),
(5, 1, 'receipt', 'Screenshot 2025-10-13 154255.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/receipts/694567135c43f_1766156051.png', 7853, 'image/png', 13, '2025-12-19 14:54:11'),
(6, 2, 'receipt', 'Screenshot 2025-10-13 154255.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/receipts/6945672650860_1766156070.png', 7853, 'image/png', 13, '2025-12-19 14:54:30'),
(7, 3, 'receipt', 'Screenshot 2025-10-13 154255.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/receipts/69456738dff25_1766156088.png', 7853, 'image/png', 13, '2025-12-19 14:54:48'),
(8, 6, 'invoice', 'Screenshot 2025-10-13 154255.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/invoices/694574a9c11bc_1766159529.png', 7853, 'image/png', 12, '2025-12-19 15:52:09'),
(9, 5, 'invoice', 'Screenshot 2025-10-13 154255.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/invoices/694574b7e3e17_1766159543.png', 7853, 'image/png', 12, '2025-12-19 15:52:23'),
(10, 4, 'invoice', 'Screenshot 2025-10-13 154255.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/invoices/694574c48ae61_1766159556.png', 7853, 'image/png', 12, '2025-12-19 15:52:36'),
(11, 4, 'receipt', 'Screenshot 2025-10-13 154255.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/receipts/694574f087edf_1766159600.png', 7853, 'image/png', 10, '2025-12-19 15:53:20'),
(12, 6, 'receipt', 'Screenshot 2025-10-13 154255.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/receipts/6945750088cfa_1766159616.png', 7853, 'image/png', 10, '2025-12-19 15:53:36'),
(13, 5, 'receipt', 'Screenshot 2025-10-13 154255.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/receipts/69457511d5369_1766159633.png', 7853, 'image/png', 10, '2025-12-19 15:53:53'),
(14, 8, 'attachment', 'Screenshot 2025-10-13 142230.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/attachments/694874c24ebfc_1766356162.png', 29842, 'image/png', 13, '2025-12-21 22:29:22'),
(15, 10, 'invoice', 'Annotation 2025-04-17 114648.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/invoices/694a6e53ce6b3_1766485587.png', 38917, 'image/png', 12, '2025-12-23 10:26:27'),
(16, 19, 'attachment', 'Screenshot 2025-10-13 154255.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/attachments/694d7cad5968c_1766685869.png', 7853, 'image/png', 13, '2025-12-25 18:04:29'),
(17, 10, 'receipt', 'Screenshot 2025-10-13 142230.png', '/home2/gateweyc/public_html/request.gatewey.com.ng/uploads/receipts/694d7e09befa7_1766686217.png', 29842, 'image/png', 13, '2025-12-25 18:10:17');

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
(1, 1, 'Anita', 1, 5000.00, 5000.00, '2025-12-19 14:45:29', '2025-12-19 14:45:29'),
(2, 1, 'Angel', 1, 5000.00, 5000.00, '2025-12-19 14:45:29', '2025-12-19 14:45:29'),
(3, 2, 'Cway', 10, 2000.00, 20000.00, '2025-12-19 14:46:56', '2025-12-19 14:46:56'),
(4, 3, 'Joshua', 1, 5000.00, 5000.00, '2025-12-19 14:47:48', '2025-12-19 14:47:48'),
(5, 3, 'Alex', 1, 2000.00, 2000.00, '2025-12-19 14:47:48', '2025-12-19 14:47:48'),
(6, 4, 'office cleaning', 1, 2000.00, 2000.00, '2025-12-19 15:48:22', '2025-12-19 15:48:22'),
(7, 4, 'Saturday washing', 1, 2000.00, 2000.00, '2025-12-19 15:48:22', '2025-12-19 15:48:22'),
(8, 5, 'Abubaka Uniform', 1, 2300.00, 2300.00, '2025-12-19 15:49:01', '2025-12-19 15:49:01'),
(9, 6, 'frank uniform', 1, 2000.00, 2000.00, '2025-12-19 15:49:45', '2025-12-19 15:49:45'),
(10, 7, 'pipe', 1, 1000.00, 1000.00, '2025-12-21 22:19:53', '2025-12-21 22:19:53'),
(13, 9, 'testing 2', 1, 2000.00, 2000.00, '2025-12-21 22:34:05', '2025-12-21 22:34:05'),
(14, 10, 'Cway', 24, 1200.00, 28800.00, '2025-12-22 10:11:14', '2025-12-22 10:11:14'),
(15, 11, 'Cway', 12, 1000.00, 12000.00, '2025-12-23 09:41:03', '2025-12-23 09:41:03'),
(16, 11, 'Bottle water', 3, 500.00, 1500.00, '2025-12-23 09:41:03', '2025-12-23 09:41:03'),
(17, 8, 'what not', 1, 1000.00, 1000.00, '2025-12-23 11:11:19', '2025-12-23 11:11:19'),
(18, 8, 'not what', 12, 1000.00, 12000.00, '2025-12-23 11:11:19', '2025-12-23 11:11:19'),
(20, 13, 'Finance Flow', 1, 2000.00, 2000.00, '2025-12-23 21:33:32', '2025-12-23 21:33:32'),
(21, 12, 'testing flow', 1, 2000.00, 2000.00, '2025-12-24 08:57:25', '2025-12-24 08:57:25'),
(22, 14, 'testing', 1, 2000.00, 2000.00, '2025-12-24 09:57:45', '2025-12-24 09:57:45'),
(23, 15, 'alex', 1, 10000.00, 10000.00, '2025-12-24 10:04:41', '2025-12-24 10:04:41'),
(24, 16, 'Keyboard', 1, 2000.00, 2000.00, '2025-12-24 10:09:24', '2025-12-24 10:09:24'),
(25, 17, 'Keyboard', 1, 1999.01, 1999.01, '2025-12-25 07:11:26', '2025-12-25 07:11:26'),
(26, 18, 'Keyboard', 1, 2000.00, 2000.00, '2025-12-25 07:32:48', '2025-12-25 07:32:48'),
(27, 19, 'Expenses for the office', 11, 2000.00, 22000.00, '2025-12-25 18:04:26', '2025-12-25 18:04:26'),
(28, 19, 'Expenses for the office again', 5, 30000.00, 150000.00, '2025-12-25 18:04:26', '2025-12-25 18:04:26'),
(29, 20, 'Keyboard', 1, 2000.00, 2000.00, '2025-12-25 19:15:04', '2025-12-25 19:15:04'),
(30, 21, 'testing 2', 1, 2000.00, 2000.00, '2025-12-26 17:24:20', '2025-12-26 17:24:20');

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
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `role_code`, `description`, `can_raise_requisition`, `can_approve`, `can_view_all`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'SUPER_ADMIN', 'System administrator with full access to all features. Can manage users, departments, and system settings. Cannot raise requisitions.', 0, 0, 1, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(2, 'Managing Director', 'MD', 'Organization-wide oversight and approval authority. Can approve requisitions from Line Managers and Team Members. Can raise requisitions that go directly to Finance Manager. Can generate organization-wide reports.', 1, 1, 1, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(3, 'Finance Manager', 'FINANCE_MGR', 'Reviews all approved requisitions before payment processing. Can reject requisitions back to previous approvers. Oversees payment processing. Can generate organization-wide reports.', 1, 1, 1, 1, '2025-11-24 13:55:52', '2025-12-26 13:58:57'),
(4, 'Finance Member', 'FINANCE_MEM', 'Processes requisitions approved by Finance Manager. Uploads invoices and proof of payment. Marks requisitions as paid. Views receipts after payment.', 0, 0, 1, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(5, 'Line Manager', 'LINE_MGR', 'Department manager with approval authority. Approves requisitions from team members. Can raise requisitions that require MD approval. Can generate department reports.', 1, 1, 0, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(6, 'Team Member', 'TEAM_MEM', 'Raises requisitions for department needs. Requisitions require Line Manager and MD approval. Can generate personal reports.', 1, 0, 0, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(7, 'Project Manager', 'PG', '', 1, 1, 1, 1, '2025-12-26 13:58:37', '2025-12-26 13:58:37');

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
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `twofa_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Secret key for TOTP 2FA',
  `twofa_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether 2FA is enabled for this user',
  `twofa_verified_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp when 2FA was last verified/setup'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `department_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`, `twofa_secret`, `twofa_enabled`, `twofa_verified_at`) VALUES
(1, 1, NULL, 'System', 'Administrator', 'admin@gatewey.com', '$2y$10$a7G1KN6cEH9ycp5T/lcFGOUbNuA/CIbObIt.bvfMlPylHsAcj73mO', '+234 800 000 0000', 1, '2025-12-26 13:57:34', '2025-11-24 13:55:52', '2025-12-26 13:57:34', 'BBYIP7D2WWPUSSQI', 1, '2025-12-25 07:27:20'),
(2, 2, 10, 'John', 'Okonkwo', 'md@gatewey.com', '$2y$10$0K90qD7ST5fvx/i/CPVwpu4vLAa5Uh55wwrA5KGJheqB.rF38yFT.', '+234 801 234 5678', 1, '2025-12-23 09:28:50', '2025-11-24 13:55:52', '2025-12-23 09:28:50', NULL, 0, NULL),
(3, 3, 3, 'Amina', 'Yusuf', 'finance.manager@gatewey.com', '$2y$10$5.0MP/wSe1ifUTKnN.FMI.yHftQDlPl6isCdPb28CEuiWyt0eKoaK', '+234 802 345 6789', 1, '2025-12-24 10:30:51', '2025-11-24 13:55:52', '2025-12-24 10:30:51', NULL, 0, NULL),
(4, 4, 3, 'Chidinma', 'Okafor', 'finance.member@gatewey.com', '$2y$10$Pgk6rsEvor7A.cwT5YRw0e/S0.nJSPX8yPS/46SZXrnuOOcl0Sb.u', '+234 803 456 7890', 1, '2025-12-24 09:07:31', '2025-11-24 13:55:52', '2025-12-24 09:07:31', NULL, 0, NULL),
(5, 5, 1, 'Emekaa', 'Nwankwo', 'nnanamadumere@gmail.com', '$2y$10$aSMiTWqUPZ/TVAfUUHQl..lUjofotRg.nmgZzFOze4NHwdq5U0CGa', '+234 804 567 8901', 1, '2025-12-23 21:27:06', '2025-11-24 13:55:52', '2025-12-23 21:27:06', NULL, 0, NULL),
(6, 5, 4, 'Fatimaa', 'Abubakar', 'marketing.manager@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 805 678 9012', 1, NULL, '2025-11-24 13:55:52', '2025-11-24 21:09:01', NULL, 0, NULL),
(7, 6, 1, 'Oluwaseun', 'Adebayo', 'oluwaseun.adebayo@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 806 789 0123', 0, NULL, '2025-11-24 13:55:52', '2025-11-25 13:11:50', NULL, 0, NULL),
(8, 6, 4, 'Blessing2', 'Eze', 'blessing.eze@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 807 890 1234', 0, NULL, '2025-11-24 13:55:53', '2025-11-25 13:05:33', NULL, 0, NULL),
(9, 6, 2, 'Ibrahim', 'Mohammed', 'ibrahim.mohammed@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 808 901 2345', 1, NULL, '2025-11-24 13:55:53', '2025-11-24 13:55:53', NULL, 0, NULL),
(10, 6, 9, 'Peter', 'Madumere', 'nnana@gmail.com', '$2y$10$o2OEslS8q4Wa/xeMKThnjuOVy1oIAfuI/FofROzQOvD5xJEXBFqte', '09069225818', 1, '2025-12-21 22:08:03', '2025-11-25 13:20:44', '2025-12-21 22:08:03', NULL, 0, NULL),
(11, 5, 9, 'Peter', 'Madumere', 'peter@gamil.com', '$2y$10$pqzePl73WbifAxJo0SZLNOPZdJ/HxHuM3p6Tp/HVUQgq7df6Hp8bK', '+2349069225818', 1, '2025-12-19 15:50:04', '2025-12-07 15:38:12', '2025-12-19 15:50:04', NULL, 0, NULL),
(12, 4, 3, 'Mary', 'Madumere', 'mary@gmail.com', '$2y$10$JSpuQ9QMRtTD8sVFX1Jr0OJOYm7fQCgob2JDe0XxIhnsrB2se0NEi', '+234 816 035 3627', 1, '2025-12-23 10:24:19', '2025-12-07 17:01:13', '2025-12-23 10:24:19', NULL, 0, NULL),
(13, 6, 1, 'Paul', 'Madumere', 'madumerepeter.u@gmail.com', '$2y$10$iF3jld.DEdHrNg4Ulz/AwuxgeHkDHVQU/V3PS96tpea6Tpgyzm4pO', '+2349069225818', 1, '2025-12-26 17:23:41', '2025-12-10 13:00:11', '2025-12-26 17:23:41', 'YE4UVLDOYUK7HC5X', 1, '2025-12-25 07:03:57'),
(15, 5, 1, 'Peter', 'Madumere', 'petermadumere7485@gmail.com', '$2y$10$7W2lj1TzHx9z0JJORZhK6.TCLnMCVe8wWOYelbBg4cuimePKRw88u', '+2349069225818', 1, '2025-12-11 19:54:16', '2025-12-11 19:54:00', '2025-12-11 19:54:16', NULL, 0, NULL),
(16, 3, 3, 'JOSHUA', 'UWAKWE', 'joshua.u@kadickintegrated.com', '$2y$10$O5vwac1a.LBo.GCYFQkwquoUQoHFkAd1v5wxw4Q4mvAW4OJuWOBcm', '08032034293', 1, NULL, '2025-12-12 08:51:34', '2025-12-12 08:51:34', NULL, 0, NULL),
(17, 3, 3, 'EMEKA', 'EMEKA', 'emeka.n@kadickintegrated.com', '$2y$10$4/2s0gabIVFuIMp.qzF3gO8X43zVdUjDm1oGjDwTPzRQvzIieK/Qm', '08032034284', 1, '2025-12-19 11:59:11', '2025-12-12 10:39:16', '2025-12-19 11:59:11', NULL, 0, NULL),
(18, 7, 10, 'Anthony', 'Paul', 'anthony@gmail.com', '$2y$10$VtmRWEn8OmfFhakyoBsBIuXxfEwtChl3eLc8d.8vjYwBovqraaJYC', '123456782321', 1, NULL, '2025-12-26 14:24:31', '2025-12-26 14:24:31', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_active_users`
-- (See below for the actual view)
--
CREATE TABLE `v_active_users` (
`created_at` timestamp
,`department_code` varchar(20)
,`department_name` varchar(100)
,`email` varchar(100)
,`first_name` varchar(50)
,`full_name` varchar(101)
,`id` int
,`last_login` timestamp
,`last_name` varchar(50)
,`phone` varchar(20)
,`role_code` varchar(20)
,`role_name` varchar(50)
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
-- Indexes for table `budget_allocations`
--
ALTER TABLE `budget_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_requisition` (`requisition_id`),
  ADD KEY `idx_budget_type` (`budget_id`,`allocation_type`),
  ADD KEY `idx_allocation_type` (`allocation_type`,`allocated_at`);

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
-- Indexes for table `department_budgets`
--
ALTER TABLE `department_budgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_department_dates` (`department_id`,`start_date`,`end_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_budget_status` (`status`,`end_date`);

--
-- Indexes for table `help_support`
--
ALTER TABLE `help_support`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_display_order` (`display_order`),
  ADD KEY `idx_created_by` (`created_by`);

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
  ADD KEY `fk_requisitions_paid_by` (`paid_by`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_selected_approver` (`selected_approver_id`),
  ADD KEY `idx_assigned_finance_member` (`assigned_finance_member_id`),
  ADD KEY `idx_assigned_by` (`assigned_by_id`);

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
  ADD KEY `idx_display_order` (`display_order`),
  ADD KEY `idx_parent_id` (`parent_id`);

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
  ADD KEY `idx_role_code` (`role_code`),
  ADD KEY `idx_active` (`is_active`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=247;

--
-- AUTO_INCREMENT for table `budget_allocations`
--
ALTER TABLE `budget_allocations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `department_budgets`
--
ALTER TABLE `department_budgets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `help_support`
--
ALTER TABLE `help_support`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `requisitions`
--
ALTER TABLE `requisitions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `requisition_approvals`
--
ALTER TABLE `requisition_approvals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `requisition_categories`
--
ALTER TABLE `requisition_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `requisition_documents`
--
ALTER TABLE `requisition_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `requisition_items`
--
ALTER TABLE `requisition_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

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
-- Constraints for table `budget_allocations`
--
ALTER TABLE `budget_allocations`
  ADD CONSTRAINT `budget_allocations_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `department_budgets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_allocations_ibfk_2` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `department_budgets`
--
ALTER TABLE `department_budgets`
  ADD CONSTRAINT `department_budgets_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `department_budgets_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD CONSTRAINT `fk_assigned_by` FOREIGN KEY (`assigned_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_assigned_finance_member` FOREIGN KEY (`assigned_finance_member_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_requisitions_paid_by` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_selected_approver` FOREIGN KEY (`selected_approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requisitions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requisitions_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `requisitions_ibfk_3` FOREIGN KEY (`current_approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requisitions_ibfk_4` FOREIGN KEY (`rejected_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requisitions_ibfk_5` FOREIGN KEY (`category_id`) REFERENCES `requisition_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `requisition_approvals`
--
ALTER TABLE `requisition_approvals`
  ADD CONSTRAINT `requisition_approvals_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requisition_approvals_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requisition_categories`
--
ALTER TABLE `requisition_categories`
  ADD CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `requisition_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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

-- GateWey Requisition Management System
-- Database Backup
-- Generated: 25/11/2025 15:26:02
-- Database: requisition
-- MySQL Server Version: 10.4.32-MariaDB

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";



-- --------------------------------------------------------
-- Table structure for table `audit_log`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `audit_log`;
CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requisition_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_requisition` (`requisition_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `audit_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `audit_log`

INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('1', NULL, '1', 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 21:00:32');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('2', NULL, '1', 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 21:11:27');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('3', NULL, '1', 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 21:14:14');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('4', NULL, '5', 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 22:08:48');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('5', NULL, '6', 'user_updated', 'User account updated', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 22:09:01');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('6', NULL, '5', 'user_updated', 'User account updated', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 22:12:12');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('7', NULL, '1', 'user_login', 'User logged in successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 09:37:31');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('8', NULL, '8', 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 11:00:26');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('9', NULL, '8', 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 11:00:40');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('10', NULL, '8', 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 11:01:02');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('11', NULL, '8', 'user_updated', 'User account updated', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:01:40');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('12', NULL, '8', 'user_updated', 'User account updated', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:05:27');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('13', NULL, '8', 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:05:33');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('14', NULL, '8', 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:06:55');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('15', NULL, '8', 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:08:38');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('16', NULL, '8', 'user_updated', 'User account updated', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:09:27');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('17', NULL, '8', 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:09:41');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('18', NULL, '8', 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:10:42');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('19', NULL, '7', 'user_deleted', 'User account deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:11:50');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('20', NULL, '10', 'user_created', 'User account created: nnanamadumere@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:20:44');
INSERT INTO `audit_log` (`id`, `requisition_id`, `user_id`, `action`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES ('21', NULL, '10', 'user_password_changed', 'Password changed for user: nnanamadumere@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:02:23');


-- --------------------------------------------------------
-- Table structure for table `departments`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(100) NOT NULL,
  `department_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_name` (`department_name`),
  UNIQUE KEY `department_code` (`department_code`),
  KEY `idx_dept_code` (`department_code`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `departments`

INSERT INTO `departments` (`id`, `department_name`, `department_code`, `description`, `budget`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'Information Technology', 'IT', 'Manages all technology infrastructure, software development, and IT support services', '5000000.00', '1', '2025-11-24 14:55:52', '2025-11-24 14:55:52');
INSERT INTO `departments` (`id`, `department_name`, `department_code`, `description`, `budget`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'Human Resources', 'HR', 'Handles recruitment, employee relations, training, and HR administration', '2000000.00', '1', '2025-11-24 14:55:52', '2025-11-24 14:55:52');
INSERT INTO `departments` (`id`, `department_name`, `department_code`, `description`, `budget`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'Finance & Accounting', 'FIN', 'Manages financial operations, accounting, budgeting, and financial reporting', '3000000.00', '1', '2025-11-24 14:55:52', '2025-11-24 14:55:52');
INSERT INTO `departments` (`id`, `department_name`, `department_code`, `description`, `budget`, `is_active`, `created_at`, `updated_at`) VALUES ('4', 'Marketing & Sales', 'MKT', 'Drives business growth through marketing campaigns, sales strategies, and customer acquisition', '4000000.00', '1', '2025-11-24 14:55:52', '2025-11-24 14:55:52');
INSERT INTO `departments` (`id`, `department_name`, `department_code`, `description`, `budget`, `is_active`, `created_at`, `updated_at`) VALUES ('5', 'Operations', 'OPS', 'Oversees day-to-day business operations, logistics, and process improvements', '3500000.00', '1', '2025-11-24 14:55:52', '2025-11-24 14:55:52');
INSERT INTO `departments` (`id`, `department_name`, `department_code`, `description`, `budget`, `is_active`, `created_at`, `updated_at`) VALUES ('6', 'Administration', 'ADM', 'Provides general administrative support and office management', '1500000.00', '1', '2025-11-24 14:55:52', '2025-11-24 14:55:52');
INSERT INTO `departments` (`id`, `department_name`, `department_code`, `description`, `budget`, `is_active`, `created_at`, `updated_at`) VALUES ('7', 'Biology', 'BY', 'Very Good Department', '0.00', '1', '2025-11-24 22:10:40', '2025-11-24 22:10:40');


-- --------------------------------------------------------
-- Table structure for table `notifications`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_requisition` (`requisition_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_notification_type` (`notification_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `password_resets`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_used` tinyint(1) DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_token` (`token`),
  KEY `idx_user` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `password_resets`

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `is_used`, `used_at`, `ip_address`, `created_at`) VALUES ('1', '1', 'acd36621c44a8be7456df6bb8e7dce6ee4bc8ceaf24b6011a674c6b61914850e', '2025-11-24 16:56:58', '0', NULL, '::1', '2025-11-24 15:56:58');
INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `is_used`, `used_at`, `ip_address`, `created_at`) VALUES ('2', '1', 'aa90ecfd98165fe432fa100ba0219077fcf19371e738892c62d820676d1c8268', '2025-11-24 16:57:37', '0', NULL, '::1', '2025-11-24 15:57:37');


-- --------------------------------------------------------
-- Table structure for table `requisition_approvals`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `requisition_approvals`;
CREATE TABLE `requisition_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requisition_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_at_approval` varchar(50) NOT NULL,
  `action` enum('approved','rejected','returned') NOT NULL,
  `comments` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_requisition` (`requisition_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `requisition_approvals_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `requisition_approvals_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `requisition_documents`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `requisition_documents`;
CREATE TABLE `requisition_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requisition_id` int(11) NOT NULL,
  `document_type` enum('attachment','invoice','receipt','proof_of_payment') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_requisition` (`requisition_id`),
  KEY `idx_document_type` (`document_type`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  CONSTRAINT `requisition_documents_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `requisition_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `requisition_items`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `requisition_items`;
CREATE TABLE `requisition_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requisition_id` int(11) NOT NULL,
  `item_description` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_requisition` (`requisition_id`),
  CONSTRAINT `requisition_items_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `requisitions`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `requisitions`;
CREATE TABLE `requisitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requisition_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `purpose` text NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `status` enum('draft','pending_line_manager','pending_md','pending_finance_manager','approved_for_payment','paid','completed','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `current_approver_id` int(11) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `rejected_by_id` int(11) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `receipt_uploaded` tinyint(1) DEFAULT 0,
  `receipt_uploaded_at` timestamp NULL DEFAULT NULL,
  `is_draft` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `submitted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `requisition_number` (`requisition_number`),
  KEY `rejected_by_id` (`rejected_by_id`),
  KEY `idx_requisition_number` (`requisition_number`),
  KEY `idx_user` (`user_id`),
  KEY `idx_department` (`department_id`),
  KEY `idx_status` (`status`),
  KEY `idx_current_approver` (`current_approver_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_submitted_at` (`submitted_at`),
  CONSTRAINT `requisitions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `requisitions_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `requisitions_ibfk_3` FOREIGN KEY (`current_approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `requisitions_ibfk_4` FOREIGN KEY (`rejected_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure for table `roles`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `role_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `can_raise_requisition` tinyint(1) DEFAULT 1,
  `can_approve` tinyint(1) DEFAULT 0,
  `can_view_all` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`),
  UNIQUE KEY `role_code` (`role_code`),
  KEY `idx_role_code` (`role_code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `roles`

INSERT INTO `roles` (`id`, `role_name`, `role_code`, `description`, `can_raise_requisition`, `can_approve`, `can_view_all`, `created_at`, `updated_at`) VALUES ('1', 'Super Admin', 'SUPER_ADMIN', 'System administrator with full access to all features. Can manage users, departments, and system settings. Cannot raise requisitions.', '0', '0', '1', '2025-11-24 14:55:52', '2025-11-24 14:55:52');
INSERT INTO `roles` (`id`, `role_name`, `role_code`, `description`, `can_raise_requisition`, `can_approve`, `can_view_all`, `created_at`, `updated_at`) VALUES ('2', 'Managing Director', 'MD', 'Organization-wide oversight and approval authority. Can approve requisitions from Line Managers and Team Members. Can raise requisitions that go directly to Finance Manager. Can generate organization-wide reports.', '1', '1', '1', '2025-11-24 14:55:52', '2025-11-24 14:55:52');
INSERT INTO `roles` (`id`, `role_name`, `role_code`, `description`, `can_raise_requisition`, `can_approve`, `can_view_all`, `created_at`, `updated_at`) VALUES ('3', 'Finance Manager', 'FINANCE_MGR', 'Reviews all approved requisitions before payment processing. Can reject requisitions back to previous approvers. Oversees payment processing. Can generate organization-wide reports.', '0', '1', '1', '2025-11-24 14:55:52', '2025-11-24 14:55:52');
INSERT INTO `roles` (`id`, `role_name`, `role_code`, `description`, `can_raise_requisition`, `can_approve`, `can_view_all`, `created_at`, `updated_at`) VALUES ('4', 'Finance Member', 'FINANCE_MEM', 'Processes requisitions approved by Finance Manager. Uploads invoices and proof of payment. Marks requisitions as paid. Views receipts after payment.', '0', '0', '1', '2025-11-24 14:55:52', '2025-11-24 14:55:52');
INSERT INTO `roles` (`id`, `role_name`, `role_code`, `description`, `can_raise_requisition`, `can_approve`, `can_view_all`, `created_at`, `updated_at`) VALUES ('5', 'Line Manager', 'LINE_MGR', 'Department manager with approval authority. Approves requisitions from team members. Can raise requisitions that require MD approval. Can generate department reports.', '1', '1', '0', '2025-11-24 14:55:52', '2025-11-24 14:55:52');
INSERT INTO `roles` (`id`, `role_name`, `role_code`, `description`, `can_raise_requisition`, `can_approve`, `can_view_all`, `created_at`, `updated_at`) VALUES ('6', 'Team Member', 'TEAM_MEM', 'Raises requisitions for department needs. Requisitions require Line Manager and MD approval. Can generate personal reports.', '1', '0', '0', '2025-11-24 14:55:52', '2025-11-24 14:55:52');


-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role_id`),
  KEY `idx_department` (`department_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users`

INSERT INTO `users` (`id`, `role_id`, `department_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('1', '1', NULL, 'System', 'Administrator', 'admin@gatewey.com', '$2y$12$1IrmI9xCl22.8kgpUGU39.GfUdhjb28eCzStjykQCEswAe6LktQsW', '+234 800 000 0000', '1', '2025-11-25 09:37:31', '2025-11-24 14:55:52', '2025-11-25 09:37:31');
INSERT INTO `users` (`id`, `role_id`, `department_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('2', '2', NULL, 'John', 'Okonkwo', 'md@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 801 234 5678', '1', NULL, '2025-11-24 14:55:52', '2025-11-24 14:55:52');
INSERT INTO `users` (`id`, `role_id`, `department_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('3', '3', '3', 'Amina', 'Yusuf', 'finance.manager@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 802 345 6789', '1', NULL, '2025-11-24 14:55:52', '2025-11-24 14:55:52');
INSERT INTO `users` (`id`, `role_id`, `department_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('4', '4', '3', 'Chidinma', 'Okafor', 'finance.member@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 803 456 7890', '1', NULL, '2025-11-24 14:55:52', '2025-11-24 14:55:52');
INSERT INTO `users` (`id`, `role_id`, `department_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('5', '5', '1', 'Emeka', 'Nwankwo', 'it.manager@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 804 567 8901', '1', NULL, '2025-11-24 14:55:52', '2025-11-24 22:12:12');
INSERT INTO `users` (`id`, `role_id`, `department_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('6', '5', '4', 'Fatimaa', 'Abubakar', 'marketing.manager@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 805 678 9012', '1', NULL, '2025-11-24 14:55:52', '2025-11-24 22:09:01');
INSERT INTO `users` (`id`, `role_id`, `department_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('7', '6', '1', 'Oluwaseun', 'Adebayo', 'oluwaseun.adebayo@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 806 789 0123', '0', NULL, '2025-11-24 14:55:52', '2025-11-25 14:11:50');
INSERT INTO `users` (`id`, `role_id`, `department_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('8', '6', '4', 'Blessing2', 'Eze', 'blessing.eze@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 807 890 1234', '0', NULL, '2025-11-24 14:55:53', '2025-11-25 14:05:33');
INSERT INTO `users` (`id`, `role_id`, `department_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('9', '6', '2', 'Ibrahim', 'Mohammed', 'ibrahim.mohammed@gatewey.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 808 901 2345', '1', NULL, '2025-11-24 14:55:53', '2025-11-24 14:55:53');
INSERT INTO `users` (`id`, `role_id`, `department_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES ('10', '6', '1', 'Peter', 'Madumere', 'nnanamadumere@gmail.com', '$2y$10$2taMO2gOQnLm2aDRj2.S5O66lovhez672rUPdGMoheJguCkgG35Fy', 'admin@gatewey.com', '1', NULL, '2025-11-25 14:20:44', '2025-11-25 15:02:23');


-- --------------------------------------------------------
-- Table structure for table `v_active_users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `v_active_users`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_active_users` AS select `u`.`id` AS `id`,`u`.`first_name` AS `first_name`,`u`.`last_name` AS `last_name`,concat(`u`.`first_name`,' ',`u`.`last_name`) AS `full_name`,`u`.`email` AS `email`,`u`.`phone` AS `phone`,`r`.`role_name` AS `role_name`,`r`.`role_code` AS `role_code`,`d`.`department_name` AS `department_name`,`d`.`department_code` AS `department_code`,`u`.`last_login` AS `last_login`,`u`.`created_at` AS `created_at` from ((`users` `u` join `roles` `r` on(`u`.`role_id` = `r`.`id`)) left join `departments` `d` on(`u`.`department_id` = `d`.`id`)) where `u`.`is_active` = 1;

-- Dumping data for table `v_active_users`

INSERT INTO `v_active_users` (`id`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `role_name`, `role_code`, `department_name`, `department_code`, `last_login`, `created_at`) VALUES ('1', 'System', 'Administrator', 'System Administrator', 'admin@gatewey.com', '+234 800 000 0000', 'Super Admin', 'SUPER_ADMIN', NULL, NULL, '2025-11-25 09:37:31', '2025-11-24 14:55:52');
INSERT INTO `v_active_users` (`id`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `role_name`, `role_code`, `department_name`, `department_code`, `last_login`, `created_at`) VALUES ('2', 'John', 'Okonkwo', 'John Okonkwo', 'md@gatewey.com', '+234 801 234 5678', 'Managing Director', 'MD', NULL, NULL, NULL, '2025-11-24 14:55:52');
INSERT INTO `v_active_users` (`id`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `role_name`, `role_code`, `department_name`, `department_code`, `last_login`, `created_at`) VALUES ('3', 'Amina', 'Yusuf', 'Amina Yusuf', 'finance.manager@gatewey.com', '+234 802 345 6789', 'Finance Manager', 'FINANCE_MGR', 'Finance & Accounting', 'FIN', NULL, '2025-11-24 14:55:52');
INSERT INTO `v_active_users` (`id`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `role_name`, `role_code`, `department_name`, `department_code`, `last_login`, `created_at`) VALUES ('4', 'Chidinma', 'Okafor', 'Chidinma Okafor', 'finance.member@gatewey.com', '+234 803 456 7890', 'Finance Member', 'FINANCE_MEM', 'Finance & Accounting', 'FIN', NULL, '2025-11-24 14:55:52');
INSERT INTO `v_active_users` (`id`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `role_name`, `role_code`, `department_name`, `department_code`, `last_login`, `created_at`) VALUES ('5', 'Emeka', 'Nwankwo', 'Emeka Nwankwo', 'it.manager@gatewey.com', '+234 804 567 8901', 'Line Manager', 'LINE_MGR', 'Information Technology', 'IT', NULL, '2025-11-24 14:55:52');
INSERT INTO `v_active_users` (`id`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `role_name`, `role_code`, `department_name`, `department_code`, `last_login`, `created_at`) VALUES ('6', 'Fatimaa', 'Abubakar', 'Fatimaa Abubakar', 'marketing.manager@gatewey.com', '+234 805 678 9012', 'Line Manager', 'LINE_MGR', 'Marketing & Sales', 'MKT', NULL, '2025-11-24 14:55:52');
INSERT INTO `v_active_users` (`id`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `role_name`, `role_code`, `department_name`, `department_code`, `last_login`, `created_at`) VALUES ('9', 'Ibrahim', 'Mohammed', 'Ibrahim Mohammed', 'ibrahim.mohammed@gatewey.com', '+234 808 901 2345', 'Team Member', 'TEAM_MEM', 'Human Resources', 'HR', NULL, '2025-11-24 14:55:53');
INSERT INTO `v_active_users` (`id`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `role_name`, `role_code`, `department_name`, `department_code`, `last_login`, `created_at`) VALUES ('10', 'Peter', 'Madumere', 'Peter Madumere', 'nnanamadumere@gmail.com', 'admin@gatewey.com', 'Team Member', 'TEAM_MEM', 'Information Technology', 'IT', NULL, '2025-11-25 14:20:44');


-- --------------------------------------------------------
-- Table structure for table `v_pending_approvals`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `v_pending_approvals`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pending_approvals` AS select `r`.`id` AS `id`,`r`.`requisition_number` AS `requisition_number`,`r`.`purpose` AS `purpose`,`r`.`total_amount` AS `total_amount`,`r`.`status` AS `status`,`r`.`current_approver_id` AS `current_approver_id`,`r`.`submitted_at` AS `submitted_at`,`r`.`created_at` AS `created_at`,concat(`u`.`first_name`,' ',`u`.`last_name`) AS `requester_name`,`d`.`department_name` AS `department_name`,to_days(current_timestamp()) - to_days(`r`.`submitted_at`) AS `days_pending` from ((`requisitions` `r` join `users` `u` on(`r`.`user_id` = `u`.`id`)) join `departments` `d` on(`r`.`department_id` = `d`.`id`)) where `r`.`current_approver_id` is not null and `r`.`status` in ('pending_line_manager','pending_md','pending_finance_manager');


-- --------------------------------------------------------
-- Table structure for table `v_requisitions_summary`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `v_requisitions_summary`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_requisitions_summary` AS select `r`.`id` AS `id`,`r`.`requisition_number` AS `requisition_number`,`r`.`purpose` AS `purpose`,`r`.`total_amount` AS `total_amount`,`r`.`status` AS `status`,`r`.`is_draft` AS `is_draft`,`r`.`created_at` AS `created_at`,`r`.`submitted_at` AS `submitted_at`,`r`.`payment_date` AS `payment_date`,`r`.`receipt_uploaded` AS `receipt_uploaded`,concat(`u`.`first_name`,' ',`u`.`last_name`) AS `requester_name`,`u`.`email` AS `requester_email`,`d`.`department_name` AS `department_name`,`d`.`department_code` AS `department_code`,concat(`approver`.`first_name`,' ',`approver`.`last_name`) AS `current_approver_name`,`r`.`rejection_reason` AS `rejection_reason`,concat(`rejected_by`.`first_name`,' ',`rejected_by`.`last_name`) AS `rejected_by_name`,`r`.`rejected_at` AS `rejected_at` from ((((`requisitions` `r` join `users` `u` on(`r`.`user_id` = `u`.`id`)) join `departments` `d` on(`r`.`department_id` = `d`.`id`)) left join `users` `approver` on(`r`.`current_approver_id` = `approver`.`id`)) left join `users` `rejected_by` on(`r`.`rejected_by_id` = `rejected_by`.`id`));

COMMIT;

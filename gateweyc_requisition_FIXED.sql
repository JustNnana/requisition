-- phpMyAdmin SQL Dump - FIXED VERSION
-- Properly ordered for clean import
-- Generated: 2025-12-27 09:23:34
--
-- IMPORTANT: This file has been reorganized to prevent foreign key errors
-- Order: DROP → CREATE → PRIMARY KEYS → INDEXES → INSERT DATA → FOREIGN KEYS → VIEWS

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Disable foreign key checks for clean import
SET FOREIGN_KEY_CHECKS=0;

-- ============================================
-- STEP 1: DROP ALL EXISTING TABLES & VIEWS
-- ============================================

DROP TABLE IF EXISTS `v_requisitions_summary`;
DROP TABLE IF EXISTS `v_pending_approvals`;
DROP TABLE IF EXISTS `v_active_users`;
DROP TABLE IF EXISTS `requisition_items`;
DROP TABLE IF EXISTS `requisition_documents`;
DROP TABLE IF EXISTS `requisition_approvals`;
DROP TABLE IF EXISTS `requisitions`;
DROP TABLE IF EXISTS `requisition_categories`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `help_support`;
DROP TABLE IF EXISTS `department_budgets`;
DROP TABLE IF EXISTS `budget_allocations`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `audit_log`;

-- ============================================
-- STEP 2: CREATE ALL TABLES
-- ============================================

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

-- ============================================
-- STEP 3: ADD PRIMARY KEYS
-- ============================================

ALTER TABLE `audit_log` ADD PRIMARY KEY (`id`);

ALTER TABLE `budget_allocations` ADD PRIMARY KEY (`id`);

ALTER TABLE `departments` ADD PRIMARY KEY (`id`);

ALTER TABLE `department_budgets` ADD PRIMARY KEY (`id`);

ALTER TABLE `help_support` ADD PRIMARY KEY (`id`);

ALTER TABLE `password_resets` ADD PRIMARY KEY (`id`);

ALTER TABLE `requisitions` ADD PRIMARY KEY (`id`);

ALTER TABLE `requisition_approvals` ADD PRIMARY KEY (`id`);

ALTER TABLE `requisition_categories` ADD PRIMARY KEY (`id`);

ALTER TABLE `requisition_documents` ADD PRIMARY KEY (`id`);

ALTER TABLE `requisition_items` ADD PRIMARY KEY (`id`);

ALTER TABLE `roles` ADD PRIMARY KEY (`id`);

ALTER TABLE `users` ADD PRIMARY KEY (`id`);

-- ============================================
-- STEP 4: ADD INDEXES
-- ============================================

ALTER TABLE `audit_log` ADD KEY `idx_requisition` (`requisition_id`);

ALTER TABLE `audit_log` ADD KEY `idx_user` (`user_id`);

ALTER TABLE `audit_log` ADD KEY `idx_action` (`action`);

ALTER TABLE `audit_log` ADD KEY `idx_created_at` (`created_at`);

ALTER TABLE `budget_allocations` ADD KEY `idx_requisition` (`requisition_id`);

ALTER TABLE `budget_allocations` ADD KEY `idx_budget_type` (`budget_id`,`allocation_type`);

ALTER TABLE `budget_allocations` ADD KEY `idx_allocation_type` (`allocation_type`,`allocated_at`);

ALTER TABLE `departments` ADD UNIQUE KEY `department_name` (`department_name`);

ALTER TABLE `departments` ADD UNIQUE KEY `department_code` (`department_code`);

ALTER TABLE `departments` ADD KEY `idx_dept_code` (`department_code`);

ALTER TABLE `departments` ADD KEY `idx_active` (`is_active`);

ALTER TABLE `department_budgets` ADD KEY `created_by` (`created_by`);

ALTER TABLE `department_budgets` ADD KEY `idx_department_dates` (`department_id`,`start_date`,`end_date`);

ALTER TABLE `department_budgets` ADD KEY `idx_status` (`status`);

ALTER TABLE `department_budgets` ADD KEY `idx_budget_status` (`status`,`end_date`);

ALTER TABLE `help_support` ADD KEY `idx_type` (`type`);

ALTER TABLE `help_support` ADD KEY `idx_category` (`category`);

ALTER TABLE `help_support` ADD KEY `idx_is_active` (`is_active`);

ALTER TABLE `help_support` ADD KEY `idx_display_order` (`display_order`);

ALTER TABLE `help_support` ADD KEY `idx_created_by` (`created_by`);

ALTER TABLE `password_resets` ADD UNIQUE KEY `token` (`token`);

ALTER TABLE `password_resets` ADD KEY `idx_token` (`token`);

ALTER TABLE `password_resets` ADD KEY `idx_user` (`user_id`);

ALTER TABLE `password_resets` ADD KEY `idx_expires_at` (`expires_at`);

ALTER TABLE `requisitions` ADD UNIQUE KEY `requisition_number` (`requisition_number`);

ALTER TABLE `requisitions` ADD KEY `rejected_by_id` (`rejected_by_id`);

ALTER TABLE `requisitions` ADD KEY `idx_requisition_number` (`requisition_number`);

ALTER TABLE `requisitions` ADD KEY `idx_user` (`user_id`);

ALTER TABLE `requisitions` ADD KEY `idx_department` (`department_id`);

ALTER TABLE `requisitions` ADD KEY `idx_status` (`status`);

ALTER TABLE `requisitions` ADD KEY `idx_current_approver` (`current_approver_id`);

ALTER TABLE `requisitions` ADD KEY `idx_created_at` (`created_at`);

ALTER TABLE `requisitions` ADD KEY `idx_submitted_at` (`submitted_at`);

ALTER TABLE `requisitions` ADD KEY `fk_requisitions_paid_by` (`paid_by`);

ALTER TABLE `requisitions` ADD KEY `idx_category` (`category_id`);

ALTER TABLE `requisitions` ADD KEY `idx_selected_approver` (`selected_approver_id`);

ALTER TABLE `requisitions` ADD KEY `idx_assigned_finance_member` (`assigned_finance_member_id`);

ALTER TABLE `requisitions` ADD KEY `idx_assigned_by` (`assigned_by_id`);

ALTER TABLE `requisition_approvals` ADD KEY `idx_requisition` (`requisition_id`);

ALTER TABLE `requisition_approvals` ADD KEY `idx_user` (`user_id`);

ALTER TABLE `requisition_approvals` ADD KEY `idx_created_at` (`created_at`);

ALTER TABLE `requisition_categories` ADD UNIQUE KEY `category_name` (`category_name`);

ALTER TABLE `requisition_categories` ADD KEY `idx_active` (`is_active`);

ALTER TABLE `requisition_categories` ADD KEY `idx_display_order` (`display_order`);

ALTER TABLE `requisition_categories` ADD KEY `idx_parent_id` (`parent_id`);

ALTER TABLE `requisition_documents` ADD KEY `idx_requisition` (`requisition_id`);

ALTER TABLE `requisition_documents` ADD KEY `idx_document_type` (`document_type`);

ALTER TABLE `requisition_documents` ADD KEY `idx_uploaded_by` (`uploaded_by`);

ALTER TABLE `requisition_items` ADD KEY `idx_requisition` (`requisition_id`);

ALTER TABLE `roles` ADD UNIQUE KEY `role_name` (`role_name`);

ALTER TABLE `roles` ADD UNIQUE KEY `role_code` (`role_code`);

ALTER TABLE `roles` ADD KEY `idx_role_code` (`role_code`);

ALTER TABLE `roles` ADD KEY `idx_active` (`is_active`);

ALTER TABLE `users` ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `users` ADD KEY `idx_email` (`email`);

ALTER TABLE `users` ADD KEY `idx_role` (`role_id`);

ALTER TABLE `users` ADD KEY `idx_department` (`department_id`);

ALTER TABLE `users` ADD KEY `idx_active` (`is_active`);

-- ============================================
-- STEP 5: INSERT DATA
-- ============================================

INSERT INTO `roles` (`id`, `role_name`, `role_code`, `description`, `can_raise_requisition`, `can_approve`, `can_view_all`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'SUPER_ADMIN', 'System administrator with full access to all features. Can manage users, departments, and system settings. Cannot raise requisitions.', 0, 0, 1, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(2, 'Managing Director', 'MD', 'Organization-wide oversight and approval authority. Can approve requisitions from Line Managers and Team Members. Can raise requisitions that go directly to Finance Manager. Can generate organization-wide reports.', 1, 1, 1, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(3, 'Finance Manager', 'FINANCE_MGR', 'Reviews all approved requisitions before payment processing. Can reject requisitions back to previous approvers. Oversees payment processing. Can generate organization-wide reports.', 1, 1, 1, 1, '2025-11-24 13:55:52', '2025-12-26 13:58:57'),
(4, 'Finance Member', 'FINANCE_MEM', 'Processes requisitions approved by Finance Manager. Uploads invoices and proof of payment. Marks requisitions as paid. Views receipts after payment.', 0, 0, 1, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(5, 'Line Manager', 'LINE_MGR', 'Department manager with approval authority. Approves requisitions from team members. Can raise requisitions that require MD approval. Can generate department reports.', 1, 1, 0, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(6, 'Team Member', 'TEAM_MEM', 'Raises requisitions for department needs. Requisitions require Line Manager and MD approval. Can generate personal reports.', 1, 0, 0, 1, '2025-11-24 13:55:52', '2025-11-24 13:55:52'),
(7, 'Project Manager', 'PG', '', 1, 1, 1, 1, '2025-12-26 13:58:37', '2025-12-26 13:58:37');

INSERT INTO `users` (`id`, `role_id`, `department_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`, `twofa_secret`, `twofa_enabled`, `twofa_verified_at`) VALUES
(1, 1, NULL, 'System', 'Administrator', 'admin@gatewey.com', '$2y$10$a7G1KN6cEH9ycp5T/lcFGOUbNuA/CIbObIt.bvfMlPylHsAcj73mO', '+234 800 000 0000', 1, '2025-12-26 13:57:34', '2025-11-24 13:55:52', '2025-12-26 13:57:34', 'BBYIP7D2WWPUSSQI', 1, '2025-12-25 07:27:20');

-- ============================================
-- STEP 6: ADD FOREIGN KEY CONSTRAINTS
-- ============================================

ALTER TABLE `audit_log` ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE;

ALTER TABLE `audit_log` ADD CONSTRAINT `audit_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `budget_allocations` ADD CONSTRAINT `budget_allocations_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `department_budgets` (`id`) ON DELETE CASCADE;

ALTER TABLE `budget_allocations` ADD CONSTRAINT `budget_allocations_ibfk_2` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE;

ALTER TABLE `department_budgets` ADD CONSTRAINT `department_budgets_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

ALTER TABLE `department_budgets` ADD CONSTRAINT `department_budgets_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `password_resets` ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `requisitions` ADD CONSTRAINT `fk_assigned_by` FOREIGN KEY (`assigned_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `requisitions` ADD CONSTRAINT `fk_assigned_finance_member` FOREIGN KEY (`assigned_finance_member_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `requisitions` ADD CONSTRAINT `fk_requisitions_paid_by` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `requisitions` ADD CONSTRAINT `fk_selected_approver` FOREIGN KEY (`selected_approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `requisitions` ADD CONSTRAINT `requisitions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `requisitions` ADD CONSTRAINT `requisitions_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

ALTER TABLE `requisitions` ADD CONSTRAINT `requisitions_ibfk_3` FOREIGN KEY (`current_approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `requisitions` ADD CONSTRAINT `requisitions_ibfk_4` FOREIGN KEY (`rejected_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `requisitions` ADD CONSTRAINT `requisitions_ibfk_5` FOREIGN KEY (`category_id`) REFERENCES `requisition_categories` (`id`) ON DELETE SET NULL;

ALTER TABLE `requisition_approvals` ADD CONSTRAINT `requisition_approvals_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE;

ALTER TABLE `requisition_approvals` ADD CONSTRAINT `requisition_approvals_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `requisition_categories` ADD CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `requisition_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `requisition_documents` ADD CONSTRAINT `requisition_documents_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE;

ALTER TABLE `requisition_documents` ADD CONSTRAINT `requisition_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `requisition_items` ADD CONSTRAINT `requisition_items_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE;

ALTER TABLE `users` ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

ALTER TABLE `users` ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

-- ============================================
-- STEP 7: CREATE VIEWS
-- ============================================

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


-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

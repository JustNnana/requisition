-- =====================================================
-- Help and Support System Table
-- =====================================================
-- Creates table to store help articles, tips, and video tutorials
-- =====================================================

CREATE TABLE IF NOT EXISTS `help_support` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL COMMENT 'Title of the help article/tip',
  `description` TEXT NOT NULL COMMENT 'Detailed description or writeup',
  `type` ENUM('tip', 'video', 'article') NOT NULL DEFAULT 'tip' COMMENT 'Type of help content',
  `video_url` VARCHAR(500) NULL COMMENT 'YouTube or video URL (for iframe embed)',
  `category` VARCHAR(100) NOT NULL COMMENT 'Category for filtering (e.g., Requisitions, Approvals, Payments)',
  `icon` VARCHAR(50) NULL DEFAULT 'fa-info-circle' COMMENT 'FontAwesome icon class',
  `display_order` INT(11) NOT NULL DEFAULT 0 COMMENT 'Order for displaying items',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Whether this help item is active',
  `created_by` INT(11) UNSIGNED NOT NULL COMMENT 'User ID who created this (usually super admin)',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_category` (`category`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_display_order` (`display_order`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Help and support articles, tips, and video tutorials';

-- Insert sample help content
INSERT INTO `help_support` (`title`, `description`, `type`, `video_url`, `category`, `icon`, `display_order`, `created_by`) VALUES
('How to Create a Requisition', 'Learn how to create a new requisition in the system. Fill in all required fields including purpose, description, category, and line items. Make sure to attach any supporting documents before submitting.', 'tip', NULL, 'Requisitions', 'fa-file-alt', 1, 1),
('Understanding Approval Workflow', 'Requisitions go through multiple approval levels based on the amount and department. Track your requisition status in real-time and receive notifications when action is required.', 'tip', NULL, 'Approvals', 'fa-check-circle', 2, 1),
('How to Track Your Requisition', 'View all your requisitions in the dashboard. Use filters to find specific requisitions by status, date range, or category. Click on any requisition to see detailed information and approval history.', 'article', NULL, 'Requisitions', 'fa-search', 3, 1);

-- Migration: Add is_active column to roles table
-- Purpose: Allow roles to be deactivated instead of deleted

-- Add is_active column to roles table
ALTER TABLE roles
ADD COLUMN is_active TINYINT(1) DEFAULT 1 NOT NULL
AFTER can_view_all,
ADD INDEX idx_active (is_active);

-- Set all existing roles as active
UPDATE roles SET is_active = 1;

-- Display confirmation message
SELECT 'Migration completed successfully. All roles set as active.' AS message;

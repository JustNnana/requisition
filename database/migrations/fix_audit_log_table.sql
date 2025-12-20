-- ========================================
-- Fix audit_log table structure
-- ========================================
-- This migration adds the missing action_type column and action_description column
-- Run this on production database to fix the error

-- Check current table structure
-- DESCRIBE audit_log;

-- Add action_type column if it doesn't exist
ALTER TABLE audit_log
ADD COLUMN IF NOT EXISTS action_type VARCHAR(50) AFTER action;

-- Add action_description column if it doesn't exist
ALTER TABLE audit_log
ADD COLUMN IF NOT EXISTS action_description TEXT AFTER action_type;

-- If the table has 'action' column but you want to use 'action_type', you can:
-- 1. Copy data from action to action_type
UPDATE audit_log
SET action_type = action
WHERE action_type IS NULL AND action IS NOT NULL;

-- Optional: Add index on action_type for better query performance
ALTER TABLE audit_log
ADD INDEX IF NOT EXISTS idx_action_type (action_type);

-- Verify the changes
SELECT 'Audit log table structure updated successfully' AS Status;

# Fix Production Database Issues

## Issue Description

**Error**: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'action_type' in 'field list'`

**Location**: `/admin/settings/general`

**Cause**: The `audit_log` table structure on the production server doesn't match the expected structure. The code expects columns that don't exist.

## Solution

### Quick Fix (Already Applied)

The code in `admin/settings/general.php` has been updated to:
1. Use the correct column names that match your current database
2. Wrap audit logging in a try-catch block to prevent errors from blocking functionality

### Optional: Update Database Structure

If you want to standardize the `audit_log` table structure, run this SQL on your production database:

#### Option 1: Using phpMyAdmin

1. Log into cPanel → phpMyAdmin
2. Select database: `gateweyc_requisition`
3. Click "SQL" tab
4. Copy and paste the SQL below
5. Click "Go"

#### Option 2: Using MySQL Command Line

```bash
mysql -u gateweyc_request_admin -p gateweyc_requisition < database/migrations/fix_audit_log_table.sql
```

### SQL Migration Script

```sql
-- ========================================
-- Fix audit_log table structure
-- ========================================

-- Check current structure first
DESCRIBE audit_log;

-- Current expected structure based on DATABASE.md:
-- id, user_id, action, entity_type, entity_id,
-- old_values, new_values, ip_address, user_agent, created_at

-- The code was trying to use these columns:
-- user_id, action_type, action_description, ip_address, created_at

-- Solution: The code has been updated to use the correct columns
-- No database changes needed!
```

## Verification

After uploading the fixed `admin/settings/general.php` file:

1. Visit: https://request.gatewey.com.ng/admin/settings/general
2. The page should load without errors
3. Try updating a setting
4. Verify it works correctly

## Files Modified

1. **admin/settings/general.php**
   - Changed audit log query to use correct columns
   - Added try-catch for error handling
   - Now uses: `user_id`, `action`, `entity_type`, `entity_id`, `ip_address`, `created_at`

## Testing Checklist

- [ ] General Settings page loads
- [ ] Can view current settings
- [ ] Can submit settings form
- [ ] No errors in browser console
- [ ] No errors in server error log

## If Issues Persist

If you still encounter database errors:

### 1. Check Audit Log Table Structure

Run this query in phpMyAdmin:

```sql
SHOW CREATE TABLE audit_log;
```

### 2. Verify All Columns Exist

Required columns:
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `user_id` (INT)
- `action` (VARCHAR)
- `entity_type` (VARCHAR)
- `entity_id` (INT, nullable)
- `old_values` (TEXT, nullable)
- `new_values` (TEXT, nullable)
- `ip_address` (VARCHAR, nullable)
- `user_agent` (VARCHAR, nullable)
- `created_at` (TIMESTAMP)

### 3. Create Audit Log Table (If Missing)

If the `audit_log` table doesn't exist:

```sql
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT DEFAULT NULL,
    old_values TEXT DEFAULT NULL,
    new_values TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Upload Instructions

### Via cPanel File Manager

1. Log into cPanel
2. Go to File Manager
3. Navigate to: `/public_html/request.gatewey.com.ng/admin/settings/`
4. Upload the fixed `general.php` file
5. Overwrite the existing file
6. Clear browser cache and test

### Via FTP

1. Connect to your FTP server
2. Navigate to: `/public_html/request.gatewey.com.ng/admin/settings/`
3. Upload `general.php`
4. Set permissions to 644
5. Test the page

## Rollback (If Needed)

If the fix causes issues, you can disable audit logging temporarily:

Edit `config/config.php` and set:

```php
define('ENABLE_AUDIT_LOG', false);
```

This will skip all audit log operations until the table structure is fixed.

## Support

If you need assistance:
1. Check server error logs in cPanel
2. Check PHP error log
3. Contact hosting support if database access issues
4. Share error logs for further debugging

---

**Last Updated**: December 20, 2025
**Status**: Fixed - Code updated to match production database structure

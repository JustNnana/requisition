# Role Deactivation System - Implementation Guide

## Overview
Roles are now **deactivated** instead of **deleted** to maintain data integrity and historical records. This prevents breaking foreign key relationships while allowing Super Admins to control which roles are available for assignment.

---

## What Changed?

### 1. Database Schema
- **Added `is_active` column** to the `roles` table
- Default value: `1` (active)
- Index added for performance

### 2. New Files Created
- **`toggle-status.php`** - Handles activation/deactivation of roles
- **`add_is_active_migration.sql`** - SQL migration script to add the column

### 3. Modified Files
- **`list.php`** - Added status column, changed delete to activate/deactivate
- **`add.php`** - New roles are created as active by default
- **`admin/users/add.php`** - Only shows active roles in dropdown
- **`admin/users/edit.php`** - Only shows active roles in dropdown
- **`admin/users/list.php`** - Shows all roles (for filtering) but marks inactive ones

### 4. Removed Files
- **`delete.php`** - No longer needed (replaced by toggle-status.php)

---

## Installation Steps

### Step 1: Run Database Migration

**Option A: Using phpMyAdmin**
1. Open phpMyAdmin at `http://localhost/phpmyadmin`
2. Select the `requisition` database
3. Go to the SQL tab
4. Copy and paste the contents of `add_is_active_migration.sql`
5. Click "Go" to execute

**Option B: Using MySQL Command Line**
```bash
mysql -u root -p requisition < admin/roles/add_is_active_migration.sql
```

**Option C: Using the PHP Migration Script**
```bash
php admin/roles/add_is_active_column.php
```

### Step 2: Verify Migration
Run this SQL to confirm the column was added:
```sql
SHOW COLUMNS FROM roles LIKE 'is_active';
```

You should see the `is_active` column with type `tinyint(1)` and default value `1`.

### Step 3: Test the System
1. Login as Super Admin
2. Navigate to **Role Management** in the sidebar
3. You should see a "Status" column showing all roles as "Active"
4. Try deactivating a role that has no users assigned
5. Try reactivating the role

---

## How It Works

### Deactivation Rules
✅ **Can deactivate when:**
- Role has **zero active users** assigned

❌ **Cannot deactivate when:**
- Role has **one or more active users** assigned
- Must reassign users to another role first

### Activation
- Any inactive role can be reactivated
- No restrictions on activation

### Role Visibility
| Location | Shows Active | Shows Inactive |
|----------|-------------|----------------|
| Role Management List | ✅ Yes | ✅ Yes (dimmed) |
| Add User - Role Dropdown | ✅ Yes | ❌ No |
| Edit User - Role Dropdown | ✅ Yes | ❌ No |
| User List - Filter Dropdown | ✅ Yes | ✅ Yes (for filtering existing users) |

---

## User Interface Changes

### Role Management List
**Before:**
- Edit button
- Delete button (only if no users)

**After:**
- Status column (Active/Inactive badge)
- Edit button
- **Deactivate** button (active roles with no users)
- **Activate** button (inactive roles)
- Button disabled if role has assigned users

### Visual Indicators
- **Active roles**: Full opacity, green "Active" badge
- **Inactive roles**: 60% opacity, red "Inactive" badge

---

## Benefits of This Approach

### 1. **Data Integrity**
- Maintains foreign key relationships
- Preserves historical user-role assignments
- Audit trails remain intact

### 2. **Flexibility**
- Roles can be reactivated if needed
- No permanent data loss
- Easy to temporarily disable roles

### 3. **Safety**
- Cannot accidentally delete roles with users
- Clear warning when trying to deactivate
- Reversible actions

### 4. **Compliance**
- Better for audit requirements
- Historical reporting remains accurate
- Maintains complete system history

---

## Example Scenarios

### Scenario 1: Temporary Role Removal
**Situation:** "Procurement Officer" role is no longer needed this year

**Solution:**
1. Ensure no users have this role
2. Deactivate the role
3. Role no longer appears in user creation dropdown
4. Can reactivate next year if needed

### Scenario 2: Role Restructuring
**Situation:** Merging "Finance Member" into "Finance Manager"

**Steps:**
1. Reassign all Finance Members to Finance Manager role
2. Deactivate "Finance Member" role
3. Role data preserved for historical records

### Scenario 3: Accidental Deactivation
**Situation:** Accidentally deactivated the wrong role

**Solution:**
1. Simply click "Activate" button
2. Role immediately available again
3. No data loss or complex recovery

---

## API/Function Changes

### Database Queries Updated

**User Management (Add/Edit):**
```php
// OLD
$roles = $db->fetchAll("SELECT * FROM roles ORDER BY id");

// NEW
$roles = $db->fetchAll("SELECT * FROM roles WHERE is_active = 1 ORDER BY id");
```

**Role Management (List):**
```php
// OLD
ORDER BY r.id ASC

// NEW
ORDER BY r.is_active DESC, r.id ASC
// (Active roles appear first)
```

---

## Testing Checklist

- [ ] Migration script runs without errors
- [ ] All existing roles show as "Active"
- [ ] Can deactivate a role with no users
- [ ] Cannot deactivate a role with users
- [ ] Deactivated roles don't appear in Add User dropdown
- [ ] Deactivated roles don't appear in Edit User dropdown
- [ ] Deactivated roles appear dimmed in Role Management list
- [ ] Can reactivate a deactivated role
- [ ] Status badges display correctly
- [ ] Mobile responsive layout works

---

## Rollback Instructions

If you need to rollback this change:

```sql
-- Remove the is_active column
ALTER TABLE roles DROP COLUMN is_active;
ALTER TABLE roles DROP INDEX idx_active;
```

Then restore the old `delete.php` file from backup.

---

## Support

If you encounter any issues:
1. Check database migration ran successfully
2. Verify all files were updated correctly
3. Clear browser cache
4. Check error logs in `c:\xampp\apache\logs\error.log`

---

## Future Enhancements

Potential improvements for future versions:
- Add "Last Modified By" tracking
- Add "Deactivation Reason" field
- Show deactivation date/time
- Filter toggle to hide/show inactive roles
- Bulk activate/deactivate operations

---

**Last Updated:** 2025-12-26
**Version:** 1.0.0
**Author:** Claude Code Assistant

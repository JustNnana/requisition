# Budget Allocation - Permission-Based Fix

## What Was Changed

**File:** `classes/Approval.php` (Lines 100-120)

## The Problem

Budget allocation was hardcoded to only work with 3 specific roles:
- ROLE_LINE_MANAGER
- ROLE_MANAGING_DIRECTOR
- ROLE_FINANCE_MANAGER

This meant custom roles with `can_approve = 1` would NOT trigger budget allocation.

## The Solution

**Simple permission-based check:**

```php
// Get approver's role permissions
$approverRole = $this->db->fetchOne(
    "SELECT role_name, can_approve FROM roles WHERE id = ?",
    [$approver['role_id']]
);

// Budget is allocated if:
// 1. Approver has approval permission (can_approve = 1)
// 2. Requisition has a category (budget tracking)
if (!empty($requisition['category_id']) && $approverRole && $approverRole['can_approve']) {
    $shouldCheckBudget = true;
    $budgetAllocationReason = 'Allocated by ' . $approverRole['role_name'] . ' approval';
}
```

## How It Works Now

### ✅ Budget Allocation (Fixed)
**ANY role with `can_approve = 1` will trigger budget allocation**

Examples:
- Line Manager approves → Budget allocated ✅
- MD approves → Budget allocated ✅
- Finance Manager approves → Budget allocated ✅
- **Project Manager (custom role with can_approve=1) approves → Budget allocated ✅**
- **Department Head (custom role with can_approve=1) approves → Budget allocated ✅**

### ✅ Budget Reversal (Already Works)
**Budget is released when ANY role rejects a requisition**
- This was never hardcoded and works perfectly

## Testing

To test with a custom role:

1. **Create a custom role** (e.g., "Project Manager"):
   - Go to Role Management
   - Add New Role
   - Set `can_approve = 1` (checked)
   - Save

2. **Assign user to the role**:
   - Go to User Management
   - Edit a user
   - Change their role to "Project Manager"

3. **Test budget allocation**:
   - Create a requisition as a Team Member
   - Have the Project Manager approve it
   - **Check**: Budget should be deducted from department
   - **Verify**: `budget_allocations` table should have a new entry

4. **Test budget reversal**:
   - Reject the requisition (any approver)
   - **Check**: Budget should be added back to department

## What Still Needs Fixing (Future)

⚠️ **Workflow Routing** - Still hardcoded in `classes/WorkflowEngine.php`
- Custom roles won't know where to route requisitions
- This requires a more complex fix
- For now, custom roles will work for budget but may have workflow issues

## Impact

### ✅ Fixed
- Budget allocation works for ANY custom role with `can_approve = 1`
- No database changes required
- Backwards compatible with existing roles

### ⚠️ Still Limited
- Workflow routing still expects specific role IDs
- May need manual workflow configuration for custom roles

---

**Date:** 2025-12-26
**Status:** ✅ Complete
**Files Changed:** 1 (`classes/Approval.php`)
**Database Changes:** None

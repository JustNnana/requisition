<?php
/**
 * GateWey Requisition Management System
 * Permissions Helper Functions
 * 
 * File: helpers/permissions.php
 * Purpose: Role-based permission checking functions
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

/**
 * Check if current user can raise requisitions
 * 
 * @return bool
 */
function can_user_raise_requisition() {
    $roleId = Session::getUserRoleId();
    return in_array($roleId, CAN_RAISE_REQUISITION);
}

/**
 * Check if current user can approve requisitions
 * 
 * @return bool
 */
function can_user_approve() {
    $roleId = Session::getUserRoleId();
    return in_array($roleId, CAN_APPROVE);
}

/**
 * Check if current user can process payments
 * 
 * @return bool
 */
function can_user_process_payment() {
    $roleId = Session::getUserRoleId();
    return in_array($roleId, CAN_PROCESS_PAYMENT);
}

/**
 * Check if current user can view all requisitions
 * 
 * @return bool
 */
function can_user_view_all() {
    $roleId = Session::getUserRoleId();
    return in_array($roleId, CAN_VIEW_ALL);
}

/**
 * Check if current user can view department requisitions
 * 
 * @return bool
 */
function can_user_view_department() {
    $roleId = Session::getUserRoleId();
    return in_array($roleId, CAN_VIEW_DEPARTMENT);
}

/**
 * Check if current user can manage users
 * 
 * @return bool
 */
function can_user_manage_users() {
    $roleId = Session::getUserRoleId();
    return in_array($roleId, CAN_MANAGE_USERS);
}

/**
 * Check if current user can view organization reports
 * 
 * @return bool
 */
function can_user_view_org_reports() {
    $roleId = Session::getUserRoleId();
    return in_array($roleId, CAN_VIEW_ORG_REPORTS);
}

/**
 * Check if current user is Super Admin
 * 
 * @return bool
 */
function is_super_admin() {
    return Session::getUserRoleId() === ROLE_SUPER_ADMIN;
}

/**
 * Check if current user is Managing Director
 * 
 * @return bool
 */
function is_managing_director() {
    return Session::getUserRoleId() === ROLE_MANAGING_DIRECTOR;
}

/**
 * Check if current user is Finance Manager
 * 
 * @return bool
 */
function is_finance_manager() {
    return Session::getUserRoleId() === ROLE_FINANCE_MANAGER;
}

/**
 * Check if current user is Finance Member
 * 
 * @return bool
 */
function is_finance_member() {
    return Session::getUserRoleId() === ROLE_FINANCE_MEMBER;
}

/**
 * Check if current user is Line Manager
 * 
 * @return bool
 */
function is_line_manager() {
    return Session::getUserRoleId() === ROLE_LINE_MANAGER;
}

/**
 * Check if current user is Team Member
 * 
 * @return bool
 */
function is_team_member() {
    return Session::getUserRoleId() === ROLE_TEAM_MEMBER;
}

/**
 * Check if current user has any of the specified roles
 * 
 * @param array $roles Array of role IDs
 * @return bool
 */
function has_any_role($roles) {
    $userRoleId = Session::getUserRoleId();
    return in_array($userRoleId, $roles);
}

/**
 * Get current user's role name
 * 
 * @return string Role name
 */
function get_current_user_role_name() {
    $roleId = Session::getUserRoleId();
    return get_role_name($roleId);
}

/**
 * Get current user's department ID
 * 
 * @return int|null Department ID or null
 */
function get_current_user_department_id() {
    return Session::getUserDepartmentId();
}

/**
 * Check if user owns a requisition
 * 
 * @param int $requisitionUserId Requisition owner's user ID
 * @return bool
 */
function user_owns_requisition($requisitionUserId) {
    return Session::getUserId() === (int)$requisitionUserId;
}

/**
 * Check if user is in the same department as requisition
 * 
 * @param int $requisitionDepartmentId Requisition's department ID
 * @return bool
 */
function user_in_same_department($requisitionDepartmentId) {
    $userDepartmentId = Session::getUserDepartmentId();
    return $userDepartmentId && $userDepartmentId === (int)$requisitionDepartmentId;
}

/**
 * Check if user can view a specific requisition
 * 
 * @param array $requisition Requisition data
 * @return bool
 */
function can_user_view_requisition($requisition) {
    // Super admin, MD, Finance can view all
    if (can_user_view_all()) {
        return true;
    }
    
    // Line managers can view department requisitions
    if (is_line_manager() && user_in_same_department($requisition['department_id'])) {
        return true;
    }
    
    // Users can view their own requisitions
    if (user_owns_requisition($requisition['user_id'])) {
        return true;
    }
    
    // Approvers can view requisitions they need to approve
    if (can_user_approve() && $requisition['current_approver_id'] == Session::getUserId()) {
        return true;
    }
    
    return false;
}

/**
 * Check if user can edit a requisition
 * Only when rejected and returned to them
 * 
 * @param array $requisition Requisition data
 * @return bool
 */
function can_user_edit_requisition($requisition) {
    // Must own the requisition
    if (!user_owns_requisition($requisition['user_id'])) {
        return false;
    }
    
    // Must be rejected or draft
    if ($requisition['status'] !== STATUS_REJECTED && $requisition['status'] !== STATUS_DRAFT) {
        return false;
    }
    
    return true;
}

/**
 * Check if user can cancel a requisition
 * 
 * @param array $requisition Requisition data
 * @return bool
 */
function can_user_cancel_requisition($requisition) {
    // Must own the requisition
    if (!user_owns_requisition($requisition['user_id'])) {
        return false;
    }
    
    // Can only cancel if rejected or draft
    if ($requisition['status'] !== STATUS_REJECTED && $requisition['status'] !== STATUS_DRAFT) {
        return false;
    }
    
    return true;
}

/**
 * Check if user can approve a requisition
 * 
 * @param array $requisition Requisition data
 * @return bool
 */
function can_user_approve_requisition($requisition) {
    // Must have approval permission
    if (!can_user_approve()) {
        return false;
    }
    
    // Must be the current approver
    if ($requisition['current_approver_id'] != Session::getUserId()) {
        return false;
    }
    
    // Must be in pending status
    $pendingStatuses = [
        STATUS_PENDING_LINE_MANAGER,
        STATUS_PENDING_MD,
        STATUS_PENDING_FINANCE_MANAGER
    ];
    
    return in_array($requisition['status'], $pendingStatuses);
}

/**
 * Check if user can process payment for a requisition
 * 
 * @param array $requisition Requisition data
 * @return bool
 */
function can_user_process_payment_for_requisition($requisition) {
    // Must be finance member
    if (!is_finance_member()) {
        return false;
    }
    
    // Must be approved for payment
    return $requisition['status'] === STATUS_APPROVED_FOR_PAYMENT;
}

/**
 * Require specific permission or redirect
 * 
 * @param callable $permissionCheck Permission checking function
 * @param string $redirectUrl URL to redirect to if no permission
 * @param string $errorMessage Error message to display
 */
function require_permission($permissionCheck, $redirectUrl = null, $errorMessage = 'You do not have permission to access this resource.') {
    if (!$permissionCheck()) {
        Session::setFlash('error', $errorMessage);
        
        if ($redirectUrl) {
            header('Location: ' . $redirectUrl);
        } else {
            header('Location: ' . APP_URL . '/errors/403.php');
        }
        exit;
    }
}

/**
 * Require Super Admin role
 */
function require_super_admin() {
    require_permission(
        'is_super_admin',
        APP_URL . '/dashboard/index.php',
        'This area is restricted to Super Administrators only.'
    );
}

/**
 * Require Finance role (Manager or Member)
 */
function require_finance_role() {
    require_permission(
        function() {
            return is_finance_manager() || is_finance_member();
        },
        APP_URL . '/dashboard/index.php',
        'This area is restricted to Finance staff only.'
    );
}

/**
 * Require approval permission
 */
function require_approval_permission() {
    require_permission(
        'can_user_approve',
        APP_URL . '/dashboard/index.php',
        'You do not have permission to approve requisitions.'
    );
}

/**
 * Get accessible dashboard URL for current user
 * 
 * @return string Dashboard URL
 */
function get_user_dashboard_url() {
    $roleId = Session::getUserRoleId();
    
    switch ($roleId) {
        case ROLE_SUPER_ADMIN:
            return APP_URL . '/admin/index.php';
        case ROLE_MANAGING_DIRECTOR:
            return APP_URL . '/dashboard/managing-director.php';
        case ROLE_FINANCE_MANAGER:
            return APP_URL . '/dashboard/finance-manager.php';
        case ROLE_FINANCE_MEMBER:
            return APP_URL . '/dashboard/finance-member.php';
        case ROLE_LINE_MANAGER:
            return APP_URL . '/dashboard/line-manager.php';
        case ROLE_TEAM_MEMBER:
            return APP_URL . '/dashboard/team-member.php';
        default:
            return APP_URL . '/dashboard/index.php';
    }
}

/**
 * Get navigation menu items based on user role
 * 
 * @return array Navigation items
 */
function get_user_navigation() {
    $roleId = Session::getUserRoleId();
    $nav = [];
    
    // Dashboard (all users)
    $nav[] = [
        'title' => 'Dashboard',
        'icon' => 'fa-home',
        'url' => get_user_dashboard_url()
    ];
    
    // Requisitions (Team Members, Line Managers, MD)
    if (can_user_raise_requisition()) {
        $nav[] = [
            'title' => 'My Requisitions',
            'icon' => 'fa-file-invoice',
            'url' => APP_URL . '/requisitions/list.php'
        ];
        
        $nav[] = [
            'title' => 'Create Requisition',
            'icon' => 'fa-plus-circle',
            'url' => APP_URL . '/requisitions/create.php'
        ];
    }
    
    // Approvals (Line Managers, MD, Finance Manager)
    if (can_user_approve()) {
        $nav[] = [
            'title' => 'Pending Approvals',
            'icon' => 'fa-clock',
            'url' => APP_URL . '/requisitions/pending.php',
            'badge' => 'approval_count' // You can fetch actual count dynamically
        ];
    }
    
    // Finance (Finance Manager, Finance Member)
    if (is_finance_manager() || is_finance_member()) {
        $nav[] = [
            'title' => 'Finance',
            'icon' => 'fa-coins',
            'submenu' => [
                [
                    'title' => 'Pending Payments',
                    'url' => APP_URL . '/finance/pending-payment.php'
                ],
                [
                    'title' => 'Payment History',
                    'url' => APP_URL . '/finance/payment-history.php'
                ],
                [
                    'title' => 'Pending Receipts',
                    'url' => APP_URL . '/finance/pending-receipts.php'
                ]
            ]
        ];
    }
    
    // Reports
    $nav[] = [
        'title' => 'Reports',
        'icon' => 'fa-chart-bar',
        'url' => APP_URL . '/reports/generate.php'
    ];
    
    // Admin (Super Admin only)
    if (is_super_admin()) {
        $nav[] = [
            'title' => 'Administration',
            'icon' => 'fa-cog',
            'submenu' => [
                [
                    'title' => 'Users',
                    'url' => APP_URL . '/admin/users/list.php'
                ],
                [
                    'title' => 'Departments',
                    'url' => APP_URL . '/admin/departments/list.php'
                ],
                [
                    'title' => 'Settings',
                    'url' => APP_URL . '/admin/settings/general.php'
                ]
            ]
        ];
    }
    
    return $nav;
}
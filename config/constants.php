<?php
/**
 * GateWey Requisition Management System
 * Application Constants
 * 
 * File: config/constants.php
 * Purpose: Define all application constants, roles, statuses, and enums
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

/**
 * USER ROLES
 */
define('ROLE_SUPER_ADMIN', 1);
define('ROLE_MANAGING_DIRECTOR', 2);
define('ROLE_FINANCE_MANAGER', 3);
define('ROLE_FINANCE_MEMBER', 4);
define('ROLE_LINE_MANAGER', 5);
define('ROLE_TEAM_MEMBER', 6);

// Role Names Mapping
define('ROLE_NAMES', [
    ROLE_SUPER_ADMIN => 'Super Admin',
    ROLE_MANAGING_DIRECTOR => 'Managing Director',
    ROLE_FINANCE_MANAGER => 'Finance Manager',
    ROLE_FINANCE_MEMBER => 'Finance Member',
    ROLE_LINE_MANAGER => 'Line Manager',
    ROLE_TEAM_MEMBER => 'Team Member'
]);

// Role Codes Mapping
define('ROLE_CODES', [
    ROLE_SUPER_ADMIN => 'SUPER_ADMIN',
    ROLE_MANAGING_DIRECTOR => 'MD',
    ROLE_FINANCE_MANAGER => 'FINANCE_MGR',
    ROLE_FINANCE_MEMBER => 'FINANCE_MEM',
    ROLE_LINE_MANAGER => 'LINE_MGR',
    ROLE_TEAM_MEMBER => 'TEAM_MEM'
]);

/**
 * REQUISITION STATUSES
 */
define('STATUS_DRAFT', 'draft');
define('STATUS_PENDING_LINE_MANAGER', 'pending_line_manager');
define('STATUS_PENDING_MD', 'pending_md');
define('STATUS_PENDING_FINANCE_MANAGER', 'pending_finance_manager');
define('STATUS_APPROVED_FOR_PAYMENT', 'approved_for_payment');
define('STATUS_PAID', 'paid');
define('STATUS_COMPLETED', 'completed');
define('STATUS_REJECTED', 'rejected');
define('STATUS_CANCELLED', 'cancelled');

// Status Display Names
define('STATUS_NAMES', [
    STATUS_DRAFT => 'Draft',
    STATUS_PENDING_LINE_MANAGER => 'Pending Line Manager Approval',
    STATUS_PENDING_MD => 'Pending Managing Director Approval',
    STATUS_PENDING_FINANCE_MANAGER => 'Pending Finance Manager Review',
    STATUS_APPROVED_FOR_PAYMENT => 'Approved for Payment',
    STATUS_PAID => 'Paid',
    STATUS_COMPLETED => 'Completed',
    STATUS_REJECTED => 'Rejected',
    STATUS_CANCELLED => 'Cancelled'
]);

// Status Colors for Badges (Dasher UI colors)
define('STATUS_COLORS', [
    STATUS_DRAFT => 'secondary',
    STATUS_PENDING_LINE_MANAGER => 'warning',
    STATUS_PENDING_MD => 'warning',
    STATUS_PENDING_FINANCE_MANAGER => 'info',
    STATUS_APPROVED_FOR_PAYMENT => 'primary',
    STATUS_PAID => 'success',
    STATUS_COMPLETED => 'success',
    STATUS_REJECTED => 'danger',
    STATUS_CANCELLED => 'secondary'
]);

// Status Icons (Font Awesome)
define('STATUS_ICONS', [
    STATUS_DRAFT => 'fa-file-alt',
    STATUS_PENDING_LINE_MANAGER => 'fa-clock',
    STATUS_PENDING_MD => 'fa-clock',
    STATUS_PENDING_FINANCE_MANAGER => 'fa-clock',
    STATUS_APPROVED_FOR_PAYMENT => 'fa-check-circle',
    STATUS_PAID => 'fa-money-check-alt',
    STATUS_COMPLETED => 'fa-check-double',
    STATUS_REJECTED => 'fa-times-circle',
    STATUS_CANCELLED => 'fa-ban'
]);

/**
 * APPROVAL ACTIONS
 */
define('ACTION_APPROVED', 'approved');
define('ACTION_REJECTED', 'rejected');
define('ACTION_RETURNED', 'returned');

// Action Display Names
define('ACTION_NAMES', [
    ACTION_APPROVED => 'Approved',
    ACTION_REJECTED => 'Rejected',
    ACTION_RETURNED => 'Returned'
]);

/**
 * DOCUMENT TYPES
 */
define('DOC_TYPE_ATTACHMENT', 'attachment');
define('DOC_TYPE_INVOICE', 'invoice');
define('DOC_TYPE_RECEIPT', 'receipt');
define('DOC_TYPE_PROOF_OF_PAYMENT', 'proof_of_payment');

// Document Type Display Names
define('DOC_TYPE_NAMES', [
    DOC_TYPE_ATTACHMENT => 'Supporting Document',
    DOC_TYPE_INVOICE => 'Invoice',
    DOC_TYPE_RECEIPT => 'Receipt',
    DOC_TYPE_PROOF_OF_PAYMENT => 'Proof of Payment'
]);

/**
 * NOTIFICATION TYPES
 */
define('NOTIF_REQUISITION_SUBMITTED', 'requisition_submitted');
define('NOTIF_REQUISITION_APPROVED', 'requisition_approved');
define('NOTIF_REQUISITION_REJECTED', 'requisition_rejected');
define('NOTIF_REQUISITION_PAID', 'requisition_paid');
define('NOTIF_RECEIPT_UPLOADED', 'receipt_uploaded');
define('NOTIF_ACTION_REQUIRED', 'action_required');
define('NOTIF_REQUISITION_CANCELLED', 'requisition_cancelled');

// Notification Type Display Names
define('NOTIF_TYPE_NAMES', [
    NOTIF_REQUISITION_SUBMITTED => 'Requisition Submitted',
    NOTIF_REQUISITION_APPROVED => 'Requisition Approved',
    NOTIF_REQUISITION_REJECTED => 'Requisition Rejected',
    NOTIF_REQUISITION_PAID => 'Requisition Paid',
    NOTIF_RECEIPT_UPLOADED => 'Receipt Uploaded',
    NOTIF_ACTION_REQUIRED => 'Action Required',
    NOTIF_REQUISITION_CANCELLED => 'Requisition Cancelled'
]);

/**
 * AUDIT LOG ACTIONS
 */
define('AUDIT_REQUISITION_CREATED', 'requisition_created');
define('AUDIT_REQUISITION_SUBMITTED', 'requisition_submitted');
define('AUDIT_REQUISITION_APPROVED', 'requisition_approved');
define('AUDIT_REQUISITION_REJECTED', 'requisition_rejected');
define('AUDIT_REQUISITION_EDITED', 'requisition_edited');
define('AUDIT_REQUISITION_CANCELLED', 'requisition_cancelled');
define('AUDIT_PAYMENT_PROCESSED', 'payment_processed');
define('AUDIT_RECEIPT_UPLOADED', 'receipt_uploaded');
define('AUDIT_DOCUMENT_UPLOADED', 'document_uploaded');
define('AUDIT_DOCUMENT_DELETED', 'document_deleted');
define('AUDIT_USER_LOGIN', 'user_login');
define('AUDIT_USER_LOGOUT', 'user_logout');
define('AUDIT_USER_CREATED', 'user_created');
define('AUDIT_USER_UPDATED', 'user_updated');
define('AUDIT_USER_DELETED', 'user_deleted');

/**
 * PERMISSION FLAGS
 */
// Roles that can raise requisitions
define('CAN_RAISE_REQUISITION', [
    ROLE_TEAM_MEMBER,
    ROLE_LINE_MANAGER,
    ROLE_MANAGING_DIRECTOR
]);

// Roles that can approve requisitions
define('CAN_APPROVE', [
    ROLE_LINE_MANAGER,
    ROLE_MANAGING_DIRECTOR,
    ROLE_FINANCE_MANAGER
]);

// Roles that can process payments
define('CAN_PROCESS_PAYMENT', [
    ROLE_FINANCE_MEMBER
]);

// Roles that can view all requisitions
define('CAN_VIEW_ALL', [
    ROLE_SUPER_ADMIN,
    ROLE_MANAGING_DIRECTOR,
    ROLE_FINANCE_MANAGER,
    ROLE_FINANCE_MEMBER
]);

// Roles that can view department requisitions
define('CAN_VIEW_DEPARTMENT', [
    ROLE_LINE_MANAGER
]);

// Roles that can manage users
define('CAN_MANAGE_USERS', [
    ROLE_SUPER_ADMIN
]);

// Roles that can generate organization reports
define('CAN_VIEW_ORG_REPORTS', [
    ROLE_MANAGING_DIRECTOR,
    ROLE_FINANCE_MANAGER
]);

/**
 * WORKFLOW MAPPING
 * Defines the approval chain based on requester role
 */
define('APPROVAL_WORKFLOW', [
    // Team Member: TM → LM → MD → FM → Finance Member
    ROLE_TEAM_MEMBER => [
        STATUS_PENDING_LINE_MANAGER,
        STATUS_PENDING_MD,
        STATUS_PENDING_FINANCE_MANAGER,
        STATUS_APPROVED_FOR_PAYMENT
    ],
    
    // Line Manager: LM → MD → FM → Finance Member
    ROLE_LINE_MANAGER => [
        STATUS_PENDING_MD,
        STATUS_PENDING_FINANCE_MANAGER,
        STATUS_APPROVED_FOR_PAYMENT
    ],
    
    // Managing Director: MD → FM → Finance Member
    ROLE_MANAGING_DIRECTOR => [
        STATUS_PENDING_FINANCE_MANAGER,
        STATUS_APPROVED_FOR_PAYMENT
    ]
]);

/**
 * REJECTION RETURN MAPPING
 * Defines where requisition returns when rejected at each stage
 */
define('REJECTION_RETURN_STATUS', [
    STATUS_PENDING_LINE_MANAGER => STATUS_DRAFT, // Returns to Team Member
    STATUS_PENDING_MD => [
        ROLE_TEAM_MEMBER => STATUS_PENDING_LINE_MANAGER, // TM req returns to LM
        ROLE_LINE_MANAGER => STATUS_DRAFT // LM req returns to LM (draft state)
    ],
    STATUS_PENDING_FINANCE_MANAGER => [
        ROLE_TEAM_MEMBER => STATUS_PENDING_MD, // Returns to MD
        ROLE_LINE_MANAGER => STATUS_PENDING_MD, // Returns to MD
        ROLE_MANAGING_DIRECTOR => STATUS_DRAFT // MD req returns to MD (draft state)
    ]
]);

/**
 * Helper Functions for Constants
 */

/**
 * Get role name by ID
 * 
 * @param int $roleId Role ID
 * @return string Role name
 */
function get_role_name($roleId) {
    return ROLE_NAMES[$roleId] ?? 'Unknown Role';
}

/**
 * Get role code by ID
 * 
 * @param int $roleId Role ID
 * @return string Role code
 */
function get_role_code($roleId) {
    return ROLE_CODES[$roleId] ?? 'UNKNOWN';
}

/**
 * Get status display name
 * 
 * @param string $status Status code
 * @return string Status display name
 */
function get_status_name($status) {
    return STATUS_NAMES[$status] ?? 'Unknown Status';
}

/**
 * Get status badge color
 * 
 * @param string $status Status code
 * @return string Badge color class
 */
function get_status_color($status) {
    return STATUS_COLORS[$status] ?? 'secondary';
}

/**
 * Get status icon
 * 
 * @param string $status Status code
 * @return string Icon class
 */
function get_status_icon($status) {
    return STATUS_ICONS[$status] ?? 'fa-question-circle';
}

/**
 * Check if role can raise requisitions
 * 
 * @param int $roleId Role ID
 * @return bool
 */
function can_raise_requisition($roleId) {
    return in_array($roleId, CAN_RAISE_REQUISITION);
}

/**
 * Check if role can approve requisitions
 * 
 * @param int $roleId Role ID
 * @return bool
 */
function can_approve($roleId) {
    return in_array($roleId, CAN_APPROVE);
}

/**
 * Check if role can process payments
 * 
 * @param int $roleId Role ID
 * @return bool
 */
function can_process_payment($roleId) {
    return in_array($roleId, CAN_PROCESS_PAYMENT);
}

/**
 * Check if role can view all requisitions
 * 
 * @param int $roleId Role ID
 * @return bool
 */
function can_view_all($roleId) {
    return in_array($roleId, CAN_VIEW_ALL);
}

/**
 * Check if role can manage users
 * 
 * @param int $roleId Role ID
 * @return bool
 */
function can_manage_users($roleId) {
    return in_array($roleId, CAN_MANAGE_USERS);
}

/**
 * Get next approval status in workflow
 * 
 * @param string $currentStatus Current status
 * @param int $requesterRoleId Requester's role ID
 * @return string|null Next status or null if completed
 */
function get_next_approval_status($currentStatus, $requesterRoleId) {
    $workflow = APPROVAL_WORKFLOW[$requesterRoleId] ?? [];
    
    $currentIndex = array_search($currentStatus, $workflow);
    if ($currentIndex !== false && isset($workflow[$currentIndex + 1])) {
        return $workflow[$currentIndex + 1];
    }
    
    return null; // Workflow complete
}

/**
 * Get return status when rejected
 * 
 * @param string $currentStatus Current status
 * @param int $requesterRoleId Requester's role ID
 * @return string Return status
 */
function get_rejection_return_status($currentStatus, $requesterRoleId) {
    $returnMapping = REJECTION_RETURN_STATUS[$currentStatus] ?? STATUS_DRAFT;
    
    if (is_array($returnMapping)) {
        return $returnMapping[$requesterRoleId] ?? STATUS_DRAFT;
    }
    
    return $returnMapping;
}
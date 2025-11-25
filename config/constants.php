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
 * ROLE PERMISSIONS
 * Define which roles can perform specific actions
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

// Roles that can view organization-wide reports
define('CAN_VIEW_ORG_REPORTS', [
    ROLE_MANAGING_DIRECTOR,
    ROLE_FINANCE_MANAGER
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

// Status Colors (for badges)
define('STATUS_COLORS', [
    STATUS_DRAFT => 'secondary',
    STATUS_PENDING_LINE_MANAGER => 'warning',
    STATUS_PENDING_MD => 'warning',
    STATUS_PENDING_FINANCE_MANAGER => 'info',
    STATUS_APPROVED_FOR_PAYMENT => 'success',
    STATUS_PAID => 'success',
    STATUS_COMPLETED => 'primary',
    STATUS_REJECTED => 'danger',
    STATUS_CANCELLED => 'dark'
]);

/**
 * APPROVAL ACTIONS
 */
define('APPROVAL_PENDING', 'pending');
define('APPROVAL_APPROVED', 'approved');
define('APPROVAL_REJECTED', 'rejected');

/**
 * NOTIFICATION TYPES
 * Email and system notification type constants
 */
define('NOTIF_REQUISITION_SUBMITTED', 'requisition_submitted');
define('NOTIF_REQUISITION_APPROVED', 'requisition_approved');
define('NOTIF_REQUISITION_REJECTED', 'requisition_rejected');
define('NOTIF_REQUISITION_PAID', 'requisition_paid');
define('NOTIF_RECEIPT_UPLOADED', 'receipt_uploaded');
define('NOTIF_ACTION_REQUIRED', 'action_required');
define('NOTIF_REQUISITION_CANCELLED', 'requisition_cancelled');
define('NOTIF_PASSWORD_RESET', 'password_reset');
define('NOTIF_ACCOUNT_CREATED', 'account_created');

// Notification Display Names
define('NOTIFICATION_NAMES', [
    NOTIF_REQUISITION_SUBMITTED => 'Requisition Submitted',
    NOTIF_REQUISITION_APPROVED => 'Requisition Approved',
    NOTIF_REQUISITION_REJECTED => 'Requisition Rejected',
    NOTIF_REQUISITION_PAID => 'Requisition Paid',
    NOTIF_RECEIPT_UPLOADED => 'Receipt Uploaded',
    NOTIF_ACTION_REQUIRED => 'Action Required',
    NOTIF_REQUISITION_CANCELLED => 'Requisition Cancelled',
    NOTIF_PASSWORD_RESET => 'Password Reset',
    NOTIF_ACCOUNT_CREATED => 'Account Created'
]);

// System Notification Types (for UI notifications)
define('NOTIFICATION_INFO', 'info');
define('NOTIFICATION_SUCCESS', 'success');
define('NOTIFICATION_WARNING', 'warning');
define('NOTIFICATION_ERROR', 'error');

/**
 * DOCUMENT TYPES
 */
define('DOC_TYPE_INVOICE', 'invoice');
define('DOC_TYPE_RECEIPT', 'receipt');
define('DOC_TYPE_ATTACHMENT', 'attachment');
define('DOC_TYPE_SUPPORTING', 'supporting');

/**
 * FILE UPLOAD CONSTANTS
 */
define('UPLOAD_MAX_SIZE', 5242880); // 5 MB in bytes
define('INVOICE_MAX_SIZE', 10485760); // 10 MB in bytes
define('RECEIPT_MAX_SIZE', 10485760); // 10 MB in bytes

// Allowed file extensions
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_EXTENSIONS', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);
define('ALLOWED_FILE_EXTENSIONS', array_merge(
    ALLOWED_IMAGE_EXTENSIONS, 
    ALLOWED_DOCUMENT_EXTENSIONS
));

// MIME types
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
]);


/**
 * AUDIT LOG ACTION TYPES
 */
// User actions
define('AUDIT_USER_CREATED', 'user_created');
define('AUDIT_USER_UPDATED', 'user_updated');
define('AUDIT_USER_DELETED', 'user_deleted');
define('AUDIT_USER_LOGIN', 'user_login');
define('AUDIT_USER_LOGOUT', 'user_logout');
define('AUDIT_USER_LOGIN_FAILED', 'user_login_failed');
define('AUDIT_USER_PASSWORD_CHANGED', 'user_password_changed');
define('AUDIT_USER_PASSWORD_RESET', 'user_password_reset');
define('AUDIT_USER_ACTIVATED', 'user_activated');
define('AUDIT_USER_DEACTIVATED', 'user_deactivated');

// Department actions
define('AUDIT_DEPARTMENT_CREATED', 'department_created');
define('AUDIT_DEPARTMENT_UPDATED', 'department_updated');
define('AUDIT_DEPARTMENT_DELETED', 'department_deleted');
define('AUDIT_DEPARTMENT_ACTIVATED', 'department_activated');
define('AUDIT_DEPARTMENT_DEACTIVATED', 'department_deactivated');

// Requisition actions
define('AUDIT_REQUISITION_CREATED', 'requisition_created');
define('AUDIT_REQUISITION_UPDATED', 'requisition_updated');
define('AUDIT_REQUISITION_DELETED', 'requisition_deleted');
define('AUDIT_REQUISITION_SUBMITTED', 'requisition_submitted');
define('AUDIT_REQUISITION_APPROVED', 'requisition_approved');
define('AUDIT_REQUISITION_REJECTED', 'requisition_rejected');
define('AUDIT_REQUISITION_CANCELLED', 'requisition_cancelled');
define('AUDIT_REQUISITION_PAID', 'requisition_paid');
define('AUDIT_REQUISITION_COMPLETED', 'requisition_completed');
define('AUDIT_REQUISITION_RETURNED', 'requisition_returned');

// Approval actions
define('AUDIT_APPROVAL_GRANTED', 'approval_granted');
define('AUDIT_APPROVAL_DENIED', 'approval_denied');
define('AUDIT_APPROVAL_PENDING', 'approval_pending');

// Document actions
define('AUDIT_DOCUMENT_UPLOADED', 'document_uploaded');
define('AUDIT_DOCUMENT_DELETED', 'document_deleted');
define('AUDIT_DOCUMENT_VIEWED', 'document_viewed');
define('AUDIT_RECEIPT_UPLOADED', 'receipt_uploaded');
define('AUDIT_INVOICE_UPLOADED', 'invoice_uploaded');
define('AUDIT_ATTACHMENT_UPLOADED', 'attachment_uploaded');

// Finance actions
define('AUDIT_PAYMENT_PROCESSED', 'payment_processed');
define('AUDIT_PAYMENT_VERIFIED', 'payment_verified');
define('AUDIT_PAYMENT_REJECTED', 'payment_rejected');

// System actions
define('AUDIT_SETTINGS_UPDATED', 'settings_updated');
define('AUDIT_BACKUP_CREATED', 'backup_created');
define('AUDIT_BACKUP_RESTORED', 'backup_restored');
define('AUDIT_EMAIL_SENT', 'email_sent');
define('AUDIT_REPORT_GENERATED', 'report_generated');
define('AUDIT_DATA_EXPORTED', 'data_exported');

/**
 * PAGINATION
 */
define('DEFAULT_ITEMS_PER_PAGE', 15);
define('MAX_ITEMS_PER_PAGE', 100);

/**
 * REPORT PERIODS
 */
define('PERIOD_WEEKLY', 'weekly');
define('PERIOD_MONTHLY', 'monthly');
define('PERIOD_QUARTERLY', 'quarterly');
define('PERIOD_YEARLY', 'yearly');
define('PERIOD_CUSTOM', 'custom');

/**
 * QUARTERS
 */
define('QUARTERS', [
    'Q1' => ['start' => '01-01', 'end' => '03-31'],
    'Q2' => ['start' => '04-01', 'end' => '06-30'],
    'Q3' => ['start' => '07-01', 'end' => '09-30'],
    'Q4' => ['start' => '10-01', 'end' => '12-31']
]);

/**
 * EMAIL TEMPLATES (Deprecated - use NOTIF_* constants instead)
 * Kept for backward compatibility
 */
define('EMAIL_REQUISITION_SUBMITTED', NOTIF_REQUISITION_SUBMITTED);
define('EMAIL_REQUISITION_APPROVED', NOTIF_REQUISITION_APPROVED);
define('EMAIL_REQUISITION_REJECTED', NOTIF_REQUISITION_REJECTED);
define('EMAIL_REQUISITION_CANCELLED', NOTIF_REQUISITION_CANCELLED);
define('EMAIL_REQUISITION_PAID', NOTIF_REQUISITION_PAID);
define('EMAIL_RECEIPT_UPLOADED', NOTIF_RECEIPT_UPLOADED);
define('EMAIL_PASSWORD_RESET', NOTIF_PASSWORD_RESET);
define('EMAIL_ACCOUNT_CREATED', NOTIF_ACCOUNT_CREATED);

/**
 * CURRENCY
 */
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_CODE', 'USD');
define('CURRENCY_POSITION', 'before'); // 'before' or 'after'

/**
 * DATE FORMATS
 */
define('DB_DATE_FORMAT', 'Y-m-d');
define('DB_DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'M d, Y');
define('DISPLAY_DATETIME_FORMAT', 'M d, Y H:i:s');
define('DISPLAY_TIME_FORMAT', 'H:i:s');

/**
 * VALIDATION RULES
 */
define('MIN_PASSWORD_LENGTH', 8);
define('MAX_PASSWORD_LENGTH', 128);
define('MIN_USERNAME_LENGTH', 3);
define('MAX_USERNAME_LENGTH', 50);

/**
 * SESSION TIMEOUT
 */
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('REMEMBER_ME_DURATION', 2592000); // 30 days in seconds

/**
 * ========================================
 * HELPER FUNCTIONS
 * ========================================
 */

/**
 * Get role name by ID
 * 
 * @param int $roleId Role ID
 * @return string Role name
 */
function get_role_name($roleId) {
    $roles = ROLE_NAMES;
    return $roles[$roleId] ?? 'Unknown Role';
}

/**
 * Get role code by ID
 * 
 * @param int $roleId Role ID
 * @return string Role code
 */
function get_role_code($roleId) {
    $codes = ROLE_CODES;
    return $codes[$roleId] ?? 'UNKNOWN';
}

/**
 * Get status name
 * 
 * @param string $status Status code
 * @return string Status display name
 */
function get_status_name($status) {
    $statuses = STATUS_NAMES;
    return $statuses[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

/**
 * Get status badge color
 * 
 * @param string $status Status code
 * @return string Badge color class
 */
function get_status_color($status) {
    $colors = STATUS_COLORS;
    return $colors[$status] ?? 'secondary';
}

/**
 * Get notification name
 * 
 * @param string $notificationType Notification type constant
 * @return string Notification display name
 */
function get_notification_name($notificationType) {
    $names = NOTIFICATION_NAMES;
    return $names[$notificationType] ?? ucfirst(str_replace('_', ' ', $notificationType));
}

/**
 * Format currency
 * 
 * @param float $amount Amount to format
 * @param bool $includeSymbol Include currency symbol
 * @return string Formatted currency
 */
function format_currency($amount, $includeSymbol = true) {
    $formatted = number_format($amount, 2);
    
    if ($includeSymbol) {
        if (CURRENCY_POSITION === 'before') {
            return CURRENCY_SYMBOL . $formatted;
        } else {
            return $formatted . ' ' . CURRENCY_SYMBOL;
        }
    }
    
    return $formatted;
}

/**
 * Format date for display
 * 
 * @param string $date Date string
 * @param string $format Format string (default: DISPLAY_DATE_FORMAT)
 * @return string Formatted date
 */
function format_date($date, $format = DISPLAY_DATE_FORMAT) {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    
    try {
        $dateTime = new DateTime($date);
        return $dateTime->format($format);
    } catch (Exception $e) {
        return 'Invalid Date';
    }
}

/**
 * Format datetime for display
 * 
 * @param string $datetime Datetime string
 * @return string Formatted datetime
 */
function format_datetime($datetime) {
    return format_date($datetime, DISPLAY_DATETIME_FORMAT);
}

/**
 * Format time for display
 * 
 * @param string $time Time string
 * @return string Formatted time
 */
function format_time($time) {
    return format_date($time, DISPLAY_TIME_FORMAT);
}

/**
 * Get relative time (e.g., "2 hours ago")
 * 
 * @param string $datetime Datetime string
 * @return string Relative time string
 */
function get_relative_time($datetime) {
    try {
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
        } else {
            return format_date($datetime);
        }
    } catch (Exception $e) {
        return 'Unknown';
    }
}

/**
 * Get file extension from filename
 * 
 * @param string $filename Filename
 * @return string File extension (lowercase)
 */
function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file extension is allowed
 * 
 * @param string $filename Filename
 * @return bool True if allowed
 */
function is_allowed_file($filename) {
    $extension = get_file_extension($filename);
    return in_array($extension, ALLOWED_FILE_EXTENSIONS);
}

/**
 * Get human-readable file size
 * 
 * @param int $bytes File size in bytes
 * @param int $decimals Number of decimal places
 * @return string Formatted file size
 */
function format_file_size($bytes, $decimals = 2) {
    if ($bytes <= 0) {
        return '0 B';
    }
    
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $factor = floor((strlen((string)$bytes) - 1) / 3);
    
    return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), $units[$factor]);
}

/**
 * Generate unique filename
 * 
 * @param string $originalFilename Original filename
 * @return string Unique filename
 */
function generate_unique_filename($originalFilename) {
    $extension = get_file_extension($originalFilename);
    return uniqid() . '_' . time() . '.' . $extension;
}

/**
 * Sanitize filename
 * 
 * @param string $filename Filename to sanitize
 * @return string Sanitized filename
 */
function sanitize_filename($filename) {
    // Remove any path components
    $filename = basename($filename);
    
    // Replace spaces with underscores
    $filename = str_replace(' ', '_', $filename);
    
    // Remove any character that isn't alphanumeric, underscore, hyphen, or dot
    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);
    
    return $filename;
}

/**
 * Get requisition status badge HTML
 * 
 * @param string $status Status code
 * @return string Badge HTML
 */
function get_status_badge($status) {
    $name = get_status_name($status);
    $color = get_status_color($status);
    
    return '<span class="badge badge-' . $color . '">' . htmlspecialchars($name) . '</span>';
}

/**
 * Check if user can edit requisition
 * 
 * @param string $status Current requisition status
 * @param int $userId User ID
 * @param int $creatorId Requisition creator ID
 * @return bool True if can edit
 */
function can_edit_requisition($status, $userId, $creatorId) {
    // Can only edit if:
    // 1. Status is DRAFT or REJECTED
    // 2. User is the creator
    return ($status === STATUS_DRAFT || $status === STATUS_REJECTED) && ($userId === $creatorId);
}

/**
 * Get next approver role for requisition
 * 
 * @param int $currentRoleId Current user's role ID
 * @return int|null Next approver role ID or null if none
 */
function get_next_approver_role($currentRoleId) {
    $workflow = [
        ROLE_TEAM_MEMBER => ROLE_LINE_MANAGER,
        ROLE_LINE_MANAGER => ROLE_MANAGING_DIRECTOR,
        ROLE_MANAGING_DIRECTOR => ROLE_FINANCE_MANAGER,
        ROLE_FINANCE_MANAGER => ROLE_FINANCE_MEMBER
    ];
    
    return $workflow[$currentRoleId] ?? null;
}

/**
 * Get next requisition status after approval
 * 
 * @param int $approverRoleId Approver's role ID
 * @return string Next status
 */
function get_next_status_after_approval($approverRoleId) {
    $statusFlow = [
        ROLE_LINE_MANAGER => STATUS_PENDING_MD,
        ROLE_MANAGING_DIRECTOR => STATUS_PENDING_FINANCE_MANAGER,
        ROLE_FINANCE_MANAGER => STATUS_APPROVED_FOR_PAYMENT,
        ROLE_FINANCE_MEMBER => STATUS_PAID
    ];
    
    return $statusFlow[$approverRoleId] ?? STATUS_PENDING_LINE_MANAGER;
}

/**
 * Get initial requisition status based on requester role
 * 
 * @param int $requesterRoleId Requester's role ID
 * @return string Initial status
 */
function get_initial_requisition_status($requesterRoleId) {
    $initialStatus = [
        ROLE_TEAM_MEMBER => STATUS_PENDING_LINE_MANAGER,
        ROLE_LINE_MANAGER => STATUS_PENDING_MD,
        ROLE_MANAGING_DIRECTOR => STATUS_PENDING_FINANCE_MANAGER
    ];
    
    return $initialStatus[$requesterRoleId] ?? STATUS_DRAFT;
}

/**
 * Check if status is pending approval
 * 
 * @param string $status Status code
 * @return bool True if pending
 */
function is_pending_status($status) {
    $pendingStatuses = [
        STATUS_PENDING_LINE_MANAGER,
        STATUS_PENDING_MD,
        STATUS_PENDING_FINANCE_MANAGER
    ];
    
    return in_array($status, $pendingStatuses);
}

/**
 * Check if status is final (no further action needed)
 * 
 * @param string $status Status code
 * @return bool True if final
 */
function is_final_status($status) {
    $finalStatuses = [
        STATUS_COMPLETED,
        STATUS_REJECTED,
        STATUS_CANCELLED
    ];
    
    return in_array($status, $finalStatuses);
}

/**
 * Generate requisition number
 * 
 * @param int $id Requisition ID
 * @return string Formatted requisition number
 */
function generate_requisition_number($id) {
    $prefix = REQUISITION_NUMBER_PREFIX;
    $paddedId = str_pad($id, REQUISITION_NUMBER_LENGTH - strlen($prefix), '0', STR_PAD_LEFT);
    return $prefix . $paddedId;
}
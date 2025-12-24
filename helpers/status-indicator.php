<?php
/**
 * GateWey Requisition Management System
 * Status Indicator Helper
 * 
 * File: helpers/status-indicator.php
 * Purpose: Generate status indicators with dot styling instead of badges
 */

// Prevent direct access
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

/**
 * Get status indicator with dot styling
 * Now shows person's name instead of role
 *
 * @param string $status Status constant
 * @param array|null $reqData Requisition data (optional, for personalized status)
 * @return string HTML for status indicator
 */
function get_status_indicator($status, $reqData = null) {
    // Default status map (fallback when no approver info available)
    $statusMap = [
        STATUS_DRAFT => ['class' => 'draft', 'text' => 'Draft'],
        STATUS_PENDING_LINE_MANAGER => ['class' => 'pending', 'text' => 'Pending Line Manager'],
        STATUS_PENDING_MD => ['class' => 'pending', 'text' => 'Pending MD'],
        STATUS_PENDING_FINANCE_MANAGER => ['class' => 'pending', 'text' => 'Pending Finance'],
        STATUS_APPROVED_FOR_PAYMENT => ['class' => 'approved', 'text' => 'Approved for Payment'],
        STATUS_PAID => ['class' => 'paid', 'text' => 'Paid'],
        STATUS_COMPLETED => ['class' => 'completed', 'text' => 'Completed'],
        STATUS_REJECTED => ['class' => 'rejected', 'text' => 'Rejected'],
        STATUS_CANCELLED => ['class' => 'cancelled', 'text' => 'Cancelled']
    ];

    $statusInfo = $statusMap[$status] ?? ['class' => 'draft', 'text' => 'Unknown'];

    // NEW: Personalize status text with approver name
    if ($reqData && isset($reqData['current_approver_first_name']) && !empty($reqData['current_approver_first_name'])) {
        $approverName = trim($reqData['current_approver_first_name'] . ' ' . $reqData['current_approver_last_name']);

        if (in_array($status, [STATUS_PENDING_LINE_MANAGER, STATUS_PENDING_MD, STATUS_PENDING_FINANCE_MANAGER])) {
            $statusInfo['text'] = 'Pending ' . htmlspecialchars($approverName);
        }
    }
    // Alternative: use selected_approver if current_approver not set
    elseif ($reqData && isset($reqData['selected_approver_first_name']) && !empty($reqData['selected_approver_first_name'])) {
        $approverName = trim($reqData['selected_approver_first_name'] . ' ' . $reqData['selected_approver_last_name']);

        if (in_array($status, [STATUS_PENDING_LINE_MANAGER, STATUS_PENDING_MD, STATUS_PENDING_FINANCE_MANAGER])) {
            $statusInfo['text'] = 'Pending ' . htmlspecialchars($approverName);
        }
    }

    return '<span class="status-indicator status-' . $statusInfo['class'] . '">
                <span class="status-dot"></span>
                <span class="status-text">' . $statusInfo['text'] . '</span>
            </span>';
}

/**
 * Get short status indicator (for compact displays)
 * 
 * @param string $status Status constant
 * @return string HTML for short status indicator
 */
function get_status_indicator_short($status) {
    $statusMap = [
        STATUS_DRAFT => ['class' => 'draft', 'text' => 'Draft'],
        STATUS_PENDING_LINE_MANAGER => ['class' => 'pending', 'text' => 'Pending LM'],
        STATUS_PENDING_MD => ['class' => 'pending', 'text' => 'Pending MD'],
        STATUS_PENDING_FINANCE_MANAGER => ['class' => 'pending', 'text' => 'Pending Fin.'],
        STATUS_APPROVED_FOR_PAYMENT => ['class' => 'approved', 'text' => 'Approved'],
        STATUS_PAID => ['class' => 'paid', 'text' => 'Paid'],
        STATUS_COMPLETED => ['class' => 'completed', 'text' => 'Completed'],
        STATUS_REJECTED => ['class' => 'rejected', 'text' => 'Rejected'],
        STATUS_CANCELLED => ['class' => 'cancelled', 'text' => 'Cancelled']
    ];
    
    $statusInfo = $statusMap[$status] ?? ['class' => 'draft', 'text' => 'Unknown'];
    
    return '<span class="status-indicator status-' . $statusInfo['class'] . '">
                <span class="status-dot"></span>
                <span class="status-text">' . htmlspecialchars($statusInfo['text']) . '</span>
            </span>';
}

/**
 * Get status dot only (for minimal displays)
 * 
 * @param string $status Status constant
 * @return string HTML for status dot with tooltip
 */
function get_status_dot($status) {
    $statusMap = [
        STATUS_DRAFT => ['class' => 'draft', 'text' => 'Draft'],
        STATUS_PENDING_LINE_MANAGER => ['class' => 'pending', 'text' => 'Pending Line Manager'],
        STATUS_PENDING_MD => ['class' => 'pending', 'text' => 'Pending MD'],
        STATUS_PENDING_FINANCE_MANAGER => ['class' => 'pending', 'text' => 'Pending Finance'],
        STATUS_APPROVED_FOR_PAYMENT => ['class' => 'approved', 'text' => 'Approved for Payment'],
        STATUS_PAID => ['class' => 'paid', 'text' => 'Paid'],
        STATUS_COMPLETED => ['class' => 'completed', 'text' => 'Completed'],
        STATUS_REJECTED => ['class' => 'rejected', 'text' => 'Rejected'],
        STATUS_CANCELLED => ['class' => 'cancelled', 'text' => 'Cancelled']
    ];
    
    $statusInfo = $statusMap[$status] ?? ['class' => 'draft', 'text' => 'Unknown'];
    
    return '<span class="status-dot-only status-' . $statusInfo['class'] . '" 
                  title="' . htmlspecialchars($statusInfo['text']) . '"></span>';
}

/**
 * Get status class name for styling
 * 
 * @param string $status Status constant
 * @return string CSS class name
 */
function get_status_class($status) {
    $statusMap = [
        STATUS_DRAFT => 'draft',
        STATUS_PENDING_LINE_MANAGER => 'pending',
        STATUS_PENDING_MD => 'pending',
        STATUS_PENDING_FINANCE_MANAGER => 'pending',
        STATUS_APPROVED_FOR_PAYMENT => 'approved',
        STATUS_PAID => 'paid',
        STATUS_COMPLETED => 'completed',
        STATUS_REJECTED => 'rejected',
        STATUS_CANCELLED => 'cancelled'
    ];
    
    return $statusMap[$status] ?? 'draft';
}

/**
 * Get status text only
 * 
 * @param string $status Status constant
 * @return string Status text
 */
function get_status_text($status) {
    $statusMap = [
        STATUS_DRAFT => 'Draft',
        STATUS_PENDING_LINE_MANAGER => 'Pending Line Manager',
        STATUS_PENDING_MD => 'Pending MD',
        STATUS_PENDING_FINANCE_MANAGER => 'Pending Finance Manager',
        STATUS_APPROVED_FOR_PAYMENT => 'Approved for Payment',
        STATUS_PAID => 'Paid',
        STATUS_COMPLETED => 'Completed',
        STATUS_REJECTED => 'Rejected',
        STATUS_CANCELLED => 'Cancelled'
    ];
    
    return $statusMap[$status] ?? 'Unknown';
}
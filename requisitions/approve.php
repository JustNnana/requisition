<?php
/**
 * GateWey Requisition Management System
 * Approve Requisition Handler
 * 
 * File: requisitions/approve.php
 * Purpose: Process requisition approval action
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';

// Load helpers
require_once __DIR__ . '/../helpers/permissions.php';
require_once __DIR__ . '/../helpers/status-indicator.php';

// Check if user can approve
if (!can_user_approve()) {
    Session::setFlash('error', 'You do not have permission to approve requisitions.');
    header('Location: ' . get_user_dashboard_url());
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::setFlash('error', 'Invalid request method.');
    header('Location: pending.php');
    exit;
}

// Verify CSRF token
if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    Session::setFlash('error', 'Invalid security token. Please try again.');
    header('Location: pending.php');
    exit;
}

// Get and sanitize input
$requisitionId = Sanitizer::int($_POST['requisition_id'] ?? 0);
$comments = Sanitizer::string($_POST['comments'] ?? '');

// Validate requisition ID
if (!$requisitionId) {
    Session::setFlash('error', 'Invalid requisition ID.');
    header('Location: pending.php');
    exit;
}

// Get current user ID
$userId = Session::getUserId();

// Initialize Approval class
$approval = new Approval();

// Attempt to approve the requisition
$result = $approval->approve($requisitionId, $userId, $comments);

// Set flash message based on result
if ($result['success']) {
    Session::setFlash('success', $result['message']);
    
    // Add info about next step
    if (isset($result['next_approver'])) {
        $nextApproverName = $result['next_approver']['first_name'] . ' ' . $result['next_approver']['last_name'];
        Session::setFlash('info', 'Requisition has been forwarded to ' . $nextApproverName . ' for approval.');
    }
} else {
    Session::setFlash('error', $result['message']);
}

// Redirect back to pending approvals
header('Location: pending.php');
exit;
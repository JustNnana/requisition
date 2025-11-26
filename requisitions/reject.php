<?php
/**
 * GateWey Requisition Management System
 * Reject Requisition Handler
 * 
 * File: requisitions/reject.php
 * Purpose: Process requisition rejection action with mandatory reason
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

// Check if user can approve/reject
if (!can_user_approve()) {
    Session::setFlash('error', 'You do not have permission to reject requisitions.');
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
$reason = Sanitizer::string($_POST['reason'] ?? '');

// Validate requisition ID
if (!$requisitionId) {
    Session::setFlash('error', 'Invalid requisition ID.');
    header('Location: pending.php');
    exit;
}

// Validate rejection reason
$validator = new Validator();
$validator->setData(['reason' => $reason]);
$validator->setRules(['reason' => 'required|min:10']);

if (!$validator->validate()) {
    $errors = $validator->getErrors();
    Session::setFlash('error', $errors['reason'][0] ?? 'Rejection reason is required (minimum 10 characters).');
    header('Location: view.php?id=' . $requisitionId);
    exit;
}

// Get current user ID
$userId = Session::getUserId();

// Initialize Approval class
$approval = new Approval();

// Attempt to reject the requisition
$result = $approval->reject($requisitionId, $userId, $reason);

// Set flash message based on result
if ($result['success']) {
    Session::setFlash('success', $result['message']);
    Session::setFlash('info', 'The requester has been notified and can now edit and resubmit the requisition.');
} else {
    Session::setFlash('error', $result['message']);
}

// Redirect back to pending approvals
header('Location: pending.php');
exit;
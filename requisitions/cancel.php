<?php
/**
 * GateWey Requisition Management System
 * Cancel Requisition Handler
 * 
 * File: requisitions/cancel.php
 * Purpose: POST handler for cancelling requisitions
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';
require_once __DIR__ . '/../helpers/permissions.php';

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::setFlash('error', 'Invalid request method.');
    header('Location: list.php');
    exit;
}

// Verify CSRF token
if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    Session::setFlash('error', 'Invalid security token. Please try again.');
    header('Location: list.php');
    exit;
}

// Get requisition ID
$requisitionId = isset($_POST['requisition_id']) ? (int)$_POST['requisition_id'] : 0;

if (!$requisitionId) {
    Session::setFlash('error', 'Invalid requisition ID.');
    header('Location: list.php');
    exit;
}

// Initialize objects
$requisition = new Requisition();

// Cancel the requisition
$result = $requisition->cancel($requisitionId);

if ($result['success']) {
    Session::setFlash('success', $result['message']);
} else {
    Session::setFlash('error', $result['message']);
}

// Redirect to list
header('Location: list.php');
exit;
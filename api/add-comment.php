<?php
/**
 * GateWey Requisition Management System
 * Add Comment API
 * 
 * File: api/add-comment.php
 * Purpose: Add approval comments to requisitions (AJAX endpoint)
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';

// Set JSON header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

// Verify CSRF token
if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid security token.'
    ]);
    exit;
}

// Get and sanitize input
$requisitionId = Sanitizer::int($_POST['requisition_id'] ?? 0);
$comment = Sanitizer::string($_POST['comment'] ?? '');

// Validate input
if (!$requisitionId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid requisition ID.'
    ]);
    exit;
}

// Validate comment
$validator = new Validator();
$validator->setData(['comment' => $comment]);
$validator->setRules(['comment' => 'required|min:5']);

if (!$validator->validate()) {
    $errors = $validator->getErrors();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $errors['comment'][0] ?? 'Comment is required (minimum 5 characters).'
    ]);
    exit;
}

// Get current user ID
$userId = Session::getUserId();

// Initialize Approval class
$approval = new Approval();

// Add the comment
$result = $approval->addComment($requisitionId, $userId, $comment);

// Return result
if ($result['success']) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $result['message'],
        'comment' => [
            'user_id' => $userId,
            'user_name' => Session::getUserFullName(),
            'comment' => $comment,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $result['message']
    ]);
}

exit;
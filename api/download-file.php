<?php
/**
 * GateWey Requisition Management System
 * Download File API Endpoint
 * 
 * File: api/download-file.php
 * Purpose: Serve requisition documents securely
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

// Get document ID
$documentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$documentId) {
    http_response_code(400);
    die('Invalid document ID.');
}

// Initialize file upload handler
$fileUpload = new FileUpload();

// Serve the file
$result = $fileUpload->serveFile($documentId);

if (!$result) {
    http_response_code(404);
    die('File not found or access denied.');
}

// File served successfully (output handled in FileUpload class)
exit;
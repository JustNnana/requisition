<?php
/**
 * GateWey Requisition Management System
 * Get Child Categories API Endpoint
 * 
 * File: api/get-child-categories.php
 * Purpose: Return child categories for a given parent category (AJAX)
 * 
 * USAGE: Called from requisition forms when parent category is selected
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Set JSON header
header('Content-Type: application/json');

// Check authentication
if (!Session::isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized access'
    ]);
    exit;
}

// Initialize category model
$categoryModel = new RequisitionCategory();

// Get parent ID from request
$parentId = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;

// Validate parent ID
if ($parentId <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid parent category ID'
    ]);
    exit;
}

// Verify parent category exists
$parentCategory = $categoryModel->getById($parentId);

if (!$parentCategory) {
    echo json_encode([
        'success' => false,
        'error' => 'Parent category not found'
    ]);
    exit;
}

// Get child categories (active only)
$children = $categoryModel->getChildCategories($parentId, true);

// Return success response
echo json_encode([
    'success' => true,
    'parent' => [
        'id' => $parentCategory['id'],
        'name' => $parentCategory['category_name'],
        'code' => $parentCategory['category_code']
    ],
    'children' => $children,
    'count' => count($children)
]);
exit;
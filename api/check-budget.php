<?php
/**
 * GateWey Requisition Management System
 * Check Budget Availability API
 * 
 * File: api/check-budget.php
 * Purpose: AJAX endpoint to check if requisition amount is within budget
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
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required.'
    ]);
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !Session::validateCsrfToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid security token.'
    ]);
    exit;
}

// Get parameters
$departmentId = isset($_POST['department_id']) ? (int)$_POST['department_id'] : 0;
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;

// Validate inputs
if (!$departmentId || $amount <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid parameters.'
    ]);
    exit;
}

// Verify user has access to this department
$userDepartmentId = Session::getUserDepartmentId();
$userRole = Session::getUserRole();

// Managing Director and Finance roles can check any department
$canCheckAnyDepartment = in_array($userRole, [ROLE_MANAGING_DIRECTOR, ROLE_FINANCE_MANAGER, ROLE_FINANCE_MEMBER]);

if (!$canCheckAnyDepartment && $departmentId != $userDepartmentId) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'You do not have permission to check this department\'s budget.'
    ]);
    exit;
}

try {
    // Get budget information
    $budgetModel = new Budget();
    $budgetInfo = $budgetModel->getBudgetStats($departmentId);
    
    if (!$budgetInfo) {
        echo json_encode([
            'success' => false,
            'has_budget' => false,
            'message' => 'No active budget found for this department.'
        ]);
        exit;
    }
    
    // Check availability
    $availableAmount = (float)$budgetInfo['available_amount'];
    $isSufficient = $availableAmount >= $amount;
    $remainingAfter = $availableAmount - $amount;
    $utilizationPercentage = ($amount / (float)$budgetInfo['budget_amount']) * 100;
    
    // Determine status level
    $statusLevel = 'success'; // green
    if ($utilizationPercentage >= 90 || !$isSufficient) {
        $statusLevel = 'danger'; // red
    } elseif ($utilizationPercentage >= 75) {
        $statusLevel = 'warning'; // orange
    }
    
    // Build response
    $response = [
        'success' => true,
        'has_budget' => true,
        'sufficient' => $isSufficient,
        'budget_amount' => (float)$budgetInfo['budget_amount'],
        'allocated_amount' => (float)$budgetInfo['allocated_amount'],
        'available_amount' => $availableAmount,
        'requested_amount' => $amount,
        'remaining_after' => $remainingAfter,
        'utilization_percentage' => round($utilizationPercentage, 2),
        'status_level' => $statusLevel,
        'message' => $isSufficient 
            ? 'Budget is sufficient for this requisition.' 
            : 'Insufficient budget. Amount exceeds available budget by ₦' . number_format(abs($remainingAfter), 2) . '.'
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Budget check API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while checking budget availability.'
    ]);
}
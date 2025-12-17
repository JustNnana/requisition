<?php
/**
 * AJAX Endpoint: Get Department Budget Data
 * 
 * Purpose: Fetch real-time budget information for:
 * - Dashboard budget cards
 * - Requisition form budget validation
 * - Line Manager budget status
 */

define('APP_ACCESS', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/session.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Budget.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
Session::requireLogin();

try {
    // Get department ID from request
    $departmentId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : null;
    
    // If no department specified, get user's department
    if (!$departmentId) {
        $departmentId = Session::getDepartmentId();
    }
    
    // Validate department access
    $userRoleId = Session::getRoleId();
    $userDeptId = Session::getDepartmentId();
    
    // Only Line Managers and Finance roles can view budget
    // Line Managers can only view their own department
    // Finance Manager can view any department
    if ($userRoleId == ROLE_LINE_MANAGER && $departmentId != $userDeptId) {
        echo json_encode([
            'success' => false,
            'message' => 'Access denied: You can only view your own department budget.'
        ]);
        exit;
    }
    
    // Check if user has permission to view budgets
    if (!in_array($userRoleId, [ROLE_FINANCE_MANAGER, ROLE_LINE_MANAGER])) {
        echo json_encode([
            'success' => false,
            'message' => 'Access denied: Insufficient permissions to view budget.'
        ]);
        exit;
    }
    
    if (!$departmentId) {
        echo json_encode([
            'success' => false,
            'message' => 'No department specified and user is not assigned to a department.'
        ]);
        exit;
    }
    
    // Initialize Budget class
    $budget = new Budget();
    
    // Get active budget
    $activeBudget = $budget->getActiveBudget($departmentId);
    
    if (!$activeBudget) {
        echo json_encode([
            'success' => true,
            'has_budget' => false,
            'message' => 'No active budget set for this department.',
            'data' => null
        ]);
        exit;
    }
    
    // Get budget statistics
    $stats = $budget->getBudgetStats($departmentId);
    
    // Calculate additional metrics
    $utilizationPercentage = $activeBudget['budget_amount'] > 0 
        ? round(($activeBudget['allocated_amount'] / $activeBudget['budget_amount']) * 100, 2)
        : 0;
    
    $availablePercentage = 100 - $utilizationPercentage;
    
    // Determine status color
    $statusColor = 'success'; // Green
    if ($utilizationPercentage >= 90) {
        $statusColor = 'danger'; // Red
    } elseif ($utilizationPercentage >= 70) {
        $statusColor = 'warning'; // Yellow
    }
    
    // Calculate days remaining
    $today = new DateTime();
    $endDate = new DateTime($activeBudget['end_date']);
    $daysRemaining = $today->diff($endDate)->days;
    
    // Prepare response
    $response = [
        'success' => true,
        'has_budget' => true,
        'data' => [
            'budget_id' => $activeBudget['id'],
            'department_id' => $activeBudget['department_id'],
            'department_name' => $activeBudget['department_name'],
            'department_code' => $activeBudget['department_code'],
            
            // Budget amounts
            'budget_amount' => (float)$activeBudget['budget_amount'],
            'allocated_amount' => (float)$activeBudget['allocated_amount'],
            'available_amount' => (float)$activeBudget['available_amount'],
            
            // Formatted amounts
            'budget_amount_formatted' => '₦' . number_format($activeBudget['budget_amount'], 2),
            'allocated_amount_formatted' => '₦' . number_format($activeBudget['allocated_amount'], 2),
            'available_amount_formatted' => '₦' . number_format($activeBudget['available_amount'], 2),
            
            // Percentages
            'utilization_percentage' => $utilizationPercentage,
            'available_percentage' => $availablePercentage,
            
            // Status
            'status' => $activeBudget['status'],
            'status_color' => $statusColor,
            
            // Dates
            'start_date' => $activeBudget['start_date'],
            'end_date' => $activeBudget['end_date'],
            'start_date_formatted' => date('M d, Y', strtotime($activeBudget['start_date'])),
            'end_date_formatted' => date('M d, Y', strtotime($activeBudget['end_date'])),
            'days_remaining' => $daysRemaining,
            
            // Additional info
            'duration_type' => $activeBudget['duration_type'],
            'created_by' => $activeBudget['created_by_name'],
            'created_at' => $activeBudget['created_at'],
            
            // Statistics (if available)
            'total_allocations' => $stats['total_allocations'] ?? 0,
            'active_allocations' => $stats['active_allocations'] ?? 0
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Get department budget error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving budget data: ' . $e->getMessage()
    ]);
}
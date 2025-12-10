<?php
/**
 * GateWey Requisition Management System
 * Budget Management Class
 * 
 * File: classes/Budget.php
 * Purpose: Handle department budget operations, allocation, and tracking
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

class Budget {
    
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Set budget for a department
     * 
     * @param int $departmentId Department ID
     * @param float $amount Budget amount
     * @param string $durationType quarterly, yearly, or custom
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @param int $createdBy User ID creating the budget
     * @return array Result with success status
     */
    public function setBudget($departmentId, $amount, $durationType, $startDate, $endDate, $createdBy) {
        try {
            // Validate inputs
            if (empty($departmentId) || empty($amount) || empty($durationType) || empty($startDate) || empty($endDate)) {
                return ['success' => false, 'message' => 'All fields are required.'];
            }
            
            if ($amount <= 0) {
                return ['success' => false, 'message' => 'Budget amount must be greater than zero.'];
            }
            
            if (strtotime($startDate) >= strtotime($endDate)) {
                return ['success' => false, 'message' => 'End date must be after start date.'];
            }
            
            // Check if there's an active budget that overlaps with this period
            $sql = "SELECT id FROM department_budgets 
                    WHERE department_id = ? 
                    AND status = 'active'
                    AND (
                        (start_date BETWEEN ? AND ?) OR
                        (end_date BETWEEN ? AND ?) OR
                        (start_date <= ? AND end_date >= ?)
                    )";
            
            $overlap = $this->db->fetchOne($sql, [
                $departmentId,
                $startDate, $endDate,
                $startDate, $endDate,
                $startDate, $endDate
            ]);
            
            if ($overlap) {
                return ['success' => false, 'message' => 'There is already an active budget for this department that overlaps with the selected period.'];
            }
            
            // Determine status based on dates
            $today = date('Y-m-d');
            $status = 'upcoming';
            if ($startDate <= $today && $endDate >= $today) {
                $status = 'active';
            } elseif ($endDate < $today) {
                $status = 'expired';
            }
            
            // Insert new budget
            $sql = "INSERT INTO department_budgets 
                    (department_id, budget_amount, allocated_amount, available_amount, 
                     duration_type, start_date, end_date, status, created_by, created_at, updated_at)
                    VALUES (?, ?, 0.00, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $this->db->execute($sql, [
                $departmentId,
                $amount,
                $amount, // available_amount starts equal to budget_amount
                $durationType,
                $startDate,
                $endDate,
                $status,
                $createdBy
            ]);
            
            // Log action
            if (defined('ENABLE_AUDIT_LOG') && ENABLE_AUDIT_LOG) {
                $this->logBudgetAction(
                    $departmentId,
                    'budget_set',
                    "Budget of ₦" . number_format($amount, 2) . " set for period {$startDate} to {$endDate}"
                );
            }
            
            return [
                'success' => true,
                'message' => 'Budget set successfully.',
                'budget_id' => $this->db->getConnection()->lastInsertId()
            ];
            
        } catch (Exception $e) {
            error_log("Set budget error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to set budget. Please try again.'];
        }
    }
    
    /**
     * Get active budget for a department
     * 
     * @param int $departmentId Department ID
     * @return array|null Budget details or null if not found
     */
    public function getActiveBudget($departmentId) {
        try {
            $sql = "SELECT b.*, d.department_name, d.department_code,
                           CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                    FROM department_budgets b
                    INNER JOIN departments d ON b.department_id = d.id
                    INNER JOIN users u ON b.created_by = u.id
                    WHERE b.department_id = ? 
                    AND b.status = 'active'
                    AND b.start_date <= CURDATE()
                    AND b.end_date >= CURDATE()
                    ORDER BY b.created_at DESC
                    LIMIT 1";
            
            return $this->db->fetchOne($sql, [$departmentId]);
        } catch (Exception $e) {
            error_log("Get active budget error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if amount is available in department budget
     * 
     * @param int $departmentId Department ID
     * @param float $amount Amount to check
     * @return bool True if available
     */
    public function checkAvailability($departmentId, $amount) {
        try {
            $budget = $this->getActiveBudget($departmentId);
            
            if (!$budget) {
                return false; // No active budget
            }
            
            return (float)$budget['available_amount'] >= (float)$amount;
        } catch (Exception $e) {
            error_log("Check availability error: " . $e->getMessage());
            return false;
        }
    }
    
/**
 * Allocate budget when Line Manager approves requisition
 * 
 * @param int $budgetId Budget ID
 * @param int $requisitionId Requisition ID
 * @param float $amount Amount to allocate
 * @param string $notes Optional notes
 * @return array Result with success status
 */
public function allocateBudget($budgetId, $requisitionId, $amount, $notes = '') {
    try {
        // Check if we're already in a transaction (called from Approval.php)
        $inTransaction = $this->db->getConnection()->inTransaction();
        $startedTransaction = false;
        
        // Only start a new transaction if we're not already in one
        if (!$inTransaction) {
            $this->db->getConnection()->beginTransaction();
            $startedTransaction = true;
        }
        
        // Get current budget with row lock
        $sql = "SELECT * FROM department_budgets WHERE id = ? FOR UPDATE";
        $budget = $this->db->fetchOne($sql, [$budgetId]);
        
        if (!$budget) {
            if ($startedTransaction) {
                $this->db->getConnection()->rollBack();
            }
            return ['success' => false, 'message' => 'Budget not found.'];
        }
        
        // Check if enough budget is available
        if ((float)$budget['available_amount'] < (float)$amount) {
            if ($startedTransaction) {
                $this->db->getConnection()->rollBack();
            }
            return [
                'success' => false, 
                'message' => 'Insufficient budget available. Available: ₦' . number_format($budget['available_amount'], 2) . ', Required: ₦' . number_format($amount, 2)
            ];
        }
        
        // Update budget amounts
        $sql = "UPDATE department_budgets 
                SET allocated_amount = allocated_amount + ?,
                    available_amount = available_amount - ?,
                    updated_at = NOW()
                WHERE id = ?";
        
        $this->db->execute($sql, [$amount, $amount, $budgetId]);
        
        // Record allocation
        $sql = "INSERT INTO budget_allocations 
                (budget_id, requisition_id, amount, allocation_type, allocated_at, notes)
                VALUES (?, ?, ?, 'allocated', NOW(), ?)";
        
        $this->db->execute($sql, [$budgetId, $requisitionId, $amount, $notes]);
        
        // Only commit if WE started the transaction
        if ($startedTransaction) {
            $this->db->getConnection()->commit();
        }
        
        // Log action
        if (defined('ENABLE_AUDIT_LOG') && ENABLE_AUDIT_LOG) {
            $this->logBudgetAction(
                $budget['department_id'],
                'budget_allocated',
                "Budget of ₦" . number_format($amount, 2) . " allocated to requisition #{$requisitionId}"
            );
        }
        
        return [
            'success' => true,
            'message' => 'Budget allocated successfully.'
        ];
        
    } catch (Exception $e) {
        // Only rollback if WE started the transaction
        if (isset($startedTransaction) && $startedTransaction && $this->db->getConnection()->inTransaction()) {
            $this->db->getConnection()->rollBack();
        }
        
        error_log("Allocate budget error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        return [
            'success' => false, 
            'message' => 'Failed to allocate budget: ' . $e->getMessage()
        ];
    }
}
    
/**
 * Release budget when requisition is rejected
 * 
 * @param int $requisitionId Requisition ID
 * @param string|null $notes Optional notes for release
 * @return array Result with success status
 */
public function releaseBudget($requisitionId, $notes = null) {
    try {
        // Check if we're already in a transaction
        $inTransaction = $this->db->getConnection()->inTransaction();
        $startedTransaction = false;
        
        // Only start a new transaction if we're not already in one
        if (!$inTransaction) {
            $this->db->getConnection()->beginTransaction();
            $startedTransaction = true;
        }
        
        // Get allocation record
        $sql = "SELECT * FROM budget_allocations 
                WHERE requisition_id = ? 
                AND allocation_type = 'allocated'
                ORDER BY allocated_at DESC 
                LIMIT 1";
        
        $allocation = $this->db->fetchOne($sql, [$requisitionId]);
        
        if (!$allocation) {
            if ($startedTransaction) {
                $this->db->getConnection()->rollBack();
            }
            return ['success' => false, 'message' => 'No allocation found for this requisition.'];
        }
        
        // Update budget amounts
        $sql = "UPDATE department_budgets 
                SET allocated_amount = allocated_amount - ?,
                    available_amount = available_amount + ?,
                    updated_at = NOW()
                WHERE id = ?";
        
        $this->db->execute($sql, [
            $allocation['amount'],
            $allocation['amount'],
            $allocation['budget_id']
        ]);
        
        // Update allocation record to mark as released
        $releaseNotes = $notes ?: 'Released due to rejection';
        $sql = "UPDATE budget_allocations 
                SET allocation_type = 'released',
                    released_at = NOW(),
                    notes = CONCAT(COALESCE(notes, ''), ' - ', ?)
                WHERE id = ?";
        
        $this->db->execute($sql, [$releaseNotes, $allocation['id']]);
        
        // Only commit if WE started the transaction
        if ($startedTransaction) {
            $this->db->getConnection()->commit();
        }
        
        // Get budget info for logging
        $budget = $this->db->fetchOne("SELECT department_id FROM department_budgets WHERE id = ?", [$allocation['budget_id']]);
        
        // Log action
        if (defined('ENABLE_AUDIT_LOG') && ENABLE_AUDIT_LOG && $budget) {
            $logMessage = "Budget of ₦" . number_format($allocation['amount'], 2) . " released from requisition #{$requisitionId}";
            if ($notes) {
                $logMessage .= " - {$notes}";
            }
            
            $this->logBudgetAction(
                $budget['department_id'],
                'budget_released',
                $logMessage
            );
        }
        
        return [
            'success' => true,
            'message' => 'Budget released successfully.'
        ];
        
    } catch (Exception $e) {
        // Only rollback if WE started the transaction
        if (isset($startedTransaction) && $startedTransaction && $this->db->getConnection()->inTransaction()) {
            $this->db->getConnection()->rollBack();
        }
        
        error_log("Release budget error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        return [
            'success' => false, 
            'message' => 'Failed to release budget: ' . $e->getMessage()
        ];
    }
}
    
    /**
     * Get allocation record for a requisition
     * 
     * @param int $requisitionId Requisition ID
     * @return array|null Allocation details or null
     */
    public function getAllocationByRequisition($requisitionId) {
        try {
            $sql = "SELECT * FROM budget_allocations 
                    WHERE requisition_id = ? 
                    AND allocation_type = 'allocated'
                    ORDER BY allocated_at DESC 
                    LIMIT 1";
            
            return $this->db->fetchOne($sql, [$requisitionId]);
        } catch (Exception $e) {
            error_log("Get allocation error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if budget can be set (based on last budget end date)
     * 
     * @param int $departmentId Department ID
     * @return array Result with can_set boolean and message
     */
    public function canSetBudget($departmentId) {
        try {
            // Get the most recent budget
            $sql = "SELECT * FROM department_budgets 
                    WHERE department_id = ? 
                    ORDER BY end_date DESC 
                    LIMIT 1";
            
            $lastBudget = $this->db->fetchOne($sql, [$departmentId]);
            
            if (!$lastBudget) {
                return [
                    'can_set' => true,
                    'message' => 'No previous budget found. You can set a new budget.'
                ];
            }
            
            // Check if last budget has expired
            $today = date('Y-m-d');
            if ($lastBudget['end_date'] >= $today) {
                return [
                    'can_set' => false,
                    'message' => 'An active budget exists until ' . date('F d, Y', strtotime($lastBudget['end_date'])) . '. Please wait until it expires.',
                    'last_budget' => $lastBudget
                ];
            }
            
            return [
                'can_set' => true,
                'message' => 'Previous budget has expired. You can set a new budget.',
                'last_budget' => $lastBudget
            ];
            
        } catch (Exception $e) {
            error_log("Can set budget error: " . $e->getMessage());
            return [
                'can_set' => false,
                'message' => 'Error checking budget status.'
            ];
        }
    }
    
    /**
     * Expire old budgets (run via cron or manually)
     * 
     * @return int Number of budgets expired
     */
    public function expireBudgets() {
        try {
            $sql = "UPDATE department_budgets 
                    SET status = 'expired', updated_at = NOW()
                    WHERE status = 'active' 
                    AND end_date < CURDATE()";
            
            $this->db->execute($sql);
            
            return $this->db->getConnection()->lastInsertId();
        } catch (Exception $e) {
            error_log("Expire budgets error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Activate upcoming budgets
     * 
     * @return int Number of budgets activated
     */
    public function activateUpcomingBudgets() {
        try {
            $sql = "UPDATE department_budgets 
                    SET status = 'active', updated_at = NOW()
                    WHERE status = 'upcoming' 
                    AND start_date <= CURDATE()
                    AND end_date >= CURDATE()";
            
            $this->db->execute($sql);
            
            return $this->db->getConnection()->lastInsertId();
        } catch (Exception $e) {
            error_log("Activate budgets error: " . $e->getMessage());
            return 0;
        }
    }
    
/**
 * Get budget usage statistics for a department
 * 
 * @param int $departmentId Department ID
 * @param int|null $budgetId Specific budget ID (optional)
 * @return array|null Budget statistics or null if not found
 */
public function getBudgetStats($departmentId = null, $budgetId = null) {
    try {
        if ($budgetId) {
            // Get stats for specific budget
            $sql = "SELECT b.*, d.department_name, d.department_code,
                           (SELECT COUNT(*) FROM budget_allocations WHERE budget_id = b.id) as total_allocations,
                           (SELECT COUNT(*) FROM budget_allocations WHERE budget_id = b.id AND allocation_type = 'allocated') as active_allocations,
                           ROUND((b.allocated_amount / b.budget_amount) * 100, 2) as utilization_percentage
                    FROM department_budgets b
                    INNER JOIN departments d ON b.department_id = d.id
                    WHERE b.id = ?";
            
            return $this->db->fetchOne($sql, [$budgetId]);
        } else if ($departmentId) {
            // Get stats for active budget
            $budget = $this->getActiveBudget($departmentId);
            
            if (!$budget) {
                return null;
            }
            
            return $this->getBudgetStats(null, $budget['id']);
        }
        
        return null;
    } catch (Exception $e) {
        error_log("Get budget stats error: " . $e->getMessage());
        return null;
    }
}
    
    /**
     * Get all budgets for a department
     * 
     * @param int $departmentId Department ID
     * @param string|null $status Filter by status (optional)
     * @return array List of budgets
     */
    public function getDepartmentBudgets($departmentId, $status = null) {
        try {
            $sql = "SELECT b.*, d.department_name, d.department_code,
                           CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                           ROUND((b.allocated_amount / b.budget_amount) * 100, 2) as utilization_percentage
                    FROM department_budgets b
                    INNER JOIN departments d ON b.department_id = d.id
                    INNER JOIN users u ON b.created_by = u.id
                    WHERE b.department_id = ?";
            
            $params = [$departmentId];
            
            if ($status) {
                $sql .= " AND b.status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY b.created_at DESC";
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Get department budgets error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all budgets (for Finance Manager)
     * 
     * @return array List of all budgets
     */
    public function getAllBudgets() {
        try {
            $sql = "SELECT b.*, d.department_name, d.department_code,
                           CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                           ROUND((b.allocated_amount / b.budget_amount) * 100, 2) as utilization_percentage
                    FROM department_budgets b
                    INNER JOIN departments d ON b.department_id = d.id
                    INNER JOIN users u ON b.created_by = u.id
                    ORDER BY b.status ASC, b.end_date DESC, b.created_at DESC";
            
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            error_log("Get all budgets error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get budget allocation history
     * 
     * @param int $budgetId Budget ID
     * @return array List of allocations
     */
    public function getAllocationHistory($budgetId) {
        try {
            $sql = "SELECT ba.*, r.requisition_number, r.purpose, r.status as requisition_status,
                           CONCAT(u.first_name, ' ', u.last_name) as requester_name
                    FROM budget_allocations ba
                    INNER JOIN requisitions r ON ba.requisition_id = r.id
                    INNER JOIN users u ON r.user_id = u.id
                    WHERE ba.budget_id = ?
                    ORDER BY ba.allocated_at DESC";
            
            return $this->db->fetchAll($sql, [$budgetId]);
        } catch (Exception $e) {
            error_log("Get allocation history error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Log budget action to audit trail
     * 
     * @param int $departmentId Department ID
     * @param string $action Action type
     * @param string $description Action description
     */
    private function logBudgetAction($departmentId, $action, $description) {
        try {
            $sql = "INSERT INTO audit_log 
                    (requisition_id, user_id, action, description, ip_address, user_agent, created_at)
                    VALUES (NULL, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                Session::getUserId(),
                $action,
                $description . " (Department: $departmentId)",
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ];
            
            $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Log budget action error: " . $e->getMessage());
        }
    }
    
    /**
 * Update existing budget
 * 
 * @param int $budgetId Budget ID to update
 * @param float $amount New budget amount
 * @param string $durationType quarterly, yearly, or custom
 * @param string $startDate Start date (YYYY-MM-DD)
 * @param string $endDate End date (YYYY-MM-DD)
 * @param int $updatedBy User ID updating the budget
 * @return array Result with success status
 */
public function updateBudget($budgetId, $amount, $durationType, $startDate, $endDate, $updatedBy) {
    try {
        // Validate inputs
        if (empty($budgetId) || empty($amount) || empty($durationType) || empty($startDate) || empty($endDate)) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }
        
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Budget amount must be greater than zero.'];
        }
        
        if (strtotime($startDate) >= strtotime($endDate)) {
            return ['success' => false, 'message' => 'End date must be after start date.'];
        }
        
        // Get current budget data
        $sql = "SELECT * FROM department_budgets WHERE id = ?";
        $currentBudget = $this->db->fetchOne($sql, [$budgetId]);
        
        if (!$currentBudget) {
            return ['success' => false, 'message' => 'Budget not found.'];
        }
        
        // Validate: Cannot decrease budget below allocated amount
        if ($amount < (float)$currentBudget['allocated_amount']) {
            return [
                'success' => false, 
                'message' => 'Budget amount cannot be less than currently allocated amount (₦' . number_format($currentBudget['allocated_amount'], 2) . ').'
            ];
        }
        
        // Validate: Cannot change start date if budget is active or expired
        if (($currentBudget['status'] === 'active' || $currentBudget['status'] === 'expired') 
            && $startDate !== $currentBudget['start_date']) {
            return [
                'success' => false, 
                'message' => 'Cannot change start date of ' . $currentBudget['status'] . ' budget.'
            ];
        }
        
        // Validate: End date cannot be in the past
        $today = date('Y-m-d');
        if ($endDate < $today) {
            return ['success' => false, 'message' => 'End date cannot be in the past.'];
        }
        
        // Check for overlapping budgets (excluding current budget)
        $sql = "SELECT id FROM department_budgets 
                WHERE department_id = ? 
                AND id != ?
                AND status IN ('active', 'upcoming')
                AND (
                    (start_date BETWEEN ? AND ?) OR
                    (end_date BETWEEN ? AND ?) OR
                    (start_date <= ? AND end_date >= ?)
                )";
        
        $overlap = $this->db->fetchOne($sql, [
            $currentBudget['department_id'],
            $budgetId,
            $startDate, $endDate,
            $startDate, $endDate,
            $startDate, $endDate
        ]);
        
        if ($overlap) {
            return ['success' => false, 'message' => 'The selected period overlaps with another budget for this department.'];
        }
        
        // Determine new status based on dates
        $status = 'upcoming';
        if ($startDate <= $today && $endDate >= $today) {
            $status = 'active';
        } elseif ($endDate < $today) {
            $status = 'expired';
        }
        
        // Calculate new available amount
        $allocatedAmount = (float)$currentBudget['allocated_amount'];
        $newAvailableAmount = $amount - $allocatedAmount;
        
        // Update budget
        $sql = "UPDATE department_budgets 
                SET budget_amount = ?,
                    available_amount = ?,
                    duration_type = ?,
                    start_date = ?,
                    end_date = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE id = ?";
        
        $this->db->execute($sql, [
            $amount,
            $newAvailableAmount,
            $durationType,
            $startDate,
            $endDate,
            $status,
            $budgetId
        ]);
        
        // Log action
        if (defined('ENABLE_AUDIT_LOG') && ENABLE_AUDIT_LOG) {
            $changes = [];
            if ($currentBudget['budget_amount'] != $amount) {
                $changes[] = "Amount: ₦" . number_format($currentBudget['budget_amount'], 2) . " → ₦" . number_format($amount, 2);
            }
            if ($currentBudget['start_date'] != $startDate) {
                $changes[] = "Start: {$currentBudget['start_date']} → {$startDate}";
            }
            if ($currentBudget['end_date'] != $endDate) {
                $changes[] = "End: {$currentBudget['end_date']} → {$endDate}";
            }
            if ($currentBudget['duration_type'] != $durationType) {
                $changes[] = "Type: {$currentBudget['duration_type']} → {$durationType}";
            }
            
            $changeLog = implode(', ', $changes);
            $this->logBudgetAction(
                $currentBudget['department_id'],
                'budget_updated',
                "Budget updated. Changes: {$changeLog}"
            );
        }
        
        return [
            'success' => true,
            'message' => 'Budget updated successfully.'
        ];
        
    } catch (Exception $e) {
        error_log("Update budget error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update budget. Please try again.'];
    }
}
}

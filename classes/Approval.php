<?php
/**
 * GateWey Requisition Management System
 * Approval Class - WITH BUDGET ALLOCATION/RELEASE LOGIC
 * 
 * File: classes/Approval.php
 * Purpose: Handle requisition approval and rejection operations
 * 
 * UPDATED: Added budget allocation on Line Manager approval and budget release on rejection
 */

class Approval {
    
    private $db;
    private $workflow;
    private $auditLog;
    private $budgetModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->workflow = new WorkflowEngine();
        $this->auditLog = new AuditLog();
        $this->budgetModel = new Budget(); // Initialize budget model
    }
    
    /**
     * Approve a requisition
     * WITH BUDGET ALLOCATION LOGIC
     * 
     * @param int $requisitionId Requisition ID
     * @param int $approverId Approver user ID
     * @param string|null $comments Optional comments
     * @return array Result with success status and message
     */
    public function approve($requisitionId, $approverId, $comments = null) {
        try {
            // Verify approver can approve this requisition
            if (!$this->workflow->canUserApprove($approverId, $requisitionId)) {
                return [
                    'success' => false,
                    'message' => 'You do not have permission to approve this requisition.'
                ];
            }
            
            // Get requisition details with category and department info
            $requisition = $this->db->fetchOne(
                "SELECT r.*, rc.category_name, rc.id as category_id, d.department_name
                 FROM requisitions r
                 LEFT JOIN requisition_categories rc ON r.category_id = rc.id
                 LEFT JOIN departments d ON r.department_id = d.id
                 WHERE r.id = ?",
                [$requisitionId]
            );
            
            if (!$requisition) {
                return [
                    'success' => false,
                    'message' => 'Requisition not found.'
                ];
            }
            
            // Check if already approved or in final state
            $nonApprovableStatuses = [STATUS_PAID, STATUS_COMPLETED, STATUS_CANCELLED];
            if (in_array($requisition['status'], $nonApprovableStatuses)) {
                return [
                    'success' => false,
                    'message' => 'This requisition cannot be approved in its current state.'
                ];
            }
            
            // Get approver details
            $approver = $this->db->fetchOne(
                "SELECT * FROM users WHERE id = ?",
                [$approverId]
            );
            
            if (!$approver) {
                return [
                    'success' => false,
                    'message' => 'Approver not found.'
                ];
            }
            
            // Begin transaction
            $this->db->beginTransaction();
            
// ============================================
// BUDGET ALLOCATION LOGIC - COMPLETE VERSION
// Allocate budget when:
// 1. Line Manager approves Team Member's "Budget" category requisition
// 2. Managing Director approves Line Manager's "Budget" category requisition
// 3. Finance Manager approves Managing Director's "Budget" category requisition
// ============================================
$shouldCheckBudget = false;
$budgetAllocationReason = '';

// Check if this approval should trigger budget allocation
if ((int)$approver['role_id'] === ROLE_LINE_MANAGER && !empty($requisition['category_id'])) {
    // Line Manager approving (Team Member's requisition)
    $shouldCheckBudget = true;
    $budgetAllocationReason = 'Allocated by Line Manager approval - ' . $approver['first_name'] . ' ' . $approver['last_name'];
    error_log("=== BUDGET CHECK: Line Manager approving Team Member requisition ===");
    
} elseif ((int)$approver['role_id'] === ROLE_MANAGING_DIRECTOR && !empty($requisition['category_id'])) {
    // MD approving - check if this is a Line Manager's requisition
    $requester = $this->db->fetchOne(
        "SELECT role_id FROM users WHERE id = ?",
        [$requisition['user_id']]
    );
    
    if ($requester && (int)$requester['role_id'] === ROLE_LINE_MANAGER) {
        // MD is approving a Line Manager's requisition
        $shouldCheckBudget = true;
        $budgetAllocationReason = 'Allocated by Managing Director approval - ' . $approver['first_name'] . ' ' . $approver['last_name'];
        error_log("=== BUDGET CHECK: MD approving Line Manager requisition ===");
    }
    
} elseif ((int)$approver['role_id'] === ROLE_FINANCE_MANAGER && !empty($requisition['category_id'])) {
    // Finance Manager approving - check if this is an MD's requisition
    $requester = $this->db->fetchOne(
        "SELECT role_id FROM users WHERE id = ?",
        [$requisition['user_id']]
    );
    
    if ($requester && (int)$requester['role_id'] === ROLE_MANAGING_DIRECTOR) {
        // Finance Manager is approving an MD's requisition
        $shouldCheckBudget = true;
        $budgetAllocationReason = 'Allocated by Finance Manager approval - ' . $approver['first_name'] . ' ' . $approver['last_name'];
        error_log("=== BUDGET CHECK: Finance Manager approving MD requisition ===");
    }
}

if ($shouldCheckBudget) {
    error_log("=== BUDGET CHECK START ===");
    error_log("Approver Role ID: " . $approver['role_id']);
    error_log("Requisition Category ID: " . $requisition['category_id']);
    error_log("Requisition ID: " . $requisitionId);
    error_log("Requester User ID: " . $requisition['user_id']);
    
    try {
        // Get category details to check if it affects budget
        $category = $this->db->fetchOne(
            "SELECT * FROM requisition_categories WHERE id = ?",
            [$requisition['category_id']]
        );
        
        error_log("Category Found: " . ($category ? 'YES' : 'NO'));
        if ($category) {
            error_log("Category Name: " . $category['category_name']);
            error_log("Category Name (lowercase trimmed): " . strtolower(trim($category['category_name'])));
        }
        
        // Check if this is the "Budget" category
        if ($category && strtolower(trim($category['category_name'])) === 'budget') {
            error_log("This IS a budget category requisition");
            
            // Check if Budget class exists
            if (!class_exists('Budget')) {
                error_log("ERROR: Budget class does not exist!");
                $this->db->rollback();
                return [
                    'success' => false,
                    'message' => 'System error: Budget management system not available. Please contact administrator.'
                ];
            }
            
            error_log("Budget class exists");
            
            // Try to get active budget
            try {
                $activeBudget = $this->budgetModel->getActiveBudget($requisition['department_id']);
                error_log("Active Budget: " . ($activeBudget ? 'FOUND' : 'NOT FOUND'));
                
                if ($activeBudget) {
                    error_log("Active Budget ID: " . $activeBudget['id']);
                    error_log("Budget Amount: " . $activeBudget['budget_amount']);
                    error_log("Available Amount: " . $activeBudget['available_amount']);
                    error_log("Allocated Amount: " . $activeBudget['allocated_amount']);
                }
            } catch (Exception $e) {
                error_log("ERROR getting active budget: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                $this->db->rollback();
                return [
                    'success' => false,
                    'message' => 'Budget system error: ' . $e->getMessage()
                ];
            }
            
            if (!$activeBudget) {
                error_log("No active budget - rejecting approval");
                $this->db->rollback();
                return [
                    'success' => false,
                    'message' => 'Cannot approve: No active budget set for ' . ($requisition['department_name'] ?? 'this') . ' department. Please contact Finance Manager to set budget.'
                ];
            }
            
            // Check if enough budget is available
            try {
                $hasAvailability = $this->budgetModel->checkAvailability($requisition['department_id'], $requisition['total_amount']);
                error_log("Budget availability check: " . ($hasAvailability ? 'SUFFICIENT' : 'INSUFFICIENT'));
                error_log("Required Amount: " . $requisition['total_amount']);
            } catch (Exception $e) {
                error_log("ERROR checking budget availability: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                $this->db->rollback();
                return [
                    'success' => false,
                    'message' => 'Budget check error: ' . $e->getMessage()
                ];
            }
            
            if (!$hasAvailability) {
                error_log("Insufficient budget - rejecting approval");
                $this->db->rollback();
                return [
                    'success' => false,
                    'message' => 'Cannot approve: Insufficient department budget. Available: ₦' . number_format($activeBudget['available_amount'], 2) . ', Required: ₦' . number_format($requisition['total_amount'], 2)
                ];
            }
            
            // Check for existing ACTIVE allocation to prevent double allocation
            try {
                $existingActiveAllocation = $this->db->fetchOne(
                    "SELECT * FROM budget_allocations 
                     WHERE requisition_id = ? 
                     AND allocation_type = 'allocated'
                     ORDER BY allocated_at DESC 
                     LIMIT 1",
                    [$requisitionId]
                );
                
                error_log("Existing ACTIVE allocation: " . ($existingActiveAllocation ? 'YES (ID: ' . $existingActiveAllocation['id'] . ')' : 'NO'));
                
                if ($existingActiveAllocation) {
                    error_log("Skipping allocation - budget already allocated for this requisition");
                    error_log("Existing allocation amount: " . $existingActiveAllocation['amount']);
                    error_log("=== BUDGET CHECK END (ALREADY ALLOCATED) ===");
                    // Don't allocate again - just continue with approval
                } else {
                    // No active allocation found - proceed with allocation
                    error_log("No active allocation found - proceeding with new allocation");
                    
                    // Allocate budget
                    try {
                        error_log("Attempting to allocate budget...");
                        error_log("Budget ID: " . $activeBudget['id']);
                        error_log("Requisition ID: " . $requisitionId);
                        error_log("Amount to allocate: " . $requisition['total_amount']);
                        
                        $allocationResult = $this->budgetModel->allocateBudget(
                            $activeBudget['id'],
                            $requisitionId,
                            $requisition['total_amount'],
                            $budgetAllocationReason
                        );
                        
                        error_log("Allocation result success: " . ($allocationResult['success'] ? 'YES' : 'NO'));
                        error_log("Allocation result message: " . $allocationResult['message']);
                        error_log("Full allocation result: " . json_encode($allocationResult));
                        
                        if (!$allocationResult['success']) {
                            error_log("Budget allocation failed: " . $allocationResult['message']);
                            $this->db->rollback();
                            return $allocationResult;
                        }
                        
                        error_log("Budget allocated successfully!");
                        error_log("=== BUDGET CHECK END (ALLOCATION SUCCESSFUL) ===");
                    } catch (Exception $e) {
                        error_log("ERROR allocating budget: " . $e->getMessage());
                        error_log("Stack trace: " . $e->getTraceAsString());
                        $this->db->rollback();
                        return [
                            'success' => false,
                            'message' => 'Budget allocation error: ' . $e->getMessage()
                        ];
                    }
                }
            } catch (Exception $e) {
                error_log("ERROR in allocation check: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                $this->db->rollback();
                return [
                    'success' => false,
                    'message' => 'Budget allocation check error: ' . $e->getMessage()
                ];
            }
            
        } else {
            error_log("This is NOT a budget category requisition - skipping budget check");
            error_log("=== BUDGET CHECK END (NOT BUDGET CATEGORY) ===");
        }
    } catch (Exception $e) {
        error_log("FATAL ERROR in budget check section: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $this->db->rollback();
        return [
            'success' => false,
            'message' => 'System error during budget check: ' . $e->getMessage()
        ];
    }
}
// ============================================
// END BUDGET ALLOCATION LOGIC
// ============================================
            
            // Determine next status
            $currentStatus = $requisition['status'];
            $nextStatus = $this->workflow->getNextStatus($currentStatus);
            
            // Get next approver
            $nextApprover = $this->workflow->getNextApprover($requisitionId, $nextStatus);
            $nextApproverId = $nextApprover ? $nextApprover['id'] : null;
            
            // Update requisition status
            $sql = "UPDATE requisitions 
                    SET status = ?,
                        current_approver_id = ?,
                        updated_at = NOW()
                    WHERE id = ?";
            
            $this->db->execute($sql, [$nextStatus, $nextApproverId, $requisitionId]);
            
            // Get user's role name for role_at_approval
            $roleResult = $this->db->fetchOne(
                "SELECT role_name FROM roles WHERE id = ?",
                [$approver['role_id']]
            );
            $roleName = $roleResult ? $roleResult['role_name'] : 'Unknown';
            
            // Record approval in requisition_approvals table
            $sql = "INSERT INTO requisition_approvals (
                        requisition_id,
                        user_id,
                        role_at_approval,
                        action,
                        comments,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, NOW())";
            
            $this->db->execute($sql, [
                $requisitionId,
                $approverId,
                $roleName,
                APPROVAL_APPROVED,
                $comments
            ]);
            
            // Log the approval in audit log
            $description = "Requisition approved by " . $approver['first_name'] . " " . $approver['last_name'];
            if ($comments) {
                $description .= " - Comment: " . substr($comments, 0, 100);
            }
            
            $this->auditLog->logRequisitionAction(
                $approverId,
                $requisitionId,
                AUDIT_REQUISITION_APPROVED,
                $description,
                [
                    'previous_status' => $currentStatus,
                    'new_status' => $nextStatus,
                    'comments' => $comments
                ]
            );
            
            // Commit transaction
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Requisition approved successfully.',
                'next_status' => $nextStatus,
                'next_approver' => $nextApprover
            ];
            
        } catch (Exception $e) {
            // Rollback on error
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->rollback();
            }
            
            error_log("Approval error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while approving the requisition.'
            ];
        }
    }
    
    /**
     * Reject a requisition
     * WITH BUDGET RELEASE LOGIC
     * 
     * @param int $requisitionId Requisition ID
     * @param int $rejecterId Rejecter user ID
     * @param string $reason Rejection reason (required)
     * @return array Result with success status and message
     */
    public function reject($requisitionId, $rejecterId, $reason) {
        try {
            // Validate reason
            if (empty(trim($reason))) {
                return [
                    'success' => false,
                    'message' => 'Rejection reason is required.'
                ];
            }
            
            // Verify rejecter can approve/reject this requisition
            if (!$this->workflow->canUserApprove($rejecterId, $requisitionId)) {
                return [
                    'success' => false,
                    'message' => 'You do not have permission to reject this requisition.'
                ];
            }
            
            // Get requisition details with category info
            $requisition = $this->db->fetchOne(
                "SELECT r.*, rc.category_name, rc.id as category_id
                 FROM requisitions r
                 LEFT JOIN requisition_categories rc ON r.category_id = rc.id
                 WHERE r.id = ?",
                [$requisitionId]
            );
            
            if (!$requisition) {
                return [
                    'success' => false,
                    'message' => 'Requisition not found.'
                ];
            }
            
            // Check if can be rejected
            $nonRejectableStatuses = [STATUS_PAID, STATUS_COMPLETED, STATUS_CANCELLED];
            if (in_array($requisition['status'], $nonRejectableStatuses)) {
                return [
                    'success' => false,
                    'message' => 'This requisition cannot be rejected in its current state.'
                ];
            }
            
            // Get rejecter details
            $rejecter = $this->db->fetchOne(
                "SELECT * FROM users WHERE id = ?",
                [$rejecterId]
            );
            
            if (!$rejecter) {
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // ============================================
            // BUDGET RELEASE LOGIC
            // Release budget if this was a "Budget" category requisition that had been allocated
            // ============================================
            if (!empty($requisition['category_id'])) {
                // Get category details
                $category = $this->db->fetchOne(
                    "SELECT * FROM requisition_categories WHERE id = ?",
                    [$requisition['category_id']]
                );
                
                // If this is a "Budget" category requisition
                if ($category && strtolower(trim($category['category_name'])) === 'budget') {
                    // Check if budget was allocated (Line Manager had approved)
                    $allocation = $this->budgetModel->getAllocationByRequisition($requisitionId);
                    
                    if ($allocation && $allocation['allocation_type'] === 'allocated') {
                        // Release budget back to department
                        $releaseResult = $this->budgetModel->releaseBudget(
                            $requisitionId,
                            'Released due to rejection by ' . $rejecter['first_name'] . ' ' . $rejecter['last_name']
                        );
                        
                        if (!$releaseResult['success']) {
                            $this->db->rollback();
                            return $releaseResult;
                        }
                    }
                }
            }
            // ============================================
            // END BUDGET RELEASE LOGIC
            // ============================================
            
            // Update requisition status to rejected
            $sql = "UPDATE requisitions 
                    SET status = ?,
                        current_approver_id = ?,
                        rejected_by_id = ?,
                        rejection_reason = ?,
                        updated_at = NOW()
                    WHERE id = ?";
            
            $this->db->execute($sql, [
                STATUS_REJECTED,
                $requisition['user_id'], // Return to original requester
                $rejecterId,
                $reason,
                $requisitionId
            ]);
            
            // Get user's role name for role_at_approval
            $roleResult = $this->db->fetchOne(
                "SELECT role_name FROM roles WHERE id = ?",
                [$rejecter['role_id']]
            );
            $roleName = $roleResult ? $roleResult['role_name'] : 'Unknown';
            
            // Record rejection in requisition_approvals table
            $sql = "INSERT INTO requisition_approvals (
                        requisition_id,
                        user_id,
                        role_at_approval,
                        action,
                        comments,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, NOW())";
            
            $this->db->execute($sql, [
                $requisitionId,
                $rejecterId,
                $roleName,
                APPROVAL_REJECTED,
                $reason
            ]);
            
            // Log the rejection in audit log
            $description = "Requisition rejected by " . $rejecter['first_name'] . " " . $rejecter['last_name'];
            $description .= " - Reason: " . substr($reason, 0, 100);
            
            $this->auditLog->logRequisitionAction(
                $rejecterId,
                $requisitionId,
                AUDIT_REQUISITION_REJECTED,
                $description,
                [
                    'previous_status' => $requisition['status'],
                    'new_status' => STATUS_REJECTED,
                    'rejection_reason' => $reason
                ]
            );
            
            // Commit transaction
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Requisition rejected successfully.',
                'new_status' => STATUS_REJECTED
            ];
            
        } catch (Exception $e) {
            // Rollback on error
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->rollback();
            }
            
            error_log("Rejection error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while rejecting the requisition.'
            ];
        }
    }
    
    /**
     * Get approval history for a requisition
     * 
     * @param int $requisitionId Requisition ID
     * @return array Approval history
     */
    public function getApprovalHistory($requisitionId) {
        try {
            $sql = "SELECT a.*, 
                           u.first_name, 
                           u.last_name, 
                           u.email,
                           a.role_at_approval as role_name
                    FROM requisition_approvals a
                    JOIN users u ON a.user_id = u.id
                    WHERE a.requisition_id = ?
                    ORDER BY a.created_at ASC";
            
            return $this->db->fetchAll($sql, [$requisitionId]);
            
        } catch (Exception $e) {
            error_log("Error getting approval history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get current approver for a requisition
     * 
     * @param int $requisitionId Requisition ID
     * @return array|null Current approver info or null
     */
    public function getCurrentApprover($requisitionId) {
        try {
            $sql = "SELECT u.*, r.role_name
                    FROM requisitions req
                    JOIN users u ON req.current_approver_id = u.id
                    JOIN roles r ON u.role_id = r.id
                    WHERE req.id = ?";
            
            return $this->db->fetchOne($sql, [$requisitionId]);
            
        } catch (Exception $e) {
            error_log("Error getting current approver: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if requisition is awaiting approval
     * 
     * @param int $requisitionId Requisition ID
     * @return bool True if awaiting approval
     */
    public function isAwaitingApproval($requisitionId) {
        try {
            $requisition = $this->db->fetchOne(
                "SELECT status FROM requisitions WHERE id = ?",
                [$requisitionId]
            );
            
            if (!$requisition) {
                return false;
            }
            
            $pendingStatuses = [
                STATUS_PENDING_LINE_MANAGER,
                STATUS_PENDING_MD,
                STATUS_PENDING_FINANCE_MANAGER,
                STATUS_APPROVED_FOR_PAYMENT
            ];
            
            return in_array($requisition['status'], $pendingStatuses);
            
        } catch (Exception $e) {
            error_log("Error checking approval status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get pending approvals count for a user
     * 
     * @param int $userId User ID
     * @return int Count of pending approvals
     */
    public function getPendingCount($userId) {
        try {
            $pendingRequisitions = $this->workflow->getPendingRequisitionsForUser($userId);
            return count($pendingRequisitions);
            
        } catch (Exception $e) {
            error_log("Error getting pending count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get approval statistics for a user
     * 
     * @param int $userId User ID
     * @param array $filters Optional filters (date_from, date_to)
     * @return array Statistics
     */
    public function getApprovalStatistics($userId, $filters = []) {
        try {
            // Build WHERE clause
            $where = ["user_id = ?"];
            $params = [$userId];
            
            if (!empty($filters['date_from'])) {
                $where[] = "DATE(created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where[] = "DATE(created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            // Use constants directly in SQL to avoid parameter ordering issues
            $approvedConst = APPROVAL_APPROVED;
            $rejectedConst = APPROVAL_REJECTED;
            
            $sql = "SELECT 
                        COUNT(*) as total_actions,
                        COUNT(CASE WHEN action = '{$approvedConst}' THEN 1 END) as approved_count,
                        COUNT(CASE WHEN action = '{$rejectedConst}' THEN 1 END) as rejected_count
                    FROM requisition_approvals
                    WHERE " . implode(' AND ', $where);
            
            $result = $this->db->fetchOne($sql, $params);
            
            // Ensure we return an array with default values if no results
            if (!$result) {
                return [
                    'total_actions' => 0,
                    'approved_count' => 0,
                    'rejected_count' => 0
                ];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error getting approval statistics: " . $e->getMessage());
            return [
                'total_actions' => 0,
                'approved_count' => 0,
                'rejected_count' => 0
            ];
        }
    }
    
    /**
     * Add a comment to a requisition approval stage
     * 
     * @param int $requisitionId Requisition ID
     * @param int $userId User ID adding comment
     * @param string $comment Comment text
     * @return array Result with success status
     */
    public function addComment($requisitionId, $userId, $comment) {
        try {
            if (empty(trim($comment))) {
                return [
                    'success' => false,
                    'message' => 'Comment cannot be empty.'
                ];
            }
            
            // Get user details
            $user = $this->db->fetchOne(
                "SELECT * FROM users WHERE id = ?",
                [$userId]
            );
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }
            
            // Get user's role name
            $roleResult = $this->db->fetchOne(
                "SELECT role_name FROM roles WHERE id = ?",
                [$user['role_id']]
            );
            $roleName = $roleResult ? $roleResult['role_name'] : 'Unknown';
            
            // Insert comment as a special approval record with 'comment' action
            $sql = "INSERT INTO requisition_approvals (
                        requisition_id,
                        user_id,
                        role_at_approval,
                        action,
                        comments,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, NOW())";
            
            $this->db->execute($sql, [
                $requisitionId,
                $userId,
                $roleName,
                'comment', // Special action type for comments
                $comment
            ]);
            
            // Log in audit trail
            $description = "Comment added by " . $user['first_name'] . " " . $user['last_name'];
            $this->auditLog->logRequisitionAction(
                $userId,
                $requisitionId,
                'requisition_comment_added',
                $description,
                ['comment' => substr($comment, 0, 200)]
            );
            
            return [
                'success' => true,
                'message' => 'Comment added successfully.'
            ];
            
        } catch (Exception $e) {
            error_log("Error adding comment: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while adding the comment.'
            ];
        }
    }
}
<?php
/**
 * GateWey Requisition Management System
 * Approval Class
 * 
 * File: classes/Approval.php
 * Purpose: Handle requisition approval and rejection operations
 * 
 * FIXED: 
 * - Column names corrected (approver_id → user_id, approver_role_id → role_at_approval)
 * - getApprovalStatistics() query fixed with proper parameter ordering
 */

class Approval {
    
    private $db;
    private $workflow;
    private $auditLog;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->workflow = new WorkflowEngine();
        $this->auditLog = new AuditLog();
    }
    
    /**
     * Approve a requisition
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
            
            // Get requisition details
            $requisition = $this->db->fetchOne(
                "SELECT * FROM requisitions WHERE id = ?",
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
            
            // Determine next status
            $currentStatus = $requisition['status'];
            $nextStatus = $this->workflow->getNextStatus($currentStatus);
            
            // Get next approver
            // Pass the NEXT status to get the correct approver
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
            // FIXED: Using correct column names - user_id and role_at_approval
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
            
            // Get requisition details
            $requisition = $this->db->fetchOne(
                "SELECT * FROM requisitions WHERE id = ?",
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
            // FIXED: Using correct column names - user_id and role_at_approval
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
            // FIXED: Using correct column names
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
            
            // FIXED: Use constants directly in SQL instead of as parameters
            // This avoids parameter ordering issues
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
            // FIXED: Using correct column names - user_id and role_at_approval
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
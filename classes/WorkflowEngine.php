<?php
/**
 * GateWey Requisition Management System
 * Workflow Engine Class
 * 
 * File: classes/WorkflowEngine.php
 * Purpose: Determine approval chains and route requisitions through proper workflow
 * 
 * FIXED: Type casting for database values to ensure strict comparison works correctly
 */

class WorkflowEngine {
    
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get approval chain for a requisition
     * Returns array of approver role IDs in order
     * 
     * @param int $requesterRoleId Requester's role ID
     * @return array Array of role IDs in approval order
     */
    public function getApprovalChain($requesterRoleId) {
        $chain = [];
        
        switch ($requesterRoleId) {
            case ROLE_TEAM_MEMBER:
                // Team Member → Line Manager → MD → Finance Manager → Finance Member
                $chain = [
                    ROLE_LINE_MANAGER,
                    ROLE_MANAGING_DIRECTOR,
                    ROLE_FINANCE_MANAGER,
                    ROLE_FINANCE_MEMBER
                ];
                break;
                
            case ROLE_LINE_MANAGER:
                // Line Manager → MD → Finance Manager → Finance Member
                $chain = [
                    ROLE_MANAGING_DIRECTOR,
                    ROLE_FINANCE_MANAGER,
                    ROLE_FINANCE_MEMBER
                ];
                break;
                
            case ROLE_MANAGING_DIRECTOR:
                // Managing Director → Finance Manager → Finance Member
                $chain = [
                    ROLE_FINANCE_MANAGER,
                    ROLE_FINANCE_MEMBER
                ];
                break;
                
            default:
                // Invalid role for raising requisitions
                $chain = [];
                break;
        }
        
        return $chain;
    }
    
    /**
     * Get initial status for a new requisition
     * 
     * @param int $requesterRoleId Requester's role ID
     * @return string Initial status constant
     */
    public function getInitialStatus($requesterRoleId) {
        switch ($requesterRoleId) {
            case ROLE_TEAM_MEMBER:
                return STATUS_PENDING_LINE_MANAGER;
                
            case ROLE_LINE_MANAGER:
                return STATUS_PENDING_MD;
                
            case ROLE_MANAGING_DIRECTOR:
                return STATUS_PENDING_FINANCE_MANAGER;
                
            default:
                return STATUS_DRAFT;
        }
    }
    
/**
 * Get next approver for a requisition
 * 
 * @param int $requisitionId Requisition ID
 * @param string|null $forStatus Optional status to get approver for (defaults to current status)
 * @return array|null Next approver info or null if no more approvers
 */
public function getNextApprover($requisitionId, $forStatus = null) {
    try {
        // Get requisition details
        $sql = "SELECT r.*, u.role_id as requester_role_id, u.department_id
                FROM requisitions r
                JOIN users u ON r.user_id = u.id
                WHERE r.id = ?";
        
        $requisition = $this->db->fetchOne($sql, [$requisitionId]);
        
        if (!$requisition) {
            return null;
        }
        
        // Use provided status or current status
        $statusToCheck = $forStatus ?? $requisition['status'];
        
        // Get the role needed for this status
        $nextRoleId = $this->getNextRoleFromStatus($statusToCheck);
        
        if (!$nextRoleId) {
            return null;
        }
        
        // Find the appropriate approver
        return $this->findApprover($nextRoleId, $requisition['department_id']);
        
    } catch (Exception $e) {
        error_log("Error getting next approver: " . $e->getMessage());
        return null;
    }
}
    
    /**
     * Get next role ID from current status
     * 
     * @param string $currentStatus Current requisition status
     * @param array $chain Approval chain
     * @return int|null Next role ID or null
     */
    private function getNextRoleFromStatus($currentStatus) {
        // Map status to next role in chain
        $statusToRoleMap = [
            STATUS_PENDING_LINE_MANAGER => ROLE_LINE_MANAGER,
            STATUS_PENDING_MD => ROLE_MANAGING_DIRECTOR,
            STATUS_PENDING_FINANCE_MANAGER => ROLE_FINANCE_MANAGER,
            STATUS_APPROVED_FOR_PAYMENT => ROLE_FINANCE_MEMBER
        ];
        
        return $statusToRoleMap[$currentStatus] ?? null;
    }
    
    /**
     * Find an approver by role
     * 
     * @param int $roleId Required role ID
     * @param int|null $departmentId Department ID (for line managers)
     * @return array|null Approver info or null
     */
    private function findApprover($roleId, $departmentId = null) {
        try {
            // For Line Manager, find the department's line manager
            if ($roleId === ROLE_LINE_MANAGER && $departmentId) {
                $sql = "SELECT id, first_name, last_name, email, role_id
                        FROM users
                        WHERE role_id = ? 
                          AND department_id = ? 
                          AND is_active = 1
                        LIMIT 1";
                
                $approver = $this->db->fetchOne($sql, [$roleId, $departmentId]);
                
                if ($approver) {
                    return $approver;
                }
            }
            
            // For other roles (MD, Finance Manager, Finance Member), find any active user with that role
            $sql = "SELECT id, first_name, last_name, email, role_id
                    FROM users
                    WHERE role_id = ? 
                      AND is_active = 1
                    LIMIT 1";
            
            return $this->db->fetchOne($sql, [$roleId]);
            
        } catch (Exception $e) {
            error_log("Error finding approver: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get next status after approval
     * 
     * @param string $currentStatus Current status
     * @return string Next status constant
     */
    public function getNextStatus($currentStatus) {
        $statusProgression = [
            STATUS_PENDING_LINE_MANAGER => STATUS_PENDING_MD,
            STATUS_PENDING_MD => STATUS_PENDING_FINANCE_MANAGER,
            STATUS_PENDING_FINANCE_MANAGER => STATUS_APPROVED_FOR_PAYMENT,
            STATUS_APPROVED_FOR_PAYMENT => STATUS_PAID, // After Finance Member approves
        ];
        
        return $statusProgression[$currentStatus] ?? STATUS_COMPLETED;
    }
    
    /**
     * Get previous status (for rejection flow)
     * 
     * @param string $currentStatus Current status
     * @return string Previous status constant
     */
    public function getPreviousStatus($currentStatus) {
        $reverseProgression = [
            STATUS_PENDING_MD => STATUS_PENDING_LINE_MANAGER,
            STATUS_PENDING_FINANCE_MANAGER => STATUS_PENDING_MD,
            STATUS_APPROVED_FOR_PAYMENT => STATUS_PENDING_FINANCE_MANAGER,
        ];
        
        return $reverseProgression[$currentStatus] ?? STATUS_REJECTED;
    }
    
    /**
     * Check if user can approve a requisition
     * 
     * @param int $userId User ID
     * @param int $requisitionId Requisition ID
     * @return bool True if user can approve
     */
    public function canUserApprove($userId, $requisitionId) {
        try {
            // Get requisition and user details
            $sql = "SELECT r.status, r.current_approver_id, r.department_id,
                           u.role_id, u.department_id as user_department_id
                    FROM requisitions r
                    CROSS JOIN users u
                    WHERE r.id = ? AND u.id = ?";
            
            $data = $this->db->fetchOne($sql, [$requisitionId, $userId]);
            
            if (!$data) {
                return false;
            }
            
            // ✅ FIX: Cast database values to integers for strict comparison
            $data['role_id'] = (int)$data['role_id'];
            $data['user_department_id'] = (int)$data['user_department_id'];
            $data['department_id'] = (int)$data['department_id'];
            $data['current_approver_id'] = $data['current_approver_id'] ? (int)$data['current_approver_id'] : null;
            
            // Check if requisition is in a pending status
            $pendingStatuses = [
                STATUS_PENDING_LINE_MANAGER,
                STATUS_PENDING_MD,
                STATUS_PENDING_FINANCE_MANAGER,
                STATUS_APPROVED_FOR_PAYMENT
            ];
            
            if (!in_array($data['status'], $pendingStatuses)) {
                return false;
            }
            
            // Check if user's role matches the required approver role
            $requiredRole = $this->getRequiredRoleForStatus($data['status']);
            
            if ($data['role_id'] !== $requiredRole) {
                return false;
            }
            
            // For Line Manager, check if they're in the same department
            if ($data['role_id'] === ROLE_LINE_MANAGER) {
                if ($data['user_department_id'] !== $data['department_id']) {
                    return false;
                }
            }
            
            // Additional check: user should be the current approver
            // ✅ FIX: Changed to strict comparison (!==)
            if ($data['current_approver_id'] && $data['current_approver_id'] !== $userId) {
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error checking approval permission: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get required role for a status
     * 
     * @param string $status Requisition status
     * @return int|null Required role ID
     */
    private function getRequiredRoleForStatus($status) {
        $statusRoleMap = [
            STATUS_PENDING_LINE_MANAGER => ROLE_LINE_MANAGER,
            STATUS_PENDING_MD => ROLE_MANAGING_DIRECTOR,
            STATUS_PENDING_FINANCE_MANAGER => ROLE_FINANCE_MANAGER,
            STATUS_APPROVED_FOR_PAYMENT => ROLE_FINANCE_MEMBER
        ];
        
        return $statusRoleMap[$status] ?? null;
    }
    
    /**
     * Get all pending requisitions for a user (based on their role)
     * 
     * @param int $userId User ID
     * @return array Array of pending requisitions
     */
    public function getPendingRequisitionsForUser($userId) {
        try {
            // Get user info
            $user = $this->db->fetchOne(
                "SELECT role_id, department_id FROM users WHERE id = ?", 
                [$userId]
            );
            
            if (!$user) {
                return [];
            }
            
            // ✅ FIX: Cast database values to integers for strict comparison
            $user['role_id'] = (int)$user['role_id'];
            $user['department_id'] = $user['department_id'] ? (int)$user['department_id'] : null;
            
            // Build query based on role
            $sql = "SELECT r.*, 
                           u.first_name as requester_first_name,
                           u.last_name as requester_last_name,
                           u.email as requester_email,
                           d.department_name,
                           d.department_code
                    FROM requisitions r
                    JOIN users u ON r.user_id = u.id
                    LEFT JOIN departments d ON r.department_id = d.id
                    WHERE r.status = ?";
            
            $params = [];
            
            // Determine which status to look for based on user role
            switch ($user['role_id']) {
                case ROLE_LINE_MANAGER:
                    $sql .= " AND r.department_id = ?";
                    $params = [STATUS_PENDING_LINE_MANAGER, $user['department_id']];
                    break;
                    
                case ROLE_MANAGING_DIRECTOR:
                    $params = [STATUS_PENDING_MD];
                    break;
                    
                case ROLE_FINANCE_MANAGER:
                    $params = [STATUS_PENDING_FINANCE_MANAGER];
                    break;
                    
                case ROLE_FINANCE_MEMBER:
                    $params = [STATUS_APPROVED_FOR_PAYMENT];
                    break;
                    
                default:
                    // User role cannot approve
                    return [];
            }
            
            $sql .= " ORDER BY r.submitted_at ASC, r.created_at ASC";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Error getting pending requisitions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get approval progress for a requisition
     * Returns array with all approval stages and their status
     * 
     * @param int $requisitionId Requisition ID
     * @return array Approval progress data
     */
    public function getApprovalProgress($requisitionId) {
        try {
            // Get requisition details
            $sql = "SELECT r.*, u.role_id as requester_role_id
                    FROM requisitions r
                    JOIN users u ON r.user_id = u.id
                    WHERE r.id = ?";
            
            $requisition = $this->db->fetchOne($sql, [$requisitionId]);
            
            if (!$requisition) {
                return [];
            }
            
            // Get approval chain
            $chain = $this->getApprovalChain($requisition['requester_role_id']);
            
            // Get approval history
            $approvals = $this->db->fetchAll(
                "SELECT * FROM requisition_approvals WHERE requisition_id = ? ORDER BY created_at ASC",
                [$requisitionId]
            );
            
            // Build progress array
            $progress = [];
            $currentStatus = $requisition['status'];
            
            // Role to status mapping
            $roleStatusMap = [
                ROLE_LINE_MANAGER => STATUS_PENDING_LINE_MANAGER,
                ROLE_MANAGING_DIRECTOR => STATUS_PENDING_MD,
                ROLE_FINANCE_MANAGER => STATUS_PENDING_FINANCE_MANAGER,
                ROLE_FINANCE_MEMBER => STATUS_APPROVED_FOR_PAYMENT
            ];
            
            foreach ($chain as $roleId) {
                $status = $roleStatusMap[$roleId] ?? null;
                
                // Find approval record for this role
                $approval = null;
                foreach ($approvals as $app) {
                    if ($app['approver_role_id'] == $roleId) {
                        $approval = $app;
                        break;
                    }
                }
                
                $progress[] = [
                    'role_id' => $roleId,
                    'role_name' => get_role_name($roleId),
                    'status' => $status,
                    'is_current' => ($currentStatus === $status),
                    'is_completed' => ($approval && $approval['action'] === APPROVAL_APPROVED),
                    'is_rejected' => ($approval && $approval['action'] === APPROVAL_REJECTED),
                    'approval_data' => $approval
                ];
            }
            
            return $progress;
            
        } catch (Exception $e) {
            error_log("Error getting approval progress: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate workflow transition
     * 
     * @param string $fromStatus Current status
     * @param string $toStatus New status
     * @return bool True if transition is valid
     */
    public function isValidTransition($fromStatus, $toStatus) {
        $validTransitions = [
            STATUS_DRAFT => [STATUS_PENDING_LINE_MANAGER, STATUS_PENDING_MD, STATUS_PENDING_FINANCE_MANAGER],
            STATUS_PENDING_LINE_MANAGER => [STATUS_PENDING_MD, STATUS_REJECTED],
            STATUS_PENDING_MD => [STATUS_PENDING_FINANCE_MANAGER, STATUS_REJECTED, STATUS_PENDING_LINE_MANAGER],
            STATUS_PENDING_FINANCE_MANAGER => [STATUS_APPROVED_FOR_PAYMENT, STATUS_REJECTED, STATUS_PENDING_MD],
            STATUS_APPROVED_FOR_PAYMENT => [STATUS_PAID, STATUS_REJECTED, STATUS_PENDING_FINANCE_MANAGER],
            STATUS_PAID => [STATUS_COMPLETED],
            STATUS_REJECTED => [STATUS_PENDING_LINE_MANAGER, STATUS_PENDING_MD, STATUS_PENDING_FINANCE_MANAGER, STATUS_CANCELLED],
            STATUS_COMPLETED => [] // Final state
        ];
        
        if (!isset($validTransitions[$fromStatus])) {
            return false;
        }
        
        return in_array($toStatus, $validTransitions[$fromStatus]);
    }
}
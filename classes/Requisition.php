<?php

/**
 * GateWey Requisition Management System
 * Requisition Class - UPDATED WITH EMAIL NOTIFICATION ON RESUBMIT
 * 
 * File: classes/Requisition.php
 * Purpose: Handle all requisition-related operations including CRUD, workflow, and validation
 * 
 * UPDATES:
 * - Added email notification when rejected requisition is resubmitted
 */

class Requisition
{

    private $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new requisition
     * 
     * @param array $data Requisition data
     * @return array Result with success status and requisition ID
     */
    public function create($data)
    {
        try {
            $this->db->beginTransaction();

            // Generate requisition number
            $requisitionNumber = $this->generateRequisitionNumber();

            // Determine initial status based on requester role
            $requesterRoleId = Session::getUserRoleId();
            $status = $data['is_draft'] ? STATUS_DRAFT : get_initial_requisition_status($requesterRoleId);

            // Determine current approver
            $currentApproverId = null;
            if (!$data['is_draft']) {
                $currentApproverId = $this->determineNextApprover($requesterRoleId, Session::getUserDepartmentId());
            }

// Insert requisition
$sql = "INSERT INTO requisitions (
            requisition_number, user_id, department_id, purpose, description,
            category_id, total_amount, status, current_approver_id, is_draft,
            created_at, submitted_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

$params = [
    $requisitionNumber,
    Session::getUserId(),
    Session::getUserDepartmentId(),
    $data['purpose'],
    $data['description'] ?? null,
    $data['category_id'] ?? null,
    $data['total_amount'],
    $status,
    $currentApproverId,
    $data['is_draft'] ? 1 : 0,
    $data['is_draft'] ? null : date('Y-m-d H:i:s')
];

            $this->db->execute($sql, $params);
            $requisitionId = $this->db->lastInsertId();

            // Insert requisition items
            if (!empty($data['items'])) {
                $this->saveItems($requisitionId, $data['items']);
            }

            // Log action
            if (ENABLE_AUDIT_LOG) {
                $this->logAction(
                    $requisitionId,
                    $data['is_draft'] ? AUDIT_REQUISITION_CREATED : AUDIT_REQUISITION_SUBMITTED,
                    $data['is_draft'] ? "Requisition saved as draft" : "Requisition submitted for approval"
                );
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => $data['is_draft'] ? 'Requisition saved as draft.' : 'Requisition submitted successfully.',
                'requisition_id' => $requisitionId,
                'requisition_number' => $requisitionNumber
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Requisition creation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create requisition. Please try again.'
            ];
        }
    }

    /**
     * Update an existing requisition (only when rejected or draft)
     * 
     * @param int $requisitionId Requisition ID
     * @param array $data Requisition data
     * @return array Result with success status
     */
    public function update($requisitionId, $data)
    {
        try {
            // Verify user can edit this requisition
            $requisition = $this->getById($requisitionId);

            if (!$requisition) {
                return ['success' => false, 'message' => 'Requisition not found.'];
            }

            if (!can_user_edit_requisition($requisition)) {
                return ['success' => false, 'message' => 'You cannot edit this requisition.'];
            }

            $this->db->beginTransaction();

            // Determine new status if submitting (not saving as draft)
            $newStatus = $data['is_draft'] ? STATUS_DRAFT : get_initial_requisition_status(Session::getUserRoleId());
            $currentApproverId = null;

            if (!$data['is_draft']) {
                $currentApproverId = $this->determineNextApprover(
                    Session::getUserRoleId(),
                    Session::getUserDepartmentId()
                );
            }

// Update requisition
$sql = "UPDATE requisitions 
        SET purpose = ?, 
            description = ?,
            category_id = ?,
            total_amount = ?, 
            status = ?,
            current_approver_id = ?,
            is_draft = ?,
            rejection_reason = NULL,
            rejected_by_id = NULL,
            rejected_at = NULL,
            submitted_at = ?,
            updated_at = NOW()
        WHERE id = ?";

$params = [
    $data['purpose'],
    $data['description'] ?? null,
    $data['category_id'] ?? null,
    $data['total_amount'],
    $newStatus,
    $currentApproverId,
    $data['is_draft'] ? 1 : 0,
    $data['is_draft'] ? null : date('Y-m-d H:i:s'),
    $requisitionId
];

            $this->db->execute($sql, $params);

            // Delete existing items and insert new ones
            $this->deleteItems($requisitionId);

            if (!empty($data['items'])) {
                $this->saveItems($requisitionId, $data['items']);
            }

            // Log action
            if (ENABLE_AUDIT_LOG) {
                $this->logAction(
                    $requisitionId,
                    AUDIT_REQUISITION_UPDATED,
                    $data['is_draft'] ? "Requisition updated and saved as draft" : "Requisition resubmitted after revision"
                );
            }

            $this->db->commit();

            // ✅ Send email notification when resubmitting (not draft)
            if (!$data['is_draft']) {
                try {
                    Notification::send(NOTIF_REQUISITION_SUBMITTED, $requisitionId);
                } catch (Exception $e) {
                    error_log("Resubmission email notification failed: " . $e->getMessage());
                    // Don't block the resubmit if email fails
                }
            }

            return [
                'success' => true,
                'message' => $data['is_draft'] ? 'Requisition updated and saved as draft.' : 'Requisition resubmitted successfully.'
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Requisition update error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update requisition. Please try again.'
            ];
        }
    }

    /**
     * Get requisition by ID
     * 
     * @param int $requisitionId Requisition ID
     * @return array|null Requisition data or null
     */
    public function getById($requisitionId)
    {
        try {
            $sql = "SELECT r.*, 
                           u.first_name, u.last_name, u.email as requester_email,
                           d.department_name, d.department_code,
                           approver.first_name as approver_first_name,
                           approver.last_name as approver_last_name,
                           rejected_by.first_name as rejected_by_first_name,
                           rejected_by.last_name as rejected_by_last_name,
                           payer.first_name as paid_by_first_name,
                           payer.last_name  as paid_by_last_name
                    FROM requisitions r
                    INNER JOIN users u ON r.user_id = u.id
                    INNER JOIN departments d ON r.department_id = d.id
                    LEFT JOIN users approver ON r.current_approver_id = approver.id
                    LEFT JOIN users rejected_by ON r.rejected_by_id = rejected_by.id
                    LEFT JOIN users payer ON r.paid_by = payer.id
                    WHERE r.id = ?";

            $requisition = $this->db->fetchOne($sql, [$requisitionId]);

            if ($requisition) {
                // Get items
                $requisition['items'] = $this->getItems($requisitionId);

                // Get approval history
                $requisition['approvals'] = $this->getApprovalHistory($requisitionId);

                // Get documents
                $requisition['documents'] = $this->getDocuments($requisitionId);
            }

            return $requisition;
        } catch (Exception $e) {
            error_log("Get requisition error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get requisitions for current user based on their role
     * 
     * @param array $filters Filter options (status, date_from, date_to, search)
     * @param int $page Page number for pagination
     * @param int $perPage Records per page
     * @return array Requisitions and pagination data
     */
    public function getForUser($filters = [], $page = 1, $perPage = 15)
    {
        try {
            $userId = Session::getUserId();
            $roleId = Session::getUserRoleId();
            $departmentId = Session::getUserDepartmentId();

            $whereClauses = [];
            $params = [];

            // Role-based filtering
            if (in_array($roleId, CAN_VIEW_ALL)) {
                // Super Admin, MD, Finance can view all
                // No additional filtering needed
            } elseif ($roleId == ROLE_LINE_MANAGER) {
                // Line managers see their department's requisitions
                $whereClauses[] = "(r.department_id = ? OR r.user_id = ?)";
                $params[] = $departmentId;
                $params[] = $userId;
            } else {
                // Team members see only their own
                $whereClauses[] = "r.user_id = ?";
                $params[] = $userId;
            }

            // Status filter
            if (!empty($filters['status'])) {
                $whereClauses[] = "r.status = ?";
                $params[] = $filters['status'];
            }

            // Date filter
            if (!empty($filters['date_from'])) {
                $whereClauses[] = "DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $whereClauses[] = "DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }

            // Search filter
            if (!empty($filters['search'])) {
                $whereClauses[] = "(r.requisition_number LIKE ? OR r.purpose LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $whereSQL = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

            // Get total count
            $countSql = "SELECT COUNT(*) as total 
                         FROM requisitions r
                         INNER JOIN users u ON r.user_id = u.id
                         $whereSQL";

            $countResult = $this->db->fetchOne($countSql, $params);
            $totalRecords = $countResult['total'];
            $totalPages = ceil($totalRecords / $perPage);

            // Get requisitions
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT r.id, r.requisition_number, r.purpose, r.total_amount, 
                           r.status, r.is_draft, r.created_at, r.submitted_at,
                           CONCAT(u.first_name, ' ', u.last_name) as requester_name,
                           d.department_name
                    FROM requisitions r
                    INNER JOIN users u ON r.user_id = u.id
                    INNER JOIN departments d ON r.department_id = d.id
                    $whereSQL
                    ORDER BY r.created_at DESC
                    LIMIT ? OFFSET ?";

            $params[] = $perPage;
            $params[] = $offset;

            $requisitions = $this->db->fetchAll($sql, $params);

            return [
                'success' => true,
                'requisitions' => $requisitions,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_records' => $totalRecords,
                    'per_page' => $perPage
                ]
            ];
        } catch (Exception $e) {
            error_log("Get requisitions error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve requisitions.',
                'requisitions' => [],
                'pagination' => []
            ];
        }
    }
/**
 * Get pending requisitions for a specific approver based on their role
 * 
 * @param int $userId User ID of the approver
 * @return array List of pending requisitions
 */
public function getPendingForApprover($userId)
{
    try {
        // Get user information
        $sql = "SELECT role_id, department_id FROM users WHERE id = ?";
        $user = $this->db->fetchOne($sql, [$userId]);
        
        if (!$user) {
            return [];
        }
        
        $roleId = $user['role_id'];
        $departmentId = $user['department_id'];
        
        // Build query based on role
        $baseSql = "SELECT r.*, 
                           CONCAT(u.first_name, ' ', u.last_name) as requester_name,
                           u.email as requester_email,
                           d.department_name
                    FROM requisitions r
                    INNER JOIN users u ON r.user_id = u.id
                    INNER JOIN departments d ON r.department_id = d.id
                    WHERE ";
        
        $params = [];
        
        switch ($roleId) {
            case ROLE_LINE_MANAGER:
                // Line managers see requisitions from their department team members
                if (!$departmentId) {
                    return [];
                }
                $baseSql .= "r.status = ? 
                             AND r.department_id = ? 
                             AND r.user_id != ?";
                $params = [STATUS_PENDING_LINE_MANAGER, $departmentId, $userId];
                break;
                
            case ROLE_MANAGING_DIRECTOR:
                // MD sees all requisitions pending MD approval
                $baseSql .= "r.status = ?";
                $params = [STATUS_PENDING_MD];
                break;
                
            case ROLE_FINANCE_MANAGER:
                // Finance Manager sees all requisitions pending finance manager approval
                $baseSql .= "r.status = ?";
                $params = [STATUS_PENDING_FINANCE_MANAGER];
                break;
                
            default:
                // Other roles don't have pending approvals to review
                return [];
        }
        
        $baseSql .= " ORDER BY r.created_at DESC";
        
        return $this->db->fetchAll($baseSql, $params);
        
    } catch (Exception $e) {
        error_log("Error in getPendingForApprover: " . $e->getMessage());
        return [];
    }
}
    /**
     * Cancel a requisition (only allowed when rejected or draft)
     * 
     * @param int $requisitionId Requisition ID
     * @return array Result with success status
     */
    public function cancel($requisitionId)
    {
        try {
            $requisition = $this->getById($requisitionId);

            if (!$requisition) {
                return ['success' => false, 'message' => 'Requisition not found.'];
            }

            if (!can_user_cancel_requisition($requisition)) {
                return ['success' => false, 'message' => 'You cannot cancel this requisition.'];
            }

            $sql = "UPDATE requisitions 
                    SET status = ?, updated_at = NOW()
                    WHERE id = ?";

            $this->db->execute($sql, [STATUS_CANCELLED, $requisitionId]);

            // Log action
            if (ENABLE_AUDIT_LOG) {
                $this->logAction($requisitionId, AUDIT_REQUISITION_CANCELLED, "Requisition cancelled by requester");
            }

            return [
                'success' => true,
                'message' => 'Requisition cancelled successfully.'
            ];
        } catch (Exception $e) {
            error_log("Requisition cancellation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to cancel requisition.'
            ];
        }
    }

    /**
     * Get requisition items
     * 
     * @param int $requisitionId Requisition ID
     * @return array Items array
     */
    public function getItems($requisitionId)
    {
        try {
            $sql = "SELECT * FROM requisition_items 
                    WHERE requisition_id = ? 
                    ORDER BY id ASC";

            return $this->db->fetchAll($sql, [$requisitionId]);
        } catch (Exception $e) {
            error_log("Get items error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Save requisition items
     * 
     * @param int $requisitionId Requisition ID
     * @param array $items Items array
     */
    private function saveItems($requisitionId, $items)
    {
        $sql = "INSERT INTO requisition_items 
                (requisition_id, item_description, quantity, unit_price, subtotal)
                VALUES (?, ?, ?, ?, ?)";

        foreach ($items as $item) {
            $params = [
                $requisitionId,
                $item['description'],
                $item['quantity'],
                $item['unit_price'],
                $item['subtotal']
            ];

            $this->db->execute($sql, $params);
        }
    }

    /**
     * Delete requisition items
     * 
     * @param int $requisitionId Requisition ID
     */
    private function deleteItems($requisitionId)
    {
        $sql = "DELETE FROM requisition_items WHERE requisition_id = ?";
        $this->db->execute($sql, [$requisitionId]);
    }

    /**
     * Get approval history for requisition
     * 
     * @param int $requisitionId Requisition ID
     * @return array Approval history
     */
    public function getApprovalHistory($requisitionId)
    {
        try {
            $sql = "SELECT ra.*, 
                           CONCAT(u.first_name, ' ', u.last_name) as approver_name,
                           r.role_name
                    FROM requisition_approvals ra
                    INNER JOIN users u ON ra.user_id = u.id
                    INNER JOIN roles r ON u.role_id = r.id
                    WHERE ra.requisition_id = ?
                    ORDER BY ra.created_at ASC";

            return $this->db->fetchAll($sql, [$requisitionId]);
        } catch (Exception $e) {
            error_log("Get approval history error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get documents for requisition
     * 
     * @param int $requisitionId Requisition ID
     * @return array Documents array
     */
    public function getDocuments($requisitionId)
    {
        try {
            $sql = "SELECT rd.*, 
                           CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name
                    FROM requisition_documents rd
                    INNER JOIN users u ON rd.uploaded_by = u.id
                    WHERE rd.requisition_id = ?
                    ORDER BY rd.uploaded_at DESC";

            return $this->db->fetchAll($sql, [$requisitionId]);
        } catch (Exception $e) {
            error_log("Get documents error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate unique requisition number
     * 
     * @return string Requisition number
     */
    private function generateRequisitionNumber()
    {
        // Get the last requisition number
        $sql = "SELECT MAX(id) as last_id FROM requisitions";
        $result = $this->db->fetchOne($sql);

        $nextId = ($result['last_id'] ?? 0) + 1;

        return generate_requisition_number($nextId);
    }

    /**
     * Determine next approver based on requester role and department
     * 
     * @param int $requesterRoleId Requester's role ID
     * @param int $departmentId Department ID
     * @return int|null Next approver user ID
     */
    private function determineNextApprover($requesterRoleId, $departmentId)
    {
        try {
            $nextApproverRoleId = get_next_approver_role($requesterRoleId);

            if (!$nextApproverRoleId) {
                return null;
            }

            // For Line Manager approval, find the line manager of the department
            if ($nextApproverRoleId == ROLE_LINE_MANAGER) {
                $sql = "SELECT id FROM users 
                        WHERE role_id = ? AND department_id = ? AND is_active = 1 
                        LIMIT 1";

                $approver = $this->db->fetchOne($sql, [$nextApproverRoleId, $departmentId]);

                return $approver['id'] ?? null;
            }

            // For MD, Finance Manager, Finance Member - find any active user with that role
            $sql = "SELECT id FROM users 
                    WHERE role_id = ? AND is_active = 1 
                    LIMIT 1";

            $approver = $this->db->fetchOne($sql, [$nextApproverRoleId]);

            return $approver['id'] ?? null;
        } catch (Exception $e) {
            error_log("Determine next approver error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Log action to audit trail
     * 
     * @param int $requisitionId Requisition ID
     * @param string $action Action constant
     * @param string $description Action description
     */
    private function logAction($requisitionId, $action, $description)
    {
        try {
            $sql = "INSERT INTO audit_log 
                    (requisition_id, user_id, action, description, ip_address, user_agent, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";

            $params = [
                $requisitionId,
                Session::getUserId(),
                $action,
                $description,
                LOG_IP_ADDRESS ? ($_SERVER['REMOTE_ADDR'] ?? null) : null,
                LOG_USER_AGENT ? ($_SERVER['HTTP_USER_AGENT'] ?? null) : null
            ];

            $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
        }
    }

/**
 * Add this method to the Requisition class if it doesn't exist
 * Location: classes/Requisition.php
 * 
 * This method counts total requisitions for a specific user
 */

/**
 * Count requisitions by user ID
 * 
 * @param int $userId User ID
 * @param array $filters Optional filters (status, etc.)
 * @return int Count of requisitions
 */
public function countByUser($userId, $filters = [])
{
    try {
        $whereClauses = ["r.user_id = ?"];
        $params = [$userId];
        
        // Optional status filter
        if (!empty($filters['status'])) {
            $whereClauses[] = "r.status = ?";
            $params[] = $filters['status'];
        }
        
        // Don't count drafts unless specifically requested
        if (!isset($filters['include_drafts']) || !$filters['include_drafts']) {
            $whereClauses[] = "r.is_draft = 0";
        }
        
        $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
        
        $sql = "SELECT COUNT(*) as total 
                FROM requisitions r
                $whereSQL";
        
        $result = $this->db->fetchOne($sql, $params);
        
        return (int)($result['total'] ?? 0);
        
    } catch (Exception $e) {
        error_log("Count requisitions by user error: " . $e->getMessage());
        return 0;
    }
}
    /**
     * Get statistics for dashboard
     * 
     * @return array Statistics data
     */
    public function getStatistics()
    {
        try {
            $userId = Session::getUserId();
            $roleId = Session::getUserRoleId();
            $departmentId = Session::getUserDepartmentId();

            $stats = [];

            // Build WHERE clause based on role
            $whereClause = "";
            $params = [];

            if (!in_array($roleId, CAN_VIEW_ALL)) {
                if ($roleId == ROLE_LINE_MANAGER) {
                    $whereClause = "WHERE (user_id = ? OR department_id = ?)";
                    $params = [$userId, $departmentId];
                } else {
                    $whereClause = "WHERE user_id = ?";
                    $params = [$userId];
                }
            }

            // Total requisitions
            $sql = "SELECT COUNT(*) as total FROM requisitions $whereClause";
            $result = $this->db->fetchOne($sql, $params);
            $stats['total'] = $result['total'];

            // Pending requisitions
            $sql = "SELECT COUNT(*) as total FROM requisitions 
                    $whereClause " . ($whereClause ? "AND" : "WHERE") . " status IN (?, ?, ?)";
            $pendingParams = array_merge($params, [STATUS_PENDING_LINE_MANAGER, STATUS_PENDING_MD, STATUS_PENDING_FINANCE_MANAGER]);
            $result = $this->db->fetchOne($sql, $pendingParams);
            $stats['pending'] = $result['total'];

            // Approved requisitions
            $sql = "SELECT COUNT(*) as total FROM requisitions 
                    $whereClause " . ($whereClause ? "AND" : "WHERE") . " status IN (?, ?, ?)";
            $approvedParams = array_merge($params, [STATUS_APPROVED_FOR_PAYMENT, STATUS_PAID, STATUS_COMPLETED]);
            $result = $this->db->fetchOne($sql, $approvedParams);
            $stats['approved'] = $result['total'];

            // Rejected requisitions
            $sql = "SELECT COUNT(*) as total FROM requisitions 
                    $whereClause " . ($whereClause ? "AND" : "WHERE") . " status = ?";
            $rejectedParams = array_merge($params, [STATUS_REJECTED]);
            $result = $this->db->fetchOne($sql, $rejectedParams);
            $stats['rejected'] = $result['total'];

            // Total amount (paid)
            $sql = "SELECT COALESCE(SUM(total_amount), 0) as total FROM requisitions 
                    $whereClause " . ($whereClause ? "AND" : "WHERE") . " status = ?";
            $paidParams = array_merge($params, [STATUS_PAID]);
            $result = $this->db->fetchOne($sql, $paidParams);
            $stats['total_spent'] = $result['total'];

            return $stats;
        } catch (Exception $e) {
            error_log("Get statistics error: " . $e->getMessage());
            return [];
        }
    }
}
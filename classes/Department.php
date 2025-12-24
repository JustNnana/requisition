<?php
/**
 * GateWey Requisition Management System
 * Department Management Class
 * 
 * File: classes/Department.php
 * Purpose: Handle department CRUD operations and related queries
 */

class Department {
    
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new department
     * 
     * @param array $data Department data
     * @return array Result with success status and department_id or error message
     */
    public function create($data) {
        try {
            // Validate required fields
            if (empty($data['department_name']) || empty($data['department_code'])) {
                return ['success' => false, 'message' => 'Department name and code are required.'];
            }
            
            // Check if department code already exists
            if ($this->codeExists($data['department_code'])) {
                return ['success' => false, 'message' => 'Department code already exists.'];
            }
            
            // Check if department name already exists
            if ($this->nameExists($data['department_name'])) {
                return ['success' => false, 'message' => 'Department name already exists.'];
            }
            
            // Prepare SQL
            $sql = "INSERT INTO departments (
                        department_name, department_code, description, is_active
                    ) VALUES (?, ?, ?, ?)";
            
            $params = [
                $data['department_name'],
                strtoupper($data['department_code']),
                $data['description'] ?? null,
                $data['is_active'] ?? 1
            ];
            
            $this->db->execute($sql, $params);
            $departmentId = $this->db->lastInsertId();
            
            // Log department creation
            if (ENABLE_AUDIT_LOG && isset($_SESSION['user_id'])) {
                $this->logAction($departmentId, AUDIT_DEPARTMENT_CREATED, "Department created: {$data['department_name']}");
            }
            
            return [
                'success' => true,
                'message' => 'Department created successfully.',
                'department_id' => $departmentId
            ];
            
        } catch (Exception $e) {
            error_log("Department creation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while creating the department.'];
        }
    }
    
    /**
     * Get department by ID
     * 
     * @param int $departmentId Department ID
     * @return array|false Department data or false if not found
     */
    public function getById($departmentId) {
        $sql = "SELECT d.*, 
                       (SELECT COUNT(*) FROM users WHERE department_id = d.id) as user_count,
                       (SELECT COUNT(*) FROM requisitions WHERE department_id = d.id) as requisition_count
                FROM departments d
                WHERE d.id = ?";
        
        return $this->db->fetchOne($sql, [$departmentId]);
    }
    
    /**
     * Get all departments
     * 
     * @param bool $activeOnly Whether to return only active departments
     * @return array Array of departments
     */
    public function getAll($activeOnly = false) {
        $sql = "SELECT d.*, 
                       (SELECT COUNT(*) FROM users WHERE department_id = d.id) as user_count,
                       (SELECT COUNT(*) FROM requisitions WHERE department_id = d.id) as requisition_count
                FROM departments d";
        
        if ($activeOnly) {
            $sql .= " WHERE d.is_active = 1";
        }
        
        $sql .= " ORDER BY d.department_name";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Update department
     * 
     * @param int $departmentId Department ID
     * @param array $data Updated department data
     * @return array Result with success status
     */
    public function update($departmentId, $data) {
        try {
            // Validate required fields
            if (empty($data['department_name']) || empty($data['department_code'])) {
                return ['success' => false, 'message' => 'Department name and code are required.'];
            }
            
            // Check if department code already exists (excluding current department)
            if ($this->codeExists($data['department_code'], $departmentId)) {
                return ['success' => false, 'message' => 'Department code already exists.'];
            }
            
            // Check if department name already exists (excluding current department)
            if ($this->nameExists($data['department_name'], $departmentId)) {
                return ['success' => false, 'message' => 'Department name already exists.'];
            }
            
            // Prepare SQL
            $sql = "UPDATE departments SET 
                        department_name = ?, 
                        department_code = ?, 
                        description = ?,
                        is_active = ?
                    WHERE id = ?";
            
            $params = [
                $data['department_name'],
                strtoupper($data['department_code']),
                $data['description'] ?? null,
                $data['is_active'] ?? 1,
                $departmentId
            ];
            
            $this->db->execute($sql, $params);
            
            // Log department update
            if (ENABLE_AUDIT_LOG && isset($_SESSION['user_id'])) {
                $this->logAction($departmentId, AUDIT_DEPARTMENT_UPDATED, "Department updated: {$data['department_name']}");
            }
            
            return [
                'success' => true,
                'message' => 'Department updated successfully.'
            ];
            
        } catch (Exception $e) {
            error_log("Department update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating the department.'];
        }
    }
    
    /**
     * Delete department
     * Note: This will CASCADE delete all related users and requisitions
     * 
     * @param int $departmentId Department ID
     * @return array Result with success status
     */
    public function delete($departmentId) {
        try {
            // Get department info for logging
            $department = $this->getById($departmentId);
            if (!$department) {
                return ['success' => false, 'message' => 'Department not found.'];
            }
            
            // Check if department has users or requisitions
            if ($department['user_count'] > 0 || $department['requisition_count'] > 0) {
                return [
                    'success' => false, 
                    'message' => 'Cannot delete department. It has ' . $department['user_count'] . ' user(s) and ' . $department['requisition_count'] . ' requisition(s). Please reassign or delete them first.'
                ];
            }
            
            // Delete department
            $sql = "DELETE FROM departments WHERE id = ?";
            $this->db->execute($sql, [$departmentId]);
            
            // Log department deletion
            if (ENABLE_AUDIT_LOG && isset($_SESSION['user_id'])) {
                $this->logAction($departmentId, AUDIT_DEPARTMENT_DELETED, "Department deleted: {$department['department_name']}");
            }
            
            return [
                'success' => true,
                'message' => 'Department deleted successfully.'
            ];
            
        } catch (Exception $e) {
            error_log("Department deletion error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting the department.'];
        }
    }
    
    /**
     * Activate department
     * 
     * @param int $departmentId Department ID
     * @return array Result with success status
     */
    public function activate($departmentId) {
        try {
            $sql = "UPDATE departments SET is_active = 1 WHERE id = ?";
            $this->db->execute($sql, [$departmentId]);
            
            return ['success' => true, 'message' => 'Department activated successfully.'];
            
        } catch (Exception $e) {
            error_log("Department activation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while activating department.'];
        }
    }
    
    /**
     * Deactivate department
     * 
     * @param int $departmentId Department ID
     * @return array Result with success status
     */
    public function deactivate($departmentId) {
        try {
            $sql = "UPDATE departments SET is_active = 0 WHERE id = ?";
            $this->db->execute($sql, [$departmentId]);
            
            return ['success' => true, 'message' => 'Department deactivated successfully.'];
            
        } catch (Exception $e) {
            error_log("Department deactivation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deactivating department.'];
        }
    }
    
    /**
     * Check if department code exists
     * 
     * @param string $code Department code
     * @param int $excludeDepartmentId Department ID to exclude from check (for updates)
     * @return bool
     */
    public function codeExists($code, $excludeDepartmentId = null) {
        $sql = "SELECT COUNT(*) as count FROM departments WHERE department_code = ?";
        $params = [strtoupper($code)];
        
        if ($excludeDepartmentId) {
            $sql .= " AND id != ?";
            $params[] = $excludeDepartmentId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Check if department name exists
     * 
     * @param string $name Department name
     * @param int $excludeDepartmentId Department ID to exclude from check (for updates)
     * @return bool
     */
    public function nameExists($name, $excludeDepartmentId = null) {
        $sql = "SELECT COUNT(*) as count FROM departments WHERE department_name = ?";
        $params = [$name];
        
        if ($excludeDepartmentId) {
            $sql .= " AND id != ?";
            $params[] = $excludeDepartmentId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Get department users
     * 
     * @param int $departmentId Department ID
     * @return array Array of users
     */
    public function getUsers($departmentId) {
        $sql = "SELECT u.*, r.role_name
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.department_id = ?
                ORDER BY u.first_name, u.last_name";
        
        return $this->db->fetchAll($sql, [$departmentId]);
    }
    
    /**
     * Get department statistics
     * 
     * @param int $departmentId Department ID
     * @return array Statistics
     */
    public function getStatistics($departmentId) {
        $stats = [];
        
        // Total users
        $sql = "SELECT COUNT(*) as total FROM users WHERE department_id = ?";
        $result = $this->db->fetchOne($sql, [$departmentId]);
        $stats['total_users'] = $result['total'];
        
        // Active users
        $sql = "SELECT COUNT(*) as total FROM users WHERE department_id = ? AND is_active = 1";
        $result = $this->db->fetchOne($sql, [$departmentId]);
        $stats['active_users'] = $result['total'];
        
        // Total requisitions
        $sql = "SELECT COUNT(*) as total FROM requisitions WHERE department_id = ?";
        $result = $this->db->fetchOne($sql, [$departmentId]);
        $stats['total_requisitions'] = $result['total'];
        
        // Pending requisitions
        $sql = "SELECT COUNT(*) as total FROM requisitions 
                WHERE department_id = ? 
                AND status IN (?, ?, ?)";
        $result = $this->db->fetchOne($sql, [
            $departmentId,
            STATUS_PENDING_LINE_MANAGER,
            STATUS_PENDING_MD,
            STATUS_PENDING_FINANCE_MANAGER
        ]);
        $stats['pending_requisitions'] = $result['total'];
        
        // Total spending this month
        $sql = "SELECT COALESCE(SUM(total_amount), 0) as total 
                FROM requisitions 
                WHERE department_id = ? 
                AND status = ?
                AND MONTH(created_at) = MONTH(CURRENT_DATE())
                AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $result = $this->db->fetchOne($sql, [$departmentId, STATUS_PAID]);
        $stats['monthly_spending'] = $result['total'];
        
        // Total spending this year
        $sql = "SELECT COALESCE(SUM(total_amount), 0) as total 
                FROM requisitions 
                WHERE department_id = ? 
                AND status = ?
                AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $result = $this->db->fetchOne($sql, [$departmentId, STATUS_PAID]);
        $stats['yearly_spending'] = $result['total'];
        
        return $stats;
    }
    
    /**
     * Search departments
     * 
     * @param string $searchTerm Search term
     * @return array Array of departments
     */
    public function search($searchTerm) {
        $sql = "SELECT d.*, 
                       (SELECT COUNT(*) FROM users WHERE department_id = d.id) as user_count,
                       (SELECT COUNT(*) FROM requisitions WHERE department_id = d.id) as requisition_count
                FROM departments d
                WHERE d.department_name LIKE ? 
                   OR d.department_code LIKE ?
                   OR d.description LIKE ?
                ORDER BY d.department_name";
        
        $searchParam = '%' . $searchTerm . '%';
        return $this->db->fetchAll($sql, [$searchParam, $searchParam, $searchParam]);
    }
    
    /**
     * Get Executive department ID
     *
     * @return int|false Executive department ID or false if not found
     */
    public function getExecutiveDepartmentId() {
        $sql = "SELECT id FROM departments
                WHERE UPPER(department_name) LIKE '%EXECUTIVE%'
                   OR UPPER(department_code) = 'EXEC'
                   OR UPPER(department_code) = 'EXECUTIVE'
                LIMIT 1";

        $result = $this->db->fetchOne($sql);
        return $result ? (int)$result['id'] : false;
    }

    /**
     * Get users in Executive department for approver dropdown
     *
     * @return array Array of users in Executive department
     */
    public function getExecutiveDepartmentUsers() {
        $executiveDeptId = $this->getExecutiveDepartmentId();

        if (!$executiveDeptId) {
            return [];
        }

        $sql = "SELECT u.id, u.first_name, u.last_name, u.email, r.role_name
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.department_id = ?
                  AND u.is_active = 1
                ORDER BY u.first_name, u.last_name";

        return $this->db->fetchAll($sql, [$executiveDeptId]);
    }

    /**
     * Log action to audit log
     *
     * @param int $departmentId Department ID
     * @param string $action Action performed
     * @param string $details Action details
     */
    private function logAction($departmentId, $action, $details) {
        try {
            // Fixed: Use correct column names (action, description) instead of (action_type, table_name, record_id, details)
            $sql = "INSERT INTO audit_log (user_id, action, description, ip_address, created_at)
                    VALUES (?, ?, ?, ?, NOW())";

            $params = [
                $_SESSION['user_id'] ?? null,
                $action,
                "Department #{$departmentId}: {$details}",
                $_SERVER['REMOTE_ADDR'] ?? null
            ];

            $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
        }
    }
}
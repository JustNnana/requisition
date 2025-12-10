<?php
/**
 * GateWey Requisition Management System
 * User Management Class
 * 
 * File: classes/User.php
 * Purpose: Handle user CRUD operations, authentication, and user-related queries
 */

class User {
    
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new user
     * WITH AUTO-ASSIGNMENT OF EXECUTIVE DEPARTMENT FOR MD
     * 
     * @param array $data User data
     * @return array Result with success status and user_id or error message
     */
    public function create($data) {
        try {
            // Validate required fields
            if (empty($data['role_id']) || empty($data['email']) || 
                empty($data['password']) || empty($data['first_name']) || 
                empty($data['last_name'])) {
                return ['success' => false, 'message' => 'All required fields must be provided.'];
            }
            
            // Check if email already exists
            if ($this->emailExists($data['email'])) {
                return ['success' => false, 'message' => 'Email address already exists.'];
            }
            
            // AUTO-ASSIGN: If role is Managing Director, assign to Executive department
            if ((int)$data['role_id'] === ROLE_MANAGING_DIRECTOR) {
                $execDept = $this->db->fetchOne(
                    "SELECT id FROM departments WHERE department_code = 'EXEC' AND is_active = 1 LIMIT 1"
                );
                
                if ($execDept) {
                    $data['department_id'] = $execDept['id'];
                    error_log("Auto-assigning MD to Executive Office department (ID: {$data['department_id']})");
                } else {
                    // Executive department not found - create it automatically
                    $this->db->execute(
                        "INSERT INTO departments (department_name, department_code, is_active, created_at, updated_at) 
                         VALUES ('Executive Office', 'EXEC', 1, NOW(), NOW())"
                    );
                    $data['department_id'] = $this->db->lastInsertId();
                    error_log("Created Executive Office department (ID: {$data['department_id']})");
                }
            }
            
            // Hash password
            $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
            
            // Prepare SQL
            $sql = "INSERT INTO users (
                        role_id, department_id, first_name, last_name, 
                        email, password_hash, phone, is_active
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $data['role_id'],
                $data['department_id'] ?? null,
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $passwordHash,
                $data['phone'] ?? null,
                $data['is_active'] ?? 1
            ];
            
            $this->db->execute($sql, $params);
            $userId = $this->db->lastInsertId();
            
            // Log user creation
            if (ENABLE_AUDIT_LOG) {
                $this->logAction($userId, AUDIT_USER_CREATED, "User account created: {$data['email']}");
            }
            
            return [
                'success' => true,
                'message' => 'User created successfully.',
                'user_id' => $userId
            ];
            
        } catch (Exception $e) {
            error_log("User creation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while creating the user.'];
        }
    }
    
    /**
     * Get user by ID
     * 
     * @param int $userId User ID
     * @return array|false User data or false if not found
     */
    public function getById($userId) {
        $sql = "SELECT u.*, r.role_name, r.role_code, d.department_name, d.department_code
                FROM users u
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN departments d ON u.department_id = d.id
                WHERE u.id = ?";
        
        return $this->db->fetchOne($sql, [$userId]);
    }
    
    /**
     * Get user by email
     * 
     * @param string $email Email address
     * @return array|false User data or false if not found
     */
    public function getByEmail($email) {
        $sql = "SELECT u.*, r.role_name, r.role_code, d.department_name, d.department_code
                FROM users u
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN departments d ON u.department_id = d.id
                WHERE u.email = ? AND u.is_active = 1";
        
        return $this->db->fetchOne($sql, [$email]);
    }
    
    /**
     * Update user
     * WITH AUTO-ASSIGNMENT OF EXECUTIVE DEPARTMENT FOR MD
     * 
     * @param int $userId User ID
     * @param array $data User data to update
     * @return array Result with success status
     */
    public function update($userId, $data) {
        try {
            // Check if user exists
            $user = $this->getById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found.'];
            }
            
            // Check email uniqueness if email is being changed
            if (isset($data['email']) && $data['email'] !== $user['email']) {
                if ($this->emailExists($data['email'], $userId)) {
                    return ['success' => false, 'message' => 'Email address already exists.'];
                }
            }
            
            // AUTO-ASSIGN: If role is being changed to Managing Director, assign to Executive department
            if (isset($data['role_id']) && (int)$data['role_id'] === ROLE_MANAGING_DIRECTOR) {
                $execDept = $this->db->fetchOne(
                    "SELECT id FROM departments WHERE department_code = 'EXEC' AND is_active = 1 LIMIT 1"
                );
                
                if ($execDept) {
                    $data['department_id'] = $execDept['id'];
                    error_log("Auto-assigning MD to Executive Office department (ID: {$data['department_id']})");
                } else {
                    // Executive department not found - create it automatically
                    $this->db->execute(
                        "INSERT INTO departments (department_name, department_code, is_active, created_at, updated_at) 
                         VALUES ('Executive Office', 'EXEC', 1, NOW(), NOW())"
                    );
                    $data['department_id'] = $this->db->lastInsertId();
                    error_log("Created Executive Office department (ID: {$data['department_id']})");
                }
            }
            
            // Build update query
            $fields = [];
            $params = [];
            
            if (isset($data['role_id'])) {
                $fields[] = "role_id = ?";
                $params[] = $data['role_id'];
            }
            
            if (isset($data['department_id'])) {
                $fields[] = "department_id = ?";
                $params[] = $data['department_id'];
            }
            
            if (isset($data['first_name'])) {
                $fields[] = "first_name = ?";
                $params[] = $data['first_name'];
            }
            
            if (isset($data['last_name'])) {
                $fields[] = "last_name = ?";
                $params[] = $data['last_name'];
            }
            
            if (isset($data['email'])) {
                $fields[] = "email = ?";
                $params[] = $data['email'];
            }
            
            if (isset($data['phone'])) {
                $fields[] = "phone = ?";
                $params[] = $data['phone'];
            }
            
            if (isset($data['is_active'])) {
                $fields[] = "is_active = ?";
                $params[] = $data['is_active'];
            }
            
            // Handle password update separately
            if (isset($data['password']) && !empty($data['password'])) {
                $fields[] = "password_hash = ?";
                $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
            }
            
            if (empty($fields)) {
                return ['success' => false, 'message' => 'No fields to update.'];
            }
            
            $params[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            
            $this->db->execute($sql, $params);
            
            // Log user update
            if (ENABLE_AUDIT_LOG) {
                $this->logAction($userId, AUDIT_USER_UPDATED, "User account updated");
            }
            
            return ['success' => true, 'message' => 'User updated successfully.'];
            
        } catch (Exception $e) {
            error_log("User update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating the user.'];
        }
    }
    
    /**
     * Delete user (soft delete by setting is_active = 0)
     * 
     * @param int $userId User ID
     * @return array Result with success status
     */
    public function delete($userId) {
        try {
            // Check if user exists
            $user = $this->getById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found.'];
            }
            
            // Soft delete (set is_active = 0)
            $sql = "UPDATE users SET is_active = 0 WHERE id = ?";
            $this->db->execute($sql, [$userId]);
            
            // Log user deletion
            if (ENABLE_AUDIT_LOG) {
                $this->logAction($userId, AUDIT_USER_DELETED, "User account deleted");
            }
            
            return ['success' => true, 'message' => 'User deleted successfully.'];
            
        } catch (Exception $e) {
            error_log("User deletion error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting the user.'];
        }
    }
    
    /**
     * Hard delete user (permanently remove from database)
     * WARNING: This will CASCADE delete all related records!
     * 
     * @param int $userId User ID
     * @return array Result with success status
     */
    public function hardDelete($userId) {
        try {
            // Check if user exists
            $user = $this->getById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found.'];
            }
            
            // Hard delete (will CASCADE)
            $sql = "DELETE FROM users WHERE id = ?";
            $this->db->execute($sql, [$userId]);
            
            return ['success' => true, 'message' => 'User permanently deleted.'];
            
        } catch (Exception $e) {
            error_log("User hard deletion error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting the user.'];
        }
    }
    
    /**
     * Get all users with pagination
     * 
     * @param array $filters Filters (role_id, department_id, is_active, search)
     * @param int $page Page number
     * @param int $perPage Records per page
     * @return array Users data with pagination info
     */
    public function getAll($filters = [], $page = 1, $perPage = RECORDS_PER_PAGE) {
        try {
            $where = ["1=1"];
            $params = [];
            
            // Apply filters
            if (isset($filters['role_id'])) {
                $where[] = "u.role_id = ?";
                $params[] = $filters['role_id'];
            }
            
            if (isset($filters['department_id'])) {
                $where[] = "u.department_id = ?";
                $params[] = $filters['department_id'];
            }
            
            if (isset($filters['is_active'])) {
                $where[] = "u.is_active = ?";
                $params[] = $filters['is_active'];
            }
            
            if (isset($filters['search']) && !empty($filters['search'])) {
                $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = implode(' AND ', $where);
            
            // Count total records
            $countSql = "SELECT COUNT(*) as total FROM users u WHERE $whereClause";
            $countResult = $this->db->fetchOne($countSql, $params);
            $totalRecords = $countResult['total'];
            
            // Calculate pagination
            $totalPages = ceil($totalRecords / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Fetch users
            $sql = "SELECT u.*, r.role_name, r.role_code, d.department_name, d.department_code
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    LEFT JOIN departments d ON u.department_id = d.id
                    WHERE $whereClause
                    ORDER BY u.created_at DESC
                    LIMIT ? OFFSET ?";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $users = $this->db->fetchAll($sql, $params);
            
            return [
                'success' => true,
                'users' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_records' => $totalRecords,
                    'total_pages' => $totalPages
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Get all users error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while fetching users.'];
        }
    }
    
    /**
     * Get users by role
     * 
     * @param int $roleId Role ID
     * @return array Users array
     */
    public function getByRole($roleId) {
        $sql = "SELECT u.*, r.role_name, d.department_name
                FROM users u
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN departments d ON u.department_id = d.id
                WHERE u.role_id = ? AND u.is_active = 1
                ORDER BY u.first_name, u.last_name";
        
        return $this->db->fetchAll($sql, [$roleId]);
    }
    
    /**
     * Get users by department
     * 
     * @param int $departmentId Department ID
     * @return array Users array
     */
    public function getByDepartment($departmentId) {
        $sql = "SELECT u.*, r.role_name, d.department_name
                FROM users u
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN departments d ON u.department_id = d.id
                WHERE u.department_id = ? AND u.is_active = 1
                ORDER BY u.first_name, u.last_name";
        
        return $this->db->fetchAll($sql, [$departmentId]);
    }
    
    /**
     * Check if email exists
     * 
     * @param string $email Email address
     * @param int $excludeUserId User ID to exclude from check (for updates)
     * @return bool
     */
    public function emailExists($email, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeUserId) {
            $sql .= " AND id != ?";
            $params[] = $excludeUserId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Update user's last login timestamp
     * 
     * @param int $userId User ID
     */
    public function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $this->db->execute($sql, [$userId]);
    }
    
    /**
     * Change user password
     * 
     * @param int $userId User ID
     * @param string $newPassword New password
     * @return array Result with success status
     */
    public function changePassword($userId, $newPassword) {
        try {
            // Verify user exists
            $user = $this->getById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found.'];
            }
            
            $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
            
            $sql = "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($sql, [$passwordHash, $userId]);
            
            // Log password change
            if (ENABLE_AUDIT_LOG) {
                $this->logAction($userId, AUDIT_USER_PASSWORD_CHANGED, "Password changed for user: {$user['email']}");
            }
            
            return ['success' => true, 'message' => 'Password changed successfully.'];
            
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while changing password.'];
        }
    }
    
    /**
     * Verify user password
     * 
     * @param int $userId User ID
     * @param string $password Password to verify
     * @return bool
     */
    public function verifyPassword($userId, $password) {
        $sql = "SELECT password_hash FROM users WHERE id = ?";
        $result = $this->db->fetchOne($sql, [$userId]);
        
        if ($result) {
            return password_verify($password, $result['password_hash']);
        }
        
        return false;
    }
    
    /**
     * Activate user account
     * 
     * @param int $userId User ID
     * @return array Result with success status
     */
    public function activate($userId) {
        try {
            $sql = "UPDATE users SET is_active = 1 WHERE id = ?";
            $this->db->execute($sql, [$userId]);
            
            return ['success' => true, 'message' => 'User activated successfully.'];
            
        } catch (Exception $e) {
            error_log("User activation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while activating user.'];
        }
    }
    
    /**
     * Deactivate user account
     * 
     * @param int $userId User ID
     * @return array Result with success status
     */
    public function deactivate($userId) {
        try {
            $sql = "UPDATE users SET is_active = 0 WHERE id = ?";
            $this->db->execute($sql, [$userId]);
            
            return ['success' => true, 'message' => 'User deactivated successfully.'];
            
        } catch (Exception $e) {
            error_log("User deactivation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deactivating user.'];
        }
    }
    
    /**
     * Get user statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        try {
            $stats = [];
            
            // Total users
            $sql = "SELECT COUNT(*) as total FROM users WHERE is_active = 1";
            $result = $this->db->fetchOne($sql);
            $stats['total_users'] = $result['total'];
            
            // Users by role
            $sql = "SELECT r.role_name, COUNT(u.id) as count
                    FROM roles r
                    LEFT JOIN users u ON r.id = u.role_id AND u.is_active = 1
                    GROUP BY r.id, r.role_name
                    ORDER BY r.id";
            $stats['by_role'] = $this->db->fetchAll($sql);
            
            // Users by department
            $sql = "SELECT d.department_name, COUNT(u.id) as count
                    FROM departments d
                    LEFT JOIN users u ON d.id = u.department_id AND u.is_active = 1
                    WHERE d.is_active = 1
                    GROUP BY d.id, d.department_name
                    ORDER BY d.department_name";
            $stats['by_department'] = $this->db->fetchAll($sql);
            
            // Active vs inactive
            $sql = "SELECT 
                        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
                    FROM users";
            $result = $this->db->fetchOne($sql);
            $stats['active'] = $result['active'];
            $stats['inactive'] = $result['inactive'];
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Get user statistics error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Log user action to audit trail
     * 
     * @param int $userId User ID
     * @param string $action Action constant
     * @param string $description Action description
     */
    private function logAction($userId, $action, $description) {
        try {
            $sql = "INSERT INTO audit_log (user_id, action, description, ip_address, user_agent)
                    VALUES (?, ?, ?, ?, ?)";
            
            $params = [
                $userId,
                $action,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ];
            
            $this->db->execute($sql, $params);
            
        } catch (Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
        }
    }
}
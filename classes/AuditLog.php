<?php
/**
 * GateWey Requisition Management System
 * Audit Log Class
 * 
 * File: classes/AuditLog.php
 * Purpose: Comprehensive logging of all system actions for accountability and tracking
 */

class AuditLog {
    
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Log an action
     * 
     * @param int $userId User who performed the action
     * @param string $actionType Action type constant (AUDIT_*)
     * @param string $description Action description
     * @param int|null $requisitionId Related requisition ID (optional)
     * @param array $metadata Additional metadata (optional)
     * @return bool Success status
     */
    public function log($userId, $actionType, $description, $requisitionId = null, $metadata = []) {
        try {
            // Get client info
            $ipAddress = $this->getClientIp();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            // Convert metadata to JSON
            $metadataJson = !empty($metadata) ? json_encode($metadata) : null;
            
            // Insert audit log entry
            $sql = "INSERT INTO audit_log (
                        user_id, 
                        requisition_id, 
                        action_type, 
                        description, 
                        ip_address, 
                        user_agent, 
                        metadata,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $userId,
                $requisitionId,
                $actionType,
                $description,
                $ipAddress,
                $userAgent,
                $metadataJson
            ];
            
            $this->db->execute($sql, $params);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log user action (shortcut for user-related actions)
     * 
     * @param int $userId User ID performing action
     * @param string $actionType Action type constant
     * @param string $description Action description
     * @param int|null $targetUserId Target user ID (for user management actions)
     * @return bool Success status
     */
    public function logUserAction($userId, $actionType, $description, $targetUserId = null) {
        $metadata = $targetUserId ? ['target_user_id' => $targetUserId] : [];
        return $this->log($userId, $actionType, $description, null, $metadata);
    }
    
    /**
     * Log requisition action (shortcut for requisition-related actions)
     * 
     * @param int $userId User ID performing action
     * @param int $requisitionId Requisition ID
     * @param string $actionType Action type constant
     * @param string $description Action description
     * @param array $metadata Additional metadata
     * @return bool Success status
     */
    public function logRequisitionAction($userId, $requisitionId, $actionType, $description, $metadata = []) {
        return $this->log($userId, $actionType, $description, $requisitionId, $metadata);
    }
    
    /**
     * Log approval action
     * 
     * @param int $approverId Approver user ID
     * @param int $requisitionId Requisition ID
     * @param string $action 'approved' or 'rejected'
     * @param string|null $comments Approval/rejection comments
     * @return bool Success status
     */
    public function logApproval($approverId, $requisitionId, $action, $comments = null) {
        $actionType = ($action === 'approved') ? AUDIT_REQUISITION_APPROVED : AUDIT_REQUISITION_REJECTED;
        $description = ($action === 'approved') 
            ? "Requisition approved" 
            : "Requisition rejected" . ($comments ? ": " . substr($comments, 0, 100) : "");
        
        $metadata = [
            'action' => $action,
            'comments' => $comments
        ];
        
        return $this->logRequisitionAction($approverId, $requisitionId, $actionType, $description, $metadata);
    }
    
    /**
     * Get audit log entries for a requisition
     * 
     * @param int $requisitionId Requisition ID
     * @param int|null $limit Limit number of entries
     * @return array Audit log entries
     */
    public function getRequisitionLog($requisitionId, $limit = null) {
        try {
            $sql = "SELECT a.*, u.first_name, u.last_name, u.email, r.role_name
                    FROM audit_log a
                    LEFT JOIN users u ON a.user_id = u.id
                    LEFT JOIN roles r ON u.role_id = r.id
                    WHERE a.requisition_id = ?
                    ORDER BY a.created_at DESC";
            
            if ($limit) {
                $sql .= " LIMIT " . (int)$limit;
            }
            
            return $this->db->fetchAll($sql, [$requisitionId]);
            
        } catch (Exception $e) {
            error_log("Error fetching requisition log: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get audit log entries for a user
     * 
     * @param int $userId User ID
     * @param int|null $limit Limit number of entries
     * @return array Audit log entries
     */
    public function getUserLog($userId, $limit = null) {
        try {
            $sql = "SELECT a.*, r.requisition_number
                    FROM audit_log a
                    LEFT JOIN requisitions r ON a.requisition_id = r.id
                    WHERE a.user_id = ?
                    ORDER BY a.created_at DESC";
            
            if ($limit) {
                $sql .= " LIMIT " . (int)$limit;
            }
            
            return $this->db->fetchAll($sql, [$userId]);
            
        } catch (Exception $e) {
            error_log("Error fetching user log: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent audit log entries (system-wide)
     * 
     * @param int $limit Number of entries to fetch
     * @param array $filters Optional filters (action_type, user_id, date_from, date_to)
     * @return array Audit log entries
     */
    public function getRecentLog($limit = 50, $filters = []) {
        try {
            $where = ["1=1"];
            $params = [];
            
            // Apply filters
            if (!empty($filters['action_type'])) {
                $where[] = "a.action_type = ?";
                $params[] = $filters['action_type'];
            }
            
            if (!empty($filters['user_id'])) {
                $where[] = "a.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (!empty($filters['date_from'])) {
                $where[] = "DATE(a.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where[] = "DATE(a.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            $sql = "SELECT a.*, 
                           u.first_name, u.last_name, u.email, 
                           ro.role_name,
                           r.requisition_number
                    FROM audit_log a
                    LEFT JOIN users u ON a.user_id = u.id
                    LEFT JOIN roles ro ON u.role_id = ro.id
                    LEFT JOIN requisitions r ON a.requisition_id = r.id
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY a.created_at DESC
                    LIMIT " . (int)$limit;
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Error fetching recent log: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get audit statistics
     * 
     * @param array $filters Optional filters (date_from, date_to, user_id)
     * @return array Statistics
     */
    public function getStatistics($filters = []) {
        try {
            $where = ["1=1"];
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $where[] = "DATE(created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where[] = "DATE(created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['user_id'])) {
                $where[] = "user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            $sql = "SELECT 
                        COUNT(*) as total_actions,
                        COUNT(DISTINCT user_id) as unique_users,
                        COUNT(DISTINCT requisition_id) as unique_requisitions,
                        COUNT(CASE WHEN action_type LIKE '%login%' THEN 1 END) as login_actions,
                        COUNT(CASE WHEN action_type LIKE '%requisition%' THEN 1 END) as requisition_actions,
                        COUNT(CASE WHEN action_type LIKE '%approval%' THEN 1 END) as approval_actions
                    FROM audit_log
                    WHERE " . implode(' AND ', $where);
            
            return $this->db->fetchOne($sql, $params);
            
        } catch (Exception $e) {
            error_log("Error fetching audit statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get actions by type
     * 
     * @param string $actionType Action type constant
     * @param int $limit Limit number of entries
     * @return array Audit log entries
     */
    public function getByActionType($actionType, $limit = 50) {
        try {
            $sql = "SELECT a.*, 
                           u.first_name, u.last_name, u.email,
                           r.requisition_number
                    FROM audit_log a
                    LEFT JOIN users u ON a.user_id = u.id
                    LEFT JOIN requisitions r ON a.requisition_id = r.id
                    WHERE a.action_type = ?
                    ORDER BY a.created_at DESC
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$actionType, $limit]);
            
        } catch (Exception $e) {
            error_log("Error fetching actions by type: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search audit log
     * 
     * @param string $searchTerm Search term
     * @param int $limit Limit number of results
     * @return array Search results
     */
    public function search($searchTerm, $limit = 50) {
        try {
            $searchTerm = '%' . $searchTerm . '%';
            
            $sql = "SELECT a.*, 
                           u.first_name, u.last_name, u.email,
                           r.requisition_number
                    FROM audit_log a
                    LEFT JOIN users u ON a.user_id = u.id
                    LEFT JOIN requisitions r ON a.requisition_id = r.id
                    WHERE a.description LIKE ? 
                       OR u.email LIKE ?
                       OR r.requisition_number LIKE ?
                    ORDER BY a.created_at DESC
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $limit]);
            
        } catch (Exception $e) {
            error_log("Error searching audit log: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clean old audit log entries
     * 
     * @param int $days Number of days to keep
     * @return int Number of deleted entries
     */
    public function cleanOldEntries($days = 365) {
        try {
            $sql = "DELETE FROM audit_log 
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            
            $stmt = $this->db->execute($sql, [$days]);
            return $stmt->rowCount();
            
        } catch (Exception $e) {
            error_log("Error cleaning audit log: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get client IP address
     * 
     * @return string IP address
     */
    private function getClientIp() {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
    
    /**
     * Export audit log to CSV
     * 
     * @param array $filters Filters (date_from, date_to, user_id, action_type)
     * @param string $filename Output filename
     * @return bool Success status
     */
    public function exportToCsv($filters = [], $filename = 'audit_log.csv') {
        try {
            $entries = $this->getRecentLog(10000, $filters);
            
            if (empty($entries)) {
                return false;
            }
            
            // Create output file
            $output = fopen('php://output', 'w');
            
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            // Write CSV header
            fputcsv($output, [
                'ID',
                'Date/Time',
                'User',
                'Email',
                'Role',
                'Action Type',
                'Description',
                'Requisition',
                'IP Address'
            ]);
            
            // Write data rows
            foreach ($entries as $entry) {
                fputcsv($output, [
                    $entry['id'],
                    $entry['created_at'],
                    trim(($entry['first_name'] ?? '') . ' ' . ($entry['last_name'] ?? '')),
                    $entry['email'] ?? '',
                    $entry['role_name'] ?? '',
                    $entry['action_type'],
                    $entry['description'],
                    $entry['requisition_number'] ?? 'N/A',
                    $entry['ip_address']
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            error_log("Error exporting audit log: " . $e->getMessage());
            return false;
        }
    }
}
<?php
/**
 * GateWey Requisition Management System
 * Report Class
 * 
 * File: classes/Report.php
 * Purpose: Handle report generation, statistics, and data filtering
 */

class Report {
    
    private $db;
    private $auditLog;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->auditLog = new AuditLog();
    }
    
    /**
     * Generate personal report (Team Member)
     * 
     * @param array $filters Filter options
     * @param int $page Page number
     * @param int $perPage Records per page
     * @return array Report data with statistics and requisitions
     */
    public function generatePersonalReport($filters = [], $page = 1, $perPage = 15) {
        try {
            $userId = Session::getUserId();
            
            // Base query
            $sql = "SELECT r.*,
                           d.department_name,
                           d.department_code
                    FROM requisitions r
                    LEFT JOIN departments d ON r.department_id = d.id
                    WHERE r.user_id = ?";
            
            $params = [$userId];
            
            // Apply filters
            $sql .= $this->buildFilterQuery($filters, $params);
            
            // Get statistics
            $stats = $this->calculateStatistics($sql, $params);
            
            // Add pagination
            $sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
            $offset = ($page - 1) * $perPage;
            $params[] = $perPage;
            $params[] = $offset;
            
            // Get requisitions
            $requisitions = $this->db->fetchAll($sql, $params);
            
            // Calculate pagination
            $totalPages = ceil($stats['total_count'] / $perPage);
            
            // Log report generation
            $this->logReportGeneration('personal', $filters);
            
            return [
                'success' => true,
                'statistics' => $stats,
                'requisitions' => $requisitions,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_records' => $stats['total_count'],
                    'total_pages' => $totalPages
                ],
                'filters' => $filters
            ];
            
        } catch (Exception $e) {
            error_log("Generate personal report error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while generating the report.'
            ];
        }
    }
    
    /**
     * Generate department report (Line Manager)
     * 
     * @param array $filters Filter options
     * @param int $page Page number
     * @param int $perPage Records per page
     * @return array Report data with statistics and requisitions
     */
    public function generateDepartmentReport($filters = [], $page = 1, $perPage = 15) {
        try {
            $departmentId = Session::getUserDepartmentId();
            
            if (!$departmentId) {
                return [
                    'success' => false,
                    'message' => 'You are not assigned to a department.'
                ];
            }
            
            // Base query
            $sql = "SELECT r.*,
                           u.first_name as requester_first_name,
                           u.last_name as requester_last_name,
                           u.email as requester_email,
                           d.department_name,
                           d.department_code
                    FROM requisitions r
                    JOIN users u ON r.user_id = u.id
                    LEFT JOIN departments d ON r.department_id = d.id
                    WHERE r.department_id = ?";
            
            $params = [$departmentId];
            
            // Apply filters
            $sql .= $this->buildFilterQuery($filters, $params);
            
            // Get statistics
            $stats = $this->calculateStatistics($sql, $params);
            
            // Add pagination
            $sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
            $offset = ($page - 1) * $perPage;
            $params[] = $perPage;
            $params[] = $offset;
            
            // Get requisitions
            $requisitions = $this->db->fetchAll($sql, $params);
            
            // Calculate pagination
            $totalPages = ceil($stats['total_count'] / $perPage);
            
            // Log report generation
            $this->logReportGeneration('department', $filters);
            
            return [
                'success' => true,
                'statistics' => $stats,
                'requisitions' => $requisitions,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_records' => $stats['total_count'],
                    'total_pages' => $totalPages
                ],
                'filters' => $filters,
                'department_id' => $departmentId
            ];
            
        } catch (Exception $e) {
            error_log("Generate department report error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while generating the report.'
            ];
        }
    }
    
    /**
     * Generate organization-wide report (MD / Finance Manager)
     * 
     * @param array $filters Filter options
     * @param int $page Page number
     * @param int $perPage Records per page
     * @return array Report data with statistics and requisitions
     */
    public function generateOrganizationReport($filters = [], $page = 1, $perPage = 15) {
        try {
            // Verify permission
            if (!is_managing_director() && !is_finance_manager()) {
                return [
                    'success' => false,
                    'message' => 'You do not have permission to view organization-wide reports.'
                ];
            }
            
            // Base query
            $sql = "SELECT r.*,
                           u.first_name as requester_first_name,
                           u.last_name as requester_last_name,
                           u.email as requester_email,
                           d.department_name,
                           d.department_code
                    FROM requisitions r
                    JOIN users u ON r.user_id = u.id
                    LEFT JOIN departments d ON r.department_id = d.id
                    WHERE 1=1";
            
            $params = [];
            
            // Apply filters
            $sql .= $this->buildFilterQuery($filters, $params);
            
            // Get statistics
            $stats = $this->calculateStatistics($sql, $params);
            
            // Add pagination
            $sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
            $offset = ($page - 1) * $perPage;
            $params[] = $perPage;
            $params[] = $offset;
            
            // Get requisitions
            $requisitions = $this->db->fetchAll($sql, $params);
            
            // Calculate pagination
            $totalPages = ceil($stats['total_count'] / $perPage);
            
            // Get department breakdown
            $departmentBreakdown = $this->getDepartmentBreakdown($filters);
            
            // Log report generation
            $this->logReportGeneration('organization', $filters);
            
            return [
                'success' => true,
                'statistics' => $stats,
                'requisitions' => $requisitions,
                'department_breakdown' => $departmentBreakdown,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_records' => $stats['total_count'],
                    'total_pages' => $totalPages
                ],
                'filters' => $filters
            ];
            
        } catch (Exception $e) {
            error_log("Generate organization report error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while generating the report.'
            ];
        }
    }
    
    /**
     * Build filter query
     * 
     * @param array $filters Filter options
     * @param array &$params Parameters array (passed by reference)
     * @return string SQL WHERE clauses
     */
    private function buildFilterQuery($filters, &$params) {
        $whereClauses = [];
        
        // Time period filter
        if (!empty($filters['period'])) {
            $dateRange = $this->getPeriodDateRange($filters['period']);
            if ($dateRange) {
                $whereClauses[] = "DATE(r.created_at) >= ?";
                $params[] = $dateRange['start'];
                $whereClauses[] = "DATE(r.created_at) <= ?";
                $params[] = $dateRange['end'];
            }
        }
        
        // Custom date range
        if (!empty($filters['date_from'])) {
            $whereClauses[] = "DATE(r.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClauses[] = "DATE(r.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Status filter
        if (!empty($filters['status'])) {
            $whereClauses[] = "r.status = ?";
            $params[] = $filters['status'];
        }
        
        // Department filter (for organization reports)
        if (!empty($filters['department_id'])) {
            $whereClauses[] = "r.department_id = ?";
            $params[] = $filters['department_id'];
        }
        
        // User filter (for organization reports)
        if (!empty($filters['user_id'])) {
            $whereClauses[] = "r.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $whereClauses[] = "(r.requisition_number LIKE ? OR r.purpose LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Exclude drafts by default (unless specifically requested)
        if (!isset($filters['include_drafts']) || !$filters['include_drafts']) {
            $whereClauses[] = "r.status != ?";
            $params[] = STATUS_DRAFT;
        }
        
        return !empty($whereClauses) ? ' AND ' . implode(' AND ', $whereClauses) : '';
    }
    
    /**
     * Calculate statistics for report
     * 
     * @param string $sql Base SQL query
     * @param array $params Query parameters
     * @return array Statistics
     */
    private function calculateStatistics($sql, $params) {
        try {
            // Total count
            $countSql = "SELECT COUNT(*) as total_count,
                               COALESCE(SUM(total_amount), 0) as total_amount,
                               COALESCE(AVG(total_amount), 0) as average_amount,
                               COALESCE(MIN(total_amount), 0) as min_amount,
                               COALESCE(MAX(total_amount), 0) as max_amount
                        FROM (" . $sql . ") as subquery";
            
            $result = $this->db->fetchOne($countSql, $params);
            
            // Status breakdown
            $statusSql = "SELECT status, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total
                         FROM (" . $sql . ") as subquery
                         GROUP BY status";
            
            $statusBreakdown = $this->db->fetchAll($statusSql, $params);
            
            return [
                'total_count' => (int)$result['total_count'],
                'total_amount' => (float)$result['total_amount'],
                'average_amount' => (float)$result['average_amount'],
                'min_amount' => (float)$result['min_amount'],
                'max_amount' => (float)$result['max_amount'],
                'status_breakdown' => $statusBreakdown
            ];
            
        } catch (Exception $e) {
            error_log("Calculate statistics error: " . $e->getMessage());
            return [
                'total_count' => 0,
                'total_amount' => 0,
                'average_amount' => 0,
                'min_amount' => 0,
                'max_amount' => 0,
                'status_breakdown' => []
            ];
        }
    }
    
    /**
     * Get department breakdown for organization reports
     * 
     * @param array $filters Filter options
     * @return array Department breakdown
     */
    private function getDepartmentBreakdown($filters) {
        try {
            $sql = "SELECT d.department_name,
                           d.department_code,
                           COUNT(r.id) as requisition_count,
                           COALESCE(SUM(r.total_amount), 0) as total_amount
                    FROM departments d
                    LEFT JOIN requisitions r ON d.id = r.department_id";
            
            $params = [];
            $whereClauses = ["1=1"];
            
            // Apply date filters
            if (!empty($filters['date_from'])) {
                $whereClauses[] = "DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereClauses[] = "DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            // Exclude drafts
            $whereClauses[] = "(r.status != ? OR r.status IS NULL)";
            $params[] = STATUS_DRAFT;
            
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
            $sql .= " GROUP BY d.id, d.department_name, d.department_code";
            $sql .= " ORDER BY total_amount DESC";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get department breakdown error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get period date range
     * 
     * @param string $period Period type (weekly, monthly, quarterly, yearly)
     * @return array|null Date range ['start' => date, 'end' => date]
     */
    private function getPeriodDateRange($period) {
        $now = new DateTime();
        
        switch ($period) {
            case 'weekly':
                $start = (clone $now)->modify('monday this week');
                $end = (clone $now)->modify('sunday this week');
                break;
                
            case 'monthly':
                $start = (clone $now)->modify('first day of this month');
                $end = (clone $now)->modify('last day of this month');
                break;
                
            case 'quarterly':
                $currentMonth = (int)$now->format('n');
                $currentQuarter = ceil($currentMonth / 3);
                $firstMonthOfQuarter = ($currentQuarter - 1) * 3 + 1;
                
                $start = (clone $now)->setDate((int)$now->format('Y'), $firstMonthOfQuarter, 1);
                $end = (clone $start)->modify('+2 months')->modify('last day of this month');
                break;
                
            case 'yearly':
                $start = (clone $now)->modify('first day of January');
                $end = (clone $now)->modify('last day of December');
                break;
                
            default:
                return null;
        }
        
        return [
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d')
        ];
    }
    
    /**
     * Get data for Excel export
     * 
     * @param array $filters Filter options
     * @param string $reportType Report type (personal, department, organization)
     * @return array Export data
     */
    public function getExportData($filters, $reportType = 'personal') {
        try {
            switch ($reportType) {
                case 'department':
                    $report = $this->generateDepartmentReport($filters, 1, 10000);
                    break;
                case 'organization':
                    $report = $this->generateOrganizationReport($filters, 1, 10000);
                    break;
                default:
                    $report = $this->generatePersonalReport($filters, 1, 10000);
                    break;
            }
            
            if (!$report['success']) {
                return null;
            }
            
            return [
                'statistics' => $report['statistics'],
                'requisitions' => $report['requisitions'],
                'filters' => $report['filters'],
                'report_type' => $reportType,
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => Session::getUserFullName()
            ];
            
        } catch (Exception $e) {
            error_log("Get export data error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Log report generation
     * 
     * @param string $reportType Report type
     * @param array $filters Filters used
     */
    private function logReportGeneration($reportType, $filters) {
        try {
            $description = "Generated " . ucfirst($reportType) . " report";
            
            if (!empty($filters['period'])) {
                $description .= " - Period: " . $filters['period'];
            }
            
            if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
                $description .= " - Date range: " . ($filters['date_from'] ?? 'any') . " to " . ($filters['date_to'] ?? 'any');
            }
            
            $this->auditLog->log(
                Session::getUserId(),
                null,
                AUDIT_REPORT_GENERATED,
                $description,
                $filters
            );
            
        } catch (Exception $e) {
            error_log("Log report generation error: " . $e->getMessage());
        }
    }
}
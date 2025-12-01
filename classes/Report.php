<?php
/**
 * GateWey Requisition Management System
 * Report Class - Enhanced with Analytics
 * 
 * File: classes/Report.php
 * Purpose: Handle report generation, statistics, and data filtering with chart support
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
     * Generate personal report with enhanced analytics
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
                           d.department_code,
                           rc.category_name
                    FROM requisitions r
                    LEFT JOIN departments d ON r.department_id = d.id
                    LEFT JOIN requisition_categories rc ON r.purpose = rc.category_name
                    WHERE r.user_id = ?";
            
            $params = [$userId];
            
            // Apply filters
            $sql .= $this->buildFilterQuery($filters, $params);
            
            // Get comprehensive statistics
            $stats = $this->calculateComprehensiveStatistics($sql, $params, $userId);
            
            // Add pagination
            $sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
            $offset = ($page - 1) * $perPage;
            $params[] = $perPage;
            $params[] = $offset;
            
            // Get requisitions
            $requisitions = $this->db->fetchAll($sql, $params);
            
            // Calculate pagination
            $totalPages = ceil($stats['total_count'] / $perPage);
            
            // Get chart data
            $chartData = [
                'trends' => $this->getRequisitionTrends($userId, $filters),
                'categories' => $this->getCategoryDistribution($userId, $filters),
                'status' => $this->getStatusDistribution($userId, $filters),
                'amounts' => $this->getAmountTrends($userId, $filters),
                'hourly' => $this->getHourlyDistribution($userId, $filters),
                'weekday' => $this->getWeekdayDistribution($userId, $filters),
                'monthly' => $this->getMonthlySpending($userId, $filters)
            ];
            
            // Get advanced analytics
            $analytics = $this->getAdvancedAnalytics($userId, $filters);
            
            // Log report generation
            $this->logReportGeneration('personal', $filters);
            
            return [
                'success' => true,
                'statistics' => $stats,
                'requisitions' => $requisitions,
                'chart_data' => $chartData,
                'analytics' => $analytics,
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
     * Get requisition trends over time
     */
    private function getRequisitionTrends($userId, $filters) {
        try {
            $interval = $filters['interval'] ?? 'daily';
            
            $format = "";
            $groupBy = "";
            
            switch ($interval) {
                case 'hourly':
                    $format = "%Y-%m-%d %H:00";
                    $groupBy = "DATE_FORMAT(r.created_at, '%Y-%m-%d %H:00')";
                    break;
                case 'weekly':
                    $format = "Week %u, %Y";
                    $groupBy = "YEARWEEK(r.created_at, 1)";
                    break;
                case 'monthly':
                    $format = "%b %Y";
                    $groupBy = "DATE_FORMAT(r.created_at, '%Y-%m')";
                    break;
                case 'daily':
                default:
                    $format = "%Y-%m-%d";
                    $groupBy = "DATE(r.created_at)";
                    break;
            }
            
            $sql = "SELECT DATE_FORMAT(r.created_at, '$format') as time_period,
                           COUNT(*) as count,
                           SUM(r.total_amount) as total_amount
                    FROM requisitions r
                    WHERE r.user_id = ? AND r.status != 'draft'";
            
            $params = [$userId];
            
            // Only add date filters if specified
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            // Add period filter if specified
            if (!empty($filters['period']) && $filters['period'] !== 'custom') {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            $sql .= " GROUP BY $groupBy ORDER BY r.created_at ASC";
            
            error_log("Trends Query: " . $sql);
            error_log("Trends Params: " . json_encode($params));
            
            $result = $this->db->fetchAll($sql, $params);
            error_log("Trends Result Count: " . count($result));
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Get requisition trends error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get category distribution
     */
    private function getCategoryDistribution($userId, $filters) {
        try {
            $sql = "SELECT COALESCE(rc.category_name, r.purpose) as category,
                           COUNT(*) as count,
                           SUM(r.total_amount) as total_amount
                    FROM requisitions r
                    LEFT JOIN requisition_categories rc ON r.purpose = rc.category_name
                    WHERE r.user_id = ? AND r.status != 'draft'";
            
            $params = [$userId];
            
            // Only add date filters if specified
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            // Add period filter if specified
            if (!empty($filters['period']) && $filters['period'] !== 'custom') {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            $sql .= " GROUP BY category ORDER BY count DESC LIMIT 10";
            
            error_log("Category Query: " . $sql);
            error_log("Category Params: " . json_encode($params));
            
            $result = $this->db->fetchAll($sql, $params);
            error_log("Category Result Count: " . count($result));
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Get category distribution error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get status distribution
     */
    private function getStatusDistribution($userId, $filters) {
        try {
            $sql = "SELECT r.status,
                           COUNT(*) as count,
                           SUM(r.total_amount) as total_amount
                    FROM requisitions r
                    WHERE r.user_id = ? AND r.status != 'draft'";
            
            $params = [$userId];
            
            // Only add date filters if specified
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            // Add period filter if specified
            if (!empty($filters['period']) && $filters['period'] !== 'custom') {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            $sql .= " GROUP BY r.status ORDER BY count DESC";
            
            error_log("Status Query: " . $sql);
            error_log("Status Params: " . json_encode($params));
            
            $result = $this->db->fetchAll($sql, $params);
            error_log("Status Result Count: " . count($result));
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Get status distribution error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get amount trends
     */
    private function getAmountTrends($userId, $filters) {
        try {
            $sql = "SELECT DATE_FORMAT(r.created_at, '%Y-%m') as month,
                           AVG(r.total_amount) as avg_amount,
                           MIN(r.total_amount) as min_amount,
                           MAX(r.total_amount) as max_amount,
                           SUM(r.total_amount) as total_amount
                    FROM requisitions r
                    WHERE r.user_id = ?";
            
            $params = [$userId];
            $sql .= $this->buildFilterQuery($filters, $params);
            $sql .= " GROUP BY month ORDER BY month ASC";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get amount trends error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get hourly distribution
     */
    private function getHourlyDistribution($userId, $filters) {
        try {
            $sql = "SELECT HOUR(r.created_at) as hour,
                           COUNT(*) as count
                    FROM requisitions r
                    WHERE r.user_id = ? AND r.status != 'draft'";
            
            $params = [$userId];
            
            // Only add date filters if specified
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            // Add period filter if specified
            if (!empty($filters['period']) && $filters['period'] !== 'custom') {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            $sql .= " GROUP BY hour ORDER BY hour ASC";
            
            error_log("Hourly Query: " . $sql);
            error_log("Hourly Params: " . json_encode($params));
            
            $result = $this->db->fetchAll($sql, $params);
            error_log("Hourly Result Count: " . count($result));
            
            // Fill in missing hours with 0
            $hourlyData = array_fill(0, 24, 0);
            foreach ($result as $item) {
                $hourlyData[(int)$item['hour']] = (int)$item['count'];
            }
            
            error_log("Hourly Data Sum: " . array_sum($hourlyData));
            
            return $hourlyData;
            
        } catch (Exception $e) {
            error_log("Get hourly distribution error: " . $e->getMessage());
            return array_fill(0, 24, 0);
        }
    }
    
    /**
     * Get weekday distribution
     */
    private function getWeekdayDistribution($userId, $filters) {
        try {
            $sql = "SELECT WEEKDAY(r.created_at) as weekday,
                           COUNT(*) as count
                    FROM requisitions r
                    WHERE r.user_id = ? AND r.status != 'draft'";
            
            $params = [$userId];
            
            // Only add date filters if specified
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            // Add period filter if specified
            if (!empty($filters['period']) && $filters['period'] !== 'custom') {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            $sql .= " GROUP BY weekday ORDER BY weekday ASC";
            
            error_log("Weekday Query: " . $sql);
            error_log("Weekday Params: " . json_encode($params));
            
            $result = $this->db->fetchAll($sql, $params);
            error_log("Weekday Result Count: " . count($result));
            
            // Fill in missing days with 0
            $weekdayData = array_fill(0, 7, 0);
            foreach ($result as $item) {
                $weekdayData[(int)$item['weekday']] = (int)$item['count'];
            }
            
            error_log("Weekday Data Sum: " . array_sum($weekdayData));
            
            return $weekdayData;
            
        } catch (Exception $e) {
            error_log("Get weekday distribution error: " . $e->getMessage());
            return array_fill(0, 7, 0);
        }
    }
    
    /**
     * Get monthly spending patterns
     */
    private function getMonthlySpending($userId, $filters) {
        try {
            $sql = "SELECT DATE_FORMAT(r.created_at, '%b %Y') as month,
                           SUM(r.total_amount) as total_amount,
                           COUNT(*) as count
                    FROM requisitions r
                    WHERE r.user_id = ? AND r.status != 'draft'";
            
            $params = [$userId];
            
            // Only add date filters if specified
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            // Add period filter if specified
            if (!empty($filters['period']) && $filters['period'] !== 'custom') {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            $sql .= " GROUP BY DATE_FORMAT(r.created_at, '%Y-%m')
                     ORDER BY r.created_at ASC
                     LIMIT 12";
            
            error_log("Monthly Query: " . $sql);
            error_log("Monthly Params: " . json_encode($params));
            
            $result = $this->db->fetchAll($sql, $params);
            error_log("Monthly Result Count: " . count($result));
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Get monthly spending error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calculate comprehensive statistics
     */
    private function calculateComprehensiveStatistics($sql, $params, $userId) {
        try {
            // Basic counts and amounts
            $countSql = "SELECT COUNT(*) as total_count,
                               COALESCE(SUM(total_amount), 0) as total_amount,
                               COALESCE(AVG(total_amount), 0) as average_amount,
                               COALESCE(MIN(total_amount), 0) as min_amount,
                               COALESCE(MAX(total_amount), 0) as max_amount
                        FROM (" . $sql . ") as subquery";
            
            $result = $this->db->fetchOne($countSql, $params);
            
            // Status-specific counts
            $stats = [
                'total_count' => (int)$result['total_count'],
                'total_amount' => (float)$result['total_amount'],
                'average_amount' => (float)$result['average_amount'],
                'min_amount' => (float)$result['min_amount'],
                'max_amount' => (float)$result['max_amount']
            ];
            
            // Get status breakdown
            $statusSql = "SELECT status, COUNT(*) as count
                         FROM (" . $sql . ") as subquery
                         GROUP BY status";
            
            $statusBreakdown = $this->db->fetchAll($statusSql, $params);
            
            // Initialize status counts
            $stats['pending_count'] = 0;
            $stats['approved_count'] = 0;
            $stats['rejected_count'] = 0;
            $stats['completed_count'] = 0;
            
            foreach ($statusBreakdown as $status) {
                switch ($status['status']) {
                    case 'pending_line_manager':
                    case 'pending_md':
                    case 'pending_finance_manager':
                        $stats['pending_count'] += $status['count'];
                        break;
                    case 'approved_for_payment':
                    case 'paid':
                        $stats['approved_count'] += $status['count'];
                        break;
                    case 'rejected':
                        $stats['rejected_count'] = $status['count'];
                        break;
                    case 'completed':
                        $stats['completed_count'] = $status['count'];
                        break;
                }
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Calculate comprehensive statistics error: " . $e->getMessage());
            return [
                'total_count' => 0,
                'total_amount' => 0,
                'average_amount' => 0,
                'min_amount' => 0,
                'max_amount' => 0,
                'pending_count' => 0,
                'approved_count' => 0,
                'rejected_count' => 0,
                'completed_count' => 0
            ];
        }
    }
    
    /**
     * Get advanced analytics
     */
    private function getAdvancedAnalytics($userId, $filters) {
        try {
            $analytics = [];
            
            // Average approval time
            $approvalTimeSql = "SELECT AVG(TIMESTAMPDIFF(DAY, r.created_at, r.payment_date)) as avg_approval_days
                               FROM requisitions r
                               WHERE r.user_id = ? AND r.payment_date IS NOT NULL";
            
            $approvalParams = [$userId];
            $approvalTimeSql .= $this->buildFilterQuery($filters, $approvalParams);
            
            $approvalTime = $this->db->fetchOne($approvalTimeSql, $approvalParams);
            $analytics['avg_approval_days'] = round($approvalTime['avg_approval_days'] ?? 0, 1);
            
            // Most common category
            $categorySql = "SELECT COALESCE(rc.category_name, r.purpose) as category, COUNT(*) as count
                           FROM requisitions r
                           LEFT JOIN requisition_categories rc ON r.purpose = rc.category_name
                           WHERE r.user_id = ?";
            
            $categoryParams = [$userId];
            $categorySql .= $this->buildFilterQuery($filters, $categoryParams);
            $categorySql .= " GROUP BY category ORDER BY count DESC LIMIT 1";
            
            $category = $this->db->fetchOne($categorySql, $categoryParams);
            $analytics['most_common_category'] = $category['category'] ?? 'N/A';
            $analytics['most_common_category_count'] = $category['count'] ?? 0;
            
            // Busiest submission day
            $weekdayData = $this->getWeekdayDistribution($userId, $filters);
            $maxDay = array_search(max($weekdayData), $weekdayData);
            $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $analytics['busiest_day'] = $dayNames[$maxDay];
            
            // Busiest submission hour
            $hourlyData = $this->getHourlyDistribution($userId, $filters);
            $maxHour = array_search(max($hourlyData), $hourlyData);
            $startPeriod = $maxHour >= 12 ? 'PM' : 'AM';
            $startHour12 = $maxHour % 12;
            $startHour12 = $startHour12 == 0 ? 12 : $startHour12;
            $endHour = ($maxHour + 1) % 24;
            $endPeriod = $endHour >= 12 ? 'PM' : 'AM';
            $endHour12 = $endHour % 12;
            $endHour12 = $endHour12 == 0 ? 12 : $endHour12;
            $analytics['busiest_hour'] = sprintf('%02d:00 %s - %02d:00 %s', $startHour12, $startPeriod, $endHour12, $endPeriod);
            
            // Approval rate
            $totalCount = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM requisitions WHERE user_id = ?",
                [$userId]
            )['count'];
            
            $approvedCount = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM requisitions 
                 WHERE user_id = ? AND status IN ('completed', 'paid', 'approved_for_payment')",
                [$userId]
            )['count'];
            
            $analytics['approval_rate'] = $totalCount > 0 ? round(($approvedCount / $totalCount) * 100, 1) : 0;
            
            // Average requisition per month
            $monthsSql = "SELECT COUNT(DISTINCT DATE_FORMAT(created_at, '%Y-%m')) as months
                         FROM requisitions
                         WHERE user_id = ?";
            
            $monthsParams = [$userId];
            $monthsSql .= $this->buildFilterQuery($filters, $monthsParams);
            
            $months = $this->db->fetchOne($monthsSql, $monthsParams)['months'];
            
            $countSql = "SELECT COUNT(*) as count FROM requisitions WHERE user_id = ?";
            $countParams = [$userId];
            $countSql .= $this->buildFilterQuery($filters, $countParams);
            
            $count = $this->db->fetchOne($countSql, $countParams)['count'];
            
            $analytics['avg_per_month'] = $months > 0 ? round($count / $months, 1) : 0;
            
            return $analytics;
            
        } catch (Exception $e) {
            error_log("Get advanced analytics error: " . $e->getMessage());
            return [
                'avg_approval_days' => 0,
                'most_common_category' => 'N/A',
                'busiest_day' => 'N/A',
                'busiest_hour' => 'N/A',
                'approval_rate' => 0,
                'avg_per_month' => 0
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
        
        // Category filter
        if (!empty($filters['category'])) {
            $whereClauses[] = "r.purpose = ?";
            $params[] = $filters['category'];
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
            $whereClauses[] = "r.status != 'draft'";
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
<?php
/**
 * GateWey Requisition Management System
 * Report Class - Enhanced with Analytics & Category Filtering
 * 
 * File: classes/Report.php
 * Purpose: Handle report generation, statistics, and data filtering with chart support
 */

// Include permissions helper for role checking functions
if (!function_exists('is_managing_director')) {
    require_once __DIR__ . '/../helpers/permissions.php';
}

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
               rc.category_name,
               parent.category_name as parent_category_name,
               ca.first_name as current_approver_first_name,
               ca.last_name as current_approver_last_name,
               sa.first_name as selected_approver_first_name,
               sa.last_name as selected_approver_last_name
        FROM requisitions r
        LEFT JOIN departments d ON r.department_id = d.id
        LEFT JOIN requisition_categories rc ON r.purpose = rc.category_name
        LEFT JOIN requisition_categories parent ON rc.parent_id = parent.id
        LEFT JOIN users ca ON r.current_approver_id = ca.id
        LEFT JOIN users sa ON r.selected_approver_id = sa.id
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
     * Get requisition trends over time - ✅ UPDATED with status & category filters
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
            
            // ✅ ADD STATUS FILTER
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            // ✅ ADD CATEGORY FILTER
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
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
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get requisition trends error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get category distribution - ✅ UPDATED with status & category filters
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
            
            // ✅ ADD STATUS FILTER
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            // ✅ ADD CATEGORY FILTER (if filtering by specific category, this will show only that category)
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
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
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get category distribution error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get status distribution - ✅ UPDATED with category filter
     */
    private function getStatusDistribution($userId, $filters) {
        try {
            $sql = "SELECT r.status,
                           COUNT(*) as count,
                           SUM(r.total_amount) as total_amount
                    FROM requisitions r
                    WHERE r.user_id = ? AND r.status != 'draft'";
            
            $params = [$userId];
            
            // ✅ ADD STATUS FILTER (only if not filtering by all statuses)
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            // ✅ ADD CATEGORY FILTER
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
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
            
            return $this->db->fetchAll($sql, $params);
            
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
     * Get hourly distribution - ✅ UPDATED with status & category filters
     */
    private function getHourlyDistribution($userId, $filters) {
        try {
            $sql = "SELECT HOUR(r.created_at) as hour,
                           COUNT(*) as count
                    FROM requisitions r
                    WHERE r.user_id = ? AND r.status != 'draft'";
            
            $params = [$userId];
            
            // ✅ ADD STATUS FILTER
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            // ✅ ADD CATEGORY FILTER
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
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
            
            $result = $this->db->fetchAll($sql, $params);
            
            // Fill in missing hours with 0
            $hourlyData = array_fill(0, 24, 0);
            foreach ($result as $item) {
                $hourlyData[(int)$item['hour']] = (int)$item['count'];
            }
            
            return $hourlyData;
            
        } catch (Exception $e) {
            error_log("Get hourly distribution error: " . $e->getMessage());
            return array_fill(0, 24, 0);
        }
    }
    
    /**
     * Get weekday distribution - ✅ UPDATED with status & category filters
     */
    private function getWeekdayDistribution($userId, $filters) {
        try {
            $sql = "SELECT WEEKDAY(r.created_at) as weekday,
                           COUNT(*) as count
                    FROM requisitions r
                    WHERE r.user_id = ? AND r.status != 'draft'";
            
            $params = [$userId];
            
            // ✅ ADD STATUS FILTER
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            // ✅ ADD CATEGORY FILTER
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
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
            
            $result = $this->db->fetchAll($sql, $params);
            
            // Fill in missing days with 0
            $weekdayData = array_fill(0, 7, 0);
            foreach ($result as $item) {
                $weekdayData[(int)$item['weekday']] = (int)$item['count'];
            }
            
            return $weekdayData;
            
        } catch (Exception $e) {
            error_log("Get weekday distribution error: " . $e->getMessage());
            return array_fill(0, 7, 0);
        }
    }
    
    /**
     * Get monthly spending patterns - ✅ UPDATED with status & category filters
     */
    private function getMonthlySpending($userId, $filters) {
        try {
            // Base query - only paid/completed by default
            $sql = "SELECT DATE_FORMAT(r.created_at, '%b %Y') as month,
                           SUM(r.total_amount) as total_amount,
                           COUNT(*) as count
                    FROM requisitions r
                    WHERE r.user_id = ?";
            
            $params = [$userId];
            
            // ✅ HANDLE STATUS FILTER
            if (!empty($filters['status'])) {
                // If specific status selected, use it
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            } else {
                // Default: only paid/completed
                $sql .= " AND r.status IN (?, ?)";
                $params[] = STATUS_PAID;
                $params[] = STATUS_COMPLETED;
            }
            
            // ✅ ADD CATEGORY FILTER
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
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
            
            return $this->db->fetchAll($sql, $params);
            
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
            // Basic counts (all non-draft requisitions)
            $countSql = "SELECT COUNT(*) as total_count
                        FROM (" . $sql . ") as subquery";
            
            $countResult = $this->db->fetchOne($countSql, $params);
            
            // Amounts - ONLY for paid/completed requisitions
            $amountSql = "SELECT COALESCE(SUM(total_amount), 0) as total_amount,
                                COALESCE(AVG(total_amount), 0) as average_amount,
                                COALESCE(MIN(total_amount), 0) as min_amount,
                                COALESCE(MAX(total_amount), 0) as max_amount
                         FROM (" . $sql . ") as subquery
                         WHERE status IN (?, ?)";
            
            // Add STATUS_PAID and STATUS_COMPLETED to params for amount calculation
            $amountParams = array_merge($params, [STATUS_PAID, STATUS_COMPLETED]);
            $amountResult = $this->db->fetchOne($amountSql, $amountParams);
            
            // Status-specific counts
            $stats = [
                'total_count' => (int)$countResult['total_count'],
                'total_amount' => (float)$amountResult['total_amount'],
                'average_amount' => (float)$amountResult['average_amount'],
                'min_amount' => (float)$amountResult['min_amount'],
                'max_amount' => (float)$amountResult['max_amount']
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
     * Get advanced analytics metrics
     * 
     * @param int $userId User ID
     * @param array $filters Optional filters
     * @return array Advanced analytics
     */
    private function getAdvancedAnalytics($userId, $filters = []) {
        try {
            // Build WHERE clause
            $sql = "SELECT * FROM requisitions WHERE user_id = ? AND status != 'draft'";
            $params = [$userId];
            
            // Add date filters if present
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            // Add status filter if present
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            // Add category filter if present
            if (!empty($filters['category'])) {
                $sql .= " AND purpose = ?";
                $params[] = $filters['category'];
            }
            
            $requisitions = $this->db->fetchAll($sql, $params);
            
            if (empty($requisitions)) {
                return [
                    'avg_approval_days' => '0',
                    'most_common_category' => 'N/A',
                    'busiest_day' => 'N/A',
                    'busiest_hour' => 'N/A',
                    'approval_rate' => '0',
                    'avg_per_month' => '0'
                ];
            }
            
            // Calculate average approval time (for completed requisitions)
            $totalApprovalDays = 0;
            $completedCount = 0;
            $approvedCount = 0;
            $totalCount = count($requisitions);
            
            foreach ($requisitions as $req) {
                // Count as approved if status is completed, paid, or approved_for_payment
                if (in_array($req['status'], [STATUS_COMPLETED, STATUS_PAID, STATUS_APPROVED_FOR_PAYMENT])) {
                    $approvedCount++;
                    
                    // Calculate approval time for completed requisitions
                    if ($req['status'] == STATUS_COMPLETED && !empty($req['submitted_at'])) {
                        $submittedDate = new DateTime($req['submitted_at']);
                        
                        // Use payment_date if available, otherwise updated_at
                        $completedDate = !empty($req['payment_date']) 
                            ? new DateTime($req['payment_date'])
                            : new DateTime($req['updated_at']);
                        
                        $interval = $submittedDate->diff($completedDate);
                        $totalApprovalDays += $interval->days;
                        $completedCount++;
                    }
                }
            }
            
            $avgApprovalDays = $completedCount > 0 
                ? round($totalApprovalDays / $completedCount, 1) 
                : 0;
            
            // Calculate approval rate (percentage of approved vs total)
            $approvalRate = $totalCount > 0 
                ? round(($approvedCount / $totalCount) * 100, 1) 
                : 0;
            
            // Get most common category
            $sql = "SELECT purpose as category, COUNT(*) as count 
                    FROM requisitions 
                    WHERE user_id = ? AND status != 'draft'";
            $params = [$userId];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND purpose = ?";
                $params[] = $filters['category'];
            }
            
            $sql .= " GROUP BY purpose ORDER BY count DESC LIMIT 1";
            
            $topCategory = $this->db->fetchOne($sql, $params);
            $mostCommonCategory = $topCategory ? $topCategory['category'] : 'N/A';
            
            // Truncate long category names
            if (strlen($mostCommonCategory) > 30) {
                $mostCommonCategory = substr($mostCommonCategory, 0, 27) . '...';
            }
            
            // Get busiest day of week
            $sql = "SELECT DAYOFWEEK(created_at) - 1 as day_index, COUNT(*) as count
                    FROM requisitions 
                    WHERE user_id = ? AND status != 'draft'";
            $params = [$userId];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND purpose = ?";
                $params[] = $filters['category'];
            }
            
            $sql .= " GROUP BY day_index ORDER BY count DESC LIMIT 1";
            
            $busiestDayData = $this->db->fetchOne($sql, $params);
            
            if ($busiestDayData) {
                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                $busiestDay = $days[$busiestDayData['day_index']];
            } else {
                $busiestDay = 'N/A';
            }
            
            // Get busiest hour
            $sql = "SELECT HOUR(created_at) as hour, COUNT(*) as count
                    FROM requisitions 
                    WHERE user_id = ? AND status != 'draft'";
            $params = [$userId];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND purpose = ?";
                $params[] = $filters['category'];
            }
            
            $sql .= " GROUP BY hour ORDER BY count DESC LIMIT 1";
            
            $busiestHourData = $this->db->fetchOne($sql, $params);
            
            if ($busiestHourData) {
                $hour = (int)$busiestHourData['hour'];
                // Format as 12-hour time
                if ($hour == 0) {
                    $busiestHour = '12:00 AM';
                } elseif ($hour < 12) {
                    $busiestHour = $hour . ':00 AM';
                } elseif ($hour == 12) {
                    $busiestHour = '12:00 PM';
                } else {
                    $busiestHour = ($hour - 12) . ':00 PM';
                }
            } else {
                $busiestHour = 'N/A';
            }
            
            // Calculate average per month
            $sql = "SELECT MIN(DATE(created_at)) as first_date, 
                           MAX(DATE(created_at)) as last_date,
                           COUNT(*) as total_count
                    FROM requisitions 
                    WHERE user_id = ? AND status != 'draft'";
            $params = [$userId];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND purpose = ?";
                $params[] = $filters['category'];
            }
            
            $dateRange = $this->db->fetchOne($sql, $params);
            
            if ($dateRange && $dateRange['first_date'] && $dateRange['last_date']) {
                $firstDate = new DateTime($dateRange['first_date']);
                $lastDate = new DateTime($dateRange['last_date']);
                $interval = $firstDate->diff($lastDate);
                
                // Calculate months (including partial months)
                $months = ($interval->y * 12) + $interval->m;
                if ($interval->d > 0) {
                    $months += 1;
                }
                
                // Ensure at least 1 month
                $months = max(1, $months);
                
                $avgPerMonth = round($dateRange['total_count'] / $months, 1);
            } else {
                $avgPerMonth = $totalCount;
            }
            
            return [
                'avg_approval_days' => $avgApprovalDays,
                'most_common_category' => $mostCommonCategory,
                'busiest_day' => $busiestDay,
                'busiest_hour' => $busiestHour,
                'approval_rate' => $approvalRate,
                'avg_per_month' => $avgPerMonth
            ];
            
        } catch (Exception $e) {
            error_log("Error calculating advanced analytics: " . $e->getMessage());
            return [
                'avg_approval_days' => '0',
                'most_common_category' => 'N/A',
                'busiest_day' => 'N/A',
                'busiest_hour' => 'N/A',
                'approval_rate' => '0',
                'avg_per_month' => '0'
            ];
        }
    }

    /**
     * Generate department report with enhanced analytics
     */
    public function generateDepartmentReport($filters = [], $page = 1, $perPage = 15) {
        try {
            $userId = Session::getUserId();
            $departmentId = Session::getUserDepartmentId();
            
            if (!$departmentId) {
                return [
                    'success' => false,
                    'message' => 'User department not found'
                ];
            }
            
            // Build base query
$sql = "SELECT r.*,
               u.first_name as requester_first_name,
               u.last_name as requester_last_name,
               u.email as requester_email,
               d.department_name,
               rc.category_name,
               parent.category_name as parent_category_name,
               ca.first_name as current_approver_first_name,
               ca.last_name as current_approver_last_name,
               sa.first_name as selected_approver_first_name,
               sa.last_name as selected_approver_last_name
        FROM requisitions r
        INNER JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        LEFT JOIN requisition_categories rc ON r.purpose = rc.category_name
        LEFT JOIN requisition_categories parent ON rc.parent_id = parent.id
        LEFT JOIN users ca ON r.current_approver_id = ca.id
        LEFT JOIN users sa ON r.selected_approver_id = sa.id
        WHERE u.department_id = ?";
            
            $params = [$departmentId];
            
            // Add filters
            $sql .= $this->buildFilterQuery($filters, $params);
            
            // Calculate statistics
            $statistics = $this->calculateDepartmentStatistics($departmentId, $filters);
            
            // Get chart data
            $chartData = [
                'trends' => $this->getDepartmentTrends($departmentId, $filters),
                'status' => $this->getStatusDistribution($userId, $filters),
                'top_users' => $this->getTopRequesters($departmentId, $filters),
                'monthly' => $this->getDepartmentMonthlySpending($departmentId, $filters),
                'hourly' => $this->getDepartmentHourlyDistribution($departmentId, $filters),
                'weekday' => $this->getDepartmentWeekdayDistribution($departmentId, $filters)
            ];
            
            // Get advanced analytics
            $analytics = $this->getDepartmentAnalytics($departmentId, $filters);
            
            // Get paginated requisitions
            $offset = ($page - 1) * $perPage;
            $paginatedSql = $sql . " ORDER BY r.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $requisitions = $this->db->fetchAll($paginatedSql, $params);
            
            // Get total count for pagination
            $countSql = "SELECT COUNT(*) as total 
                         FROM requisitions r
                         INNER JOIN users u ON r.user_id = u.id
                         WHERE u.department_id = ?";
            $countParams = [$departmentId];
            $countSql .= $this->buildFilterQuery($filters, $countParams);
            $totalCount = $this->db->fetchOne($countSql, $countParams)['total'];
            
            return [
                'success' => true,
                'statistics' => $statistics,
                'requisitions' => $requisitions,
                'chart_data' => $chartData,
                'analytics' => $analytics,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_records' => $totalCount,
                    'total_pages' => ceil($totalCount / $perPage)
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Generate department report error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calculate department statistics
     */
    private function calculateDepartmentStatistics($departmentId, $filters = []) {
        try {
            // Count all non-draft requisitions
            $countSql = "SELECT COUNT(*) as total_count
                        FROM requisitions r
                        INNER JOIN users u ON r.user_id = u.id
                        WHERE u.department_id = ? AND r.status != ?";
            
            $countParams = [$departmentId, STATUS_DRAFT];
            
            if (!empty($filters['date_from'])) {
                $countSql .= " AND DATE(r.created_at) >= ?";
                $countParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $countSql .= " AND DATE(r.created_at) <= ?";
                $countParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['status'])) {
                $countSql .= " AND r.status = ?";
                $countParams[] = $filters['status'];
            }
            
            if (!empty($filters['user_id'])) {
                $countSql .= " AND r.user_id = ?";
                $countParams[] = $filters['user_id'];
            }
            
            if (!empty($filters['category'])) {
                $countSql .= " AND r.purpose = ?";
                $countParams[] = $filters['category'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $countSql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $countParams[] = $dateRange['start'];
                    $countParams[] = $dateRange['end'];
                }
            }
            
            $countResult = $this->db->fetchOne($countSql, $countParams);
            
            // Amounts - ONLY for paid/completed requisitions
            $amountSql = "SELECT 
                            COALESCE(SUM(r.total_amount), 0) as total_amount,
                            COALESCE(AVG(r.total_amount), 0) as average_amount,
                            COALESCE(MIN(r.total_amount), 0) as min_amount,
                            COALESCE(MAX(r.total_amount), 0) as max_amount
                        FROM requisitions r
                        INNER JOIN users u ON r.user_id = u.id
                        WHERE u.department_id = ? 
                        AND r.status IN (?, ?)";
            
            $amountParams = [$departmentId, STATUS_PAID, STATUS_COMPLETED];
            
            if (!empty($filters['date_from'])) {
                $amountSql .= " AND DATE(r.created_at) >= ?";
                $amountParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $amountSql .= " AND DATE(r.created_at) <= ?";
                $amountParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['user_id'])) {
                $amountSql .= " AND r.user_id = ?";
                $amountParams[] = $filters['user_id'];
            }
            
            if (!empty($filters['category'])) {
                $amountSql .= " AND r.purpose = ?";
                $amountParams[] = $filters['category'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $amountSql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $amountParams[] = $dateRange['start'];
                    $amountParams[] = $dateRange['end'];
                }
            }
            
            $amountResult = $this->db->fetchOne($amountSql, $amountParams);
            
            // Status counts
            $statusSql = "SELECT 
                            SUM(CASE WHEN r.status = ? THEN 1 ELSE 0 END) as completed_count,
                            SUM(CASE WHEN r.status IN (?, ?, ?) THEN 1 ELSE 0 END) as pending_count,
                            SUM(CASE WHEN r.status = ? THEN 1 ELSE 0 END) as rejected_count
                        FROM requisitions r
                        INNER JOIN users u ON r.user_id = u.id
                        WHERE u.department_id = ? AND r.status != ?";
            
            $statusParams = [
                STATUS_COMPLETED,
                STATUS_PENDING_LINE_MANAGER, 
                STATUS_PENDING_MD, 
                STATUS_PENDING_FINANCE_MANAGER,
                STATUS_REJECTED,
                $departmentId,
                STATUS_DRAFT
            ];
            
            if (!empty($filters['date_from'])) {
                $statusSql .= " AND DATE(r.created_at) >= ?";
                $statusParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $statusSql .= " AND DATE(r.created_at) <= ?";
                $statusParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['user_id'])) {
                $statusSql .= " AND r.user_id = ?";
                $statusParams[] = $filters['user_id'];
            }
            
            if (!empty($filters['category'])) {
                $statusSql .= " AND r.purpose = ?";
                $statusParams[] = $filters['category'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $statusSql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $statusParams[] = $dateRange['start'];
                    $statusParams[] = $dateRange['end'];
                }
            }
            
            $statusResult = $this->db->fetchOne($statusSql, $statusParams);
            
            return [
                'total_count' => (int)$countResult['total_count'],
                'total_amount' => (float)$amountResult['total_amount'],
                'average_amount' => (float)$amountResult['average_amount'],
                'min_amount' => (float)$amountResult['min_amount'],
                'max_amount' => (float)$amountResult['max_amount'],
                'completed_count' => (int)$statusResult['completed_count'],
                'pending_count' => (int)$statusResult['pending_count'],
                'rejected_count' => (int)$statusResult['rejected_count']
            ];
            
        } catch (Exception $e) {
            error_log("Calculate department statistics error: " . $e->getMessage());
            return [
                'total_count' => 0,
                'total_amount' => 0,
                'average_amount' => 0,
                'min_amount' => 0,
                'max_amount' => 0,
                'completed_count' => 0,
                'pending_count' => 0,
                'rejected_count' => 0
            ];
        }
    }

    /**
     * Get department requisition trends - ✅ UPDATED with status & category filters
     */
    private function getDepartmentTrends($departmentId, $filters = []) {
        try {
            $interval = $filters['interval'] ?? 'daily';
            
            switch ($interval) {
                case 'weekly':
                    $dateFormat = '%Y-W%u';
                    break;
                case 'monthly':
                    $dateFormat = '%Y-%m';
                    break;
                default:
                    $dateFormat = '%Y-%m-%d';
                    break;
            }
            
            $sql = "SELECT 
                        DATE_FORMAT(r.created_at, '{$dateFormat}') as time_period,
                        COUNT(*) as count,
                        SUM(r.total_amount) as total_amount
                    FROM requisitions r
                    INNER JOIN users u ON r.user_id = u.id
                    WHERE u.department_id = ? AND r.status != 'draft'";
            
            $params = [$departmentId];
            
            // ✅ ADD STATUS FILTER
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            // ✅ ADD CATEGORY FILTER
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['user_id'])) {
                $sql .= " AND r.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            $sql .= " GROUP BY time_period ORDER BY r.created_at ASC";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get department trends error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top requesters in department - ✅ UPDATED with status & category filters
     */
    private function getTopRequesters($departmentId, $filters = []) {
        try {
            $sql = "SELECT 
                        u.id,
                        CONCAT(u.first_name, ' ', u.last_name) as user_name,
                        COUNT(r.id) as count,
                        SUM(r.total_amount) as total_amount
                    FROM users u
                    INNER JOIN requisitions r ON u.id = r.user_id
                    WHERE u.department_id = ? AND r.status != 'draft'";
            
            $params = [$departmentId];
            
            // ✅ ADD STATUS FILTER
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            // ✅ ADD CATEGORY FILTER
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            $sql .= " GROUP BY u.id, user_name ORDER BY count DESC LIMIT 10";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get top requesters error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get monthly spending for department - ✅ UPDATED with status & category filters
     */
    private function getDepartmentMonthlySpending($departmentId, $filters = []) {
        try {
            $sql = "SELECT 
                        DATE_FORMAT(r.created_at, '%b %Y') as month,
                        SUM(r.total_amount) as total_amount,
                        COUNT(*) as count
                    FROM requisitions r
                    INNER JOIN users u ON r.user_id = u.id
                    WHERE u.department_id = ? 
                    AND r.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
            
            $params = [$departmentId];
            
            // ✅ HANDLE STATUS FILTER
            if (!empty($filters['status'])) {
                // If specific status selected, use it
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            } else {
                // Default: only paid/completed
                $sql .= " AND r.status IN (?, ?)";
                $params[] = STATUS_PAID;
                $params[] = STATUS_COMPLETED;
            }
            
            // ✅ ADD CATEGORY FILTER
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['user_id'])) {
                $sql .= " AND r.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            $sql .= " GROUP BY DATE_FORMAT(r.created_at, '%Y-%m'), month 
                      ORDER BY DATE_FORMAT(r.created_at, '%Y-%m') ASC";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get monthly spending error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get hourly distribution for department - ✅ UPDATED with status & category filters
     */
    private function getDepartmentHourlyDistribution($departmentId, $filters = []) {
        try {
            $sql = "SELECT 
                        HOUR(r.created_at) as hour,
                        COUNT(*) as count
                    FROM requisitions r
                    INNER JOIN users u ON r.user_id = u.id
                    WHERE u.department_id = ? AND r.status != 'draft'";
            
            $params = [$departmentId];
            
            // ✅ ADD STATUS FILTER
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            // ✅ ADD CATEGORY FILTER
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['user_id'])) {
                $sql .= " AND r.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            $sql .= " GROUP BY hour ORDER BY hour ASC";
            
            $results = $this->db->fetchAll($sql, $params);
            
            // Initialize 24-hour array
            $hourly = array_fill(0, 24, 0);
            
            // Fill in actual counts
            foreach ($results as $row) {
                $hourly[(int)$row['hour']] = (int)$row['count'];
            }
            
            return $hourly;
            
        } catch (Exception $e) {
            error_log("Get hourly distribution error: " . $e->getMessage());
            return array_fill(0, 24, 0);
        }
    }

    /**
     * Get weekday distribution for department - ✅ UPDATED with status & category filters
     */
    private function getDepartmentWeekdayDistribution($departmentId, $filters = []) {
        try {
            $sql = "SELECT 
                        WEEKDAY(r.created_at) as weekday,
                        COUNT(*) as count
                    FROM requisitions r
                    INNER JOIN users u ON r.user_id = u.id
                    WHERE u.department_id = ? AND r.status != 'draft'";
            
            $params = [$departmentId];
            
            // ✅ ADD STATUS FILTER
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            // ✅ ADD CATEGORY FILTER
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['user_id'])) {
                $sql .= " AND r.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            $sql .= " GROUP BY weekday ORDER BY weekday ASC";
            
            $results = $this->db->fetchAll($sql, $params);
            
            // Initialize 7-day array (Monday=0, ..., Sunday=6)
            $weekdays = array_fill(0, 7, 0);
            
            // Fill in actual counts
            foreach ($results as $row) {
                $weekdays[(int)$row['weekday']] = (int)$row['count'];
            }
            
            return $weekdays;
            
        } catch (Exception $e) {
            error_log("Get weekday distribution error: " . $e->getMessage());
            return array_fill(0, 7, 0);
        }
    }

    /**
     * Get department analytics metrics
     */
    private function getDepartmentAnalytics($departmentId, $filters = []) {
        try {
            $sql = "SELECT r.* 
                    FROM requisitions r
                    INNER JOIN users u ON r.user_id = u.id
                    WHERE u.department_id = ? AND r.status != 'draft'";
            
            $params = [$departmentId];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['user_id'])) {
                $sql .= " AND r.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            $requisitions = $this->db->fetchAll($sql, $params);
            
            if (empty($requisitions)) {
                return [
                    'avg_approval_days' => '0',
                    'top_requester' => 'N/A',
                    'busiest_day' => 'N/A',
                    'busiest_hour' => 'N/A',
                    'approval_rate' => '0'
                ];
            }
            
            // Calculate metrics (same as personal but for department)
            $totalApprovalDays = 0;
            $completedCount = 0;
            $approvedCount = 0;
            $totalCount = count($requisitions);
            
            foreach ($requisitions as $req) {
                if (in_array($req['status'], ['completed', 'paid', 'approved_for_payment'])) {
                    $approvedCount++;
                    
                    if ($req['status'] == 'completed' && !empty($req['submitted_at'])) {
                        $submittedDate = new DateTime($req['submitted_at']);
                        $completedDate = !empty($req['payment_date']) 
                            ? new DateTime($req['payment_date'])
                            : new DateTime($req['updated_at']);
                        
                        $interval = $submittedDate->diff($completedDate);
                        $totalApprovalDays += $interval->days;
                        $completedCount++;
                    }
                }
            }
            
            $avgApprovalDays = $completedCount > 0 
                ? round($totalApprovalDays / $completedCount, 1) 
                : 0;
            
            $approvalRate = $totalCount > 0 
                ? round(($approvedCount / $totalCount) * 100, 1) 
                : 0;
            
            // Get top requester
            $sql = "SELECT 
                        CONCAT(u.first_name, ' ', u.last_name) as name,
                        COUNT(r.id) as count
                    FROM users u
                    INNER JOIN requisitions r ON u.id = r.user_id
                    WHERE u.department_id = ? AND r.status != 'draft'";
            
            $topReqParams = [$departmentId];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $topReqParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $topReqParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $topReqParams[] = $filters['category'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $topReqParams[] = $dateRange['start'];
                    $topReqParams[] = $dateRange['end'];
                }
            }
            
            $sql .= " GROUP BY u.id, name ORDER BY count DESC LIMIT 1";
            
            $topRequester = $this->db->fetchOne($sql, $topReqParams);
            $topRequesterName = $topRequester ? $topRequester['name'] : 'N/A';
            
            // Get busiest day
            $sql = "SELECT WEEKDAY(r.created_at) as weekday, COUNT(*) as count
                    FROM requisitions r
                    INNER JOIN users u ON r.user_id = u.id
                    WHERE u.department_id = ? AND r.status != 'draft'";
            
            $dayParams = [$departmentId];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $dayParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $dayParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $dayParams[] = $filters['category'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $dayParams[] = $dateRange['start'];
                    $dayParams[] = $dateRange['end'];
                }
            }
            
            $sql .= " GROUP BY weekday ORDER BY count DESC LIMIT 1";
            
            $busiestDayData = $this->db->fetchOne($sql, $dayParams);
            
            if ($busiestDayData) {
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                $busiestDay = $days[$busiestDayData['weekday']];
            } else {
                $busiestDay = 'N/A';
            }
            
            // Get busiest hour
            $sql = "SELECT HOUR(r.created_at) as hour, COUNT(*) as count
                    FROM requisitions r
                    INNER JOIN users u ON r.user_id = u.id
                    WHERE u.department_id = ? AND r.status != 'draft'";
            
            $hourParams = [$departmentId];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $hourParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $hourParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $hourParams[] = $filters['category'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $hourParams[] = $dateRange['start'];
                    $hourParams[] = $dateRange['end'];
                }
            }
            
            $sql .= " GROUP BY hour ORDER BY count DESC LIMIT 1";
            
            $busiestHourData = $this->db->fetchOne($sql, $hourParams);
            
            if ($busiestHourData) {
                $hour = (int)$busiestHourData['hour'];
                if ($hour == 0) {
                    $busiestHour = '12:00 AM';
                } elseif ($hour < 12) {
                    $busiestHour = $hour . ':00 AM';
                } elseif ($hour == 12) {
                    $busiestHour = '12:00 PM';
                } else {
                    $busiestHour = ($hour - 12) . ':00 PM';
                }
            } else {
                $busiestHour = 'N/A';
            }
            
            return [
                'avg_approval_days' => $avgApprovalDays,
                'top_requester' => $topRequesterName,
                'busiest_day' => $busiestDay,
                'busiest_hour' => $busiestHour,
                'approval_rate' => $approvalRate
            ];
            
        } catch (Exception $e) {
            error_log("Get department analytics error: " . $e->getMessage());
            return [
                'avg_approval_days' => '0',
                'top_requester' => 'N/A',
                'busiest_day' => 'N/A',
                'busiest_hour' => 'N/A',
                'approval_rate' => '0'
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
            // Verify permission - allow MD, Finance Manager, and Finance Member
            if (!is_managing_director() && !is_finance_manager() && !is_finance_member()) {
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
               d.department_code,
               rc.category_name,
               parent.category_name as parent_category_name,
               ca.first_name as current_approver_first_name,
               ca.last_name as current_approver_last_name,
               sa.first_name as selected_approver_first_name,
               sa.last_name as selected_approver_last_name
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        LEFT JOIN requisition_categories rc ON r.purpose = rc.category_name
        LEFT JOIN requisition_categories parent ON rc.parent_id = parent.id
        LEFT JOIN users ca ON r.current_approver_id = ca.id
        LEFT JOIN users sa ON r.selected_approver_id = sa.id
        WHERE 1=1";
            
            $params = [];
            
            // Apply filters
            $sql .= $this->buildFilterQuery($filters, $params);
            
            // Get enhanced statistics
            $stats = $this->calculateOrganizationStatistics($filters);
            
            // Get chart data
            $chartData = [
                'trends' => $this->getOrganizationTrends($filters),
                'departments' => $this->getDepartmentComparison($filters),
                'status' => $this->getOrganizationStatusDistribution($filters),
                'top_requesters' => $this->getOrganizationTopRequesters($filters),
                'categories' => $this->getOrganizationCategories($filters),
                'monthly' => $this->getOrganizationMonthlySpending($filters),
                'hourly' => $this->getOrganizationHourlyDistribution($filters),
                'weekday' => $this->getOrganizationWeekdayDistribution($filters)
            ];
            
            // Get advanced analytics
            $analytics = $this->getOrganizationAnalytics($filters);
            
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
            error_log("Generate organization report error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while generating the report.'
            ];
        }
    }
    
    /**
     * Build filter query - ✅ FIXED category column name
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
        
        // ✅ FIXED - Category filter (use r.purpose, not r.category_name)
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
            // Count all non-draft requisitions per department
            $sql = "SELECT d.department_name,
                           d.department_code,
                           d.id as department_id,
                           COUNT(r.id) as requisition_count
                    FROM departments d
                    LEFT JOIN requisitions r ON d.id = r.department_id 
                        AND r.status != ?";
            
            $params = [STATUS_DRAFT];
            $whereClauses = [];
            
            // Apply date filters
            if (!empty($filters['date_from'])) {
                $whereClauses[] = "DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereClauses[] = "DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($whereClauses)) {
                $sql .= " AND " . implode(' AND ', $whereClauses);
            }
            
            $sql .= " GROUP BY d.id, d.department_name, d.department_code";
            
            $departments = $this->db->fetchAll($sql, $params);
            
            // Now get amounts - ONLY for paid/completed requisitions
            $result = [];
            foreach ($departments as $dept) {
                $amountSql = "SELECT COALESCE(SUM(r.total_amount), 0) as total_amount
                             FROM requisitions r
                             WHERE r.department_id = ?
                             AND r.status IN (?, ?)";
                
                $amountParams = [$dept['department_id'], STATUS_PAID, STATUS_COMPLETED];
                
                // Apply same date filters to amounts
                if (!empty($filters['date_from'])) {
                    $amountSql .= " AND DATE(r.created_at) >= ?";
                    $amountParams[] = $filters['date_from'];
                }
                
                if (!empty($filters['date_to'])) {
                    $amountSql .= " AND DATE(r.created_at) <= ?";
                    $amountParams[] = $filters['date_to'];
                }
                
                $amountResult = $this->db->fetchOne($amountSql, $amountParams);
                
                $result[] = [
                    'department_name' => $dept['department_name'],
                    'department_code' => $dept['department_code'],
                    'requisition_count' => (int)$dept['requisition_count'],
                    'total_amount' => (float)$amountResult['total_amount']
                ];
            }
            
            // Sort by total amount descending
            usort($result, function($a, $b) {
                return $b['total_amount'] <=> $a['total_amount'];
            });
            
            return $result;
            
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

    /**
     * ========================================================================
     * ORGANIZATION-WIDE REPORTING METHODS - ✅ ALL UPDATED WITH FILTERS
     * ========================================================================
     */

    /**
     * Calculate organization-wide statistics with enhanced metrics
     */
    private function calculateOrganizationStatistics($filters = []) {
        try {
            // Count all non-draft requisitions
            $countSql = "SELECT COUNT(*) as total_count
                        FROM requisitions r
                        WHERE r.status != ?";
            
            $countParams = [STATUS_DRAFT];
            
            if (!empty($filters['date_from'])) {
                $countSql .= " AND DATE(r.created_at) >= ?";
                $countParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $countSql .= " AND DATE(r.created_at) <= ?";
                $countParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $countSql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $countParams[] = $dateRange['start'];
                    $countParams[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['status'])) {
                $countSql .= " AND r.status = ?";
                $countParams[] = $filters['status'];
            }
            
            if (!empty($filters['department_id'])) {
                $countSql .= " AND r.department_id = ?";
                $countParams[] = $filters['department_id'];
            }
            
            if (!empty($filters['user_id'])) {
                $countSql .= " AND r.user_id = ?";
                $countParams[] = $filters['user_id'];
            }
            
            if (!empty($filters['category'])) {
                $countSql .= " AND r.purpose = ?";
                $countParams[] = $filters['category'];
            }
            
            $countResult = $this->db->fetchOne($countSql, $countParams);
            
            // Amounts - ONLY for paid/completed requisitions
            $amountSql = "SELECT 
                            COALESCE(SUM(r.total_amount), 0) as total_amount,
                            COALESCE(AVG(r.total_amount), 0) as average_amount,
                            COALESCE(MIN(r.total_amount), 0) as min_amount,
                            COALESCE(MAX(r.total_amount), 0) as max_amount
                        FROM requisitions r
                        WHERE r.status IN (?, ?)";
            
            $amountParams = [STATUS_PAID, STATUS_COMPLETED];
            
            if (!empty($filters['date_from'])) {
                $amountSql .= " AND DATE(r.created_at) >= ?";
                $amountParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $amountSql .= " AND DATE(r.created_at) <= ?";
                $amountParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $amountSql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $amountParams[] = $dateRange['start'];
                    $amountParams[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['department_id'])) {
                $amountSql .= " AND r.department_id = ?";
                $amountParams[] = $filters['department_id'];
            }
            
            if (!empty($filters['user_id'])) {
                $amountSql .= " AND r.user_id = ?";
                $amountParams[] = $filters['user_id'];
            }
            
            if (!empty($filters['category'])) {
                $amountSql .= " AND r.purpose = ?";
                $amountParams[] = $filters['category'];
            }
            
            $amountResult = $this->db->fetchOne($amountSql, $amountParams);
            
            // Status counts
            $statusSql = "SELECT 
                            SUM(CASE WHEN r.status = ? THEN 1 ELSE 0 END) as completed_count,
                            SUM(CASE WHEN r.status IN (?, ?, ?) THEN 1 ELSE 0 END) as pending_count,
                            SUM(CASE WHEN r.status = ? THEN 1 ELSE 0 END) as rejected_count
                        FROM requisitions r
                        WHERE r.status != ?";
            
            $statusParams = [
                STATUS_COMPLETED,
                STATUS_PENDING_LINE_MANAGER,
                STATUS_PENDING_MD,
                STATUS_PENDING_FINANCE_MANAGER,
                STATUS_REJECTED,
                STATUS_DRAFT
            ];
            
            if (!empty($filters['date_from'])) {
                $statusSql .= " AND DATE(r.created_at) >= ?";
                $statusParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $statusSql .= " AND DATE(r.created_at) <= ?";
                $statusParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $statusSql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $statusParams[] = $dateRange['start'];
                    $statusParams[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['department_id'])) {
                $statusSql .= " AND r.department_id = ?";
                $statusParams[] = $filters['department_id'];
            }
            
            if (!empty($filters['user_id'])) {
                $statusSql .= " AND r.user_id = ?";
                $statusParams[] = $filters['user_id'];
            }
            
            if (!empty($filters['category'])) {
                $statusSql .= " AND r.purpose = ?";
                $statusParams[] = $filters['category'];
            }
            
            $statusResult = $this->db->fetchOne($statusSql, $statusParams);
            
            return [
                'total_count' => (int)$countResult['total_count'],
                'total_amount' => (float)$amountResult['total_amount'],
                'average_amount' => (float)$amountResult['average_amount'],
                'min_amount' => (float)$amountResult['min_amount'],
                'max_amount' => (float)$amountResult['max_amount'],
                'completed_count' => (int)$statusResult['completed_count'],
                'pending_count' => (int)$statusResult['pending_count'],
                'rejected_count' => (int)$statusResult['rejected_count']
            ];
            
        } catch (Exception $e) {
            error_log("Calculate organization statistics error: " . $e->getMessage());
            return [
                'total_count' => 0,
                'total_amount' => 0,
                'average_amount' => 0,
                'min_amount' => 0,
                'max_amount' => 0,
                'completed_count' => 0,
                'pending_count' => 0,
                'rejected_count' => 0
            ];
        }
    }

    /**
     * Get organization-wide requisition trends - ✅ UPDATED with filters
     */
    private function getOrganizationTrends($filters = []) {
        try {
            $interval = $filters['interval'] ?? 'daily';
            
            switch ($interval) {
                case 'weekly':
                    $dateFormat = '%Y-W%u';
                    break;
                case 'monthly':
                    $dateFormat = '%Y-%m';
                    break;
                default:
                    $dateFormat = '%Y-%m-%d';
                    break;
            }
            
            // Count all non-draft requisitions
            $countSql = "SELECT 
                            DATE_FORMAT(r.created_at, '{$dateFormat}') as time_period,
                            COUNT(*) as count
                        FROM requisitions r
                        WHERE r.status != ?";
            
            $countParams = [STATUS_DRAFT];
            
            if (!empty($filters['status'])) {
                $countSql .= " AND r.status = ?";
                $countParams[] = $filters['status'];
            }
            
            if (!empty($filters['category'])) {
                $countSql .= " AND r.purpose = ?";
                $countParams[] = $filters['category'];
            }
            
            if (!empty($filters['date_from'])) {
                $countSql .= " AND DATE(r.created_at) >= ?";
                $countParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $countSql .= " AND DATE(r.created_at) <= ?";
                $countParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $countSql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $countParams[] = $dateRange['start'];
                    $countParams[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['department_id'])) {
                $countSql .= " AND r.department_id = ?";
                $countParams[] = $filters['department_id'];
            }
            
            $countSql .= " GROUP BY time_period ORDER BY r.created_at ASC";
            
            $countData = $this->db->fetchAll($countSql, $countParams);
            
            // Get amounts - ONLY for paid/completed requisitions
            $amountSql = "SELECT 
                            DATE_FORMAT(r.created_at, '{$dateFormat}') as time_period,
                            SUM(r.total_amount) as total_amount
                        FROM requisitions r
                        WHERE r.status IN (?, ?)";
            
            $amountParams = [STATUS_PAID, STATUS_COMPLETED];
            
            if (!empty($filters['category'])) {
                $amountSql .= " AND r.purpose = ?";
                $amountParams[] = $filters['category'];
            }
            
            if (!empty($filters['date_from'])) {
                $amountSql .= " AND DATE(r.created_at) >= ?";
                $amountParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $amountSql .= " AND DATE(r.created_at) <= ?";
                $amountParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $amountSql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $amountParams[] = $dateRange['start'];
                    $amountParams[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['department_id'])) {
                $amountSql .= " AND r.department_id = ?";
                $amountParams[] = $filters['department_id'];
            }
            
            $amountSql .= " GROUP BY time_period ORDER BY r.created_at ASC";
            
            $amountData = $this->db->fetchAll($amountSql, $amountParams);
            
            // Merge count and amount data
            $result = [];
            foreach ($countData as $count) {
                $amount = 0;
                foreach ($amountData as $amt) {
                    if ($amt['time_period'] === $count['time_period']) {
                        $amount = (float)$amt['total_amount'];
                        break;
                    }
                }
                
                $result[] = [
                    'time_period' => $count['time_period'],
                    'count' => (int)$count['count'],
                    'total_amount' => $amount
                ];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Get organization trends error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get department comparison data - ✅ UPDATED with filters
     */
    private function getDepartmentComparison($filters = []) {
        try {
            $sql = "SELECT 
                        d.department_name,
                        d.department_code,
                        COUNT(r.id) as count,
                        SUM(r.total_amount) as total_amount
                    FROM departments d
                    LEFT JOIN requisitions r ON d.id = r.department_id AND r.status != 'draft'";
            
            $params = [];
            $whereClauses = [];
            
            if (!empty($filters['status'])) {
                $whereClauses[] = "r.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['category'])) {
                $whereClauses[] = "r.purpose = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['date_from'])) {
                $whereClauses[] = "DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereClauses[] = "DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $whereClauses[] = "DATE(r.created_at) >= ?";
                    $whereClauses[] = "DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }
            
            $sql .= " GROUP BY d.id, d.department_name, d.department_code 
                     ORDER BY total_amount DESC LIMIT 10";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get department comparison error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get organization-wide status distribution - ✅ UPDATED with filters
     */
    private function getOrganizationStatusDistribution($filters = []) {
        try {
            $sql = "SELECT 
                        r.status,
                        COUNT(*) as count,
                        SUM(r.total_amount) as total_amount
                    FROM requisitions r
                    WHERE r.status != 'draft'";
            
            $params = [];
            
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['department_id'])) {
                $sql .= " AND r.department_id = ?";
                $params[] = $filters['department_id'];
            }
            
            $sql .= " GROUP BY r.status ORDER BY count DESC";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get organization status distribution error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top requesters across organization - ✅ UPDATED with filters
     */
    private function getOrganizationTopRequesters($filters = []) {
        try {
            $sql = "SELECT 
                        u.id,
                        CONCAT(u.first_name, ' ', u.last_name) as user_name,
                        d.department_name,
                        COUNT(r.id) as count,
                        SUM(r.total_amount) as total_amount
                    FROM users u
                    INNER JOIN requisitions r ON u.id = r.user_id
                    LEFT JOIN departments d ON u.department_id = d.id
                    WHERE r.status != 'draft'";
            
            $params = [];
            
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['department_id'])) {
                $sql .= " AND u.department_id = ?";
                $params[] = $filters['department_id'];
            }
            
            $sql .= " GROUP BY u.id, user_name, d.department_name 
                     ORDER BY count DESC LIMIT 10";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get organization top requesters error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get organization-wide category distribution - ✅ UPDATED with filters
     */
    private function getOrganizationCategories($filters = []) {
        try {
            $sql = "SELECT 
                        COALESCE(rc.category_name, r.purpose) as category,
                        COUNT(*) as count,
                        SUM(r.total_amount) as total_amount
                    FROM requisitions r
                    LEFT JOIN requisition_categories rc ON r.purpose = rc.category_name
                    WHERE r.status != 'draft'";
            
            $params = [];
            
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['department_id'])) {
                $sql .= " AND r.department_id = ?";
                $params[] = $filters['department_id'];
            }
            
            $sql .= " GROUP BY category ORDER BY count DESC LIMIT 10";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get organization categories error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get organization-wide monthly spending - ✅ UPDATED with filters
     */
    private function getOrganizationMonthlySpending($filters = []) {
        try {
            $sql = "SELECT 
                        DATE_FORMAT(r.created_at, '%b %Y') as month,
                        SUM(r.total_amount) as total_amount,
                        COUNT(*) as count
                    FROM requisitions r
                    WHERE r.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
            
            $params = [];
            
            // Handle status filter
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            } else {
                // Default: only paid/completed
                $sql .= " AND r.status IN (?, ?)";
                $params[] = STATUS_PAID;
                $params[] = STATUS_COMPLETED;
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['department_id'])) {
                $sql .= " AND r.department_id = ?";
                $params[] = $filters['department_id'];
            }
            
            $sql .= " GROUP BY DATE_FORMAT(r.created_at, '%Y-%m'), month 
                     ORDER BY DATE_FORMAT(r.created_at, '%Y-%m') ASC";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get organization monthly spending error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get organization-wide hourly distribution - ✅ UPDATED with filters
     */
    private function getOrganizationHourlyDistribution($filters = []) {
        try {
            $sql = "SELECT 
                        HOUR(r.created_at) as hour,
                        COUNT(*) as count
                    FROM requisitions r
                    WHERE r.status != 'draft'";
            
            $params = [];
            
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['department_id'])) {
                $sql .= " AND r.department_id = ?";
                $params[] = $filters['department_id'];
            }
            
            $sql .= " GROUP BY hour ORDER BY hour ASC";
            
            $results = $this->db->fetchAll($sql, $params);
            
            // Initialize 24-hour array
            $hourly = array_fill(0, 24, 0);
            
            // Fill in actual counts
            foreach ($results as $row) {
                $hourly[(int)$row['hour']] = (int)$row['count'];
            }
            
            return $hourly;
            
        } catch (Exception $e) {
            error_log("Get organization hourly distribution error: " . $e->getMessage());
            return array_fill(0, 24, 0);
        }
    }

    /**
     * Get organization-wide weekday distribution - ✅ UPDATED with filters
     */
    private function getOrganizationWeekdayDistribution($filters = []) {
        try {
            $sql = "SELECT 
                        WEEKDAY(r.created_at) as weekday,
                        COUNT(*) as count
                    FROM requisitions r
                    WHERE r.status != 'draft'";
            
            $params = [];
            
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['department_id'])) {
                $sql .= " AND r.department_id = ?";
                $params[] = $filters['department_id'];
            }
            
            $sql .= " GROUP BY weekday ORDER BY weekday ASC";
            
            $results = $this->db->fetchAll($sql, $params);
            
            // Initialize 7-day array (Monday=0, ..., Sunday=6)
            $weekdays = array_fill(0, 7, 0);
            
            // Fill in actual counts
            foreach ($results as $row) {
                $weekdays[(int)$row['weekday']] = (int)$row['count'];
            }
            
            return $weekdays;
            
        } catch (Exception $e) {
            error_log("Get organization weekday distribution error: " . $e->getMessage());
            return array_fill(0, 7, 0);
        }
    }

    /**
     * Get organization-wide analytics metrics
     */
    private function getOrganizationAnalytics($filters = []) {
        try {
            $sql = "SELECT r.* 
                    FROM requisitions r
                    WHERE r.status != 'draft'";
            
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $params[] = $dateRange['start'];
                    $params[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['department_id'])) {
                $sql .= " AND r.department_id = ?";
                $params[] = $filters['department_id'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND r.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $params[] = $filters['category'];
            }
            
            $requisitions = $this->db->fetchAll($sql, $params);
            
            if (empty($requisitions)) {
                return [
                    'avg_approval_days' => '0',
                    'top_department' => 'N/A',
                    'top_requester' => 'N/A',
                    'busiest_day' => 'N/A',
                    'busiest_hour' => 'N/A',
                    'approval_rate' => '0'
                ];
            }
            
            // Calculate approval metrics
            $totalApprovalDays = 0;
            $completedCount = 0;
            $approvedCount = 0;
            $totalCount = count($requisitions);
            
            foreach ($requisitions as $req) {
                if (in_array($req['status'], ['completed', 'paid', 'approved_for_payment'])) {
                    $approvedCount++;
                    
                    if ($req['status'] == 'completed' && !empty($req['submitted_at'])) {
                        $submittedDate = new DateTime($req['submitted_at']);
                        $completedDate = !empty($req['payment_date']) 
                            ? new DateTime($req['payment_date'])
                            : new DateTime($req['updated_at']);
                        
                        $interval = $submittedDate->diff($completedDate);
                        $totalApprovalDays += $interval->days;
                        $completedCount++;
                    }
                }
            }
            
            $avgApprovalDays = $completedCount > 0 
                ? round($totalApprovalDays / $completedCount, 1) 
                : 0;
            
            $approvalRate = $totalCount > 0 
                ? round(($approvedCount / $totalCount) * 100, 1) 
                : 0;
            
            // Get top department
            $sql = "SELECT 
                        d.department_name,
                        COUNT(r.id) as count
                    FROM departments d
                    INNER JOIN requisitions r ON d.id = r.department_id
                    WHERE r.status != 'draft'";
            
            $topDeptParams = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $topDeptParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $topDeptParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $topDeptParams[] = $dateRange['start'];
                    $topDeptParams[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $topDeptParams[] = $filters['category'];
            }
            
            $sql .= " GROUP BY d.id, d.department_name ORDER BY count DESC LIMIT 1";
            
            $topDept = $this->db->fetchOne($sql, $topDeptParams);
            $topDepartment = $topDept ? $topDept['department_name'] : 'N/A';
            
            // Get top requester
            $sql = "SELECT 
                        CONCAT(u.first_name, ' ', u.last_name) as name,
                        COUNT(r.id) as count
                    FROM users u
                    INNER JOIN requisitions r ON u.id = r.user_id
                    WHERE r.status != 'draft'";
            
            $topReqParams = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $topReqParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $topReqParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $topReqParams[] = $dateRange['start'];
                    $topReqParams[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['department_id'])) {
                $sql .= " AND r.department_id = ?";
                $topReqParams[] = $filters['department_id'];
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $topReqParams[] = $filters['category'];
            }
            
            $sql .= " GROUP BY u.id, name ORDER BY count DESC LIMIT 1";
            
            $topReq = $this->db->fetchOne($sql, $topReqParams);
            $topRequester = $topReq ? $topReq['name'] : 'N/A';
            
            // Get busiest day
            $sql = "SELECT WEEKDAY(r.created_at) as weekday, COUNT(*) as count
                    FROM requisitions r
                    WHERE r.status != 'draft'";
            
            $dayParams = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $dayParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $dayParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $dayParams[] = $dateRange['start'];
                    $dayParams[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $dayParams[] = $filters['category'];
            }
            
            $sql .= " GROUP BY weekday ORDER BY count DESC LIMIT 1";
            
            $busiestDayData = $this->db->fetchOne($sql, $dayParams);
            
            if ($busiestDayData) {
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                $busiestDay = $days[$busiestDayData['weekday']];
            } else {
                $busiestDay = 'N/A';
            }
            
            // Get busiest hour
            $sql = "SELECT HOUR(r.created_at) as hour, COUNT(*) as count
                    FROM requisitions r
                    WHERE r.status != 'draft'";
            
            $hourParams = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(r.created_at) >= ?";
                $hourParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(r.created_at) <= ?";
                $hourParams[] = $filters['date_to'];
            }
            
            if (!empty($filters['period'])) {
                $dateRange = $this->getPeriodDateRange($filters['period']);
                if ($dateRange) {
                    $sql .= " AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?";
                    $hourParams[] = $dateRange['start'];
                    $hourParams[] = $dateRange['end'];
                }
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND r.purpose = ?";
                $hourParams[] = $filters['category'];
            }
            
            $sql .= " GROUP BY hour ORDER BY count DESC LIMIT 1";
            
            $busiestHourData = $this->db->fetchOne($sql, $hourParams);
            
            if ($busiestHourData) {
                $hour = (int)$busiestHourData['hour'];
                if ($hour == 0) {
                    $busiestHour = '12:00 AM';
                } elseif ($hour < 12) {
                    $busiestHour = $hour . ':00 AM';
                } elseif ($hour == 12) {
                    $busiestHour = '12:00 PM';
                } else {
                    $busiestHour = ($hour - 12) . ':00 PM';
                }
            } else {
                $busiestHour = 'N/A';
            }
            
            return [
                'avg_approval_days' => $avgApprovalDays,
                'top_department' => $topDepartment,
                'top_requester' => $topRequester,
                'busiest_day' => $busiestDay,
                'busiest_hour' => $busiestHour,
                'approval_rate' => $approvalRate
            ];
            
        } catch (Exception $e) {
            error_log("Get organization analytics error: " . $e->getMessage());
            return [
                'avg_approval_days' => '0',
                'top_department' => 'N/A',
                'top_requester' => 'N/A',
                'busiest_day' => 'N/A',
                'busiest_hour' => 'N/A',
                'approval_rate' => '0'
            ];
        }
    }
}
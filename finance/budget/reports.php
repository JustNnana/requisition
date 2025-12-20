<?php
/**
 * GateWey Requisition Management System
 * Budget Reports & Analysis
 *
 * File: finance/budget/reports.php
 * Purpose: Advanced budget analysis and year-over-year comparisons
 */

define('APP_ACCESS', true);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../helpers/permissions.php';

Session::start();
require_once __DIR__ . '/../../middleware/auth-check.php';
require_once __DIR__ . '/../../middleware/role-check.php';

// Finance Manager and Managing Director can access
$userRole = Session::getUserRoleId();
if ($userRole != ROLE_FINANCE_MANAGER && $userRole != ROLE_MANAGING_DIRECTOR) {
    Session::setFlash('error', 'Access denied. This page is only accessible to Finance Manager and Managing Director.');
    redirect(BASE_URL . '/dashboard/index.php');
    exit;
}

$isFinanceManager = (Session::getUserRoleId() == ROLE_FINANCE_MANAGER);
$isManagingDirector = (Session::getUserRoleId() == ROLE_MANAGING_DIRECTOR);

$budget = new Budget();
$department = new Department();
$db = Database::getInstance();

// Get all departments
$departments = $department->getAll();

// Get filter parameters
$reportType = isset($_GET['report_type']) ? Sanitizer::string($_GET['report_type']) : 'department';
$selectedDepartment = isset($_GET['department']) ? (int)$_GET['department'] : 0;
$year1 = isset($_GET['year1']) ? (int)$_GET['year1'] : (int)date('Y');
$year2 = isset($_GET['year2']) ? (int)$_GET['year2'] : (int)date('Y') - 1;
$quarter1 = isset($_GET['quarter1']) ? (int)$_GET['quarter1'] : 0;
$quarter2 = isset($_GET['quarter2']) ? (int)$_GET['quarter2'] : 0;

// Get available years from budgets
$sql = "SELECT DISTINCT YEAR(start_date) as year FROM department_budgets ORDER BY year DESC";
$availableYears = $db->fetchAll($sql);

// Get quarters
$quarters = [
    0 => 'Full Year',
    1 => 'Q1 (Jan-Mar)',
    2 => 'Q2 (Apr-Jun)',
    3 => 'Q3 (Jul-Sep)',
    4 => 'Q4 (Oct-Dec)'
];

// Initialize report data
$reportData = [];
$comparisonData = [];

if ($reportType === 'department' && $selectedDepartment > 0) {
    // Department-specific analysis
    $deptInfo = $department->getById($selectedDepartment);

    // Get budget data for Year 1
    $year1Data = getBudgetDataForYear($db, $selectedDepartment, $year1, $quarter1);

    // Get budget data for Year 2
    $year2Data = getBudgetDataForYear($db, $selectedDepartment, $year2, $quarter2);

    // Calculate comparison metrics
    $comparisonData = [
        'year1' => $year1Data,
        'year2' => $year2Data,
        'budget_change' => $year1Data['total_budget'] - $year2Data['total_budget'],
        'budget_change_percent' => $year2Data['total_budget'] > 0
            ? (($year1Data['total_budget'] - $year2Data['total_budget']) / $year2Data['total_budget']) * 100
            : 0,
        'allocated_change' => $year1Data['total_allocated'] - $year2Data['total_allocated'],
        'allocated_change_percent' => $year2Data['total_allocated'] > 0
            ? (($year1Data['total_allocated'] - $year2Data['total_allocated']) / $year2Data['total_allocated']) * 100
            : 0,
        'utilization_change' => $year1Data['utilization_rate'] - $year2Data['utilization_rate']
    ];

    // Get category breakdown for Year 1
    $year1Data['category_breakdown'] = getCategoryBreakdown($db, $selectedDepartment, $year1, $quarter1);

    // Get category breakdown for Year 2
    $year2Data['category_breakdown'] = getCategoryBreakdown($db, $selectedDepartment, $year2, $quarter2);

    // Get monthly trends for both years
    $year1Data['monthly_trends'] = getMonthlyTrends($db, $selectedDepartment, $year1, $quarter1);
    $year2Data['monthly_trends'] = getMonthlyTrends($db, $selectedDepartment, $year2, $quarter2);

} elseif ($reportType === 'organization') {
    // Organization-wide analysis

    // Get all department budgets for Year 1
    foreach ($departments as $dept) {
        $year1DeptData = getBudgetDataForYear($db, $dept['id'], $year1, $quarter1);
        $year2DeptData = getBudgetDataForYear($db, $selectedDepartment, $year2, $quarter2);

        $reportData[] = [
            'department_id' => $dept['id'],
            'department_name' => $dept['department_name'],
            'department_code' => $dept['department_code'],
            'year1' => $year1DeptData,
            'year2' => $year2DeptData,
            'budget_change' => $year1DeptData['total_budget'] - $year2DeptData['total_budget'],
            'budget_change_percent' => $year2DeptData['total_budget'] > 0
                ? (($year1DeptData['total_budget'] - $year2DeptData['total_budget']) / $year2DeptData['total_budget']) * 100
                : 0
        ];
    }

    // Calculate organization totals
    $orgYear1Total = array_sum(array_column(array_column($reportData, 'year1'), 'total_budget'));
    $orgYear1Allocated = array_sum(array_column(array_column($reportData, 'year1'), 'total_allocated'));
    $orgYear2Total = array_sum(array_column(array_column($reportData, 'year2'), 'total_budget'));
    $orgYear2Allocated = array_sum(array_column(array_column($reportData, 'year2'), 'total_allocated'));

    $comparisonData = [
        'org_year1_total' => $orgYear1Total,
        'org_year1_allocated' => $orgYear1Allocated,
        'org_year1_utilization' => $orgYear1Total > 0 ? ($orgYear1Allocated / $orgYear1Total) * 100 : 0,
        'org_year2_total' => $orgYear2Total,
        'org_year2_allocated' => $orgYear2Allocated,
        'org_year2_utilization' => $orgYear2Total > 0 ? ($orgYear2Allocated / $orgYear2Total) * 100 : 0,
        'org_budget_change' => $orgYear1Total - $orgYear2Total,
        'org_budget_change_percent' => $orgYear2Total > 0
            ? (($orgYear1Total - $orgYear2Total) / $orgYear2Total) * 100
            : 0
    ];
}

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

$pageTitle = 'Budget Reports & Analysis';

/**
 * Helper function to get budget data for a specific year and department
 */
function getBudgetDataForYear($db, $departmentId, $year, $quarter = 0) {
    $quarterCondition = '';
    if ($quarter > 0) {
        $quarterMonth = ($quarter - 1) * 3 + 1;
        $quarterCondition = " AND MONTH(start_date) >= $quarterMonth AND MONTH(start_date) < " . ($quarterMonth + 3);
    }

    $sql = "SELECT
                COALESCE(SUM(budget_amount), 0) as total_budget,
                COALESCE(SUM(allocated_amount), 0) as total_allocated,
                COALESCE(SUM(available_amount), 0) as total_available,
                COUNT(*) as budget_count
            FROM department_budgets
            WHERE department_id = ?
            AND YEAR(start_date) = ?
            $quarterCondition";

    $result = $db->fetchOne($sql, [$departmentId, $year]);

    $utilizationRate = $result['total_budget'] > 0
        ? ($result['total_allocated'] / $result['total_budget']) * 100
        : 0;

    // Get requisition count
    $reqSql = "SELECT COUNT(*) as req_count, COALESCE(SUM(total_amount), 0) as req_total
               FROM requisitions
               WHERE department_id = ?
               AND YEAR(created_at) = ?
               AND status IN (?, ?)
               $quarterCondition";

    $reqResult = $db->fetchOne($reqSql, [$departmentId, $year, STATUS_PAID, STATUS_COMPLETED]);

    return [
        'total_budget' => (float)$result['total_budget'],
        'total_allocated' => (float)$result['total_allocated'],
        'total_available' => (float)$result['total_available'],
        'budget_count' => (int)$result['budget_count'],
        'utilization_rate' => $utilizationRate,
        'requisition_count' => (int)$reqResult['req_count'],
        'requisition_total' => (float)$reqResult['req_total']
    ];
}

/**
 * Helper function to get category breakdown
 */
function getCategoryBreakdown($db, $departmentId, $year, $quarter = 0) {
    $quarterCondition = '';
    if ($quarter > 0) {
        $quarterMonth = ($quarter - 1) * 3 + 1;
        $quarterCondition = " AND MONTH(r.created_at) >= $quarterMonth AND MONTH(r.created_at) < " . ($quarterMonth + 3);
    }

    $sql = "SELECT
                rc.category_name,
                COUNT(r.id) as req_count,
                COALESCE(SUM(r.total_amount), 0) as total_spent
            FROM requisitions r
            LEFT JOIN requisition_categories rc ON r.category_id = rc.id
            WHERE r.department_id = ?
            AND YEAR(r.created_at) = ?
            AND r.status IN (?, ?)
            $quarterCondition
            GROUP BY rc.category_name
            ORDER BY total_spent DESC";

    return $db->fetchAll($sql, [$departmentId, $year, STATUS_PAID, STATUS_COMPLETED]);
}

/**
 * Helper function to get monthly trends
 */
function getMonthlyTrends($db, $departmentId, $year, $quarter = 0) {
    $startMonth = $quarter > 0 ? ($quarter - 1) * 3 + 1 : 1;
    $endMonth = $quarter > 0 ? $startMonth + 2 : 12;

    $trends = [];
    for ($month = $startMonth; $month <= $endMonth; $month++) {
        $sql = "SELECT
                    COUNT(*) as req_count,
                    COALESCE(SUM(total_amount), 0) as total_spent
                FROM requisitions
                WHERE department_id = ?
                AND YEAR(created_at) = ?
                AND MONTH(created_at) = ?
                AND status IN (?, ?)";

        $result = $db->fetchOne($sql, [$departmentId, $year, $month, STATUS_PAID, STATUS_COMPLETED]);

        $trends[] = [
            'month' => date('M', mktime(0, 0, 0, $month, 1)),
            'req_count' => (int)$result['req_count'],
            'total_spent' => (float)$result['total_spent']
        ];
    }

    return $trends;
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
    .filter-section {
        background: var(--bg-subtle);
        padding: var(--spacing-5);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-6);
        border: 1px solid var(--border-color);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-4);
        margin-bottom: var(--spacing-4);
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-2);
    }

    .filter-label {
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
    }

    .filter-select {
        padding: var(--spacing-3);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        background: var(--bg-input);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
    }

    .comparison-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--spacing-5);
        margin-bottom: var(--spacing-6);
    }

    .comparison-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-5);
    }

    .comparison-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-4);
        padding-bottom: var(--spacing-3);
        border-bottom: 1px solid var(--border-color);
    }

    .comparison-title {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
    }

    .comparison-year {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        background: var(--bg-subtle);
        padding: var(--spacing-1) var(--spacing-3);
        border-radius: var(--border-radius-full);
    }

    .metric-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--spacing-3) 0;
        border-bottom: 1px solid var(--border-color);
    }

    .metric-row:last-child {
        border-bottom: none;
    }

    .metric-label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .metric-value {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .change-indicator {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-1);
        padding: var(--spacing-1) var(--spacing-2);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
    }

    .change-indicator.positive {
        background: rgba(var(--success-rgb), 0.1);
        color: var(--success);
    }

    .change-indicator.negative {
        background: rgba(var(--danger-rgb), 0.1);
        color: var(--danger);
    }

    .change-indicator.neutral {
        background: rgba(var(--text-rgb), 0.1);
        color: var(--text-secondary);
    }

    .chart-container {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-5);
        margin-bottom: var(--spacing-6);
    }

    .chart-header {
        margin-bottom: var(--spacing-4);
    }

    .chart-title {
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-1) 0;
    }

    .chart-subtitle {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-4);
        margin-bottom: var(--spacing-6);
    }

    .stat-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-4);
        text-align: center;
    }

    .stat-value {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-1) 0;
    }

    .stat-label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0 0 var(--spacing-2) 0;
    }

    .table-container {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        overflow: hidden;
        margin-bottom: var(--spacing-6);
    }

    .table-header {
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
    }

    .table-title {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">
                <i class="fas fa-chart-line me-2"></i>
                Budget Reports & Analysis
            </h1>
            <p class="content-subtitle">
                Comprehensive budget comparison and performance analysis
            </p>
        </div>
        <div class="content-actions">
            <?php if (($reportType === 'department' && $selectedDepartment > 0) || $reportType === 'organization'): ?>
            <a href="export-report.php?<?php echo http_build_query($_GET); ?>" class="btn btn-success me-2">
                <i class="fas fa-file-excel me-2"></i>Export to Excel
            </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/finance/budget/index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Budgets
            </a>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($successMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-error alert-dismissible">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($errorMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="filter-section">
    <form method="GET" action="">
        <div class="filter-grid">
            <div class="filter-group">
                <label class="filter-label">Report Type</label>
                <select name="report_type" class="filter-select" id="reportType" onchange="toggleDepartmentFilter()">
                    <option value="department" <?php echo $reportType === 'department' ? 'selected' : ''; ?>>Department Analysis</option>
                    <option value="organization" <?php echo $reportType === 'organization' ? 'selected' : ''; ?>>Organization-Wide</option>
                </select>
            </div>

            <div class="filter-group" id="departmentFilter">
                <label class="filter-label">Department</label>
                <select name="department" class="filter-select">
                    <option value="0">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo $selectedDepartment == $dept['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['department_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Year 1 (Current)</label>
                <select name="year1" class="filter-select">
                    <?php foreach ($availableYears as $y): ?>
                        <option value="<?php echo $y['year']; ?>" <?php echo $year1 == $y['year'] ? 'selected' : ''; ?>>
                            <?php echo $y['year']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Quarter 1</label>
                <select name="quarter1" class="filter-select">
                    <?php foreach ($quarters as $qNum => $qLabel): ?>
                        <option value="<?php echo $qNum; ?>" <?php echo $quarter1 == $qNum ? 'selected' : ''; ?>>
                            <?php echo $qLabel; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Year 2 (Compare)</label>
                <select name="year2" class="filter-select">
                    <?php foreach ($availableYears as $y): ?>
                        <option value="<?php echo $y['year']; ?>" <?php echo $year2 == $y['year'] ? 'selected' : ''; ?>>
                            <?php echo $y['year']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Quarter 2</label>
                <select name="quarter2" class="filter-select">
                    <?php foreach ($quarters as $qNum => $qLabel): ?>
                        <option value="<?php echo $qNum; ?>" <?php echo $quarter2 == $qNum ? 'selected' : ''; ?>>
                            <?php echo $qLabel; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: var(--spacing-3);">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-chart-bar me-2"></i>Generate Report
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='reports.php'">
                <i class="fas fa-redo me-2"></i>Reset
            </button>
        </div>
    </form>
</div>

<?php if ($reportType === 'department' && $selectedDepartment > 0 && isset($deptInfo)): ?>
    <!-- Department Analysis -->
    <div style="margin-bottom: var(--spacing-4);">
        <h2 style="font-size: var(--font-size-2xl); font-weight: var(--font-weight-semibold); color: var(--text-primary); margin-bottom: var(--spacing-2);">
            <?php echo htmlspecialchars($deptInfo['department_name']); ?> (<?php echo htmlspecialchars($deptInfo['department_code']); ?>)
        </h2>
        <p style="color: var(--text-secondary);">
            Comparing <?php echo $year1; ?> <?php echo $quarter1 > 0 ? $quarters[$quarter1] : ''; ?>
            with <?php echo $year2; ?> <?php echo $quarter2 > 0 ? $quarters[$quarter2] : ''; ?>
        </p>
    </div>

    <!-- Year Comparison Cards -->
    <div class="comparison-grid">
        <!-- Year 1 Card -->
        <div class="comparison-card">
            <div class="comparison-header">
                <h3 class="comparison-title">
                    <?php echo $year1; ?> <?php echo $quarter1 > 0 ? $quarters[$quarter1] : ''; ?>
                </h3>
                <span class="comparison-year">Current Period</span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Total Budget</span>
                <span class="metric-value">₦<?php echo number_format($comparisonData['year1']['total_budget'], 2); ?></span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Allocated</span>
                <span class="metric-value">₦<?php echo number_format($comparisonData['year1']['total_allocated'], 2); ?></span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Available</span>
                <span class="metric-value">₦<?php echo number_format($comparisonData['year1']['total_available'], 2); ?></span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Utilization Rate</span>
                <span class="metric-value"><?php echo number_format($comparisonData['year1']['utilization_rate'], 1); ?>%</span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Requisitions</span>
                <span class="metric-value"><?php echo number_format($comparisonData['year1']['requisition_count']); ?></span>
            </div>
        </div>

        <!-- Year 2 Card -->
        <div class="comparison-card">
            <div class="comparison-header">
                <h3 class="comparison-title">
                    <?php echo $year2; ?> <?php echo $quarter2 > 0 ? $quarters[$quarter2] : ''; ?>
                </h3>
                <span class="comparison-year">Previous Period</span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Total Budget</span>
                <span class="metric-value">₦<?php echo number_format($comparisonData['year2']['total_budget'], 2); ?></span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Allocated</span>
                <span class="metric-value">₦<?php echo number_format($comparisonData['year2']['total_allocated'], 2); ?></span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Available</span>
                <span class="metric-value">₦<?php echo number_format($comparisonData['year2']['total_available'], 2); ?></span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Utilization Rate</span>
                <span class="metric-value"><?php echo number_format($comparisonData['year2']['utilization_rate'], 1); ?>%</span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Requisitions</span>
                <span class="metric-value"><?php echo number_format($comparisonData['year2']['requisition_count']); ?></span>
            </div>
        </div>

        <!-- Change Analysis Card -->
        <div class="comparison-card" style="grid-column: span 1;">
            <div class="comparison-header">
                <h3 class="comparison-title">Year-over-Year Change</h3>
                <span class="comparison-year">Analysis</span>
            </div>
            <div class="metric-row">
                <span class="metric-label">Budget Change</span>
                <div style="text-align: right;">
                    <div class="metric-value">₦<?php echo number_format(abs($comparisonData['budget_change']), 2); ?></div>
                    <span class="change-indicator <?php echo $comparisonData['budget_change'] > 0 ? 'positive' : ($comparisonData['budget_change'] < 0 ? 'negative' : 'neutral'); ?>">
                        <i class="fas fa-<?php echo $comparisonData['budget_change'] > 0 ? 'arrow-up' : ($comparisonData['budget_change'] < 0 ? 'arrow-down' : 'minus'); ?>"></i>
                        <?php echo number_format(abs($comparisonData['budget_change_percent']), 1); ?>%
                    </span>
                </div>
            </div>
            <div class="metric-row">
                <span class="metric-label">Allocation Change</span>
                <div style="text-align: right;">
                    <div class="metric-value">₦<?php echo number_format(abs($comparisonData['allocated_change']), 2); ?></div>
                    <span class="change-indicator <?php echo $comparisonData['allocated_change'] > 0 ? 'positive' : ($comparisonData['allocated_change'] < 0 ? 'negative' : 'neutral'); ?>">
                        <i class="fas fa-<?php echo $comparisonData['allocated_change'] > 0 ? 'arrow-up' : ($comparisonData['allocated_change'] < 0 ? 'arrow-down' : 'minus'); ?>"></i>
                        <?php echo number_format(abs($comparisonData['allocated_change_percent']), 1); ?>%
                    </span>
                </div>
            </div>
            <div class="metric-row">
                <span class="metric-label">Utilization Change</span>
                <div style="text-align: right;">
                    <span class="change-indicator <?php echo $comparisonData['utilization_change'] > 0 ? 'positive' : ($comparisonData['utilization_change'] < 0 ? 'negative' : 'neutral'); ?>">
                        <i class="fas fa-<?php echo $comparisonData['utilization_change'] > 0 ? 'arrow-up' : ($comparisonData['utilization_change'] < 0 ? 'arrow-down' : 'minus'); ?>"></i>
                        <?php echo number_format(abs($comparisonData['utilization_change']), 1); ?>%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: var(--spacing-5); margin-bottom: var(--spacing-6);">
        <!-- Monthly Trends Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Monthly Spending Trends</h3>
                <p class="chart-subtitle">Comparing monthly allocation patterns</p>
            </div>
            <canvas id="monthlyTrendsChart" style="max-height: 300px;"></canvas>
        </div>

        <!-- Category Breakdown Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Category Distribution</h3>
                <p class="chart-subtitle"><?php echo $year1; ?> vs <?php echo $year2; ?> spending by category</p>
            </div>
            <canvas id="categoryChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Category Breakdown Table -->
    <?php if (!empty($comparisonData['year1']['category_breakdown'])): ?>
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Category Breakdown - <?php echo $year1; ?></h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="text-end">Requisitions</th>
                        <th class="text-end">Total Spent</th>
                        <th class="text-end">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalSpent = array_sum(array_column($comparisonData['year1']['category_breakdown'], 'total_spent'));
                    foreach ($comparisonData['year1']['category_breakdown'] as $category):
                        $percentage = $totalSpent > 0 ? ($category['total_spent'] / $totalSpent) * 100 : 0;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['category_name'] ?? 'Uncategorized'); ?></td>
                        <td class="text-end"><?php echo number_format($category['req_count']); ?></td>
                        <td class="text-end">₦<?php echo number_format($category['total_spent'], 2); ?></td>
                        <td class="text-end"><?php echo number_format($percentage, 1); ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

<?php elseif ($reportType === 'organization'): ?>
    <!-- Organization-Wide Analysis -->
    <div style="margin-bottom: var(--spacing-4);">
        <h2 style="font-size: var(--font-size-2xl); font-weight: var(--font-weight-semibold); color: var(--text-primary); margin-bottom: var(--spacing-2);">
            Organization-Wide Budget Analysis
        </h2>
        <p style="color: var(--text-secondary);">
            Comparing <?php echo $year1; ?> <?php echo $quarter1 > 0 ? $quarters[$quarter1] : ''; ?>
            with <?php echo $year2; ?> <?php echo $quarter2 > 0 ? $quarters[$quarter2] : ''; ?>
        </p>
    </div>

    <!-- Organization Summary -->
    <div class="stats-grid">
        <div class="stat-card">
            <p class="stat-value">₦<?php echo number_format($comparisonData['org_year1_total'], 2); ?></p>
            <p class="stat-label"><?php echo $year1; ?> Total Budget</p>
            <?php
            $change = $comparisonData['org_budget_change'];
            $changePercent = $comparisonData['org_budget_change_percent'];
            ?>
            <span class="change-indicator <?php echo $change > 0 ? 'positive' : ($change < 0 ? 'negative' : 'neutral'); ?>">
                <i class="fas fa-<?php echo $change > 0 ? 'arrow-up' : ($change < 0 ? 'arrow-down' : 'minus'); ?>"></i>
                <?php echo number_format(abs($changePercent), 1); ?>% vs <?php echo $year2; ?>
            </span>
        </div>

        <div class="stat-card">
            <p class="stat-value">₦<?php echo number_format($comparisonData['org_year1_allocated'], 2); ?></p>
            <p class="stat-label"><?php echo $year1; ?> Total Allocated</p>
            <span class="change-indicator neutral">
                <?php echo number_format($comparisonData['org_year1_utilization'], 1); ?>% utilization
            </span>
        </div>

        <div class="stat-card">
            <p class="stat-value"><?php echo count($reportData); ?></p>
            <p class="stat-label">Total Departments</p>
        </div>
    </div>

    <!-- Department Comparison Table -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Department Budget Comparison</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th class="text-end"><?php echo $year1; ?> Budget</th>
                        <th class="text-end"><?php echo $year1; ?> Utilized</th>
                        <th class="text-end"><?php echo $year2; ?> Budget</th>
                        <th class="text-end"><?php echo $year2; ?> Utilized</th>
                        <th class="text-end">Change</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $deptData): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($deptData['department_name']); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($deptData['department_code']); ?></small>
                        </td>
                        <td class="text-end">₦<?php echo number_format($deptData['year1']['total_budget'], 2); ?></td>
                        <td class="text-end"><?php echo number_format($deptData['year1']['utilization_rate'], 1); ?>%</td>
                        <td class="text-end">₦<?php echo number_format($deptData['year2']['total_budget'], 2); ?></td>
                        <td class="text-end"><?php echo number_format($deptData['year2']['utilization_rate'], 1); ?>%</td>
                        <td class="text-end">
                            <span class="change-indicator <?php echo $deptData['budget_change'] > 0 ? 'positive' : ($deptData['budget_change'] < 0 ? 'negative' : 'neutral'); ?>">
                                <i class="fas fa-<?php echo $deptData['budget_change'] > 0 ? 'arrow-up' : ($deptData['budget_change'] < 0 ? 'arrow-down' : 'minus'); ?>"></i>
                                <?php echo number_format(abs($deptData['budget_change_percent']), 1); ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php else: ?>
    <!-- No Report Generated -->
    <div style="text-align: center; padding: var(--spacing-8); color: var(--text-muted);">
        <i class="fas fa-chart-line" style="font-size: 4rem; margin-bottom: var(--spacing-3); opacity: 0.5;"></i>
        <h3>No Report Generated</h3>
        <p>Please select a department and configure the filters above to generate a budget report.</p>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle department filter based on report type
    toggleDepartmentFilter();

    <?php if ($reportType === 'department' && $selectedDepartment > 0 && !empty($comparisonData['year1']['monthly_trends'])): ?>
    // Monthly Trends Chart
    const monthlyCtx = document.getElementById('monthlyTrendsChart');
    if (monthlyCtx) {
        const year1Trends = <?php echo json_encode($comparisonData['year1']['monthly_trends']); ?>;
        const year2Trends = <?php echo json_encode($comparisonData['year2']['monthly_trends']); ?>;

        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: year1Trends.map(t => t.month),
                datasets: [
                    {
                        label: '<?php echo $year1; ?> Spending',
                        data: year1Trends.map(t => t.total_spent),
                        borderColor: getComputedStyle(document.documentElement).getPropertyValue('--primary'),
                        backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--primary') + '20',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: '<?php echo $year2; ?> Spending',
                        data: year2Trends.map(t => t.total_spent),
                        borderColor: getComputedStyle(document.documentElement).getPropertyValue('--info'),
                        backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--info') + '20',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ₦' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₦' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Category Breakdown Chart
    <?php if (!empty($comparisonData['year1']['category_breakdown'])): ?>
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        const categories = <?php echo json_encode($comparisonData['year1']['category_breakdown']); ?>;

        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categories.map(c => c.category_name || 'Uncategorized'),
                datasets: [{
                    data: categories.map(c => c.total_spent),
                    backgroundColor: [
                        getComputedStyle(document.documentElement).getPropertyValue('--primary'),
                        getComputedStyle(document.documentElement).getPropertyValue('--success'),
                        getComputedStyle(document.documentElement).getPropertyValue('--warning'),
                        getComputedStyle(document.documentElement).getPropertyValue('--danger'),
                        getComputedStyle(document.documentElement).getPropertyValue('--info'),
                        '#6c757d',
                        '#20c997',
                        '#fd7e14'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = '₦' + context.parsed.toLocaleString();
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percent = ((context.parsed / total) * 100).toFixed(1) + '%';
                                return label + ': ' + value + ' (' + percent + ')';
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>
    <?php endif; ?>
});

function toggleDepartmentFilter() {
    const reportType = document.getElementById('reportType').value;
    const deptFilter = document.getElementById('departmentFilter');

    if (reportType === 'organization') {
        deptFilter.style.display = 'none';
    } else {
        deptFilter.style.display = 'flex';
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

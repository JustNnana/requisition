<?php
/**
 * GateWey Requisition Management System
 * Organization Reports Page - Enhanced with Analytics
 * 
 * File: reports/organization.php
 * Purpose: Generate comprehensive organization-wide requisition reports (MD / Finance Manager view)
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';

// Load helpers
require_once __DIR__ . '/../helpers/permissions.php';
require_once __DIR__ . '/../helpers/status-indicator.php';

// Check if user is MD, Finance Manager, or Finance Member
if (!is_managing_director() && !is_finance_manager() && !is_finance_member()) {
    Session::setFlash('error', 'Only Managing Director, Finance Manager, and Finance Members can access organization reports.');
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

// Initialize classes
$report = new Report();
$department = new Department();
$user = new User();
$db = Database::getInstance();

// Get filters from request
$filters = [
    'period' => Sanitizer::string($_GET['period'] ?? ''),
    'date_from' => Sanitizer::string($_GET['date_from'] ?? ''),
    'date_to' => Sanitizer::string($_GET['date_to'] ?? ''),
    'status' => Sanitizer::string($_GET['status'] ?? ''),
    'department_id' => Sanitizer::int($_GET['department_id'] ?? 0),
    'user_id' => Sanitizer::int($_GET['user_id'] ?? 0),
    'category' => Sanitizer::string($_GET['category'] ?? ''),
    'search' => Sanitizer::string($_GET['search'] ?? ''),
    'interval' => Sanitizer::string($_GET['interval'] ?? 'daily')
];

// Get page
$page = Sanitizer::int($_GET['page'] ?? 1);
$page = max(1, $page);

// Generate report
$reportData = $report->generateOrganizationReport($filters, $page, 15);

if (!$reportData['success']) {
    Session::setFlash('error', $reportData['message']);
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

$statistics = $reportData['statistics'];
$requisitions = $reportData['requisitions'];
$pagination = $reportData['pagination'];
$departmentBreakdown = $reportData['department_breakdown'];
$chartData = $reportData['chart_data'] ?? [];
$analytics = $reportData['analytics'] ?? [];

// Get all departments for filter
$departments = $department->getAll(true);

// Get all users if department selected
$allUsers = [];
if (!empty($filters['department_id'])) {
    $allUsers = $user->getByDepartment($filters['department_id']);
}

// Get categories for filter
$categories = $db->fetchAll("SELECT category_name FROM requisition_categories WHERE is_active = 1 ORDER BY display_order");

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Organization Reports';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Add Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Add Status Indicator CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/status-indicators.css">

<style>
/* Enhanced Styles for Organization Reports */
.chart-container {
    position: relative;
    height: 320px;
    margin-bottom: var(--spacing-4);
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-6);
}

.analytics-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    padding: var(--spacing-4);
    transition: all 0.3s ease;
}

.analytics-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.analytics-label {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin-bottom: var(--spacing-2);
    font-weight: var(--font-weight-medium);
}

.analytics-value {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
}

.analytics-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-bottom: var(--spacing-3);
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: var(--spacing-4);
    margin-bottom: var(--spacing-6);
}

.chart-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    padding: var(--spacing-4);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-4);
    padding-bottom: var(--spacing-3);
    border-bottom: 1px solid var(--border-color);
}

.chart-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin: 0;
}

.filter-section {
    background: var(--bg-subtle);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    padding: var(--spacing-4);
    margin-bottom: var(--spacing-6);
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-3);
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-label {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--text-secondary);
    margin-bottom: var(--spacing-2);
}

.filter-select {
    padding: var(--spacing-2) var(--spacing-3);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: var(--font-size-base);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state-icon {
    color: var(--text-muted);
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state-title {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin-bottom: 10px;
}

.empty-state-text {
    font-size: var(--font-size-base);
    color: var(--text-secondary);
    max-width: 500px;
    margin: 0 auto;
}

.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: var(--spacing-4);
    padding-top: var(--spacing-4);
    border-top: 1px solid var(--border-color);
}

.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: var(--spacing-2);
}

.page-item.active .page-link {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.page-link {
    padding: var(--spacing-2) var(--spacing-4);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    color: var(--text-primary);
    text-decoration: none;
}

.page-link:hover:not(.active) {
    background: var(--bg-hover);
}

.pagination-info {
    color: var(--text-muted);
    font-size: var(--font-size-sm);
}

.bg-subtle {
    background: var(--bg-subtle);
    border: 1px solid var(--border-color);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-4);
    padding-bottom: var(--spacing-3);
    border-bottom: 2px solid var(--border-color);
}

.section-title {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.section-subtitle {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}
</style>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">
                <i class="fas fa-chart-bar me-2"></i>Organization Reports & Analytics
            </h1>
            <p class="content-subtitle">Comprehensive organization-wide requisition analysis with interactive charts</p>
        </div>
        <div class="d-flex gap-2">
            <?php if (!empty($requisitions)): ?>
                <a href="export-excel.php?<?php echo http_build_query(array_merge($filters, ['type' => 'organization'])); ?>" 
                   class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
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

<!-- Statistics Cards -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Requisitions</p>
            <p class="stat-value"><?php echo number_format($statistics['total_count']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Amount</p>
            <p class="stat-value"><?php echo format_currency($statistics['total_amount']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Average Amount</p>
            <p class="stat-value"><?php echo format_currency($statistics['average_amount']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Active Departments</p>
            <p class="stat-value"><?php echo count($departments); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Completed</p>
            <p class="stat-value"><?php echo number_format($statistics['completed_count'] ?? 0); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Pending</p>
            <p class="stat-value"><?php echo number_format($statistics['pending_count'] ?? 0); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Rejected</p>
            <p class="stat-value"><?php echo number_format($statistics['rejected_count'] ?? 0); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-teal">
            <i class="fas fa-calculator"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Highest Amount</p>
            <p class="stat-value"><?php echo format_currency($statistics['max_amount']); ?></p>
        </div>
    </div>
</div>

<!-- Advanced Analytics Section -->
<?php if (!empty($analytics)): ?>
<div class="section-header">
    <div>
        <h2 class="section-title">
            <i class="fas fa-chart-pie"></i>
            Advanced Analytics
        </h2>
        <p class="section-subtitle">Key performance indicators across the organization</p>
    </div>
</div>

<div class="analytics-grid">
    <div class="analytics-card">
        <div class="analytics-icon bg-info">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="analytics-label">Avg. Approval Time</div>
        <div class="analytics-value"><?php echo $analytics['avg_approval_days']; ?> days</div>
    </div>

    <div class="analytics-card">
        <div class="analytics-icon bg-success">
            <i class="fas fa-percentage"></i>
        </div>
        <div class="analytics-label">Approval Rate</div>
        <div class="analytics-value"><?php echo $analytics['approval_rate']; ?>%</div>
    </div>

    <div class="analytics-card">
        <div class="analytics-icon bg-primary">
            <i class="fas fa-building"></i>
        </div>
        <div class="analytics-label">Top Department</div>
        <div class="analytics-value" style="font-size: var(--font-size-lg);">
            <?php echo htmlspecialchars($analytics['top_department']); ?>
        </div>
    </div>

    <div class="analytics-card">
        <div class="analytics-icon bg-warning">
            <i class="fas fa-user-tie"></i>
        </div>
        <div class="analytics-label">Top Requester</div>
        <div class="analytics-value" style="font-size: var(--font-size-lg);">
            <?php echo htmlspecialchars($analytics['top_requester']); ?>
        </div>
    </div>

    <div class="analytics-card">
        <div class="analytics-icon bg-purple">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="analytics-label">Busiest Day</div>
        <div class="analytics-value" style="font-size: var(--font-size-lg);">
            <?php echo $analytics['busiest_day']; ?>
        </div>
    </div>

    <div class="analytics-card">
        <div class="analytics-icon bg-orange">
            <i class="fas fa-clock"></i>
        </div>
        <div class="analytics-label">Peak Hour</div>
        <div class="analytics-value" style="font-size: var(--font-size-lg);">
            <?php echo $analytics['busiest_hour']; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filter Section -->
<div class="filter-section">
    <div class="section-header" style="border: none; margin-bottom: var(--spacing-3);">
        <h3 class="section-title" style="font-size: var(--font-size-lg);">
            <i class="fas fa-filter"></i>
            Report Filters
        </h3>
    </div>

    <form method="GET" action="">
        <div class="filter-grid">
            <!-- Time Period -->
            <div class="filter-group">
                <label class="filter-label">Time Period</label>
                <select name="period" class="filter-select" id="periodSelect">
                    <option value="">All Time</option>
                    <option value="weekly" <?php echo ($filters['period'] === 'weekly') ? 'selected' : ''; ?>>This Week</option>
                    <option value="monthly" <?php echo ($filters['period'] === 'monthly') ? 'selected' : ''; ?>>This Month</option>
                    <option value="quarterly" <?php echo ($filters['period'] === 'quarterly') ? 'selected' : ''; ?>>This Quarter</option>
                    <option value="yearly" <?php echo ($filters['period'] === 'yearly') ? 'selected' : ''; ?>>This Year</option>
                    <option value="custom" <?php echo ($filters['period'] === 'custom') ? 'selected' : ''; ?>>Custom Range</option>
                </select>
            </div>

            <!-- Date From -->
            <div class="filter-group" id="dateFromGroup" style="display: <?php echo ($filters['period'] === 'custom') ? 'flex' : 'none'; ?>;">
                <label class="filter-label">Date From</label>
                <input type="date" name="date_from" class="filter-select" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
            </div>

            <!-- Date To -->
            <div class="filter-group" id="dateToGroup" style="display: <?php echo ($filters['period'] === 'custom') ? 'flex' : 'none'; ?>;">
                <label class="filter-label">Date To</label>
                <input type="date" name="date_to" class="filter-select" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
            </div>

            <!-- Department -->
            <div class="filter-group">
                <label class="filter-label">Department</label>
                <select name="department_id" class="filter-select" id="departmentSelect">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo ($filters['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['department_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- User -->
            <div class="filter-group" id="userFilterGroup" style="<?php echo !empty($filters['department_id']) ? 'display: flex;' : 'display: none;'; ?>">
                <label class="filter-label">User</label>
                <select name="user_id" class="filter-select">
                    <option value="">All Users</option>
                    <?php foreach ($allUsers as $member): ?>
                        <option value="<?php echo $member['id']; ?>" <?php echo ($filters['user_id'] == $member['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status -->
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-select">
                    <option value="">All Statuses</option>
                    <option value="pending_line_manager" <?php echo ($filters['status'] === 'pending_line_manager') ? 'selected' : ''; ?>>Pending Line Manager</option>
                    <option value="pending_md" <?php echo ($filters['status'] === 'pending_md') ? 'selected' : ''; ?>>Pending MD</option>
                    <option value="pending_finance_manager" <?php echo ($filters['status'] === 'pending_finance_manager') ? 'selected' : ''; ?>>Pending Finance</option>
                    <option value="approved_for_payment" <?php echo ($filters['status'] === 'approved_for_payment') ? 'selected' : ''; ?>>Approved for Payment</option>
                    <option value="paid" <?php echo ($filters['status'] === 'paid') ? 'selected' : ''; ?>>Paid</option>
                    <option value="completed" <?php echo ($filters['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="rejected" <?php echo ($filters['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>

            <!-- Category -->
            <div class="filter-group">
                <label class="filter-label">Category</label>
                <select name="category" class="filter-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['category_name']); ?>" 
                                <?php echo ($filters['category'] === $cat['category_name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Interval -->
            <div class="filter-group">
                <label class="filter-label">Chart Interval</label>
                <select name="interval" class="filter-select">
                    <option value="daily" <?php echo ($filters['interval'] === 'daily') ? 'selected' : ''; ?>>Daily</option>
                    <option value="weekly" <?php echo ($filters['interval'] === 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                    <option value="monthly" <?php echo ($filters['interval'] === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                </select>
            </div>
        </div>

        <!-- Search and Actions -->
        <div class="filter-grid mt-3">
            <div class="filter-group" style="grid-column: 1 / -1;">
                <label class="filter-label">Search</label>
                <input type="text" name="search" class="filter-select" 
                       placeholder="Search by requisition number, purpose, or requester name..."
                       value="<?php echo htmlspecialchars($filters['search']); ?>">
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end mt-3">
            <a href="?" class="btn btn-ghost">
                <i class="fas fa-redo"></i> Reset
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Generate Report
            </button>
        </div>
    </form>
</div>

<!-- Charts Section -->
<?php if (!empty($chartData)): ?>
<div class="section-header">
    <div>
        <h2 class="section-title">
            <i class="fas fa-chart-area"></i>
            Visual Analytics
        </h2>
        <p class="section-subtitle">Interactive charts and data visualizations</p>
    </div>
</div>

<div class="charts-grid">
    <!-- Requisition Trends Chart -->
    <div class="chart-card" style="grid-column: 1 / -1;">
        <div class="chart-header">
            <h3 class="chart-title">
                <i class="fas fa-chart-line"></i>
                Requisition Trends
            </h3>
        </div>
        <div class="chart-container">
            <canvas id="trendsChart"></canvas>
        </div>
    </div>

    <!-- Department Comparison Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">
                <i class="fas fa-building"></i>
                Department Comparison
            </h3>
        </div>
        <div class="chart-container">
            <canvas id="departmentChart"></canvas>
        </div>
    </div>

    <!-- Status Distribution Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">
                <i class="fas fa-tasks"></i>
                Status Distribution
            </h3>
        </div>
        <div class="chart-container">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <!-- Top Requesters Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">
                <i class="fas fa-users"></i>
                Top Requesters
            </h3>
        </div>
        <div class="chart-container">
            <canvas id="topRequestersChart"></canvas>
        </div>
    </div>

    <!-- Category Distribution Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">
                <i class="fas fa-tags"></i>
                Category Distribution
            </h3>
        </div>
        <div class="chart-container">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <!-- Monthly Spending Chart -->
    <div class="chart-card" style="grid-column: 1 / -1;">
        <div class="chart-header">
            <h3 class="chart-title">
                <i class="fas fa-calendar-alt"></i>
                Monthly Spending Trends
            </h3>
        </div>
        <div class="chart-container">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <!-- Hourly Distribution Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">
                <i class="fas fa-clock"></i>
                Hourly Distribution
            </h3>
        </div>
        <div class="chart-container">
            <canvas id="hourlyChart"></canvas>
        </div>
    </div>

    <!-- Weekday Distribution Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">
                <i class="fas fa-calendar-week"></i>
                Weekday Distribution
            </h3>
        </div>
        <div class="chart-container">
            <canvas id="weekdayChart"></canvas>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Department Breakdown Table -->
<?php if (!empty($departmentBreakdown)): ?>
<div class="section-header mt-5">
    <div>
        <h2 class="section-title">
            <i class="fas fa-building"></i>
            Department Breakdown
        </h2>
        <p class="section-subtitle">Detailed analysis by department</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th class="text-center">Requisitions</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Avg. Amount</th>
                        <th class="text-end">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departmentBreakdown as $dept): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($dept['department_name']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($dept['department_code']); ?></small>
                            </td>
                            <td class="text-center">
                                <span style="font-weight: var(--font-weight-semibold);">
                                    <?php echo number_format($dept['requisition_count']); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <span style="font-weight: var(--font-weight-semibold);">
                                    <?php echo format_currency($dept['total_amount']); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <?php 
                                $avgAmount = $dept['requisition_count'] > 0 
                                    ? $dept['total_amount'] / $dept['requisition_count'] 
                                    : 0;
                                ?>
                                <span class="text-muted">
                                    <?php echo format_currency($avgAmount); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <?php 
                                $percentage = $statistics['total_amount'] > 0 
                                    ? ($dept['total_amount'] / $statistics['total_amount']) * 100 
                                    : 0;
                                ?>
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <div class="progress" style="width: 60px; height: 8px;">
                                        <div class="progress-bar bg-primary" 
                                             style="width: <?php echo min(100, $percentage); ?>%"></div>
                                    </div>
                                    <span class="text-muted">
                                        <?php echo number_format($percentage, 1); ?>%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Requisitions Table -->
<div class="section-header">
    <div>
        <h2 class="section-title">
            <i class="fas fa-list"></i>
            All Requisitions
        </h2>
        <p class="section-subtitle">Showing <?php echo number_format($statistics['total_count']); ?> requisitions</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($requisitions)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-chart-bar fa-3x"></i>
                </div>
                <h3 class="empty-state-title">No Data Found</h3>
                <p class="empty-state-text">
                    No requisitions match your filter criteria.
                    Try adjusting your filters or check back later.
                </p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Req. No.</th>
                            <th>Date</th>
                            <th>Requester</th>
                            <th>Department</th>
                            <th>Purpose</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requisitions as $req): ?>
                            <tr>
                                <td>
                                    <span style="font-weight: var(--font-weight-medium); color: var(--text-primary);">
                                        <?php echo htmlspecialchars($req['requisition_number']); ?>
                                    </span>
                                </td>
                                <td><?php echo format_date($req['created_at']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($req['requester_first_name'] . ' ' . $req['requester_last_name']); ?>
                                </td>
                                <td>
                                    <span class="text-muted"><?php echo htmlspecialchars($req['department_code'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <span style="max-width: 180px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($req['purpose']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span style="font-weight: var(--font-weight-semibold);">
                                        <?php echo format_currency($req['total_amount']); ?>
                                    </span>
                                </td>
                                <td><?php echo get_status_indicator($req['status']); ?></td>
                                <td class="text-end">
                                    <a href="<?php echo BASE_URL; ?>/requisitions/view.php?id=<?php echo $req['id']; ?>" 
                                       class="btn btn-sm btn-ghost"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="pagination-wrapper">
                    <nav>
                        <ul class="pagination mb-0">
                            <!-- Previous -->
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($pagination['current_page'] - 1); ?>&<?php echo http_build_query($filters); ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <!-- Page Numbers -->
                            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                <li class="page-item <?php echo ($i == $pagination['current_page']) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next -->
                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($pagination['current_page'] + 1); ?>&<?php echo http_build_query($filters); ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    
                    <div class="pagination-info">
                        Showing <?php echo (($pagination['current_page'] - 1) * $pagination['per_page']) + 1; ?> 
                        to <?php echo min($pagination['current_page'] * $pagination['per_page'], $pagination['total_records']); ?> 
                        of <?php echo $pagination['total_records']; ?> requisitions
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Chart Initialization JavaScript -->
<script>
// Chart.js Configuration
Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
Chart.defaults.color = '#64748b';

// Parse PHP data for charts
const chartData = <?php echo json_encode($chartData); ?>;

// 1. Requisition Trends Chart
if (chartData.trends && chartData.trends.length > 0) {
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: chartData.trends.map(item => item.time_period),
            datasets: [{
                label: 'Number of Requisitions',
                data: chartData.trends.map(item => item.count),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            }, {
                label: 'Total Amount',
                data: chartData.trends.map(item => item.total_amount),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Count'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Amount'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
}

// 2. Department Comparison Chart
if (chartData.departments && chartData.departments.length > 0) {
    const deptCtx = document.getElementById('departmentChart').getContext('2d');
    new Chart(deptCtx, {
        type: 'bar',
        data: {
            labels: chartData.departments.map(item => item.department_name),
            datasets: [{
                label: 'Total Amount',
                data: chartData.departments.map(item => item.total_amount),
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', 
                    '#8b5cf6', '#06b6d4', '#ec4899', '#14b8a6'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}

// 3. Status Distribution Chart
if (chartData.status && chartData.status.length > 0) {
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: chartData.status.map(item => item.status.replace(/_/g, ' ').toUpperCase()),
            datasets: [{
                data: chartData.status.map(item => item.count),
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
                    '#8b5cf6', '#06b6d4', '#ec4899'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// 4. Top Requesters Chart
if (chartData.top_requesters && chartData.top_requesters.length > 0) {
    const requestersCtx = document.getElementById('topRequestersChart').getContext('2d');
    new Chart(requestersCtx, {
        type: 'bar',
        data: {
            labels: chartData.top_requesters.map(item => item.user_name),
            datasets: [{
                label: 'Requisitions',
                data: chartData.top_requesters.map(item => item.count),
                backgroundColor: '#3b82f6'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}

// 5. Category Distribution Chart
if (chartData.categories && chartData.categories.length > 0) {
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'pie',
        data: {
            labels: chartData.categories.map(item => item.category),
            datasets: [{
                data: chartData.categories.map(item => item.count),
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
                    '#8b5cf6', '#06b6d4', '#ec4899', '#14b8a6'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// 6. Monthly Spending Chart
if (chartData.monthly && chartData.monthly.length > 0) {
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: chartData.monthly.map(item => item.month),
            datasets: [{
                label: 'Total Amount',
                data: chartData.monthly.map(item => item.total_amount),
                backgroundColor: '#10b981'
            }, {
                label: 'Count',
                data: chartData.monthly.map(item => item.count),
                backgroundColor: '#3b82f6',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Amount'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Count'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
}

// 7. Hourly Distribution Chart
if (chartData.hourly && Array.isArray(chartData.hourly)) {
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: Array.from({length: 24}, (_, i) => `${i}:00`),
            datasets: [{
                label: 'Requisitions',
                data: chartData.hourly,
                backgroundColor: '#8b5cf6'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// 8. Weekday Distribution Chart
if (chartData.weekday && Array.isArray(chartData.weekday)) {
    const weekdayCtx = document.getElementById('weekdayChart').getContext('2d');
    new Chart(weekdayCtx, {
        type: 'bar',
        data: {
            labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            datasets: [{
                label: 'Requisitions',
                data: chartData.weekday,
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Dynamic Filter Handlers
document.getElementById('periodSelect').addEventListener('change', function() {
    const dateFromGroup = document.getElementById('dateFromGroup');
    const dateToGroup = document.getElementById('dateToGroup');
    
    if (this.value === 'custom') {
        dateFromGroup.style.display = 'flex';
        dateToGroup.style.display = 'flex';
    } else {
        dateFromGroup.style.display = 'none';
        dateToGroup.style.display = 'none';
    }
});

document.getElementById('departmentSelect').addEventListener('change', function() {
    if (this.value) {
        // Reload page with department filter to populate users
        const form = this.closest('form');
        const params = new URLSearchParams(new FormData(form));
        window.location.href = '?' + params.toString();
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
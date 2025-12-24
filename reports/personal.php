<?php
/**
 * GateWey Requisition Management System
 * Personal Reports Page - Enhanced with Analytics
 * 
 * File: reports/personal.php
 * Purpose: Generate comprehensive personal requisition reports with charts and analytics
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

// Initialize classes
$report = new Report();

// Get filters from request
$filters = [
    'period' => Sanitizer::string($_GET['period'] ?? ''),
    'date_from' => Sanitizer::string($_GET['date_from'] ?? ''),
    'date_to' => Sanitizer::string($_GET['date_to'] ?? ''),
    'status' => Sanitizer::string($_GET['status'] ?? ''),
    'parent_category_id' => Sanitizer::int($_GET['parent_category_id'] ?? 0),
    'category' => Sanitizer::string($_GET['category'] ?? ''),
    'search' => Sanitizer::string($_GET['search'] ?? ''),
    'interval' => Sanitizer::string($_GET['interval'] ?? 'daily')
];

// Get page
$page = Sanitizer::int($_GET['page'] ?? 1);
$page = max(1, $page);

// Generate report
$reportData = $report->generatePersonalReport($filters, $page, 10);

if (!$reportData['success']) {
    Session::setFlash('error', $reportData['message']);
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

$statistics = $reportData['statistics'];
$requisitions = $reportData['requisitions'];
$pagination = $reportData['pagination'];
$chartData = $reportData['chart_data'];
$analytics = $reportData['analytics'];

// Get parent categories and subcategories for filter
$db = Database::getInstance();
$categoryModel = new RequisitionCategory();

// Get all parent categories (categories with no parent)
$parentCategories = $db->fetchAll(
    "SELECT id, category_name, category_code 
     FROM requisition_categories 
     WHERE parent_id IS NULL AND is_active = 1 
     ORDER BY display_order, category_name"
);

// Get subcategories if parent is selected
$subcategories = [];
if (!empty($filters['parent_category_id'])) {
    $subcategories = $categoryModel->getChildCategories($filters['parent_category_id'], true);
}

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'My Requisition Analytics';
$includeCharts = true;
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Add Status Indicator CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/status-indicators.css">

<!-- Enhanced Dasher UI Styles -->
<style>
    /* Filter Styling */
    .filter-form {
        width: 100%;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-4);
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-2);
    }

    .filter-label {
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
    }

    .filter-select {
        background: var(--bg-input);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-2) var(--spacing-3);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
        transition: var(--theme-transition);
    }

    .filter-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
        outline: none;
    }

    /* Improved Stats Cards */
    .improved-stats-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-6);
        transition: var(--theme-transition);
    }

    .improved-stats-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .improved-stats-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
        margin-bottom: var(--spacing-2);
    }

    .improved-stats-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-xl);
        color: white;
        flex-shrink: 0;
    }

    .improved-stats-icon.primary {
        background-color: var(--primary);
    }

    .improved-stats-icon.success {
        background-color: var(--success);
    }

    .improved-stats-icon.warning {
        background-color: var(--warning);
    }

    .improved-stats-icon.info {
        background-color: var(--info);
    }

    .improved-stats-icon.danger {
        background-color: var(--danger);
    }

    .improved-stats-content {
        flex: 1;
    }

    .improved-stats-title {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0 0 var(--spacing-1) 0;
        font-weight: var(--font-weight-medium);
    }

    .improved-stats-value {
        font-size: var(--font-size-4xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin: 0;
        line-height: 1;
    }

    .improved-stats-change {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-top: var(--spacing-2);
    }

    /* Improved Metric Cards */
    .improved-metric-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-5);
        transition: var(--theme-transition);
        white-space: nowrap;
    }

    .improved-metric-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .improved-metric-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .improved-metric-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-lg);
        color: white;
        flex-shrink: 0;
    }

    .improved-metric-icon.primary {
        background-color: var(--primary);
    }

    .improved-metric-icon.success {
        background-color: var(--success);
    }

    .improved-metric-icon.warning {
        background-color: var(--warning);
    }

    .improved-metric-icon.info {
        background-color: var(--info);
    }

    .improved-metric-content {
        flex: 1;
    }

    .improved-metric-value {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-1);
        line-height: 1;
    }

    .improved-metric-label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0;
    }

    /* Charts Column Layout */
    .charts-column-layout {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-6);
        margin-bottom: var(--spacing-6);
    }

    @media (min-width: 1200px) {
        .charts-column-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--spacing-6);
        }
    }

    /* Mobile Filter Toggle */
    .mobile-filter-toggle {
        display: none;
        gap: var(--spacing-2);
    }

    .mobile-filter-toggle .toggle-icon {
        transition: transform 0.3s ease;
        font-size: var(--font-size-sm);
    }

    .mobile-filter-toggle.active .toggle-icon {
        transform: rotate(180deg);
    }

    /* Filter Collapse */
    .filter-collapse {
        max-height: 1000px;
        overflow: hidden;
        transition: max-height 0.3s ease, opacity 0.3s ease, padding 0.3s ease;
        opacity: 1;
    }

    .filter-collapse.collapsed {
        max-height: 0;
        opacity: 0;
        padding: 0 !important;
    }

    /* Print Styles */
    @media print {
        .navbar,
        .sidebar,
        .footer,
        .content-actions,
        .btn,
        .filter-form,
        .breadcrumb {
            display: none !important;
        }

        .content {
            margin-left: 0 !important;
            margin-top: 0 !important;
            padding: 0 !important;
        }

        .table-container,
        .chart-container {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            margin-bottom: 20px !important;
        }
    }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .mobile-filter-toggle {
            display: inline-flex;
            align-items: center;
        }

        .filter-collapse {
            max-height: 0;
            opacity: 0;
            padding: 0 !important;
        }

        .filter-collapse.show {
            max-height: 2000px;
            opacity: 1;
            padding: var(--spacing-4) !important;
        }

        .chart-grid {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            gap: 0.75rem !important;
            padding-bottom: 0.5rem !important;
        }

        .improved-stats-card {
            flex: 0 0 auto !important;
            min-width: 200px !important;
        }

        .filter-grid {
            grid-template-columns: 1fr;
            gap: var(--spacing-3);
        }

        .charts-column-layout {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-4);
        }

        .btn-group .btn {
            font-size: var(--font-size-xs);
            padding: var(--spacing-1) var(--spacing-2);
        }

        .content-actions .btn {
            white-space: nowrap;
            font-size: var(--font-size-sm);
        }

        .content-actions .btn i {
            margin-right: var(--spacing-2);
        }
    }
</style>


    <!-- Content Header -->
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="content-title">My Requisition Analytics</h1>
                <!-- <nav class="content-breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?php echo BASE_URL; ?>/dashboard/" class="breadcrumb-link">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?php echo BASE_URL; ?>/reports/" class="breadcrumb-link">Reports</a>
                        </li>
                        <li class="breadcrumb-item active">Personal Analytics</li>
                    </ol>
                </nav> -->
            </div>
            <div class="content-actions">
                <button type="button" class="btn btn-primary" id="print-report">
                    <i class="fas fa-print"></i>
                    <span>Print Report</span>
                </button>
                <?php if (!empty($requisitions)): ?>
                    <a href="export-excel.php?<?php echo http_build_query(array_merge($filters, ['type' => 'personal'])); ?>" 
                       class="btn btn-success">
                        <i class="fas fa-file-excel"></i>
                        <span>Export Excel</span>
                    </a>
                <?php endif; ?>
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

    <!-- Enhanced Statistics Cards -->
    <div class="chart-grid">
        <div class="improved-stats-card">
            <div class="improved-stats-header">
                <div class="improved-stats-icon primary">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="improved-stats-content">
                    <h3 class="improved-stats-title">Total Requisitions</h3>
                    <p class="improved-stats-value"><?php echo number_format($statistics['total_count']); ?></p>
                </div>
            </div>
            <div class="improved-stats-change">
                <span>All submitted requisitions</span>
            </div>
        </div>

        <div class="improved-stats-card">
            <div class="improved-stats-header">
                <div class="improved-stats-icon success">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="improved-stats-content">
                    <h3 class="improved-stats-title">Total Amount</h3>
                    <p class="improved-stats-value">₦<?php echo number_format((float)$statistics['total_amount'], 2); ?></p>
                </div>
            </div>
            <div class="improved-stats-change">
                <span>Lifetime spending</span>
            </div>
        </div>

        <div class="improved-stats-card">
            <div class="improved-stats-header">
                <div class="improved-stats-icon info">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="improved-stats-content">
                    <h3 class="improved-stats-title">Completed</h3>
                    <p class="improved-stats-value"><?php echo number_format($statistics['completed_count']); ?></p>
                </div>
            </div>
            <div class="improved-stats-change">
                <span><?php echo $statistics['total_count'] > 0 ? round(($statistics['completed_count'] / $statistics['total_count']) * 100, 1) : 0; ?>% completion rate</span>
            </div>
        </div>

        <div class="improved-stats-card">
            <div class="improved-stats-header">
                <div class="improved-stats-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="improved-stats-content">
                    <h3 class="improved-stats-title">Pending</h3>
                    <p class="improved-stats-value"><?php echo number_format($statistics['pending_count']); ?></p>
                </div>
            </div>
            <div class="improved-stats-change">
                <span>Awaiting approval</span>
            </div>
        </div>

        <div class="improved-stats-card">
            <div class="improved-stats-header">
                <div class="improved-stats-icon danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="improved-stats-content">
                    <h3 class="improved-stats-title">Rejected</h3>
                    <p class="improved-stats-value"><?php echo number_format($statistics['rejected_count']); ?></p>
                </div>
            </div>
            <div class="improved-stats-change">
                <span><?php echo $statistics['total_count'] > 0 ? round(($statistics['rejected_count'] / $statistics['total_count']) * 100, 1) : 0; ?>% rejection rate</span>
            </div>
        </div>

        <div class="improved-stats-card">
            <div class="improved-stats-header">
                <div class="improved-stats-icon" style="background-color: var(--text-secondary);">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="improved-stats-content">
                    <h3 class="improved-stats-title">Average Amount</h3>
                    <p class="improved-stats-value">₦<?php echo number_format((float)$statistics['average_amount'], 2); ?></p>
                </div>
            </div>
            <div class="improved-stats-change">
                <span>Per requisition</span>
            </div>
        </div>
    </div>

    <!-- Enhanced Filters -->
    <div class="table-container">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <h2 class="card-title">Filter Options</h2>
                <!-- Mobile Filter Toggle Button -->
                <button type="button" class="btn btn-outline-primary mobile-filter-toggle" id="mobileFilterToggle">
                    <i class="fas fa-filter"></i>
                    <span>Filters</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </button>
            </div>
        </div>
        <div class="card-body filter-collapse" id="filterCollapse">
            <form action="" method="get" class="filter-form">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="period" class="filter-label">Time Period</label>
                        <select class="filter-select" id="period" name="period">
                            <option value="">All Time</option>
                            <option value="weekly" <?php echo ($filters['period'] === 'weekly') ? 'selected' : ''; ?>>This Week</option>
                            <option value="monthly" <?php echo ($filters['period'] === 'monthly') ? 'selected' : ''; ?>>This Month</option>
                            <option value="quarterly" <?php echo ($filters['period'] === 'quarterly') ? 'selected' : ''; ?>>This Quarter</option>
                            <option value="yearly" <?php echo ($filters['period'] === 'yearly') ? 'selected' : ''; ?>>This Year</option>
                            <option value="custom" <?php echo ($filters['period'] === 'custom') ? 'selected' : ''; ?>>Custom Range</option>
                        </select>
                    </div>

                    <div class="filter-group" id="dateFromGroup" style="display: <?php echo ($filters['period'] === 'custom' || (!empty($filters['date_from']) && empty($filters['period']))) ? 'flex' : 'none'; ?>;">
                        <label for="date_from" class="filter-label">From Date</label>
                        <input type="date" class="filter-select" id="date_from" name="date_from" value="<?php echo $filters['date_from']; ?>">
                    </div>

                    <div class="filter-group" id="dateToGroup" style="display: <?php echo ($filters['period'] === 'custom' || (!empty($filters['date_to']) && empty($filters['period']))) ? 'flex' : 'none'; ?>;">
                        <label for="date_to" class="filter-label">To Date</label>
                        <input type="date" class="filter-select" id="date_to" name="date_to" value="<?php echo $filters['date_to']; ?>">
                    </div>

                    <div class="filter-group">
                        <label for="status" class="filter-label">Status</label>
                        <select class="filter-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="<?php echo STATUS_PENDING_LINE_MANAGER; ?>" <?php echo ($filters['status'] === STATUS_PENDING_LINE_MANAGER) ? 'selected' : ''; ?>>Pending Line Manager</option>
                            <option value="<?php echo STATUS_PENDING_MD; ?>" <?php echo ($filters['status'] === STATUS_PENDING_MD) ? 'selected' : ''; ?>>Pending MD</option>
                            <option value="<?php echo STATUS_PENDING_FINANCE_MANAGER; ?>" <?php echo ($filters['status'] === STATUS_PENDING_FINANCE_MANAGER) ? 'selected' : ''; ?>>Pending Finance</option>
                            <option value="<?php echo STATUS_APPROVED_FOR_PAYMENT; ?>" <?php echo ($filters['status'] === STATUS_APPROVED_FOR_PAYMENT) ? 'selected' : ''; ?>>Approved for Payment</option>
                            <option value="<?php echo STATUS_PAID; ?>" <?php echo ($filters['status'] === STATUS_PAID) ? 'selected' : ''; ?>>Paid</option>
                            <option value="<?php echo STATUS_COMPLETED; ?>" <?php echo ($filters['status'] === STATUS_COMPLETED) ? 'selected' : ''; ?>>Completed</option>
                            <option value="<?php echo STATUS_REJECTED; ?>" <?php echo ($filters['status'] === STATUS_REJECTED) ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>

                    <!-- Parent Category Filter -->
<div class="filter-group">
    <label for="parent_category_id" class="filter-label">Parent Category</label>
    <select class="filter-select" id="parent_category_id" name="parent_category_id">
        <option value="">All Parent Categories</option>
        <?php foreach ($parentCategories as $parent): ?>
            <option value="<?php echo $parent['id']; ?>" 
                    <?php echo ($filters['parent_category_id'] == $parent['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($parent['category_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- Subcategory Filter (dynamically populated) -->
<div class="filter-group" id="subcategoryGroup" 
     style="display: <?php echo !empty($filters['parent_category_id']) ? 'flex' : 'none'; ?>;">
    <label for="category" class="filter-label">Subcategory</label>
    <select class="filter-select" id="category" name="category">
        <option value="">All Subcategories</option>
        <?php foreach ($subcategories as $sub): ?>
            <option value="<?php echo htmlspecialchars($sub['category_name']); ?>" 
                    <?php echo ($filters['category'] === $sub['category_name']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($sub['category_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

                    <div class="filter-group">
                        <label for="interval" class="filter-label">Time Interval</label>
                        <select class="filter-select" id="interval" name="interval">
                            <option value="daily" <?php echo ($filters['interval'] === 'daily') ? 'selected' : ''; ?>>Daily</option>
                            <option value="weekly" <?php echo ($filters['interval'] === 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                            <option value="monthly" <?php echo ($filters['interval'] === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i>
                            <span>Apply Filters</span>
                        </button>
                        <a href="<?php echo BASE_URL; ?>/reports/personal.php" class="btn btn-outline-secondary">
                            <i class="fas fa-sync"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Charts Column Layout -->
    <div class="charts-column-layout">
        <!-- Requisition Trends Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <div>
                    <h2 class="chart-title">Requisition Trends</h2>
                    <p class="chart-subtitle">Submissions over time</p>
                </div>
                <div class="btn-group btn-group-sm" role="group" id="chartTypeToggle">
                    <button type="button" class="btn btn-outline-primary active" data-chart-type="line">Line</button>
                    <button type="button" class="btn btn-outline-primary" data-chart-type="bar">Bar</button>
                    <button type="button" class="btn btn-outline-primary" data-chart-type="area">Area</button>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="requisitionTrendsChart" class="chart-canvas"></canvas>
            </div>
        </div>

        <!-- Status Distribution Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <div>
                    <h2 class="chart-title">Status Distribution</h2>
                    <p class="chart-subtitle">Breakdown by status</p>
                </div>
            </div>
            <div class="chart-body">
                <?php if (empty($chartData['status'])): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-chart-pie" style="font-size: 2rem; color: var(--text-muted); opacity: 0.5;"></i>
                        <p style="color: var(--text-muted);">No data available for the selected filters.</p>
                    </div>
                <?php else: ?>
                    <canvas id="statusDistributionChart" class="chart-canvas"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- More Charts -->
    <div class="charts-column-layout">
        <!-- Category Distribution Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <div>
                    <h2 class="chart-title">Category Distribution</h2>
                    <p class="chart-subtitle">Top 10 categories by count</p>
                </div>
            </div>
            <div class="chart-body">
                <?php if (empty($chartData['categories'])): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-chart-pie" style="font-size: 2rem; color: var(--text-muted); opacity: 0.5;"></i>
                        <p style="color: var(--text-muted);">No category data available.</p>
                    </div>
                <?php else: ?>
                    <canvas id="categoryDistributionChart" class="chart-canvas"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <!-- Monthly Spending Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <div>
                    <h2 class="chart-title">Monthly Spending</h2>
                    <p class="chart-subtitle">Last 12 months</p>
                </div>
            </div>
            <div class="chart-body">
                <?php if (empty($chartData['monthly'])): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-chart-bar" style="font-size: 2rem; color: var(--text-muted); opacity: 0.5;"></i>
                        <p style="color: var(--text-muted);">No monthly data available.</p>
                    </div>
                <?php else: ?>
                    <canvas id="monthlySpendingChart" class="chart-canvas"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Distribution Charts -->
    <div class="charts-column-layout">
        <div class="chart-container">
            <div class="chart-header">
                <div>
                    <h2 class="chart-title">Hourly Submission Pattern</h2>
                    <p class="chart-subtitle">Submissions by hour of day</p>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="hourlyDistributionChart" class="chart-canvas"></canvas>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart-header">
                <div>
                    <h2 class="chart-title">Day of Week Analysis</h2>
                    <p class="chart-subtitle">Submissions by weekday</p>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="weekdayDistributionChart" class="chart-canvas"></canvas>
            </div>
        </div>
    </div>

    <!-- Advanced Analytics -->
    <div class="table-container">
        <div class="card-header">
            <h2 class="card-title">Advanced Analytics</h2>
        </div>
        <div class="card-body">
            <div class="chart-grid">
                <div class="improved-metric-card">
                    <div class="improved-metric-header">
                        <div class="improved-metric-icon primary">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="improved-metric-content">
                            <div class="improved-metric-value"><?php echo $analytics['avg_per_month']; ?></div>
                            <div class="improved-metric-label">Average Per Month</div>
                        </div>
                    </div>
                </div>

                <div class="improved-metric-card">
                    <div class="improved-metric-header">
                        <div class="improved-metric-icon success">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="improved-metric-content">
                            <div class="improved-metric-value"><?php echo $analytics['avg_approval_days']; ?> days</div>
                            <div class="improved-metric-label">Avg Approval Time</div>
                        </div>
                    </div>
                </div>

                <div class="improved-metric-card">
                    <div class="improved-metric-header">
                        <div class="improved-metric-icon warning">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="improved-metric-content">
                            <div class="improved-metric-value" style="font-size: var(--font-size-lg);"><?php echo htmlspecialchars($analytics['most_common_category']); ?></div>
                            <div class="improved-metric-label">Most Common Category</div>
                        </div>
                    </div>
                </div>

                <div class="improved-metric-card">
                    <div class="improved-metric-header">
                        <div class="improved-metric-icon info">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div class="improved-metric-content">
                            <div class="improved-metric-value" style="font-size: var(--font-size-xl);"><?php echo $analytics['busiest_day']; ?></div>
                            <div class="improved-metric-label">Busiest Submission Day</div>
                        </div>
                    </div>
                </div>

                <div class="improved-metric-card">
                    <div class="improved-metric-header">
                        <div class="improved-metric-icon" style="background-color: var(--primary);">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="improved-metric-content">
                            <div class="improved-metric-value" style="font-size: var(--font-size-lg);"><?php echo $analytics['busiest_hour']; ?></div>
                            <div class="improved-metric-label">Peak Submission Time</div>
                        </div>
                    </div>
                </div>

                <div class="improved-metric-card">
                    <div class="improved-metric-header">
                        <div class="improved-metric-icon success">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="improved-metric-content">
                            <div class="improved-metric-value"><?php echo $analytics['approval_rate']; ?>%</div>
                            <div class="improved-metric-label">Approval Rate</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Requisitions Table -->
    <div class="table-container">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="card-title">Recent Requisitions</h2>
                <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="btn btn-primary">
                    <i class="fas fa-list"></i>
                    <span>View All</span>
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Req. No.</th>
                        <th>Category</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Created On</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requisitions)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div style="color: var(--text-muted);">
                                    <i class="fas fa-file-alt" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                    <p>No requisitions found for the selected filters.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requisitions as $req): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo build_encrypted_url(BASE_URL . '/requisitions/view.php', $req['id']); ?>"
                                        style="color: var(--primary); text-decoration: none; font-weight: var(--font-weight-medium);">
                                        <?php echo htmlspecialchars($req['requisition_number']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span style="color: var(--info);"><?php echo htmlspecialchars($req['category_name'] ?? 'Other'); ?></span>
                                </td>
                                <td>
                                    <span style="max-width: 200px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($req['purpose']); ?>
                                    </span>
                                </td>
                                <td><?php echo get_status_indicator($req['status'], $req); ?></td>
                                <td style="color: var(--text-secondary); font-size: var(--font-size-sm);">
                                    <?php echo date('M d, Y g:i A', strtotime($req['created_at'])); ?>
                                </td>
                                <td class="text-end">
                                    <span style="font-weight: var(--font-weight-semibold);">₦<?php echo number_format((float)$req['total_amount'], 2); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?php echo build_encrypted_url(BASE_URL . '/requisitions/view.php', $req['id']); ?>"
                                       class="btn btn-sm btn-ghost"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- Dasher Chart Configuration and Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('ðŸŽ¨ Initializing Dasher UI Requisition Analytics...');

    function getDasherChartConfig() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

        return {
            colors: {
                primary: getComputedStyle(document.documentElement).getPropertyValue('--primary').trim(),
                success: getComputedStyle(document.documentElement).getPropertyValue('--success').trim(),
                warning: getComputedStyle(document.documentElement).getPropertyValue('--warning').trim(),
                danger: getComputedStyle(document.documentElement).getPropertyValue('--danger').trim(),
                info: getComputedStyle(document.documentElement).getPropertyValue('--info').trim(),
                text: getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim(),
                textSecondary: getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim(),
                border: getComputedStyle(document.documentElement).getPropertyValue('--border-color').trim()
            },
            font: {
                family: getComputedStyle(document.documentElement).getPropertyValue('--font-family-base').trim(),
                size: 12,
                weight: '400'
            }
        };
    }

    const chartConfig = getDasherChartConfig();

    const colorSet = [
        chartConfig.colors.primary + 'B3',
        chartConfig.colors.danger + 'B3',
        chartConfig.colors.success + 'B3',
        chartConfig.colors.warning + 'B3',
        chartConfig.colors.info + 'B3',
        '#9b59b6B3', '#e74c3cB3', '#3498dbB3', '#f39c12B3', '#1abc9cB3', '#34495eB3', '#e91e63B3'
    ];

    // Prepare trends data
    const trendsData = <?php echo json_encode($chartData['trends']); ?>;
    const trendLabels = trendsData.map(item => item.time_period);
    const trendCounts = trendsData.map(item => parseInt(item.count));

    // Requisition Trends Chart
    const requisitionTrendsChart = new Chart(
        document.getElementById('requisitionTrendsChart'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Requisitions',
                    data: trendCounts,
                    backgroundColor: chartConfig.colors.primary + '20',
                    borderColor: chartConfig.colors.primary,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: chartConfig.colors.primary,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: chartConfig.colors.text + '10',
                        titleColor: chartConfig.colors.text,
                        bodyColor: chartConfig.colors.text,
                        borderColor: chartConfig.colors.border,
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 12,
                        titleFont: {
                            family: chartConfig.font.family,
                            size: 14,
                            weight: '600'
                        },
                        bodyFont: {
                            family: chartConfig.font.family,
                            size: 13
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: chartConfig.colors.border + '40',
                            drawBorder: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            precision: 0,
                            color: chartConfig.colors.textSecondary,
                            font: {
                                family: chartConfig.font.family,
                                size: 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: chartConfig.colors.textSecondary,
                            font: {
                                family: chartConfig.font.family,
                                size: 12
                            }
                        }
                    }
                }
            }
        }
    );

    // Chart Type Toggle
    document.getElementById('chartTypeToggle').addEventListener('click', function(e) {
        if (e.target.tagName === 'BUTTON') {
            this.querySelectorAll('.btn').forEach(btn => btn.classList.remove('active'));
            e.target.classList.add('active');

            const chartType = e.target.getAttribute('data-chart-type');

            if (chartType === 'area') {
                requisitionTrendsChart.config.type = 'line';
                requisitionTrendsChart.data.datasets[0].fill = true;
            } else {
                requisitionTrendsChart.config.type = chartType;
                requisitionTrendsChart.data.datasets[0].fill = false;
            }

            requisitionTrendsChart.update();
        }
    });

    // Status Distribution Chart
    <?php if (!empty($chartData['status'])): ?>
        const statusData = <?php echo json_encode($chartData['status']); ?>;
        const statusLabels = statusData.map(item => {
            const statusMap = {
                'pending_line_manager': 'Pending Line Mgr',
                'pending_md': 'Pending MD',
                'pending_finance_manager': 'Pending Finance',
                'approved_for_payment': 'Approved',
                'paid': 'Paid',
                'completed': 'Completed',
                'rejected': 'Rejected',
                'cancelled': 'Cancelled'
            };
            return statusMap[item.status] || item.status;
        });
        const statusCounts = statusData.map(item => parseInt(item.count));

        new Chart(document.getElementById('statusDistributionChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusCounts,
                    backgroundColor: colorSet,
                    borderColor: colorSet.map(color => color.replace('B3', '')),
                    borderWidth: 0,
                    hoverBorderWidth: 2,
                    cutout: '60%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: chartConfig.colors.text,
                            font: {
                                family: chartConfig.font.family,
                                size: 12
                            },
                            padding: 10,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: chartConfig.colors.text + '10',
                        titleColor: chartConfig.colors.text,
                        bodyColor: chartConfig.colors.text,
                        borderColor: chartConfig.colors.border,
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 12
                    }
                }
            }
        });
    <?php endif; ?>

    // Category Distribution Chart
    <?php if (!empty($chartData['categories'])): ?>
        const categoryData = <?php echo json_encode($chartData['categories']); ?>;
        const categoryLabels = categoryData.map(item => item.category);
        const categoryCounts = categoryData.map(item => parseInt(item.count));

        new Chart(document.getElementById('categoryDistributionChart'), {
            type: 'pie',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryCounts,
                    backgroundColor: colorSet,
                    borderColor: colorSet.map(color => color.replace('B3', '')),
                    borderWidth: 0,
                    hoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: chartConfig.colors.text,
                            font: {
                                family: chartConfig.font.family,
                                size: 12
                            },
                            padding: 10,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: chartConfig.colors.text + '10',
                        titleColor: chartConfig.colors.text,
                        bodyColor: chartConfig.colors.text,
                        borderColor: chartConfig.colors.border,
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 12
                    }
                }
            }
        });
    <?php endif; ?>

    // Monthly Spending Chart
    <?php if (!empty($chartData['monthly'])): ?>
        const monthlyData = <?php echo json_encode($chartData['monthly']); ?>;
        const monthlyLabels = monthlyData.map(item => item.month);
        const monthlyAmounts = monthlyData.map(item => parseFloat(item.total_amount));

        new Chart(document.getElementById('monthlySpendingChart'), {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Total Spending',
                    data: monthlyAmounts,
                    backgroundColor: chartConfig.colors.success + '20',
                    borderColor: chartConfig.colors.success,
                    borderWidth: 2,
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: chartConfig.colors.text + '10',
                        titleColor: chartConfig.colors.text,
                        bodyColor: chartConfig.colors.text,
                        borderColor: chartConfig.colors.border,
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return 'Amount: â‚¦' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: chartConfig.colors.border + '40',
                            drawBorder: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: chartConfig.colors.textSecondary,
                            callback: function(value) {
                                return 'â‚¦' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: chartConfig.colors.textSecondary
                        }
                    }
                }
            }
        });
    <?php endif; ?>

    // Hourly Distribution Chart
    const hourlyData = <?php echo json_encode($chartData['hourly']); ?>;
    new Chart(document.getElementById('hourlyDistributionChart'), {
        type: 'bar',
        data: {
            labels: Array.from({length: 24}, (_, i) => `${i}:00`),
            datasets: [{
                label: 'Submissions by Hour',
                data: hourlyData,
                backgroundColor: chartConfig.colors.info + '20',
                borderColor: chartConfig.colors.info,
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: chartConfig.colors.text + '10',
                    titleColor: chartConfig.colors.text,
                    bodyColor: chartConfig.colors.text,
                    borderColor: chartConfig.colors.border,
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: chartConfig.colors.border + '40',
                        drawBorder: false
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        precision: 0,
                        color: chartConfig.colors.textSecondary
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        color: chartConfig.colors.textSecondary
                    }
                }
            }
        }
    });

    // Weekday Distribution Chart
    const weekdayData = <?php echo json_encode($chartData['weekday']); ?>;
    const weekdayLabels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    new Chart(document.getElementById('weekdayDistributionChart'), {
        type: 'bar',
        data: {
            labels: weekdayLabels,
            datasets: [{
                label: 'Submissions by Day',
                data: weekdayData,
                backgroundColor: chartConfig.colors.warning + '20',
                borderColor: chartConfig.colors.warning,
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: chartConfig.colors.text + '10',
                    titleColor: chartConfig.colors.text,
                    bodyColor: chartConfig.colors.text,
                    borderColor: chartConfig.colors.border,
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: chartConfig.colors.border + '40',
                        drawBorder: false
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        precision: 0,
                        color: chartConfig.colors.textSecondary
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        color: chartConfig.colors.textSecondary
                    }
                }
            }
        }
    });

    // Print Report
    document.getElementById('print-report').addEventListener('click', function() {
        window.print();
    });

    // Period select change handler
    document.getElementById('period').addEventListener('change', function() {
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

    // Mobile Filter Toggle
    const mobileFilterToggle = document.getElementById('mobileFilterToggle');
    const filterCollapse = document.getElementById('filterCollapse');

    if (mobileFilterToggle && filterCollapse) {
        mobileFilterToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            filterCollapse.classList.toggle('show');
            filterCollapse.classList.toggle('collapsed');

            // Update button text
            const buttonText = this.querySelector('span');
            if (filterCollapse.classList.contains('show')) {
                buttonText.textContent = 'Hide Filters';
            } else {
                buttonText.textContent = 'Filters';
            }
        });

        // Initialize collapsed state on mobile
        if (window.innerWidth <= 768) {
            filterCollapse.classList.add('collapsed');
        }
    }

    // Update charts when theme changes
    document.addEventListener('themeChanged', function(event) {
        console.log('ðŸŽ¨ Updating requisition analytics charts for theme:', event.detail.theme);

        const newConfig = getDasherChartConfig();

        Chart.helpers.each(Chart.instances, function(instance) {
            if (instance.options.plugins && instance.options.plugins.legend) {
                instance.options.plugins.legend.labels.color = newConfig.colors.text;
            }

            if (instance.options.scales) {
                Object.keys(instance.options.scales).forEach(scale => {
                    if (instance.options.scales[scale].ticks) {
                        instance.options.scales[scale].ticks.color = newConfig.colors.textSecondary;
                    }
                    if (instance.options.scales[scale].grid) {
                        instance.options.scales[scale].grid.color = newConfig.colors.border + '40';
                    }
                });
            }

            instance.update('none');
        });
    });

    console.log('âœ… Dasher UI Requisition Analytics initialized successfully');
});
// Parent Category Change Handler - Load Subcategories
document.getElementById('parent_category_id').addEventListener('change', function() {
    const parentId = this.value;
    const subcategoryGroup = document.getElementById('subcategoryGroup');
    const subcategorySelect = document.getElementById('category');
    
    if (parentId) {
        // Show subcategory dropdown
        subcategoryGroup.style.display = 'flex';
        
        // Fetch subcategories via AJAX
        fetch(`<?php echo BASE_URL; ?>/api/get-child-categories.php?parent_id=${parentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear existing options
                    subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
                    
                    // Add new options from children array
                    data.children.forEach(child => {
                        const option = document.createElement('option');
                        option.value = child.category_name;
                        option.textContent = child.category_name;
                        subcategorySelect.appendChild(option);
                    });
                    
                    console.log(`✅ Loaded ${data.count} subcategories for parent: ${data.parent.name}`);
                } else {
                    console.error('Error fetching subcategories:', data.error);
                    alert('Error loading subcategories: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error fetching subcategories:', error);
                alert('Error loading subcategories. Please try again.');
            });
    } else {
        // Hide subcategory dropdown
        subcategoryGroup.style.display = 'none';
        subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
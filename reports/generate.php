<?php
/**
 * GateWey Requisition Management System
 * Main Report Generation Page
 * 
 * File: reports/generate.php
 * Purpose: Landing page for report generation with quick access to all report types
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';

// Get user role info
$roleId = Session::getUserRoleId();
$canViewDepartment = is_line_manager();
$canViewOrganization = is_managing_director() || is_finance_manager();

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Generate Reports';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">
                <i class="fas fa-chart-bar me-2"></i>Generate Reports
            </h1>
            <p class="content-subtitle">Analyze requisition data with customizable reports</p>
        </div>
        <div>
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

<!-- Info Alert -->
<div class="alert alert-info">
    <h6 class="alert-heading">
        <i class="fas fa-info-circle"></i> About Reports
    </h6>
    <p class="mb-0">
        Generate detailed reports with customizable filters including time periods, status, departments, and more.
        Export your reports to Excel for offline analysis and record keeping.
    </p>
</div>

<!-- Report Type Cards -->
<div class="report-cards-grid">
    <!-- Personal Reports -->
    <div class="report-card">
        <div class="report-card-icon bg-info">
            <i class="fas fa-user"></i>
        </div>
        <div class="report-card-content">
            <h3 class="report-card-title">Personal Reports</h3>
            <p class="report-card-description">
                View all your requisitions with detailed statistics and analysis.
                Track your spending patterns and requisition history.
            </p>
            <ul class="report-card-features">
                <li><i class="fas fa-check text-success"></i> Your requisitions only</li>
                <li><i class="fas fa-check text-success"></i> Time period filters</li>
                <li><i class="fas fa-check text-success"></i> Status breakdown</li>
                <li><i class="fas fa-check text-success"></i> Excel export</li>
            </ul>
        </div>
        <div class="report-card-footer">
            <a href="personal.php" class="btn btn-primary btn-block">
                <i class="fas fa-chart-line"></i> Generate Personal Report
            </a>
        </div>
    </div>

    <!-- Department Reports -->
    <?php if ($canViewDepartment): ?>
        <div class="report-card">
            <div class="report-card-icon bg-warning">
                <i class="fas fa-users"></i>
            </div>
            <div class="report-card-content">
                <h3 class="report-card-title">Department Reports</h3>
                <p class="report-card-description">
                    Analyze all requisitions from your department team members.
                    Monitor department spending and team performance.
                </p>
                <ul class="report-card-features">
                    <li><i class="fas fa-check text-success"></i> All department requisitions</li>
                    <li><i class="fas fa-check text-success"></i> Filter by team member</li>
                    <li><i class="fas fa-check text-success"></i> Department statistics</li>
                    <li><i class="fas fa-check text-success"></i> Excel export</li>
                </ul>
            </div>
            <div class="report-card-footer">
                <a href="department.php" class="btn btn-warning btn-block">
                    <i class="fas fa-chart-bar"></i> Generate Department Report
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Organization Reports -->
    <?php if ($canViewOrganization): ?>
        <div class="report-card">
            <div class="report-card-icon bg-success">
                <i class="fas fa-building"></i>
            </div>
            <div class="report-card-content">
                <h3 class="report-card-title">Organization Reports</h3>
                <p class="report-card-description">
                    Comprehensive organization-wide requisition analysis.
                    Track spending across all departments with detailed breakdowns.
                </p>
                <ul class="report-card-features">
                    <li><i class="fas fa-check text-success"></i> All requisitions</li>
                    <li><i class="fas fa-check text-success"></i> Department breakdown</li>
                    <li><i class="fas fa-check text-success"></i> Advanced filters</li>
                    <li><i class="fas fa-check text-success"></i> Excel export</li>
                </ul>
            </div>
            <div class="report-card-footer">
                <a href="organization.php" class="btn btn-success btn-block">
                    <i class="fas fa-chart-pie"></i> Generate Organization Report
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Report Features Section -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-star"></i> Report Features
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-filter"></i>
                    </div>
                    <div class="feature-content">
                        <h6 class="feature-title">Advanced Filters</h6>
                        <p class="feature-description">
                            Filter by time periods (weekly, monthly, quarterly, yearly, custom),
                            status, departments, users, and search terms.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="feature-content">
                        <h6 class="feature-title">Detailed Statistics</h6>
                        <p class="feature-description">
                            View comprehensive statistics including total amounts, averages,
                            status breakdowns, and department comparisons.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-file-excel"></i>
                    </div>
                    <div class="feature-content">
                        <h6 class="feature-title">Excel Export</h6>
                        <p class="feature-description">
                            Export reports to Excel with formatted data, summary sheets,
                            and detailed requisition information for offline analysis.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Tips -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-lightbulb"></i> Quick Tips
        </h5>
    </div>
    <div class="card-body">
        <ul class="mb-0">
            <li class="mb-2">
                <strong>Time Periods:</strong> Use preset time periods for quick analysis or select custom date ranges for specific periods.
            </li>
            <li class="mb-2">
                <strong>Export to Excel:</strong> Click the "Export to Excel" button to download a formatted spreadsheet with summary and detailed data sheets.
            </li>
            <li class="mb-2">
                <strong>Status Filters:</strong> Filter by specific statuses to analyze requisitions at different stages of the approval workflow.
            </li>
            <li class="mb-0">
                <strong>Search Function:</strong> Use the search box to quickly find specific requisitions by number or purpose.
            </li>
        </ul>
    </div>
</div>

<style>
.report-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: var(--spacing-6);
    margin-top: var(--spacing-4);
}

.report-card {
    background: transparent;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    transition: var(--theme-transition);
    display: flex;
    flex-direction: column;
}

.report-card:hover {
    border-color: var(--primary);
    box-shadow: var(--shadow-md);
    transform: translateY(-4px);
}

.report-card-icon {
    width: 64px;
    height: 64px;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-3xl);
    color: white;
    margin: var(--spacing-6) var(--spacing-6) var(--spacing-4);
}

.report-card-icon.bg-info {
    background: var(--info);
}

.report-card-icon.bg-warning {
    background: var(--warning);
}

.report-card-icon.bg-success {
    background: var(--success);
}

.report-card-content {
    padding: 0 var(--spacing-6) var(--spacing-6);
    flex: 1;
}

.report-card-title {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin-bottom: var(--spacing-3);
}

.report-card-description {
    color: var(--text-secondary);
    font-size: var(--font-size-sm);
    margin-bottom: var(--spacing-4);
    line-height: var(--line-height-relaxed);
}

.report-card-features {
    list-style: none;
    padding: 0;
    margin: 0;
}

.report-card-features li {
    padding: var(--spacing-2) 0;
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.report-card-footer {
    padding: var(--spacing-6);
    border-top: 1px solid var(--border-color);
}

.btn-block {
    width: 100%;
    justify-content: center;
}

.feature-item {
    display: flex;
    gap: var(--spacing-4);
}

.feature-icon {
    width: 48px;
    height: 48px;
    background: var(--bg-subtle);
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: var(--font-size-xl);
    flex-shrink: 0;
}

.feature-content {
    flex: 1;
}

.feature-title {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin-bottom: var(--spacing-2);
}

.feature-description {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin: 0;
    line-height: var(--line-height-relaxed);
}

@media (max-width: 768px) {
    .report-cards-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
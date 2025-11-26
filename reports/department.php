<?php
/**
 * GateWey Requisition Management System
 * Department Reports Page
 * 
 * File: reports/department.php
 * Purpose: Generate department requisition reports (Line Manager view)
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';

// Check if user is Line Manager
if (!is_line_manager()) {
    Session::setFlash('error', 'Only Line Managers can access department reports.');
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

// Load helpers
require_once __DIR__ . '/../helpers/status-indicator.php';

// Initialize classes
$report = new Report();

// Get filters from request
$filters = [
    'period' => Sanitizer::string($_GET['period'] ?? ''),
    'date_from' => Sanitizer::string($_GET['date_from'] ?? ''),
    'date_to' => Sanitizer::string($_GET['date_to'] ?? ''),
    'status' => Sanitizer::string($_GET['status'] ?? ''),
    'user_id' => Sanitizer::int($_GET['user_id'] ?? 0),
    'search' => Sanitizer::string($_GET['search'] ?? '')
];

// Get page
$page = Sanitizer::int($_GET['page'] ?? 1);
$page = max(1, $page);

// Generate report
$reportData = $report->generateDepartmentReport($filters, $page, 15);

if (!$reportData['success']) {
    Session::setFlash('error', $reportData['message']);
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

$statistics = $reportData['statistics'];
$requisitions = $reportData['requisitions'];
$pagination = $reportData['pagination'];

// Get department users for filter
$user = new User();
$departmentUsers = $user->getByDepartment(Session::getUserDepartmentId());

// Get department info
$department = new Department();
$departmentInfo = $department->getById(Session::getUserDepartmentId());

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Department Reports';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Add Status Indicator CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/status-indicators.css">

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">
                <i class="fas fa-chart-bar me-2"></i>Department Reports
            </h1>
            <p class="content-subtitle">
                <?php echo htmlspecialchars($departmentInfo['department_name']); ?> - Team Requisition Analysis
            </p>
        </div>
        <div class="d-flex gap-2">
            <?php if (!empty($requisitions)): ?>
                <a href="export-excel.php?<?php echo http_build_query(array_merge($filters, ['type' => 'department'])); ?>" 
                   class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/dashboard/line-manager.php" class="btn btn-ghost">
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
<div class="stats-grid">
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
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Team Members</p>
            <p class="stat-value"><?php echo count($departmentUsers); ?></p>
        </div>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info mt-4">
    <h6 class="alert-heading">
        <i class="fas fa-info-circle"></i> About Department Reports
    </h6>
    <p class="mb-0">
        This report shows all requisitions from your department (<?php echo htmlspecialchars($departmentInfo['department_name']); ?>).
        Filter by team member, time period, or status to analyze spending patterns.
    </p>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-filter"></i> Report Filters
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <!-- Time Period -->
            <div class="col-md-3">
                <label class="form-label">Time Period</label>
                <select name="period" class="form-control" id="periodSelect">
                    <option value="">All Time</option>
                    <option value="weekly" <?php echo ($filters['period'] === 'weekly') ? 'selected' : ''; ?>>This Week</option>
                    <option value="monthly" <?php echo ($filters['period'] === 'monthly') ? 'selected' : ''; ?>>This Month</option>
                    <option value="quarterly" <?php echo ($filters['period'] === 'quarterly') ? 'selected' : ''; ?>>This Quarter</option>
                    <option value="yearly" <?php echo ($filters['period'] === 'yearly') ? 'selected' : ''; ?>>This Year</option>
                    <option value="custom" <?php echo ($filters['period'] === 'custom') ? 'selected' : ''; ?>>Custom Range</option>
                </select>
            </div>

            <!-- Date From -->
            <div class="col-md-2" id="dateFromGroup" style="display: <?php echo ($filters['period'] === 'custom' || (!empty($filters['date_from']) && empty($filters['period']))) ? 'block' : 'none'; ?>;">
                <label class="form-label">Date From</label>
                <input type="date" 
                       name="date_from" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($filters['date_from']); ?>">
            </div>

            <!-- Date To -->
            <div class="col-md-2" id="dateToGroup" style="display: <?php echo ($filters['period'] === 'custom' || (!empty($filters['date_to']) && empty($filters['period']))) ? 'block' : 'none'; ?>;">
                <label class="form-label">Date To</label>
                <input type="date" 
                       name="date_to" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($filters['date_to']); ?>">
            </div>

            <!-- Team Member Filter -->
            <div class="col-md-3">
                <label class="form-label">Team Member</label>
                <select name="user_id" class="form-control">
                    <option value="">All Team Members</option>
                    <?php foreach ($departmentUsers as $member): ?>
                        <option value="<?php echo $member['id']; ?>" 
                                <?php echo ($filters['user_id'] == $member['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="<?php echo STATUS_PENDING_LINE_MANAGER; ?>" <?php echo ($filters['status'] === STATUS_PENDING_LINE_MANAGER) ? 'selected' : ''; ?>>Pending My Approval</option>
                    <option value="<?php echo STATUS_PENDING_MD; ?>" <?php echo ($filters['status'] === STATUS_PENDING_MD) ? 'selected' : ''; ?>>Pending MD</option>
                    <option value="<?php echo STATUS_PENDING_FINANCE_MANAGER; ?>" <?php echo ($filters['status'] === STATUS_PENDING_FINANCE_MANAGER) ? 'selected' : ''; ?>>Pending Finance</option>
                    <option value="<?php echo STATUS_APPROVED_FOR_PAYMENT; ?>" <?php echo ($filters['status'] === STATUS_APPROVED_FOR_PAYMENT) ? 'selected' : ''; ?>>Approved for Payment</option>
                    <option value="<?php echo STATUS_PAID; ?>" <?php echo ($filters['status'] === STATUS_PAID) ? 'selected' : ''; ?>>Paid</option>
                    <option value="<?php echo STATUS_COMPLETED; ?>" <?php echo ($filters['status'] === STATUS_COMPLETED) ? 'selected' : ''; ?>>Completed</option>
                    <option value="<?php echo STATUS_REJECTED; ?>" <?php echo ($filters['status'] === STATUS_REJECTED) ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>

            <!-- Search -->
            <div class="col-md-12">
                <label class="form-label">Search</label>
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search by requisition number or purpose..."
                       value="<?php echo htmlspecialchars($filters['search']); ?>">
            </div>

            <!-- Action Buttons -->
            <div class="col-md-12 d-flex gap-2 justify-content-end">
                <a href="?" class="btn btn-ghost">
                    <i class="fas fa-redo"></i> Reset
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Report Results Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> Department Requisitions
            </h5>
            <span class="text-muted">Showing <?php echo number_format($statistics['total_count']); ?> requisitions</span>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($requisitions)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-chart-bar fa-3x"></i>
                </div>
                <h3 class="empty-state-title">No Data Found</h3>
                <p class="empty-state-text">
                    No requisitions match your filter criteria for this department.
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
                                    <span style="max-width: 200px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
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

<!-- Status Breakdown (if data exists) -->
<?php if (!empty($statistics['status_breakdown'])): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-chart-pie"></i> Status Breakdown
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($statistics['status_breakdown'] as $statusData): ?>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex justify-content-between align-items-center p-3 bg-subtle rounded">
                            <div>
                                <?php echo get_status_indicator($statusData['status']); ?>
                            </div>
                            <div class="text-end">
                                <div style="font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); color: var(--text-primary);">
                                    <?php echo $statusData['count']; ?>
                                </div>
                                <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">
                                    <?php echo format_currency($statusData['total']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- JavaScript for Dynamic Date Fields -->
<script>
document.getElementById('periodSelect').addEventListener('change', function() {
    const dateFromGroup = document.getElementById('dateFromGroup');
    const dateToGroup = document.getElementById('dateToGroup');
    
    if (this.value === 'custom') {
        dateFromGroup.style.display = 'block';
        dateToGroup.style.display = 'block';
    } else {
        dateFromGroup.style.display = 'none';
        dateToGroup.style.display = 'none';
    }
});
</script>

<style>
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
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
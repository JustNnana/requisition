<?php
/**
 * GateWey Requisition Management System
 * View Budget Details Page - Dasher UI Enhanced
 * 
 * File: finance/budget/view-budget.php
 * Purpose: Display detailed view of a single budget
 */

// Define access level
define('APP_ACCESS', true);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../helpers/permissions.php';

Session::start();
require_once __DIR__ . '/../../middleware/auth-check.php';
require_once __DIR__ . '/../../middleware/role-check.php';

// Only Finance Manager can access
checkRole(ROLE_FINANCE_MANAGER);

// Get budget ID (encrypted)
$budgetId = get_encrypted_id();

if (!$budgetId) {
    Session::setFlash('error', 'Invalid budget ID.');
    header('Location: index.php');
    exit;
}

// Initialize Budget object
$budgetModel = new Budget();

// Get budget data with statistics
$budgetData = $budgetModel->getBudgetStats(null, $budgetId);

if (!$budgetData) {
    Session::setFlash('error', 'Budget not found.');
    header('Location: index.php');
    exit;
}

// Get allocation history
$allocations = $budgetModel->getAllocationHistory($budgetId);

// Calculate additional metrics
$utilizationPercentage = (float)$budgetData['utilization_percentage'];
$daysRemaining = 0;
$daysTotal = 0;
$isExpired = false;
$isExpiringSoon = false;

if ($budgetData['status'] === 'active' || $budgetData['status'] === 'upcoming') {
    $today = new DateTime();
    $endDate = new DateTime($budgetData['end_date']);
    $startDate = new DateTime($budgetData['start_date']);
    
    $daysRemaining = max(0, $today->diff($endDate)->days);
    $daysTotal = $startDate->diff($endDate)->days;
    
    if ($budgetData['status'] === 'active' && $daysRemaining <= 7) {
        $isExpiringSoon = true;
    }
} elseif ($budgetData['status'] === 'expired') {
    $isExpired = true;
}

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Budget Details - ' . $budgetData['department_name'];

// Helper function to get status color
function getBudgetStatusClass($status) {
    switch ($status) {
        case 'active': return 'success';
        case 'expired': return 'danger';
        case 'upcoming': return 'info';
        default: return 'secondary';
    }
}

// Helper function to get utilization color
function getUtilizationColor($percentage) {
    if ($percentage >= 90) return 'danger';
    if ($percentage >= 75) return 'warning';
    return 'success';
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<!-- Dasher UI Enhanced Styles (Same as requisitions/view.php) -->
<style>
    /* Main Layout Grid */
    .budget-view-grid {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: var(--spacing-6);
        margin: 0 auto;
    }

    @media (max-width: 992px) {
        .budget-view-grid {
            grid-template-columns: 1fr;
        }

        .budget-sidebar {
            order: 0;
        }
    }

    /* Section Cards */
    .section-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        margin-bottom: var(--spacing-5);
        overflow: hidden;
        transition: var(--theme-transition);
    }

    .section-card:hover {
        box-shadow: var(--shadow-sm);
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--spacing-5);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
    }

    .section-header-content {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .section-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-lg);
        flex-shrink: 0;
    }

    .section-icon.primary {
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
    }

    .section-icon.success {
        background: rgba(var(--success-rgb), 0.1);
        color: var(--success);
    }

    .section-icon.info {
        background: rgba(var(--info-rgb), 0.1);
        color: var(--info);
    }

    .section-icon.warning {
        background: rgba(var(--warning-rgb), 0.1);
        color: var(--warning);
    }

    .section-title {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-1);
    }

    .section-title h5 {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
    }

    .section-title p {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        margin: 0;
    }

    .section-body {
        padding: var(--spacing-5);
    }

    /* Status Banner */
    .status-banner {
        background: transparent;
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-5);
        margin-bottom: var(--spacing-5);
        border: 1px solid var(--border-color);
        transition: var(--theme-transition);
    }

    .status-banner.active {
        border-left: 4px solid var(--success);
        background: rgba(var(--success-rgb), 0.02);
    }

    .status-banner.expired {
        border-left: 4px solid var(--danger);
        background: rgba(var(--danger-rgb), 0.02);
    }

    .status-banner.upcoming {
        border-left: 4px solid var(--info);
        background: rgba(var(--info-rgb), 0.02);
    }

    .status-banner-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: var(--spacing-4);
    }

    .status-info {
        flex: 1;
    }

    .status-info h5 {
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-semibold);
        margin: 0 0 var(--spacing-2) 0;
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .status-meta {
        display: flex;
        gap: var(--spacing-4);
        margin-top: var(--spacing-3);
        flex-wrap: wrap;
    }

    .status-meta-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .status-meta-item i {
        color: var(--text-muted);
    }

    /* Expiry Warning */
    .expiry-warning {
        margin-top: var(--spacing-3);
        padding: var(--spacing-3);
        background: rgba(var(--warning-rgb), 0.1);
        border-left: 3px solid var(--warning);
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .expiry-warning i {
        color: var(--warning);
        font-size: var(--font-size-lg);
    }

    .expiry-warning p {
        margin: 0;
        color: var(--text-primary);
        font-weight: var(--font-weight-medium);
    }

    /* Detail Grid */
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-4);
    }

    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .detail-item {
        padding: var(--spacing-3);
        background: var(--bg-subtle);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
    }

    .detail-label {
        display: block;
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: var(--spacing-2);
        font-weight: var(--font-weight-medium);
    }

    .detail-value {
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .detail-value-muted {
        color: var(--text-muted);
        font-size: var(--font-size-sm);
        margin-left: var(--spacing-2);
    }

    /* Budget Utilization */
    .utilization-container {
        margin-top: var(--spacing-4);
    }

    .utilization-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-3);
    }

    .utilization-label {
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .utilization-percentage {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-bold);
    }

    .utilization-percentage.success { color: var(--success); }
    .utilization-percentage.warning { color: var(--warning); }
    .utilization-percentage.danger { color: var(--danger); }

    .progress-bar-container {
        position: relative;
        height: 32px;
        background: var(--bg-subtle);
        border-radius: var(--border-radius-full);
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .progress-bar-fill {
        height: 100%;
        transition: width 0.5s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: var(--font-weight-semibold);
        font-size: var(--font-size-sm);
        color: white;
    }

    .progress-bar-fill.success { background: var(--success); }
    .progress-bar-fill.warning { background: var(--warning); }
    .progress-bar-fill.danger { background: var(--danger); }

    .progress-bar-text {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: var(--font-weight-semibold);
        font-size: var(--font-size-sm);
        z-index: 1;
    }

    .progress-bar-text.light { color: white; }
    .progress-bar-text.dark { color: var(--text-primary); }

    .budget-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--spacing-3);
        margin-top: var(--spacing-4);
    }

    @media (max-width: 768px) {
        .budget-stats-grid {
            grid-template-columns: 1fr;
        }
    }

    .stat-box {
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
        text-align: center;
    }

    .stat-box-label {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: var(--spacing-2);
    }

    .stat-box-value {
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
    }

    .stat-box-value.success { color: var(--success); }
    .stat-box-value.warning { color: var(--warning); }
    .stat-box-value.primary { color: var(--primary); }

    /* Allocations Table */
    .allocations-table-container {
        overflow-x: auto;
    }

    .allocations-table {
        width: 100%;
        border-collapse: collapse;
    }

    .allocations-table thead th {
        background: var(--bg-subtle);
        padding: var(--spacing-3) var(--spacing-4);
        text-align: left;
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-secondary);
        border-bottom: 2px solid var(--border-color);
        white-space: nowrap;
    }

    .allocations-table tbody td {
        padding: var(--spacing-3) var(--spacing-4);
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
    }

    .allocations-table tbody tr:hover {
        background: var(--bg-hover);
    }

    .allocations-table .text-right {
        text-align: right;
    }

    .allocations-table .req-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: var(--font-weight-medium);
    }

    .allocations-table .req-link:hover {
        text-decoration: underline;
    }

    /* Empty State */
    .empty-state {
        padding: var(--spacing-8) var(--spacing-4);
        text-align: center;
    }

    .empty-state i {
        font-size: 3rem;
        color: var(--text-muted);
        margin-bottom: var(--spacing-4);
        opacity: 0.5;
    }

    .empty-state h6 {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-2);
    }

    .empty-state p {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0;
    }

    /* Sidebar */
    .budget-sidebar {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-4);
    }

    .sidebar-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        transition: var(--theme-transition);
    }

    .sidebar-card:hover {
        box-shadow: var(--shadow-sm);
    }

    .sidebar-card-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
    }

    .sidebar-card-header i {
        color: var(--primary);
        font-size: var(--font-size-lg);
    }

    .sidebar-card-header h6 {
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
    }

    .sidebar-card-body {
        padding: var(--spacing-4);
    }

    /* Summary Items */
    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--spacing-3) 0;
        border-bottom: 1px solid var(--border-light);
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .summary-label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .summary-value {
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .summary-value.highlight {
        color: var(--success);
        font-size: var(--font-size-lg);
    }

    /* Quick Actions */
    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-2);
    }

    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        padding: var(--spacing-3);
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        color: var(--text-primary);
        text-decoration: none;
        transition: var(--transition-fast);
        font-size: var(--font-size-sm);
    }

    .quick-action-btn:hover {
        border-color: var(--primary);
        background: var(--bg-hover);
        transform: translateX(4px);
    }

    .quick-action-btn i {
        width: 20px;
        text-align: center;
    }

    /* Timeline */
    .timeline {
        position: relative;
        padding-left: var(--spacing-6);
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 16px;
        top: 10px;
        bottom: 10px;
        width: 2px;
        background: var(--border-color);
    }

    .timeline-item {
        position: relative;
        padding-bottom: var(--spacing-5);
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-marker {
        position: absolute;
        left: -37px;
        width: 34px;
        height: 34px;
        border-radius: var(--border-radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        z-index: 1;
        border: 3px solid var(--bg-card);
        font-size: var(--font-size-sm);
    }

    .timeline-marker.allocated {
        background: var(--primary);
    }

    .timeline-marker.released {
        background: var(--warning);
    }

    .timeline-content {
        background: var(--bg-subtle);
        padding: var(--spacing-4);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
        transition: var(--transition-fast);
    }

    .timeline-content:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-2);
        gap: var(--spacing-3);
    }

    .timeline-header strong {
        color: var(--text-primary);
        font-weight: var(--font-weight-semibold);
    }

    .timeline-purpose {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-2);
    }

    .timeline-time {
        font-size: var(--font-size-xs);
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: var(--spacing-1);
    }

    /* Mobile Optimizations */
    @media (max-width: 768px) {
        .section-header {
            padding: var(--spacing-4);
        }

        .section-body {
            padding: var(--spacing-4);
        }

        .section-icon {
            width: 36px;
            height: 36px;
            font-size: var(--font-size-base);
        }

        .status-banner {
            padding: var(--spacing-4);
        }

        .status-banner-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .status-meta {
            flex-direction: column;
            gap: var(--spacing-2);
        }

        .allocations-table {
            font-size: var(--font-size-sm);
        }

        .allocations-table thead th,
        .allocations-table tbody td {
            padding: var(--spacing-2) var(--spacing-3);
        }

        .content-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .content-actions .btn {
            flex: 1;
            min-width: 120px;
        }
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">
                <i class="fas fa-wallet me-2"></i>
                <?php echo htmlspecialchars($budgetData['department_name']); ?> Budget
            </h1>
            <p class="content-description">
                <?php echo ucfirst($budgetData['duration_type']); ?> budget period: 
                <?php echo format_date($budgetData['start_date'], 'M d, Y'); ?> - 
                <?php echo format_date($budgetData['end_date'], 'M d, Y'); ?>
            </p>
        </div>
        <div class="content-actions">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                <span>Back to List</span>
            </a>
            <?php if ($budgetData['status'] === 'active' || $budgetData['status'] === 'upcoming'): ?>
                <a href="<?php echo build_encrypted_url('edit-budget.php', $budgetData['id']); ?>" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>
                    <span>Edit Budget</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Success!</div>
            <div class="alert-message"><?php echo htmlspecialchars($successMessage); ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Error</div>
            <div class="alert-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Status Banner -->
<div class="status-banner <?php echo $budgetData['status']; ?>">
    <div class="status-banner-header">
        <div class="status-info">
            <h5>
                <span class="badge badge-<?php echo getBudgetStatusClass($budgetData['status']); ?>">
                    <?php echo strtoupper($budgetData['status']); ?>
                </span>
            </h5>
            
            <div class="status-meta">
                <div class="status-meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Created <?php echo format_date($budgetData['created_at'], 'M d, Y'); ?></span>
                </div>
                <div class="status-meta-item">
                    <i class="fas fa-user"></i>
                    <span>By <?php echo htmlspecialchars($budgetData['created_by_name'] ?? 'System'); ?></span>
                </div>
                <?php if ($budgetData['status'] === 'active' && $daysRemaining > 0): ?>
                    <div class="status-meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?php echo $daysRemaining; ?> days remaining</span>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($isExpiringSoon): ?>
                <div class="expiry-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>This budget will expire in <?php echo $daysRemaining; ?> day<?php echo $daysRemaining != 1 ? 's' : ''; ?>. Consider setting a new budget soon.</p>
                </div>
            <?php endif; ?>

            <?php if ($isExpired): ?>
                <div class="expiry-warning" style="border-left-color: var(--danger); background: rgba(var(--danger-rgb), 0.1);">
                    <i class="fas fa-times-circle" style="color: var(--danger);"></i>
                    <p>This budget has expired. <a href="set-budget.php?department=<?php echo $budgetData['department_id']; ?>" class="text-danger"><strong>Set a new budget</strong></a> for this department.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="budget-view-grid">
    <!-- Main Content Section -->
    <div class="budget-main-content">
        
        <!-- Budget Details Card -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-header-content">
                    <div class="section-icon primary">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="section-title">
                        <h5>Budget Details</h5>
                        <p>Basic information about this budget</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label class="detail-label">Department</label>
                        <div class="detail-value">
                            <?php echo htmlspecialchars($budgetData['department_name']); ?>
                            <span class="detail-value-muted">
                                (<?php echo htmlspecialchars($budgetData['department_code']); ?>)
                            </span>
                        </div>
                    </div>

                    <div class="detail-item">
                        <label class="detail-label">Duration Type</label>
                        <div class="detail-value">
                            <?php echo ucfirst($budgetData['duration_type']); ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <label class="detail-label">Start Date</label>
                        <div class="detail-value">
                            <?php echo format_date($budgetData['start_date'], 'F d, Y'); ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <label class="detail-label">End Date</label>
                        <div class="detail-value">
                            <?php echo format_date($budgetData['end_date'], 'F d, Y'); ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <label class="detail-label">Total Budget</label>
                        <div class="detail-value" style="color: var(--primary);">
                            ₦<?php echo number_format((float)$budgetData['budget_amount'], 2); ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <label class="detail-label">Status</label>
                        <div class="detail-value">
                            <span class="badge badge-<?php echo getBudgetStatusClass($budgetData['status']); ?>">
                                <?php echo strtoupper($budgetData['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Utilization Card -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-header-content">
                    <div class="section-icon warning">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="section-title">
                        <h5>Budget Utilization</h5>
                        <p>Current usage and available balance</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="utilization-container">
                    <div class="utilization-header">
                        <span class="utilization-label">Overall Utilization</span>
                        <span class="utilization-percentage <?php echo getUtilizationColor($utilizationPercentage); ?>">
                            <?php echo number_format($utilizationPercentage, 1); ?>%
                        </span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill <?php echo getUtilizationColor($utilizationPercentage); ?>" 
                             style="width: <?php echo min(100, $utilizationPercentage); ?>%;"></div>
                        <div class="progress-bar-text <?php echo $utilizationPercentage > 50 ? 'light' : 'dark'; ?>">
                            <?php echo number_format($utilizationPercentage, 1); ?>% Allocated
                        </div>
                    </div>
                </div>

                <div class="budget-stats-grid">
                    <div class="stat-box">
                        <div class="stat-box-label">Total Budget</div>
                        <div class="stat-box-value primary">
                            ₦<?php echo number_format((float)$budgetData['budget_amount'], 2); ?>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-label">Allocated</div>
                        <div class="stat-box-value warning">
                            ₦<?php echo number_format((float)$budgetData['allocated_amount'], 2); ?>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-label">Available</div>
                        <div class="stat-box-value success">
                            ₦<?php echo number_format((float)$budgetData['available_amount'], 2); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Allocation History Card -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-header-content">
                    <div class="section-icon success">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="section-title">
                        <h5>Allocation History (<?php echo count($allocations); ?>)</h5>
                        <p>All budget allocations for requisitions</p>
                    </div>
                </div>
            </div>
            <div class="section-body" style="padding: 0;">
                <?php if (empty($allocations)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h6>No Allocations Yet</h6>
                        <p>This budget hasn't been allocated to any requisitions yet.</p>
                    </div>
                <?php else: ?>
                    <div class="allocations-table-container">
                        <table class="allocations-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Requisition</th>
                                    <th>Requester</th>
                                    <th>Purpose</th>
                                    <th class="text-right">Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allocations as $allocation): ?>
                                    <tr>
                                        <td><?php echo format_date($allocation['allocated_at'], 'M d, Y'); ?></td>
                                        <td>
                                            <a href="<?php echo build_encrypted_url('../../requisitions/view.php', $allocation['requisition_id']); ?>"
                                               class="req-link">
                                                <?php echo htmlspecialchars($allocation['requisition_number']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($allocation['requester_name']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($allocation['purpose'], 0, 40)) . (strlen($allocation['purpose']) > 40 ? '...' : ''); ?></td>
                                        <td class="text-right">₦<?php echo number_format((float)$allocation['amount'], 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $allocation['allocation_type'] === 'allocated' ? 'primary' : 'warning'; 
                                            ?>">
                                                <?php echo ucfirst($allocation['allocation_type']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Sidebar Section -->
    <div class="budget-sidebar">
        
        <!-- Summary Card -->
        <div class="sidebar-card">
            <div class="sidebar-card-header">
                <i class="fas fa-calculator"></i>
                <h6>Summary</h6>
            </div>
            <div class="sidebar-card-body">
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-building"></i>Department
                    </span>
                    <span class="summary-value"><?php echo htmlspecialchars($budgetData['department_code']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-calendar"></i>Duration
                    </span>
                    <span class="summary-value"><?php echo ucfirst($budgetData['duration_type']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-link"></i>Active Allocations
                    </span>
                    <span class="summary-value"><?php echo $budgetData['active_allocations']; ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-chart-line"></i>Utilization
                    </span>
                    <span class="summary-value <?php echo getUtilizationColor($utilizationPercentage); ?>">
                        <?php echo number_format($utilizationPercentage, 1); ?>%
                    </span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-info-circle"></i>Status
                    </span>
                    <span class="summary-value">
                        <span class="badge badge-<?php echo getBudgetStatusClass($budgetData['status']); ?>">
                            <?php echo strtoupper($budgetData['status']); ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="sidebar-card">
            <div class="sidebar-card-header">
                <i class="fas fa-bolt"></i>
                <h6>Quick Actions</h6>
            </div>
            <div class="sidebar-card-body">
                <div class="quick-actions">
                    <a href="index.php" class="quick-action-btn">
                        <i class="fas fa-list"></i>
                        <span>All Budgets</span>
                    </a>
                    <?php if ($budgetData['status'] === 'active' || $budgetData['status'] === 'upcoming'): ?>
                        <a href="<?php echo build_encrypted_url('edit-budget.php', $budgetData['id']); ?>" class="quick-action-btn">
                            <i class="fas fa-edit"></i>
                            <span>Edit This Budget</span>
                        </a>
                    <?php endif; ?>
                    <a href="set-budget.php" class="quick-action-btn">
                        <i class="fas fa-plus"></i>
                        <span>Set New Budget</span>
                    </a>
                    <a href="../../reports/department.php?department=<?php echo $budgetData['department_id']; ?>" class="quick-action-btn">
                        <i class="fas fa-chart-bar"></i>
                        <span>View Reports</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <?php if (!empty($allocations)): ?>
            <?php
            // Get last 5 allocations
            $recentAllocations = array_slice($allocations, 0, 5);
            ?>
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <i class="fas fa-clock"></i>
                    <h6>Recent Activity</h6>
                </div>
                <div class="sidebar-card-body">
                    <div class="timeline">
                        <?php foreach ($recentAllocations as $allocation): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker <?php echo $allocation['allocation_type']; ?>">
                                    <i class="fas <?php echo $allocation['allocation_type'] === 'allocated' ? 'fa-plus' : 'fa-undo'; ?>"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <a href="<?php echo build_encrypted_url('../../requisitions/view.php', $allocation['requisition_id']); ?>"
                                           class="req-link">
                                            <?php echo htmlspecialchars($allocation['requisition_number']); ?>
                                        </a>
                                        <span class="badge badge-<?php 
                                            echo $allocation['allocation_type'] === 'allocated' ? 'primary' : 'warning'; 
                                        ?>">
                                            ₦<?php echo number_format((float)$allocation['amount'], 2); ?>
                                        </span>
                                    </div>
                                    <div class="timeline-purpose">
                                        <?php echo htmlspecialchars(substr($allocation['purpose'], 0, 50)) . (strlen($allocation['purpose']) > 50 ? '...' : ''); ?>
                                    </div>
                                    <div class="timeline-time">
                                        <i class="fas fa-clock"></i>
                                        <?php echo format_date($allocation['allocated_at'], 'M d, Y'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php
/**
 * GateWey Requisition Management System
 * Budget Management - Index/List Page
 * 
 * File: finance/budget/index.php
 * Purpose: List all department budgets with status filtering
 */

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

$budget = new Budget();
$department = new Department();

// Get all departments for filter
$departments = $department->getAll();

// Get filter parameters
$filterStatus = isset($_GET['status']) ? Sanitizer::string($_GET['status']) : 'all';
$filterDepartment = isset($_GET['department']) ? (int)$_GET['department'] : 0;

// Get all budgets
$allBudgets = $budget->getAllBudgets();

// Filter budgets based on criteria
$filteredBudgets = array_filter($allBudgets, function($b) use ($filterStatus, $filterDepartment) {
    $statusMatch = ($filterStatus === 'all' || $b['status'] === $filterStatus);
    $deptMatch = ($filterDepartment === 0 || $b['department_id'] == $filterDepartment);
    return $statusMatch && $deptMatch;
});

// Separate budgets by status for display sections
$expiredBudgets = array_filter($filteredBudgets, function($b) {
    return $b['status'] === 'expired';
});

$expiringSoonBudgets = array_filter($filteredBudgets, function($b) {
    if ($b['status'] !== 'active') return false;
    $daysUntilExpiry = (strtotime($b['end_date']) - time()) / (60 * 60 * 24);
    return $daysUntilExpiry <= 7 && $daysUntilExpiry >= 0;
});

$activeBudgets = array_filter($filteredBudgets, function($b) {
    return $b['status'] === 'active';
});

$upcomingBudgets = array_filter($filteredBudgets, function($b) {
    return $b['status'] === 'upcoming';
});

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

$pageTitle = 'Budget Management';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<style>
    /* Action Required Section */
    .action-required-section {
        background: rgba(var(--danger-rgb), 0.05);
        border: 2px solid rgba(var(--danger-rgb), 0.2);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-5);
        margin-bottom: var(--spacing-6);
    }

    .action-required-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
        margin-bottom: var(--spacing-4);
    }

    .action-required-icon {
        width: 40px;
        height: 40px;
        background: var(--danger);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: var(--font-size-lg);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .action-required-title {
        margin: 0;
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-bold);
        color: var(--danger);
    }

    .action-required-count {
        background: var(--danger);
        color: white;
        padding: var(--spacing-1) var(--spacing-3);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
    }

    /* Expiring Soon Section */
    .expiring-soon-section {
        background: rgba(var(--warning-rgb), 0.05);
        border: 2px solid rgba(var(--warning-rgb), 0.2);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-5);
        margin-bottom: var(--spacing-6);
    }

    .expiring-soon-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
        margin-bottom: var(--spacing-4);
    }

    .expiring-soon-icon {
        width: 40px;
        height: 40px;
        background: var(--warning);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: var(--font-size-lg);
    }

    .expiring-soon-title {
        margin: 0;
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-bold);
        color: var(--warning);
    }

    /* Budget Table Section */
    .budget-section {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        margin-bottom: var(--spacing-6);
    }

    .budget-section-header {
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .budget-section-title {
        margin: 0;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .budget-count-badge {
        background: var(--primary);
        color: white;
        padding: var(--spacing-1) var(--spacing-3);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
    }

    /* Enhanced Table */
    .enhanced-table {
        width: 100%;
        border-collapse: collapse;
    }

    .enhanced-table thead {
        background: var(--bg-subtle);
    }

    .enhanced-table th {
        padding: var(--spacing-4);
        text-align: left;
        font-weight: var(--font-weight-semibold);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    .enhanced-table td {
        padding: var(--spacing-4);
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
    }

    .enhanced-table tbody tr {
        transition: var(--theme-transition);
    }

    .enhanced-table tbody tr:hover {
        background: var(--bg-subtle);
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-2);
        padding: var(--spacing-1) var(--spacing-3);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        white-space: nowrap;
    }

    .status-badge i {
        font-size: var(--font-size-xs);
    }

    .status-badge.status-active {
        background: rgba(var(--success-rgb), 0.1);
        color: var(--success);
    }

    .status-badge.status-expired {
        background: rgba(var(--danger-rgb), 0.1);
        color: var(--danger);
    }

    .status-badge.status-upcoming {
        background: rgba(var(--info-rgb), 0.1);
        color: var(--info);
    }

    /* Progress Bar */
    .progress-bar-container {
        width: 100%;
        max-width: 150px;
    }

    .progress-bar {
        height: 8px;
        background: var(--bg-subtle);
        border-radius: var(--border-radius-full);
        overflow: hidden;
        margin-bottom: var(--spacing-1);
    }

    .progress-fill {
        height: 100%;
        border-radius: var(--border-radius-full);
        transition: width 0.3s ease;
    }

    .progress-fill.low {
        background: var(--success);
    }

    .progress-fill.medium {
        background: var(--warning);
    }

    .progress-fill.high {
        background: var(--danger);
    }

    .progress-text {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        text-align: center;
    }

    /* Filter Section */
.filter-section {
    background: var(--bg-subtle);
    padding: var(--spacing-4);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing-5);
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: var(--spacing-4);
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.filter-label {
    display: block;
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
    font-size: var(--font-size-sm);
    margin: 0;
}

.filter-select {
    width: 100%;
    padding: var(--spacing-3);
    font-size: var(--font-size-sm);
    background: var(--bg-input);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    color: var(--text-primary);
    transition: var(--theme-transition);
    height: 42px; /* Match button height */
}

.filter-select:focus {
    border-color: var(--primary);
    outline: none;
}

/* Make Reset button match input height */
.filter-section .btn {
    height: 42px;
    padding: 0 var(--spacing-4);
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-2);
}

/* Responsive */
@media (max-width: 768px) {
    .filter-section {
        grid-template-columns: 1fr;
    }
}

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: var(--spacing-2);
    }

    .btn-sm {
        padding: var(--spacing-2) var(--spacing-3);
        font-size: var(--font-size-xs);
    }

    /* Empty State */
    .empty-state {
        padding: var(--spacing-8) var(--spacing-4);
        text-align: center;
        color: var(--text-secondary);
    }

    .empty-state-icon {
        font-size: 4rem;
        color: var(--text-muted);
        margin-bottom: var(--spacing-4);
    }

    .empty-state-title {
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-2);
    }

    .empty-state-text {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-4);
    }

    /* Alert Messages */
    .alert {
        display: flex;
        align-items: flex-start;
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-4);
        gap: var(--spacing-3);
    }

    .alert-success {
        background: rgba(var(--success-rgb), 0.1);
        border-color: rgba(var(--success-rgb), 0.2);
        color: var(--success);
    }

    .alert-danger {
        background: rgba(var(--danger-rgb), 0.1);
        border-color: rgba(var(--danger-rgb), 0.2);
        color: var(--danger);
    }

    .alert i {
        font-size: var(--font-size-lg);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .alert-content {
        flex: 1;
    }

    /* Breadcrumb */
    .content-breadcrumb {
        margin-top: var(--spacing-2);
    }

    .breadcrumb {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: var(--spacing-1);
        align-items: center;
    }

    .breadcrumb-item {
        display: flex;
        align-items: center;
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .breadcrumb-item a {
        color: var(--primary);
        text-decoration: none;
        transition: var(--theme-transition);
    }

    .breadcrumb-item a:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    .breadcrumb-item.active {
        color: var(--text-primary);
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "/";
        margin: 0 var(--spacing-2);
        color: var(--text-muted);
    }

    /* Content Header */
    .content-header {
        margin-bottom: var(--spacing-6);
    }

    .content-actions {
        display: flex;
        gap: var(--spacing-3);
        align-items: center;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .action-buttons {
            flex-direction: column;
            width: 100%;
        }

        .action-buttons .btn {
            width: 100%;
        }

        .content-header {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        .content-actions {
            width: 100%;
            margin-top: var(--spacing-3);
        }
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-start flex-wrap">
        <div>
            <h1 class="content-title">Budget Management</h1>
            <!--<nav class="content-breadcrumb">-->
            <!--    <ol class="breadcrumb">-->
            <!--        <li class="breadcrumb-item">-->
            <!--            <a href="../../dashboard/">Dashboard</a>-->
            <!--        </li>-->
            <!--        <li class="breadcrumb-item">-->
            <!--            <a href="../">Finance</a>-->
            <!--        </li>-->
            <!--        <li class="breadcrumb-item active">Budget Management</li>-->
            <!--    </ol>-->
            <!--</nav>-->
        </div>
        <div class="content-actions">
            <a href="set-budget.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                <span>Set New Budget</span>
            </a>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if ($successMessage): ?>
    <div class="alert alert-success" role="alert">
        <i class="fas fa-check-circle"></i>
        <div class="alert-content">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <div class="alert-content">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="filter-section">
    <div class="filter-group">
        <label class="filter-label">Status</label>
        <select class="filter-select" id="status-filter" onchange="applyFilters()">
            <option value="all" <?php echo $filterStatus === 'all' ? 'selected' : ''; ?>>All Statuses</option>
            <option value="active" <?php echo $filterStatus === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="expired" <?php echo $filterStatus === 'expired' ? 'selected' : ''; ?>>Expired</option>
            <option value="upcoming" <?php echo $filterStatus === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
        </select>
    </div>
    
    <div class="filter-group">
        <label class="filter-label">Department</label>
        <select class="filter-select" id="department-filter" onchange="applyFilters()">
            <option value="0" <?php echo $filterDepartment === 0 ? 'selected' : ''; ?>>All Departments</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?php echo $dept['id']; ?>" <?php echo $filterDepartment == $dept['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($dept['department_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="filter-group">
        <button type="button" class="btn btn-secondary" onclick="resetFilters()">
            <i class="fas fa-redo"></i>
            <span>Reset</span>
        </button>
    </div>
</div>

<!-- ACTION REQUIRED - Expired Budgets -->
<?php if (!empty($expiredBudgets)): ?>
<div class="action-required-section">
    <div class="action-required-header">
        <div class="action-required-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2 class="action-required-title">Action Required - Expired Budgets</h2>
        <span class="action-required-count"><?php echo count($expiredBudgets); ?></span>
    </div>
    
    <div class="table-responsive">
        <table class="enhanced-table">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Expired Date</th>
                    <th>Budget Amount</th>
                    <th>Final Utilization</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expiredBudgets as $bud): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($bud['department_name']); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($bud['department_code']); ?></small>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($bud['end_date'])); ?></td>
                    <td><strong>₦<?php echo number_format($bud['budget_amount'], 2); ?></strong></td>
                    <td>
                        <div class="progress-bar-container">
                            <div class="progress-bar">
                                <?php 
                                $percent = $bud['utilization_percentage'];
                                $progressClass = $percent >= 75 ? 'high' : ($percent >= 50 ? 'medium' : 'low');
                                ?>
                                <div class="progress-fill <?php echo $progressClass; ?>" style="width: <?php echo min($percent, 100); ?>%"></div>
                            </div>
                            <div class="progress-text"><?php echo number_format($percent, 1); ?>%</div>
                        </div>
                    </td>
                    <td class="text-right">
                        <a href="set-budget.php?department=<?php echo $bud['department_id']; ?>" class="btn btn-sm btn-danger">
                            <i class="fas fa-plus"></i>
                            Set New Budget
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- EXPIRING SOON - Active Budgets -->
<?php if (!empty($expiringSoonBudgets)): ?>
<div class="expiring-soon-section">
    <div class="expiring-soon-header">
        <div class="expiring-soon-icon">
            <i class="fas fa-clock"></i>
        </div>
        <h2 class="expiring-soon-title">Expiring Soon</h2>
        <span class="budget-count-badge" style="background: var(--warning);"><?php echo count($expiringSoonBudgets); ?></span>
    </div>
    
    <div class="table-responsive">
        <table class="enhanced-table">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Expires In</th>
                    <th>Budget Amount</th>
                    <th>Available</th>
                    <th>Utilization</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expiringSoonBudgets as $bud): 
                    $daysLeft = ceil((strtotime($bud['end_date']) - time()) / (60 * 60 * 24));
                ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($bud['department_name']); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($bud['department_code']); ?></small>
                    </td>
                    <td>
                        <span class="status-badge" style="background: rgba(var(--warning-rgb), 0.1); color: var(--warning);">
                            <i class="fas fa-clock"></i>
                            <?php echo $daysLeft; ?> day<?php echo $daysLeft != 1 ? 's' : ''; ?>
                        </span>
                    </td>
                    <td><strong>₦<?php echo number_format($bud['budget_amount'], 2); ?></strong></td>
                    <td>₦<?php echo number_format($bud['available_amount'], 2); ?></td>
                    <td>
                        <div class="progress-bar-container">
                            <div class="progress-bar">
                                <?php 
                                $percent = $bud['utilization_percentage'];
                                $progressClass = $percent >= 75 ? 'high' : ($percent >= 50 ? 'medium' : 'low');
                                ?>
                                <div class="progress-fill <?php echo $progressClass; ?>" style="width: <?php echo min($percent, 100); ?>%"></div>
                            </div>
                            <div class="progress-text"><?php echo number_format($percent, 1); ?>%</div>
                        </div>
                    </td>
                    <td class="text-right">
                        <div class="action-buttons">
                            <a href="view-budget.php?id=<?php echo $bud['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                                View
                            </a>
                            <a href="set-budget.php?department=<?php echo $bud['department_id']; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-sync-alt"></i>
                                Renew
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ACTIVE BUDGETS -->
<?php if (!empty($activeBudgets)): ?>
<div class="budget-section">
    <div class="budget-section-header">
        <h3 class="budget-section-title">
            <i class="fas fa-check-circle" style="color: var(--success);"></i>
            Active Budgets
            <span class="budget-count-badge"><?php echo count($activeBudgets); ?></span>
        </h3>
    </div>
    
    <div class="table-responsive">
        <table class="enhanced-table">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Period</th>
                    <th>Budget Amount</th>
                    <th>Allocated</th>
                    <th>Available</th>
                    <th>Utilization</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activeBudgets as $bud): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($bud['department_name']); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($bud['department_code']); ?></small>
                    </td>
                    <td>
                        <small>
                            <?php echo date('M d, Y', strtotime($bud['start_date'])); ?><br>
                            to <?php echo date('M d, Y', strtotime($bud['end_date'])); ?>
                        </small>
                    </td>
                    <td><strong>₦<?php echo number_format($bud['budget_amount'], 2); ?></strong></td>
                    <td>₦<?php echo number_format($bud['allocated_amount'], 2); ?></td>
                    <td>₦<?php echo number_format($bud['available_amount'], 2); ?></td>
                    <td>
                        <div class="progress-bar-container">
                            <div class="progress-bar">
                                <?php 
                                $percent = $bud['utilization_percentage'];
                                $progressClass = $percent >= 75 ? 'high' : ($percent >= 50 ? 'medium' : 'low');
                                ?>
                                <div class="progress-fill <?php echo $progressClass; ?>" style="width: <?php echo min($percent, 100); ?>%"></div>
                            </div>
                            <div class="progress-text"><?php echo number_format($percent, 1); ?>%</div>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge status-active">
                            <i class="fas fa-check-circle"></i>
                            Active
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="action-buttons">
                            <a href="view-budget.php?id=<?php echo $bud['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                                View
                            </a>
                            <a href="edit-budget.php?id=<?php echo $bud['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-edit"></i>
                                Edit
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- UPCOMING BUDGETS -->
<?php if (!empty($upcomingBudgets)): ?>
<div class="budget-section">
    <div class="budget-section-header">
        <h3 class="budget-section-title">
            <i class="fas fa-calendar-alt" style="color: var(--info);"></i>
            Upcoming Budgets
            <span class="budget-count-badge" style="background: var(--info);"><?php echo count($upcomingBudgets); ?></span>
        </h3>
    </div>
    
    <div class="table-responsive">
        <table class="enhanced-table">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Starts In</th>
                    <th>Period</th>
                    <th>Budget Amount</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($upcomingBudgets as $bud): 
                    $daysUntilStart = ceil((strtotime($bud['start_date']) - time()) / (60 * 60 * 24));
                ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($bud['department_name']); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($bud['department_code']); ?></small>
                    </td>
                    <td><?php echo $daysUntilStart; ?> day<?php echo $daysUntilStart != 1 ? 's' : ''; ?></td>
                    <td>
                        <small>
                            <?php echo date('M d, Y', strtotime($bud['start_date'])); ?><br>
                            to <?php echo date('M d, Y', strtotime($bud['end_date'])); ?>
                        </small>
                    </td>
                    <td><strong>₦<?php echo number_format($bud['budget_amount'], 2); ?></strong></td>
                    <td>
                        <span class="status-badge status-upcoming">
                            <i class="fas fa-calendar-alt"></i>
                            Upcoming
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="action-buttons">
                            <a href="view-budget.php?id=<?php echo $bud['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                                View
                            </a>
                            <a href="edit-budget.php?id=<?php echo $bud['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-edit"></i>
                                Edit
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Empty State -->
<?php if (empty($filteredBudgets)): ?>
<div class="budget-section">
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="fas fa-wallet"></i>
        </div>
        <h3 class="empty-state-title">No Budgets Found</h3>
        <p class="empty-state-text">
            <?php if ($filterStatus !== 'all' || $filterDepartment !== 0): ?>
                No budgets match your current filters. Try adjusting your search criteria.
            <?php else: ?>
                No department budgets have been set yet. Click "Set New Budget" to create one.
            <?php endif; ?>
        </p>
        <?php if ($filterStatus === 'all' && $filterDepartment === 0): ?>
            <a href="set-budget.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                <span>Set First Budget</span>
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
function applyFilters() {
    const status = document.getElementById('status-filter').value;
    const department = document.getElementById('department-filter').value;
    
    let url = 'index.php?';
    const params = [];
    
    if (status !== 'all') {
        params.push('status=' + status);
    }
    
    if (department !== '0') {
        params.push('department=' + department);
    }
    
    window.location.href = url + params.join('&');
}

function resetFilters() {
    window.location.href = 'index.php';
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Budget Management Index initialized');
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
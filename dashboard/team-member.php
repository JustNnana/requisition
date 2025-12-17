<?php

/**
 * GateWey Requisition Management System
 * Team Member Dashboard - Dasher UI Enhanced
 * 
 * File: dashboard/team-member.php
 * Purpose: Dashboard for Team Members (Requisition creators)
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';
// Load helpers - IMPORTANT: Include permissions.php to use role-checking functions
require_once __DIR__ . '/../helpers/permissions.php';
// Check if user is Team Member
if (!is_team_member() && !is_line_manager() && !is_managing_director()) {
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

// Load helpers
require_once __DIR__ . '/../helpers/status-indicator.php';

// Initialize classes
$db = Database::getInstance();

// Get user info
$userId = Session::getUserId();
$userName = Session::getUserFullName();
$userFirstName = Session::get('user_first_name', 'User');

// Get dashboard statistics
$stats = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'paid' => 0,
    'rejected' => 0,
    'total_amount' => 0,
    'this_month' => 0
];

// Total requisitions (count all non-draft requisitions)
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE user_id = ? AND status != ?";
$result = $db->fetchOne($sql, [$userId, STATUS_DRAFT]);
$stats['total'] = $result['count'];

// Total amount (ONLY paid or completed requisitions)
$sql = "SELECT COALESCE(SUM(total_amount), 0) as total 
        FROM requisitions 
        WHERE user_id = ? 
        AND status IN (?, ?)";
$result = $db->fetchOne($sql, [$userId, STATUS_PAID, STATUS_COMPLETED]);
$stats['total_amount'] = $result['total'];

// Pending requisitions
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE user_id = ? 
        AND status IN (?, ?, ?)";
$result = $db->fetchOne($sql, [
    $userId,
    STATUS_PENDING_LINE_MANAGER,
    STATUS_PENDING_MD,
    STATUS_PENDING_FINANCE_MANAGER
]);
$stats['pending'] = $result['count'];

// Approved requisitions
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE user_id = ? 
        AND status = ?";
$result = $db->fetchOne($sql, [$userId, STATUS_APPROVED_FOR_PAYMENT]);
$stats['approved'] = $result['count'];

// Paid requisitions
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE user_id = ? 
        AND status IN (?, ?)";
$result = $db->fetchOne($sql, [$userId, STATUS_PAID, STATUS_COMPLETED]);
$stats['paid'] = $result['count'];

// Rejected requisitions
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE user_id = ? 
        AND status = ?";
$result = $db->fetchOne($sql, [$userId, STATUS_REJECTED]);
$stats['rejected'] = $result['count'];

// This month requisitions
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE user_id = ? 
        AND MONTH(created_at) = MONTH(CURRENT_DATE())
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
        AND status != ?";
$result = $db->fetchOne($sql, [$userId, STATUS_DRAFT]);
$stats['this_month'] = $result['count'];

// Get recent requisitions (last 5)
$sql = "SELECT r.*, d.department_name, d.department_code
        FROM requisitions r
        LEFT JOIN departments d ON r.department_id = d.id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
        LIMIT 5";
$recentRequisitions = $db->fetchAll($sql, [$userId]);

// Get pending actions (rejected requisitions that need editing)
$sql = "SELECT r.*, d.department_name
        FROM requisitions r
        LEFT JOIN departments d ON r.department_id = d.id
        WHERE r.user_id = ? AND r.status = ?
        ORDER BY r.updated_at DESC
        LIMIT 3";
$pendingActions = $db->fetchAll($sql, [$userId, STATUS_REJECTED]);

// Get monthly data for chart (last 6 months - PAID/COMPLETED only)
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $sql = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total
            FROM requisitions
            WHERE user_id = ?
            AND DATE_FORMAT(created_at, '%Y-%m') = ?
            AND status IN (?, ?)";  // âœ… CORRECT - only paid/completed
    $result = $db->fetchOne($sql, [$userId, $date, STATUS_PAID, STATUS_COMPLETED]);
    $monthlyData[] = [
        'month' => date('M Y', strtotime($date . '-01')),
        'count' => $result['count'],
        'amount' => $result['total']
    ];
}

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Dashboard';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Add Status Indicator CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/status-indicators.css">

<!-- Dasher UI Enhanced Styles -->
<style>
    /* Revenue Cards - Improved Design */
    .revenue-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: var(--spacing-4);
        margin-bottom: var(--spacing-6);
    }

    .revenue-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-5);
        transition: var(--theme-transition);
    }

    .revenue-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
        transform: translateY(-2px);
    }

    .revenue-card-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .revenue-card-icon {
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

    .revenue-card-content {
        flex: 1;
    }

    .revenue-card-title {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0 0 var(--spacing-1) 0;
        font-weight: var(--font-weight-medium);
    }

    .revenue-card-value {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin: 0;
        line-height: 1;
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

    .improved-stats-icon.danger {
        background-color: var(--danger);
    }

    .improved-stats-icon.info {
        background-color: var(--info);
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

    /* Quick Action Cards - Dasher UI Style */
    .quick-action-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-4);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
        transition: var(--transition-fast);
        position: relative;
        overflow: hidden;
    }

    .quick-action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        opacity: 0;
        transition: var(--transition-fast);
    }

    .quick-action-card.primary::before {
        background-color: var(--primary);
    }

    .quick-action-card.success::before {
        background-color: var(--success);
    }

    .quick-action-card.info::before {
        background-color: var(--info);
    }

    .quick-action-card.warning::before {
        background-color: var(--warning);
    }

    .quick-action-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
        transform: translateY(-2px);
        text-decoration: none;
    }

    .quick-action-card:hover::before {
        opacity: 1;
    }

    .quick-action-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-lg);
        flex-shrink: 0;
        transition: var(--transition-fast);
    }

    .quick-action-card.primary .quick-action-icon {
        background-color: var(--primary-light);
        color: var(--primary);
    }

    .quick-action-card.success .quick-action-icon {
        background-color: var(--success-light);
        color: var(--success);
    }

    .quick-action-card.info .quick-action-icon {
        background-color: var(--info-light);
        color: var(--info);
    }

    .quick-action-card.warning .quick-action-icon {
        background-color: var(--warning-light);
        color: var(--warning);
    }

    .quick-action-card:hover .quick-action-icon {
        transform: scale(1.1);
    }

    .quick-action-content {
        flex: 1;
    }

    .quick-action-title {
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-1) 0;
    }

    .quick-action-description {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        margin: 0;
    }

    .quick-action-arrow {
        color: var(--text-muted);
        font-size: var(--font-size-base);
        transition: var(--transition-fast);
        opacity: 0.5;
    }

    .quick-action-card:hover .quick-action-arrow {
        transform: translateX(4px);
        opacity: 1;
    }

    .quick-action-card.primary:hover .quick-action-arrow {
        color: var(--primary);
    }

    .quick-action-card.success:hover .quick-action-arrow {
        color: var(--success);
    }

    .quick-action-card.info:hover .quick-action-arrow {
        color: var(--info);
    }

    .quick-action-card.warning:hover .quick-action-arrow {
        color: var(--warning);
    }

    /* Chart Grid */
    .chart-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: var(--spacing-4);
        margin-bottom: var(--spacing-6);
    }

    /* Chart Container */
    .chart-container {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-5);
        transition: var(--theme-transition);
    }

    .chart-container:hover {
        box-shadow: var(--shadow-sm);
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

    .chart-body {
        position: relative;
        min-height: 300px;
    }

    .chart-canvas {
        width: 100% !important;
        height: 300px !important;
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

    /* Table Container */
    .table-container {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        overflow: hidden;
        transition: var(--theme-transition);
    }

    .table-container:hover {
        box-shadow: var(--shadow-sm);
    }

    .card-header {
        padding: var(--spacing-5);
        border-bottom: 1px solid var(--border-color);
        background: var(--bg-card);
    }

    .card-title {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
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
            padding: var(--spacing-4) !important;
        }

        .improved-stats-icon {
            width: 40px !important;
            height: 40px !important;
            font-size: var(--font-size-lg) !important;
        }

        .improved-stats-value {
            font-size: var(--font-size-2xl) !important;
        }

        .revenue-cards-grid {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            gap: var(--spacing-3) !important;
            padding-bottom: 0.5rem !important;
            scroll-snap-type: x mandatory;
        }

        .revenue-card {
            flex: 0 0 auto !important;
            width: 280px !important;
            min-width: 280px !important;
            scroll-snap-align: start;
        }

        .revenue-card-value {
            font-size: var(--font-size-2xl) !important;
        }

        .content-actions {
            display: flex !important;
            justify-content: flex-end !important;
            gap: 0.5rem;
            flex-wrap: wrap;
            white-space: nowrap !important;
        }

        .content-actions .btn {
            flex: 0 1 auto !important;
            white-space: nowrap;
        }
    }

    .status-warning .status-dot {
        background-color: var(--warning);
    }

    .status-warning .status-text {
        color: var(--warning);
        font-weight: 600;
    }
</style>


<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">
                <i class="fas fa-tachometer-alt me-2"></i>
                Welcome, <?php echo htmlspecialchars($userFirstName); ?>
            </h1>
            <p class="content-subtitle">Your requisition dashboard</p>
        </div>
        <div class="content-actions">
            <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>New Requisition
            </a>
            <a href="<?php echo BASE_URL; ?>/reports/personal.php" class="btn btn-outline-primary">
                <i class="fas fa-chart-bar me-2"></i>View Reports
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

<!-- Pending Actions Alert -->
<?php if (!empty($pendingActions)): ?>
    <div style="border: solid 1px var(--danger); border-radius: var(--border-radius); padding: var(--spacing-5); margin-bottom: var(--spacing-6); color: white;">
        <div class="d-flex align-items-start gap-3">
            <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-top: 0.25rem; margin-right: 0.55rem;"></i>
            <div style="flex: 1;">
                <h5 style="margin: 0 0 var(--spacing-2) 0; font-weight: var(--font-weight-semibold);">Action Required</h5>
                <p style="margin: 0 0 var(--spacing-3) 0; opacity: 0.9;">You have <?php echo count($pendingActions); ?> rejected requisition(s) that need your attention:</p>
                <div style="display: flex; flex-direction: column; gap: var(--spacing-2); color:var(--text-primary);">
                    <?php foreach ($pendingActions as $action): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; border:1px solid var(--border-color); padding: var(--spacing-3); border-radius: var(--border-radius);">
                            <div>
                                <strong><?php echo htmlspecialchars($action['requisition_number']); ?></strong> -
                                <?php echo htmlspecialchars(substr($action['purpose'], 0, 50)) . (strlen($action['purpose']) > 50 ? '...' : ''); ?>
                            </div>
                            <a href="<?php echo BASE_URL; ?>/requisitions/view.php?id=<?php echo $action['id']; ?>" class="btn btn-danger">
                                <i class="fas fa-eye me-2"></i>View & Edit
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Receipt Upload Alert -->
<?php
// Get paid requisitions needing receipt upload
$sql = "SELECT r.*, d.department_name
        FROM requisitions r
        LEFT JOIN departments d ON r.department_id = d.id
        WHERE r.user_id = ? 
        AND r.status = ?
        AND (r.receipt_uploaded IS NULL OR r.receipt_uploaded = '' OR r.receipt_uploaded = 0)
        ORDER BY r.payment_date DESC
        LIMIT 5";
$needsReceipt = $db->fetchAll($sql, [$userId, STATUS_PAID]);
?>

<?php if (!empty($needsReceipt)): ?>
    <div style="border: solid 1px var(--info); border-radius: var(--border-radius); padding: var(--spacing-5); margin-bottom: var(--spacing-6);">
        <div class="d-flex align-items-start gap-3">
            <i class="fas fa-receipt" style="font-size: 2rem; color: var(--info); margin-top: 0.25rem; margin-right: 0.55rem;"></i>
            <div style="flex: 1;">
                <h5 style="margin: 0 0 var(--spacing-2) 0; font-weight: var(--font-weight-semibold); color: white;">Receipt Upload Required</h5>
                <p style="margin: 0 0 var(--spacing-3) 0; opacity: 0.9; color: white;">You have <?php echo count($needsReceipt); ?> paid requisition(s) that require receipt upload:</p>
                <div style="display: flex; flex-direction: column; gap: var(--spacing-2);">
                    <?php foreach ($needsReceipt as $req): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.1); padding: var(--spacing-3); border-radius: var(--border-radius);">
                            <div style="flex: 1; min-width: 0;">
                                <strong><?php echo htmlspecialchars($req['requisition_number']); ?></strong>
                                <span style="margin: 0 var(--spacing-2);">•</span>
                                <span style="opacity: 0.9;">
                                    ₦<?php echo number_format((float)$req['total_amount'], 2); ?>
                                </span>
                                <span style="margin: 0 var(--spacing-2);">•</span>
                                <span style="opacity: 0.8; font-size: var(--font-size-sm);">
                                    Paid: <?php echo format_date($req['payment_date']); ?>
                                </span>
                            </div>
                            <a href="<?php echo BASE_URL; ?>/requisitions/view.php?id=<?php echo $req['id']; ?>" class="btn btn-info" style="white-space: nowrap; margin-left: var(--spacing-3);">
                                <i class="fas fa-upload me-2"></i>Upload Receipt
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<!-- Department Budget Status Card (if applicable) -->
<?php
// Get department budget information
$budgetModel = new Budget();
$departmentId = Session::getUserDepartmentId();
$budgetInfo = null;

if ($departmentId) {
    $budgetInfo = $budgetModel->getBudgetStats($departmentId);
}
?>

<?php if ($budgetInfo): ?>
    <?php
    $utilizationPercentage = ($budgetInfo['budget_amount'] > 0) 
        ? ($budgetInfo['allocated_amount'] / $budgetInfo['budget_amount']) * 100 
        : 0;
    $isLowBudget = $utilizationPercentage > 75;
    $isCritical = $utilizationPercentage > 90;
    ?>
    
    <div style="border: 1px solid <?php echo $isCritical ? 'var(--danger)' : ($isLowBudget ? 'var(--warning)' : 'var(--info)'); ?>; 
                border-radius: var(--border-radius); 
                padding: var(--spacing-5); 
                margin-bottom: var(--spacing-6); 
                background: rgba(<?php echo $isCritical ? 'var(--danger-rgb)' : ($isLowBudget ? 'var(--warning-rgb)' : 'var(--info-rgb)'); ?>, 0.05);">
        <div class="d-flex align-items-start gap-3">
            <i class="fas fa-wallet" style="font-size: 2rem; color: <?php echo $isCritical ? 'var(--danger)' : ($isLowBudget ? 'var(--warning)' : 'var(--info)'); ?>; flex-shrink: 0; margin-top: 0.25rem; margin-right: 0.75rem;"></i>
            
            <div style="flex: 1;">
                <h5 style="margin: 0 0 var(--spacing-2) 0; font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                    <?php echo $isCritical ? '⚠️ Critical: ' : ($isLowBudget ? '⚠️ Low Budget: ' : ''); ?>Department Budget Status
                </h5>
                <p style="margin: 0 0 var(--spacing-3) 0; color: var(--text-secondary); font-size: var(--font-size-sm);">
                    Your department has used <strong>₦<?php echo number_format($budgetInfo['allocated_amount'], 2); ?></strong> 
                    of <strong>₦<?php echo number_format($budgetInfo['budget_amount'], 2); ?></strong> 
                    (<?php echo number_format($utilizationPercentage, 1); ?>% utilized)
                </p>
                
<!-- Progress Bar -->
<div style="background: var(--bg-subtle); border-radius: var(--border-radius-full); height: 24px; margin-bottom: var(--spacing-3); border: 1px solid var(--border-color); position: relative; overflow: hidden;">
    <div style="background: <?php echo $isCritical ? 'var(--danger)' : ($isLowBudget ? 'var(--warning)' : 'var(--success)'); ?>; 
                height: 100%; 
                width: <?php echo min($utilizationPercentage, 100); ?>%; 
                transition: width 0.3s ease;">
    </div>
    <div style="position: absolute; 
                top: 0; 
                left: 0; 
                right: 0; 
                bottom: 0; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                color: <?php echo $utilizationPercentage > 50 ? 'white' : 'var(--text-primary)'; ?>; 
                font-weight: var(--font-weight-semibold); 
                font-size: var(--font-size-xs);
                pointer-events: none;">
        <?php echo number_format($utilizationPercentage, 1); ?>%
    </div>
</div>
                
                <div style="display: flex; justify-content: space-between; font-size: var(--font-size-sm);">
                    <span style="color: var(--text-secondary);">
                        <i class="fa-solid fa-users"></i> Original Budget: ₦ <strong><?php echo number_format($budgetInfo['original_budget'], 2); ?></strong>
                    </span>
                    
<?php
$budgetDiff = $budgetInfo['budget_amount'] - $budgetInfo['original_budget'];
if ($budgetDiff != 0):
    $absDiff = abs($budgetDiff);
    if ($budgetDiff > 0):
?>
        <span style="color: var(--success);">
            <i class="fa-solid fa-circle-plus"></i> Added Supplements: +₦<strong><?php echo number_format($absDiff, 2); ?></strong>
        </span>
    <?php else: ?>
        <span style="color: var(--warning);">
            <i class="fa-solid fa-circle-minus"></i> Budget Reduction: -₦<strong><?php echo number_format($absDiff, 2); ?></strong>
        </span>
    <?php endif; ?>
<?php endif; ?>
                    <span style="color: var(--text-secondary);">
                        <i class="fas fa-check-circle"></i> Remaining: <strong style="color: var(--success);">₦<?php echo number_format($budgetInfo['available_amount'], 2); ?></strong>
                    </span>
                    <span style="color: var(--text-secondary);">
                        <i class="fas fa-calendar"></i> Period ends: <strong><?php echo date('M d, Y', strtotime($budgetInfo['end_date'])); ?></strong>
                    </span>
                </div>
                
                <?php if ($isCritical || $isLowBudget): ?>
                <p style="margin: var(--spacing-3) 0 0 0; color: <?php echo $isCritical ? 'var(--danger)' : 'var(--warning)'; ?>; font-size: var(--font-size-xs);">
                    <i class="fas fa-info-circle"></i> 
                    <?php if ($isCritical): ?>
                        <strong>Critical:</strong> Department budget is nearly exhausted. New requisitions may be delayed.
                    <?php else: ?>
                        <strong>Notice:</strong> Department budget is running low. Plan accordingly for upcoming requests.
                    <?php endif; ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<!-- Key Metrics - Revenue Card Style -->
<div class="revenue-cards-grid">
    <div class="revenue-card">
        <div class="revenue-card-header">
            <div class="revenue-card-icon" style="background-color: var(--primary);">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="revenue-card-content">
                <h3 class="revenue-card-title">Total Spent</h3>
                <p class="revenue-card-value">₦<?php echo number_format((float)$stats['total_amount'], 2); ?></p>
            </div>
        </div>
    </div>

    <div class="revenue-card">
        <div class="revenue-card-header">
            <div class="revenue-card-icon" style="background-color: var(--info);">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="revenue-card-content">
                <h3 class="revenue-card-title">This Month</h3>
                <p class="revenue-card-value"><?php echo number_format($stats['this_month']); ?></p>
            </div>
        </div>
    </div>

    <div class="revenue-card">
        <div class="revenue-card-header">
            <div class="revenue-card-icon" style="background-color: var(--success);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="revenue-card-content">
                <h3 class="revenue-card-title">Completed</h3>
                <p class="revenue-card-value"><?php echo number_format($stats['paid']); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards - Dasher UI Style -->
<div class="chart-grid">
    <!-- Total Requisitions -->
    <div class="improved-stats-card">
        <div class="improved-stats-header">
            <div class="improved-stats-icon info">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="improved-stats-content">
                <h3 class="improved-stats-title">Total Requisitions</h3>
                <p class="improved-stats-value"><?php echo number_format($stats['total']); ?></p>
            </div>
        </div>
        <div style="margin-top: var(--spacing-3);">
            <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
    </div>

    <!-- Pending Approval -->
    <div class="improved-stats-card">
        <div class="improved-stats-header">
            <div class="improved-stats-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="improved-stats-content">
                <h3 class="improved-stats-title">Pending Approval</h3>
                <p class="improved-stats-value"><?php echo number_format($stats['pending']); ?></p>
            </div>
        </div>
        <div style="margin-top: var(--spacing-3);">
            <a href="<?php echo BASE_URL; ?>/requisitions/list.php?status=pending" class="btn btn-sm btn-outline-warning">View</a>
        </div>
    </div>

    <!-- Approved -->
    <div class="improved-stats-card">
        <div class="improved-stats-header">
            <div class="improved-stats-icon primary">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="improved-stats-content">
                <h3 class="improved-stats-title">Approved</h3>
                <p class="improved-stats-value"><?php echo number_format($stats['approved']); ?></p>
            </div>
        </div>
        <div style="margin-top: var(--spacing-3);">
            <a href="<?php echo BASE_URL; ?>/requisitions/list.php?status=approved" class="btn btn-sm btn-outline-primary">View</a>
        </div>
    </div>

    <!-- Rejected -->
    <?php if ($stats['rejected'] > 0): ?>
        <div class="improved-stats-card">
            <div class="improved-stats-header">
                <div class="improved-stats-icon danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="improved-stats-content">
                    <h3 class="improved-stats-title">Rejected</h3>
                    <p class="improved-stats-value"><?php echo number_format($stats['rejected']); ?></p>
                </div>
            </div>
            <div style="margin-top: var(--spacing-3);">
                <a href="<?php echo BASE_URL; ?>/requisitions/list.php?status=rejected" class="btn btn-sm btn-outline-danger">Review</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Charts and Quick Actions Row -->
<div class="charts-column-layout">
    <!-- Monthly Trend Chart -->
    <div class="chart-container">
        <div class="chart-header">
            <div>
                <h2 class="chart-title">Monthly Trend</h2>
                <p class="chart-subtitle">Requisitions over the last 6 months</p>
            </div>
        </div>
        <div class="chart-body">
            <?php if (empty(array_filter(array_column($monthlyData, 'count')))): ?>
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: var(--spacing-8); color: var(--text-muted);">
                    <i class="fas fa-chart-line" style="font-size: 3rem; margin-bottom: var(--spacing-3); opacity: 0.5;"></i>
                    <p style="margin: 0;">No data available yet. Create your first requisition to see trends.</p>
                </div>
            <?php else: ?>
                <canvas id="monthlySummaryChart" class="chart-canvas"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="chart-container">
        <div class="chart-header">
            <h2 class="chart-title">Quick Actions</h2>
        </div>
        <div class="chart-body" style="display: flex; flex-direction: column; gap: var(--spacing-3); min-height: auto;">
            <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="quick-action-card primary">
                <div class="quick-action-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="quick-action-content">
                    <h3 class="quick-action-title">Create Requisition</h3>
                    <p class="quick-action-description">Submit a new request</p>
                </div>
                <div class="quick-action-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>

            <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="quick-action-card info">
                <div class="quick-action-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="quick-action-content">
                    <h3 class="quick-action-title">My Requisitions</h3>
                    <p class="quick-action-description">View all requests</p>
                </div>
                <div class="quick-action-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>

            <a href="<?php echo BASE_URL; ?>/reports/personal.php" class="quick-action-card success">
                <div class="quick-action-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="quick-action-content">
                    <h3 class="quick-action-title">View Reports</h3>
                    <p class="quick-action-description">Analyze your spending</p>
                </div>
                <div class="quick-action-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>

            <?php if ($stats['this_month'] > 0): ?>
                <a href="<?php echo BASE_URL; ?>/reports/personal.php?period=monthly" class="quick-action-card warning">
                    <div class="quick-action-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="quick-action-content">
                        <h3 class="quick-action-title">This Month</h3>
                        <p class="quick-action-description"><?php echo number_format($stats['this_month']); ?> requisitions</p>
                    </div>
                    <div class="quick-action-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Requisitions -->
<div class="table-container">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="card-title">
                <i class="fas fa-history me-2"></i> Recent Requisitions
            </h2>
            <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
    </div>
    <div class="table-responsive">
        <?php if (empty($recentRequisitions)): ?>
            <div style="padding: var(--spacing-8); text-align: center;">
                <i class="fas fa-inbox" style="font-size: 3rem; color: var(--text-muted); margin-bottom: var(--spacing-3); opacity: 0.5;"></i>
                <h6 style="color: var(--text-muted); margin-bottom: var(--spacing-2);">No Requisitions Yet</h6>
                <p style="color: var(--text-muted); margin-bottom: var(--spacing-3);">Start by creating your first requisition</p>
                <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create Requisition
                </a>
            </div>
        <?php else: ?>
            <table class="table table-sortable">
                <thead>
                    <tr>
                        <th>Req. No.</th>
                        <th>Date</th>
                        <th>Purpose</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentRequisitions as $req): ?>
                        <tr>
                            <td>
                                <span style="font-weight: var(--font-weight-medium);">
                                    <?php echo htmlspecialchars($req['requisition_number']); ?>
                                </span>
                            </td>
                            <td><?php echo format_date($req['created_at']); ?></td>
                            <td>
                                <span style="max-width: 200px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($req['purpose']); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <span style="font-weight: var(--font-weight-semibold);">₦<?php echo number_format((float)$req['total_amount'], 2); ?></span>
                                </span>
                            </td>
                            <td>
                                <?php
                                if ($req['status'] === 'paid' && empty($req['receipt_uploaded'])) {
                                    echo '<span class="status-indicator status-warning">
                                    <span class="status-dot"></span>
                                    <span class="status-text">Required Reciept</span>
                                    </span>';
                                } else {
                                    echo get_status_indicator($req['status']);
                                }
                                ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo BASE_URL; ?>/requisitions/view.php?id=<?php echo $req['id']; ?>"
                                    class="btn btn-sm btn-ghost" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Dasher Chart Configuration and Initialization -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('ðŸŽ¨ Initializing Team Member Dashboard...');

        // Wait for Chart.js to be available
        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded');
            return;
        }

        // Dasher theme-aware chart configuration
        function getDasherChartConfig() {
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
        let monthlySummaryChart = null;

        // Monthly Summary Chart
        <?php if (!empty(array_filter(array_column($monthlyData, 'count')))): ?>
            const summaryCtx = document.getElementById('monthlySummaryChart');
            if (summaryCtx) {
                const monthlyData = <?php echo json_encode($monthlyData); ?>;

                monthlySummaryChart = new Chart(summaryCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: monthlyData.map(d => d.month),
                        datasets: [{
                                label: 'Number of Requisitions',
                                data: monthlyData.map(d => d.count),
                                borderColor: chartConfig.colors.primary,
                                backgroundColor: chartConfig.colors.primary + '20',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: chartConfig.colors.primary,
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Total Amount (₦)',
                                data: monthlyData.map(d => d.amount),
                                borderColor: chartConfig.colors.success,
                                backgroundColor: chartConfig.colors.success + '20',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: chartConfig.colors.success,
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    color: chartConfig.colors.text,
                                    font: {
                                        family: chartConfig.font.family,
                                        size: 12
                                    },
                                    padding: 15,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.9)',  // ✅ Black background
    titleColor: '#ffffff',                   // ✅ White text
    bodyColor: '#ffffff',                    // ✅ White text
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
                                },
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.datasetIndex === 1) {
                                            label += '₦' + context.parsed.y.toLocaleString();
                                        } else {
                                            label += context.parsed.y;
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
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
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
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
                                    font: {
                                        family: chartConfig.font.family,
                                        size: 12
                                    },
                                    callback: function(value) {
                                        return value;
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Number of Requisitions',
                                    color: chartConfig.colors.text,
                                    font: {
                                        family: chartConfig.font.family,
                                        size: 12,
                                        weight: '500'
                                    }
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                beginAtZero: true,
                                grid: {
                                    drawOnChartArea: false
                                },
                                border: {
                                    display: false
                                },
                                ticks: {
                                    color: chartConfig.colors.textSecondary,
                                    font: {
                                        family: chartConfig.font.family,
                                        size: 12
                                    },
                                    callback: function(value) {
                                        return '₦' + value.toLocaleString();
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Amount (₦)',
                                    color: chartConfig.colors.text,
                                    font: {
                                        family: chartConfig.font.family,
                                        size: 12,
                                        weight: '500'
                                    }
                                }
                            }
                        }
                    }
                });
            }
        <?php endif; ?>

        // Update charts when theme changes
    document.addEventListener('themeChanged', function(event) {
    console.log('🎨 Updating team member charts for theme:', event.detail.theme);
    const newConfig = getDasherChartConfig();

    if (monthlySummaryChart) {
        // Existing updates (datasets, points, etc.)
        monthlySummaryChart.data.datasets[0].borderColor = newConfig.colors.primary;
        monthlySummaryChart.data.datasets[0].backgroundColor = newConfig.colors.primary + '20';
        monthlySummaryChart.data.datasets[0].pointBackgroundColor = newConfig.colors.primary;

        monthlySummaryChart.data.datasets[1].borderColor = newConfig.colors.success;
        monthlySummaryChart.data.datasets[1].backgroundColor = newConfig.colors.success + '20';
        monthlySummaryChart.data.datasets[1].pointBackgroundColor = newConfig.colors.success;

        // Ticks and grid
        monthlySummaryChart.options.scales.x.ticks.color = newConfig.colors.textSecondary;
        monthlySummaryChart.options.scales.y.ticks.color = newConfig.colors.textSecondary;
        monthlySummaryChart.options.scales.y.grid.color = newConfig.colors.border + '40';
        monthlySummaryChart.options.scales.y1.ticks.color = newConfig.colors.textSecondary;

        // === ADD THESE LINES FOR DARK MODE SUPPORT ===
        // Legend text color
        monthlySummaryChart.options.plugins.legend.labels.color = newConfig.colors.text;
        
        // Axis titles (y and y1)
        monthlySummaryChart.options.scales.y.title.color = newConfig.colors.text;
        monthlySummaryChart.options.scales.y1.title.color = newConfig.colors.text;

        // Tooltip border (optional, for consistency)
        monthlySummaryChart.options.plugins.tooltip.borderColor = newConfig.colors.border;

        // Apply all changes
        monthlySummaryChart.update('none');
    }
});

        console.log('âœ… Team Member Dashboard initialized successfully');
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
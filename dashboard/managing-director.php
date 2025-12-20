<?php
/**
 * GateWey Requisition Management System
 * Managing Director Dashboard - Dasher UI Enhanced
 * 
 * File: dashboard/managing-director.php
 * Purpose: Dashboard for Managing Director (Organization oversight)
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
require_once __DIR__ . '/../helpers/status-indicator.php';
// Check if user is Managing Director
if (!is_managing_director()) {
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
    'pending_my_approval' => 0,
    'organization_total' => 0,
    'organization_amount' => 0,
    'this_month' => 0,
    'approved' => 0,
    'rejected' => 0
];

// Pending my approval
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE status = ?";
$result = $db->fetchOne($sql, [STATUS_PENDING_MD]);
$stats['pending_my_approval'] = $result['count'];

// Organization total (count all non-draft requisitions)
$sql = "SELECT COUNT(*) as count
        FROM requisitions 
        WHERE status != ?";
$result = $db->fetchOne($sql, [STATUS_DRAFT]);
$stats['organization_total'] = $result['count'];

// Organization amount (ONLY paid or completed requisitions)
$sql = "SELECT COALESCE(SUM(total_amount), 0) as total
        FROM requisitions 
        WHERE status IN (?, ?)";
$result = $db->fetchOne($sql, [STATUS_PAID, STATUS_COMPLETED]);
$stats['organization_amount'] = $result['total'];  // âœ… CORRECT - only paid/completed

// This month
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
        AND status != ?";
$result = $db->fetchOne($sql, [STATUS_DRAFT]);
$stats['this_month'] = $result['count'];

// Approved by me
$sql = "SELECT COUNT(*) as count 
        FROM requisition_approvals 
        WHERE user_id = ? 
        AND action = ?";
$result = $db->fetchOne($sql, [$userId, APPROVAL_APPROVED]);
$stats['approved'] = $result['count'];

// Rejected by me
$sql = "SELECT COUNT(*) as count 
        FROM requisition_approvals 
        WHERE user_id = ? 
        AND action = ?";
$result = $db->fetchOne($sql, [$userId, APPROVAL_REJECTED]);
$stats['rejected'] = $result['count'];

// Get pending approvals
$sql = "SELECT r.*, u.first_name, u.last_name, d.department_name, d.department_code
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        WHERE r.status = ?
        ORDER BY r.created_at ASC
        LIMIT 10";
$pendingApprovals = $db->fetchAll($sql, [STATUS_PENDING_MD]);

// Get recent organization activity
$sql = "SELECT r.*, u.first_name, u.last_name, d.department_name
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        WHERE r.status != ?
        ORDER BY r.updated_at DESC
        LIMIT 5";
$recentActivity = $db->fetchAll($sql, [STATUS_DRAFT]);

// Get department summary - FIXED to show only paid/completed amounts
$sql = "SELECT d.department_name, d.department_code, d.id as department_id,
               COUNT(r.id) as requisition_count
        FROM departments d
        LEFT JOIN requisitions r ON d.id = r.department_id AND r.status != ?
        WHERE d.is_active = 1
        GROUP BY d.id, d.department_name, d.department_code";
$departmentCounts = $db->fetchAll($sql, [STATUS_DRAFT]);

// Now get amounts - ONLY for paid/completed requisitions
$departmentSummary = [];
foreach ($departmentCounts as $dept) {
    $amountSql = "SELECT COALESCE(SUM(total_amount), 0) as total_amount
                  FROM requisitions
                  WHERE department_id = ?
                  AND status IN (?, ?)";
    $amountResult = $db->fetchOne($amountSql, [$dept['department_id'], STATUS_PAID, STATUS_COMPLETED]);
    
    $departmentSummary[] = [
        'department_name' => $dept['department_name'],
        'department_code' => $dept['department_code'],
        'requisition_count' => $dept['requisition_count'],
        'total_amount' => $amountResult['total_amount']
    ];
}

// Sort by total amount descending
usort($departmentSummary, function($a, $b) {
    return $b['total_amount'] <=> $a['total_amount'];
});

// Get monthly data for chart (last 6 months) - FIXED to show only paid/completed amounts
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    
    // Count all non-draft requisitions
    $countSql = "SELECT COUNT(*) as count
                 FROM requisitions
                 WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
                 AND status != ?";
    $countResult = $db->fetchOne($countSql, [$date, STATUS_DRAFT]);
    
    // Sum ONLY paid/completed requisitions
    $amountSql = "SELECT COALESCE(SUM(total_amount), 0) as total
                  FROM requisitions
                  WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
                  AND status IN (?, ?)";
    $amountResult = $db->fetchOne($amountSql, [$date, STATUS_PAID, STATUS_COMPLETED]);
    
    $monthlyData[] = [
        'month' => date('M Y', strtotime($date . '-01')),
        'count' => $countResult['count'],
        'amount' => $amountResult['total']
    ];
}

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Managing Director Dashboard';
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
        margin-bottom: var(--spacing-6);
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
            <p class="content-subtitle">Managing Director - Organization Overview</p>
        </div>
        <div class="content-actions">
            <!-- <?php if ($stats['pending_my_approval'] > 0): ?>
                <a href="<?php echo BASE_URL; ?>/requisitions/pending.php" class="btn btn-warning">
                    <i class="fas fa-clock me-2"></i>Pending Approvals
                    <span class="badge bg-white text-warning ms-2"><?php echo $stats['pending_my_approval']; ?></span>
                </a>
            <?php endif; ?> -->
            <a href="<?php echo BASE_URL; ?>/reports/organization.php" class="btn btn-outline-primary">
                <i class="fas fa-chart-bar me-2"></i>Reports
            </a>
            <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>New Requisition
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

<!-- Pending Approvals Alert -->
<?php if ($stats['pending_my_approval'] > 0): ?>
    <div style="border: 1px solid var(--warning); border-radius: var(--border-radius); padding: var(--spacing-5); margin-bottom: var(--spacing-6);">
        
        <div class="d-flex align-items-start gap-3">
            <!-- Warning Icon -->
            <i class="fas fa-exclamation-triangle" 
               style="font-size: 2rem; color: var(--warning); flex-shrink: 0; margin-top: 0.25rem; margin-right: 0.75rem;"></i>

            <!-- Content Area: Text + Button -->
            <div style="flex: 1; display: flex; justify-content: space-between; align-items: flex-end; gap: 2rem; min-width: 0;">
                
                <!-- Left: Text -->
                <div style="flex: 1;">
                    <h5 style="margin: 0 0 var(--spacing-2) 0; font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                        Action Required
                    </h5>
                    <p style="margin: 0; opacity: 0.9; color: var(--text-primary);">
                        You have <strong><?php echo $stats['pending_my_approval']; ?></strong> 
                        requisition<?php echo $stats['pending_my_approval'] > 1 ? 's' : ''; ?> awaiting your approval.
                    </p>
                </div>

                <!-- Right: Button (pushed to the far end) -->
                <div style="flex-shrink: 0;">
                    <a href="<?php echo BASE_URL; ?>/requisitions/pending.php" 
                       class="btn btn-warning" 
                       style="white-space: nowrap;">
                        <i class="fas fa-clock me-2"></i>
                        Review Now <strong><?php echo $stats['pending_my_approval']; ?></strong>
                    </a>
                </div>
            </div>
        </div>
        
    </div>
<?php endif; ?>

<!-- Receipt Upload Alert -->
<?php
// Get organization-wide paid requisitions needing receipt upload
$sql = "SELECT r.*, u.first_name, u.last_name, d.department_name, d.department_code
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        WHERE r.status = ?
        AND (r.receipt_uploaded IS NULL OR r.receipt_uploaded = '' OR r.receipt_uploaded = 0)
        ORDER BY r.payment_date DESC
        LIMIT 10";
$needsReceipt = $db->fetchAll($sql, [STATUS_PAID]);
?>

<?php if (!empty($needsReceipt)): ?>
    <div style="border: 1px solid var(--info); border-radius: var(--border-radius); padding: var(--spacing-5); margin-bottom: var(--spacing-6);">
        <div style="display: flex; align-items: flex-start; gap: 1rem;">
            <!-- Info Icon -->
            <i class="fas fa-receipt" style="font-size: 2.2rem; color: var(--info); flex-shrink: 0; margin-top: 0.2rem;"></i>

            <!-- Text + Button Container -->
            <div style="flex: 1; display: flex; justify-content: space-between; align-items: flex-end; min-width: 0;">
                <!-- Text -->
                <div style="flex: 1; min-width: 0;">
                    <h5 style="margin: 0 0 var(--spacing-2) 0; font-weight: 600; color: white;">
                        Organization Receipt Uploads Pending
                    </h5>
                    <p style="margin: 0 0 var(--spacing-3) 0; opacity: 0.9; color: white;">
                        <?php echo count($needsReceipt); ?> paid requisition(s) across the organization are awaiting receipt upload.
                    </p>
                    <div style="display: flex; flex-direction: column; gap: var(--spacing-2); font-size: var(--font-size-sm);">
                        <?php foreach (array_slice($needsReceipt, 0, 4) as $req): ?>
                            <div style="display: flex; align-items: center; gap: var(--spacing-2); opacity: 0.9;">
                                <i class="fas fa-user" style="opacity: 0.7;"></i>
                                <span><strong><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></strong></span>
                                <span>•</span>
                                <span style="opacity: 0.7;"><?php echo htmlspecialchars($req['department_code'] ?? 'N/A'); ?></span>
                                <span>•</span>
                                <span><?php echo htmlspecialchars($req['requisition_number']); ?></span>
                                <span>•</span>
                                <span>₦<?php echo number_format((float)$req['total_amount'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($needsReceipt) > 4): ?>
                            <div style="opacity: 0.7; font-style: italic;">
                                ... and <?php echo count($needsReceipt) - 4; ?> more
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Button - pushed to the right -->
                <div style="margin-left: 2rem; flex-shrink: 0;">
                    <a href="<?php echo BASE_URL; ?>/requisitions/list.php?status=paid&no_receipt=1" 
                       class="btn btn-info" 
                       style="white-space: nowrap;">
                        <i class="fas fa-list me-2"></i>
                        View All <strong><?php echo count($needsReceipt); ?></strong>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<!-- Executive Office Budget Status Card -->
<?php
// Get Executive Office budget information
$budgetModel = new Budget();
$departmentId = Session::getUserDepartmentId(); // MD's department (Executive Office)
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
    
    <div style="border: 1px solid <?php echo $isCritical ? 'var(--danger)' : ($isLowBudget ? 'var(--warning)' : 'var(--success)'); ?>; 
                border-radius: var(--border-radius); 
                padding: var(--spacing-5); 
                margin-bottom: var(--spacing-6); 
                background: rgba(<?php echo $isCritical ? 'var(--danger-rgb)' : ($isLowBudget ? 'var(--warning-rgb)' : 'var(--success-rgb)'); ?>, 0.05);">
        <div class="d-flex align-items-start gap-3">
            <i class="fas fa-wallet" style="font-size: 2rem; color: <?php echo $isCritical ? 'var(--danger)' : ($isLowBudget ? 'var(--warning)' : 'var(--success)'); ?>; flex-shrink: 0; margin-top: 0.25rem; margin-right: 0.75rem;"></i>
            
            <div style="flex: 1;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--spacing-2);">
                    <h5 style="margin: 0; font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                        <?php echo $isCritical ? '🔴 Critical: ' : ($isLowBudget ? '⚠️ Warning: ' : '✅ '); ?>Executive Office Budget
                    </h5>
                    <span style="background: var(--bg-subtle); padding: var(--spacing-1) var(--spacing-3); border-radius: var(--border-radius-full); font-size: var(--font-size-xs); font-weight: var(--font-weight-semibold);">
                        <?php echo date('M Y', strtotime($budgetInfo['start_date'])); ?> - <?php echo date('M Y', strtotime($budgetInfo['end_date'])); ?>
                    </span>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-4); margin-bottom: var(--spacing-4);">
                    <div>
                        <p style="margin: 0; font-size: var(--font-size-xs); color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">Total Budget</p>
                        <p style="margin: var(--spacing-1) 0 0 0; font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); color: var(--text-primary);">
                            ₦<?php echo number_format($budgetInfo['budget_amount'], 2); ?>
                        </p>
                    </div>
                    <div>
                        <p style="margin: 0; font-size: var(--font-size-xs); color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">Allocated</p>
                        <p style="margin: var(--spacing-1) 0 0 0; font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); color: var(--warning);">
                            ₦<?php echo number_format($budgetInfo['allocated_amount'], 2); ?>
                        </p>
                    </div>
                    <div>
                        <p style="margin: 0; font-size: var(--font-size-xs); color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">Available</p>
                        <p style="margin: var(--spacing-1) 0 0 0; font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); color: <?php echo $isCritical ? 'var(--danger)' : 'var(--success)'; ?>;">
                            ₦<?php echo number_format($budgetInfo['available_amount'], 2); ?>
                        </p>
                    </div>
                </div>
                
<!-- Progress Bar -->
<div style="background: var(--bg-subtle); border-radius: var(--border-radius-full); height: 28px; margin-bottom: var(--spacing-2); border: 1px solid var(--border-color); position: relative; overflow: hidden;">
    <div style="background: <?php echo $isCritical ? 'var(--danger)' : ($isLowBudget ? 'var(--warning)' : 'var(--success)'); ?>; 
                height: 100%; 
                width: <?php echo min($utilizationPercentage, 100); ?>%; 
                transition: width 0.3s ease;
                box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);">
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
                font-weight: var(--font-weight-bold); 
                font-size: var(--font-size-sm);
                pointer-events: none;">
        <?php echo number_format($utilizationPercentage, 1); ?>% Used
    </div>
</div>
                
                <div style="display: flex; gap: var(--spacing-4); font-size: var(--font-size-sm); flex-wrap: wrap;">
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
                        <i class="fas fa-chart-line"></i> Active Allocations: <strong><?php echo $budgetInfo['active_allocations']; ?></strong>
                    </span>
                    <span style="color: var(--text-secondary);">
                        <i class="fas fa-history"></i> Total Allocations: <strong><?php echo $budgetInfo['total_allocations']; ?></strong>
                    </span>
                    <?php
                    $daysRemaining = max(0, floor((strtotime($budgetInfo['end_date']) - time()) / (60 * 60 * 24)));
                    ?>
                    <span style="color: <?php echo $daysRemaining < 7 ? 'var(--warning)' : 'var(--text-secondary)'; ?>;">
                        <i class="fas fa-clock"></i> Days Remaining: <strong><?php echo $daysRemaining; ?></strong>
                    </span>
                </div>
                
                <?php if ($isCritical): ?>
                <div style="margin-top: var(--spacing-3); padding: var(--spacing-3); background: rgba(var(--danger-rgb), 0.1); border-radius: var(--border-radius); border-left: 4px solid var(--danger);">
                    <p style="margin: 0; color: var(--danger); font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold);">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Critical:</strong> Only ₦<?php echo number_format($budgetInfo['available_amount'], 2); ?> remaining in Executive Office budget. New requisitions may be rejected due to insufficient budget.
                    </p>
                </div>
                <?php elseif ($isLowBudget): ?>
                <div style="margin-top: var(--spacing-3); padding: var(--spacing-3); background: rgba(var(--warning-rgb), 0.1); border-radius: var(--border-radius); border-left: 4px solid var(--warning);">
                    <p style="margin: 0; color: var(--warning); font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold);">
                        <i class="fas fa-info-circle"></i> <strong>Notice:</strong> Executive Office budget is <?php echo number_format($utilizationPercentage, 1); ?>% utilized. Consider planning for budget renewal.
                    </p>
                </div>
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
                <h3 class="revenue-card-title">Total Spending</h3>
                <p class="revenue-card-value">₦<?php echo number_format((float)$stats['organization_amount'], 2); ?></p>
            </div>
        </div>
    </div>

    <div class="revenue-card">
        <div class="revenue-card-header">
            <div class="revenue-card-icon" style="background-color: var(--info);">
                <i class="fas fa-building"></i>
            </div>
            <div class="revenue-card-content">
                <h3 class="revenue-card-title">Total Requisitions</h3>
                <p class="revenue-card-value"><?php echo number_format($stats['organization_total']); ?></p>
            </div>
        </div>
    </div>

    <div class="revenue-card">
        <div class="revenue-card-header">
            <div class="revenue-card-icon" style="background-color: var(--success);">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="revenue-card-content">
                <h3 class="revenue-card-title">This Month</h3>
                <p class="revenue-card-value"><?php echo number_format($stats['this_month']); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards - Dasher UI Style -->
<div class="chart-grid">
    <!-- Pending Approval -->
    <div class="improved-stats-card">
        <div class="improved-stats-header">
            <div class="improved-stats-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="improved-stats-content">
                <h3 class="improved-stats-title">Pending Approval</h3>
                <p class="improved-stats-value"><?php echo number_format($stats['pending_my_approval']); ?></p>
            </div>
        </div>
        <div style="margin-top: var(--spacing-3);">
            <a href="<?php echo BASE_URL; ?>/requisitions/pending.php" class="btn btn-sm btn-outline-warning">Review</a>
        </div>
    </div>

    <!-- Approved by Me -->
    <div class="improved-stats-card">
        <div class="improved-stats-header">
            <div class="improved-stats-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="improved-stats-content">
                <h3 class="improved-stats-title">Approved by Me</h3>
                <p class="improved-stats-value"><?php echo number_format($stats['approved']); ?></p>
            </div>
        </div>
        <div style="margin-top: var(--spacing-3);">
            <a href="<?php echo BASE_URL; ?>/requisitions/list.php?approved_by=me" class="btn btn-sm btn-outline-success">View</a>
        </div>
    </div>

    <!-- Rejected by Me -->
    <?php if ($stats['rejected'] > 0): ?>
    <div class="improved-stats-card">
        <div class="improved-stats-header">
            <div class="improved-stats-icon danger">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="improved-stats-content">
                <h3 class="improved-stats-title">Rejected by Me</h3>
                <p class="improved-stats-value"><?php echo number_format($stats['rejected']); ?></p>
            </div>
        </div>
        <div style="margin-top: var(--spacing-3);">
            <a href="<?php echo BASE_URL; ?>/requisitions/list.php?rejected_by=me" class="btn btn-sm btn-outline-danger">View</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Charts and Quick Actions Row -->
<div class="charts-column-layout">
    <!-- Organization Trend Chart -->
    <div class="chart-container">
        <div class="chart-header">
            <div>
                <h2 class="chart-title">Organization Spending Trend</h2>
                <p class="chart-subtitle">Last 6 months organization overview</p>
            </div>
        </div>
        <div class="chart-body">
            <?php if (empty(array_filter(array_column($monthlyData, 'amount')))): ?>
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: var(--spacing-8); color: var(--text-muted);">
                    <i class="fas fa-chart-line" style="font-size: 3rem; margin-bottom: var(--spacing-3); opacity: 0.5;"></i>
                    <p style="margin: 0;">No spending data available yet.</p>
                </div>
            <?php else: ?>
                <canvas id="organizationTrendChart" class="chart-canvas"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="chart-container">
        <div class="chart-header">
            <h2 class="chart-title">Quick Actions</h2>
        </div>
        <div class="chart-body" style="display: flex; flex-direction: column; gap: var(--spacing-3); min-height: auto;">
            <a href="<?php echo BASE_URL; ?>/requisitions/pending.php" class="quick-action-card warning">
                <div class="quick-action-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="quick-action-content">
                    <h3 class="quick-action-title">Pending Approvals</h3>
                    <p class="quick-action-description"><?php echo $stats['pending_my_approval']; ?> awaiting</p>
                </div>
                <div class="quick-action-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>

            <a href="<?php echo BASE_URL; ?>/reports/organization.php" class="quick-action-card info">
                <div class="quick-action-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="quick-action-content">
                    <h3 class="quick-action-title">Organization Reports</h3>
                    <p class="quick-action-description">View analytics</p>
                </div>
                <div class="quick-action-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>

            <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="quick-action-card primary">
                <div class="quick-action-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="quick-action-content">
                    <h3 class="quick-action-title">Create Requisition</h3>
                    <p class="quick-action-description">Submit request</p>
                </div>
                <div class="quick-action-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>

            <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="quick-action-card success">
                <div class="quick-action-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="quick-action-content">
                    <h3 class="quick-action-title">My Requisitions</h3>
                    <p class="quick-action-description">View mine</p>
                </div>
                <div class="quick-action-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Pending Approvals Table -->
<?php if (!empty($pendingApprovals)): ?>
    <div class="table-container">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="card-title">
                    <i class="fas fa-clock me-2"></i> Pending Approvals
                </h2>
                <a href="<?php echo BASE_URL; ?>/requisitions/pending.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sortable">
                <thead>
                    <tr>
                        <th>Req. No.</th>
                        <th>Date</th>
                        <th>Requester</th>
                        <th>Department</th>
                        <th>Purpose</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingApprovals as $req): ?>
                        <tr>
                            <td>
                                <span style="font-weight: var(--font-weight-medium);">
                                    <?php echo htmlspecialchars($req['requisition_number']); ?>
                                </span>
                            </td>
                            <td><?php echo format_date($req['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></td>
                            <td>
                                <span class="text-muted"><?php echo htmlspecialchars($req['department_code'] ?? 'N/A'); ?></span>
                            </td>
                            <td>
                                <span style="max-width: 150px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($req['purpose']); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <span style="font-weight: var(--font-weight-semibold);">₦<?php echo number_format((float)$req['total_amount'], 2); ?></span>

                            </td>
                            <td class="text-end">
                                <a href="<?php echo build_encrypted_url(BASE_URL . '/requisitions/view.php', $req['id']); ?>"
                                   class="btn btn-sm btn-warning" title="Review">
                                    <i class="fas fa-check me-1"></i>Review
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Department Summary -->
<div class="table-container">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-building me-2"></i> Department Overview
        </h2>
    </div>
    <div class="table-responsive">
        <table class="table table-sortable">
            <thead>
                <tr>
                    <th>Department</th>
                    <th class="text-center">Requisitions</th>
                    <th class="text-end">Total Amount</th>
                    <th class="text-end">Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departmentSummary as $dept): ?>
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
                            <span style="font-weight: var(--font-weight-semibold);">₦<?php echo number_format((float)$dept['total_amount'], 2); ?></span>

                        </td>
                        <td class="text-end">
                            <?php 
                            $percentage = $stats['organization_amount'] > 0 
                                ? ($dept['total_amount'] / $stats['organization_amount']) * 100 
                                : 0;
                            ?>
                            <span class="text-muted">
                                <?php echo number_format($percentage, 1); ?>%
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Organization Activity -->
<div class="table-container">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-history me-2"></i> Recent Organization Activity
        </h2>
    </div>
    <div class="table-responsive">
        <table class="table table-sortable">
            <thead>
                <tr>
                    <th>Req. No.</th>
                    <th>Date</th>
                    <th>Requester</th>
                    <th>Department</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentActivity as $req): ?>
                    <tr>
                        <td>
                            <span style="font-weight: var(--font-weight-medium);">
                                <?php echo htmlspecialchars($req['requisition_number']); ?>
                            </span>
                        </td>
                        <td><?php echo format_date($req['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></td>
                        <td>
                            <span class="text-muted"><?php echo htmlspecialchars($req['department_name'] ?? 'N/A'); ?></span>
                        </td>
                        <td class="text-end">
<span style="font-weight: var(--font-weight-semibold);">₦<?php echo number_format((float)$req['total_amount'], 2); ?></span>

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
                            <a href="<?php echo build_encrypted_url(BASE_URL . '/requisitions/view.php', $req['id']); ?>"
                               class="btn btn-sm btn-ghost" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Dasher Chart Configuration and Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('ðŸŽ¨ Initializing Managing Director Dashboard...');

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
    let organizationTrendChart = null;

    // Organization Trend Chart
    <?php if (!empty(array_filter(array_column($monthlyData, 'amount')))): ?>
        const trendCtx = document.getElementById('organizationTrendChart');
        if (trendCtx) {
            const monthlyData = <?php echo json_encode($monthlyData); ?>;

            organizationTrendChart = new Chart(trendCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: monthlyData.map(d => d.month),
                    datasets: [{
                        label: 'Organization Spending (₦)',
                        data: monthlyData.map(d => d.amount),
                        borderColor: chartConfig.colors.primary,
                        backgroundColor: chartConfig.colors.primary + '20',
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
                                    return 'Spending: ₦' + context.parsed.y.toLocaleString();
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
                                    return '₦' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }
    <?php endif; ?>

    // Update chart when theme changes
document.addEventListener('themeChanged', function(event) {
    console.log('🎨 Updating MD charts for theme:', event.detail.theme);
    const newConfig = getDasherChartConfig();

    if (organizationTrendChart) {
        // Line & fill
        organizationTrendChart.data.datasets[0].borderColor = newConfig.colors.primary;
        organizationTrendChart.data.datasets[0].backgroundColor = newConfig.colors.primary + '20';
        organizationTrendChart.data.datasets[0].pointBackgroundColor = newConfig.colors.primary;

        // Ticks
        organizationTrendChart.options.scales.x.ticks.color = newConfig.colors.textSecondary;
        organizationTrendChart.options.scales.y.ticks.color = newConfig.colors.textSecondary;

        // Grid
        organizationTrendChart.options.scales.y.grid.color = newConfig.colors.border + '40';

        // Tooltip border (optional but nice)
        organizationTrendChart.options.plugins.tooltip.borderColor = newConfig.colors.border;

        organizationTrendChart.update('none');
    }
});

    console.log('âœ… Managing Director Dashboard initialized successfully');
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
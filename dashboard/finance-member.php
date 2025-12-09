<?php
/**
 * GateWey Requisition Management System
 * Finance Member Dashboard - Dasher UI Enhanced
 * 
 * File: dashboard/finance-member.php
 * Purpose: Dashboard for Finance Members (Payment processing)
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
// Check if user is Finance Member
if (!is_finance_member()) {
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
    'pending_payment' => 0,
    'total_processed' => 0,
    'total_processed_amount' => 0,
    'this_month_processed' => 0,
    'pending_receipts' => 0
];

// Pending payment
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE status = ?";
$result = $db->fetchOne($sql, [STATUS_APPROVED_FOR_PAYMENT]);
$stats['pending_payment'] = $result['count'];

// Total processed
$sql = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total
        FROM requisitions 
        WHERE status IN (?, ?)";
$result = $db->fetchOne($sql, [STATUS_PAID, STATUS_COMPLETED]);
$stats['total_processed'] = $result['count'];
$stats['total_processed_amount'] = $result['total'];

// This month processed
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE MONTH(payment_date) = MONTH(CURRENT_DATE())
        AND YEAR(payment_date) = YEAR(CURRENT_DATE())
        AND status IN (?, ?)";
$result = $db->fetchOne($sql, [STATUS_PAID, STATUS_COMPLETED]);
$stats['this_month_processed'] = $result['count'];

// Pending receipts
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE status = ?";
$result = $db->fetchOne($sql, [STATUS_PAID]);
$stats['pending_receipts'] = $result['count'];

// Get pending payment requisitions
$sql = "SELECT r.*, u.first_name, u.last_name, d.department_name, d.department_code
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        WHERE r.status = ?
        ORDER BY r.updated_at ASC
        LIMIT 10";
$pendingPayments = $db->fetchAll($sql, [STATUS_APPROVED_FOR_PAYMENT]);

// Get recent payments processed by me
$sql = "SELECT r.*, u.first_name, u.last_name, d.department_name
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        WHERE r.status IN (?, ?)
        AND r.paid_by = ?
        ORDER BY r.payment_date DESC
        LIMIT 5";
$recentPayments = $db->fetchAll($sql, [STATUS_PAID, STATUS_COMPLETED, $userId]);

// Get pending receipts
$sql = "SELECT r.*, u.first_name, u.last_name, d.department_name
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        WHERE r.status = ?
        ORDER BY r.payment_date ASC
        LIMIT 5";
$pendingReceipts = $db->fetchAll($sql, [STATUS_PAID]);

// Get monthly data for chart (last 6 months)
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $sql = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total
            FROM requisitions
            WHERE DATE_FORMAT(payment_date, '%Y-%m') = ?
            AND paid_by = ?
            AND status IN (?, ?)";
    $result = $db->fetchOne($sql, [$date, $userId, STATUS_PAID, STATUS_COMPLETED]);
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
$pageTitle = 'Finance Member Dashboard';
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
            <p class="content-subtitle">Finance Member - Payment Processing Dashboard</p>
        </div>
        <div class="content-actions">
            <!-- <?php if ($stats['pending_payment'] > 0): ?>
                <a href="<?php echo BASE_URL; ?>/finance/pending-payment.php" class="btn btn-success">
                    <i class="fas fa-money-check-alt me-2"></i>Process Payments
                    <span class="badge bg-white text-success ms-2"><?php echo $stats['pending_payment']; ?></span>
                </a>
            <?php endif; ?> -->
            <a href="<?php echo BASE_URL; ?>/finance/payment-history.php" class="btn btn-outline-primary">
                <i class="fas fa-history me-2"></i>Payment History
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

<!-- Action Required Alert - Pending Payment -->
<?php if ($stats['pending_payment'] > 0): ?>
    <div style="border: 1px solid var(--success); border-radius: var(--border-radius); padding: var(--spacing-5); margin-bottom: var(--spacing-6);">
        
        <div class="d-flex align-items-start gap-3">
            <!-- Success Info Icon -->
            <i class="fas fa-info-circle" 
               style="font-size: 2rem; color: var(--success); flex-shrink: 0; margin-top: 0.25rem; margin-right: 0.75rem;"></i>

            <!-- Content: Text + Button (button at far right) -->
            <div style="flex: 1; display: flex; justify-content: space-between; align-items: flex-end; gap: 2rem; min-width: 0;">
                
                <!-- Left: Text -->
                <div style="flex: 1;">
                    <h5 style="margin: 0 0 var(--spacing-2) 0; font-weight: var(--font-weight-semibold); color: white;">
                        Action Required
                    </h5>
                    <p style="margin: 0; opacity: 0.9; color: white;">
                        You have <strong><?php echo $stats['pending_payment']; ?></strong> 
                        requisition<?php echo $stats['pending_payment'] > 1 ? 's' : ''; ?> ready for payment processing.
                    </p>
                </div>

                <!-- Right: Button - pushed to the far end -->
                <div style="flex-shrink: 0;">
                    <a href="<?php echo BASE_URL; ?>/finance/pending-payment.php" 
                       class="btn btn-success" 
                       style="white-space: nowrap; font-weight: 600;">
                        <i class="fas fa-money-check-alt me-2"></i>
                        Process Now <strong><?php echo $stats['pending_payment']; ?></strong>
                    </a>
                </div>
            </div>
        </div>
        
    </div>
<?php endif; ?>

<!-- Key Metrics - Revenue Card Style -->
<div class="revenue-cards-grid">
    <div class="revenue-card">
        <div class="revenue-card-header">
            <div class="revenue-card-icon" style="background-color: var(--info);">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="revenue-card-content">
                <h3 class="revenue-card-title">Total Processed</h3>
                <p class="revenue-card-value">₦<?php echo number_format((float)$stats['total_processed_amount'], 2); ?></p>
            </div>
        </div>
    </div>

    <div class="revenue-card">
        <div class="revenue-card-header">
            <div class="revenue-card-icon" style="background-color: var(--primary);">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="revenue-card-content">
                <h3 class="revenue-card-title">Payments Made</h3>
                <p class="revenue-card-value"><?php echo number_format($stats['total_processed']); ?></p>
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
                <p class="revenue-card-value"><?php echo number_format($stats['this_month_processed']); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards - Dasher UI Style -->
<div class="chart-grid">
    <!-- Pending Payment -->
    <div class="improved-stats-card">
        <div class="improved-stats-header">
            <div class="improved-stats-icon success">
                <i class="fas fa-money-check-alt"></i>
            </div>
            <div class="improved-stats-content">
                <h3 class="improved-stats-title">Pending Payment</h3>
                <p class="improved-stats-value"><?php echo number_format($stats['pending_payment']); ?></p>
            </div>
        </div>
        <div style="margin-top: var(--spacing-3);">
            <a href="<?php echo BASE_URL; ?>/finance/pending-payment.php" class="btn btn-sm btn-outline-success">Process</a>
        </div>
    </div>

    <!-- Pending Receipts -->
    <div class="improved-stats-card">
        <div class="improved-stats-header">
            <div class="improved-stats-icon warning">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="improved-stats-content">
                <h3 class="improved-stats-title">Pending Receipts</h3>
                <p class="improved-stats-value"><?php echo number_format($stats['pending_receipts']); ?></p>
            </div>
        </div>
        <div style="margin-top: var(--spacing-3);">
            <a href="<?php echo BASE_URL; ?>/finance/pending-receipts.php" class="btn btn-sm btn-outline-warning">Track</a>
        </div>
    </div>
</div>

<!-- Charts and Quick Actions Row -->
<div class="charts-column-layout">
    <!-- Processing Activity Chart -->
    <div class="chart-container">
        <div class="chart-header">
            <div>
                <h2 class="chart-title">My Processing Activity</h2>
                <p class="chart-subtitle">Last 6 months payment processing</p>
            </div>
        </div>
        <div class="chart-body">
            <?php if (empty(array_filter(array_column($monthlyData, 'count')))): ?>
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: var(--spacing-8); color: var(--text-muted);">
                    <i class="fas fa-chart-bar" style="font-size: 3rem; margin-bottom: var(--spacing-3); opacity: 0.5;"></i>
                    <p style="margin: 0;">No processing data available yet.</p>
                </div>
            <?php else: ?>
                <canvas id="processingActivityChart" class="chart-canvas"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="chart-container">
        <div class="chart-header">
            <h2 class="chart-title">Quick Actions</h2>
        </div>
        <div class="chart-body" style="display: flex; flex-direction: column; gap: var(--spacing-3); min-height: auto;">
            <a href="<?php echo BASE_URL; ?>/finance/pending-payment.php" class="quick-action-card success">
                <div class="quick-action-icon">
                    <i class="fas fa-money-check-alt"></i>
                </div>
                <div class="quick-action-content">
                    <h3 class="quick-action-title">Process Payments</h3>
                    <p class="quick-action-description"><?php echo $stats['pending_payment']; ?> ready</p>
                </div>
                <div class="quick-action-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>

            <a href="<?php echo BASE_URL; ?>/finance/pending-receipts.php" class="quick-action-card warning">
                <div class="quick-action-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="quick-action-content">
                    <h3 class="quick-action-title">Pending Receipts</h3>
                    <p class="quick-action-description"><?php echo $stats['pending_receipts']; ?> pending</p>
                </div>
                <div class="quick-action-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>

            <a href="<?php echo BASE_URL; ?>/finance/payment-history.php" class="quick-action-card info">
                <div class="quick-action-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div class="quick-action-content">
                    <h3 class="quick-action-title">Payment History</h3>
                    <p class="quick-action-description"><?php echo $stats['total_processed']; ?> processed</p>
                </div>
                <div class="quick-action-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>

            <a href="<?php echo BASE_URL; ?>/reports/personal.php?period=monthly" class="quick-action-card primary">
                <div class="quick-action-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="quick-action-content">
                    <h3 class="quick-action-title">This Month</h3>
                    <p class="quick-action-description"><?php echo $stats['this_month_processed']; ?> payments</p>
                </div>
                <div class="quick-action-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Pending Payments Table -->
<?php if (!empty($pendingPayments)): ?>
    <div class="table-container">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="card-title">
                    <i class="fas fa-money-check-alt me-2"></i> Ready for Payment
                </h2>
                <a href="<?php echo BASE_URL; ?>/finance/pending-payment.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sortable">
                <thead>
                    <tr>
                        <th>Req. No.</th>
                        <th>Date Approved</th>
                        <th>Requester</th>
                        <th>Department</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingPayments as $req): ?>
                        <tr>
                            <td>
                                <span style="font-weight: var(--font-weight-medium);">
                                    <?php echo htmlspecialchars($req['requisition_number']); ?>
                                </span>
                            </td>
                            <td><?php echo format_date($req['updated_at']); ?></td>
                            <td><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></td>
                            <td>
                                <span class="text-muted"><?php echo htmlspecialchars($req['department_code'] ?? 'N/A'); ?></span>
                            </td>
                            <td class="text-end">
                                <span style="font-weight: var(--font-weight-semibold);">₦<?php echo number_format((float)$req['total_amount'], 2); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo BASE_URL; ?>/finance/process-payment.php?id=<?php echo $req['id']; ?>" 
                                   class="btn btn-sm btn-success" title="Process Payment">
                                    <i class="fas fa-money-check-alt me-1"></i>Process
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Pending Receipts -->
<?php if (!empty($pendingReceipts)): ?>
    <div class="table-container">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="card-title">
                    <i class="fas fa-receipt me-2"></i> Awaiting Receipt Submission
                </h2>
                <a href="<?php echo BASE_URL; ?>/finance/pending-receipts.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sortable">
                <thead>
                    <tr>
                        <th>Req. No.</th>
                        <th>Date Paid</th>
                        <th>Requester</th>
                        <th>Department</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingReceipts as $req): ?>
                        <tr>
                            <td>
                                <span style="font-weight: var(--font-weight-medium);">
                                    <?php echo htmlspecialchars($req['requisition_number']); ?>
                                </span>
                            </td>
                            <td><?php echo format_date($req['payment_date']); ?></td>
                            <td><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></td>
                            <td>
                                <span class="text-muted"><?php echo htmlspecialchars($req['department_name'] ?? 'N/A'); ?></span>
                            </td>
                            <td class="text-end">
                                <span style="font-weight: var(--font-weight-semibold);">₦<?php echo number_format((float)$req['total_amount'], 2); ?>
                                </span>
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
        </div>
    </div>
<?php endif; ?>

<!-- My Recent Payments -->
<div class="table-container">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="card-title">
                <i class="fas fa-history me-2"></i> My Recent Payments
            </h2>
            <a href="<?php echo BASE_URL; ?>/finance/payment-history.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
    </div>
    <div class="table-responsive">
        <?php if (empty($recentPayments)): ?>
            <div style="padding: var(--spacing-8); text-align: center;">
                <i class="fas fa-money-bill-wave" style="font-size: 3rem; color: var(--text-muted); margin-bottom: var(--spacing-3); opacity: 0.5;"></i>
                <h6 style="color: var(--text-muted); margin-bottom: var(--spacing-2);">No Payments Processed Yet</h6>
                <p style="color: var(--text-muted);">Your processed payments will appear here</p>
            </div>
        <?php else: ?>
            <table class="table table-sortable">
                <thead>
                    <tr>
                        <th>Req. No.</th>
                        <th>Date Paid</th>
                        <th>Requester</th>
                        <th class="text-end">Amount</th>
                        <th>Receipt</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentPayments as $req): ?>
                        <tr>
                            <td>
                                <span style="font-weight: var(--font-weight-medium);">
                                    <?php echo htmlspecialchars($req['requisition_number']); ?>
                                </span>
                            </td>
                            <td><?php echo format_date($req['payment_date']); ?></td>
                            <td><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></td>
                            <td class="text-end">
                                <span style="font-weight: var(--font-weight-semibold);">₦<?php echo number_format((float)$req['total_amount'], 2); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($req['status'] == STATUS_COMPLETED): ?>
                                    <span class="status-indicator status-completed">
                                        <span class="status-dot"></span>
                                        <span class="status-text">Submitted</span>
                                    </span>
                                <?php else: ?>
                                    <span class="text-warning">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                <?php endif; ?>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Dasher Chart Configuration and Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('ðŸŽ¨ Initializing Finance Member Dashboard...');

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
    let processingActivityChart = null;

    // Processing Activity Chart
    <?php if (!empty(array_filter(array_column($monthlyData, 'count')))): ?>
        const activityCtx = document.getElementById('processingActivityChart');
        if (activityCtx) {
            const monthlyData = <?php echo json_encode($monthlyData); ?>;

            processingActivityChart = new Chart(activityCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: monthlyData.map(d => d.month),
                    datasets: [{
                        label: 'Payments Processed',
                        data: monthlyData.map(d => d.count),
                        backgroundColor: chartConfig.colors.success + '40',
                        borderColor: chartConfig.colors.success,
                        borderWidth: 2
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
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    <?php endif; ?>

    // Update chart when theme changes
    document.addEventListener('themeChanged', function(event) {
        console.log('ðŸŽ¨ Updating Finance Member charts for theme:', event.detail.theme);

        const newConfig = getDasherChartConfig();

        // Update processing activity chart
        if (processingActivityChart) {
            processingActivityChart.data.datasets[0].backgroundColor = newConfig.colors.success + '40';
            processingActivityChart.data.datasets[0].borderColor = newConfig.colors.success;
            processingActivityChart.options.scales.x.ticks.color = newConfig.colors.textSecondary;
            processingActivityChart.options.scales.y.ticks.color = newConfig.colors.textSecondary;
            processingActivityChart.options.scales.y.grid.color = newConfig.colors.border + '40';
            processingActivityChart.update('none');
        }
    });

    console.log('âœ… Finance Member Dashboard initialized successfully');
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
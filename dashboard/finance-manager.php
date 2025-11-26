<?php
/**
 * GateWey Requisition Management System
 * Finance Manager Dashboard
 * 
 * File: dashboard/finance-manager.php
 * Purpose: Dashboard for Finance Manager (Final approval & payment oversight)
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
// Check if user is Finance Manager
if (!is_finance_manager()) {
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
    'pending_review' => 0,
    'approved_for_payment' => 0,
    'total_paid' => 0,
    'pending_receipts' => 0,
    'total_paid_amount' => 0,
    'this_month_paid' => 0
];

// Pending my review
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE status = ?";
$result = $db->fetchOne($sql, [STATUS_PENDING_FINANCE_MANAGER]);
$stats['pending_review'] = $result['count'];

// Approved for payment
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE status = ?";
$result = $db->fetchOne($sql, [STATUS_APPROVED_FOR_PAYMENT]);
$stats['approved_for_payment'] = $result['count'];

// Total paid
$sql = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total
        FROM requisitions 
        WHERE status IN (?, ?)";
$result = $db->fetchOne($sql, [STATUS_PAID, STATUS_COMPLETED]);
$stats['total_paid'] = $result['count'];
$stats['total_paid_amount'] = $result['total'];

// Pending receipts
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE status = ?";
$result = $db->fetchOne($sql, [STATUS_PAID]);
$stats['pending_receipts'] = $result['count'];

// This month paid
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE MONTH(paid_at) = MONTH(CURRENT_DATE())
        AND YEAR(paid_at) = YEAR(CURRENT_DATE())
        AND status IN (?, ?)";
$result = $db->fetchOne($sql, [STATUS_PAID, STATUS_COMPLETED]);
$stats['this_month_paid'] = $result['count'];

// Get pending review requisitions
$sql = "SELECT r.*, u.first_name, u.last_name, d.department_name, d.department_code
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        WHERE r.status = ?
        ORDER BY r.updated_at ASC
        LIMIT 10";
$pendingReview = $db->fetchAll($sql, [STATUS_PENDING_FINANCE_MANAGER]);

// Get approved for payment
$sql = "SELECT r.*, u.first_name, u.last_name, d.department_name
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        WHERE r.status = ?
        ORDER BY r.updated_at ASC
        LIMIT 5";
$approvedForPayment = $db->fetchAll($sql, [STATUS_APPROVED_FOR_PAYMENT]);

// Get recent payments
$sql = "SELECT r.*, u.first_name, u.last_name, d.department_name
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        WHERE r.status IN (?, ?)
        ORDER BY r.paid_at DESC
        LIMIT 5";
$recentPayments = $db->fetchAll($sql, [STATUS_PAID, STATUS_COMPLETED]);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Finance Manager Dashboard';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Add Status Indicator CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/status-indicators.css">

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">
                Welcome, <?php echo htmlspecialchars($userFirstName); ?>
            </h1>
            <p class="content-subtitle">Finance Manager - Payment Oversight Dashboard</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>/finance/review-queue.php" class="btn btn-warning">
                <i class="fas fa-tasks"></i> Review Queue
                <?php if ($stats['pending_review'] > 0): ?>
                    <span class="badge bg-white text-warning"><?php echo $stats['pending_review']; ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo BASE_URL; ?>/reports/organization.php" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Requisition
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

<!-- Action Required Alert -->
<?php if ($stats['pending_review'] > 0): ?>
    <div class="alert alert-warning">
        <h6 class="alert-heading">
            <i class="fas fa-exclamation-triangle"></i> Action Required
        </h6>
        <p class="mb-0">
            You have <strong><?php echo $stats['pending_review']; ?></strong> requisition(s) awaiting your review.
            <a href="<?php echo BASE_URL; ?>/finance/review-queue.php" class="btn btn-sm btn-warning ms-2">
                <i class="fas fa-eye"></i> Review Now
            </a>
        </p>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Pending Review</p>
            <p class="stat-value"><?php echo number_format($stats['pending_review']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Approved for Payment</p>
            <p class="stat-value"><?php echo number_format($stats['approved_for_payment']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Paid</p>
            <p class="stat-value"><?php echo format_currency($stats['total_paid_amount']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Pending Receipts</p>
            <p class="stat-value"><?php echo number_format($stats['pending_receipts']); ?></p>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-bolt"></i> Quick Actions
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/finance/review-queue.php" class="quick-action-card">
                    <div class="quick-action-icon bg-warning">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>Review Queue</h6>
                        <p><?php echo $stats['pending_review']; ?> awaiting</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/finance/pending-receipts.php" class="quick-action-card">
                    <div class="quick-action-icon bg-info">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>Pending Receipts</h6>
                        <p><?php echo $stats['pending_receipts']; ?> pending</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/finance/payment-history.php" class="quick-action-card">
                    <div class="quick-action-icon bg-success">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>Payment History</h6>
                        <p><?php echo $stats['total_paid']; ?> paid</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/reports/organization.php" class="quick-action-card">
                    <div class="quick-action-icon bg-primary">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>Reports</h6>
                        <p>View analytics</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Pending Review Table -->
<?php if (!empty($pendingReview)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-hourglass-half"></i> Pending Review
                </h5>
                <a href="<?php echo BASE_URL; ?>/finance/review-queue.php" class="btn btn-sm btn-ghost">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Req. No.</th>
                            <th>Date</th>
                            <th>Requester</th>
                            <th>Department</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingReview as $req): ?>
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
                                <td class="text-end">
                                    <span style="font-weight: var(--font-weight-semibold);">
                                        <?php echo format_currency($req['total_amount']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?php echo BASE_URL; ?>/requisitions/view.php?id=<?php echo $req['id']; ?>" 
                                       class="btn btn-sm btn-warning" title="Review">
                                        <i class="fas fa-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Approved for Payment -->
<?php if (!empty($approvedForPayment)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-check-circle"></i> Approved for Payment
                </h5>
                <a href="<?php echo BASE_URL; ?>/finance/pending-payment.php" class="btn btn-sm btn-ghost">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Req. No.</th>
                            <th>Requester</th>
                            <th>Department</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approvedForPayment as $req): ?>
                            <tr>
                                <td>
                                    <span style="font-weight: var(--font-weight-medium);">
                                        <?php echo htmlspecialchars($req['requisition_number']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></td>
                                <td>
                                    <span class="text-muted"><?php echo htmlspecialchars($req['department_name'] ?? 'N/A'); ?></span>
                                </td>
                                <td class="text-end">
                                    <span style="font-weight: var(--font-weight-semibold);">
                                        <?php echo format_currency($req['total_amount']); ?>
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
    </div>
<?php endif; ?>

<!-- Recent Payments -->
<div class="card mt-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-money-check-alt"></i> Recent Payments
            </h5>
            <a href="<?php echo BASE_URL; ?>/finance/payment-history.php" class="btn btn-sm btn-ghost">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($recentPayments)): ?>
            <div class="text-center py-5">
                <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">No Payments Yet</h6>
                <p class="text-muted">Paid requisitions will appear here</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Req. No.</th>
                            <th>Date Paid</th>
                            <th>Requester</th>
                            <th>Department</th>
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
                                <td><?php echo format_date($req['paid_at']); ?></td>
                                <td><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></td>
                                <td>
                                    <span class="text-muted"><?php echo htmlspecialchars($req['department_name'] ?? 'N/A'); ?></span>
                                </td>
                                <td class="text-end">
                                    <span style="font-weight: var(--font-weight-semibold);">
                                        <?php echo format_currency($req['total_amount']); ?>
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
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Monthly Payment Chart -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-chart-bar"></i> Monthly Payment Trends (Last 6 Months)
        </h5>
    </div>
    <div class="card-body">
        <canvas id="paymentTrendsChart" height="80"></canvas>
    </div>
</div>

<style>
.quick-action-card {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding: var(--spacing-4);
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    text-decoration: none;
    color: var(--text-primary);
    transition: var(--theme-transition);
}

.quick-action-card:hover {
    border-color: var(--primary);
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.quick-action-icon {
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

.quick-action-content h6 {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    margin: 0 0 var(--spacing-1);
}

.quick-action-content p {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin: 0;
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Payment trends chart data
<?php
// Get last 6 months payment data
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $sql = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total
            FROM requisitions
            WHERE DATE_FORMAT(paid_at, '%Y-%m') = ?
            AND status IN (?, ?)";
    $result = $db->fetchOne($sql, [$date, STATUS_PAID, STATUS_COMPLETED]);
    $monthlyData[] = [
        'month' => date('M Y', strtotime($date . '-01')),
        'count' => $result['count'],
        'amount' => $result['total']
    ];
}
?>

const monthlyData = <?php echo json_encode($monthlyData); ?>;

const ctx = document.getElementById('paymentTrendsChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: monthlyData.map(d => d.month),
        datasets: [{
            label: 'Payment Count',
            data: monthlyData.map(d => d.count),
            backgroundColor: 'rgba(99, 102, 241, 0.2)',
            borderColor: 'rgba(99, 102, 241, 1)',
            borderWidth: 2,
            yAxisID: 'y'
        }, {
            label: 'Payment Amount',
            data: monthlyData.map(d => d.amount),
            backgroundColor: 'rgba(16, 185, 129, 0.2)',
            borderColor: 'rgba(16, 185, 129, 1)',
            borderWidth: 2,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Payment Count'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Amount (₦)'
                },
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php
/**
 * GateWey Requisition Management System
 * Finance Member Dashboard
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
        WHERE MONTH(paid_at) = MONTH(CURRENT_DATE())
        AND YEAR(paid_at) = YEAR(CURRENT_DATE())
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
        ORDER BY r.paid_at DESC
        LIMIT 5";
$recentPayments = $db->fetchAll($sql, [STATUS_PAID, STATUS_COMPLETED, $userId]);

// Get pending receipts
$sql = "SELECT r.*, u.first_name, u.last_name, d.department_name
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        WHERE r.status = ?
        ORDER BY r.paid_at ASC
        LIMIT 5";
$pendingReceipts = $db->fetchAll($sql, [STATUS_PAID]);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Finance Member Dashboard';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Add Status Indicator CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/status-indicators.css">

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">
                <i class="fas fa-wallet me-2"></i>Welcome, <?php echo htmlspecialchars($userName); ?>
            </h1>
            <p class="content-subtitle">Finance Member - Payment Processing Dashboard</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>/finance/pending-payment.php" class="btn btn-success">
                <i class="fas fa-money-check-alt"></i> Process Payments
                <?php if ($stats['pending_payment'] > 0): ?>
                    <span class="badge bg-white text-success"><?php echo $stats['pending_payment']; ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo BASE_URL; ?>/finance/payment-history.php" class="btn btn-info">
                <i class="fas fa-history"></i> Payment History
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
<?php if ($stats['pending_payment'] > 0): ?>
    <div class="alert alert-success">
        <h6 class="alert-heading">
            <i class="fas fa-info-circle"></i> Action Required
        </h6>
        <p class="mb-0">
            You have <strong><?php echo $stats['pending_payment']; ?></strong> requisition(s) ready for payment processing.
            <a href="<?php echo BASE_URL; ?>/finance/pending-payment.php" class="btn btn-sm btn-success ms-2">
                <i class="fas fa-money-check-alt"></i> Process Now
            </a>
        </p>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-money-check-alt"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Pending Payment</p>
            <p class="stat-value"><?php echo number_format($stats['pending_payment']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-check-double"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Processed</p>
            <p class="stat-value"><?php echo number_format($stats['total_processed']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Amount</p>
            <p class="stat-value"><?php echo format_currency($stats['total_processed_amount']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-warning">
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
                <a href="<?php echo BASE_URL; ?>/finance/pending-payment.php" class="quick-action-card">
                    <div class="quick-action-icon bg-success">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>Process Payments</h6>
                        <p><?php echo $stats['pending_payment']; ?> ready</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/finance/pending-receipts.php" class="quick-action-card">
                    <div class="quick-action-icon bg-warning">
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
                    <div class="quick-action-icon bg-info">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>Payment History</h6>
                        <p><?php echo $stats['total_processed']; ?> processed</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/reports/personal.php?period=monthly" class="quick-action-card">
                    <div class="quick-action-icon bg-primary">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>This Month</h6>
                        <p><?php echo $stats['this_month_processed']; ?> payments</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Pending Payments Table -->
<?php if (!empty($pendingPayments)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-money-check-alt"></i> Ready for Payment
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
                                    <span style="font-weight: var(--font-weight-semibold);">
                                        <?php echo format_currency($req['total_amount']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?php echo BASE_URL; ?>/finance/process-payment.php?id=<?php echo $req['id']; ?>" 
                                       class="btn btn-sm btn-success" title="Process Payment">
                                        <i class="fas fa-money-check-alt"></i> Process
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

<!-- Pending Receipts -->
<?php if (!empty($pendingReceipts)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-receipt"></i> Awaiting Receipt Submission
                </h5>
                <a href="<?php echo BASE_URL; ?>/finance/pending-receipts.php" class="btn btn-sm btn-ghost">
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

<!-- My Recent Payments -->
<div class="card mt-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-history"></i> My Recent Payments
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
                <h6 class="text-muted">No Payments Processed Yet</h6>
                <p class="text-muted">Your processed payments will appear here</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
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
                                <td><?php echo format_date($req['paid_at']); ?></td>
                                <td><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></td>
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

<!-- Monthly Processing Chart -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-chart-bar"></i> My Processing Activity (Last 6 Months)
        </h5>
    </div>
    <div class="card-body">
        <canvas id="processingActivityChart" height="80"></canvas>
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
// Processing activity chart data
<?php
// Get last 6 months my processing data
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $sql = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total
            FROM requisitions
            WHERE DATE_FORMAT(paid_at, '%Y-%m') = ?
            AND paid_by = ?
            AND status IN (?, ?)";
    $result = $db->fetchOne($sql, [$date, $userId, STATUS_PAID, STATUS_COMPLETED]);
    $monthlyData[] = [
        'month' => date('M Y', strtotime($date . '-01')),
        'count' => $result['count'],
        'amount' => $result['total']
    ];
}
?>

const monthlyData = <?php echo json_encode($monthlyData); ?>;

const ctx = document.getElementById('processingActivityChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: monthlyData.map(d => d.month),
        datasets: [{
            label: 'Payments Processed',
            data: monthlyData.map(d => d.count),
            backgroundColor: 'rgba(16, 185, 129, 0.2)',
            borderColor: 'rgba(16, 185, 129, 1)',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
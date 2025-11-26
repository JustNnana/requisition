<?php
/**
 * GateWey Requisition Management System
 * Team Member Dashboard
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

// Total requisitions
$sql = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total 
        FROM requisitions 
        WHERE user_id = ? AND status != ?";
$result = $db->fetchOne($sql, [$userId, STATUS_DRAFT]);
$stats['total'] = $result['count'];
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

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Dashboard';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Add Status Indicator CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/status-indicators.css">

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">
                <i class="fas fa-home me-2"></i>Welcome, <?php echo htmlspecialchars($userName); ?>
            </h1>
            <p class="content-subtitle">Your requisition dashboard</p>
        </div>
        <div>
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

<!-- Pending Actions Alert -->
<?php if (!empty($pendingActions)): ?>
    <div class="alert alert-warning">
        <h6 class="alert-heading">
            <i class="fas fa-exclamation-triangle"></i> Action Required
        </h6>
        <p class="mb-2">You have <?php echo count($pendingActions); ?> rejected requisition(s) that need your attention:</p>
        <ul class="mb-0">
            <?php foreach ($pendingActions as $action): ?>
                <li>
                    <strong><?php echo htmlspecialchars($action['requisition_number']); ?></strong> - 
                    <?php echo htmlspecialchars($action['purpose']); ?>
                    <a href="<?php echo BASE_URL; ?>/requisitions/view.php?id=<?php echo $action['id']; ?>" 
                       class="btn btn-sm btn-warning ms-2">
                        <i class="fas fa-eye"></i> View & Edit
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
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
            <p class="stat-value"><?php echo number_format($stats['total']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Pending Approval</p>
            <p class="stat-value"><?php echo number_format($stats['pending']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Paid</p>
            <p class="stat-value"><?php echo number_format($stats['paid']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Amount</p>
            <p class="stat-value"><?php echo format_currency($stats['total_amount']); ?></p>
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
                <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="quick-action-card">
                    <div class="quick-action-icon bg-primary">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>Create Requisition</h6>
                        <p>Submit a new request</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="quick-action-card">
                    <div class="quick-action-icon bg-info">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>My Requisitions</h6>
                        <p>View all requests</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/reports/personal.php" class="quick-action-card">
                    <div class="quick-action-icon bg-success">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>Reports</h6>
                        <p>View analytics</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/reports/personal.php?period=monthly" class="quick-action-card">
                    <div class="quick-action-icon bg-warning">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>This Month</h6>
                        <p><?php echo number_format($stats['this_month']); ?> requisitions</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Requisitions -->
<div class="card mt-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-history"></i> Recent Requisitions
            </h5>
            <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="btn btn-sm btn-ghost">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($recentRequisitions)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">No Requisitions Yet</h6>
                <p class="text-muted mb-3">Start by creating your first requisition</p>
                <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Requisition
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
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
                                    <span style="font-weight: var(--font-weight-semibold);">
                                        <?php echo format_currency($req['total_amount']); ?>
                                    </span>
                                </td>
                                <td><?php echo get_status_indicator($req['status']); ?></td>
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

<!-- Monthly Summary Chart -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-chart-line"></i> Monthly Summary (Last 6 Months)
        </h5>
    </div>
    <div class="card-body">
        <canvas id="monthlySummaryChart" height="80"></canvas>
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
<script src="<?php echo BASE_URL; ?>/assets/js/charts.js"></script>
<script>
// Monthly summary chart data
<?php
// Get last 6 months data
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $sql = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total
            FROM requisitions
            WHERE user_id = ?
            AND DATE_FORMAT(created_at, '%Y-%m') = ?
            AND status != ?";
    $result = $db->fetchOne($sql, [$userId, $date, STATUS_DRAFT]);
    $monthlyData[] = [
        'month' => date('M Y', strtotime($date . '-01')),
        'count' => $result['count'],
        'amount' => $result['total']
    ];
}
?>

const monthlyData = <?php echo json_encode($monthlyData); ?>;

const ctx = document.getElementById('monthlySummaryChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: monthlyData.map(d => d.month),
        datasets: [{
            label: 'Number of Requisitions',
            data: monthlyData.map(d => d.count),
            backgroundColor: 'rgba(99, 102, 241, 0.2)',
            borderColor: 'rgba(99, 102, 241, 1)',
            borderWidth: 2,
            yAxisID: 'y'
        }, {
            label: 'Total Amount',
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
                    text: 'Number of Requisitions'
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
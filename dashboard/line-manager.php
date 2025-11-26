<?php
/**
 * GateWey Requisition Management System
 * Line Manager Dashboard
 * 
 * File: dashboard/line-manager.php
 * Purpose: Dashboard for Line Managers (Department approvers)
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
$departmentId = Session::getUserDepartmentId();

// Get department info
$department = new Department();
$deptInfo = $department->getById($departmentId);

// Get dashboard statistics
$stats = [
    'pending_my_approval' => 0,
    'department_total' => 0,
    'department_this_month' => 0,
    'department_amount' => 0,
    'team_members' => 0
];

// Pending my approval
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE department_id = ? 
        AND status = ?";
$result = $db->fetchOne($sql, [$departmentId, STATUS_PENDING_LINE_MANAGER]);
$stats['pending_my_approval'] = $result['count'];

// Department total requisitions
$sql = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total
        FROM requisitions 
        WHERE department_id = ? 
        AND status != ?";
$result = $db->fetchOne($sql, [$departmentId, STATUS_DRAFT]);
$stats['department_total'] = $result['count'];
$stats['department_amount'] = $result['total'];

// Department this month
$sql = "SELECT COUNT(*) as count 
        FROM requisitions 
        WHERE department_id = ? 
        AND MONTH(created_at) = MONTH(CURRENT_DATE())
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
        AND status != ?";
$result = $db->fetchOne($sql, [$departmentId, STATUS_DRAFT]);
$stats['department_this_month'] = $result['count'];

// Team members count
$sql = "SELECT COUNT(*) as count 
        FROM users 
        WHERE department_id = ? 
        AND role_id = ? 
        AND is_active = 1";
$result = $db->fetchOne($sql, [$departmentId, ROLE_TEAM_MEMBER]);
$stats['team_members'] = $result['count'];

// Get pending approvals
$sql = "SELECT r.*, u.first_name, u.last_name, u.email
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        WHERE r.department_id = ? 
        AND r.status = ?
        ORDER BY r.created_at ASC
        LIMIT 10";
$pendingApprovals = $db->fetchAll($sql, [$departmentId, STATUS_PENDING_LINE_MANAGER]);

// Get recent department activity
$sql = "SELECT r.*, u.first_name, u.last_name
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        WHERE r.department_id = ?
        ORDER BY r.updated_at DESC
        LIMIT 5";
$recentActivity = $db->fetchAll($sql, [$departmentId]);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Line Manager Dashboard';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Add Status Indicator CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/status-indicators.css">

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">
                <i class="fas fa-users me-2"></i>Welcome, <?php echo htmlspecialchars($userName); ?>
            </h1>
            <p class="content-subtitle">
                <?php echo htmlspecialchars($deptInfo['department_name']); ?> - Line Manager Dashboard
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>/requisitions/pending.php" class="btn btn-warning">
                <i class="fas fa-clock"></i> Pending Approvals
                <?php if ($stats['pending_my_approval'] > 0): ?>
                    <span class="badge bg-white text-warning"><?php echo $stats['pending_my_approval']; ?></span>
                <?php endif; ?>
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

<!-- Pending Approvals Alert -->
<?php if ($stats['pending_my_approval'] > 0): ?>
    <div class="alert alert-warning">
        <h6 class="alert-heading">
            <i class="fas fa-exclamation-triangle"></i> Action Required
        </h6>
        <p class="mb-0">
            You have <strong><?php echo $stats['pending_my_approval']; ?></strong> requisition(s) awaiting your approval.
            <a href="<?php echo BASE_URL; ?>/requisitions/pending.php" class="btn btn-sm btn-warning ms-2">
                <i class="fas fa-check"></i> Review Now
            </a>
        </p>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Pending My Approval</p>
            <p class="stat-value"><?php echo number_format($stats['pending_my_approval']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Department Requisitions</p>
            <p class="stat-value"><?php echo number_format($stats['department_total']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Department Total</p>
            <p class="stat-value"><?php echo format_currency($stats['department_amount']); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Team Members</p>
            <p class="stat-value"><?php echo number_format($stats['team_members']); ?></p>
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
                <a href="<?php echo BASE_URL; ?>/requisitions/pending.php" class="quick-action-card">
                    <div class="quick-action-icon bg-warning">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>Pending Approvals</h6>
                        <p><?php echo $stats['pending_my_approval']; ?> awaiting</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/reports/department.php" class="quick-action-card">
                    <div class="quick-action-icon bg-info">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>Department Reports</h6>
                        <p>View analytics</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="quick-action-card">
                    <div class="quick-action-icon bg-primary">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>Create Requisition</h6>
                        <p>Submit request</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="quick-action-card">
                    <div class="quick-action-icon bg-success">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="quick-action-content">
                        <h6>My Requisitions</h6>
                        <p>View mine</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Pending Approvals Table -->
<?php if (!empty($pendingApprovals)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock"></i> Pending Approvals
                </h5>
                <a href="<?php echo BASE_URL; ?>/requisitions/pending.php" class="btn btn-sm btn-ghost">
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
                                    <span style="max-width: 200px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($req['purpose']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span style="font-weight: var(--font-weight-semibold);">
                                        <?php echo format_currency($req['total_amount']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?php echo BASE_URL; ?>/requisitions/view.php?id=<?php echo $req['id']; ?>" 
                                       class="btn btn-sm btn-warning" title="Review">
                                        <i class="fas fa-check"></i> Review
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

<!-- Recent Department Activity -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-history"></i> Recent Department Activity
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($recentActivity)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">No Activity Yet</h6>
                <p class="text-muted">Department requisitions will appear here</p>
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
                                    <span style="max-width: 180px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
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

<!-- Department Monthly Chart -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-chart-line"></i> Department Spending Trend (Last 6 Months)
        </h5>
    </div>
    <div class="card-body">
        <canvas id="departmentTrendChart" height="80"></canvas>
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
// Department trend chart data
<?php
// Get last 6 months department data
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $sql = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total
            FROM requisitions
            WHERE department_id = ?
            AND DATE_FORMAT(created_at, '%Y-%m') = ?
            AND status != ?";
    $result = $db->fetchOne($sql, [$departmentId, $date, STATUS_DRAFT]);
    $monthlyData[] = [
        'month' => date('M Y', strtotime($date . '-01')),
        'count' => $result['count'],
        'amount' => $result['total']
    ];
}
?>

const monthlyData = <?php echo json_encode($monthlyData); ?>;

const ctx = document.getElementById('departmentTrendChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: monthlyData.map(d => d.month),
        datasets: [{
            label: 'Department Spending',
            data: monthlyData.map(d => d.amount),
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            borderColor: 'rgba(99, 102, 241, 1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '₦ ' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₦ ' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
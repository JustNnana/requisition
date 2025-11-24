<?php
/**
 * GateWey Requisition Management System
 * Super Admin Dashboard
 * 
 * File: admin/index.php
 * Purpose: Main dashboard for Super Admin with system overview
 */

// Define access level
define('APP_ACCESS', true);
define('ADMIN_ACCESS', true);

// Include necessary files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Department.php';
require_once __DIR__ . '/../helpers/permissions.php';

// Start session
Session::start();

// Check authentication and authorization
require_once __DIR__ . '/../middleware/auth-check.php';
require_once __DIR__ . '/../middleware/role-check.php';
checkRole(ROLE_SUPER_ADMIN);

// Initialize objects
$db = Database::getInstance();
$user = new User();
$department = new Department();

// Get system statistics
$stats = [];

// Total users
$result = $db->fetchOne("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $result['total'];

// Active users
$result = $db->fetchOne("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
$stats['active_users'] = $result['total'];

// Total departments
$result = $db->fetchOne("SELECT COUNT(*) as total FROM departments");
$stats['total_departments'] = $result['total'];

// Active departments
$result = $db->fetchOne("SELECT COUNT(*) as total FROM departments WHERE is_active = 1");
$stats['active_departments'] = $result['total'];

// Total requisitions
$result = $db->fetchOne("SELECT COUNT(*) as total FROM requisitions");
$stats['total_requisitions'] = $result['total'];

// Pending requisitions
$result = $db->fetchOne("SELECT COUNT(*) as total FROM requisitions 
                         WHERE status IN (?, ?, ?)",
                         [STATUS_PENDING_LINE_MANAGER, STATUS_PENDING_MD, STATUS_PENDING_FINANCE_MANAGER]);
$stats['pending_requisitions'] = $result['total'];

// Total spending this month
$result = $db->fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total 
                         FROM requisitions 
                         WHERE status = ?
                         AND MONTH(created_at) = MONTH(CURRENT_DATE())
                         AND YEAR(created_at) = YEAR(CURRENT_DATE())",
                         [STATUS_PAID]);
$stats['monthly_spending'] = $result['total'];

// Total spending this year
$result = $db->fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total 
                         FROM requisitions 
                         WHERE status = ?
                         AND YEAR(created_at) = YEAR(CURRENT_DATE())",
                         [STATUS_PAID]);
$stats['yearly_spending'] = $result['total'];

// Get recent users (last 5)
$recentUsers = $db->fetchAll("SELECT u.*, r.role_name, d.department_name
                               FROM users u
                               JOIN roles r ON u.role_id = r.id
                               LEFT JOIN departments d ON u.department_id = d.id
                               ORDER BY u.created_at DESC
                               LIMIT 5");

// Get recent activity (audit log)
$recentActivity = $db->fetchAll("SELECT a.*, u.first_name, u.last_name, u.email
                                  FROM audit_log a
                                  LEFT JOIN users u ON a.user_id = u.id
                                  ORDER BY a.created_at DESC
                                  LIMIT 10");

// Get user distribution by role
$usersByRole = $db->fetchAll("SELECT r.role_name, COUNT(u.id) as user_count
                               FROM roles r
                               LEFT JOIN users u ON r.id = u.role_id AND u.is_active = 1
                               GROUP BY r.id, r.role_name
                               ORDER BY r.id");

// Page title
$pageTitle = 'Admin Dashboard';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="content-title">Admin Dashboard</h1>
            <p class="content-subtitle">System overview and statistics</p>
        </div>
        <div>
            <span class="text-muted">Last updated: <?php echo date('M d, Y H:i'); ?></span>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-5">
    <!-- Users -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2" style="font-size: var(--font-size-sm);">Total Users</p>
                        <h2 class="mb-0" style="color: var(--primary);"><?php echo number_format($stats['total_users']); ?></h2>
                        <p class="text-success mb-0 mt-2" style="font-size: var(--font-size-xs);">
                            <?php echo $stats['active_users']; ?> active
                        </p>
                    </div>
                    <div class="icon-box" style="width: 50px; height: 50px; background: var(--primary-light); border-radius: var(--border-radius); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-users" style="font-size: 1.5rem; color: var(--primary);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Departments -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2" style="font-size: var(--font-size-sm);">Departments</p>
                        <h2 class="mb-0" style="color: var(--info);"><?php echo number_format($stats['total_departments']); ?></h2>
                        <p class="text-success mb-0 mt-2" style="font-size: var(--font-size-xs);">
                            <?php echo $stats['active_departments']; ?> active
                        </p>
                    </div>
                    <div class="icon-box" style="width: 50px; height: 50px; background: var(--info-light); border-radius: var(--border-radius); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-building" style="font-size: 1.5rem; color: var(--info);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Requisitions -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2" style="font-size: var(--font-size-sm);">Requisitions</p>
                        <h2 class="mb-0" style="color: var(--warning);"><?php echo number_format($stats['total_requisitions']); ?></h2>
                        <p class="text-warning mb-0 mt-2" style="font-size: var(--font-size-xs);">
                            <?php echo $stats['pending_requisitions']; ?> pending
                        </p>
                    </div>
                    <div class="icon-box" style="width: 50px; height: 50px; background: var(--warning-light); border-radius: var(--border-radius); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-file-alt" style="font-size: 1.5rem; color: var(--warning);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Spending -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2" style="font-size: var(--font-size-sm);">This Month</p>
                        <h2 class="mb-0" style="color: var(--success);">$<?php echo number_format($stats['monthly_spending'], 2); ?></h2>
                        <p class="text-muted mb-0 mt-2" style="font-size: var(--font-size-xs);">
                            Year: $<?php echo number_format($stats['yearly_spending'], 2); ?>
                        </p>
                    </div>
                    <div class="icon-box" style="width: 50px; height: 50px; background: var(--success-light); border-radius: var(--border-radius); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-dollar-sign" style="font-size: 1.5rem; color: var(--success);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Users by Role -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Users by Role</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th class="text-end">Active Users</th>
                                <th class="text-end">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usersByRole as $role): ?>
                                <?php 
                                    $percentage = $stats['active_users'] > 0 ? ($role['user_count'] / $stats['active_users']) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($role['role_name']); ?></td>
                                    <td class="text-end"><?php echo number_format($role['user_count']); ?></td>
                                    <td class="text-end">
                                        <span class="badge badge-primary"><?php echo number_format($percentage, 1); ?>%</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Users -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Users</h5>
                    <a href="users/list.php" class="btn btn-sm btn-ghost">View All</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $recentUser): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: var(--font-weight-medium);">
                                            <?php echo htmlspecialchars($recentUser['first_name'] . ' ' . $recentUser['last_name']); ?>
                                        </div>
                                        <div style="font-size: var(--font-size-xs); color: var(--text-secondary);">
                                            <?php echo htmlspecialchars($recentUser['email']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($recentUser['role_name']); ?></td>
                                    <td>
                                        <?php if ($recentUser['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Activity</h5>
                    <a href="audit-log.php" class="btn btn-sm btn-ghost">View All</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentActivity)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No recent activity</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentActivity as $activity): ?>
                                    <tr>
                                        <td>
                                            <div><?php echo date('M d, Y', strtotime($activity['created_at'])); ?></div>
                                            <div style="font-size: var(--font-size-xs); color: var(--text-secondary);">
                                                <?php echo date('H:i:s', strtotime($activity['created_at'])); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($activity['first_name']): ?>
                                                <div><?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?></div>
                                                <div style="font-size: var(--font-size-xs); color: var(--text-secondary);">
                                                    <?php echo htmlspecialchars($activity['email']); ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">System</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($activity['action_type']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($activity['details']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['ip_address'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
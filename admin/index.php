<?php

/**
 * GateWey Requisition Management System
 * Super Admin Dashboard - Dasher UI Enhanced
 * 
 * File: admin/index.php
 * Purpose: Main dashboard for Super Admin with system overview
 */

// Define access level
define('APP_ACCESS', true);
define('ADMIN_ACCESS', true);

// Include configuration (this auto-loads classes and includes database.php and constants.php)
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication and authorization
require_once __DIR__ . '/../middleware/auth-check.php';
require_once __DIR__ . '/../middleware/role-check.php';
require_once __DIR__ . '/../helpers/permissions.php';
checkRole(ROLE_SUPER_ADMIN);

// Initialize objects
$db = Database::getInstance();
$user = new User();
$department = new Department();

// Get system statistics
$stats = [];

// Total users
$result = $db->fetchOne("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $result['total'] ?? 0;

// Active users
$result = $db->fetchOne("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
$stats['active_users'] = $result['total'] ?? 0;

// Total departments
$result = $db->fetchOne("SELECT COUNT(*) as total FROM departments");
$stats['total_departments'] = $result['total'] ?? 0;

// Active departments
$result = $db->fetchOne("SELECT COUNT(*) as total FROM departments WHERE is_active = 1");
$stats['active_departments'] = $result['total'] ?? 0;

// Total requisitions
$result = $db->fetchOne("SELECT COUNT(*) as total FROM requisitions");
$stats['total_requisitions'] = $result['total'] ?? 0;

// Pending requisitions
$result = $db->fetchOne(
    "SELECT COUNT(*) as total FROM requisitions 
                         WHERE status IN (?, ?, ?)",
    [STATUS_PENDING_LINE_MANAGER, STATUS_PENDING_MD, STATUS_PENDING_FINANCE_MANAGER]
);
$stats['pending_requisitions'] = $result['total'] ?? 0;

// Total spending this month
$result = $db->fetchOne(
    "SELECT COALESCE(SUM(total_amount), 0) as total 
                         FROM requisitions 
                         WHERE status = ?
                         AND MONTH(created_at) = MONTH(CURRENT_DATE())
                         AND YEAR(created_at) = YEAR(CURRENT_DATE())",
    [STATUS_PAID]
);
$stats['monthly_spending'] = $result['total'] ?? 0;

// Total spending this year
$result = $db->fetchOne(
    "SELECT COALESCE(SUM(total_amount), 0) as total 
                         FROM requisitions 
                         WHERE status = ?
                         AND YEAR(created_at) = YEAR(CURRENT_DATE())",
    [STATUS_PAID]
);
$stats['yearly_spending'] = $result['total'] ?? 0;

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

<!-- Dasher UI Enhanced Styles -->
<style>
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
        transform: translateY(-2px);
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

    .improved-stats-icon.info {
        background-color: var(--info);
    }

    .improved-stats-icon.warning {
        background-color: var(--warning);
    }

    .improved-stats-icon.success {
        background-color: var(--success);
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

    .stats-subtitle {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        margin: var(--spacing-2) 0 0 0;
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

    /* Section Header */
    .section-header {
        margin-bottom: var(--spacing-4);
    }

    .section-title {
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
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

    /* Recent Tables Grid - Desktop */
    .recent-tables-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-6);
        margin-bottom: var(--spacing-6);
    }

    @media (max-width: 1024px) {
        .recent-tables-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Enhanced Table Container */
    .table-container {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        overflow: hidden;
        transition: var(--theme-transition);
    }

    .table-container:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .table-container .card-header {
        padding: var(--spacing-4);
        border-bottom: 1px solid var(--border-color);
    }

    .table-container .card-title {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    /* Info Cards Grid */
    .info-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: var(--spacing-4);
        margin-bottom: var(--spacing-6);
    }

    .info-box-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-5);
        transition: var(--theme-transition);
    }

    .info-box-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .info-box-label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0 0 var(--spacing-2) 0;
        font-weight: var(--font-weight-medium);
    }

    .info-box-value {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
        word-break: break-word;
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

        .content-header {
      flex-direction: column !important;
    }

    .content-actions {
      display: flex !important;
      flex-direction: column !important;
      gap: 0.5rem !important;
    }

        .content-actions .btn {
            flex: 0 1 auto !important;
            white-space: nowrap;
        }

        /* Mobile Tables - Stack Vertically */
        .recent-tables-grid {
            display: flex !important;
            flex-direction: column !important;
            gap: var(--spacing-4) !important;
        }

        /* Info Cards - Horizontal Scroll */
        .info-cards-grid {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            gap: var(--spacing-3) !important;
            padding-bottom: 0.5rem !important;
            scroll-snap-type: x mandatory;
        }

        .info-box-card {
            flex: 0 0 auto !important;
            min-width: 240px !important;
            scroll-snap-align: start;
        }
    }

    /* Tablet View */
    @media (min-width: 769px) and (max-width: 1024px) {
        .info-cards-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">
                <i class="fas fa-tachometer-alt me-2"></i>
                Admin Dashboard
            </h1>
            <p class="content-subtitle">System overview and statistics</p>
        </div>
        <div class="content-actions">
            <a href="users/add.php" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Add User
            </a>
            <a href="departments/add.php" class="btn btn-outline-primary">
                <i class="fas fa-building me-2"></i>Add Department
            </a>
        </div>
    </div>


    <!-- Statistics Cards - Dasher UI Enhanced Design -->
    <div class="chart-grid">
        <!-- Total Users -->
        <div class="improved-stats-card">
            <div class="improved-stats-header">
                <div class="improved-stats-icon primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="improved-stats-content">
                    <h3 class="improved-stats-title">Total Users</h3>
                    <p class="improved-stats-value"><?php echo number_format($stats['total_users']); ?></p>
                </div>
            </div>
            <p class="stats-subtitle">
                <span style="color: var(--success); font-weight: var(--font-weight-semibold);">
                    <?php echo $stats['active_users']; ?> active
                </span>
                <span style="color: var(--text-muted);"> / <?php echo $stats['total_users'] - $stats['active_users']; ?> inactive</span>
            </p>
            <div style="margin-top: var(--spacing-3);">
                <a href="users/list.php" class="btn btn-sm btn-outline-primary">Manage Users</a>
            </div>
        </div>

        <!-- Departments -->
        <div class="improved-stats-card">
            <div class="improved-stats-header">
                <div class="improved-stats-icon info">
                    <i class="fas fa-building"></i>
                </div>
                <div class="improved-stats-content">
                    <h3 class="improved-stats-title">Departments</h3>
                    <p class="improved-stats-value"><?php echo number_format($stats['total_departments']); ?></p>
                </div>
            </div>
            <p class="stats-subtitle">
                <span style="color: var(--success); font-weight: var(--font-weight-semibold);">
                    <?php echo $stats['active_departments']; ?> active
                </span>
                <span style="color: var(--text-muted);"> departments</span>
            </p>
            <div style="margin-top: var(--spacing-3);">
                <a href="departments/list.php" class="btn btn-sm btn-outline-info">Manage Departments</a>
            </div>
        </div>

        <!-- Requisitions -->
        <div class="improved-stats-card">
            <div class="improved-stats-header">
                <div class="improved-stats-icon warning">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="improved-stats-content">
                    <h3 class="improved-stats-title">Total Requisitions</h3>
                    <p class="improved-stats-value"><?php echo number_format($stats['total_requisitions']); ?></p>
                </div>
            </div>
            <p class="stats-subtitle">
                <span style="color: var(--warning); font-weight: var(--font-weight-semibold);">
                    <?php echo $stats['pending_requisitions']; ?> pending
                </span>
                <span style="color: var(--text-muted);"> approval</span>
            </p>
            <div style="margin-top: var(--spacing-3);">
                <a href="../requisitions/list.php" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
        </div>

        <!-- Monthly Spending -->
        <div class="improved-stats-card">
            <div class="improved-stats-header">
                <div class="improved-stats-icon success">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="improved-stats-content">
                    <h3 class="improved-stats-title">This Month</h3>
                    <p class="improved-stats-value">₦<?php echo number_format($stats['monthly_spending'], 2); ?></p>
                </div>
            </div>
            <p class="stats-subtitle">
                <span style="color: var(--text-muted);">Year: </span>
                <span style="font-weight: var(--font-weight-semibold);">₦<?php echo number_format($stats['yearly_spending'], 2); ?>
                </span>
            </p>
            <div style="margin-top: var(--spacing-3);">
                <a href="../reports/generate.php" class="btn btn-sm btn-outline-success">View Reports</a>
            </div>
        </div>
    </div>

    <!-- Users by Role and Quick Actions -->
    <div class="charts-column-layout">
        <!-- Users by Role -->
        <div class="table-container">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="card-title">
                        <i class="fas fa-chart-pie me-2"></i>Users by Role
                    </h2>
                </div>
            </div>
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
                        <?php if (empty($usersByRole)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    <div style="color: var(--text-muted);">
                                        <i class="fas fa-users" style="font-size: 2rem; margin-bottom: var(--spacing-3); opacity: 0.5;"></i>
                                        <p style="margin: 0;">No users available</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usersByRole as $role): ?>
                                <?php
                                $percentage = $stats['active_users'] > 0 ? ($role['user_count'] / $stats['active_users']) * 100 : 0;
                                ?>
                                <tr>
                                    <td style="font-weight: var(--font-weight-medium); color: var(--text-primary);">
                                        <?php echo htmlspecialchars($role['role_name'] ?? 'Unknown'); ?>
                                    </td>
                                    <td class="text-end" style="font-weight: var(--font-weight-semibold);">
                                        <?php echo number_format($role['user_count'] ?? 0); ?>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge badge-primary"><?php echo number_format($percentage, 1); ?>%</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="table-container">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h2>
            </div>
            <div style="padding: var(--spacing-4); display: flex; flex-direction: column; gap: var(--spacing-3);">
                <a href="users/add.php" class="quick-action-card primary">
                    <div class="quick-action-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="quick-action-content">
                        <h3 class="quick-action-title">Add New User</h3>
                        <p class="quick-action-description">Create user accounts</p>
                    </div>
                    <div class="quick-action-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>

                <a href="departments/add.php" class="quick-action-card success">
                    <div class="quick-action-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="quick-action-content">
                        <h3 class="quick-action-title">Add Department</h3>
                        <p class="quick-action-description">Create new department</p>
                    </div>
                    <div class="quick-action-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>

                <a href="users/list.php" class="quick-action-card info">
                    <div class="quick-action-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div class="quick-action-content">
                        <h3 class="quick-action-title">Manage Users</h3>
                        <p class="quick-action-description">View all users</p>
                    </div>
                    <div class="quick-action-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>

                <a href="settings/general.php" class="quick-action-card" style="border-color: var(--border-color);">
                    <div class="quick-action-icon" style="background-color: var(--text-muted-light); color: var(--text-muted);">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="quick-action-content">
                        <h3 class="quick-action-title">System Settings</h3>
                        <p class="quick-action-description">Configure system</p>
                    </div>
                    <div class="quick-action-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Users and Recent Activity -->
    <div class="recent-tables-grid">
        <!-- Recent Users -->
        <div class="table-container">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="card-title">
                        <i class="fas fa-user-clock me-2"></i>Recent Users
                    </h2>
                    <a href="users/list.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
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
                        <?php if (empty($recentUsers)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    <div style="color: var(--text-muted);">
                                        <i class="fas fa-user-plus" style="font-size: 2rem; margin-bottom: var(--spacing-3); opacity: 0.5;"></i>
                                        <p style="margin: 0;">No recent users</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentUsers as $recentUser): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: var(--font-weight-medium); color: var(--text-primary);">
                                            <?php echo htmlspecialchars(($recentUser['first_name'] ?? '') . ' ' . ($recentUser['last_name'] ?? '')); ?>
                                        </div>
                                        <div style="font-size: var(--font-size-xs); color: var(--text-secondary);">
                                            <?php echo htmlspecialchars($recentUser['email'] ?? ''); ?>
                                        </div>
                                    </td>
                                    <td style="color: var(--text-secondary);">
                                        <?php echo htmlspecialchars($recentUser['role_name'] ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <?php if (isset($recentUser['is_active']) && $recentUser['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="table-container">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="card-title">
                        <i class="fas fa-history me-2"></i>Recent Activity
                    </h2>
                    <a href="audit-log.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentActivity)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    <div style="color: var(--text-muted);">
                                        <i class="fas fa-history" style="font-size: 2rem; margin-bottom: var(--spacing-3); opacity: 0.5;"></i>
                                        <p style="margin: 0;">No recent activity</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach (array_slice($recentActivity, 0, 5) as $activity): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($activity['first_name'])): ?>
                                            <div style="font-weight: var(--font-weight-medium); color: var(--text-primary);">
                                                <?php echo htmlspecialchars(($activity['first_name'] ?? '') . ' ' . ($activity['last_name'] ?? '')); ?>
                                            </div>
                                            <div style="font-size: var(--font-size-xs); color: var(--text-secondary);">
                                                <?php echo htmlspecialchars($activity['email'] ?? ''); ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">System</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?php echo htmlspecialchars($activity['action'] ?? 'N/A'); ?></span>
                                        <div style="font-size: var(--font-size-xs); color: var(--text-secondary); margin-top: var(--spacing-1);">
                                            <?php echo htmlspecialchars($activity['description'] ?? 'N/A'); ?>
                                        </div>
                                    </td>
                                    <td style="color: var(--text-muted); font-size: var(--font-size-sm);">
                                        <?php echo isset($activity['created_at']) ? date('M j, Y', strtotime($activity['created_at'])) : 'N/A'; ?>
                                        <div style="font-size: var(--font-size-xs);">
                                            <?php echo isset($activity['created_at']) ? date('H:i:s', strtotime($activity['created_at'])) : ''; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- System Information Section -->
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-info-circle me-2"></i>
            System Information
        </h2>
    </div>

    <div class="info-cards-grid">
        <div class="info-box-card">
            <p class="info-box-label">Last Updated</p>
            <p class="info-box-value">
                <?php echo date('M d, Y H:i'); ?>
            </p>
        </div>
        <div class="info-box-card">
            <p class="info-box-label">System Version</p>
            <p class="info-box-value">
                <?php echo APP_VERSION; ?>
            </p>
        </div>
        <div class="info-box-card">
            <p class="info-box-label">Environment</p>
            <p class="info-box-value">
                <?php echo ucfirst(APP_ENV); ?>
            </p>
        </div>
        <div class="info-box-card">
            <p class="info-box-label">Status</p>
            <p class="info-box-value">
                <span class="badge badge-success">Operational</span>
            </p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
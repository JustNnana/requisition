<?php
/**
 * GateWey Requisition Management System
 * Sidebar Navigation
 * 
 * File: includes/navbar.php
 * Purpose: Role-based sidebar navigation menu
 */

// Ensure this file is included from a valid entry point
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

// Get current user role
$userRoleId = $currentUser['role_id'] ?? 0;

// Helper function to check if current page is active
function isActive($page, $dir = null) {
    global $currentPage, $currentDir;
    if ($dir) {
        return ($currentDir === $dir) ? 'active' : '';
    }
    return ($currentPage === $page) ? 'active' : '';
}
?>

<!-- Sidebar -->
<aside class="sidebar">
    <nav class="sidebar-nav">
        <ul>
            <!-- Dashboard - All Roles -->
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="sidebar-link <?php echo isActive('index.php', 'dashboard'); ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <?php if ($userRoleId == ROLE_SUPER_ADMIN): ?>
                <!-- Super Admin Menu -->
                <li class="sidebar-item">
                    <div class="sidebar-heading">ADMINISTRATION</div>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/admin/users/list.php" class="sidebar-link <?php echo isActive('', 'users'); ?>">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/admin/departments/list.php" class="sidebar-link <?php echo isActive('', 'departments'); ?>">
                        <i class="fas fa-building"></i>
                        <span>Departments</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/admin/settings/general.php" class="sidebar-link <?php echo isActive('', 'settings'); ?>">
                        <i class="fas fa-cog"></i>
                        <span>System Settings</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/admin/audit-log.php" class="sidebar-link <?php echo isActive('audit-log.php'); ?>">
                        <i class="fas fa-history"></i>
                        <span>Audit Log</span>
                    </a>
                </li>
                
            <?php elseif ($userRoleId == ROLE_MANAGING_DIRECTOR): ?>
                <!-- Managing Director Menu -->
                <li class="sidebar-item">
                    <div class="sidebar-heading">REQUISITIONS</div>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="sidebar-link <?php echo isActive('create.php', 'requisitions'); ?>">
                        <i class="fas fa-plus-circle"></i>
                        <span>New Requisition</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="sidebar-link <?php echo isActive('list.php', 'requisitions'); ?>">
                        <i class="fas fa-file-alt"></i>
                        <span>My Requisitions</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/requisitions/pending.php" class="sidebar-link <?php echo isActive('pending.php', 'requisitions'); ?>">
                        <i class="fas fa-clock"></i>
                        <span>Pending Approvals</span>
                        <span class="badge badge-warning" style="margin-left: auto;">5</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <div class="sidebar-heading">REPORTS</div>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/reports/organization.php" class="sidebar-link <?php echo isActive('organization.php', 'reports'); ?>">
                        <i class="fas fa-chart-bar"></i>
                        <span>Organization Reports</span>
                    </a>
                </li>
                
            <?php elseif ($userRoleId == ROLE_FINANCE_MANAGER): ?>
                <!-- Finance Manager Menu -->
                <li class="sidebar-item">
                    <div class="sidebar-heading">FINANCE</div>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/finance/review-queue.php" class="sidebar-link <?php echo isActive('review-queue.php', 'finance'); ?>">
                        <i class="fas fa-search-dollar"></i>
                        <span>Review Queue</span>
                        <span class="badge badge-info" style="margin-left: auto;">3</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/finance/pending-payment.php" class="sidebar-link <?php echo isActive('pending-payment.php', 'finance'); ?>">
                        <i class="fas fa-hourglass-half"></i>
                        <span>Pending Payments</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/finance/payment-history.php" class="sidebar-link <?php echo isActive('payment-history.php', 'finance'); ?>">
                        <i class="fas fa-history"></i>
                        <span>Payment History</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/finance/pending-receipts.php" class="sidebar-link <?php echo isActive('pending-receipts.php', 'finance'); ?>">
                        <i class="fas fa-receipt"></i>
                        <span>Pending Receipts</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <div class="sidebar-heading">REPORTS</div>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/reports/organization.php" class="sidebar-link <?php echo isActive('organization.php', 'reports'); ?>">
                        <i class="fas fa-chart-bar"></i>
                        <span>Financial Reports</span>
                    </a>
                </li>
                
            <?php elseif ($userRoleId == ROLE_FINANCE_MEMBER): ?>
                <!-- Finance Member Menu -->
                <li class="sidebar-item">
                    <div class="sidebar-heading">PAYMENTS</div>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/finance/pending-payment.php" class="sidebar-link <?php echo isActive('pending-payment.php', 'finance'); ?>">
                        <i class="fas fa-money-check-alt"></i>
                        <span>Process Payments</span>
                        <span class="badge badge-success" style="margin-left: auto;">2</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/finance/payment-history.php" class="sidebar-link <?php echo isActive('payment-history.php', 'finance'); ?>">
                        <i class="fas fa-history"></i>
                        <span>Payment History</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/finance/pending-receipts.php" class="sidebar-link <?php echo isActive('pending-receipts.php', 'finance'); ?>">
                        <i class="fas fa-receipt"></i>
                        <span>Pending Receipts</span>
                    </a>
                </li>
                
            <?php elseif ($userRoleId == ROLE_LINE_MANAGER): ?>
                <!-- Line Manager Menu -->
                <li class="sidebar-item">
                    <div class="sidebar-heading">REQUISITIONS</div>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="sidebar-link <?php echo isActive('create.php', 'requisitions'); ?>">
                        <i class="fas fa-plus-circle"></i>
                        <span>New Requisition</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="sidebar-link <?php echo isActive('list.php', 'requisitions'); ?>">
                        <i class="fas fa-file-alt"></i>
                        <span>My Requisitions</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/requisitions/pending.php" class="sidebar-link <?php echo isActive('pending.php', 'requisitions'); ?>">
                        <i class="fas fa-clock"></i>
                        <span>Team Approvals</span>
                        <span class="badge badge-warning" style="margin-left: auto;">4</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <div class="sidebar-heading">REPORTS</div>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/reports/department.php" class="sidebar-link <?php echo isActive('department.php', 'reports'); ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Department Reports</span>
                    </a>
                </li>
                
            <?php elseif ($userRoleId == ROLE_TEAM_MEMBER): ?>
                <!-- Team Member Menu -->
                <li class="sidebar-item">
                    <div class="sidebar-heading">REQUISITIONS</div>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="sidebar-link <?php echo isActive('create.php', 'requisitions'); ?>">
                        <i class="fas fa-plus-circle"></i>
                        <span>New Requisition</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="sidebar-link <?php echo isActive('list.php', 'requisitions'); ?>">
                        <i class="fas fa-file-alt"></i>
                        <span>My Requisitions</span>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <div class="sidebar-heading">REPORTS</div>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/reports/personal.php" class="sidebar-link <?php echo isActive('personal.php', 'reports'); ?>">
                        <i class="fas fa-file-invoice"></i>
                        <span>My Reports</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- Common Menu Items for All Roles (except Super Admin) -->
            <?php if ($userRoleId != ROLE_SUPER_ADMIN): ?>
                <li class="sidebar-item">
                    <div class="sidebar-heading">ACCOUNT</div>
                </li>
                
                <li class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/profile/index.php" class="sidebar-link <?php echo isActive('index.php', 'profile'); ?>">
                        <i class="fas fa-user-circle"></i>
                        <span>My Profile</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="sidebar-item">
                <a href="<?php echo BASE_URL; ?>/help/index.php" class="sidebar-link <?php echo isActive('index.php', 'help'); ?>">
                    <i class="fas fa-question-circle"></i>
                    <span>Help & Support</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Sidebar Footer -->
    <div style="padding: var(--spacing-4); border-top: 1px solid var(--border-color); margin-top: auto;">
        <div style="font-size: var(--font-size-xs); color: var(--text-secondary); text-align: center;">
            <p style="margin-bottom: var(--spacing-2);"><?php echo APP_NAME; ?></p>
            <p>Version 1.0.0</p>
        </div>
    </div>
</aside>
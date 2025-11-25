<?php
/**
 * GateWey Requisition Management System
 * Sidebar Navigation with Dasher UI Design System
 * 
 * File: includes/navbar.php
 * Purpose: Role-based sidebar navigation menu with professional styling
 */

// Ensure this file is included from a valid entry point
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

// Get current user data from Session class (FIXED)
$userRoleId = Session::getUserRoleId() ?? 0;
$userFullName = Session::getUserFullName() ?? 'User';
$userEmail = Session::getUserEmail() ?? '';

// Generate initials from full name
$nameParts = explode(' ', $userFullName);
$userInitials = '';
if (count($nameParts) >= 2) {
    $userInitials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
} else if (count($nameParts) == 1) {
    $userInitials = strtoupper(substr($nameParts[0], 0, 2));
}

// Debug logging (remove in production)
if (APP_DEBUG) {
    error_log("Navbar Debug - Role ID: " . $userRoleId . " | Role Name: " . get_role_name($userRoleId));
}

// Helper function to check if current page is active
function isActive($page, $dir = null) {
    global $currentPage, $currentDir;
    if ($dir) {
        return ($currentDir === $dir) ? 'active' : '';
    }
    return ($currentPage === $page) ? 'active' : '';
}

// Get notification counts (placeholder - implement actual counts from database)
$pendingApprovalsCount = 0;
$pendingPaymentsCount = 0;
$pendingReceiptsCount = 0;

// You can add actual database queries here to get real counts
// Example:
// $pendingApprovalsCount = $requisition->getPendingApprovalsCount(Session::getUserId(), $userRoleId);
?>

<!-- Dasher UI Sidebar Styles -->
<style>
/* ===== DASHER UI SIDEBAR STYLES ===== */

.sidebar {
    width: var(--sidebar-width);
    background-color: var(--bg-sidebar);
    border-right: 1px solid var(--border-color);
    position: fixed;
    top: var(--navbar-height);
    left: 0;
    bottom: 0;
    overflow-y: auto;
    overflow-x: hidden;
    transition: var(--theme-transition);
    z-index: 1020;
    box-shadow: var(--shadow-xs);
}

.sidebar-content {
    padding: var(--spacing-6) 0;
    display: flex;
    flex-direction: column;
    gap: var(--spacing-1);
    max-width: 100%;
    overflow-x: hidden;
}

/* Sidebar Categories */
.sidebar-category {
    padding: var(--spacing-4) var(--spacing-6) var(--spacing-2);
    margin-top: var(--spacing-4);
}

.sidebar-category:first-child {
    margin-top: 0;
}

.sidebar-category-text {
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-semibold);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
}

/* Sidebar Links */
.sidebar-link {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding: var(--spacing-3) var(--spacing-6);
    color: var(--text-secondary);
    text-decoration: none;
    transition: var(--transition-fast);
    border-radius: 0;
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    position: relative;
    min-height: 44px;
    max-width: 100%;
    box-sizing: border-box;
}

.sidebar-link:hover {
    background-color: var(--bg-hover);
    color: var(--text-primary);
    text-decoration: none;
}

.sidebar-link.active {
    background-color: var(--primary-light);
    color: var(--primary);
    border-right: 3px solid var(--primary);
    font-weight: var(--font-weight-semibold);
}

.sidebar-link.active:hover {
    background-color: var(--primary-light);
    color: var(--primary);
}

/* Sidebar Icons */
.sidebar-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: var(--font-size-lg);
}

.sidebar-text {
    flex: 1;
    line-height: 1.4;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    min-width: 0;
}

/* Sidebar Badges */
.sidebar-badge {
    background-color: var(--gray-500);
    color: white;
    border-radius: var(--border-radius-full);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-bold);
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 var(--spacing-2);
    line-height: 1;
    flex-shrink: 0;
    margin-left: auto;
}

.sidebar-badge-primary {
    background-color: var(--primary);
}

.sidebar-badge-success {
    background-color: var(--success);
}

.sidebar-badge-warning {
    background-color: var(--warning);
    color: var(--text-primary);
}

.sidebar-badge-danger {
    background-color: var(--danger);
}

.sidebar-badge-info {
    background-color: var(--info);
}

/* Badge Animations */
.sidebar-badge-pulse {
    animation: dasher-pulse 2s infinite;
}

@keyframes dasher-pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.8;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Sidebar Footer */
.sidebar-footer {
    padding: var(--spacing-4);
    border-top: 1px solid var(--border-color);
    margin-top: auto;
}

.sidebar-footer-content {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
    text-align: center;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform var(--transition-normal);
        z-index: 1050;
        box-shadow: none;
    }

    .sidebar.active {
        transform: translateX(0);
        box-shadow: var(--shadow-lg);
    }

    .sidebar-content {
        padding: var(--spacing-4) 0;
    }

    .sidebar-link {
        padding: var(--spacing-3) var(--spacing-4);
        min-height: 48px;
    }

    .sidebar-category {
        padding: var(--spacing-3) var(--spacing-4) var(--spacing-2);
    }

    .sidebar-badge {
        min-width: 18px;
        height: 18px;
        font-size: 0.6rem;
    }
}

@media (max-width: 576px) {
    .sidebar-link {
        padding: var(--spacing-2) var(--spacing-3);
        font-size: var(--font-size-xs);
    }

    .sidebar-icon {
        width: 18px;
        height: 18px;
        font-size: var(--font-size-base);
    }

    .sidebar-category-text {
        font-size: 0.65rem;
    }
}

/* Scrollbar Styling */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: var(--bg-secondary);
}

.sidebar::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: var(--border-radius-xl);
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: var(--text-muted);
}

/* Dark Mode Adjustments */
[data-theme="dark"] .sidebar {
    box-shadow: var(--shadow-sm);
}

/* Focus States for Accessibility */
.sidebar-link:focus {
    outline: 2px solid var(--primary);
    outline-offset: -2px;
}

.sidebar-link:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: -2px;
}

/* High Contrast Mode Support */
@media (prefers-contrast: high) {
    .sidebar-link {
        border-bottom: 1px solid var(--border-color);
    }

    .sidebar-badge {
        border: 1px solid;
    }
}

/* Reduced Motion Support */
@media (prefers-reduced-motion: reduce) {
    .sidebar,
    .sidebar-link,
    .sidebar-badge-pulse {
        animation: none;
        transition: none;
    }
}

/* Print Styles */
@media print {
    .sidebar {
        display: none;
    }
}

/* Enhanced Visual Feedback */
.sidebar-link:active {
    transform: translateX(2px);
}

/* Hover effects for badges */
.sidebar-link:hover .sidebar-badge {
    transform: scale(1.05);
    transition: transform var(--transition-fast);
}
</style>

<!-- Dasher UI Sidebar -->
<aside class="sidebar" id="mainSidebar">
    <div class="sidebar-content">
        <!-- Dashboard Menu Item (common for all roles) -->
        <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="sidebar-link <?php echo isActive('index.php', 'dashboard'); ?>">
            <div class="sidebar-icon">
                <i class="fas fa-tachometer-alt"></i>
            </div>
            <span class="sidebar-text">Dashboard</span>
        </a>

        <?php if ($userRoleId == ROLE_SUPER_ADMIN): ?>
            <!-- Super Admin Menu -->
            <div class="sidebar-category">
                <span class="sidebar-category-text">ADMINISTRATION</span>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/admin/users/list.php" class="sidebar-link <?php echo isActive('', 'users'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-users"></i>
                </div>
                <span class="sidebar-text">User Management</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/admin/departments/list.php" class="sidebar-link <?php echo isActive('', 'departments'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-building"></i>
                </div>
                <span class="sidebar-text">Departments</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/admin/settings/general.php" class="sidebar-link <?php echo isActive('', 'settings'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <span class="sidebar-text">System Settings</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/admin/audit-log.php" class="sidebar-link <?php echo isActive('audit-log.php'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-history"></i>
                </div>
                <span class="sidebar-text">Audit Log</span>
            </a>
            
        <?php elseif ($userRoleId == ROLE_MANAGING_DIRECTOR): ?>
            <!-- Managing Director Menu -->
            <div class="sidebar-category">
                <span class="sidebar-category-text">REQUISITIONS</span>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="sidebar-link <?php echo isActive('create.php', 'requisitions'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <span class="sidebar-text">New Requisition</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="sidebar-link <?php echo isActive('list.php', 'requisitions'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <span class="sidebar-text">My Requisitions</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/requisitions/pending.php" class="sidebar-link <?php echo isActive('pending.php', 'requisitions'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <span class="sidebar-text">Pending Approvals</span>
                <?php if ($pendingApprovalsCount > 0): ?>
                    <div class="sidebar-badge sidebar-badge-warning sidebar-badge-pulse"><?php echo $pendingApprovalsCount; ?></div>
                <?php endif; ?>
            </a>
            
            <div class="sidebar-category">
                <span class="sidebar-category-text">REPORTS</span>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/reports/organization.php" class="sidebar-link <?php echo isActive('organization.php', 'reports'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <span class="sidebar-text">Organization Reports</span>
            </a>
            
        <?php elseif ($userRoleId == ROLE_FINANCE_MANAGER): ?>
            <!-- Finance Manager Menu -->
            <div class="sidebar-category">
                <span class="sidebar-category-text">FINANCE</span>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/finance/review-queue.php" class="sidebar-link <?php echo isActive('review-queue.php', 'finance'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-search-dollar"></i>
                </div>
                <span class="sidebar-text">Review Queue</span>
                <?php if ($pendingApprovalsCount > 0): ?>
                    <div class="sidebar-badge sidebar-badge-info"><?php echo $pendingApprovalsCount; ?></div>
                <?php endif; ?>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/finance/pending-payment.php" class="sidebar-link <?php echo isActive('pending-payment.php', 'finance'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <span class="sidebar-text">Pending Payments</span>
                <?php if ($pendingPaymentsCount > 0): ?>
                    <div class="sidebar-badge sidebar-badge-warning"><?php echo $pendingPaymentsCount; ?></div>
                <?php endif; ?>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/finance/payment-history.php" class="sidebar-link <?php echo isActive('payment-history.php', 'finance'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-history"></i>
                </div>
                <span class="sidebar-text">Payment History</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/finance/pending-receipts.php" class="sidebar-link <?php echo isActive('pending-receipts.php', 'finance'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <span class="sidebar-text">Pending Receipts</span>
                <?php if ($pendingReceiptsCount > 0): ?>
                    <div class="sidebar-badge sidebar-badge-danger"><?php echo $pendingReceiptsCount; ?></div>
                <?php endif; ?>
            </a>
            
            <div class="sidebar-category">
                <span class="sidebar-category-text">REPORTS</span>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/reports/organization.php" class="sidebar-link <?php echo isActive('organization.php', 'reports'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <span class="sidebar-text">Financial Reports</span>
            </a>
            
        <?php elseif ($userRoleId == ROLE_FINANCE_MEMBER): ?>
            <!-- Finance Member Menu -->
            <div class="sidebar-category">
                <span class="sidebar-category-text">PAYMENTS</span>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/finance/pending-payment.php" class="sidebar-link <?php echo isActive('pending-payment.php', 'finance'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-money-check-alt"></i>
                </div>
                <span class="sidebar-text">Process Payments</span>
                <?php if ($pendingPaymentsCount > 0): ?>
                    <div class="sidebar-badge sidebar-badge-success sidebar-badge-pulse"><?php echo $pendingPaymentsCount; ?></div>
                <?php endif; ?>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/finance/payment-history.php" class="sidebar-link <?php echo isActive('payment-history.php', 'finance'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-history"></i>
                </div>
                <span class="sidebar-text">Payment History</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/finance/pending-receipts.php" class="sidebar-link <?php echo isActive('pending-receipts.php', 'finance'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <span class="sidebar-text">Pending Receipts</span>
                <?php if ($pendingReceiptsCount > 0): ?>
                    <div class="sidebar-badge sidebar-badge-warning"><?php echo $pendingReceiptsCount; ?></div>
                <?php endif; ?>
            </a>
            
        <?php elseif ($userRoleId == ROLE_LINE_MANAGER): ?>
            <!-- Line Manager Menu -->
            <div class="sidebar-category">
                <span class="sidebar-category-text">REQUISITIONS</span>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="sidebar-link <?php echo isActive('create.php', 'requisitions'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <span class="sidebar-text">New Requisition</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="sidebar-link <?php echo isActive('list.php', 'requisitions'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <span class="sidebar-text">My Requisitions</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/requisitions/pending.php" class="sidebar-link <?php echo isActive('pending.php', 'requisitions'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <span class="sidebar-text">Team Approvals</span>
                <?php if ($pendingApprovalsCount > 0): ?>
                    <div class="sidebar-badge sidebar-badge-warning sidebar-badge-pulse"><?php echo $pendingApprovalsCount; ?></div>
                <?php endif; ?>
            </a>
            
            <div class="sidebar-category">
                <span class="sidebar-category-text">REPORTS</span>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/reports/department.php" class="sidebar-link <?php echo isActive('department.php', 'reports'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <span class="sidebar-text">Department Reports</span>
            </a>
            
        <?php elseif ($userRoleId == ROLE_TEAM_MEMBER): ?>
            <!-- Team Member Menu -->
            <div class="sidebar-category">
                <span class="sidebar-category-text">REQUISITIONS</span>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="sidebar-link <?php echo isActive('create.php', 'requisitions'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <span class="sidebar-text">New Requisition</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="sidebar-link <?php echo isActive('list.php', 'requisitions'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <span class="sidebar-text">My Requisitions</span>
            </a>
            
            <div class="sidebar-category">
                <span class="sidebar-category-text">REPORTS</span>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/reports/personal.php" class="sidebar-link <?php echo isActive('personal.php', 'reports'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <span class="sidebar-text">My Reports</span>
            </a>
        <?php endif; ?>
        
        <!-- Common Menu Items for All Roles (except Super Admin) -->
        <?php if ($userRoleId != ROLE_SUPER_ADMIN): ?>
            <div class="sidebar-category">
                <span class="sidebar-category-text">ACCOUNT</span>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/profile/index.php" class="sidebar-link <?php echo isActive('index.php', 'profile'); ?>">
                <div class="sidebar-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <span class="sidebar-text">My Profile</span>
            </a>
        <?php endif; ?>
        
        <a href="<?php echo BASE_URL; ?>/help/index.php" class="sidebar-link <?php echo isActive('index.php', 'help'); ?>">
            <div class="sidebar-icon">
                <i class="fas fa-question-circle"></i>
            </div>
            <span class="sidebar-text">Help & Support</span>
        </a>
    </div>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="sidebar-footer-content">
            <p style="margin-bottom: var(--spacing-2); font-weight: var(--font-weight-semibold);"><?php echo APP_NAME; ?></p>
            <p style="margin-bottom: 0;">Version 1.0.0</p>
        </div>
    </div>
</aside>

<!-- Enhanced JavaScript for Dasher UI Sidebar -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎨 Initializing Dasher UI sidebar for Requisition System...');

    // ===== MOBILE SIDEBAR FUNCTIONALITY =====
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('mainSidebar');
    const content = document.querySelector('.content');

    if (sidebarToggle && sidebar) {
        let isMobileOpen = false;

        function toggleMobileSidebar() {
            if (window.innerWidth <= 768) {
                isMobileOpen = !isMobileOpen;

                if (isMobileOpen) {
                    sidebar.classList.add('active');
                    if (content) content.classList.add('sidebar-active');
                    document.body.style.overflow = 'hidden';
                    sidebarToggle.setAttribute('aria-expanded', 'true');
                } else {
                    sidebar.classList.remove('active');
                    if (content) content.classList.remove('sidebar-active');
                    document.body.style.overflow = '';
                    sidebarToggle.setAttribute('aria-expanded', 'false');
                }

                console.log('📱 Sidebar:', isMobileOpen ? 'OPEN' : 'CLOSED');
            }
        }

        // Toggle on button click
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleMobileSidebar();
        });

        // Close on outside click (mobile only)
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && isMobileOpen) {
                const clickedInSidebar = sidebar.contains(e.target);
                const clickedToggle = sidebarToggle.contains(e.target);

                if (!clickedInSidebar && !clickedToggle) {
                    isMobileOpen = false;
                    sidebar.classList.remove('active');
                    if (content) content.classList.remove('sidebar-active');
                    document.body.style.overflow = '';
                    sidebarToggle.setAttribute('aria-expanded', 'false');
                }
            }
        });

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isMobileOpen && window.innerWidth <= 768) {
                isMobileOpen = false;
                sidebar.classList.remove('active');
                if (content) content.classList.remove('sidebar-active');
                document.body.style.overflow = '';
                sidebarToggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Reset on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                isMobileOpen = false;
                sidebar.classList.remove('active');
                if (content) content.classList.remove('sidebar-active');
                document.body.style.overflow = '';
                sidebarToggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Close when clicking menu items on mobile
        sidebar.addEventListener('click', function(e) {
            const link = e.target.closest('a.sidebar-link');
            if (link && window.innerWidth <= 768 && isMobileOpen) {
                setTimeout(() => {
                    isMobileOpen = false;
                    sidebar.classList.remove('active');
                    if (content) content.classList.remove('sidebar-active');
                    document.body.style.overflow = '';
                    sidebarToggle.setAttribute('aria-expanded', 'false');
                }, 100);
            }
        });
    }

    // ===== BADGE UPDATE FUNCTIONALITY =====
    function animateBadgeUpdate(badge, newValue) {
        if (badge) {
            badge.style.transform = 'scale(1.2)';
            badge.style.transition = 'transform 0.3s ease';

            setTimeout(() => {
                badge.textContent = newValue;
                badge.style.transform = 'scale(1)';
            }, 150);
        }
    }

    // Update requisition counts
    function updateRequisitionCounts() {
        fetch('<?php echo BASE_URL; ?>/api/get-requisition-counts.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update pending approvals badge
                    const approvalsBadge = document.querySelector('.sidebar-link[href*="pending.php"] .sidebar-badge');
                    if (approvalsBadge && data.pendingApprovals > 0) {
                        animateBadgeUpdate(approvalsBadge, data.pendingApprovals);
                        approvalsBadge.style.display = 'flex';
                    } else if (approvalsBadge && data.pendingApprovals === 0) {
                        approvalsBadge.style.display = 'none';
                    }

                    // Update pending payments badge
                    const paymentsBadge = document.querySelector('.sidebar-link[href*="pending-payment.php"] .sidebar-badge');
                    if (paymentsBadge && data.pendingPayments > 0) {
                        animateBadgeUpdate(paymentsBadge, data.pendingPayments);
                        paymentsBadge.style.display = 'flex';
                    } else if (paymentsBadge && data.pendingPayments === 0) {
                        paymentsBadge.style.display = 'none';
                    }

                    // Update pending receipts badge
                    const receiptsBadge = document.querySelector('.sidebar-link[href*="pending-receipts.php"] .sidebar-badge');
                    if (receiptsBadge && data.pendingReceipts > 0) {
                        animateBadgeUpdate(receiptsBadge, data.pendingReceipts);
                        receiptsBadge.style.display = 'flex';
                    } else if (receiptsBadge && data.pendingReceipts === 0) {
                        receiptsBadge.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.log('Requisition count update failed:', error);
            });
    }

    // Update counts every 30 seconds
    setInterval(updateRequisitionCounts, 30000);
    
    // Initial load after 2 seconds
    setTimeout(updateRequisitionCounts, 2000);

    // ===== KEYBOARD SHORTCUTS =====
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.altKey) {
            switch (e.key) {
                case 'n':
                    e.preventDefault();
                    window.location.href = '<?php echo BASE_URL; ?>/requisitions/create.php';
                    break;
                case 'r':
                    e.preventDefault();
                    window.location.href = '<?php echo BASE_URL; ?>/requisitions/list.php';
                    break;
                case 'p':
                    e.preventDefault();
                    window.location.href = '<?php echo BASE_URL; ?>/requisitions/pending.php';
                    break;
                case 'd':
                    e.preventDefault();
                    window.location.href = '<?php echo BASE_URL; ?>/dashboard/index.php';
                    break;
            }
        }
    });

    // ===== SMOOTH SCROLLING =====
    document.querySelectorAll('.sidebar-link[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    console.log('✅ Dasher UI sidebar initialized successfully');
});

// Export functions for external use
window.updateSidebarCounts = function() {
    if (typeof updateRequisitionCounts === 'function') updateRequisitionCounts();
};
</script>
<?php
/**
 * GateWey Requisition Management System
 * Line Manager - Team Members View
 *
 * File: dashboard/team-members.php
 * Purpose: View team members for line managers
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';
require_once __DIR__ . '/../helpers/permissions.php';

// Check if user is Line Manager or Managing Director
if (!is_line_manager() && !is_managing_director()) {
    Session::setFlash('error', 'Access denied. This page is only accessible to Line Managers and Managing Directors.');
    redirect(BASE_URL . '/dashboard/index.php');
    exit;
}

// Get user info
$userId = Session::getUserId();
$userRole = Session::getUserRoleId();
$departmentId = Session::getUserDepartmentId();

// Get department info
$department = new Department();
$deptInfo = $department->getById($departmentId);

if (!$deptInfo) {
    Session::setFlash('error', 'Department not found.');
    redirect(BASE_URL . '/dashboard/index.php');
    exit;
}

// Initialize User class
$userModel = new User();

// Get all team members in the department
$db = Database::getInstance();
$sql = "SELECT u.*, r.role_name, d.department_name, d.department_code
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        LEFT JOIN departments d ON u.department_id = d.id
        WHERE u.department_id = ?
        AND u.role_id = ?
        ORDER BY u.first_name ASC, u.last_name ASC";
$teamMembers = $db->fetchAll($sql, [$departmentId, ROLE_TEAM_MEMBER]);

// Get statistics
$stats = [
    'total_members' => count($teamMembers),
    'active_members' => count(array_filter($teamMembers, fn($m) => $m['is_active'] == 1)),
    'inactive_members' => count(array_filter($teamMembers, fn($m) => $m['is_active'] == 0))
];

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Team Members - ' . htmlspecialchars($deptInfo['department_name']);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
    .team-member-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-4);
        transition: var(--theme-transition);
        display: flex;
        align-items: center;
        gap: var(--spacing-4);
    }

    .team-member-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
        transform: translateY(-2px);
    }

    .member-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--primary-light);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-bold);
        color: var(--primary);
        flex-shrink: 0;
    }

    .member-info {
        flex: 1;
    }

    .member-name {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-1) 0;
    }

    .member-email {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0 0 var(--spacing-2) 0;
    }

    .member-meta {
        display: flex;
        gap: var(--spacing-3);
        flex-wrap: wrap;
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
    }

    .member-actions {
        display: flex;
        gap: var(--spacing-2);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-4);
        margin-bottom: var(--spacing-6);
    }

    .stat-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-4);
        text-align: center;
    }

    .stat-value {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-1) 0;
    }

    .stat-label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0;
    }

    .team-grid {
        display: grid;
        gap: var(--spacing-4);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-1);
        padding: var(--spacing-1) var(--spacing-2);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
    }

    .status-badge.active {
        background: rgba(var(--success-rgb), 0.1);
        color: var(--success);
    }

    .status-badge.inactive {
        background: rgba(var(--danger-rgb), 0.1);
        color: var(--danger);
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">
                <i class="fas fa-users me-2"></i>
                Team Members
            </h1>
            <p class="content-subtitle">
                <?php echo htmlspecialchars($deptInfo['department_name']); ?> (<?php echo htmlspecialchars($deptInfo['department_code']); ?>)
            </p>
        </div>
        <div class="content-actions">
            <a href="<?php echo BASE_URL; ?>/dashboard/line-manager.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
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

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-value"><?php echo $stats['total_members']; ?></p>
        <p class="stat-label">Total Members</p>
    </div>
    <div class="stat-card">
        <p class="stat-value" style="color: var(--success);"><?php echo $stats['active_members']; ?></p>
        <p class="stat-label">Active</p>
    </div>
    <div class="stat-card">
        <p class="stat-value" style="color: var(--danger);"><?php echo $stats['inactive_members']; ?></p>
        <p class="stat-label">Inactive</p>
    </div>
</div>

<!-- Team Members List -->
<?php if (empty($teamMembers)): ?>
    <div style="text-align: center; padding: var(--spacing-8); color: var(--text-muted);">
        <i class="fas fa-users" style="font-size: 4rem; margin-bottom: var(--spacing-3); opacity: 0.5;"></i>
        <h3>No Team Members Yet</h3>
        <p>There are no team members assigned to your department.</p>
    </div>
<?php else: ?>
    <div class="team-grid">
        <?php foreach ($teamMembers as $member): ?>
            <div class="team-member-card">
                <div class="member-avatar">
                    <?php
                    $initials = strtoupper(
                        substr($member['first_name'], 0, 1) .
                        substr($member['last_name'], 0, 1)
                    );
                    echo htmlspecialchars($initials);
                    ?>
                </div>
                <div class="member-info">
                    <h3 class="member-name">
                        <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                    </h3>
                    <p class="member-email">
                        <i class="fas fa-envelope me-1"></i>
                        <?php echo htmlspecialchars($member['email']); ?>
                    </p>
                    <div class="member-meta">
                        <span>
                            <i class="fas fa-id-badge me-1"></i>
                            <?php echo htmlspecialchars($member['role_name']); ?>
                        </span>
                        <?php if (!empty($member['phone'])): ?>
                        <span>
                            <i class="fas fa-phone me-1"></i>
                            <?php echo htmlspecialchars($member['phone']); ?>
                        </span>
                        <?php endif; ?>
                        <span>
                            <i class="fas fa-calendar me-1"></i>
                            Joined <?php echo date('M Y', strtotime($member['created_at'])); ?>
                        </span>
                    </div>
                </div>
                <div class="member-actions">
                    <?php if ($member['is_active']): ?>
                        <span class="status-badge active">
                            <i class="fas fa-check-circle"></i>
                            Active
                        </span>
                    <?php else: ?>
                        <span class="status-badge inactive">
                            <i class="fas fa-times-circle"></i>
                            Inactive
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * GateWey Requisition Management System
 * Role Management - Toggle Role Status (Activate/Deactivate)
 *
 * File: admin/roles/toggle-status.php
 * Purpose: Activate or deactivate a role (only if no users are assigned)
 */

// Define access level
define('APP_ACCESS', true);
define('ADMIN_ACCESS', true);

// Include necessary files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../helpers/permissions.php';

// Start session
Session::start();

// Check authentication and authorization
require_once __DIR__ . '/../../middleware/auth-check.php';
require_once __DIR__ . '/../../middleware/role-check.php';
checkRole(ROLE_SUPER_ADMIN);

// Initialize database
$db = Database::getInstance();

// Get role ID and action from URL
$roleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($roleId <= 0) {
    Session::setFlash('error', 'Invalid role ID.');
    redirect('list.php');
}

if (!in_array($action, ['activate', 'deactivate'])) {
    Session::setFlash('error', 'Invalid action.');
    redirect('list.php');
}

// Get role data
$role = $db->fetchOne("SELECT * FROM roles WHERE id = ?", [$roleId]);

if (!$role) {
    Session::setFlash('error', 'Role not found.');
    redirect('list.php');
}

// Check if role has users assigned (only when deactivating)
if ($action === 'deactivate') {
    $userCount = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role_id = ? AND is_active = 1", [$roleId]);

    if ($userCount['count'] > 0) {
        Session::setFlash('error', 'Cannot deactivate role "' . $role['role_name'] . '" because it is assigned to ' . $userCount['count'] . ' active user(s). Please reassign these users first.');
        redirect('list.php');
    }
}

// Handle status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        Session::setFlash('error', 'Invalid security token. Please try again.');
        redirect('list.php');
    }

    // Toggle role status
    try {
        $newStatus = ($action === 'activate') ? 1 : 0;
        $result = $db->execute("UPDATE roles SET is_active = ? WHERE id = ?", [$newStatus, $roleId]);

        if ($result) {
            $actionText = ($action === 'activate') ? 'activated' : 'deactivated';
            Session::setFlash('success', 'Role "' . $role['role_name'] . '" has been ' . $actionText . ' successfully.');
        } else {
            Session::setFlash('error', 'Failed to update role status. Please try again.');
        }
    } catch (Exception $e) {
        error_log("Toggle role status error: " . $e->getMessage());
        Session::setFlash('error', 'An error occurred while updating the role status.');
    }

    redirect('list.php');
}

// Page title
$pageTitle = ucfirst($action) . ' Role';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<style>
/* Breadcrumb Styling */
.content-breadcrumb {
    margin-top: var(--spacing-2);
}

.breadcrumb {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: var(--spacing-1);
    align-items: center;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.breadcrumb-item a {
    color: var(--primary);
    text-decoration: none;
    transition: var(--theme-transition);
}

.breadcrumb-item a:hover {
    color: var(--primary-dark);
}

.breadcrumb-item.active {
    color: var(--text-primary);
    font-weight: 500;
}

.breadcrumb-separator {
    margin: 0 var(--spacing-2);
    color: var(--text-muted);
}

/* Confirmation Container */
.confirmation-container {
    max-width: 600px;
    margin: 0 auto;
}

.confirmation-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    padding: var(--spacing-6);
}

.warning-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    padding: var(--spacing-5);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing-6);
}

.warning-header.deactivate {
    background: rgba(239, 68, 68, 0.1);
    border: 2px solid rgba(239, 68, 68, 0.3);
}

.warning-header.activate {
    background: rgba(34, 197, 94, 0.1);
    border: 2px solid rgba(34, 197, 94, 0.3);
}

.warning-icon {
    font-size: 3rem;
}

.warning-icon.deactivate {
    color: var(--danger);
}

.warning-icon.activate {
    color: var(--success);
}

.warning-content h2 {
    margin: 0 0 var(--spacing-2) 0;
    font-size: var(--font-size-xl);
    font-weight: 600;
}

.warning-content h2.deactivate {
    color: var(--danger);
}

.warning-content h2.activate {
    color: var(--success);
}

.warning-content p {
    margin: 0;
    color: var(--text-secondary);
}

.role-details {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: var(--spacing-4);
    margin-bottom: var(--spacing-6);
}

.role-detail-item {
    display: flex;
    justify-content: space-between;
    padding: var(--spacing-2) 0;
    border-bottom: 1px solid var(--border-color);
}

.role-detail-item:last-child {
    border-bottom: none;
}

.role-detail-label {
    font-weight: 500;
    color: var(--text-secondary);
}

.role-detail-value {
    color: var(--text-primary);
    font-weight: 600;
}

.role-code-value {
    font-family: 'Courier New', monospace;
    background: var(--bg-primary);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--border-radius);
    font-size: var(--font-size-xs);
    color: var(--primary);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-1);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--border-radius);
    font-size: var(--font-size-xs);
    font-weight: 500;
}

.status-badge.active {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

.status-badge.inactive {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.confirmation-text {
    padding: var(--spacing-4);
    background: var(--bg-secondary);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing-6);
    color: var(--text-primary);
}

.confirmation-text.deactivate {
    border-left: 4px solid var(--danger);
}

.confirmation-text.activate {
    border-left: 4px solid var(--success);
}

.confirmation-text strong {
    color: inherit;
}

.form-actions {
    display: flex;
    gap: var(--spacing-3);
    padding-top: var(--spacing-4);
}

.btn-danger {
    background: var(--danger);
    color: white;
    border: 1px solid var(--danger);
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-success {
    background: var(--success);
    color: white;
    border: 1px solid var(--success);
}

.btn-success:hover {
    background: #16a34a;
}

.btn-cancel {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.btn-cancel:hover {
    background: var(--bg-hover);
}

/* Responsive */
@media (max-width: 768px) {
    .warning-header {
        flex-direction: column;
        text-align: center;
    }

    .confirmation-card {
        padding: var(--spacing-4);
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .form-actions .btn {
        width: 100%;
    }

    .role-detail-item {
        flex-direction: column;
        gap: var(--spacing-1);
    }
}
</style>

<div class="page-header">
    <h1><i class="fas fa-user-shield"></i> <?php echo ucfirst($action); ?> Role</h1>
    <div class="content-breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="list.php">Role Management</a></li>
                <li class="breadcrumb-item active"><?php echo ucfirst($action); ?> Role</li>
            </ol>
        </nav>
    </div>
</div>

<div class="confirmation-container">
    <div class="confirmation-card">
        <div class="warning-header <?php echo $action; ?>">
            <div class="warning-icon <?php echo $action; ?>">
                <?php if ($action === 'deactivate'): ?>
                    <i class="fas fa-ban"></i>
                <?php else: ?>
                    <i class="fas fa-check-circle"></i>
                <?php endif; ?>
            </div>
            <div class="warning-content">
                <h2 class="<?php echo $action; ?>">Confirm <?php echo ucfirst($action); ?></h2>
                <p>You are about to <?php echo $action; ?> this role.</p>
            </div>
        </div>

        <div class="role-details">
            <div class="role-detail-item">
                <span class="role-detail-label">Role Name:</span>
                <span class="role-detail-value"><?php echo htmlspecialchars($role['role_name']); ?></span>
            </div>
            <div class="role-detail-item">
                <span class="role-detail-label">Role Code:</span>
                <span class="role-code-value"><?php echo htmlspecialchars($role['role_code']); ?></span>
            </div>
            <div class="role-detail-item">
                <span class="role-detail-label">Current Status:</span>
                <?php if ($role['is_active']): ?>
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
            <div class="role-detail-item">
                <span class="role-detail-label">Description:</span>
                <span class="role-detail-value"><?php echo htmlspecialchars($role['description'] ?: 'N/A'); ?></span>
            </div>
        </div>

        <div class="confirmation-text <?php echo $action; ?>">
            <i class="fas fa-info-circle"></i>
            <?php if ($action === 'deactivate'): ?>
                <strong>Warning:</strong> Deactivating this role will prevent it from being assigned to new users.
                This action can be reversed by reactivating the role later.
            <?php else: ?>
                <strong>Note:</strong> Activating this role will make it available for assignment to users.
            <?php endif; ?>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo Session::generateCsrfToken(); ?>">

            <div class="form-actions">
                <a href="list.php" class="btn btn-cancel">
                    <i class="fas fa-arrow-left"></i>
                    <span>Cancel</span>
                </a>
                <?php if ($action === 'deactivate'): ?>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban"></i>
                        <span>Yes, Deactivate Role</span>
                    </button>
                <?php else: ?>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i>
                        <span>Yes, Activate Role</span>
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

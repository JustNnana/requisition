<?php
/**
 * GateWey Requisition Management System
 * Role Management - Delete Role
 *
 * File: admin/roles/delete.php
 * Purpose: Delete a role (only if no users are assigned)
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

// Get role ID from URL
$roleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($roleId <= 0) {
    Session::setFlash('error', 'Invalid role ID.');
    redirect('list.php');
}

// Get role data
$role = $db->fetchOne("SELECT * FROM roles WHERE id = ?", [$roleId]);

if (!$role) {
    Session::setFlash('error', 'Role not found.');
    redirect('list.php');
}

// Check if role has users assigned
$userCount = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role_id = ?", [$roleId]);

if ($userCount['count'] > 0) {
    Session::setFlash('error', 'Cannot delete role "' . $role['role_name'] . '" because it is assigned to ' . $userCount['count'] . ' user(s). Please reassign or remove these users first.');
    redirect('list.php');
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        Session::setFlash('error', 'Invalid security token. Please try again.');
        redirect('list.php');
    }

    // Delete the role
    try {
        $result = $db->execute("DELETE FROM roles WHERE id = ?", [$roleId]);

        if ($result) {
            Session::setFlash('success', 'Role "' . $role['role_name'] . '" has been deleted successfully.');
        } else {
            Session::setFlash('error', 'Failed to delete role. Please try again.');
        }
    } catch (Exception $e) {
        error_log("Delete role error: " . $e->getMessage());
        Session::setFlash('error', 'An error occurred while deleting the role.');
    }

    redirect('list.php');
}

// Page title
$pageTitle = 'Delete Role';
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
    background: rgba(239, 68, 68, 0.1);
    border: 2px solid rgba(239, 68, 68, 0.3);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing-6);
}

.warning-icon {
    font-size: 3rem;
    color: var(--danger);
}

.warning-content h2 {
    margin: 0 0 var(--spacing-2) 0;
    font-size: var(--font-size-xl);
    font-weight: 600;
    color: var(--danger);
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

.confirmation-text {
    padding: var(--spacing-4);
    background: var(--bg-secondary);
    border-left: 4px solid var(--danger);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing-6);
    color: var(--text-primary);
}

.confirmation-text strong {
    color: var(--danger);
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
    <h1><i class="fas fa-user-shield"></i> Delete Role</h1>
    <div class="content-breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="list.php">Role Management</a></li>
                <li class="breadcrumb-item active">Delete Role</li>
            </ol>
        </nav>
    </div>
</div>

<div class="confirmation-container">
    <div class="confirmation-card">
        <div class="warning-header">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="warning-content">
                <h2>Confirm Deletion</h2>
                <p>You are about to permanently delete this role.</p>
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
                <span class="role-detail-label">Description:</span>
                <span class="role-detail-value"><?php echo htmlspecialchars($role['description'] ?: 'N/A'); ?></span>
            </div>
        </div>

        <div class="confirmation-text">
            <i class="fas fa-info-circle"></i>
            <strong>Warning:</strong> This action cannot be undone. Once deleted, this role cannot be recovered.
            Make sure no users are assigned to this role before proceeding.
        </div>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo Session::generateCsrfToken(); ?>">

            <div class="form-actions">
                <a href="list.php" class="btn btn-cancel">
                    <i class="fas fa-arrow-left"></i>
                    <span>Cancel</span>
                </a>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    <span>Yes, Delete Role</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

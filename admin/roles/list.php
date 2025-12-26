<?php

/**
 * GateWey Requisition Management System
 * Role Management - List Roles
 *
 * File: admin/roles/list.php
 * Purpose: Display all roles with add, edit, and delete functionality
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

// Get all roles (both active and inactive)
$roles = $db->fetchAll("SELECT r.*,
                        COUNT(u.id) as user_count
                        FROM roles r
                        LEFT JOIN users u ON r.id = u.role_id AND u.is_active = 1
                        GROUP BY r.id
                        ORDER BY r.is_active DESC, r.id ASC");

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Role Management';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<style>
/* Role Management Styles */
.role-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-6);
}

.role-header h1 {
    margin: 0;
    font-size: var(--font-size-2xl);
    font-weight: 600;
    color: var(--text-primary);
}

.role-table-wrapper {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
}

.role-table {
    width: 100%;
    border-collapse: collapse;
}

.role-table thead {
    background: var(--bg-secondary);
    border-bottom: 2px solid var(--border-color);
}

.role-table th {
    padding: var(--spacing-4);
    text-align: left;
    font-size: var(--font-size-sm);
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.role-table tbody tr {
    border-bottom: 1px solid var(--border-color);
    transition: background-color 0.15s ease;
}

.role-table tbody tr:hover {
    background: var(--bg-hover);
}

.role-table td {
    padding: var(--spacing-4);
    font-size: var(--font-size-sm);
    color: var(--text-primary);
}

.role-code {
    font-family: 'Courier New', monospace;
    background: var(--bg-secondary);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--border-radius);
    font-size: var(--font-size-xs);
    color: var(--primary);
}

.permission-badges {
    display: flex;
    gap: var(--spacing-2);
    flex-wrap: wrap;
}

.permission-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-1);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--border-radius);
    font-size: var(--font-size-xs);
    font-weight: 500;
}

.permission-badge.active {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

.permission-badge.inactive {
    background: var(--bg-secondary);
    color: var(--text-muted);
}

.permission-badge i {
    font-size: var(--font-size-xs);
}

.user-count-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-1);
    padding: var(--spacing-1) var(--spacing-3);
    background: var(--bg-secondary);
    border-radius: var(--border-radius-full);
    font-size: var(--font-size-sm);
    font-weight: 500;
    color: var(--text-primary);
}

.action-buttons {
    display: flex;
    gap: var(--spacing-2);
}

.action-btn {
    padding: var(--spacing-2) var(--spacing-3);
    border-radius: var(--border-radius);
    font-size: var(--font-size-sm);
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-2);
    transition: all 0.15s ease;
    border: 1px solid transparent;
}

.action-btn.edit {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border-color: rgba(59, 130, 246, 0.2);
}

.action-btn.edit:hover {
    background: rgba(59, 130, 246, 0.2);
}

.action-btn.delete {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.2);
}

.action-btn.delete:hover {
    background: rgba(239, 68, 68, 0.2);
}

.action-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.empty-state {
    text-align: center;
    padding: var(--spacing-12);
    color: var(--text-muted);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: var(--spacing-4);
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
    .role-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-4);
    }

    .role-table-wrapper {
        overflow-x: auto;
    }

    .role-table {
        min-width: 800px;
    }

    .action-buttons {
        flex-direction: column;
    }
}
</style>

<div class="page-header">
    <div class="role-header">
        <h1><i class="fas fa-user-shield"></i> Role Management</h1>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            <span>Add New Role</span>
        </a>
    </div>
</div>

<?php if ($successMessage): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span><?php echo htmlspecialchars($successMessage); ?></span>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo htmlspecialchars($errorMessage); ?></span>
    </div>
<?php endif; ?>

<div class="role-table-wrapper">
    <?php if (empty($roles)): ?>
        <div class="empty-state">
            <i class="fas fa-user-shield"></i>
            <p>No roles found in the system.</p>
        </div>
    <?php else: ?>
        <table class="role-table">
            <thead>
                <tr>
                    <th>Role Name</th>
                    <th>Role Code</th>
                    <th>Status</th>
                    <th>Permissions</th>
                    <th>Users</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr style="<?php echo !$role['is_active'] ? 'opacity: 0.6;' : ''; ?>">
                        <td>
                            <strong><?php echo htmlspecialchars($role['role_name']); ?></strong>
                        </td>
                        <td>
                            <span class="role-code"><?php echo htmlspecialchars($role['role_code']); ?></span>
                        </td>
                        <td>
                            <?php if ($role['is_active']): ?>
                                <span class="permission-badge active">
                                    <i class="fas fa-check-circle"></i>
                                    Active
                                </span>
                            <?php else: ?>
                                <span class="permission-badge inactive">
                                    <i class="fas fa-times-circle"></i>
                                    Inactive
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="permission-badges">
                                <span class="permission-badge <?php echo $role['can_raise_requisition'] ? 'active' : 'inactive'; ?>"
                                      title="<?php echo $role['can_raise_requisition'] ? 'Can raise requisitions' : 'Cannot raise requisitions'; ?>">
                                    <i class="fas fa-<?php echo $role['can_raise_requisition'] ? 'check' : 'times'; ?>"></i>
                                    Raise
                                </span>
                                <span class="permission-badge <?php echo $role['can_approve'] ? 'active' : 'inactive'; ?>"
                                      title="<?php echo $role['can_approve'] ? 'Can approve requisitions' : 'Cannot approve requisitions'; ?>">
                                    <i class="fas fa-<?php echo $role['can_approve'] ? 'check' : 'times'; ?>"></i>
                                    Approve
                                </span>
                                <span class="permission-badge <?php echo $role['can_view_all'] ? 'active' : 'inactive'; ?>"
                                      title="<?php echo $role['can_view_all'] ? 'Can view all requisitions' : 'Can view own requisitions only'; ?>">
                                    <i class="fas fa-<?php echo $role['can_view_all'] ? 'check' : 'times'; ?>"></i>
                                    View All
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="user-count-badge">
                                <i class="fas fa-users"></i>
                                <?php echo $role['user_count']; ?>
                            </span>
                        </td>
                        <td>
                            <div style="max-width: 300px; font-size: var(--font-size-xs); color: var(--text-secondary);">
                                <?php echo htmlspecialchars(substr($role['description'], 0, 100)) . (strlen($role['description']) > 100 ? '...' : ''); ?>
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit.php?id=<?php echo $role['id']; ?>" class="action-btn edit">
                                    <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <?php if ($role['user_count'] == 0): ?>
                                    <?php if ($role['is_active']): ?>
                                        <a href="toggle-status.php?id=<?php echo $role['id']; ?>&action=deactivate"
                                           class="action-btn delete"
                                           onclick="return confirm('Are you sure you want to deactivate the role \'<?php echo htmlspecialchars($role['role_name']); ?>\'? Users will not be able to use this role.');">
                                            <i class="fas fa-ban"></i>
                                            Deactivate
                                        </a>
                                    <?php else: ?>
                                        <a href="toggle-status.php?id=<?php echo $role['id']; ?>&action=activate"
                                           class="action-btn edit"
                                           onclick="return confirm('Are you sure you want to activate the role \'<?php echo htmlspecialchars($role['role_name']); ?>\'?');">
                                            <i class="fas fa-check-circle"></i>
                                            Activate
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="action-btn delete" disabled title="Cannot deactivate role with assigned users. Please reassign users first.">
                                        <i class="fas fa-ban"></i>
                                        Deactivate
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

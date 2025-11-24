<?php
/**
 * GateWey Requisition Management System
 * Department Management - List Departments
 * 
 * File: admin/departments/list.php
 * Purpose: Display all departments with statistics
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

// Initialize objects
$department = new Department();

// Get all departments
$departments = $department->getAll();

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Department Management';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Department Management</h1>
            <p class="content-subtitle">Manage organizational departments</p>
        </div>
        <div>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Department
            </a>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($successMessage): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($successMessage); ?>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($errorMessage); ?>
    </div>
<?php endif; ?>

<!-- Departments Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            All Departments (<?php echo count($departments); ?>)
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Department Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th class="text-center">Users</th>
                        <th class="text-center">Requisitions</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($departments)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-building fa-3x mb-3" style="opacity: 0.3;"></i>
                                <p>No departments found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($departments as $dept): ?>
                            <tr>
                                <td><?php echo $dept['id']; ?></td>
                                <td>
                                    <div style="font-weight: var(--font-weight-medium);">
                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo htmlspecialchars($dept['department_code']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo $dept['description'] ? htmlspecialchars($dept['description']) : '<span class="text-muted">No description</span>'; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary"><?php echo $dept['user_count']; ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-warning"><?php echo $dept['requisition_count']; ?></span>
                                </td>
                                <td>
                                    <?php if ($dept['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="table-actions">
                                        <a href="edit.php?id=<?php echo $dept['id']; ?>" 
                                           class="btn btn-sm btn-ghost" 
                                           title="Edit Department">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $dept['id']; ?>" 
                                           class="btn btn-sm btn-ghost text-danger" 
                                           title="Delete Department"
                                           data-confirm-delete="Are you sure you want to delete this department? This will also delete all associated users and requisitions.">
                                            <i class="fas fa-trash"></i>
                                        </a>
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>
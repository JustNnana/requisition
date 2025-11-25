<?php

/**
 * GateWey Requisition Management System
 * User Management - List Users
 * 
 * File: admin/users/list.php
 * Purpose: Display all users with search, filter, and pagination
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
$user = new User();
$department = new Department();

// Handle search and filters
$searchTerm = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$departmentFilter = $_GET['department'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Build query
$sql = "SELECT u.*, r.role_name, d.department_name
        FROM users u
        JOIN roles r ON u.role_id = r.id
        LEFT JOIN departments d ON u.department_id = d.id
        WHERE u.is_active = 1";

$params = [];

// Apply filters
if (!empty($searchTerm)) {
    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $searchParam = '%' . $searchTerm . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($roleFilter)) {
    $sql .= " AND u.role_id = ?";
    $params[] = $roleFilter;
}

if (!empty($departmentFilter)) {
    $sql .= " AND u.department_id = ?";
    $params[] = $departmentFilter;
}

if ($statusFilter !== '') {
    $sql .= " AND u.is_active = ?";
    $params[] = $statusFilter;
}

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as count_table";
$db = Database::getInstance();
$totalResult = $db->fetchOne($countSql, $params);
$totalUsers = $totalResult['total'];
$totalPages = ceil($totalUsers / $perPage);

// Get users with pagination
$sql .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

$users = $db->fetchAll($sql, $params);

// Get all roles for filter dropdown
$roles = $db->fetchAll("SELECT * FROM roles ORDER BY id");

// Get all departments for filter dropdown
$departments = $department->getAll(true);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'User Management';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">User Management</h1>
            <p class="content-subtitle">Manage system users and their roles</p>
        </div>
        <div>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New User
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

<!-- Filters and Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text"
                    name="search"
                    class="form-control"
                    placeholder="Name or email..."
                    value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label">Role</label>
                <select name="role" class="form-control">
                    <option value="">All Roles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>"
                            <?php echo ($roleFilter == $role['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Department</label>
                <select name="department" class="form-control">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>"
                            <?php echo ($departmentFilter == $dept['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['department_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="1" <?php echo ($statusFilter === '1') ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo ($statusFilter === '0') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="list.php" class="btn btn-ghost">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            All Users (<?php echo number_format($totalUsers); ?>)
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-users fa-3x mb-3" style="opacity: 0.3;"></i>
                                <p>No users found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $userData): ?>
                            <tr>
                                <td><?php echo $userData['id']; ?></td>
                                <td>
                                    <div style="font-weight: var(--font-weight-medium);">
                                        <?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($userData['email']); ?>">
                                        <?php echo htmlspecialchars($userData['email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo htmlspecialchars($userData['role_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $userData['department_name'] ? htmlspecialchars($userData['department_name']) : '<span class="text-muted">N/A</span>'; ?>
                                </td>
                                <td>
                                    <?php if ($userData['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?php echo date('M d, Y', strtotime($userData['created_at'])); ?></div>
                                    <div style="font-size: var(--font-size-xs); color: var(--text-secondary);">
                                        <?php echo date('H:i', strtotime($userData['created_at'])); ?>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="table-actions">
                                        <a href="edit.php?id=<?php echo $userData['id']; ?>"
                                            class="btn btn-sm btn-ghost"
                                            title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php
                                        $currentUser = Session::get('user');
                                        if ($userData['id'] != ($currentUser['id'] ?? 0)):
                                        ?>
                                            <a href="delete.php?id=<?php echo $userData['id']; ?>"
    class="btn btn-sm btn-ghost text-danger"
    title="Delete User"
    onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
    <i class="fas fa-trash"></i>
</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalUsers); ?> of <?php echo number_format($totalUsers); ?> users
                </div>
                <nav>
                    <ul class="pagination mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page - 1) . '&search=' . urlencode($searchTerm) . '&role=' . urlencode($roleFilter) . '&department=' . urlencode($departmentFilter) . '&status=' . urlencode($statusFilter); ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i . '&search=' . urlencode($searchTerm) . '&role=' . urlencode($roleFilter) . '&department=' . urlencode($departmentFilter) . '&status=' . urlencode($statusFilter); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page + 1) . '&search=' . urlencode($searchTerm) . '&role=' . urlencode($roleFilter) . '&department=' . urlencode($departmentFilter) . '&status=' . urlencode($statusFilter); ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
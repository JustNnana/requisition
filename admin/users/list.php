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
        WHERE 1=1";

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
<style>
/* ============================================
   User List Filter Section Styles
   ============================================ */

/* Filter Card */
.card.mb-4 {
    margin-bottom: 1.5rem;
}

/* Form Row with Side-by-Side Layout */
.row.g-3 {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin: 0;
    align-items: flex-end;
}

.row.g-3 > [class*="col-"] {
    padding: 0;
    flex-shrink: 0;
}

/* Form Labels */
.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    line-height: 1.5;
}

/* Form Controls (Input & Select) */
.form-control,
.form-select {
    display: block;
    width: 100%;
    padding: 0.625rem 0.875rem;
    font-size: 0.875rem;
    font-weight: 400;
    line-height: 1.5;
    background-clip: padding-box;
    border-radius: 0.375rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

/* Input Field Specific */
.form-control {
    appearance: none;
}

/* Select Dropdown Specific */
.form-select {
    appearance: none;
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px 12px;
    padding-right: 2.5rem;
    cursor: pointer;
    background-color:var(--bg-primary);
    color:var(--text-secondary);
}

.form-select:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

/* Select Options Styling */
.form-select option {
    padding: 0.5rem;
    font-size: 0.875rem;
}

/* Disabled State */
.form-control:disabled,
.form-select:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Filter Buttons Container */
.d-flex.align-items-end {
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
}

/* Button Spacing */
.btn.me-2 {
    margin-right: 0.5rem;
}

/* Filter Buttons */
.btn-primary,
.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    line-height: 1.5;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    white-space: nowrap;
}

.btn-primary:hover,
.btn-secondary:hover {
    transform: translateY(-1px);
}

.btn-primary:active,
.btn-secondary:active {
    transform: translateY(0);
}

.btn-primary i,
.btn-secondary i {
    margin-right: 0.5rem;
}

/* Column Sizing for Side-by-Side Layout */
.col-md-3 {
    flex: 0 0 auto;
    width: calc(25% - 0.75rem);
}

.col-md-2 {
    flex: 0 0 auto;
    width: calc(16.666667% - 0.75rem);
}

/* Ensure columns fill remaining space properly */
.row.g-3 > .col-md-3:first-child {
    width: calc(25% - 0.75rem);
}

/* Pagination Styles */
.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 0.25rem;
}

.page-item {
    display: inline-block;
}

.page-link {
    display: block;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    text-decoration: none;
    border-radius: 0.375rem;
    transition: all 0.15s ease-in-out;
}

.page-link:hover {
    transform: translateY(-1px);
}

.page-item.active .page-link {
    font-weight: 600;
}

/* Table Actions */
.table-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

/* Responsive Design */
@media (max-width: 992px) {
    .col-md-3,
    .col-md-2 {
        width: calc(50% - 0.5rem);
    }
}

@media (max-width: 768px) {
    .col-md-3,
    .col-md-2 {
        width: 100%;
    }
    
    .row.g-3 {
        gap: 1rem;
    }
    
    .d-flex.align-items-end {
        flex-direction: row;
        width: 100%;
    }
    
    .btn-primary,
    .btn-secondary {
        flex: 1;
        margin-right: 0 !important;
    }
    
    .btn-primary {
        margin-right: 0.5rem !important;
    }
}

/* Focus Visible for Accessibility */
.form-control:focus-visible,
.form-select:focus-visible {
    outline-offset: 2px;
}

/* Empty State Styling */
.text-center.text-muted.py-5 {
    padding: 3rem 0;
}

.text-center.text-muted.py-5 i {
    display: block;
    margin-bottom: 1rem;
}

.text-center.text-muted.py-5 p {
    margin: 0.5rem 0;
}

/* Number Formatting */
.d-flex.justify-content-between {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.mt-4 {
    margin-top: 1.5rem;
}

.mb-0 {
    margin-bottom: 0;
}    
    
</style>
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
                <label for="search" class="form-label">Search Users</label>
                <input type="text"
                    id="search"
                    name="search"
                    class="form-control"
                    placeholder="Search by name or email..."
                    value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>

            <div class="col-md-2">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-select">
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
                <label for="department" class="form-label">Department</label>
                <select id="department" name="department" class="form-select">
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
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="1" <?php echo ($statusFilter === '1') ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo ($statusFilter === '0') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="list.php" class="btn btn-secondary">
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
                        <th>#</th>
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
                                <?php if (!empty($searchTerm) || !empty($roleFilter) || !empty($departmentFilter) || $statusFilter !== ''): ?>
                                    <a href="list.php" class="btn btn-secondary btn-sm mt-2">
                                        Clear Filters
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $rowNumber = $offset + 1;
                        foreach ($users as $userData): 
                        ?>
                            <tr>
                                <td><?php echo $rowNumber++; ?></td>
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
                                    <?php if (!empty($userData['department_name'])): ?>
                                        <?php echo htmlspecialchars($userData['department_name']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
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
                                        <a href="<?php echo build_encrypted_url('edit.php', $userData['id']); ?>"
                                            class="btn btn-sm btn-ghost"
                                            title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php
                                        $currentUser = Session::get('user');
                                        if ($userData['id'] != ($currentUser['id'] ?? 0)):
                                        ?>
                                            <a href="<?php echo build_encrypted_url('delete.php', $userData['id']); ?>"
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
                    Showing <?php echo number_format($offset + 1); ?> to <?php echo number_format(min($offset + $perPage, $totalUsers)); ?> of <?php echo number_format($totalUsers); ?> users
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
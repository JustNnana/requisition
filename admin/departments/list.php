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

// Get filter parameters
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Get all departments
$allDepartments = $department->getAll();

// Apply filters
$departments = $allDepartments;

// Search filter
if (!empty($searchTerm)) {
    $departments = array_filter($departments, function($dept) use ($searchTerm) {
        $search = strtolower($searchTerm);
        return (
            stripos($dept['department_name'], $searchTerm) !== false ||
            stripos($dept['department_code'], $searchTerm) !== false ||
            stripos($dept['description'], $searchTerm) !== false
        );
    });
}

// Status filter
if ($statusFilter !== 'all') {
    $departments = array_filter($departments, function($dept) use ($statusFilter) {
        if ($statusFilter === 'active') {
            return $dept['is_active'] == 1;
        } elseif ($statusFilter === 'inactive') {
            return $dept['is_active'] == 0;
        }
        return true;
    });
}

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Department Management';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>
<style> 
/* ============================================
   Filter Section Styles (No Color Overrides)
   ============================================ */

/* Filter Card */
.card.mb-4 {
    margin-bottom: 1.5rem;
}

/* Form Row with Gap */
.row.g-3 {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin: 0;
}

.row.g-3 > [class*="col-"] {
    padding: 0;
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

/* Column Sizing */
.col-md-6 {
    flex: 0 0 auto;
    width: 50%;
}

.col-md-3 {
    flex: 0 0 auto;
    width: 25%;
}

/* Responsive Design */
@media (max-width: 768px) {
    .col-md-6,
    .col-md-3 {
        width: 100%;
    }
    
    .d-flex.align-items-end {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn-primary,
    .btn-secondary {
        width: 100%;
        margin-right: 0 !important;
        margin-bottom: 0.5rem;
    }
    
    .btn-secondary {
        margin-bottom: 0;
    }
}

/* Focus Visible for Accessibility */
.form-control:focus-visible,
.form-select:focus-visible {
    outline-offset: 2px;
}
</style>
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

<!-- Filters Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Search Departments</label>
                <input type="text" 
                       class="form-control" 
                       id="search" 
                       name="search" 
                       placeholder="Search by name, code, or description..."
                       value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active Only</option>
                    <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive Only</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Departments Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            All Departments (<?php echo count($departments); ?> of <?php echo count($allDepartments); ?>)
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
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
                                <?php if (!empty($searchTerm) || $statusFilter !== 'all'): ?>
                                    <a href="list.php" class="btn btn-secondary btn-sm mt-2">
                                        Clear Filters
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $rowNumber = 1;
                        foreach ($departments as $dept): 
                        ?>
                            <tr>
                                <td><?php echo $rowNumber++; ?></td>
                                <td>
                                    <?php if (!empty($dept['department_name'])): ?>
                                        <strong><?php echo htmlspecialchars($dept['department_name']); ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
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
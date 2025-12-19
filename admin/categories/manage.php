<?php
/**
 * GateWey Requisition Management System
 * Category Management Page - Custom Modals (No Bootstrap)
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/../../config/config.php';

Session::start();
require_once __DIR__ . '/../../middleware/auth-check.php';

if (Session::getUserRoleId() != ROLE_SUPER_ADMIN) {
    Session::setFlash('error', 'Access denied. Super Admin only.');
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

$categoryModel = new RequisitionCategory();
$auditLog = new AuditLog();
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errorMessage = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add_category':
                $data = [
                    'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                    'category_name' => Sanitizer::string($_POST['category_name'] ?? ''),
                    'category_code' => Sanitizer::string($_POST['category_code'] ?? ''),
                    'description' => Sanitizer::string($_POST['description'] ?? ''),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'display_order' => (int)($_POST['display_order'] ?? 0)
                ];
                
                if (empty($data['category_name'])) {
                    $errorMessage = 'Category name is required.';
                } elseif ($categoryModel->nameExists($data['category_name'])) {
                    $errorMessage = 'Category name already exists.';
                } elseif (!empty($data['category_code']) && $categoryModel->codeExists($data['category_code'])) {
                    $errorMessage = 'Category code already exists.';
                } else {
                    $newId = $categoryModel->create($data);
                    if ($newId) {
                        $type = $data['parent_id'] ? 'Child' : 'Parent';
                        Session::setFlash('success', "{$type} category created successfully!");
                        $auditLog->log(Session::getUserId(), 'category_created', "Created {$type} category: {$data['category_name']}", null, ['category_id' => $newId]);
                    } else {
                        $errorMessage = 'Failed to create category.';
                    }
                }
                break;
                
            case 'edit_category':
                $categoryId = (int)($_POST['category_id'] ?? 0);
                if (!$categoryId) {
                    $errorMessage = 'Invalid category ID.';
                } else {
                    $data = [
                        'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                        'category_name' => Sanitizer::string($_POST['category_name'] ?? ''),
                        'category_code' => Sanitizer::string($_POST['category_code'] ?? ''),
                        'description' => Sanitizer::string($_POST['description'] ?? ''),
                        'is_active' => isset($_POST['is_active']) ? 1 : 0,
                        'display_order' => (int)($_POST['display_order'] ?? 0)
                    ];
                    
                    if (empty($data['category_name'])) {
                        $errorMessage = 'Category name is required.';
                    } elseif ($categoryModel->nameExists($data['category_name'], $categoryId)) {
                        $errorMessage = 'Category name already exists.';
                    } elseif (!empty($data['category_code']) && $categoryModel->codeExists($data['category_code'], $categoryId)) {
                        $errorMessage = 'Category code already exists.';
                    } elseif ($data['parent_id'] && !$categoryModel->isValidParentChildRelationship($categoryId, $data['parent_id'])) {
                        $errorMessage = 'Invalid parent-child relationship.';
                    } else {
                        if ($categoryModel->update($categoryId, $data)) {
                            Session::setFlash('success', 'Category updated successfully!');
                            $auditLog->log(Session::getUserId(), 'category_updated', "Updated category: {$data['category_name']}", null, ['category_id' => $categoryId]);
                        } else {
                            $errorMessage = 'Failed to update category.';
                        }
                    }
                }
                break;
                
            case 'toggle_active':
                $categoryId = (int)($_POST['category_id'] ?? 0);
                if ($categoryId && $categoryModel->toggleActive($categoryId)) {
                    Session::setFlash('success', 'Category status updated!');
                    $auditLog->log(Session::getUserId(), 'category_status_changed', "Toggled status for category ID: {$categoryId}", null, ['category_id' => $categoryId]);
                } else {
                    $errorMessage = 'Failed to update status.';
                }
                break;
                
            case 'delete_category':
                $categoryId = (int)($_POST['category_id'] ?? 0);
                $category = $categoryModel->getById($categoryId);
                
                if (!$category) {
                    $errorMessage = 'Category not found.';
                } elseif ($categoryModel->hasChildren($categoryId)) {
                    $errorMessage = 'Cannot delete category with children.';
                } elseif ($categoryModel->delete($categoryId)) {
                    Session::setFlash('success', 'Category deleted successfully!');
                    $auditLog->log(Session::getUserId(), 'category_deleted', "Deleted category: {$category['category_name']}", null, ['category_id' => $categoryId]);
                } else {
                    $errorMessage = 'Failed to delete category.';
                }
                break;
        }
    }
    
    if ($errorMessage) {
        Session::setFlash('error', $errorMessage);
    }
    header('Location: manage.php');
    exit;
}

$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');
$hierarchy = $categoryModel->getCategoryHierarchy(false);
$stats = $categoryModel->getStatistics();
$parentCategories = $categoryModel->getParentCategories(false);
$pageTitle = 'Category Management';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<style>
    /* Custom Modal Styles */
    .custom-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        overflow-y: auto;
    }

    .custom-modal.active {
        display: block;
    }

    .custom-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1;
    }

    .custom-modal-content {
        position: relative;
        background: var(--bg-primary, #fff);
        margin: 50px auto;
        max-width: 600px;
        border-radius: var(--border-radius-lg, 8px);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        z-index: 2;
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .custom-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px;
        border-bottom: 1px solid var(--border-color, #ddd);
        /*background: var(--bg-subtle, #f9f9f9);*/
    }

    .custom-modal-header h5 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary, #333);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .custom-modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-secondary, #666);
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .custom-modal-close:hover {
        background: var(--bg-hover, #f5f5f5);
        color: var(--text-primary, #333);
    }

    .custom-modal-body {
        padding: 20px;
        max-height: calc(100vh - 250px);
        overflow-y: auto;
    }

    .custom-modal-footer {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        padding: 15px 20px;
        border-top: 1px solid var(--border-color, #ddd);
        /*background: var(--bg-subtle, #f9f9f9);*/
    }

    /* Section Cards */
    .section-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        margin-bottom: var(--spacing-5);
        overflow: hidden;
        transition: var(--theme-transition);
    }

    .section-card:hover {
        box-shadow: var(--shadow-sm);
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--spacing-5);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
    }

    .section-header-content {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .section-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-lg);
        flex-shrink: 0;
    }

    .section-icon.primary {
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
    }

    .section-icon.success {
        background: rgba(var(--success-rgb), 0.1);
        color: var(--success);
    }

    .section-icon.warning {
        background: rgba(var(--warning-rgb), 0.1);
        color: var(--warning);
    }

    .section-icon.info {
        background: rgba(var(--info-rgb), 0.1);
        color: var(--info);
    }

    .section-title h5 {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
    }

    .section-title p {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        margin: 0;
    }

    .section-body {
        padding: var(--spacing-5);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-4);
        margin-bottom: var(--spacing-5);
    }

    .stat-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-4);
        transition: var(--theme-transition);
    }

    .stat-card:hover {
        box-shadow: var(--shadow-sm);
        transform: translateY(-2px);
    }

    .stat-card-content {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-icon.primary {
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
    }

    .stat-icon.success {
        background: rgba(var(--success-rgb), 0.1);
        color: var(--success);
    }

    .stat-icon.info {
        background: rgba(var(--info-rgb), 0.1);
        color: var(--info);
    }

    .stat-icon.warning {
        background: rgba(var(--warning-rgb), 0.1);
        color: var(--warning);
    }

    .stat-info h3 {
        font-size: 1.75rem;
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin: 0;
    }

    .stat-info p {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0;
    }

    /* Category Table */
    .category-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .category-table thead th {
        background: var(--bg-subtle);
        padding: var(--spacing-3);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border-color);
    }

    .category-table tbody tr {
        transition: background-color 0.2s;
    }

    .category-table tbody tr:hover {
        background: var(--bg-hover);
    }

    .category-table tbody td {
        padding: var(--spacing-4);
        border-bottom: 1px solid var(--border-light);
    }

    .parent-row {
        background: var(--bg-subtle);
        font-weight: var(--font-weight-medium);
    }

    .child-row {
        display: none;
    }

    .child-row.show {
        display: table-row;
    }

    .child-row td:first-child {
        padding-left: 3rem;
    }

    .inactive-row {
        opacity: 0.6;
    }

    .collapse-toggle {
        cursor: pointer;
        padding: var(--spacing-2);
        background: transparent;
        border: none;
        color: var(--text-secondary);
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-1);
    }

    .collapse-toggle:hover {
        color: var(--primary);
    }

    .collapse-toggle i {
        transition: transform 0.2s;
    }

    .collapse-toggle.expanded i {
        transform: rotate(90deg);
    }

    .category-name-cell {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .category-name-cell i {
        flex-shrink: 0;
    }

    .category-name-cell .category-details {
        flex: 1;
    }

    .category-name-cell .category-title {
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-1);
    }

    .category-name-cell .category-desc {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .empty-state {
        text-align: center;
        padding: var(--spacing-8) var(--spacing-4);
        color: var(--text-secondary);
    }

    .empty-state i {
        font-size: 3rem;
        color: var(--border-color);
        margin-bottom: var(--spacing-3);
    }

    .empty-state h3 {
        font-size: var(--font-size-lg);
        color: var(--text-primary);
        margin-bottom: var(--spacing-2);
    }

    .empty-state p {
        margin-bottom: var(--spacing-4);
    }
    
    /* Enhanced Dropdown Styling */
.form-select,
select.form-control {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-color: var(--bg-primary, #fff);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 12px;
    border: 1px solid var(--border-color, #ddd);
    border-radius: var(--border-radius, 6px);
    padding: 10px 35px 10px 12px;
    font-size: var(--font-size-sm, 14px);
    color: var(--text-primary, #333);
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 42px;
}

.form-select:hover,
select.form-control:hover {
    border-color: var(--bg-hover);
    /*background-color: var(--bg-hover, #f8f9fa);*/
}

.form-select:focus,
select.form-control:focus {
    outline: none;
    border-color: var(--bg-hover);
    /*box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 0, 123, 255), 0.1);*/
}

.form-select:disabled,
select.form-control:disabled {
    background-color: var(--bg-subtle, #f5f5f5);
    cursor: not-allowed;
    opacity: 0.6;
}

/* Style for option elements */
.form-select option,
select.form-control option {
    padding: 10px;
    background-color: var(--bg-primary, #fff);
    color: var(--text-primary, #333);
}

.form-select option:hover,
select.form-control option:hover {
    background-color: var(--bg-hover, #f0f0f0);
}

/* First option (placeholder style) */
.form-select option:first-child,
select.form-control option:first-child {
    color: var(--text-secondary, #666);
    font-style: italic;
}

/* Label for select */
.form-label {
    display: block;
    margin-bottom: 6px;
    font-size: var(--font-size-sm, 14px);
    font-weight: var(--font-weight-medium, 500);
    color: var(--text-primary, #333);
}

/* Form group wrapper */
.mb-3 {
    margin-bottom: 1rem;
}
</style>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">
                <i class="fas fa-folder-tree me-2"></i>
                Category Management
            </h1>
            <p class="content-description">Manage requisition categories with parent-child hierarchy</p>
        </div>
        <div class="content-actions">
            <button type="button" class="btn btn-primary" onclick="openModal('addCategoryModal', 'parent')">
                <i class="fas fa-plus me-2"></i> Add Parent Category
            </button>
        </div>
    </div>
</div>

<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo htmlspecialchars($successMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?php echo htmlspecialchars($errorMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-content">
            <div class="stat-icon primary">
                <i class="fas fa-folder"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['parent_categories']; ?></h3>
                <p>Parent Categories</p>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-content">
            <div class="stat-icon success">
                <i class="fas fa-folder-open"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['child_categories']; ?></h3>
                <p>Child Categories</p>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-content">
            <div class="stat-icon info">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['active_categories']; ?></h3>
                <p>Active Categories</p>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-content">
            <div class="stat-icon warning">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['total_categories']; ?></h3>
                <p>Total Categories</p>
            </div>
        </div>
    </div>
</div>

<div class="section-card">
    <div class="section-header">
        <div class="section-header-content">
            <div class="section-icon primary">
                <i class="fas fa-sitemap"></i>
            </div>
            <div class="section-title">
                <h5>Category Hierarchy</h5>
                <p>Parent and child categories organized by structure</p>
            </div>
        </div>
    </div>
    <div class="section-body">
        <?php if (empty($hierarchy)): ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>No Categories Yet</h3>
                <p>Create your first parent category to get started</p>
                <button type="button" class="btn btn-primary" onclick="openModal('addCategoryModal', 'parent')">
                    <i class="fas fa-plus me-2"></i> Add Parent Category
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="category-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Category Name</th>
                            <th style="width: 15%;">Code</th>
                            <th style="width: 15%;" class="text-center">Children</th>
                            <th style="width: 10%;" class="text-center">Status</th>
                            <th style="width: 10%;" class="text-center">Order</th>
                            <th style="width: 10%;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hierarchy as $parent): ?>
                            <tr class="parent-row <?php echo $parent['is_active'] ? '' : 'inactive-row'; ?> <?php echo !empty($parent['children']) ? 'has-children' : ''; ?>" data-parent-id="<?php echo $parent['id']; ?>">
                                <td data-label="Category Name">
                                    <div class="category-name-cell">
                                        <?php if (!empty($parent['children'])): ?>
                                            <button type="button" class="collapse-toggle" onclick="toggleChildren(<?php echo $parent['id']; ?>)">
                                                <i class="fas fa-chevron-right"></i>
                                            </button>
                                        <?php else: ?>
                                            <span style="width: 24px; display: inline-block;"></span>
                                        <?php endif; ?>
                                        <i class="fas fa-folder" style="font-size: 1.25rem;"></i>
                                        <div class="category-details">
                                            <div class="category-title"><?php echo htmlspecialchars($parent['category_name']); ?></div>
                                            <?php if ($parent['description']): ?>
                                                <div class="category-desc"><?php echo htmlspecialchars($parent['description']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Code">
                                    <code class="text-muted"><?php echo htmlspecialchars($parent['category_code'] ?? '-'); ?></code>
                                </td>
                                <td data-label="Children" class="text-center">
                                    <span class="badge"><?php echo $parent['child_count']; ?></span>
                                    <button type="button" class="btn btn-sm btn-link" onclick="openModal('addCategoryModal', 'child', <?php echo $parent['id']; ?>, '<?php echo htmlspecialchars($parent['category_name'], ENT_QUOTES); ?>')">
                                        <i class="fas fa-plus-circle"></i>
                                    </button>
                                </td>
                                <td data-label="Status" class="text-center">
                                    <form method="POST" class="d-inline">
                                        <?php echo Session::csrfField(); ?>
                                        <input type="hidden" name="action" value="toggle_active">
                                        <input type="hidden" name="category_id" value="<?php echo $parent['id']; ?>">
                                        <button type="submit" class="btn btn-sm <?php echo $parent['is_active'] ? 'btn-success' : 'btn-secondary'; ?>" onclick="return confirm('Toggle category status?');">
                                            <i class="fas fa-<?php echo $parent['is_active'] ? 'check' : 'times'; ?> me-1"></i>
                                            <?php echo $parent['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td data-label="Order" class="text-center">
                                    <span class="badge bg-secondary"><?php echo $parent['display_order']; ?></span>
                                </td>
                                <td data-label="Actions" class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-warning" onclick='openEditModal(<?php echo json_encode($parent); ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($parent['child_count'] == 0): ?>
                                            <form method="POST" class="d-inline">
                                                <?php echo Session::csrfField(); ?>
                                                <input type="hidden" name="action" value="delete_category">
                                                <input type="hidden" name="category_id" value="<?php echo $parent['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            
                            <?php if (!empty($parent['children'])): ?>
                                <?php foreach ($parent['children'] as $child): ?>
                                    <tr class="child-row <?php echo $child['is_active'] ? '' : 'inactive-row'; ?>" data-parent-id="<?php echo $parent['id']; ?>">
                                        <td data-label="Category Name">
                                            <div class="category-name-cell">
                                                <i class="fas fa-level-up-alt fa-rotate-90 text-muted me-2"></i>
                                                <i class="fas fa-file" style="font-size: 1rem;"></i>
                                                <div class="category-details">
                                                    <div class="category-title"><?php echo htmlspecialchars($child['category_name']); ?></div>
                                                    <?php if ($child['description']): ?>
                                                        <div class="category-desc"><?php echo htmlspecialchars($child['description']); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Code">
                                            <code class="text-muted"><?php echo htmlspecialchars($child['category_code'] ?? '-'); ?></code>
                                        </td>
                                        <td data-label="Children" class="text-center">
                                            <span class="text-muted">—</span>
                                        </td>
                                        <td data-label="Status" class="text-center">
                                            <form method="POST" class="d-inline">
                                                <?php echo Session::csrfField(); ?>
                                                <input type="hidden" name="action" value="toggle_active">
                                                <input type="hidden" name="category_id" value="<?php echo $child['id']; ?>">
                                                <button type="submit" class="btn btn-sm <?php echo $child['is_active'] ? 'btn-success' : 'btn-secondary'; ?>" onclick="return confirm('Toggle?');">
                                                    <i class="fas fa-<?php echo $child['is_active'] ? 'check' : 'times'; ?> me-1"></i>
                                                    <?php echo $child['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td data-label="Order" class="text-center">
                                            <span class="badge bg-secondary"><?php echo $child['display_order']; ?></span>
                                        </td>
                                        <td data-label="Actions" class="text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-warning" onclick='openEditModal(<?php echo json_encode($child); ?>)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" class="d-inline">
                                                    <?php echo Session::csrfField(); ?>
                                                    <input type="hidden" name="action" value="delete_category">
                                                    <input type="hidden" name="category_id" value="<?php echo $child['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?');">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Category Modal - CUSTOM (NO BOOTSTRAP) -->
<div class="custom-modal" id="addCategoryModal">
    <div class="custom-modal-overlay" onclick="closeModal('addCategoryModal')"></div>
    <div class="custom-modal-content">
        <form method="POST">
            <?php echo Session::csrfField(); ?>
            <input type="hidden" name="action" value="add_category">
            <input type="hidden" name="parent_id" id="add_parent_id">
            
            <div class="custom-modal-header">
                <h5>
                    <i class="fas fa-folder-plus"></i>
                    <span id="addModalTitle">Add Parent Category</span>
                </h5>
                <button type="button" class="custom-modal-close" onclick="closeModal('addCategoryModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="custom-modal-body">
                <div id="parentCategoryInfo" class="alert alert-info" style="display: none;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Parent Category:</strong> <span id="parentCategoryName"></span>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Category Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="category_name" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Category Code</label>
                    <input type="text" class="form-control" name="category_code" placeholder="e.g., OFFICE_SUPPLIES">
                    <small class="text-muted">Optional. Use uppercase with underscores.</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" class="form-control" name="display_order" value="0" min="0">
                    <small class="text-muted">Lower numbers appear first.</small>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="add_is_active" name="is_active" checked>
                    <label class="form-check-label" for="add_is_active">
                        Active (visible in requisition forms)
                    </label>
                </div>
            </div>
            
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addCategoryModal')">
                    <i class="fas fa-times me-2"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Add Category
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal - CUSTOM (NO BOOTSTRAP) -->
<div class="custom-modal" id="editCategoryModal">
    <div class="custom-modal-overlay" onclick="closeModal('editCategoryModal')"></div>
    <div class="custom-modal-content">
        <form method="POST">
            <?php echo Session::csrfField(); ?>
            <input type="hidden" name="action" value="edit_category">
            <input type="hidden" name="category_id" id="edit_category_id">
            
            <div class="custom-modal-header">
                <h5>
                    <i class="fas fa-edit"></i> Edit Category
                </h5>
                <button type="button" class="custom-modal-close" onclick="closeModal('editCategoryModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="custom-modal-body">
                <div class="mb-3">
                    <label class="form-label">Parent Category</label>
                    <select class="form-select" id="edit_parent_id" name="parent_id">
                        <option value="">-- None (Make this a parent category) --</option>
                        <?php foreach ($parentCategories as $parent): ?>
                            <option value="<?php echo $parent['id']; ?>">
                                <?php echo htmlspecialchars($parent['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Category Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Category Code</label>
                    <input type="text" class="form-control" id="edit_category_code" name="category_code">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" class="form-control" id="edit_display_order" name="display_order" min="0">
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                    <label class="form-check-label" for="edit_is_active">
                        Active (visible in requisition forms)
                    </label>
                </div>
            </div>
            
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editCategoryModal')">
                    <i class="fas fa-times me-2"></i> Cancel
                </button>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save me-2"></i> Update Category
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Open Modal
function openModal(modalId, type, parentId, parentName) {
    const modal = document.getElementById(modalId);
    if (!modal) return; // safety

    if (modalId === 'addCategoryModal') {
        // Reset form
        const form = modal.querySelector('form');
        if (form) form.reset();

        const activeCheckbox = document.getElementById('add_is_active');
        if (activeCheckbox) activeCheckbox.checked = true;

        const titleEl = document.getElementById('addModalTitle');
        const parentIdInput = document.getElementById('add_parent_id');
        const infoDiv = document.getElementById('parentCategoryInfo');
        const nameSpan = document.getElementById('parentCategoryName');

        if (type === 'parent') {
            if (titleEl) titleEl.textContent = 'Add Parent Category';
            if (parentIdInput) parentIdInput.value = '';
            if (infoDiv) infoDiv.style.display = 'none';
        } else {
            if (titleEl) titleEl.textContent = 'Add Child Category';
            if (parentIdInput) parentIdInput.value = parentId || '';
            if (nameSpan && parentName) nameSpan.textContent = parentName;
            if (infoDiv) infoDiv.style.display = 'block';
        }
    }

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Close Modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// Open Edit Modal
function openEditModal(category) {
    document.getElementById('edit_category_id').value = category.id;
    document.getElementById('edit_parent_id').value = category.parent_id || '';
    document.getElementById('edit_category_name').value = category.category_name;
    document.getElementById('edit_category_code').value = category.category_code || '';
    document.getElementById('edit_description').value = category.description || '';
    document.getElementById('edit_display_order').value = category.display_order;
    document.getElementById('edit_is_active').checked = category.is_active == 1;
    
    const parentSelect = document.getElementById('edit_parent_id');
    Array.from(parentSelect.options).forEach(option => {
        option.disabled = (option.value == category.id);
    });
    
    openModal('editCategoryModal');
}

// Toggle Children Visibility
function toggleChildren(parentId) {
    const childRows = document.querySelectorAll(`tr.child-row[data-parent-id="${parentId}"]`);
    const toggleButton = document.querySelector(`tr[data-parent-id="${parentId}"] .collapse-toggle`);
    
    childRows.forEach(row => row.classList.toggle('show'));
    if (toggleButton) toggleButton.classList.toggle('expanded');
}

// Close modal when pressing ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const activeModals = document.querySelectorAll('.custom-modal.active');
        activeModals.forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
});

// Auto-dismiss alerts
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
            alert.remove();
        });
    }, 5000);
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
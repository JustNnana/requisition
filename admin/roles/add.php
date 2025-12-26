<?php
/**
 * GateWey Requisition Management System
 * Role Management - Add New Role
 *
 * File: admin/roles/add.php
 * Purpose: Form to add a new role
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

// Initialize variables
$errors = [];
$success = '';
$formData = [
    'role_name' => '',
    'role_code' => '',
    'description' => '',
    'can_raise_requisition' => 1,
    'can_approve' => 0,
    'can_view_all' => 0
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Sanitize input
        $formData = [
            'role_name' => Sanitizer::string($_POST['role_name'] ?? ''),
            'role_code' => strtoupper(Sanitizer::string($_POST['role_code'] ?? '')),
            'description' => Sanitizer::string($_POST['description'] ?? ''),
            'can_raise_requisition' => isset($_POST['can_raise_requisition']) ? 1 : 0,
            'can_approve' => isset($_POST['can_approve']) ? 1 : 0,
            'can_view_all' => isset($_POST['can_view_all']) ? 1 : 0
        ];

        // Validate input
        if (empty($formData['role_name'])) {
            $errors[] = 'Role name is required.';
        } elseif (strlen($formData['role_name']) < 2 || strlen($formData['role_name']) > 50) {
            $errors[] = 'Role name must be between 2 and 50 characters.';
        }

        if (empty($formData['role_code'])) {
            $errors[] = 'Role code is required.';
        } elseif (strlen($formData['role_code']) < 2 || strlen($formData['role_code']) > 20) {
            $errors[] = 'Role code must be between 2 and 20 characters.';
        } elseif (!preg_match('/^[A-Z_]+$/', $formData['role_code'])) {
            $errors[] = 'Role code must contain only uppercase letters and underscores.';
        }

        if (strlen($formData['description']) > 500) {
            $errors[] = 'Description must not exceed 500 characters.';
        }

        // Check if role name or code already exists
        if (empty($errors)) {
            $existingRole = $db->fetchOne("SELECT id FROM roles WHERE role_name = ? OR role_code = ?", [
                $formData['role_name'],
                $formData['role_code']
            ]);

            if ($existingRole) {
                $errors[] = 'A role with this name or code already exists.';
            }
        }

        // Insert role if no errors
        if (empty($errors)) {
            try {
                $sql = "INSERT INTO roles (role_name, role_code, description, can_raise_requisition, can_approve, can_view_all, is_active)
                        VALUES (?, ?, ?, ?, ?, ?, 1)";

                $result = $db->execute($sql, [
                    $formData['role_name'],
                    $formData['role_code'],
                    $formData['description'],
                    $formData['can_raise_requisition'],
                    $formData['can_approve'],
                    $formData['can_view_all']
                ]);

                if ($result) {
                    Session::setFlash('success', 'Role "' . $formData['role_name'] . '" created successfully.');
                    redirect('list.php');
                } else {
                    $errors[] = 'Failed to create role. Please try again.';
                }
            } catch (Exception $e) {
                error_log("Add role error: " . $e->getMessage());
                $errors[] = 'An error occurred while creating the role.';
            }
        }
    }
}

// Page title
$pageTitle = 'Add New Role';
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

/* Form Container */
.form-container {
    max-width: 800px;
    margin: 0 auto;
}

.form-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    padding: var(--spacing-6);
}

.form-section {
    margin-bottom: var(--spacing-6);
}

.form-section:last-child {
    margin-bottom: 0;
}

.form-section-title {
    font-size: var(--font-size-lg);
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--spacing-4);
    padding-bottom: var(--spacing-3);
    border-bottom: 2px solid var(--border-color);
}

.form-group {
    margin-bottom: var(--spacing-4);
}

.form-label {
    display: block;
    margin-bottom: var(--spacing-2);
    font-size: var(--font-size-sm);
    font-weight: 500;
    color: var(--text-primary);
}

.form-label .required {
    color: var(--danger);
    margin-left: var(--spacing-1);
}

.form-control {
    display: block;
    width: 100%;
    padding: var(--spacing-3);
    font-size: var(--font-size-sm);
    line-height: 1.5;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    transition: all 0.15s ease;
    color: var(--text-primary);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-control::placeholder {
    color: var(--text-muted);
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.form-help {
    display: block;
    margin-top: var(--spacing-1);
    font-size: var(--font-size-xs);
    color: var(--text-muted);
}

/* Checkbox Group */
.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
}

.checkbox-item {
    display: flex;
    align-items: flex-start;
    padding: var(--spacing-3);
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    transition: all 0.15s ease;
}

.checkbox-item:hover {
    background: var(--bg-hover);
}

.checkbox-item input[type="checkbox"] {
    margin-top: 2px;
    margin-right: var(--spacing-3);
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-label {
    flex: 1;
    cursor: pointer;
}

.checkbox-title {
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: var(--spacing-1);
}

.checkbox-description {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
    line-height: 1.4;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: var(--spacing-3);
    margin-top: var(--spacing-6);
    padding-top: var(--spacing-6);
    border-top: 1px solid var(--border-color);
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
    .form-card {
        padding: var(--spacing-4);
    }

    .form-actions {
        flex-direction: column;
    }

    .form-actions .btn {
        width: 100%;
    }
}
</style>

<div class="page-header">
    <h1><i class="fas fa-user-shield"></i> Add New Role</h1>
    <div class="content-breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="list.php">Role Management</a></li>
                <li class="breadcrumb-item active">Add New Role</li>
            </ol>
        </nav>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <?php foreach ($errors as $error): ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="form-container">
    <div class="form-card">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo Session::generateCsrfToken(); ?>">

            <div class="form-section">
                <h2 class="form-section-title">Basic Information</h2>

                <div class="form-group">
                    <label for="role_name" class="form-label">
                        Role Name<span class="required">*</span>
                    </label>
                    <input type="text"
                           id="role_name"
                           name="role_name"
                           class="form-control"
                           value="<?php echo htmlspecialchars($formData['role_name']); ?>"
                           placeholder="e.g., Project Manager"
                           required>
                    <small class="form-help">The display name for this role (2-50 characters)</small>
                </div>

                <div class="form-group">
                    <label for="role_code" class="form-label">
                        Role Code<span class="required">*</span>
                    </label>
                    <input type="text"
                           id="role_code"
                           name="role_code"
                           class="form-control"
                           value="<?php echo htmlspecialchars($formData['role_code']); ?>"
                           placeholder="e.g., PROJ_MGR"
                           style="text-transform: uppercase;"
                           required>
                    <small class="form-help">Unique identifier using uppercase letters and underscores (2-20 characters)</small>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">
                        Description
                    </label>
                    <textarea id="description"
                              name="description"
                              class="form-control"
                              placeholder="Describe the responsibilities and capabilities of this role..."><?php echo htmlspecialchars($formData['description']); ?></textarea>
                    <small class="form-help">A brief description of what this role can do (max 500 characters)</small>
                </div>
            </div>

            <div class="form-section">
                <h2 class="form-section-title">Permissions</h2>

                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox"
                               id="can_raise_requisition"
                               name="can_raise_requisition"
                               value="1"
                               <?php echo $formData['can_raise_requisition'] ? 'checked' : ''; ?>>
                        <label for="can_raise_requisition" class="checkbox-label">
                            <div class="checkbox-title">
                                <i class="fas fa-file-alt"></i> Can Raise Requisitions
                            </div>
                            <div class="checkbox-description">
                                Users with this role can create and submit new requisitions for approval.
                            </div>
                        </label>
                    </div>

                    <div class="checkbox-item">
                        <input type="checkbox"
                               id="can_approve"
                               name="can_approve"
                               value="1"
                               <?php echo $formData['can_approve'] ? 'checked' : ''; ?>>
                        <label for="can_approve" class="checkbox-label">
                            <div class="checkbox-title">
                                <i class="fas fa-check-circle"></i> Can Approve Requisitions
                            </div>
                            <div class="checkbox-description">
                                Users with this role can approve or reject requisitions submitted by others.
                            </div>
                        </label>
                    </div>

                    <div class="checkbox-item">
                        <input type="checkbox"
                               id="can_view_all"
                               name="can_view_all"
                               value="1"
                               <?php echo $formData['can_view_all'] ? 'checked' : ''; ?>>
                        <label for="can_view_all" class="checkbox-label">
                            <div class="checkbox-title">
                                <i class="fas fa-eye"></i> Can View All Requisitions
                            </div>
                            <div class="checkbox-description">
                                Users with this role can view all requisitions across the organization. Without this permission, users can only see their own requisitions and those in their department.
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <span>Create Role</span>
                </button>
                <a href="list.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i>
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

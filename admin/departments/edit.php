<?php

/**
 * GateWey Requisition Management System
 * Department Management - Edit Department
 * 
 * File: admin/departments/edit.php
 */

define('APP_ACCESS', true);
define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../helpers/permissions.php';

Session::start();
require_once __DIR__ . '/../../middleware/auth-check.php';
require_once __DIR__ . '/../../middleware/role-check.php';
checkRole(ROLE_SUPER_ADMIN);

$department = new Department();
$departmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$departmentId) {
    Session::setFlash('error', 'Invalid department ID.');
    header('Location: list.php');
    exit;
}

$departmentData = $department->getById($departmentId);
if (!$departmentData) {
    Session::setFlash('error', 'Department not found.');
    header('Location: list.php');
    exit;
}

$errors = [];
$success = '';
$formData = $departmentData;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $formData = [
            'department_name' => Sanitizer::string($_POST['department_name'] ?? ''),
            'department_code' => Sanitizer::string($_POST['department_code'] ?? ''),
            'description' => Sanitizer::string($_POST['description'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        $validator = new Validator();
        $validator->setData($formData);
        $validator->setRules([
            'department_name' => 'required|min:2|max:100',
            'department_code' => 'required|min:2|max:20',
            'description' => 'max:500'
        ]);

        if (!$validator->validate()) {
            $errors = array_column($validator->getErrors(), 0);
        } else {
            $result = $department->update($departmentId, $formData);
            if ($result['success']) {
                $success = 'Department updated successfully.';
                // Refresh department data
                $departmentData = $department->getById($departmentId);
                $formData = $departmentData;
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$pageTitle = 'Edit Department';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<!-- Enhanced Styles -->
<style>
    /* Form Container & Layout */
    .form-container {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: var(--spacing-6);
        max-width: 1200px;
        margin: 0 auto;
    }

    @media (max-width: 992px) {
        .form-container {
            grid-template-columns: 1fr;
        }
    }

    /* Form Card */
    .form-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        transition: var(--theme-transition);
    }

    .form-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    /* Form Header */
    .form-header {
        padding: var(--spacing-6);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: var(--spacing-4);
    }

    .form-icon {
        width: 48px;
        height: 48px;
        background: var(--primary);
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }

    .form-header-content {
        flex: 1;
    }

    .form-title {
        margin: 0 0 var(--spacing-1) 0;
        font-size: var(--font-size-2xl);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .form-subtitle {
        margin: 0;
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
    }

    /* Form Body */
    .form-body {
        padding: var(--spacing-6);
    }

    /* Form Sections */
    .form-section {
        margin-bottom: var(--spacing-8);
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .form-section-header {
        margin-bottom: var(--spacing-5);
        padding-bottom: var(--spacing-3);
        border-bottom: 1px solid var(--border-color);
    }

    .form-section-title {
        margin: 0 0 var(--spacing-1) 0;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .form-section-subtitle {
        margin: 0;
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
    }

    /* Form Rows */
    .form-row {
        margin-bottom: var(--spacing-5);
    }

    .form-row-2-cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-4);
    }

    @media (max-width: 768px) {
        .form-row-2-cols {
            grid-template-columns: 1fr;
        }
    }

    /* Form Groups */
    .form-group {
        position: relative;
    }

    .form-label {
        display: block;
        margin-bottom: var(--spacing-2);
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
    }

    .form-label.required::after {
        content: ' *';
        color: var(--danger);
    }

    .form-control {
        display: block;
        width: 100%;
        padding: var(--spacing-3) var(--spacing-4);
        font-size: var(--font-size-base);
        background: var(--bg-input);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        color: var(--text-primary);
        transition: var(--theme-transition);
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
        outline: none;
    }

    .form-control::placeholder {
        color: var(--text-muted);
    }

    .form-text {
        margin-top: var(--spacing-2);
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
    }

    /* Textarea */
    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    /* Checkbox Styling */
    .checkbox-wrapper {
        position: relative;
    }

    .form-checkbox {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        padding: var(--spacing-3) var(--spacing-4);
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--theme-transition);
        position: relative;
    }

    .checkbox-label::before {
        content: '';
        width: 16px;
        height: 16px;
        border: 2px solid var(--border-color);
        border-radius: 4px;
        margin-right: var(--spacing-3);
        flex-shrink: 0;
        transition: var(--theme-transition);
    }

    .form-checkbox:checked+.checkbox-label {
        background: rgba(var(--primary-rgb), 0.1);
        border-color: var(--primary);
    }

    .form-checkbox:checked+.checkbox-label::before {
        border-color: var(--primary);
        background: var(--primary);
    }

    .form-checkbox:checked+.checkbox-label::after {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        left: 17px;
        top: 50%;
        transform: translateY(-50%);
        color: white;
        font-size: 10px;
    }

    .checkbox-content {
        flex: 1;
    }

    .checkbox-title {
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
    }

    .checkbox-description {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        margin-top: 2px;
    }

    /* Form Actions */
    .form-actions {
        margin-top: var(--spacing-8);
        padding-top: var(--spacing-6);
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: var(--spacing-3);
        justify-content: flex-end;
    }

    @media (max-width: 576px) {
        .form-actions {
            flex-direction: column;
        }
    }

    .stats-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        height: fit-content;
        position: sticky;
        top: var(--spacing-4);
    }

    .stats-header {
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .stats-icon {
        color: var(--info);
        font-size: var(--font-size-lg);
    }

    .stats-title {
        margin: 0;
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .stats-content {
        padding: var(--spacing-5);
    }

    .stat-item {
        padding: var(--spacing-4) 0;
        border-bottom: 1px solid var(--border-color);
    }

    .stat-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .stat-item:first-child {
        padding-top: 0;
    }

    .stat-label {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-3);
        font-weight: var(--font-weight-medium);
    }

    .stat-label i {
        font-size: var(--font-size-sm);
        opacity: 0.7;
    }

    .stat-value {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .stat-number {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        line-height: 1;
    }

    .stat-text {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
    }

    .stat-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .stat-text.info {
        color: var(--info);
    }

    .stat-text.info .stat-dot {
        background-color: var(--info);
    }

    .stat-text.warning {
        color: var(--warning);
    }

    .stat-text.warning .stat-dot {
        background-color: var(--warning);
    }

    .stat-text.success {
        color: var(--success);
    }

    .stat-text.success .stat-dot {
        background-color: var(--success);
    }

    .stat-text.danger {
        color: var(--danger);
    }

    .stat-text.danger .stat-dot {
        background-color: var(--danger);
    }
    /* Alert Messages */
    .alert {
        display: flex;
        align-items: flex-start;
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-4);
        gap: var(--spacing-3);
    }

    .alert-danger {
        background: rgba(var(--danger-rgb), 0.1);
        border-color: rgba(var(--danger-rgb), 0.2);
        color: var(--danger);
    }

    .alert-success {
        background: rgba(var(--success-rgb), 0.1);
        border-color: rgba(var(--success-rgb), 0.2);
        color: var(--success);
    }

    .alert i {
        font-size: var(--font-size-lg);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .alert-content {
        flex: 1;
    }

    .alert-title {
        font-weight: var(--font-weight-semibold);
        margin-bottom: var(--spacing-1);
    }

    .alert-message {
        font-size: var(--font-size-sm);
        line-height: 1.5;
        margin-bottom: var(--spacing-3);
    }

    .alert-message ul {
        margin: 0;
        padding-left: var(--spacing-4);
    }

    .alert-message li {
        margin-bottom: var(--spacing-1);
    }

    .alert-actions {
        display: flex;
        gap: var(--spacing-2);
        flex-wrap: wrap;
    }

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
        text-decoration: underline;
    }

    .breadcrumb-item.active {
        color: var(--text-primary);
    }

    .breadcrumb-item+.breadcrumb-item::before {
        content: "/";
        margin: 0 var(--spacing-2);
        color: var(--text-muted);
    }

    /* Content Header */
    .content-header {
        margin-bottom: var(--spacing-6);
    }

    .content-actions {
        display: flex;
        gap: var(--spacing-3);
        align-items: center;
    }

    /* Warning Banner */
    .warning-banner {
        display: flex;
        align-items: flex-start;
        padding: var(--spacing-4);
        background: rgba(var(--warning-rgb), 0.1);
        border: 1px solid rgba(var(--warning-rgb), 0.2);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-4);
        gap: var(--spacing-3);
    }

    .warning-banner i {
        color: var(--warning);
        font-size: var(--font-size-lg);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .warning-banner-content {
        flex: 1;
        color: var(--warning);
    }

    .warning-banner-title {
        font-weight: var(--font-weight-semibold);
        margin-bottom: var(--spacing-1);
    }

    .warning-banner-message {
        font-size: var(--font-size-sm);
        line-height: 1.5;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .form-header {
            flex-direction: column;
            text-align: center;
        }

        .form-icon {
            margin: 0 auto;
        }

        .stats-card {
            position: static;
        }

        .content-actions {
            flex-wrap: wrap;
            gap: var(--spacing-2);
        }
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">Edit Department</h1>
            <nav class="content-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="../../dashboard/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="list.php">Departments</a>
                    </li>
                    <li class="breadcrumb-item active">Edit Department</li>
                </ol>
            </nav>
        </div>
        <div class="content-actions">
            <a href="list.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Departments</span>
            </a>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Error</div>
            <div class="alert-message">
                <strong>Please correct the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success" role="alert">
        <i class="fas fa-check-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Success!</div>
            <div class="alert-message"><?php echo htmlspecialchars($success); ?></div>
            <div class="alert-actions">
                <a href="list.php" class="btn btn-sm btn-primary">View All Departments</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Warning Banner (if department has users) -->
<?php if ($departmentData['user_count'] > 0): ?>
    <div class="warning-banner">
        <i class="fas fa-exclamation-triangle"></i>
        <div class="warning-banner-content">
            <div class="warning-banner-title">Active Department</div>
            <div class="warning-banner-message">
                This department currently has <?php echo $departmentData['user_count']; ?> assigned user(s).
                Deactivating it may affect their access and ability to create requisitions.
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Edit Department Form -->
<div class="form-container">
    <div class="form-card">
        <div class="form-header">
            <div class="form-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="form-header-content">
                <h2 class="form-title">Department Information</h2>
                <p class="form-subtitle">Update department details and settings</p>
            </div>
        </div>

        <div class="form-body">
            <form method="POST" action="" class="enhanced-form">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">

                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">Basic Information</h3>
                        <p class="form-section-subtitle">Department name and identification code</p>
                    </div>

                    <div class="form-row form-row-2-cols">
                        <div class="form-group">
                            <label for="department_name" class="form-label required">Department Name</label>
                            <input type="text"
                                id="department_name"
                                name="department_name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($formData['department_name']); ?>"
                                placeholder="Enter department name"
                                required>
                            <div class="form-text">Full name of the department</div>
                        </div>

                        <div class="form-group">
                            <label for="department_code" class="form-label required">Department Code</label>
                            <input type="text"
                                id="department_code"
                                name="department_code"
                                class="form-control"
                                value="<?php echo htmlspecialchars($formData['department_code']); ?>"
                                placeholder="e.g., IT, HR, FIN"
                                style="text-transform: uppercase;"
                                maxlength="20"
                                required>
                            <div class="form-text">Short unique code (2-20 characters)</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description"
                                name="description"
                                class="form-control"
                                rows="4"
                                placeholder="Enter department description (optional)"><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
                            <div class="form-text">Brief description of department's role and responsibilities</div>
                        </div>
                    </div>
                </div>

                <!-- Status Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">Department Status</h3>
                        <p class="form-section-subtitle">Control department availability</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="checkbox-wrapper">
                                <input type="checkbox"
                                    id="is_active"
                                    name="is_active"
                                    class="form-checkbox"
                                    <?php echo $formData['is_active'] ? 'checked' : ''; ?>>
                                <label class="checkbox-label" for="is_active">
                                    <div class="checkbox-content">
                                        <div class="checkbox-title">Active Department</div>
                                        <div class="checkbox-description">Users can be assigned to this department and create requisitions</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i>
                        <span>Update Department</span>
                    </button>
                    <a href="list.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Card -->
    <div class="stats-card">
        <div class="stats-header">
            <i class="fas fa-chart-bar stats-icon"></i>
            <h3 class="stats-title">Department Statistics</h3>
        </div>
        <div class="stats-content">
            <div class="stat-item">
                <span class="stat-label">
                    <i class="fas fa-users"></i> Assigned Users
                </span>
                <div class="stat-value">
                    <span class="stat-number"><?php echo $departmentData['user_count']; ?></span>
                    <span class="stat-text info">
                        <span class="stat-dot"></span>
                        <?php echo $departmentData['user_count'] == 1 ? 'User' : 'Users'; ?>
                    </span>
                </div>
            </div>

            <div class="stat-item">
                <span class="stat-label">
                    <i class="fas fa-file-alt"></i> Total Requisitions
                </span>
                <div class="stat-value">
                    <span class="stat-number"><?php echo $departmentData['requisition_count']; ?></span>
                    <span class="stat-text warning">
                        <span class="stat-dot"></span>
                        Requisitions
                    </span>
                </div>
            </div>

            <div class="stat-item">
                <span class="stat-label">
                    <i class="fas fa-toggle-on"></i> Status
                </span>
                <div class="stat-value">
                    <span class="stat-text <?php echo $departmentData['is_active'] ? 'success' : 'danger'; ?>">
                        <span class="stat-dot"></span>
                        <?php echo $departmentData['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🎨 Initializing Edit Department Form...');

        const form = document.querySelector('.enhanced-form');
        const departmentCode = document.getElementById('department_code');
        const isActiveCheckbox = document.getElementById('is_active');
        const userCount = <?php echo $departmentData['user_count']; ?>;

        // Auto-uppercase department code
        if (departmentCode) {
            departmentCode.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }

        // Warn when deactivating department with users
        if (isActiveCheckbox && userCount > 0) {
            isActiveCheckbox.addEventListener('change', function() {
                if (!this.checked) {
                    const confirmed = confirm(
                        `Warning: This department has ${userCount} assigned user(s).\n\n` +
                        'Deactivating it may affect their access and ability to create requisitions.\n\n' +
                        'Are you sure you want to deactivate this department?'
                    );

                    if (!confirmed) {
                        this.checked = true;
                    }
                }
            });
        }

        // Form submission enhancement
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitButton = form.querySelector('button[type="submit"]');

                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Updating Department...</span>';
            });
        }

        // Real-time validation
        const formControls = document.querySelectorAll('.form-control');
        formControls.forEach(control => {
            control.addEventListener('input', function() {
                if (this.hasAttribute('required') && this.value.trim()) {
                    this.style.borderColor = 'var(--success)';
                } else if (this.hasAttribute('required') && !this.value.trim()) {
                    this.style.borderColor = 'var(--border-color)';
                }
            });

            // Reset border on blur if empty
            control.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.style.borderColor = 'var(--border-color)';
                }
            });
        });

        console.log('✅ Edit Department Form initialized successfully');
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
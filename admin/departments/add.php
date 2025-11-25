<?php
/**
 * GateWey Requisition Management System
 * Department Management - Add Department
 * 
 * File: admin/departments/add.php
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
$errors = [];
$success = '';
$formData = [];

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
            $result = $department->create($formData);
            if ($result['success']) {
                $success = 'Department created successfully.';
                
                // Reset form data
                $formData = [
                    'department_name' => '',
                    'department_code' => '',
                    'description' => '',
                    'is_active' => 1
                ];
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$pageTitle = 'Add Department';
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

    .form-checkbox:checked + .checkbox-label {
        background: rgba(var(--primary-rgb), 0.1);
        border-color: var(--primary);
    }

    .form-checkbox:checked + .checkbox-label::before {
        border-color: var(--primary);
        background: var(--primary);
    }

    .form-checkbox:checked + .checkbox-label::after {
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

    /* Tips Card */
    .tips-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        height: fit-content;
        position: sticky;
        top: var(--spacing-4);
    }

    .tips-header {
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .tips-icon {
        color: var(--warning);
        font-size: var(--font-size-lg);
    }

    .tips-title {
        margin: 0;
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .tips-content {
        padding: var(--spacing-4);
    }

    .tip-item {
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-3);
        margin-bottom: var(--spacing-4);
    }

    .tip-item:last-child {
        margin-bottom: 0;
    }

    .tip-icon {
        color: var(--success);
        font-size: var(--font-size-sm);
        margin-top: 2px;
        flex-shrink: 0;
    }

    .tip-text {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        line-height: 1.5;
    }

    .tip-text strong {
        color: var(--text-primary);
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

    .breadcrumb-item + .breadcrumb-item::before {
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

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .form-header {
            flex-direction: column;
            text-align: center;
        }

        .form-icon {
            margin: 0 auto;
        }

        .tips-card {
            position: static;
        }

        .content-header {
      flex-direction: column !important;
    }

    .content-actions {
      display: flex !important;
      flex-direction: column !important;
      gap: 0.5rem !important;
      white-space: nowrap !important;
    }

    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-start flex-wrap">
        <div>
            <h1 class="content-title">Department</h1>
            <nav class="content-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="../../dashboard/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="list.php">Departments</a>
                    </li>
                    <li class="breadcrumb-item active">Create Department</li>
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
                <a href="add.php" class="btn btn-sm btn-outline-primary">Create Another Department</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Create Department Form -->
<div class="form-container">
    <div class="form-card">
        <div class="form-header">
            <div class="form-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="form-header-content">
                <h2 class="form-title">Department Information</h2>
                <p class="form-subtitle">Create a new department in the organization</p>
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
                                   value="<?php echo htmlspecialchars($formData['department_name'] ?? ''); ?>"
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
                                   value="<?php echo htmlspecialchars($formData['department_code'] ?? ''); ?>"
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
                        <p class="form-section-subtitle">Set the initial status for this department</p>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       class="form-checkbox"
                                       <?php echo (!isset($formData['is_active']) || $formData['is_active']) ? 'checked' : ''; ?>>
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
                        <i class="fas fa-building"></i>
                        <span>Create Department</span>
                    </button>
                    <a href="list.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Tips Card -->
    <div class="tips-card">
        <div class="tips-header">
            <i class="fas fa-lightbulb tips-icon"></i>
            <h3 class="tips-title">Quick Tips</h3>
        </div>
        <div class="tips-content">
            <div class="tip-item">
                <i class="fas fa-info-circle tip-icon"></i>
                <div class="tip-text">
                    <strong>Department Name:</strong> Use clear, descriptive names like "Information Technology" or "Human Resources"
                </div>
            </div>
            
            <div class="tip-item">
                <i class="fas fa-code tip-icon"></i>
                <div class="tip-text">
                    <strong>Department Code:</strong> Must be unique. Common examples: IT, HR, FIN, MKT, OPS
                </div>
            </div>
            
            <div class="tip-item">
                <i class="fas fa-users tip-icon"></i>
                <div class="tip-text">
                    <strong>User Assignment:</strong> Line Managers and Team Members must be assigned to a department
                </div>
            </div>
            
            <div class="tip-item">
                <i class="fas fa-toggle-on tip-icon"></i>
                <div class="tip-text">
                    <strong>Active Status:</strong> Only active departments appear in user assignment dropdowns
                </div>
            </div>
            
            <div class="tip-item">
                <i class="fas fa-file-alt tip-icon"></i>
                <div class="tip-text">
                    <strong>Requisitions:</strong> Department codes appear on requisition reports for tracking and analysis
                </div>
            </div>
            
            <div class="tip-item">
                <i class="fas fa-shield-alt tip-icon"></i>
                <div class="tip-text">
                    <strong>Security:</strong> Department codes cannot be changed once users are assigned to prevent data integrity issues
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎨 Initializing Create Department Form...');

    const form = document.querySelector('.enhanced-form');
    const departmentCode = document.getElementById('department_code');

    // Auto-uppercase department code
    if (departmentCode) {
        departmentCode.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    // Form submission enhancement
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Creating Department...</span>';
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

    console.log('✅ Create Department Form initialized successfully');
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
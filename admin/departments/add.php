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
                Session::setFlash('success', 'Department created successfully.');
                header('Location: list.php');
                exit;
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$pageTitle = 'Add Department';
$customCSS = ".required-field::after { content: ' *'; color: var(--danger); }";
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Add New Department</h1>
            <p class="content-subtitle">Create a new department</p>
        </div>
        <a href="list.php" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Department Information</h5></div>
            <div class="card-body">
                <form method="POST" data-loading>
                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="department_name" class="form-label required-field">Department Name</label>
                            <input type="text" id="department_name" name="department_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['department_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <label for="department_code" class="form-label required-field">Department Code</label>
                            <input type="text" id="department_code" name="department_code" class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['department_code'] ?? ''); ?>" required
                                   style="text-transform: uppercase;" maxlength="20">
                            <small class="form-text">Short code (e.g., IT, HR, FIN)</small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" id="is_active" name="is_active" class="form-check-input"
                                   <?php echo (!isset($formData['is_active']) || $formData['is_active']) ? 'checked' : ''; ?>>
                            <label for="is_active" class="form-check-label">Active</label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="list.php" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
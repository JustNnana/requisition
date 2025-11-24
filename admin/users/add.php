<?php
/**
 * GateWey Requisition Management System
 * User Management - Add New User
 * 
 * File: admin/users/add.php
 * Purpose: Form to add a new user
 */

// Define access level
define('APP_ACCESS', true);
define('ADMIN_ACCESS', true);

// Include necessary files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Department.php';
require_once __DIR__ . '/../../classes/Validator.php';
require_once __DIR__ . '/../../classes/Sanitizer.php';
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
$db = Database::getInstance();

// Get all roles and departments for dropdowns
$roles = $db->fetchAll("SELECT * FROM roles ORDER BY id");
$departments = $department->getAll(true);

// Initialize variables
$errors = [];
$formData = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Session::validateCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Sanitize input
        $formData = [
            'first_name' => Sanitizer::string($_POST['first_name'] ?? ''),
            'last_name' => Sanitizer::string($_POST['last_name'] ?? ''),
            'email' => Sanitizer::email($_POST['email'] ?? ''),
            'phone' => Sanitizer::string($_POST['phone'] ?? ''),
            'role_id' => Sanitizer::int($_POST['role_id'] ?? ''),
            'department_id' => !empty($_POST['department_id']) ? Sanitizer::int($_POST['department_id']) : null,
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Validate input
        $validator = new Validator();
        $validator->setData([
            'first_name' => $formData['first_name'],
            'last_name' => $formData['last_name'],
            'email' => $formData['email'],
            'phone' => $formData['phone'],
            'role_id' => $formData['role_id'],
            'password' => $formData['password'],
            'confirm_password' => $formData['confirm_password']
        ]);
        
        $validator->setRules([
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'email' => 'required|email',
            'phone' => 'max:20',
            'role_id' => 'required|numeric',
            'password' => 'required|min:' . PASSWORD_MIN_LENGTH . '|strong_password',
            'confirm_password' => 'required|match:password'
        ]);
        
        if (!$validator->validate()) {
            $errors = $validator->getErrors();
            $errors = array_column($errors, 0); // Get first error for each field
        } else {
            // Check if email already exists
            if ($user->emailExists($formData['email'])) {
                $errors[] = 'Email address already exists.';
            } else {
                // Create user
                $result = $user->create($formData);
                
                if ($result['success']) {
                    Session::setFlash('success', 'User created successfully.');
                    header('Location: list.php');
                    exit;
                } else {
                    $errors[] = $result['message'];
                }
            }
        }
    }
}

// Page title
$pageTitle = 'Add New User';

// Custom CSS for form
$customCSS = "
.required-field::after {
    content: ' *';
    color: var(--danger);
}
";
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Add New User</h1>
            <p class="content-subtitle">Create a new user account</p>
        </div>
        <div>
            <a href="list.php" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>
</div>

<!-- Error Messages -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Add User Form -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" data-loading>
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRF(); ?>">
                    
                    <div class="row">
                        <!-- First Name -->
                        <div class="col-md-6 mb-4">
                            <label for="first_name" class="form-label required-field">First Name</label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>"
                                   required>
                        </div>
                        
                        <!-- Last Name -->
                        <div class="col-md-6 mb-4">
                            <label for="last_name" class="form-label required-field">Last Name</label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Email -->
                        <div class="col-md-6 mb-4">
                            <label for="email" class="form-label required-field">Email Address</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                                   required>
                            <small class="form-text">This will be used for login</small>
                        </div>
                        
                        <!-- Phone -->
                        <div class="col-md-6 mb-4">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Role -->
                        <div class="col-md-6 mb-4">
                            <label for="role_id" class="form-label required-field">Role</label>
                            <select id="role_id" name="role_id" class="form-control" required>
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>"
                                            <?php echo (isset($formData['role_id']) && $formData['role_id'] == $role['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($role['role_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Department -->
                        <div class="col-md-6 mb-4">
                            <label for="department_id" class="form-label">Department</label>
                            <select id="department_id" name="department_id" class="form-control">
                                <option value="">None (Super Admin/MD)</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"
                                            <?php echo (isset($formData['department_id']) && $formData['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text">Required for Line Managers and Team Members</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Password -->
                        <div class="col-md-6 mb-4">
                            <label for="password" class="form-label required-field">Password</label>
                            <div class="input-group">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-control" 
                                       required>
                                <button class="btn btn-ghost" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-text">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters, include uppercase, lowercase, number and special character</small>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div class="col-md-6 mb-4">
                            <label for="confirm_password" class="form-label required-field">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       class="form-control" 
                                       required>
                                <button class="btn btn-ghost" type="button" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   class="form-check-input"
                                   <?php echo (!isset($formData['is_active']) || $formData['is_active']) ? 'checked' : ''; ?>>
                            <label for="is_active" class="form-check-label">
                                Active (User can login)
                            </label>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="list.php" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Help Panel -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle"></i> User Roles
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong class="text-primary">Super Admin</strong>
                    <p class="text-muted mb-0" style="font-size: var(--font-size-sm);">
                        Manages users, departments, and system settings. Cannot raise requisitions.
                    </p>
                </div>
                
                <div class="mb-3">
                    <strong class="text-primary">Managing Director</strong>
                    <p class="text-muted mb-0" style="font-size: var(--font-size-sm);">
                        Approves requisitions from Line Managers and Team Members. Can raise requisitions. No department assignment needed.
                    </p>
                </div>
                
                <div class="mb-3">
                    <strong class="text-primary">Finance Manager</strong>
                    <p class="text-muted mb-0" style="font-size: var(--font-size-sm);">
                        Reviews and approves requisitions for payment. Oversees Finance Members.
                    </p>
                </div>
                
                <div class="mb-3">
                    <strong class="text-primary">Finance Member</strong>
                    <p class="text-muted mb-0" style="font-size: var(--font-size-sm);">
                        Processes payments and uploads proof of payment.
                    </p>
                </div>
                
                <div class="mb-3">
                    <strong class="text-primary">Line Manager</strong>
                    <p class="text-muted mb-0" style="font-size: var(--font-size-sm);">
                        Approves team requisitions. Can raise requisitions. Must be assigned to a department.
                    </p>
                </div>
                
                <div class="mb-0">
                    <strong class="text-primary">Team Member</strong>
                    <p class="text-muted mb-0" style="font-size: var(--font-size-sm);">
                        Creates requisitions. Must be assigned to a department.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$customJS = "
// Password toggle
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

document.getElementById('toggleConfirmPassword')?.addEventListener('click', function() {
    const passwordInput = document.getElementById('confirm_password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Role-based department requirement
document.getElementById('role_id')?.addEventListener('change', function() {
    const departmentField = document.getElementById('department_id');
    const selectedRole = parseInt(this.value);
    
    // Roles that require department: Line Manager (5) and Team Member (6)
    if (selectedRole === 5 || selectedRole === 6) {
        departmentField.setAttribute('required', 'required');
        departmentField.closest('.col-md-6').querySelector('label').classList.add('required-field');
    } else {
        departmentField.removeAttribute('required');
        departmentField.closest('.col-md-6').querySelector('label').classList.remove('required-field');
    }
});
";
?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
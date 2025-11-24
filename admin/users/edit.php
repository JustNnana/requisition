<?php
/**
 * GateWey Requisition Management System
 * User Management - Edit User
 * 
 * File: admin/users/edit.php
 * Purpose: Form to edit an existing user
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
$db = Database::getInstance();

// Get user ID from query string
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$userId) {
    Session::setFlash('error', 'Invalid user ID.');
    header('Location: list.php');
    exit;
}

// Get user data
$userData = $user->getById($userId);

if (!$userData) {
    Session::setFlash('error', 'User not found.');
    header('Location: list.php');
    exit;
}

// Get all roles and departments for dropdowns
$roles = $db->fetchAll("SELECT * FROM roles ORDER BY id");
$departments = $department->getAll(true);

// Initialize variables
$errors = [];
$formData = $userData;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
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
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Validate input
        $validator = new Validator();
        $validator->setData([
            'first_name' => $formData['first_name'],
            'last_name' => $formData['last_name'],
            'email' => $formData['email'],
            'phone' => $formData['phone'],
            'role_id' => $formData['role_id']
        ]);
        
        $validator->setRules([
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'email' => 'required|email',
            'phone' => 'max:20',
            'role_id' => 'required|numeric'
        ]);
        
        if (!$validator->validate()) {
            $errors = $validator->getErrors();
            $errors = array_column($errors, 0);
        } else {
            // Check if email already exists (excluding current user)
            if ($user->emailExists($formData['email'], $userId)) {
                $errors[] = 'Email address already exists.';
            } else {
                // Update user
                $result = $user->update($userId, $formData);
                
                if ($result['success']) {
                    Session::setFlash('success', 'User updated successfully.');
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
$pageTitle = 'Edit User';

// Custom CSS
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
            <h1 class="content-title">Edit User</h1>
            <p class="content-subtitle">Update user information</p>
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

<!-- Edit User Form -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" data-loading>
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                    
                    <div class="row">
                        <!-- First Name -->
                        <div class="col-md-6 mb-4">
                            <label for="first_name" class="form-label required-field">First Name</label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['first_name']); ?>"
                                   required>
                        </div>
                        
                        <!-- Last Name -->
                        <div class="col-md-6 mb-4">
                            <label for="last_name" class="form-label required-field">Last Name</label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['last_name']); ?>"
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
                                   value="<?php echo htmlspecialchars($formData['email']); ?>"
                                   required>
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
                                            <?php echo ($formData['role_id'] == $role['id']) ? 'selected' : ''; ?>>
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
                                            <?php echo ($formData['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   class="form-check-input"
                                   <?php echo $formData['is_active'] ? 'checked' : ''; ?>>
                            <label for="is_active" class="form-check-label">
                                Active (User can login)
                            </label>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="list.php" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Password Change Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Change Password</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">To change the user's password, click the button below.</p>
                <a href="change-password.php?id=<?php echo $userId; ?>" class="btn btn-warning">
                    <i class="fas fa-key"></i> Change Password
                </a>
            </div>
        </div>
    </div>
    
    <!-- User Info Panel -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle"></i> User Details
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>User ID:</strong><br>
                    <span class="text-muted"><?php echo $userData['id']; ?></span>
                </div>
                
                <div class="mb-3">
                    <strong>Created:</strong><br>
                    <span class="text-muted"><?php echo date('M d, Y H:i', strtotime($userData['created_at'])); ?></span>
                </div>
                
                <?php if ($userData['last_login']): ?>
                    <div class="mb-3">
                        <strong>Last Login:</strong><br>
                        <span class="text-muted"><?php echo date('M d, Y H:i', strtotime($userData['last_login'])); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="mb-0">
                    <strong>Current Status:</strong><br>
                    <?php if ($userData['is_active']): ?>
                        <span class="badge badge-success">Active</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Inactive</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$customJS = "
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

// Trigger on page load
document.getElementById('role_id')?.dispatchEvent(new Event('change'));
";
?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
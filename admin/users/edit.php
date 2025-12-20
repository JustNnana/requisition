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

// Get user ID from query string (encrypted)
$userId = get_encrypted_id();

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
$success = '';
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
                    $success = 'User updated successfully.';
                    // Refresh user data
                    $userData = $user->getById($userId);
                    $formData = $userData;
                } else {
                    $errors[] = $result['message'];
                }
            }
        }
    }
}

// Page title
$pageTitle = 'Edit User';
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
        margin-bottom: var(--spacing-4);
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

    .form-icon.warning {
        background: var(--warning);
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

    /* Info Card */
    .info-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        height: fit-content;
        position: sticky;
        top: var(--spacing-4);
        margin-bottom: var(--spacing-4);
    }

    .info-header {
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .info-icon {
        color: var(--info);
        font-size: var(--font-size-lg);
    }

    .info-title {
        margin: 0;
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .info-content {
        padding: var(--spacing-5);
    }

    .info-item {
        padding: var(--spacing-4) 0;
        border-bottom: 1px solid var(--border-color);
    }

    .info-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-item:first-child {
        padding-top: 0;
    }

    .info-label {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-2);
        font-weight: var(--font-weight-medium);
    }

    .info-label i {
        font-size: var(--font-size-sm);
        opacity: 0.7;
    }

    .info-value {
        font-size: var(--font-size-base);
        color: var(--text-primary);
    }

    .info-status {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-2);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .info-status.success {
        color: var(--success);
    }

    .info-status.success .status-dot {
        background-color: var(--success);
    }

    .info-status.danger {
        color: var(--danger);
    }

    .info-status.danger .status-dot {
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

    /* Department Field */
    .department-field {
        transition: opacity 0.3s ease, height 0.3s ease;
    }

    /* Password Action Panel */
    .password-action-panel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-radius: var(--border-radius);
        gap: var(--spacing-4);
    }

    .password-action-content {
        flex: 1;
    }

    .password-action-title {
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
        margin-bottom: var(--spacing-1);
    }

    .password-action-text {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
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

        .info-card {
            position: static;
        }

        .content-actions {
            flex-wrap: wrap;
            gap: var(--spacing-2);
        }

        .password-action-panel {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">Edit User</h1>
            <nav class="content-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="../../dashboard/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="list.php">Users</a>
                    </li>
                    <li class="breadcrumb-item active">Edit User</li>
                </ol>
            </nav>
        </div>
        <div class="content-actions">
            <a href="list.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Users</span>
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
                <a href="list.php" class="btn btn-sm btn-primary">View All Users</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Edit User Form -->
<div class="form-container">
    <div>
        <!-- User Information Card -->
        <div class="form-card">
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div class="form-header-content">
                    <h2 class="form-title">User Information</h2>
                    <p class="form-subtitle">Update user account details and permissions</p>
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
                            <p class="form-section-subtitle">Personal details and contact information</p>
                        </div>

                        <div class="form-row form-row-2-cols">
                            <div class="form-group">
                                <label for="first_name" class="form-label required">First Name</label>
                                <input type="text" 
                                       id="first_name" 
                                       name="first_name" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($formData['first_name']); ?>"
                                       placeholder="Enter first name"
                                       required>
                                <div class="form-text">User's given name</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name" class="form-label required">Last Name</label>
                                <input type="text" 
                                       id="last_name" 
                                       name="last_name" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($formData['last_name']); ?>"
                                       placeholder="Enter last name"
                                       required>
                                <div class="form-text">User's family name</div>
                            </div>
                        </div>
                        
                        <div class="form-row form-row-2-cols">
                            <div class="form-group">
                                <label for="email" class="form-label required">Email Address</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($formData['email']); ?>"
                                       placeholder="user@example.com"
                                       required>
                                <div class="form-text">Used for login and notifications</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>"
                                       placeholder="+234 xxx xxx xxxx">
                                <div class="form-text">Optional contact number</div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Settings Section -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h3 class="form-section-title">Account Settings</h3>
                            <p class="form-section-subtitle">Role, permissions, and account status</p>
                        </div>
                        
                        <div class="form-row form-row-2-cols">
                            <div class="form-group">
                                <label for="role_id" class="form-label required">User Role</label>
                                <select id="role_id" name="role_id" class="form-control form-select" required>
                                    <option value="">-- Select Role --</option>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?php echo $role['id']; ?>"
                                                <?php echo ($formData['role_id'] == $role['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($role['role_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Determines user permissions and access level</div>
                            </div>
                            
                            <div class="form-group department-field">
                                <label for="department_id" class="form-label">Department</label>
                                <select id="department_id" name="department_id" class="form-control form-select">
                                    <option value="">-- None (Super Admin/MD) --</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>"
                                                <?php echo ($formData['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['department_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Required for Line Managers and Team Members</div>
                            </div>
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
                                            <div class="checkbox-title">Active Account</div>
                                            <div class="checkbox-description">User can login and access the system</div>
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
                            <span>Update User</span>
                        </button>
                        <a href="list.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i>
                            <span>Cancel</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password Change Card -->
        <div class="form-card">
            <div class="form-header">
                <div class="form-icon warning">
                    <i class="fas fa-key"></i>
                </div>
                <div class="form-header-content">
                    <h2 class="form-title">Change Password</h2>
                    <p class="form-subtitle">Update user's login credentials</p>
                </div>
            </div>

            <div class="form-body">
                <div class="password-action-panel">
                    <div class="password-action-content">
                        <div class="password-action-title">Reset User Password</div>
                        <div class="password-action-text">Click the button below to change the user's password securely.</div>
                    </div>
                    <a href="<?php echo build_encrypted_url('change-password.php', $userData['id']); ?>" class="btn btn-warning">
    <i class="fas fa-key"></i> Change Password
</a>
                </div>
            </div>
        </div>
    </div>

    <!-- User Details Card -->
    <div class="info-card">
        <div class="info-header">
            <i class="fas fa-info-circle info-icon"></i>
            <h3 class="info-title">User Details</h3>
        </div>
        <div class="info-content">
            <div class="info-item">
                <span class="info-label">
                    <i class="fas fa-id-badge"></i> User ID
                </span>
                <div class="info-value">#<?php echo str_pad($userData['id'], 4, '0', STR_PAD_LEFT); ?></div>
            </div>

            <div class="info-item">
                <span class="info-label">
                    <i class="fas fa-calendar-plus"></i> Account Created
                </span>
                <div class="info-value"><?php echo date('M d, Y', strtotime($userData['created_at'])); ?></div>
                <div class="form-text"><?php echo date('h:i A', strtotime($userData['created_at'])); ?></div>
            </div>

            <?php if ($userData['last_login']): ?>
                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-sign-in-alt"></i> Last Login
                    </span>
                    <div class="info-value"><?php echo date('M d, Y', strtotime($userData['last_login'])); ?></div>
                    <div class="form-text"><?php echo date('h:i A', strtotime($userData['last_login'])); ?></div>
                </div>
            <?php else: ?>
                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-sign-in-alt"></i> Last Login
                    </span>
                    <div class="info-value">Never logged in</div>
                </div>
            <?php endif; ?>

            <div class="info-item">
                <span class="info-label">
                    <i class="fas fa-toggle-on"></i> Account Status
                </span>
                <div class="info-status <?php echo $userData['is_active'] ? 'success' : 'danger'; ?>">
                    <span class="status-dot"></span>
                    <?php echo $userData['is_active'] ? 'Active' : 'Inactive'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎨 Initializing Edit User Form...');

    const roleSelect = document.getElementById('role_id');
    const departmentField = document.querySelector('.department-field');
    const departmentSelect = document.getElementById('department_id');
    const form = document.querySelector('.enhanced-form');

    // Role-based department requirement
    function updateDepartmentVisibility() {
        if (roleSelect && departmentField && departmentSelect) {
            const selectedRole = parseInt(roleSelect.value);
            
            // Roles that require department: Line Manager (5) and Team Member (6)
            if (selectedRole === 5 || selectedRole === 6) {
                departmentField.style.display = 'block';
                departmentSelect.setAttribute('required', 'required');
                departmentField.querySelector('label').classList.add('required');
            } else {
                departmentField.style.display = 'block';
                departmentSelect.removeAttribute('required');
                departmentField.querySelector('label').classList.remove('required');
            }
        }
    }

    // Initial setup
    updateDepartmentVisibility();

    // Event listeners
    if (roleSelect) {
        roleSelect.addEventListener('change', updateDepartmentVisibility);
    }

    // Form submission enhancement
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');

            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Updating User...</span>';
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

    console.log('✅ Edit User Form initialized successfully');
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
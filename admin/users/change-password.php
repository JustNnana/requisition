<?php
/**
 * GateWey Requisition Management System
 * User Management - Change User Password
 * 
 * File: admin/users/change-password.php
 * Purpose: Form to change a user's password
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

// Initialize variables
$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate input
        $validator = new Validator();
        $validator->setData([
            'password' => $password,
            'confirm_password' => $confirmPassword
        ]);
        
        $validator->setRules([
            'password' => 'required|min:' . PASSWORD_MIN_LENGTH . '|strong_password',
            'confirm_password' => 'required|match:password'
        ]);
        
        if (!$validator->validate()) {
            $errors = $validator->getErrors();
            $errors = array_column($errors, 0);
        } else {
            // Update password
            $result = $user->changePassword($userId, $password);
            
            if ($result['success']) {
                $success = 'Password changed successfully. The user can now login with the new password.';
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

// Page title
$pageTitle = 'Change Password';
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
        background: var(--warning);
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

    /* Input Group */
    .input-group {
        display: flex;
        position: relative;
    }

    .input-group .form-control {
        flex: 1;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .input-group .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-left: 0;
    }

    /* Password Strength Indicator */
    .password-strength {
        margin-top: var(--spacing-3);
        display: none;
    }

    .password-strength.show {
        display: block;
    }

    .strength-label {
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-medium);
        margin-bottom: var(--spacing-2);
        color: var(--text-secondary);
    }

    .strength-bar {
        height: 4px;
        background: var(--bg-subtle);
        border-radius: var(--border-radius-full);
        overflow: hidden;
    }

    .strength-progress {
        height: 100%;
        width: 0;
        transition: width 0.3s ease, background-color 0.3s ease;
    }

    .strength-progress.weak {
        width: 33%;
        background-color: var(--danger);
    }

    .strength-progress.medium {
        width: 66%;
        background-color: var(--warning);
    }

    .strength-progress.strong {
        width: 100%;
        background-color: var(--success);
    }

    .strength-text {
        font-size: var(--font-size-xs);
        margin-top: var(--spacing-2);
        font-weight: var(--font-weight-medium);
    }

    .strength-text.weak {
        color: var(--danger);
    }

    .strength-text.medium {
        color: var(--warning);
    }

    .strength-text.strong {
        color: var(--success);
    }

    /* Password Requirements */
    .password-requirements {
        margin-top: var(--spacing-3);
        padding: var(--spacing-3);
        background: var(--bg-subtle);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
    }

    .requirement-title {
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-2);
    }

    .requirement-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-1);
    }

    .requirement-item:last-child {
        margin-bottom: 0;
    }

    .requirement-item i {
        font-size: 10px;
        color: var(--text-muted);
    }

    .requirement-item.met {
        color: var(--success);
    }

    .requirement-item.met i {
        color: var(--success);
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

    .user-profile {
        text-align: center;
        margin-bottom: var(--spacing-4);
        padding-bottom: var(--spacing-4);
        border-bottom: 1px solid var(--border-color);
    }

    .user-avatar2 {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-2xl);
        font-weight: var(--font-weight-bold);
        margin: 0 auto var(--spacing-3);
    }

    .user-name {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-1);
    }

    .user-email {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .info-item {
        padding: var(--spacing-3) 0;
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

        .info-card {
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
            <h1 class="content-title">Change Password</h1>
            <nav class="content-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="../../dashboard/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="list.php">Users</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="edit.php?id=<?php echo $userId; ?>">Edit User</a>
                    </li>
                    <li class="breadcrumb-item active">Change Password</li>
                </ol>
            </nav>
        </div>
        <div class="content-actions">
            <a href="edit.php?id=<?php echo $userId; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Back to User</span>
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
                <a href="edit.php?id=<?php echo $userId; ?>" class="btn btn-sm btn-primary">Back to User Profile</a>
                <a href="list.php" class="btn btn-sm btn-outline-primary">View All Users</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Warning Banner -->
<div class="warning-banner">
    <i class="fas fa-exclamation-triangle"></i>
    <div class="warning-banner-content">
        <div class="warning-banner-title">Security Notice</div>
        <div class="warning-banner-message">
            Changing this user's password will immediately update their login credentials. Make sure to communicate the new password securely.
        </div>
    </div>
</div>

<!-- Change Password Form -->
<div class="form-container">
    <div class="form-card">
        <div class="form-header">
            <div class="form-icon">
                <i class="fas fa-key"></i>
            </div>
            <div class="form-header-content">
                <h2 class="form-title">New Password</h2>
                <p class="form-subtitle">Set a secure password for this user account</p>
            </div>
        </div>

        <div class="form-body">
            <form method="POST" action="" class="enhanced-form" id="passwordForm">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                
                <!-- Password Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">Password Information</h3>
                        <p class="form-section-subtitle">Enter and confirm the new password</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label required">New Password</label>
                            <div class="input-group">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-control" 
                                       placeholder="Enter new password"
                                       required>
                                <button class="btn btn-ghost" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-lock"></i>
                                Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters
                            </div>
                            
                            <!-- Password Strength Indicator -->
                            <div class="password-strength" id="passwordStrength">
                                <div class="strength-label">Password Strength</div>
                                <div class="strength-bar">
                                    <div class="strength-progress" id="strengthProgress"></div>
                                </div>
                                <div class="strength-text" id="strengthText"></div>
                            </div>

                            <!-- Password Requirements -->
                            <div class="password-requirements">
                                <div class="requirement-title">Password must contain:</div>
                                <div class="requirement-item" id="req-length">
                                    <i class="fas fa-circle"></i>
                                    <span>At least <?php echo PASSWORD_MIN_LENGTH; ?> characters</span>
                                </div>
                                <div class="requirement-item" id="req-uppercase">
                                    <i class="fas fa-circle"></i>
                                    <span>One uppercase letter (A-Z)</span>
                                </div>
                                <div class="requirement-item" id="req-lowercase">
                                    <i class="fas fa-circle"></i>
                                    <span>One lowercase letter (a-z)</span>
                                </div>
                                <div class="requirement-item" id="req-number">
                                    <i class="fas fa-circle"></i>
                                    <span>One number (0-9)</span>
                                </div>
                                <div class="requirement-item" id="req-special">
                                    <i class="fas fa-circle"></i>
                                    <span>One special character (!@#$%^&*)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="confirm_password" class="form-label required">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       class="form-control" 
                                       placeholder="Confirm new password"
                                       required>
                                <button class="btn btn-ghost" type="button" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Must match the password above</div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                    </button>
                    <a href="edit.php?id=<?php echo $userId; ?>" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- User Info Card -->
    <div class="info-card">
        <div class="info-header">
            <i class="fas fa-user info-icon"></i>
            <h3 class="info-title">User Information</h3>
        </div>
        <div class="info-content">
            <div class="user-profile">
                <div class="user-avatar2">
                    <?php 
                    $initials = strtoupper(substr($userData['first_name'], 0, 1) . substr($userData['last_name'], 0, 1));
                    echo $initials;
                    ?>
                </div>
                <div class="user-name">
                    <?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?>
                </div>
                <div class="user-email">
                    <?php echo htmlspecialchars($userData['email']); ?>
                </div>
            </div>

            <div class="info-item">
                <span class="info-label">
                    <i class="fas fa-id-badge"></i> User ID
                </span>
                <div class="info-value">#<?php echo str_pad($userData['id'], 4, '0', STR_PAD_LEFT); ?></div>
            </div>

            <div class="info-item">
                <span class="info-label">
                    <i class="fas fa-user-tag"></i> Role
                </span>
                <div class="info-value"><?php echo htmlspecialchars($userData['role_name']); ?></div>
            </div>

            <?php if ($userData['department_name']): ?>
                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-building"></i> Department
                    </span>
                    <div class="info-value"><?php echo htmlspecialchars($userData['department_name']); ?></div>
                </div>
            <?php endif; ?>

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
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔐 Initializing Change Password Form...');

    const form = document.getElementById('passwordForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const strengthIndicator = document.getElementById('passwordStrength');
    const strengthProgress = document.getElementById('strengthProgress');
    const strengthText = document.getElementById('strengthText');

    // Password toggle functionality
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

    // Password strength checker
    function checkPasswordStrength(pwd) {
        let strength = 0;
        const requirements = {
            length: pwd.length >= <?php echo PASSWORD_MIN_LENGTH; ?>,
            uppercase: /[A-Z]/.test(pwd),
            lowercase: /[a-z]/.test(pwd),
            number: /[0-9]/.test(pwd),
            special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(pwd)
        };

        // Update requirement indicators
        Object.keys(requirements).forEach(req => {
            const element = document.getElementById(`req-${req}`);
            if (requirements[req]) {
                element.classList.add('met');
                strength++;
            } else {
                element.classList.remove('met');
            }
        });

        return strength;
    }

    function updatePasswordStrength(pwd) {
        if (!pwd) {
            strengthIndicator.classList.remove('show');
            return;
        }

        strengthIndicator.classList.add('show');
        const strength = checkPasswordStrength(pwd);

        // Remove all strength classes
        strengthProgress.classList.remove('weak', 'medium', 'strong');
        strengthText.classList.remove('weak', 'medium', 'strong');

        // Update strength indicator
        if (strength <= 2) {
            strengthProgress.classList.add('weak');
            strengthText.classList.add('weak');
            strengthText.textContent = 'Weak Password';
        } else if (strength <= 4) {
            strengthProgress.classList.add('medium');
            strengthText.classList.add('medium');
            strengthText.textContent = 'Medium Strength';
        } else {
            strengthProgress.classList.add('strong');
            strengthText.classList.add('strong');
            strengthText.textContent = 'Strong Password';
        }
    }

    // Password confirmation validation
    function validatePassword() {
        if (password.value && confirmPassword.value) {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
                confirmPassword.style.borderColor = 'var(--danger)';
                return false;
            } else {
                confirmPassword.setCustomValidity('');
                confirmPassword.style.borderColor = 'var(--success)';
                return true;
            }
        }
        confirmPassword.setCustomValidity('');
        confirmPassword.style.borderColor = 'var(--border-color)';
        return true;
    }

    // Event listeners
    if (password) {
        password.addEventListener('input', function() {
            updatePasswordStrength(this.value);
            if (confirmPassword.value) {
                validatePassword();
            }
        });
    }

    if (confirmPassword) {
        confirmPassword.addEventListener('input', validatePassword);
    }

    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validatePassword()) {
                e.preventDefault();
                return false;
            }

            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Changing Password...</span>';
        });
    }

    console.log('✅ Change Password Form initialized successfully');
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
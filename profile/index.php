<?php

/**
 * GateWey Requisition Management System
 * User Profile Page - Dasher UI Enhanced
 * 
 * File: profile/index.php
 * Purpose: View and edit user profile information
 * 
 * Features:
 * - View profile information
 * - Edit profile inline
 * - Change password
 * - View account statistics
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';

// Get current user ID
$userId = Session::getUserId();

if (!$userId) {
    Session::setFlash('error', 'User session not found.');
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Initialize objects
$user = new User();
$requisition = new Requisition();

// Get user data
$userData = $user->getById($userId);

if (!$userData) {
    Session::setFlash('error', 'User profile not found.');
    header('Location: ' . BASE_URL . '/dashboard/');
    exit;
}

// Handle form submissions
$errors = [];
$success = '';
$isEditMode = isset($_GET['edit']) && $_GET['edit'] === 'true';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Verify CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Sanitize inputs
        $firstName = Sanitizer::string($_POST['first_name'] ?? '');
        $lastName = Sanitizer::string($_POST['last_name'] ?? '');
        $phone = Sanitizer::string($_POST['phone'] ?? '');
        
        // Validate
        if (empty($firstName)) $errors[] = 'First name is required.';
        if (empty($lastName)) $errors[] = 'Last name is required.';
        
        if (empty($errors)) {
            $updateData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone
            ];
            
            $result = $user->update($userId, $updateData);
            
            if ($result['success']) {
                // Update session data
                Session::set('user_first_name', $firstName);
                Session::set('user_last_name', $lastName);
                
                // Also update nested user array if it exists
                $userSession = Session::get('user');
                if ($userSession) {
                    $userSession['first_name'] = $firstName;
                    $userSession['last_name'] = $lastName;
                    Session::set('user', $userSession);
                }
                
                Session::setFlash('success', 'Profile updated successfully!');
                header('Location: index.php');
                exit;
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    // Verify CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate using Validator class
        $validator = new Validator();
        $validator->setData([
            'current_password' => $currentPassword,
            'new_password' => $newPassword,
            'confirm_password' => $confirmPassword
        ]);
        
        $validator->setRules([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|match:new_password'
        ]);
        
        if (!$validator->validate()) {
            $validatorErrors = $validator->getErrors();
            foreach ($validatorErrors as $field => $fieldErrors) {
                foreach ($fieldErrors as $error) {
                    $errors[] = $error;
                }
            }
        }
        
        // Verify current password
        if (empty($errors) && !$user->verifyPassword($userId, $currentPassword)) {
            $errors[] = 'Current password is incorrect.';
        }
        
        // Check if new password is same as current
        if (empty($errors) && $newPassword === $currentPassword) {
            $errors[] = 'New password must be different from current password.';
        }
        
        if (empty($errors)) {
            $result = $user->changePassword($userId, $newPassword);
            
            if ($result['success']) {
                Session::setFlash('success', 'Password changed successfully!');
                header('Location: index.php');
                exit;
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

// Get user statistics
try {
    // Total requisitions
    $totalRequisitions = $requisition->countByUser($userId);
    
    // Pending approvals (if user is an approver)
    $pendingCount = 0;
    if (in_array($userData['role_id'], [ROLE_LINE_MANAGER, ROLE_MANAGING_DIRECTOR, ROLE_FINANCE_MANAGER])) {
        $pending = $requisition->getPendingForApprover($userId);
        $pendingCount = count($pending);
    }
    
    // Get total amount spent (completed requisitions)
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as total_spent
        FROM requisitions
        WHERE user_id = ? AND status = 'completed'
    ");
    $stmt->execute([$userId]);
    $totalSpent = $stmt->fetchColumn();
    
} catch (Exception $e) {
    error_log("Error fetching user statistics: " . $e->getMessage());
    $totalRequisitions = 0;
    $pendingCount = 0;
    $totalSpent = 0;
}

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'My Profile';

// Generate user initials
$initials = strtoupper(substr($userData['first_name'], 0, 1) . substr($userData['last_name'], 0, 1));
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Dasher UI Enhanced Styles (matching view.php) -->
<style>
    /* Main Layout Grid */
    .profile-view-grid {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: var(--spacing-6);
        margin: 0 auto;
    }

    @media (max-width: 992px) {
        .profile-view-grid {
            grid-template-columns: 1fr;
        }

        .profile-sidebar {
            order: -1;
        }
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
    }

    .section-icon.success {
        background: rgba(var(--success-rgb), 0.1);
    }

    .section-icon.warning {
        background: rgba(var(--warning-rgb), 0.1);
    }

    .section-icon.info {
        background: rgba(var(--info-rgb), 0.1);
    }

    .section-title {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-1);
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

    /* Profile Header */
    .profile-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-5);
        padding: var(--spacing-6);
        background: var(--bg-subtle);
        border-radius: var(--border-radius-lg);
        border: 1px solid var(--border-color);
        margin-bottom: var(--spacing-5);
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: var(--border-radius-full);
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: var(--font-weight-bold);
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
    }

    .profile-info {
        flex: 1;
    }

    .profile-name {
        font-size: 1.75rem;
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-2) 0;
    }

    .profile-role {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-2);
        padding: var(--spacing-2) var(--spacing-3);
        background: rgba(var(--info-rgb), 0.1);
        color: var(--info);
        border-radius: var(--border-radius);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
        margin-bottom: var(--spacing-3);
    }

    .profile-meta {
        display: flex;
        gap: var(--spacing-4);
        flex-wrap: wrap;
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
    }

    .profile-meta-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    /* Detail Grid */
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-4);
    }

    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .detail-item {
        padding: var(--spacing-3);
        background: var(--bg-subtle);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
    }

    .detail-label {
        display: block;
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: var(--spacing-2);
        font-weight: var(--font-weight-medium);
    }

    .detail-value {
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    /* Form Groups */
    .form-group {
        margin-bottom: var(--spacing-4);
    }

    .form-label {
        display: block;
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
        margin-bottom: var(--spacing-2);
    }

    .form-label.required::after {
        content: '*';
        color: var(--danger);
        margin-left: var(--spacing-1);
    }

    .form-control {
        width: 100%;
        padding: var(--spacing-3);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        background: var(--bg-input);
        color: var(--text-primary);
        font-size: var(--font-size-base);
        transition: var(--transition-fast);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--bg-hover);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
    }

    .form-control:disabled {
        background: var(--bg-disabled);
        cursor: not-allowed;
        opacity: 0.6;
    }

    .form-text {
        font-size: var(--font-size-xs);
        color: var(--text-muted);
        margin-top: var(--spacing-2);
    }

    /* Input Group */
    .input-group {
        display: flex;
        gap: 0;
    }

    .input-group .form-control {
        border-radius: var(--border-radius) 0 0 var(--border-radius);
    }

    .input-group .btn {
        border-radius: 0 var(--border-radius) var(--border-radius) 0;
        border-left: none;
    }

    /* Password Strength Indicator */
    .password-strength {
        margin-top: var(--spacing-2);
    }

    .strength-bar {
        height: 4px;
        background: var(--border-color);
        border-radius: var(--border-radius);
        overflow: hidden;
        margin-bottom: var(--spacing-2);
    }

    .strength-bar-fill {
        height: 100%;
        transition: var(--transition-base);
        width: 0%;
    }

    .strength-bar-fill.weak {
        background: var(--danger);
        width: 33%;
    }

    .strength-bar-fill.medium {
        background: var(--warning);
        width: 66%;
    }

    .strength-bar-fill.strong {
        background: var(--success);
        width: 100%;
    }

    .strength-text {
        font-size: var(--font-size-xs);
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

    /* Action Buttons */
    .form-actions {
        display: flex;
        gap: var(--spacing-3);
        margin-top: var(--spacing-5);
        padding-top: var(--spacing-5);
        border-top: 1px solid var(--border-color);
    }

    /* Sidebar */
    .profile-sidebar {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-4);
    }

    .sidebar-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        transition: var(--theme-transition);
    }

    .sidebar-card:hover {
        box-shadow: var(--shadow-sm);
    }

    .sidebar-card-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
    }

    .sidebar-card-header i {
        font-size: var(--font-size-lg);
    }

    .sidebar-card-header h6 {
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
    }

    .sidebar-card-body {
        padding: var(--spacing-4);
    }

    /* Stats Items */
    .stats-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--spacing-3) 0;
        border-bottom: 1px solid var(--border-light);
    }

    .stats-item:last-child {
        border-bottom: none;
    }

    .stats-label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .stats-value {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
    }

    .stats-value.highlight {
        color: var(--success);
    }

    /* Quick Actions */
    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-2);
    }

    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        padding: var(--spacing-3);
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        color: var(--text-primary);
        text-decoration: none;
        transition: var(--transition-fast);
        font-size: var(--font-size-sm);
    }

    .quick-action-btn:hover {
        background: var(--bg-hover);
        transform: translateX(4px);
    }

    .quick-action-btn i {
        width: 20px;
        text-align: center;
    }

    /* Edit Mode Toggle */
    .edit-mode-notice {
        background: rgba(var(--info-rgb), 0.1);
        border: 1px solid var(--info);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-4);
        margin-bottom: var(--spacing-5);
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .edit-mode-notice i {
        font-size: 1.5rem;
        color: var(--info);
    }

    .edit-mode-notice-content {
        flex: 1;
    }

    .edit-mode-notice-content h6 {
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-1) 0;
    }

    .edit-mode-notice-content p {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0;
    }

    /* Mobile Optimizations */
    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
            padding: var(--spacing-5);
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            font-size: 2rem;
        }

        .profile-name {
            font-size: 1.5rem;
        }

        .profile-meta {
            justify-content: center;
        }

        .section-header {
            padding: var(--spacing-4);
        }

        .section-body {
            padding: var(--spacing-4);
        }

        .section-icon {
            width: 36px;
            height: 36px;
            font-size: var(--font-size-base);
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .btn {
            width: 100%;
        }
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">
                <i class="fas fa-user-circle me-2"></i>
                My Profile
            </h1>
            <p class="content-description">View and manage your account information</p>
        </div>
        <div class="content-actions">
            <?php if (!$isEditMode): ?>
                <a href="?edit=true" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>
                    <span>Edit Profile</span>
                </a>
            <?php else: ?>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>
                    <span>Cancel Editing</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Success!</div>
            <div class="alert-message"><?php echo htmlspecialchars($successMessage); ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Error</div>
            <div class="alert-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Please correct the following errors:</div>
            <div class="alert-message">
                <ul style="margin: var(--spacing-2) 0 0 var(--spacing-4); padding: 0;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Edit Mode Notice -->
<?php if ($isEditMode): ?>
    <div class="edit-mode-notice">
        <i class="fas fa-info-circle"></i>
        <div class="edit-mode-notice-content">
            <h6>Edit Mode Active</h6>
            <p>You can now modify your profile information below. Click "Save Changes" when done or "Cancel" to discard changes.</p>
        </div>
    </div>
<?php endif; ?>

<!-- Profile Header -->
<div class="profile-header">
    <div class="user-avatar">
        <?php echo $initials; ?>
    </div>
    <div class="profile-info">
        <h2 class="profile-name">
            <?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?>
        </h2>
        <div class="profile-role">
            <i class="fas fa-user-tag"></i>
            <?php echo htmlspecialchars($userData['role_name']); ?>
        </div>
        <div class="profile-meta">
            <div class="profile-meta-item">
                <i class="fas fa-envelope"></i>
                <?php echo htmlspecialchars($userData['email']); ?>
            </div>
            <?php if (!empty($userData['phone'])): ?>
                <div class="profile-meta-item">
                    <i class="fas fa-phone"></i>
                    <?php echo htmlspecialchars($userData['phone']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($userData['department_name'])): ?>
                <div class="profile-meta-item">
                    <i class="fas fa-building"></i>
                    <?php echo htmlspecialchars($userData['department_name']); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="profile-view-grid">
    <!-- Main Content Section -->
    <div class="profile-main-content">
        <!-- Personal Information Card -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-header-content">
                    <div class="section-icon primary">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="section-title">
                        <h5>Personal Information</h5>
                        <p><?php echo $isEditMode ? 'Update your name and phone number' : 'Your basic account details'; ?></p>
                    </div>
                </div>
                <?php if (!$isEditMode): ?>
                    <a href="?edit=true" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                <?php endif; ?>
            </div>
            <div class="section-body">
                <?php if (!$isEditMode): ?>
                    <!-- View Mode -->
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label class="detail-label">First Name</label>
                            <div class="detail-value"><?php echo htmlspecialchars($userData['first_name']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <label class="detail-label">Last Name</label>
                            <div class="detail-value"><?php echo htmlspecialchars($userData['last_name']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <label class="detail-label">Email Address</label>
                            <div class="detail-value"><?php echo htmlspecialchars($userData['email']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <label class="detail-label">Phone Number</label>
                            <div class="detail-value">
                                <?php echo !empty($userData['phone']) ? htmlspecialchars($userData['phone']) : '<span style="color: var(--text-muted);">Not provided</span>'; ?>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <label class="detail-label">Role</label>
                            <div class="detail-value"><?php echo htmlspecialchars($userData['role_name']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <label class="detail-label">Department</label>
                            <div class="detail-value">
                                <?php echo !empty($userData['department_name']) ? htmlspecialchars($userData['department_name']) : '<span style="color: var(--text-muted);">Not assigned</span>'; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Edit Mode -->
                    <form method="POST" action="">
                        <?php echo Session::csrfField(); ?>
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="detail-grid">
                            <div class="form-group">
                                <label for="first_name" class="form-label required">First Name</label>
                                <input type="text" 
                                       id="first_name" 
                                       name="first_name" 
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($userData['first_name']); ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name" class="form-label required">Last Name</label>
                                <input type="text" 
                                       id="last_name" 
                                       name="last_name" 
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($userData['last_name']); ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($userData['email']); ?>"
                                       disabled
                                       readonly>
                                <div class="form-text">
                                    <i class="fas fa-lock"></i>
                                    Email cannot be changed (used for login)
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>"
                                       placeholder="+234 XXX XXX XXXX">
                                <div class="form-text">Optional contact number</div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Account Information Card -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-header-content">
                    <div class="section-icon info">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="section-title">
                        <h5>Account Information</h5>
                        <p>System-managed account details</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label class="detail-label">Account Status</label>
                        <div class="detail-value">
                            <?php if ($userData['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <label class="detail-label">Member Since</label>
                        <div class="detail-value">
                            <?php echo format_date($userData['created_at'], 'F d, Y'); ?>
                        </div>
                    </div>
                    
                    <?php if ($userData['last_login']): ?>
                        <div class="detail-item">
                            <label class="detail-label">Last Login</label>
                            <div class="detail-value">
                                <?php echo format_date($userData['last_login'], 'M d, Y \a\t h:i A'); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="detail-item">
                        <label class="detail-label">User ID</label>
                        <div class="detail-value" style="font-family: monospace; color: var(--text-muted);">
                            #<?php echo str_pad($userData['id'], 6, '0', STR_PAD_LEFT); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Card -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-header-content">
                    <div class="section-icon warning">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="section-title">
                        <h5>Security Settings</h5>
                        <p>Manage your password and security</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <form method="POST" action="" id="passwordForm">
                    <?php echo Session::csrfField(); ?>
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label for="current_password" class="form-label required">Current Password</label>
                        <div class="input-group">
                            <input type="password" 
                                   id="current_password" 
                                   name="current_password" 
                                   class="form-control"
                                   placeholder="Enter your current password"
                                   required>
                            <button class="btn btn-ghost" type="button" id="toggleCurrentPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password" class="form-label required">New Password</label>
                        <div class="input-group">
                            <input type="password" 
                                   id="new_password" 
                                   name="new_password" 
                                   class="form-control"
                                   placeholder="Enter new password"
                                   required>
                            <button class="btn btn-ghost" type="button" id="toggleNewPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength" style="display: none;">
                            <div class="strength-bar">
                                <div class="strength-bar-fill" id="strengthBar"></div>
                            </div>
                            <div class="strength-text" id="strengthText"></div>
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i>
                            Must be at least 8 characters long
                        </div>
                    </div>
                    
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
                        <div class="form-text" id="passwordMatch"></div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo me-2"></i>Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar Section -->
    <div class="profile-sidebar">
        <!-- Statistics Card -->
        <div class="sidebar-card">
            <div class="sidebar-card-header">
                <i class="fas fa-chart-line"></i>
                <h6>Statistics</h6>
            </div>
            <div class="sidebar-card-body">
                <div class="stats-item">
                    <span class="stats-label">
                        <i class="fas fa-file-alt"></i>Total Requisitions
                    </span>
                    <span class="stats-value"><?php echo number_format($totalRequisitions); ?></span>
                </div>
                
                <?php if ($pendingCount > 0): ?>
                    <div class="stats-item">
                        <span class="stats-label">
                            <i class="fas fa-clock"></i>Pending Approvals
                        </span>
                        <span class="stats-value" style="color: var(--warning);">
                            <?php echo number_format($pendingCount); ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <div class="stats-item">
                    <span class="stats-label">
                        <i class="fas fa-money-bill-wave"></i>Total Spent
                    </span>
                    <span class="stats-value highlight">
                        ₦<?php echo number_format((float)$totalSpent, 2); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="sidebar-card">
            <div class="sidebar-card-header">
                <i class="fas fa-bolt"></i>
                <h6>Quick Actions</h6>
            </div>
            <div class="sidebar-card-body">
                <div class="quick-actions">
                    <a href="<?php echo BASE_URL; ?>/requisitions/create.php" class="quick-action-btn">
                        <i class="fas fa-plus"></i>
                        <span>Create Requisition</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="quick-action-btn">
                        <i class="fas fa-list"></i>
                        <span>My Requisitions</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/reports/generate.php" class="quick-action-btn">
                        <i class="fas fa-chart-bar"></i>
                        <span>View Reports</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/dashboard/" class="quick-action-btn">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Security Tips Card -->
        <div class="sidebar-card">
            <div class="sidebar-card-header">
                <i class="fas fa-lightbulb"></i>
                <h6>Security Tips</h6>
            </div>
            <div class="sidebar-card-body">
                <div style="font-size: var(--font-size-sm); color: var(--text-secondary); line-height: 1.6;">
                    <div style="margin-bottom: var(--spacing-3);">
                        <i class="fas fa-check-circle" style="color: var(--success);"></i>
                        Use a strong, unique password
                    </div>
                    <div style="margin-bottom: var(--spacing-3);">
                        <i class="fas fa-check-circle" style="color: var(--success);"></i>
                        Never share your password
                    </div>
                    <div style="margin-bottom: var(--spacing-3);">
                        <i class="fas fa-check-circle" style="color: var(--success);"></i>
                        Change password regularly
                    </div>
                    <div>
                        <i class="fas fa-check-circle" style="color: var(--success);"></i>
                        Always logout when done
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    // Namespace for profile page
    const ProfileManager = {
        init() {
            this.setupPasswordToggle();
            this.setupPasswordStrength();
            this.setupPasswordMatch();
        },

        setupPasswordToggle() {
            const toggleButtons = [
                { btn: 'toggleCurrentPassword', input: 'current_password' },
                { btn: 'toggleNewPassword', input: 'new_password' },
                { btn: 'toggleConfirmPassword', input: 'confirm_password' }
            ];

            toggleButtons.forEach(({ btn, input }) => {
                const button = document.getElementById(btn);
                const field = document.getElementById(input);

                if (button && field) {
                    button.addEventListener('click', () => {
                        const type = field.type === 'password' ? 'text' : 'password';
                        field.type = type;

                        const icon = button.querySelector('i');
                        icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
                    });
                }
            });
        },

        setupPasswordStrength() {
            const newPassword = document.getElementById('new_password');
            const strengthContainer = document.getElementById('passwordStrength');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            if (newPassword && strengthContainer) {
                newPassword.addEventListener('input', (e) => {
                    const password = e.target.value;

                    if (password.length === 0) {
                        strengthContainer.style.display = 'none';
                        return;
                    }

                    strengthContainer.style.display = 'block';

                    const strength = this.calculatePasswordStrength(password);

                    strengthBar.className = 'strength-bar-fill ' + strength.level;
                    strengthText.className = 'strength-text ' + strength.level;
                    strengthText.textContent = 'Password strength: ' + strength.text;
                });
            }
        },

        calculatePasswordStrength(password) {
            let score = 0;

            // Length
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;

            // Character variety
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^a-zA-Z0-9]/.test(password)) score++;

            if (score <= 2) {
                return { level: 'weak', text: 'Weak' };
            } else if (score <= 4) {
                return { level: 'medium', text: 'Medium' };
            } else {
                return { level: 'strong', text: 'Strong' };
            }
        },

        setupPasswordMatch() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            const matchText = document.getElementById('passwordMatch');

            if (newPassword && confirmPassword && matchText) {
                const checkMatch = () => {
                    if (confirmPassword.value.length === 0) {
                        matchText.textContent = '';
                        matchText.style.color = '';
                        return;
                    }

                    if (newPassword.value === confirmPassword.value) {
                        matchText.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match';
                        matchText.style.color = 'var(--success)';
                    } else {
                        matchText.innerHTML = '<i class="fas fa-times-circle"></i> Passwords do not match';
                        matchText.style.color = 'var(--danger)';
                    }
                };

                newPassword.addEventListener('input', checkMatch);
                confirmPassword.addEventListener('input', checkMatch);
            }
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => ProfileManager.init());
    } else {
        ProfileManager.init();
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
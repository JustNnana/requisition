<?php
/**
 * GateWey Requisition Management System
 * Reset User Two-Factor Authentication
 *
 * File: admin/users/reset-2fa.php
 * Purpose: Allow super admin to reset user's 2FA
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../../middleware/auth-check.php';

// Check if user is super admin
if (!in_array(Session::getUserRoleId(), CAN_MANAGE_USERS)) {
    Session::setFlash('error', 'Unauthorized access.');
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

// Get user ID from encrypted query parameter
$userId = get_encrypted_id();

if (!$userId) {
    Session::setFlash('error', 'Invalid user ID.');
    header('Location: list.php');
    exit;
}

// Initialize classes
$userModel = new User();
$twofa = new TwoFactorAuth();

// Get user data
$user = $userModel->getById($userId);

if (!$user) {
    Session::setFlash('error', 'User not found.');
    header('Location: list.php');
    exit;
}

// Check if user is trying to reset their own 2FA
$isSelfReset = ($userId == Session::getUserId());

// Only super admin (role_id = 1) can reset their own 2FA
if ($isSelfReset && Session::getUserRoleId() != 1) {
    Session::setFlash('error', 'You cannot reset your own 2FA. Please contact a super administrator.');
    header('Location: list.php');
    exit;
}

// Get user's current 2FA status
$twofaStatus = $twofa->get2FAStatus($userId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        Session::setFlash('error', 'Invalid request. Please try again.');
    } else {
        // Reset 2FA
        if ($twofa->disable2FA($userId, Session::getUserId())) {
            if ($isSelfReset) {
                // Super admin is resetting their own 2FA - log them out for security
                Session::setFlash('success', 'Your 2FA has been reset. Please log in again and set up 2FA.');
                Session::destroy();
                header('Location: ' . BASE_URL . '/auth/login.php');
                exit;
            } else {
                // Admin is resetting another user's 2FA
                Session::setFlash('success', "Two-factor authentication has been reset for {$user['first_name']} {$user['last_name']}. They will be required to set it up again on next login.");
                header('Location: list.php');
                exit;
            }
        } else {
            $error = 'Failed to reset two-factor authentication. Please try again.';
        }
    }
}

$pageTitle = 'Reset Two-Factor Authentication';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<style>
    .reset-card {
        max-width: 600px;
        margin: 0 auto;
        background: var(--bg-card);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }

    .reset-header {
        background: var(--bg-warning);
        border-left: 4px solid var(--warning);
        padding: var(--spacing-6);
        display: flex;
        align-items: center;
        gap: var(--spacing-4);
    }

    .reset-icon {
        width: 64px;
        height: 64px;
        background: var(--warning);
        color: white;
        border-radius: var(--border-radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-3xl);
        flex-shrink: 0;
    }

    .reset-header-content h1 {
        margin: 0 0 var(--spacing-1) 0;
        font-size: var(--font-size-2xl);
        color: var(--warning);
    }

    .reset-header-content p {
        margin: 0;
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
    }

    .reset-body {
        padding: var(--spacing-6);
    }

    .user-info-card {
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-5);
        margin-bottom: var(--spacing-6);
    }

    .user-info-row {
        display: flex;
        align-items: center;
        gap: var(--spacing-4);
        margin-bottom: var(--spacing-3);
    }

    .user-info-row:last-child {
        margin-bottom: 0;
    }

    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: var(--border-radius-full);
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-bold);
        flex-shrink: 0;
    }

    .user-details h3 {
        margin: 0 0 var(--spacing-1) 0;
        font-size: var(--font-size-lg);
        color: var(--text-primary);
    }

    .user-details p {
        margin: 0;
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .twofa-status {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        padding: var(--spacing-3) var(--spacing-4);
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-6);
    }

    .twofa-status.enabled {
        border-color: var(--success);
        background: var(--bg-success);
    }

    .twofa-status.disabled {
        border-color: var(--text-muted);
        background: var(--bg-subtle);
    }

    .twofa-status-icon {
        font-size: var(--font-size-xl);
    }

    .twofa-status.enabled .twofa-status-icon {
        color: var(--success);
    }

    .twofa-status.disabled .twofa-status-icon {
        color: var(--text-muted);
    }

    .warning-box {
        background: var(--bg-warning);
        border-left: 4px solid var(--warning);
        padding: var(--spacing-4);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-6);
    }

    .warning-box h4 {
        margin: 0 0 var(--spacing-2) 0;
        color: var(--warning);
        font-size: var(--font-size-base);
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .warning-box ul {
        margin: 0;
        padding-left: var(--spacing-5);
        color: var(--text-primary);
    }

    .warning-box li {
        margin-bottom: var(--spacing-2);
        font-size: var(--font-size-sm);
    }

    .form-actions {
        display: flex;
        gap: var(--spacing-3);
        margin-top: var(--spacing-6);
    }

    .btn {
        padding: var(--spacing-3) var(--spacing-6);
        border-radius: var(--border-radius);
        font-weight: var(--font-weight-semibold);
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-2);
        cursor: pointer;
        border: none;
        transition: all 0.2s;
    }

    .btn-warning {
        background: var(--warning);
        color: white;
    }

    .btn-warning:hover {
        background: var(--warning-dark);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .btn-secondary {
        background: var(--bg-subtle);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }

    .btn-secondary:hover {
        background: var(--bg-hover);
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="content-title">Reset Two-Factor Authentication</h1>
            <nav class="content-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?php echo BASE_URL; ?>/dashboard/" class="breadcrumb-link">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?php echo BASE_URL; ?>/admin/users/list.php" class="breadcrumb-link">Users</a>
                    </li>
                    <li class="breadcrumb-item active">Reset 2FA</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Error Message -->
<?php if (isset($error)): ?>
    <div class="alert alert-error alert-dismissible">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Reset Card -->
<div class="reset-card">
    <div class="reset-header">
        <div class="reset-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="reset-header-content">
            <h1>Reset Two-Factor Authentication</h1>
            <p>This will disable 2FA and require the user to set it up again</p>
        </div>
    </div>

    <div class="reset-body">
        <!-- User Info -->
        <div class="user-info-card">
            <div class="user-info-row">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                    <p>
                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                        <span style="margin: 0 var(--spacing-2);">•</span>
                        <i class="fas fa-user-tag"></i> <?php echo htmlspecialchars($user['role_name']); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Current 2FA Status -->
        <div class="twofa-status <?php echo $twofaStatus['enabled'] ? 'enabled' : 'disabled'; ?>">
            <div class="twofa-status-icon">
                <?php if ($twofaStatus['enabled']): ?>
                    <i class="fas fa-check-circle"></i>
                <?php else: ?>
                    <i class="fas fa-times-circle"></i>
                <?php endif; ?>
            </div>
            <div>
                <strong>Current Status:</strong>
                <?php if ($twofaStatus['enabled']): ?>
                    2FA Enabled
                    <?php if ($twofaStatus['verified_at']): ?>
                        (Setup on <?php echo date('M d, Y', strtotime($twofaStatus['verified_at'])); ?>)
                    <?php endif; ?>
                <?php else: ?>
                    2FA Not Enabled
                <?php endif; ?>
            </div>
        </div>

        <?php if ($twofaStatus['enabled']): ?>
            <?php if ($isSelfReset): ?>
                <!-- Self-Reset Warning (Super Admin Only) -->
                <div class="warning-box" style="background: var(--bg-danger); border-left-color: var(--danger);">
                    <h4 style="color: var(--danger);">
                        <i class="fas fa-exclamation-circle"></i>
                        ⚠️ Emergency Self-Reset Warning
                    </h4>
                    <ul>
                        <li><strong>You are about to reset YOUR OWN 2FA!</strong></li>
                        <li>You will be logged out immediately after reset</li>
                        <li>You must set up 2FA again before you can access the system</li>
                        <li>Only use this if you have lost access to your authenticator app</li>
                        <li>Consider using the SQL emergency reset method if you're not currently logged in</li>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Warning Box -->
            <div class="warning-box">
                <h4>
                    <i class="fas fa-exclamation-triangle"></i>
                    Important Information
                </h4>
                <ul>
                    <li>This action will immediately disable two-factor authentication for this <?php echo $isSelfReset ? 'account' : 'user'; ?></li>
                    <li><?php echo $isSelfReset ? 'You' : 'The user'; ?> will be required to set up 2FA again on <?php echo $isSelfReset ? 'your' : 'their'; ?> next login</li>
                    <li><?php echo $isSelfReset ? 'Your' : 'Their'; ?> existing authenticator app configuration will no longer work</li>
                    <li>This action will be logged in the audit trail</li>
                </ul>
            </div>

            <!-- Confirmation Form -->
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Session::generateCsrfToken(); ?>">

                <div class="form-actions">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-sync-alt"></i>
                        Reset 2FA for This User
                    </button>
                    <a href="<?php echo BASE_URL; ?>/admin/users/list.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                This user does not have two-factor authentication enabled yet.
            </div>

            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/admin/users/list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Back to Users List
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

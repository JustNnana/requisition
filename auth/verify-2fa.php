<?php
/**
 * GateWey Requisition Management System
 * Two-Factor Authentication Verification Page
 *
 * File: auth/verify-2fa.php
 * Purpose: Verify 2FA code during login
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check if user is in temp login state
if (!isset($_SESSION['temp_user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$userId = $_SESSION['temp_user_id'];

// Initialize 2FA
$twofa = new TwoFactorAuth();

// Get user 2FA status
$status = $twofa->get2FAStatus($userId);

// If 2FA not enabled, redirect to setup
if (!$status['enabled'] || !$status['has_secret']) {
    header('Location: ' . BASE_URL . '/auth/setup-2fa.php');
    exit;
}

// Get user info
$db = Database::getInstance();
$user = $db->fetchOne(
    "SELECT id, email, first_name, last_name, role_id, department_id, is_active
     FROM users
     WHERE id = ?",
    [$userId]
);

if (!$user || !$user['is_active']) {
    Session::destroy();
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = Sanitizer::string($_POST['code'] ?? '');

    if (empty($code)) {
        $error = 'Please enter the 6-digit code from your authenticator app.';
    } elseif (!preg_match('/^\d{6}$/', $code)) {
        $error = 'Code must be 6 digits.';
    } else {
        // Verify the code
        if ($twofa->verifyCode($status['secret'], $code)) {
            // Complete the login using Session::login()
            Session::login($user);

            // Clear temp session
            unset($_SESSION['temp_user_id']);

            // Update last login
            $db->execute("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);

            // Log login
            if (ENABLE_AUDIT_LOG) {
                $auditLog = new AuditLog();
                $auditLog->log($user['id'], AUDIT_USER_LOGIN, 'User logged in successfully with 2FA');
            }

            // Redirect to dashboard
            header('Location: ' . BASE_URL . '/dashboard/index.php');
            exit;
        } else {
            $error = 'Invalid code. Please try again.';

            // Log failed attempt
            if (ENABLE_AUDIT_LOG) {
                $auditLog = new AuditLog();
                $auditLog->log($user['id'], 'twofa_verification_failed', 'Failed 2FA verification attempt');
            }
        }
    }
}

$pageTitle = 'Verify Two-Factor Authentication';
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . APP_NAME; ?></title>

    <!-- Dasher UI CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dasher-ui.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/theme.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: var(--spacing-4);
        }

        .auth-card {
            background: var(--bg-primary);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }

        .auth-header {
            background: var(--primary);
            color: white;
            padding: var(--spacing-8) var(--spacing-6);
            text-align: center;
        }

        .auth-header h1 {
            margin: 0 0 var(--spacing-2) 0;
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
        }

        .auth-header p {
            margin: 0;
            opacity: 0.9;
            font-size: var(--font-size-sm);
        }

        .auth-body {
            padding: var(--spacing-8) var(--spacing-6);
        }

        .user-info {
            text-align: center;
            margin-bottom: var(--spacing-6);
            padding: var(--spacing-4);
            background: var(--bg-subtle);
            border-radius: var(--border-radius);
        }

        .user-avatar {
            width: 64px;
            height: 64px;
            border-radius: var(--border-radius-full);
            background: var(--primary);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-2xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-3);
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

        .form-group {
            margin-bottom: var(--spacing-6);
        }

        .form-label {
            display: block;
            margin-bottom: var(--spacing-2);
            font-weight: var(--font-weight-medium);
            color: var(--text-primary);
            text-align: center;
        }

        .form-control {
            width: 100%;
            padding: var(--spacing-3);
            font-size: var(--font-size-2xl);
            text-align: center;
            letter-spacing: 8px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            background: var(--bg-input);
            color: var(--text-primary);
            font-family: monospace;
            font-weight: var(--font-weight-semibold);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        .btn {
            display: inline-block;
            padding: var(--spacing-3) var(--spacing-6);
            font-size: var(--font-size-base);
            font-weight: var(--font-weight-semibold);
            text-align: center;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-text {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            margin-top: var(--spacing-3);
        }

        .btn-text:hover {
            background: var(--bg-subtle);
            color: var(--text-primary);
            transform: none;
        }

        .alert {
            padding: var(--spacing-4);
            border-radius: var(--border-radius);
            margin-bottom: var(--spacing-4);
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
        }

        .alert-error {
            background: var(--bg-danger);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .help-text {
            text-align: center;
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            margin-top: var(--spacing-4);
        }

        .help-text i {
            color: var(--info);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="fas fa-shield-alt"></i> Verify Identity</h1>
                <p>Enter the code from your authenticator app</p>
            </div>

            <div class="auth-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                    </div>
                    <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                    <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="code" class="form-label">
                            <i class="fas fa-hashtag"></i> Authentication Code
                        </label>
                        <input
                            type="text"
                            id="code"
                            name="code"
                            class="form-control"
                            placeholder="000000"
                            maxlength="6"
                            pattern="\d{6}"
                            inputmode="numeric"
                            autocomplete="off"
                            required
                            autofocus
                        >
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Verify & Login
                    </button>

                    <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-text">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </form>

                <div class="help-text">
                    <i class="fas fa-info-circle"></i> Open your authenticator app and enter the 6-digit code
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-format and limit input to digits only
        const codeInput = document.getElementById('code');
        codeInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 6);
        });

        // Auto-submit when 6 digits entered
        codeInput.addEventListener('input', function() {
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
    </script>
</body>
</html>

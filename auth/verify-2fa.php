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
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dasher-variables.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dasher-core-styles.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-primary);
            padding: 20px;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
        }

        .login-card {
            background: var(--bg-card);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, #EC3338 0%, #ec3339bd 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .login-logo {
            width: 90px;
            height: 90px;
            background: #ffffff;
            border-radius: var(--border-radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
        }

        .logo-img {
            height: 40px;
            width: auto;
        }

        .login-header h1 {
            font-size: var(--font-size-2xl);
            margin-bottom: var(--spacing-2);
            font-weight: var(--font-weight-bold);
            color: white;
        }

        .login-header p {
            font-size: var(--font-size-sm);
            opacity: 0.9;
            margin: 0;
            color: white;
        }

        .login-body {
            padding: 40px 30px;
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
            font-family: 'Courier New', monospace;
            font-weight: var(--font-weight-semibold);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-soft);
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
            transition: var(--transition-normal);
            width: 100%;
            text-decoration: none;
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
            display: block;
        }

        .btn-text:hover {
            background: var(--bg-subtle);
            color: var(--text-primary);
            transform: none;
        }

        .alert {
            padding: var(--spacing-4);
            border-radius: var(--border-radius);
            margin-bottom: var(--spacing-5);
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

        /* Theme Toggle */
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-full);
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition-normal);
            box-shadow: var(--shadow-md);
            z-index: 1000;
        }

        .theme-toggle:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-lg);
        }

        .theme-toggle i {
            font-size: 1.2rem;
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <!-- Theme Toggle -->
    <div class="theme-toggle" id="themeToggle" title="Toggle Dark/Light Mode">
        <i class="fas fa-moon"></i>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo" class="logo-img">
                </div>
                <h1><i class="fas fa-shield-alt"></i> Verify Identity</h1>
                <p>Enter the code from your authenticator app</p>
            </div>

            <div class="login-body">
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

        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const themeIcon = themeToggle.querySelector('i');

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);

        // Toggle theme on click
        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });

        // Update icon based on theme
        function updateThemeIcon(theme) {
            if (theme === 'dark') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
        }
    </script>
</body>
</html>

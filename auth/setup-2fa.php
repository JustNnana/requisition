<?php
/**
 * GateWey Requisition Management System
 * Two-Factor Authentication Setup Page
 *
 * File: auth/setup-2fa.php
 * Purpose: First-time 2FA setup for users
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check if user is logged in (partial login for 2FA setup)
if (!Session::isLoggedIn() && !isset($_SESSION['temp_user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Get user ID (either from full session or temp session)
$userId = Session::getUserId() ?? $_SESSION['temp_user_id'];

// Initialize 2FA
$twofa = new TwoFactorAuth();

// Check if 2FA is already enabled
$status = $twofa->get2FAStatus($userId);
if ($status['enabled']) {
    // Already setup, redirect to verification
    header('Location: ' . BASE_URL . '/auth/verify-2fa.php');
    exit;
}

// Generate new secret if not exists
if (!isset($_SESSION['temp_2fa_secret'])) {
    $_SESSION['temp_2fa_secret'] = $twofa->generateSecret();
}

$secret = $_SESSION['temp_2fa_secret'];

// Get user email for QR code
$db = Database::getInstance();
$user = $db->fetchOne("SELECT email, first_name, last_name FROM users WHERE id = ?", [$userId]);

if (!$user) {
    Session::destroy();
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Generate QR code URL
$qrCodeUrl = $twofa->getQRCodeUrl($secret, $user['email']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = Sanitizer::string($_POST['code'] ?? '');

    if (empty($code)) {
        $error = 'Please enter the 6-digit code from your authenticator app.';
    } elseif (!preg_match('/^\d{6}$/', $code)) {
        $error = 'Code must be 6 digits.';
    } else {
        // Verify the code
        if ($twofa->verifyCode($secret, $code)) {
            // Enable 2FA for user
            if ($twofa->enable2FA($userId, $secret)) {
                // Clear temp session data
                unset($_SESSION['temp_2fa_secret']);
                unset($_SESSION['temp_user_id']);

                // If this was during login, complete the login
                if (!Session::isLoggedIn()) {
                    // Re-fetch user data and complete login
                    $userData = $db->fetchOne(
                        "SELECT id, email, first_name, last_name, role_id, department_id, is_active
                         FROM users
                         WHERE id = ?",
                        [$userId]
                    );

                    if ($userData && $userData['is_active']) {
                        // Complete the login using Session::login()
                        Session::login($userData);

                        // Log login
                        if (ENABLE_AUDIT_LOG) {
                            $auditLog = new AuditLog();
                            $auditLog->log($userData['id'], AUDIT_USER_LOGIN, 'User logged in successfully with 2FA');
                        }
                    }
                }

                Session::setFlash('success', 'Two-factor authentication has been enabled successfully!');
                header('Location: ' . BASE_URL . '/dashboard/index.php');
                exit;
            } else {
                $error = 'Failed to enable two-factor authentication. Please try again.';
            }
        } else {
            $error = 'Invalid code. Please try again.';
        }
    }
}

$pageTitle = 'Setup Two-Factor Authentication';
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
            max-width: 500px;
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

        .qr-container {
            text-align: center;
            margin-bottom: var(--spacing-6);
            padding: var(--spacing-6);
            background: var(--bg-subtle);
            border-radius: var(--border-radius);
            border: 2px dashed var(--border-color);
        }

        .qr-container img {
            max-width: 200px;
            height: auto;
            margin-bottom: var(--spacing-4);
        }

        .secret-key {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: var(--spacing-3);
            font-family: monospace;
            font-size: var(--font-size-lg);
            letter-spacing: 2px;
            word-break: break-all;
            margin-top: var(--spacing-3);
            text-align: center;
        }

        .instructions {
            background: var(--bg-info);
            border-left: 4px solid var(--info);
            padding: var(--spacing-4);
            border-radius: var(--border-radius);
            margin-bottom: var(--spacing-6);
        }

        .instructions h3 {
            margin: 0 0 var(--spacing-3) 0;
            color: var(--info);
            font-size: var(--font-size-lg);
        }

        .instructions ol {
            margin: 0;
            padding-left: var(--spacing-5);
        }

        .instructions li {
            margin-bottom: var(--spacing-2);
            color: var(--text-primary);
        }

        .form-group {
            margin-bottom: var(--spacing-6);
        }

        .form-label {
            display: block;
            margin-bottom: var(--spacing-2);
            font-weight: var(--font-weight-medium);
            color: var(--text-primary);
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

        .app-badges {
            display: flex;
            gap: var(--spacing-3);
            justify-content: center;
            margin-top: var(--spacing-4);
            flex-wrap: wrap;
        }

        .app-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-2);
            padding: var(--spacing-2) var(--spacing-3);
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: var(--font-size-xs);
            color: var(--text-secondary);
        }

        .app-badge i {
            font-size: var(--font-size-base);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="fas fa-shield-alt"></i> Setup 2FA</h1>
                <p>Secure your account with two-factor authentication</p>
            </div>

            <div class="auth-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="instructions">
                    <h3><i class="fas fa-info-circle"></i> Setup Instructions</h3>
                    <ol>
                        <li>Download an authenticator app (Google Authenticator, Authy, Microsoft Authenticator, etc.)</li>
                        <li>Scan the QR code below with your authenticator app</li>
                        <li>Enter the 6-digit code from your app to verify setup</li>
                    </ol>
                </div>

                <div class="qr-container">
                    <p style="margin: 0 0 var(--spacing-3) 0; font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                        Scan this QR Code
                    </p>
                    <img src="<?php echo htmlspecialchars($qrCodeUrl); ?>" alt="QR Code">
                    <p style="margin: var(--spacing-3) 0 0 0; font-size: var(--font-size-sm); color: var(--text-secondary);">
                        Can't scan? Enter this secret manually:
                    </p>
                    <div class="secret-key"><?php echo htmlspecialchars($secret); ?></div>

                    <div class="app-badges">
                        <span class="app-badge">
                            <i class="fab fa-google"></i> Google Authenticator
                        </span>
                        <span class="app-badge">
                            <i class="fas fa-key"></i> Authy
                        </span>
                        <span class="app-badge">
                            <i class="fab fa-microsoft"></i> Microsoft Authenticator
                        </span>
                    </div>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="code" class="form-label">
                            <i class="fas fa-hashtag"></i> Enter 6-Digit Code
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
                        <i class="fas fa-check-circle"></i> Verify & Enable 2FA
                    </button>
                </form>
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

<?php
/**
 * GateWey Requisition Management System
 * Password Reset Page
 * 
 * File: auth/reset-password.php
 * Purpose: Reset password with token verification
 */

// Define app access
define('APP_ACCESS', true);

// Include configuration
require_once '../config/config.php';

// Initialize session
Session::init();

// Check if already logged in
$auth = new Auth();
if ($auth->isAuthenticated()) {
    header('Location: ' . $auth->getIntendedUrl());
    exit;
}

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    Session::setFlash('error', 'Invalid reset link.');
    header('Location: forgot-password.php');
    exit;
}

// Verify token
$tokenData = $auth->verifyResetToken($token);

if (!$tokenData['success']) {
    Session::setFlash('error', $tokenData['message']);
    header('Location: forgot-password.php');
    exit;
}

$userData = $tokenData['data'];

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Get form data
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
            $error = $errors['password'][0] ?? $errors['confirm_password'][0] ?? 'Please correct the errors.';
        } else {
            // Reset password
            $result = $auth->resetPassword($token, $password);
            
            if ($result['success']) {
                Session::setFlash('success', 'Password reset successful! Please login with your new password.');
                header('Location: login.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Page title
$pageTitle = 'Reset Password';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Dasher UI CSS -->
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dasher-variables.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dasher-core-styles.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #00a76f 0%, #008865 100%);
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
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            background: linear-gradient(135deg, #00a76f 0%, #008865 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
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
        
        .user-info {
            background: rgba(255, 255, 255, 0.1);
            padding: var(--spacing-3);
            border-radius: var(--border-radius);
            margin-top: var(--spacing-3);
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }
        
        .user-info i {
            font-size: 1.2rem;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .alert {
            padding: var(--spacing-4);
            border-radius: var(--border-radius);
            margin-bottom: var(--spacing-5);
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-error {
            background-color: var(--danger-light);
            color: var(--danger-800);
            border: 1px solid var(--danger-200);
        }
        
        .alert i {
            font-size: 1.2rem;
        }
        
        .form-group {
            margin-bottom: var(--spacing-5);
        }
        
        .form-label {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            margin-bottom: var(--spacing-2);
            color: var(--text-primary);
            font-weight: var(--font-weight-medium);
            font-size: var(--font-size-sm);
        }
        
        .form-label i {
            color: var(--primary);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            transition: var(--transition-fast);
        }
        
        .input-group .form-control {
            padding-left: 45px;
            padding-right: 45px;
            height: 50px;
            font-size: var(--font-size-base);
        }
        
        .input-group .form-control:focus + i {
            color: var(--primary);
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 5px;
            transition: var(--transition-fast);
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        .password-requirements {
            margin-top: var(--spacing-3);
            padding: var(--spacing-3);
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            font-size: var(--font-size-xs);
        }
        
        .password-requirements h4 {
            font-size: var(--font-size-sm);
            margin-bottom: var(--spacing-2);
            color: var(--text-primary);
        }
        
        .password-requirements ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .password-requirements li {
            padding: var(--spacing-1) 0;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }
        
        .password-requirements li i {
            width: 16px;
            font-size: 0.8rem;
        }
        
        .password-requirements li.valid {
            color: var(--success);
        }
        
        .password-requirements li.valid i {
            color: var(--success);
        }
        
        .btn-submit {
            width: 100%;
            height: 50px;
            font-size: var(--font-size-base);
            font-weight: var(--font-weight-semibold);
            border-radius: var(--border-radius);
            background: linear-gradient(135deg, #00a76f 0%, #008865 100%);
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 167, 111, 0.3);
        }
        
        .login-footer {
            text-align: center;
            padding: 20px 30px;
            background: var(--bg-secondary);
            border-top: 1px solid var(--border-color);
        }
        
        .login-footer p {
            margin: 0;
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
        }
        
        .theme-toggle-wrapper {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        
        .theme-toggle {
            width: 45px;
            height: 45px;
            border-radius: var(--border-radius-full);
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(15deg) scale(1.1);
        }
        
        @media (max-width: 576px) {
            .login-header, .login-body {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: var(--font-size-xl);
            }
            
            .login-logo {
                width: 60px;
                height: 60px;
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Theme Toggle -->
    <div class="theme-toggle-wrapper">
        <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
            <i class="fas fa-moon"></i>
        </button>
    </div>
    
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-lock"></i>
                </div>
                <h1>Reset Your Password</h1>
                <p>Create a new strong password</p>
                
                <!-- User Info -->
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($userData['email']); ?></span>
                </div>
            </div>
            
            <!-- Body -->
            <div class="login-body">
                <!-- Error Message -->
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
                <?php endif; ?>
                
                <!-- Form -->
                <form method="POST" action="" id="resetForm">
                    <?php echo Session::csrfField(); ?>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <!-- New Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            New Password
                        </label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                placeholder="Enter new password"
                                required
                                autocomplete="new-password"
                                autofocus
                            >
                            <i class="fas fa-lock"></i>
                            <button type="button" class="password-toggle" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Confirm Password
                        </label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                class="form-control" 
                                id="confirm_password" 
                                name="confirm_password" 
                                placeholder="Confirm new password"
                                required
                                autocomplete="new-password"
                            >
                            <i class="fas fa-lock"></i>
                            <button type="button" class="password-toggle" data-target="confirm_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Password Requirements -->
                    <div class="password-requirements">
                        <h4><i class="fas fa-info-circle"></i> Password Requirements:</h4>
                        <ul id="passwordChecks">
                            <li id="check-length">
                                <i class="fas fa-circle"></i>
                                <span>At least <?php echo PASSWORD_MIN_LENGTH; ?> characters</span>
                            </li>
                            <?php if (PASSWORD_REQUIRE_UPPERCASE): ?>
                            <li id="check-uppercase">
                                <i class="fas fa-circle"></i>
                                <span>One uppercase letter</span>
                            </li>
                            <?php endif; ?>
                            <?php if (PASSWORD_REQUIRE_NUMBER): ?>
                            <li id="check-number">
                                <i class="fas fa-circle"></i>
                                <span>One number</span>
                            </li>
                            <?php endif; ?>
                            <?php if (PASSWORD_REQUIRE_SPECIAL_CHAR): ?>
                            <li id="check-special">
                                <i class="fas fa-circle"></i>
                                <span>One special character</span>
                            </li>
                            <?php endif; ?>
                            <li id="check-match">
                                <i class="fas fa-circle"></i>
                                <span>Passwords match</span>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-submit" id="submitBtn">
                        <i class="fas fa-check"></i> Reset Password
                    </button>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> GateWey Technologies. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <!-- Dasher Theme System -->
    <script src="<?php echo JS_URL; ?>/dasher-theme-system.js"></script>
    
    <!-- Page Scripts -->
    <script>
        // Password toggle functionality
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
        
        // Password validation
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        
        function validatePassword() {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            
            // Length check
            const lengthCheck = document.getElementById('check-length');
            if (password.length >= <?php echo PASSWORD_MIN_LENGTH; ?>) {
                lengthCheck.classList.add('valid');
                lengthCheck.querySelector('i').className = 'fas fa-check-circle';
            } else {
                lengthCheck.classList.remove('valid');
                lengthCheck.querySelector('i').className = 'fas fa-circle';
            }
            
            <?php if (PASSWORD_REQUIRE_UPPERCASE): ?>
            // Uppercase check
            const uppercaseCheck = document.getElementById('check-uppercase');
            if (/[A-Z]/.test(password)) {
                uppercaseCheck.classList.add('valid');
                uppercaseCheck.querySelector('i').className = 'fas fa-check-circle';
            } else {
                uppercaseCheck.classList.remove('valid');
                uppercaseCheck.querySelector('i').className = 'fas fa-circle';
            }
            <?php endif; ?>
            
            <?php if (PASSWORD_REQUIRE_NUMBER): ?>
            // Number check
            const numberCheck = document.getElementById('check-number');
            if (/[0-9]/.test(password)) {
                numberCheck.classList.add('valid');
                numberCheck.querySelector('i').className = 'fas fa-check-circle';
            } else {
                numberCheck.classList.remove('valid');
                numberCheck.querySelector('i').className = 'fas fa-circle';
            }
            <?php endif; ?>
            
            <?php if (PASSWORD_REQUIRE_SPECIAL_CHAR): ?>
            // Special character check
            const specialCheck = document.getElementById('check-special');
            if (/[^A-Za-z0-9]/.test(password)) {
                specialCheck.classList.add('valid');
                specialCheck.querySelector('i').className = 'fas fa-check-circle';
            } else {
                specialCheck.classList.remove('valid');
                specialCheck.querySelector('i').className = 'fas fa-circle';
            }
            <?php endif; ?>
            
            // Match check
            const matchCheck = document.getElementById('check-match');
            if (password && confirm && password === confirm) {
                matchCheck.classList.add('valid');
                matchCheck.querySelector('i').className = 'fas fa-check-circle';
            } else {
                matchCheck.classList.remove('valid');
                matchCheck.querySelector('i').className = 'fas fa-circle';
            }
        }
        
        passwordInput.addEventListener('input', validatePassword);
        confirmInput.addEventListener('input', validatePassword);
        
        // Form submission
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    </script>
</body>
</html>
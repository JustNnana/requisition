<?php
/**
 * GateWey Requisition Management System
 * Login Page
 * 
 * File: auth/login.php
 * Purpose: User login with email and password
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

// Check remember me
$auth->checkRememberMe();
if ($auth->isAuthenticated()) {
    header('Location: ' . $auth->getIntendedUrl());
    exit;
}

// Handle login form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Sanitize input
        $email = Sanitizer::email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        
        // Validate input
        $validator = new Validator();
        $validator->setData([
            'email' => $email,
            'password' => $password
        ]);
        
        $validator->setRules([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (!$validator->validate()) {
            $error = 'Please provide valid email and password.';
        } else {
            // Attempt login
            $result = $auth->login($email, $password, $rememberMe);
            
            if ($result['success']) {
                // Redirect to intended URL (Auth class handles this)
                header('Location: ' . $auth->getIntendedUrl());
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Page title
$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="GateWey Requisition Management System - Login">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Dasher UI CSS -->
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dasher-variables.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dasher-core-styles.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Login Styles -->
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
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-error {
            background-color: var(--danger-light);
            color: var(--danger-800);
            border: 1px solid var(--danger-200);
        }
        
        .alert-success {
            background-color: var(--success-light);
            color: var(--success-800);
            border: 1px solid var(--success-200);
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
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-5);
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }
        
        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary);
        }
        
        .checkbox-wrapper label {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            cursor: pointer;
            margin: 0;
        }
        
        .forgot-link {
            font-size: var(--font-size-sm);
            color: var(--primary);
            text-decoration: none;
            transition: var(--transition-fast);
        }
        
        .forgot-link:hover {
            color: var(--primary-600);
            text-decoration: underline;
        }
        
        .btn-login {
            width: 100%;
            height: 50px;
            font-size: var(--font-size-base);
            font-weight: var(--font-weight-semibold);
            border-radius: var(--border-radius);
            background: linear-gradient(135deg, #00a76f 0%, #008865 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 167, 111, 0.3);
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:active {
            transform: translateY(0);
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
        
        /* Loading state */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.7;
        }
        
        .btn-login.loading i {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .login-header {
                padding: 30px 20px;
            }
            
            .login-body {
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
            
            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-3);
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
            <!-- Login Header -->
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h1>Welcome Back</h1>
                <p>Login to your GateWey account</p>
            </div>
            
<!-- Login Body -->
<div class="login-body">
    <!-- Error Message -->
    <?php 
    // Handle URL error parameters for system-generated errors
    if (!$error && isset($_GET['error'])) {
        switch ($_GET['error']) {
            case 'account_inactive':
                $error = 'Your account has been deactivated. Please contact the administrator.';
                break;
            case 'system_error':
                $error = 'A system error occurred. Please try again or contact support.';
                break;
        }
    }
    
    if ($error): 
    ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo htmlspecialchars($error); ?></span>
    </div>
    <?php endif; ?>
                
                <!-- Success Message -->
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form method="POST" action="" id="loginForm">
                    <?php echo Session::csrfField(); ?>
                    
                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i>
                            Email Address
                        </label>
                        <div class="input-group">
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                placeholder="Enter your email"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                required
                                autocomplete="email"
                                autofocus
                            >
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                            >
                            <i class="fas fa-lock"></i>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Options -->
                    <div class="form-options">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="remember_me" name="remember_me">
                            <label for="remember_me">Remember me</label>
                        </div>
                        <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-login" id="loginBtn">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
            </div>
            
            <!-- Login Footer -->
            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> GateWey Technologies. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <!-- Dasher Theme System -->
    <script src="<?php echo JS_URL; ?>/dasher-theme-system.js"></script>
    
    <!-- Login Page Scripts -->
    <script>
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
        
        // Form submission with loading state
        document.getElementById('loginForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner"></i> Logging in...';
        });
        
        // Auto-focus on email field
        window.addEventListener('load', function() {
            document.getElementById('email')?.focus();
        });
        
        // Clear error message after 5 seconds
        setTimeout(function() {
            const alert = document.querySelector('.alert-error');
            if (alert) {
                alert.style.animation = 'slideUp 0.3s ease-out reverse';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }
        }, 5000);
    </script>
</body>
</html>
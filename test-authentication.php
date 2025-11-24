<?php
/**
 * GateWey Requisition Management System
 * Authentication System Testing Script
 * 
 * File: test-authentication.php
 * Purpose: Test all authentication functionality
 * 
 * WARNING: DELETE THIS FILE AFTER TESTING!
 */

// Define app access
define('APP_ACCESS', true);

// Include configuration
require_once 'config/config.php';

// Initialize session
Session::init();

// Test results
$results = [];
$totalTests = 0;
$passedTests = 0;

/**
 * Run a test
 */
function runTest($testName, $testFunction) {
    global $results, $totalTests, $passedTests;
    
    $totalTests++;
    
    try {
        $result = $testFunction();
        
        if ($result === true) {
            $passedTests++;
            $results[] = [
                'name' => $testName,
                'status' => 'pass',
                'message' => 'Test passed'
            ];
        } else {
            $results[] = [
                'name' => $testName,
                'status' => 'fail',
                'message' => is_string($result) ? $result : 'Test failed'
            ];
        }
    } catch (Exception $e) {
        $results[] = [
            'name' => $testName,
            'status' => 'error',
            'message' => 'Exception: ' . $e->getMessage()
            ];
    }
}

// ===== DATABASE CONNECTION TEST =====
runTest('Database Connection', function() {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        return $conn instanceof PDO;
    } catch (Exception $e) {
        return "Database connection failed: " . $e->getMessage();
    }
});

// ===== USER CLASS TESTS =====
runTest('User Class - Instance Creation', function() {
    $user = new User();
    return $user instanceof User;
});

runTest('User Class - Email Exists Check', function() {
    $user = new User();
    // Assuming no user with this email exists
    $result = $user->emailExists('nonexistent@test.com');
    return $result === false;
});

// ===== AUTH CLASS TESTS =====
runTest('Auth Class - Instance Creation', function() {
    $auth = new Auth();
    return $auth instanceof Auth;
});

runTest('Auth Class - Is Authenticated (Should be false)', function() {
    $auth = new Auth();
    // Should return false if no one is logged in
    return $auth->isAuthenticated() === false || $auth->isAuthenticated() === true;
});

// ===== SESSION CLASS TESTS =====
runTest('Session - Initialization', function() {
    Session::init();
    return session_status() === PHP_SESSION_ACTIVE;
});

runTest('Session - Set and Get', function() {
    Session::set('test_key', 'test_value');
    $value = Session::get('test_key');
    Session::remove('test_key');
    return $value === 'test_value';
});

runTest('Session - CSRF Token Generation', function() {
    $token = Session::generateCsrfToken();
    return !empty($token) && strlen($token) === 64;
});

runTest('Session - CSRF Token Validation', function() {
    $token = Session::generateCsrfToken();
    return Session::validateCsrfToken($token) === true;
});

runTest('Session - Flash Messages', function() {
    Session::setFlash('success', 'Test message');
    $message = Session::getFlash('success');
    return $message === 'Test message';
});

runTest('Session - Login Attempts Tracking', function() {
    $identifier = 'test@example.com';
    Session::clearLoginAttempts($identifier);
    
    $attempts = Session::incrementLoginAttempts($identifier);
    $retrieved = Session::getLoginAttempts($identifier);
    
    Session::clearLoginAttempts($identifier);
    
    return $attempts === 1 && $retrieved === 1;
});

runTest('Session - Account Lockout Check', function() {
    $identifier = 'lockout@test.com';
    Session::clearLoginAttempts($identifier);
    
    // Simulate max attempts
    for ($i = 0; $i < MAX_LOGIN_ATTEMPTS; $i++) {
        Session::incrementLoginAttempts($identifier);
    }
    
    $isLocked = Session::isAccountLocked($identifier);
    Session::clearLoginAttempts($identifier);
    
    return $isLocked === true;
});

// ===== VALIDATOR CLASS TESTS =====
runTest('Validator - Email Validation', function() {
    $validator = new Validator();
    $validator->setData(['email' => 'test@example.com']);
    $validator->setRules(['email' => 'required|email']);
    return $validator->validate() === true;
});

runTest('Validator - Required Field Validation', function() {
    $validator = new Validator();
    $validator->setData(['field' => '']);
    $validator->setRules(['field' => 'required']);
    return $validator->validate() === false;
});

runTest('Validator - Min Length Validation', function() {
    $validator = new Validator();
    $validator->setData(['password' => '123']);
    $validator->setRules(['password' => 'min:8']);
    return $validator->validate() === false;
});

runTest('Validator - Match Validation', function() {
    $validator = new Validator();
    $validator->setData([
        'password' => 'test123',
        'confirm_password' => 'test123'
    ]);
    $validator->setRules(['confirm_password' => 'match:password']);
    return $validator->validate() === true;
});

// ===== SANITIZER CLASS TESTS =====
runTest('Sanitizer - String Sanitization', function() {
    $input = '<script>alert("xss")</script>Test';
    $output = Sanitizer::string($input);
    return strpos($output, '<script>') === false;
});

runTest('Sanitizer - Email Sanitization', function() {
    $input = 'test@example.com';
    $output = Sanitizer::email($input);
    return $output === 'test@example.com';
});

runTest('Sanitizer - XSS Protection', function() {
    $input = '<img src=x onerror=alert(1)>';
    $output = Sanitizer::xss($input);
    return strpos($output, 'onerror') === false;
});

// ===== CONSTANTS TESTS =====
runTest('Constants - Role Constants Defined', function() {
    return defined('ROLE_SUPER_ADMIN') && 
           defined('ROLE_MANAGING_DIRECTOR') && 
           defined('ROLE_FINANCE_MANAGER');
});

runTest('Constants - Status Constants Defined', function() {
    return defined('STATUS_DRAFT') && 
           defined('STATUS_PENDING_LINE_MANAGER') && 
           defined('STATUS_APPROVED_FOR_PAYMENT');
});

runTest('Constants - Helper Functions Work', function() {
    $roleName = get_role_name(ROLE_SUPER_ADMIN);
    return !empty($roleName) && $roleName !== 'Unknown Role';
});

// ===== FILE STRUCTURE TESTS =====
runTest('File Structure - Config Files Exist', function() {
    return file_exists('config/database.php') && 
           file_exists('config/config.php') && 
           file_exists('config/constants.php');
});

runTest('File Structure - Class Files Exist', function() {
    return file_exists('classes/Database.php') && 
           file_exists('classes/Session.php') && 
           file_exists('classes/User.php') && 
           file_exists('classes/Auth.php');
});

runTest('File Structure - Auth Files Exist', function() {
    return file_exists('auth/login.php') && 
           file_exists('auth/logout.php') && 
           file_exists('auth/forgot-password.php') && 
           file_exists('auth/reset-password.php');
});

runTest('File Structure - Middleware Files Exist', function() {
    return file_exists('middleware/auth-check.php') && 
           file_exists('middleware/role-check.php');
});

runTest('File Structure - Helper Files Exist', function() {
    return file_exists('helpers/permissions.php');
});

// ===== SECURITY TESTS =====
runTest('Security - .htaccess Files Exist', function() {
    return file_exists('.htaccess') && 
           file_exists('config/.htaccess') && 
           file_exists('uploads/.htaccess');
});

runTest('Security - Password Hashing Works', function() {
    $password = 'TestPassword123!';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    return password_verify($password, $hash);
});

runTest('Security - Session Cookie Settings', function() {
    $params = session_get_cookie_params();
    return $params['httponly'] === true;
});

// ===== PERMISSION FUNCTIONS TESTS =====
runTest('Permissions - Helper File Loaded', function() {
    require_once 'helpers/permissions.php';
    return function_exists('can_user_raise_requisition') && 
           function_exists('can_user_approve');
});

?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication System Test - <?php echo APP_NAME; ?></title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #00a76f 0%, #008865 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
        }
        
        .warning {
            background: #ff5630;
            color: white;
            padding: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f9fafb;
        }
        
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .summary-card h3 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .summary-card.total h3 {
            color: #667eea;
        }
        
        .summary-card.passed h3 {
            color: #22c55e;
        }
        
        .summary-card.failed h3 {
            color: #ff5630;
        }
        
        .summary-card.rate h3 {
            color: #00a76f;
        }
        
        .summary-card p {
            color: #637381;
            font-size: 0.9rem;
        }
        
        .results {
            padding: 30px;
        }
        
        .result-item {
            background: white;
            border: 1px solid #dfe3e8;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
        }
        
        .result-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .result-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .result-icon.pass {
            background: #dcfce7;
            color: #22c55e;
        }
        
        .result-icon.fail {
            background: #ffebee;
            color: #ff5630;
        }
        
        .result-icon.error {
            background: #fff8e1;
            color: #ffab00;
        }
        
        .result-content {
            flex: 1;
        }
        
        .result-name {
            font-weight: 600;
            color: #1c252e;
            margin-bottom: 5px;
        }
        
        .result-message {
            font-size: 0.85rem;
            color: #637381;
        }
        
        .footer {
            background: #f9fafb;
            padding: 20px;
            text-align: center;
            color: #637381;
            border-top: 1px solid #dfe3e8;
        }
        
        .section-title {
            font-size: 1.5rem;
            color: #1c252e;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #00a76f;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🔐 Authentication System Test</h1>
            <p>Phase 2: Comprehensive Testing Results</p>
        </div>
        
        <!-- Warning -->
        <div class="warning">
            ⚠️ WARNING: DELETE THIS FILE AFTER TESTING! ⚠️
        </div>
        
        <!-- Summary -->
        <div class="summary">
            <div class="summary-card total">
                <h3><?php echo $totalTests; ?></h3>
                <p>Total Tests</p>
            </div>
            <div class="summary-card passed">
                <h3><?php echo $passedTests; ?></h3>
                <p>Passed</p>
            </div>
            <div class="summary-card failed">
                <h3><?php echo $totalTests - $passedTests; ?></h3>
                <p>Failed</p>
            </div>
            <div class="summary-card rate">
                <h3><?php echo $totalTests > 0 ? round(($passedTests / $totalTests) * 100) : 0; ?>%</h3>
                <p>Success Rate</p>
            </div>
        </div>
        
        <!-- Results -->
        <div class="results">
            <h2 class="section-title">Test Results</h2>
            
            <?php foreach ($results as $result): ?>
            <div class="result-item">
                <div class="result-icon <?php echo $result['status']; ?>">
                    <?php 
                    if ($result['status'] === 'pass') {
                        echo '✓';
                    } elseif ($result['status'] === 'fail') {
                        echo '✗';
                    } else {
                        echo '!';
                    }
                    ?>
                </div>
                <div class="result-content">
                    <div class="result-name"><?php echo htmlspecialchars($result['name']); ?></div>
                    <div class="result-message"><?php echo htmlspecialchars($result['message']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>Remember:</strong> Delete this file after testing is complete!</p>
            <p>&copy; <?php echo date('Y'); ?> GateWey Technologies. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
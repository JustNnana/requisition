<?php
/**
 * GateWey Requisition Management System
 * System Settings - Email Configuration & Testing
 * 
 * File: admin/settings/email.php
 * Purpose: Test email configuration and send test emails
 */

// Define access level
define('APP_ACCESS', true);
define('ADMIN_ACCESS', true);

// Include necessary files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/email.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../classes/Sanitizer.php';
require_once __DIR__ . '/../../helpers/permissions.php';

// Start session
Session::start();

// Check authentication and authorization
require_once __DIR__ . '/../../middleware/auth-check.php';
require_once __DIR__ . '/../../middleware/role-check.php';
checkRole(ROLE_SUPER_ADMIN);

// Initialize database
$db = Database::getInstance();

// Initialize variables
$errors = [];
$successMessage = '';
$testResults = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'test_email') {
            // Send test email
            $testEmail = Sanitizer::email($_POST['test_email'] ?? '');
            
            if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please provide a valid email address.';
            } else {
                // Test email sending
                try {
                    // Check if mail function exists
                    if (!function_exists('mail')) {
                        throw new Exception('PHP mail() function is not available on this server.');
                    }
                    
                    // Prepare test email
                    $to = $testEmail;
                    $subject = EMAIL_SUBJECT_PREFIX . ' Email Configuration Test';
                    $message = "
                    <html>
                    <head>
                        <title>Email Test</title>
                    </head>
                    <body>
                        <h2>Email Configuration Test</h2>
                        <p>This is a test email from <strong>" . APP_NAME . "</strong>.</p>
                        <p>If you received this email, your SMTP configuration is working correctly.</p>
                        <hr>
                        <p><strong>Configuration Details:</strong></p>
                        <ul>
                            <li>SMTP Host: " . SMTP_HOST . "</li>
                            <li>SMTP Port: " . SMTP_PORT . "</li>
                            <li>SMTP Secure: " . SMTP_SECURE . "</li>
                            <li>From Email: " . MAIL_FROM_EMAIL . "</li>
                        </ul>
                        <hr>
                        <p style='color: #666; font-size: 12px;'>
                            Test Date: " . date(DATETIME_FORMAT) . "<br>
                            Sent from: " . APP_URL . "
                        </p>
                    </body>
                    </html>
                    ";
                    
                    // Set headers
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_EMAIL . ">" . "\r\n";
                    $headers .= "Reply-To: " . MAIL_REPLYTO_EMAIL . "\r\n";
                    $headers .= "X-Mailer: PHP/" . phpversion();
                    
                    // Send email
                    if (SMTP_ENABLED) {
                        // Note: For production, you should use a proper SMTP library like PHPMailer
                        // This is a simple implementation using PHP's mail() function
                        
                        $mailSent = @mail($to, $subject, $message, $headers);
                        
                        if ($mailSent) {
                            $successMessage = "Test email sent successfully to {$testEmail}. Please check your inbox (and spam folder).";
                            $testResults = [
                                'status' => 'success',
                                'message' => 'Email sent via PHP mail() function',
                                'recipient' => $testEmail,
                                'timestamp' => date(DATETIME_FORMAT)
                            ];
                            
                            // Log successful test
                            if (ENABLE_AUDIT_LOG) {
                                $logSql = "INSERT INTO audit_log (user_id, action_type, action_description, ip_address, created_at)
                                           VALUES (?, ?, ?, ?, NOW())";
                                $logParams = [
                                    Session::getUserId(),
                                    AUDIT_EMAIL_SENT,
                                    "Email configuration test sent to {$testEmail}"
                                ];
                                
                                if (LOG_IP_ADDRESS) {
                                    $logParams[] = $_SERVER['REMOTE_ADDR'] ?? '';
                                } else {
                                    $logParams[] = null;
                                }
                                
                                $db->execute($logSql, $logParams);
                            }
                        } else {
                            $errors[] = 'Failed to send test email. Please check your SMTP configuration and server mail settings.';
                            $testResults = [
                                'status' => 'error',
                                'message' => 'Email sending failed',
                                'details' => error_get_last()
                            ];
                        }
                    } else {
                        $errors[] = 'Email sending is disabled. Please enable SMTP in config/email.php.';
                    }
                    
                } catch (Exception $e) {
                    $errors[] = 'Email test failed: ' . $e->getMessage();
                    $testResults = [
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
            }
        }
    }
}

// Page title
$pageTitle = 'Email Configuration';

// Custom CSS
$customCSS = "
.required-field::after {
    content: ' *';
    color: var(--danger);
}
.config-info {
    background-color: var(--background-secondary, #f8f9fa);
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 8px;
    padding: 1rem;
}
.config-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-color, #dee2e6);
}
.config-item:last-child {
    border-bottom: none;
}
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
}
.status-enabled {
    background-color: #d4edda;
    color: #155724;
}
.status-disabled {
    background-color: #f8d7da;
    color: #721c24;
}
";
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Email Configuration</h1>
            <p class="content-subtitle">Test and verify email settings</p>
        </div>
        <div>
            <a href="<?php echo APP_URL; ?>/admin/index.php" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Success Message -->
<?php if ($successMessage): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($successMessage); ?>
    </div>
<?php endif; ?>

<!-- Error Messages -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Error:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Current Configuration -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-cog"></i> Current SMTP Configuration</h5>
            </div>
            <div class="card-body">
                <div class="config-info">
                    <div class="config-item">
                        <span><strong>SMTP Status:</strong></span>
                        <span class="status-badge <?php echo SMTP_ENABLED ? 'status-enabled' : 'status-disabled'; ?>">
                            <?php echo SMTP_ENABLED ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </div>
                    
                    <div class="config-item">
                        <span><strong>SMTP Host:</strong></span>
                        <span><?php echo htmlspecialchars(SMTP_HOST); ?></span>
                    </div>
                    
                    <div class="config-item">
                        <span><strong>SMTP Port:</strong></span>
                        <span><?php echo htmlspecialchars(SMTP_PORT); ?></span>
                    </div>
                    
                    <div class="config-item">
                        <span><strong>SMTP Secure:</strong></span>
                        <span><?php echo htmlspecialchars(SMTP_SECURE ?: 'None'); ?></span>
                    </div>
                    
                    <div class="config-item">
                        <span><strong>SMTP Authentication:</strong></span>
                        <span class="status-badge <?php echo SMTP_AUTH ? 'status-enabled' : 'status-disabled'; ?>">
                            <?php echo SMTP_AUTH ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </div>
                    
                    <div class="config-item">
                        <span><strong>SMTP Username:</strong></span>
                        <span><?php echo htmlspecialchars(SMTP_USERNAME); ?></span>
                    </div>
                    
                    <div class="config-item">
                        <span><strong>From Email:</strong></span>
                        <span><?php echo htmlspecialchars(MAIL_FROM_EMAIL); ?></span>
                    </div>
                    
                    <div class="config-item">
                        <span><strong>From Name:</strong></span>
                        <span><?php echo htmlspecialchars(MAIL_FROM_NAME); ?></span>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle"></i>
                    <small>To change these settings, edit the <code>config/email.php</code> file.</small>
                </div>
            </div>
        </div>
        
        <!-- Notification Settings -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-bell"></i> Notification Settings</h5>
            </div>
            <div class="card-body">
                <div class="config-info">
                    <div class="config-item">
                        <span>Requisition Submitted</span>
                        <span class="status-badge <?php echo SEND_REQUISITION_SUBMITTED_EMAIL ? 'status-enabled' : 'status-disabled'; ?>">
                            <?php echo SEND_REQUISITION_SUBMITTED_EMAIL ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </div>
                    
                    <div class="config-item">
                        <span>Requisition Approved</span>
                        <span class="status-badge <?php echo SEND_REQUISITION_APPROVED_EMAIL ? 'status-enabled' : 'status-disabled'; ?>">
                            <?php echo SEND_REQUISITION_APPROVED_EMAIL ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </div>
                    
                    <div class="config-item">
                        <span>Requisition Rejected</span>
                        <span class="status-badge <?php echo SEND_REQUISITION_REJECTED_EMAIL ? 'status-enabled' : 'status-disabled'; ?>">
                            <?php echo SEND_REQUISITION_REJECTED_EMAIL ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </div>
                    
                    <div class="config-item">
                        <span>Requisition Paid</span>
                        <span class="status-badge <?php echo SEND_REQUISITION_PAID_EMAIL ? 'status-enabled' : 'status-disabled'; ?>">
                            <?php echo SEND_REQUISITION_PAID_EMAIL ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </div>
                    
                    <div class="config-item">
                        <span>Receipt Uploaded</span>
                        <span class="status-badge <?php echo SEND_RECEIPT_UPLOADED_EMAIL ? 'status-enabled' : 'status-disabled'; ?>">
                            <?php echo SEND_RECEIPT_UPLOADED_EMAIL ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Test Email Form -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-envelope"></i> Send Test Email</h5>
            </div>
            <div class="card-body">
                <?php if (SMTP_ENABLED): ?>
                    <form method="POST" action="" data-loading>
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                        <input type="hidden" name="action" value="test_email">
                        
                        <div class="mb-4">
                            <label for="test_email" class="form-label required-field">Test Email Address</label>
                            <input type="email" 
                                   id="test_email" 
                                   name="test_email" 
                                   class="form-control" 
                                   placeholder="your-email@example.com"
                                   value="<?php echo htmlspecialchars(Session::getUserEmail() ?? ''); ?>"
                                   required>
                            <small class="form-text">A test email will be sent to this address.</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane"></i> Send Test Email
                        </button>
                    </form>
                    
                    <!-- Test Results -->
                    <?php if (!empty($testResults)): ?>
                        <div class="mt-4 p-3 <?php echo ($testResults['status'] === 'success') ? 'alert alert-success' : 'alert alert-error'; ?>">
                            <h6 class="mb-2"><i class="fas fa-<?php echo ($testResults['status'] === 'success') ? 'check-circle' : 'exclamation-circle'; ?>"></i> Test Results</h6>
                            <div style="font-size: var(--font-size-sm);">
                                <strong>Status:</strong> <?php echo htmlspecialchars($testResults['status']); ?><br>
                                <strong>Message:</strong> <?php echo htmlspecialchars($testResults['message']); ?>
                                <?php if (isset($testResults['recipient'])): ?>
                                    <br><strong>Recipient:</strong> <?php echo htmlspecialchars($testResults['recipient']); ?>
                                <?php endif; ?>
                                <?php if (isset($testResults['timestamp'])): ?>
                                    <br><strong>Timestamp:</strong> <?php echo htmlspecialchars($testResults['timestamp']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Email Sending Disabled</strong>
                        <p class="mb-0 mt-2">
                            Email sending is currently disabled. To enable it, set <code>SMTP_ENABLED</code> to <code>true</code> in <code>config/email.php</code>.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Setup Instructions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-book"></i> Setup Instructions</h5>
            </div>
            <div class="card-body">
                <h6>Gmail Configuration:</h6>
                <ol style="font-size: var(--font-size-sm);">
                    <li>Enable 2-Step Verification in your Google Account</li>
                    <li>Generate an App Password at <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a></li>
                    <li>Use the App Password in <code>SMTP_PASSWORD</code></li>
                    <li>Set <code>SMTP_HOST</code> to <code>smtp.gmail.com</code></li>
                    <li>Set <code>SMTP_PORT</code> to <code>587</code></li>
                </ol>
                
                <hr>
                
                <h6>Office 365 Configuration:</h6>
                <ol style="font-size: var(--font-size-sm);">
                    <li>Set <code>SMTP_HOST</code> to <code>smtp.office365.com</code></li>
                    <li>Set <code>SMTP_PORT</code> to <code>587</code></li>
                    <li>Use your regular email and password</li>
                    <li>Ensure SMTP is enabled in Office 365 admin panel</li>
                </ol>
                
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle"></i>
                    <small>For production use, consider using a dedicated SMTP service like SendGrid, Mailgun, or AWS SES.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
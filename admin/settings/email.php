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
$success = '';
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
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                            .content { background: #f8f9fa; padding: 20px; }
                            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                            ul { background: white; padding: 20px; border-radius: 5px; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>Email Configuration Test</h2>
                            </div>
                            <div class='content'>
                                <p>This is a test email from <strong>" . APP_NAME . "</strong>.</p>
                                <p>If you received this email, your SMTP configuration is working correctly.</p>
                                <h3>Configuration Details:</h3>
                                <ul>
                                    <li><strong>SMTP Host:</strong> " . SMTP_HOST . "</li>
                                    <li><strong>SMTP Port:</strong> " . SMTP_PORT . "</li>
                                    <li><strong>SMTP Secure:</strong> " . SMTP_SECURE . "</li>
                                    <li><strong>From Email:</strong> " . MAIL_FROM_EMAIL . "</li>
                                </ul>
                            </div>
                            <div class='footer'>
                                Test Date: " . date(DATETIME_FORMAT) . "<br>
                                Sent from: " . APP_URL . "
                            </div>
                        </div>
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
                        // Note: For production, use PHPMailer library
                        $mailSent = @mail($to, $subject, $message, $headers);
                        
                        if ($mailSent) {
                            $success = "Test email sent successfully to {$testEmail}. Please check your inbox (and spam folder).";
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
                                    "Email configuration test sent to {$testEmail}",
                                    LOG_IP_ADDRESS ? ($_SERVER['REMOTE_ADDR'] ?? '') : null
                                ];
                                
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
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<!-- Enhanced Styles -->
<style>
    /* Form Container & Layout */
    .form-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-6);
        max-width: 1400px;
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
        margin-bottom: var(--spacing-4);
        height: fit-content;
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
        background: var(--primary);
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }

    .form-icon.info {
        background: var(--info);
    }

    .form-icon.success {
        background: var(--success);
    }

    .form-icon.warning {
        background: var(--warning);
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

    /* Configuration Item */
    .config-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--spacing-3) 0;
        border-bottom: 1px solid var(--border-color);
    }

    .config-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .config-item:first-child {
        padding-top: 0;
    }

    .config-label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        font-weight: var(--font-weight-medium);
    }

    .config-value {
        font-size: var(--font-size-sm);
        color: var(--text-primary);
        font-weight: var(--font-weight-medium);
        font-family: 'Courier New', monospace;
    }

    /* Status Badge with Dot */
    .status-badge {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .status-enabled .status-dot {
        background-color: var(--success);
    }

    .status-enabled {
        color: var(--success);
    }

    .status-disabled .status-dot {
        background-color: var(--danger);
    }

    .status-disabled {
        color: var(--danger);
    }

    /* Form Section */
    .form-section {
        margin-bottom: var(--spacing-6);
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .form-section-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        margin-bottom: var(--spacing-4);
        padding-bottom: var(--spacing-3);
        border-bottom: 2px solid var(--border-color);
    }

    .form-section-icon {
        color: var(--primary);
        font-size: var(--font-size-lg);
    }

    .form-section-title {
        margin: 0;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    /* Form Group */
    .form-group {
        margin-bottom: var(--spacing-4);
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        margin-bottom: var(--spacing-2);
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
    }

    .form-label.required::after {
        content: " *";
        color: var(--danger);
    }

    .form-control {
        width: 100%;
        padding: var(--spacing-3) var(--spacing-4);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        background: var(--bg-input);
        color: var(--text-primary);
        font-size: var(--font-size-base);
        transition: var(--theme-transition);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
    }

    .form-text {
        display: block;
        margin-top: var(--spacing-2);
        font-size: var(--font-size-xs);
        color: var(--text-muted);
    }

    /* Test Results Box */
    .test-results {
        margin-top: var(--spacing-4);
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
    }

    .test-results.success {
        background: rgba(var(--success-rgb), 0.1);
        border-color: rgba(var(--success-rgb), 0.2);
    }

    .test-results.error {
        background: rgba(var(--danger-rgb), 0.1);
        border-color: rgba(var(--danger-rgb), 0.2);
    }

    .test-results-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        margin-bottom: var(--spacing-3);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .test-results.success .test-results-header {
        color: var(--success);
    }

    .test-results.error .test-results-header {
        color: var(--danger);
    }

    .test-results-content {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        line-height: 1.8;
    }

    .test-results-content strong {
        color: var(--text-primary);
    }

    /* Alert Banner */
    .alert-banner {
        display: flex;
        align-items: flex-start;
        padding: var(--spacing-3);
        background: rgba(var(--info-rgb), 0.1);
        border: 1px solid rgba(var(--info-rgb), 0.2);
        border-radius: var(--border-radius);
        gap: var(--spacing-2);
        margin-bottom: var(--spacing-4);
    }

    .alert-banner i {
        color: var(--info);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .alert-banner-content {
        flex: 1;
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .alert-banner code {
        background: rgba(var(--info-rgb), 0.1);
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: var(--font-size-xs);
        color: var(--info);
    }

    /* Warning Box */
    .warning-box {
        display: flex;
        align-items: flex-start;
        padding: var(--spacing-4);
        background: rgba(var(--warning-rgb), 0.1);
        border: 1px solid rgba(var(--warning-rgb), 0.2);
        border-radius: var(--border-radius);
        gap: var(--spacing-3);
    }

    .warning-box i {
        color: var(--warning);
        flex-shrink: 0;
        margin-top: 2px;
        font-size: var(--font-size-lg);
    }

    .warning-box-content {
        flex: 1;
    }

    .warning-box-title {
        font-weight: var(--font-weight-semibold);
        color: var(--warning);
        margin-bottom: var(--spacing-2);
    }

    .warning-box-text {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        line-height: 1.6;
        margin: 0;
    }

    /* Info List */
    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-list li {
        padding: var(--spacing-2) 0 var(--spacing-2) var(--spacing-5);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        position: relative;
        line-height: 1.6;
    }

    .info-list li::before {
        content: '•';
        position: absolute;
        left: var(--spacing-3);
        color: var(--primary);
        font-weight: bold;
    }

    .info-list.numbered {
        counter-reset: item;
    }

    .info-list.numbered li {
        counter-increment: item;
    }

    .info-list.numbered li::before {
        content: counter(item) ".";
        color: var(--primary);
    }

    .info-list a {
        color: var(--primary);
        text-decoration: none;
    }

    .info-list a:hover {
        text-decoration: underline;
    }

    .info-list code {
        background: var(--bg-subtle);
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: var(--font-size-xs);
        color: var(--primary);
    }

    /* Info Divider */
    .info-divider {
        margin: var(--spacing-4) 0;
        border: 0;
        border-top: 1px solid var(--border-color);
    }

    /* Section Title */
    .section-title {
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-3);
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
    }

    .alert-message ul {
        margin: var(--spacing-2) 0 0 0;
        padding-left: var(--spacing-4);
    }

    .alert-message li {
        margin-bottom: var(--spacing-1);
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

        .config-item {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-2);
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
            <h1 class="content-title">Email Configuration</h1>
            <nav class="content-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="../../dashboard/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="../index.php">Admin</a>
                    </li>
                    <li class="breadcrumb-item active">Email Configuration</li>
                </ol>
            </nav>
        </div>
        <div class="content-actions">
            <a href="../index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
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
                <strong>The following errors occurred:</strong>
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
        </div>
    </div>
<?php endif; ?>

<!-- Email Configuration -->
<div class="form-container">
    <!-- Left Column: Configuration -->
    <div>
        <!-- SMTP Configuration Card -->
        <div class="form-card">
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="form-header-content">
                    <h2 class="form-title">SMTP Configuration</h2>
                    <p class="form-subtitle">Current email server settings</p>
                </div>
            </div>

            <div class="form-body">
                <div class="config-item">
                    <span class="config-label">SMTP Status</span>
                    <span class="status-badge <?php echo SMTP_ENABLED ? 'status-enabled' : 'status-disabled'; ?>">
                        <span class="status-dot"></span>
                        <?php echo SMTP_ENABLED ? 'Enabled' : 'Disabled'; ?>
                    </span>
                </div>

                <div class="config-item">
                    <span class="config-label">SMTP Host</span>
                    <span class="config-value"><?php echo htmlspecialchars(SMTP_HOST); ?></span>
                </div>

                <div class="config-item">
                    <span class="config-label">SMTP Port</span>
                    <span class="config-value"><?php echo htmlspecialchars(SMTP_PORT); ?></span>
                </div>

                <div class="config-item">
                    <span class="config-label">SMTP Secure</span>
                    <span class="config-value"><?php echo htmlspecialchars(SMTP_SECURE ?: 'None'); ?></span>
                </div>

                <div class="config-item">
                    <span class="config-label">SMTP Authentication</span>
                    <span class="status-badge <?php echo SMTP_AUTH ? 'status-enabled' : 'status-disabled'; ?>">
                        <span class="status-dot"></span>
                        <?php echo SMTP_AUTH ? 'Enabled' : 'Disabled'; ?>
                    </span>
                </div>

                <div class="config-item">
                    <span class="config-label">SMTP Username</span>
                    <span class="config-value"><?php echo htmlspecialchars(SMTP_USERNAME); ?></span>
                </div>

                <div class="config-item">
                    <span class="config-label">From Email</span>
                    <span class="config-value"><?php echo htmlspecialchars(MAIL_FROM_EMAIL); ?></span>
                </div>

                <div class="config-item">
                    <span class="config-label">From Name</span>
                    <span class="config-value"><?php echo htmlspecialchars(MAIL_FROM_NAME); ?></span>
                </div>

                <div class="alert-banner">
                    <i class="fas fa-info-circle"></i>
                    <div class="alert-banner-content">
                        To change these settings, edit the <code>config/email.php</code> file.
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Settings Card -->
        <div class="form-card">
            <div class="form-header">
                <div class="form-icon info">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="form-header-content">
                    <h2 class="form-title">Notification Settings</h2>
                    <p class="form-subtitle">Email notification triggers</p>
                </div>
            </div>

            <div class="form-body">
                <div class="config-item">
                    <span class="config-label">Requisition Submitted</span>
                    <span class="status-badge <?php echo SEND_REQUISITION_SUBMITTED_EMAIL ? 'status-enabled' : 'status-disabled'; ?>">
                        <span class="status-dot"></span>
                        <?php echo SEND_REQUISITION_SUBMITTED_EMAIL ? 'Enabled' : 'Disabled'; ?>
                    </span>
                </div>

                <div class="config-item">
                    <span class="config-label">Requisition Approved</span>
                    <span class="status-badge <?php echo SEND_REQUISITION_APPROVED_EMAIL ? 'status-enabled' : 'status-disabled'; ?>">
                        <span class="status-dot"></span>
                        <?php echo SEND_REQUISITION_APPROVED_EMAIL ? 'Enabled' : 'Disabled'; ?>
                    </span>
                </div>

                <div class="config-item">
                    <span class="config-label">Requisition Rejected</span>
                    <span class="status-badge <?php echo SEND_REQUISITION_REJECTED_EMAIL ? 'status-enabled' : 'status-disabled'; ?>">
                        <span class="status-dot"></span>
                        <?php echo SEND_REQUISITION_REJECTED_EMAIL ? 'Enabled' : 'Disabled'; ?>
                    </span>
                </div>

                <div class="config-item">
                    <span class="config-label">Requisition Paid</span>
                    <span class="status-badge <?php echo SEND_REQUISITION_PAID_EMAIL ? 'status-enabled' : 'status-disabled'; ?>">
                        <span class="status-dot"></span>
                        <?php echo SEND_REQUISITION_PAID_EMAIL ? 'Enabled' : 'Disabled'; ?>
                    </span>
                </div>

                <div class="config-item">
                    <span class="config-label">Receipt Uploaded</span>
                    <span class="status-badge <?php echo SEND_RECEIPT_UPLOADED_EMAIL ? 'status-enabled' : 'status-disabled'; ?>">
                        <span class="status-dot"></span>
                        <?php echo SEND_RECEIPT_UPLOADED_EMAIL ? 'Enabled' : 'Disabled'; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Test Email -->
    <div>
        <!-- Test Email Card -->
        <div class="form-card">
            <div class="form-header">
                <div class="form-icon success">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="form-header-content">
                    <h2 class="form-title">Send Test Email</h2>
                    <p class="form-subtitle">Verify your email configuration</p>
                </div>
            </div>

            <div class="form-body">
                <?php if (SMTP_ENABLED): ?>
                    <form method="POST" action="" id="emailTestForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                        <input type="hidden" name="action" value="test_email">

                        <div class="form-group">
                            <label for="test_email" class="form-label required">Test Email Address</label>
                            <input type="email" 
                                   id="test_email" 
                                   name="test_email" 
                                   class="form-control" 
                                   placeholder="your-email@example.com"
                                   value="<?php echo htmlspecialchars(Session::getUserEmail() ?? ''); ?>"
                                   required>
                            <small class="form-text">A test email will be sent to this address.</small>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg" style="width: 100%;">
                            <i class="fas fa-paper-plane"></i> Send Test Email
                        </button>
                    </form>

                    <!-- Test Results -->
                    <?php if (!empty($testResults)): ?>
                        <div class="test-results <?php echo $testResults['status']; ?>">
                            <div class="test-results-header">
                                <i class="fas fa-<?php echo ($testResults['status'] === 'success') ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                                Test Results
                            </div>
                            <div class="test-results-content">
                                <strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($testResults['status'])); ?><br>
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
                    <div class="warning-box">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div class="warning-box-content">
                            <div class="warning-box-title">Email Sending Disabled</div>
                            <p class="warning-box-text">
                                Email sending is currently disabled. To enable it, set <code>SMTP_ENABLED</code> to <code>true</code> in <code>config/email.php</code>.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Setup Instructions Card -->
        <div class="form-card">
            <div class="form-header">
                <div class="form-icon warning">
                    <i class="fas fa-book"></i>
                </div>
                <div class="form-header-content">
                    <h2 class="form-title">Setup Instructions</h2>
                    <p class="form-subtitle">Configure your email provider</p>
                </div>
            </div>

            <div class="form-body">
                <h6 class="section-title">Gmail Configuration:</h6>
                <ol class="info-list numbered">
                    <li>Enable 2-Step Verification in your Google Account</li>
                    <li>Generate an App Password at <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a></li>
                    <li>Use the App Password in <code>SMTP_PASSWORD</code></li>
                    <li>Set <code>SMTP_HOST</code> to <code>smtp.gmail.com</code></li>
                    <li>Set <code>SMTP_PORT</code> to <code>587</code></li>
                </ol>

                <hr class="info-divider">

                <h6 class="section-title">Office 365 Configuration:</h6>
                <ol class="info-list numbered">
                    <li>Set <code>SMTP_HOST</code> to <code>smtp.office365.com</code></li>
                    <li>Set <code>SMTP_PORT</code> to <code>587</code></li>
                    <li>Use your regular email and password</li>
                    <li>Ensure SMTP is enabled in Office 365 admin panel</li>
                </ol>

                <div class="alert-banner" style="margin-top: var(--spacing-4); margin-bottom: 0;">
                    <i class="fas fa-lightbulb"></i>
                    <div class="alert-banner-content">
                        For production use, consider using a dedicated SMTP service like SendGrid, Mailgun, or AWS SES for better deliverability and tracking.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('📧 Initializing Email Configuration Manager...');

    const form = document.getElementById('emailTestForm');
    const emailInput = document.getElementById('test_email');

    // Real-time email validation
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const value = this.value.trim();
            
            if (value === '') {
                this.style.borderColor = '';
                return;
            }
            
            // Email validation regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (emailRegex.test(value)) {
                this.style.borderColor = 'var(--success)';
            } else {
                this.style.borderColor = 'var(--danger)';
            }
        });
    }

    // Form submission enhancement
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            const emailValue = emailInput.value.trim();
            
            // Validate email before submission
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailValue)) {
                e.preventDefault();
                emailInput.style.borderColor = 'var(--danger)';
                emailInput.focus();
                return;
            }
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Email...';
        });
    }

    console.log('✅ Email Configuration Manager initialized successfully');
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
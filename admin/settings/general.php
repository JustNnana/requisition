<?php
/**
 * GateWey Requisition Management System
 * System Settings - General Settings
 * 
 * File: admin/settings/general.php
 * Purpose: Configure general application settings
 */

// Define access level
define('APP_ACCESS', true);
define('ADMIN_ACCESS', true);

// Include necessary files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
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

// Settings file path
$configFilePath = __DIR__ . '/../../config/config.php';

// Get current settings from constants
$currentSettings = [
    'app_name' => APP_NAME,
    'app_version' => APP_VERSION,
    'app_env' => APP_ENV,
    'app_debug' => APP_DEBUG,
    'app_timezone' => APP_TIMEZONE,
    'date_format' => DATE_FORMAT,
    'time_format' => TIME_FORMAT,
    'records_per_page' => RECORDS_PER_PAGE,
    'max_file_size' => UPLOAD_MAX_SIZE,
    'password_min_length' => PASSWORD_MIN_LENGTH,
    'max_login_attempts' => MAX_LOGIN_ATTEMPTS,
    'login_lockout_time' => LOGIN_LOCKOUT_TIME,
    'enable_audit_log' => ENABLE_AUDIT_LOG,
    'enable_email_notifications' => ENABLE_EMAIL_NOTIFICATIONS
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $newSettings = [
            'app_name' => Sanitizer::string($_POST['app_name'] ?? ''),
            'app_timezone' => Sanitizer::string($_POST['app_timezone'] ?? ''),
            'records_per_page' => Sanitizer::int($_POST['records_per_page'] ?? ''),
            'max_file_size' => Sanitizer::int($_POST['max_file_size'] ?? ''),
            'password_min_length' => Sanitizer::int($_POST['password_min_length'] ?? ''),
            'max_login_attempts' => Sanitizer::int($_POST['max_login_attempts'] ?? ''),
            'login_lockout_time' => Sanitizer::int($_POST['login_lockout_time'] ?? ''),
            'enable_audit_log' => isset($_POST['enable_audit_log']) ? 1 : 0,
            'enable_email_notifications' => isset($_POST['enable_email_notifications']) ? 1 : 0
        ];
        
        // Validate settings
        if (empty($newSettings['app_name'])) {
            $errors[] = 'Application name is required.';
        }
        
        if ($newSettings['records_per_page'] < 5 || $newSettings['records_per_page'] > 100) {
            $errors[] = 'Records per page must be between 5 and 100.';
        }
        
        if ($newSettings['max_file_size'] < 1048576 || $newSettings['max_file_size'] > 10485760) {
            $errors[] = 'Maximum file size must be between 1MB and 10MB.';
        }
        
        if ($newSettings['password_min_length'] < 6 || $newSettings['password_min_length'] > 32) {
            $errors[] = 'Password minimum length must be between 6 and 32 characters.';
        }
        
        if ($newSettings['max_login_attempts'] < 3 || $newSettings['max_login_attempts'] > 10) {
            $errors[] = 'Maximum login attempts must be between 3 and 10.';
        }
        
        if (empty($errors)) {
            // Attempt to update config.php file
            $updateSuccess = false;
            $updateError = '';

            try {
                // Check if config file is writable
                if (!is_writable($configFilePath)) {
                    throw new Exception("Configuration file is not writable. Please check file permissions.");
                }

                // Read the current config file
                $configContent = file_get_contents($configFilePath);
                if ($configContent === false) {
                    throw new Exception("Unable to read configuration file.");
                }

                // Create backup before modifying
                $backupPath = $configFilePath . '.backup.' . date('Y-m-d_H-i-s');
                if (!copy($configFilePath, $backupPath)) {
                    throw new Exception("Unable to create backup of configuration file.");
                }

                // Update configuration values using regex
                $patterns = [
                    'APP_NAME' => "/define\('APP_NAME',\s*'[^']*'\);/",
                    'APP_TIMEZONE' => "/define\('APP_TIMEZONE',\s*'[^']*'\);/",
                    'RECORDS_PER_PAGE' => "/define\('RECORDS_PER_PAGE',\s*\d+\);/",
                    'PASSWORD_MIN_LENGTH' => "/define\('PASSWORD_MIN_LENGTH',\s*\d+\);/",
                    'MAX_LOGIN_ATTEMPTS' => "/define\('MAX_LOGIN_ATTEMPTS',\s*\d+\);/",
                    'LOGIN_LOCKOUT_TIME' => "/define\('LOGIN_LOCKOUT_TIME',\s*\d+\);/",
                    'ENABLE_AUDIT_LOG' => "/define\('ENABLE_AUDIT_LOG',\s*(true|false)\);/",
                    'ENABLE_EMAIL_NOTIFICATIONS' => "/define\('ENABLE_EMAIL_NOTIFICATIONS',\s*(true|false)\);/"
                ];

                $replacements = [
                    'APP_NAME' => "define('APP_NAME', '" . addslashes($newSettings['app_name']) . "');",
                    'APP_TIMEZONE' => "define('APP_TIMEZONE', '" . addslashes($newSettings['app_timezone']) . "');",
                    'RECORDS_PER_PAGE' => "define('RECORDS_PER_PAGE', " . $newSettings['records_per_page'] . ");",
                    'PASSWORD_MIN_LENGTH' => "define('PASSWORD_MIN_LENGTH', " . $newSettings['password_min_length'] . ");",
                    'MAX_LOGIN_ATTEMPTS' => "define('MAX_LOGIN_ATTEMPTS', " . $newSettings['max_login_attempts'] . ");",
                    'LOGIN_LOCKOUT_TIME' => "define('LOGIN_LOCKOUT_TIME', " . $newSettings['login_lockout_time'] . ");",
                    'ENABLE_AUDIT_LOG' => "define('ENABLE_AUDIT_LOG', " . ($newSettings['enable_audit_log'] ? 'true' : 'false') . ");",
                    'ENABLE_EMAIL_NOTIFICATIONS' => "define('ENABLE_EMAIL_NOTIFICATIONS', " . ($newSettings['enable_email_notifications'] ? 'true' : 'false') . ");"
                ];

                // Apply all replacements
                foreach ($patterns as $key => $pattern) {
                    $configContent = preg_replace($pattern, $replacements[$key], $configContent);
                }

                // Write the updated config file
                if (file_put_contents($configFilePath, $configContent) === false) {
                    throw new Exception("Unable to write to configuration file.");
                }

                $updateSuccess = true;

            } catch (Exception $e) {
                $updateError = $e->getMessage();
                error_log("Config update error: " . $e->getMessage());

                // If backup exists, try to restore
                if (isset($backupPath) && file_exists($backupPath)) {
                    copy($backupPath, $configFilePath);
                }
            }

            // Log the settings change
            if (ENABLE_AUDIT_LOG) {
                try {
                    $logSql = "INSERT INTO audit_log (user_id, action, description, ip_address, created_at)
                               VALUES (?, ?, ?, ?, NOW())";
                    $logParams = [
                        Session::getUserId(),
                        'settings_update',
                        $updateSuccess ? 'General settings updated successfully' : 'General settings validation failed: ' . $updateError,
                        $_SERVER['REMOTE_ADDR'] ?? ''
                    ];

                    $db->execute($logSql, $logParams);
                } catch (Exception $e) {
                    // Silently fail if audit log fails - don't block settings update
                    error_log("Audit log error: " . $e->getMessage());
                }
            }

            if ($updateSuccess) {
                $success = 'Settings updated successfully! The changes have been saved to the configuration file. Please note: Some settings may require a page refresh to take full effect.';
            } else {
                $errors[] = "Settings were validated but could not be saved: " . $updateError;
            }
        }
    }
}

// Page title
$pageTitle = 'General Settings';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<!-- Enhanced Styles -->
<style>
    /* Form Container & Layout */
    .form-container {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: var(--spacing-6);
        max-width: 1200px;
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

    /* Form Sections */
    .form-section {
        margin-bottom: var(--spacing-8);
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .form-section-header {
        margin-bottom: var(--spacing-5);
        padding-bottom: var(--spacing-3);
        border-bottom: 1px solid var(--border-color);
    }

    .form-section-title {
        margin: 0 0 var(--spacing-1) 0;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .form-section-title i {
        color: var(--primary);
    }

    .form-section-subtitle {
        margin: 0;
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
    }

    /* Form Rows */
    .form-row {
        margin-bottom: var(--spacing-5);
    }

    .form-row-2-cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-4);
    }

    @media (max-width: 768px) {
        .form-row-2-cols {
            grid-template-columns: 1fr;
        }
    }

    /* Form Groups */
    .form-group {
        position: relative;
    }

    .form-label {
        display: block;
        margin-bottom: var(--spacing-2);
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
    }

    .form-label.required::after {
        content: ' *';
        color: var(--danger);
    }

    .form-control {
        display: block;
        width: 100%;
        padding: var(--spacing-3) var(--spacing-4);
        font-size: var(--font-size-base);
        background: var(--bg-input);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        color: var(--text-primary);
        transition: var(--theme-transition);
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
        outline: none;
    }

    .form-control:disabled,
    .form-control:read-only {
        background-color: var(--bg-subtle);
        cursor: not-allowed;
        opacity: 0.7;
    }

    .form-control::placeholder {
        color: var(--text-muted);
    }

    .form-text {
        margin-top: var(--spacing-2);
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
    }

    .form-text code {
        padding: 2px 6px;
        background: var(--bg-subtle);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-xs);
        color: var(--primary);
    }

    /* Checkbox Styling */
    .checkbox-wrapper {
        position: relative;
        margin-bottom: var(--spacing-3);
    }

    .checkbox-wrapper:last-child {
        margin-bottom: 0;
    }

    .form-checkbox {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .checkbox-label {
        display: flex;
        align-items: flex-start;
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--theme-transition);
        position: relative;
    }

    .checkbox-label::before {
        content: '';
        width: 20px;
        height: 20px;
        border: 2px solid var(--border-color);
        border-radius: 4px;
        margin-right: var(--spacing-3);
        flex-shrink: 0;
        transition: var(--theme-transition);
        margin-top: 2px;
    }

    .form-checkbox:checked + .checkbox-label {
        background: rgba(var(--primary-rgb), 0.1);
        border-color: var(--primary);
    }

    .form-checkbox:checked + .checkbox-label::before {
        border-color: var(--primary);
        background: var(--primary);
    }

    .form-checkbox:checked + .checkbox-label::after {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        left: 20px;
        top: 21px;
        color: white;
        font-size: 12px;
    }

    .checkbox-content {
        flex: 1;
    }

    .checkbox-title {
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-1);
    }

    .checkbox-description {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    /* Form Actions */
    .form-actions {
        margin-top: var(--spacing-8);
        padding-top: var(--spacing-6);
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: var(--spacing-3);
        justify-content: flex-end;
    }

    @media (max-width: 576px) {
        .form-actions {
            flex-direction: column;
        }
    }

    /* Info Card */
    .info-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        height: fit-content;
        position: sticky;
        top: var(--spacing-4);
        margin-bottom: var(--spacing-4);
    }

    .info-header {
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .info-icon {
        color: var(--info);
        font-size: var(--font-size-lg);
    }

    .info-title {
        margin: 0;
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .info-content {
        padding: var(--spacing-4);
    }

    .info-text {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        line-height: 1.6;
        margin-bottom: var(--spacing-4);
    }

    .info-divider {
        margin: var(--spacing-4) 0;
        border: 0;
        border-top: 1px solid var(--border-color);
    }

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
        line-height: 1.5;
    }

    .info-list li::before {
        content: '•';
        position: absolute;
        left: var(--spacing-3);
        color: var(--primary);
        font-weight: bold;
    }

    /* Warning Card */
    .warning-card {
        background: transparent;
        border: 1px solid rgba(var(--warning-rgb), 0.3);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
    }

    .warning-header {
        padding: var(--spacing-4);
        background: rgba(var(--warning-rgb), 0.1);
        border-bottom: 1px solid rgba(var(--warning-rgb), 0.2);
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .warning-icon {
        color: var(--warning);
        font-size: var(--font-size-lg);
    }

    .warning-title {
        margin: 0;
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--warning);
    }

    .warning-content {
        padding: var(--spacing-4);
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
        margin-bottom: var(--spacing-3);
    }

    .alert-message ul {
        margin: 0;
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

        .info-card {
            position: static;
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
            <h1 class="content-title">General Settings</h1>
            <nav class="content-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="../../dashboard/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="../index.php">Admin</a>
                    </li>
                    <li class="breadcrumb-item active">General Settings</li>
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
                <strong>Please correct the following errors:</strong>
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

<!-- Settings Form -->
<div class="form-container">
    <div class="form-card">
        <div class="form-header">
            <div class="form-icon">
                <i class="fas fa-cog"></i>
            </div>
            <div class="form-header-content">
                <h2 class="form-title">System Configuration</h2>
                <p class="form-subtitle">Configure general application settings and preferences</p>
            </div>
        </div>

        <div class="form-body">
            <form method="POST" action="" class="enhanced-form" id="settingsForm">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                
                <!-- Application Settings Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">
                            <i class="fas fa-desktop"></i>
                            Application Settings
                        </h3>
                        <p class="form-section-subtitle">Basic application information and configuration</p>
                    </div>

                    <div class="form-row form-row-2-cols">
                        <div class="form-group">
                            <label for="app_name" class="form-label required">Application Name</label>
                            <input type="text" 
                                   id="app_name" 
                                   name="app_name" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($currentSettings['app_name']); ?>"
                                   placeholder="Enter application name"
                                   required>
                            <div class="form-text">The name displayed throughout the application</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="app_version" class="form-label">Application Version</label>
                            <input type="text" 
                                   id="app_version" 
                                   name="app_version" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($currentSettings['app_version']); ?>"
                                   readonly>
                            <div class="form-text">Current version (read-only)</div>
                        </div>
                    </div>
                    
                    <div class="form-row form-row-2-cols">
                        <div class="form-group">
                            <label for="app_env" class="form-label">Environment</label>
                            <select id="app_env" name="app_env" class="form-control form-select" disabled>
                                <option value="development" <?php echo ($currentSettings['app_env'] === 'development') ? 'selected' : ''; ?>>Development</option>
                                <option value="production" <?php echo ($currentSettings['app_env'] === 'production') ? 'selected' : ''; ?>>Production</option>
                            </select>
                            <div class="form-text">Change this in <code>config/config.php</code> file</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="app_timezone" class="form-label required">Timezone</label>
                            <select id="app_timezone" name="app_timezone" class="form-control form-select" required>
                                <option value="Africa/Lagos" <?php echo ($currentSettings['app_timezone'] === 'Africa/Lagos') ? 'selected' : ''; ?>>Africa/Lagos (Nigeria)</option>
                                <option value="UTC" <?php echo ($currentSettings['app_timezone'] === 'UTC') ? 'selected' : ''; ?>>UTC</option>
                                <option value="Africa/Johannesburg" <?php echo ($currentSettings['app_timezone'] === 'Africa/Johannesburg') ? 'selected' : ''; ?>>Africa/Johannesburg</option>
                                <option value="Africa/Cairo" <?php echo ($currentSettings['app_timezone'] === 'Africa/Cairo') ? 'selected' : ''; ?>>Africa/Cairo</option>
                                <option value="Africa/Nairobi" <?php echo ($currentSettings['app_timezone'] === 'Africa/Nairobi') ? 'selected' : ''; ?>>Africa/Nairobi</option>
                            </select>
                            <div class="form-text">Application timezone for date/time operations</div>
                        </div>
                    </div>
                </div>
                
                <!-- Display Settings Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">
                            <i class="fas fa-eye"></i>
                            Display Settings
                        </h3>
                        <p class="form-section-subtitle">Control how data is displayed in the application</p>
                    </div>
                    
                    <div class="form-row form-row-2-cols">
                        <div class="form-group">
                            <label for="records_per_page" class="form-label required">Records Per Page</label>
                            <input type="number" 
                                   id="records_per_page" 
                                   name="records_per_page" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($currentSettings['records_per_page']); ?>"
                                   min="5" 
                                   max="100" 
                                   required>
                            <div class="form-text">Number of records to display per page (5-100)</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_format" class="form-label">Date Format</label>
                            <input type="text" 
                                   id="date_format" 
                                   name="date_format" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($currentSettings['date_format']); ?>"
                                   readonly>
                            <div class="form-text">Current: <?php echo date($currentSettings['date_format']); ?> (read-only)</div>
                        </div>
                    </div>
                </div>
                
                <!-- Security Settings Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">
                            <i class="fas fa-shield-alt"></i>
                            Security Settings
                        </h3>
                        <p class="form-section-subtitle">Configure security and authentication parameters</p>
                    </div>
                    
                    <div class="form-row form-row-2-cols">
                        <div class="form-group">
                            <label for="password_min_length" class="form-label required">Minimum Password Length</label>
                            <input type="number" 
                                   id="password_min_length" 
                                   name="password_min_length" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($currentSettings['password_min_length']); ?>"
                                   min="6" 
                                   max="32" 
                                   required>
                            <div class="form-text">Minimum characters required for passwords (6-32)</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="max_login_attempts" class="form-label required">Max Login Attempts</label>
                            <input type="number" 
                                   id="max_login_attempts" 
                                   name="max_login_attempts" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($currentSettings['max_login_attempts']); ?>"
                                   min="3" 
                                   max="10" 
                                   required>
                            <div class="form-text">Failed login attempts before lockout (3-10)</div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="login_lockout_time" class="form-label required">Lockout Duration (minutes)</label>
                            <input type="number" 
                                   id="login_lockout_time" 
                                   name="login_lockout_time" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($currentSettings['login_lockout_time'] / 60); ?>"
                                   min="5" 
                                   max="60" 
                                   required>
                            <div class="form-text">Account lockout duration in minutes (5-60)</div>
                        </div>
                    </div>
                </div>
                
                <!-- File Upload Settings Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">
                            <i class="fas fa-file-upload"></i>
                            File Upload Settings
                        </h3>
                        <p class="form-section-subtitle">Configure file upload restrictions</p>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="max_file_size" class="form-label required">Maximum File Size (MB)</label>
                            <input type="number" 
                                   id="max_file_size" 
                                   name="max_file_size" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($currentSettings['max_file_size'] / 1048576); ?>"
                                   min="1" 
                                   max="10" 
                                   step="0.5" 
                                   required>
                            <div class="form-text">Maximum file size for uploads (1-10 MB)</div>
                        </div>
                    </div>
                </div>
                
                <!-- Feature Toggles Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">
                            <i class="fas fa-toggle-on"></i>
                            Feature Toggles
                        </h3>
                        <p class="form-section-subtitle">Enable or disable system features</p>
                    </div>
                    
                    <div class="checkbox-wrapper">
                        <input type="checkbox" 
                               id="enable_audit_log" 
                               name="enable_audit_log" 
                               class="form-checkbox"
                               <?php echo $currentSettings['enable_audit_log'] ? 'checked' : ''; ?>>
                        <label class="checkbox-label" for="enable_audit_log">
                            <div class="checkbox-content">
                                <div class="checkbox-title">Enable Audit Log</div>
                                <div class="checkbox-description">Track all user actions and system changes for security and compliance</div>
                            </div>
                        </label>
                    </div>
                    
                    <div class="checkbox-wrapper">
                        <input type="checkbox" 
                               id="enable_email_notifications" 
                               name="enable_email_notifications" 
                               class="form-checkbox"
                               <?php echo $currentSettings['enable_email_notifications'] ? 'checked' : ''; ?>>
                        <label class="checkbox-label" for="enable_email_notifications">
                            <div class="checkbox-content">
                                <div class="checkbox-title">Enable Email Notifications</div>
                                <div class="checkbox-description">Send automatic email notifications for requisition actions and updates</div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i>
                        <span>Save Settings</span>
                    </button>
                    <a href="../index.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Info Card -->
        <div class="info-card">
            <div class="info-header">
                <i class="fas fa-info-circle info-icon"></i>
                <h3 class="info-title">About Settings</h3>
            </div>
            <div class="info-content">
                <p class="info-text">
                    These settings control various aspects of the application behavior and functionality.
                </p>
                <hr class="info-divider">
                <p class="info-text" style="margin-bottom: 0;">
                    <strong>Note:</strong> Changes to these settings require updating the <code>config/config.php</code> file to take effect permanently.
                </p>
            </div>
        </div>
        
        <!-- Warning Card -->
        <div class="warning-card">
            <div class="warning-header">
                <i class="fas fa-exclamation-triangle warning-icon"></i>
                <h3 class="warning-title">Important</h3>
            </div>
            <div class="warning-content">
                <ul class="info-list">
                    <li>Test settings in development environment first</li>
                    <li>Backup configuration before making changes</li>
                    <li>Some settings require server restart</li>
                    <li>Invalid settings may break the application</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('⚙️ Initializing Settings Form...');

    const form = document.getElementById('settingsForm');

    // Form submission enhancement
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            
            // Convert lockout time to seconds
            const lockoutInput = document.getElementById('login_lockout_time');
            const minutes = parseInt(lockoutInput.value);
            lockoutInput.value = minutes * 60;
            
            // Convert file size to bytes
            const fileSizeInput = document.getElementById('max_file_size');
            const mb = parseFloat(fileSizeInput.value);
            fileSizeInput.value = Math.round(mb * 1048576);
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Validating Settings...</span>';
        });
    }

    // Real-time validation
    const formControls = document.querySelectorAll('.form-control:not([readonly]):not([disabled])');
    formControls.forEach(control => {
        control.addEventListener('input', function() {
            if (this.hasAttribute('required') && this.value.trim()) {
                this.style.borderColor = 'var(--success)';
            } else if (this.hasAttribute('required') && !this.value.trim()) {
                this.style.borderColor = 'var(--border-color)';
            }
        });

        // Reset border on blur if empty
        control.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.style.borderColor = 'var(--border-color)';
            }
        });
    });

    // Range validation
    const rangeInputs = document.querySelectorAll('input[type="number"]');
    rangeInputs.forEach(input => {
        input.addEventListener('input', function() {
            const min = parseInt(this.getAttribute('min'));
            const max = parseInt(this.getAttribute('max'));
            const value = parseInt(this.value);

            if (value < min || value > max) {
                this.style.borderColor = 'var(--danger)';
            } else {
                this.style.borderColor = 'var(--success)';
            }
        });
    });

    console.log('✅ Settings Form initialized successfully');
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
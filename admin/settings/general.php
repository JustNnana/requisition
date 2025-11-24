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
    'max_file_size' => MAX_FILE_SIZE,
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
        // Note: In a production environment, these settings should be stored in a database
        // or a separate configuration file that can be updated programmatically
        // For now, we'll just validate and show a message
        
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
            // In a real application, you would update the config file or database here
            // For this demo, we'll just show a success message
            
            // Log the settings change
            if (ENABLE_AUDIT_LOG) {
                $logSql = "INSERT INTO audit_log (user_id, action_type, action_description, ip_address, created_at)
                           VALUES (?, ?, ?, ?, NOW())";
                $logParams = [
                    Session::getUserId(),
                    AUDIT_SETTINGS_UPDATED,
                    "Updated general system settings"
                ];
                
                if (LOG_IP_ADDRESS) {
                    $logParams[] = $_SERVER['REMOTE_ADDR'] ?? '';
                } else {
                    $logParams[] = null;
                }
                
                $db->execute($logSql, $logParams);
            }
            
            $successMessage = 'Settings validated successfully. Note: To apply these changes, you need to update the config/config.php file manually or implement a dynamic configuration system.';
        }
    }
}

// Page title
$pageTitle = 'General Settings';

// Custom CSS
$customCSS = "
.required-field::after {
    content: ' *';
    color: var(--danger);
}
.settings-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border-color, #dee2e6);
}
.settings-section:last-child {
    border-bottom: none;
}
";
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">General Settings</h1>
            <p class="content-subtitle">Configure general application settings</p>
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
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Settings Form -->
<div class="row">
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">System Configuration</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" data-loading>
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                    
                    <!-- Application Settings -->
                    <div class="settings-section">
                        <h6 class="mb-3"><i class="fas fa-cog"></i> Application Settings</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="app_name" class="form-label required-field">Application Name</label>
                                <input type="text" 
                                       id="app_name" 
                                       name="app_name" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($currentSettings['app_name']); ?>"
                                       required>
                                <small class="form-text">The name displayed throughout the application.</small>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label for="app_version" class="form-label">Application Version</label>
                                <input type="text" 
                                       id="app_version" 
                                       name="app_version" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($currentSettings['app_version']); ?>"
                                       readonly>
                                <small class="form-text">Current version (read-only).</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="app_env" class="form-label">Environment</label>
                                <select id="app_env" name="app_env" class="form-control" disabled>
                                    <option value="development" <?php echo ($currentSettings['app_env'] === 'development') ? 'selected' : ''; ?>>Development</option>
                                    <option value="production" <?php echo ($currentSettings['app_env'] === 'production') ? 'selected' : ''; ?>>Production</option>
                                </select>
                                <small class="form-text">Change this in config/config.php file.</small>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label for="app_timezone" class="form-label required-field">Timezone</label>
                                <select id="app_timezone" name="app_timezone" class="form-control" required>
                                    <option value="Africa/Lagos" <?php echo ($currentSettings['app_timezone'] === 'Africa/Lagos') ? 'selected' : ''; ?>>Africa/Lagos (Nigeria)</option>
                                    <option value="UTC" <?php echo ($currentSettings['app_timezone'] === 'UTC') ? 'selected' : ''; ?>>UTC</option>
                                    <option value="Africa/Johannesburg" <?php echo ($currentSettings['app_timezone'] === 'Africa/Johannesburg') ? 'selected' : ''; ?>>Africa/Johannesburg</option>
                                    <option value="Africa/Cairo" <?php echo ($currentSettings['app_timezone'] === 'Africa/Cairo') ? 'selected' : ''; ?>>Africa/Cairo</option>
                                    <option value="Africa/Nairobi" <?php echo ($currentSettings['app_timezone'] === 'Africa/Nairobi') ? 'selected' : ''; ?>>Africa/Nairobi</option>
                                </select>
                                <small class="form-text">Application timezone for date/time operations.</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Display Settings -->
                    <div class="settings-section">
                        <h6 class="mb-3"><i class="fas fa-desktop"></i> Display Settings</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="records_per_page" class="form-label required-field">Records Per Page</label>
                                <input type="number" 
                                       id="records_per_page" 
                                       name="records_per_page" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($currentSettings['records_per_page']); ?>"
                                       min="5" 
                                       max="100" 
                                       required>
                                <small class="form-text">Number of records to display per page (5-100).</small>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label for="date_format" class="form-label">Date Format</label>
                                <input type="text" 
                                       id="date_format" 
                                       name="date_format" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($currentSettings['date_format']); ?>"
                                       readonly>
                                <small class="form-text">Current: <?php echo date($currentSettings['date_format']); ?> (read-only)</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Security Settings -->
                    <div class="settings-section">
                        <h6 class="mb-3"><i class="fas fa-shield-alt"></i> Security Settings</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="password_min_length" class="form-label required-field">Minimum Password Length</label>
                                <input type="number" 
                                       id="password_min_length" 
                                       name="password_min_length" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($currentSettings['password_min_length']); ?>"
                                       min="6" 
                                       max="32" 
                                       required>
                                <small class="form-text">Minimum characters required for passwords (6-32).</small>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label for="max_login_attempts" class="form-label required-field">Max Login Attempts</label>
                                <input type="number" 
                                       id="max_login_attempts" 
                                       name="max_login_attempts" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($currentSettings['max_login_attempts']); ?>"
                                       min="3" 
                                       max="10" 
                                       required>
                                <small class="form-text">Failed login attempts before lockout (3-10).</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="login_lockout_time" class="form-label required-field">Lockout Duration (minutes)</label>
                                <input type="number" 
                                       id="login_lockout_time" 
                                       name="login_lockout_time" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($currentSettings['login_lockout_time'] / 60); ?>"
                                       min="5" 
                                       max="60" 
                                       required>
                                <small class="form-text">Account lockout duration in minutes (5-60).</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- File Upload Settings -->
                    <div class="settings-section">
                        <h6 class="mb-3"><i class="fas fa-file-upload"></i> File Upload Settings</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="max_file_size" class="form-label required-field">Maximum File Size (MB)</label>
                                <input type="number" 
                                       id="max_file_size" 
                                       name="max_file_size" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($currentSettings['max_file_size'] / 1048576); ?>"
                                       min="1" 
                                       max="10" 
                                       step="0.5" 
                                       required>
                                <small class="form-text">Maximum file size for uploads (1-10 MB).</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Feature Toggles -->
                    <div class="settings-section">
                        <h6 class="mb-3"><i class="fas fa-toggle-on"></i> Feature Toggles</h6>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" 
                                       id="enable_audit_log" 
                                       name="enable_audit_log" 
                                       class="form-check-input"
                                       <?php echo $currentSettings['enable_audit_log'] ? 'checked' : ''; ?>>
                                <label for="enable_audit_log" class="form-check-label">
                                    <strong>Enable Audit Log</strong>
                                    <br>
                                    <small class="text-muted">Track all user actions in the system</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-0">
                            <div class="form-check">
                                <input type="checkbox" 
                                       id="enable_email_notifications" 
                                       name="enable_email_notifications" 
                                       class="form-check-input"
                                       <?php echo $currentSettings['enable_email_notifications'] ? 'checked' : ''; ?>>
                                <label for="enable_email_notifications" class="form-check-label">
                                    <strong>Enable Email Notifications</strong>
                                    <br>
                                    <small class="text-muted">Send email notifications for requisition actions</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="<?php echo APP_URL; ?>/admin/index.php" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Validate Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Help Panel -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle"></i> About Settings
                </h5>
            </div>
            <div class="card-body">
                <p style="font-size: var(--font-size-sm);">
                    These settings control various aspects of the application behavior.
                </p>
                <hr>
                <p style="font-size: var(--font-size-sm);" class="mb-0">
                    <strong>Note:</strong> Changes to these settings require updating the <code>config/config.php</code> file to take effect permanently.
                </p>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Important
                </h5>
            </div>
            <div class="card-body">
                <ul class="mb-0" style="font-size: var(--font-size-sm);">
                    <li>Test settings in development environment first</li>
                    <li>Backup configuration before making changes</li>
                    <li>Some settings require server restart</li>
                    <li>Invalid settings may break the application</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
// Custom JavaScript
$customJS = "
// Convert lockout time to seconds on submit
document.querySelector('form').addEventListener('submit', function(e) {
    const lockoutInput = document.getElementById('login_lockout_time');
    const minutes = parseInt(lockoutInput.value);
    lockoutInput.value = minutes * 60; // Convert to seconds
});

// Convert file size to bytes on submit
document.querySelector('form').addEventListener('submit', function(e) {
    const fileSizeInput = document.getElementById('max_file_size');
    const mb = parseFloat(fileSizeInput.value);
    fileSizeInput.value = Math.round(mb * 1048576); // Convert to bytes
});
";
?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
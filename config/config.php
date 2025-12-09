<?php
/**
 * GateWey Requisition Management System
 * Main Application Configuration
 * 
 * File: config/config.php
 * Purpose: General application settings and constants
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

// Environment Configuration
define('APP_ENV', 'development'); // development | production
define('APP_DEBUG', true); // Set to false in production

// Application Details
define('APP_NAME', 'GateWey Requisition Management');
define('APP_VERSION', '3.0.0');
define('APP_URL', 'https://request.gatewey.com.ng');
define('BASE_URL', 'https://request.gatewey.com.ng');

// Directory Paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CLASSES_PATH', ROOT_PATH . '/classes');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('HELPERS_PATH', ROOT_PATH . '/helpers');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// Upload Directories
define('INVOICES_DIR', UPLOADS_PATH . '/invoices');
define('RECEIPTS_DIR', UPLOADS_PATH . '/receipts');
define('ATTACHMENTS_DIR', UPLOADS_PATH . '/attachments');

// URL Paths
define('ASSETS_URL', APP_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMAGES_URL', ASSETS_URL . '/images');

// Session Configuration
define('SESSION_NAME', 'gatewey_requisition_session');
define('SESSION_LIFETIME', 7200); // 2 hours in seconds
define('SESSION_PATH', '/');
define('SESSION_DOMAIN', ''); // Set domain for production
define('SESSION_SECURE', false); // Set to true with HTTPS
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Strict'); // Strict | Lax | None

// Security Settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_SPECIAL_CHAR', true);
define('PASSWORD_REQUIRE_NUMBER', true);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Pagination Settings
define('RECORDS_PER_PAGE', 20);
define('PAGINATION_RANGE', 5);

// Date & Time Settings
define('APP_TIMEZONE', 'Africa/Lagos'); // Nigeria timezone
define('DATE_FORMAT', 'd/m/Y'); // DD/MM/YYYY
define('TIME_FORMAT', 'H:i:s'); // 24-hour format
define('DATETIME_FORMAT', 'd/m/Y H:i:s');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Email Settings (Basic - detailed in email.php)
define('EMAIL_FROM_NAME', 'GateWey Requisitions');
define('EMAIL_FROM_ADDRESS', 'noreply@gatewey.com');

// Requisition Settings
define('REQUISITION_NUMBER_PREFIX', 'REQ');
define('REQUISITION_NUMBER_LENGTH', 8); // Total length including prefix

// Audit Log Settings
define('ENABLE_AUDIT_LOG', true);
define('LOG_IP_ADDRESS', true);
define('LOG_USER_AGENT', true);

// Notification Settings
define('ENABLE_EMAIL_NOTIFICATIONS', true);
define('NOTIFICATION_QUEUE_ENABLED', false); // Enable for production
define('EMAIL_RETRY_ATTEMPTS', 3);

// Report Settings
define('REPORT_DATE_RANGES', [
    'weekly' => 'Last 7 Days',
    'monthly' => 'Last 30 Days',
    'quarterly' => 'Last 90 Days',
    'yearly' => 'Last 365 Days',
    'custom' => 'Custom Range'
]);

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . '/error.log');
}

// Character Encoding
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Auto-load configuration
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/constants.php';
require_once __DIR__ . '/../helpers/utilities.php';

/**
 * Autoload classes
 */
spl_autoload_register(function($class) {
    $file = CLASSES_PATH . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

/**
 * Get configuration value
 * 
 * @param string $key Configuration key
 * @param mixed $default Default value if not found
 * @return mixed
 */
function config($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

/**
 * Get application URL
 * 
 * @param string $path Path to append
 * @return string
 */
function app_url($path = '') {
    $url = rtrim(APP_URL, '/');
    $path = ltrim($path, '/');
    return $path ? $url . '/' . $path : $url;
}

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @param int $statusCode HTTP status code
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Check if request is AJAX
 * 
 * @return bool
 */
function is_ajax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get current URL
 * 
 * @return string
 */
function current_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Sanitize output for HTML display
 * 
 * @param string $string String to sanitize
 * @return string
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
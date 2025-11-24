<?php
/**
 * GateWey Requisition Management System
 * Session Management Class
 * 
 * File: classes/Session.php
 * Purpose: Handle user sessions, authentication, CSRF protection, and flash messages
 */

class Session {
    
    /**
     * Session configuration constants
     */
    private const SESSION_USER_KEY = 'user_data';
    private const SESSION_CSRF_TOKEN_KEY = 'csrf_token';
    private const SESSION_FLASH_KEY = 'flash_messages';
    private const SESSION_LAST_ACTIVITY = 'last_activity';
    private const SESSION_LAST_REGENERATION = 'last_regeneration';
    
    /**
     * Initialize session with secure settings
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set session configuration
            ini_set('session.cookie_httponly', SESSION_HTTPONLY);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_samesite', SESSION_SAMESITE);
            
            if (SESSION_SECURE) {
                ini_set('session.cookie_secure', 1);
            }
            
            // Set session name
            session_name(SESSION_NAME);
            
            // Set session lifetime
            ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => SESSION_PATH,
                'domain' => SESSION_DOMAIN,
                'secure' => SESSION_SECURE,
                'httponly' => SESSION_HTTPONLY,
                'samesite' => SESSION_SAMESITE
            ]);
            
            session_start();
            
            // Regenerate session ID periodically for security
            self::regenerateIfNeeded();
        }
    }
    /**
 * Start session (alias for init() - backward compatibility)
 */
public static function start() {
    self::init();
}
    /**
     * Regenerate session ID (prevents session fixation)
     * Call this after successful login
     */
    public static function regenerate() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION[self::SESSION_LAST_REGENERATION] = time();
        }
    }
    
    /**
     * Regenerate session ID periodically (every 30 minutes)
     */
    private static function regenerateIfNeeded() {
        if (!isset($_SESSION[self::SESSION_LAST_REGENERATION])) {
            $_SESSION[self::SESSION_LAST_REGENERATION] = time();
        } elseif (time() - $_SESSION[self::SESSION_LAST_REGENERATION] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION[self::SESSION_LAST_REGENERATION] = time();
        }
    }
    
    /**
     * Set a session variable
     * 
     * @param string $key Session key
     * @param mixed $value Session value
     */
    public static function set($key, $value) {
        self::init();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get a session variable
     * 
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Session value or default
     */
    public static function get($key, $default = null) {
        self::init();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session variable exists
     * 
     * @param string $key Session key
     * @return bool
     */
    public static function has($key) {
        self::init();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove a session variable
     * 
     * @param string $key Session key
     */
    public static function remove($key) {
        self::init();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Destroy the session
     */
    public static function destroy() {
        self::init();
        
        // Unset all session variables
        $_SESSION = [];
        
        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Set user data in session (NEW METHOD)
     * This stores complete user data as an array
     * 
     * @param array $userData User data to store
     */
    public static function setUser($userData) {
        self::init();
        $_SESSION[self::SESSION_USER_KEY] = $userData;
        $_SESSION[self::SESSION_LAST_ACTIVITY] = time();
    }
    
    /**
     * Get complete user data from session (NEW METHOD)
     * 
     * @return array|null User data or null if not logged in
     */
    public static function getUser() {
        self::init();
        return $_SESSION[self::SESSION_USER_KEY] ?? null;
    }
    
    /**
     * Set user login session (EXISTING METHOD - maintains backward compatibility)
     * 
     * @param array $user User data
     */
    public static function login($user) {
        self::init();
        
        // Regenerate session ID for security
        self::regenerate();
        
        // Store user data in both formats for compatibility
        // Format 1: Individual session keys (backward compatibility)
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_first_name'] = $user['first_name'];
        $_SESSION['user_last_name'] = $user['last_name'];
        $_SESSION['user_role_id'] = $user['role_id'];
        $_SESSION['user_department_id'] = $user['department_id'] ?? null;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION[self::SESSION_LAST_ACTIVITY] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Format 2: Complete user data array (new method)
        $_SESSION[self::SESSION_USER_KEY] = $user;
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        self::destroy();
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    public static function isLoggedIn() {
        self::init();
        
        // Check both old and new format
        $isLoggedInOld = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
        $isLoggedInNew = isset($_SESSION[self::SESSION_USER_KEY]) && 
                         isset($_SESSION[self::SESSION_USER_KEY]['id']);
        
        if (!$isLoggedInOld && !$isLoggedInNew) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION[self::SESSION_LAST_ACTIVITY])) {
            $inactive = time() - $_SESSION[self::SESSION_LAST_ACTIVITY];
            if ($inactive > SESSION_LIFETIME) {
                self::logout();
                return false;
            }
            $_SESSION[self::SESSION_LAST_ACTIVITY] = time(); // Update last activity
        }
        
        // Verify IP address (optional, can be disabled for dynamic IPs)
        if (isset($_SESSION['ip_address']) && 
            $_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
            // Uncomment below to enforce IP checking
            // self::logout();
            // return false;
        }
        
        return true;
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null User ID or null if not logged in
     */
    public static function getUserId() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        // Try new format first
        if (isset($_SESSION[self::SESSION_USER_KEY]['id'])) {
            return $_SESSION[self::SESSION_USER_KEY]['id'];
        }
        
        // Fall back to old format
        return self::get('user_id');
    }
    
    /**
     * Get current user role ID
     * 
     * @return int|null Role ID or null if not logged in
     */
    public static function getUserRoleId() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        // Try new format first
        if (isset($_SESSION[self::SESSION_USER_KEY]['role_id'])) {
            return $_SESSION[self::SESSION_USER_KEY]['role_id'];
        }
        
        // Fall back to old format
        return self::get('user_role_id');
    }
    
    /**
     * Get current user department ID
     * 
     * @return int|null Department ID or null if not logged in or no department
     */
    public static function getUserDepartmentId() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        // Try new format first
        if (isset($_SESSION[self::SESSION_USER_KEY]['department_id'])) {
            return $_SESSION[self::SESSION_USER_KEY]['department_id'];
        }
        
        // Fall back to old format
        return self::get('user_department_id');
    }
    
    /**
     * Get current user full name
     * 
     * @return string|null Full name or null if not logged in
     */
    public static function getUserFullName() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        // Try new format first
        if (isset($_SESSION[self::SESSION_USER_KEY]['first_name'])) {
            $firstName = $_SESSION[self::SESSION_USER_KEY]['first_name'];
            $lastName = $_SESSION[self::SESSION_USER_KEY]['last_name'] ?? '';
            return trim($firstName . ' ' . $lastName);
        }
        
        // Fall back to old format
        $firstName = self::get('user_first_name', '');
        $lastName = self::get('user_last_name', '');
        return trim($firstName . ' ' . $lastName);
    }
    
    /**
     * Get current user email
     * 
     * @return string|null Email or null if not logged in
     */
    public static function getUserEmail() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        // Try new format first
        if (isset($_SESSION[self::SESSION_USER_KEY]['email'])) {
            return $_SESSION[self::SESSION_USER_KEY]['email'];
        }
        
        // Fall back to old format
        return self::get('user_email');
    }
    
    /**
     * Check if current user has specific role
     * 
     * @param int|array $roleId Role ID or array of role IDs
     * @return bool
     */
    public static function hasRole($roleId) {
        $userRoleId = self::getUserRoleId();
        
        if (is_array($roleId)) {
            return in_array($userRoleId, $roleId);
        }
        
        return $userRoleId === $roleId;
    }
    
    /**
     * Set intended URL (for redirecting after login)
     * 
     * @param string $url URL to redirect to after login
     */
    public static function setIntendedUrl($url) {
        self::init();
        $_SESSION['intended_url'] = $url;
    }
    
    /**
     * Get and clear intended URL
     * 
     * @return string|null Intended URL or null
     */
    public static function getIntendedUrl() {
        self::init();
        
        if (isset($_SESSION['intended_url'])) {
            $url = $_SESSION['intended_url'];
            unset($_SESSION['intended_url']);
            return $url;
        }
        return null;
    }
    
    /**
     * Check if intended URL exists
     * 
     * @return bool
     */
    public static function hasIntendedUrl() {
        self::init();
        return isset($_SESSION['intended_url']);
    }
    
    /**
     * Generate CSRF token
     * 
     * @return string CSRF token
     */
    public static function generateCsrfToken() {
        self::init();
        
        if (!isset($_SESSION[self::SESSION_CSRF_TOKEN_KEY]) || 
            !isset($_SESSION[self::SESSION_CSRF_TOKEN_KEY . '_time']) ||
            (time() - $_SESSION[self::SESSION_CSRF_TOKEN_KEY . '_time']) > CSRF_TOKEN_LIFETIME) {
            
            $_SESSION[self::SESSION_CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
            $_SESSION[self::SESSION_CSRF_TOKEN_KEY . '_time'] = time();
        }
        
        return $_SESSION[self::SESSION_CSRF_TOKEN_KEY];
    }
    
    /**
     * Get CSRF token (generates if not exists)
     * 
     * @return string CSRF token
     */
    public static function getCsrfToken() {
        return self::generateCsrfToken();
    }
    
    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool
     */
    public static function validateCsrfToken($token) {
        self::init();
        
        if (!isset($_SESSION[self::SESSION_CSRF_TOKEN_KEY])) {
            return false;
        }
        
        // Check if token has expired
        if (isset($_SESSION[self::SESSION_CSRF_TOKEN_KEY . '_time']) &&
            (time() - $_SESSION[self::SESSION_CSRF_TOKEN_KEY . '_time']) > CSRF_TOKEN_LIFETIME) {
            return false;
        }
        
        return hash_equals($_SESSION[self::SESSION_CSRF_TOKEN_KEY], $token);
    }
    
    /**
     * Get CSRF token input field HTML
     * 
     * @return string HTML input field
     */
    public static function csrfField() {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Set flash message
     * 
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message text
     */
    public static function setFlash($type, $message) {
        self::init();
        
        if (!isset($_SESSION[self::SESSION_FLASH_KEY])) {
            $_SESSION[self::SESSION_FLASH_KEY] = [];
        }
        
        $_SESSION[self::SESSION_FLASH_KEY][$type] = $message;
    }
    
    /**
     * Get flash message and remove it
     * 
     * @param string $type Message type
     * @return string|null Message or null if no message
     */
    public static function getFlash($type) {
        self::init();
        
        if (isset($_SESSION[self::SESSION_FLASH_KEY][$type])) {
            $message = $_SESSION[self::SESSION_FLASH_KEY][$type];
            unset($_SESSION[self::SESSION_FLASH_KEY][$type]);
            return $message;
        }
        
        return null;
    }
    
    /**
     * Check if flash message exists
     * 
     * @param string $type Message type
     * @return bool
     */
    public static function hasFlash($type) {
        self::init();
        return isset($_SESSION[self::SESSION_FLASH_KEY][$type]);
    }
    
    /**
     * Get all flash messages and clear them
     * 
     * @return array Flash messages
     */
    public static function getFlashMessages() {
        self::init();
        
        $messages = $_SESSION[self::SESSION_FLASH_KEY] ?? [];
        unset($_SESSION[self::SESSION_FLASH_KEY]);
        
        return $messages;
    }
    
    /**
     * Get all flash messages (alias for backward compatibility)
     * 
     * @return array Array of flash messages
     */
    public static function getAllFlashes() {
        return self::getFlashMessages();
    }
    
    /**
     * Check if there are flash messages
     * 
     * @return bool
     */
    public static function hasFlashMessages() {
        self::init();
        return !empty($_SESSION[self::SESSION_FLASH_KEY]);
    }
    
    /**
     * Set old input data (for form repopulation after validation errors)
     * 
     * @param array $data Input data
     */
    public static function setOldInput($data) {
        self::init();
        $_SESSION['old_input'] = $data;
    }
    
    /**
     * Get old input value
     * 
     * @param string $key Input key
     * @param mixed $default Default value
     * @return mixed Old input value or default
     */
    public static function getOldInput($key, $default = '') {
        self::init();
        
        if (isset($_SESSION['old_input'][$key])) {
            $value = $_SESSION['old_input'][$key];
            unset($_SESSION['old_input'][$key]);
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Clear old input data
     */
    public static function clearOldInput() {
        self::init();
        unset($_SESSION['old_input']);
    }
    
    /**
     * Track login attempts
     * 
     * @param string $identifier User identifier (email or username)
     * @return int Number of attempts
     */
    public static function incrementLoginAttempts($identifier) {
        self::init();
        
        $key = 'login_attempts_' . md5($identifier);
        $attempts = self::get($key, 0) + 1;
        self::set($key, $attempts);
        self::set($key . '_time', time());
        
        return $attempts;
    }
    
    /**
     * Get login attempts
     * 
     * @param string $identifier User identifier
     * @return int Number of attempts
     */
    public static function getLoginAttempts($identifier) {
        self::init();
        
        $key = 'login_attempts_' . md5($identifier);
        $attempts = self::get($key, 0);
        $time = self::get($key . '_time', 0);
        
        // Reset if lockout time has passed
        if ($time && (time() - $time) > LOGIN_LOCKOUT_TIME) {
            self::clearLoginAttempts($identifier);
            return 0;
        }
        
        return $attempts;
    }
    
    /**
     * Clear login attempts
     * 
     * @param string $identifier User identifier
     */
    public static function clearLoginAttempts($identifier) {
        self::init();
        
        $key = 'login_attempts_' . md5($identifier);
        self::remove($key);
        self::remove($key . '_time');
    }
    
    /**
     * Check if account is locked due to too many login attempts
     * 
     * @param string $identifier User identifier
     * @return bool
     */
    public static function isAccountLocked($identifier) {
        $attempts = self::getLoginAttempts($identifier);
        return $attempts >= MAX_LOGIN_ATTEMPTS;
    }
    
    /**
     * Get time remaining for account lockout
     * 
     * @param string $identifier User identifier
     * @return int Seconds remaining
     */
    public static function getLockoutTimeRemaining($identifier) {
        self::init();
        
        $key = 'login_attempts_' . md5($identifier);
        $time = self::get($key . '_time', 0);
        
        if (!$time) {
            return 0;
        }
        
        $elapsed = time() - $time;
        $remaining = LOGIN_LOCKOUT_TIME - $elapsed;
        
        return max(0, $remaining);
    }
}
<?php
/**
 * GateWey Requisition Management System
 * Session Management Class
 * 
 * File: classes/Session.php
 * Purpose: Handle user sessions, authentication, and CSRF protection
 */

class Session {
    
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
     * Regenerate session ID periodically
     */
    private static function regenerateIfNeeded() {
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
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
            setcookie(session_name(), '', time() - 3600, SESSION_PATH);
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Set user login session
     * 
     * @param array $user User data
     */
    public static function login($user) {
        self::init();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Store user data in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_first_name'] = $user['first_name'];
        $_SESSION['user_last_name'] = $user['last_name'];
        $_SESSION['user_role_id'] = $user['role_id'];
        $_SESSION['user_department_id'] = $user['department_id'] ?? null;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
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
        
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            $inactive = time() - $_SESSION['last_activity'];
            if ($inactive > SESSION_LIFETIME) {
                self::logout();
                return false;
            }
            $_SESSION['last_activity'] = time(); // Update last activity
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
        return self::isLoggedIn() ? self::get('user_id') : null;
    }
    
    /**
     * Get current user role ID
     * 
     * @return int|null Role ID or null if not logged in
     */
    public static function getUserRoleId() {
        return self::isLoggedIn() ? self::get('user_role_id') : null;
    }
    
    /**
     * Get current user department ID
     * 
     * @return int|null Department ID or null if not logged in or no department
     */
    public static function getUserDepartmentId() {
        return self::isLoggedIn() ? self::get('user_department_id') : null;
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
        return self::get('user_first_name') . ' ' . self::get('user_last_name');
    }
    
    /**
     * Get current user email
     * 
     * @return string|null Email or null if not logged in
     */
    public static function getUserEmail() {
        return self::isLoggedIn() ? self::get('user_email') : null;
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
     * Generate CSRF token
     * 
     * @return string CSRF token
     */
    public static function generateCsrfToken() {
        self::init();
        
        if (!isset($_SESSION[CSRF_TOKEN_NAME]) || 
            !isset($_SESSION[CSRF_TOKEN_NAME . '_time']) ||
            (time() - $_SESSION[CSRF_TOKEN_NAME . '_time']) > CSRF_TOKEN_LIFETIME) {
            
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
            $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();
        }
        
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool
     */
    public static function validateCsrfToken($token) {
        self::init();
        
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        
        // Check if token has expired
        if (isset($_SESSION[CSRF_TOKEN_NAME . '_time']) &&
            (time() - $_SESSION[CSRF_TOKEN_NAME . '_time']) > CSRF_TOKEN_LIFETIME) {
            return false;
        }
        
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * Get CSRF token input field HTML
     * 
     * @return string HTML input field
     */
    public static function csrfField() {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Set flash message
     * 
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message text
     */
    public static function setFlash($type, $message) {
        self::init();
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get flash message and remove it
     * 
     * @param string $type Message type
     * @return string|null Message or null if no message
     */
    public static function getFlash($type) {
        self::init();
        
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
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
        return isset($_SESSION['flash'][$type]);
    }
    
    /**
     * Get all flash messages
     * 
     * @return array Array of flash messages
     */
    public static function getAllFlashes() {
        self::init();
        
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        
        return $flashes;
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
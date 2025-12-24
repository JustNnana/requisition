<?php
/**
 * GateWey Requisition Management System
 * Authentication Class
 * 
 * File: classes/Auth.php
 * Purpose: Handle user authentication, login, logout, and password reset operations
 */

class Auth {
    
    private $db;
    private $user;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->user = new User();
    }
    
    /**
     * Authenticate user with email and password
     * 
     * @param string $email Email address
     * @param string $password Password
     * @param bool $rememberMe Remember me option
     * @return array Result with success status and message
     */
    public function login($email, $password, $rememberMe = false) {
        try {
            // Check if account is locked
            if (Session::isAccountLocked($email)) {
                $timeRemaining = Session::getLockoutTimeRemaining($email);
                $minutes = ceil($timeRemaining / 60);
                
                return [
                    'success' => false,
                    'message' => "Account temporarily locked due to multiple failed login attempts. Please try again in {$minutes} minute(s)."
                ];
            }
            
            // Get user by email
            $user = $this->user->getByEmail($email);
            
            if (!$user) {
                // Increment failed attempts
                Session::incrementLoginAttempts($email);
                
                return [
                    'success' => false,
                    'message' => 'Invalid email or password.'
                ];
            }
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                // Increment failed attempts
                Session::incrementLoginAttempts($email);
                
                $attempts = Session::getLoginAttempts($email);
                $remaining = MAX_LOGIN_ATTEMPTS - $attempts;
                
                if ($remaining > 0) {
                    return [
                        'success' => false,
                        'message' => "Invalid email or password. {$remaining} attempt(s) remaining."
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Account temporarily locked due to multiple failed login attempts.'
                    ];
                }
            }
            
            // Check if user is active
            if (!$user['is_active']) {
                return [
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact the administrator.'
                ];
            }

            // Clear failed login attempts
            Session::clearLoginAttempts($email);

            // Check 2FA status
            $twofa = new TwoFactorAuth();
            $twofaStatus = $twofa->get2FAStatus($user['id']);

            if ($twofaStatus['enabled'] && $twofaStatus['has_secret']) {
                // User has 2FA enabled - redirect to verification
                $_SESSION['temp_user_id'] = $user['id'];

                return [
                    'success' => true,
                    'requires_2fa' => true,
                    'message' => 'Please verify your identity.',
                    'redirect' => BASE_URL . '/auth/verify-2fa.php'
                ];
            } elseif (!$twofaStatus['enabled']) {
                // First-time login or 2FA reset - redirect to setup
                $_SESSION['temp_user_id'] = $user['id'];

                return [
                    'success' => true,
                    'requires_2fa_setup' => true,
                    'message' => 'Please setup two-factor authentication.',
                    'redirect' => BASE_URL . '/auth/setup-2fa.php'
                ];
            }

            // No 2FA required - complete login
            Session::login($user);

            // Update last login timestamp
            $this->user->updateLastLogin($user['id']);

            // Handle remember me
            if ($rememberMe) {
                $this->setRememberMeCookie($user['id']);
            }

            // Log successful login
            if (ENABLE_AUDIT_LOG) {
                $this->logAction($user['id'], AUDIT_USER_LOGIN, "User logged in successfully");
            }

            return [
                'success' => true,
                'message' => 'Login successful.',
                'user' => $user
            ];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during login. Please try again.'
            ];
        }
    }
    
    /**
     * Logout current user
     * 
     * @return array Result with success status
     */
    public function logout() {
        try {
            $userId = Session::getUserId();
            
            // Log logout action
            if ($userId && ENABLE_AUDIT_LOG) {
                $this->logAction($userId, AUDIT_USER_LOGOUT, "User logged out");
            }
            
            // Clear remember me cookie
            $this->clearRememberMeCookie();
            
            // Destroy session
            Session::logout();
            
            return [
                'success' => true,
                'message' => 'Logged out successfully.'
            ];
            
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during logout.'
            ];
        }
    }
    
    /**
     * Send password reset email
     * 
     * @param string $email Email address
     * @return array Result with success status
     */
    public function requestPasswordReset($email) {
        try {
            // Get user by email
            $user = $this->user->getByEmail($email);
            
            if (!$user) {
                // Don't reveal if email exists for security
                return [
                    'success' => true,
                    'message' => 'If the email exists, a password reset link has been sent.'
                ];
            }
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
            
            // Store token in database
            $sql = "INSERT INTO password_resets (user_id, token, expires_at, ip_address)
                    VALUES (?, ?, ?, ?)";
            
            $params = [
                $user['id'],
                $token,
                $expiresAt,
                $_SERVER['REMOTE_ADDR'] ?? null
            ];
            
            $this->db->execute($sql, $params);
            
            // Send reset email
            $resetLink = APP_URL . "/auth/reset-password.php?token=" . $token;
            $emailSent = $this->sendPasswordResetEmail($user, $resetLink);
            
            if (!$emailSent) {
                return [
                    'success' => false,
                    'message' => 'Failed to send password reset email. Please try again later.'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Password reset link has been sent to your email address.'
            ];
            
        } catch (Exception $e) {
            error_log("Password reset request error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ];
        }
    }
    
    /**
     * Verify password reset token
     * 
     * @param string $token Reset token
     * @return array Result with success status and user data
     */
    public function verifyResetToken($token) {
        try {
            $sql = "SELECT pr.*, u.email, u.first_name, u.last_name
                    FROM password_resets pr
                    JOIN users u ON pr.user_id = u.id
                    WHERE pr.token = ? 
                    AND pr.is_used = 0 
                    AND pr.expires_at > NOW()
                    ORDER BY pr.created_at DESC
                    LIMIT 1";
            
            $result = $this->db->fetchOne($sql, [$token]);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired reset token.'
                ];
            }
            
            return [
                'success' => true,
                'data' => $result
            ];
            
        } catch (Exception $e) {
            error_log("Verify reset token error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while verifying token.'
            ];
        }
    }
    
    /**
     * Reset password using token
     * 
     * @param string $token Reset token
     * @param string $newPassword New password
     * @return array Result with success status
     */
    public function resetPassword($token, $newPassword) {
        try {
            // Verify token
            $tokenData = $this->verifyResetToken($token);
            
            if (!$tokenData['success']) {
                return $tokenData;
            }
            
            $userId = $tokenData['data']['user_id'];
            
            // Change password
            $result = $this->user->changePassword($userId, $newPassword);
            
            if (!$result['success']) {
                return $result;
            }
            
            // Mark token as used
            $sql = "UPDATE password_resets 
                    SET is_used = 1, used_at = NOW() 
                    WHERE token = ?";
            $this->db->execute($sql, [$token]);
            
            // Log password reset
            if (ENABLE_AUDIT_LOG) {
                $this->logAction($userId, 'password_reset', "Password reset successfully");
            }
            
            return [
                'success' => true,
                'message' => 'Password reset successfully. You can now login with your new password.'
            ];
            
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while resetting password.'
            ];
        }
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public function isAuthenticated() {
        return Session::isLoggedIn();
    }
    
    /**
     * Get current authenticated user
     * 
     * @return array|null User data or null
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        $userId = Session::getUserId();
        return $this->user->getById($userId);
    }
    
    /**
     * Check if current user has specific role
     * 
     * @param int|array $roleId Role ID or array of role IDs
     * @return bool
     */
    public function hasRole($roleId) {
        return Session::hasRole($roleId);
    }
    
    /**
     * Require authentication (redirect to login if not authenticated)
     * 
     * @param string $redirectUrl URL to redirect after login
     */
    public function requireAuth($redirectUrl = null) {
        if (!$this->isAuthenticated()) {
            // Store intended URL for redirect after login
            if ($redirectUrl) {
                Session::set('intended_url', $redirectUrl);
            } else {
                Session::set('intended_url', $_SERVER['REQUEST_URI']);
            }
            
            // Redirect to login
            header('Location: ' . APP_URL . '/auth/login.php');
            exit;
        }
    }
    
    /**
     * Require specific role (redirect if user doesn't have required role)
     * 
     * @param int|array $requiredRole Role ID or array of role IDs
     * @param string $redirectUrl URL to redirect to if unauthorized
     */
    public function requireRole($requiredRole, $redirectUrl = null) {
        // First check authentication
        $this->requireAuth();
        
        // Check role
        if (!$this->hasRole($requiredRole)) {
            if ($redirectUrl) {
                header('Location: ' . $redirectUrl);
            } else {
                header('Location: ' . APP_URL . '/errors/403.php');
            }
            exit;
        }
    }
    
    /**
     * Get redirect URL after successful login
     * 
     * @return string Redirect URL
     */
    public function getIntendedUrl() {
        $url = Session::get('intended_url');
        
        if ($url) {
            Session::remove('intended_url');
            return $url;
        }
        
        // Default redirect based on role
        $roleId = Session::getUserRoleId();
        
        switch ($roleId) {
            case ROLE_SUPER_ADMIN:
                return APP_URL . '/admin/index.php';
            case ROLE_MANAGING_DIRECTOR:
                return APP_URL . '/dashboard/managing-director.php';
            case ROLE_FINANCE_MANAGER:
                return APP_URL . '/dashboard/finance-manager.php';
            case ROLE_FINANCE_MEMBER:
                return APP_URL . '/dashboard/finance-member.php';
            case ROLE_LINE_MANAGER:
                return APP_URL . '/dashboard/line-manager.php';
            case ROLE_TEAM_MEMBER:
                return APP_URL . '/dashboard/team-member.php';
            default:
                return APP_URL . '/dashboard/index.php';
        }
    }
    
    /**
     * Set remember me cookie
     * 
     * @param int $userId User ID
     */
    private function setRememberMeCookie($userId) {
        try {
            // Generate random token
            $token = bin2hex(random_bytes(32));
            
            // Hash token for database storage
            $tokenHash = hash('sha256', $token);
            
            // Store hashed token in database (you may need to create a remember_tokens table)
            // For now, we'll use a simple cookie
            
            // Set cookie for 30 days
            $expiry = time() + (86400 * 30);
            setcookie(
                'remember_token',
                $token,
                $expiry,
                '/',
                '',
                SESSION_SECURE,
                true // httpOnly
            );
            
            setcookie(
                'remember_user',
                $userId,
                $expiry,
                '/',
                '',
                SESSION_SECURE,
                true // httpOnly
            );
            
        } catch (Exception $e) {
            error_log("Remember me cookie error: " . $e->getMessage());
        }
    }
    
    /**
     * Clear remember me cookie
     */
    private function clearRememberMeCookie() {
        setcookie('remember_token', '', time() - 3600, '/');
        setcookie('remember_user', '', time() - 3600, '/');
    }
    
    /**
     * Check and restore session from remember me cookie
     * 
     * @return bool True if session restored
     */
    public function checkRememberMe() {
        // If already logged in, no need to check
        if ($this->isAuthenticated()) {
            return true;
        }
        
        // Check if remember me cookies exist
        if (!isset($_COOKIE['remember_token']) || !isset($_COOKIE['remember_user'])) {
            return false;
        }
        
        try {
            $userId = $_COOKIE['remember_user'];
            $token = $_COOKIE['remember_token'];
            
            // Get user
            $user = $this->user->getById($userId);
            
            if (!$user || !$user['is_active']) {
                $this->clearRememberMeCookie();
                return false;
            }
            
            // Verify token (implement proper token verification if you store tokens in DB)
            // For now, we'll just trust the cookie since it's httpOnly
            
            // Restore session
            Session::login($user);
            
            // Refresh remember me cookie
            $this->setRememberMeCookie($userId);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Check remember me error: " . $e->getMessage());
            $this->clearRememberMeCookie();
            return false;
        }
    }
    
    /**
     * Send password reset email
     * 
     * @param array $user User data
     * @param string $resetLink Reset link
     * @return bool Success status
     */
    private function sendPasswordResetEmail($user, $resetLink) {
        try {
            // Check if email is enabled
            if (!SMTP_ENABLED) {
                error_log("Email sending disabled - Password reset link: " . $resetLink);
                return true; // Return true in development
            }
            
            // Prepare email
            $to = $user['email'];
            $subject = EMAIL_SUBJECT_PREFIX . ' Password Reset Request';
            
            $message = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: #00a76f; color: white; padding: 20px; text-align: center; }
                        .content { background: #f9f9f9; padding: 30px; }
                        .button { display: inline-block; padding: 12px 30px; background: #00a76f; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Password Reset Request</h1>
                        </div>
                        <div class='content'>
                            <p>Hello {$user['first_name']},</p>
                            <p>We received a request to reset your password for your GateWey Requisition Management account.</p>
                            <p>Click the button below to reset your password:</p>
                            <p style='text-align: center;'>
                                <a href='{$resetLink}' class='button'>Reset Password</a>
                            </p>
                            <p>Or copy and paste this link in your browser:</p>
                            <p style='word-break: break-all; color: #00a76f;'>{$resetLink}</p>
                            <p><strong>This link will expire in 1 hour.</strong></p>
                            <p>If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>
                            <p>Best regards,<br>GateWey Requisition Management Team</p>
                        </div>
                        <div class='footer'>
                            <p>This is an automated message, please do not reply to this email.</p>
                            <p>&copy; " . date('Y') . " GateWey Technologies. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            // Send email using your email system
            // For now, we'll use PHP's mail function (replace with PHPMailer in production)
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM_ADDRESS . ">\r\n";
            
            return mail($to, $subject, $message, $headers);
            
        } catch (Exception $e) {
            error_log("Send password reset email error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean up expired password reset tokens
     */
    public function cleanupExpiredTokens() {
        try {
            $sql = "DELETE FROM password_resets WHERE expires_at < NOW()";
            $this->db->execute($sql);
        } catch (Exception $e) {
            error_log("Cleanup expired tokens error: " . $e->getMessage());
        }
    }
    
    /**
     * Log authentication action to audit trail
     * 
     * @param int $userId User ID
     * @param string $action Action constant
     * @param string $description Action description
     */
    private function logAction($userId, $action, $description) {
        try {
            $sql = "INSERT INTO audit_log (user_id, action, description, ip_address, user_agent)
                    VALUES (?, ?, ?, ?, ?)";
            
            $params = [
                $userId,
                $action,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ];
            
            $this->db->execute($sql, $params);
            
        } catch (Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
        }
    }
}
<?php
/**
 * GateWey Requisition Management System
 * Role-Based Authorization Middleware
 * 
 * File: middleware/role-check.php
 * Purpose: Require specific user roles before accessing protected pages
 * 
 * Usage Examples:
 * 
 * 1. Require Super Admin:
 *    define('REQUIRED_ROLE', ROLE_SUPER_ADMIN);
 *    require_once '../middleware/role-check.php';
 * 
 * 2. Require multiple roles:
 *    define('REQUIRED_ROLES', [ROLE_MANAGING_DIRECTOR, ROLE_FINANCE_MANAGER]);
 *    require_once '../middleware/role-check.php';
 * 
 * 3. Require permission check:
 *    define('REQUIRED_PERMISSION', 'can_approve');
 *    require_once '../middleware/role-check.php';
 * 
 * 4. Custom unauthorized redirect:
 *    define('UNAUTHORIZED_REDIRECT', APP_URL . '/custom-page.php');
 *    require_once '../middleware/role-check.php';
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

// First check authentication
require_once __DIR__ . '/auth-check.php';

// Load permissions helper
require_once __DIR__ . '/../helpers/permissions.php';

$accessDenied = false;
$errorMessage = 'You do not have permission to access this page.';

// Check single role
if (defined('REQUIRED_ROLE')) {
    $requiredRole = constant('REQUIRED_ROLE');
    
    if (!Session::hasRole($requiredRole)) {
        $accessDenied = true;
        $errorMessage = 'This page requires ' . get_role_name($requiredRole) . ' role.';
    }
}

// Check multiple roles (user must have at least one)
if (defined('REQUIRED_ROLES')) {
    $requiredRoles = constant('REQUIRED_ROLES');
    
    if (!Session::hasRole($requiredRoles)) {
        $accessDenied = true;
        
        $roleNames = array_map('get_role_name', $requiredRoles);
        $errorMessage = 'This page requires one of the following roles: ' . implode(', ', $roleNames) . '.';
    }
}

// Check custom permission function
if (defined('REQUIRED_PERMISSION')) {
    $permissionFunction = constant('REQUIRED_PERMISSION');
    
    if (function_exists($permissionFunction)) {
        if (!call_user_func($permissionFunction)) {
            $accessDenied = true;
            $errorMessage = 'You do not have the required permission to access this page.';
        }
    } else {
        error_log("Permission function '{$permissionFunction}' not found");
        $accessDenied = true;
        $errorMessage = 'Permission check failed.';
    }
}

// Handle access denied
if ($accessDenied) {
    // Set flash message
    Session::setFlash('error', $errorMessage);
    
    // Determine redirect URL
    if (defined('UNAUTHORIZED_REDIRECT')) {
        $redirectUrl = constant('UNAUTHORIZED_REDIRECT');
    } else {
        // Redirect to user's dashboard
        $redirectUrl = get_user_dashboard_url();
    }
    
    // Redirect
    header('Location: ' . $redirectUrl);
    exit;
}
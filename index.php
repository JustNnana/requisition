<?php
/**
 * GateWey Requisition Management System
 * Application Entry Point
 * 
 * File: index.php (Root)
 * Purpose: Main entry point - redirects users to appropriate dashboard or login
 */

// Define access level
define('APP_ACCESS', true);

// Include necessary files
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Session.php';
require_once __DIR__ . '/helpers/permissions.php';

// Start session
Session::start();

// Check if user is logged in
if (!Session::isLoggedIn()) {
    // Not logged in - redirect to login page
    header('Location: ' . APP_URL . '/auth/login.php');
    exit;
}

// User is logged in - get role and redirect to appropriate dashboard
$userRoleId = Session::getUserRoleId();

// Determine redirect URL based on role
switch ($userRoleId) {
    case ROLE_SUPER_ADMIN:
        $redirectUrl = APP_URL . '/admin/index.php';
        break;
        
    case ROLE_MANAGING_DIRECTOR:
        $redirectUrl = APP_URL . '/dashboard/managing-director.php';
        break;
        
    case ROLE_FINANCE_MANAGER:
        $redirectUrl = APP_URL . '/dashboard/finance-manager.php';
        break;
        
    case ROLE_FINANCE_MEMBER:
        $redirectUrl = APP_URL . '/dashboard/finance-member.php';
        break;
        
    case ROLE_LINE_MANAGER:
        $redirectUrl = APP_URL . '/dashboard/line-manager.php';
        break;
        
    case ROLE_TEAM_MEMBER:
        $redirectUrl = APP_URL . '/dashboard/team-member.php';
        break;
        
    default:
        // Unknown role - logout and redirect to login
        Session::logout();
        Session::setFlash('error', 'Invalid user role. Please contact administrator.');
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
}

// Redirect to appropriate dashboard
header('Location: ' . $redirectUrl);
exit;
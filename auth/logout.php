<?php
/**
 * GateWey Requisition Management System
 * Logout Handler
 * 
 * File: auth/logout.php
 * Purpose: Handle user logout and session destruction
 */

// Define app access
define('APP_ACCESS', true);

// Include configuration
require_once '../config/config.php';

// Initialize session
Session::init();

// Initialize auth
$auth = new Auth();

// Perform logout
$result = $auth->logout();

// Set flash message
if ($result['success']) {
    Session::setFlash('success', 'You have been logged out successfully.');
} else {
    Session::setFlash('error', 'An error occurred during logout.');
}

// Redirect to login page
header('Location: login.php');
exit;
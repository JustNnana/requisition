<?php
/**
 * GateWey Requisition Management System
 * Authentication Middleware
 * 
 * File: middleware/auth-check.php
 * Purpose: Require user authentication before accessing protected pages
 * 
 * Usage: Include this file at the top of any page that requires authentication
 * Example: require_once '../middleware/auth-check.php';
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

// Ensure config is loaded
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/constant.php';
}

// Initialize session if not already done
Session::init();

// Initialize Auth class
if (!isset($auth)) {
    $auth = new Auth();
}

// Check if user is authenticated
if (!$auth->isAuthenticated()) {
    // Store current URL for redirect after login
    Session::set('intended_url', $_SERVER['REQUEST_URI']);
    
    // Set flash message
    Session::setFlash('error', 'Please login to access this page.');
    
    // Redirect to login
    header('Location: ' . APP_URL . '/auth/login.php');
    exit;
}

// Check remember me on every page load
$auth->checkRememberMe();
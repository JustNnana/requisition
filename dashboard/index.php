<?php
/**
 * GateWey Requisition Management System
 * Main Dashboard Index
 * 
 * File: dashboard/index.php
 * Purpose: Role-based dashboard redirect
 */

// Define app access
define('APP_ACCESS', true);

// Include configuration
require_once '../config/config.php';

// Initialize session
Session::init();

// Require authentication
require_once '../middleware/auth-check.php';

// Load permissions
require_once '../helpers/permissions.php';

// Get user's dashboard URL and redirect
$dashboardUrl = get_user_dashboard_url();
header('Location: ' . $dashboardUrl);
exit;
<?php
/**
 * GateWey Requisition Management System
 * Common Header Include
 * 
 * File: includes/header.php
 * Purpose: Common header with logo, navigation, and theme system for all authenticated pages
 */

// Ensure this file is included from a valid entry point
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

// Get current user information
$currentUser = Session::get('user');
$userRole = $currentUser['role_name'] ?? 'Guest';
$userName = $currentUser['first_name'] . ' ' . $currentUser['last_name'];
$userEmail = $currentUser['email'] ?? '';

// Determine active page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="GateWey Requisition Management System">
    <meta name="author" content="GateWey Systems">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Dasher UI CSS -->
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dasher-variables.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dasher-core-styles.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dasher-table-chart-styles.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Page Styles -->
    <?php if (isset($customCSS)): ?>
        <style><?php echo $customCSS; ?></style>
    <?php endif; ?>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar">
        <div style="display: flex; align-items: center; gap: var(--spacing-4);">
            <!-- Logo and Brand -->
            <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="navbar-brand" style="display: flex; align-items: center; gap: var(--spacing-3);">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="GateWey Logo" style="height: 40px; width: auto;">
                <span style="font-weight: var(--font-weight-bold); color: var(--text-primary);">GateWey</span>
            </a>
        </div>
        
        <!-- Right Side Navigation -->
        <div class="navbar-nav">
            <!-- Notifications -->
            <div class="dropdown">
                <button class="btn btn-ghost btn-icon" id="notificationsDropdown" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="badge badge-danger" style="position: absolute; top: 8px; right: 8px; font-size: 10px;">3</span>
                </button>
            </div>
            
            <!-- Theme Toggle -->
            <button class="btn btn-ghost btn-icon theme-toggle" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon"></i>
            </button>
            
            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <button class="btn btn-ghost" id="userDropdown" style="display: flex; align-items: center; gap: var(--spacing-2);">
                    <div class="avatar" style="width: 32px; height: 32px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; border-radius: var(--border-radius-full); font-weight: var(--font-weight-semibold); font-size: var(--font-size-sm);">
                        <?php echo strtoupper(substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1)); ?>
                    </div>
                    <span style="font-size: var(--font-size-sm);"><?php echo htmlspecialchars($userName); ?></span>
                    <i class="fas fa-chevron-down" style="font-size: var(--font-size-xs);"></i>
                </button>
                <div class="dropdown-menu" id="userDropdownMenu" style="display: none;">
                    <div style="padding: var(--spacing-3) var(--spacing-4); border-bottom: 1px solid var(--border-color);">
                        <div style="font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                            <?php echo htmlspecialchars($userName); ?>
                        </div>
                        <div style="font-size: var(--font-size-xs); color: var(--text-secondary);">
                            <?php echo htmlspecialchars($userRole); ?>
                        </div>
                        <div style="font-size: var(--font-size-xs); color: var(--text-secondary);">
                            <?php echo htmlspecialchars($userEmail); ?>
                        </div>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/profile/index.php" class="dropdown-item">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                    <a href="<?php echo BASE_URL; ?>/profile/settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Include Sidebar Navigation -->
        <?php include __DIR__ . '/navbar.php'; ?>
        
        <!-- Main Content Area -->
        <main class="content"></main>
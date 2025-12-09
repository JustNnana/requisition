<?php

/**
 * GateWey Requisition Management System
 * Common Header Include
 * 
 * File: includes/header.php
 * Purpose: Common header with logo, navigation, and theme system for all authenticated pages
 * 
 * IMPORTANT: This file assumes authentication has already been verified by middleware.
 * Do NOT add authentication redirects here as it will cause redirect loops.
 */

// Ensure this file is included from a valid entry point
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

// Get current user information using Session helper methods
$userName = Session::getUserFullName() ?? 'User';
$userEmail = Session::getUserEmail() ?? '';
$userRoleId = Session::getUserRoleId();
$userRole = get_role_name($userRoleId);

// Generate initials from full name
$nameParts = explode(' ', $userName);
$userInitials = '';
if (count($nameParts) >= 2) {
    $userInitials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
} else if (count($nameParts) == 1) {
    $userInitials = strtoupper(substr($nameParts[0], 0, 2));
}

// Determine active page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Get current date
$currentDate = date('m/d/Y');

// Get greeting based on time of day
$hour = date('H');
if ($hour < 12) {
    $greeting = 'Good Morning';
} elseif ($hour < 18) {
    $greeting = 'Good Afternoon';
} else {
    $greeting = 'Good Evening';
}

// Get notification count (placeholder - implement actual notification logic)
// $notificationCount = 0;

// Get message count (placeholder - implement actual message logic)
// $messageCount = 0;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#00a76f">
    <meta name="description" content="GateWey Requisition Management System">
    <meta name="author" content="GateWey Systems">

    <!-- DASHER UI INSTANT THEME APPLICATION -->
    <script>
        (function() {
            'use strict';
            const THEME_KEY = 'gatewey-requisition-theme';

            function getTheme() {
                try {
                    const saved = localStorage.getItem(THEME_KEY);
                    if (saved === 'dark' || saved === 'light') return saved;
                } catch (e) {}
                return (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
            }

            const theme = getTheme();
            const html = document.documentElement;

            // Apply Dasher UI theme attributes
            html.setAttribute('data-theme', theme);
            html.setAttribute('data-bs-theme', theme);

            // Add theme classes for compatibility
            if (theme === 'dark') {
                html.classList.add('theme-dark');
                html.classList.remove('theme-light');
            } else {
                html.classList.add('theme-light');
                html.classList.remove('theme-dark');
            }

            // Update meta theme color immediately
            const metaThemeColor = document.querySelector('meta[name="theme-color"]');
            if (metaThemeColor) {
                metaThemeColor.content = theme === 'dark' ? '#1c252e' : '#00a76f';
            }
        })();
    </script>

    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - <?php echo APP_NAME; ?></title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- PURE DASHER UI CSS SYSTEM -->
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dasher-variables.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dasher-core-styles.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dasher-table-chart-styles.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/status-indicators.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/requisitions.css">

    <!-- Page-specific CSS -->
    <?php if (isset($pageCSS) && is_array($pageCSS)): ?>
        <?php foreach ($pageCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo BASE_URL . $css; ?>">
        <?php endforeach; ?>
    <?php elseif (isset($pageCSS)): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL . $pageCSS; ?>">
    <?php endif; ?>

    <!-- Custom Page Styles -->
    <?php if (isset($customCSS)): ?>
        <style>
            <?php echo $customCSS; ?>
        </style>
    <?php endif; ?>

    <!-- Custom Header Styling - Responsive to Theme -->
    <style>
        /* RESPONSIVE HEADER STYLING - ADAPTS TO LIGHT/DARK THEME */
        .navbar {
            background-color: var(--bg-navbar);
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--navbar-height, 60px);
            z-index: 1030;
            padding: 0 var(--spacing-6, 1.5rem);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: var(--theme-transition);
            backdrop-filter: blur(10px);
        }

        /* Header elements - responsive to theme */
        .navbar-brand {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
            color: var(--text-primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
            transition: var(--theme-transition);
        }

        .navbar-brand:hover {
            color: var(--primary);
        }

        .navbar-nav {
            display: flex;
            align-items: center;
            gap: var(--spacing-4);
            margin-left: auto;
        }

        .mobile-menu-btn {
            border: none;
            background: transparent;
            padding: var(--spacing-2);
            margin-right: var(--spacing-2);
            color: var(--text-primary);
            border-radius: var(--border-radius);
            transition: var(--theme-transition);
            font-size: var(--font-size-lg);
            cursor: pointer;
        }

        .mobile-menu-btn:hover {
            background-color: var(--bg-hover);
        }

        /* Theme toggle - responsive styling */
        .theme-toggle {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: var(--border-radius-full);
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition-fast);
            font-size: var(--font-size-lg);
            margin-right: var(--spacing-2);
        }

        .theme-toggle:hover {
            background-color: var(--bg-hover);
            border-color: var(--primary);
        }

        /* Notification badges - responsive to theme */
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--danger);
            color: white;
            border-radius: var(--border-radius-full);
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-bold);
            border: 2px solid var(--bg-navbar);
        }

        /* User avatar - responsive to theme */
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: var(--border-radius-full);
            background: linear-gradient(135deg, var(--primary), var(--primary-600));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-sm);
            cursor: pointer;
            transition: var(--transition-fast);
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow);
        }

        /* Navigation buttons - responsive to theme */
        .nav-btn {
            position: relative;
            width: 44px;
            height: 44px;
            border-radius: var(--border-radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: transparent;
            border: none;
            color: var(--text-primary);
            transition: var(--transition-fast);
            text-decoration: none;
            cursor: pointer;
        }

        .nav-btn:hover,
        .nav-btn:focus {
            background-color: var(--bg-hover);
            color: var(--text-primary);
            text-decoration: none;
        }

        /* Date display - responsive to theme */
        .date-display .btn {
            color: var(--text-secondary);
            font-size: var(--font-size-sm);
            padding: var(--spacing-2) var(--spacing-3);
            border-radius: var(--border-radius);
            transition: var(--transition-fast);
            background: transparent;
            border: none;
            cursor: default;
        }

        .date-display .btn:hover {
            background-color: var(--bg-hover);
            color: var(--text-primary);
        }

        /* Dropdown menu styling - follows Dasher UI theme */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            padding: var(--spacing-2) 0;
            min-width: 200px;
            margin-top: var(--spacing-2);
            display: none;
            z-index: 1040;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            padding: var(--spacing-2) var(--spacing-4);
            color: var(--text-primary);
            text-decoration: none;
            font-size: var(--font-size-sm);
            transition: var(--transition-fast);
            cursor: pointer;
        }

        .dropdown-item:hover {
            background-color: var(--bg-hover);
            color: var(--primary);
        }

        .dropdown-item.text-danger:hover {
            color: var(--danger);
        }

        .dropdown-header {
            padding: var(--spacing-3) var(--spacing-4);
            color: var(--text-primary);
            font-size: var(--font-size-sm);
            border-bottom: 1px solid var(--border-color);
        }

        .dropdown-divider {
            height: 0;
            margin: var(--spacing-2) 0;
            border-top: 1px solid var(--border-color);
        }

        .dropdown-toggle {
            background: transparent;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            padding: 0;
            transition: var(--transition-fast);
        }

        .dropdown-toggle:hover {
            color: var(--primary);
        }

        /* Username text in header */
        .username-text {
            color: var(--text-primary);
            font-weight: var(--font-weight-medium);
        }

        /* Logo styling */
        .logo-img {
            height: 40px;
            width: auto;
            transition: var(--transition-normal);
        }

        /* Mobile bottom navigation - follows Dasher UI theme */
        .mobile-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: var(--bg-navbar);
            display: none;
            box-shadow: var(--shadow-sm);
            z-index: 1020;
            border-top: 1px solid var(--border-color);
            padding: var(--spacing-2) 0;
        }

        .mobile-nav-items {
            display: flex;
            justify-content: space-around;
        }

        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: var(--text-secondary);
            text-decoration: none;
            padding: var(--spacing-2) var(--spacing-1);
            font-size: var(--font-size-xs);
            border-radius: var(--border-radius);
            transition: var(--transition-fast);
            min-width: 60px;
        }

        .mobile-nav-item.active {
            color: var(--primary);
            background-color: var(--primary-light);
        }

        .mobile-nav-item i {
            font-size: var(--font-size-lg);
            margin-bottom: var(--spacing-1);
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .navbar {
                padding: 0 var(--spacing-4);
            }

            .logo-img {
                height: 32px;
            }

            .mobile-bottom-nav {
                display: block;
            }

            .content {
                padding-bottom: 90px;
            }

            .navbar-nav .d-none {
                display: none !important;
            }
        }

        @media (max-width: 576px) {
            .navbar-brand {
                font-size: var(--font-size-lg);
            }

            .user-avatar {
                width: 32px;
                height: 32px;
                font-size: var(--font-size-xs);
            }

            .logo-img {
                height: 28px;
            }
        }

        /* Extra small mobile devices */
        @media (max-width: 390px) {
            .navbar {
                padding: 0 var(--spacing-2);
                height: 56px;
            }

            .mobile-menu-btn {
                padding: var(--spacing-1);
                margin-right: var(--spacing-1);
                font-size: var(--font-size-base);
            }

            .logo-img {
                height: 24px;
            }

            .navbar-brand span {
                display: none !important;
            }

            .user-avatar {
                width: 28px;
                height: 28px;
                font-size: 10px;
            }

            .username-text {
                display: none !important;
            }

            .nav-btn {
                width: 36px;
                height: 36px;
                font-size: var(--font-size-sm);
            }

            .theme-toggle {
                width: 36px;
                height: 36px;
                font-size: var(--font-size-base);
                margin-right: var(--spacing-1);
            }

            .notification-badge {
                width: 16px;
                height: 16px;
                font-size: 9px;
                top: -6px;
                right: -6px;
            }
        }

        /* Desktop-only elements */
        @media (min-width: 769px) {
            .mobile-only {
                display: none !important;
            }
        }

        /* Logo theme switching */
        .logo-light {
            display: block;
        }

        .logo-dark {
            display: none;
        }

        [data-theme="dark"] .logo-light {
            display: none;
        }

        [data-theme="dark"] .logo-dark {
            display: block;
        }

        /* Ensure smooth transitions */
        .logo-img {
            transition: opacity var(--transition-fast);
        }
    </style>
</head>

<body>

    <!-- Top Navbar -->
    <nav class="navbar">
        <div style="display: flex; align-items: center;">
            <!-- Mobile menu button -->
            <button class="mobile-menu-btn d-lg-none" type="button" id="sidebar-toggle" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Logo and Brand -->
            <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="navbar-brand">
                <!-- Light mode logo -->
                <img src="<?php echo BASE_URL; ?>/assets/images/icons/logo.png" alt="GateWey Logo" class="logo-img logo-light">
                <!-- Dark mode logo -->
                <img src="<?php echo BASE_URL; ?>/assets/images/icons/logo-dark.png" alt="GateWey Logo" class="logo-img logo-dark">
                <span class="d-none d-sm-inline">GateWey</span>
            </a>
        </div>

        <!-- Desktop navigation -->
        <div class="navbar-nav d-none d-lg-flex">
            <!-- Date display -->
            <div class="date-display">
                <button class="btn" type="button" title="Current date">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <span><?php echo $currentDate; ?></span>
                </button>
            </div>

            <!-- Theme toggle -->
            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Toggle theme">
                <i class="fas fa-moon"></i>
            </button>

            <!-- Notifications -->
            <!-- <button class="nav-btn position-relative" title="Notifications">
                <i class="fas fa-bell"></i>
                <?php if ($notificationCount > 0): ?>
                    <span class="notification-badge"><?php echo $notificationCount; ?></span>
                <?php endif; ?>
            </button> -->

            <!-- Messages -->
            <!-- <button class="nav-btn position-relative" title="Messages">
                <i class="fas fa-envelope"></i>
                <?php if ($messageCount > 0): ?>
                    <span class="notification-badge"><?php echo $messageCount; ?></span>
                <?php endif; ?>
            </button> -->
        </div>

        <!-- Right side with mobile actions and profile -->
        <div style="display: flex; align-items: center;">
            <!-- Mobile action buttons -->
            <div class="d-lg-none" style="display: flex; align-items: center;">
                <!-- Theme toggle (mobile) -->
                <button class="theme-toggle me-2" type="button" data-theme-toggle aria-label="Toggle theme">
                    <i class="fas fa-moon"></i>
                </button>

                <!-- Notifications (mobile) -->
                <!-- <button class="nav-btn position-relative me-1">
                    <i class="fas fa-bell"></i>
                    <?php if ($notificationCount > 0): ?>
                        <span class="notification-badge"><?php echo $notificationCount; ?></span>
                    <?php endif; ?>
                </button> -->

                <!-- Messages (mobile) -->
                <!-- <button class="nav-btn position-relative me-2">
                    <i class="fas fa-envelope"></i>
                    <?php if ($messageCount > 0): ?>
                        <span class="notification-badge"><?php echo $messageCount; ?></span>
                    <?php endif; ?>
                </button> -->
            </div>

            <!-- User profile dropdown -->
            <div class="dropdown">
                <button class="dropdown-toggle" type="button" id="userDropdown" aria-expanded="false">
                    <div class="user-avatar">
                        <?php echo $userInitials; ?>
                    </div>
                    <span class="d-none d-md-inline-block ms-2 username-text"><?php echo htmlspecialchars($userName); ?></span>
                </button>
                <div class="dropdown-menu" id="userDropdownMenu">
                    <div class="dropdown-header">
                        <strong><?php echo htmlspecialchars($userName); ?></strong>
                        <br>
                        <small style="color: var(--text-secondary);"><?php echo htmlspecialchars($userRole); ?></small>
                        <br>
                        <small style="color: var(--text-secondary);"><?php echo htmlspecialchars($userEmail); ?></small>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?php echo BASE_URL; ?>#">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                    <!-- <a class="dropdown-item" href="<?php echo BASE_URL; ?>/profile/settings.php">
                        <i class="fas fa-cog"></i> Settings
                    </a> -->
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Bottom Navigation -->
    <div class="mobile-bottom-nav d-lg-none">
        <div class="mobile-nav-items">
            <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="mobile-nav-item <?php echo ($currentDir === 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/requisitions/list.php" class="mobile-nav-item <?php echo ($currentDir === 'requisitions') ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
                <span>Requisitions</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/reports/generate.php" class="mobile-nav-item <?php echo ($currentDir === 'reports') ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
            <a href="<?php echo BASE_URL; ?>#" class="mobile-nav-item <?php echo ($currentDir === 'profile') ? 'active' : ''; ?>">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </div>
    </div>
    <script>
        /**
         * Dasher UI Theme System
         * Handles theme switching, dropdowns, and header interactions
         */

        (function() {
            'use strict';

            const THEME_KEY = 'gatewey-requisition-theme';

            // ===== THEME MANAGEMENT =====
            class ThemeManager {
                constructor() {
                    this.currentTheme = this.getTheme();
                    this.init();
                }

                getTheme() {
                    try {
                        const saved = localStorage.getItem(THEME_KEY);
                        if (saved === 'dark' || saved === 'light') return saved;
                    } catch (e) {
                        console.warn('Could not access localStorage for theme');
                    }
                    return (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
                }

                setTheme(theme) {
                    this.currentTheme = theme;
                    const html = document.documentElement;

                    // Apply Dasher UI theme attributes
                    html.setAttribute('data-theme', theme);
                    html.setAttribute('data-bs-theme', theme);

                    // Add/remove theme classes
                    if (theme === 'dark') {
                        html.classList.add('theme-dark');
                        html.classList.remove('theme-light');
                    } else {
                        html.classList.add('theme-light');
                        html.classList.remove('theme-dark');
                    }

                    // Update meta theme color
                    const metaThemeColor = document.querySelector('meta[name="theme-color"]');
                    if (metaThemeColor) {
                        metaThemeColor.content = theme === 'dark' ? '#1c252e' : '#00a76f';
                    }

                    // Update theme toggle icons
                    this.updateThemeIcons(theme);

                    // Save to localStorage
                    try {
                        localStorage.setItem(THEME_KEY, theme);
                    } catch (e) {
                        console.warn('Could not save theme to localStorage');
                    }

                    // Dispatch theme changed event
                    document.dispatchEvent(new CustomEvent('themeChanged', {
                        detail: {
                            theme: theme
                        }
                    }));
                }

                updateThemeIcons(theme) {
                    const themeToggles = document.querySelectorAll('[data-theme-toggle]');
                    themeToggles.forEach(toggle => {
                        const icon = toggle.querySelector('i');
                        if (icon) {
                            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                        }
                    });
                }

                toggleTheme() {
                    const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
                    this.setTheme(newTheme);
                }

                init() {
                    // Set initial theme
                    this.setTheme(this.currentTheme);

                    // Listen for theme toggle clicks
                    document.addEventListener('click', (e) => {
                        const toggleBtn = e.target.closest('[data-theme-toggle]');
                        if (toggleBtn) {
                            e.preventDefault();
                            this.toggleTheme();
                        }
                    });

                    // Listen for system theme changes
                    if (window.matchMedia) {
                        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                            // Only auto-switch if user hasn't manually set a preference
                            try {
                                const saved = localStorage.getItem(THEME_KEY);
                                if (!saved) {
                                    this.setTheme(e.matches ? 'dark' : 'light');
                                }
                            } catch (e) {
                                // If localStorage is not available, follow system preference
                                this.setTheme(e.matches ? 'dark' : 'light');
                            }
                        });
                    }
                }
            }

            // ===== DROPDOWN MANAGEMENT =====
            class DropdownManager {
                constructor() {
                    this.activeDropdown = null;
                    this.init();
                }

                init() {
                    // Handle dropdown toggle clicks
                    document.addEventListener('click', (e) => {
                        const toggle = e.target.closest('.dropdown-toggle');

                        if (toggle) {
                            e.preventDefault();
                            e.stopPropagation();
                            this.toggleDropdown(toggle);
                        } else {
                            // Close all dropdowns when clicking outside
                            this.closeAllDropdowns();
                        }
                    });

                    // Close dropdowns on escape key
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            this.closeAllDropdowns();

                            // Return focus to the toggle button if there was an active dropdown
                            if (this.activeDropdown) {
                                const toggle = this.activeDropdown.previousElementSibling;
                                if (toggle && toggle.classList.contains('dropdown-toggle')) {
                                    toggle.focus();
                                }
                            }
                        }
                    });

                    // Handle dropdown item clicks
                    document.addEventListener('click', (e) => {
                        const dropdownItem = e.target.closest('.dropdown-item');
                        if (dropdownItem && !dropdownItem.href) {
                            this.closeAllDropdowns();
                        }
                    });
                }

                toggleDropdown(toggle) {
                    const menu = toggle.nextElementSibling;

                    if (!menu || !menu.classList.contains('dropdown-menu')) {
                        return;
                    }

                    const isVisible = menu.classList.contains('show');

                    // Close all other dropdowns
                    this.closeAllDropdowns();

                    // Toggle this dropdown
                    if (!isVisible) {
                        menu.classList.add('show');
                        toggle.setAttribute('aria-expanded', 'true');
                        this.activeDropdown = menu;

                        // Smart positioning
                        setTimeout(() => {
                            this.positionDropdown(toggle, menu);
                        }, 10);
                    }
                }

                closeAllDropdowns() {
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        menu.classList.remove('show');
                        const toggle = menu.previousElementSibling;
                        if (toggle) {
                            toggle.setAttribute('aria-expanded', 'false');
                        }
                    });
                    this.activeDropdown = null;
                }

                positionDropdown(toggle, menu) {
                    const toggleRect = toggle.getBoundingClientRect();
                    const menuRect = menu.getBoundingClientRect();
                    const viewportWidth = window.innerWidth;
                    const viewportHeight = window.innerHeight;

                    // Check if we need custom positioning
                    const needsCustomPosition = window.innerWidth <= 768 ||
                        toggleRect.right + menuRect.width > viewportWidth ||
                        toggleRect.bottom + menuRect.height > viewportHeight;

                    if (needsCustomPosition) {
                        menu.style.position = 'fixed';
                        menu.style.zIndex = '9999';

                        // Calculate horizontal position
                        let leftPos = toggleRect.right - menuRect.width;

                        // Ensure menu doesn't go off the left edge
                        if (leftPos < 10) {
                            leftPos = 10;
                        }

                        // Ensure menu doesn't go off the right edge
                        if (leftPos + menuRect.width > viewportWidth - 10) {
                            leftPos = viewportWidth - menuRect.width - 10;
                        }

                        // Calculate vertical position
                        let topPos = toggleRect.bottom + 5;

                        // If dropdown would go below viewport, position it above the toggle
                        if (topPos + menuRect.height > viewportHeight - 10) {
                            topPos = toggleRect.top - menuRect.height - 5;
                        }

                        // Ensure dropdown doesn't go above viewport
                        if (topPos < 10) {
                            topPos = 10;
                        }

                        menu.style.left = leftPos + 'px';
                        menu.style.top = topPos + 'px';
                    } else {
                        // Reset positioning for desktop
                        menu.style.position = '';
                        menu.style.top = '';
                        menu.style.left = '';
                        menu.style.right = '';
                    }
                }
            }

            // ===== SIDEBAR MANAGEMENT =====
            class SidebarManager {
                constructor() {
                    this.sidebar = document.querySelector('.sidebar');
                    this.content = document.querySelector('.content');
                    this.toggle = document.getElementById('sidebar-toggle');
                    this.init();
                }

                init() {
                    if (!this.toggle || !this.sidebar) {
                        return;
                    }

                    // Handle toggle button click
                    this.toggle.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.toggleSidebar();
                    });

                    // Close sidebar when clicking outside on mobile
                    document.addEventListener('click', (e) => {
                        const isClickInsideSidebar = this.sidebar.contains(e.target);
                        const isClickOnToggle = this.toggle.contains(e.target);

                        if (!isClickInsideSidebar && !isClickOnToggle &&
                            window.innerWidth <= 768 &&
                            this.sidebar.classList.contains('active')) {
                            this.closeSidebar();
                        }
                    });

                    // Handle window resize
                    window.addEventListener('resize', () => {
                        if (window.innerWidth > 768) {
                            this.closeSidebar();
                        }
                    });
                }

                toggleSidebar() {
                    this.sidebar.classList.toggle('active');
                    if (this.content) {
                        this.content.classList.toggle('sidebar-active');
                    }
                    this.toggle.setAttribute('aria-expanded',
                        this.sidebar.classList.contains('active'));
                }

                closeSidebar() {
                    this.sidebar.classList.remove('active');
                    if (this.content) {
                        this.content.classList.remove('sidebar-active');
                    }
                    this.toggle.setAttribute('aria-expanded', 'false');
                }
            }

            // ===== MOBILE NAVIGATION =====
            class MobileNavigationManager {
                constructor() {
                    this.init();
                }

                init() {
                    this.highlightCurrentPage();

                    // Re-highlight on route change (for SPAs)
                    window.addEventListener('popstate', () => {
                        this.highlightCurrentPage();
                    });
                }

                highlightCurrentPage() {
                    const currentPath = window.location.pathname;
                    const navItems = document.querySelectorAll('.mobile-nav-item');

                    navItems.forEach(item => {
                        const href = item.getAttribute('href');
                        if (href && currentPath.includes(href.split('/').pop())) {
                            item.classList.add('active');
                        } else {
                            item.classList.remove('active');
                        }
                    });
                }
            }

            // ===== INITIALIZATION =====
            document.addEventListener('DOMContentLoaded', () => {
                // Initialize all managers
                new ThemeManager();
                new DropdownManager();
                new SidebarManager();
                new MobileNavigationManager();

                // Log initialization
                console.log('Dasher UI Theme System initialized');
            });

            // Also initialize immediately if DOM is already loaded
            if (document.readyState === 'interactive' || document.readyState === 'complete') {
                new ThemeManager();
                new DropdownManager();
                new SidebarManager();
                new MobileNavigationManager();
            }

        })();
    </script>
    <!-- Main Wrapper -->
    <div class="">
        <!-- Include Sidebar Navigation -->
        <?php include __DIR__ . '/navbar.php'; ?>

        <!-- Main Content Area -->
        <main class="content">
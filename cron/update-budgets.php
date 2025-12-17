<?php
/**
 * Budget Status Update Cron Job
 * 
 * Purpose: Automatically expire old budgets and activate upcoming budgets
 * Schedule: Run daily at 12:01 AM
 * 
 * Cron command:
 * 1 0 * * * /usr/bin/php /home2/gateweyc/public_html/request.gatewey.com.ng/cron/update-budgets.php >> /home2/gateweyc/public_html/request.gatewey.com.ng/logs/cron-output.log 2>&1
 */

// Set execution time limit
set_time_limit(300); // 5 minutes max

// Set error reporting for cron
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Define APP_ACCESS constant (required by config files)
if (!defined('APP_ACCESS')) {
    define('APP_ACCESS', true);
}

// Get the root directory (parent of cron folder)
$rootDir = dirname(__DIR__);

// Define required paths (only if not already defined)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', $rootDir);
}
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', $rootDir . '/config');
}
if (!defined('CLASSES_PATH')) {
    define('CLASSES_PATH', $rootDir . '/classes');
}
if (!defined('LOGS_PATH')) {
    define('LOGS_PATH', $rootDir . '/logs');
}

// Log file
$logFile = LOGS_PATH . '/cron-budget-updates.log';

// Ensure logs directory exists
if (!file_exists(LOGS_PATH)) {
    mkdir(LOGS_PATH, 0755, true);
}

/**
 * Log messages to file and console
 */
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    echo $logEntry; // Also output to console
}

// Log start
logMessage("=== Budget Update Cron Job Started ===");
logMessage("Root directory: " . ROOT_PATH);

// Verify required directories exist
$requiredDirs = [
    'config' => CONFIG_PATH,
    'classes' => CLASSES_PATH,
    'logs' => LOGS_PATH
];

foreach ($requiredDirs as $name => $path) {
    if (!is_dir($path)) {
        logMessage("ERROR: Required directory '{$name}' not found at: {$path}");
        logMessage("=== Budget Update Cron Job Failed (Missing Directory) ===");
        exit(1);
    }
}

// Verify required files exist
$requiredFiles = [
    CONFIG_PATH . '/config.php' => 'config.php',
    CONFIG_PATH . '/database.php' => 'database.php (contains Database class)',
    CLASSES_PATH . '/Budget.php' => 'Budget.php'
];

foreach ($requiredFiles as $path => $name) {
    if (!file_exists($path)) {
        logMessage("ERROR: Required file '{$name}' not found at: {$path}");
        logMessage("=== Budget Update Cron Job Failed (Missing File) ===");
        exit(1);
    }
}

logMessage("All required files verified successfully");

// Include required files in correct order
try {
    // 1. Load main config (this loads constants and paths)
    require_once CONFIG_PATH . '/config.php';
    logMessage("Loaded: config.php");
    
    // 2. Load database config (this contains the Database class)
    require_once CONFIG_PATH . '/database.php';
    logMessage("Loaded: database.php (Database class included)");
    
    // 3. Load Budget class
    require_once CLASSES_PATH . '/Budget.php';
    logMessage("Loaded: Budget.php");
    
} catch (Exception $e) {
    logMessage("ERROR loading required files: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
    logMessage("=== Budget Update Cron Job Failed (Load Error) ===");
    exit(1);
}

// Main execution
try {
    logMessage("Initializing Budget class...");
    
    // Initialize Budget class
    $budgetManager = new Budget(); // ✅ FIX: Renamed from $budget to $budgetManager
    logMessage("Budget class initialized successfully");
    
    // Test database connection
    $db = Database::getInstance();
    logMessage("Database connection verified");
    
    // Get current MySQL date for logging
    $conn = $db->getConnection();
    $mysqlDate = $conn->query("SELECT CURDATE() as today, NOW() as now")->fetch();
    logMessage("Current MySQL date: {$mysqlDate['today']} {$mysqlDate['now']}");
    
    // 1. Expire old budgets
    logMessage("Checking for budgets to expire...");
    logMessage("Query: Budgets with status='active' AND end_date < CURDATE()");
    
    $expiredCount = $budgetManager->expireBudgets(); // ✅ FIX: Use $budgetManager
    
    if ($expiredCount > 0) {
        logMessage("✓ Successfully expired {$expiredCount} budget(s)");
        
        // Log details of expired budgets
        $expiredBudgets = $conn->query("
            SELECT b.id, d.department_name, b.end_date, b.budget_amount
            FROM department_budgets b
            LEFT JOIN departments d ON b.department_id = d.id
            WHERE b.status = 'expired' 
            AND b.updated_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ORDER BY b.updated_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($expiredBudgets as $expiredBudget) { // ✅ FIX: Use $expiredBudget
            logMessage("  - Budget #{$expiredBudget['id']}: {$expiredBudget['department_name']} (ended {$expiredBudget['end_date']})");
        }
    } else {
        logMessage("No budgets expired (all active budgets are still valid)");
    }
    
    // 2. Activate upcoming budgets
    logMessage("Checking for budgets to activate...");
    logMessage("Query: Budgets with status='upcoming' AND start_date <= CURDATE() AND end_date >= CURDATE()");
    
    $activatedCount = $budgetManager->activateUpcomingBudgets(); // ✅ FIX: Use $budgetManager
    
    if ($activatedCount > 0) {
        logMessage("✓ Successfully activated {$activatedCount} budget(s)");
        
        // Log details of activated budgets
        $activatedBudgets = $conn->query("
            SELECT b.id, d.department_name, b.start_date, b.end_date, b.budget_amount
            FROM department_budgets b
            LEFT JOIN departments d ON b.department_id = d.id
            WHERE b.status = 'active' 
            AND b.updated_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            AND b.start_date = CURDATE()
            ORDER BY b.updated_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($activatedBudgets as $activatedBudget) { // ✅ FIX: Use $activatedBudget
            logMessage("  - Budget #{$activatedBudget['id']}: {$activatedBudget['department_name']} (period: {$activatedBudget['start_date']} to {$activatedBudget['end_date']})");
        }
    } else {
        logMessage("No budgets activated (no upcoming budgets ready to start)");
    }
    
    // 3. Log summary
    logMessage("=== Budget Update Cron Job Completed Successfully ===");
    logMessage("Summary: Expired={$expiredCount}, Activated={$activatedCount}");
    logMessage("Execution time: " . round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . " ms");
    logMessage("Memory used: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB");
    
    // Exit with success code
    exit(0);
    
} catch (Exception $e) {
    logMessage("ERROR during execution: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
    logMessage("File: " . $e->getFile() . " (Line: " . $e->getLine() . ")");
    logMessage("=== Budget Update Cron Job Failed ===");
    
    // Exit with error code
    exit(1);
}
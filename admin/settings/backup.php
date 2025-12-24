<?php
/**
 * GateWey Requisition Management System
 * System Settings - Database Backup
 * 
 * File: admin/settings/backup.php
 * Purpose: Create and download database backups
 */

// Define access level
define('APP_ACCESS', true);
define('ADMIN_ACCESS', true);

// Include necessary files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../helpers/permissions.php';

// Start session
Session::start();

// Check authentication and authorization
require_once __DIR__ . '/../../middleware/auth-check.php';
require_once __DIR__ . '/../../middleware/role-check.php';
checkRole(ROLE_SUPER_ADMIN);

// Initialize database
$db = Database::getInstance();

// Initialize variables
$errors = [];
$success = '';

// Create backups directory if it doesn't exist
$backupDir = ROOT_PATH . '/backups';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create_backup') {
            try {
                // Generate backup filename
                $timestamp = date('Y-m-d_H-i-s');
                $filename = 'backup_' . DB_NAME . '_' . $timestamp . '.sql';
                $filepath = $backupDir . '/' . $filename;

                // Temporarily enable emulated prepares to avoid "prepared statement needs to be re-prepared" errors
                // This is especially important on production servers with table_definition_cache issues
                $pdo = $db->getConnection();
                $originalEmulateMode = $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES);
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

                try {
                    // Get all tables
                    $tables = [];
                    $stmt = $pdo->query("SHOW TABLES");
                    $result = $stmt->fetchAll(PDO::FETCH_NUM);
                    $stmt->closeCursor();
                    foreach ($result as $row) {
                        $tables[] = $row[0];
                    }
                
                // Start building SQL dump
                $sqlDump = "-- GateWey Requisition Management System\n";
                $sqlDump .= "-- Database Backup\n";
                $sqlDump .= "-- Generated: " . date(DATETIME_FORMAT) . "\n";
                $sqlDump .= "-- Database: " . DB_NAME . "\n";
                $sqlDump .= "-- MySQL Server Version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n\n";
                $sqlDump .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
                $sqlDump .= "SET AUTOCOMMIT = 0;\n";
                $sqlDump .= "START TRANSACTION;\n";
                $sqlDump .= "SET time_zone = \"+00:00\";\n\n";
                
                // Loop through tables
                foreach ($tables as $table) {
                    // Drop table if exists
                    $sqlDump .= "\n\n-- --------------------------------------------------------\n";
                    $sqlDump .= "-- Table structure for table `{$table}`\n";
                    $sqlDump .= "-- --------------------------------------------------------\n\n";
                    $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";

                    // Get CREATE TABLE statement with emulated prepares enabled
                    $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
                    $createTableResult = $stmt->fetch(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();

                    // Handle different possible key names (case-sensitive)
                    $createStatement = null;
                    if (isset($createTableResult['Create Table'])) {
                        $createStatement = $createTableResult['Create Table'];
                    } elseif (isset($createTableResult['create table'])) {
                        $createStatement = $createTableResult['create table'];
                    } elseif (isset($createTableResult['CREATE TABLE'])) {
                        $createStatement = $createTableResult['CREATE TABLE'];
                    } else {
                        // Fallback: get second value
                        $values = array_values($createTableResult);
                        $createStatement = $values[1] ?? '';
                    }

                    if ($createStatement) {
                        $sqlDump .= $createStatement . ";\n";
                    } else {
                        throw new Exception("Could not retrieve CREATE TABLE statement for table: {$table}");
                    }

                    // Get table data with emulated prepares enabled
                    $stmt = $pdo->query("SELECT * FROM `{$table}`");
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                    
                    if (!empty($rows)) {
                        $sqlDump .= "\n-- Dumping data for table `{$table}`\n\n";
                        
                        foreach ($rows as $row) {
                            $values = array_map(function($value) use ($db) {
                                if ($value === null) {
                                    return 'NULL';
                                }
                                return "'" . addslashes($value) . "'";
                            }, array_values($row));
                            
                            $columns = array_keys($row);
                            $sqlDump .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                        }
                    }
                }
                
                $sqlDump .= "\nCOMMIT;\n";
                
                // Write to file
                if (file_put_contents($filepath, $sqlDump) !== false) {
                    // Log backup creation
                    if (ENABLE_AUDIT_LOG) {
                        $logSql = "INSERT INTO audit_log (user_id, action, description, ip_address, created_at)
                                   VALUES (?, ?, ?, ?, NOW())";
                        $logParams = [
                            Session::getUserId(),
                            AUDIT_BACKUP_CREATED,
                            "Database backup created: {$filename}",
                            LOG_IP_ADDRESS ? ($_SERVER['REMOTE_ADDR'] ?? '') : null
                        ];
                        
                        $db->execute($logSql, $logParams);
                    }
                    
                    $success = "Database backup created successfully: {$filename}";
                } else {
                    $errors[] = 'Failed to write backup file. Please check directory permissions.';
                }

                } finally {
                    // Restore original emulate prepares setting
                    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $originalEmulateMode);
                }

            } catch (PDOException $e) {
                // Log PDO-specific errors
                error_log("Backup PDO Error: " . $e->getMessage() . " | Code: " . $e->getCode());
                $errors[] = 'Database backup failed: ' . $e->getMessage();
            } catch (Exception $e) {
                // Log general errors
                error_log("Backup Error: " . $e->getMessage());
                $errors[] = 'Backup failed: ' . $e->getMessage();
            }
        } elseif ($action === 'download_backup') {
            $filename = $_POST['filename'] ?? '';
            $filepath = $backupDir . '/' . basename($filename);
            
            if (file_exists($filepath)) {
                // Force download
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
                header('Content-Length: ' . filesize($filepath));
                header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: public');
                
                readfile($filepath);
                exit;
            } else {
                $errors[] = 'Backup file not found.';
            }
        } elseif ($action === 'delete_backup') {
            $filename = $_POST['filename'] ?? '';
            $filepath = $backupDir . '/' . basename($filename);
            
            if (file_exists($filepath)) {
                if (unlink($filepath)) {
                    // Log backup deletion
                    if (ENABLE_AUDIT_LOG) {
                        $logSql = "INSERT INTO audit_log (user_id, action, description, ip_address, created_at)
                                   VALUES (?, ?, ?, ?, NOW())";
                        $logParams = [
                            Session::getUserId(),
                            'backup_deleted',
                            "Database backup deleted: {$filename}",
                            LOG_IP_ADDRESS ? ($_SERVER['REMOTE_ADDR'] ?? '') : null
                        ];
                        
                        $db->execute($logSql, $logParams);
                    }
                    
                    $success = "Backup file deleted successfully.";
                } else {
                    $errors[] = 'Failed to delete backup file.';
                }
            } else {
                $errors[] = 'Backup file not found.';
            }
        }
    }
}

// Get list of existing backups
$backupFiles = [];
if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $filepath = $backupDir . '/' . $file;
            $backupFiles[] = [
                'filename' => $file,
                'size' => filesize($filepath),
                'date' => filemtime($filepath)
            ];
        }
    }
    
    // Sort by date (newest first)
    usort($backupFiles, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

// Page title
$pageTitle = 'Database Backup';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<!-- Enhanced Styles -->
<style>
    /* Form Container & Layout */
    .form-container {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: var(--spacing-6);
        max-width: 1400px;
        margin: 0 auto;
    }

    @media (max-width: 992px) {
        .form-container {
            grid-template-columns: 1fr;
        }
    }

    /* Form Card */
    .form-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        transition: var(--theme-transition);
        margin-bottom: var(--spacing-4);
    }

    .form-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    /* Form Header */
    .form-header {
        padding: var(--spacing-6);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: var(--spacing-4);
    }

    .form-icon {
        width: 48px;
        height: 48px;
        background: var(--primary);
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }

    .form-icon.success {
        background: var(--success);
    }

    .form-header-content {
        flex: 1;
    }

    .form-title {
        margin: 0 0 var(--spacing-1) 0;
        font-size: var(--font-size-2xl);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .form-subtitle {
        margin: 0;
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
    }

    /* Form Body */
    .form-body {
        padding: var(--spacing-6);
    }

    /* Backup Item */
    .backup-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-3);
        transition: var(--theme-transition);
    }

    .backup-item:last-child {
        margin-bottom: 0;
    }

    .backup-item:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .backup-info {
        flex: 1;
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .backup-icon {
        width: 40px;
        height: 40px;
        background: var(--primary);
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: var(--font-size-lg);
        flex-shrink: 0;
    }

    .backup-details {
        flex: 1;
    }

    .backup-name {
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-1);
    }

    .backup-meta {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
    }

    .backup-meta-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-1);
    }

    .backup-actions {
        display: flex;
        gap: var(--spacing-2);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: var(--spacing-8);
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 4rem;
        opacity: 0.3;
        margin-bottom: var(--spacing-4);
    }

    .empty-state-text {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
    }

    /* Info Card */
    .info-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        height: fit-content;
        margin-bottom: var(--spacing-4);
    }

    .info-header {
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .info-icon {
        color: var(--info);
        font-size: var(--font-size-lg);
    }

    .info-title {
        margin: 0;
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .info-content {
        padding: var(--spacing-4);
    }

    .info-text {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        line-height: 1.6;
        margin-bottom: var(--spacing-4);
    }

    .info-text:last-child {
        margin-bottom: 0;
    }

    .info-item {
        padding: var(--spacing-3) 0;
        border-bottom: 1px solid var(--border-color);
    }

    .info-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-item:first-child {
        padding-top: 0;
    }

    .info-label {
        display: block;
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-1);
        font-weight: var(--font-weight-medium);
    }

    .info-value {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        font-weight: var(--font-weight-medium);
    }

    .info-divider {
        margin: var(--spacing-4) 0;
        border: 0;
        border-top: 1px solid var(--border-color);
    }

    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-list li {
        padding: var(--spacing-2) 0 var(--spacing-2) var(--spacing-5);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        position: relative;
        line-height: 1.6;
    }

    .info-list li::before {
        content: '•';
        position: absolute;
        left: var(--spacing-3);
        color: var(--primary);
        font-weight: bold;
    }

    .info-list.numbered {
        counter-reset: item;
        padding-left: 0;
    }

    .info-list.numbered li {
        counter-increment: item;
        padding-left: var(--spacing-6);
    }

    .info-list.numbered li::before {
        content: counter(item) ".";
        left: var(--spacing-2);
        color: var(--primary);
    }

    /* Alert Banner */
    .alert-banner {
        display: flex;
        align-items: flex-start;
        padding: var(--spacing-3);
        background: rgba(var(--info-rgb), 0.1);
        border: 1px solid rgba(var(--info-rgb), 0.2);
        border-radius: var(--border-radius);
        gap: var(--spacing-2);
        margin-bottom: var(--spacing-4);
    }

    .alert-banner i {
        color: var(--info);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .alert-banner-content {
        flex: 1;
        font-size: var(--font-size-sm);
        color: var(--info);
    }

    .alert-banner-content strong {
        display: block;
        margin-bottom: var(--spacing-1);
    }

    /* Warning Card */
    .warning-card {
        background: transparent;
        border: 1px solid rgba(var(--warning-rgb), 0.3);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
    }

    .warning-header {
        padding: var(--spacing-4);
        background: rgba(var(--warning-rgb), 0.1);
        border-bottom: 1px solid rgba(var(--warning-rgb), 0.2);
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .warning-icon {
        color: var(--warning);
        font-size: var(--font-size-lg);
    }

    .warning-title {
        margin: 0;
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--warning);
    }

    .warning-content {
        padding: var(--spacing-4);
    }

    .warning-box {
        display: flex;
        align-items: flex-start;
        padding: var(--spacing-3);
        background: rgba(var(--warning-rgb), 0.1);
        border: 1px solid rgba(var(--warning-rgb), 0.2);
        border-radius: var(--border-radius);
        gap: var(--spacing-2);
        margin-top: var(--spacing-3);
    }

    .warning-box i {
        color: var(--warning);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .warning-box-content {
        flex: 1;
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    /* Alert Messages */
    .alert {
        display: flex;
        align-items: flex-start;
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-4);
        gap: var(--spacing-3);
    }

    .alert-danger {
        background: rgba(var(--danger-rgb), 0.1);
        border-color: rgba(var(--danger-rgb), 0.2);
        color: var(--danger);
    }

    .alert-success {
        background: rgba(var(--success-rgb), 0.1);
        border-color: rgba(var(--success-rgb), 0.2);
        color: var(--success);
    }

    .alert i {
        font-size: var(--font-size-lg);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .alert-content {
        flex: 1;
    }

    .alert-title {
        font-weight: var(--font-weight-semibold);
        margin-bottom: var(--spacing-1);
    }

    .alert-message {
        font-size: var(--font-size-sm);
        line-height: 1.5;
        margin-bottom: var(--spacing-3);
    }

    .alert-message ul {
        margin: 0;
        padding-left: var(--spacing-4);
    }

    .alert-message li {
        margin-bottom: var(--spacing-1);
    }

    /* Breadcrumb Styling */
    .content-breadcrumb {
        margin-top: var(--spacing-2);
    }

    .breadcrumb {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: var(--spacing-1);
        align-items: center;
    }

    .breadcrumb-item {
        display: flex;
        align-items: center;
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .breadcrumb-item a {
        color: var(--primary);
        text-decoration: none;
        transition: var(--theme-transition);
    }

    .breadcrumb-item a:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    .breadcrumb-item.active {
        color: var(--text-primary);
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "/";
        margin: 0 var(--spacing-2);
        color: var(--text-muted);
    }

    /* Content Header */
    .content-header {
        margin-bottom: var(--spacing-6);
    }

    .content-actions {
        display: flex;
        gap: var(--spacing-3);
        align-items: center;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .form-header {
            flex-direction: column;
            text-align: center;
        }

        .form-icon {
            margin: 0 auto;
        }

        .backup-item {
            flex-direction: column;
            gap: var(--spacing-3);
        }

        .backup-actions {
            width: 100%;
            justify-content: center;
        }

        .content-actions {
            flex-wrap: wrap;
            gap: var(--spacing-2);
        }
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">Database Backup</h1>
            <nav class="content-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="../../dashboard/">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="../index.php">Admin</a>
                    </li>
                    <li class="breadcrumb-item active">Database Backup</li>
                </ol>
            </nav>
        </div>
        <div class="content-actions">
            <a href="../index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Error</div>
            <div class="alert-message">
                <strong>The following errors occurred:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success" role="alert">
        <i class="fas fa-check-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Success!</div>
            <div class="alert-message"><?php echo htmlspecialchars($success); ?></div>
        </div>
    </div>
<?php endif; ?>

<!-- Backup Management -->
<div class="form-container">
    <div>
        <!-- Backup History Card -->
        <div class="form-card">
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div class="form-header-content">
                    <h2 class="form-title">
                        Backup History
                        <span class="badge badge-primary" style="font-size: var(--font-size-sm); margin-left: var(--spacing-2);">
                            <?php echo count($backupFiles); ?>
                        </span>
                    </h2>
                    <p class="form-subtitle">Manage and download your database backups</p>
                </div>
            </div>

            <div class="form-body">
                <?php if (empty($backupFiles)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <p class="empty-state-text">
                            No backups found. Create your first backup to get started.
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($backupFiles as $backup): ?>
                        <div class="backup-item">
                            <div class="backup-info">
                                <div class="backup-icon">
                                    <i class="fas fa-file-archive"></i>
                                </div>
                                <div class="backup-details">
                                    <div class="backup-name">
                                        <?php echo htmlspecialchars($backup['filename']); ?>
                                    </div>
                                    <div class="backup-meta">
                                        <span class="backup-meta-item">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('M d, Y', $backup['date']); ?>
                                        </span>
                                        <span class="backup-meta-item">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('h:i A', $backup['date']); ?>
                                        </span>
                                        <span class="backup-meta-item">
                                            <i class="fas fa-hdd"></i>
                                            <?php echo number_format($backup['size'] / 1024, 2); ?> KB
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="backup-actions">
                                <!-- Download Button -->
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                                    <input type="hidden" name="action" value="download_backup">
                                    <input type="hidden" name="filename" value="<?php echo htmlspecialchars($backup['filename']); ?>">
                                    <button type="submit" class="btn btn-primary" title="Download">
                                        <i class="fas fa-download"></i>
                                        Download
                                    </button>
                                </form>
                                
                                <!-- Delete Button -->
                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this backup?\n\nThis action cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                                    <input type="hidden" name="action" value="delete_backup">
                                    <input type="hidden" name="filename" value="<?php echo htmlspecialchars($backup['filename']); ?>">
                                    <button type="submit" class="btn btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Important Notes Card -->
        <div class="warning-card">
            <div class="warning-header">
                <i class="fas fa-exclamation-triangle warning-icon"></i>
                <h3 class="warning-title">Important Information</h3>
            </div>
            <div class="warning-content">
                <h6 class="mb-3">Backup Best Practices:</h6>
                <ul class="info-list">
                    <li>Create regular backups before major system updates</li>
                    <li>Store backups in multiple locations (local and cloud)</li>
                    <li>Test backup restoration periodically</li>
                    <li>Keep at least 3-5 recent backups for redundancy</li>
                    <li>Secure backup files with appropriate permissions</li>
                </ul>
                
                <hr class="info-divider">
                
                <h6 class="mb-3">Restore Instructions:</h6>
                <ol class="info-list numbered">
                    <li>Download the backup file you want to restore</li>
                    <li>Access your database through phpMyAdmin or MySQL command line</li>
                    <li>Drop existing database or create new one</li>
                    <li>Import the SQL backup file</li>
                    <li>Verify data integrity after restoration</li>
                </ol>
                
                <div class="warning-box">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="warning-box-content">
                        <strong>Warning:</strong> Restoring a backup will overwrite all current data. Always create a backup of current data before restoration.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Create Backup Card -->
        <div class="form-card">
            <div class="form-header">
                <div class="form-icon success">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="form-header-content">
                    <h2 class="form-title">Create Backup</h2>
                    <p class="form-subtitle">Generate a new database backup</p>
                </div>
            </div>

            <div class="form-body">
                <p class="info-text">
                    Create a complete backup of the database including all tables, data, and structure.
                </p>
                
                <div class="alert-banner">
                    <i class="fas fa-info-circle"></i>
                    <div class="alert-banner-content">
                        <strong>Database:</strong> <?php echo htmlspecialchars(DB_NAME); ?><br>
                        <strong>Location:</strong> /backups/
                    </div>
                </div>
                
                <form method="POST" action="" id="backupForm">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                    <input type="hidden" name="action" value="create_backup">
                    
                    <button type="submit" class="btn btn-success btn-lg" style="width: 100%;">
                        <i class="fas fa-database"></i> Create Backup Now
                    </button>
                </form>
            </div>
        </div>
        
        <!-- System Information Card -->
        <div class="info-card">
            <div class="info-header">
                <i class="fas fa-server info-icon"></i>
                <h3 class="info-title">System Information</h3>
            </div>
            <div class="info-content">
                <div class="info-item">
                    <span class="info-label">Database Host</span>
                    <div class="info-value"><?php echo htmlspecialchars(DB_HOST); ?></div>
                </div>

                <div class="info-item">
                    <span class="info-label">Database Name</span>
                    <div class="info-value"><?php echo htmlspecialchars(DB_NAME); ?></div>
                </div>

                <div class="info-item">
                    <span class="info-label">MySQL Version</span>
                    <div class="info-value"><?php echo htmlspecialchars($db->getConnection()->getAttribute(PDO::ATTR_SERVER_VERSION)); ?></div>
                </div>

                <div class="info-item">
                    <span class="info-label">Total Tables</span>
                    <div class="info-value">
                        <?php 
                        $tableCount = $db->fetchOne("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?", [DB_NAME]);
                        echo htmlspecialchars($tableCount['count']); 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('💾 Initializing Backup Manager...');

    const form = document.getElementById('backupForm');

    // Form submission enhancement
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Backup...';
        });
    }

    // Delete confirmation enhancement
    const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            
            if (submitButton && !submitButton.disabled) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            }
        });
    });

    console.log('✅ Backup Manager initialized successfully');
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
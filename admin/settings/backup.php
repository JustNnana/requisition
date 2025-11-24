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
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Session.php';
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
$successMessage = '';

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
                
                // Get all tables
                $tables = [];
                $result = $db->fetchAll("SHOW TABLES");
                foreach ($result as $row) {
                    $tables[] = array_values($row)[0];
                }
                
                // Start building SQL dump
                $sqlDump = "-- GateWey Requisition Management System\n";
                $sqlDump .= "-- Database Backup\n";
                $sqlDump .= "-- Generated: " . date(DATETIME_FORMAT) . "\n";
                $sqlDump .= "-- Database: " . DB_NAME . "\n";
                $sqlDump .= "-- MySQL Server Version: " . $db->getConnection()->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n\n";
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
                    
                    // Get CREATE TABLE statement
                    $createTableResult = $db->fetchOne("SHOW CREATE TABLE `{$table}`");
                    $sqlDump .= $createTableResult['Create Table'] . ";\n";
                    
                    // Get table data
                    $rows = $db->fetchAll("SELECT * FROM `{$table}`");
                    
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
                        $logSql = "INSERT INTO audit_log (user_id, action_type, action_description, ip_address, created_at)
                                   VALUES (?, ?, ?, ?, NOW())";
                        $logParams = [
                            Session::getUserId(),
                            AUDIT_BACKUP_CREATED,
                            "Database backup created: {$filename}"
                        ];
                        
                        if (LOG_IP_ADDRESS) {
                            $logParams[] = $_SERVER['REMOTE_ADDR'] ?? '';
                        } else {
                            $logParams[] = null;
                        }
                        
                        $db->execute($logSql, $logParams);
                    }
                    
                    $successMessage = "Database backup created successfully: {$filename}";
                } else {
                    $errors[] = 'Failed to write backup file. Please check directory permissions.';
                }
                
            } catch (Exception $e) {
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
                        $logSql = "INSERT INTO audit_log (user_id, action_type, action_description, ip_address, created_at)
                                   VALUES (?, ?, ?, ?, NOW())";
                        $logParams = [
                            Session::getUserId(),
                            'backup_deleted',
                            "Database backup deleted: {$filename}"
                        ];
                        
                        if (LOG_IP_ADDRESS) {
                            $logParams[] = $_SERVER['REMOTE_ADDR'] ?? '';
                        } else {
                            $logParams[] = null;
                        }
                        
                        $db->execute($logSql, $logParams);
                    }
                    
                    $successMessage = "Backup file deleted successfully.";
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

// Custom CSS
$customCSS = "
.backup-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 8px;
    margin-bottom: 0.75rem;
    background-color: var(--background, #fff);
}
.backup-item:hover {
    background-color: var(--background-secondary, #f8f9fa);
}
.backup-info {
    flex: 1;
}
.backup-actions {
    display: flex;
    gap: 0.5rem;
}
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text-muted, #6c757d);
}
";
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Database Backup</h1>
            <p class="content-subtitle">Create and manage database backups</p>
        </div>
        <div>
            <a href="<?php echo APP_URL; ?>/admin/index.php" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Success Message -->
<?php if ($successMessage): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($successMessage); ?>
    </div>
<?php endif; ?>

<!-- Error Messages -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Error:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Create Backup -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-plus-circle"></i> Create New Backup</h5>
            </div>
            <div class="card-body">
                <p style="font-size: var(--font-size-sm);">
                    Create a complete backup of the database including all tables, data, and structure.
                </p>
                
                <form method="POST" action="" data-loading>
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                    <input type="hidden" name="action" value="create_backup">
                    
                    <div class="alert alert-info mb-3">
                        <small>
                            <i class="fas fa-info-circle"></i>
                            <strong>Database:</strong> <?php echo htmlspecialchars(DB_NAME); ?><br>
                            <strong>Location:</strong> /backups/
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-database"></i> Create Backup Now
                    </button>
                </form>
            </div>
        </div>
        
        <!-- System Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-server"></i> System Information</h5>
            </div>
            <div class="card-body">
                <div style="font-size: var(--font-size-sm);">
                    <p class="mb-2">
                        <strong>Database Host:</strong><br>
                        <?php echo htmlspecialchars(DB_HOST); ?>
                    </p>
                    <p class="mb-2">
                        <strong>Database Name:</strong><br>
                        <?php echo htmlspecialchars(DB_NAME); ?>
                    </p>
                    <p class="mb-2">
                        <strong>MySQL Version:</strong><br>
                        <?php echo htmlspecialchars($db->getConnection()->getAttribute(PDO::ATTR_SERVER_VERSION)); ?>
                    </p>
                    <p class="mb-0">
                        <strong>Total Tables:</strong><br>
                        <?php 
                        $tableCount = $db->fetchOne("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?", [DB_NAME]);
                        echo htmlspecialchars($tableCount['count']); 
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Existing Backups -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history"></i> Backup History
                    <span class="badge badge-primary"><?php echo count($backupFiles); ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($backupFiles)): ?>
                    <div class="empty-state">
                        <i class="fas fa-database" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="mt-3 mb-0">No backups found. Create your first backup to get started.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($backupFiles as $backup): ?>
                        <div class="backup-item">
                            <div class="backup-info">
                                <h6 class="mb-1">
                                    <i class="fas fa-file-archive"></i>
                                    <?php echo htmlspecialchars($backup['filename']); ?>
                                </h6>
                                <div style="font-size: var(--font-size-sm); color: var(--text-muted, #6c757d);">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date(DATETIME_FORMAT, $backup['date']); ?>
                                    &nbsp;&nbsp;|&nbsp;&nbsp;
                                    <i class="fas fa-hdd"></i>
                                    <?php echo number_format($backup['size'] / 1024, 2); ?> KB
                                </div>
                            </div>
                            <div class="backup-actions">
                                <!-- Download Button -->
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                                    <input type="hidden" name="action" value="download_backup">
                                    <input type="hidden" name="filename" value="<?php echo htmlspecialchars($backup['filename']); ?>">
                                    <button type="submit" class="btn btn-sm btn-primary" title="Download">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </form>
                                
                                <!-- Delete Button -->
                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this backup? This action cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                                    <input type="hidden" name="action" value="delete_backup">
                                    <input type="hidden" name="filename" value="<?php echo htmlspecialchars($backup['filename']); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Important Notes -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle"></i> Important Information</h5>
            </div>
            <div class="card-body">
                <h6>Backup Best Practices:</h6>
                <ul style="font-size: var(--font-size-sm);">
                    <li>Create regular backups before major system updates</li>
                    <li>Store backups in multiple locations (local and cloud)</li>
                    <li>Test backup restoration periodically</li>
                    <li>Keep at least 3-5 recent backups for redundancy</li>
                    <li>Secure backup files with appropriate permissions</li>
                </ul>
                
                <hr>
                
                <h6>Restore Instructions:</h6>
                <ol style="font-size: var(--font-size-sm);">
                    <li>Download the backup file you want to restore</li>
                    <li>Access your database through phpMyAdmin or MySQL command line</li>
                    <li>Drop existing database or create new one</li>
                    <li>Import the SQL backup file</li>
                    <li>Verify data integrity after restoration</li>
                </ol>
                
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle"></i>
                    <small><strong>Warning:</strong> Restoring a backup will overwrite all current data. Always create a backup of current data before restoration.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
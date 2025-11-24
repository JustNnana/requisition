<?php
/**
 * GateWey Requisition Management System
 * System Settings - Database Backup
 * 
 * File: admin/settings/backup.php
 */

define('APP_ACCESS', true);
define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../helpers/permissions.php';

Session::init();
require_once __DIR__ . '/../../middleware/auth-check.php';
require_once __DIR__ . '/../../middleware/role-check.php';
checkRole(ROLE_SUPER_ADMIN);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token.';
        $messageType = 'error';
    } else {
        $action = $_POST['action'];
        
        if ($action === 'backup') {
            // Perform database backup
            $backupDir = DB_BACKUP_DIR;
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
                // Create .htaccess to prevent direct access
                file_put_contents($backupDir . '/.htaccess', "Deny from all");
            }
            
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $backupDir . '/' . $filename;
            
            // MySQL dump command
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s 2>&1',
                escapeshellarg(DB_USER),
                escapeshellarg(DB_PASS),
                escapeshellarg(DB_HOST),
                escapeshellarg(DB_NAME),
                escapeshellarg($filepath)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($filepath) && filesize($filepath) > 0) {
                $message = "Database backup created successfully: {$filename}";
                $messageType = 'success';
                
                // Log the backup
                $db = Database::getInstance();
                $auditData = [
                    'user_id' => Session::getUserId(),
                    'action_type' => AUDIT_BACKUP_CREATED,
                    'details' => "Database backup created: {$filename}",
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $db->insert('audit_log', $auditData);
            } else {
                $message = "Failed to create database backup.";
                if (!empty($output)) {
                    $message .= " Error: " . implode("\n", $output);
                }
                $messageType = 'error';
                
                // Clean up failed backup file
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
        }
    }
}

// Get existing backups
$backupDir = DB_BACKUP_DIR;
$backups = [];

if (is_dir($backupDir)) {
    $files = glob($backupDir . '/backup_*.sql');
    foreach ($files as $file) {
        $backups[] = [
            'filename' => basename($file),
            'filepath' => $file,
            'size' => filesize($file),
            'date' => filemtime($file)
        ];
    }
    // Sort by date descending
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

$pageTitle = 'Database Backup';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Database Backup</h1>
            <p class="content-subtitle">Create and manage database backups</p>
        </div>
        <a href="general.php" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to Settings</a>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-database"></i> Create Backup</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Create a complete backup of the database.</p>
                
                <form method="POST">
                    <?php echo Session::csrfField(); ?>
                    <input type="hidden" name="action" value="backup">
                    
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-download"></i> Create Backup Now
                    </button>
                </form>
                
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i>
                    <strong>Database:</strong> <?php echo htmlspecialchars(DB_NAME); ?><br>
                    <strong>Host:</strong> <?php echo htmlspecialchars(DB_HOST); ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-history"></i> Backup History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($backups)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-database fa-3x mb-3" style="opacity: 0.3;"></i>
                        <p>No backups found</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Filename</th>
                                    <th>Date Created</th>
                                    <th>Size</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backups as $backup): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-file-archive text-muted"></i>
                                            <?php echo htmlspecialchars($backup['filename']); ?>
                                        </td>
                                        <td>
                                            <?php echo date('M d, Y', $backup['date']); ?><br>
                                            <small class="text-muted"><?php echo date('H:i:s', $backup['date']); ?></small>
                                        </td>
                                        <td><?php echo format_file_size($backup['size']); ?></td>
                                        <td class="text-end">
                                            <a href="download-backup.php?file=<?php echo urlencode($backup['filename']); ?>" 
                                               class="btn btn-sm btn-ghost" 
                                               title="Download Backup">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Backup Information -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-question-circle"></i> Backup Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>What is backed up?</h6>
                <ul>
                    <li>All database tables and data</li>
                    <li>Table structures and indexes</li>
                    <li>User accounts and permissions</li>
                    <li>All requisitions and audit logs</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Best Practices</h6>
                <ul>
                    <li>Create regular backups (daily or weekly)</li>
                    <li>Store backups in a secure location</li>
                    <li>Test backup restoration periodically</li>
                    <li>Keep multiple backup versions</li>
                </ul>
            </div>
        </div>
        
        <div class="alert alert-warning mt-3">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Important:</strong> Backups do NOT include uploaded files (invoices, receipts, documents). These must be backed up separately from the <code>/uploads</code> directory.
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
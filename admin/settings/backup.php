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

Session::start();
require_once __DIR__ . '/../../middleware/auth-check.php';
require_once __DIR__ . '/../../middleware/role-check.php';
checkRole(ROLE_SUPER_ADMIN);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!Session::validateCSRF($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token.';
        $messageType = 'error';
    } else {
        $action = $_POST['action'];
        
        if ($action === 'backup') {
            // Perform database backup
            $backupDir = __DIR__ . '/../../backups';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $backupDir . '/' . $filename;
            
            // MySQL dump command
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                DB_USER,
                DB_PASS,
                DB_HOST,
                DB_NAME,
                escapeshellarg($filepath)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                $message = "Database backup created successfully: {$filename}";
                $messageType = 'success';
            } else {
                $message = "Failed to create database backup. Error code: {$returnCode}";
                $messageType = 'error';
            }
        }
    }
}

// Get existing backups
$backupDir = __DIR__ . '/../../backups';
$backups = [];

if (is_dir($backupDir)) {
    $files = glob($backupDir . '/backup_*.sql');
    foreach ($files as $file) {
        $backups[] = [
            'filename' => basename($file),
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
                    <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRF(); ?>">
                    <input type="hidden" name="action" value="backup">
                    
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-download"></i> Create Backup Now
                    </button>
                </form>
                
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> Backups are stored in the <code>/backups</code> directory.
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
                                        <td>
                                            <?php 
                                            $size = $backup['size'];
                                            if ($size > 1048576) {
                                                echo number_format($size / 1048576, 2) . ' MB';
                                            } elseif ($size > 1024) {
                                                echo number_format($size / 1024, 2) . ' KB';
                                            } else {
                                                echo $size . ' B';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="../../backups/<?php echo htmlspecialchars($backup['filename']); ?>" 
                                               class="btn btn-sm btn-ghost" 
                                               download
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
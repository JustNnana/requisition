<?php
/**
 * GateWey Requisition Management System
 * Debug Script for Rejection Email Issue
 * 
 * File: test-rejection-email.php
 * Purpose: Test and debug rejection email sending
 * 
 * HOW TO USE:
 * 1. Upload this file to your root directory
 * 2. Access it via: http://request.gatewey.com.ng/test-rejection-email.php
 * 3. It will show you diagnostic information about rejection emails
 */

// Initialize app
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/constants.php';
require_once 'config/email.php';

// Auto-load classes
spl_autoload_register(function($class) {
    $classFile = CLASSES_PATH . '/' . $class . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

// Set content type
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejection Email Debugging</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .section {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #009F6C;
            padding-bottom: 10px;
        }
        h2 {
            color: #009F6C;
            margin-top: 0;
        }
        .success {
            padding: 15px;
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            padding: 15px;
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
            border-radius: 4px;
            margin: 10px 0;
        }
        .warning {
            padding: 15px;
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            padding: 15px;
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
            border-radius: 4px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #009F6C;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background: #f5f5f5;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border-left: 4px solid #009F6C;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #009F6C;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn:hover {
            background: #007a54;
        }
    </style>
</head>
<body>
    <h1>🔍 Rejection Email Debugging Tool</h1>
    
    <?php
    // Database connection test
    echo '<div class="section">';
    echo '<h2>1️⃣ Database Connection</h2>';
    try {
        $db = Database::getInstance();
        echo '<div class="success">✅ Database connection successful</div>';
    } catch (Exception $e) {
        echo '<div class="error">❌ Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
        die();
    }
    echo '</div>';
    
    // Check requisition_approvals table structure
    echo '<div class="section">';
    echo '<h2>2️⃣ Requisition Approvals Table Structure</h2>';
    try {
        $columns = $db->fetchAll("DESCRIBE requisition_approvals");
        echo '<table>';
        echo '<tr><th>Column Name</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
        foreach ($columns as $col) {
            echo '<tr>';
            echo '<td><strong>' . htmlspecialchars($col['Field']) . '</strong></td>';
            echo '<td>' . htmlspecialchars($col['Type']) . '</td>';
            echo '<td>' . htmlspecialchars($col['Null']) . '</td>';
            echo '<td>' . htmlspecialchars($col['Key']) . '</td>';
            echo '<td>' . htmlspecialchars($col['Default']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        // Check for expected columns
        $columnNames = array_column($columns, 'Field');
        $hasUserId = in_array('user_id', $columnNames);
        $hasApproverId = in_array('approver_id', $columnNames);
        $hasCreatedAt = in_array('created_at', $columnNames);
        $hasApprovedAt = in_array('approved_at', $columnNames);
        
        echo '<div class="info">';
        echo '<strong>Column Check:</strong><br>';
        echo '• user_id: ' . ($hasUserId ? '✅ Found' : '❌ Missing') . '<br>';
        echo '• approver_id: ' . ($hasApproverId ? '✅ Found' : '❌ Not found (this is OK)') . '<br>';
        echo '• created_at: ' . ($hasCreatedAt ? '✅ Found' : '❌ Missing') . '<br>';
        echo '• approved_at: ' . ($hasApprovedAt ? '✅ Found' : '❌ Not found (this is OK)') . '<br>';
        echo '</div>';
        
        if (!$hasUserId || !$hasCreatedAt) {
            echo '<div class="error">';
            echo '<strong>⚠️ ISSUE DETECTED:</strong><br>';
            echo 'The rejection email template expects columns <code>user_id</code> and <code>created_at</code>, but they were not found in the table.';
            echo '</div>';
        }
        
    } catch (Exception $e) {
        echo '<div class="error">❌ Failed to check table structure: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    echo '</div>';
    
    // Check recent rejections
    echo '<div class="section">';
    echo '<h2>3️⃣ Recent Rejection Records</h2>';
    try {
        $rejections = $db->fetchAll(
            "SELECT ra.*, u.first_name, u.last_name, r.requisition_number
             FROM requisition_approvals ra
             INNER JOIN users u ON ra.user_id = u.id
             INNER JOIN requisitions r ON ra.requisition_id = r.id
             WHERE ra.action = ?
             ORDER BY ra.created_at DESC
             LIMIT 5",
            [APPROVAL_REJECTED]
        );
        
        if (empty($rejections)) {
            echo '<div class="warning">⚠️ No rejection records found in the database</div>';
        } else {
            echo '<div class="success">✅ Found ' . count($rejections) . ' recent rejection(s)</div>';
            echo '<table>';
            echo '<tr><th>Requisition</th><th>Rejected By</th><th>Comments</th><th>Date</th></tr>';
            foreach ($rejections as $rej) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($rej['requisition_number']) . '</td>';
                echo '<td>' . htmlspecialchars($rej['first_name'] . ' ' . $rej['last_name']) . '</td>';
                echo '<td>' . htmlspecialchars(substr($rej['comments'] ?? 'No comment', 0, 50)) . '</td>';
                echo '<td>' . htmlspecialchars($rej['created_at']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    } catch (Exception $e) {
        echo '<div class="error">❌ Query failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<div class="warning">';
        echo '<strong>This error suggests the column name mismatch issue!</strong><br>';
        echo 'The query is trying to use <code>user_id</code> but your table might have <code>approver_id</code> instead.';
        echo '</div>';
    }
    echo '</div>';
    
    // Check email configuration
    echo '<div class="section">';
    echo '<h2>4️⃣ Email Configuration</h2>';
    echo '<table>';
    echo '<tr><th>Setting</th><th>Value</th><th>Status</th></tr>';
    echo '<tr><td>SMTP Enabled</td><td>' . (SMTP_ENABLED ? 'Yes' : 'No') . '</td><td>' . (SMTP_ENABLED ? '<span style="color: green;">✅</span>' : '<span style="color: red;">❌</span>') . '</td></tr>';
    echo '<tr><td>SMTP Host</td><td>' . htmlspecialchars(SMTP_HOST) . '</td><td>✅</td></tr>';
    echo '<tr><td>SMTP Port</td><td>' . htmlspecialchars(SMTP_PORT) . '</td><td>✅</td></tr>';
    echo '<tr><td>Send Rejection Emails</td><td>' . (SEND_REQUISITION_REJECTED_EMAIL ? 'Yes' : 'No') . '</td><td>' . (SEND_REQUISITION_REJECTED_EMAIL ? '<span style="color: green;">✅</span>' : '<span style="color: red;">❌</span>') . '</td></tr>';
    echo '</table>';
    
    if (!SEND_REQUISITION_REJECTED_EMAIL) {
        echo '<div class="error">⚠️ Rejection emails are DISABLED in config/email.php. Set <code>SEND_REQUISITION_REJECTED_EMAIL</code> to <code>true</code></div>';
    }
    echo '</div>';
    
    // Check email template file
    echo '<div class="section">';
    echo '<h2>5️⃣ Email Template File</h2>';
    $templatePath = EMAIL_TEMPLATES_PATH . '/requisition-rejected.php';
    if (file_exists($templatePath)) {
        echo '<div class="success">✅ Template file exists: ' . htmlspecialchars($templatePath) . '</div>';
        
        // Check file contents for the issue
        $templateContent = file_get_contents($templatePath);
        $hasApproverIdIssue = strpos($templateContent, 'ra.approver_id') !== false;
        $hasApprovedAtIssue = strpos($templateContent, 'ra.approved_at') !== false;
        
        if ($hasApproverIdIssue || $hasApprovedAtIssue) {
            echo '<div class="error">';
            echo '<strong>🐛 BUG FOUND IN TEMPLATE!</strong><br>';
            if ($hasApproverIdIssue) {
                echo '• Template uses <code>ra.approver_id</code> but should use <code>ra.user_id</code><br>';
            }
            if ($hasApprovedAtIssue) {
                echo '• Template uses <code>ra.approved_at</code> but should use <code>ra.created_at</code><br>';
            }
            echo '</div>';
        } else {
            echo '<div class="success">✅ Template appears to use correct column names</div>';
        }
    } else {
        echo '<div class="error">❌ Template file not found: ' . htmlspecialchars($templatePath) . '</div>';
    }
    echo '</div>';
    
    // Solution
    echo '<div class="section">';
    echo '<h2>6️⃣ Solution</h2>';
    echo '<div class="info">';
    echo '<strong>The issue is in the requisition-rejected.php template file.</strong><br><br>';
    echo 'The template query uses incorrect column names that don\'t match the database schema:';
    echo '</div>';
    
    echo '<h3>❌ Current (WRONG):</h3>';
    echo '<pre>';
    echo htmlspecialchars('$rejectionSql = "SELECT ra.*, u.first_name, u.last_name, u.role_id
                 FROM requisition_approvals ra
                 INNER JOIN users u ON ra.approver_id = u.id  // ❌ Wrong column
                 WHERE ra.requisition_id = ? AND ra.action = ?
                 ORDER BY ra.approved_at DESC  // ❌ Wrong column
                 LIMIT 1";');
    echo '</pre>';
    
    echo '<h3>✅ Corrected (RIGHT):</h3>';
    echo '<pre>';
    echo htmlspecialchars('$rejectionSql = "SELECT ra.*, u.first_name, u.last_name, u.role_id
                 FROM requisition_approvals ra
                 INNER JOIN users u ON ra.user_id = u.id  // ✅ Correct column
                 WHERE ra.requisition_id = ? AND ra.action = ?
                 ORDER BY ra.created_at DESC  // ✅ Correct column
                 LIMIT 1";');
    echo '</pre>';
    
    echo '<div class="warning">';
    echo '<strong>📝 Steps to Fix:</strong><br>';
    echo '1. Replace <code>/emails/templates/requisition-rejected.php</code> with the corrected version<br>';
    echo '2. Change <code>ra.approver_id</code> to <code>ra.user_id</code><br>';
    echo '3. Change <code>ra.approved_at</code> to <code>ra.created_at</code><br>';
    echo '4. Test by rejecting a requisition';
    echo '</div>';
    echo '</div>';
    
    // Email log check
    echo '<div class="section">';
    echo '<h2>7️⃣ Recent Email Log Entries</h2>';
    $logPath = LOGS_PATH . '/email.log';
    if (file_exists($logPath)) {
        $logContent = file_get_contents($logPath);
        $logLines = explode("\n", $logContent);
        $recentLines = array_slice(array_reverse($logLines), 0, 20);
        $recentLines = array_reverse($recentLines);
        
        echo '<pre style="max-height: 400px; overflow-y: auto;">';
        foreach ($recentLines as $line) {
            if (!empty(trim($line))) {
                // Highlight rejection emails
                if (stripos($line, 'reject') !== false) {
                    echo '<span style="color: #dc3545; font-weight: bold;">' . htmlspecialchars($line) . '</span>' . "\n";
                } else {
                    echo htmlspecialchars($line) . "\n";
                }
            }
        }
        echo '</pre>';
    } else {
        echo '<div class="warning">⚠️ Email log file not found. Enable <code>EMAIL_LOG_ENABLED</code> in config/email.php</div>';
    }
    echo '</div>';
    ?>
    
    <div class="section">
        <h2>8️⃣ Quick Actions</h2>
        <a href="<?php echo APP_URL; ?>/dashboard" class="btn">← Back to Dashboard</a>
        <a href="?refresh=1" class="btn">🔄 Refresh Check</a>
    </div>
    
    <div class="section" style="background: #f8f9fa; border-left: 4px solid #17a2b8;">
        <p style="margin: 0; color: #666; font-size: 14px;">
            <strong>ℹ️ Note:</strong> Delete this file after debugging for security reasons.
        </p>
    </div>
</body>
</html>
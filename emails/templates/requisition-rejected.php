<?php
/**
 * GateWey Requisition Management System
 * Requisition Rejected Email Template - FIXED VERSION
 * 
 * File: emails/templates/requisition-rejected.php
 * Purpose: Email sent when a requisition is rejected
 * 
 * Recipients: Requester (can edit and resubmit)
 * 
 * FIXES:
 * - Changed 'approver_id' to 'user_id' in JOIN
 * - Changed 'approved_at' to 'created_at' in ORDER BY
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

// Extract data
$requisition = $data['requisition'] ?? [];
$recipient = $data['recipient'] ?? [];
$appUrl = $data['app_url'] ?? APP_URL;

// Get rejection details - FIXED QUERY
$db = Database::getInstance();
$rejectionSql = "SELECT ra.*, u.first_name, u.last_name, u.role_id
                 FROM requisition_approvals ra
                 INNER JOIN users u ON ra.user_id = u.id
                 WHERE ra.requisition_id = ? AND ra.action = ?
                 ORDER BY ra.created_at DESC
                 LIMIT 1";
$rejection = $db->fetchOne($rejectionSql, [$requisition['id'], APPROVAL_REJECTED]);

$rejectedBy = 'An approver';
$rejectionReason = 'No reason provided';

if ($rejection) {
    $rejectedBy = $rejection['first_name'] . ' ' . $rejection['last_name'];
    
    // Get role name from role_at_approval field
    if (!empty($rejection['role_at_approval'])) {
        $rejectedBy .= ' (' . $rejection['role_at_approval'] . ')';
    }
    
    $rejectionReason = $rejection['comments'] ?? 'No reason provided';
}

// Prepare template data
$title = 'Requisition Rejected';
$preheader = "Requisition {$requisition['requisition_number']} has been rejected";

// Build content
ob_start();
?>

<p>Hello <strong><?php echo htmlspecialchars($recipient['name']); ?></strong>,</p>

<p>Your requisition has been rejected and requires your attention.</p>

<div class="info-box">
    <h3>📋 Requisition Details</h3>
    <div class="info-row">
        <span class="info-label">Requisition Number:</span>
        <span class="info-value"><strong><?php echo htmlspecialchars($requisition['requisition_number']); ?></strong></span>
    </div>
    <?php if (!empty($requisition['department_name'])): ?>
    <div class="info-row">
        <span class="info-label">Department:</span>
        <span class="info-value"><?php echo htmlspecialchars($requisition['department_name']); ?></span>
    </div>
    <?php endif; ?>
    <div class="info-row">
        <span class="info-label">Total Amount:</span>
        <span class="info-value"><strong style="color: #009F6C; font-size: 18px;">₦<?php echo number_format($requisition['total_amount'], 2); ?></strong></span>
    </div>
    <div class="info-row">
        <span class="info-label">Purpose:</span>
        <span class="info-value"><?php echo nl2br(htmlspecialchars($requisition['purpose'])); ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Status:</span>
        <span class="info-value"><span class="status-badge status-rejected">Rejected</span></span>
    </div>
</div>

<div class="alert alert-danger">
    <strong>❌ Rejection Details</strong><br>
    <strong>Rejected By:</strong> <?php echo htmlspecialchars($rejectedBy); ?><br>
    <strong>Reason:</strong><br>
    <div style="margin-top: 10px; padding: 10px; background-color: rgba(255,255,255,0.5); border-radius: 4px;">
        <?php echo nl2br(htmlspecialchars($rejectionReason)); ?>
    </div>
</div>

<div class="alert alert-warning">
    <strong>📝 What's Next?</strong><br>
    You can now review the rejection reason, make necessary changes to your requisition, and resubmit it for approval.
</div>

<div class="button-container">
    <a href="<?php echo build_encrypted_url($appUrl . '/requisitions/view.php', $requisition['id']); ?>" class="button">
        Review & Edit Requisition →
    </a>
</div>

<p style="margin-top: 30px; color: #666; font-size: 14px;">
    <strong>Tips for Resubmission:</strong>
</p>
<ul style="color: #666; font-size: 14px; line-height: 1.8;">
    <li>Carefully review the rejection reason provided above</li>
    <li>Make all necessary corrections to address the concerns</li>
    <li>Add any additional supporting documents if required</li>
    <li>Resubmit the requisition when you're ready</li>
</ul>

<?php
$content = ob_get_clean();

// Include base template
include __DIR__ . '/base.php';
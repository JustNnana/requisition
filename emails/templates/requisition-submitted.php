<?php
/**
 * GateWey Requisition Management System
 * Requisition Submitted Email Template
 * 
 * File: emails/templates/requisition-submitted.php
 * Purpose: Email sent when a new requisition is submitted
 * 
 * Recipients: Next Approver + Requester (confirmation)
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

// Extract data
$requisition = $data['requisition'] ?? [];
$recipient = $data['recipient'] ?? [];
$appUrl = $data['app_url'] ?? APP_URL;

// Prepare template data
$title = 'New Requisition Submitted';
$preheader = "Requisition {$requisition['requisition_number']} requires your attention";

// Determine if recipient is approver or requester
$isApprover = $recipient['id'] != $requisition['user_id'];

// Build content
ob_start();
?>

<?php if ($isApprover): ?>
    <p>Hello <strong><?php echo htmlspecialchars($recipient['name']); ?></strong>,</p>
    
    <p>A new requisition has been submitted and requires your approval.</p>
    
    <div class="info-box">
        <h3>📋 Requisition Details</h3>
        <div class="info-row">
            <span class="info-label">Requisition Number:</span>
            <span class="info-value"><strong><?php echo htmlspecialchars($requisition['requisition_number']); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Submitted By:</span>
            <span class="info-value"><?php echo htmlspecialchars($requisition['requester_first_name'] . ' ' . $requisition['requester_last_name']); ?></span>
        </div>
        <?php if (!empty($requisition['department_name'])): ?>
        <div class="info-row">
            <span class="info-label">Department:</span>
            <span class="info-value"><?php echo htmlspecialchars($requisition['department_name']); ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <span class="info-label">Date Submitted:</span>
            <span class="info-value"><?php echo date('M d, Y', strtotime($requisition['created_at'])); ?></span>
        </div>
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
            <span class="info-value"><span class="status-badge status-pending">Pending Your Approval</span></span>
        </div>
    </div>
    
    <div class="alert alert-info">
        <strong>⏰ Action Required</strong><br>
        Please review this requisition and take appropriate action (Approve or Reject).
    </div>
    
    <div class="button-container">
        <a href="<?php echo build_encrypted_url($appUrl . '/requisitions/view.php', $requisition['id']); ?>" class="button">
            Review Requisition →
        </a>
    </div>
    
    <p style="margin-top: 30px; color: #666; font-size: 14px;">
        You can also view this requisition by logging into your dashboard and navigating to "Pending Approvals".
    </p>
    
<?php else: ?>
    <p>Hello <strong><?php echo htmlspecialchars($recipient['name']); ?></strong>,</p>
    
    <p>Your requisition has been successfully submitted and is now awaiting approval.</p>
    
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
            <span class="info-label">Date Submitted:</span>
            <span class="info-value"><?php echo date('M d, Y', strtotime($requisition['created_at'])); ?></span>
        </div>
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
            <span class="info-value"><span class="status-badge status-pending">Awaiting Approval</span></span>
        </div>
    </div>
    
    <div class="alert alert-success">
        <strong>✓ Submission Confirmed</strong><br>
        Your requisition has been forwarded to the appropriate approver. You will receive a notification once it has been reviewed.
    </div>
    
    <div class="button-container">
        <a href="<?php echo build_encrypted_url($appUrl . '/requisitions/view.php', $requisition['id']); ?>" class="button">
            View Requisition →
        </a>
    </div>
    
    <p style="margin-top: 30px; color: #666; font-size: 14px;">
        You can track the progress of your requisition by logging into your dashboard.
    </p>
<?php endif; ?>

<?php
$content = ob_get_clean();

// Include base template
include __DIR__ . '/base.php';
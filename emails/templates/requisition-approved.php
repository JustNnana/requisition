<?php
/**
 * GateWey Requisition Management System
 * Requisition Approved Email Template
 * 
 * File: emails/templates/requisition-approved.php
 * Purpose: Email sent when a requisition is approved
 * 
 * Recipients: Requester + Next Approver (if applicable)
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

// Extract data
$requisition = $data['requisition'] ?? [];
$recipient = $data['recipient'] ?? [];
$appUrl = $data['app_url'] ?? APP_URL;

// Prepare template data
$title = 'Requisition Approved';
$preheader = "Requisition {$requisition['requisition_number']} has been approved";

// Determine if recipient is next approver or requester
$isNextApprover = $recipient['id'] != $requisition['user_id'];

// Get status name
$statusName = get_status_name($requisition['status']);

// Build content
ob_start();
?>

<?php if ($isNextApprover): ?>
    <p>Hello <strong><?php echo htmlspecialchars($recipient['name']); ?></strong>,</p>
    
    <p>A requisition has been approved and now requires your attention.</p>
    
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
            <span class="info-label">Total Amount:</span>
            <span class="info-value"><strong style="color: #667eea; font-size: 18px;">₦<?php echo number_format($requisition['total_amount'], 2); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Purpose:</span>
            <span class="info-value"><?php echo nl2br(htmlspecialchars($requisition['purpose'])); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Current Status:</span>
            <span class="info-value"><span class="status-badge status-pending">Awaiting Your Action</span></span>
        </div>
    </div>
    
    <div class="alert alert-info">
        <strong>⏰ Action Required</strong><br>
        This requisition has been approved by the previous approver and now requires your review.
    </div>
    
    <div class="button-container">
        <a href="<?php echo $appUrl; ?>/requisitions/view.php?id=<?php echo $requisition['id']; ?>" class="button">
            Review Requisition →
        </a>
    </div>

<?php else: ?>
    <p>Hello <strong><?php echo htmlspecialchars($recipient['name']); ?></strong>,</p>
    
    <p>Great news! Your requisition has been approved and is progressing through the approval workflow.</p>
    
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
            <span class="info-value"><strong style="color: #667eea; font-size: 18px;">₦<?php echo number_format($requisition['total_amount'], 2); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Purpose:</span>
            <span class="info-value"><?php echo nl2br(htmlspecialchars($requisition['purpose'])); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Current Status:</span>
            <span class="info-value"><span class="status-badge status-approved"><?php echo htmlspecialchars($statusName); ?></span></span>
        </div>
    </div>
    
    <div class="alert alert-success">
        <strong>✓ Approval Received</strong><br>
        Your requisition has been approved and moved to the next stage in the approval process. 
        <?php if ($requisition['status'] == STATUS_APPROVED_FOR_PAYMENT): ?>
        It is now ready for payment processing by the Finance team.
        <?php else: ?>
        It will be reviewed by the next approver shortly.
        <?php endif; ?>
    </div>
    
    <div class="button-container">
        <a href="<?php echo $appUrl; ?>/requisitions/view.php?id=<?php echo $requisition['id']; ?>" class="button">
            View Requisition →
        </a>
    </div>
    
    <p style="margin-top: 30px; color: #666; font-size: 14px;">
        You will receive another notification when there are further updates on this requisition.
    </p>
<?php endif; ?>

<?php
$content = ob_get_clean();

// Include base template
include __DIR__ . '/base.php';
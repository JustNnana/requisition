<?php
/**
 * GateWey Requisition Management System
 * Requisition Cancelled Email Template
 * 
 * File: emails/templates/requisition-cancelled.php
 * Purpose: Email sent when a requisition is cancelled
 * 
 * Recipients: Line Manager, MD, Finance Manager
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

// Extract data
$requisition = $data['requisition'] ?? [];
$recipient = $data['recipient'] ?? [];
$appUrl = $data['app_url'] ?? APP_URL;

// Prepare template data
$title = 'Requisition Cancelled';
$preheader = "Requisition {$requisition['requisition_number']} has been cancelled";

// Build content
ob_start();
?>

<p>Hello <strong><?php echo htmlspecialchars($recipient['name']); ?></strong>,</p>

<p>A requisition has been cancelled by the requester.</p>

<div class="info-box">
    <h3>📋 Requisition Details</h3>
    <div class="info-row">
        <span class="info-label">Requisition Number:</span>
        <span class="info-value"><strong><?php echo htmlspecialchars($requisition['requisition_number']); ?></strong></span>
    </div>
    <div class="info-row">
        <span class="info-label">Cancelled By:</span>
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
        <span class="info-label">Status:</span>
        <span class="info-value"><span class="status-badge status-rejected">Cancelled</span></span>
    </div>
</div>

<div class="alert alert-warning">
    <strong>ℹ️ Cancellation Notice</strong><br>
    This requisition has been cancelled by the requester. No further action is required from you.
</div>

<div class="button-container">
    <a href="<?php echo $appUrl; ?>/requisitions/view.php?id=<?php echo $requisition['id']; ?>" class="button">
        View Requisition Details →
    </a>
</div>

<p style="margin-top: 30px; color: #666; font-size: 14px;">
    This is a notification email for your records. The requisition workflow has been terminated.
</p>

<?php
$content = ob_get_clean();

// Include base template
include __DIR__ . '/base.php';
<?php
/**
 * GateWey Requisition Management System
 * Requisition Paid Email Template
 * 
 * File: emails/templates/requisition-paid.php
 * Purpose: Email sent when payment has been processed
 * 
 * Recipients: Requester (must upload receipt)
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

// Extract data
$requisition = $data['requisition'] ?? [];
$recipient = $data['recipient'] ?? [];
$appUrl = $data['app_url'] ?? APP_URL;

// Prepare template data
$title = 'Payment Processed';
$preheader = "Payment has been processed for requisition {$requisition['requisition_number']}";

// Build content
ob_start();
?>

<p>Hello <strong><?php echo htmlspecialchars($recipient['name']); ?></strong>,</p>

<p>Great news! Payment has been successfully processed for your requisition.</p>

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
        <span class="info-value"><strong style="color: #28a745; font-size: 20px;">₦<?php echo number_format($requisition['total_amount'], 2); ?></strong></span>
    </div>
    <div class="info-row">
        <span class="info-label">Purpose:</span>
        <span class="info-value"><?php echo nl2br(htmlspecialchars($requisition['purpose'])); ?></span>
    </div>
    <?php if (!empty($requisition['payment_date'])): ?>
    <div class="info-row">
        <span class="info-label">Payment Date:</span>
        <span class="info-value"><?php echo date('M d, Y', strtotime($requisition['payment_date'])); ?></span>
    </div>
    <?php endif; ?>
    <?php if (!empty($requisition['payment_method'])): ?>
    <div class="info-row">
        <span class="info-label">Payment Method:</span>
        <span class="info-value"><?php echo htmlspecialchars($requisition['payment_method']); ?></span>
    </div>
    <?php endif; ?>
    <?php if (!empty($requisition['payment_reference'])): ?>
    <div class="info-row">
        <span class="info-label">Payment Reference:</span>
        <span class="info-value"><strong><?php echo htmlspecialchars($requisition['payment_reference']); ?></strong></span>
    </div>
    <?php endif; ?>
    <div class="info-row">
        <span class="info-label">Status:</span>
        <span class="info-value"><span class="status-badge status-paid">Paid</span></span>
    </div>
</div>

<div class="alert alert-success">
    <strong>✓ Payment Confirmed</strong><br>
    The payment for this requisition has been processed successfully. The Finance team has uploaded the invoice/proof of payment.
</div>

<div class="alert alert-warning">
    <strong>⚠️ Important: Receipt Required</strong><br>
    To complete this requisition, you must now upload the official receipt for this purchase. This is a mandatory step to finalize the transaction.
</div>

<div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 25px 0; border-radius: 4px;">
    <h3 style="margin: 0 0 15px 0; color: #856404; font-size: 18px;">📄 Next Steps</h3>
    <ol style="margin: 0; padding-left: 20px; color: #856404;">
        <li style="margin-bottom: 10px;">Collect the official receipt from the vendor/supplier</li>
        <li style="margin-bottom: 10px;">Ensure the receipt shows the correct amount and details</li>
        <li style="margin-bottom: 10px;">Upload a clear photo or scan of the receipt (PDF or Image format)</li>
        <li style="margin-bottom: 0;">Submit the receipt through the system</li>
    </ol>
</div>

<div class="button-container">
    <a href="<?php echo build_encrypted_url($appUrl . '/requisitions/upload-receipt.php', $requisition['id']); ?>" class="button" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
        Upload Receipt Now →
    </a>
</div>

<p style="margin-top: 30px; color: #666; font-size: 14px;">
    <strong>Supported File Formats:</strong> PDF, JPEG, JPG, PNG<br>
    <strong>Maximum File Size:</strong> 5MB
</p>

<div style="margin-top: 30px; padding: 15px; background-color: #e7f3ff; border-radius: 6px;">
    <p style="margin: 0; color: #004085; font-size: 14px;">
        <strong>💡 Tip:</strong> Uploading the receipt promptly helps maintain accurate financial records and ensures timely completion of the requisition process.
    </p>
</div>

<?php
$content = ob_get_clean();

// Include base template
include __DIR__ . '/base.php';
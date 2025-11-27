<?php
/**
 * GateWey Requisition Management System
 * View Requisition Page
 * 
 * File: requisitions/view.php
 * Purpose: Display detailed view of a single requisition
 * 
 * UPDATED: Added preview feature for images and PDFs
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';
require_once __DIR__ . '/../helpers/permissions.php';
require_once __DIR__ . '/../helpers/status-indicator.php';

// Get requisition ID
$requisitionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$requisitionId) {
    Session::setFlash('error', 'Invalid requisition ID.');
    header('Location: list.php');
    exit;
}

// Initialize objects
$requisition = new Requisition();

// Get requisition data
$reqData = $requisition->getById($requisitionId);

if (!$reqData) {
    Session::setFlash('error', 'Requisition not found.');
    header('Location: list.php');
    exit;
}

// Check permission to view
if (!can_user_view_requisition($reqData)) {
    Session::setFlash('error', 'You do not have permission to view this requisition.');
    header('Location: list.php');
    exit;
}

// Check if user can perform actions
$canEdit = can_user_edit_requisition($reqData);
$canCancel = can_user_cancel_requisition($reqData);
$canApprove = can_user_approve_requisition($reqData);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Requisition ' . $reqData['requisition_number'];

/**
 * Helper function to check if file is previewable
 */
function isPreviewable($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf']);
}

/**
 * Get preview type
 */
function getPreviewType($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        return 'image';
    } elseif ($ext === 'pdf') {
        return 'pdf';
    }
    return 'none';
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">
                Requisition <?php echo htmlspecialchars($reqData['requisition_number']); ?>
                <?php if ($reqData['is_draft']): ?>
                    <span class="badge badge-secondary">DRAFT</span>
                <?php endif; ?>
            </h1>
            <p class="content-subtitle">
                Created on <?php echo format_date($reqData['created_at'], 'F d, Y \a\t h:i A'); ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="list.php" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <?php if ($canEdit): ?>
                <a href="edit.php?id=<?php echo $reqData['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($successMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-error alert-dismissible">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($errorMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Approval Actions (Show if user can approve) -->
<?php if ($canApprove): ?>
    <div class="card mb-4" style="border-left: 4px solid var(--warning);">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 style="margin: 0; color: var(--warning);">
                        <i class="fas fa-exclamation-triangle"></i> Action Required
                    </h5>
                    <p style="margin: var(--spacing-2) 0 0 0; color: var(--text-secondary);">
                        This requisition is awaiting your approval. Please review and take action.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-danger" onclick="showRejectModal()">
                        <i class="fas fa-times-circle"></i> Reject
                    </button>
                    <button type="button" class="btn btn-success" onclick="showApproveModal()">
                        <i class="fas fa-check-circle"></i> Approve
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<!-- Receipt Upload Prompt (Show if paid but no receipt) -->
<?php if ($reqData['status'] == 'paid' && empty($reqData['receipt_file_path']) && $reqData['user_id'] == $_SESSION['user_id']): ?>
    <div class="card mb-4" style="border-left: 4px solid var(--success); background: linear-gradient(135deg, #e8f5e9 0%, #ffffff 100%);">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div style="flex: 1;">
                    <h5 style="margin: 0; color: var(--success);">
                        <i class="fas fa-check-circle"></i> Payment Completed - Receipt Required
                    </h5>
                    <p style="margin: var(--spacing-2) 0 0 0; color: var(--text-secondary);">
                        Your requisition has been paid! Please upload your receipt as proof of purchase to complete this requisition.
                    </p>
                    <?php if ($reqData['payment_date']): ?>
                        <p style="margin: var(--spacing-1) 0 0 0; color: var(--text-muted); font-size: var(--font-size-sm);">
                            <i class="fas fa-calendar-check"></i> Paid on <?php echo format_date($reqData['payment_date'], 'M d, Y \a\t h:i A'); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="upload-receipt.php?id=<?php echo $reqData['id']; ?>" class="btn btn-success btn-lg">
    <i class="fas fa-cloud-upload-alt"></i> Upload Receipt
</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<!-- Receipt Upload Modal -->
<div id="receiptUploadModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cloud-upload-alt"></i> Upload Receipt
                </h5>
            </div>
            <div class="card-body">
                <p>Please upload your receipt as proof of purchase for this requisition.</p>
                <p><strong>Requisition:</strong> <?php echo htmlspecialchars($reqData['requisition_number']); ?><br>
                <strong>Amount Paid:</strong> <?php echo format_currency($reqData['total_amount']); ?></p>
                
                <form method="POST" action="upload-receipt.php" id="receiptUploadForm" enctype="multipart/form-data">
                    <?php echo Session::csrfField(); ?>
                    <input type="hidden" name="requisition_id" value="<?php echo $reqData['id']; ?>">
                    
                    <div class="form-group">
                        <label for="receipt_file">Receipt File <span class="text-danger">*</span></label>
                        <input type="file" name="receipt_file" id="receipt_file" class="form-control" 
                               accept=".pdf,.jpg,.jpeg,.png,.gif" required>
                        <small class="form-text text-muted">
                            Accepted formats: PDF, JPG, PNG, GIF (Max size: 5MB)
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="receipt_comments">Comments (Optional)</label>
                        <textarea name="comments" id="receipt_comments" class="form-control" rows="3" 
                                  placeholder="Add any comments about this receipt..."></textarea>
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary" onclick="closeReceiptUploadModal()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-cloud-upload-alt"></i> Upload Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Status Banner -->
<div class="card" style="border-left: 4px solid var(--<?php echo get_status_color($reqData['status']); ?>);">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 style="margin: 0;">
                    <?php echo get_status_badge($reqData['status']); ?>
                </h5>
                <?php if ($reqData['rejection_reason']): ?>
                    <p style="margin: var(--spacing-2) 0 0 0; color: var(--danger);">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($reqData['rejection_reason']); ?>
                    </p>
                    <?php if ($reqData['rejected_by_first_name']): ?>
                        <p style="margin: var(--spacing-1) 0 0 0; color: var(--text-muted); font-size: var(--font-size-sm);">
                            Rejected by <?php echo htmlspecialchars($reqData['rejected_by_first_name'] . ' ' . $reqData['rejected_by_last_name']); ?>
                            on <?php echo format_date($reqData['rejected_at'], 'M d, Y \a\t h:i A'); ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <?php if ($canCancel): ?>
                <button type="button" class="btn btn-danger" onclick="confirmCancel()">
                    <i class="fas fa-times-circle"></i> Cancel Requisition
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="row mt-4">
    <!-- Left Column: Details and Items -->
    <div class="col-md-8">
        <!-- Requisition Details -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle"></i> Requisition Details
                </h5>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Requester</label>
                        <div><?php echo htmlspecialchars($reqData['first_name'] . ' ' . $reqData['last_name']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <label>Department</label>
                        <div>
                            <?php echo htmlspecialchars($reqData['department_name']); ?>
                            <span style="color: var(--text-muted); font-size: var(--font-size-sm);">
                                (<?php echo htmlspecialchars($reqData['department_code']); ?>)
                            </span>
                        </div>
                    </div>
                    
                    <?php if ($reqData['submitted_at']): ?>
                        <div class="detail-item">
                            <label>Submitted</label>
                            <div><?php echo format_date($reqData['submitted_at'], 'M d, Y \a\t h:i A'); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($reqData['current_approver_id'] && !is_final_status($reqData['status'])): ?>
                        <div class="detail-item">
                            <label>Current Approver</label>
                            <div><?php echo htmlspecialchars($reqData['approver_first_name'] . ' ' . $reqData['approver_last_name']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-4">
                    <label><strong>Purpose/Description</strong></label>
                    <p style="margin-top: var(--spacing-2); white-space: pre-wrap;">
                        <?php echo htmlspecialchars($reqData['purpose']); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Items -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list"></i> Items (<?php echo count($reqData['items']); ?>)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th>Description</th>
                                <th style="width: 12%;">Qty</th>
                                <th style="width: 15%;">Unit Price</th>
                                <th style="width: 15%;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reqData['items'] as $index => $item): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($item['item_description']); ?></td>
                                    <td><?php echo number_format($item['quantity']); ?></td>
                                    <td><?php echo format_currency($item['unit_price']); ?></td>
                                    <td><strong><?php echo format_currency($item['subtotal']); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; font-size: var(--font-size-lg); background: var(--bg-subtle);">
                                <td colspan="4" style="text-align: right;">Total Amount:</td>
                                <td style="color: var(--primary);"><?php echo format_currency($reqData['total_amount']); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Documents with Preview -->
        <?php if (!empty($reqData['documents'])): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-paperclip"></i> Attachments (<?php echo count($reqData['documents']); ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <?php foreach ($reqData['documents'] as $doc): ?>
                        <div class="uploaded-file">
                            <div class="file-info">
                                <i class="fas <?php echo FileUpload::getFileIcon($doc['file_name']); ?> file-icon"></i>
                                <div>
                                    <div style="font-weight: var(--font-weight-semibold);">
                                        <?php echo htmlspecialchars($doc['file_name']); ?>
                                        <?php if (isPreviewable($doc['file_name'])): ?>
                                            <span class="badge badge-info" style="font-size: var(--font-size-xs); margin-left: var(--spacing-2);">
                                                <i class="fas fa-eye"></i> Previewable
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: var(--font-size-sm); color: var(--text-muted);">
                                        <?php echo format_file_size($doc['file_size']); ?> • 
                                        Uploaded by <?php echo htmlspecialchars($doc['uploaded_by_name']); ?> • 
                                        <?php echo format_date($doc['uploaded_at'], 'M d, Y'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if (isPreviewable($doc['file_name'])): ?>
                                    <button type="button" class="btn btn-sm btn-secondary" 
                                            onclick="previewFile('<?php echo $doc['id']; ?>', '<?php echo htmlspecialchars($doc['file_name'], ENT_QUOTES); ?>', '<?php echo getPreviewType($doc['file_name']); ?>')">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                <?php endif; ?>
                                <a href="../api/download-file.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Right Column: Approval History -->
    <div class="col-md-4">
        <!-- Approval Timeline -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history"></i> Approval History
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($reqData['approvals'])): ?>
                    <p style="color: var(--text-muted); text-align: center; padding: var(--spacing-4) 0;">
                        <i class="fas fa-info-circle"></i><br>
                        No approvals yet
                    </p>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($reqData['approvals'] as $approval): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker <?php echo $approval['action'] == 'approved' ? 'success' : 'danger'; ?>">
                                    <i class="fas <?php echo $approval['action'] == 'approved' ? 'fa-check' : 'fa-times'; ?>"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <strong><?php echo htmlspecialchars($approval['approver_name']); ?></strong>
                                        <span class="badge badge-<?php echo $approval['action'] == 'approved' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($approval['action']); ?>
                                        </span>
                                    </div>
                                    <div class="timeline-role">
                                        <?php echo htmlspecialchars($approval['role_name']); ?>
                                    </div>
                                    <?php if ($approval['comments']): ?>
                                        <div class="timeline-comment">
                                            <i class="fas fa-comment-alt"></i>
                                            <?php echo htmlspecialchars($approval['comments']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="timeline-time">
                                        <i class="fas fa-clock"></i>
                                        <?php echo format_date($approval['created_at'], 'M d, Y \a\t h:i A'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- File Preview Modal -->
<div id="previewModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-eye"></i> <span id="previewFileName">File Preview</span>
                    </h5>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="closePreviewModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style="padding: 0; min-height: 400px;">
                <div id="previewContent" style="width: 100%; height: 70vh; display: flex; align-items: center; justify-content: center;">
                    <div class="spinner">
                        <i class="fas fa-spinner fa-spin fa-3x"></i>
                        <p style="margin-top: var(--spacing-3);">Loading preview...</p>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-secondary" onclick="closePreviewModal()">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <a id="previewDownloadBtn" href="#" class="btn btn-primary" target="_blank">
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-check-circle"></i> Approve Requisition
                </h5>
            </div>
            <div class="card-body">
                <p>Are you sure you want to approve this requisition?</p>
                <p><strong>Requisition:</strong> <?php echo htmlspecialchars($reqData['requisition_number']); ?><br>
                <strong>Amount:</strong> <?php echo format_currency($reqData['total_amount']); ?></p>
                
                <form method="POST" action="approve.php" id="approveForm">
                    <?php echo Session::csrfField(); ?>
                    <input type="hidden" name="requisition_id" value="<?php echo $reqData['id']; ?>">
                    
                    <div class="form-group">
                        <label for="approve_comments">Comments (Optional)</label>
                        <textarea name="comments" id="approve_comments" class="form-control" rows="3" 
                                  placeholder="Add any comments about this approval..."></textarea>
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary" onclick="closeApproveModal()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-times-circle"></i> Reject Requisition
                </h5>
            </div>
            <div class="card-body">
                <p>Are you sure you want to reject this requisition?</p>
                <p><strong>Requisition:</strong> <?php echo htmlspecialchars($reqData['requisition_number']); ?></p>
                
                <form method="POST" action="reject.php" id="rejectForm">
                    <?php echo Session::csrfField(); ?>
                    <input type="hidden" name="requisition_id" value="<?php echo $reqData['id']; ?>">
                    
                    <div class="form-group">
                        <label for="reject_reason">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reject_reason" class="form-control" rows="4" 
                                  placeholder="Please provide a clear reason for rejection..." required></textarea>
                        <small class="form-text text-muted">This will be visible to the requester.</small>
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times-circle"></i> Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<?php if ($canCancel): ?>
<div id="cancelModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Cancel Requisition
                </h5>
            </div>
            <div class="card-body">
                <p>Are you sure you want to cancel this requisition? This action cannot be undone.</p>
                <form method="POST" action="cancel.php">
                    <?php echo Session::csrfField(); ?>
                    <input type="hidden" name="requisition_id" value="<?php echo $reqData['id']; ?>">
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">
                            No, Keep It
                        </button>
                        <button type="submit" class="btn btn-danger">
                            Yes, Cancel Requisition
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// File Preview Functions
function previewFile(fileId, fileName, type) {
    const modal = document.getElementById('previewModal');
    const content = document.getElementById('previewContent');
    const fileNameEl = document.getElementById('previewFileName');
    const downloadBtn = document.getElementById('previewDownloadBtn');
    
    // Set file name
    fileNameEl.textContent = fileName;
    
    // Set download link
    downloadBtn.href = '../api/download-file.php?id=' + fileId;
    
    // Show modal
    modal.style.display = 'flex';
    
    // Show loading
    content.innerHTML = '<div class="spinner"><i class="fas fa-spinner fa-spin fa-3x"></i><p style="margin-top: var(--spacing-3);">Loading preview...</p></div>';
    
    // Build preview URL
    const previewUrl = '../api/download-file.php?id=' + fileId;
    
    // Load content based on type
    if (type === 'image') {
        content.innerHTML = '<img src="' + previewUrl + '" alt="' + fileName + '" style="max-width: 100%; max-height: 70vh; object-fit: contain;">';
    } else if (type === 'pdf') {
        content.innerHTML = '<iframe src="' + previewUrl + '" style="width: 100%; height: 70vh; border: none;"></iframe>';
    }
}

function closePreviewModal() {
    document.getElementById('previewModal').style.display = 'none';
    document.getElementById('previewContent').innerHTML = '';
}

// Approve Modal
function showApproveModal() {
    document.getElementById('approveModal').style.display = 'flex';
}

function closeApproveModal() {
    document.getElementById('approveModal').style.display = 'none';
}

// Reject Modal
function showRejectModal() {
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
// Receipt Upload Modal
function showReceiptUploadModal() {
    document.getElementById('receiptUploadModal').style.display = 'flex';
}

function closeReceiptUploadModal() {
    document.getElementById('receiptUploadModal').style.display = 'none';
}

// Form validation for receipt upload
document.getElementById('receiptUploadForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('receipt_file');
    if (!fileInput.files || fileInput.files.length === 0) {
        e.preventDefault();
        alert('Please select a receipt file to upload.');
        return false;
    }
    
    // Check file size (5MB max)
    const maxSize = 5 * 1024 * 1024; // 5MB in bytes
    if (fileInput.files[0].size > maxSize) {
        e.preventDefault();
        alert('File size must not exceed 5MB.');
        return false;
    }
});
// Cancel Modal
function confirmCancel() {
    document.getElementById('cancelModal').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}

// Close modals on outside click
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // ESC key closes all modals
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.style.display = 'none';
        });
    }
});

// Form validation for reject
document.getElementById('rejectForm').addEventListener('submit', function(e) {
    const reason = document.getElementById('reject_reason').value.trim();
    if (reason.length < 10) {
        e.preventDefault();
        alert('Please provide a detailed reason for rejection (at least 10 characters).');
        return false;
    }
});
</script>

<style>
/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-4);
}

.modal-dialog {
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-dialog.modal-lg {
    max-width: 900px;
}

/* Preview Modal Specific Styles */
#previewModal .card-body {
    background: #f8f9fa;
}

.spinner {
    text-align: center;
    color: var(--text-muted);
}

/* Detail Grid */
.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-4);
}

.detail-item label {
    display: block;
    font-size: var(--font-size-sm);
    color: var(--text-muted);
    margin-bottom: var(--spacing-1);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-item div {
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: var(--spacing-6);
}

.timeline::before {
    content: '';
    position: absolute;
    left: 16px;
    top: 10px;
    bottom: 10px;
    width: 2px;
    background: var(--border-color);
}

.timeline-item {
    position: relative;
    padding-bottom: var(--spacing-4);
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -37px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    z-index: 1;
    border: 3px solid var(--bg-primary);
}

.timeline-marker.success {
    background: var(--success);
}

.timeline-marker.danger {
    background: var(--danger);
}

.timeline-content {
    background: var(--bg-subtle);
    padding: var(--spacing-3);
    border-radius: var(--border-radius);
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-2);
}

.timeline-role {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
    margin-bottom: var(--spacing-2);
}

.timeline-comment {
    background: var(--bg-primary);
    padding: var(--spacing-2);
    border-radius: var(--border-radius);
    font-size: var(--font-size-sm);
    margin: var(--spacing-2) 0;
    border-left: 3px solid var(--primary);
}

.timeline-comment i {
    color: var(--primary);
    margin-right: var(--spacing-2);
}

.timeline-time {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
}

.timeline-time i {
    margin-right: var(--spacing-1);
}

/* File Upload Styles */
.uploaded-file {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-3);
    background: var(--bg-subtle);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing-2);
}

.file-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.file-icon {
    font-size: var(--font-size-2xl);
    color: var(--primary);
}

/* Responsive */
@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .modal-dialog,
    .modal-dialog.modal-lg {
        max-width: 100%;
    }
    
    #previewContent {
        height: 50vh !important;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
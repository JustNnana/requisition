<?php
/**
 * GateWey Requisition Management System
 * View Requisition Page
 * 
 * File: requisitions/view.php
 * Purpose: Display detailed view of a single requisition
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

// Check if user can edit
$canEdit = can_user_edit_requisition($reqData);
$canCancel = can_user_cancel_requisition($reqData);

// Page title
$pageTitle = 'Requisition ' . $reqData['requisition_number'];
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
        
        <!-- Documents -->
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
                                    </div>
                                    <div style="font-size: var(--font-size-sm); color: var(--text-muted);">
                                        <?php echo format_file_size($doc['file_size']); ?> • 
                                        Uploaded by <?php echo htmlspecialchars($doc['uploaded_by_name']); ?> • 
                                        <?php echo format_date($doc['uploaded_at'], 'M d, Y'); ?>
                                    </div>
                                </div>
                            </div>
                            <div>
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

<!-- Cancel Confirmation Modal (Hidden by default) -->
<div id="cancelModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 500px; margin: var(--spacing-4);">
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

<script>
function confirmCancel() {
    document.getElementById('cancelModal').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}
</script>

<style>
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
}

@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
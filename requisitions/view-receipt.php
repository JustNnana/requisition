<?php
/**
 * GateWey Requisition Management System
 * View Receipt Page
 * 
 * File: requisitions/view-receipt.php
 * Purpose: View and verify uploaded receipt (Finance and approvers)
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';

// Get requisition ID
$requisitionId = Sanitizer::int($_GET['id'] ?? 0);

if (!$requisitionId) {
    Session::setFlash('error', 'Invalid requisition ID.');
    header('Location: list.php');
    exit;
}

// Initialize classes
$requisition = new Requisition();
$payment = new Payment();
$fileUpload = new FileUpload();

// Get requisition details
$req = $requisition->getById($requisitionId);

if (!$req) {
    Session::setFlash('error', 'Requisition not found.');
    header('Location: list.php');
    exit;
}

// Check permission to view receipt
$canView = false;

// Owner can view
if ($req['user_id'] == Session::getUserId()) {
    $canView = true;
}

// Finance roles can view
if (is_finance_manager() || is_finance_member()) {
    $canView = true;
}

// Managing Director can view
if (is_managing_director()) {
    $canView = true;
}

// Line Manager can view department receipts
if (is_line_manager() && $req['department_id'] == Session::getUserDepartmentId()) {
    $canView = true;
}

if (!$canView) {
    Session::setFlash('error', 'You do not have permission to view this receipt.');
    header('Location: view.php?id=' . $requisitionId);
    exit;
}

// Get receipt document
$receipt = $payment->getReceipt($requisitionId);

if (!$receipt) {
    Session::setFlash('error', 'No receipt has been uploaded for this requisition.');
    header('Location: view.php?id=' . $requisitionId);
    exit;
}

// Get invoice document
$invoice = $payment->getInvoice($requisitionId);

// Get requisition items
$items = $requisition->getItems($requisitionId);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'View Receipt - ' . $req['requisition_number'];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">View Receipt</h1>
            <p class="content-subtitle">Receipt for Requisition <?php echo htmlspecialchars($req['requisition_number']); ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="view.php?id=<?php echo $requisitionId; ?>" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Back to Requisition
            </a>
            <?php if (is_finance_manager() || is_finance_member()): ?>
                <a href="../finance/pending-receipts.php" class="btn btn-ghost">
                    <i class="fas fa-receipt"></i> All Receipts
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

<!-- Status Alert -->
<div class="alert alert-success">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h6 class="alert-heading mb-2">
                <i class="fas fa-check-circle"></i> Receipt Uploaded Successfully
            </h6>
            <p class="mb-0">
                This requisition is complete. Receipt was uploaded on 
                <strong><?php echo format_datetime($req['receipt_uploaded_at']); ?></strong>
            </p>
        </div>
        <?php echo get_status_badge($req['status']); ?>
    </div>
</div>

<div class="row">
    <!-- Left Column - Requisition Summary -->
    <div class="col-md-5">
        <!-- Requisition Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-file-invoice"></i> Requisition Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="info-group">
                    <label>Requisition Number:</label>
                    <p class="text-primary"><strong><?php echo htmlspecialchars($req['requisition_number']); ?></strong></p>
                </div>
                
                <div class="info-group">
                    <label>Requester:</label>
                    <p>
                        <strong><?php echo htmlspecialchars($req['requester_first_name'] . ' ' . $req['requester_last_name']); ?></strong><br>
                        <span class="text-muted"><?php echo htmlspecialchars($req['requester_email']); ?></span>
                    </p>
                </div>
                
                <?php if ($req['department_name']): ?>
                    <div class="info-group">
                        <label>Department:</label>
                        <p>
                            <span class="badge badge-outline-secondary"><?php echo htmlspecialchars($req['department_code']); ?></span>
                            <?php echo htmlspecialchars($req['department_name']); ?>
                        </p>
                    </div>
                <?php endif; ?>
                
                <div class="info-group">
                    <label>Purpose:</label>
                    <p><?php echo nl2br(htmlspecialchars($req['purpose'])); ?></p>
                </div>
                
                <div class="info-group">
                    <label>Total Amount:</label>
                    <p class="text-success" style="font-size: 1.3em; font-weight: bold;">
                        <?php echo format_currency($req['total_amount']); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Items Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-list"></i> Items
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['item_description']); ?></td>
                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="text-right"><?php echo format_currency($item['subtotal']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Payment Details Card -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-money-bill-wave"></i> Payment Details
                </h5>
            </div>
            <div class="card-body">
                <div class="info-group">
                    <label>Payment Date:</label>
                    <p><?php echo format_datetime($req['paid_at']); ?></p>
                </div>
                
                <div class="info-group">
                    <label>Payment Method:</label>
                    <p>
                        <span class="badge badge-outline-info">
                            <?php echo htmlspecialchars($req['payment_method'] ?? 'N/A'); ?>
                        </span>
                    </p>
                </div>
                
                <?php if ($req['payment_reference']): ?>
                    <div class="info-group">
                        <label>Payment Reference:</label>
                        <p><?php echo htmlspecialchars($req['payment_reference']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($req['payment_notes']): ?>
                    <div class="info-group">
                        <label>Payment Notes:</label>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($req['payment_notes'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Invoice Link -->
                <?php if ($invoice): ?>
                    <div class="info-group">
                        <label>Invoice/Proof of Payment:</label>
                        <a href="../api/download-file.php?id=<?php echo $invoice['id']; ?>" 
                           class="btn btn-sm btn-outline-primary" 
                           target="_blank">
                            <i class="fas fa-file-invoice"></i> View Invoice
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Right Column - Receipt Details -->
    <div class="col-md-7">
        <!-- Receipt Card -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-receipt"></i> Receipt Document
                </h5>
            </div>
            <div class="card-body">
                <!-- Receipt Info -->
                <div class="receipt-info-box">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="receipt-icon">
                                <i class="<?php echo FileUpload::getFileIcon($receipt['file_name']); ?> fa-3x"></i>
                            </div>
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($receipt['file_name']); ?></h6>
                                <p class="text-muted mb-0">
                                    <small>
                                        <i class="fas fa-hdd"></i> <?php echo format_file_size($receipt['file_size']); ?>
                                        &nbsp;|&nbsp;
                                        <i class="fas fa-clock"></i> Uploaded <?php echo get_relative_time($receipt['uploaded_at']); ?>
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Receipt Action Buttons -->
                    <div class="d-flex gap-2">
                        <a href="../api/download-file.php?id=<?php echo $receipt['id']; ?>" 
                           class="btn btn-primary flex-1" 
                           target="_blank">
                            <i class="fas fa-eye"></i> View Receipt
                        </a>
                        <a href="../api/download-file.php?id=<?php echo $receipt['id']; ?>&download=1" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
                
                <!-- Receipt Notes -->
                <?php if ($req['receipt_notes']): ?>
                    <div class="alert alert-info mt-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-comment-alt"></i> Receipt Notes
                        </h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($req['receipt_notes'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Receipt Metadata -->
                <div class="card bg-light mt-4">
                    <div class="card-body">
                        <h6 class="card-title">Receipt Metadata</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-group-sm">
                                    <label>Uploaded By:</label>
                                    <p><?php echo htmlspecialchars($req['requester_first_name'] . ' ' . $req['requester_last_name']); ?></p>
                                </div>
                                <div class="info-group-sm">
                                    <label>Upload Date:</label>
                                    <p><?php echo format_datetime($receipt['uploaded_at']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group-sm">
                                    <label>File Type:</label>
                                    <p><?php echo strtoupper(pathinfo($receipt['file_name'], PATHINFO_EXTENSION)); ?></p>
                                </div>
                                <div class="info-group-sm">
                                    <label>File Size:</label>
                                    <p><?php echo format_file_size($receipt['file_size']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Receipt Verification Checklist (Finance View) -->
        <?php if (is_finance_manager() || is_finance_member()): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-clipboard-check"></i> Receipt Verification Checklist
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Use this checklist to verify the receipt quality and authenticity:</p>
                    
                    <div class="checklist">
                        <div class="checklist-item">
                            <input type="checkbox" id="check1">
                            <label for="check1">Receipt is clear and legible</label>
                        </div>
                        <div class="checklist-item">
                            <input type="checkbox" id="check2">
                            <label for="check2">Shows date of purchase/delivery</label>
                        </div>
                        <div class="checklist-item">
                            <input type="checkbox" id="check3">
                            <label for="check3">Shows vendor/supplier name</label>
                        </div>
                        <div class="checklist-item">
                            <input type="checkbox" id="check4">
                            <label for="check4">Shows items purchased/received</label>
                        </div>
                        <div class="checklist-item">
                            <input type="checkbox" id="check5">
                            <label for="check5">Shows total amount paid</label>
                        </div>
                        <div class="checklist-item">
                            <input type="checkbox" id="check6">
                            <label for="check6">Amount matches requisition total</label>
                        </div>
                        <div class="checklist-item">
                            <input type="checkbox" id="check7">
                            <label for="check7">Receipt appears authentic and unaltered</label>
                        </div>
                        <div class="checklist-item">
                            <input type="checkbox" id="check8">
                            <label for="check8">Receipt matches invoice details</label>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-3">
                        <small>
                            <i class="fas fa-info-circle"></i> 
                            If you notice any issues with the receipt, contact the requester for clarification or a replacement receipt.
                        </small>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.info-group {
    margin-bottom: var(--spacing-4);
}

.info-group label {
    font-weight: var(--font-weight-semibold);
    color: var(--text-secondary);
    font-size: var(--font-size-sm);
    text-transform: uppercase;
    margin-bottom: var(--spacing-2);
    display: block;
}

.info-group p {
    margin-bottom: 0;
    color: var(--text-primary);
}

.info-group-sm {
    margin-bottom: var(--spacing-3);
}

.info-group-sm label {
    font-weight: var(--font-weight-semibold);
    color: var(--text-secondary);
    font-size: var(--font-size-xs);
    text-transform: uppercase;
    margin-bottom: var(--spacing-1);
    display: block;
}

.info-group-sm p {
    margin-bottom: 0;
    color: var(--text-primary);
    font-size: var(--font-size-sm);
}

.receipt-info-box {
    padding: var(--spacing-4);
    background: var(--bg-subtle);
    border-radius: var(--border-radius);
    border: 2px solid var(--border-color);
}

.receipt-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: var(--border-radius);
    border: 1px solid var(--border-color);
}

.checklist {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
}

.checklist-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding: var(--spacing-3);
    background: var(--bg-subtle);
    border-radius: var(--border-radius);
    border: 1px solid var(--border-color);
    transition: all 0.2s ease;
}

.checklist-item:hover {
    background: var(--bg-hover);
    border-color: var(--primary);
}

.checklist-item input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.checklist-item label {
    margin: 0;
    cursor: pointer;
    user-select: none;
    flex: 1;
}

.checklist-item input[type="checkbox"]:checked + label {
    text-decoration: line-through;
    color: var(--text-muted);
}

.table-total {
    border-top: 2px solid var(--border-color);
    background: var(--bg-subtle);
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
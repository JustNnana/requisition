<?php
/**
 * GateWey Requisition Management System
 * Upload Receipt Page
 * 
 * File: requisitions/upload-receipt.php
 * Purpose: Upload receipt after payment has been processed (Requester)
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

// Get requisition details
$req = $requisition->getById($requisitionId);

if (!$req) {
    Session::setFlash('error', 'Requisition not found.');
    header('Location: list.php');
    exit;
}

// Verify ownership
if ($req['user_id'] != Session::getUserId()) {
    Session::setFlash('error', 'You can only upload receipts for your own requisitions.');
    header('Location: list.php');
    exit;
}

// Verify status is paid
if ($req['status'] !== STATUS_PAID) {
    Session::setFlash('error', 'Receipt can only be uploaded after payment has been processed.');
    header('Location: view.php?id=' . $requisitionId);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        Session::setFlash('error', 'Invalid security token. Please try again.');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    
    // Get receipt notes
    $receiptNotes = Sanitizer::string($_POST['receipt_notes'] ?? '');
    
    // Get receipt file
    $receiptFile = $_FILES['receipt'] ?? null;
    
    // Upload receipt
    $result = $payment->uploadReceipt($requisitionId, $receiptFile, $receiptNotes);
    
    if ($result['success']) {
        Session::setFlash('success', $result['message']);
        Session::setFlash('info', 'All approvers have been notified of receipt submission.');
        header('Location: view.php?id=' . $requisitionId);
        exit;
    } else {
        $errorMessage = $result['message'];
    }
}

// Get requisition items
$items = $requisition->getItems($requisitionId);

// Get invoice
$invoice = $payment->getInvoice($requisitionId);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = $errorMessage ?? Session::getFlash('error');

// Page title
$pageTitle = 'Upload Receipt - ' . $req['requisition_number'];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Upload Receipt</h1>
            <p class="content-subtitle">Submit receipt after receiving goods/services</p>
        </div>
        <div class="d-flex gap-2">
            <a href="view.php?id=<?php echo $requisitionId; ?>" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Back to Requisition
            </a>
        </div>
    </div>
</div>

<!-- Error Messages -->
<?php if ($errorMessage): ?>
    <div class="alert alert-error alert-dismissible">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($errorMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Left Column - Requisition Details -->
    <div class="col-md-6">
        <!-- Requisition Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-file-invoice"></i> Requisition Details
                </h5>
            </div>
            <div class="card-body">
                <div class="info-group">
                    <label>Requisition Number:</label>
                    <p class="text-primary"><strong><?php echo htmlspecialchars($req['requisition_number']); ?></strong></p>
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
                    <label>Status:</label>
                    <p><?php echo get_status_badge($req['status']); ?></p>
                </div>
                
                <div class="info-group">
                    <label>Paid Date:</label>
                    <p>
                        <?php echo format_datetime($req['paid_at']); ?><br>
                        <span class="text-muted"><?php echo get_relative_time($req['paid_at']); ?></span>
                    </p>
                </div>
                
                <?php if ($req['payment_method']): ?>
                    <div class="info-group">
                        <label>Payment Method:</label>
                        <p>
                            <span class="badge badge-outline-info"><?php echo htmlspecialchars($req['payment_method']); ?></span>
                            <?php if ($req['payment_reference']): ?>
                                <br><span class="text-muted small">Ref: <?php echo htmlspecialchars($req['payment_reference']); ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Items Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-list"></i> Requisition Items
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['item_description']); ?></td>
                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="text-right"><?php echo format_currency($item['unit_price']); ?></td>
                                    <td class="text-right"><strong><?php echo format_currency($item['subtotal']); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-total">
                                <th colspan="3" class="text-right">Total Amount:</th>
                                <th class="text-right">
                                    <span class="text-success" style="font-size: 1.2em;">
                                        <?php echo format_currency($req['total_amount']); ?>
                                    </span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Invoice Card (if available) -->
        <?php if ($invoice): ?>
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice"></i> Payment Invoice
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <i class="<?php echo FileUpload::getFileIcon($invoice['file_name']); ?> fa-2x"></i>
                            <span class="ml-2"><?php echo htmlspecialchars($invoice['file_name']); ?></span>
                            <br>
                            <small class="text-muted"><?php echo format_file_size($invoice['file_size']); ?></small>
                        </div>
                        <a href="../api/download-file.php?id=<?php echo $invoice['id']; ?>" 
                           class="btn btn-primary" 
                           target="_blank">
                            <i class="fas fa-download"></i> View Invoice
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Right Column - Receipt Upload Form -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-receipt"></i> Upload Receipt
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-4">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle"></i> Important
                    </h6>
                    <p class="mb-0">
                        Please upload a clear and legible receipt showing proof that goods/services 
                        were received. This is required to complete the requisition process.
                    </p>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                    
                    <!-- Receipt Upload -->
                    <div class="form-group">
                        <label for="receipt" class="required">Receipt File</label>
                        <input type="file" 
                               name="receipt" 
                               id="receipt" 
                               class="form-control" 
                               accept=".pdf,.jpg,.jpeg,.png"
                               required>
                        <small class="form-text text-muted">
                            Upload receipt (PDF, JPG, PNG - Max 10MB)
                        </small>
                    </div>
                    
                    <!-- Receipt Notes -->
                    <div class="form-group">
                        <label for="receipt_notes">Notes (Optional)</label>
                        <textarea name="receipt_notes" 
                                  id="receipt_notes" 
                                  class="form-control" 
                                  rows="4"
                                  placeholder="Add any notes about the receipt or goods/services received..."></textarea>
                        <small class="form-text text-muted">
                            Example: Goods received in good condition, Delivery date: DD/MM/YYYY
                        </small>
                    </div>
                    
                    <!-- Receipt Checklist -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Receipt Requirements Checklist:</h6>
                            <ul class="mb-0">
                                <li>Receipt is clear and legible</li>
                                <li>Shows date of purchase/delivery</li>
                                <li>Shows vendor/supplier name</li>
                                <li>Shows items purchased/received</li>
                                <li>Shows total amount paid</li>
                                <li>Receipt is authentic and unaltered</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-upload"></i> Upload Receipt
                        </button>
                        <a href="view.php?id=<?php echo $requisitionId; ?>" class="btn btn-ghost btn-lg btn-block">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Help Card -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-question-circle"></i> Need Help?
                </h6>
                <p>After uploading your receipt:</p>
                <ul class="mb-0">
                    <li>The requisition will be marked as "Completed"</li>
                    <li>All approvers will be notified</li>
                    <li>The receipt will be stored in the system</li>
                    <li>Finance can verify the receipt</li>
                </ul>
            </div>
        </div>
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

.table-total {
    border-top: 2px solid var(--border-color);
    background: var(--bg-subtle);
}

.form-actions {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
    margin-top: var(--spacing-6);
}

.required::after {
    content: ' *';
    color: var(--danger);
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
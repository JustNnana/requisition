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

// Load helpers - MUST include permissions.php before using role functions
require_once __DIR__ . '/../helpers/permissions.php';
require_once __DIR__ . '/../helpers/status-indicator.php';

// Get requisition ID
$requisitionId = get_encrypted_id();

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
    header('Location: ' . build_encrypted_url('view.php', $requisitionId));
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
        header('Location: ' . build_encrypted_url('view.php', $requisitionId));
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

<style>
    /* Content Header */
    .content-header {
        margin-bottom: var(--spacing-6);
    }

    .content-title {
        font-size: var(--font-size-2xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-1) 0;
    }

    .content-subtitle {
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
        margin: 0;
    }

    /* Alert Messages */
    .alert {
        display: flex;
        align-items: flex-start;
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-4);
        gap: var(--spacing-3);
        position: relative;
    }

    .alert-error {
        background: rgba(var(--danger-rgb), 0.1);
        border-color: rgba(var(--danger-rgb), 0.2);
        color: var(--danger);
    }

    .alert-warning {
        background: rgba(var(--warning-rgb), 0.1);
        border-color: rgba(var(--warning-rgb), 0.2);
        color: var(--warning);
    }

    .alert i {
        font-size: var(--font-size-lg);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .alert-dismissible {
        padding-right: var(--spacing-8);
    }

    .btn-close {
        position: absolute;
        right: var(--spacing-3);
        top: var(--spacing-3);
        background: transparent;
        border: none;
        font-size: var(--font-size-lg);
        color: currentColor;
        opacity: 0.5;
        cursor: pointer;
        padding: var(--spacing-1);
        line-height: 1;
        transition: var(--theme-transition);
    }

    .btn-close:hover {
        opacity: 1;
    }

    .btn-close::before {
        content: "×";
        font-size: 24px;
    }

    .alert-heading {
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        margin-bottom: var(--spacing-2);
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    /* Row and Column Layout */
    .row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-5);
        margin-bottom: var(--spacing-6);
    }

    .col-md-6 {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-4);
    }

    /* Card Styles */
    .card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
    }

    .card-header {
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
    }

    .card-header.bg-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border-bottom-color: transparent;
    }

    .card-header.bg-info {
        background: linear-gradient(135deg, var(--info), #0284c7);
        border-bottom-color: transparent;
    }

    .card-header.bg-primary .card-title,
    .card-header.bg-info .card-title {
        color: white;
    }

    .card-title {
        margin: 0;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .card-title i {
        font-size: var(--font-size-base);
    }

    .card-body {
        padding: var(--spacing-5);
    }

    .bg-light {
        background: var(--bg-subtle) !important;
    }

    /* Info Groups */
    .info-group {
        margin-bottom: var(--spacing-4);
        padding-bottom: var(--spacing-4);
        border-bottom: 1px solid var(--border-color);
    }

    .info-group:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .info-group label {
        font-weight: var(--font-weight-semibold);
        color: var(--text-secondary);
        font-size: var(--font-size-xs);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: var(--spacing-2);
        display: block;
    }

    .info-group p {
        margin: 0;
        color: var(--text-primary);
        font-size: var(--font-size-sm);
    }

    /* Table Styles */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead {
        background: var(--bg-subtle);
    }

    .table th {
        padding: var(--spacing-3);
        text-align: left;
        font-weight: var(--font-weight-semibold);
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        padding: var(--spacing-3);
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
    }

    .table tbody tr {
        transition: var(--theme-transition);
    }

    .table tbody tr:hover {
        background: var(--bg-subtle);
    }

    .table-sm th,
    .table-sm td {
        padding: var(--spacing-2) var(--spacing-3);
    }

    .table-total {
        border-top: 2px solid var(--border-color);
        background: var(--bg-subtle);
        font-weight: var(--font-weight-bold);
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: var(--spacing-4);
    }

    .form-group label {
        display: block;
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
        margin-bottom: var(--spacing-2);
    }

    .form-group label.required::after {
        content: ' *';
        color: var(--danger);
    }

    .form-control {
        width: 100%;
        padding: var(--spacing-3);
        font-size: var(--font-size-sm);
        background: var(--bg-input);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        color: var(--text-primary);
        transition: var(--theme-transition);
    }

    .form-control:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .form-text {
        display: block;
        margin-top: var(--spacing-2);
        font-size: var(--font-size-xs);
        color: var(--text-muted);
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-3);
        margin-top: var(--spacing-6);
        padding-top: var(--spacing-6);
        border-top: 1px solid var(--border-color);
    }

    /* Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-2);
        padding: var(--spacing-3) var(--spacing-4);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
        border-radius: var(--border-radius);
        border: 1px solid transparent;
        cursor: pointer;
        transition: var(--theme-transition);
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    .btn-ghost {
        background: transparent;
        color: var(--text-secondary);
        border-color: var(--border-color);
    }

    .btn-ghost:hover {
        background: var(--bg-subtle);
        color: var(--text-primary);
        border-color: var(--border-color);
    }

    .btn-lg {
        padding: var(--spacing-4) var(--spacing-5);
        font-size: var(--font-size-base);
    }

    .btn-block {
        width: 100%;
    }

    /* Badge Styles */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-1);
        padding: var(--spacing-1) var(--spacing-3);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        white-space: nowrap;
    }

    .badge-outline-secondary {
        background: transparent;
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
    }

    .badge-outline-info {
        background: transparent;
        border: 1px solid var(--info);
        color: var(--info);
    }

    /* File Display */
    .d-flex {
        display: flex;
    }

    .align-items-center {
        align-items: center;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    .gap-2 {
        gap: var(--spacing-2);
    }

    .ml-2 {
        margin-left: var(--spacing-2);
    }

    .fa-2x {
        font-size: 2em;
    }

    /* Checklist */
    .card-body ul {
        margin: 0;
        padding-left: var(--spacing-5);
    }

    .card-body ul li {
        margin-bottom: var(--spacing-2);
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
    }

    .card-body ul li:last-child {
        margin-bottom: 0;
    }

    /* Utility Classes */
    .mb-0 {
        margin-bottom: 0;
    }

    .mb-4 {
        margin-bottom: var(--spacing-4);
    }

    .mt-4 {
        margin-top: var(--spacing-4);
    }

    .text-muted {
        color: var(--text-muted) !important;
    }

    .text-primary {
        color: var(--primary) !important;
    }

    .text-success {
        color: var(--success) !important;
    }

    .text-white {
        color: white !important;
    }

    .small {
        font-size: var(--font-size-xs);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .content-header .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: var(--spacing-3);
        }

        .content-header .btn {
            width: 100%;
        }

        .table {
            font-size: var(--font-size-xs);
        }

        .table th,
        .table td {
            padding: var(--spacing-2);
        }

        .form-actions {
            flex-direction: column;
        }
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Upload Receipt</h1>
            <p class="content-subtitle">Submit receipt after receiving goods/services</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo build_encrypted_url('view.php', $requisitionId); ?>" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Requisition</span>
            </a>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if ($errorMessage): ?>
    <div class="alert alert-error alert-dismissible">
        <i class="fas fa-exclamation-circle"></i>
        <div><?php echo htmlspecialchars($errorMessage); ?></div>
        <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Left Column - Requisition Details -->
    <div class="col-md-6">
        <!-- Requisition Info Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-file-invoice"></i>
                    <span>Requisition Details</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="info-group">
                    <label>Requisition Number</label>
                    <p class="text-primary"><strong><?php echo htmlspecialchars($req['requisition_number']); ?></strong></p>
                </div>
                
                <?php if ($req['department_name']): ?>
                    <div class="info-group">
                        <label>Department</label>
                        <p>
                            <span class="badge badge-outline-secondary"><?php echo htmlspecialchars($req['department_code']); ?></span>
                            <?php echo htmlspecialchars($req['department_name']); ?>
                        </p>
                    </div>
                <?php endif; ?>
                
                <div class="info-group">
                    <label>Purpose</label>
                    <p><?php echo nl2br(htmlspecialchars($req['purpose'])); ?></p>
                </div>
                
                <div class="info-group">
                    <label>Status</label>
                    <p><?php echo get_status_badge($req['status']); ?></p>
                </div>
                
                <div class="info-group">
                    <label>Paid Date</label>
                    <p>
                        <?php echo format_datetime($req['payment_date']); ?><br>
                        <span class="text-muted"><?php echo get_relative_time($req['payment_date']); ?></span>
                    </p>
                </div>
                
                <?php if ($req['payment_method']): ?>
                    <div class="info-group">
                        <label>Payment Method</label>
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

        <!-- Account Details Card -->
        <?php if ($req['account_name'] || $req['account_number'] || $req['bank_name']): ?>
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, var(--warning), #f59e0b); border-bottom-color: transparent;">
                <h5 class="card-title" style="color: white;">
                    <i class="fas fa-university"></i>
                    <span>Payment Received To</span>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($req['account_name']): ?>
                    <div class="info-group">
                        <label>Account Name</label>
                        <p><strong><?php echo htmlspecialchars($req['account_name']); ?></strong></p>
                    </div>
                <?php endif; ?>

                <?php if ($req['account_number']): ?>
                    <div class="info-group">
                        <label>Account Number</label>
                        <p><strong><?php echo htmlspecialchars($req['account_number']); ?></strong></p>
                    </div>
                <?php endif; ?>

                <?php if ($req['bank_name']): ?>
                    <div class="info-group">
                        <label>Bank Name</label>
                        <p><?php echo htmlspecialchars($req['bank_name']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Items Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-list"></i>
                    <span>Requisition Items</span>
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
                                    <td class="text-right">₦<?php echo number_format((float)$item['unit_price'], 2); ?></td>
                                    <td class="text-right"><strong>₦<?php echo number_format((float)$item['subtotal'], 2); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-total">
                                <th colspan="3" class="text-right">Total Amount:</th>
                                <th class="text-right">
                                    <span class="text-success" style="font-size: 1.2em;">
                                        ₦<?php echo number_format((float)$req['total_amount'], 2); ?>
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
                    <h5 class="card-title">
                        <i class="fas fa-file-invoice"></i>
                        <span>Payment Invoice</span>
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
                        <a href="<?php echo build_encrypted_url('../api/download-file.php', $invoice['id']); ?>"
                           class="btn btn-primary"
                           target="_blank">
                            <i class="fas fa-download"></i>
                            <span>View Invoice</span>
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
                <h5 class="card-title">
                    <i class="fas fa-receipt"></i>
                    <span>Upload Receipt</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-4">
                    <div>
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Important</span>
                        </h6>
                        <p class="mb-0">
                            Please upload a clear and legible receipt showing proof that goods/services 
                            were received. This is required to complete the requisition process.
                        </p>
                    </div>
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
                        <small class="form-text">
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
                        <small class="form-text">
                            Example: Goods received in good condition, Delivery date: DD/MM/YYYY
                        </small>
                    </div>
                    
                    <!-- Receipt Checklist -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Receipt Requirements Checklist:</h6>
                            <ul>
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
                            <i class="fas fa-upload"></i>
                            <span>Upload Receipt</span>
                        </button>
                        <a href="<?php echo build_encrypted_url('view.php', $requisitionId); ?>" class="btn btn-ghost btn-lg btn-block">
                            <i class="fas fa-times"></i>
                            <span>Cancel</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Help Card -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">
                    <i class="fas fa-question-circle"></i>
                    <span>Need Help?</span>
                </h6>
            </div>
            <div class="card-body">
                <p>After uploading your receipt:</p>
                <ul>
                    <li>The requisition will be marked as "Completed"</li>
                    <li>All approvers will be notified</li>
                    <li>The receipt will be stored in the system</li>
                    <li>Finance can verify the receipt</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
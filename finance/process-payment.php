<?php
/**
 * GateWey Requisition Management System
 * Process Payment Page
 * 
 * File: finance/process-payment.php
 * Purpose: Upload invoice and mark requisition as paid (Finance Member)
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

// Check if user is Finance Member
if (!is_finance_member()) {
    Session::setFlash('error', 'Only Finance Members can process payments.');
    header('Location: ' . get_user_dashboard_url());
    exit;
}

// Get requisition ID (encrypted)
$requisitionId = get_encrypted_id();

if (!$requisitionId) {
    Session::setFlash('error', 'Invalid requisition ID.');
    header('Location: pending-payment.php');
    exit;
}

// Initialize classes
$requisition = new Requisition();
$payment = new Payment();

// Get requisition details
$req = $requisition->getById($requisitionId);

if (!$req) {
    Session::setFlash('error', 'Requisition not found.');
    header('Location: pending-payment.php');
    exit;
}

// Verify status
if ($req['status'] !== STATUS_APPROVED_FOR_PAYMENT) {
    Session::setFlash('error', 'This requisition is not approved for payment.');
    header('Location: pending-payment.php');
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
    
    // Get payment data
    $paymentData = [
        'payment_method' => Sanitizer::string($_POST['payment_method'] ?? ''),
        'payment_reference' => Sanitizer::string($_POST['payment_reference'] ?? ''),
        'payment_notes' => Sanitizer::string($_POST['payment_notes'] ?? '')
    ];
    
    // Get invoice file
    $invoiceFile = $_FILES['invoice'] ?? null;
    
    // Process payment
    $result = $payment->processPayment($requisitionId, $invoiceFile, $paymentData);
    
    if ($result['success']) {
        Session::setFlash('success', $result['message']);
        Session::setFlash('info', 'Requester will be notified to upload receipt.');
        header('Location: pending-payment.php');
        exit;
    } else {
        $errorMessage = $result['message'];
    }
}

// Get requisition items
$items = $requisition->getItems($requisitionId);
$reqData = $requisition->getById($requisitionId);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = $errorMessage ?? Session::getFlash('error');

// Page title
$pageTitle = 'Process Payment - ' . $req['requisition_number'];
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

    .alert-info {
        background: rgba(var(--info-rgb), 0.1);
        border-color: rgba(var(--info-rgb), 0.2);
        color: var(--info);
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

    .card-header.bg-success {
        background: linear-gradient(135deg, var(--success), #059669);
        border-bottom-color: transparent;
    }

    .card-header.bg-success .card-title {
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

    select.form-control {
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23666' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right var(--spacing-3) center;
        background-size: 16px 12px;
        padding-right: var(--spacing-8);
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

    .btn-success {
        background: var(--success);
        color: white;
        border-color: var(--success);
    }

    .btn-success:hover {
        background: #059669;
        border-color: #059669;
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

    /* Utility Classes */
    .d-flex {
        display: flex;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    .align-items-center {
        align-items: center;
    }

    .gap-2 {
        gap: var(--spacing-2);
    }

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

    /* Help Card */
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
            <h1 class="content-title">Process Payment</h1>
            <p class="content-subtitle">Upload invoice and mark requisition as paid</p>
        </div>
        <div class="d-flex gap-2">
            <a href="pending-payment.php" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Pending</span>
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
                
                <div class="info-group">
                    <label>Requester</label>
                    <p>
                        <strong><?php echo htmlspecialchars($reqData['first_name'] . ' ' . $reqData['last_name']); ?></strong><br>
                        <span class="text-muted"><?php echo htmlspecialchars($req['requester_email']); ?></span>
                    </p>
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
                    <label>Submitted</label>
                    <p><?php echo format_datetime($req['submitted_at']); ?></p>
                </div>
            </div>
        </div>
        
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
    </div>
    
    <!-- Right Column - Payment Form -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="card-title">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Payment Processing</span>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                    
                    <!-- Payment Method -->
                    <div class="form-group">
                        <label for="payment_method" class="required">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <option value="">-- Select Payment Method --</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Check">Check</option>
                            <option value="Cash">Cash</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Mobile Payment">Mobile Payment</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <!-- Payment Reference -->
                    <div class="form-group">
                        <label for="payment_reference" class="required">Payment Reference/Transaction ID</label>
                        <input type="text" 
                               name="payment_reference" 
                               id="payment_reference" 
                               class="form-control" 
                               placeholder="e.g., TXN123456, Check #789, etc."
                               required>
                        <small class="form-text">
                            Enter transaction ID, check number, or reference number
                        </small>
                    </div>
                    
                    <!-- Invoice Upload -->
                    <div class="form-group">
                        <label for="invoice" class="required">Invoice/Proof of Payment</label>
                        <input type="file" 
                               name="invoice" 
                               id="invoice" 
                               class="form-control" 
                               accept=".pdf,.jpg,.jpeg,.png"
                               required>
                        <small class="form-text">
                            Upload invoice or payment proof (PDF, JPG, PNG - Max 10MB)
                        </small>
                    </div>
                    
                    <!-- Payment Notes -->
                    <div class="form-group">
                        <label for="payment_notes">Payment Notes (Optional)</label>
                        <textarea name="payment_notes" 
                                  id="payment_notes" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Add any additional notes about the payment..."></textarea>
                    </div>
                    
                    <!-- Payment Summary -->
                    <div class="alert alert-info">
                        <div>
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle"></i>
                                <span>Payment Summary</span>
                            </h6>
                            <p class="mb-0">
                                <strong>Amount to Pay:</strong> 
                                <span class="text-success" style="font-size: 1.3em; font-weight: bold;">
                                    ₦<?php echo number_format((float)$req['total_amount'], 2); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-check-circle"></i>
                            <span>Mark as Paid</span>
                        </button>
                        <a href="pending-payment.php" class="btn btn-ghost btn-lg btn-block">
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
                <ul>
                    <li>Verify all payment details are correct</li>
                    <li>Upload clear and legible invoice/proof</li>
                    <li>Double-check the payment amount</li>
                    <li>Requester will be notified to upload receipt after payment</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
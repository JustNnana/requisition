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

// Check if user is Finance Member
if (!is_finance_member()) {
    Session::setFlash('error', 'Only Finance Members can process payments.');
    header('Location: ' . get_user_dashboard_url());
    exit;
}

// Get requisition ID
$requisitionId = Sanitizer::int($_GET['id'] ?? 0);

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

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = $errorMessage ?? Session::getFlash('error');

// Page title
$pageTitle = 'Process Payment - ' . $req['requisition_number'];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Process Payment</h1>
            <p class="content-subtitle">Upload invoice and mark as paid</p>
        </div>
        <div class="d-flex gap-2">
            <a href="pending-payment.php" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Back to Pending
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
                    <label>Status:</label>
                    <p><?php echo get_status_badge($req['status']); ?></p>
                </div>
                
                <div class="info-group">
                    <label>Submitted:</label>
                    <p><?php echo format_datetime($req['submitted_at']); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Items Card -->
        <div class="card">
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
    </div>
    
    <!-- Right Column - Payment Form -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-money-bill-wave"></i> Payment Processing
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
                        <small class="form-text text-muted">
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
                        <small class="form-text text-muted">
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
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle"></i> Payment Summary
                        </h6>
                        <p class="mb-0">
                            <strong>Amount to Pay:</strong> 
                            <span class="text-success" style="font-size: 1.3em; font-weight: bold;">
                                <?php echo format_currency($req['total_amount']); ?>
                            </span>
                        </p>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-check-circle"></i> Mark as Paid
                        </button>
                        <a href="pending-payment.php" class="btn btn-ghost btn-lg btn-block">
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
                <ul class="mb-0">
                    <li>Verify all payment details are correct</li>
                    <li>Upload clear and legible invoice/proof</li>
                    <li>Double-check the payment amount</li>
                    <li>Requester will be notified to upload receipt after payment</li>
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
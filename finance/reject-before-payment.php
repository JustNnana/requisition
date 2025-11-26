<?php
/**
 * GateWey Requisition Management System
 * Reject Before Payment Page
 * 
 * File: finance/reject-before-payment.php
 * Purpose: Finance Manager rejects requisition with financial reasons before payment processing
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
// Check if user is Finance Manager
if (!is_finance_manager()) {
    Session::setFlash('error', 'Only Finance Managers can reject requisitions before payment.');
    header('Location: ' . get_user_dashboard_url());
    exit;
}

// Get requisition ID
$requisitionId = Sanitizer::int($_GET['id'] ?? 0);

if (!$requisitionId) {
    Session::setFlash('error', 'Invalid requisition ID.');
    header('Location: review-queue.php');
    exit;
}

// Initialize classes
$requisition = new Requisition();
$approval = new Approval();

// Get requisition details
$req = $requisition->getById($requisitionId);

if (!$req) {
    Session::setFlash('error', 'Requisition not found.');
    header('Location: review-queue.php');
    exit;
}

// Verify status
if ($req['status'] !== STATUS_PENDING_FINANCE_MANAGER) {
    Session::setFlash('error', 'This requisition is not awaiting Finance Manager review.');
    header('Location: review-queue.php');
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
    
    // Get rejection data
    $rejectionReason = Sanitizer::string($_POST['rejection_reason'] ?? '');
    $rejectionCategory = Sanitizer::string($_POST['rejection_category'] ?? '');
    $returnTo = Sanitizer::string($_POST['return_to'] ?? 'managing_director');
    $additionalNotes = Sanitizer::string($_POST['additional_notes'] ?? '');
    
    // Build rejection message
    $rejectionMessage = "Rejected by Finance Manager\n\n";
    $rejectionMessage .= "Category: " . ucfirst(str_replace('_', ' ', $rejectionCategory)) . "\n\n";
    $rejectionMessage .= "Reason:\n" . $rejectionReason;
    
    if ($additionalNotes) {
        $rejectionMessage .= "\n\nAdditional Notes:\n" . $additionalNotes;
    }
    
    // Validate
    if (strlen($rejectionReason) < 20) {
        $errorMessage = 'Rejection reason must be at least 20 characters long.';
    } else {
        // Reject the requisition
        // Note: reject() expects (requisitionId, reason, comments)
        $result = $approval->reject($requisitionId, $rejectionMessage, $additionalNotes);
        
        if ($result['success']) {
            Session::setFlash('success', 'Requisition rejected successfully.');
            Session::setFlash('info', 'The requisition has been returned to ' . ($returnTo == 'managing_director' ? 'Managing Director' : 'the previous approver') . '.');
            header('Location: review-queue.php');
            exit;
        } else {
            $errorMessage = $result['message'];
        }
    }
}

// Get requisition items
$items = $requisition->getItems($requisitionId);

// Get approval history
$approvalHistory = $approval->getApprovalHistory($requisitionId);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = $errorMessage ?? Session::getFlash('error');

// Page title
$pageTitle = 'Reject Before Payment - ' . $req['requisition_number'];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Reject Before Payment</h1>
            <p class="content-subtitle">Provide financial reasons for rejection</p>
        </div>
        <div class="d-flex gap-2">
            <a href="review-queue.php" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Back to Review Queue
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

<!-- Warning Alert -->
<div class="alert alert-warning">
    <h6 class="alert-heading">
        <i class="fas fa-exclamation-triangle"></i> Important
    </h6>
    <p class="mb-0">
        Rejecting this requisition will return it to the previous approver. 
        Please provide clear and specific financial reasons for the rejection to help resolve any issues.
    </p>
</div>

<div class="row">
    <!-- Left Column - Requisition Details -->
    <div class="col-md-5">
        <!-- Requisition Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-invoice"></i> Requisition Under Review
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
                    <p class="text-danger" style="font-size: 1.3em; font-weight: bold;">
                        <?php echo format_currency($req['total_amount']); ?>
                    </p>
                </div>
                
                <div class="info-group">
                    <label>Submitted:</label>
                    <p><?php echo format_datetime($req['submitted_at']); ?></p>
                </div>
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
                                    <span class="text-danger" style="font-size: 1.2em;">
                                        <?php echo format_currency($req['total_amount']); ?>
                                    </span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Approval History Card -->
        <?php if (!empty($approvalHistory)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-history"></i> Approval History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($approvalHistory as $history): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-<?php echo $history['action'] == 'approved' ? 'success' : 'danger'; ?>">
                                    <i class="fas <?php echo $history['action'] == 'approved' ? 'fa-check' : 'fa-times'; ?>"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <strong><?php echo htmlspecialchars($history['approver_name']); ?></strong>
                                        <span class="badge badge-<?php echo $history['action'] == 'approved' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($history['action']); ?>
                                        </span>
                                    </div>
                                    <div class="timeline-role">
                                        <?php echo htmlspecialchars($history['role_name']); ?>
                                    </div>
                                    <div class="timeline-time">
                                        <?php echo format_date($history['created_at'], 'M d, Y \a\t h:i A'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Right Column - Rejection Form -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-times-circle"></i> Financial Rejection Details
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                    
                    <!-- Rejection Category -->
                    <div class="form-group">
                        <label for="rejection_category" class="required">Rejection Category</label>
                        <select name="rejection_category" id="rejection_category" class="form-control" required>
                            <option value="">-- Select Category --</option>
                            <optgroup label="Budget & Financial">
                                <option value="budget_exceeded">Budget Exceeded</option>
                                <option value="insufficient_funds">Insufficient Funds</option>
                                <option value="budget_not_allocated">Budget Not Allocated</option>
                                <option value="unauthorized_expense">Unauthorized Expense Category</option>
                            </optgroup>
                            <optgroup label="Documentation">
                                <option value="missing_documentation">Missing Documentation</option>
                                <option value="incomplete_information">Incomplete Information</option>
                                <option value="unclear_justification">Unclear Justification</option>
                                <option value="supporting_docs_required">Supporting Documents Required</option>
                            </optgroup>
                            <optgroup label="Pricing & Quotations">
                                <option value="pricing_concerns">Pricing Concerns</option>
                                <option value="quotes_required">Multiple Quotes Required</option>
                                <option value="vendor_not_approved">Vendor Not Approved</option>
                                <option value="better_alternatives">Better Alternatives Available</option>
                            </optgroup>
                            <optgroup label="Policy & Compliance">
                                <option value="policy_violation">Policy Violation</option>
                                <option value="approval_hierarchy">Approval Hierarchy Issue</option>
                                <option value="duplicate_request">Duplicate Request</option>
                                <option value="timing_inappropriate">Inappropriate Timing</option>
                            </optgroup>
                            <optgroup label="Other">
                                <option value="other">Other Financial Reason</option>
                            </optgroup>
                        </select>
                    </div>
                    
                    <!-- Rejection Reason -->
                    <div class="form-group">
                        <label for="rejection_reason" class="required">Detailed Rejection Reason</label>
                        <textarea name="rejection_reason" 
                                  id="rejection_reason" 
                                  class="form-control" 
                                  rows="6"
                                  required
                                  placeholder="Please provide a clear and detailed explanation of why this requisition is being rejected from a financial perspective. Include specific issues that need to be addressed before re-approval."></textarea>
                        <small class="form-text text-muted">
                            Minimum 20 characters. Be specific to help resolve issues quickly.
                        </small>
                        <div class="character-count mt-2">
                            <span id="char-count">0</span> / 20 minimum characters
                        </div>
                    </div>
                    
                    <!-- Return To -->
                    <div class="form-group">
                        <label for="return_to" class="required">Return To</label>
                        <select name="return_to" id="return_to" class="form-control" required>
                            <option value="managing_director">Managing Director</option>
                            <option value="previous_approver">Previous Approver in Chain</option>
                        </select>
                        <small class="form-text text-muted">
                            Select who should receive the rejected requisition
                        </small>
                    </div>
                    
                    <!-- Additional Notes -->
                    <div class="form-group">
                        <label for="additional_notes">Additional Notes (Optional)</label>
                        <textarea name="additional_notes" 
                                  id="additional_notes" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Any additional comments or suggestions for improvement..."></textarea>
                    </div>
                    
                    <!-- Common Rejection Reasons -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Common Financial Rejection Reasons (Click to use):</h6>
                            <div class="rejection-templates">
                                <button type="button" class="btn btn-sm btn-outline-secondary template-btn" 
                                        data-template="This requisition exceeds the approved budget for [Department/Category]. Please revise the request to align with budgetary constraints or obtain additional budget approval.">
                                    Budget Exceeded
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary template-btn" 
                                        data-template="Additional supporting documentation is required, including: [List required documents]. Please provide these documents for financial review and resubmit.">
                                    Missing Documentation
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary template-btn" 
                                        data-template="The pricing appears high for the requested items/services. Please obtain at least [X] competitive quotes from approved vendors and resubmit with price comparison.">
                                    Pricing Concerns
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary template-btn" 
                                        data-template="This expenditure requires additional justification. Please provide detailed business case explaining the necessity, expected ROI, and alignment with organizational goals.">
                                    Unclear Justification
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary template-btn" 
                                        data-template="The selected vendor is not on our approved vendor list. Please select an approved vendor or submit a vendor approval request through the procurement department.">
                                    Vendor Not Approved
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger btn-lg btn-block">
                            <i class="fas fa-times-circle"></i> Reject Requisition
                        </button>
                        <a href="review-queue.php" class="btn btn-ghost btn-lg btn-block">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                    </div>
                </form>
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

.character-count {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

.character-count.valid {
    color: var(--success);
}

.rejection-templates {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-2);
}

.template-btn {
    transition: all 0.2s ease;
}

.template-btn:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

/* Timeline Styles */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border-color);
}

.timeline-item {
    position: relative;
    margin-bottom: var(--spacing-4);
}

.timeline-marker {
    position: absolute;
    left: -26px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 10px;
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
    color: var(--text-muted);
    font-size: var(--font-size-sm);
    margin-bottom: var(--spacing-1);
}

.timeline-time {
    color: var(--text-muted);
    font-size: var(--font-size-xs);
}
</style>

<script>
// Character counter
document.getElementById('rejection_reason').addEventListener('input', function() {
    const count = this.value.length;
    const counter = document.getElementById('char-count');
    const counterDiv = counter.parentElement;
    
    counter.textContent = count;
    
    if (count >= 20) {
        counterDiv.classList.add('valid');
    } else {
        counterDiv.classList.remove('valid');
    }
});

// Template buttons
document.querySelectorAll('.template-btn').forEach(button => {
    button.addEventListener('click', function() {
        const template = this.dataset.template;
        const textarea = document.getElementById('rejection_reason');
        textarea.value = template;
        textarea.dispatchEvent(new Event('input'));
        textarea.focus();
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
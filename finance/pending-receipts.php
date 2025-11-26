<?php
/**
 * GateWey Requisition Management System
 * Pending Receipts Page
 * 
 * File: finance/pending-receipts.php
 * Purpose: Track requisitions awaiting receipt upload from requesters (Finance view)
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
// Check if user can view (Finance Manager or Finance Member)
if (!is_finance_manager() && !is_finance_member()) {
    Session::setFlash('error', 'You do not have permission to view this page.');
    header('Location: ' . get_user_dashboard_url());
    exit;
}

// Initialize Payment class
$payment = new Payment();

// Get pending receipts (all requisitions)
$pendingReceipts = $payment->getPendingReceipts(true);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Pending Receipts';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Pending Receipts</h1>
            <p class="content-subtitle">Track requisitions awaiting receipt upload from requesters</p>
        </div>
        <div class="d-flex gap-2">
            <?php if (is_finance_member()): ?>
                <a href="pending-payment.php" class="btn btn-ghost">
                    <i class="fas fa-clock"></i> Pending Payments
                </a>
            <?php endif; ?>
            <a href="payment-history.php" class="btn btn-ghost">
                <i class="fas fa-history"></i> Payment History
            </a>
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

<!-- Statistics Card -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Awaiting Receipts</p>
            <p class="stat-value"><?php echo count($pendingReceipts); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Amount Paid</p>
            <p class="stat-value">
                <?php 
                $totalAmount = array_sum(array_column($pendingReceipts, 'total_amount'));
                echo format_currency($totalAmount);
                ?>
            </p>
        </div>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info mt-4">
    <h6 class="alert-heading">
        <i class="fas fa-info-circle"></i> About Receipt Submission
    </h6>
    <p class="mb-0">
        Requesters are required to upload receipts after payment has been processed. 
        Once a receipt is uploaded, the requisition status will change to "Completed".
    </p>
</div>

<!-- Pending Receipts Table -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-receipt"></i> Requisitions Awaiting Receipt Upload
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($pendingReceipts)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="empty-state-title">No Pending Receipts</h3>
                <p class="empty-state-text">All paid requisitions have receipts uploaded. Excellent!</p>
                <a href="payment-history.php" class="btn btn-primary mt-3">
                    <i class="fas fa-history"></i> View Payment History
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Requisition #</th>
                            <th>Requester</th>
                            <th>Department</th>
                            <th>Amount</th>
                            <th>Paid Date</th>
                            <th>Days Since Payment</th>
                            <th>Paid By</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingReceipts as $req): ?>
                            <?php
                            // Calculate days since payment
                            $paidDate = new DateTime($req['payment_date']);
                            $now = new DateTime();
                            $daysSince = $now->diff($paidDate)->days;
                            
                            // Determine urgency class
                            $urgencyClass = '';
                            if ($daysSince > 14) {
                                $urgencyClass = 'text-danger';
                            } elseif ($daysSince > 7) {
                                $urgencyClass = 'text-warning';
                            }
                            ?>
                            <tr>
                                <td>
                                    <strong class="text-primary">
                                        <?php echo htmlspecialchars($req['requisition_number']); ?>
                                    </strong>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar-sm">
                                            <?php 
                                            $initials = strtoupper(
                                                substr($req['requester_first_name'], 0, 1) . 
                                                substr($req['requester_last_name'], 0, 1)
                                            );
                                            echo $initials;
                                            ?>
                                        </div>
                                        <div>
                                            <div class="user-name">
                                                <?php echo htmlspecialchars($req['requester_first_name'] . ' ' . $req['requester_last_name']); ?>
                                            </div>
                                            <div class="user-email">
                                                <?php echo htmlspecialchars($req['requester_email']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($req['department_name']): ?>
                                        <span class="badge badge-outline-secondary">
                                            <?php echo htmlspecialchars($req['department_code']); ?>
                                        </span>
                                        <div class="text-muted small">
                                            <?php echo htmlspecialchars($req['department_name']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-success">
                                        <?php echo format_currency($req['total_amount']); ?>
                                    </strong>
                                </td>
                                <td>
                                    <div><?php echo format_date($req['payment_date']); ?></div>
                                    <div class="text-muted small">
                                        <?php echo get_relative_time($req['payment_date']); ?>
                                    </div>
                                </td>
                                <td>
                                    <strong class="<?php echo $urgencyClass; ?>">
                                        <?php echo $daysSince; ?> days
                                    </strong>
                                    <?php if ($daysSince > 14): ?>
                                        <div class="text-danger small">
                                            <i class="fas fa-exclamation-triangle"></i> Overdue
                                        </div>
                                    <?php elseif ($daysSince > 7): ?>
                                        <div class="text-warning small">
                                            <i class="fas fa-clock"></i> Reminder needed
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        <?php echo htmlspecialchars($req['paid_by_first_name'] . ' ' . $req['paid_by_last_name']); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo get_status_badge($req['status']); ?>
                                </td>
                                <td class="text-center">
                                    <div class="table-actions">
                                        <a href="../requisitions/view.php?id=<?php echo $req['id']; ?>" 
                                           class="btn btn-sm btn-ghost" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Empty State Styles */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state-icon {
    font-size: 80px;
    color: var(--success);
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state-title {
    font-size: 24px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 10px;
}

.empty-state-text {
    font-size: 16px;
    color: var(--text-secondary);
    max-width: 500px;
    margin: 0 auto;
}

/* User Info Styles */
.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar-sm {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--primary-light);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 12px;
    flex-shrink: 0;
}

.user-name {
    font-weight: 500;
    color: var(--text-primary);
    font-size: 14px;
}

.user-email {
    color: var(--text-muted);
    font-size: 12px;
}

/* Table Actions */
.table-actions {
    display: flex;
    gap: 8px;
    justify-content: center;
    align-items: center;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php
/**
 * GateWey Requisition Management System
 * Pending Payments Page
 * 
 * File: finance/pending-payment.php
 * Purpose: View requisitions approved for payment (Finance Member + Finance Manager read-only)
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

// Check if user is Finance Member OR Finance Manager
if (!is_finance_member() && !is_finance_manager()) {
    Session::setFlash('error', 'Only Finance team members can access this page.');
    header('Location: ' . get_user_dashboard_url());
    exit;
}

// Determine if current user can process payments (Finance Member only)
$canProcessPayments = is_finance_member();

// Initialize Payment class
$payment = new Payment();

// Get pending payments
$pendingPayments = $payment->getPendingPayments();

// Get ALL-TIME statistics (not just this month)
$allTimeStats = $payment->getPaymentStatistics([]);

// Get this month's statistics
$thisMonthStats = $payment->getPaymentStatistics([
    'date_from' => date('Y-m-01'),
    'date_to' => date('Y-m-t')
]);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');
$infoMessage = Session::getFlash('info');

// Page title
$pageTitle = 'Pending Payments';
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

    .alert-success {
        background: rgba(var(--success-rgb), 0.1);
        border-color: rgba(var(--success-rgb), 0.2);
        color: var(--success);
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

    /* Statistics Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-4);
        margin-bottom: var(--spacing-6);
    }

    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-5);
        display: flex;
        align-items: center;
        gap: var(--spacing-4);
        transition: var(--theme-transition);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--border-radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-xl);
        color: white;
        flex-shrink: 0;
    }

    .stat-icon.bg-warning {
        background: linear-gradient(135deg, var(--warning), #f59e0b);
    }

    .stat-icon.bg-success {
        background: linear-gradient(135deg, var(--success), #059669);
    }

    .stat-icon.bg-info {
        background: linear-gradient(135deg, var(--info), #0284c7);
    }

    .stat-icon.bg-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0 0 var(--spacing-1) 0;
        font-weight: var(--font-weight-medium);
    }

    .stat-value {
        font-size: var(--font-size-2xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-1) 0;
        line-height: 1.2;
    }

    .stat-hint {
        font-size: var(--font-size-xs);
        color: var(--text-muted);
        margin: 0;
        font-weight: var(--font-weight-normal);
        line-height: 1.4;
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
        color: var(--warning);
    }

    .card-body {
        padding: 0;
    }

    /* Enhanced Table */
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
        padding: var(--spacing-4);
        text-align: left;
        font-weight: var(--font-weight-semibold);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    .table td {
        padding: var(--spacing-4);
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

    .text-center {
        text-align: center;
    }

    /* Empty State */
    .empty-state {
        padding: var(--spacing-8) var(--spacing-4);
        text-align: center;
    }

    .empty-state-icon {
        font-size: 4rem;
        color: var(--success);
        opacity: 0.5;
        margin-bottom: var(--spacing-4);
    }

    .empty-state-title {
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-2);
    }

    .empty-state-text {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        max-width: 500px;
        margin: 0 auto var(--spacing-4) auto;
    }

    /* User Info Styles */
    .user-info {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .user-avatar-sm {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: var(--font-weight-semibold);
        font-size: var(--font-size-xs);
        flex-shrink: 0;
    }

    .user-name {
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
    }

    .user-email {
        color: var(--text-muted);
        font-size: var(--font-size-xs);
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

    /* Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
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

    .btn-sm {
        padding: var(--spacing-2) var(--spacing-3);
        font-size: var(--font-size-xs);
    }

    /* Table Actions */
    .table-actions {
        display: flex;
        gap: var(--spacing-2);
        justify-content: center;
        align-items: center;
    }

    /* Text Utilities */
    .text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
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

    .small {
        font-size: var(--font-size-xs);
    }

    /* Layout Utilities */
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

    .mb-4 {
        margin-bottom: var(--spacing-4);
    }

    .mt-3 {
        margin-top: var(--spacing-3);
    }

    .mt-4 {
        margin-top: var(--spacing-4);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .content-header .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: var(--spacing-3);
        }

        .content-header .btn {
            width: 100%;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .user-info {
            flex-direction: column;
            align-items: flex-start;
        }

        .table-actions {
            flex-direction: column;
        }

        .table {
            font-size: var(--font-size-xs);
        }

        .table th,
        .table td {
            padding: var(--spacing-2);
        }
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Pending Payments</h1>
            <p class="content-subtitle">
                <?php if ($canProcessPayments): ?>
                    Process requisitions approved for payment
                <?php else: ?>
                    View requisitions approved for payment (Read-Only)
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="payment-history.php" class="btn btn-ghost">
                <i class="fas fa-history"></i>
                <span>Payment History</span>
            </a>
            <a href="pending-receipts.php" class="btn btn-ghost">
                <i class="fas fa-receipt"></i>
                <span>Pending Receipts</span>
            </a>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible">
        <i class="fas fa-check-circle"></i>
        <div><?php echo htmlspecialchars($successMessage); ?></div>
        <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'"></button>
    </div>
<?php endif; ?>

<?php if ($infoMessage): ?>
    <div class="alert alert-info alert-dismissible">
        <i class="fas fa-info-circle"></i>
        <div><?php echo htmlspecialchars($infoMessage); ?></div>
        <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'"></button>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-error alert-dismissible">
        <i class="fas fa-exclamation-circle"></i>
        <div><?php echo htmlspecialchars($errorMessage); ?></div>
        <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'"></button>
    </div>
<?php endif; ?>

<!-- Read-Only Notice for Finance Manager -->
<?php if (!$canProcessPayments): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <div>
            <strong>View-Only Access:</strong> As Finance Manager, you can view pending payments but cannot process them. Payment processing is handled by Finance Members.
        </div>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Pending Payments</p>
            <p class="stat-value"><?php echo count($pendingPayments); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Paid (All Time)</p>
            <p class="stat-value"><?php echo number_format($allTimeStats['paid_count'] ?? 0); ?></p>
            <p class="stat-hint">
                <?php echo number_format($thisMonthStats['paid_count'] ?? 0); ?> this month
            </p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Amount (All Time)</p>
            <p class="stat-value">₦<?php echo number_format((float)($allTimeStats['total_amount'] ?? 0), 2); ?></p>
            <p class="stat-hint">
                ₦<?php echo number_format((float)($thisMonthStats['total_amount'] ?? 0), 2); ?> this month
            </p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Average Payment</p>
            <p class="stat-value">₦<?php echo number_format((float)($allTimeStats['average_amount'] ?? 0), 2); ?></p>
            <p class="stat-hint">
                Based on <?php echo number_format($allTimeStats['total_payments'] ?? 0); ?> payments
            </p>
        </div>
    </div>
</div>

<!-- Pending Payments Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-money-check-alt"></i>
            <span>Requisitions Awaiting Payment</span>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($pendingPayments)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="empty-state-title">No Pending Payments</h3>
                <p class="empty-state-text">All requisitions have been processed. Great job!</p>
                <a href="payment-history.php" class="btn btn-primary mt-3">
                    <i class="fas fa-history"></i>
                    <span>View Payment History</span>
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
                            <th>Submitted</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingPayments as $req): ?>
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
                                        ₦<?php echo number_format((float)$req['total_amount'], 2); ?>
                                    </strong>
                                </td>
                                <td>
                                    <div><?php echo format_date($req['submitted_at']); ?></div>
                                    <div class="text-muted small">
                                        <?php echo get_relative_time($req['submitted_at']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($req['purpose']); ?>">
                                        <?php echo htmlspecialchars($req['purpose']); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo get_status_badge($req['status']); ?>
                                </td>
                                <td class="text-center">
                                    <div class="table-actions">
                                        <?php if ($canProcessPayments): ?>
                                            <!-- Finance Member: Can Process Payment -->
                                            <a href="<?php echo build_encrypted_url('process-payment.php', $req['id']); ?>"
                                               class="btn btn-sm btn-success"
                                               title="Process Payment">
                                                <i class="fas fa-money-bill-wave"></i>
                                                <span>Process</span>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Both roles can view details -->
                                        <a href="<?php echo build_encrypted_url('../requisitions/view.php', $req['id']); ?>"
                                           class="btn btn-sm btn-ghost"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                            <span>View</span>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
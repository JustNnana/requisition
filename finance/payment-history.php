<?php
/**
 * GateWey Requisition Management System
 * Payment History Page
 * 
 * File: finance/payment-history.php
 * Purpose: View all processed payments with filters (Finance roles)
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
// Check if user can view payments (Finance Manager or Finance Member)
if (!is_finance_manager() && !is_finance_member()) {
    Session::setFlash('error', 'You do not have permission to view payment history.');
    header('Location: ' . get_user_dashboard_url());
    exit;
}

// Initialize Payment class
$payment = new Payment();

// Get filters
$filters = [
    'date_from' => Sanitizer::string($_GET['date_from'] ?? ''),
    'date_to' => Sanitizer::string($_GET['date_to'] ?? ''),
    'department_id' => Sanitizer::int($_GET['department_id'] ?? 0)
];

// Get current page
$page = Sanitizer::int($_GET['page'] ?? 1);
$page = max(1, $page);

// Get payment history
$history = $payment->getPaymentHistory($filters, $page, 15);

// Get statistics
$stats = $payment->getPaymentStatistics($filters);

// Get departments for filter
$department = new Department();
$departments = $department->getAll(true);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Payment History';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Payment History</h1>
            <p class="content-subtitle">View all processed payments and receipts</p>
        </div>
        <div class="d-flex gap-2">
            <?php if (is_finance_member()): ?>
                <a href="pending-payment.php" class="btn btn-primary">
                    <i class="fas fa-clock"></i> Pending Payments
                </a>
            <?php endif; ?>
            <a href="pending-receipts.php" class="btn btn-ghost">
                <i class="fas fa-receipt"></i> Pending Receipts
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

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Payments</p>
            <p class="stat-value"><?php echo number_format($stats['total_payments'] ?? 0); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Amount Paid</p>
            <p class="stat-value">₦<?php echo number_format((float)$stats['total_amount'], 2 ?? 0); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Average Payment</p>
            <p class="stat-value">₦<?php echo number_format((float)$stats['average_amount'], 2 ?? 0); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Receipts Pending</p>
            <p class="stat-value"><?php echo number_format($stats['paid_count'] ?? 0); ?></p>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-filter"></i> Filters
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="filter-form">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="date_from">Date From</label>
                        <input type="date" 
                               name="date_from" 
                               id="date_from" 
                               class="form-control"
                               value="<?php echo htmlspecialchars($filters['date_from']); ?>">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="date_to">Date To</label>
                        <input type="date" 
                               name="date_to" 
                               id="date_to" 
                               class="form-control"
                               value="<?php echo htmlspecialchars($filters['date_to']); ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select name="department_id" id="department_id" class="form-control">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" 
                                        <?php echo $filters['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-1">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="payment-history.php" class="btn btn-ghost">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Payment History Table -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-history"></i> Payment Records
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($history['records'])): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-history"></i>
                </div>
                <h3 class="empty-state-title">No Payment Records</h3>
                <p class="empty-state-text">No payments found matching your criteria.</p>
                <a href="payment-history.php" class="btn btn-ghost mt-3">
                    <i class="fas fa-redo"></i> Clear Filters
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
                            <th>Paid By</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history['records'] as $record): ?>
                            <tr>
                                <td>
                                    <strong class="text-primary">
                                        <?php echo htmlspecialchars($record['requisition_number']); ?>
                                    </strong>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($record['requester_first_name'] . ' ' . $record['requester_last_name']); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars($record['requester_email']); ?></div>
                                </td>
                                <td>
                                    <?php if ($record['department_name']): ?>
                                        <span class="badge badge-outline-secondary">
                                            <?php echo htmlspecialchars($record['department_code']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-success">₦<?php echo number_format((float)$record['total_amount'], 2); ?>
                                    </strong>
                                </td>
                                <td>
                                    <div><?php echo format_date($record['payment_date']); ?></div>
                                    <div class="text-muted small"><?php echo get_relative_time($record['payment_date']); ?></div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($record['paid_by_first_name'] . ' ' . $record['paid_by_last_name']); ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-outline-info">
                                        <?php echo htmlspecialchars($record['payment_method'] ?? 'N/A'); ?>
                                    </span>
                                    <?php if ($record['payment_reference']): ?>
                                        <div class="text-muted small">
                                            Ref: <?php echo htmlspecialchars($record['payment_reference']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo get_status_badge($record['status']); ?>
                                </td>
                                <td class="text-center">
                                    <div class="table-actions">
                                        <a href="../requisitions/view.php?id=<?php echo $record['id']; ?>" 
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
            
            <!-- Pagination -->
            <?php if ($history['total_pages'] > 1): ?>
                <div class="pagination-wrapper">
                    <nav aria-label="Payment history pagination">
                        <ul class="pagination">
                            <!-- Previous -->
                            <li class="page-item <?php echo $history['page'] <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $history['page'] - 1; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                            
                            <!-- Page Numbers -->
                            <?php for ($i = 1; $i <= $history['total_pages']; $i++): ?>
                                <li class="page-item <?php echo $i == $history['page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <!-- Next -->
                            <li class="page-item <?php echo $history['page'] >= $history['total_pages'] ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $history['page'] + 1; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    
                    <div class="pagination-info">
                        Showing <?php echo count($history['records']); ?> of <?php echo $history['total']; ?> records
                        (Page <?php echo $history['page']; ?> of <?php echo $history['total_pages']; ?>)
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.filter-form {
    background: var(--bg-subtle);
    padding: var(--spacing-4);
    border-radius: var(--border-radius);
}

.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: var(--spacing-4);
    padding-top: var(--spacing-4);
    border-top: 1px solid var(--border-color);
}

.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: var(--spacing-2);
}

.page-item.disabled .page-link {
    opacity: 0.5;
    pointer-events: none;
}

.page-item.active .page-link {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.page-link {
    padding: var(--spacing-2) var(--spacing-4);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    color: var(--text-primary);
    text-decoration: none;
}

.page-link:hover:not(.active) {
    background: var(--bg-hover);
}

.pagination-info {
    color: var(--text-muted);
    font-size: var(--font-size-sm);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state-icon {
    font-size: 80px;
    color: var(--text-muted);
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

/* Table Actions */
.table-actions {
    display: flex;
    gap: 8px;
    justify-content: center;
    align-items: center;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
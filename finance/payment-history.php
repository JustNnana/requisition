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
// Get pending receipts (all requisitions)
$pendingReceipts = $payment->getPendingReceipts(true);
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

    .stat-icon.bg-success {
        background: linear-gradient(135deg, var(--success), #059669);
    }

    .stat-icon.bg-info {
        background: linear-gradient(135deg, var(--info), #0284c7);
    }

    .stat-icon.bg-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    }

    .stat-icon.bg-warning {
        background: linear-gradient(135deg, var(--warning), #f59e0b);
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
        margin: 0;
    }

    /* Filter Section */
    .filter-section {
        background: var(--bg-subtle);
        padding: var(--spacing-5);
        border-radius: var(--border-radius-lg);
        margin-bottom: var(--spacing-5);
        border: 1px solid var(--border-color);
    }

    .filter-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        margin-bottom: var(--spacing-4);
    }

    .filter-header h5 {
        margin: 0;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .filter-header i {
        color: var(--primary);
    }

    .filter-form {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-4);
    }

    .row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-4);
    }

    .col-md-2,
    .col-md-3,
    .col-md-4 {
        display: flex;
        flex-direction: column;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-2);
        height: 100%;
    }

    .form-group label {
        display: block;
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
        margin: 0;
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
        height: 42px;
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
        color: var(--primary);
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
        color: var(--text-muted);
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
        justify-content: center;
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

    .btn-sm {
        padding: var(--spacing-2) var(--spacing-3);
        font-size: var(--font-size-xs);
    }

    .flex-1 {
        flex: 1;
    }

    /* Table Actions */
    .table-actions {
        display: flex;
        gap: var(--spacing-2);
        justify-content: center;
        align-items: center;
    }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--spacing-4);
        border-top: 1px solid var(--border-color);
        flex-wrap: wrap;
        gap: var(--spacing-3);
    }

    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: var(--spacing-1);
    }

    .page-item {
        display: flex;
    }

    .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: var(--spacing-2) var(--spacing-3);
        font-size: var(--font-size-sm);
        color: var(--text-primary);
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        text-decoration: none;
        transition: var(--theme-transition);
    }

    .page-link:hover {
        background: var(--bg-subtle);
        border-color: var(--primary);
        color: var(--primary);
    }

    .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .page-item.disabled .page-link {
        opacity: 0.5;
        pointer-events: none;
        cursor: not-allowed;
    }

    .pagination-info {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    /* Text Utilities */
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

        .row {
            grid-template-columns: 1fr;
        }

        .pagination-wrapper {
            flex-direction: column;
            align-items: center;
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
            <h1 class="content-title">Payment History</h1>
            <p class="content-subtitle">View all processed payments and receipts</p>
        </div>
        <div class="d-flex gap-2">
            <?php if (is_finance_member()): ?>
                <a href="pending-payment.php" class="btn btn-primary">
                    <i class="fas fa-clock"></i>
                    <span>Pending Payments</span>
                </a>
            <?php endif; ?>
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

<?php if ($errorMessage): ?>
    <div class="alert alert-error alert-dismissible">
        <i class="fas fa-exclamation-circle"></i>
        <div><?php echo htmlspecialchars($errorMessage); ?></div>
        <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'"></button>
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
            <p class="stat-value">₦<?php echo number_format((float)($stats['total_amount'] ?? 0), 2); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Average Payment</p>
            <p class="stat-value">₦<?php echo number_format((float)($stats['average_amount'] ?? 0), 2); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Receipts Pending</p>
            <p class="stat-value"><?php echo count($pendingReceipts); ?></p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="filter-section">
    <div class="filter-header">
        <i class="fas fa-filter"></i>
        <h5>Filters</h5>
    </div>
    
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
                            <i class="fas fa-search"></i>
                            <span>Filter</span>
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

<!-- Payment History Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-history"></i>
            <span>Payment Records</span>
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
                    <i class="fas fa-redo"></i>
                    <span>Clear Filters</span>
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
                                    <strong class="text-success">
                                        ₦<?php echo number_format((float)$record['total_amount'], 2); ?>
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
                                    <?php echo get_status_badge($record['status'], $record); ?>
                                </td>
                                <td class="text-center">
                                    <div class="table-actions">
                                        <a href="<?php echo build_encrypted_url('../requisitions/view.php', $record['id']); ?>"
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
                                    <i class="fas fa-chevron-left"></i>
                                    <span>Previous</span>
                                </a>
                            </li>
                            
                            <!-- Page Numbers -->
                            <?php 
                            $startPage = max(1, $history['page'] - 2);
                            $endPage = min($history['total_pages'], $history['page'] + 2);
                            for ($i = $startPage; $i <= $endPage; $i++): 
                            ?>
                                <li class="page-item <?php echo $i == $history['page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <!-- Next -->
                            <li class="page-item <?php echo $history['page'] >= $history['total_pages'] ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $history['page'] + 1; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>">
                                    <span>Next</span>
                                    <i class="fas fa-chevron-right"></i>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php
/**
 * GateWey Requisition Management System
 * List Requisitions Page
 * 
 * File: requisitions/list.php
 * Purpose: Display all requisitions for current user with filters
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication and authorization
require_once __DIR__ . '/../middleware/auth-check.php';
require_once __DIR__ . '/../middleware/role-check.php';
require_once __DIR__ . '/../helpers/permissions.php';
require_once __DIR__ . '/../helpers/status-indicator.php';

// Initialize objects
$requisition = new Requisition();

// Get filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'search' => $_GET['search'] ?? ''
];

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;

// Get requisitions
$result = $requisition->getForUser($filters, $page, $perPage);
$requisitions = $result['requisitions'] ?? [];
$pagination = $result['pagination'] ?? [];

// Get statistics
$stats = $requisition->getStatistics();

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'My Requisitions';
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

    .stat-icon.bg-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    }

    .stat-icon.bg-warning {
        background: linear-gradient(135deg, var(--warning), #f59e0b);
    }

    .stat-icon.bg-success {
        background: linear-gradient(135deg, var(--success), #059669);
    }

    .stat-icon.bg-danger {
        background: linear-gradient(135deg, var(--danger), #dc2626);
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

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-4);
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-2);
    }

    .form-label {
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

    .form-select {
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23666' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right var(--spacing-3) center;
        background-size: 16px 12px;
        padding-right: var(--spacing-8);
    }

    .filter-actions {
        display: flex;
        gap: var(--spacing-3);
        justify-content: flex-end;
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

    .badge-secondary {
        background: rgba(var(--text-secondary), 0.1);
        color: var(--text-secondary);
    }

    /* Action Buttons */
    .btn-group {
        display: flex;
        gap: var(--spacing-2);
    }

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

    .btn-secondary {
        background: var(--bg-subtle);
        color: var(--text-primary);
        border-color: var(--border-color);
    }

    .btn-secondary:hover {
        background: var(--border-color);
    }

    .btn-ghost {
        background: transparent;
        color: var(--text-secondary);
        border-color: transparent;
    }

    .btn-ghost:hover {
        background: var(--bg-subtle);
        color: var(--text-primary);
    }

    .btn-sm {
        padding: var(--spacing-2) var(--spacing-3);
        font-size: var(--font-size-xs);
    }

    /* Empty State */
    .empty-state {
        padding: var(--spacing-8) var(--spacing-4);
        text-align: center;
    }

    .empty-state-icon {
        font-size: 4rem;
        color: var(--text-muted);
        margin-bottom: var(--spacing-4);
    }

    .empty-state-text {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-4);
    }

    /* Pagination */
    .pagination-container {
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

    .pagination-info {
        font-size: var(--font-size-sm);
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

    .align-items-start {
        align-items: flex-start;
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

    .text-muted {
        color: var(--text-muted) !important;
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

        .form-row {
            grid-template-columns: 1fr;
        }

        .filter-actions {
            flex-direction: column;
        }

        .filter-actions .btn {
            width: 100%;
        }

        .pagination-container {
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
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">My Requisitions</h1>
            <p class="content-subtitle">View and manage your purchase requisitions</p>
        </div>
        <?php if (can_user_raise_requisition()): ?>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                <span>New Requisition</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Alert Messages -->
<?php if ($successMessage): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <div><?php echo htmlspecialchars($successMessage); ?></div>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <div><?php echo htmlspecialchars($errorMessage); ?></div>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Requisitions</p>
            <p class="stat-value"><?php echo number_format($stats['total'] ?? 0); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Pending</p>
            <p class="stat-value"><?php echo number_format($stats['pending'] ?? 0); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Approved</p>
            <p class="stat-value"><?php echo number_format($stats['approved'] ?? 0); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Rejected</p>
            <p class="stat-value"><?php echo number_format($stats['rejected'] ?? 0); ?></p>
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
        <div class="form-row">
            <div class="form-group">
                <label for="search" class="form-label">Search</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    class="form-control"
                    placeholder="Requisition number or purpose..."
                    value="<?php echo htmlspecialchars($filters['search']); ?>">
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-control form-select">
                    <option value="">All Statuses</option>
                    <option value="<?php echo STATUS_DRAFT; ?>" <?php echo $filters['status'] == STATUS_DRAFT ? 'selected' : ''; ?>>
                        Draft
                    </option>
                    <option value="<?php echo STATUS_PENDING_LINE_MANAGER; ?>" <?php echo $filters['status'] == STATUS_PENDING_LINE_MANAGER ? 'selected' : ''; ?>>
                        Pending Line Manager
                    </option>
                    <option value="<?php echo STATUS_PENDING_MD; ?>" <?php echo $filters['status'] == STATUS_PENDING_MD ? 'selected' : ''; ?>>
                        Pending MD
                    </option>
                    <option value="<?php echo STATUS_PENDING_FINANCE_MANAGER; ?>" <?php echo $filters['status'] == STATUS_PENDING_FINANCE_MANAGER ? 'selected' : ''; ?>>
                        Pending Finance Manager
                    </option>
                    <option value="<?php echo STATUS_APPROVED_FOR_PAYMENT; ?>" <?php echo $filters['status'] == STATUS_APPROVED_FOR_PAYMENT ? 'selected' : ''; ?>>
                        Approved for Payment
                    </option>
                    <option value="<?php echo STATUS_PAID; ?>" <?php echo $filters['status'] == STATUS_PAID ? 'selected' : ''; ?>>
                        Paid
                    </option>
                    <option value="<?php echo STATUS_COMPLETED; ?>" <?php echo $filters['status'] == STATUS_COMPLETED ? 'selected' : ''; ?>>
                        Completed
                    </option>
                    <option value="<?php echo STATUS_REJECTED; ?>" <?php echo $filters['status'] == STATUS_REJECTED ? 'selected' : ''; ?>>
                        Rejected
                    </option>
                    <option value="<?php echo STATUS_CANCELLED; ?>" <?php echo $filters['status'] == STATUS_CANCELLED ? 'selected' : ''; ?>>
                        Cancelled
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="date_from" class="form-label">From Date</label>
                <input
                    type="date"
                    id="date_from"
                    name="date_from"
                    class="form-control"
                    value="<?php echo htmlspecialchars($filters['date_from']); ?>">
            </div>

            <div class="form-group">
                <label for="date_to" class="form-label">To Date</label>
                <input
                    type="date"
                    id="date_to"
                    name="date_to"
                    class="form-control"
                    value="<?php echo htmlspecialchars($filters['date_to']); ?>">
            </div>
        </div>

        <div class="filter-actions">
            <a href="list.php" class="btn btn-secondary">
                <i class="fas fa-undo"></i>
                <span>Reset</span>
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
                <span>Apply Filters</span>
            </button>
        </div>
    </form>
</div>

<!-- Requisitions Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-list"></i>
            <span>Requisitions</span>
            <?php if (!empty($pagination['total_records'])): ?>
                <span style="color: var(--text-muted); font-weight: normal; font-size: var(--font-size-sm); margin-left: var(--spacing-2);">
                    (<?php echo number_format($pagination['total_records']); ?> total)
                </span>
            <?php endif; ?>
        </h5>
    </div>
    
    <div class="card-body">
        <?php if (empty($requisitions)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <p class="empty-state-text">No requisitions found</p>
                <?php if (can_user_raise_requisition()): ?>
                    <a href="create.php" class="btn btn-primary mt-3">
                        <i class="fas fa-plus"></i>
                        <span>Create Your First Requisition</span>
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Req. Number</th>
                            <th>Purpose</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Sort requisitions by newest submitted_at (with NULL handling for drafts)
                        usort($requisitions, function ($a, $b) {
                            // Handle NULL values - use created_at for drafts or set to 0
                            $timeA = $a['submitted_at'] ? strtotime($a['submitted_at']) : ($a['created_at'] ? strtotime($a['created_at']) : 0);
                            $timeB = $b['submitted_at'] ? strtotime($b['submitted_at']) : ($b['created_at'] ? strtotime($b['created_at']) : 0);
                            
                            return $timeB - $timeA; // Newest first
                        });
                        ?>
                        <?php foreach ($requisitions as $req): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($req['requisition_number']); ?></strong>
                                    <?php if ($req['is_draft']): ?>
                                        <span class="badge badge-secondary" style="font-size: 10px; margin-left: 4px;">DRAFT</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $purpose = htmlspecialchars($req['purpose']);
                                    echo strlen($purpose) > 50 ? substr($purpose, 0, 50) . '...' : $purpose;
                                    ?>
                                </td>
                                <td>
                                    <strong>₦<?php echo number_format((float)$req['total_amount'], 2); ?></strong>
                                </td>
                                <td>
                                    <?php echo get_status_badge($req['status'], $req); ?>
                                </td>
                                <td>
                                    <?php
                                    if ($req['submitted_at']) {
                                        echo format_date($req['submitted_at'], 'M d, Y');
                                    } else {
                                        echo '<span class="text-muted">Not submitted</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo build_encrypted_url('view.php', $req['id']); ?>" class="btn btn-sm btn-ghost" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($req['is_draft'] || $req['status'] == STATUS_REJECTED): ?>
                                            <a href="<?php echo build_encrypted_url('edit.php', $req['id']); ?>" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="pagination-container">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <!-- Previous Page -->
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo !empty($filters['status']) ? '&status=' . $filters['status'] : ''; ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['date_from']) ? '&date_from=' . $filters['date_from'] : ''; ?><?php echo !empty($filters['date_to']) ? '&date_to=' . $filters['date_to'] : ''; ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <!-- Page Numbers -->
                            <?php
                            $startPage = max(1, $pagination['current_page'] - 2);
                            $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($filters['status']) ? '&status=' . $filters['status'] : ''; ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['date_from']) ? '&date_from=' . $filters['date_from'] : ''; ?><?php echo !empty($filters['date_to']) ? '&date_to=' . $filters['date_to'] : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Page -->
                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo !empty($filters['status']) ? '&status=' . $filters['status'] : ''; ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['date_from']) ? '&date_from=' . $filters['date_from'] : ''; ?><?php echo !empty($filters['date_to']) ? '&date_to=' . $filters['date_to'] : ''; ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>

                    <div class="pagination-info">
                        Showing <?php echo (($pagination['current_page'] - 1) * $pagination['per_page']) + 1; ?>
                        to <?php echo min($pagination['current_page'] * $pagination['per_page'], $pagination['total_records']); ?>
                        of <?php echo $pagination['total_records']; ?> requisitions
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
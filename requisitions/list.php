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

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">My Requisitions</h1>
            <p class="content-subtitle">View and manage your purchase requisitions</p>
        </div>
        <?php if (can_user_raise_requisition()): ?>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Requisition
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($successMessage): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo $successMessage; ?>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo $errorMessage; ?>
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
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-filter"></i> Filters
        </h5>
    </div>
    <div class="card-body">
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
                    <i class="fas fa-undo"></i> Reset
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Requisitions Table -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list"></i> Requisitions
            <?php if (!empty($pagination['total_records'])): ?>
                <span style="color: var(--text-muted); font-weight: normal; font-size: var(--font-size-sm);">
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
                        <i class="fas fa-plus"></i> Create Your First Requisition
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
                        // Sort requisitions by newest submitted_at
                        usort($requisitions, function ($a, $b) {
                            return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
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
                                    <strong><?php echo format_currency($req['total_amount']); ?></strong>
                                </td>
                                <td>
                                    <?php echo get_status_badge($req['status']); ?>
                                </td>
                                <td>
                                    <?php
                                    if ($req['submitted_at']) {
                                        echo format_date($req['submitted_at'], 'M d, Y');
                                    } else {
                                        echo '<span style="color: var(--text-muted);">Not submitted</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="view.php?id=<?php echo $req['id']; ?>" class="btn btn-sm btn-ghost" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <?php if ($req['is_draft'] || $req['status'] == STATUS_REJECTED): ?>
                                            <a href="edit.php?id=<?php echo $req['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
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
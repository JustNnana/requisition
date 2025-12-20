<?php
/**
 * GateWey Requisition Management System
 * Finance Manager Review Queue
 * 
 * File: finance/review-queue.php
 * Purpose: Finance Manager reviews requisitions before Finance Member processes payment
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
    Session::setFlash('error', 'Only Finance Managers can access this page.');
    header('Location: ' . get_user_dashboard_url());
    exit;
}

// Initialize classes
$requisition = new Requisition();
$department = new Department();

// Get filter parameters
$filters = [
    'department_id' => Sanitizer::int($_GET['department_id'] ?? 0),
    'min_amount' => Sanitizer::float($_GET['min_amount'] ?? 0),
    'max_amount' => Sanitizer::float($_GET['max_amount'] ?? 0),
    'sort' => Sanitizer::string($_GET['sort'] ?? 'date_desc')
];

// Get all requisitions pending Finance Manager approval
$sql = "SELECT r.*, 
               u.first_name as requester_first_name,
               u.last_name as requester_last_name,
               u.email as requester_email,
               d.department_name,
               d.department_code,
               md.first_name as md_first_name,
               md.last_name as md_last_name
        FROM requisitions r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        LEFT JOIN users md ON r.current_approver_id = md.id
        WHERE r.status = ?";

$params = [STATUS_PENDING_FINANCE_MANAGER];

// Apply filters
if ($filters['department_id']) {
    $sql .= " AND r.department_id = ?";
    $params[] = $filters['department_id'];
}

if ($filters['min_amount']) {
    $sql .= " AND r.total_amount >= ?";
    $params[] = $filters['min_amount'];
}

if ($filters['max_amount']) {
    $sql .= " AND r.total_amount <= ?";
    $params[] = $filters['max_amount'];
}

// Apply sorting
switch ($filters['sort']) {
    case 'amount_asc':
        $sql .= " ORDER BY r.total_amount ASC";
        break;
    case 'amount_desc':
        $sql .= " ORDER BY r.total_amount DESC";
        break;
    case 'date_desc':
        $sql .= " ORDER BY r.submitted_at DESC";
        break;
    case 'date_asc':
    default:
        $sql .= " ORDER BY r.submitted_at DESC";
        break;
}

$db = Database::getInstance();
$pendingRequisitions = $db->fetchAll($sql, $params);

// Calculate statistics
$totalAmount = array_sum(array_column($pendingRequisitions, 'total_amount'));
$avgAmount = count($pendingRequisitions) > 0 ? $totalAmount / count($pendingRequisitions) : 0;
$maxAmount = count($pendingRequisitions) > 0 ? max(array_column($pendingRequisitions, 'total_amount')) : 0;

// Get departments for filter
$departments = $department->getAll(true);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Review Queue - Finance Manager';
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
        position: relative;
        padding-right: var(--spacing-10);
    }

    .btn-close {
        position: absolute;
        top: var(--spacing-4);
        right: var(--spacing-4);
        background: none;
        border: none;
        font-size: var(--font-size-xl);
        cursor: pointer;
        color: inherit;
        opacity: 0.5;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-close:hover {
        opacity: 1;
    }

    .btn-close::before {
        content: '×';
    }

    .alert-heading {
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        margin: 0 0 var(--spacing-2) 0;
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .alert p {
        margin: 0;
        font-size: var(--font-size-sm);
        color: var(--text-primary);
    }

    /* Statistics Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: var(--spacing-4);
        margin-bottom: var(--spacing-5);
    }

    .stat-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-5);
        display: flex;
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
        font-size: var(--font-size-2xl);
        color: white;
        flex-shrink: 0;
    }

    .stat-icon.bg-warning {
        background: linear-gradient(135deg, var(--warning), #d97706);
    }

    .stat-icon.bg-info {
        background: linear-gradient(135deg, var(--info), #0284c7);
    }

    .stat-icon.bg-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    }

    .stat-icon.bg-danger {
        background: linear-gradient(135deg, var(--danger), #dc2626);
    }

    .stat-content {
        flex: 1;
        min-width: 0;
    }

    .stat-label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0 0 var(--spacing-2) 0;
        font-weight: var(--font-weight-medium);
    }

    .stat-value {
        font-size: var(--font-size-2xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin: 0;
    }

    /* Card Styles */
    .card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        margin-bottom: var(--spacing-5);
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

    .card-body {
        padding: var(--spacing-5);
    }

    /* Filter Form */
    .filter-form .row {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 2fr 1fr;
        gap: var(--spacing-4);
        align-items: end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
        margin-bottom: var(--spacing-2);
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

    /* Table Styles */
    .table-responsive {
        overflow-x: auto;
        margin: calc(var(--spacing-5) * -1);
        padding: var(--spacing-5);
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
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border-color);
    }

    .table td {
        padding: var(--spacing-4);
        font-size: var(--font-size-sm);
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
    }

    .table tbody tr {
        transition: var(--theme-transition);
    }

    .table tbody tr:hover {
        background: var(--bg-subtle);
    }

    .table th.text-center,
    .table td.text-center {
        text-align: center;
    }

    /* User Info */
    .user-info {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .user-avatar-sm {
        width: 36px;
        height: 36px;
        border-radius: var(--border-radius-full);
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
        margin-bottom: var(--spacing-1);
    }

    .user-email {
        color: var(--text-muted);
        font-size: var(--font-size-xs);
    }

    /* Badge Styles */
    .badge {
        display: inline-flex;
        align-items: center;
        padding: var(--spacing-1) var(--spacing-3);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        line-height: 1;
    }

    .badge-outline-secondary {
        background: transparent;
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
    }

    /* Text Utilities */
    .text-primary {
        color: var(--primary) !important;
    }

    .text-success {
        color: var(--success) !important;
    }

    .text-danger {
        color: var(--danger) !important;
    }

    .text-warning {
        color: var(--warning) !important;
    }

    .text-muted {
        color: var(--text-muted) !important;
    }

    .text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .small {
        font-size: var(--font-size-xs);
    }

    /* Table Actions */
    .table-actions {
        display: flex;
        gap: var(--spacing-2);
        justify-content: center;
        align-items: center;
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

    .btn-ghost {
        background: transparent;
        color: var(--text-primary);
        border-color: var(--border-color);
    }

    .btn-ghost:hover {
        background: var(--bg-subtle);
    }

    .btn-danger {
        background: var(--danger);
        color: white;
        border-color: var(--danger);
    }

    .btn-danger:hover {
        background: #dc2626;
        border-color: #dc2626;
    }

    .btn-sm {
        padding: var(--spacing-2) var(--spacing-3);
        font-size: var(--font-size-xs);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: var(--spacing-10) var(--spacing-5);
    }

    .empty-state-icon {
        font-size: 4rem;
        color: var(--success);
        margin-bottom: var(--spacing-4);
        opacity: 0.5;
    }

    .empty-state-title {
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-2) 0;
    }

    .empty-state-text {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        max-width: 500px;
        margin: 0 auto var(--spacing-4) auto;
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

    .mb-4 {
        margin-bottom: var(--spacing-4);
    }

    .mt-3 {
        margin-top: var(--spacing-3);
    }

    .mt-4 {
        margin-top: var(--spacing-4);
    }

    .mb-0 {
        margin-bottom: 0;
    }

    .flex-1 {
        flex: 1;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .content-header .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: var(--spacing-3);
        }

        .content-header .d-flex .d-flex {
            width: 100%;
            flex-direction: column;
        }

        .content-header .btn {
            width: 100%;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .filter-form .row {
            grid-template-columns: 1fr;
        }

        .table-responsive {
            margin: 0;
            padding: 0;
        }

        .table {
            font-size: var(--font-size-xs);
        }

        .table th,
        .table td {
            padding: var(--spacing-2);
        }

        .user-info {
            flex-direction: column;
            align-items: flex-start;
        }

        .table-actions {
            flex-direction: column;
            width: 100%;
        }

        .table-actions .btn {
            width: 100%;
        }
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Finance Manager Review Queue</h1>
            <p class="content-subtitle">Review and approve requisitions before payment processing</p>
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

<!-- Success/Error Messages -->
<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible">
        <i class="fas fa-check-circle"></i>
        <span><?php echo htmlspecialchars($successMessage); ?></span>
        <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'"></button>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-error alert-dismissible">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo htmlspecialchars($errorMessage); ?></span>
        <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'"></button>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Awaiting Review</p>
            <p class="stat-value"><?php echo count($pendingRequisitions); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Amount</p>
            <p class="stat-value">₦<?php echo number_format($totalAmount, 2); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Average Amount</p>
            <p class="stat-value">₦<?php echo number_format($avgAmount, 2); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Highest Amount</p>
            <p class="stat-value">₦<?php echo number_format($maxAmount, 2); ?></p>
        </div>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info mt-4">
    <i class="fas fa-info-circle"></i>
    <div>
        <h6 class="alert-heading">Your Role as Finance Manager</h6>
        <p class="mb-0">
            You are responsible for the final financial review before payment processing. 
            Review requisitions for budget compliance, documentation completeness, and financial accuracy. 
            Approve to send to Finance Member for payment, or reject to return to previous approver.
        </p>
    </div>
</div>

<!-- Filters Card -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-filter"></i>
            <span>Filter & Sort</span>
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="filter-form">
            <div class="row">
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
                
                <div class="form-group">
                    <label for="min_amount">Min Amount</label>
                    <input type="number" 
                           name="min_amount" 
                           id="min_amount" 
                           class="form-control"
                           step="0.01"
                           placeholder="0.00"
                           value="<?php echo $filters['min_amount'] ? $filters['min_amount'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="max_amount">Max Amount</label>
                    <input type="number" 
                           name="max_amount" 
                           id="max_amount" 
                           class="form-control"
                           step="0.01"
                           placeholder="0.00"
                           value="<?php echo $filters['max_amount'] ? $filters['max_amount'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="sort">Sort By</label>
                    <select name="sort" id="sort" class="form-control">
                        <option value="date_asc" <?php echo $filters['sort'] == 'date_asc' ? 'selected' : ''; ?>>
                            Date (Oldest First)
                        </option>
                        <option value="date_desc" <?php echo $filters['sort'] == 'date_desc' ? 'selected' : ''; ?>>
                            Date (Newest First)
                        </option>
                        <option value="amount_asc" <?php echo $filters['sort'] == 'amount_asc' ? 'selected' : ''; ?>>
                            Amount (Low to High)
                        </option>
                        <option value="amount_desc" <?php echo $filters['sort'] == 'amount_desc' ? 'selected' : ''; ?>>
                            Amount (High to Low)
                        </option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-1">
                            <i class="fas fa-search"></i>
                            <span>Apply</span>
                        </button>
                        <a href="review-queue.php" class="btn btn-ghost">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Review Queue Table -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-clipboard-list"></i>
            <span>Requisitions Awaiting Your Review</span>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($pendingRequisitions)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="empty-state-title">No Requisitions to Review</h3>
                <p class="empty-state-text">All requisitions have been reviewed. Great job!</p>
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
                            <th>Days Pending</th>
                            <th>Purpose</th>
                            <th>Previous Approver</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingRequisitions as $req): ?>
                            <?php
                            // Calculate days pending
                            $submittedDate = new DateTime($req['submitted_at']);
                            $now = new DateTime();
                            $daysPending = $now->diff($submittedDate)->days;
                            
                            // Determine urgency
                            $urgencyClass = '';
                            $urgencyIcon = '';
                            if ($daysPending > 7) {
                                $urgencyClass = 'text-danger';
                                $urgencyIcon = '<i class="fas fa-exclamation-triangle"></i>';
                            } elseif ($daysPending > 3) {
                                $urgencyClass = 'text-warning';
                                $urgencyIcon = '<i class="fas fa-clock"></i>';
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
                                    <strong class="text-success" style="font-size: 1.1em;">
                                        ₦<?php echo number_format($req['total_amount'], 2); ?>
                                    </strong>
                                </td>
                                <td>
                                    <div><?php echo format_date($req['submitted_at']); ?></div>
                                    <div class="text-muted small">
                                        <?php echo get_relative_time($req['submitted_at']); ?>
                                    </div>
                                </td>
                                <td>
                                    <strong class="<?php echo $urgencyClass; ?>">
                                        <?php echo $urgencyIcon; ?> <?php echo $daysPending; ?> days
                                    </strong>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($req['purpose']); ?>">
                                        <?php echo htmlspecialchars($req['purpose']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        <i class="fas fa-user-tie"></i> 
                                        <?php echo htmlspecialchars($req['md_first_name'] . ' ' . $req['md_last_name']); ?>
                                        <div class="text-muted">Managing Director</div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="table-actions">
                                        <a href="<?php echo build_encrypted_url('../requisitions/view.php', $req['id']); ?>"
                                           class="btn btn-sm btn-primary"
                                           title="Review & Approve">
                                            <i class="fas fa-clipboard-check"></i>
                                            <span>Review</span>
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
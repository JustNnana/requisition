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
    'sort' => Sanitizer::string($_GET['sort'] ?? 'date_asc')
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
        $sql .= " ORDER BY r.submitted_at ASC";
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

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Finance Manager Review Queue</h1>
            <p class="content-subtitle">Review and approve requisitions before payment processing</p>
        </div>
        <div class="d-flex gap-2">
            <a href="payment-history.php" class="btn btn-ghost">
                <i class="fas fa-history"></i> Payment History
            </a>
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
            <p class="stat-value">₦<?php echo ($totalAmount); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Average Amount</p>
            <p class="stat-value">₦<?php echo ($avgAmount); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Highest Amount</p>
            <p class="stat-value">₦<?php echo ($maxAmount); ?></p>
        </div>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info mt-4">
    <h6 class="alert-heading">
        <i class="fas fa-info-circle"></i> Your Role as Finance Manager
    </h6>
    <p class="mb-0">
        You are responsible for the final financial review before payment processing. 
        Review requisitions for budget compliance, documentation completeness, and financial accuracy. 
        Approve to send to Finance Member for payment, or reject to return to previous approver.
    </p>
</div>

<!-- Filters Card -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-filter"></i> Filter & Sort
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="filter-form">
            <div class="row">
                <div class="col-md-3">
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
                        <label for="min_amount">Min Amount</label>
                        <input type="number" 
                               name="min_amount" 
                               id="min_amount" 
                               class="form-control"
                               step="0.01"
                               placeholder="0.00"
                               value="<?php echo $filters['min_amount'] ? $filters['min_amount'] : ''; ?>">
                    </div>
                </div>
                
                <div class="col-md-2">
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
                </div>
                
                <div class="col-md-3">
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
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-1">
                                <i class="fas fa-search"></i> Apply
                            </button>
                            <a href="review-queue.php" class="btn btn-ghost">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
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
            <i class="fas fa-clipboard-list"></i> Requisitions Awaiting Your Review
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
                                    <strong class="text-success" style="font-size: 1.1em;">₦<?php echo ($req['total_amount']); ?>
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
                                        <a href="../requisitions/view.php?id=<?php echo $req['id']; ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="Review & Approve">
                                            <i class="fas fa-clipboard-check"></i> Review
                                        </a>
                                        <!-- <a href="reject-before-payment.php?id=<?php echo $req['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Reject">
                                            <i class="fas fa-times"></i>
                                        </a> -->
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

/* Filter Form */
.filter-form {
    background: var(--bg-subtle);
    padding: var(--spacing-4);
    border-radius: var(--border-radius);
}

/* Text Truncate */
.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
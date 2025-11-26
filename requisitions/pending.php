<?php
/**
 * GateWey Requisition Management System
 * Pending Approvals Page
 * 
 * File: requisitions/pending.php
 * Purpose: Display requisitions pending approval for current user
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';
// Load helpers - MUST include permissions.php before using role functions
require_once __DIR__ . '/../helpers/permissions.php';
require_once __DIR__ . '/../helpers/status-indicator.php';
// Check if user can approve (Line Manager, MD, Finance Manager, Finance Member)
if (!can_user_approve()) {
    Session::setFlash('error', 'You do not have permission to access this page.');
    header('Location: ' . get_user_dashboard_url());
    exit;
}

// Initialize objects
$workflow = new WorkflowEngine();
$approval = new Approval();

// Get current user info
$userId = Session::getUserId();
$userRoleId = Session::getUserRoleId();

// Get pending requisitions for this user
$pendingRequisitions = $workflow->getPendingRequisitionsForUser($userId);

// Get statistics
$stats = $approval->getApprovalStatistics($userId);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Pending Approvals';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Pending Approvals</h1>
            <p class="content-subtitle">Review and approve requisitions awaiting your action</p>
        </div>
        <div class="d-flex gap-2">
            <a href="list.php" class="btn btn-ghost">
                <i class="fas fa-list"></i> My Requisitions
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
            <p class="stat-label">Pending Approvals</p>
            <p class="stat-value"><?php echo count($pendingRequisitions); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Approved</p>
            <p class="stat-value"><?php echo number_format($stats['approved_count'] ?? 0); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Rejected</p>
            <p class="stat-value"><?php echo number_format($stats['rejected_count'] ?? 0); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">Total Actions</p>
            <p class="stat-value"><?php echo number_format($stats['total_actions'] ?? 0); ?></p>
        </div>
    </div>
</div>

<!-- Pending Requisitions Table -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-clock"></i> Requisitions Awaiting Your Approval
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($pendingRequisitions)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="empty-state-title">No Pending Approvals</h3>
                <p class="empty-state-text">You're all caught up! There are no requisitions awaiting your approval at this time.</p>
                <a href="<?php echo get_user_dashboard_url(); ?>" class="btn btn-primary mt-3">
                    <i class="fas fa-home"></i> Go to Dashboard
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
                        <?php foreach ($pendingRequisitions as $req): ?>
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
                                        <a href="view.php?id=<?php echo $req['id']; ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="Review & Approve/Reject">
                                            <i class="fas fa-eye"></i> Review
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

/* Text Truncate */
.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php
/**
 * GateWey Requisition Management System
 * Admin Help & Support Management
 *
 * File: admin/help/index.php
 * Purpose: Manage help articles, tips, and video tutorials
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../../middleware/auth-check.php';

// Check if user is super admin (role_id = 1)
if (Session::getUserRoleId() != 1) {
    Session::setFlash('error', 'Unauthorized access. Only super administrators can manage help content.');
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

// Initialize model
$helpModel = new HelpSupport();

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $result = $helpModel->delete($id);

    if ($result['success']) {
        Session::setFlash('success', $result['message']);
    } else {
        Session::setFlash('error', $result['message']);
    }

    header('Location: index.php');
    exit;
}

// Get all help items
$helpItems = $helpModel->getAll();

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

$pageTitle = 'Manage Help & Support';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="page-wrapper">
    <div class="page-header">
        <div class="page-header-content">
            <div class="page-header-text">
                <h1><i class="fas fa-question-circle"></i> <?php echo $pageTitle; ?></h1>
                <p>Create and manage help articles, tips, and video tutorials for users</p>
            </div>
        </div>
        <div class="page-actions">
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Add Help Item
            </a>
        </div>
    </div>

    <?php if ($successMessage): ?>
        <div class="alert alert-success alert-dismissible">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($successMessage); ?>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-error alert-dismissible">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($errorMessage); ?>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                Help Items
            </h3>
            <div class="card-actions">
                <span class="badge badge-info"><?php echo count($helpItems); ?> Total</span>
            </div>
        </div>

        <div class="card-body">
            <?php if (empty($helpItems)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Help Items Yet</h3>
                    <p>Click "Add Help Item" to create your first help article, tip, or video tutorial.</p>
                    <a href="create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Help Item
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($helpItems as $item): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-secondary"><?php echo $item['display_order']; ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas <?php echo htmlspecialchars($item['icon']); ?> text-primary"></i>
                                            <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $typeColors = [
                                            'tip' => 'info',
                                            'video' => 'danger',
                                            'article' => 'success'
                                        ];
                                        $typeColor = $typeColors[$item['type']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?php echo $typeColor; ?>">
                                            <?php echo ucfirst($item['type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-outline-primary">
                                            <?php echo htmlspecialchars($item['category']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($item['is_active']): ?>
                                            <span class="status-badge status-active">
                                                <i class="fas fa-check-circle"></i>
                                                Active
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-inactive">
                                                <i class="fas fa-times-circle"></i>
                                                Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="edit.php?id=<?php echo $item['id']; ?>"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?action=delete&id=<?php echo $item['id']; ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this help item? This action cannot be undone.');"
                                               title="Delete">
                                                <i class="fas fa-trash"></i>
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
</div>

<style>
    .empty-state {
        text-align: center;
        padding: var(--spacing-8) var(--spacing-4);
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--text-muted);
        margin-bottom: var(--spacing-4);
    }

    .empty-state h3 {
        color: var(--text-primary);
        margin-bottom: var(--spacing-2);
    }

    .empty-state p {
        color: var(--text-secondary);
        margin-bottom: var(--spacing-4);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-1);
        padding: var(--spacing-1) var(--spacing-3);
        border-radius: var(--border-radius);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
    }

    .status-active {
        background: var(--bg-success);
        color: var(--success);
    }

    .status-inactive {
        background: var(--bg-muted);
        color: var(--text-muted);
    }

    .btn-group {
        display: inline-flex;
        gap: var(--spacing-2);
    }
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

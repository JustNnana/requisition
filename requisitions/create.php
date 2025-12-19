<?php
/**
 * GateWey Requisition Management System
 * Create Requisition Page
 * 
 * File: requisitions/create.php
 * Purpose: Form to create a new requisition with dynamic item table and real-time budget checking
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

// Only users who can raise requisitions can access this page
if (!can_user_raise_requisition()) {
    Session::setFlash('error', 'You do not have permission to create requisitions.');
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

// Check if user has department and active budget
$userDepartmentId = Session::getUserDepartmentId();
$userRoleId = Session::getUserRoleId();
$hasBudget = false;
$budgetInfo = null;
$showBudgetCheck = false;

// Check budget for Team Members, Line Managers, AND Managing Directors in departments
if ($userDepartmentId && in_array($userRoleId, [ROLE_TEAM_MEMBER, ROLE_LINE_MANAGER, ROLE_MANAGING_DIRECTOR])) {
    $budgetModel = new Budget();
    $budgetInfo = $budgetModel->getBudgetStats($userDepartmentId);
    
    if ($budgetInfo) {
        $hasBudget = true;
        $showBudgetCheck = true;
    }
}

// Only Finance roles bypass budget checks (Finance Manager and Finance Member)
$bypassBudget = in_array($userRoleId, [ROLE_FINANCE_MANAGER, ROLE_FINANCE_MEMBER]);

// Initialize objects
$requisition = new Requisition();
$categoryModel = new RequisitionCategory();

// Load active categories from database
$parentCategories = $categoryModel->getParentCategories(true);

// Initialize variables
$errors = [];
$success = '';
$formData = [
    'purpose' => '',
    'description' => '',
    'items' => []
];

// Page title
$pageTitle = 'Create Requisition';
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
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .content-description {
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

    .alert-warning {
        background: rgba(var(--warning-rgb), 0.1);
        border-color: rgba(var(--warning-rgb), 0.2);
        color: var(--warning);
    }

    .alert-danger {
        background: rgba(var(--danger-rgb), 0.1);
        border-color: rgba(var(--danger-rgb), 0.2);
        color: var(--danger);
    }

    .alert i {
        font-size: var(--font-size-lg);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .alert-content {
        flex: 1;
    }

    .alert-title {
        font-weight: var(--font-weight-semibold);
        margin-bottom: var(--spacing-1);
    }

    .alert-message {
        font-size: var(--font-size-sm);
    }

    /* Workflow Info Alert */
    .workflow-info-alert {
        border: 1px solid var(--info);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-5);
        background: rgba(var(--info-rgb), 0.1);
        color: var(--info);
        margin-bottom: var(--spacing-5);
    }

    .workflow-info-content {
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-4);
    }

    .workflow-info-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }

    .workflow-info-text {
        flex: 1;
    }

    .workflow-info-text h5 {
        margin: 0 0 var(--spacing-2) 0;
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .workflow-info-text p {
        margin: 0;
        color: var(--text-primary);
    }

    /* Budget Card */
    .budget-availability-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        margin-bottom: var(--spacing-5);
        overflow: hidden;
    }

    .budget-card-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
    }

    .budget-card-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--border-radius);
        background: rgba(var(--primary-rgb), 0.1);
        /*color: var(--primary);*/
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-xl);
    }

    .budget-card-title h5 {
        margin: 0 0 var(--spacing-1) 0;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .budget-card-title p {
        margin: 0;
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .budget-card-body {
        padding: var(--spacing-5);
    }

    .budget-stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--spacing-4);
        margin-bottom: var(--spacing-4);
    }

    .budget-stat-item {
        text-align: center;
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
    }

    .budget-stat-item label {
        display: block;
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: var(--spacing-2);
        font-weight: var(--font-weight-medium);
    }

    .budget-stat-value {
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
    }

    .text-warning {
        color: var(--warning) !important;
    }

    .text-success {
        color: var(--success) !important;
    }

    .budget-check-container {
        padding-top: var(--spacing-4);
        border-top: 1px solid var(--border-color);
    }

    .budget-check-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-3);
        padding: var(--spacing-3);
        background: var(--bg-subtle);
        border-radius: var(--border-radius);
    }

    .budget-check-label {
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-secondary);
    }

    .budget-check-amount {
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-bold);
        color: var(--primary);
    }

    .budget-status-indicator {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
        padding: var(--spacing-4);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
        background: var(--bg-subtle);
    }

    .budget-status-indicator.success {
        background: rgba(var(--success-rgb), 0.1);
        border-color: var(--success);
    }

    .budget-status-indicator.warning {
        background: rgba(var(--warning-rgb), 0.1);
        border-color: var(--warning);
    }

    .budget-status-indicator.danger {
        background: rgba(var(--danger-rgb), 0.1);
        border-color: var(--danger);
    }

    .budget-status-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--border-radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }

    .budget-status-indicator.success .budget-status-icon {
        background: var(--success);
        color: white;
    }

    .budget-status-indicator.warning .budget-status-icon {
        background: var(--warning);
        color: white;
    }

    .budget-status-indicator.danger .budget-status-icon {
        background: var(--danger);
        color: white;
    }

    .budget-status-text {
        flex: 1;
    }

    .budget-status-text strong {
        display: block;
        margin-bottom: var(--spacing-1);
        color: var(--text-primary);
    }

    .budget-status-text p {
        margin: 0;
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .budget-remaining {
        margin-top: var(--spacing-3);
        padding: var(--spacing-3);
        background: var(--bg-subtle);
        border-radius: var(--border-radius);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    /* Form Section Card */
    .form-section-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        margin-bottom: var(--spacing-5);
        overflow: hidden;
    }

    .form-section-header {
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-4);
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
    }

    .form-section-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }

    .form-section-icon.primary {
        background: rgba(var(--primary-rgb), 0.1);
        /*color: var(--primary);*/
    }

    .form-section-icon.success {
        background: rgba(var(--success-rgb), 0.1);
        /*color: var(--success);*/
    }

    .form-section-icon.info {
        background: rgba(var(--info-rgb), 0.1);
        color: var(--info);
    }

    .form-section-title {
        flex: 1;
    }

    .form-section-title h5 {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-1) 0;
    }

    .form-section-title p {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0;
    }

    .form-section-body {
        padding: var(--spacing-5);
    }

    /* Form Controls */
    .form-group {
        margin-bottom: var(--spacing-4);
    }

    .form-label {
        display: block;
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
        margin-bottom: var(--spacing-2);
    }

    .form-label.required::after {
        content: ' *';
        color: var(--danger);
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
    }

    .form-control:focus {
        border-color: var(--bg-hover);
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

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .form-text {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        margin-top: var(--spacing-2);
        display: block;
    }

    /* Items Container */
    .items-container {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-4);
    }

    .item-row {
        display: grid;
        grid-template-columns: 3fr 1fr 1fr 1fr auto;
        gap: var(--spacing-3);
        align-items: start;
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        transition: var(--theme-transition);
    }

    .item-row:hover {
        border-color: var(--bg-hover);
        box-shadow: var(--shadow-sm);
        background:none !important;
    }

    .item-number {
        display: inline-block;
        width: 32px;
        height: 32px;
        line-height: 32px;
        text-align: center;
        background: var(--primary);
        color: white;
        border-radius: var(--border-radius-full);
        font-weight: var(--font-weight-semibold);
        font-size: var(--font-size-sm);
        margin-bottom: var(--spacing-2);
    }

    .item-field {
        display: flex;
        flex-direction: column;
    }

    .remove-item-btn {
        background: var(--danger);
        color: white;
        border: none;
        padding: var(--spacing-2) var(--spacing-3);
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--theme-transition);
        font-size: var(--font-size-sm);
        height: 44px;
        margin-top: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .remove-item-btn:hover {
        background: #dc2626;
        transform: scale(1.05);
    }

    .add-item-btn {
        background: var(--primary);
        color: white;
        border: none;
        padding: var(--spacing-3) var(--spacing-4);
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--theme-transition);
        font-weight: var(--font-weight-medium);
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-2);
        font-size: var(--font-size-sm);
    }

    .add-item-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    /* Total Section */
    .total-section {
        background: var(--bg-subtle);
        padding: var(--spacing-5);
        border-radius: var(--border-radius-lg);
        margin-top: var(--spacing-5);
        border: 2px solid var(--border-color);
    }

    .total-section-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .total-section-label h4 {
        margin: 0 0 var(--spacing-1) 0;
        color: var(--text-primary);
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-semibold);
    }

    .total-section-label p {
        margin: 0;
        color: var(--text-muted);
        font-size: var(--font-size-sm);
    }

    .total-amount {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        color: var(--success);
    }

    /* File Upload Area */
    .file-upload-area {
        border: 2px dashed var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-6);
        text-align: center;
        cursor: pointer;
        transition: var(--theme-transition);
    }

    .file-upload-area:hover {
        border-color: var(--primary);
        background: var(--bg-subtle);
    }

    .file-upload-area.drag-over {
        border-color: var(--primary);
        background: rgba(var(--primary-rgb), 0.1);
    }

    .file-upload-icon {
        font-size: var(--font-size-4xl);
        color: var(--text-muted);
        margin-bottom: var(--spacing-3);
    }

    .file-upload-text {
        margin: 0 0 var(--spacing-2) 0;
        color: var(--text-primary);
        font-weight: var(--font-weight-semibold);
        font-size: var(--font-size-base);
    }

    .file-upload-hint {
        margin: 0;
        color: var(--text-muted);
        font-size: var(--font-size-sm);
    }

    /* Uploaded Files */
    .uploaded-files {
        margin-top: var(--spacing-4);
    }

    .uploaded-file {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-3);
        transition: var(--theme-transition);
    }

    .uploaded-file:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .file-info {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
        flex: 1;
        min-width: 0;
    }

    .file-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--border-radius);
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }

    .file-details {
        flex: 1;
        min-width: 0;
    }

    .file-name {
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-1) 0;
        font-size: var(--font-size-sm);
    }

    .file-meta {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
    }

    .file-actions {
        display: flex;
        gap: var(--spacing-2);
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: var(--spacing-3);
        justify-content: flex-end;
        margin-top: var(--spacing-6);
        padding-top: var(--spacing-5);
        border-top: 1px solid var(--border-color);
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

    .btn-secondary {
        background: var(--bg-subtle);
        color: var(--text-primary);
        border-color: var(--border-color);
    }

    .btn-secondary:hover {
        background: var(--border-color);
    }

    .btn-outline-primary {
        background: transparent;
        color: var(--primary);
        /*border-color: var(--primary);*/
    }

    .btn-outline-primary:hover {
        /*background: var(--primary);*/
        color: white;
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

    /* Utility Classes */
    .d-flex {
        display: flex;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    .align-items-start {
        align-items: flex-start;
    }

    .me-2 {
        margin-right: var(--spacing-2);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .content-header .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: var(--spacing-3);
        }

        .content-actions {
            width: 100%;
        }

        .content-actions .btn {
            width: 100%;
        }

        .budget-stats-row {
            grid-template-columns: 1fr;
        }

        .budget-check-header {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-2);
        }

        .workflow-info-content {
            flex-direction: column;
        }

        .form-section-header {
            padding: var(--spacing-4);
        }

        .form-section-body {
            padding: var(--spacing-4);
        }

        .item-row {
            grid-template-columns: 1fr;
            padding: var(--spacing-3);
        }

        .remove-item-btn {
            margin-top: 0;
            width: 100%;
        }

        .total-section {
            padding: var(--spacing-4);
        }

        .total-section-content {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-3);
        }

        .total-amount {
            font-size: var(--font-size-2xl);
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .form-actions .btn {
            width: 100%;
        }

        .file-upload-area {
            padding: var(--spacing-4);
        }

        .uploaded-file {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-3);
        }

        .file-actions {
            width: 100%;
        }

        .file-actions .btn {
            flex: 1;
        }
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">
                <i class="fas fa-plus-circle"></i>
                <span>Create New Requisition</span>
            </h1>
            <p class="content-description">Submit a new purchase requisition for approval</p>
        </div>
        <div class="content-actions">
            <a href="list.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>
</div>
<!-- Error/Success Messages -->
<?php if (Session::hasFlash('error')): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <div class="alert-content">
            <div class="alert-title">Error</div>
            <div class="alert-message"><?php echo Session::getFlash('error'); ?></div>
        </div>
    </div>
<?php endif; ?>

<?php if (Session::hasFlash('success')): ?>
    <div class="alert alert-success" style="background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.2); color: #22c55e;">
        <i class="fas fa-check-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Success</div>
            <div class="alert-message"><?php echo Session::getFlash('success'); ?></div>
        </div>
    </div>
<?php endif; ?>

<?php if (Session::hasFlash('warning')): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <div class="alert-content">
            <div class="alert-title">Warning</div>
            <div class="alert-message"><?php echo Session::getFlash('warning'); ?></div>
        </div>
    </div>
<?php endif; ?>

<!-- Budget Availability Card (Only shows when category = "Budget") -->
<?php if ($showBudgetCheck && $hasBudget): ?>
<div class="budget-availability-card" id="budgetCard">
    <div class="budget-card-header">
        <div class="budget-card-icon">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="budget-card-title">
            <h5>Department Budget Status</h5>
            <p><?php echo htmlspecialchars($budgetInfo['department_name']); ?></p>
        </div>
    </div>
    
    <div class="budget-card-body">
        <div class="budget-stats-row">
            <div class="budget-stat-item">
                <label>Total Budget</label>
                <div class="budget-stat-value">₦<?php echo number_format((float)$budgetInfo['budget_amount'], 2); ?></div>
            </div>
            <div class="budget-stat-item">
                <label>Allocated</label>
                <div class="budget-stat-value text-warning">₦<?php echo number_format((float)$budgetInfo['allocated_amount'], 2); ?></div>
            </div>
            <div class="budget-stat-item">
                <label>Available</label>
                <div class="budget-stat-value text-success" id="availableBudget">
                    ₦<?php echo number_format((float)$budgetInfo['available_amount'], 2); ?>
                </div>
            </div>
        </div>

        <div class="budget-check-container" id="budgetCheckContainer" style="display: none;">
            <div class="budget-check-header">
                <span class="budget-check-label">Your Requisition Total:</span>
                <span class="budget-check-amount" id="requisitionTotal">₦0.00</span>
            </div>
            
            <div class="budget-status-indicator" id="budgetStatus">
                <div class="budget-status-icon">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="budget-status-text">
                    <strong>Checking budget availability...</strong>
                </div>
            </div>

            <div class="budget-remaining" id="budgetRemaining" style="display: none;">
                <i class="fas fa-info-circle me-2"></i>
                <span>Remaining after submission: <strong id="remainingAmount">₦0.00</strong></span>
            </div>
        </div>
    </div>
</div>

<!-- Budget Check Warning (Hidden by default) -->
<div class="alert alert-danger budget-error-alert" id="budgetErrorAlert" style="display: none;">
    <i class="fas fa-exclamation-triangle"></i>
    <div class="alert-content">
        <div class="alert-title">Insufficient Budget</div>
        <div class="alert-message" id="budgetErrorMessage"></div>
    </div>
</div>

<script>
// Show/hide budget card when category changes
document.addEventListener('DOMContentLoaded', function() {
    const purposeSelect = document.getElementById('purpose');
    const budgetCard = document.getElementById('budgetCard');
    
    if (purposeSelect && budgetCard) {
        purposeSelect.addEventListener('change', function() {
            // Budget card always visible when any category is selected
            if (this.value) {
                budgetCard.style.display = 'block';
            } else {
                budgetCard.style.display = 'none';
            }
        });
        
        // Show on page load if category pre-selected
        if (purposeSelect.value) {
            budgetCard.style.display = 'block';
        }
    }
});
</script>

<?php elseif ($showBudgetCheck && !$hasBudget): ?>
<!-- No Budget Warning -->
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <div class="alert-content">
        <div class="alert-title">No Active Budget</div>
        <div class="alert-message">
            Your department does not have an active budget set. Please contact the Finance Manager to set up a budget.
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Workflow Info Alert -->
<div class="workflow-info-alert">
    <div class="workflow-info-content">
        <i class="fas fa-info-circle workflow-info-icon"></i>
        <div class="workflow-info-text">
            <h5>Approval Workflow</h5>
            <p>Your requisition will be sent to 
            <?php
            $roleId = Session::getUserRoleId();
            if ($roleId == ROLE_TEAM_MEMBER) {
                echo "<strong>your Line Manager</strong>";
            } elseif ($roleId == ROLE_LINE_MANAGER) {
                echo "<strong>the Managing Director</strong>";
            } elseif ($roleId == ROLE_MANAGING_DIRECTOR) {
                echo "<strong>the Finance Manager</strong>";
            }
            ?> for approval.</p>
        </div>
    </div>
</div>

<!-- Requisition Form -->
<form id="requisitionForm" action="save.php" method="POST" enctype="multipart/form-data">
    <?php echo Session::csrfField(); ?>
    <input type="hidden" name="action" value="create">
    <input type="hidden" name="is_draft" id="is_draft" value="0">
    
    <!-- Requisition Details Card -->
    <div class="form-section-card">
        <div class="form-section-header">
            <div class="form-section-icon primary">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="form-section-title">
                <h5>Requisition Details</h5>
                <p>Select the purpose and provide additional details</p>
            </div>
        </div>
        <div class="form-section-body">
            <!-- Purpose/Category Dropdown -->
<!-- Parent Category Dropdown -->
<div class="form-group">
    <label for="parent_category" class="form-label required">Parent Category</label>
    <select 
        id="parent_category" 
        name="parent_category_id" 
        class="form-control" 
        required
    >
        <option value="">-- Select Parent Category --</option>
        <?php foreach ($parentCategories as $parent): ?>
            <option 
                value="<?php echo $parent['id']; ?>" 
                data-category-name="<?php echo htmlspecialchars($parent['category_name']); ?>"
            >
                <?php echo htmlspecialchars($parent['category_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="form-text">Select the main category for this requisition.</div>
</div>

<!-- Child Category Dropdown (Hidden until parent selected) -->
<div class="form-group" id="child_category_wrapper" style="display: none;">
    <label for="child_category" class="form-label required">Child Category</label>
    <select 
        id="child_category" 
        name="category_id" 
        class="form-control"
    >
        <option value="">-- Select Child Category --</option>
    </select>
    <div class="form-text">Select the specific subcategory.</div>
    <span id="child_loading" style="display: none; color: var(--text-muted); font-size: var(--font-size-xs);">
        <i class="fas fa-spinner fa-spin"></i> Loading options...
    </span>
</div>

<!-- Message when parent has no children -->
<div id="no_children_message" style="display: none;"></div>

<!-- Hidden field to store final category name for display -->
<input type="hidden" id="category_name_display" name="purpose" value="">
            
            <!-- Additional Description (Optional) -->
            <div class="form-group">
                <label for="description" class="form-label">Additional Details (Optional)</label>
                <textarea 
                    id="description" 
                    name="description" 
                    class="form-control" 
                    rows="3"
                    placeholder="Add any additional details or notes about this requisition..."
                ><?php echo htmlspecialchars($formData['description']); ?></textarea>
                <div class="form-text">Provide any extra information that might be helpful for approvers.</div>
            </div>
        </div>
    </div>
    
    <!-- Items Section -->
    <div class="form-section-card">
        <div class="form-section-header">
            <div class="form-section-icon success">
                <i class="fas fa-list"></i>
            </div>
            <div class="form-section-title">
                <h5>Items</h5>
                <p>List all items you need to requisition</p>
            </div>
        </div>
        <div class="form-section-body">
            <div id="itemsContainer" class="items-container">
                <div id="itemsTableBody">
                    <!-- Initial item row -->
                    <div class="item-row" data-item-index="0">
                        <div class="item-field">
                            <span class="item-number">1</span>
                            <label class="form-label required">Item Description</label>
                            <input 
                                type="text" 
                                name="items[0][description]" 
                                class="form-control item-description" 
                                placeholder="Enter item description"
                                required
                            >
                        </div>
                        <div class="item-field">
                            <label class="form-label required">Quantity</label>
                            <input 
                                type="number" 
                                name="items[0][quantity]" 
                                class="form-control item-quantity" 
                                min="1" 
                                value="1"
                                required
                            >
                        </div>
                        <div class="item-field">
                            <label class="form-label required">Unit Price (₦)</label>
                            <input 
                                type="number" 
                                name="items[0][unit_price]" 
                                class="form-control item-unit-price" 
                                min="0" 
                                step="0.01"
                                placeholder="0.00"
                                required
                            >
                        </div>
                        <div class="item-field">
                            <label class="form-label">Subtotal</label>
                            <input 
                                type="text" 
                                class="form-control item-subtotal" 
                                readonly 
                                value="₦ 0.00"
                            >
                            <input type="hidden" name="items[0][subtotal]" class="item-subtotal-value" value="0">
                        </div>
                        <div class="item-field">
                            <button type="button" class="remove-item-btn" style="display: none;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: var(--spacing-4);">
                <button type="button" id="addItemBtn" class="add-item-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Another Item</span>
                </button>
            </div>
            
            <!-- Total Section -->
            <div class="total-section">
                <div class="total-section-content">
                    <div class="total-section-label">
                        <h4>Total Amount</h4>
                        <p>Sum of all items</p>
                    </div>
                    <div class="total-amount" id="grandTotal">
                        ₦0.00
                    </div>
                </div>
                <input type="hidden" name="total_amount" id="totalAmount" value="0">
            </div>
        </div>
    </div>
    
    <!-- Attachments Section -->
    <div class="form-section-card">
        <div class="form-section-header">
            <div class="form-section-icon info">
                <i class="fas fa-paperclip"></i>
            </div>
            <div class="form-section-title">
                <h5>Supporting Documents (Optional)</h5>
                <p>Upload any relevant files to support your requisition</p>
            </div>
        </div>
        <div class="form-section-body">
            <div class="file-upload-area" id="fileUploadArea">
                <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                <p class="file-upload-text">
                    Drag & drop files here or click to browse
                </p>
                <p class="file-upload-hint">
                    Supported formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max <?php echo format_file_size(UPLOAD_MAX_SIZE); ?>)
                </p>
                <input type="file" id="fileInput" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif" style="display: none;">
            </div>
            
            <div id="uploadedFiles" class="uploaded-files" style="display: none;">
                <!-- Uploaded files will appear here -->
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="form-actions">
        <button type="button" class="btn btn-secondary" onclick="window.location.href='list.php'">
            <i class="fas fa-times"></i>
            <span>Cancel</span>
        </button>
        <button type="submit" name="save_draft" class="btn btn-outline-primary" onclick="document.getElementById('is_draft').value='1'">
            <i class="fas fa-save"></i>
            <span>Save as Draft</span>
        </button>
        <button type="submit" name="submit" id="submitRequisitionBtn" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i>
            <span>Submit for Approval</span>
        </button>
    </div>
</form>

<!-- JavaScript for dynamic items -->
<script src="<?php echo BASE_URL; ?>/assets/js/requisition.js"></script>
<!-- Category Cascade JavaScript -->
<script>
// Define BASE_URL for category cascade script
const BASE_URL = '<?php echo BASE_URL; ?>';
</script>
<script src="<?php echo BASE_URL; ?>/assets/js/category-cascade.js"></script>

<?php if ($showBudgetCheck && $hasBudget): ?>
<!-- Budget Check JavaScript -->
<script>
// Pass budget data to JavaScript
window.BUDGET_CONFIG = {
    departmentId: <?php echo $userDepartmentId; ?>,
    hasBudget: <?php echo $hasBudget ? 'true' : 'false'; ?>,
    availableAmount: <?php echo (float)$budgetInfo['available_amount']; ?>,
    checkUrl: '<?php echo BASE_URL; ?>api/check-budget.php'
};
</script>
<script src="<?php echo BASE_URL; ?>assets/js/requisition-budget.js"></script>
<?php endif; ?>

<script>
// Sync category name when dropdown changes
document.getElementById('purpose').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const categoryName = selectedOption.getAttribute('data-category-name') || '';
    document.getElementById('category_name').value = categoryName;
    
    // Update data attribute for budget checking
    this.setAttribute('data-category-name', categoryName);
});

// Set initial value if form has data
document.addEventListener('DOMContentLoaded', function() {
    const purposeSelect = document.getElementById('purpose');
    if (purposeSelect.value) {
        const selectedOption = purposeSelect.options[purposeSelect.selectedIndex];
        const categoryName = selectedOption.getAttribute('data-category-name') || '';
        document.getElementById('category_name').value = categoryName;
        purposeSelect.setAttribute('data-category-name', categoryName);
    }
});

// Add confirmation for navigation
window.addEventListener('beforeunload', function (e) {
    const form = document.getElementById('requisitionForm');
    if (form && form.dataset.changed === 'true') {
        e.preventDefault();
        e.returnValue = '';
    }
});

// Track form changes
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('requisitionForm');
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            form.dataset.changed = 'true';
        });
    });
    
    // Remove change tracking on submit
    form.addEventListener('submit', function() {
        form.dataset.changed = 'false';
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
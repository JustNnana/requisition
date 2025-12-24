<?php

/**
 * GateWey Requisition Management System
 * Edit Requisition Page - Dasher UI Enhanced (Fully Recoded)
 * 
 * File: requisitions/edit.php
 * Purpose: Edit draft or rejected requisitions with budget checking
 * 
 * UPDATED: Added budget checking functionality for all categories
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

// Get requisition ID (decrypt from URL)
$requisitionId = get_encrypted_id();

if ($requisitionId === false || $requisitionId <= 0) {
    Session::setFlash('error', 'Invalid requisition ID.');
    header('Location: list.php');
    exit;
}

// Initialize objects
$requisition = new Requisition();
$categoryModel = new RequisitionCategory();

// Get requisition data FIRST
$reqData = $requisition->getById($requisitionId);

if (!$reqData) {
    Session::setFlash('error', 'Requisition not found.');
    header('Location: list.php');
    exit;
}

// Check if user can edit
if (!can_user_edit_requisition($reqData)) {
    Session::setFlash('error', 'You cannot edit this requisition.');
    header('Location: ' . build_encrypted_url('view.php', $requisitionId));
    exit;
}

// Load active parent categories
$parentCategories = $categoryModel->getParentCategories(true);

// Get parent and child info for pre-selection
$currentCategoryId = $reqData['category_id'] ?? null;
$selectedParentId = null;
$selectedChildId = null;

if ($currentCategoryId) {
    $currentCategory = $categoryModel->getById($currentCategoryId);
    if ($currentCategory) {
        if ($currentCategory['parent_id']) {
            // Current category is a child
            $selectedParentId = $currentCategory['parent_id'];
            $selectedChildId = $currentCategory['id'];
        } else {
            // Current category is a parent (no children system before)
            $selectedParentId = $currentCategory['id'];
            // Child will be empty
        }
    }
}

// ============================================
// BUDGET CHECK FUNCTIONALITY
// ============================================
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
// ============================================
// END BUDGET CHECK FUNCTIONALITY
// ============================================

// Load available approvers for dropdown
$userModel = new User();
$userId = Session::getUserId();
$availableApprovers = $userModel->getAvailableApprovers($userId, $userDepartmentId);

// Page title
$pageTitle = 'Edit Requisition ' . $reqData['requisition_number'];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Dasher UI Enhanced Styles -->
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
        outline: none !important;
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
                <i class="fas fa-edit me-2"></i>
                Edit Requisition <?php echo htmlspecialchars($reqData['requisition_number']); ?>
            </h1>
            <p class="content-description">Update your requisition details and resubmit for approval</p>
        </div>
        <div class="content-actions">
            <a href="view.php?id=<?php echo $requisitionId; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                <span>Back to View</span>
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
<!-- Rejection Alert -->
<?php if ($reqData['status'] == STATUS_REJECTED && $reqData['rejection_reason']): ?>
    <div class="rejection-alert">
        <div class="rejection-alert-content">
            <i class="fas fa-exclamation-triangle rejection-alert-icon"></i>
            <div class="rejection-alert-text">
                <h5>Rejection Reason</h5>
                <p><?php echo htmlspecialchars($reqData['rejection_reason']); ?></p>
                <?php if ($reqData['rejected_by_first_name']): ?>
                    <div class="rejection-metadata">
                        Rejected by <?php echo htmlspecialchars($reqData['rejected_by_first_name'] . ' ' . $reqData['rejected_by_last_name']); ?>
                        on <?php echo format_date($reqData['rejected_at'], 'M d, Y \a\t h:i A'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Budget Availability Card -->
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

<!-- Requisition Form -->
<form id="requisitionForm" action="save.php" method="POST" enctype="multipart/form-data">
    <?php echo Session::csrfField(); ?>
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="requisition_id" value="<?php echo $requisitionId; ?>">
    <input type="hidden" name="is_draft" id="is_draft" value="0">

    <!-- Requisition Details Card -->
    <div class="form-section-card">
        <div class="form-section-header">
            <div class="form-section-icon primary">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="form-section-title">
                <h5>Requisition Details</h5>
                <p>Provide a clear description of what you need</p>
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
                <?php echo ($selectedParentId == $parent['id']) ? 'selected' : ''; ?>
            >
                <?php echo htmlspecialchars($parent['category_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="form-text">Select the main category for this requisition.</div>
</div>

<!-- Child Category Dropdown -->
<div class="form-group" id="child_category_wrapper" style="<?php echo $selectedParentId ? 'display: block;' : 'display: none;'; ?>">
    <label for="child_category" class="form-label required">Subcategory</label>
    <select 
        id="child_category" 
        name="category_id" 
        class="form-control"
        <?php if ($selectedChildId): ?>data-preselected="<?php echo $selectedChildId; ?>"<?php endif; ?>
    >
        <option value="">-- Select Subcategory --</option>
    </select>
    <div class="form-text">Select the specific subcategory.</div>
    <span id="child_loading" style="display: none; color: var(--text-muted); font-size: var(--font-size-xs);">
        <i class="fas fa-spinner fa-spin"></i> Loading options...
    </span>
</div>

<div id="no_children_message" style="display: none;"></div>
<input type="hidden" id="category_name_display" name="purpose" value="<?php echo htmlspecialchars($reqData['purpose']); ?>">

            <!-- Additional Description (Optional) -->
            <div class="form-group">
                <label for="description" class="form-label">Additional Details (Optional)</label>
                <textarea
                    id="description"
                    name="description"
                    class="form-control"
                    rows="3"
                    placeholder="Add any additional details or notes about this requisition..."><?php echo htmlspecialchars($reqData['description'] ?? ''); ?></textarea>
                <div class="form-text">Provide any extra information that might be helpful for approvers.</div>
            </div>

            <!-- Select Approver Dropdown -->
            <div class="form-group">
                <label for="selected_approver" class="form-label required">Select Approver</label>
                <select
                    id="selected_approver"
                    name="selected_approver_id"
                    class="form-control"
                    required
                >
                    <option value="">-- Select who should approve this request --</option>

                    <?php if (!empty($availableApprovers['line_managers'])): ?>
                        <optgroup label="Line Managers (Your Department)">
                            <?php foreach ($availableApprovers['line_managers'] as $approver): ?>
                                <option value="<?php echo $approver['id']; ?>"
                                    <?php echo ($reqData['selected_approver_id'] == $approver['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($approver['first_name'] . ' ' . $approver['last_name']); ?>
                                    (<?php echo htmlspecialchars($approver['role_name']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>

                    <?php if (!empty($availableApprovers['executive'])): ?>
                        <optgroup label="Executive Department">
                            <?php foreach ($availableApprovers['executive'] as $approver): ?>
                                <option value="<?php echo $approver['id']; ?>"
                                    <?php echo ($reqData['selected_approver_id'] == $approver['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($approver['first_name'] . ' ' . $approver['last_name']); ?>
                                    (<?php echo htmlspecialchars($approver['role_name']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>

                    <?php if (!empty($availableApprovers['finance_managers'])): ?>
                        <optgroup label="Finance Managers">
                            <?php foreach ($availableApprovers['finance_managers'] as $approver): ?>
                                <option value="<?php echo $approver['id']; ?>"
                                    <?php echo ($reqData['selected_approver_id'] == $approver['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($approver['first_name'] . ' ' . $approver['last_name']); ?>
                                    (<?php echo htmlspecialchars($approver['role_name']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
                <div class="form-text">
                    <i class="fas fa-info-circle"></i>
                    Choose who will review and approve this requisition first. After their approval, it will proceed to Finance Manager, then Finance Team for payment.
                </div>
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
                <!-- Existing items -->
                <?php foreach ($reqData['items'] as $index => $item): ?>
                    <div class="item-row" data-item-index="<?php echo $index; ?>">
                        <div class="item-field">
                            <span class="item-number"><?php echo $index + 1; ?></span>
                            <label class="form-label required">Item Description</label>
                            <input
                                type="text"
                                name="items[<?php echo $index; ?>][description]"
                                class="form-control item-description"
                                placeholder="Enter item description"
                                value="<?php echo htmlspecialchars($item['item_description']); ?>"
                                required>
                        </div>
                        <div class="item-field">
                            <label class="form-label required">Quantity</label>
                            <input
                                type="number"
                                name="items[<?php echo $index; ?>][quantity]"
                                class="form-control item-quantity"
                                min="1"
                                value="<?php echo $item['quantity']; ?>"
                                required>
                        </div>
                        <div class="item-field">
                            <label class="form-label required">Unit Price (<?php echo CURRENCY_SYMBOL; ?>)</label>
                            <input
                                type="number"
                                name="items[<?php echo $index; ?>][unit_price]"
                                class="form-control item-unit-price"
                                min="0"
                                step="0.01"
                                value="<?php echo $item['unit_price']; ?>"
                                required>
                        </div>
                        <div class="item-field">
                            <label class="form-label">Subtotal</label>
                            <input
                                type="text"
                                class="form-control item-subtotal"
                                readonly
                                value="<?php echo '₦' . number_format($item['subtotal'], 2); ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][subtotal]" class="item-subtotal-value" value="<?php echo $item['subtotal']; ?>">
                        </div>
                        <div class="item-field">
                            <button type="button" class="remove-item-btn" <?php echo count($reqData['items']) <= 1 ? 'style="display:none;"' : ''; ?>>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: var(--spacing-4);">
                <button type="button" id="addItemBtn" class="add-item-btn">
                    <i class="fas fa-plus-circle"></i> Add Another Item
                </button>
            </div>

            <!-- Total Section -->
            <div class="total-section">
                <div class="total-section-content">
                    <div class="total-section-label">
                        <h4>Total Amount</h4>
                        <p>Sum of all items</p>
                    </div>
                    <div class="total-amount" id="grandTotal">₦<?php echo number_format((float)$reqData['total_amount'], 2); ?>
                    </div>
                </div>
                <input type="hidden" name="total_amount" id="total_amount" value="<?php echo $reqData['total_amount']; ?>">
            </div>
        </div>
    </div>

    <!-- Existing Attachments -->
    <?php if (!empty($reqData['documents'])): ?>
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="form-section-icon info">
                    <i class="fas fa-paperclip"></i>
                </div>
                <div class="form-section-title">
                    <h5>Existing Attachments</h5>
                    <p>Files currently attached to this requisition</p>
                </div>
            </div>
            <div class="form-section-body">
                <?php foreach ($reqData['documents'] as $doc): ?>
                    <div class="uploaded-file">
                        <div class="file-info">
                            <div class="file-icon">
                                <i class="fas <?php echo FileUpload::getFileIcon($doc['file_name']); ?>"></i>
                            </div>
                            <div class="file-details">
                                <div class="file-name">
                                    <?php echo htmlspecialchars($doc['file_name']); ?>
                                </div>
                                <div class="file-meta">
                                    <?php echo format_file_size($doc['file_size']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="file-actions">
                            <a href="../api/download-file.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-ghost" target="_blank">
                                <i class="fas fa-eye me-1"></i>View
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- New Attachments -->
    <div class="form-section-card">
        <div class="form-section-header">
            <div class="form-section-icon info">
                <i class="fas fa-plus-circle"></i>
            </div>
            <div class="form-section-title">
                <h5>Add More Attachments (Optional)</h5>
                <p>Upload additional supporting documents</p>
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
                <!-- New uploaded files will appear here -->
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
        <button type="button" class="btn btn-secondary" onclick="window.location.href='view.php?id=<?php echo $requisitionId; ?>'">
            <i class="fas fa-times me-2"></i>Cancel
        </button>
        <button type="submit" name="save_draft" class="btn btn-outline-primary" onclick="document.getElementById('is_draft').value='1'">
            <i class="fas fa-save me-2"></i>Save as Draft
        </button>
        <button type="submit" name="submit" id="submitRequisitionBtn" class="btn btn-primary">
            <i class="fas fa-paper-plane me-2"></i>Resubmit for Approval
        </button>
    </div>
</form>
</div>

<!-- JavaScript for dynamic items -->
<script src="<?php echo BASE_URL; ?>/assets/js/requisition.js"></script>
<!-- Category Cascade JavaScript -->
<script>
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
// Set the starting item index for new items
window.itemIndex = <?php echo count($reqData['items']); ?>;

// Calculate initial total
if (typeof calculateGrandTotal === 'function') {
    calculateGrandTotal();
}

// Sync the hidden purpose field when category dropdown changes
// document.addEventListener('DOMContentLoaded', function() {
//     const categorySelect = document.getElementById('category_id');
//     const purposeHidden = document.getElementById('purpose');
    
//     if (categorySelect && purposeHidden) {
//         categorySelect.addEventListener('change', function() {
//             const selectedOption = this.options[this.selectedIndex];
//             if (selectedOption && selectedOption.value) {
//                 purposeHidden.value = selectedOption.getAttribute('data-name') || selectedOption.text;
//             } else {
//                 purposeHidden.value = '';
//             }
//         });
        
//         // Initialize purpose on page load
//         if (categorySelect.value) {
//             const selectedOption = categorySelect.options[categorySelect.selectedIndex];
//             purposeHidden.value = selectedOption.getAttribute('data-name') || selectedOption.text;
//         }
//     }
// });

// Add confirmation for navigation
window.addEventListener('beforeunload', function(e) {
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
<?php
/**
 * GateWey Requisition Management System
 * Budget Management - Edit Budget
 * 
 * File: finance/budget/edit-budget.php
 * Purpose: Form to edit existing department budget
 */

define('APP_ACCESS', true);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../helpers/permissions.php';

Session::start();
require_once __DIR__ . '/../../middleware/auth-check.php';
require_once __DIR__ . '/../../middleware/role-check.php';

// Only Finance Manager can access
checkRole(ROLE_FINANCE_MANAGER);

$budgetModel = new Budget();
$budgetId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$budgetId) {
    Session::setFlash('error', 'Invalid budget ID.');
    header('Location: index.php');
    exit;
}

// Get budget data with statistics
// First, get basic budget info to get department_id
$db = Database::getInstance();
$sql = "SELECT department_id FROM department_budgets WHERE id = ?";
$basicBudget = $db->fetchOne($sql, [$budgetId]);

if (!$basicBudget) {
    Session::setFlash('error', 'Budget not found.');
    header('Location: index.php');
    exit;
}

// Now get full budget stats with department info
$budgetData = $budgetModel->getBudgetStats($basicBudget['department_id'], $budgetId);
if (!$budgetData) {
    Session::setFlash('error', 'Budget not found.');
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';
$formData = $budgetData;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $formData = [
            'budget_amount' => (float)str_replace(',', '', $_POST['budget_amount']),
            'duration_type' => Sanitizer::string($_POST['duration_type']),
            'start_date' => Sanitizer::string($_POST['start_date']),
            'end_date' => Sanitizer::string($_POST['end_date'])
        ];
        
        $validator = new Validator();
        $validator->setData($formData);
        $validator->setRules([
            'budget_amount' => 'required|numeric',
            'duration_type' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);
        
        if (!$validator->validate()) {
            $errors = array_column($validator->getErrors(), 0);
        } else {
            // Update the budget
            $result = $budgetModel->updateBudget(
                $budgetId,
                $formData['budget_amount'],
                $formData['duration_type'],
                $formData['start_date'],
                $formData['end_date'],
                Session::getUserId()
            );
            
            if ($result['success']) {
                Session::setFlash('success', $result['message']);
                header('Location: view-budget.php?id=' . $budgetId);
                exit;
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$pageTitle = 'Edit Budget';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<style>
    /* Form Container & Layout */
    .form-container {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: var(--spacing-6);
        max-width: 1200px;
        margin: 0 auto;
    }

    @media (max-width: 992px) {
        .form-container {
            grid-template-columns: 1fr;
        }
    }

    /* Form Card */
    .form-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        transition: var(--theme-transition);
    }

    .form-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    /* Form Header */
    .form-header {
        padding: var(--spacing-6);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: var(--spacing-4);
    }

    .form-icon {
        width: 48px;
        height: 48px;
        background: var(--primary);
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }

    .form-header-content {
        flex: 1;
    }

    .form-title {
        margin: 0 0 var(--spacing-1) 0;
        font-size: var(--font-size-2xl);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .form-subtitle {
        margin: 0;
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
    }

    /* Form Body */
    .form-body {
        padding: var(--spacing-6);
    }

    /* Form Sections */
    .form-section {
        margin-bottom: var(--spacing-8);
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .form-section-header {
        margin-bottom: var(--spacing-5);
        padding-bottom: var(--spacing-3);
        border-bottom: 1px solid var(--border-color);
    }

    .form-section-title {
        margin: 0 0 var(--spacing-1) 0;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .form-section-subtitle {
        margin: 0;
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
    }

    /* Form Rows */
    .form-row {
        margin-bottom: var(--spacing-5);
    }

    .form-row-2-cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-4);
    }

    @media (max-width: 768px) {
        .form-row-2-cols {
            grid-template-columns: 1fr;
        }
    }

    /* Form Groups */
    .form-group {
        position: relative;
    }

    .form-label {
        display: block;
        margin-bottom: var(--spacing-2);
        font-weight: var(--font-weight-medium);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
    }

    .form-label.required::after {
        content: ' *';
        color: var(--danger);
    }

    .form-control {
        display: block;
        width: 100%;
        padding: var(--spacing-3) var(--spacing-4);
        font-size: var(--font-size-base);
        background: var(--bg-input);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        color: var(--text-primary);
        transition: var(--theme-transition);
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
        outline: none;
    }

    .form-control:disabled {
        background: var(--bg-subtle);
        cursor: not-allowed;
        opacity: 0.6;
    }

    .form-control::placeholder {
        color: var(--text-muted);
    }

    .form-text {
        margin-top: var(--spacing-2);
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
    }

    .form-text.text-warning {
        color: var(--warning);
        font-weight: var(--font-weight-medium);
    }

    /* Radio Button Styling */
    .radio-group {
        display: flex;
        gap: var(--spacing-3);
        flex-wrap: wrap;
    }

    .radio-wrapper {
        flex: 1;
        min-width: 150px;
    }

    .form-radio {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .radio-label {
        display: flex;
        flex-direction: column;
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border: 2px solid var(--border-color);
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--theme-transition);
        text-align: center;
    }

    .radio-label:hover {
        border-color: var(--primary);
        background: rgba(var(--primary-rgb), 0.05);
    }

    .form-radio:checked + .radio-label {
        background: rgba(var(--primary-rgb), 0.1);
        border-color: var(--primary);
        border-width: 2px;
    }

    .radio-icon {
        font-size: var(--font-size-2xl);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-2);
    }

    .form-radio:checked + .radio-label .radio-icon {
        color: var(--primary);
    }

    .radio-title {
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-1);
    }

    .radio-description {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
    }

    /* Form Actions */
    .form-actions {
        margin-top: var(--spacing-8);
        padding-top: var(--spacing-6);
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: var(--spacing-3);
        justify-content: flex-end;
    }

    @media (max-width: 576px) {
        .form-actions {
            flex-direction: column;
        }
    }

    /* Stats Card */
    .stats-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        height: fit-content;
        position: sticky;
        top: var(--spacing-4);
        margin-bottom: var(--spacing-4);
    }

    .stats-header {
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .stats-icon {
        color: var(--info);
        font-size: var(--font-size-lg);
    }

    .stats-title {
        margin: 0;
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .stats-content {
        padding: var(--spacing-5);
    }

    .stat-item {
        padding: var(--spacing-4) 0;
        border-bottom: 1px solid var(--border-color);
    }

    .stat-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .stat-item:first-child {
        padding-top: 0;
    }

    .stat-label {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-3);
        font-weight: var(--font-weight-medium);
    }

    .stat-label i {
        font-size: var(--font-size-sm);
        opacity: 0.7;
    }

    .stat-value {
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
    }

    .stat-subtext {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        margin-top: var(--spacing-1);
    }

    /* Warning Banner */
    .warning-banner {
        display: flex;
        align-items: flex-start;
        padding: var(--spacing-4);
        background: rgba(var(--warning-rgb), 0.1);
        border: 1px solid rgba(var(--warning-rgb), 0.2);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-4);
        gap: var(--spacing-3);
    }

    .warning-banner i {
        color: var(--warning);
        font-size: var(--font-size-lg);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .warning-banner-content {
        flex: 1;
        color: var(--warning);
    }

    .warning-banner-title {
        font-weight: var(--font-weight-semibold);
        margin-bottom: var(--spacing-1);
    }

    .warning-banner-message {
        font-size: var(--font-size-sm);
        line-height: 1.5;
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

    .alert-danger {
        background: rgba(var(--danger-rgb), 0.1);
        border-color: rgba(var(--danger-rgb), 0.2);
        color: var(--danger);
    }

    .alert-success {
        background: rgba(var(--success-rgb), 0.1);
        border-color: rgba(var(--success-rgb), 0.2);
        color: var(--success);
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
        line-height: 1.5;
    }

    .alert-message ul {
        margin: 0;
        padding-left: var(--spacing-4);
    }

    .alert-message li {
        margin-bottom: var(--spacing-1);
    }

    /* Breadcrumb */
    .content-breadcrumb {
        margin-top: var(--spacing-2);
    }

    .breadcrumb {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: var(--spacing-1);
        align-items: center;
    }

    .breadcrumb-item {
        display: flex;
        align-items: center;
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .breadcrumb-item a {
        color: var(--primary);
        text-decoration: none;
        transition: var(--theme-transition);
    }

    .breadcrumb-item a:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    .breadcrumb-item.active {
        color: var(--text-primary);
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "/";
        margin: 0 var(--spacing-2);
        color: var(--text-muted);
    }

    /* Content Header */
    .content-header {
        margin-bottom: var(--spacing-6);
    }

    .content-actions {
        display: flex;
        gap: var(--spacing-3);
        align-items: center;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-2);
        padding: var(--spacing-1) var(--spacing-3);
        border-radius: var(--border-radius-full);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
    }

    .status-badge.status-active {
        background: rgba(var(--success-rgb), 0.1);
        color: var(--success);
    }

    .status-badge.status-expired {
        background: rgba(var(--danger-rgb), 0.1);
        color: var(--danger);
    }

    .status-badge.status-upcoming {
        background: rgba(var(--info-rgb), 0.1);
        color: var(--info);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-header {
            flex-direction: column;
            text-align: center;
        }

        .form-icon {
            margin: 0 auto;
        }

        .stats-card {
            position: static;
        }

        .radio-group {
            flex-direction: column;
        }

        .radio-wrapper {
            min-width: 100%;
        }

        .content-header {
            flex-direction: column !important;
        }

        .content-actions {
            width: 100%;
        }
    }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">Edit Budget</h1>
            <!--<nav class="content-breadcrumb">-->
            <!--    <ol class="breadcrumb">-->
            <!--        <li class="breadcrumb-item">-->
            <!--            <a href="../../dashboard/">Dashboard</a>-->
            <!--        </li>-->
            <!--        <li class="breadcrumb-item">-->
            <!--            <a href="../">Finance</a>-->
            <!--        </li>-->
            <!--        <li class="breadcrumb-item">-->
            <!--            <a href="index.php">Budget Management</a>-->
            <!--        </li>-->
            <!--        <li class="breadcrumb-item active">Edit Budget</li>-->
            <!--    </ol>-->
            <!--</nav>-->
        </div>
        <div class="content-actions">
            <a href="view-budget.php?id=<?php echo $budgetId; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Details</span>
            </a>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Error</div>
            <div class="alert-message">
                <strong>Please correct the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success" role="alert">
        <i class="fas fa-check-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Success!</div>
            <div class="alert-message"><?php echo htmlspecialchars($success); ?></div>
        </div>
    </div>
<?php endif; ?>

<!-- Warning Banner (if budget has allocations) -->
<?php if ($budgetData['allocated_amount'] > 0): ?>
    <div class="warning-banner">
        <i class="fas fa-exclamation-triangle"></i>
        <div class="warning-banner-content">
            <div class="warning-banner-title">Budget Has Active Allocations</div>
            <div class="warning-banner-message">
                This budget has ₦<?php echo number_format($budgetData['allocated_amount'], 2); ?> currently allocated to <?php echo $budgetData['active_allocations']; ?> requisition(s).
                You cannot decrease the budget below the allocated amount.
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Edit Budget Form -->
<div class="form-container">
    <div class="form-card">
        <div class="form-header">
            <div class="form-icon">
                <i class="fas fa-edit"></i>
            </div>
            <div class="form-header-content">
                <h2 class="form-title">Budget Information</h2>
                <p class="form-subtitle">Update budget details and settings</p>
            </div>
        </div>

        <div class="form-body">
            <form method="POST" action="" class="enhanced-form" id="budget-form">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                
                <!-- Department (Read-only) -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">Department</h3>
                        <p class="form-section-subtitle">Department cannot be changed for existing budgets</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Department</label>
                            <input type="text" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($budgetData['department_name']); ?> (<?php echo htmlspecialchars($budgetData['department_code']); ?>)"
                                   disabled>
                            <div class="form-text">Cannot be modified for existing budgets</div>
                        </div>
                    </div>
                </div>

                <!-- Budget Amount -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">Budget Amount</h3>
                        <p class="form-section-subtitle">Adjust the total budget allocation</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="budget_amount" class="form-label required">Budget Amount (₦)</label>
                            <input type="text" 
                                   id="budget_amount" 
                                   name="budget_amount" 
                                   class="form-control" 
                                   value="<?php echo number_format($formData['budget_amount'], 2); ?>"
                                   placeholder="Enter budget amount"
                                   required>
                            <?php if ($budgetData['allocated_amount'] > 0): ?>
                                <div class="form-text text-warning">
                                    ⚠️ Minimum allowed: ₦<?php echo number_format($budgetData['allocated_amount'], 2); ?> (currently allocated)
                                </div>
                            <?php else: ?>
                                <div class="form-text">Total budget available for this period</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Duration Type -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">Budget Duration</h3>
                        <p class="form-section-subtitle">Budget period type</p>
                    </div>

                    <div class="form-row">
                        <div class="radio-group">
                            <div class="radio-wrapper">
                                <input type="radio" 
                                       id="duration_quarterly" 
                                       name="duration_type" 
                                       value="quarterly" 
                                       class="form-radio"
                                       <?php echo $formData['duration_type'] === 'quarterly' ? 'checked' : ''; ?>>
                                <label class="radio-label" for="duration_quarterly">
                                    <div class="radio-icon">
                                        <i class="fas fa-calendar-week"></i>
                                    </div>
                                    <div class="radio-title">Quarterly</div>
                                    <div class="radio-description">3 months</div>
                                </label>
                            </div>

                            <div class="radio-wrapper">
                                <input type="radio" 
                                       id="duration_yearly" 
                                       name="duration_type" 
                                       value="yearly" 
                                       class="form-radio"
                                       <?php echo $formData['duration_type'] === 'yearly' ? 'checked' : ''; ?>>
                                <label class="radio-label" for="duration_yearly">
                                    <div class="radio-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="radio-title">Yearly</div>
                                    <div class="radio-description">12 months</div>
                                </label>
                            </div>

                            <div class="radio-wrapper">
                                <input type="radio" 
                                       id="duration_custom" 
                                       name="duration_type" 
                                       value="custom" 
                                       class="form-radio"
                                       <?php echo $formData['duration_type'] === 'custom' ? 'checked' : ''; ?>>
                                <label class="radio-label" for="duration_custom">
                                    <div class="radio-icon">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div class="radio-title">Custom</div>
                                    <div class="radio-description">Choose dates</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date Range -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">Budget Period</h3>
                        <p class="form-section-subtitle">Adjust start and end dates</p>
                    </div>

                    <div class="form-row form-row-2-cols">
                        <div class="form-group">
                            <label for="start_date" class="form-label required">Start Date</label>
                            <input type="date" 
                                   id="start_date" 
                                   name="start_date" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['start_date']); ?>"
                                   <?php echo $budgetData['status'] === 'active' || $budgetData['status'] === 'expired' ? 'disabled' : ''; ?>
                                   required>
                            <?php if ($budgetData['status'] === 'active' || $budgetData['status'] === 'expired'): ?>
                                <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($formData['start_date']); ?>">
                                <div class="form-text text-warning">
                                    ⚠️ Cannot change start date of <?php echo $budgetData['status']; ?> budget
                                </div>
                            <?php else: ?>
                                <div class="form-text">Budget becomes active on this date</div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="end_date" class="form-label required">End Date</label>
                            <input type="date" 
                                   id="end_date" 
                                   name="end_date" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['end_date']); ?>"
                                   min="<?php echo date('Y-m-d'); ?>"
                                   required>
                            <div class="form-text">Budget expires on this date (cannot be in the past)</div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i>
                        <span>Update Budget</span>
                    </button>
                    <a href="view-budget.php?id=<?php echo $budgetId; ?>" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Sidebar -->
    <div>
        <!-- Current Status Card -->
        <div class="stats-card">
            <div class="stats-header">
                <i class="fas fa-info-circle stats-icon"></i>
                <h3 class="stats-title">Current Status</h3>
            </div>
            <div class="stats-content">
                <div class="stat-item">
                    <span class="stat-label">
                        <i class="fas fa-toggle-on"></i> Status
                    </span>
                    <div>
                        <span class="status-badge status-<?php echo $budgetData['status']; ?>">
                            <i class="fas fa-circle"></i>
                            <?php echo ucfirst($budgetData['status']); ?>
                        </span>
                    </div>
                </div>

                <div class="stat-item">
                    <span class="stat-label">
                        <i class="fas fa-wallet"></i> Allocated Amount
                    </span>
                    <div class="stat-value">₦<?php echo number_format($budgetData['allocated_amount'], 2); ?></div>
                    <div class="stat-subtext"><?php echo $budgetData['active_allocations']; ?> active requisition(s)</div>
                </div>

                <div class="stat-item">
                    <span class="stat-label">
                        <i class="fas fa-chart-pie"></i> Utilization
                    </span>
                    <div class="stat-value"><?php echo number_format($budgetData['utilization_percentage'], 1); ?>%</div>
                    <div class="stat-subtext"><?php echo $budgetData['total_allocations']; ?> total allocation(s)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎨 Initializing Edit Budget Form...');

    const form = document.getElementById('budget-form');
    const budgetAmountInput = document.getElementById('budget_amount');
    const minAmount = <?php echo $budgetData['allocated_amount']; ?>;

    // Format currency on budget amount field
    if (budgetAmountInput) {
        budgetAmountInput.addEventListener('input', function(e) {
            let value = this.value.replace(/,/g, '');
            if (!isNaN(value) && value !== '') {
                this.value = parseFloat(value).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        });

        // Validate minimum amount
        budgetAmountInput.addEventListener('blur', function() {
            let value = parseFloat(this.value.replace(/,/g, ''));
            if (value < minAmount) {
                alert('Budget amount cannot be less than allocated amount (₦' + minAmount.toLocaleString('en-US', {minimumFractionDigits: 2}) + ')');
                this.value = minAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                this.focus();
            }
        });

        // Remove commas before submission
        form.addEventListener('submit', function() {
            budgetAmountInput.value = budgetAmountInput.value.replace(/,/g, '');
        });
    }

    // Form submission enhancement
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Updating Budget...</span>';
        });
    }

    console.log('✅ Edit Budget Form initialized successfully');
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
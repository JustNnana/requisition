<?php
/**
 * GateWey Requisition Management System
 * Budget Management - Set New Budget
 * 
 * File: finance/budget/set-budget.php
 * Purpose: Form to set new department budget
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

$budget = new Budget();
$departmentModel = new Department();

// Get all active departments
$departments = $departmentModel->getAll(['is_active' => 1]);

// Check if pre-populating for specific department
$preDepartmentId = isset($_GET['department']) ? (int)$_GET['department'] : 0;
$previousBudget = null;

if ($preDepartmentId) {
    // Get last budget for this department
    $lastBudget = $budget->canSetBudget($preDepartmentId);
    if (isset($lastBudget['last_budget'])) {
        $previousBudget = $lastBudget['last_budget'];
    }
}

$errors = [];
$success = '';
$formData = [
    'department_id' => $preDepartmentId,
    'budget_amount' => $previousBudget['budget_amount'] ?? '',
    'duration_type' => 'quarterly',
    'start_date' => '',
    'end_date' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $formData = [
            'department_id' => (int)$_POST['department_id'],
            'budget_amount' => (float)str_replace(',', '', $_POST['budget_amount']),
            'duration_type' => Sanitizer::string($_POST['duration_type']),
            'start_date' => Sanitizer::string($_POST['start_date']),
            'end_date' => Sanitizer::string($_POST['end_date'])
        ];
        
        $validator = new Validator();
        $validator->setData($formData);
        $validator->setRules([
            'department_id' => 'required|numeric',
            'budget_amount' => 'required|numeric',
            'duration_type' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);
        
        if (!$validator->validate()) {
            $errors = array_column($validator->getErrors(), 0);
        } else {
            // Set the budget
            $result = $budget->setBudget(
                $formData['department_id'],
                $formData['budget_amount'],
                $formData['duration_type'],
                $formData['start_date'],
                $formData['end_date'],
                Session::getUserId()
            );
            
            if ($result['success']) {
                Session::setFlash('success', $result['message']);
                header('Location: index.php');
                exit;
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$pageTitle = 'Set New Budget';
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

    .form-control::placeholder {
        color: var(--text-muted);
    }

    .form-text {
        margin-top: var(--spacing-2);
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
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

    /* Tips Card */
    .tips-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        height: fit-content;
        position: sticky;
        top: var(--spacing-4);
    }

    .tips-header {
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .tips-icon {
        color: var(--info);
        font-size: var(--font-size-lg);
    }

    .tips-title {
        margin: 0;
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .tips-content {
        padding: var(--spacing-4);
    }

    .tip-item {
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-3);
        margin-bottom: var(--spacing-4);
    }

    .tip-item:last-child {
        margin-bottom: 0;
    }

    .tip-icon {
        color: var(--success);
        font-size: var(--font-size-sm);
        margin-top: 2px;
        flex-shrink: 0;
    }

    .tip-text {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        line-height: 1.5;
    }

    .tip-text strong {
        color: var(--text-primary);
    }

    /* Previous Budget Card */
    .previous-budget-card {
        background: rgba(var(--info-rgb), 0.05);
        border: 1px solid rgba(var(--info-rgb), 0.2);
        border-radius: var(--border-radius);
        padding: var(--spacing-4);
        margin-bottom: var(--spacing-5);
    }

    .previous-budget-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        margin-bottom: var(--spacing-3);
        color: var(--info);
    }

    .previous-budget-title {
        font-weight: var(--font-weight-semibold);
        font-size: var(--font-size-sm);
    }

    .previous-budget-detail {
        display: flex;
        justify-content: space-between;
        padding: var(--spacing-2) 0;
        border-bottom: 1px solid rgba(var(--info-rgb), 0.1);
        font-size: var(--font-size-sm);
    }

    .previous-budget-detail:last-child {
        border-bottom: none;
    }

    .previous-budget-label {
        color: var(--text-secondary);
    }

    .previous-budget-value {
        color: var(--text-primary);
        font-weight: var(--font-weight-medium);
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

    /* Responsive */
    @media (max-width: 768px) {
        .form-header {
            flex-direction: column;
            text-align: center;
        }

        .form-icon {
            margin: 0 auto;
        }

        .tips-card {
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
            <h1 class="content-title">Set New Budget</h1>
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
            <!--        <li class="breadcrumb-item active">Set Budget</li>-->
            <!--    </ol>-->
            <!--</nav>-->
        </div>
        <div class="content-actions">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Budgets</span>
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

<!-- Set Budget Form -->
<div class="form-container">
    <div class="form-card">
        <div class="form-header">
            <div class="form-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="form-header-content">
                <h2 class="form-title">Budget Information</h2>
                <p class="form-subtitle">Set budget allocation for a department</p>
            </div>
        </div>

        <div class="form-body">
            <form method="POST" action="" class="enhanced-form" id="budget-form">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                
                <!-- Previous Budget Info (if applicable) -->
                <?php if ($previousBudget): ?>
                <div class="previous-budget-card">
                    <div class="previous-budget-header">
                        <i class="fas fa-info-circle"></i>
                        <span class="previous-budget-title">Previous Budget Information</span>
                    </div>
                    <div class="previous-budget-detail">
                        <span class="previous-budget-label">Budget Amount:</span>
                        <span class="previous-budget-value">₦<?php echo number_format($previousBudget['budget_amount'], 2); ?></span>
                    </div>
                    <div class="previous-budget-detail">
                        <span class="previous-budget-label">Period:</span>
                        <span class="previous-budget-value">
                            <?php echo date('M d, Y', strtotime($previousBudget['start_date'])); ?> - 
                            <?php echo date('M d, Y', strtotime($previousBudget['end_date'])); ?>
                        </span>
                    </div>
                    <div class="previous-budget-detail">
                        <span class="previous-budget-label">Final Utilization:</span>
                        <span class="previous-budget-value">
                            ₦<?php echo number_format($previousBudget['allocated_amount'], 2); ?> 
                            (<?php echo number_format(($previousBudget['allocated_amount'] / $previousBudget['budget_amount']) * 100, 1); ?>%)
                        </span>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Department Selection -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">Department Selection</h3>
                        <p class="form-section-subtitle">Choose the department for this budget</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="department_id" class="form-label required">Department</label>
                            <select id="department_id" 
                                    name="department_id" 
                                    class="form-control" 
                                    required
                                    <?php echo $preDepartmentId ? 'disabled' : ''; ?>>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" 
                                            <?php echo $formData['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['department_name']); ?> (<?php echo htmlspecialchars($dept['department_code']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($preDepartmentId): ?>
                                <input type="hidden" name="department_id" value="<?php echo $preDepartmentId; ?>">
                            <?php endif; ?>
                            <div class="form-text">Select the department that will use this budget</div>
                        </div>
                    </div>
                </div>

                <!-- Budget Amount -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">Budget Amount</h3>
                        <p class="form-section-subtitle">Set the total budget allocation</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="budget_amount" class="form-label required">Budget Amount (₦)</label>
                            <input type="text" 
                                   id="budget_amount" 
                                   name="budget_amount" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['budget_amount']); ?>"
                                   placeholder="Enter budget amount"
                                   required>
                            <div class="form-text">Total budget available for this period</div>
                        </div>
                    </div>
                </div>

                <!-- Duration Type -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h3 class="form-section-title">Budget Duration</h3>
                        <p class="form-section-subtitle">Select budget period type</p>
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
                                    <div class="radio-description">3 months duration</div>
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
                                    <div class="radio-description">12 months duration</div>
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
                        <p class="form-section-subtitle">Set start and end dates</p>
                    </div>

                    <div class="form-row form-row-2-cols">
                        <div class="form-group">
                            <label for="start_date" class="form-label required">Start Date</label>
                            <input type="date" 
                                   id="start_date" 
                                   name="start_date" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['start_date']); ?>"
                                   required>
                            <div class="form-text">Budget becomes active on this date</div>
                        </div>

                        <div class="form-group">
                            <label for="end_date" class="form-label required">End Date</label>
                            <input type="date" 
                                   id="end_date" 
                                   name="end_date" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($formData['end_date']); ?>"
                                   required>
                            <div class="form-text">Budget expires on this date</div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-check"></i>
                        <span>Set Budget</span>
                    </button>
                    <a href="index.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Tips Card -->
    <div class="tips-card">
        <div class="tips-header">
            <i class="fas fa-lightbulb tips-icon"></i>
            <h3 class="tips-title">Quick Tips</h3>
        </div>
        <div class="tips-content">
            <div class="tip-item">
                <i class="fas fa-info-circle tip-icon"></i>
                <div class="tip-text">
                    <strong>Budget Amount:</strong> Set a realistic amount based on department needs and historical spending
                </div>
            </div>
            
            <div class="tip-item">
                <i class="fas fa-calendar tip-icon"></i>
                <div class="tip-text">
                    <strong>Duration:</strong> Quarterly budgets are recommended for better tracking and adjustment
                </div>
            </div>
            
            <div class="tip-item">
                <i class="fas fa-shield-alt tip-icon"></i>
                <div class="tip-text">
                    <strong>Overlap Check:</strong> System will prevent setting budgets with overlapping date ranges
                </div>
            </div>
            
            <div class="tip-item">
                <i class="fas fa-users tip-icon"></i>
                <div class="tip-text">
                    <strong>Line Managers:</strong> Only Line Managers can approve "Budget" category requisitions
                </div>
            </div>
            
            <div class="tip-item">
                <i class="fas fa-chart-line tip-icon"></i>
                <div class="tip-text">
                    <strong>Tracking:</strong> Budget utilization is updated in real-time when requisitions are approved
                </div>
            </div>
            
            <div class="tip-item">
                <i class="fas fa-redo tip-icon"></i>
                <div class="tip-text">
                    <strong>Renewal:</strong> Set new budgets before current ones expire to avoid disruption
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎨 Initializing Set Budget Form...');

    const form = document.getElementById('budget-form');
    const budgetAmountInput = document.getElementById('budget_amount');
    const durationTypeInputs = document.querySelectorAll('input[name="duration_type"]');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    // Format currency on budget amount field
    if (budgetAmountInput) {
        budgetAmountInput.addEventListener('input', function(e) {
            let value = this.value.replace(/,/g, '');
            if (!isNaN(value) && value !== '') {
                this.value = parseFloat(value).toLocaleString('en-US');
            }
        });

        // Remove commas before submission
        form.addEventListener('submit', function() {
            budgetAmountInput.value = budgetAmountInput.value.replace(/,/g, '');
        });
    }

    // Auto-calculate end date based on duration type
    function updateEndDate() {
        const startDate = startDateInput.value;
        const durationType = document.querySelector('input[name="duration_type"]:checked')?.value;

        if (!startDate || !durationType) return;

        const start = new Date(startDate);
        let end = new Date(start);

        if (durationType === 'quarterly') {
            end.setMonth(start.getMonth() + 3);
            end.setDate(end.getDate() - 1); // Last day of 3rd month
        } else if (durationType === 'yearly') {
            end.setFullYear(start.getFullYear() + 1);
            end.setDate(end.getDate() - 1); // Last day of 12th month
        }

        if (durationType !== 'custom') {
            endDateInput.value = end.toISOString().split('T')[0];
            endDateInput.setAttribute('readonly', 'readonly');
        } else {
            endDateInput.removeAttribute('readonly');
        }
    }

    // Listen to duration type changes
    durationTypeInputs.forEach(input => {
        input.addEventListener('change', updateEndDate);
    });

    // Listen to start date changes
    if (startDateInput) {
        startDateInput.addEventListener('change', updateEndDate);
    }

    // Initialize end date if needed
    updateEndDate();

    // Form submission enhancement
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Setting Budget...</span>';
        });
    }

    // Real-time validation
    const formControls = document.querySelectorAll('.form-control');
    formControls.forEach(control => {
        control.addEventListener('input', function() {
            if (this.hasAttribute('required') && this.value.trim()) {
                this.style.borderColor = 'var(--success)';
            } else if (this.hasAttribute('required') && !this.value.trim()) {
                this.style.borderColor = 'var(--border-color)';
            }
        });
        
        control.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.style.borderColor = 'var(--border-color)';
            }
        });
    });

    console.log('✅ Set Budget Form initialized successfully');
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
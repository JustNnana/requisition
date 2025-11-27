<?php
/**
 * GateWey Requisition Management System
 * Create Requisition Page
 * 
 * File: requisitions/create.php
 * Purpose: Form to create a new requisition with dynamic item table
 * UPDATED: Purpose field changed to dropdown with predefined categories
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

// Initialize objects
$requisition = new Requisition();

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
$customCSS = '
<style>
    .items-table {
        margin-top: var(--spacing-4);
    }
    
    .item-row {
        display: grid;
        grid-template-columns: 3fr 1fr 1fr 1fr auto;
        gap: var(--spacing-3);
        align-items: start;
        margin-bottom: var(--spacing-3);
        padding: var(--spacing-3);
        background: var(--bg-subtle);
        border-radius: var(--border-radius);
    }
    
    .item-row:hover {
        background: var(--bg-hover);
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
    
    .remove-item-btn {
        background: var(--danger);
        color: white;
        border: none;
        padding: var(--spacing-2) var(--spacing-3);
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition-fast);
        font-size: var(--font-size-sm);
        height: 44px;
        margin-top: 28px;
    }
    
    .remove-item-btn:hover {
        background: var(--danger-dark);
        transform: scale(1.05);
    }
    
    .add-item-btn {
        background: var(--primary);
        color: white;
        border: none;
        padding: var(--spacing-3) var(--spacing-4);
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition-fast);
        font-weight: var(--font-weight-semibold);
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-2);
    }
    
    .add-item-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }
    
    .total-section {
        background: var(--bg-subtle);
        padding: var(--spacing-4);
        border-radius: var(--border-radius);
        margin-top: var(--spacing-4);
        border: 2px solid var(--border-color);
    }
    
    .total-amount {
        font-size: var(--font-size-2xl);
        font-weight: var(--font-weight-bold);
        color: var(--primary);
    }
    
    .form-actions {
        display: flex;
        gap: var(--spacing-3);
        justify-content: flex-end;
        margin-top: var(--spacing-6);
        padding-top: var(--spacing-4);
        border-top: 1px solid var(--border-color);
    }
    
    .file-upload-area {
        border: 2px dashed var(--border-color);
        border-radius: var(--border-radius);
        padding: var(--spacing-4);
        text-align: center;
        cursor: pointer;
        transition: var(--transition-fast);
        margin-top: var(--spacing-3);
    }
    
    .file-upload-area:hover {
        border-color: var(--primary);
        background: var(--bg-subtle);
    }
    
    .file-upload-area.drag-over {
        border-color: var(--primary);
        background: rgba(var(--primary-rgb), 0.1);
    }
    
    .uploaded-files {
        margin-top: var(--spacing-3);
    }
    
    .uploaded-file {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--spacing-3);
        background: var(--bg-subtle);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-2);
    }
    
    .file-info {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }
    
    .file-icon {
        font-size: var(--font-size-xl);
    }
    
    @media (max-width: 768px) {
        .item-row {
            grid-template-columns: 1fr;
        }
        
        .remove-item-btn {
            margin-top: 0;
            width: 100%;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .form-actions .btn {
            width: 100%;
        }
    }
</style>
';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Create New Requisition</h1>
            <p class="content-subtitle">Submit a new purchase requisition for approval</p>
        </div>
        <a href="list.php" class="btn btn-ghost">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Workflow:</strong> Your requisition will be sent to 
    <?php
    $roleId = Session::getUserRoleId();
    if ($roleId == ROLE_TEAM_MEMBER) {
        echo "your Line Manager";
    } elseif ($roleId == ROLE_LINE_MANAGER) {
        echo "the Managing Director";
    } elseif ($roleId == ROLE_MANAGING_DIRECTOR) {
        echo "the Finance Manager";
    }
    ?> for approval.
</div>

<!-- Requisition Form -->
<form id="requisitionForm" action="save.php" method="POST" enctype="multipart/form-data">
    <?php echo Session::csrfField(); ?>
    <input type="hidden" name="action" value="create">
    <input type="hidden" name="is_draft" id="is_draft" value="0">
    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-file-alt"></i> Requisition Details
            </h5>
        </div>
        <div class="card-body">
            <!-- Purpose/Category Dropdown -->
            <div class="form-group">
                <label for="purpose" class="form-label required">Purpose/Category</label>
                <select 
                    id="purpose" 
                    name="purpose" 
                    class="form-control" 
                    required
                >
                    <option value="">-- Select Purpose --</option>
                    <option value="Drink water" <?php echo ($formData['purpose'] == 'Drink water') ? 'selected' : ''; ?>>Drink water</option>
                    <option value="Entertainment" <?php echo ($formData['purpose'] == 'Entertainment') ? 'selected' : ''; ?>>Entertainment</option>
                    <option value="Business Travel" <?php echo ($formData['purpose'] == 'Business Travel') ? 'selected' : ''; ?>>Business Travel</option>
                    <option value="Transportation" <?php echo ($formData['purpose'] == 'Transportation') ? 'selected' : ''; ?>>Transportation</option>
                    <option value="Internet facility" <?php echo ($formData['purpose'] == 'Internet facility') ? 'selected' : ''; ?>>Internet facility</option>
                    <option value="Cleaning" <?php echo ($formData['purpose'] == 'Cleaning') ? 'selected' : ''; ?>>Cleaning</option>
                    <option value="Waste management" <?php echo ($formData['purpose'] == 'Waste management') ? 'selected' : ''; ?>>Waste management</option>
                    <option value="Electricity" <?php echo ($formData['purpose'] == 'Electricity') ? 'selected' : ''; ?>>Electricity</option>
                    <option value="Beverage" <?php echo ($formData['purpose'] == 'Beverage') ? 'selected' : ''; ?>>Beverage</option>
                    <option value="Mobile Telephone" <?php echo ($formData['purpose'] == 'Mobile Telephone') ? 'selected' : ''; ?>>Mobile Telephone</option>
                    <option value="Assets" <?php echo ($formData['purpose'] == 'Assets') ? 'selected' : ''; ?>>Assets</option>
                    <option value="Diesel purchase" <?php echo ($formData['purpose'] == 'Diesel purchase') ? 'selected' : ''; ?>>Diesel purchase</option>
                    <option value="Fuel purchase" <?php echo ($formData['purpose'] == 'Fuel purchase') ? 'selected' : ''; ?>>Fuel purchase</option>
                    <option value="Miscellaneous" <?php echo ($formData['purpose'] == 'Miscellaneous') ? 'selected' : ''; ?>>Miscellaneous</option>
                    <option value="Subscriptions" <?php echo ($formData['purpose'] == 'Subscriptions') ? 'selected' : ''; ?>>Subscriptions</option>
                    <option value="Tax" <?php echo ($formData['purpose'] == 'Tax') ? 'selected' : ''; ?>>Tax</option>
                    <option value="Paye" <?php echo ($formData['purpose'] == 'Paye') ? 'selected' : ''; ?>>Paye</option>
                    <option value="R.M. Generator" <?php echo ($formData['purpose'] == 'R.M. Generator') ? 'selected' : ''; ?>>R.M. Generator</option>
                    <option value="R.M. Vehicle" <?php echo ($formData['purpose'] == 'R.M. Vehicle') ? 'selected' : ''; ?>>R.M. Vehicle</option>
                    <option value="R.M. Computer" <?php echo ($formData['purpose'] == 'R.M. Computer') ? 'selected' : ''; ?>>R.M. Computer</option>
                    <option value="R.M. Building" <?php echo ($formData['purpose'] == 'R.M. Building') ? 'selected' : ''; ?>>R.M. Building</option>
                    <option value="R.M. Office Equipment" <?php echo ($formData['purpose'] == 'R.M. Office Equipment') ? 'selected' : ''; ?>>R.M. Office Equipment</option>
                    <option value="Salary and Wages" <?php echo ($formData['purpose'] == 'Salary and Wages') ? 'selected' : ''; ?>>Salary and Wages</option>
                    <option value="Security Cost" <?php echo ($formData['purpose'] == 'Security Cost') ? 'selected' : ''; ?>>Security Cost</option>
                    <option value="Bank Charge" <?php echo ($formData['purpose'] == 'Bank Charge') ? 'selected' : ''; ?>>Bank Charge</option>
                    <option value="Medical Expenses" <?php echo ($formData['purpose'] == 'Medical Expenses') ? 'selected' : ''; ?>>Medical Expenses</option>
                    <option value="Loans" <?php echo ($formData['purpose'] == 'Loans') ? 'selected' : ''; ?>>Loans</option>
                    <option value="Refund" <?php echo ($formData['purpose'] == 'Refund') ? 'selected' : ''; ?>>Refund</option>
                    <option value="Furniture and Fittings" <?php echo ($formData['purpose'] == 'Furniture and Fittings') ? 'selected' : ''; ?>>Furniture and Fittings</option>
                    <option value="Management Expense" <?php echo ($formData['purpose'] == 'Management Expense') ? 'selected' : ''; ?>>Management Expense</option>
                    <option value="Training" <?php echo ($formData['purpose'] == 'Training') ? 'selected' : ''; ?>>Training</option>
                    <option value="Postage and Delivery" <?php echo ($formData['purpose'] == 'Postage and Delivery') ? 'selected' : ''; ?>>Postage and Delivery</option>
                </select>
                <div class="form-text">Select the category that best describes this requisition.</div>
            </div>
            
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
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> Items
            </h5>
        </div>
        <div class="card-body">
            <div id="itemsContainer">
                <!-- Items will be added here dynamically -->
                <div class="item-row" data-item-index="0">
                    <div>
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
                    <div>
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
                    <div>
                        <label class="form-label required">Unit Price (<?php echo CURRENCY_SYMBOL; ?>)</label>
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
                    <div>
                        <label class="form-label">Subtotal</label>
                        <input 
                            type="text" 
                            class="form-control item-subtotal" 
                            readonly 
                            value="<?php echo CURRENCY_SYMBOL; ?>0.00"
                        >
                        <input type="hidden" name="items[0][subtotal]" class="item-subtotal-value" value="0">
                    </div>
                    <div>
                        <button type="button" class="remove-item-btn" style="display: none;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="button" id="addItemBtn" class="add-item-btn">
                    <i class="fas fa-plus-circle"></i> Add Another Item
                </button>
            </div>
            
            <!-- Total Section -->
            <div class="total-section">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 style="margin: 0; color: var(--text-primary);">Total Amount</h4>
                        <p style="margin: var(--spacing-1) 0 0 0; color: var(--text-muted); font-size: var(--font-size-sm);">
                            Sum of all items
                        </p>
                    </div>
                    <div class="total-amount" id="grandTotal">
                        <?php echo CURRENCY_SYMBOL; ?>0.00
                    </div>
                </div>
                <input type="hidden" name="total_amount" id="total_amount" value="0">
            </div>
        </div>
    </div>
    
    <!-- Attachments Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-paperclip"></i> Supporting Documents (Optional)
            </h5>
        </div>
        <div class="card-body">
            <div class="file-upload-area" id="fileUploadArea">
                <i class="fas fa-cloud-upload-alt" style="font-size: var(--font-size-3xl); color: var(--text-muted); margin-bottom: var(--spacing-2);"></i>
                <p style="margin: 0; color: var(--text-primary); font-weight: var(--font-weight-semibold);">
                    Drag & drop files here or click to browse
                </p>
                <p style="margin: var(--spacing-2) 0 0 0; color: var(--text-muted); font-size: var(--font-size-sm);">
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
            <i class="fas fa-times"></i> Cancel
        </button>
        <button type="submit" name="save_draft" class="btn btn-ghost" onclick="document.getElementById('is_draft').value='1'">
            <i class="fas fa-save"></i> Save as Draft
        </button>
        <button type="submit" name="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i> Submit for Approval
        </button>
    </div>
</form>

<!-- JavaScript for dynamic items -->
<script src="<?php echo BASE_URL; ?>/assets/js/requisition.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php
/**
 * GateWey Requisition Management System
 * Create Requisition Page - Dasher UI Enhanced (Fully Recoded)
 * 
 * File: requisitions/create.php
 * Purpose: Form to create a new requisition with dynamic item table
 * 
 * UPDATED: Complete Dasher UI redesign with modern layout and styling
 * Purpose field changed to dropdown with predefined categories
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
$categoryModel = new RequisitionCategory();

// Load active categories from database
$categories = $categoryModel->getAllActive();

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

<!-- Dasher UI Enhanced Styles -->
<style>
    /* Form Section Card */
    .form-section-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        margin-bottom: var(--spacing-5);
        overflow: hidden;
        transition: var(--theme-transition);
    }

    .form-section-card:hover {
        box-shadow: var(--shadow-sm);
    }

    .form-section-header {
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-4);
        padding: var(--spacing-5);
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
        color: var(--primary);
    }

    .form-section-icon.success {
        background: rgba(var(--success-rgb), 0.1);
        color: var(--success);
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

    /* Info Alert */
    .workflow-info-alert {
        border: solid 1px var(--info);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-5);
        color: white;
        margin-bottom: var(--spacing-5);
    }

    .workflow-info-content {
        display: flex;
        align-items: start;
        gap: var(--spacing-4);
    }

    .workflow-info-icon {
        font-size: 2rem;
        flex-shrink: 0;
        margin-top: var(--spacing-1);
    }

    .workflow-info-text {
        flex: 1;
    }

    .workflow-info-text h5 {
        margin: 0 0 var(--spacing-2) 0;
        font-weight: var(--font-weight-semibold);
    }

    .workflow-info-text p {
        margin: 0;
        opacity: 0.9;
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

    .form-control:disabled,
    .form-control:read-only {
        background: var(--bg-subtle);
        color: var(--text-muted);
        cursor: not-allowed;
    }

    .form-text {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        margin-top: var(--spacing-2);
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
        transition: var(--transition-fast);
    }

    .item-row:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
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
        transition: var(--transition-fast);
        font-size: var(--font-size-sm);
        height: 44px;
        margin-top: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
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

    /* Total Section */
    .total-section {
        background: var(--bg-subtle);
        padding: var(--spacing-5);
        border-radius: var(--border-radius);
        margin-top: var(--spacing-5);
        border: 2px solid var(--border-color);
    }

    .total-section-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .total-section-label h4 {
        margin: 0;
        color: var(--text-primary);
        font-size: var(--font-size-xl);
    }

    .total-section-label p {
        margin: var(--spacing-1) 0 0 0;
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

    .file-upload-icon {
        font-size: var(--font-size-4xl);
        color: var(--text-muted);
        margin-bottom: var(--spacing-3);
    }

    .file-upload-text {
        margin: 0;
        color: var(--text-primary);
        font-weight: var(--font-weight-semibold);
        font-size: var(--font-size-base);
    }

    .file-upload-hint {
        margin: var(--spacing-2) 0 0 0;
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
        transition: var(--transition-fast);
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

    /* Mobile Optimizations */
    @media (max-width: 768px) {
        .form-section-header {
            padding: var(--spacing-4);
        }

        .form-section-body {
            padding: var(--spacing-4);
        }

        .form-section-icon {
            width: 40px;
            height: 40px;
            font-size: var(--font-size-lg);
        }

        .workflow-info-alert {
            padding: var(--spacing-4);
        }

        .workflow-info-content {
            flex-direction: column;
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

        .content-actions {
            display: flex !important;
            justify-content: flex-end !important;
            gap: 0.5rem;
            flex-wrap: wrap;
            white-space: nowrap !important;
        }

        .content-actions .btn {
            flex: 0 1 auto !important;
            white-space: nowrap;
        }
    }
</style>
    <!-- Content Header -->
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="content-title">
                    <i class="fas fa-plus-circle me-2"></i>
                    Create New Requisition
                </h1>
                <!-- <nav class="content-breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?php echo BASE_URL; ?>dashboard/" class="breadcrumb-link">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="list.php" class="breadcrumb-link">Requisitions</a>
                        </li>
                        <li class="breadcrumb-item active">Create New</li>
                    </ol>
                </nav> -->
                <p class="content-description">Submit a new purchase requisition for approval</p>
            </div>
            <div class="content-actions">
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    <span>Back to List</span>
                </a>
            </div>
        </div>
    </div>

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
                <div class="form-group">
                    <label for="purpose" class="form-label required">Purpose/Category</label>
                    <select 
                        id="purpose" 
                        name="purpose" 
                        class="form-control" 
                        required
                    >
                        <option value="">-- Select Purpose --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['category_name']); ?>" 
                                <?php echo ($formData['purpose'] == $category['category_name']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
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
                        <div class="total-amount" id="grandTotal">
                            <?php echo CURRENCY_SYMBOL; ?>0.00
                        </div>
                    </div>
                    <input type="hidden" name="total_amount" id="total_amount" value="0">
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
                <i class="fas fa-times me-2"></i>Cancel
            </button>
            <button type="submit" name="save_draft" class="btn btn-outline-primary" onclick="document.getElementById('is_draft').value='1'">
                <i class="fas fa-save me-2"></i>Save as Draft
            </button>
            <button type="submit" name="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane me-2"></i>Submit for Approval
            </button>
        </div>
    </form>
</div>

<!-- JavaScript for dynamic items -->
<script src="<?php echo BASE_URL; ?>/assets/js/requisition.js"></script>
<script>
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
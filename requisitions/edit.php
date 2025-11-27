<?php
/**
 * GateWey Requisition Management System
 * Edit Requisition Page - Dasher UI Enhanced (Fully Recoded)
 * 
 * File: requisitions/edit.php
 * Purpose: Edit draft or rejected requisitions
 * 
 * UPDATED: Complete Dasher UI redesign with modern layout and styling
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

// Get requisition ID
$requisitionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$requisitionId) {
    Session::setFlash('error', 'Invalid requisition ID.');
    header('Location: list.php');
    exit;
}

// Initialize objects
$requisition = new Requisition();
$categoryModel = new RequisitionCategory();

// Load active categories from database
$categories = $categoryModel->getAllActive();

// Get requisition data
$reqData = $requisition->getById($requisitionId);

if (!$reqData) {
    Session::setFlash('error', 'Requisition not found.');
    header('Location: list.php');
    exit;
}

// Check if user can edit
if (!can_user_edit_requisition($reqData)) {
    Session::setFlash('error', 'You cannot edit this requisition.');
    header('Location: view.php?id=' . $requisitionId);
    exit;
}

// Page title
$pageTitle = 'Edit Requisition ' . $reqData['requisition_number'];
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

    /* Rejection Alert */
    .rejection-alert {
        border: solid 1px var(--danger);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-5);
        color: white;
        margin-bottom: var(--spacing-5);
    }

    .rejection-alert-content {
        display: flex;
        align-items: start;
        gap: var(--spacing-4);
    }

    .rejection-alert-icon {
        font-size: 2rem;
        flex-shrink: 0;
        margin-top: var(--spacing-1);
    }

    .rejection-alert-text h5 {
        margin: 0 0 var(--spacing-2) 0;
        font-weight: var(--font-weight-semibold);
    }

    .rejection-alert-text p {
        margin: 0;
        opacity: 0.9;
    }

    .rejection-metadata {
        margin-top: var(--spacing-2);
        opacity: 0.8;
        font-size: var(--font-size-sm);
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
                    <i class="fas fa-edit me-2"></i>
                    Edit Requisition <?php echo htmlspecialchars($reqData['requisition_number']); ?>
                </h1>
                <!-- <nav class="content-breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?php echo BASE_URL; ?>dashboard/" class="breadcrumb-link">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="list.php" class="breadcrumb-link">Requisitions</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="view.php?id=<?php echo $requisitionId; ?>" class="breadcrumb-link">
                                <?php echo htmlspecialchars($reqData['requisition_number']); ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav> -->
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
                                <?php echo ($reqData['purpose'] == $category['category_name']) ? 'selected' : ''; ?>>
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
                    ></textarea>
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
                                    required
                                >
                            </div>
                            <div class="item-field">
                                <label class="form-label required">Quantity</label>
                                <input 
                                    type="number" 
                                    name="items[<?php echo $index; ?>][quantity]" 
                                    class="form-control item-quantity" 
                                    min="1" 
                                    value="<?php echo $item['quantity']; ?>"
                                    required
                                >
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
                                    required
                                >
                            </div>
                            <div class="item-field">
                                <label class="form-label">Subtotal</label>
                                <input 
                                    type="text" 
                                    class="form-control item-subtotal" 
                                    readonly 
                                    value="<?php echo CURRENCY_SYMBOL . number_format($item['subtotal'], 2); ?>"
                                >
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
                        <div class="total-amount" id="grandTotal">
                            <?php echo format_currency($reqData['total_amount']); ?>
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
            <button type="submit" name="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane me-2"></i>Resubmit for Approval
            </button>
        </div>
    </form>
</div>

<!-- JavaScript for dynamic items -->
<script src="<?php echo BASE_URL; ?>/assets/js/requisition.js"></script>
<script>
// Set the starting item index for new items
window.itemIndex = <?php echo count($reqData['items']); ?>;

// Calculate initial total
if (typeof calculateGrandTotal === 'function') {
    calculateGrandTotal();
}

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
<?php
/**
 * GateWey Requisition Management System
 * Create Requisition Page
 * 
 * File: requisitions/create.php
 * Purpose: Form to create a new requisition with dynamic item table
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
            <!-- Purpose -->
            <div class="form-group">
                <label for="purpose" class="form-label required">Purpose/Description</label>
                <textarea 
                    id="purpose" 
                    name="purpose" 
                    class="form-control" 
                    rows="4"
                    placeholder="Enter the purpose or description of this requisition..."
                    required
                ><?php echo htmlspecialchars($formData['purpose']); ?></textarea>
                <div class="form-text">Provide a clear and detailed description of what you need and why.</div>
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
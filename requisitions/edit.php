<?php
/**
 * GateWey Requisition Management System
 * Edit Requisition Page
 * 
 * File: requisitions/edit.php
 * Purpose: Edit draft or rejected requisitions
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
$customCSS = '
<style>
    /* Same CSS as create.php */
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
            <h1 class="content-title">Edit Requisition <?php echo htmlspecialchars($reqData['requisition_number']); ?></h1>
            <p class="content-subtitle">Update your requisition details and resubmit</p>
        </div>
        <a href="view.php?id=<?php echo $requisitionId; ?>" class="btn btn-ghost">
            <i class="fas fa-arrow-left"></i> Back to View
        </a>
    </div>
</div>

<!-- Rejection Alert -->
<?php if ($reqData['status'] == STATUS_REJECTED && $reqData['rejection_reason']): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($reqData['rejection_reason']); ?>
        <?php if ($reqData['rejected_by_first_name']): ?>
            <br>
            <small>
                Rejected by <?php echo htmlspecialchars($reqData['rejected_by_first_name'] . ' ' . $reqData['rejected_by_last_name']); ?>
                on <?php echo format_date($reqData['rejected_at'], 'M d, Y \a\t h:i A'); ?>
            </small>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Requisition Form -->
<form id="requisitionForm" action="save.php" method="POST" enctype="multipart/form-data">
    <?php echo Session::csrfField(); ?>
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="requisition_id" value="<?php echo $requisitionId; ?>">
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
                ><?php echo htmlspecialchars($reqData['purpose']); ?></textarea>
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
                <!-- Existing items -->
                <?php foreach ($reqData['items'] as $index => $item): ?>
                    <div class="item-row" data-item-index="<?php echo $index; ?>">
                        <div>
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
                        <div>
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
                        <div>
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
                        <div>
                            <label class="form-label">Subtotal</label>
                            <input 
                                type="text" 
                                class="form-control item-subtotal" 
                                readonly 
                                value="<?php echo CURRENCY_SYMBOL . number_format($item['subtotal'], 2); ?>"
                            >
                            <input type="hidden" name="items[<?php echo $index; ?>][subtotal]" class="item-subtotal-value" value="<?php echo $item['subtotal']; ?>">
                        </div>
                        <div>
                            <button type="button" class="remove-item-btn" <?php echo count($reqData['items']) <= 1 ? 'style="display:none;"' : ''; ?>>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
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
                        <?php echo format_currency($reqData['total_amount']); ?>
                    </div>
                </div>
                <input type="hidden" name="total_amount" id="total_amount" value="<?php echo $reqData['total_amount']; ?>">
            </div>
        </div>
    </div>
    
    <!-- Existing Attachments -->
    <?php if (!empty($reqData['documents'])): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-paperclip"></i> Existing Attachments
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($reqData['documents'] as $doc): ?>
                    <div class="uploaded-file">
                        <div class="file-info">
                            <i class="fas <?php echo FileUpload::getFileIcon($doc['file_name']); ?> file-icon"></i>
                            <div>
                                <div style="font-weight: var(--font-weight-semibold);">
                                    <?php echo htmlspecialchars($doc['file_name']); ?>
                                </div>
                                <div style="font-size: var(--font-size-sm); color: var(--text-muted);">
                                    <?php echo format_file_size($doc['file_size']); ?>
                                </div>
                            </div>
                        </div>
                        <div>
                            <a href="../api/download-file.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-ghost" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- New Attachments -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-plus-circle"></i> Add More Attachments (Optional)
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
                <!-- New uploaded files will appear here -->
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="form-actions">
        <button type="button" class="btn btn-secondary" onclick="window.location.href='view.php?id=<?php echo $requisitionId; ?>'">
            <i class="fas fa-times"></i> Cancel
        </button>
        <button type="submit" name="save_draft" class="btn btn-ghost" onclick="document.getElementById('is_draft').value='1'">
            <i class="fas fa-save"></i> Save as Draft
        </button>
        <button type="submit" name="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i> Resubmit for Approval
        </button>
    </div>
</form>

<!-- JavaScript for dynamic items -->
<script src="<?php echo BASE_URL; ?>/assets/js/requisition.js"></script>
<script>
// Set the starting item index for new items
window.itemIndex = <?php echo count($reqData['items']); ?>;
// Calculate initial total
calculateGrandTotal();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
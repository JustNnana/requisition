/**
 * GateWey Requisition Management System
 * Requisition Form JavaScript
 * 
 * File: assets/js/requisition.js
 * Purpose: Handle dynamic item rows, calculations, and file uploads
 */

// Global item index counter
let itemIndex = 1;

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize
    initializeItemCalculations();
    initializeFileUpload();
    
    // Add item button
    const addItemBtn = document.getElementById('addItemBtn');
    if (addItemBtn) {
        addItemBtn.addEventListener('click', addNewItem);
    }
    
    // Initial calculation
    calculateGrandTotal();
});

/**
 * Initialize calculations for all items
 */
function initializeItemCalculations() {
    const itemsContainer = document.getElementById('itemsContainer');
    if (!itemsContainer) return;
    
    // Add event listeners to all quantity and unit price inputs
    itemsContainer.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-quantity') || 
            e.target.classList.contains('item-unit-price')) {
            const itemRow = e.target.closest('.item-row');
            calculateItemSubtotal(itemRow);
        }
    });
    
    // Add event listeners to remove buttons
    itemsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item-btn')) {
            const itemRow = e.target.closest('.item-row');
            removeItem(itemRow);
        }
    });
}

/**
 * Add new item row
 */
function addNewItem() {
    const itemsContainer = document.getElementById('itemsContainer');
    const currentItemCount = itemsContainer.querySelectorAll('.item-row').length;
    
    // Use global itemIndex if set, otherwise use count
    const newIndex = window.itemIndex !== undefined ? window.itemIndex : currentItemCount;
    
    const newItemRow = document.createElement('div');
    newItemRow.className = 'item-row';
    newItemRow.setAttribute('data-item-index', newIndex);
    
    newItemRow.innerHTML = `
        <div>
            <span class="item-number">${currentItemCount + 1}</span>
            <label class="form-label required">Item Description</label>
            <input 
                type="text" 
                name="items[${newIndex}][description]" 
                class="form-control item-description" 
                placeholder="Enter item description"
                required
            >
        </div>
        <div>
            <label class="form-label required">Quantity</label>
            <input 
                type="number" 
                name="items[${newIndex}][quantity]" 
                class="form-control item-quantity" 
                min="1" 
                value="1"
                required
            >
        </div>
        <div>
            <label class="form-label required">Unit Price (${window.CURRENCY_SYMBOL || '₦'})</label>
            <input 
                type="number" 
                name="items[${newIndex}][unit_price]" 
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
                value="${window.CURRENCY_SYMBOL || '₦'}0.00"
            >
            <input type="hidden" name="items[${newIndex}][subtotal]" class="item-subtotal-value" value="0">
        </div>
        <div>
            <button type="button" class="remove-item-btn">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    itemsContainer.appendChild(newItemRow);
    
    // Increment item index for next item
    if (window.itemIndex !== undefined) {
        window.itemIndex++;
    } else {
        itemIndex++;
    }
    
    // Update remove button visibility
    updateRemoveButtons();
    
    // Focus on the new item description
    const newDescInput = newItemRow.querySelector('.item-description');
    if (newDescInput) {
        newDescInput.focus();
    }
}

/**
 * Remove item row
 */
function removeItem(itemRow) {
    const itemsContainer = document.getElementById('itemsContainer');
    const itemRows = itemsContainer.querySelectorAll('.item-row');
    
    // Don't allow removing if only one item
    if (itemRows.length <= 1) {
        alert('At least one item is required.');
        return;
    }
    
    // Remove the row
    itemRow.remove();
    
    // Renumber items
    renumberItems();
    
    // Update remove button visibility
    updateRemoveButtons();
    
    // Recalculate total
    calculateGrandTotal();
}

/**
 * Renumber item rows
 */
function renumberItems() {
    const itemRows = document.querySelectorAll('.item-row');
    itemRows.forEach((row, index) => {
        const itemNumber = row.querySelector('.item-number');
        if (itemNumber) {
            itemNumber.textContent = index + 1;
        }
    });
}

/**
 * Update remove button visibility
 */
function updateRemoveButtons() {
    const itemRows = document.querySelectorAll('.item-row');
    const removeButtons = document.querySelectorAll('.remove-item-btn');
    
    removeButtons.forEach((btn, index) => {
        if (itemRows.length <= 1) {
            btn.style.display = 'none';
        } else {
            btn.style.display = 'block';
        }
    });
}

/**
 * Calculate item subtotal
 */
function calculateItemSubtotal(itemRow) {
    const quantityInput = itemRow.querySelector('.item-quantity');
    const unitPriceInput = itemRow.querySelector('.item-unit-price');
    const subtotalDisplay = itemRow.querySelector('.item-subtotal');
    const subtotalValue = itemRow.querySelector('.item-subtotal-value');
    
    const quantity = parseFloat(quantityInput.value) || 0;
    const unitPrice = parseFloat(unitPriceInput.value) || 0;
    const subtotal = quantity * unitPrice;
    
    // Update display
    subtotalDisplay.value = formatCurrency(subtotal);
    subtotalValue.value = subtotal.toFixed(2);
    
    // Recalculate grand total
    calculateGrandTotal();
}

/**
 * Calculate grand total
 */
function calculateGrandTotal() {
    const subtotalValues = document.querySelectorAll('.item-subtotal-value');
    let grandTotal = 0;
    
    subtotalValues.forEach(input => {
        const value = parseFloat(input.value) || 0;
        grandTotal += value;
    });
    
    // Update display
    const grandTotalDisplay = document.getElementById('grandTotal');
    const totalAmountInput = document.getElementById('total_amount');
    
    if (grandTotalDisplay) {
        grandTotalDisplay.textContent = formatCurrency(grandTotal);
    }
    
    if (totalAmountInput) {
        totalAmountInput.value = grandTotal.toFixed(2);
    }
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    const symbol = window.CURRENCY_SYMBOL || '₦';
    const formatted = parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    return symbol + formatted;
}

/**
 * Initialize file upload functionality
 */
function initializeFileUpload() {
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('fileInput');
    const uploadedFilesDiv = document.getElementById('uploadedFiles');
    
    if (!fileUploadArea || !fileInput) return;
    
    // Click to upload
    fileUploadArea.addEventListener('click', function() {
        fileInput.click();
    });
    
    // File input change
    fileInput.addEventListener('change', function() {
        displaySelectedFiles();
    });
    
    // Drag and drop
    fileUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        fileUploadArea.classList.add('drag-over');
    });
    
    fileUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        fileUploadArea.classList.remove('drag-over');
    });
    
    fileUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        fileUploadArea.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            displaySelectedFiles();
        }
    });
}

/**
 * Display selected files
 */
function displaySelectedFiles() {
    const fileInput = document.getElementById('fileInput');
    const uploadedFilesDiv = document.getElementById('uploadedFiles');
    
    if (!fileInput.files.length) {
        uploadedFilesDiv.style.display = 'none';
        return;
    }
    
    uploadedFilesDiv.innerHTML = '';
    uploadedFilesDiv.style.display = 'block';
    
    Array.from(fileInput.files).forEach((file, index) => {
        const fileDiv = document.createElement('div');
        fileDiv.className = 'uploaded-file';
        
        const fileIcon = getFileIcon(file.name);
        const fileSize = formatFileSize(file.size);
        
        fileDiv.innerHTML = `
            <div class="file-info">
                <i class="fas ${fileIcon} file-icon"></i>
                <div>
                    <div style="font-weight: var(--font-weight-semibold);">
                        ${escapeHtml(file.name)}
                    </div>
                    <div style="font-size: var(--font-size-sm); color: var(--text-muted);">
                        ${fileSize}
                    </div>
                </div>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeFile(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        uploadedFilesDiv.appendChild(fileDiv);
    });
}

/**
 * Remove file from selection
 */
function removeFile(index) {
    const fileInput = document.getElementById('fileInput');
    const dt = new DataTransfer();
    const files = Array.from(fileInput.files);
    
    files.forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    fileInput.files = dt.files;
    displaySelectedFiles();
}

/**
 * Get file icon class based on extension
 */
function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    
    const icons = {
        'pdf': 'fa-file-pdf text-danger',
        'doc': 'fa-file-word text-primary',
        'docx': 'fa-file-word text-primary',
        'xls': 'fa-file-excel text-success',
        'xlsx': 'fa-file-excel text-success',
        'jpg': 'fa-file-image text-info',
        'jpeg': 'fa-file-image text-info',
        'png': 'fa-file-image text-info',
        'gif': 'fa-file-image text-info'
    };
    
    return icons[ext] || 'fa-file text-secondary';
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Form validation before submit
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('requisitionForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validate at least one item
            const itemRows = document.querySelectorAll('.item-row');
            if (itemRows.length === 0) {
                e.preventDefault();
                alert('Please add at least one item.');
                return false;
            }
            
            // Validate total amount
            const totalAmount = parseFloat(document.getElementById('total_amount').value);
            if (totalAmount <= 0) {
                e.preventDefault();
                alert('Total amount must be greater than zero.');
                return false;
            }
            
            // All validations passed
            return true;
        });
    }
});
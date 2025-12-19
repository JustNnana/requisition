/**
 * GateWey Requisition Management System
 * Category Cascade Script
 * 
 * File: assets/js/category-cascade.js
 * Purpose: Handle parent-child category dropdown interaction in requisition forms
 * 
 * USAGE: Include this file in create.php and edit.php requisition pages
 */

(function() {
    'use strict';
    
    /**
     * Initialize category cascade functionality
     */
    function initCategoryCascade() {
        const parentSelect = document.getElementById('parent_category');
        const childSelect = document.getElementById('child_category');
        const childWrapper = document.getElementById('child_category_wrapper');
        
        if (!parentSelect || !childSelect) {
            console.warn('Category cascade: Required elements not found');
            return;
        }
        
        // Handle parent category change
        parentSelect.addEventListener('change', function() {
            const parentId = this.value;
            
            if (!parentId) {
                // No parent selected - hide child dropdown
                hideChildCategory();
                return;
            }
            
            // Load child categories for selected parent
            loadChildCategories(parentId);
        });
        
        // If parent is pre-selected on page load, load children
        if (parentSelect.value) {
            loadChildCategories(parentSelect.value);
        }
    }
    
    /**
     * Load child categories via AJAX
     */
    function loadChildCategories(parentId) {
        const childSelect = document.getElementById('child_category');
        const childWrapper = document.getElementById('child_category_wrapper');
        const loadingIndicator = document.getElementById('child_loading');
        
        // Show loading state
        childSelect.disabled = true;
        childSelect.innerHTML = '<option value="">Loading...</option>';
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
        }
        
        // Make AJAX request
        fetch(BASE_URL + '/api/get-child-categories.php?parent_id=' + parentId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateChildCategories(data.children);
                    
                    // Show child dropdown if there are children
                    if (data.children.length > 0) {
                        showChildCategory();
                    } else {
                        hideChildCategory();
                        // Show info message
                        showNoChildrenMessage(data.parent.name);
                    }
                } else {
                    console.error('Error loading categories:', data.error);
                    childSelect.innerHTML = '<option value="">Error loading categories</option>';
                }
            })
            .catch(error => {
                console.error('AJAX error:', error);
                childSelect.innerHTML = '<option value="">Error loading categories</option>';
            })
            .finally(() => {
                childSelect.disabled = false;
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }
            });
    }
    
    /**
     * Populate child category dropdown
     */
    function populateChildCategories(children) {
        const childSelect = document.getElementById('child_category');
        
        // Clear existing options
        childSelect.innerHTML = '<option value="">-- Select Child Category --</option>';
        
        // Add child options
        children.forEach(function(child) {
            const option = document.createElement('option');
            option.value = child.id;
            option.textContent = child.category_name;
            option.setAttribute('data-category-name', child.category_name);
            option.setAttribute('data-category-code', child.category_code || '');
            
            // Mark inactive categories
            if (child.is_active != 1) {
                option.textContent += ' (Inactive)';
                option.disabled = true;
            }
            
            childSelect.appendChild(option);
        });
        
        // Trigger change event if there's a pre-selected value
        const preSelectedValue = childSelect.getAttribute('data-preselected');
        if (preSelectedValue) {
            childSelect.value = preSelectedValue;
            childSelect.removeAttribute('data-preselected');
            childSelect.dispatchEvent(new Event('change'));
        }
    }
    
    /**
     * Show child category dropdown
     */
    function showChildCategory() {
        const childWrapper = document.getElementById('child_category_wrapper');
        const childSelect = document.getElementById('child_category');
        
        if (childWrapper) {
            childWrapper.style.display = 'block';
            childSelect.required = true;
        }
    }
    
    /**
     * Hide child category dropdown
     */
    function hideChildCategory() {
        const childWrapper = document.getElementById('child_category_wrapper');
        const childSelect = document.getElementById('child_category');
        
        if (childWrapper) {
            childWrapper.style.display = 'none';
            childSelect.required = false;
            childSelect.innerHTML = '<option value="">-- Select Child Category --</option>';
        }
        
        // Clear any "no children" message
        clearNoChildrenMessage();
    }
    
    /**
     * Show message when parent has no children
     */
    function showNoChildrenMessage(parentName) {
        const messageContainer = document.getElementById('no_children_message');
        
        if (messageContainer) {
            messageContainer.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>${parentName}</strong> has no child categories yet.
                </div>
            `;
            messageContainer.style.display = 'block';
        }
    }
    
    /**
     * Clear "no children" message
     */
    function clearNoChildrenMessage() {
        const messageContainer = document.getElementById('no_children_message');
        
        if (messageContainer) {
            messageContainer.innerHTML = '';
            messageContainer.style.display = 'none';
        }
    }
    
    /**
     * Update hidden category name field (for display purposes)
     */
    function updateCategoryNameField() {
        const parentSelect = document.getElementById('parent_category');
        const childSelect = document.getElementById('child_category');
        const categoryNameField = document.getElementById('category_name_display');
        
        if (!categoryNameField) return;
        
        let categoryName = '';
        
        // If child is selected, use child name
        if (childSelect && childSelect.value) {
            const selectedChild = childSelect.options[childSelect.selectedIndex];
            categoryName = selectedChild.getAttribute('data-category-name') || selectedChild.textContent;
        }
        // Otherwise use parent name
        else if (parentSelect && parentSelect.value) {
            const selectedParent = parentSelect.options[parentSelect.selectedIndex];
            categoryName = selectedParent.getAttribute('data-category-name') || selectedParent.textContent;
        }
        
        categoryNameField.value = categoryName;
    }
    
    /**
     * Attach event listeners for category name sync
     */
    function attachCategoryNameSync() {
        const parentSelect = document.getElementById('parent_category');
        const childSelect = document.getElementById('child_category');
        
        if (parentSelect) {
            parentSelect.addEventListener('change', updateCategoryNameField);
        }
        
        if (childSelect) {
            childSelect.addEventListener('change', updateCategoryNameField);
        }
    }
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initCategoryCascade();
            attachCategoryNameSync();
        });
    } else {
        initCategoryCascade();
        attachCategoryNameSync();
    }
})();
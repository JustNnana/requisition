/**
 * GateWey Requisition Management System
 * Requisition Budget Check JavaScript
 * 
 * File: assets/js/requisition-budget.js
 * Purpose: Real-time budget validation for ALL categories
 */

(function() {
    'use strict';

    // Check if budget config exists
    if (typeof window.BUDGET_CONFIG === 'undefined' || !window.BUDGET_CONFIG.hasBudget) {
        return;
    }

    const BudgetChecker = {
        config: window.BUDGET_CONFIG,
        currentTotal: 0,
        isChecking: false,
        checkTimeout: null,
        isBudgetCategory: false,

        init() {
            this.cacheElements();
            this.bindEvents();
            console.log('Budget checker initialized');
        },

        cacheElements() {
            this.elements = {
                purposeSelect: document.getElementById('purpose'),
                budgetCard: document.getElementById('budgetCard'),
                budgetCheckContainer: document.getElementById('budgetCheckContainer'),
                budgetStatus: document.getElementById('budgetStatus'),
                budgetRemaining: document.getElementById('budgetRemaining'),
                requisitionTotal: document.getElementById('requisitionTotal'),
                remainingAmount: document.getElementById('remainingAmount'),
                budgetErrorAlert: document.getElementById('budgetErrorAlert'),
                budgetErrorMessage: document.getElementById('budgetErrorMessage'),
                submitButton: document.getElementById('submitRequisitionBtn'),
                itemsContainer: document.getElementById('itemsTableBody'),
                totalAmountDisplay: document.getElementById('grandTotal')
            };
        },

bindEvents() {
    // Watch for category change - ALL categories now affect budget
    if (this.elements.purposeSelect) {
        this.elements.purposeSelect.addEventListener('change', (e) => {
            // Show budget card whenever ANY category is selected
            if (e.target.value) {
                this.isBudgetCategory = true;  // Always true now
                this.elements.budgetCard.style.display = 'block';
                // Trigger check if items already exist
                if (this.currentTotal > 0) {
                    this.handleItemChange();
                }
            } else {
                // Only hide if NO category is selected
                this.isBudgetCategory = false;
                this.elements.budgetCard.style.display = 'none';
                this.hideError();
                this.enableSubmit();
            }
        });
    }

            // Watch for changes in items table
            if (this.elements.itemsContainer) {
                // Use event delegation for dynamically added items
                this.elements.itemsContainer.addEventListener('input', (e) => {
                    if (e.target.matches('.item-quantity, .item-unit-price')) {
                        this.handleItemChange();
                    }
                });

                // Watch for item removal
                this.elements.itemsContainer.addEventListener('click', (e) => {
                    if (e.target.closest('.remove-item-btn')) {
                        setTimeout(() => this.handleItemChange(), 100);
                    }
                });
            }

            // Watch for new items being added
            const addItemBtn = document.getElementById('addItemBtn');
            if (addItemBtn) {
                addItemBtn.addEventListener('click', () => {
                    setTimeout(() => this.handleItemChange(), 100);
                });
            }
        },

handleItemChange() {
    // Calculate current total
    this.calculateTotal();

    // Only check budget if a category is selected (any category, not just "Budget")
    if (!this.isBudgetCategory) {
        return;
    }

    // Debounce budget check
    clearTimeout(this.checkTimeout);
    this.checkTimeout = setTimeout(() => {
        if (this.currentTotal > 0) {
            this.checkBudget();
        } else {
            this.hideBudgetCheck();
        }
    }, 500);
},

        calculateTotal() {
            let total = 0;
            const rows = this.elements.itemsContainer.querySelectorAll('.item-row');

            rows.forEach(row => {
                const quantityInput = row.querySelector('.item-quantity');
                const priceInput = row.querySelector('.item-unit-price');

                if (quantityInput && priceInput) {
                    const quantity = parseFloat(quantityInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    total += quantity * price;
                }
            });

            this.currentTotal = total;

            // Update display
            if (this.elements.requisitionTotal) {
                this.elements.requisitionTotal.textContent = '₦' + this.formatNumber(total);
            }

            return total;
        },

        checkBudget() {
            if (this.isChecking) return;

            this.isChecking = true;
            this.showBudgetCheck();
            this.updateStatus('checking', 'Checking budget availability...', '');

            // Get CSRF token
            const csrfToken = document.querySelector('input[name="csrf_token"]').value;

            // Make AJAX request
            fetch(this.config.checkUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    csrf_token: csrfToken,
                    department_id: this.config.departmentId,
                    amount: this.currentTotal
                })
            })
            .then(response => response.json())
            .then(data => {
                this.handleBudgetResponse(data);
            })
            .catch(error => {
                console.error('Budget check error:', error);
                this.updateStatus('danger', 'Error checking budget', 'Please try again.');
            })
            .finally(() => {
                this.isChecking = false;
            });
        },

        handleBudgetResponse(data) {
            if (!data.success) {
                this.updateStatus('danger', 'Budget Check Failed', data.message);
                this.disableSubmit(data.message);
                return;
            }

            const { sufficient, remaining_after, utilization_percentage, status_level, message } = data;

            let statusTitle, statusDesc;

            if (sufficient) {
                if (utilization_percentage < 75) {
                    statusTitle = 'Within Budget ✓';
                    statusDesc = `This requisition uses ${utilization_percentage.toFixed(1)}% of your available budget.`;
                } else if (utilization_percentage < 90) {
                    statusTitle = 'Near Budget Limit';
                    statusDesc = `This requisition uses ${utilization_percentage.toFixed(1)}% of your available budget. Consider if all items are necessary.`;
                } else {
                    statusTitle = 'High Budget Usage';
                    statusDesc = `This requisition uses ${utilization_percentage.toFixed(1)}% of your available budget. Very little will remain.`;
                }

                this.updateStatus(status_level, statusTitle, statusDesc);
                this.showRemaining(remaining_after);
                this.enableSubmit();
                this.hideError();

            } else {
                statusTitle = 'Exceeds Budget';
                statusDesc = message;

                this.updateStatus('danger', statusTitle, statusDesc);
                this.hideRemaining();
                this.disableSubmit(message);
                this.showError(message);
            }
        },

        updateStatus(level, title, description) {
            const iconMap = {
                checking: 'fa-spinner fa-spin',
                success: 'fa-check-circle',
                warning: 'fa-exclamation-triangle',
                danger: 'fa-times-circle'
            };

            this.elements.budgetStatus.className = 'budget-status-indicator ' + level;
            this.elements.budgetStatus.innerHTML = `
                <div class="budget-status-icon">
                    <i class="fas ${iconMap[level] || iconMap.success}"></i>
                </div>
                <div class="budget-status-text">
                    <strong>${title}</strong>
                    ${description ? `<p>${description}</p>` : ''}
                </div>
            `;
        },

        showRemaining(amount) {
            if (this.elements.budgetRemaining && this.elements.remainingAmount) {
                this.elements.remainingAmount.textContent = '₦' + this.formatNumber(amount);
                this.elements.budgetRemaining.style.display = 'block';
            }
        },

        hideRemaining() {
            if (this.elements.budgetRemaining) {
                this.elements.budgetRemaining.style.display = 'none';
            }
        },

        showBudgetCheck() {
            if (this.elements.budgetCheckContainer) {
                this.elements.budgetCheckContainer.style.display = 'block';
            }
        },

        hideBudgetCheck() {
            if (this.elements.budgetCheckContainer) {
                this.elements.budgetCheckContainer.style.display = 'none';
            }
            this.hideError();
            this.enableSubmit();
        },

        showError(message) {
            if (this.elements.budgetErrorAlert && this.elements.budgetErrorMessage) {
                this.elements.budgetErrorMessage.innerHTML = `
                    ${message}<br><br>
                    <strong>What you can do:</strong>
                    <ul style="margin: var(--spacing-2) 0 0 var(--spacing-4);">
                        <li>Reduce the quantity or remove some items</li>
                        <li>Split this into multiple requisitions</li>
                        <li>Contact the Finance Manager for budget adjustment</li>
                    </ul>
                `;
                this.elements.budgetErrorAlert.style.display = 'flex';
            }
        },

        hideError() {
            if (this.elements.budgetErrorAlert) {
                this.elements.budgetErrorAlert.style.display = 'none';
            }
        },

        disableSubmit(reason) {
            if (this.elements.submitButton) {
                this.elements.submitButton.disabled = true;
                this.elements.submitButton.title = reason || 'Budget exceeded';
            }
        },

        enableSubmit() {
            if (this.elements.submitButton) {
                this.elements.submitButton.disabled = false;
                this.elements.submitButton.title = '';
            }
        },

        formatNumber(num) {
            return parseFloat(num).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => BudgetChecker.init());
    } else {
        BudgetChecker.init();
    }

})();
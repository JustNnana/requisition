</main>
    </div>
    
    <!-- Dasher Theme System -->
    <script src="<?php echo JS_URL; ?>/dasher-theme-system.js"></script>
    
    <!-- jQuery (if needed for plugins) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Common JavaScript -->
    <script>
        // Dropdown Toggle
        document.addEventListener('DOMContentLoaded', function() {
            // User dropdown
            const userDropdown = document.getElementById('userDropdown');
            const userDropdownMenu = document.getElementById('userDropdownMenu');
            
            if (userDropdown && userDropdownMenu) {
                userDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdownMenu.style.display = userDropdownMenu.style.display === 'none' ? 'block' : 'none';
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function() {
                    userDropdownMenu.style.display = 'none';
                });
                
                userDropdownMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
            
            // Mobile sidebar toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
                alerts.forEach(function(alert) {
                    alert.style.animation = 'slideUp 0.3s ease-out reverse';
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                });
            }, 5000);
            
            // Confirm delete actions
            const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
            deleteButtons.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    const message = this.getAttribute('data-confirm-delete') || 'Are you sure you want to delete this item?';
                    if (!confirm(message)) {
                        e.preventDefault();
                        return false;
                    }
                });
            });
            
            // Form submission loading state
            const forms = document.querySelectorAll('form[data-loading]');
            forms.forEach(function(form) {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.classList.contains('no-loading')) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                        
                        // Restore after 30 seconds if form doesn't redirect
                        setTimeout(function() {
                            submitBtn.classList.remove('loading');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }, 30000);
                    }
                });
            });
        });
        
        // Utility function for AJAX requests
        function ajaxRequest(url, method = 'GET', data = null) {
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open(method, url, true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            resolve(JSON.parse(xhr.responseText));
                        } catch(e) {
                            resolve(xhr.responseText);
                        }
                    } else {
                        reject(new Error('Request failed'));
                    }
                };
                
                xhr.onerror = function() {
                    reject(new Error('Network error'));
                };
                
                xhr.send(data ? JSON.stringify(data) : null);
            });
        }
        
        // Show toast notification
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type}`;
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = '9999';
            toast.style.minWidth = '300px';
            toast.style.animation = 'slideUp 0.3s ease-out';
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                ${message}
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(function() {
                toast.style.animation = 'slideUp 0.3s ease-out reverse';
                setTimeout(function() {
                    toast.remove();
                }, 300);
            }, 3000);
        }
    </script>
    
    <!-- Custom Page Scripts -->
    <?php if (isset($customJS)): ?>
        <script><?php echo $customJS; ?></script>
    <?php endif; ?>
</body>
</html>
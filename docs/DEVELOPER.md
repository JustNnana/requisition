# Developer Guide

## Overview

This guide provides information for developers working on the GateWey Requisition Management System, including code structure, conventions, and best practices.

## Development Environment Setup

### Prerequisites

1. **Local Server**:
   - XAMPP 8.0+ (recommended) or
   - WAMP, MAMP, or standalone Apache + PHP + MySQL

2. **Code Editor**:
   - VS Code (recommended) with extensions:
     - PHP Intelephense
     - PHP Debug
     - MySQL
     - GitLens

3. **Version Control**:
   - Git 2.30+
   - GitHub account (for collaboration)

4. **Additional Tools**:
   - Composer (for dependency management)
   - Node.js + npm (for frontend tools, optional)

### Local Setup

1. Clone repository to your web root:
   ```bash
   cd c:/xampp/htdocs
   git clone <repository-url> requisition
   cd requisition
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy and configure:
   ```bash
   cp config/config.example.php config/config.php
   # Edit config.php with your local settings
   ```

4. Import database:
   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p < database/sample_data.sql
   ```

5. Set permissions:
   ```bash
   chmod -R 755 uploads/
   ```

## Architecture

### MVC Pattern

The application follows a modified MVC (Model-View-Controller) pattern:

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │
       ▼
┌─────────────────┐
│  Page (View)    │ ← PHP files in root/subdirectories
│  - header.php   │
│  - navbar.php   │
│  - content      │
│  - footer.php   │
└────────┬────────┘
         │
         ▼
┌──────────────────┐
│ Classes (Model)  │ ← classes/
│  - Database.php  │
│  - User.php      │
│  - Requisition   │
│  - Budget.php    │
└──────────────────┘
```

**Controllers** are implicit - each page file acts as its own controller.

### Directory Structure

```
requisition/
├── classes/              # Models (Business Logic)
│   ├── Database.php     # Database connection singleton
│   ├── User.php         # User management
│   ├── Requisition.php  # Requisition operations
│   ├── Budget.php       # Budget operations
│   └── Session.php      # Session management
│
├── helpers/             # Utility Functions
│   └── utilities.php    # Common helper functions
│
├── includes/            # Common UI Components
│   ├── header.php       # Page header (HTML head, navbar)
│   ├── navbar.php       # Sidebar navigation
│   └── footer.php       # Page footer (scripts)
│
├── config/              # Configuration
│   └── config.php       # Main configuration file
│
├── assets/              # Static Assets
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   └── images/         # Images
│
├── api/                 # AJAX Endpoints
│   └── *.php           # API endpoints
│
└── [modules]/           # Feature Modules
    ├── requisitions/   # Requisition pages
    ├── finance/        # Finance pages
    ├── admin/          # Admin pages
    ├── dashboard/      # Dashboard pages
    ├── profile/        # Profile pages
    └── reports/        # Report pages
```

## Coding Standards

### PHP Coding Style

Follow PSR-12 coding standards with some customizations:

#### File Structure

```php
<?php
/**
 * GateWey Requisition Management System
 * [Brief description of file purpose]
 *
 * File: path/to/file.php
 * Purpose: Detailed description
 *
 * @author Your Name
 * @version 1.0.0
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

// Includes
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';

// Class or procedural code here
```

#### Naming Conventions

```php
// Classes: PascalCase
class UserManager {}
class RequisitionItem {}

// Methods/Functions: camelCase
public function getUserById($id) {}
function calculateTotalAmount($items) {}

// Variables: snake_case for simple, camelCase for complex
$user_id = 123;
$requisition_number = 'REQ-001';
$userManager = new UserManager();

// Constants: UPPER_SNAKE_CASE
define('MAX_FILE_SIZE', 5242880);
const DEFAULT_STATUS = 'pending';

// Database columns: snake_case
// created_at, updated_at, user_id, requisition_number
```

#### Class Example

```php
<?php

class Requisition
{
    private $db;
    private $table = 'requisitions';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get requisition by ID
     *
     * @param int $id Requisition ID
     * @return array|false Requisition data or false if not found
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT r.*, d.name as department_name, u.first_name, u.last_name
            FROM {$this->table} r
            LEFT JOIN departments d ON r.department_id = d.id
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Create new requisition
     *
     * @param array $data Requisition data
     * @return int|false New requisition ID or false on failure
     */
    public function create($data)
    {
        try {
            // Validation
            if (empty($data['purpose']) || empty($data['total_amount'])) {
                throw new Exception('Required fields missing');
            }

            // Generate requisition number
            $requisition_number = $this->generateRequisitionNumber();

            // Insert requisition
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table}
                (requisition_number, user_id, department_id, purpose, total_amount, status)
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");

            $stmt->bind_param(
                'siiss',
                $requisition_number,
                $data['user_id'],
                $data['department_id'],
                $data['purpose'],
                $data['total_amount']
            );

            if ($stmt->execute()) {
                return $this->db->insert_id;
            }

            return false;

        } catch (Exception $e) {
            error_log("Requisition creation error: " . $e->getMessage());
            return false;
        }
    }

    // Private helper methods
    private function generateRequisitionNumber()
    {
        $prefix = 'REQ';
        $date = date('Ymd');

        // Get count for today
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM {$this->table}
            WHERE DATE(created_at) = CURDATE()
        ");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $sequence = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$sequence}";
    }
}
```

### HTML/PHP Views

```php
<?php
// Page logic at top
$pageTitle = 'Requisition List';
$currentPage = 'list.php';
$currentDir = 'requisitions';

// Includes
include __DIR__ . '/../includes/header.php';
?>

<!-- HTML Content -->
<div class="content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="content-header">
            <div class="content-header-left">
                <h1 class="content-title">
                    <i class="fas fa-file-alt me-2"></i>
                    <?php echo $pageTitle; ?>
                </h1>
            </div>
        </div>

        <!-- Content -->
        <div class="row">
            <?php if (!empty($requisitions)): ?>
                <?php foreach ($requisitions as $req): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5><?php echo htmlspecialchars($req['requisition_number']); ?></h5>
                                <p><?php echo htmlspecialchars($req['purpose']); ?></p>
                                <span class="badge bg-<?php echo getStatusColor($req['status']); ?>">
                                    <?php echo getStatusLabel($req['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No requisitions found.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

### JavaScript Style

```javascript
/**
 * Requisition form validation
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('requisition-form');

    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                showError('Please fill in all required fields');
                return false;
            }
        });
    }

    /**
     * Validate requisition form
     * @returns {boolean}
     */
    function validateForm() {
        const purpose = document.getElementById('purpose').value.trim();
        const amount = parseFloat(document.getElementById('total_amount').value);

        if (purpose === '' || amount <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Show error message
     * @param {string} message
     */
    function showError(message) {
        // Implementation
    }
});
```

### CSS Style

```css
/* Component: Requisition Card */
.requisition-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    padding: var(--spacing-6);
    transition: var(--transition-normal);
}

.requisition-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.requisition-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-4);
}

.requisition-card__title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}
```

## Database Operations

### Using Prepared Statements

**Always** use prepared statements to prevent SQL injection:

```php
// GOOD ✓
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();

// BAD ✗ - SQL Injection vulnerability!
$result = $db->query("SELECT * FROM users WHERE email = '$email'");
```

### Transaction Example

```php
public function processPayment($requisitionId, $paymentData)
{
    $this->db->begin_transaction();

    try {
        // Update requisition
        $stmt = $this->db->prepare("
            UPDATE requisitions
            SET status = 'paid',
                payment_method = ?,
                payment_reference = ?,
                payment_date = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param('ssi',
            $paymentData['method'],
            $paymentData['reference'],
            $requisitionId
        );
        $stmt->execute();

        // Update budget
        $stmt = $this->db->prepare("
            UPDATE budgets
            SET spent_amount = spent_amount + ?
            WHERE department_id = ? AND fiscal_year = YEAR(CURDATE())
        ");
        $stmt->bind_param('di', $paymentData['amount'], $departmentId);
        $stmt->execute();

        $this->db->commit();
        return true;

    } catch (Exception $e) {
        $this->db->rollback();
        error_log("Payment processing error: " . $e->getMessage());
        return false;
    }
}
```

## Security Best Practices

### 1. Input Validation

```php
// Validate and sanitize user input
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Example usage
$email = $_POST['email'] ?? '';
if (!validateEmail($email)) {
    Session::setFlash('error', 'Invalid email address');
    redirect('back');
}
```

### 2. Output Escaping

```php
// ALWAYS escape output in HTML
<h1><?php echo htmlspecialchars($pageTitle); ?></h1>

// For JavaScript
<script>
const userName = <?php echo json_encode($user['name']); ?>;
</script>

// For URLs
<a href="view.php?id=<?php echo urlencode($id); ?>">View</a>
```

### 3. CSRF Protection

```php
// Include in all forms
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
    <!-- form fields -->
</form>

// Validate in processing scripts
if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    Session::setFlash('error', 'Invalid request');
    redirect('back');
}
```

### 4. File Upload Security

```php
function validateFileUpload($file) {
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    $maxSize = 5242880; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    return ['success' => true];
}
```

## Common Helper Functions

### utilities.php

```php
/**
 * Encrypt ID for URL
 * @param int $id
 * @return string Encrypted string
 */
function encrypt_id($id) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($id, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
    return base64_encode(json_encode(['iv' => base64_encode($iv), 'value' => $encrypted]));
}

/**
 * Decrypt ID from URL
 * @param string $encrypted
 * @return int|false Decrypted ID or false
 */
function decrypt_id($encrypted) {
    $data = json_decode(base64_decode($encrypted), true);
    if (!$data || !isset($data['iv']) || !isset($data['value'])) {
        return false;
    }

    $iv = base64_decode($data['iv']);
    $decrypted = openssl_decrypt($data['value'], 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);

    return filter_var($decrypted, FILTER_VALIDATE_INT);
}

/**
 * Build encrypted URL
 * @param string $baseUrl
 * @param int $id
 * @param array $params Additional parameters
 * @return string Complete URL
 */
function build_encrypted_url($baseUrl, $id, $params = []) {
    $params['id'] = encrypt_id($id);
    return $baseUrl . '?' . http_build_query($params);
}

/**
 * Get encrypted ID from request
 * @return int|false
 */
function get_encrypted_id() {
    $encrypted = $_GET['id'] ?? '';
    return decrypt_id($encrypted);
}

/**
 * Redirect to URL
 * @param string $url
 */
function redirect($url) {
    if ($url === 'back') {
        $url = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    }

    if (!headers_sent()) {
        header("Location: $url");
        exit;
    }
}

/**
 * Format currency
 * @param float $amount
 * @return string
 */
function format_currency($amount) {
    return '₦' . number_format($amount, 2);
}

/**
 * Get status badge color
 * @param string $status
 * @return string Bootstrap color class
 */
function getStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'approved_by_line_manager' => 'info',
        'approved_by_md' => 'info',
        'approved_for_payment' => 'primary',
        'paid' => 'success',
        'completed' => 'success',
        'rejected' => 'danger',
        'cancelled' => 'secondary'
    ];

    return $colors[$status] ?? 'secondary';
}
```

## Testing

### Manual Testing Checklist

Before committing code:

- [ ] Test all user roles
- [ ] Test form validation
- [ ] Test error handling
- [ ] Test on different browsers
- [ ] Test on mobile devices
- [ ] Check for SQL injection vulnerabilities
- [ ] Check for XSS vulnerabilities
- [ ] Verify CSRF protection
- [ ] Test file uploads
- [ ] Check email notifications

### Browser Testing

Test on:
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest, if possible)
- Mobile browsers (Chrome Android, Safari iOS)

## Git Workflow

### Branch Strategy

```
main (production)
  └── development
       ├── feature/user-management
       ├── feature/budget-reports
       └── bugfix/payment-validation
```

### Commit Messages

Follow conventional commits:

```
feat: Add budget export to Excel
fix: Fix requisition approval notification
docs: Update API documentation
style: Format code according to PSR-12
refactor: Simplify budget calculation logic
test: Add validation tests for user creation
chore: Update dependencies
```

### Pull Request Process

1. Create feature branch
2. Make changes
3. Test thoroughly
4. Commit with clear messages
5. Push to remote
6. Create Pull Request
7. Code review
8. Merge to development
9. Test on staging
10. Merge to main

## Debugging

### Enable Debug Mode

In `config/config.php`:

```php
define('APP_DEBUG', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Error Logging

```php
// Log errors
error_log("Error in payment processing: " . $e->getMessage());

// View logs
// XAMPP: C:\xampp\apache\logs\error.log
// Linux: /var/log/apache2/error.log
```

### Database Query Debugging

```php
// Echo SQL query
echo $stmt->sqlstate;
print_r($stmt->error_list);

// Use query logging
$db->query("SET profiling = 1");
// ... run queries
$result = $db->query("SHOW PROFILES");
```

## Performance Optimization

### Query Optimization

```php
// BAD - N+1 Query Problem
foreach ($requisitions as $req) {
    $user = getUserById($req['user_id']);
}

// GOOD - Single JOIN Query
$requisitions = getRequisitionsWithUsers();
```

### Caching

```php
// Cache expensive queries
if (!isset($_SESSION['departments_cache'])) {
    $_SESSION['departments_cache'] = $department->getAll();
    $_SESSION['cache_time'] = time();
}

// Invalidate after 1 hour
if (time() - $_SESSION['cache_time'] > 3600) {
    unset($_SESSION['departments_cache']);
}
```

## Resources

- [PHP Manual](https://www.php.net/manual/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [MDN Web Docs](https://developer.mozilla.org/)
- [Bootstrap 5 Docs](https://getbootstrap.com/docs/5.0/)
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)

---

**Last Updated**: December 20, 2025

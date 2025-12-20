# URL ID Encryption Guide

This guide explains how to use the URL encryption system to hide IDs in your application URLs.

## Overview

The `UrlEncryption` class provides secure two-way encryption for IDs in URLs using AES-256-CBC encryption. This prevents users from seeing or manipulating numeric IDs in URLs.

**Before:** `view.php?id=123`
**After:** `view.php?id=eyJpdiI6IkR3VnhMWFE...` (encrypted)

## Setup

The encryption system is already configured and ready to use. The encryption key is defined in `config/config.php`:

```php
define('ENCRYPTION_KEY', 'your-secret-encryption-key-change-this-in-production-2024');
```

**IMPORTANT:** Change this key to a secure random value in production! Generate a new key using:
```bash
openssl rand -base64 32
```

## Usage Examples

### 1. Basic Encryption/Decryption

#### Encrypt an ID
```php
$requisitionId = 123;
$encryptedId = encrypt_id($requisitionId);
// Result: "eyJpdiI6IkR3VnhMWFE..."
```

#### Decrypt an ID
```php
$encryptedId = $_GET['id'];
$originalId = decrypt_id($encryptedId);
// Result: 123 (integer) or false on failure
```

### 2. Building URLs with Encrypted IDs

#### Method 1: Using build_encrypted_url()
```php
// Simple URL with encrypted ID
$url = build_encrypted_url('requisitions/view.php', 123);
// Result: requisitions/view.php?id=eyJpdiI6IkR3VnhMWFE...

// With custom parameter name
$url = build_encrypted_url('requisitions/view.php', 123, 'req_id');
// Result: requisitions/view.php?req_id=eyJpdiI6IkR3VnhMWFE...

// With additional parameters
$url = build_encrypted_url('requisitions/view.php', 123, 'id', [
    'action' => 'approve',
    'return' => 'dashboard'
]);
// Result: requisitions/view.php?id=eyJpdiI6IkR3VnhMWFE...&action=approve&return=dashboard
```

#### Method 2: Manual encryption
```php
$encryptedId = encrypt_id($requisitionId);
$url = "requisitions/view.php?id=" . urlencode($encryptedId);
```

### 3. Retrieving Encrypted IDs from Requests

#### Method 1: Using get_encrypted_id()
```php
// Get from GET request (default)
$requisitionId = get_encrypted_id();
if ($requisitionId === false) {
    die('Invalid or missing ID');
}

// Get from POST request
$requisitionId = get_encrypted_id('id', 'POST');

// Get with custom parameter name
$userId = get_encrypted_id('user_id');
```

#### Method 2: Manual decryption
```php
if (isset($_GET['id'])) {
    $requisitionId = decrypt_id($_GET['id']);
    if ($requisitionId === false) {
        die('Invalid ID');
    }
}
```

### 4. Real-World Examples

#### Example 1: Requisition List with Encrypted View Links
```php
// File: requisitions/list.php
<?php
$requisitions = $requisitionObj->getAll();

foreach ($requisitions as $req) {
    $viewUrl = build_encrypted_url('requisitions/view.php', $req['id']);
    $editUrl = build_encrypted_url('requisitions/edit.php', $req['id']);
    $deleteUrl = build_encrypted_url('requisitions/delete.php', $req['id']);

    echo '<tr>';
    echo '<td>' . e($req['requisition_number']) . '</td>';
    echo '<td><a href="' . $viewUrl . '">View</a></td>';
    echo '<td><a href="' . $editUrl . '">Edit</a></td>';
    echo '<td><a href="' . $deleteUrl . '" onclick="return confirm(\'Delete?\')">Delete</a></td>';
    echo '</tr>';
}
?>
```

#### Example 2: View Requisition Page
```php
// File: requisitions/view.php
<?php
define('APP_ACCESS', true);
require_once '../config/config.php';

// Get and decrypt the requisition ID
$requisitionId = get_encrypted_id();

if ($requisitionId === false) {
    Session::setFlashMessage('Invalid requisition ID', 'error');
    redirect('requisitions/list.php');
}

// Fetch requisition
$requisition = $requisitionObj->getById($requisitionId);

if (!$requisition) {
    Session::setFlashMessage('Requisition not found', 'error');
    redirect('requisitions/list.php');
}

// Display requisition details
?>
<h1>Requisition: <?= e($requisition['requisition_number']) ?></h1>

<!-- Edit button with encrypted ID -->
<a href="<?= build_encrypted_url('requisitions/edit.php', $requisitionId) ?>" class="btn">Edit</a>
```

#### Example 3: Edit Form with Hidden Encrypted ID
```php
// File: requisitions/edit.php
<?php
define('APP_ACCESS', true);
require_once '../config/config.php';

// Get requisition ID from URL
$requisitionId = get_encrypted_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get ID from POST (if you're passing it in form)
    $requisitionId = get_encrypted_id('id', 'POST');

    // Update logic here...
}

$requisition = $requisitionObj->getById($requisitionId);
?>

<form method="POST">
    <!-- Pass encrypted ID in hidden field -->
    <input type="hidden" name="id" value="<?= e(encrypt_id($requisitionId)) ?>">

    <!-- Form fields -->
    <input type="text" name="title" value="<?= e($requisition['title']) ?>">

    <button type="submit">Update</button>
</form>
```

#### Example 4: AJAX with Encrypted IDs
```php
// File: api/approve-requisition.php
<?php
define('APP_ACCESS', true);
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid method'], 405);
}

// Get encrypted ID from POST
$requisitionId = get_encrypted_id('id', 'POST');

if ($requisitionId === false) {
    json_response(['success' => false, 'message' => 'Invalid requisition ID'], 400);
}

// Process approval...
$result = $approvalObj->approve($requisitionId, $userId, $comments);

json_response(['success' => true, 'message' => 'Approved successfully']);
?>
```

JavaScript:
```javascript
function approveRequisition(encryptedId) {
    fetch('api/approve-requisition.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + encodeURIComponent(encryptedId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
        }
    });
}
```

#### Example 5: Redirects with Encrypted IDs
```php
// After creating a requisition
$newRequisitionId = 456;
$viewUrl = build_encrypted_url('requisitions/view.php', $newRequisitionId);
redirect($viewUrl);

// Or manually
redirect('requisitions/view.php?id=' . urlencode(encrypt_id($newRequisitionId)));
```

## Using the UrlEncryption Class Directly

If you need more control, you can use the class methods directly:

```php
// Encrypt
$encrypted = UrlEncryption::encryptId(123);

// Decrypt
$original = UrlEncryption::decryptId($encrypted);

// Build URL
$url = UrlEncryption::buildUrl('page.php', 123, 'id', ['action' => 'view']);

// Get from request
$id = UrlEncryption::getIdFromRequest('id', 'GET');
```

## Security Best Practices

1. **Change the encryption key** in production to a secure random value
2. **Never expose** the encryption key in version control or client-side code
3. **Always validate** decrypted IDs exist in your database
4. **Check permissions** even with encrypted IDs - encryption is not authorization
5. **Use HTTPS** in production to prevent man-in-the-middle attacks
6. **Handle decryption failures** gracefully and redirect users appropriately

## Error Handling

Always check if decryption returns `false`:

```php
$id = decrypt_id($_GET['id']);

if ($id === false) {
    // Log the error
    error_log("Failed to decrypt ID: " . $_GET['id']);

    // Show user-friendly message
    Session::setFlashMessage('Invalid or expired link', 'error');
    redirect('dashboard.php');
}

// Verify ID exists in database
$item = $itemObj->getById($id);
if (!$item) {
    Session::setFlashMessage('Item not found', 'error');
    redirect('list.php');
}
```

## Migration Strategy

To convert existing pages to use encrypted IDs:

### Step 1: Update URL generation
```php
// OLD
echo '<a href="view.php?id=' . $id . '">View</a>';

// NEW
echo '<a href="' . build_encrypted_url('view.php', $id) . '">View</a>';
```

### Step 2: Update ID retrieval
```php
// OLD
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// NEW
$id = get_encrypted_id();
if ($id === false) {
    // Handle error
}
```

### Step 3: Support both formats temporarily (optional)
```php
// Support both encrypted and plain IDs during migration
if (isset($_GET['id'])) {
    $id = is_numeric($_GET['id'])
        ? (int)$_GET['id']  // Plain ID (legacy)
        : decrypt_id($_GET['id']);  // Encrypted ID (new)

    if ($id === false || $id <= 0) {
        die('Invalid ID');
    }
}
```

## Troubleshooting

**Problem:** Decryption returns `false`
- Check that ENCRYPTION_KEY is defined in config.php
- Verify the encrypted string wasn't modified or truncated
- Ensure the same encryption key is used for encrypt and decrypt

**Problem:** "Unreachable code" warning in config.php
- This is a false positive from your IDE, ignore it

**Problem:** URLs are too long
- This is normal - encrypted IDs are longer than plain IDs
- Each encrypted ID is approximately 40-60 characters
- Consider using shorter parameter names if needed

## Performance

Encryption/decryption is very fast (microseconds) and has negligible impact on performance. The encrypted strings are URL-safe and can be safely used in:
- Query parameters
- Form fields
- AJAX requests
- Redirects
- Email links (if needed)

## Summary

**Helper Functions:**
- `encrypt_id($id)` - Encrypt an ID
- `decrypt_id($encrypted)` - Decrypt back to original ID
- `build_encrypted_url($url, $id, $param, $otherParams)` - Build complete URL
- `get_encrypted_id($param, $method)` - Get ID from request

**Class Methods:**
- `UrlEncryption::encryptId($id)`
- `UrlEncryption::decryptId($encrypted)`
- `UrlEncryption::buildUrl($url, $id, $param, $otherParams)`
- `UrlEncryption::getIdFromRequest($param, $method)`

Use the helper functions for simplicity, or the class methods for more control.

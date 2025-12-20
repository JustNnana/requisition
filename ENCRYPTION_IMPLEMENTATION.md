# URL Encryption Implementation Guide

## Overview
This document outlines the complete URL encryption implementation for the GateWey Requisition Management System. All sensitive ID parameters in URLs are now encrypted to prevent enumeration attacks and enhance security.

## Implementation Date
December 20, 2025

## Encryption System

### Core Components

#### 1. Encryption Configuration
**File:** `config/config.php` (Line 67)
```php
define('ENCRYPTION_KEY', 'your-secret-encryption-key-change-this-in-production-2024');
```

**IMPORTANT:** Change this key in production! Generate a new key using:
```bash
openssl rand -base64 32
```

#### 2. Helper Functions
**File:** `helpers/utilities.php`

##### encrypt_id($id)
Encrypts a numeric ID into a secure, URL-safe string.
```php
$encryptedId = encrypt_id(123);
// Returns: eyJpdiI6IlhYWFhYWCIsInZhbHVlIjoiWVlZWVlZIn0=
```

##### decrypt_id($encryptedId)
Decrypts an encrypted ID back to its original value.
```php
$id = decrypt_id($encryptedString);
// Returns: 123 (or false on failure)
```

##### build_encrypted_url($baseUrl, $id, $additionalParams = [])
Builds a complete URL with an encrypted ID parameter.
```php
$url = build_encrypted_url('requisitions/view.php', 123);
// Returns: requisitions/view.php?id=eyJpdiI6...
```

##### get_encrypted_id()
Retrieves and decrypts the ID from the current request.
```php
$id = get_encrypted_id();
// Returns: 123 (or false if invalid/missing)
```

## Updated Files

### Requisition Module
All requisition-related pages now use encrypted URLs:

1. **requisitions/index.php** - List page with encrypted view/edit links
2. **requisitions/view.php** - Accepts encrypted ID parameter
3. **requisitions/edit.php** - Accepts encrypted ID parameter
4. **requisitions/upload-receipt.php** - Accepts encrypted ID parameter
5. **requisitions/actions/approve.php** - Processes encrypted IDs
6. **requisitions/actions/reject.php** - Processes encrypted IDs
7. **requisitions/actions/cancel.php** - Processes encrypted IDs
8. **requisitions/actions/mark-paid.php** - Processes encrypted IDs
9. **requisitions/actions/mark-completed.php** - Processes encrypted IDs

### Finance/Budget Module
All budget-related pages now use encrypted URLs:

1. **finance/budget/index.php** - Budget listing with encrypted links
2. **finance/budget/view-budget.php** - Accepts encrypted ID parameter
3. **finance/budget/edit-budget.php** - Accepts encrypted ID parameter
4. **finance/budget/set-budget.php** - Uses encrypted department IDs

### Dashboard Module
Dashboard pages updated to use encrypted URLs:

1. **dashboard/finance-manager.php** - Budget links use encryption
2. **dashboard/team-members.php** - No ID parameters (department-based)

### Email Templates
All email notification templates now send encrypted URLs:

1. **emails/templates/requisition-submitted.php** - View links encrypted
2. **emails/templates/requisition-approved.php** - View links encrypted
3. **emails/templates/requisition-rejected.php** - View/edit links encrypted
4. **emails/templates/requisition-cancelled.php** - View links encrypted
5. **emails/templates/requisition-paid.php** - Upload receipt link encrypted

### API Endpoints
API endpoints that accept IDs have been secured:

1. **api/check-budget.php** - Validates department access with role checks

## Usage Examples

### Creating a Link with Encrypted ID
```php
// Simple link
<a href="<?php echo build_encrypted_url('requisitions/view.php', $requisition['id']); ?>">
    View Requisition
</a>

// Link with additional parameters
<a href="<?php echo build_encrypted_url('requisitions/edit.php', $reqId, ['tab' => 'items']); ?>">
    Edit Items
</a>
```

### Retrieving Encrypted ID in Target Page
```php
// At the top of your page (e.g., requisitions/view.php)
$requisitionId = get_encrypted_id();

if ($requisitionId === false || $requisitionId <= 0) {
    Session::setFlash('error', 'Invalid requisition ID.');
    redirect('list.php');
    exit;
}

// Use the decrypted ID safely
$requisition = new Requisition();
$reqData = $requisition->getById($requisitionId);
```

### Form Actions with Encrypted IDs
```php
<form method="POST" action="<?php echo BASE_URL; ?>/requisitions/actions/approve.php">
    <input type="hidden" name="requisition_id" value="<?php echo encrypt_id($requisition['id']); ?>">
    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
    <button type="submit" class="btn btn-success">Approve</button>
</form>
```

### Processing Encrypted IDs in Action Files
```php
// In action files (e.g., requisitions/actions/approve.php)
$encryptedId = isset($_POST['requisition_id']) ? $_POST['requisition_id'] : '';
$requisitionId = decrypt_id($encryptedId);

if ($requisitionId === false || $requisitionId <= 0) {
    Session::setFlash('error', 'Invalid requisition ID.');
    redirect(BASE_URL . '/requisitions/list.php');
    exit;
}
```

## Security Benefits

### 1. Prevents Enumeration Attacks
Without encryption:
- Attacker can guess: `/requisitions/view.php?id=1`, `/requisitions/view.php?id=2`, etc.
- Can systematically access all requisitions

With encryption:
- URLs look like: `/requisitions/view.php?id=eyJpdiI6IlhYWCIsInZhbHVlIjoiWVlZIn0=`
- IDs are not sequential or guessable
- Each encryption uses a unique IV (Initialization Vector)

### 2. Additional Access Control
The encryption works alongside existing permission checks:
```php
// Encryption prevents enumeration
$id = get_encrypted_id();

// Permission checks ensure authorization
if (!can_user_view_requisition($reqData)) {
    Session::setFlash('error', 'Access denied.');
    redirect('list.php');
    exit;
}
```

### 3. Email Link Security
Email links contain encrypted IDs, preventing:
- Link sharing leading to unauthorized access
- Email interception revealing sequential IDs

## .htaccess Configuration

The `.htaccess` file has been updated to support clean URLs while maintaining encryption:

```apache
# Redirect .php URLs to extensionless URLs (301 permanent redirect)
RewriteCond %{THE_REQUEST} ^[A-Z]{3,}\s([^.]+)\.php [NC]
RewriteRule ^ %1 [R=301,L]

# Add .php extension to URLs without extension
# This allows /requisitions/view instead of /requisitions/view.php
RewriteRule ^(.+)$ $1.php [L,QSA]
```

The `QSA` (Query String Append) flag ensures encrypted ID parameters are preserved.

## Testing Checklist

### Functional Testing
- [ ] Create new requisition - verify redirect uses encrypted ID
- [ ] View requisition - verify encrypted ID in URL works
- [ ] Edit requisition - verify encrypted ID parameter is accepted
- [ ] Approve/Reject requisition - verify form submission with encrypted ID
- [ ] Mark as paid - verify encrypted ID processing
- [ ] Upload receipt - verify encrypted ID in upload form
- [ ] View budget - verify encrypted budget ID works
- [ ] Edit budget - verify encrypted ID parameter is accepted

### Email Testing
- [ ] Submit requisition - verify email contains encrypted URL
- [ ] Approve requisition - verify notification email has encrypted URL
- [ ] Reject requisition - verify rejection email has encrypted edit URL
- [ ] Mark as paid - verify email receipt upload URL is encrypted
- [ ] Cancel requisition - verify cancellation email has encrypted URL

### Security Testing
- [ ] Try accessing with invalid encrypted ID - should show error
- [ ] Try accessing with plain numeric ID - should fail gracefully
- [ ] Try accessing another user's requisition with encrypted ID - should be denied by permission check
- [ ] Verify encrypted IDs are different each time (unique IV)

### Error Handling
- [ ] Missing ID parameter - should redirect with error message
- [ ] Invalid/corrupted encrypted ID - should redirect with error message
- [ ] Tampered encrypted ID - should fail decryption and redirect

## Backward Compatibility

**IMPORTANT:** This implementation breaks backward compatibility with old plain ID URLs.

### Migration Steps
1. All existing bookmarks with plain IDs will stop working
2. Users should be notified to use the application interface instead of bookmarked URLs
3. Email notifications sent before this update will have plain URLs (expired links)

### Handling Old URLs
Consider adding a temporary fallback (optional):
```php
// In view pages, check if plain ID was provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    // Redirect to encrypted URL
    $encryptedUrl = build_encrypted_url(basename($_SERVER['PHP_SELF']), $_GET['id']);
    redirect($encryptedUrl);
    exit;
}
```

## Performance Considerations

### Encryption Overhead
- AES-256-CBC encryption is fast (microseconds per operation)
- Minimal impact on page load times
- No database queries added for encryption/decryption

### Caching
- Encrypted URLs cannot be easily cached by proxies
- Each page load generates unique encrypted IDs for forms
- Consider this for high-traffic scenarios

## Troubleshooting

### Common Issues

#### Issue: "Invalid requisition ID" error
**Cause:** Encryption key mismatch or corrupted data
**Solution:**
- Verify `ENCRYPTION_KEY` is set correctly in `config/config.php`
- Check if the encrypted string was truncated or modified

#### Issue: Links in emails don't work
**Cause:** Email client may modify URLs
**Solution:**
- Test with different email clients
- Ensure encrypted string is URL-safe (base64_encode with URL-safe characters)

#### Issue: Form submission fails with encrypted ID
**Cause:** CSRF token or ID decryption failure
**Solution:**
- Verify CSRF token is included in form
- Check if `decrypt_id()` is being called on the encrypted value

## Future Enhancements

### Recommended Improvements
1. **Add expiry to encrypted IDs**: Time-limited URLs that expire after X hours
2. **User-specific encryption**: Include user session in encryption to prevent sharing
3. **Audit logging**: Log all decryption attempts for security monitoring
4. **Rate limiting**: Prevent brute-force decryption attempts

### Example: Time-Limited URLs
```php
function encrypt_id_with_expiry($id, $expiryHours = 24) {
    $data = [
        'id' => $id,
        'expires' => time() + ($expiryHours * 3600)
    ];
    return encrypt(json_encode($data));
}

function decrypt_id_with_expiry($encrypted) {
    $decrypted = decrypt($encrypted);
    if ($decrypted === false) return false;

    $data = json_decode($decrypted, true);
    if (!$data || !isset($data['id']) || !isset($data['expires'])) {
        return false;
    }

    if (time() > $data['expires']) {
        return false; // Expired
    }

    return $data['id'];
}
```

## Support

For issues or questions regarding the encryption implementation:
1. Check this documentation first
2. Review the helper functions in `helpers/utilities.php`
3. Test with the examples provided above
4. Verify your `ENCRYPTION_KEY` is set correctly

## Changelog

### Version 1.0 (December 20, 2025)
- Initial implementation of URL encryption
- Updated all requisition module pages
- Updated all budget module pages
- Updated all email templates
- Updated dashboard links
- Added comprehensive helper functions
- Created documentation

---

**Generated with Claude Code**

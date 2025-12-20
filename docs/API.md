# API Documentation

## Overview

The GateWey Requisition Management System includes internal API endpoints for AJAX requests and real-time data updates. These endpoints are primarily used by the frontend for dynamic functionality.

**Base URL**: `{APP_URL}/api/`

**Authentication**: All API endpoints require an active user session. Requests without valid session will return 401 Unauthorized.

## Common Response Format

### Success Response

```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        // Response data here
    }
}
```

### Error Response

```json
{
    "success": false,
    "message": "Error description",
    "error": "Detailed error message"
}
```

## Endpoints

### 1. Check Budget Availability

Check if a department has sufficient budget for a requisition.

**Endpoint**: `POST /api/check-budget.php`

**Request Body**:
```json
{
    "department_id": 5,
    "amount": 50000.00
}
```

**Response**:
```json
{
    "success": true,
    "has_budget": true,
    "data": {
        "allocated_amount": 1000000.00,
        "spent_amount": 450000.00,
        "available_amount": 550000.00,
        "requested_amount": 50000.00,
        "will_remain": 500000.00,
        "utilization_percentage": 45.0,
        "new_utilization_percentage": 50.0
    }
}
```

**Error Cases**:
- No budget set for department: `has_budget: false`
- Insufficient budget: `has_budget: false, message: "Insufficient budget"`
- Invalid department: `success: false`

---

### 2. Get Requisition Counts

Get count of pending items for badges and notifications.

**Endpoint**: `GET /api/get-requisition-counts.php`

**Response**:
```json
{
    "success": true,
    "pendingApprovals": 5,
    "pendingPayments": 3,
    "pendingReceipts": 2
}
```

**Notes**:
- Counts are role-specific
- Line Managers see only their department's approvals
- Finance staff see organization-wide payments/receipts

---

### 3. Get Department Budget

Get real-time budget information for a department.

**Endpoint**: `GET /api/get-department-budget.php?department_id={id}`

**Parameters**:
- `department_id` (required): Department ID

**Response**:
```json
{
    "success": true,
    "has_budget": true,
    "data": {
        "id": 12,
        "department_id": 5,
        "department_name": "Operations",
        "fiscal_year": 2025,
        "allocated_amount": 1000000.00,
        "spent_amount": 450000.00,
        "available_amount": 550000.00,
        "allocated_amount_formatted": "₦1,000,000.00",
        "spent_amount_formatted": "₦450,000.00",
        "available_amount_formatted": "₦550,000.00",
        "utilization_percentage": 45.0,
        "status": "healthy"
    }
}
```

**Budget Status Values**:
- `healthy`: < 70% utilized (green)
- `warning`: 70-90% utilized (yellow)
- `critical`: > 90% utilized (red)

---

### 4. Upload File

Handle file uploads (documents, receipts, invoices).

**Endpoint**: `POST /api/upload-file.php`

**Request**: `multipart/form-data`

**Form Fields**:
```
file: (binary file data)
type: "document" | "receipt" | "invoice"
requisition_id: (optional, for validation)
```

**Response**:
```json
{
    "success": true,
    "message": "File uploaded successfully",
    "data": {
        "filename": "receipt_REQ-20251220-0001_1703088000.pdf",
        "path": "uploads/receipts/receipt_REQ-20251220-0001_1703088000.pdf",
        "size": 256000,
        "type": "application/pdf"
    }
}
```

**Validation**:
- File size must be ≤ 5MB
- Allowed types: PDF, JPG, JPEG, PNG, DOC, DOCX
- Filename is sanitized and made unique

---

### 5. Get Requisition Status

Get detailed status information for a requisition.

**Endpoint**: `GET /api/get-requisition-status.php?id={encrypted_id}`

**Parameters**:
- `id` (required): Encrypted requisition ID

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 123,
        "requisition_number": "REQ-20251220-0001",
        "status": "approved_by_md",
        "status_label": "Approved by MD",
        "can_cancel": false,
        "can_edit": false,
        "can_approve": true,
        "current_approver": {
            "id": 8,
            "name": "John Doe",
            "role": "Finance Manager"
        },
        "timeline": [
            {
                "date": "2025-12-20 10:30:00",
                "action": "Submitted",
                "user": "Jane Smith"
            },
            {
                "date": "2025-12-20 14:15:00",
                "action": "Approved",
                "user": "Mike Johnson",
                "role": "Line Manager"
            }
        ]
    }
}
```

---

### 6. Get User Notifications

Get user-specific notifications (future implementation).

**Endpoint**: `GET /api/get-notifications.php`

**Response**:
```json
{
    "success": true,
    "unread_count": 3,
    "notifications": [
        {
            "id": 45,
            "type": "requisition_approved",
            "title": "Requisition Approved",
            "message": "Your requisition REQ-20251220-0001 has been approved",
            "link": "/requisitions/view?id=eyJpdiI6...",
            "read": false,
            "created_at": "2025-12-20 14:30:00"
        }
    ]
}
```

---

### 7. Export Data

Export data to various formats.

**Endpoint**: `GET /api/export.php`

**Parameters**:
- `type`: "requisitions" | "budgets" | "users"
- `format`: "excel" | "csv" | "pdf"
- `filters`: JSON string of filters

**Example**:
```
/api/export.php?type=requisitions&format=excel&filters={"status":"completed","from":"2025-01-01"}
```

**Response**: File download (binary data)

**Headers**:
```
Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
Content-Disposition: attachment; filename="requisitions_export_20251220.xlsx"
```

---

## Authentication & Authorization

### Session Requirements

All API endpoints check for:
1. Valid session ID
2. Active user account
3. Appropriate role permissions

### CSRF Protection

POST/PUT/DELETE requests must include CSRF token:

**Header**:
```
X-CSRF-Token: {token_value}
```

**Or in request body**:
```json
{
    "csrf_token": "{token_value}",
    // ... other data
}
```

Get CSRF token from:
```php
Session::getCsrfToken()
```

---

## Rate Limiting

**Current Implementation**: None

**Recommendations for Production**:
- Limit: 100 requests per minute per user
- Burst: 200 requests per minute
- Block after 5 consecutive failures

---

## Error Codes

| HTTP Code | Meaning | Description |
|-----------|---------|-------------|
| 200 | OK | Request successful |
| 400 | Bad Request | Invalid parameters or validation failure |
| 401 | Unauthorized | Not logged in or session expired |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation errors |
| 500 | Internal Server Error | Server-side error |

---

## Usage Examples

### JavaScript (Vanilla)

```javascript
// Check budget availability
async function checkBudget(departmentId, amount) {
    const response = await fetch('/api/check-budget.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('[name="csrf_token"]').value
        },
        body: JSON.stringify({
            department_id: departmentId,
            amount: amount
        })
    });

    const data = await response.json();

    if (data.success && data.has_budget) {
        console.log('Budget available:', data.data.available_amount);
    } else {
        console.log('Insufficient budget');
    }
}

// Get requisition counts
async function updateBadges() {
    const response = await fetch('/api/get-requisition-counts.php');
    const data = await response.json();

    if (data.success) {
        document.querySelector('.badge-approvals').textContent = data.pendingApprovals;
        document.querySelector('.badge-payments').textContent = data.pendingPayments;
    }
}
```

### jQuery

```javascript
// Upload file
$('#upload-form').on('submit', function(e) {
    e.preventDefault();

    var formData = new FormData(this);

    $.ajax({
        url: '/api/upload-file.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                alert('File uploaded: ' + response.data.filename);
            }
        },
        error: function() {
            alert('Upload failed');
        }
    });
});
```

### PHP (cURL)

```php
// Call API from backend
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, APP_URL . '/api/check-budget.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'department_id' => 5,
    'amount' => 50000
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-CSRF-Token: ' . $_SESSION['csrf_token']
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$data = json_decode($response, true);

curl_close($ch);

if ($data['success']) {
    // Budget is available
}
```

---

## Testing

### Test Endpoints

Use tools like:
- **Postman**: Import collection from `/docs/postman_collection.json`
- **cURL**: Command-line testing
- **Browser DevTools**: Network tab for AJAX debugging

### Sample cURL Requests

```bash
# Get requisition counts
curl -X GET "http://localhost/api/get-requisition-counts.php" \
  -H "Cookie: PHPSESSID=your_session_id"

# Check budget
curl -X POST "http://localhost/api/check-budget.php" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d '{"department_id":5,"amount":50000}'
```

---

## Future Enhancements

Planned API additions:

1. **RESTful User Management**
   - `GET /api/users` - List users
   - `POST /api/users` - Create user
   - `PUT /api/users/{id}` - Update user
   - `DELETE /api/users/{id}` - Delete user

2. **Webhooks**
   - Notify external systems of events
   - Configurable webhook URLs
   - Event types: requisition.approved, payment.processed, etc.

3. **GraphQL Endpoint**
   - Single endpoint for complex queries
   - Flexible data fetching
   - Reduced over-fetching

4. **OAuth2 Integration**
   - API access for external applications
   - Token-based authentication
   - Scoped permissions

---

**Last Updated**: December 20, 2025

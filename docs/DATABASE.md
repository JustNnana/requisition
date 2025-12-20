# Database Schema Documentation

## Overview

The GateWey Requisition Management System uses MySQL/MariaDB as its database management system. The database is designed with normalization principles while maintaining query performance.

## Database Information

- **Database Name**: `gateweyc_requisition`
- **Character Set**: `utf8mb4`
- **Collation**: `utf8mb4_unicode_ci`
- **Engine**: InnoDB (all tables)

## Entity Relationship Diagram

```
users ──┬── requisitions ──┬── requisition_items
        │                  └── requisition_approvals
        │
        └── departments ──── budgets
        │
        └── categories
        │
        └── sessions
```

## Tables

### 1. users

Stores all user account information.

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role_id INT NOT NULL,
    department_id INT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role_id),
    INDEX idx_department (department_id)
);
```

**Fields:**
- `id`: Primary key
- `email`: Unique email address for login
- `password`: Bcrypt hashed password
- `first_name`, `last_name`: User's name
- `role_id`: Foreign key to role (1=Super Admin, 2=MD, 3=Finance Manager, 4=Finance Member, 5=Line Manager, 6=Team Member)
- `department_id`: Foreign key to departments table (NULL for Super Admin/MD)
- `is_active`: Account status (1=active, 0=disabled)
- `created_at`: Account creation timestamp
- `updated_at`: Last update timestamp
- `last_login`: Last successful login

### 2. departments

Stores organizational departments.

```sql
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code)
);
```

**Fields:**
- `id`: Primary key
- `name`: Department name
- `code`: Unique department code
- `description`: Optional description
- `is_active`: Department status

### 3. categories

Stores requisition categories for classification.

```sql
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Fields:**
- `id`: Primary key
- `name`: Category name (e.g., "Office Supplies", "Travel", "Equipment")
- `description`: Optional description
- `is_active`: Category status

### 4. requisitions

Main requisition table storing requisition headers.

```sql
CREATE TABLE requisitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requisition_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    department_id INT NOT NULL,
    category_id INT DEFAULT NULL,
    purpose TEXT NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    current_approver_id INT DEFAULT NULL,
    payment_method VARCHAR(50) DEFAULT NULL,
    payment_reference VARCHAR(100) DEFAULT NULL,
    payment_date DATETIME DEFAULT NULL,
    invoice_path VARCHAR(255) DEFAULT NULL,
    receipt_path VARCHAR(255) DEFAULT NULL,
    rejection_reason TEXT DEFAULT NULL,
    cancelled_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (current_approver_id) REFERENCES users(id),
    INDEX idx_requisition_number (requisition_number),
    INDEX idx_user (user_id),
    INDEX idx_department (department_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);
```

**Fields:**
- `id`: Primary key
- `requisition_number`: Unique identifier (format: REQ-YYYYMMDD-XXXX)
- `user_id`: Requester user ID
- `department_id`: Department making the request
- `category_id`: Optional category classification
- `purpose`: Reason for requisition
- `total_amount`: Total amount requested
- `status`: Current status (pending, approved_by_line_manager, approved_by_md, approved_for_payment, paid, completed, rejected, cancelled)
- `current_approver_id`: User ID of current approver
- `payment_method`: Method of payment (Bank Transfer, Cheque, Cash)
- `payment_reference`: Payment reference number
- `payment_date`: Date payment was made
- `invoice_path`: Path to uploaded invoice/proof of payment
- `receipt_path`: Path to uploaded receipt
- `rejection_reason`: Reason if rejected
- `cancelled_at`: Timestamp if cancelled
- `completed_at`: Timestamp when completed

**Status Flow:**
1. `pending` → Initial state
2. `approved_by_line_manager` → Line Manager approved
3. `approved_by_md` → Managing Director approved
4. `approved_for_payment` → Finance Manager approved
5. `paid` → Finance Member processed payment
6. `completed` → Receipt uploaded
7. `rejected` → Rejected at any stage
8. `cancelled` → Cancelled by requester

### 5. requisition_items

Line items for each requisition.

```sql
CREATE TABLE requisition_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requisition_id INT NOT NULL,
    description TEXT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    total_price DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requisition_id) REFERENCES requisitions(id) ON DELETE CASCADE,
    INDEX idx_requisition (requisition_id)
);
```

**Fields:**
- `id`: Primary key
- `requisition_id`: Foreign key to requisitions
- `description`: Item description
- `quantity`: Number of units
- `unit_price`: Price per unit
- `total_price`: Calculated total (quantity × unit_price)

### 6. requisition_approvals

Tracks approval history for each requisition.

```sql
CREATE TABLE requisition_approvals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requisition_id INT NOT NULL,
    approver_id INT NOT NULL,
    action VARCHAR(20) NOT NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requisition_id) REFERENCES requisitions(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES users(id),
    INDEX idx_requisition (requisition_id),
    INDEX idx_approver (approver_id)
);
```

**Fields:**
- `id`: Primary key
- `requisition_id`: Foreign key to requisitions
- `approver_id`: User who took action
- `action`: Action taken (approved, rejected, cancelled)
- `comments`: Optional comments
- `created_at`: When action was taken

### 7. budgets

Department budget allocations.

```sql
CREATE TABLE budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    fiscal_year YEAR NOT NULL,
    allocated_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    spent_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    available_amount DECIMAL(15,2) GENERATED ALWAYS AS (allocated_amount - spent_amount) STORED,
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    UNIQUE KEY unique_dept_year (department_id, fiscal_year),
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_department (department_id)
);
```

**Fields:**
- `id`: Primary key
- `department_id`: Foreign key to departments
- `fiscal_year`: Year for this budget (YYYY)
- `allocated_amount`: Total budget allocated
- `spent_amount`: Amount already spent
- `available_amount`: Calculated field (allocated - spent)
- `notes`: Optional notes
- `created_by`: User who created the budget
- `created_at`, `updated_at`: Timestamps

### 8. sessions

User session management (optional - can use file-based sessions).

```sql
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    payload TEXT NOT NULL,
    last_activity INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_last_activity (last_activity)
);
```

**Fields:**
- `id`: Session ID
- `user_id`: Associated user (if logged in)
- `ip_address`: Client IP address
- `user_agent`: Browser user agent
- `payload`: Serialized session data
- `last_activity`: Unix timestamp of last activity

### 9. audit_log

System audit trail (Super Admin only).

```sql
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT DEFAULT NULL,
    old_values TEXT DEFAULT NULL,
    new_values TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
);
```

**Fields:**
- `id`: Primary key
- `user_id`: User who performed the action
- `action`: Action performed (create, update, delete, login, logout)
- `entity_type`: Type of entity (user, requisition, budget, etc.)
- `entity_id`: ID of the entity
- `old_values`: JSON of old values (for updates/deletes)
- `new_values`: JSON of new values (for creates/updates)
- `ip_address`: Client IP
- `user_agent`: Browser user agent
- `created_at`: Timestamp

## Indexes

### Performance Indexes

The following indexes are created for optimal query performance:

- **users**: email, role_id, department_id
- **departments**: code
- **requisitions**: requisition_number, user_id, department_id, status, created_at
- **requisition_items**: requisition_id
- **requisition_approvals**: requisition_id, approver_id
- **budgets**: fiscal_year, department_id
- **sessions**: last_activity
- **audit_log**: user_id, entity_type/entity_id, created_at

## Common Queries

### Get User's Pending Requisitions

```sql
SELECT r.*, d.name as department_name, c.name as category_name
FROM requisitions r
LEFT JOIN departments d ON r.department_id = d.id
LEFT JOIN categories c ON r.category_id = c.id
WHERE r.user_id = ? AND r.status != 'completed' AND r.status != 'cancelled'
ORDER BY r.created_at DESC;
```

### Get Requisitions Pending Approval by User

```sql
SELECT r.*,
       u.first_name as requester_first_name,
       u.last_name as requester_last_name,
       d.name as department_name
FROM requisitions r
JOIN users u ON r.user_id = u.id
JOIN departments d ON r.department_id = d.id
WHERE r.current_approver_id = ?
ORDER BY r.created_at ASC;
```

### Get Department Budget Status

```sql
SELECT b.*,
       d.name as department_name,
       d.code as department_code,
       (b.spent_amount / b.allocated_amount * 100) as utilization_percentage
FROM budgets b
JOIN departments d ON b.department_id = d.id
WHERE b.department_id = ? AND b.fiscal_year = YEAR(CURDATE());
```

### Get Requisition with Items and Approvals

```sql
-- Main requisition
SELECT r.*,
       u.first_name, u.last_name, u.email,
       d.name as department_name,
       c.name as category_name
FROM requisitions r
JOIN users u ON r.user_id = u.id
JOIN departments d ON r.department_id = d.id
LEFT JOIN categories c ON r.category_id = c.id
WHERE r.id = ?;

-- Items
SELECT * FROM requisition_items WHERE requisition_id = ?;

-- Approval history
SELECT ra.*,
       u.first_name as approver_first_name,
       u.last_name as approver_last_name
FROM requisition_approvals ra
JOIN users u ON ra.approver_id = u.id
WHERE ra.requisition_id = ?
ORDER BY ra.created_at ASC;
```

## Data Integrity

### Foreign Key Constraints

- All foreign keys use `InnoDB` engine for referential integrity
- Cascade deletes for dependent data (requisition_items, requisition_approvals)
- Proper indexes on foreign key columns for performance

### Triggers

No triggers are currently implemented. Business logic is handled in the application layer for:
- Budget spent_amount updates
- Requisition status changes
- Approval workflow progression

### Stored Procedures

No stored procedures are currently used. All business logic is in PHP classes.

## Backup & Maintenance

### Backup Recommendations

1. **Daily Backups**: Automated daily backups of entire database
2. **Before Updates**: Manual backup before schema changes
3. **Retention**: Keep backups for at least 30 days

### Maintenance Tasks

```sql
-- Optimize tables monthly
OPTIMIZE TABLE users, requisitions, requisition_items,
               requisition_approvals, budgets, departments,
               categories, sessions, audit_log;

-- Analyze tables for query optimization
ANALYZE TABLE users, requisitions, budgets;

-- Clean old sessions (older than 30 days)
DELETE FROM sessions WHERE last_activity < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY));

-- Clean old audit logs (older than 1 year)
DELETE FROM audit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

## Migration Notes

### Version 1.0 → Future Versions

When updating the schema:
1. Always backup first
2. Use ALTER TABLE statements, not DROP/CREATE
3. Test on development environment
4. Document all changes
5. Update this documentation

---

**Last Updated**: December 20, 2025

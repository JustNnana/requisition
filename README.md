# GateWey Requisition Management System

A comprehensive web-based requisition and budget management system built with PHP, MySQL, and modern web technologies.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [User Roles](#user-roles)
- [Security Features](#security-features)
- [Documentation](#documentation)
- [Technology Stack](#technology-stack)
- [Support](#support)

## Overview

The GateWey Requisition Management System is a robust, role-based application designed to streamline the requisition approval process, budget management, and financial tracking for organizations. The system features a multi-level approval workflow, real-time budget tracking, and comprehensive reporting capabilities.

## Features

### Core Functionality

- **Multi-Level Approval Workflow**
  - Line Manager → Managing Director → Finance Manager approval chain
  - Real-time status tracking and notifications
  - Rejection with feedback and re-submission capability

- **Budget Management**
  - Department-level budget allocation
  - Real-time budget utilization tracking
  - Budget vs. actual spending reports
  - Organization-wide budget overview

- **Requisition Management**
  - Create and submit requisitions with multiple line items
  - Attach supporting documents
  - Track requisition status in real-time
  - Upload receipts after payment

- **Payment Processing**
  - Finance team payment workflow
  - Invoice/proof of payment uploads
  - Payment history tracking
  - Receipt verification

### Advanced Features

- **Email Notifications**
  - Automated notifications for all workflow stages
  - Customizable email templates
  - HTML-formatted professional emails

- **Reporting & Analytics**
  - Personal, department, and organization-wide reports
  - Budget utilization reports
  - Excel export functionality
  - Visual charts and graphs

- **Security**
  - URL encryption for all sensitive IDs
  - CSRF protection on all forms
  - Role-based access control (RBAC)
  - Session management with timeout
  - SQL injection prevention
  - XSS protection

- **User Experience**
  - Responsive design (mobile, tablet, desktop)
  - Dark mode support
  - Clean URLs (extensionless)
  - Dasher UI design system
  - Real-time badge updates
  - Keyboard shortcuts

## System Requirements

### Server Requirements

- **Web Server**: Apache 2.4+ with mod_rewrite enabled
- **PHP**: 7.4 or higher (8.0+ recommended)
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Memory**: Minimum 256MB RAM
- **Storage**: Minimum 500MB free space

### PHP Extensions Required

```
- mysqli
- pdo_mysql
- openssl
- json
- mbstring
- zip (for Excel exports)
- gd or imagick (for image handling)
- fileinfo
```

### Client Requirements

- Modern web browser (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)
- JavaScript enabled
- Cookies enabled
- Minimum screen resolution: 320px width

## Installation

### Step 1: Clone or Download

```bash
# Clone the repository
git clone https://github.com/your-org/gatewey-requisition.git

# Or download and extract the ZIP file
```

### Step 2: Database Setup

1. Create a new MySQL database:

```sql
CREATE DATABASE gateweyc_requisition CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the database schema:

```bash
mysql -u your_username -p gateweyc_requisition < database/schema.sql
```

3. (Optional) Import sample data:

```bash
mysql -u your_username -p gateweyc_requisition < database/sample_data.sql
```

### Step 3: Configure the Application

1. Copy the configuration template:

```bash
cp config/config.example.php config/config.php
```

2. Edit `config/config.php` with your settings:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gateweyc_requisition');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');

// Application URL
define('APP_URL', 'http://yourdomain.com');

// Encryption Key (IMPORTANT: Generate a new key!)
define('ENCRYPTION_KEY', 'your-secret-encryption-key-change-this');
```

3. Generate a secure encryption key:

```bash
openssl rand -base64 32
```

### Step 4: Set File Permissions

```bash
# Set proper permissions for upload directories
chmod 755 uploads/
chmod 755 uploads/invoices/
chmod 755 uploads/receipts/
chmod 755 uploads/documents/

# Ensure config is readable but not writable by web server
chmod 644 config/config.php
```

### Step 5: Configure Email (Optional)

Edit email settings in `config/config.php`:

```php
// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM', 'noreply@yourdomain.com');
define('SMTP_FROM_NAME', 'GateWey Requisition System');
```

### Step 6: Create Default Admin User

Run the setup script to create the default Super Admin:

```bash
php scripts/create_admin.php
```

Default credentials:
- **Email**: admin@gatewey.com
- **Password**: admin123 (Change immediately after first login!)

### Step 7: Test Installation

Visit your application URL and login with the admin credentials.

## Configuration

### Application Settings

Edit `config/config.php` to customize:

- **APP_NAME**: Your organization name
- **APP_DEBUG**: Enable/disable debug mode (false in production)
- **TIMEZONE**: Your timezone (e.g., 'Africa/Lagos')
- **SESSION_LIFETIME**: Session timeout in seconds (default: 7200)

### Email Templates

Email templates are located in `emails/templates/`:
- `requisition-submitted.php`
- `requisition-approved.php`
- `requisition-rejected.php`
- `requisition-cancelled.php`
- `requisition-paid.php`

Customize the HTML/CSS as needed while preserving PHP variables.

### Upload Limits

Configure in `config/config.php`:

```php
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);
```

## User Roles

### 1. Super Admin
**Capabilities:**
- Full system access
- User management (create, edit, delete)
- Department management
- Category management
- System settings configuration
- Email settings
- Database backup
- Audit log access

**Access:** `/admin/`

### 2. Managing Director (MD)
**Capabilities:**
- Create requisitions
- Approve requisitions (final approval)
- View all budgets
- View organization-wide reports
- Monitor all requisitions

**Access:** `/dashboard/`, `/requisitions/`, `/finance/budget/`, `/reports/`

### 3. Finance Manager
**Capabilities:**
- Review requisitions
- Process payments
- Upload invoices/proof of payment
- Set department budgets
- View all budgets
- View financial reports
- Manage pending receipts

**Access:** `/finance/`, `/finance/budget/`, `/reports/`

### 4. Finance Member
**Capabilities:**
- Process payments
- Upload payment documents
- View payment history
- Manage pending receipts
- View organization reports

**Access:** `/finance/`, `/reports/`

### 5. Line Manager
**Capabilities:**
- Create requisitions
- Approve team requisitions (first approval)
- View department budget
- View department reports
- Monitor team requisitions

**Access:** `/requisitions/`, `/dashboard/department-budget`, `/reports/department`

### 6. Team Member
**Capabilities:**
- Create requisitions
- View own requisitions
- Upload receipts
- View personal reports

**Access:** `/requisitions/`, `/reports/personal`

## Security Features

### 1. URL Encryption
All sensitive ID parameters are encrypted using AES-256-CBC encryption:
- Prevents enumeration attacks
- Protects against unauthorized access
- Each encryption uses unique IV
- See [ENCRYPTION_IMPLEMENTATION.md](ENCRYPTION_IMPLEMENTATION.md) for details

### 2. CSRF Protection
All forms include CSRF tokens:
```php
<input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
```

### 3. Input Validation & Sanitization
- All user inputs are validated
- SQL injection prevention via prepared statements
- XSS protection via htmlspecialchars()
- File upload validation

### 4. Authentication & Session Management
- Secure password hashing (bcrypt)
- Session timeout (configurable)
- Session hijacking prevention
- Remember me functionality (optional)

### 5. Access Control
- Role-based permissions
- Page-level access checks
- Data-level access validation
- Department-based restrictions

### 6. File Upload Security
- File type validation
- File size limits
- Unique filename generation
- Secure storage outside web root (recommended)

## Documentation

Additional documentation available:

- [Encryption Implementation Guide](ENCRYPTION_IMPLEMENTATION.md) - URL encryption system details
- [API Documentation](docs/API.md) - Internal API endpoints
- [Database Schema](docs/DATABASE.md) - Database structure and relationships
- [Deployment Guide](docs/DEPLOYMENT.md) - Production deployment instructions
- [Troubleshooting Guide](docs/TROUBLESHOOTING.md) - Common issues and solutions
- [Developer Guide](docs/DEVELOPER.md) - Code structure and development guidelines

## Technology Stack

### Backend
- **PHP 7.4+**: Core application logic
- **MySQL 5.7+**: Database management
- **PHPMailer**: Email functionality
- **PHPSpreadsheet**: Excel export functionality

### Frontend
- **HTML5/CSS3**: Markup and styling
- **Bootstrap 5**: UI framework
- **JavaScript (ES6+)**: Client-side functionality
- **Font Awesome 6**: Icons
- **Chart.js**: Data visualization
- **Dasher UI**: Custom design system

### Architecture
- **MVC Pattern**: Model-View-Controller separation
- **OOP**: Object-oriented programming
- **Prepared Statements**: SQL injection prevention
- **RESTful Principles**: API design

### Security
- **AES-256-CBC**: URL encryption
- **bcrypt**: Password hashing
- **CSRF Tokens**: Cross-site request forgery protection
- **XSS Protection**: Output sanitization

## Project Structure

```
requisition/
├── admin/                  # Super Admin pages
│   ├── users/             # User management
│   ├── departments/       # Department management
│   ├── categories/        # Category management
│   └── settings/          # System settings
├── api/                   # API endpoints
├── assets/                # Static assets
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── images/           # Images
├── classes/              # PHP classes (Models)
│   ├── Database.php      # Database connection
│   ├── User.php          # User model
│   ├── Requisition.php   # Requisition model
│   ├── Budget.php        # Budget model
│   └── Session.php       # Session management
├── config/               # Configuration files
│   └── config.php        # Main configuration
├── dashboard/            # Role-based dashboards
├── emails/               # Email functionality
│   └── templates/        # Email templates
├── finance/              # Finance pages
│   └── budget/          # Budget management
├── helpers/              # Helper functions
│   └── utilities.php    # Common utilities
├── includes/             # Common includes
│   ├── header.php       # Page header
│   ├── navbar.php       # Navigation
│   └── footer.php       # Page footer
├── profile/              # User profile pages
├── reports/              # Reporting pages
├── requisitions/         # Requisition pages
│   └── actions/         # Requisition actions
├── uploads/              # File uploads
│   ├── documents/       # Supporting documents
│   ├── invoices/        # Payment invoices
│   └── receipts/        # Payment receipts
├── .htaccess            # Apache configuration
├── index.php            # Entry point (login)
└── README.md            # This file
```

## Key Features Explained

### Approval Workflow

1. **Team Member/Line Manager** creates requisition
2. **Line Manager** receives notification and approves/rejects
3. **Managing Director** receives notification and approves/rejects
4. **Finance Manager** reviews and approves for payment
5. **Finance Member** processes payment and uploads invoice
6. **Requester** uploads receipt
7. Requisition marked as **Completed**

### Budget Tracking

- Real-time budget utilization
- Automatic calculation of available balance
- Budget overspend warnings
- Historical budget comparison
- Department and organization-wide views

### Notification System

Automated emails sent for:
- New requisition submitted
- Requisition approved
- Requisition rejected
- Payment processed
- Receipt required

### Excel Export

Professional Excel exports with:
- Formatted headers with organization branding
- Color-coded data
- Proper number/currency formatting
- Auto-sized columns
- Multiple worksheets for complex reports

## Support

### Getting Help

- **Documentation**: Check the `docs/` folder for detailed guides
- **Issues**: Report bugs at [GitHub Issues](https://github.com/your-org/gatewey-requisition/issues)
- **Email**: support@gatewey.com

### Frequently Asked Questions

**Q: How do I reset the admin password?**
A: Run `php scripts/reset_password.php` from the command line.

**Q: Can I customize the approval workflow?**
A: Yes, modify the approval chain in `classes/Requisition.php` and `config/config.php`.

**Q: How do I backup the database?**
A: Use the built-in backup feature in Admin → System → Database Backup.

**Q: Can I add custom fields to requisitions?**
A: Yes, modify the database schema and update the forms in `requisitions/create.php`.

## License

Copyright © 2025 GateWey Solutions. All rights reserved.

This is proprietary software. Unauthorized copying, modification, distribution, or use of this software, via any medium, is strictly prohibited.

## Credits

Developed by GateWey Solutions
- **Lead Developer**: [Your Name]
- **UI/UX Design**: Dasher UI Design System
- **Version**: 1.0.0
- **Last Updated**: December 20, 2025

---

**Generated with Claude Code**

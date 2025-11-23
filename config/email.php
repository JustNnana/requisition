<?php
/**
 * GateWey Requisition Management System
 * Email Configuration
 * 
 * File: config/email.php
 * Purpose: SMTP email settings and configuration
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

/**
 * SMTP CONFIGURATION
 */

// SMTP Settings
define('SMTP_ENABLED', true); // Set to false to disable email sending
define('SMTP_HOST', 'smtp.gmail.com'); // SMTP server (e.g., smtp.gmail.com, smtp.office365.com)
define('SMTP_PORT', 587); // SMTP port (587 for TLS, 465 for SSL, 25 for non-encrypted)
define('SMTP_SECURE', 'tls'); // Encryption: 'tls', 'ssl', or false for no encryption
define('SMTP_AUTH', true); // Enable SMTP authentication
define('SMTP_USERNAME', 'your-email@gmail.com'); // SMTP username
define('SMTP_PASSWORD', 'your-app-password'); // SMTP password or app-specific password

// Email From Details
define('MAIL_FROM_EMAIL', 'noreply@gatewey.com');
define('MAIL_FROM_NAME', 'GateWey Requisitions');

// Email Reply-To
define('MAIL_REPLYTO_EMAIL', 'support@gatewey.com');
define('MAIL_REPLYTO_NAME', 'GateWey Support');

// Email Settings
define('MAIL_CHARSET', 'UTF-8');
define('MAIL_ENCODING', '8bit'); // Options: 8bit, 7bit, binary, base64, quoted-printable
define('MAIL_WORDWRAP', 70); // Word wrap for email body

// Email Delivery Settings
define('MAIL_PRIORITY', 3); // 1 = High, 3 = Normal, 5 = Low
define('ENABLE_HTML_EMAIL', true); // Send HTML emails
define('ENABLE_PLAIN_TEXT_FALLBACK', true); // Include plain text version for HTML emails

// Email Queue Settings
define('EMAIL_QUEUE_ENABLED', false); // Enable email queue (for production)
define('EMAIL_QUEUE_BATCH_SIZE', 50); // Number of emails to send per batch
define('EMAIL_QUEUE_DELAY', 2); // Delay between batches in seconds

// Email Retry Settings
define('EMAIL_MAX_RETRY_ATTEMPTS', 3);
define('EMAIL_RETRY_DELAY', 300); // 5 minutes in seconds

// Email Logging
define('EMAIL_LOG_ENABLED', true);
define('EMAIL_LOG_PATH', LOGS_PATH . '/email.log');
define('EMAIL_LOG_SUCCESS', true); // Log successful emails
define('EMAIL_LOG_FAILURES', true); // Log failed emails

// Email Templates Path
define('EMAIL_TEMPLATES_PATH', ROOT_PATH . '/emails/templates');

// Email Testing
define('EMAIL_TEST_MODE', false); // Set to true to prevent actual email sending (logs only)
define('EMAIL_TEST_RECIPIENT', 'test@example.com'); // Override all recipients in test mode

/**
 * EMAIL NOTIFICATION SETTINGS
 */

// Notification Types to Send
define('SEND_REQUISITION_SUBMITTED_EMAIL', true);
define('SEND_REQUISITION_APPROVED_EMAIL', true);
define('SEND_REQUISITION_REJECTED_EMAIL', true);
define('SEND_REQUISITION_PAID_EMAIL', true);
define('SEND_RECEIPT_UPLOADED_EMAIL', true);
define('SEND_ACTION_REQUIRED_EMAIL', true);
define('SEND_REQUISITION_CANCELLED_EMAIL', true);

// Email Subject Prefixes
define('EMAIL_SUBJECT_PREFIX', '[GateWey Requisitions]');

// Email Subjects
define('EMAIL_SUBJECTS', [
    NOTIF_REQUISITION_SUBMITTED => 'New Requisition Submitted',
    NOTIF_REQUISITION_APPROVED => 'Requisition Approved',
    NOTIF_REQUISITION_REJECTED => 'Requisition Rejected',
    NOTIF_REQUISITION_PAID => 'Requisition Payment Processed',
    NOTIF_RECEIPT_UPLOADED => 'Receipt Uploaded for Requisition',
    NOTIF_ACTION_REQUIRED => 'Action Required on Requisition',
    NOTIF_REQUISITION_CANCELLED => 'Requisition Cancelled'
]);

// Notification Recipients Configuration
// Define who receives notifications for each action
define('NOTIFICATION_RECIPIENTS', [
    NOTIF_REQUISITION_SUBMITTED => [
        'approver', // Current approver
        'requester' // Confirmation to requester
    ],
    NOTIF_REQUISITION_APPROVED => [
        'requester', // Notify requester
        'next_approver' // Notify next approver if exists
    ],
    NOTIF_REQUISITION_REJECTED => [
        'requester', // Notify requester
        'approver' // CC to the one who rejected
    ],
    NOTIF_REQUISITION_PAID => [
        'requester', // Notify requester
        'finance_manager' // CC to finance manager
    ],
    NOTIF_RECEIPT_UPLOADED => [
        'line_manager', // If applicable
        'managing_director',
        'finance_manager',
        'finance_member' // The one who processed payment
    ],
    NOTIF_ACTION_REQUIRED => [
        'approver' // Current approver
    ],
    NOTIF_REQUISITION_CANCELLED => [
        'line_manager', // If applicable
        'managing_director',
        'finance_manager'
    ]
]);

// CC and BCC Settings
define('EMAIL_CC_ADMIN_ON_ALL', false); // CC admin on all emails
define('EMAIL_ADMIN_CC', 'admin@gatewey.com'); // Admin email for CC
define('EMAIL_BCC_ARCHIVE', false); // BCC all emails to archive
define('EMAIL_ARCHIVE_BCC', 'archive@gatewey.com'); // Archive email for BCC

/**
 * COMMON SMTP PROVIDER CONFIGURATIONS
 * Uncomment the provider you're using and update credentials
 */

/*
// Gmail Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password'); // Use App Password, not regular password
*/

/*
// Office 365 / Outlook Configuration
define('SMTP_HOST', 'smtp.office365.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'your-email@outlook.com');
define('SMTP_PASSWORD', 'your-password');
*/

/*
// Yahoo Mail Configuration
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'your-email@yahoo.com');
define('SMTP_PASSWORD', 'your-app-password');
*/

/*
// SendGrid Configuration
define('SMTP_HOST', 'smtp.sendgrid.net');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'apikey');
define('SMTP_PASSWORD', 'your-sendgrid-api-key');
*/

/*
// Mailgun Configuration
define('SMTP_HOST', 'smtp.mailgun.org');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'your-mailgun-username');
define('SMTP_PASSWORD', 'your-mailgun-password');
*/

/**
 * EMAIL HELPER FUNCTIONS
 */

/**
 * Get email subject with prefix
 * 
 * @param string $notificationType Notification type constant
 * @return string Complete email subject
 */
function get_email_subject($notificationType) {
    $subject = EMAIL_SUBJECTS[$notificationType] ?? 'Notification';
    return EMAIL_SUBJECT_PREFIX . ' ' . $subject;
}

/**
 * Get notification recipients for a type
 * 
 * @param string $notificationType Notification type constant
 * @return array Array of recipient types
 */
function get_notification_recipients($notificationType) {
    return NOTIFICATION_RECIPIENTS[$notificationType] ?? [];
}

/**
 * Check if email sending is enabled
 * 
 * @return bool
 */
function is_email_enabled() {
    return SMTP_ENABLED && !EMAIL_TEST_MODE;
}

/**
 * Check if notification type should be sent
 * 
 * @param string $notificationType Notification type constant
 * @return bool
 */
function should_send_notification($notificationType) {
    $settingMap = [
        NOTIF_REQUISITION_SUBMITTED => SEND_REQUISITION_SUBMITTED_EMAIL,
        NOTIF_REQUISITION_APPROVED => SEND_REQUISITION_APPROVED_EMAIL,
        NOTIF_REQUISITION_REJECTED => SEND_REQUISITION_REJECTED_EMAIL,
        NOTIF_REQUISITION_PAID => SEND_REQUISITION_PAID_EMAIL,
        NOTIF_RECEIPT_UPLOADED => SEND_RECEIPT_UPLOADED_EMAIL,
        NOTIF_ACTION_REQUIRED => SEND_ACTION_REQUIRED_EMAIL,
        NOTIF_REQUISITION_CANCELLED => SEND_REQUISITION_CANCELLED_EMAIL
    ];
    
    return $settingMap[$notificationType] ?? false;
}

/**
 * SETUP INSTRUCTIONS
 * 
 * For Gmail:
 * 1. Enable 2-Step Verification in your Google Account
 * 2. Generate an App Password: https://myaccount.google.com/apppasswords
 * 3. Use the App Password as SMTP_PASSWORD
 * 
 * For Office 365:
 * 1. Use your regular email and password
 * 2. Ensure SMTP is enabled in your Office 365 admin panel
 * 
 * For other providers:
 * Check your email provider's SMTP settings documentation
 */
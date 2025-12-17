<?php
/**
 * GateWey Requisition Management System
 * Email Sending Class (PHPMailer Wrapper)
 * 
 * File: classes/Mailer.php
 * Purpose: SMTP email handler using PHPMailer library
 */
// Load Composer autoloader if not already loaded
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    
    private $mailer;
    private $db;
    private $errors = [];
    
    /**
     * Constructor - Initialize PHPMailer
     */
    public function __construct() {
        if (!defined('SMTP_HOST')) {
            require_once __DIR__ . '/../config/email.php';
        }
        $this->db = Database::getInstance();
        $this->mailer = new PHPMailer(true);
        $this->configureSMTP();
    }
    
    /**
     * Configure SMTP settings
     */
    private function configureSMTP() {
        try {
            // Server settings
            if (APP_ENV === 'development') {
                $this->mailer->SMTPDebug = SMTP::DEBUG_OFF; // Disable debug output in production
            }
            
            $this->mailer->isSMTP();
            $this->mailer->Host       = SMTP_HOST;
            $this->mailer->SMTPAuth   = SMTP_AUTH;
            $this->mailer->Username   = SMTP_USERNAME;
            $this->mailer->Password   = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = SMTP_SECURE;
            $this->mailer->Port       = SMTP_PORT;
            
            // Timeout settings
            $this->mailer->Timeout    = 30;
            $this->mailer->SMTPKeepAlive = false;
            
            // Default sender
            $this->mailer->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $this->mailer->addReplyTo(MAIL_REPLYTO_EMAIL, MAIL_REPLYTO_NAME);
            
            // Email settings
            $this->mailer->isHTML(ENABLE_HTML_EMAIL);
            $this->mailer->CharSet = MAIL_CHARSET;
            $this->mailer->Encoding = MAIL_ENCODING;
            $this->mailer->WordWrap = MAIL_WORDWRAP;
            $this->mailer->Priority = MAIL_PRIORITY;
            
        } catch (Exception $e) {
            $this->errors[] = "SMTP Configuration Error: {$e->getMessage()}";
            $this->logError("SMTP Configuration Failed", $e->getMessage());
        }
    }
    
    /**
     * Send email
     * 
     * @param string|array $to Recipient email(s)
     * @param string $subject Email subject
     * @param string $htmlBody HTML email body
     * @param string $plainBody Plain text body (optional)
     * @param array $options Additional options (cc, bcc, attachments, replyTo)
     * @return array Result with success status and message
     */
    public function send($to, $subject, $htmlBody, $plainBody = '', $options = []) {
        try {
            // Check if email sending is enabled
            if (!SMTP_ENABLED) {
                return [
                    'success' => false,
                    'message' => 'Email sending is disabled in configuration.'
                ];
            }
            
            // Test mode - log but don't send
            if (EMAIL_TEST_MODE) {
                $this->logEmail($to, $subject, 'test', 'Email not sent (TEST MODE)');
                return [
                    'success' => true,
                    'message' => 'Email logged (test mode - not actually sent).'
                ];
            }
            
            // Clear any previous recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();
            $this->mailer->clearAttachments();
            $this->mailer->clearReplyTos();
            
            // Add recipients
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        // Just email address
                        $this->mailer->addAddress($name);
                    } else {
                        // Email and name
                        $this->mailer->addAddress($email, $name);
                    }
                }
            } else {
                $this->mailer->addAddress($to);
            }
            
            // Add CC recipients
            if (!empty($options['cc'])) {
                if (is_array($options['cc'])) {
                    foreach ($options['cc'] as $email => $name) {
                        if (is_numeric($email)) {
                            $this->mailer->addCC($name);
                        } else {
                            $this->mailer->addCC($email, $name);
                        }
                    }
                } else {
                    $this->mailer->addCC($options['cc']);
                }
            }
            
            // Add BCC recipients
            if (!empty($options['bcc'])) {
                if (is_array($options['bcc'])) {
                    foreach ($options['bcc'] as $email => $name) {
                        if (is_numeric($email)) {
                            $this->mailer->addBCC($name);
                        } else {
                            $this->mailer->addBCC($email, $name);
                        }
                    }
                } else {
                    $this->mailer->addBCC($options['bcc']);
                }
            }
            
            // Admin CC if enabled
            if (EMAIL_CC_ADMIN_ON_ALL && !empty(EMAIL_ADMIN_CC)) {
                $this->mailer->addCC(EMAIL_ADMIN_CC);
            }
            
            // Archive BCC if enabled
            if (EMAIL_BCC_ARCHIVE && !empty(EMAIL_ARCHIVE_BCC)) {
                $this->mailer->addBCC(EMAIL_ARCHIVE_BCC);
            }
            
            // Custom reply-to
            if (!empty($options['replyTo'])) {
                $this->mailer->clearReplyTos();
                if (is_array($options['replyTo'])) {
                    $this->mailer->addReplyTo($options['replyTo']['email'], $options['replyTo']['name'] ?? '');
                } else {
                    $this->mailer->addReplyTo($options['replyTo']);
                }
            }
            
            // Add attachments
            if (!empty($options['attachments'])) {
                foreach ($options['attachments'] as $attachment) {
                    if (is_array($attachment)) {
                        $this->mailer->addAttachment(
                            $attachment['path'],
                            $attachment['name'] ?? '',
                            $attachment['encoding'] ?? 'base64',
                            $attachment['type'] ?? '',
                            $attachment['disposition'] ?? 'attachment'
                        );
                    } else {
                        $this->mailer->addAttachment($attachment);
                    }
                }
            }
            
            // Set subject and body
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $htmlBody;
            
            // Add plain text alternative
            if (ENABLE_PLAIN_TEXT_FALLBACK) {
                $this->mailer->AltBody = !empty($plainBody) ? $plainBody : strip_tags($htmlBody);
            }
            
            // Send email
            $result = $this->mailer->send();
            
            if ($result) {
                // Log success
                if (EMAIL_LOG_SUCCESS) {
                    $this->logEmail($to, $subject, 'success', 'Email sent successfully');
                }
                
                return [
                    'success' => true,
                    'message' => 'Email sent successfully.'
                ];
            } else {
                throw new Exception('Email sending failed without specific error.');
            }
            
        } catch (Exception $e) {
            $errorMessage = "Email Error: {$this->mailer->ErrorInfo}";
            
            // Log failure
            if (EMAIL_LOG_FAILURES) {
                $this->logEmail($to, $subject, 'failed', $errorMessage);
            }
            
            $this->errors[] = $errorMessage;
            
            return [
                'success' => false,
                'message' => $errorMessage,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send email using template
     * 
     * @param string|array $to Recipient email(s)
     * @param string $subject Email subject
     * @param string $templateName Template name (without .php extension)
     * @param array $data Data to pass to template
     * @param array $options Additional options
     * @return array Result with success status
     */
    public function sendTemplate($to, $subject, $templateName, $data = [], $options = []) {
        try {
            // Load and render template
            $htmlBody = $this->renderTemplate($templateName, $data);
            
            if ($htmlBody === false) {
                return [
                    'success' => false,
                    'message' => "Template '{$templateName}' not found."
                ];
            }
            
            // Generate plain text version
            $plainBody = $this->htmlToPlainText($htmlBody);
            
            // Send email
            return $this->send($to, $subject, $htmlBody, $plainBody, $options);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Template rendering error: {$e->getMessage()}"
            ];
        }
    }
    
    /**
     * Render email template
     * 
     * @param string $templateName Template name
     * @param array $data Data for template
     * @return string|false Rendered HTML or false on failure
     */
    private function renderTemplate($templateName, $data = []) {
        $templatePath = EMAIL_TEMPLATES_PATH . '/' . $templateName . '.php';
        
        if (!file_exists($templatePath)) {
            $this->logError("Template not found", "Template: {$templateName}");
            return false;
        }
        
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        try {
            // Include template
            include $templatePath;
            
            // Get buffer contents
            $html = ob_get_clean();
            
            return $html;
            
        } catch (Exception $e) {
            ob_end_clean();
            $this->logError("Template rendering error", $e->getMessage());
            return false;
        }
    }
    
    /**
     * Convert HTML to plain text
     * 
     * @param string $html HTML content
     * @return string Plain text
     */
    private function htmlToPlainText($html) {
        // Remove style and script tags
        $text = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        $text = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $text);
        
        // Convert links to plain text
        $text = preg_replace('/<a\s+href="([^"]+)"[^>]*>(.*?)<\/a>/i', '$2 ($1)', $text);
        
        // Convert headers to uppercase
        $text = preg_replace('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/i', "\n\n" . strtoupper('$1') . "\n", $text);
        
        // Convert paragraphs and breaks
        $text = preg_replace('/<\/p>/i', "\n\n", $text);
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        
        // Strip remaining HTML tags
        $text = strip_tags($text);
        
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Clean up whitespace
        $text = preg_replace('/\n\s+\n/', "\n\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = trim($text);
        
        return $text;
    }
    
    /**
     * Log email to database
     * 
     * @param string|array $to Recipients
     * @param string $subject Email subject
     * @param string $status Status (success, failed, test)
     * @param string $message Log message
     */
    private function logEmail($to, $subject, $status, $message) {
        if (!EMAIL_LOG_ENABLED) {
            return;
        }
        
        try {
            // Convert recipients to string
            $recipients = is_array($to) ? implode(', ', array_keys($to)) : $to;
            
            // Log to file
            $logMessage = sprintf(
                "[%s] Status: %s | To: %s | Subject: %s | Message: %s\n",
                date('Y-m-d H:i:s'),
                strtoupper($status),
                $recipients,
                $subject,
                $message
            );
            
            error_log($logMessage, 3, EMAIL_LOG_PATH);
            
        } catch (Exception $e) {
            // Silent fail - don't break email sending if logging fails
            error_log("Email logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Log error
     * 
     * @param string $title Error title
     * @param string $details Error details
     */
    private function logError($title, $details) {
        $logMessage = sprintf(
            "[%s] MAILER ERROR - %s: %s\n",
            date('Y-m-d H:i:s'),
            $title,
            $details
        );
        
        error_log($logMessage, 3, EMAIL_LOG_PATH);
    }
    
    /**
     * Get last error
     * 
     * @return string Last error message
     */
    public function getLastError() {
        return end($this->errors);
    }
    
    /**
     * Get all errors
     * 
     * @return array All error messages
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Test SMTP connection
     * 
     * @return array Result with success status
     */
    public function testConnection() {
        try {
            $this->mailer->SMTPDebug = SMTP::DEBUG_CONNECTION;
            
            // Try to connect
            if (!$this->mailer->smtpConnect()) {
                return [
                    'success' => false,
                    'message' => 'Failed to connect to SMTP server.',
                    'error' => $this->mailer->ErrorInfo
                ];
            }
            
            // Close connection
            $this->mailer->smtpClose();
            
            return [
                'success' => true,
                'message' => 'SMTP connection successful.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'SMTP connection test failed.',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send test email
     * 
     * @param string $toEmail Test recipient email
     * @return array Result with success status
     */
    public function sendTestEmail($toEmail) {
        $subject = EMAIL_SUBJECT_PREFIX . ' Test Email';
        
        $htmlBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;'>
                    <h2 style='margin: 0;'>Email Configuration Test</h2>
                </div>
                <div style='background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px;'>
                    <p>This is a test email from <strong>" . APP_NAME . "</strong>.</p>
                    <p>If you received this email, your SMTP configuration is working correctly! ✅</p>
                    
                    <div style='background: white; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                        <h3 style='margin-top: 0; color: #007bff;'>Configuration Details:</h3>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px; border-bottom: 1px solid #dee2e6;'><strong>SMTP Host:</strong></td>
                                <td style='padding: 8px; border-bottom: 1px solid #dee2e6;'>" . SMTP_HOST . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px; border-bottom: 1px solid #dee2e6;'><strong>SMTP Port:</strong></td>
                                <td style='padding: 8px; border-bottom: 1px solid #dee2e6;'>" . SMTP_PORT . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px; border-bottom: 1px solid #dee2e6;'><strong>Encryption:</strong></td>
                                <td style='padding: 8px; border-bottom: 1px solid #dee2e6;'>" . SMTP_SECURE . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px;'><strong>From Email:</strong></td>
                                <td style='padding: 8px;'>" . MAIL_FROM_EMAIL . "</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p style='margin-bottom: 0;'>
                        <strong>Test Date:</strong> " . date(DATETIME_FORMAT) . "<br>
                        <strong>Application URL:</strong> " . APP_URL . "
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $plainBody = "Email Configuration Test\n\n";
        $plainBody .= "This is a test email from " . APP_NAME . ".\n\n";
        $plainBody .= "If you received this email, your SMTP configuration is working correctly!\n\n";
        $plainBody .= "Configuration Details:\n";
        $plainBody .= "- SMTP Host: " . SMTP_HOST . "\n";
        $plainBody .= "- SMTP Port: " . SMTP_PORT . "\n";
        $plainBody .= "- Encryption: " . SMTP_SECURE . "\n";
        $plainBody .= "- From Email: " . MAIL_FROM_EMAIL . "\n\n";
        $plainBody .= "Test Date: " . date(DATETIME_FORMAT) . "\n";
        $plainBody .= "Application URL: " . APP_URL;
        
        return $this->send($toEmail, $subject, $htmlBody, $plainBody);
    }
}
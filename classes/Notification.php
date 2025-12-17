<?php
/**
 * GateWey Requisition Management System
 * Notification Handler Class
 * 
 * File: classes/Notification.php
 * Purpose: Manages email notifications for requisition workflow events
 */

class Notification {
    
    private $db;
    private $mailer;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->mailer = new Mailer();
    }
    
    /**
     * Send notification
     * 
     * @param string $notificationType Notification type constant
     * @param int $requisitionId Requisition ID
     * @param array $extraData Extra data for notification (optional)
     * @return array Result with success status
     */
    public static function send($notificationType, $requisitionId, $extraData = []) {
        $instance = new self();
        return $instance->sendNotification($notificationType, $requisitionId, $extraData);
    }
    
    /**
     * Send notification (instance method)
     * 
     * @param string $notificationType Notification type constant
     * @param int $requisitionId Requisition ID
     * @param array $extraData Extra data for notification
     * @return array Result with success status
     */
    private function sendNotification($notificationType, $requisitionId, $extraData = []) {
        try {
            // Check if this notification type should be sent
            if (!should_send_notification($notificationType)) {
                return [
                    'success' => false,
                    'message' => 'Notification type is disabled in settings.'
                ];
            }
            
            // Get requisition details
            $requisition = $this->getRequisitionDetails($requisitionId);
            
            if (!$requisition) {
                return [
                    'success' => false,
                    'message' => 'Requisition not found.'
                ];
            }
            
            // Get recipients for this notification type
            $recipients = $this->getRecipients($notificationType, $requisition);
            
            if (empty($recipients)) {
                return [
                    'success' => false,
                    'message' => 'No recipients found for this notification.'
                ];
            }
            
            // Prepare notification data
            $notificationData = array_merge([
                'requisition' => $requisition,
                'app_name' => APP_NAME,
                'app_url' => APP_URL,
                'notification_type' => $notificationType
            ], $extraData);
            
            // Get email subject
            $subject = get_email_subject($notificationType);
            
            // Get template name
            $templateName = $this->getTemplateName($notificationType);
            
            // Send emails to all recipients
            $sent = 0;
            $failed = 0;
            $errors = [];
            
            foreach ($recipients as $recipient) {
                // Create notification record in database
                $notificationId = $this->createNotificationRecord(
                    $recipient['id'],
                    $requisitionId,
                    $notificationType,
                    $subject,
                    $this->getNotificationMessage($notificationType, $requisition)
                );
                
                // Add recipient-specific data
                $notificationData['recipient'] = $recipient;
                
                // Send email using template
                $result = $this->mailer->sendTemplate(
                    [$recipient['email'] => $recipient['name']],
                    $subject,
                    $templateName,
                    $notificationData
                );
                
                if ($result['success']) {
                    $sent++;
                    // Update notification record as sent
                    $this->updateNotificationSent($notificationId, true);
                } else {
                    $failed++;
                    $errors[] = $recipient['email'] . ': ' . $result['message'];
                    // Update notification record with error
                    $this->updateNotificationSent($notificationId, false, $result['message']);
                }
            }
            
            // Log to audit trail
            if (ENABLE_AUDIT_LOG) {
                $this->logAudit($notificationType, $requisitionId, $sent, $failed);
            }
            
            return [
                'success' => $sent > 0,
                'message' => "Emails sent: {$sent}, Failed: {$failed}",
                'sent' => $sent,
                'failed' => $failed,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Notification error: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get requisition details with related data
     * 
     * @param int $requisitionId Requisition ID
     * @return array|null Requisition data
     */
    private function getRequisitionDetails($requisitionId) {
        $sql = "SELECT 
                    r.*,
                    u.first_name as requester_first_name,
                    u.last_name as requester_last_name,
                    u.email as requester_email,
                    u.role_id as requester_role_id,
                    d.department_name,
                    d.department_code,
                    (SELECT COUNT(*) FROM requisition_items WHERE requisition_id = r.id) as items_count,
                    (SELECT SUM(subtotal) FROM requisition_items WHERE requisition_id = r.id) as calculated_total
                FROM requisitions r
                INNER JOIN users u ON r.user_id = u.id
                LEFT JOIN departments d ON r.department_id = d.id
                WHERE r.id = ?";
        
        return $this->db->fetchOne($sql, [$requisitionId]);
    }
    
    /**
     * Get recipients for notification type
     * 
     * @param string $notificationType Notification type
     * @param array $requisition Requisition data
     * @return array Recipients array
     */
    private function getRecipients($notificationType, $requisition) {
        $recipients = [];
        $recipientTypes = get_notification_recipients($notificationType);
        
        foreach ($recipientTypes as $type) {
            switch ($type) {
                case 'requester':
                    // The person who created the requisition
                    $recipients[] = $this->getUserById($requisition['user_id']);
                    break;
                    
                case 'approver':
                case 'next_approver':
                    // Current/next approver based on status
                    $approver = $this->getNextApprover($requisition);
                    if ($approver) {
                        $recipients[] = $approver;
                    }
                    break;
                    
                case 'line_manager':
                    // Line manager of the department (if applicable)
                    if ($requisition['department_id']) {
                        $lineManager = $this->getLineManager($requisition['department_id']);
                        if ($lineManager) {
                            $recipients[] = $lineManager;
                        }
                    }
                    break;
                    
                case 'managing_director':
                    // All managing directors
                    $mds = $this->getUsersByRole(ROLE_MANAGING_DIRECTOR);
                    $recipients = array_merge($recipients, $mds);
                    break;
                    
                case 'finance_manager':
                    // All finance managers
                    $fms = $this->getUsersByRole(ROLE_FINANCE_MANAGER);
                    $recipients = array_merge($recipients, $fms);
                    break;
                    
                case 'finance_member':
                    // Finance member who processed the payment
                    if ($requisition['status'] === STATUS_PAID || $requisition['status'] === STATUS_COMPLETED) {
                        // Get the finance member who marked it as paid
                        $financeMember = $this->getPaymentProcessor($requisition['id']);
                        if ($financeMember) {
                            $recipients[] = $financeMember;
                        }
                    } else {
                        // All finance members
                        $fmembers = $this->getUsersByRole(ROLE_FINANCE_MEMBER);
                        $recipients = array_merge($recipients, $fmembers);
                    }
                    break;
            }
        }
        
        // Remove duplicates based on user ID
        $uniqueRecipients = [];
        $seen = [];
        
        foreach ($recipients as $recipient) {
            if ($recipient && !in_array($recipient['id'], $seen)) {
                $uniqueRecipients[] = $recipient;
                $seen[] = $recipient['id'];
            }
        }
        
        return $uniqueRecipients;
    }
    
    /**
     * Get next approver based on requisition status
     * 
     * @param array $requisition Requisition data
     * @return array|null Next approver user data
     */
    private function getNextApprover($requisition) {
        $status = $requisition['status'];
        $requesterRoleId = $requisition['requester_role_id'];
        
        // Determine next approver based on status
        switch ($status) {
            case STATUS_PENDING_LINE_MANAGER:
                // Need line manager approval
                if ($requisition['department_id']) {
                    return $this->getLineManager($requisition['department_id']);
                }
                break;
                
            case STATUS_PENDING_MD:
                // Need MD approval
                $mds = $this->getUsersByRole(ROLE_MANAGING_DIRECTOR);
                return !empty($mds) ? $mds[0] : null;
                
            case STATUS_PENDING_FINANCE_MANAGER:
                // Need Finance Manager approval
                $fms = $this->getUsersByRole(ROLE_FINANCE_MANAGER);
                return !empty($fms) ? $fms[0] : null;
                
            case STATUS_APPROVED_FOR_PAYMENT:
                // Need Finance Member to process
                $fmembers = $this->getUsersByRole(ROLE_FINANCE_MEMBER);
                return !empty($fmembers) ? $fmembers[0] : null;
        }
        
        return null;
    }
    
    /**
     * Get user by ID
     * 
     * @param int $userId User ID
     * @return array|null User data
     */
    private function getUserById($userId) {
        $sql = "SELECT id, first_name, last_name, email, role_id,
                       CONCAT(first_name, ' ', last_name) as name
                FROM users 
                WHERE id = ? AND is_active = 1";
        
        return $this->db->fetchOne($sql, [$userId]);
    }
    
    /**
     * Get users by role
     * 
     * @param int $roleId Role ID
     * @return array Users array
     */
    private function getUsersByRole($roleId) {
        $sql = "SELECT id, first_name, last_name, email, role_id,
                       CONCAT(first_name, ' ', last_name) as name
                FROM users 
                WHERE role_id = ? AND is_active = 1
                ORDER BY first_name, last_name";
        
        return $this->db->fetchAll($sql, [$roleId]);
    }
    
    /**
     * Get line manager for department
     * 
     * @param int $departmentId Department ID
     * @return array|null Line manager user data
     */
    private function getLineManager($departmentId) {
        $sql = "SELECT id, first_name, last_name, email, role_id,
                       CONCAT(first_name, ' ', last_name) as name
                FROM users 
                WHERE department_id = ? 
                AND role_id = ? 
                AND is_active = 1
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$departmentId, ROLE_LINE_MANAGER]);
    }
    
    /**
     * Get payment processor (finance member who marked as paid)
     * 
     * @param int $requisitionId Requisition ID
     * @return array|null Finance member user data
     */
    private function getPaymentProcessor($requisitionId) {
        $sql = "SELECT u.id, u.first_name, u.last_name, u.email, u.role_id,
                       CONCAT(u.first_name, ' ', u.last_name) as name
                FROM requisition_approvals ra
                INNER JOIN users u ON ra.approver_id = u.id
                WHERE ra.requisition_id = ?
                AND ra.action = ?
                AND u.role_id = ?
                ORDER BY ra.approved_at DESC
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [
            $requisitionId, 
            APPROVAL_APPROVED,
            ROLE_FINANCE_MEMBER
        ]);
    }
    
    /**
     * Get template name for notification type
     * 
     * @param string $notificationType Notification type
     * @return string Template name
     */
    private function getTemplateName($notificationType) {
        $templates = [
            NOTIF_REQUISITION_SUBMITTED => 'requisition-submitted',
            NOTIF_REQUISITION_APPROVED => 'requisition-approved',
            NOTIF_REQUISITION_REJECTED => 'requisition-rejected',
            NOTIF_REQUISITION_PAID => 'requisition-paid',
            NOTIF_RECEIPT_UPLOADED => 'receipt-uploaded',
            NOTIF_ACTION_REQUIRED => 'action-required',
            NOTIF_REQUISITION_CANCELLED => 'requisition-cancelled'
        ];
        
        return $templates[$notificationType] ?? 'base';
    }
    
    /**
     * Get notification message
     * 
     * @param string $notificationType Notification type
     * @param array $requisition Requisition data
     * @return string Notification message
     */
    private function getNotificationMessage($notificationType, $requisition) {
        $reqNumber = $requisition['requisition_number'];
        $amount = number_format($requisition['total_amount'], 2);
        
        $messages = [
            NOTIF_REQUISITION_SUBMITTED => "Requisition {$reqNumber} has been submitted for approval. Amount: ₦{$amount}",
            NOTIF_REQUISITION_APPROVED => "Requisition {$reqNumber} has been approved and moved to the next stage.",
            NOTIF_REQUISITION_REJECTED => "Requisition {$reqNumber} has been rejected. Please review and take action.",
            NOTIF_REQUISITION_PAID => "Payment has been processed for requisition {$reqNumber}. Amount: ₦{$amount}",
            NOTIF_RECEIPT_UPLOADED => "Receipt has been uploaded for requisition {$reqNumber}.",
            NOTIF_ACTION_REQUIRED => "Action required on requisition {$reqNumber}. Amount: ₦{$amount}",
            NOTIF_REQUISITION_CANCELLED => "Requisition {$reqNumber} has been cancelled."
        ];
        
        return $messages[$notificationType] ?? "Update on requisition {$reqNumber}";
    }
    
    /**
     * Create notification record in database
     * 
     * @param int $userId User ID
     * @param int $requisitionId Requisition ID
     * @param string $notificationType Notification type
     * @param string $title Notification title
     * @param string $message Notification message
     * @return int Notification ID
     */
    private function createNotificationRecord($userId, $requisitionId, $notificationType, $title, $message) {
        $sql = "INSERT INTO notifications 
                (user_id, requisition_id, notification_type, title, message, is_read, is_email_sent, created_at)
                VALUES (?, ?, ?, ?, ?, 0, 0, NOW())";
        
        $this->db->execute($sql, [
            $userId,
            $requisitionId,
            $notificationType,
            $title,
            $message
        ]);
        
        return $this->db->getConnection()->lastInsertId();
    }
    
    /**
     * Update notification as sent/failed
     * 
     * @param int $notificationId Notification ID
     * @param bool $success Whether email was sent successfully
     * @param string $error Error message if failed
     */
    private function updateNotificationSent($notificationId, $success, $error = null) {
        if ($success) {
            $sql = "UPDATE notifications 
                    SET is_email_sent = 1, email_sent_at = NOW()
                    WHERE id = ?";
            $this->db->execute($sql, [$notificationId]);
        } else {
            $sql = "UPDATE notifications 
                    SET is_email_sent = 0, email_error = ?
                    WHERE id = ?";
            $this->db->execute($sql, [$error, $notificationId]);
        }
    }
    
    /**
     * Log to audit trail
     * 
     * @param string $notificationType Notification type
     * @param int $requisitionId Requisition ID
     * @param int $sent Number sent
     * @param int $failed Number failed
     */
    private function logAudit($notificationType, $requisitionId, $sent, $failed) {
        $description = "Email notification sent: Type={$notificationType}, Requisition={$requisitionId}, Sent={$sent}, Failed={$failed}";
        
        $sql = "INSERT INTO audit_log 
                (user_id, action_type, action_description, requisition_id, ip_address, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $this->db->execute($sql, [
            Session::getUserId() ?? 0,
            AUDIT_EMAIL_SENT,
            $description,
            $requisitionId,
            LOG_IP_ADDRESS ? ($_SERVER['REMOTE_ADDR'] ?? null) : null
        ]);
    }
    
    /**
     * Send bulk notifications (for reminders, etc.)
     * 
     * @param string $notificationType Notification type
     * @param array $requisitionIds Array of requisition IDs
     * @return array Result summary
     */
    public static function sendBulk($notificationType, $requisitionIds) {
        $instance = new self();
        $results = [
            'total' => count($requisitionIds),
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($requisitionIds as $requisitionId) {
            $result = $instance->sendNotification($notificationType, $requisitionId);
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Requisition {$requisitionId}: " . $result['message'];
            }
        }
        
        return $results;
    }
    
    /**
     * Send reminder for pending approvals
     * 
     * @param int $daysOld Number of days old
     * @return array Result with count of reminders sent
     */
    public static function sendPendingReminders($daysOld = 3) {
        $instance = new self();
        $db = Database::getInstance();
        
        // Get pending requisitions older than X days
        $sql = "SELECT id, requisition_number, status, user_id, total_amount
                FROM requisitions
                WHERE status IN (?, ?, ?, ?)
                AND DATEDIFF(NOW(), created_at) >= ?
                ORDER BY created_at ASC";
        
        $pendingRequisitions = $db->fetchAll($sql, [
            STATUS_PENDING_LINE_MANAGER,
            STATUS_PENDING_MD,
            STATUS_PENDING_FINANCE_MANAGER,
            STATUS_APPROVED_FOR_PAYMENT,
            $daysOld
        ]);
        
        if (empty($pendingRequisitions)) {
            return [
                'success' => true,
                'message' => 'No pending requisitions requiring reminders.',
                'count' => 0
            ];
        }
        
        $requisitionIds = array_column($pendingRequisitions, 'id');
        $results = self::sendBulk(NOTIF_ACTION_REQUIRED, $requisitionIds);
        
        return [
            'success' => true,
            'message' => "Reminders sent for {$results['success']} requisitions.",
            'count' => $results['success'],
            'results' => $results
        ];
    }
    
    /**
     * Get unread notifications for user
     * 
     * @param int $userId User ID
     * @param int $limit Limit
     * @return array Notifications
     */
    public static function getUnreadForUser($userId, $limit = 10) {
        $db = Database::getInstance();
        
        $sql = "SELECT n.*, r.requisition_number, r.total_amount
                FROM notifications n
                LEFT JOIN requisitions r ON n.requisition_id = r.id
                WHERE n.user_id = ? AND n.is_read = 0
                ORDER BY n.created_at DESC
                LIMIT ?";
        
        return $db->fetchAll($sql, [$userId, $limit]);
    }
    
    /**
     * Mark notification as read
     * 
     * @param int $notificationId Notification ID
     * @return bool Success status
     */
    public static function markAsRead($notificationId) {
        $db = Database::getInstance();
        
        $sql = "UPDATE notifications 
                SET is_read = 1, read_at = NOW()
                WHERE id = ?";
        
        return $db->execute($sql, [$notificationId]);
    }
    
    /**
     * Mark all notifications as read for user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public static function markAllAsRead($userId) {
        $db = Database::getInstance();
        
        $sql = "UPDATE notifications 
                SET is_read = 1, read_at = NOW()
                WHERE user_id = ? AND is_read = 0";
        
        return $db->execute($sql, [$userId]);
    }
}
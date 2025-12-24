<?php
/**
 * GateWey Requisition Management System
 * Payment Class - DEBUG VERSION
 * 
 * File: classes/Payment.php
 * Purpose: Handle payment processing, invoice uploads, and receipt management
 */

class Payment {
    
    private $db;
    private $auditLog;
    private $fileUpload;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->auditLog = new AuditLog();
        $this->fileUpload = new FileUpload();
    }
    
    /**
     * Get requisitions awaiting payment (approved by Finance Manager)
     * 
     * @return array Requisitions ready for payment
     */
    public function getPendingPayments() {
        try {
            $sql = "SELECT r.*,
                           u.first_name as requester_first_name,
                           u.last_name as requester_last_name,
                           u.email as requester_email,
                           d.department_name,
                           d.department_code,
                           ca.first_name as current_approver_first_name,
                           ca.last_name as current_approver_last_name,
                           sa.first_name as selected_approver_first_name,
                           sa.last_name as selected_approver_last_name
                    FROM requisitions r
                    JOIN users u ON r.user_id = u.id
                    LEFT JOIN departments d ON r.department_id = d.id
                    LEFT JOIN users ca ON r.current_approver_id = ca.id
                    LEFT JOIN users sa ON r.selected_approver_id = sa.id
                    WHERE r.status = ?
                    ORDER BY r.updated_at ASC";
            
            return $this->db->fetchAll($sql, [STATUS_APPROVED_FOR_PAYMENT]);
            
        } catch (Exception $e) {
            error_log("Get pending payments error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Process payment - Upload invoice and mark as paid
     * 
     * @param int $requisitionId Requisition ID
     * @param array $invoiceFile Invoice file ($_FILES array)
     * @param array $paymentData Payment details
     * @return array Result with success status
     */
    public function processPayment($requisitionId, $invoiceFile, $paymentData) {
        try {
            error_log("=== PROCESS PAYMENT START ===");
            error_log("Requisition ID: " . $requisitionId);
            error_log("Payment Data: " . print_r($paymentData, true));
            error_log("Invoice File: " . print_r($invoiceFile, true));
            
            // Verify Finance Member permission
            if (!is_finance_member()) {
                error_log("ERROR: User is not finance member");
                return [
                    'success' => false,
                    'message' => 'Only Finance Members can process payments.'
                ];
            }
            error_log("Permission check passed");
            
            // Get requisition
            $requisition = $this->db->fetchOne(
                "SELECT * FROM requisitions WHERE id = ?",
                [$requisitionId]
            );
            error_log("Requisition fetched: " . ($requisition ? "YES" : "NO"));
            
            if (!$requisition) {
                error_log("ERROR: Requisition not found");
                return [
                    'success' => false,
                    'message' => 'Requisition not found.'
                ];
            }
            
            // Verify status
            if ($requisition['status'] !== STATUS_APPROVED_FOR_PAYMENT) {
                error_log("ERROR: Wrong status - " . $requisition['status']);
                return [
                    'success' => false,
                    'message' => 'This requisition is not approved for payment.'
                ];
            }
            error_log("Status check passed");
            
            // Validate payment data
            error_log("Starting validation...");
            $validator = new Validator();
            $validator->setData($paymentData);
            $validator->setRules([
                'payment_method' => 'required',
                'payment_reference' => 'required'
                // Removed payment_notes validation - it's optional
            ]);
            
            if (!$validator->validate()) {
                error_log("Validation failed: " . print_r($validator->getErrors(), true));
                return [
                    'success' => false,
                    'message' => 'Invalid payment data.',
                    'errors' => $validator->getErrors()
                ];
            }
            error_log("Validation passed");
            
            // Validate invoice file
            if (empty($invoiceFile) || $invoiceFile['error'] !== UPLOAD_ERR_OK) {
                error_log("ERROR: Invoice file invalid - Error code: " . ($invoiceFile['error'] ?? 'NO FILE'));
                return [
                    'success' => false,
                    'message' => 'Invoice/proof of payment is required.'
                ];
            }
            error_log("Invoice file validation passed");
            
            // Begin transaction
            error_log("Beginning transaction...");
            $this->db->beginTransaction();
            
            // Upload invoice
            error_log("Uploading invoice...");
            $uploadResult = $this->fileUpload->uploadRequisitionDocument(
                $requisitionId,
                $invoiceFile,
                DOC_TYPE_INVOICE
            );
            error_log("Upload result: " . print_r($uploadResult, true));
            
            if (!$uploadResult['success']) {
                error_log("Upload failed, rolling back");
                $this->db->rollback();
                return $uploadResult;
            }
            error_log("Invoice uploaded successfully");
            
            // Update requisition status to paid
            error_log("Updating requisition status...");
            $sql = "UPDATE requisitions 
                    SET status = ?,
                        paid_by = ?,
                        payment_date = NOW(),
                        payment_method = ?,
                        payment_reference = ?,
                        payment_notes = ?,
                        updated_at = NOW()
                    WHERE id = ?";
            
            $updateParams = [
                STATUS_PAID,
                Session::getUserId(),
                Sanitizer::string($paymentData['payment_method']),
                Sanitizer::string($paymentData['payment_reference']),
                Sanitizer::string($paymentData['payment_notes'] ?? ''),
                $requisitionId
            ];
            error_log("Update params: " . print_r($updateParams, true));
            
            $this->db->execute($sql, $updateParams);
            error_log("Requisition updated");
            
            // Log payment processing
            error_log("Logging payment action...");
            $description = "Payment processed - Method: {$paymentData['payment_method']}, Ref: {$paymentData['payment_reference']}";
            $this->auditLog->logRequisitionAction(
                Session::getUserId(),
                $requisitionId,
                AUDIT_PAYMENT_PROCESSED,
                $description,
                [
                    'payment_method' => $paymentData['payment_method'],
                    'payment_reference' => $paymentData['payment_reference'],
                    'invoice_id' => $uploadResult['document_id']
                ]
            );
            error_log("Audit log created");
            
// Commit transaction
            error_log("Committing transaction...");
            $this->db->commit();
            error_log("=== PROCESS PAYMENT SUCCESS ===");
            
            // ✅ SEND EMAIL NOTIFICATION
            try {
                Notification::send(NOTIF_REQUISITION_PAID, $requisitionId);
            } catch (Exception $e) {
                error_log("Email notification failed: " . $e->getMessage());
                // Don't block the flow if email fails
            }
            
            return [
                'success' => true,
                'message' => 'Payment processed successfully.',
                'requisition_number' => $requisition['requisition_number']
            ];
            
        } catch (Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                error_log("Exception caught, rolling back transaction");
                $this->db->rollback();
            }
            
            error_log("=== PROCESS PAYMENT ERROR ===");
            error_log("Error message: " . $e->getMessage());
            error_log("Error file: " . $e->getFile());
            error_log("Error line: " . $e->getLine());
            error_log("Error trace: " . $e->getTraceAsString());
            
            // TEMPORARY: Show actual error for debugging
            return [
                'success' => false,
                'message' => 'Payment error: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ' in ' . basename($e->getFile()) . ')'
            ];
        }
    }
    
    /**
     * Upload receipt (by requester after payment)
     * 
     * @param int $requisitionId Requisition ID
     * @param array $receiptFile Receipt file ($_FILES array)
     * @param string $notes Receipt notes
     * @return array Result with success status
     */
    public function uploadReceipt($requisitionId, $receiptFile, $notes = '') {
        try {
            // Get requisition
            $requisition = $this->db->fetchOne(
                "SELECT * FROM requisitions WHERE id = ?",
                [$requisitionId]
            );
            
            if (!$requisition) {
                return [
                    'success' => false,
                    'message' => 'Requisition not found.'
                ];
            }
            
            // Verify ownership
            if ($requisition['user_id'] != Session::getUserId()) {
                return [
                    'success' => false,
                    'message' => 'You can only upload receipts for your own requisitions.'
                ];
            }
            
            // Verify status is paid
            if ($requisition['status'] !== STATUS_PAID) {
                return [
                    'success' => false,
                    'message' => 'Receipt can only be uploaded after payment has been processed.'
                ];
            }
            
            // Validate receipt file
            if (empty($receiptFile) || $receiptFile['error'] !== UPLOAD_ERR_OK) {
                return [
                    'success' => false,
                    'message' => 'Receipt file is required.'
                ];
            }
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Upload receipt
            $uploadResult = $this->fileUpload->uploadRequisitionDocument(
                $requisitionId,
                $receiptFile,
                DOC_TYPE_RECEIPT
            );
            
            if (!$uploadResult['success']) {
                $this->db->rollback();
                return $uploadResult;
            }
            
            // Update requisition status to completed
            $sql = "UPDATE requisitions 
                    SET status = ?,
                        receipt_uploaded_at = NOW(),
                        receipt_notes = ?,
                        updated_at = NOW()
                    WHERE id = ?";
            
            $this->db->execute($sql, [
                STATUS_COMPLETED,
                Sanitizer::string($notes),
                $requisitionId
            ]);
            
            // Log receipt upload
            $this->auditLog->logRequisitionAction(
                Session::getUserId(),
                $requisitionId,
                AUDIT_RECEIPT_UPLOADED,
                "Receipt uploaded by requester",
                ['receipt_id' => $uploadResult['document_id']]
            );
            
// Commit transaction
            $this->db->commit();
            
            // ✅ SEND EMAIL NOTIFICATION
            try {
                Notification::send(NOTIF_RECEIPT_UPLOADED, $requisitionId);
            } catch (Exception $e) {
                error_log("Email notification failed: " . $e->getMessage());
                // Don't block the flow if email fails
            }
            
            return [
                'success' => true,
                'message' => 'Receipt uploaded successfully. Requisition is now complete.',
                'requisition_number' => $requisition['requisition_number']
            ];
            
        } catch (Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->rollback();
            }
            
            error_log("Upload receipt error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while uploading receipt.'
            ];
        }
    }
    
    /**
     * Get paid requisitions awaiting receipt upload
     * 
     * @param bool $allRequisitions If true, get all; if false, only current user's
     * @return array Requisitions awaiting receipts
     */
    public function getPendingReceipts($allRequisitions = false) {
        try {
            $sql = "SELECT r.*, 
                           u.first_name as requester_first_name,
                           u.last_name as requester_last_name,
                           u.email as requester_email,
                           d.department_name,
                           d.department_code,
                           payer.first_name as paid_by_first_name,
                           payer.last_name as paid_by_last_name
                    FROM requisitions r
                    JOIN users u ON r.user_id = u.id
                    LEFT JOIN departments d ON r.department_id = d.id
                    LEFT JOIN users payer ON r.paid_by = payer.id
                    WHERE r.status = ?";
            
            $params = [STATUS_PAID];
            
            // If not viewing all, filter by current user
            if (!$allRequisitions) {
                $sql .= " AND r.user_id = ?";
                $params[] = Session::getUserId();
            }
            
            $sql .= " ORDER BY r.payment_date ASC";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get pending receipts error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get payment history
     * 
     * @param array $filters Optional filters (date_from, date_to, user_id)
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Payment records
     */
    public function getPaymentHistory($filters = [], $page = 1, $perPage = 15) {
        try {
            $where = ["r.status IN (?, ?)"];
            $params = [STATUS_PAID, STATUS_COMPLETED];
            
            // Apply filters
            if (!empty($filters['date_from'])) {
                $where[] = "DATE(r.payment_date) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where[] = "DATE(r.payment_date) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['user_id'])) {
                $where[] = "r.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (!empty($filters['department_id'])) {
                $where[] = "r.department_id = ?";
                $params[] = $filters['department_id'];
            }
            
            // Calculate offset
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total 
                        FROM requisitions r
                        WHERE " . implode(' AND ', $where);
            
            $totalResult = $this->db->fetchOne($countSql, $params);
            $totalRecords = $totalResult['total'];
            
            // Get paginated results
            $sql = "SELECT r.*,
                           u.first_name as requester_first_name,
                           u.last_name as requester_last_name,
                           u.email as requester_email,
                           d.department_name,
                           d.department_code,
                           payer.first_name as paid_by_first_name,
                           payer.last_name as paid_by_last_name,
                           ca.first_name as current_approver_first_name,
                           ca.last_name as current_approver_last_name,
                           sa.first_name as selected_approver_first_name,
                           sa.last_name as selected_approver_last_name
                    FROM requisitions r
                    JOIN users u ON r.user_id = u.id
                    LEFT JOIN departments d ON r.department_id = d.id
                    LEFT JOIN users payer ON r.paid_by = payer.id
                    LEFT JOIN users ca ON r.current_approver_id = ca.id
                    LEFT JOIN users sa ON r.selected_approver_id = sa.id
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY r.payment_date DESC
                    LIMIT ? OFFSET ?";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $records = $this->db->fetchAll($sql, $params);
            
            return [
                'records' => $records,
                'total' => $totalRecords,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($totalRecords / $perPage)
            ];
            
        } catch (Exception $e) {
            error_log("Get payment history error: " . $e->getMessage());
            return [
                'records' => [],
                'total' => 0,
                'page' => 1,
                'per_page' => $perPage,
                'total_pages' => 0
            ];
        }
    }
    
/**
 * Get payment statistics
 * 
 * @param array $filters Optional filters (date_from, date_to)
 * @return array Statistics
 */
public function getPaymentStatistics($filters = []) {
    try {
        // Count paid requisitions and calculate amounts
        $sql = "SELECT 
                    COUNT(*) as paid_count,
                    COUNT(*) as total_payments,
                    COALESCE(SUM(total_amount), 0) as total_amount,
                    COALESCE(AVG(total_amount), 0) as average_amount
                FROM requisitions
                WHERE status IN (?, ?)";
        
        $params = [STATUS_PAID, STATUS_COMPLETED];
        
        // Apply date filters if provided
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(payment_date) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(payment_date) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $result = $this->db->fetchOne($sql, $params);
        
        return [
            'paid_count' => (int)$result['paid_count'],
            'total_payments' => (int)$result['total_payments'],
            'total_amount' => (float)$result['total_amount'],
            'average_amount' => (float)$result['average_amount']
        ];
        
    } catch (Exception $e) {
        error_log("Get payment statistics error: " . $e->getMessage());
        return [
            'paid_count' => 0,
            'total_payments' => 0,
            'total_amount' => 0,
            'average_amount' => 0
        ];
    }
}
    
    /**
     * Get invoice document for a requisition
     * 
     * @param int $requisitionId Requisition ID
     * @return array|null Invoice document or null
     */
    public function getInvoice($requisitionId) {
        try {
            $sql = "SELECT * FROM requisition_documents 
                    WHERE requisition_id = ? 
                      AND document_type = ?
                    ORDER BY uploaded_at DESC
                    LIMIT 1";
            
            return $this->db->fetchOne($sql, [$requisitionId, DOC_TYPE_INVOICE]);
            
        } catch (Exception $e) {
            error_log("Get invoice error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get receipt document for a requisition
     * 
     * @param int $requisitionId Requisition ID
     * @return array|null Receipt document or null
     */
    public function getReceipt($requisitionId) {
        try {
            $sql = "SELECT * FROM requisition_documents 
                    WHERE requisition_id = ? 
                      AND document_type = ?
                    ORDER BY uploaded_at DESC
                    LIMIT 1";
            
            return $this->db->fetchOne($sql, [$requisitionId, DOC_TYPE_RECEIPT]);
            
        } catch (Exception $e) {
            error_log("Get receipt error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if requisition has invoice
     * 
     * @param int $requisitionId Requisition ID
     * @return bool True if has invoice
     */
    public function hasInvoice($requisitionId) {
        return $this->getInvoice($requisitionId) !== null;
    }
    
    /**
     * Check if requisition has receipt
     * 
     * @param int $requisitionId Requisition ID
     * @return bool True if has receipt
     */
    public function hasReceipt($requisitionId) {
        return $this->getReceipt($requisitionId) !== null;
    }
}
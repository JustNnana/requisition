<?php
/**
 * GateWey Requisition Management System
 * File Upload Handler Class
 * 
 * File: classes/FileUpload.php
 * Purpose: Secure file upload handling with validation and storage
 */

class FileUpload {
    
    private $db;
    private $allowedExtensions;
    private $allowedMimeTypes;
    private $maxFileSize;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->allowedExtensions = ALLOWED_FILE_EXTENSIONS;
        $this->allowedMimeTypes = ALLOWED_MIME_TYPES;
        $this->maxFileSize = UPLOAD_MAX_SIZE;
    }
    
    /**
     * Upload file for requisition
     * 
     * @param int $requisitionId Requisition ID
     * @param array $file File from $_FILES
     * @param string $documentType Document type (attachment, invoice, receipt, proof_of_payment)
     * @param int $maxSize Maximum file size (optional, overrides default)
     * @return array Result with success status and file info
     */
    public function uploadRequisitionDocument($requisitionId, $file, $documentType, $maxSize = null) {
        try {
            // Validate file
            $validation = $this->validateFile($file, $maxSize);
            
            if (!$validation['success']) {
                return $validation;
            }
            
            // Determine upload directory based on document type
            $uploadDir = $this->getUploadDirectory($documentType);
            
            // Ensure directory exists
            if (!$this->ensureDirectoryExists($uploadDir)) {
                return [
                    'success' => false,
                    'message' => 'Failed to create upload directory.'
                ];
            }
            
            // Generate unique filename
            $originalName = $file['name'];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $newFilename = uniqid() . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . '/' . $newFilename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return [
                    'success' => false,
                    'message' => 'Failed to upload file.'
                ];
            }
            
            // Save file info to database
            $sql = "INSERT INTO requisition_documents 
                    (requisition_id, document_type, file_name, file_path, file_size, mime_type, uploaded_by, uploaded_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $requisitionId,
                $documentType,
                $originalName,
                $filePath,
                $file['size'],
                $file['type'],
                Session::getUserId()
            ];
            
            $this->db->execute($sql, $params);
            $documentId = $this->db->lastInsertId();
            
            // Log action
            if (ENABLE_AUDIT_LOG) {
                $this->logUpload($requisitionId, $documentType, $originalName);
            }
            
            return [
                'success' => true,
                'message' => 'File uploaded successfully.',
                'document_id' => $documentId,
                'filename' => $originalName,
                'file_size' => $file['size']
            ];
            
        } catch (Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            
            // Clean up file if database insert failed
            if (isset($filePath) && file_exists($filePath)) {
                @unlink($filePath);
            }
            
            return [
                'success' => false,
                'message' => 'An error occurred during file upload.'
            ];
        }
    }
    
    /**
     * Validate uploaded file
     * 
     * @param array $file File from $_FILES
     * @param int|null $maxSize Maximum file size
     * @return array Validation result
     */
    private function validateFile($file, $maxSize = null) {
        // Check if file was uploaded
        if (!isset($file['error']) || is_array($file['error'])) {
            return [
                'success' => false,
                'message' => 'Invalid file upload.'
            ];
        }
        
        // Check for upload errors
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return [
                    'success' => false,
                    'message' => 'File size exceeds the maximum allowed size.'
                ];
            case UPLOAD_ERR_PARTIAL:
                return [
                    'success' => false,
                    'message' => 'File was only partially uploaded.'
                ];
            case UPLOAD_ERR_NO_FILE:
                return [
                    'success' => false,
                    'message' => 'No file was uploaded.'
                ];
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown upload error.'
                ];
        }
        
        // Check file size
        $maxAllowedSize = $maxSize ?? $this->maxFileSize;
        
        if ($file['size'] > $maxAllowedSize) {
            return [
                'success' => false,
                'message' => 'File size exceeds ' . format_file_size($maxAllowedSize) . '.'
            ];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $this->allowedExtensions)) {
            return [
                'success' => false,
                'message' => 'File type not allowed. Allowed types: ' . implode(', ', $this->allowedExtensions)
            ];
        }
        
        // Check MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            return [
                'success' => false,
                'message' => 'Invalid file type.'
            ];
        }
        
        // Additional security: Check for PHP files disguised as other types
        $fileContent = file_get_contents($file['tmp_name'], false, null, 0, 512);
        if (preg_match('/<\?php/i', $fileContent)) {
            return [
                'success' => false,
                'message' => 'Invalid file content detected.'
            ];
        }
        
        return ['success' => true];
    }
    
    /**
     * Get upload directory based on document type
     * 
     * @param string $documentType Document type
     * @return string Upload directory path
     */
    private function getUploadDirectory($documentType) {
        switch ($documentType) {
            case DOC_TYPE_INVOICE:
                return INVOICES_DIR;
            case DOC_TYPE_RECEIPT:
                return RECEIPTS_DIR;
            case DOC_TYPE_ATTACHMENT:
            case DOC_TYPE_SUPPORTING:
            default:
                return ATTACHMENTS_DIR;
        }
    }
    
    /**
     * Ensure upload directory exists and is writable
     * 
     * @param string $directory Directory path
     * @return bool Success status
     */
    private function ensureDirectoryExists($directory) {
        if (!file_exists($directory)) {
            if (!mkdir($directory, 0755, true)) {
                error_log("Failed to create directory: " . $directory);
                return false;
            }
            
            // Create .htaccess to prevent direct access
            $htaccessContent = "# Prevent direct access to uploaded files\n";
            $htaccessContent .= "Options -Indexes\n";
            $htaccessContent .= "<FilesMatch \"\\.(jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx)$\">\n";
            $htaccessContent .= "    # Allow access only through PHP scripts\n";
            $htaccessContent .= "</FilesMatch>\n";
            
            file_put_contents($directory . '/.htaccess', $htaccessContent);
        }
        
        return is_writable($directory);
    }
    
    /**
     * Delete file and database record
     * 
     * @param int $documentId Document ID
     * @return array Result with success status
     */
    public function deleteDocument($documentId) {
        try {
            // Get document info
            $sql = "SELECT * FROM requisition_documents WHERE id = ?";
            $document = $this->db->fetchOne($sql, [$documentId]);
            
            if (!$document) {
                return [
                    'success' => false,
                    'message' => 'Document not found.'
                ];
            }
            
            // Check permission
            if (!$this->canDeleteDocument($document)) {
                return [
                    'success' => false,
                    'message' => 'You do not have permission to delete this document.'
                ];
            }
            
            // Delete file from filesystem
            if (file_exists($document['file_path'])) {
                @unlink($document['file_path']);
            }
            
            // Delete database record
            $sql = "DELETE FROM requisition_documents WHERE id = ?";
            $this->db->execute($sql, [$documentId]);
            
            // Log action
            if (ENABLE_AUDIT_LOG) {
                $this->logAction(
                    $document['requisition_id'],
                    AUDIT_DOCUMENT_DELETED,
                    "Document deleted: " . $document['file_name']
                );
            }
            
            return [
                'success' => true,
                'message' => 'Document deleted successfully.'
            ];
            
        } catch (Exception $e) {
            error_log("Delete document error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete document.'
            ];
        }
    }
    
    /**
     * Download/serve file
     * 
     * @param int $documentId Document ID
     * @return bool Success status
     */
    public function serveFile($documentId) {
        try {
            // Get document info
            $sql = "SELECT rd.*, r.user_id, r.status
                    FROM requisition_documents rd
                    INNER JOIN requisitions r ON rd.requisition_id = r.id
                    WHERE rd.id = ?";
            
            $document = $this->db->fetchOne($sql, [$documentId]);
            
            if (!$document) {
                return false;
            }
            
            // Check permission
            if (!$this->canViewDocument($document)) {
                return false;
            }
            
            // Check if file exists
            if (!file_exists($document['file_path'])) {
                return false;
            }
            
            // Log document view
            if (ENABLE_AUDIT_LOG) {
                $this->logAction(
                    $document['requisition_id'],
                    AUDIT_DOCUMENT_VIEWED,
                    "Document viewed: " . $document['file_name']
                );
            }
            
            // Serve file
            header('Content-Type: ' . $document['mime_type']);
            header('Content-Disposition: inline; filename="' . $document['file_name'] . '"');
            header('Content-Length: ' . filesize($document['file_path']));
            header('Cache-Control: private, max-age=3600');
            
            readfile($document['file_path']);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Serve file error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user can delete document
     * 
     * @param array $document Document data
     * @return bool Permission status
     */
    private function canDeleteDocument($document) {
        $userId = Session::getUserId();
        $roleId = Session::getUserRoleId();
        
        // Owner can delete if requisition is draft or rejected
        if ($document['uploaded_by'] == $userId) {
            return true;
        }
        
        // Super admin can delete
        if ($roleId == ROLE_SUPER_ADMIN) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can view document
     * 
     * @param array $document Document data
     * @return bool Permission status
     */
    private function canViewDocument($document) {
        $userId = Session::getUserId();
        $roleId = Session::getUserRoleId();
        
        // Users with view_all permission can view
        if (in_array($roleId, CAN_VIEW_ALL)) {
            return true;
        }
        
        // Requisition owner can view
        if ($document['user_id'] == $userId) {
            return true;
        }
        
        // Line managers can view documents from their department
        if ($roleId == ROLE_LINE_MANAGER) {
            $requisition = new Requisition();
            $reqData = $requisition->getById($document['requisition_id']);
            
            if ($reqData && $reqData['department_id'] == Session::getUserDepartmentId()) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Log upload action to audit trail
     * 
     * @param int $requisitionId Requisition ID
     * @param string $documentType Document type
     * @param string $filename Filename
     */
    private function logUpload($requisitionId, $documentType, $filename) {
        $action = AUDIT_DOCUMENT_UPLOADED;
        
        switch ($documentType) {
            case DOC_TYPE_INVOICE:
                $action = AUDIT_INVOICE_UPLOADED;
                break;
            case DOC_TYPE_RECEIPT:
                $action = AUDIT_RECEIPT_UPLOADED;
                break;
            case DOC_TYPE_ATTACHMENT:
            case DOC_TYPE_SUPPORTING:
                $action = AUDIT_ATTACHMENT_UPLOADED;
                break;
        }
        
        $this->logAction($requisitionId, $action, "File uploaded: " . $filename);
    }
    
    /**
     * Log action to audit trail
     * 
     * @param int $requisitionId Requisition ID
     * @param string $action Action constant
     * @param string $description Description
     */
    private function logAction($requisitionId, $action, $description) {
        try {
            $sql = "INSERT INTO audit_log 
                    (requisition_id, user_id, action, description, ip_address, user_agent, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $requisitionId,
                Session::getUserId(),
                $action,
                $description,
                LOG_IP_ADDRESS ? ($_SERVER['REMOTE_ADDR'] ?? null) : null,
                LOG_USER_AGENT ? ($_SERVER['HTTP_USER_AGENT'] ?? null) : null
            ];
            
            $this->db->execute($sql, $params);
            
        } catch (Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
        }
    }
    
    /**
     * Get file icon class based on extension
     * 
     * @param string $filename Filename
     * @return string Font Awesome icon class
     */
    public static function getFileIcon($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $icons = [
            'pdf' => 'fa-file-pdf text-danger',
            'doc' => 'fa-file-word text-primary',
            'docx' => 'fa-file-word text-primary',
            'xls' => 'fa-file-excel text-success',
            'xlsx' => 'fa-file-excel text-success',
            'jpg' => 'fa-file-image text-info',
            'jpeg' => 'fa-file-image text-info',
            'png' => 'fa-file-image text-info',
            'gif' => 'fa-file-image text-info'
        ];
        
        return $icons[$extension] ?? 'fa-file text-secondary';
    }
}
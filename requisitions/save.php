<?php
/**
 * GateWey Requisition Management System
 * Save Requisition Handler
 * 
 * File: requisitions/save.php
 * Purpose: POST handler for creating and updating requisitions
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication and authorization
require_once __DIR__ . '/../middleware/auth-check.php';
require_once __DIR__ . '/../middleware/role-check.php';
require_once __DIR__ . '/../helpers/permissions.php';

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::setFlash('error', 'Invalid request method.');
    header('Location: list.php');
    exit;
}

// Verify CSRF token
if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    Session::setFlash('error', 'Invalid security token. Please try again.');
    header('Location: ' . ($_POST['action'] == 'edit' ? 'edit.php?id=' . ($_POST['requisition_id'] ?? '') : 'create.php'));
    exit;
}

// Check permission
if (!can_user_raise_requisition()) {
    Session::setFlash('error', 'You do not have permission to create requisitions.');
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

// Initialize objects
$requisition = new Requisition();
$fileUpload = new FileUpload();

// Initialize variables
$errors = [];

// Sanitize and validate input
$action = Sanitizer::string($_POST['action'] ?? 'create');
$requisitionId = isset($_POST['requisition_id']) ? Sanitizer::int($_POST['requisition_id']) : null;
$isDraft = isset($_POST['is_draft']) && $_POST['is_draft'] == '1';

$formData = [
    'purpose' => Sanitizer::string($_POST['purpose'] ?? ''),
    'description' => Sanitizer::string($_POST['description'] ?? ''),
    'total_amount' => Sanitizer::float($_POST['total_amount'] ?? '0'),
    'is_draft' => $isDraft,
    'items' => []
];

// Validate purpose
if (empty($formData['purpose'])) {
    $errors[] = 'Purpose/Description is required.';
}

// Validate and process items
if (isset($_POST['items']) && is_array($_POST['items'])) {
    $totalAmount = 0;
    
    foreach ($_POST['items'] as $index => $item) {
        $description = Sanitizer::string($item['description'] ?? '');
        $quantity = Sanitizer::int($item['quantity'] ?? '0');
        $unitPrice = Sanitizer::float($item['unit_price'] ?? '0');
        $subtotal = $quantity * $unitPrice;
        
        // Validate item
        if (empty($description)) {
            $errors[] = "Item #" . ($index + 1) . ": Description is required.";
            continue;
        }
        
        if ($quantity < 1) {
            $errors[] = "Item #" . ($index + 1) . ": Quantity must be at least 1.";
            continue;
        }
        
        if ($unitPrice < 0) {
            $errors[] = "Item #" . ($index + 1) . ": Unit price cannot be negative.";
            continue;
        }
        
        $formData['items'][] = [
            'description' => $description,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal
        ];
        
        $totalAmount += $subtotal;
    }
    
    // Update total amount
    $formData['total_amount'] = $totalAmount;
    
    // Validate at least one item
    if (empty($formData['items'])) {
        $errors[] = 'At least one item is required.';
    }
} else {
    $errors[] = 'No items provided.';
}

// Validate total amount matches
if (abs($formData['total_amount'] - $totalAmount) > 0.01) {
    $errors[] = 'Total amount mismatch. Please refresh and try again.';
}

// If there are validation errors, redirect back
if (!empty($errors)) {
    Session::setFlash('error', implode('<br>', $errors));
    
    if ($action == 'edit' && $requisitionId) {
        header('Location: edit.php?id=' . $requisitionId);
    } else {
        header('Location: create.php');
    }
    exit;
}

// Process the requisition
if ($action == 'create') {
    // Create new requisition
    $result = $requisition->create($formData);
    
    if ($result['success']) {
        $newRequisitionId = $result['requisition_id'];
        
        // Handle file uploads
        if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
            $uploadedCount = 0;
            $fileErrors = [];
            
            // Process multiple file uploads
            foreach ($_FILES['attachments']['name'] as $key => $filename) {
                if (empty($filename)) continue;
                
                $file = [
                    'name' => $_FILES['attachments']['name'][$key],
                    'type' => $_FILES['attachments']['type'][$key],
                    'tmp_name' => $_FILES['attachments']['tmp_name'][$key],
                    'error' => $_FILES['attachments']['error'][$key],
                    'size' => $_FILES['attachments']['size'][$key]
                ];
                
                $uploadResult = $fileUpload->uploadRequisitionDocument(
                    $newRequisitionId,
                    $file,
                    DOC_TYPE_ATTACHMENT
                );
                
                if ($uploadResult['success']) {
                    $uploadedCount++;
                } else {
                    $fileErrors[] = $filename . ': ' . $uploadResult['message'];
                }
            }
            
            // Add file upload info to success message
            if ($uploadedCount > 0) {
                $result['message'] .= " {$uploadedCount} file(s) uploaded successfully.";
            }
            
            if (!empty($fileErrors)) {
                $result['message'] .= " However, some files failed: " . implode(', ', $fileErrors);
            }
        }
        
        Session::setFlash('success', $result['message']);
        
        // Redirect based on whether it's a draft or submitted
        if ($isDraft) {
            header('Location: edit.php?id=' . $newRequisitionId);
        } else {
            header('Location: view.php?id=' . $newRequisitionId);
        }
    } else {
        Session::setFlash('error', $result['message']);
        header('Location: create.php');
    }
    
} elseif ($action == 'edit') {
    // Update existing requisition
    if (!$requisitionId) {
        Session::setFlash('error', 'Invalid requisition ID.');
        header('Location: list.php');
        exit;
    }
    
    $result = $requisition->update($requisitionId, $formData);
    
    if ($result['success']) {
        // Handle file uploads
        if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
            $uploadedCount = 0;
            $fileErrors = [];
            
            foreach ($_FILES['attachments']['name'] as $key => $filename) {
                if (empty($filename)) continue;
                
                $file = [
                    'name' => $_FILES['attachments']['name'][$key],
                    'type' => $_FILES['attachments']['type'][$key],
                    'tmp_name' => $_FILES['attachments']['tmp_name'][$key],
                    'error' => $_FILES['attachments']['error'][$key],
                    'size' => $_FILES['attachments']['size'][$key]
                ];
                
                $uploadResult = $fileUpload->uploadRequisitionDocument(
                    $requisitionId,
                    $file,
                    DOC_TYPE_ATTACHMENT
                );
                
                if ($uploadResult['success']) {
                    $uploadedCount++;
                } else {
                    $fileErrors[] = $filename . ': ' . $uploadResult['message'];
                }
            }
            
            if ($uploadedCount > 0) {
                $result['message'] .= " {$uploadedCount} file(s) uploaded successfully.";
            }
            
            if (!empty($fileErrors)) {
                $result['message'] .= " However, some files failed: " . implode(', ', $fileErrors);
            }
        }
        
        Session::setFlash('success', $result['message']);
        
        // Redirect based on whether it's a draft or submitted
        if ($isDraft) {
            header('Location: edit.php?id=' . $requisitionId);
        } else {
            header('Location: view.php?id=' . $requisitionId);
        }
    } else {
        Session::setFlash('error', $result['message']);
        header('Location: edit.php?id=' . $requisitionId);
    }
    
} else {
    Session::setFlash('error', 'Invalid action.');
    header('Location: list.php');
}

exit;
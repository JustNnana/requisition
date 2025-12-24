<?php
/**
 * GateWey Requisition Management System
 * Save Requisition Handler - FIXED
 * 
 * File: requisitions/save.php
 * Purpose: POST handler for creating and updating requisitions
 * 
 * FIXED: Changed STATUS_APPROVED to proper status check
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
    $redirectUrl = ($_POST['action'] == 'edit' && !empty($_POST['requisition_id']))
        ? build_encrypted_url('edit.php', Sanitizer::int($_POST['requisition_id']))
        : 'create.php';
    header('Location: ' . $redirectUrl);
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

// Capture account details and create additional_info JSON
$additionalInfo = [
    'account_type' => Sanitizer::string($_POST['account_type'] ?? ''),
    'account_name' => Sanitizer::string($_POST['account_name'] ?? ''),
    'bank_name' => Sanitizer::string($_POST['bank_name'] ?? ''),
    'account_number' => Sanitizer::string($_POST['account_number'] ?? '')
];

// Capture form data with category_id
$formData = [
    'purpose' => Sanitizer::string($_POST['purpose'] ?? ''),
    'description' => Sanitizer::string($_POST['description'] ?? ''),
    'category_id' => !empty($_POST['category_id']) ? Sanitizer::int($_POST['category_id']) : null,
    'total_amount' => Sanitizer::float($_POST['total_amount'] ?? '0'),
    'selected_approver_id' => !empty($_POST['selected_approver_id']) ? Sanitizer::int($_POST['selected_approver_id']) : null,
    'is_draft' => $isDraft,
    'additional_info' => json_encode($additionalInfo),
    'items' => []
];

// Validate purpose
if (empty($formData['purpose'])) {
    $errors[] = 'Purpose/Description is required.';
}

// Validate account details (required for non-draft submissions)
if (!$isDraft) {
    if (empty($additionalInfo['account_type'])) {
        $errors[] = 'Account type is required.';
    }
    if (empty($additionalInfo['account_name'])) {
        $errors[] = 'Account name is required.';
    }
    if (empty($additionalInfo['bank_name'])) {
        $errors[] = 'Bank name is required.';
    }
    if (empty($additionalInfo['account_number'])) {
        $errors[] = 'Account number is required.';
    } elseif (!preg_match('/^[0-9]{10}$/', $additionalInfo['account_number'])) {
        $errors[] = 'Account number must be exactly 10 digits.';
    }
}

// Validate category_id (optional but log if present)
if (!empty($formData['category_id'])) {
    // Verify category exists and is active
    try {
        $categoryModel = new RequisitionCategory();
        $category = $categoryModel->getById($formData['category_id']);
        
        if (!$category) {
            $errors[] = 'Invalid category selected.';
        } elseif (!$category['is_active']) {
            $errors[] = 'Selected category is no longer active.';
        }
    } catch (Exception $e) {
        error_log("Category validation error: " . $e->getMessage());
        // Continue without blocking - category validation is not critical
    }
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

// ============================================
// BUDGET VALIDATION CHECK
// ============================================
// Only check budget if NOT a draft AND user is subject to budget rules
if (!$isDraft) {
    $userDepartmentId = Session::getUserDepartmentId();
    $userRoleId = Session::getUserRoleId();
    
    // Finance roles bypass budget checks
    $bypassBudget = in_array($userRoleId, [ROLE_FINANCE_MANAGER, ROLE_FINANCE_MEMBER]);
    
    // Check budget for users with departments (Team Members, Line Managers, Managing Directors)
    if (!$bypassBudget && $userDepartmentId) {
        $checkBudget = in_array($userRoleId, [ROLE_TEAM_MEMBER, ROLE_LINE_MANAGER, ROLE_MANAGING_DIRECTOR]);
        
        if ($checkBudget) {
            try {
                $budgetModel = new Budget();
                $budgetInfo = $budgetModel->getBudgetStats($userDepartmentId);
                
                if ($budgetInfo && $budgetInfo['status'] === 'active') {
                    $availableBudget = (float)$budgetInfo['available_amount'];
                    $requestedAmount = (float)$formData['total_amount'];
                    
                    // ✅ FIXED: For editing, add back the original amount to available budget
                    // Only if budget hasn't been allocated yet (not approved for payment, paid, or completed)
                    if ($action === 'edit' && $requisitionId) {
                        $existingReq = $requisition->getById($requisitionId);
                        // Budget is NOT allocated if status is: draft, rejected, or any pending status
                        if ($existingReq && !in_array($existingReq['status'], [
                            STATUS_APPROVED_FOR_PAYMENT, 
                            STATUS_PAID, 
                            STATUS_COMPLETED
                        ])) {
                            $originalAmount = (float)$existingReq['total_amount'];
                            $availableBudget += $originalAmount;
                        }
                    }
                    
                    // Check if requested amount exceeds available budget
                    if ($requestedAmount > $availableBudget) {
                        $errors[] = sprintf(
                            'Budget Exceeded: This requisition (₦%s) exceeds your department\'s available budget (₦%s). Please reduce the amount or contact your Finance Manager.',
                            number_format($requestedAmount, 2),
                            number_format($availableBudget, 2)
                        );
                    }
                } elseif ($budgetInfo && $budgetInfo['status'] === 'expired') {
                    $errors[] = 'Department Budget Expired: Your department\'s budget period has ended. Please contact your Finance Manager.';
                } elseif (!$budgetInfo) {
                    $errors[] = 'No Budget Configured: Your department does not have an active budget. Please contact your Finance Manager.';
                }
            } catch (Exception $e) {
                error_log("Budget validation error: " . $e->getMessage());
            }
        }
    }
}
// ============================================
// END BUDGET VALIDATION
// ============================================

// If there are validation errors, redirect back
if (!empty($errors)) {
    Session::setFlash('error', implode('<br>', $errors));

    if ($action == 'edit' && $requisitionId) {
        header('Location: ' . build_encrypted_url('edit.php', $requisitionId));
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
        
        // Send email notification (only if not a draft)
        if (!$isDraft) {
            try {
                Notification::send(NOTIF_REQUISITION_SUBMITTED, $newRequisitionId);
            } catch (Exception $e) {
                error_log("Email notification failed: " . $e->getMessage());
                // Don't block the flow if email fails
            }
        }
        
        Session::setFlash('success', $result['message']);

        // Redirect based on whether it's a draft or submitted
        if ($isDraft) {
            header('Location: ' . build_encrypted_url('edit.php', $newRequisitionId));
        } else {
            header('Location: ' . build_encrypted_url('view.php', $newRequisitionId));
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
            header('Location: ' . build_encrypted_url('edit.php', $requisitionId));
        } else {
            header('Location: ' . build_encrypted_url('view.php', $requisitionId));
        }
    } else {
        Session::setFlash('error', $result['message']);
        header('Location: ' . build_encrypted_url('edit.php', $requisitionId));
    }
    
} else {
    Session::setFlash('error', 'Invalid action.');
    header('Location: list.php');
}

exit;
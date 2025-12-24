<?php
/**
 * GateWey Requisition Management System
 * User Management - Assign Department
 * 
 * File: admin/users/assign-department.php
 * Purpose: Reassign user to a different department
 */

// Define access level
define('APP_ACCESS', true);
define('ADMIN_ACCESS', true);

// Include necessary files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../helpers/permissions.php';

// Start session
Session::start();

// Check authentication and authorization
require_once __DIR__ . '/../../middleware/auth-check.php';
require_once __DIR__ . '/../../middleware/role-check.php';
checkRole(ROLE_SUPER_ADMIN);

// Initialize objects
$user = new User();
$department = new Department();
$db = Database::getInstance();

// Get user ID from query string
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$userId) {
    Session::setFlash('error', 'Invalid user ID.');
    header('Location: list.php');
    exit;
}

// Get user data
$userData = $user->getById($userId);

if (!$userData) {
    Session::setFlash('error', 'User not found.');
    header('Location: list.php');
    exit;
}

// Get all active departments
$departments = $department->getAll(true);

// Initialize variables
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Get new department ID
        $newDepartmentId = !empty($_POST['department_id']) ? Sanitizer::int($_POST['department_id']) : null;
        
        // Check if department is required for this role
        $userRoleId = $userData['role_id'];
        $requiresDepartment = in_array($userRoleId, [ROLE_LINE_MANAGER, ROLE_TEAM_MEMBER]);
        
        if ($requiresDepartment && empty($newDepartmentId)) {
            $errors[] = 'Department is required for Line Managers and Team Members.';
        } else {
            // Verify department exists if provided
            if ($newDepartmentId !== null) {
                $departmentData = $department->getById($newDepartmentId);
                if (!$departmentData) {
                    $errors[] = 'Selected department not found.';
                } elseif (!$departmentData['is_active']) {
                    $errors[] = 'Cannot assign to inactive department.';
                }
            }
            
            if (empty($errors)) {
                // Update user department
                $updateData = ['department_id' => $newDepartmentId];
                $result = $user->update($userId, $updateData);
                
                if ($result['success']) {
                    // Log the action
                    if (ENABLE_AUDIT_LOG) {
                        $oldDeptName = $userData['department_name'] ?? 'None';
                        $newDeptName = $newDepartmentId ? ($departmentData['department_name'] ?? 'Unknown') : 'None';

                        $logSql = "INSERT INTO audit_log (user_id, action, description, ip_address, created_at)
                                   VALUES (?, ?, ?, ?, NOW())";
                        $logParams = [
                            Session::getUserId(),
                            AUDIT_USER_UPDATED,
                            "Changed department for user '{$userData['first_name']} {$userData['last_name']}' from '{$oldDeptName}' to '{$newDeptName}'"
                        ];
                        
                        if (LOG_IP_ADDRESS) {
                            $logParams[] = $_SERVER['REMOTE_ADDR'] ?? '';
                        } else {
                            $logParams[] = null;
                        }
                        
                        $db->execute($logSql, $logParams);
                    }
                    
                    Session::setFlash('success', 'User department updated successfully.');
                    header('Location: list.php');
                    exit;
                } else {
                    $errors[] = $result['message'];
                }
            }
        }
    }
}

// Page title
$pageTitle = 'Assign Department';

// Custom CSS
$customCSS = "
.required-field::after {
    content: ' *';
    color: var(--danger);
}
.info-card {
    background-color: var(--background-secondary, #f8f9fa);
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 8px;
    padding: 1rem;
}
";
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Assign Department</h1>
            <p class="content-subtitle">Change user's department assignment</p>
        </div>
        <div>
            <a href="list.php" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>
</div>

<!-- Error Messages -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Main Form -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Change Department Assignment</h5>
            </div>
            <div class="card-body">
                <!-- Current User Info -->
                <div class="info-card mb-4">
                    <h6 class="mb-3"><i class="fas fa-user"></i> Current User Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Name:</strong> 
                                <?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?>
                            </p>
                            <p class="mb-2">
                                <strong>Email:</strong> 
                                <?php echo htmlspecialchars($userData['email']); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Role:</strong> 
                                <?php echo htmlspecialchars($userData['role_name']); ?>
                            </p>
                            <p class="mb-0">
                                <strong>Current Department:</strong> 
                                <?php echo $userData['department_name'] ? 
                                    '<span class="badge badge-primary">' . htmlspecialchars($userData['department_name']) . '</span>' : 
                                    '<span class="badge badge-secondary">None</span>'; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Department Assignment Form -->
                <form method="POST" action="" data-loading>
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                    
                    <!-- Department Selection -->
                    <div class="mb-4">
                        <label for="department_id" class="form-label">
                            New Department
                            <?php if (in_array($userData['role_id'], [ROLE_LINE_MANAGER, ROLE_TEAM_MEMBER])): ?>
                                <span class="required-field"></span>
                            <?php endif; ?>
                        </label>
                        <select id="department_id" 
                                name="department_id" 
                                class="form-control"
                                <?php echo in_array($userData['role_id'], [ROLE_LINE_MANAGER, ROLE_TEAM_MEMBER]) ? 'required' : ''; ?>>
                            
                            <?php if (!in_array($userData['role_id'], [ROLE_LINE_MANAGER, ROLE_TEAM_MEMBER])): ?>
                                <option value="">None (Super Admin/MD/Finance)</option>
                            <?php else: ?>
                                <option value="">-- Select Department --</option>
                            <?php endif; ?>
                            
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"
                                        <?php echo ($userData['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                    (<?php echo htmlspecialchars($dept['department_code']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <?php if (in_array($userData['role_id'], [ROLE_LINE_MANAGER, ROLE_TEAM_MEMBER])): ?>
                            <small class="form-text">Department assignment is required for Line Managers and Team Members.</small>
                        <?php else: ?>
                            <small class="form-text">Department assignment is optional for this role.</small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="list.php" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Help Panel -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle"></i> Department Requirements
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-primary">Required Departments:</h6>
                    <ul class="mb-0" style="font-size: var(--font-size-sm);">
                        <li><strong>Line Manager</strong> - Must be assigned to a department to manage team members</li>
                        <li><strong>Team Member</strong> - Must be assigned to a department for requisition workflow</li>
                    </ul>
                </div>
                
                <div class="mb-0">
                    <h6 class="text-primary">Optional Departments:</h6>
                    <ul class="mb-0" style="font-size: var(--font-size-sm);">
                        <li><strong>Super Admin</strong> - System-wide access</li>
                        <li><strong>Managing Director</strong> - Organization-wide oversight</li>
                        <li><strong>Finance Manager</strong> - Financial oversight</li>
                        <li><strong>Finance Member</strong> - Payment processing</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Important Notes
                </h5>
            </div>
            <div class="card-body">
                <ul class="mb-0" style="font-size: var(--font-size-sm);">
                    <li>Changing departments may affect existing requisitions</li>
                    <li>User will only see requisitions related to their new department</li>
                    <li>Department change is logged in the audit trail</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
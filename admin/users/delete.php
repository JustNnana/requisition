<?php
/**
 * GateWey Requisition Management System
 * User Management - Delete User
 * 
 * File: admin/users/delete.php
 * Purpose: Delete user with confirmation
 */

// Define access level
define('APP_ACCESS', true);
define('ADMIN_ACCESS', true);

// Include necessary files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../helpers/permissions.php';

// Start session
Session::start();

// Check authentication and authorization
require_once __DIR__ . '/../../middleware/auth-check.php';
require_once __DIR__ . '/../../middleware/role-check.php';
checkRole(ROLE_SUPER_ADMIN);

// Initialize objects
$user = new User();

// Get user ID from query string
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$userId) {
    Session::setFlash('error', 'Invalid user ID.');
    header('Location: list.php');
    exit;
}

// Prevent self-deletion
if ($userId == Session::get('user')['id']) {
    Session::setFlash('error', 'You cannot delete your own account.');
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

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        Session::setFlash('error', 'Invalid security token. Please try again.');
        header('Location: list.php');
        exit;
    }
    
    // Delete user
    $result = $user->delete($userId);
    
    if ($result['success']) {
        Session::setFlash('success', 'User deleted successfully.');
    } else {
        Session::setFlash('error', $result['message']);
    }
    
    header('Location: list.php');
    exit;
}

// Page title
$pageTitle = 'Delete User';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Delete User</h1>
            <p class="content-subtitle">Confirm user deletion</p>
        </div>
        <div>
            <a href="list.php" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>
</div>

<!-- Confirmation Card -->
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                
                <p class="mb-4">Are you sure you want to delete this user?</p>
                
                <!-- User Info -->
                <div class="table-responsive mb-4">
                    <table class="table">
                        <tr>
                            <th style="width: 40%;">Name:</th>
                            <td><?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($userData['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Role:</th>
                            <td><?php echo htmlspecialchars($userData['role_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Department:</th>
                            <td><?php echo $userData['department_name'] ? htmlspecialchars($userData['department_name']) : '<span class="text-muted">N/A</span>'; ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> All requisitions, approvals, and audit logs associated with this user will be CASCADE deleted from the system.
                </div>
                
                <!-- Delete Form -->
                <form method="POST" action="">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="list.php" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Yes, Delete User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php
/**
 * GateWey Requisition Management System
 * User Management - Delete User
 * 
 * File: admin/users/delete.php
 * Purpose: Delete user directly with confirmation
 */

// Define access level
// Add this temporarily for debugging
error_log("Delete.php accessed - User ID: " . ($_GET['id'] ?? 'none') . ", Confirm: " . ($_GET['confirm'] ?? 'none'));

// Then continue with the rest of your code...
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

// Get user ID from query string (encrypted)
$userId = get_encrypted_id();

if (!$userId) {
    Session::setFlash('error', 'Invalid user ID.');
    header('Location: list.php');
    exit;
}

// Prevent self-deletion
$currentUser = Session::get('user');
if ($userId == ($currentUser['id'] ?? 0)) {
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

// Check if confirmation is provided (via GET parameter)
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
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
                    </table>
                </div>
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="list.php" class="btn btn-ghost">Cancel</a>
                    <a href="<?php echo build_encrypted_url('delete.php', $userId, 'id', ['confirm' => 'yes']); ?>" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Yes, Delete User
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
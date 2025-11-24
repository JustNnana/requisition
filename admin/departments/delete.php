<?php
/**
 * GateWey Requisition Management System
 * Department Management - Delete Department
 * 
 * File: admin/departments/delete.php
 */

define('APP_ACCESS', true);
define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../classes/Department.php';
require_once __DIR__ . '/../../helpers/permissions.php';

Session::start();
require_once __DIR__ . '/../../middleware/auth-check.php';
require_once __DIR__ . '/../../middleware/role-check.php';
checkRole(ROLE_SUPER_ADMIN);

$department = new Department();
$departmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$departmentId) {
    Session::setFlash('error', 'Invalid department ID.');
    header('Location: list.php');
    exit;
}

$departmentData = $department->getById($departmentId);
if (!$departmentData) {
    Session::setFlash('error', 'Department not found.');
    header('Location: list.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        Session::setFlash('error', 'Invalid security token.');
        header('Location: list.php');
        exit;
    }
    
    $result = $department->delete($departmentId);
    
    if ($result['success']) {
        Session::setFlash('success', 'Department deleted successfully.');
    } else {
        Session::setFlash('error', $result['message']);
    }
    
    header('Location: list.php');
    exit;
}

$pageTitle = 'Delete Department';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title">Delete Department</h1>
            <p class="content-subtitle">Confirm department deletion</p>
        </div>
        <a href="list.php" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                
                <p>Are you sure you want to delete this department?</p>
                
                <div class="table-responsive mb-4">
                    <table class="table">
                        <tr>
                            <th style="width: 40%;">Department Name:</th>
                            <td><?php echo htmlspecialchars($departmentData['department_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Department Code:</th>
                            <td><?php echo htmlspecialchars($departmentData['department_code']); ?></td>
                        </tr>
                        <tr>
                            <th>Total Users:</th>
                            <td><span class="badge badge-primary"><?php echo $departmentData['user_count']; ?></span></td>
                        </tr>
                        <tr>
                            <th>Total Requisitions:</th>
                            <td><span class="badge badge-warning"><?php echo $departmentData['requisition_count']; ?></span></td>
                        </tr>
                    </table>
                </div>
                
                <?php if ($departmentData['user_count'] > 0 || $departmentData['requisition_count'] > 0): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Important:</strong> This department cannot be deleted because it has <?php echo $departmentData['user_count']; ?> user(s) and <?php echo $departmentData['requisition_count']; ?> requisition(s). Please reassign or delete them first.
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="list.php" class="btn btn-primary">Back to Departments</a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        This department has no users or requisitions and can be safely deleted.
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="list.php" class="btn btn-ghost">Cancel</a>
                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Yes, Delete Department</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php
/**
 * Diagnostic Script - Check Requisition Department Assignments
 * This will show us what's actually stored in the database
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Get database instance
$db = Database::getInstance();

echo "<h2>Requisition Department Diagnostic</h2>";
echo "<p>This shows what's actually stored in the database for each requisition.</p>";

// Query to get requisition details with BOTH user's department and requisition's department
$sql = "SELECT 
    r.id,
    r.requisition_number,
    r.purpose,
    r.department_id as req_department_id,
    r.user_id,
    u.first_name,
    u.last_name,
    u.department_id as user_department_id,
    d1.department_name as requisition_department,
    d2.department_name as user_department
FROM requisitions r
INNER JOIN users u ON r.user_id = u.id
LEFT JOIN departments d1 ON r.department_id = d1.id
LEFT JOIN departments d2 ON u.department_id = d2.id
WHERE r.status != 'draft'
ORDER BY r.requisition_number ASC";

$requisitions = $db->fetchAll($sql);

echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%; margin-top: 20px;'>";
echo "<thead style='background-color: #4A90E2; color: white;'>";
echo "<tr>";
echo "<th>Requisition #</th>";
echo "<th>Purpose</th>";
echo "<th>Created By</th>";
echo "<th>User's Department</th>";
echo "<th>Requisition's Department ID</th>";
echo "<th>Requisition's Department</th>";
echo "<th>Match?</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

foreach ($requisitions as $req) {
    $match = ($req['user_department'] === $req['requisition_department']) ? '✓ Yes' : '✗ NO - MISMATCH';
    $rowColor = ($req['user_department'] === $req['requisition_department']) ? '#E8F4F8' : '#FFCDD2';
    
    echo "<tr style='background-color: {$rowColor};'>";
    echo "<td><strong>{$req['requisition_number']}</strong></td>";
    echo "<td>{$req['purpose']}</td>";
    echo "<td>{$req['first_name']} {$req['last_name']}</td>";
    echo "<td>{$req['user_department']}</td>";
    echo "<td>{$req['req_department_id']}</td>";
    echo "<td><strong>{$req['requisition_department']}</strong></td>";
    echo "<td>{$match}</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";

echo "<hr style='margin-top: 30px;'>";
echo "<h3>Key Insights:</h3>";
echo "<ul>";
echo "<li><strong>User's Department:</strong> The department the user is assigned to</li>";
echo "<li><strong>Requisition's Department:</strong> The department the requisition belongs to (stored in requisitions.department_id)</li>";
echo "<li><strong>Match:</strong> These should match if users always create requisitions for their own department</li>";
echo "<li><strong>Mismatch (Red rows):</strong> Indicates requisitions created for a different department</li>";
echo "</ul>";

echo "<hr style='margin-top: 30px;'>";
echo "<h3>What We're Looking For:</h3>";
echo "<p>If you see RED rows (mismatches), it means:</p>";
echo "<ol>";
echo "<li>The requisition was created for a different department than the user's home department</li>";
echo "<li>Reports SHOULD use the <strong>Requisition's Department</strong> (not User's Department)</li>";
echo "<li>If reports are showing wrong departments, it means the requisitions.department_id field has wrong values</li>";
echo "</ol>";

echo "<hr style='margin-top: 30px;'>";
echo "<h3>Departments Table:</h3>";

$departments = $db->fetchAll("SELECT id, department_name, department_code FROM departments ORDER BY id ASC");

echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; margin-top: 10px;'>";
echo "<thead style='background-color: #4A90E2; color: white;'>";
echo "<tr><th>ID</th><th>Department Name</th><th>Department Code</th></tr>";
echo "</thead>";
echo "<tbody>";

foreach ($departments as $dept) {
    echo "<tr>";
    echo "<td>{$dept['id']}</td>";
    echo "<td>{$dept['department_name']}</td>";
    echo "<td>{$dept['department_code']}</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
?>
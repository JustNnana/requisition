<?php
/**
 * CONSTANTS CHECK
 * Upload to ROOT and visit
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/config/config.php';

echo "<!DOCTYPE html><html><head><title>Constants Check</title>";
echo "<style>body{font-family:Arial;max-width:800px;margin:30px auto;padding:20px;background:#f5f5f5;}";
echo ".error{background:#fee;border-left:4px solid #f44336;padding:15px;color:#c62828;margin:10px 0;}";
echo ".success{background:#d4edda;border-left:4px solid #4CAF50;padding:15px;color:#155724;margin:10px 0;}";
echo "h1{color:#333;}table{width:100%;border-collapse:collapse;background:white;margin:15px 0;}";
echo "td,th{padding:10px;text-align:left;border-bottom:1px solid #ddd;}th{background:#f8f9fa;font-weight:600;}";
echo "</style></head><body>";

echo "<h1>🔍 Constants Check</h1>";

// Check if role constants are defined
$constants = [
    'ROLE_SUPER_ADMIN' => 1,
    'ROLE_MANAGING_DIRECTOR' => 2,
    'ROLE_FINANCE_MANAGER' => 3,
    'ROLE_FINANCE_MEMBER' => 4,
    'ROLE_LINE_MANAGER' => 5,
    'ROLE_TEAM_MEMBER' => 6
];

$allDefined = true;

echo "<table>";
echo "<tr><th>Constant</th><th>Expected Value</th><th>Actual Value</th><th>Status</th></tr>";

foreach ($constants as $name => $expected) {
    $defined = defined($name);
    $actual = $defined ? constant($name) : 'NOT DEFINED';
    $match = $defined && constant($name) === $expected;
    
    if (!$match) $allDefined = false;
    
    echo "<tr>";
    echo "<td><code>$name</code></td>";
    echo "<td>$expected</td>";
    echo "<td>" . ($defined ? $actual : '<span style="color:#f44336;">NOT DEFINED</span>') . "</td>";
    echo "<td>" . ($match ? '<span style="color:#4CAF50;">✓ OK</span>' : '<span style="color:#f44336;">✗ FAIL</span>') . "</td>";
    echo "</tr>";
}

echo "</table>";

if ($allDefined) {
    echo "<div class='success'>";
    echo "<strong>✓ ALL ROLE CONSTANTS ARE DEFINED CORRECTLY!</strong><br>";
    echo "The issue is not with constants. Check Session::getUserRoleId() implementation.";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<strong>✗ ROLE CONSTANTS ARE NOT DEFINED!</strong><br><br>";
    echo "<strong>This is why is_team_member() returns false!</strong><br><br>";
    echo "<strong>FIX:</strong> Edit config/config.php and add this line AFTER the APP_ACCESS check:<br>";
    echo "<pre style='background:#2d2d2d;color:#f8f8f2;padding:10px;border-radius:4px;overflow-x:auto;'>require_once CONFIG_PATH . '/constants.php';</pre>";
    echo "Or if CONFIG_PATH is not defined:<br>";
    echo "<pre style='background:#2d2d2d;color:#f8f8f2;padding:10px;border-radius:4px;overflow-x:auto;'>require_once __DIR__ . '/constants.php';</pre>";
    echo "</div>";
}

// Test the permission function
echo "<h2>Permission Function Test</h2>";

Session::start();

if (Session::isLoggedIn()) {
    $roleId = Session::getUserRoleId();
    echo "<p>Your Role ID: <strong>$roleId</strong></p>";
    
    if (defined('ROLE_TEAM_MEMBER')) {
        echo "<p>ROLE_TEAM_MEMBER constant: <strong>" . ROLE_TEAM_MEMBER . "</strong></p>";
        echo "<p>Comparison: $roleId === " . ROLE_TEAM_MEMBER . " = " . ($roleId === ROLE_TEAM_MEMBER ? 'TRUE' : 'FALSE') . "</p>";
        
        if (function_exists('is_team_member')) {
            $result = is_team_member();
            echo "<p>is_team_member() result: <strong>" . ($result ? 'TRUE' : 'FALSE') . "</strong></p>";
        }
    } else {
        echo "<p style='color:#f44336;'>ROLE_TEAM_MEMBER is NOT defined!</p>";
    }
} else {
    echo "<p>Not logged in - cannot test</p>";
}

echo "<div style='background:#fee;border:2px solid #f44336;padding:15px;text-align:center;margin:20px 0;'>";
echo "<strong style='color:#f44336;'>DELETE THIS FILE!</strong>";
echo "</div>";

echo "</body></html>";
?>
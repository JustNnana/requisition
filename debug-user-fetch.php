<?php
/**
 * DEBUG ONLY - Remove this file after debugging
 * 
 * File: debug-user-fetch.php
 * Purpose: Check what User::getByEmail() returns
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/config/config.php';

$email = 'nnanamadumere@gmail.com'; // Change this to test different users

echo "<h1>User Fetch Debug</h1>";
echo "<h2>Testing email: {$email}</h2>";
echo "<pre>";

$user = new User();
$userData = $user->getByEmail($email);

if ($userData) {
    echo "SUCCESS - User found:\n\n";
    print_r($userData);
    
    echo "\n\nSpecific fields:\n";
    echo "ID: " . var_export($userData['id'] ?? 'MISSING', true) . "\n";
    echo "First Name: " . var_export($userData['first_name'] ?? 'MISSING', true) . "\n";
    echo "Last Name: " . var_export($userData['last_name'] ?? 'MISSING', true) . "\n";
    echo "Email: " . var_export($userData['email'] ?? 'MISSING', true) . "\n";
    echo "Role ID: " . var_export($userData['role_id'] ?? 'MISSING', true) . "\n";
    
    echo "\n\nFull Name Test:\n";
    $firstName = $userData['first_name'] ?? '';
    $lastName = $userData['last_name'] ?? '';
    $fullName = trim($firstName . ' ' . $lastName);
    echo "Full Name: '{$fullName}'\n";
    
    if (empty($fullName)) {
        echo "\n⚠️ WARNING: Full name is EMPTY!\n";
        echo "First name is: '" . $firstName . "'\n";
        echo "Last name is: '" . $lastName . "'\n";
    }
} else {
    echo "❌ ERROR - User NOT found in database!\n";
    echo "Email searched: {$email}\n";
}

echo "</pre>";

// Test the SQL query directly
echo "<hr>";
echo "<h2>Direct SQL Test</h2>";
echo "<pre>";

$db = Database::getInstance();
$sql = "SELECT u.*, r.role_name, r.role_code, d.department_name, d.department_code
        FROM users u
        JOIN roles r ON u.role_id = r.id
        LEFT JOIN departments d ON u.department_id = d.id
        WHERE u.email = ? AND u.is_active = 1";

echo "SQL Query:\n{$sql}\n\n";
echo "Parameter: {$email}\n\n";

$result = $db->fetchOne($sql, [$email]);
echo "Result:\n";
print_r($result);

echo "</pre>";
?>
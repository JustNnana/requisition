<?php
/**
 * DEBUG ONLY - Remove this file after debugging
 * 
 * File: debug-session.php
 * Purpose: Check what's stored in the session
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/config/config.php';

Session::start();

// Check if logged in
if (!Session::isLoggedIn()) {
    die('Not logged in. Please login first.');
}

echo "<h1>Session Debug Information</h1>";
echo "<pre>";

echo "<h2>1. Raw Session Data:</h2>";
print_r($_SESSION);

echo "\n\n<h2>2. Session Helper Methods:</h2>";
echo "User ID: " . var_export(Session::getUserId(), true) . "\n";
echo "User Email: " . var_export(Session::getUserEmail(), true) . "\n";
echo "User Full Name: " . var_export(Session::getUserFullName(), true) . "\n";
echo "User Role ID: " . var_export(Session::getUserRoleId(), true) . "\n";
echo "User Department ID: " . var_export(Session::getUserDepartmentId(), true) . "\n";

echo "\n\n<h2>3. Check SESSION_USER_KEY:</h2>";
if (isset($_SESSION['user_data'])) {
    echo "user_data exists:\n";
    print_r($_SESSION['user_data']);
} else {
    echo "user_data NOT SET\n";
}

echo "\n\n<h2>4. Check Individual Keys:</h2>";
echo "user_first_name: " . var_export($_SESSION['user_first_name'] ?? 'NOT SET', true) . "\n";
echo "user_last_name: " . var_export($_SESSION['user_last_name'] ?? 'NOT SET', true) . "\n";
echo "user_email: " . var_export($_SESSION['user_email'] ?? 'NOT SET', true) . "\n";
echo "user_id: " . var_export($_SESSION['user_id'] ?? 'NOT SET', true) . "\n";

echo "\n\n<h2>5. Get User Data from Database:</h2>";
$user = new User();
$userData = $user->getById(Session::getUserId());
if ($userData) {
    echo "Database data:\n";
    print_r($userData);
} else {
    echo "Could not fetch user from database\n";
}

echo "</pre>";

echo '<br><br><a href="' . BASE_URL . '/dashboard/index.php">Back to Dashboard</a>';
?>
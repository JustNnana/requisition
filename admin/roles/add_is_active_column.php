<?php
/**
 * Migration Script: Add is_active column to roles table
 * Run this once to add the is_active field to the roles table
 */

// Define access level
define('APP_ACCESS', true);
define('ADMIN_ACCESS', true);

// Include necessary files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

// Get database instance
$db = Database::getInstance();

try {
    // Check if column already exists
    $result = $db->fetchAll("SHOW COLUMNS FROM roles LIKE 'is_active'");

    if (empty($result)) {
        // Add is_active column
        $sql = "ALTER TABLE roles
                ADD COLUMN is_active TINYINT(1) DEFAULT 1 NOT NULL
                AFTER can_view_all,
                ADD INDEX idx_active (is_active)";

        $db->execute($sql);

        echo "✅ SUCCESS: Added is_active column to roles table.\n";
        echo "All existing roles have been set as active (is_active = 1).\n";
    } else {
        echo "ℹ️ INFO: is_active column already exists in roles table.\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    error_log("Add is_active column error: " . $e->getMessage());
}

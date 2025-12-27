<?php
/**
 * SQL File Reorder Script
 * Reorganizes SQL dump to prevent foreign key errors
 */

$inputFile = __DIR__ . '/gateweyc_requisition.sql';
$outputFile = __DIR__ . '/gateweyc_requisition_FIXED.sql';

if (!file_exists($inputFile)) {
    die("Error: Input file not found: $inputFile\n");
}

echo "Reading SQL file...\n";
$content = file_get_contents($inputFile);

// Extract different sections
$createTables = [];
$alterTablesPK = [];
$alterTablesIndexes = [];
$insertStatements = [];
$alterTablesFKs = [];
$views = [];

// Remove MySQL comments (-- and #)
$content = preg_replace('/^--.*$/m', '', $content);
$content = preg_replace('/^#.*$/m', '', $content);

// Split by semicolons to get complete statements
$statements = preg_split('/;\s*$/m', $content, -1, PREG_SPLIT_NO_EMPTY);

foreach ($statements as $statement) {
    $stmt = trim($statement);

    // Skip empty statements
    if (empty($stmt)) {
        continue;
    }

    // Handle CREATE TABLE and INSERT statements directly
    if (preg_match('/^CREATE TABLE `v_/s', $stmt)) {
        $views[] = $stmt . ';';
        continue;
    } elseif (preg_match('/^CREATE TABLE/s', $stmt)) {
        $createTables[] = $stmt . ';';
        continue;
    } elseif (preg_match('/^INSERT INTO/s', $stmt)) {
        $insertStatements[] = $stmt . ';';
        continue;
    }

    // Handle ALTER TABLE statements - these can have multiple ADD clauses
    if (preg_match('/^ALTER TABLE/s', $stmt)) {
        // Extract table name
        if (preg_match('/^ALTER TABLE `([^`]+)`/s', $stmt, $matches)) {
            $tableName = $matches[1];

            // Split by ADD clauses and process each one separately
            $addClauses = preg_split('/,\s*ADD\s+/s', $stmt);

            foreach ($addClauses as $index => $clause) {
                $clause = trim($clause);

                // First clause includes "ALTER TABLE ... ADD", others just have the constraint
                if ($index === 0) {
                    // Extract what comes after the first ADD
                    if (preg_match('/ADD\s+(.+)$/s', $clause, $addMatch)) {
                        $constraint = trim($addMatch[1]);
                    } else {
                        continue;
                    }
                } else {
                    $constraint = $clause;
                }

                // Remove trailing semicolon and comma if present
                $constraint = rtrim($constraint, ';,');

                // Categorize each constraint
                if (preg_match('/^PRIMARY KEY/s', $constraint)) {
                    $alterTablesPK[] = "ALTER TABLE `$tableName` ADD $constraint;";
                } elseif (preg_match('/^CONSTRAINT.*FOREIGN KEY/s', $constraint)) {
                    $alterTablesFKs[] = "ALTER TABLE `$tableName` ADD $constraint;";
                } elseif (preg_match('/^(UNIQUE )?KEY/s', $constraint)) {
                    $alterTablesIndexes[] = "ALTER TABLE `$tableName` ADD $constraint;";
                }
            }
        }
    }
}

echo "Found:\n";
echo "  - " . count($createTables) . " CREATE TABLE statements\n";
echo "  - " . count($alterTablesPK) . " PRIMARY KEY statements\n";
echo "  - " . count($alterTablesIndexes) . " INDEX statements\n";
echo "  - " . count($insertStatements) . " INSERT statements\n";
echo "  - " . count($alterTablesFKs) . " FOREIGN KEY statements\n";
echo "  - " . count($views) . " VIEW statements\n";

// Build the fixed SQL file
$output = "-- phpMyAdmin SQL Dump - FIXED VERSION
-- Properly ordered for clean import
-- Generated: " . date('Y-m-d H:i:s') . "
--
-- IMPORTANT: This file has been reorganized to prevent foreign key errors
-- Order: DROP → CREATE → PRIMARY KEYS → INDEXES → INSERT DATA → FOREIGN KEYS → VIEWS

SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
START TRANSACTION;
SET time_zone = \"+00:00\";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Disable foreign key checks for clean import
SET FOREIGN_KEY_CHECKS=0;

-- ============================================
-- STEP 1: DROP ALL EXISTING TABLES & VIEWS
-- ============================================

DROP TABLE IF EXISTS `v_requisitions_summary`;
DROP TABLE IF EXISTS `v_pending_approvals`;
DROP TABLE IF EXISTS `v_active_users`;
DROP TABLE IF EXISTS `requisition_items`;
DROP TABLE IF EXISTS `requisition_documents`;
DROP TABLE IF EXISTS `requisition_approvals`;
DROP TABLE IF EXISTS `requisitions`;
DROP TABLE IF EXISTS `requisition_categories`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `help_support`;
DROP TABLE IF EXISTS `department_budgets`;
DROP TABLE IF EXISTS `budget_allocations`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `audit_log`;

-- ============================================
-- STEP 2: CREATE ALL TABLES
-- ============================================

";

// Add CREATE TABLE statements
foreach ($createTables as $stmt) {
    $output .= $stmt . "\n\n";
}

$output .= "-- ============================================
-- STEP 3: ADD PRIMARY KEYS
-- ============================================

";

// Add PRIMARY KEY statements
foreach ($alterTablesPK as $stmt) {
    $output .= $stmt . "\n\n";
}

$output .= "-- ============================================
-- STEP 4: ADD INDEXES
-- ============================================

";

// Add INDEX statements
foreach ($alterTablesIndexes as $stmt) {
    $output .= $stmt . "\n\n";
}

$output .= "-- ============================================
-- STEP 5: INSERT DATA
-- ============================================

";

// Add INSERT statements
foreach ($insertStatements as $stmt) {
    $output .= $stmt . "\n\n";
}

$output .= "-- ============================================
-- STEP 6: ADD FOREIGN KEY CONSTRAINTS
-- ============================================

";

// Add FOREIGN KEY statements
foreach ($alterTablesFKs as $stmt) {
    $output .= $stmt . "\n\n";
}

$output .= "-- ============================================
-- STEP 7: CREATE VIEWS
-- ============================================

";

// Add VIEW statements
foreach ($views as $stmt) {
    $output .= $stmt . "\n\n";
}

$output .= "
-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
";

// Write to output file
file_put_contents($outputFile, $output);

echo "\n✅ SUCCESS!\n";
echo "Fixed SQL file created: $outputFile\n";
echo "\nYou can now import this file into phpMyAdmin without errors.\n";
echo "File size: " . number_format(filesize($outputFile)) . " bytes\n";

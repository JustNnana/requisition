<?php
/**
 * GateWey Requisition Management System
 * Excel Export Handler
 * 
 * File: reports/export-excel.php
 * Purpose: Handle Excel report export requests
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';

// Load PHPSpreadsheet via Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Load status indicator helper
require_once __DIR__ . '/../helpers/status-indicator.php';

// Initialize classes
$report = new Report();
$exporter = new ExcelExporter();

// Get report type
$reportType = Sanitizer::string($_GET['type'] ?? 'personal');

// Validate report type
$validTypes = ['personal', 'department', 'organization'];
if (!in_array($reportType, $validTypes)) {
    Session::setFlash('error', 'Invalid report type.');
    header('Location: ' . BASE_URL . '/reports/personal.php');
    exit;
}

// Check permissions based on report type
switch ($reportType) {
    case 'department':
        if (!is_line_manager()) {
            Session::setFlash('error', 'Only Line Managers can export department reports.');
            header('Location: ' . BASE_URL . '/reports/personal.php');
            exit;
        }
        break;
        
    case 'organization':
        if (!is_managing_director() && !is_finance_manager()) {
            Session::setFlash('error', 'Only Managing Director and Finance Manager can export organization reports.');
            header('Location: ' . BASE_URL . '/reports/personal.php');
            exit;
        }
        break;
}

// Get filters from request
$filters = [
    'period' => Sanitizer::string($_GET['period'] ?? ''),
    'date_from' => Sanitizer::string($_GET['date_from'] ?? ''),
    'date_to' => Sanitizer::string($_GET['date_to'] ?? ''),
    'status' => Sanitizer::string($_GET['status'] ?? ''),
    'department_id' => Sanitizer::int($_GET['department_id'] ?? 0),
    'user_id' => Sanitizer::int($_GET['user_id'] ?? 0),
    'search' => Sanitizer::string($_GET['search'] ?? '')
];

try {
    // Get export data
    $exportData = $report->getExportData($filters, $reportType);
    
    if (!$exportData) {
        Session::setFlash('error', 'Failed to generate export data. Please try again.');
        
        // Redirect back to appropriate report page
        $redirectUrl = BASE_URL . '/reports/';
        switch ($reportType) {
            case 'department':
                $redirectUrl .= 'department.php';
                break;
            case 'organization':
                $redirectUrl .= 'organization.php';
                break;
            default:
                $redirectUrl .= 'personal.php';
                break;
        }
        
        header('Location: ' . $redirectUrl . '?' . http_build_query($filters));
        exit;
    }
    
    // Export to Excel
    $success = $exporter->exportReport($exportData, $reportType);
    
    if (!$success) {
        throw new Exception('Export failed');
    }
    
    // Exit after successful export (file is already sent to browser)
    exit;
    
} catch (Exception $e) {
    error_log("Excel export error: " . $e->getMessage());
    
    Session::setFlash('error', 'An error occurred while exporting the report. Please try again.');
    
    // Redirect back to appropriate report page
    $redirectUrl = BASE_URL . '/reports/';
    switch ($reportType) {
        case 'department':
            $redirectUrl .= 'department.php';
            break;
        case 'organization':
            $redirectUrl .= 'organization.php';
            break;
        default:
            $redirectUrl .= 'personal.php';
            break;
    }
    
    header('Location: ' . $redirectUrl . '?' . http_build_query($filters));
    exit;
}
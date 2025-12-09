<?php
/**
 * GateWey Requisition Management System
 * Excel Export - FIXED for All Report Types
 * 
 * File: reports/export-excel.php
 * Purpose: Export comprehensive reports to Excel with all filters applied
 */

// CRITICAL: Suppress all output before Excel generation
error_reporting(0);
ini_set('display_errors', 0);

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';

// Load PHPSpreadsheet
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

try {
    // Initialize classes
    $report = new Report();
    
    // Get filters and report type
    $type = Sanitizer::string($_GET['type'] ?? 'personal');
    $filters = [
        'period' => Sanitizer::string($_GET['period'] ?? ''),
        'date_from' => Sanitizer::string($_GET['date_from'] ?? ''),
        'date_to' => Sanitizer::string($_GET['date_to'] ?? ''),
        'status' => Sanitizer::string($_GET['status'] ?? ''),
        'category' => Sanitizer::string($_GET['category'] ?? ''),
        'search' => Sanitizer::string($_GET['search'] ?? ''),
        'interval' => Sanitizer::string($_GET['interval'] ?? 'daily'),
        'user_id' => Sanitizer::int($_GET['user_id'] ?? 0)
    ];
    
    // Generate report based on type
    switch ($type) {
        case 'department':
            $reportData = $report->generateDepartmentReport($filters, 1, 10000);
            $reportTitle = 'Department Requisition Report';
            break;
        case 'organization':
            $reportData = $report->generateOrganizationReport($filters, 1, 10000);
            $reportTitle = 'Organization-Wide Requisition Report';
            break;
        case 'personal':
        default:
            $reportData = $report->generatePersonalReport($filters, 1, 10000);
            $reportTitle = 'Personal Requisition Report';
            break;
    }
    
    if (!$reportData['success']) {
        throw new Exception($reportData['message'] ?? 'Error generating report');
    }
    
    $requisitions = $reportData['requisitions'];
    $statistics = $reportData['statistics'];
    
    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('GateWey Requisition System')
        ->setTitle($reportTitle)
        ->setSubject('Requisition Report')
        ->setDescription('Comprehensive requisition report with analytics')
        ->setKeywords('requisition report excel analytics')
        ->setCategory('Reports');
    
    // Set up styles
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 12
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4A90E2']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ];
    
    $titleStyle = [
        'font' => [
            'bold' => true,
            'size' => 16,
            'color' => ['rgb' => '2C3E50']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER
        ]
    ];
    
    $statsHeaderStyle = [
        'font' => [
            'bold' => true,
            'size' => 11,
            'color' => ['rgb' => '2C3E50']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'E8F4F8']
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ]
    ];
    
    $dataStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'CCCCCC']
            ]
        ]
    ];
    
    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(18);
    $sheet->getColumnDimension('B')->setWidth(14);
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(35);
    $sheet->getColumnDimension('E')->setWidth(20);
    $sheet->getColumnDimension('F')->setWidth(15);
    $sheet->getColumnDimension('G')->setWidth(18);
    $sheet->getColumnDimension('H')->setWidth(20);
    
    // Add title
    $sheet->mergeCells('A1:H1');
    $sheet->setCellValue('A1', $reportTitle);
    $sheet->getStyle('A1')->applyFromArray($titleStyle);
    $sheet->getRowDimension(1)->setRowHeight(30);
    
    // Add generation info
    $currentDate = date('F d, Y g:i A');
    $currentUser = Session::get('full_name') ?? Session::get('username');
    $sheet->mergeCells('A2:H2');
    $sheet->setCellValue('A2', "Generated by: {$currentUser} on {$currentDate}");
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9);
    
    // Add filter information
    $row = 3;
    if (!empty($filters['period']) || !empty($filters['date_from']) || !empty($filters['status']) || !empty($filters['category'])) {
        $sheet->mergeCells("A{$row}:H{$row}");
        $filterText = 'Applied Filters: ';
        $filterParts = [];
        
        if (!empty($filters['period'])) {
            $filterParts[] = 'Period: ' . ucfirst($filters['period']);
        } elseif (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $filterParts[] = 'Date Range: ' . $filters['date_from'] . ' to ' . $filters['date_to'];
        }
        
        if (!empty($filters['status'])) {
            $filterParts[] = 'Status: ' . get_status_label($filters['status']);
        }
        
        if (!empty($filters['category'])) {
            $filterParts[] = 'Category: ' . $filters['category'];
        }
        
        $filterText .= implode(' | ', $filterParts);
        $sheet->setCellValue("A{$row}", $filterText);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(9);
        $row++;
    }
    
    // Add empty row
    $row++;
    
    // Add statistics section
    $sheet->mergeCells("A{$row}:H{$row}");
    $sheet->setCellValue("A{$row}", 'SUMMARY STATISTICS');
    $sheet->getStyle("A{$row}")->applyFromArray($titleStyle);
    $sheet->getStyle("A{$row}")->getFont()->setSize(13);
    $row++;
    
    // Statistics headers
    $statsHeaders = ['Metric', 'Value', 'Metric', 'Value'];
    $col = 'A';
    foreach ($statsHeaders as $header) {
        $sheet->setCellValue($col . $row, $header);
        $sheet->getStyle($col . $row)->applyFromArray($statsHeaderStyle);
        $col++;
    }
    $row++;
    
    // Statistics data
    $statsData = [
        ['Total Requisitions', number_format($statistics['total_count']), 'Pending', number_format($statistics['pending_count'] ?? 0)],
        ['Total Amount','₦' .  number_format((float)$statistics['total_amount'], 2), 'Approved', number_format($statistics['approved_count'] ?? 0)],
        ['Average Amount', '₦' .  number_format((float)$statistics['average_amount'], 2), 'Rejected', number_format($statistics['rejected_count'] ?? 0)],
        ['Highest Amount', '₦' .  number_format((float)$statistics['max_amount'], 2), 'Completed', number_format($statistics['completed_count'] ?? 0)]
    ];
    
    foreach ($statsData as $statRow) {
        $col = 'A';
        foreach ($statRow as $value) {
            $sheet->setCellValue($col . $row, $value);
            if ($col === 'B' || $col === 'D') {
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
            }
            $sheet->getStyle($col . $row)->applyFromArray($dataStyle);
            $col++;
        }
        $row++;
    }
    
    // Add empty rows
    $row += 2;
    
    // Add requisitions section
    $sheet->mergeCells("A{$row}:H{$row}");
    $sheet->setCellValue("A{$row}", 'DETAILED REQUISITIONS');
    $sheet->getStyle("A{$row}")->applyFromArray($titleStyle);
    $sheet->getStyle("A{$row}")->getFont()->setSize(13);
    $row++;
    
    // Add empty row
    $row++;
    
    // Table headers - adjust based on report type
    if ($type === 'department' || $type === 'organization') {
        $headers = [
            'Requisition No.',
            'Date',
            'Requester',
            'Purpose',
            'Department',
            'Amount',
            'Status',
            'Last Updated'
        ];
    } else {
        $headers = [
            'Requisition No.',
            'Date',
            'Category',
            'Purpose',
            'Department',
            'Amount',
            'Status',
            'Last Updated'
        ];
    }
    
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $row, $header);
        $sheet->getStyle($col . $row)->applyFromArray($headerStyle);
        $col++;
    }
    $headerRow = $row;
    $row++;
    
    // Add data rows
    foreach ($requisitions as $req) {
        $sheet->setCellValue('A' . $row, $req['requisition_number'] ?? 'N/A');
        $sheet->setCellValue('B' . $row, format_date($req['created_at']));
        
        // Column C - varies by report type
        if ($type === 'department' || $type === 'organization') {
            // Show requester name
            $requesterName = trim(($req['requester_first_name'] ?? '') . ' ' . ($req['requester_last_name'] ?? ''));
            $sheet->setCellValue('C' . $row, $requesterName ?: 'N/A');
        } else {
            // Show category
            $sheet->setCellValue('C' . $row, $req['category_name'] ?? $req['purpose'] ?? 'N/A');
        }
        
        $sheet->setCellValue('D' . $row, $req['purpose'] ?? 'N/A');
        $sheet->setCellValue('E' . $row, $req['department_name'] ?? 'N/A');
        $sheet->setCellValue('F' . $row, '₦' . number_format((float)$req['total_amount'], 2 ?? 0));
        $sheet->setCellValue('G' . $row, get_status_label($req['status'] ?? ''));
        $sheet->setCellValue('H' . $row, format_date($req['updated_at']));
        
        // Apply styles
        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($dataStyle);
        
        // Center align certain columns
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Right align amount
        $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        // Color code status
        $statusColor = '';
        switch ($req['status']) {
            case STATUS_COMPLETED:
                $statusColor = 'C8E6C9'; // Green
                break;
            case STATUS_REJECTED:
                $statusColor = 'FFCDD2'; // Red
                break;
            case STATUS_PAID:
                $statusColor = 'B2EBF2'; // Blue
                break;
            case STATUS_PENDING_LINE_MANAGER:
            case STATUS_PENDING_MD:
            case STATUS_PENDING_FINANCE_MANAGER:
                $statusColor = 'FFF9C4'; // Yellow
                break;
        }
        
        if ($statusColor) {
            $sheet->getStyle("G{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($statusColor);
        }
        
        $row++;
    }
    
    // Add totals row
    $sheet->setCellValue('E' . $row, 'TOTAL:');
    $sheet->setCellValue('F' . $row,'₦' .  number_format((float)$statistics['total_amount'], 2));
    $sheet->getStyle("E{$row}:F{$row}")->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle("E{$row}:F{$row}")->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('E8F4F8');
    
    // Auto-filter
    $sheet->setAutoFilter("A{$headerRow}:H" . ($row - 1));
    
    // Freeze panes
    $sheet->freezePane('A' . ($headerRow + 1));
    
    // Add footer
    $row += 2;
    $sheet->mergeCells("A{$row}:H{$row}");
    $sheet->setCellValue("A{$row}", 'End of Report - Generated by GateWey Requisition Management System');
    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->setSize(8);
    
    // Set print settings
    $sheet->getPageSetup()
        ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
        ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
        ->setFitToWidth(1)
        ->setFitToHeight(0);
    
    // Set margins
    $sheet->getPageMargins()
        ->setTop(0.75)
        ->setRight(0.25)
        ->setLeft(0.25)
        ->setBottom(0.75);
    
    // Set header and footer
    $sheet->getHeaderFooter()
        ->setOddHeader('&C&B' . $reportTitle)
        ->setOddFooter('&LPage &P of &N&R&D &T');
    
    // Generate filename
    $timestamp = date('Y-m-d_His');
    $filename = strtolower(str_replace(' ', '_', $reportTitle)) . '_' . $timestamp . '.xlsx';
    
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');
    
    // Write file
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    
    // Clean up
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
    
    exit;
    
} catch (Exception $e) {
    // Log error
    error_log("Excel export error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Clear any output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Show error
    die('Error generating Excel file: ' . $e->getMessage());
}

/**
 * Helper function to get status label
 */
function get_status_label($status)
{
    $labels = [
        STATUS_PENDING_LINE_MANAGER => 'Pending Line Manager',
        STATUS_PENDING_MD => 'Pending MD',
        STATUS_PENDING_FINANCE_MANAGER => 'Pending Finance Manager',
        STATUS_APPROVED_FOR_PAYMENT => 'Approved for Payment',
        STATUS_PAID => 'Paid',
        STATUS_COMPLETED => 'Completed',
        STATUS_REJECTED => 'Rejected'
    ];
    
    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}
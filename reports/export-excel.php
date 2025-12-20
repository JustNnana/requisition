<?php
/**
 * GateWey Requisition Management System
 * Excel Export - WITH HIERARCHICAL SUBTOTALS
 * 
 * File: reports/export-excel.php
 * Purpose: Export comprehensive reports with parent category, purpose subtotals, and department subtotals
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
    
    // Sort requisitions by department and purpose for subtotals
    usort($requisitions, function($a, $b) {
        // First by department
        $deptCompare = strcmp($a['department_name'] ?? '', $b['department_name'] ?? '');
        if ($deptCompare !== 0) return $deptCompare;
        
        // Then by parent category
        $parentCompare = strcmp($a['parent_category_name'] ?? '', $b['parent_category_name'] ?? '');
        if ($parentCompare !== 0) return $parentCompare;
        
        // Then by purpose
        return strcmp($a['purpose'] ?? '', $b['purpose'] ?? '');
    });
    
    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('GateWey Requisition System')
        ->setTitle($reportTitle)
        ->setSubject('Requisition Report')
        ->setDescription('Comprehensive requisition report with hierarchical subtotals')
        ->setKeywords('requisition report excel analytics subtotals')
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
    
    $subtotalStyle = [
        'font' => [
            'bold' => true,
            'size' => 10,
            'italic' => true
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'F2F2F2']
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '666666']
            ]
        ]
    ];
    
    $deptSubtotalStyle = [
        'font' => [
            'bold' => true,
            'size' => 11
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'E8E8E8']
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['rgb' => '333333']
            ]
        ]
    ];
    
    $grandTotalStyle = [
        'font' => [
            'bold' => true,
            'size' => 12
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'D9D9D9']
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THICK,
                'color' => ['rgb' => '000000']
            ]
        ]
    ];
    
    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(18);
    $sheet->getColumnDimension('B')->setWidth(14);
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(25);
    $sheet->getColumnDimension('E')->setWidth(35);
    $sheet->getColumnDimension('F')->setWidth(20);
    $sheet->getColumnDimension('G')->setWidth(15);
    $sheet->getColumnDimension('H')->setWidth(18);
    $sheet->getColumnDimension('I')->setWidth(20);
    
    // Add title
    $sheet->mergeCells('A1:I1');
    $sheet->setCellValue('A1', $reportTitle);
    $sheet->getStyle('A1')->applyFromArray($titleStyle);
    $sheet->getRowDimension(1)->setRowHeight(30);
    
    // Add generation info
    $currentDate = date('F d, Y g:i A');
    $firstName = Session::get('user_first_name') ?? '';
    $lastName = Session::get('user_last_name') ?? '';
    $currentUser = trim($firstName . ' ' . $lastName);
    if (empty($currentUser)) {
        $currentUser = Session::get('user_email') ?? 'System User';
    }
    $sheet->mergeCells('A2:G2');
    $sheet->setCellValue('A2', "Generated by: {$currentUser} on {$currentDate}");
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9);
    
    // Add filter information
    $row = 3;
    if (!empty($filters['period']) || !empty($filters['date_from']) || !empty($filters['status']) || !empty($filters['category'])) {
        $sheet->mergeCells("A{$row}:I{$row}");
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
    $sheet->mergeCells("A{$row}:I{$row}");
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
        ['Total Amount', '₦' . number_format((float)$statistics['total_amount'], 2), 'Approved', number_format($statistics['approved_count'] ?? 0)],
        ['Average Amount', '₦' . number_format((float)$statistics['average_amount'], 2), 'Rejected', number_format($statistics['rejected_count'] ?? 0)],
        ['Highest Amount', '₦' . number_format((float)$statistics['max_amount'], 2), 'Completed', number_format($statistics['completed_count'] ?? 0)]
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
    $sheet->mergeCells("A{$row}:I{$row}");
    $sheet->setCellValue("A{$row}", 'DETAILED REQUISITIONS WITH SUBTOTALS');
    $sheet->getStyle("A{$row}")->applyFromArray($titleStyle);
    $sheet->getStyle("A{$row}")->getFont()->setSize(13);
    $row++;
    
    // Add empty row
    $row++;
    
    // Table headers
    if ($type === 'department' || $type === 'organization') {
        $headers = [
            'Requisition No.',
            'Date',
            'Requester',
            'Parent Category',
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
            'Parent Category',
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
    
    // Track subtotals
    $currentDepartment = null;
    $currentPurpose = null;
    $purposeCount = 0;
    $purposeAmount = 0;
    $deptCount = 0;
    $deptAmount = 0;
    
    // Add data rows with subtotals
    foreach ($requisitions as $index => $req) {
        $reqDepartment = $req['department_name'] ?? 'N/A';
        $reqPurpose = $req['purpose'] ?? 'N/A';
        
        // Check if we need to output purpose subtotal
        if ($currentPurpose !== null && $currentPurpose !== $reqPurpose) {
            // Output purpose subtotal
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->setCellValue("A{$row}", "Subtotal for: {$currentPurpose}");
            $sheet->setCellValue("G{$row}", '₦' . number_format($purposeAmount, 2));
            $sheet->setCellValue("H{$row}", $purposeCount . ' req(s)');
            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($subtotalStyle);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $row++;
            
            // Reset purpose counters
            $purposeCount = 0;
            $purposeAmount = 0;
        }
        
        // Check if we need to output department subtotal
        if ($currentDepartment !== null && $currentDepartment !== $reqDepartment) {
            // Output department subtotal
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->setCellValue("A{$row}", "DEPARTMENT SUBTOTAL: {$currentDepartment}");
            $sheet->setCellValue("G{$row}", '₦' . number_format($deptAmount, 2));
            $sheet->setCellValue("H{$row}", $deptCount . ' req(s)');
            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($deptSubtotalStyle);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $row++;
            
            // Add empty row after department subtotal
            $row++;
            
            // Reset department counters
            $deptCount = 0;
            $deptAmount = 0;
        }
        
        // Update tracking variables
        $currentDepartment = $reqDepartment;
        $currentPurpose = $reqPurpose;
        
        // Output requisition row
        $sheet->setCellValue('A' . $row, $req['requisition_number'] ?? 'N/A');
        $sheet->setCellValue('B' . $row, format_date($req['created_at']));
        
        // Column C - varies by report type
        if ($type === 'department' || $type === 'organization') {
            $requesterName = trim(($req['requester_first_name'] ?? '') . ' ' . ($req['requester_last_name'] ?? ''));
            $sheet->setCellValue('C' . $row, $requesterName ?: 'N/A');
        } else {
            $sheet->setCellValue('C' . $row, $req['category_name'] ?? $req['purpose'] ?? 'N/A');
        }
        
        // Parent Category (Column D)
        $sheet->setCellValue('D' . $row, $req['parent_category_name'] ?? 'N/A');
        
        // Purpose (Column E)
        $sheet->setCellValue('E' . $row, $req['purpose'] ?? 'N/A');
        
        // Department (Column F)
        $sheet->setCellValue('F' . $row, $reqDepartment);
        
        // Amount (Column G)
        $amount = (float)($req['total_amount'] ?? 0);
        $sheet->setCellValue('G' . $row, '₦' . number_format($amount, 2));
        
        // Status (Column H)
        $sheet->setCellValue('H' . $row, get_status_label($req['status'] ?? ''));
        
        // Last Updated (Column I)
        $sheet->setCellValue('I' . $row, format_date($req['updated_at']));
        
        // Apply styles
        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($dataStyle);
        
        // Center align certain columns
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Right align amount
        $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        // Color code status - subtle colors
        $statusColor = '';
        switch ($req['status']) {
            case STATUS_COMPLETED:
                $statusColor = 'E8F5E9'; // Very light green
                break;
            case STATUS_REJECTED:
                $statusColor = 'FFEBEE'; // Very light red
                break;
            case STATUS_PAID:
                $statusColor = 'E3F2FD'; // Very light blue
                break;
            case STATUS_PENDING_LINE_MANAGER:
            case STATUS_PENDING_MD:
            case STATUS_PENDING_FINANCE_MANAGER:
                $statusColor = 'FFF9E6'; // Very light yellow
                break;
        }
        
        if ($statusColor) {
            $sheet->getStyle("H{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($statusColor);
        }
        
        // Update counters
        $purposeCount++;
        $purposeAmount += $amount;
        $deptCount++;
        $deptAmount += $amount;
        
        $row++;
    }
    
    // Output final purpose subtotal
    if ($currentPurpose !== null) {
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", "Subtotal for: {$currentPurpose}");
        $sheet->setCellValue("G{$row}", '₦' . number_format($purposeAmount, 2));
        $sheet->setCellValue("H{$row}", $purposeCount . ' req(s)');
        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($subtotalStyle);
        $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;
    }
    
    // Output final department subtotal
    if ($currentDepartment !== null) {
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", "DEPARTMENT SUBTOTAL: {$currentDepartment}");
        $sheet->setCellValue("G{$row}", '₦' . number_format($deptAmount, 2));
        $sheet->setCellValue("H{$row}", $deptCount . ' req(s)');
        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($deptSubtotalStyle);
        $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;
    }
    
    // Add empty row
    $row++;
    
    // Add grand total
    $sheet->mergeCells("A{$row}:F{$row}");
    $sheet->setCellValue("A{$row}", "GRAND TOTAL:");
    $sheet->setCellValue("G{$row}", '₦' . number_format((float)$statistics['total_amount'], 2));
    $sheet->setCellValue("H{$row}", $statistics['total_count'] . ' req(s)');
    $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($grandTotalStyle);
    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    
    // Add footer
    $row += 2;
    $sheet->mergeCells("A{$row}:I{$row}");
    $sheet->setCellValue("A{$row}", 'End of Report - Generated by Kadick Finance System');
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
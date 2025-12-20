<?php
/**
 * GateWey Requisition Management System
 * Budget Report Export to Excel
 *
 * File: finance/budget/export-report.php
 * Purpose: Export budget comparison reports to Excel format using PHPSpreadsheet
 */

// CRITICAL: Suppress all output before Excel generation
error_reporting(0);
ini_set('display_errors', 0);

define('APP_ACCESS', true);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../helpers/permissions.php';

Session::start();
require_once __DIR__ . '/../../middleware/auth-check.php';

// Load PHPSpreadsheet
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

try {
    // Check if user is Finance Manager or Managing Director
    $userRole = Session::getUserRoleId();
    if ($userRole != ROLE_FINANCE_MANAGER && $userRole != ROLE_MANAGING_DIRECTOR) {
        Session::setFlash('error', 'Access denied.');
        redirect(BASE_URL . '/dashboard/index.php');
        exit;
    }

    // Get filter parameters from query string
    $reportType = isset($_GET['report_type']) ? Sanitizer::string($_GET['report_type']) : 'department';
    $selectedDepartment = isset($_GET['department']) ? (int)$_GET['department'] : 0;
    $year1 = isset($_GET['year1']) ? (int)$_GET['year1'] : (int)date('Y');
    $year2 = isset($_GET['year2']) ? (int)$_GET['year2'] : (int)date('Y') - 1;
    $quarter1 = isset($_GET['quarter1']) ? (int)$_GET['quarter1'] : 0;
    $quarter2 = isset($_GET['quarter2']) ? (int)$_GET['quarter2'] : 0;

    $budget = new Budget();
    $department = new Department();
    $db = Database::getInstance();

    // Get quarters
    $quarters = [
        0 => 'Full Year',
        1 => 'Q1 (Jan-Mar)',
        2 => 'Q2 (Apr-Jun)',
        3 => 'Q3 (Jul-Sep)',
        4 => 'Q4 (Oct-Dec)'
    ];

    // Helper function to get budget data for a specific year and department
    function getBudgetDataForYear($db, $departmentId, $year, $quarter = 0) {
        $quarterCondition = '';
        if ($quarter > 0) {
            $quarterMonth = ($quarter - 1) * 3 + 1;
            $quarterCondition = " AND MONTH(start_date) >= $quarterMonth AND MONTH(start_date) < " . ($quarterMonth + 3);
        }

        $sql = "SELECT
                    COALESCE(SUM(budget_amount), 0) as total_budget,
                    COALESCE(SUM(allocated_amount), 0) as total_allocated,
                    COALESCE(SUM(available_amount), 0) as total_available,
                    COUNT(*) as budget_count
                FROM department_budgets
                WHERE department_id = ?
                AND YEAR(start_date) = ?
                $quarterCondition";

        $result = $db->fetchOne($sql, [$departmentId, $year]);

        $utilizationRate = $result['total_budget'] > 0
            ? ($result['total_allocated'] / $result['total_budget']) * 100
            : 0;

        // Get requisition count
        $reqSql = "SELECT COUNT(*) as req_count, COALESCE(SUM(total_amount), 0) as req_total
                   FROM requisitions
                   WHERE department_id = ?
                   AND YEAR(created_at) = ?
                   AND status IN (?, ?)
                   $quarterCondition";

        $reqResult = $db->fetchOne($reqSql, [$departmentId, $year, STATUS_PAID, STATUS_COMPLETED]);

        return [
            'total_budget' => (float)$result['total_budget'],
            'total_allocated' => (float)$result['total_allocated'],
            'total_available' => (float)$result['total_available'],
            'budget_count' => (int)$result['budget_count'],
            'utilization_rate' => $utilizationRate,
            'requisition_count' => (int)$reqResult['req_count'],
            'requisition_total' => (float)$reqResult['req_total']
        ];
    }

    // Helper function to get category breakdown
    function getCategoryBreakdown($db, $departmentId, $year, $quarter = 0) {
        $quarterCondition = '';
        if ($quarter > 0) {
            $quarterMonth = ($quarter - 1) * 3 + 1;
            $quarterCondition = " AND MONTH(r.created_at) >= $quarterMonth AND MONTH(r.created_at) < " . ($quarterMonth + 3);
        }

        $sql = "SELECT
                    rc.category_name,
                    COUNT(r.id) as req_count,
                    COALESCE(SUM(r.total_amount), 0) as total_spent
                FROM requisitions r
                LEFT JOIN requisition_categories rc ON r.category_id = rc.id
                WHERE r.department_id = ?
                AND YEAR(r.created_at) = ?
                AND r.status IN (?, ?)
                $quarterCondition
                GROUP BY rc.category_name
                ORDER BY total_spent DESC";

        return $db->fetchAll($sql, [$departmentId, $year, STATUS_PAID, STATUS_COMPLETED]);
    }

    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Define styles
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

    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 11
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

    $sectionHeaderStyle = [
        'font' => [
            'bold' => true,
            'size' => 12,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '2196F3']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
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

    $totalStyle = [
        'font' => [
            'bold' => true,
            'size' => 11
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'F2F2F2']
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['rgb' => '333333']
            ]
        ]
    ];

    $positiveStyle = [
        'font' => ['color' => ['rgb' => '28A745']]
    ];

    $negativeStyle = [
        'font' => ['color' => ['rgb' => 'DC3545']]
    ];

    $row = 1;

    if ($reportType === 'department' && $selectedDepartment > 0) {
        // Department-specific report
        $deptInfo = $department->getById($selectedDepartment);

        if (!$deptInfo) {
            throw new Exception('Department not found');
        }

        // Get budget data
        $year1Data = getBudgetDataForYear($db, $selectedDepartment, $year1, $quarter1);
        $year2Data = getBudgetDataForYear($db, $selectedDepartment, $year2, $quarter2);

        // Calculate comparison metrics
        $budgetChange = $year1Data['total_budget'] - $year2Data['total_budget'];
        $budgetChangePercent = $year2Data['total_budget'] > 0
            ? (($year1Data['total_budget'] - $year2Data['total_budget']) / $year2Data['total_budget']) * 100
            : 0;

        $allocatedChange = $year1Data['total_allocated'] - $year2Data['total_allocated'];
        $allocatedChangePercent = $year2Data['total_allocated'] > 0
            ? (($year1Data['total_allocated'] - $year2Data['total_allocated']) / $year2Data['total_allocated']) * 100
            : 0;

        $utilizationChange = $year1Data['utilization_rate'] - $year2Data['utilization_rate'];

        // Set document properties
        $reportTitle = 'Budget Report - ' . $deptInfo['department_name'];
        $spreadsheet->getProperties()
            ->setCreator('GateWey Requisition System')
            ->setTitle($reportTitle)
            ->setSubject('Budget Report')
            ->setDescription('Year-over-year budget comparison report');

        // Title
        $sheet->setCellValue('A1', $reportTitle);
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->applyFromArray($titleStyle);
        $row++;

        // Subtitle
        $subtitle = "Comparing {$year1} " . ($quarter1 > 0 ? $quarters[$quarter1] : '') .
                    " with {$year2} " . ($quarter2 > 0 ? $quarters[$quarter2] : '');
        $sheet->setCellValue('A2', $subtitle);
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        // Generated info
        $sheet->setCellValue('A3', 'Generated on: ' . date('F d, Y h:i A'));
        $sheet->mergeCells('A3:D3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('666666');
        $row += 2;

        // Year-over-Year Comparison Section
        $sheet->setCellValue('A' . $row, 'Year-over-Year Comparison');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
        $row++;

        // Headers
        $sheet->setCellValue('A' . $row, 'Metric');
        $sheet->setCellValue('B' . $row, $year1 . ' ' . ($quarter1 > 0 ? $quarters[$quarter1] : ''));
        $sheet->setCellValue('C' . $row, $year2 . ' ' . ($quarter2 > 0 ? $quarters[$quarter2] : ''));
        $sheet->setCellValue('D' . $row, 'Change');
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($headerStyle);
        $row++;

        // Data rows
        $metrics = [
            ['Total Budget', $year1Data['total_budget'], $year2Data['total_budget'], $budgetChange, $budgetChangePercent],
            ['Allocated Amount', $year1Data['total_allocated'], $year2Data['total_allocated'], $allocatedChange, $allocatedChangePercent],
            ['Available Amount', $year1Data['total_available'], $year2Data['total_available'], $year1Data['total_available'] - $year2Data['total_available'], null],
            ['Utilization Rate', $year1Data['utilization_rate'], $year2Data['utilization_rate'], $utilizationChange, null],
            ['Number of Requisitions', $year1Data['requisition_count'], $year2Data['requisition_count'], $year1Data['requisition_count'] - $year2Data['requisition_count'], null]
        ];

        foreach ($metrics as $index => $metric) {
            $sheet->setCellValue('A' . $row, $metric[0]);

            if ($index < 3) { // Currency fields
                $sheet->setCellValue('B' . $row, $metric[1]);
                $sheet->setCellValue('C' . $row, $metric[2]);
                $sheet->setCellValue('D' . $row, abs($metric[3]));
                $sheet->getStyle('B' . $row . ':D' . $row)->getNumberFormat()
                    ->setFormatCode('₦#,##0.00');
            } elseif ($index == 3) { // Utilization Rate
                $sheet->setCellValue('B' . $row, $metric[1] / 100);
                $sheet->setCellValue('C' . $row, $metric[2] / 100);
                $sheet->setCellValue('D' . $row, $metric[3] / 100);
                $sheet->getStyle('B' . $row . ':D' . $row)->getNumberFormat()
                    ->setFormatCode('0.0%');
            } else { // Number of Requisitions
                $sheet->setCellValue('B' . $row, $metric[1]);
                $sheet->setCellValue('C' . $row, $metric[2]);
                $sheet->setCellValue('D' . $row, $metric[3]);
                $sheet->getStyle('B' . $row . ':D' . $row)->getNumberFormat()
                    ->setFormatCode('#,##0');
            }

            // Apply color to change column
            if (isset($metric[4]) && $metric[4] !== null) {
                $changeText = ($metric[3] >= 0 ? '+' : '') . number_format(abs($metric[4]), 1) . '%';
                $sheet->setCellValue('D' . $row, $changeText);
                if ($metric[3] >= 0) {
                    $sheet->getStyle('D' . $row)->applyFromArray($positiveStyle);
                } else {
                    $sheet->getStyle('D' . $row)->applyFromArray($negativeStyle);
                }
            } elseif ($metric[3] != 0 && $index != 2) {
                if ($metric[3] >= 0) {
                    $sheet->getStyle('D' . $row)->applyFromArray($positiveStyle);
                } else {
                    $sheet->getStyle('D' . $row)->applyFromArray($negativeStyle);
                }
            }

            $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($dataStyle);
            $row++;
        }

        $row++;

        // Category Breakdown
        $year1Categories = getCategoryBreakdown($db, $selectedDepartment, $year1, $quarter1);

        if (!empty($year1Categories)) {
            $sheet->setCellValue('A' . $row, 'Category Breakdown - ' . $year1);
            $sheet->mergeCells('A' . $row . ':D' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
            $row++;

            // Headers
            $sheet->setCellValue('A' . $row, 'Category');
            $sheet->setCellValue('B' . $row, 'Requisitions');
            $sheet->setCellValue('C' . $row, 'Total Spent');
            $sheet->setCellValue('D' . $row, '% of Total');
            $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($headerStyle);
            $row++;

            $totalSpent = array_sum(array_column($year1Categories, 'total_spent'));

            foreach ($year1Categories as $category) {
                $percentage = $totalSpent > 0 ? ($category['total_spent'] / $totalSpent) * 100 : 0;

                $sheet->setCellValue('A' . $row, $category['category_name'] ?? 'Uncategorized');
                $sheet->setCellValue('B' . $row, $category['req_count']);
                $sheet->setCellValue('C' . $row, $category['total_spent']);
                $sheet->setCellValue('D' . $row, $percentage / 100);

                $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('₦#,##0.00');
                $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('0.0%');
                $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($dataStyle);

                $row++;
            }

            // Total row
            $sheet->setCellValue('A' . $row, 'TOTAL');
            $sheet->setCellValue('B' . $row, array_sum(array_column($year1Categories, 'req_count')));
            $sheet->setCellValue('C' . $row, $totalSpent);
            $sheet->setCellValue('D' . $row, 1);

            $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('₦#,##0.00');
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($totalStyle);
        }

    } elseif ($reportType === 'organization') {
        // Organization-wide report
        $departments = $department->getAll();
        $reportData = [];

        foreach ($departments as $dept) {
            $year1DeptData = getBudgetDataForYear($db, $dept['id'], $year1, $quarter1);
            $year2DeptData = getBudgetDataForYear($db, $dept['id'], $year2, $quarter2);

            $reportData[] = [
                'department_id' => $dept['id'],
                'department_name' => $dept['department_name'],
                'department_code' => $dept['department_code'],
                'year1' => $year1DeptData,
                'year2' => $year2DeptData,
                'budget_change' => $year1DeptData['total_budget'] - $year2DeptData['total_budget'],
                'budget_change_percent' => $year2DeptData['total_budget'] > 0
                    ? (($year1DeptData['total_budget'] - $year2DeptData['total_budget']) / $year2DeptData['total_budget']) * 100
                    : 0
            ];
        }

        // Calculate organization totals
        $orgYear1Total = array_sum(array_column(array_column($reportData, 'year1'), 'total_budget'));
        $orgYear1Allocated = array_sum(array_column(array_column($reportData, 'year1'), 'total_allocated'));
        $orgYear2Total = array_sum(array_column(array_column($reportData, 'year2'), 'total_budget'));
        $orgYear2Allocated = array_sum(array_column(array_column($reportData, 'year2'), 'total_allocated'));

        // Set document properties
        $reportTitle = 'Organization-Wide Budget Report';
        $spreadsheet->getProperties()
            ->setCreator('GateWey Requisition System')
            ->setTitle($reportTitle)
            ->setSubject('Organization Budget Report')
            ->setDescription('Organization-wide budget comparison report');

        // Title
        $sheet->setCellValue('A1', $reportTitle);
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->applyFromArray($titleStyle);
        $row++;

        // Subtitle
        $subtitle = "Comparing {$year1} " . ($quarter1 > 0 ? $quarters[$quarter1] : '') .
                    " with {$year2} " . ($quarter2 > 0 ? $quarters[$quarter2] : '');
        $sheet->setCellValue('A2', $subtitle);
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        // Generated info
        $sheet->setCellValue('A3', 'Generated on: ' . date('F d, Y h:i A'));
        $sheet->mergeCells('A3:G3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('666666');
        $row += 2;

        // Organization Summary Section
        $sheet->setCellValue('A' . $row, 'Organization Summary');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
        $row++;

        // Summary headers
        $sheet->setCellValue('A' . $row, 'Metric');
        $sheet->setCellValue('B' . $row, $year1);
        $sheet->setCellValue('C' . $row, $year2);
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($headerStyle);
        $row++;

        // Summary data
        $summaryData = [
            ['Total Budget (All Departments)', $orgYear1Total, $orgYear2Total],
            ['Total Allocated (All Departments)', $orgYear1Allocated, $orgYear2Allocated],
            ['Organization Utilization Rate',
             $orgYear1Total > 0 ? ($orgYear1Allocated / $orgYear1Total) * 100 : 0,
             $orgYear2Total > 0 ? ($orgYear2Allocated / $orgYear2Total) * 100 : 0]
        ];

        foreach ($summaryData as $index => $data) {
            $sheet->setCellValue('A' . $row, $data[0]);
            $sheet->setCellValue('B' . $row, $data[1]);
            $sheet->setCellValue('C' . $row, $data[2]);

            if ($index < 2) {
                $sheet->getStyle('B' . $row . ':C' . $row)->getNumberFormat()
                    ->setFormatCode('₦#,##0.00');
            } else {
                $sheet->getStyle('B' . $row . ':C' . $row)->getNumberFormat()
                    ->setFormatCode('0.0%');
                $sheet->setCellValue('B' . $row, $data[1] / 100);
                $sheet->setCellValue('C' . $row, $data[2] / 100);
            }

            $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($dataStyle);
            $row++;
        }

        $row += 2;

        // Department Comparison Section
        $sheet->setCellValue('A' . $row, 'Department Budget Comparison');
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
        $row++;

        // Department headers
        $headers = ['Department', 'Code', $year1 . ' Budget', $year1 . ' Utilized',
                   $year2 . ' Budget', $year2 . ' Utilized', 'Budget Change'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }
        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($headerStyle);
        $row++;

        // Department data
        foreach ($reportData as $deptData) {
            $sheet->setCellValue('A' . $row, $deptData['department_name']);
            $sheet->setCellValue('B' . $row, $deptData['department_code']);
            $sheet->setCellValue('C' . $row, $deptData['year1']['total_budget']);
            $sheet->setCellValue('D' . $row, $deptData['year1']['utilization_rate'] / 100);
            $sheet->setCellValue('E' . $row, $deptData['year2']['total_budget']);
            $sheet->setCellValue('F' . $row, $deptData['year2']['utilization_rate'] / 100);
            $sheet->setCellValue('G' . $row, ($deptData['budget_change'] >= 0 ? '+' : '') . number_format($deptData['budget_change_percent'], 1) . '%');

            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('₦#,##0.00');
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('₦#,##0.00');
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('0.0%');

            if ($deptData['budget_change'] >= 0) {
                $sheet->getStyle('G' . $row)->applyFromArray($positiveStyle);
            } else {
                $sheet->getStyle('G' . $row)->applyFromArray($negativeStyle);
            }

            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($dataStyle);
            $row++;
        }

        // Total row
        $totalChange = $orgYear2Total > 0 ? (($orgYear1Total - $orgYear2Total) / $orgYear2Total) * 100 : 0;
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, $orgYear1Total);
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, $orgYear2Total);
        $sheet->setCellValue('F' . $row, '');
        $sheet->setCellValue('G' . $row, ($totalChange >= 0 ? '+' : '') . number_format($totalChange, 1) . '%');

        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('₦#,##0.00');
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('₦#,##0.00');

        if ($totalChange >= 0) {
            $sheet->getStyle('G' . $row)->applyFromArray($positiveStyle);
        } else {
            $sheet->getStyle('G' . $row)->applyFromArray($negativeStyle);
        }

        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($totalStyle);
    }

    // Footer
    $row += 3;
    $sheet->setCellValue('A' . $row, 'GateWey Requisition Management System');
    $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(10);
    $row++;
    $sheet->setCellValue('A' . $row, 'Generated by: ' . Session::getUserFullName());
    $sheet->getStyle('A' . $row)->getFont()->setSize(9)->getColor()->setRGB('666666');
    $row++;
    $sheet->setCellValue('A' . $row, 'Date & Time: ' . date('F d, Y h:i:s A'));
    $sheet->getStyle('A' . $row)->getFont()->setSize(9)->getColor()->setRGB('666666');

    // Auto-size columns
    foreach (range('A', $reportType === 'organization' ? 'G' : 'D') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Set filename
    $filename = '';
    if ($reportType === 'department' && $selectedDepartment > 0) {
        $deptInfo = $department->getById($selectedDepartment);
        $deptName = $deptInfo ? str_replace(' ', '_', $deptInfo['department_name']) : 'Department';
        $filename = "Budget_Report_{$deptName}_{$year1}_vs_{$year2}_" . date('Y-m-d') . ".xlsx";
    } else {
        $filename = "Budget_Report_Organization_{$year1}_vs_{$year2}_" . date('Y-m-d') . ".xlsx";
    }

    // Output the file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    error_log("Budget Report Export Error: " . $e->getMessage());
    Session::setFlash('error', 'Error generating Excel report: ' . $e->getMessage());
    redirect(BASE_URL . '/finance/budget/reports.php');
    exit;
}

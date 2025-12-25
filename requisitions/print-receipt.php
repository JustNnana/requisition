<?php
/**
 * GateWey Requisition Management System
 * Print Receipt Page
 *
 * File: requisitions/print-receipt.php
 * Purpose: Print formatted receipt with company logo and signatures for completed requisitions
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';
require_once __DIR__ . '/../helpers/permissions.php';

// Get requisition ID
$requisitionId = get_encrypted_id();

if (!$requisitionId) {
    Session::setFlash('error', 'Invalid requisition ID.');
    header('Location: list.php');
    exit;
}

// Initialize classes
$requisition = new Requisition();

// Get requisition details using existing method
$req = $requisition->getById($requisitionId);

if (!$req) {
    Session::setFlash('error', 'Requisition not found.');
    header('Location: list.php');
    exit;
}

// Check permission to view
if (!can_user_view_requisition($req)) {
    Session::setFlash('error', 'You do not have permission to view this requisition.');
    header('Location: list.php');
    exit;
}

// Verify status is completed
if ($req['status'] !== STATUS_COMPLETED) {
    Session::setFlash('error', 'Receipt can only be printed for completed requisitions.');
    header('Location: ' . build_encrypted_url('view.php', $requisitionId));
    exit;
}

// Parse additional_info JSON for account details
$accountDetails = [
    'account_type' => '',
    'account_name' => '',
    'bank_name' => '',
    'account_number' => ''
];

if (!empty($req['additional_info'])) {
    $decoded = json_decode($req['additional_info'], true);
    if (is_array($decoded)) {
        $accountDetails = array_merge($accountDetails, $decoded);
    }
}

// Extract Finance Manager and MD names from approval history
$financeManagerName = '';
$mdName = '';

if (!empty($req['approvals'])) {
    foreach ($req['approvals'] as $approval) {
        // Check if this is a Finance Manager or MD approval using role_name field
        if (!empty($approval['role_name']) && $approval['action'] === 'approved') {
            if (stripos($approval['role_name'], 'Finance Manager') !== false) {
                $financeManagerName = $approval['approver_name'];
            }
            if (stripos($approval['role_name'], 'Managing Director') !== false || stripos($approval['role_name'], 'MD') !== false) {
                $mdName = $approval['approver_name'];
            }
        }
    }
}

// Get requisition items
$items = $req['items'];

// Page title
$pageTitle = 'Receipt - ' . $req['requisition_number'];
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
    /* Print-specific styles */
    @media print {
        /* Hide navigation and other UI elements */
        .navbar,
        .sidebar,
        .content-header .content-actions,
        .no-print {
            display: none !important;
        }

        body {
            background: white !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .receipt-container {
            box-shadow: none !important;
            border: none !important;
            padding: 20px !important;
        }

        .page-break {
            page-break-after: always;
        }

        /* Hide URL in print */
        @page {
            margin-top: 0.5cm;
            margin-bottom: 0.5cm;
        }

        /* This removes the header/footer with URL */
        body::before,
        body::after {
            display: none !important;
        }
    }

    /* Receipt Container */
    .receipt-container {
        max-width: 900px;
        margin: 0 auto;
        background: var(--bg-card);
        padding: var(--spacing-8);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-lg);
    }

    /* Header with logo */
    .receipt-header {
        text-align: center;
        margin-bottom: var(--spacing-6);
        padding-bottom: var(--spacing-5);
        border-bottom: 3px solid var(--bg-hover);
    }

    .company-logo {
        max-width: 200px;
        height: auto;
        margin-bottom: var(--spacing-4);
    }

    .company-name {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        color: var(--primary);
        margin-bottom: var(--spacing-2);
    }

    .receipt-title {
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-semibold);
        color: var(--text-secondary);
        margin-top: var(--spacing-3);
    }

    /* Receipt info grid */
    .receipt-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-4);
        margin-bottom: var(--spacing-6);
    }

    .info-section {
        background: var(--bg-subtle);
        padding: var(--spacing-4);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
    }

    .info-label {
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: var(--spacing-2);
    }

    .info-value {
        font-size: var(--font-size-sm);
        color: var(--text-primary);
        font-weight: var(--font-weight-medium);
    }

    .info-value.large {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-bold);
        color: var(--primary);
    }

    /* Account details */
    .account-details {
        background: rgba(var(--warning-rgb), 0.1);
        border: 2px solid var(--warning);
        padding: var(--spacing-5);
        border-radius: var(--border-radius-lg);
        margin-bottom: var(--spacing-6);
    }

    .account-details h3 {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-4);
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .account-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-4);
    }

    /* Items table */
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: var(--spacing-6);
    }

    .items-table thead {
        background: var(--primary);
        color: white;
    }

    .items-table th {
        padding: var(--spacing-3);
        text-align: left;
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .items-table td {
        padding: var(--spacing-3);
        border-bottom: 1px solid var(--border-color);
        font-size: var(--font-size-sm);
        color: var(--text-primary);
    }

    .items-table tbody tr:hover {
        background: var(--bg-hover);
    }

    .items-table .text-right {
        text-align: right;
    }

    .items-table .text-center {
        text-align: center;
    }

    .items-table tfoot {
        background: var(--bg-subtle);
        font-weight: var(--font-weight-bold);
    }

    .items-table tfoot td {
        border-top: 2px solid var(--primary);
        border-bottom: 2px solid var(--primary);
        padding: var(--spacing-4);
        font-size: var(--font-size-lg);
    }

    .total-amount {
        color: var(--success);
        font-size: var(--font-size-xl);
    }

    /* Signatures section */
    .signatures {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-8);
        margin-top: var(--spacing-8);
        margin-bottom: var(--spacing-6);
    }

    .signature-box {
        text-align: center;
    }

    .signature-line {
        border-top: 2px solid var(--text-primary);
        margin-bottom: var(--spacing-2);
        padding-top: var(--spacing-2);
    }

    .signature-name {
        font-weight: var(--font-weight-bold);
        font-size: var(--font-size-base);
        color: var(--text-primary);
    }

    .signature-title {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        font-style: italic;
    }

    /* Footer */
    .receipt-footer {
        text-align: center;
        margin-top: var(--spacing-8);
        padding-top: var(--spacing-5);
        border-top: 2px solid var(--border-color);
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
    }

    /* Print button */
    .print-actions {
        text-align: center;
        margin-bottom: var(--spacing-5);
        display: flex;
        gap: var(--spacing-3);
        justify-content: center;
    }

    /* Table wrapper for horizontal scroll on mobile */
    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: var(--spacing-6);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .receipt-info,
        .account-grid,
        .signatures {
            grid-template-columns: 1fr;
        }

        .receipt-container {
            padding: var(--spacing-5);
        }

        /* Make table scrollable horizontally on mobile */
        .items-table {
            min-width: 600px;
            margin-bottom: 0;
        }

        .items-table th,
        .items-table td {
            padding: var(--spacing-2);
            font-size: var(--font-size-xs);
        }

        .items-table th {
            white-space: nowrap;
        }

        /* Adjust info sections for better mobile display */
        .info-section {
            padding: var(--spacing-3);
        }

        .info-label {
            font-size: 10px;
        }

        .info-value {
            font-size: var(--font-size-xs);
        }

        .info-value.large {
            font-size: var(--font-size-base);
        }

        /* Adjust account details grid */
        .account-details {
            padding: var(--spacing-4);
        }

        .account-details h3 {
            font-size: var(--font-size-base);
        }

        /* Adjust signatures for mobile */
        .signature-name {
            font-size: var(--font-size-sm);
        }

        .signature-title {
            font-size: var(--font-size-xs);
        }

        /* Adjust header for mobile */
        .company-logo {
            max-width: 120px;
        }

        .receipt-title {
            font-size: var(--font-size-lg);
        }
    }

    /* Extra small devices */
    @media (max-width: 480px) {
        .receipt-container {
            padding: var(--spacing-3);
        }

        .company-logo {
            max-width: 100px;
        }

        .receipt-title {
            font-size: var(--font-size-base);
        }

        .print-actions {
            flex-direction: column;
        }

        .print-actions .btn {
            width: 100%;
        }
    }
</style>

<!-- Content Header -->
<div class="content-header no-print">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">
                <i class="fas fa-receipt me-2"></i>
                Payment Receipt
            </h1>
            <p class="content-description">Receipt for <?php echo htmlspecialchars($req['requisition_number']); ?></p>
        </div>
    </div>
</div>

<!-- Print Actions (hidden when printing) -->
<div class="print-actions no-print">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fas fa-print me-2"></i>
        Print / Save as PDF
    </button>
    <a href="<?php echo build_encrypted_url('view.php', $requisitionId); ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>
        Back to Requisition
    </a>
</div>

<!-- Receipt Container -->
<div class="receipt-container">
    <!-- Header with Company Logo -->
    <div class="receipt-header">
        <?php if (file_exists(__DIR__ . '/../assets/images/icons/kadick-logo.png')): ?>
            <img src="<?php echo BASE_URL; ?>/assets/images/icons/kadick-logo.png" alt="Kadick Finance Logo" class="company-logo">
        <?php elseif (file_exists(__DIR__ . '/../assets/images/logo.png')): ?>
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Company Logo" class="company-logo">
        <?php endif; ?>
        <!--<div class="company-name">Kadick Finance</div>-->
        <div class="receipt-title">PAYMENT RECEIPT</div>
    </div>

    <!-- Receipt Information -->
    <div class="receipt-info">
        <div class="info-section">
            <div class="info-label">Receipt Number</div>
            <div class="info-value large"><?php echo htmlspecialchars($req['requisition_number']); ?></div>
        </div>

        <div class="info-section">
            <div class="info-label">Receipt Date</div>
            <div class="info-value"><?php echo format_date($req['completed_at'] ?? $req['receipt_uploaded_at'] ?? date('Y-m-d H:i:s')); ?></div>
        </div>

        <div class="info-section">
            <div class="info-label">Requester</div>
            <div class="info-value">
                <?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?><br>
                <small style="color: var(--text-muted);"><?php echo htmlspecialchars($req['requester_email'] ?? $req['email']); ?></small>
            </div>
        </div>

        <div class="info-section">
            <div class="info-label">Department</div>
            <div class="info-value">
                <?php if ($req['department_name']): ?>
                    <?php echo htmlspecialchars($req['department_name']); ?>
                    <small style="color: var(--text-muted);">(<?php echo htmlspecialchars($req['department_code']); ?>)</small>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </div>
        </div>

        <div class="info-section" style="grid-column: 1 / -1;">
            <div class="info-label">Purpose</div>
            <div class="info-value"><?php echo nl2br(htmlspecialchars($req['purpose'])); ?></div>
        </div>

        <?php if ($req['payment_method']): ?>
        <div class="info-section">
            <div class="info-label">Payment Method</div>
            <div class="info-value"><?php echo htmlspecialchars($req['payment_method']); ?></div>
        </div>
        <?php endif; ?>

        <?php if ($req['payment_reference']): ?>
        <div class="info-section">
            <div class="info-label">Payment Reference</div>
            <div class="info-value"><?php echo htmlspecialchars($req['payment_reference']); ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Account Details -->
    <?php if (!empty($accountDetails['account_name']) || !empty($accountDetails['account_number']) || !empty($accountDetails['bank_name'])): ?>
    <div class="account-details">
        <h3>
            <i class="fas fa-university"></i>
            Payment Account Details
        </h3>
        <div class="account-grid">
            <?php if (!empty($accountDetails['account_name'])): ?>
            <div>
                <div class="info-label">Account Name</div>
                <div class="info-value"><?php echo htmlspecialchars($accountDetails['account_name']); ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($accountDetails['account_number'])): ?>
            <div>
                <div class="info-label">Account Number</div>
                <div class="info-value"><?php echo htmlspecialchars($accountDetails['account_number']); ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($accountDetails['bank_name'])): ?>
            <div>
                <div class="info-label">Bank Name</div>
                <div class="info-value"><?php echo htmlspecialchars($accountDetails['bank_name']); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Items Table -->
    <div class="table-wrapper">
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 10%;">#</th>
                    <th style="width: 45%;">Description</th>
                    <th class="text-center" style="width: 15%;">Quantity</th>
                    <th class="text-right" style="width: 15%;">Unit Price</th>
                    <th class="text-right" style="width: 15%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $index => $item): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($item['item_description']); ?></td>
                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                    <td class="text-right">₦<?php echo number_format((float)$item['unit_price'], 2); ?></td>
                    <td class="text-right">₦<?php echo number_format((float)$item['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">TOTAL AMOUNT PAID:</td>
                    <td class="text-right">
                        <span class="total-amount">₦<?php echo number_format((float)$req['total_amount'], 2); ?></span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                <div class="signature-name">
                    <?php echo $financeManagerName ? htmlspecialchars($financeManagerName) : 'Finance Manager'; ?>
                </div>
                <div class="signature-title">Finance Manager</div>
            </div>
        </div>

        <div class="signature-box">
            <div class="signature-line">
                <div class="signature-name">
                    <?php echo $mdName ? htmlspecialchars($mdName) : 'Managing Director'; ?>
                </div>
                <div class="signature-title">Managing Director</div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="receipt-footer">
        <p>
            <strong>Kadick Finance - Requisition Management System</strong><br>
            This is an official payment receipt generated on <?php echo format_date(date('Y-m-d H:i:s')); ?>
        </p>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

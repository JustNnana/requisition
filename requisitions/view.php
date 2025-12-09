<?php

/**
 * GateWey Requisition Management System
 * View Requisition Page - Dasher UI Enhanced (Fully Recoded)
 * 
 * File: requisitions/view.php
 * Purpose: Display detailed view of a single requisition
 * 
 * UPDATED: Complete Dasher UI redesign with modern layout and styling
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
require_once __DIR__ . '/../helpers/status-indicator.php';

// Get requisition ID
$requisitionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$requisitionId) {
    Session::setFlash('error', 'Invalid requisition ID.');
    header('Location: list.php');
    exit;
}

// Initialize objects
$requisition = new Requisition();

// Get requisition data
$reqData = $requisition->getById($requisitionId);

if (!$reqData) {
    Session::setFlash('error', 'Requisition not found.');
    header('Location: list.php');
    exit;
}

// Check permission to view
if (!can_user_view_requisition($reqData)) {
    Session::setFlash('error', 'You do not have permission to view this requisition.');
    header('Location: list.php');
    exit;
}

// Check if user can perform actions
$canEdit = can_user_edit_requisition($reqData);
$canCancel = can_user_cancel_requisition($reqData);
$canApprove = can_user_approve_requisition($reqData);

// Check for flash messages
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');

// Page title
$pageTitle = 'Requisition ' . $reqData['requisition_number'];

/**
 * Helper function to check if file is previewable
 */
function isPreviewable($filename)
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf']);
}

/**
 * Get preview type
 */
function getPreviewType($filename)
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        return 'image';
    } elseif ($ext === 'pdf') {
        return 'pdf';
    }
    return 'none';
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Add Status Indicator CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/status-indicators.css">

<!-- Dasher UI Enhanced Styles -->
<style>
    /* Main Layout Grid */
    .requisition-view-grid {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: var(--spacing-6);
        margin: 0 auto;
    }

    @media (max-width: 992px) {
        .requisition-view-grid {
            grid-template-columns: 1fr;
        }

        .requisition-sidebar {
            order: 0;
        }
    }

    /* Section Cards */
    .section-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        margin-bottom: var(--spacing-5);
        overflow: hidden;
        transition: var(--theme-transition);
    }

    .section-card:hover {
        box-shadow: var(--shadow-sm);
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--spacing-5);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
    }

    .section-header-content {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .section-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-lg);
        flex-shrink: 0;
    }

    .section-icon.primary {
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
    }

    .section-icon.success {
        background: rgba(var(--success-rgb), 0.1);
        color: var(--success);
    }

    .section-icon.info {
        background: rgba(var(--info-rgb), 0.1);
        color: var(--info);
    }

    .section-icon.warning {
        background: rgba(var(--warning-rgb), 0.1);
        color: var(--warning);
    }

    .section-title {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-1);
    }

    .section-title h5 {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
    }

    .section-title p {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        margin: 0;
    }

    .section-body {
        padding: var(--spacing-5);
    }

    /* Status Banner */
    .status-banner {
        background: transparent;
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-5);
        margin-bottom: var(--spacing-5);
        border: 1px solid var(--border-color);
        transition: var(--theme-transition);
    }

    .status-banner.pending {
        border-left: 4px solid var(--warning);
        background: rgba(var(--warning-rgb), 0.02);
    }

    .status-banner.approved {
        border-left: 4px solid var(--success);
        background: rgba(var(--success-rgb), 0.02);
    }

    .status-banner.rejected {
        border-left: 4px solid var(--danger);
        background: rgba(var(--danger-rgb), 0.02);
    }

    .status-banner.completed {
        border-left: 4px solid var(--info);
        background: rgba(var(--info-rgb), 0.02);
    }

    .status-banner-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: var(--spacing-4);
    }

    .status-info {
        flex: 1;
    }

    .status-info h5 {
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-semibold);
        margin: 0 0 var(--spacing-2) 0;
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .rejection-reason {
        margin-top: var(--spacing-3);
        padding: var(--spacing-3);
        background: rgba(var(--danger-rgb), 0.05);
        border-left: 3px solid var(--danger);
        border-radius: var(--border-radius);
    }

    .rejection-reason-title {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        color: var(--danger);
        font-weight: var(--font-weight-semibold);
        margin-bottom: var(--spacing-2);
    }

    .rejection-reason-text {
        color: var(--text-primary);
        margin: 0;
    }

    .rejection-metadata {
        margin-top: var(--spacing-2);
        font-size: var(--font-size-xs);
        color: var(--text-muted);
    }

    /* Action Required Alert */
    .action-required-alert {
        border: solid 1px var(--border-light);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-5);
        color: white;
        margin-bottom: var(--spacing-5);
    }

    .action-required-content {
        display: flex;
        align-items: start;
        gap: var(--spacing-4);
    }

    .action-required-icon {
        font-size: 2rem;
        flex-shrink: 0;
        margin-top: var(--spacing-1);
    }

    .action-required-text {
        flex: 1;
    }

    .action-required-text h5 {
        margin: 0 0 var(--spacing-2) 0;
        font-weight: var(--font-weight-semibold);
    }

    .action-required-text p {
        margin: 0;
        opacity: 0.9;
    }

    .action-required-buttons {
        display: flex;
        gap: var(--spacing-2);
        margin-top: var(--spacing-3);
        flex-wrap: wrap;
    }

    /* Receipt Upload Alert */
    .receipt-upload-alert {
        border: solid 1px var(--warning);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-5);
        color: white;
        margin-bottom: var(--spacing-5);
    }

    /* Detail Grid */
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-4);
    }

    .detail-item {
        padding: var(--spacing-3);
        background: var(--bg-subtle);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
    }

    .detail-label {
        display: block;
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: var(--spacing-2);
        font-weight: var(--font-weight-medium);
    }

    .detail-value {
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .detail-value-muted {
        color: var(--text-muted);
        font-size: var(--font-size-sm);
        margin-left: var(--spacing-2);
    }

    /* Purpose Display */
    .purpose-display {
        margin-top: var(--spacing-4);
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-radius: var(--border-radius);
        border-left: 3px solid var(--primary);
    }

    .purpose-display label {
        display: block;
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-2);
    }

    .purpose-display p {
        margin: 0;
        white-space: pre-wrap;
        color: var(--text-primary);
        line-height: 1.6;
    }

    /* Items Table */
    .items-table-container {
        overflow-x: auto;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table thead th {
        background: var(--bg-subtle);
        padding: var(--spacing-3) var(--spacing-4);
        text-align: left;
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-secondary);
        border-bottom: 2px solid var(--border-color);
    }

    .items-table tbody td {
        padding: var(--spacing-3) var(--spacing-4);
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
    }

    .items-table tbody tr:hover {
        background: var(--bg-hover);
    }

    .items-table tfoot td {
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        font-weight: var(--font-weight-bold);
        font-size: var(--font-size-lg);
        border-top: 2px solid var(--border-color);
    }

    .items-table .text-right {
        text-align: right;
    }

    .items-table .item-number1 {
        font-weight: var(--font-weight-medium);
    }

    .items-table .subtotal {
        font-weight: var(--font-weight-semibold);
        color: var(--primary);
    }

    .items-table .total-amount {
        color: var(--success);
    }

    /* Document Items */
    .document-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        margin-bottom: var(--spacing-3);
        transition: var(--transition-fast);
    }

    .document-item:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .document-info {
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
        flex: 1;
        min-width: 0;
    }

    .document-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--border-radius);
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-xl);
        flex-shrink: 0;
    }

    .document-details {
        flex: 1;
        min-width: 0;
    }

    .document-name {
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0 0 var(--spacing-1) 0;
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        flex-wrap: wrap;
    }

    .document-meta {
        font-size: var(--font-size-xs);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: var(--spacing-2);
    }

    .meta-separator {
        color: var(--text-muted);
    }

    .document-actions {
        display: flex;
        gap: var(--spacing-2);
        flex-shrink: 0;
    }

    .previewable-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-1);
        padding: var(--spacing-1) var(--spacing-2);
        background: rgba(var(--info-rgb), 0.1);
        color: var(--info);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-medium);
    }

    /* Empty State */
    .empty-state {
        padding: var(--spacing-8) var(--spacing-4);
        text-align: center;
    }

    .empty-state i {
        font-size: 3rem;
        color: var(--text-muted);
        margin-bottom: var(--spacing-4);
        opacity: 0.5;
    }

    .empty-state h6 {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--spacing-2);
    }

    .empty-state p {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin: 0;
    }

    /* Timeline */
    .timeline {
        position: relative;
        padding-left: var(--spacing-6);
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 16px;
        top: 10px;
        bottom: 10px;
        width: 2px;
        background: var(--border-color);
    }

    .timeline-item {
        position: relative;
        padding-bottom: var(--spacing-5);
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item:last-child::after {
        display: none;
    }

    .timeline-marker {
        position: absolute;
        left: -37px;
        width: 34px;
        height: 34px;
        border-radius: var(--border-radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        z-index: 1;
        border: 3px solid var(--bg-card);
        font-size: var(--font-size-sm);
    }

    .timeline-marker.success {
        background: var(--success);
    }

    .timeline-marker.danger {
        background: var(--danger);
    }

    .timeline-content {
        background: var(--bg-subtle);
        padding: var(--spacing-4);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
        transition: var(--transition-fast);
    }

    .timeline-content:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-2);
        gap: var(--spacing-3);
    }

    .timeline-header strong {
        color: var(--text-primary);
        font-weight: var(--font-weight-semibold);
    }

    .timeline-role {
        font-size: var(--font-size-sm);
        color: var(--text-muted);
        margin-bottom: var(--spacing-2);
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .timeline-comment {
        background: var(--bg-card);
        padding: var(--spacing-3);
        border-radius: var(--border-radius);
        font-size: var(--font-size-sm);
        margin: var(--spacing-2) 0;
        border-left: 3px solid var(--primary);
        color: var(--text-primary);
    }

    .timeline-comment i {
        color: var(--primary);
        margin-right: var(--spacing-2);
    }

    .timeline-time {
        font-size: var(--font-size-xs);
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: var(--spacing-1);
    }

    /* Sidebar */
    .requisition-sidebar {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-4);
        height: 100%;
        /* Important for flex stretching */
    }

    .sidebar-card {
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        transition: var(--theme-transition);
    }

    .sidebar-card:hover {
        box-shadow: var(--shadow-sm);
    }

    .sidebar-card-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
    }

    .sidebar-card-header i {
        color: var(--primary);
        font-size: var(--font-size-lg);
    }

    .sidebar-card-header h6 {
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
    }

    .sidebar-card-body {
        padding: var(--spacing-4);
    }

    /* Target specifically the Approval History card body to enable scroll when content is long */
    #approvalHistoryCard .sidebar-card-body {
        flex: 1;
        /* This makes it grow to fill available space */
        overflow-y: auto;
        /* Scroll when content overflows */
        max-height: 500px;
        /* Optional: limit max height so it doesn't grow forever */
        padding-right: var(--spacing-2);
        /* Small offset for scrollbar aesthetics */
    }

    /* Optional: nicer scrollbar (WebKit) */
    #approvalHistoryCard .sidebar-card-body::-webkit-scrollbar {
        width: 6px;
    }

    #approvalHistoryCard .sidebar-card-body::-webkit-scrollbar-track {
        background: var(--bg-subtle);
        border-radius: 10px;
    }

    #approvalHistoryCard .sidebar-card-body::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 10px;
    }

    #approvalHistoryCard .sidebar-card-body::-webkit-scrollbar-thumb:hover {
        background: var(--text-muted);
    }

    /* Summary Items */
    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--spacing-3) 0;
        border-bottom: 1px solid var(--border-light);
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .summary-label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .summary-value {
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .summary-value.highlight {
        color: var(--success);
        font-size: var(--font-size-lg);
    }

    /* Quick Actions */
    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-2);
    }

    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        padding: var(--spacing-3);
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        color: var(--text-primary);
        text-decoration: none;
        transition: var(--transition-fast);
        font-size: var(--font-size-sm);
    }

    .quick-action-btn:hover {
        border-color: var(--primary);
        background: var(--bg-hover);
        transform: translateX(4px);
    }

    .quick-action-btn i {
        width: 20px;
        text-align: center;
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: var(--spacing-4);
        backdrop-filter: blur(4px);
    }

    .modal-dialog {
        width: 100%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        animation: modalSlideIn 0.3s ease-out;
    }

    .modal-dialog.modal-lg {
        max-width: 900px;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
    }

    .modal-header.bg-success {
        background: var(--success);
        color: white;
        border-bottom-color: var(--success);
    }

    .modal-header.bg-danger {
        background: var(--danger);
        color: white;
        border-bottom-color: var(--danger);
    }

    .modal-title {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        margin: 0;
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
    }

    .modal-body {
        padding: var(--spacing-5);
    }

    .modal-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--spacing-4);
        background: var(--bg-subtle);
        border-top: 1px solid var(--border-color);
        gap: var(--spacing-3);
    }

    /* Preview Modal */
    #previewModal .modal-body {
        padding: 0;
        background: #f8f9fa;
    }

    #previewContent {
        width: 100%;
        height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .preview-spinner {
        text-align: center;
        color: var(--text-muted);
    }

    .preview-spinner i {
        font-size: 3rem;
        margin-bottom: var(--spacing-3);
    }

    /* Mobile Optimizations */
    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }

        .section-header {
            padding: var(--spacing-4);
        }

        .section-body {
            padding: var(--spacing-4);
        }

        .section-icon {
            width: 36px;
            height: 36px;
            font-size: var(--font-size-base);
        }

        .status-banner {
            padding: var(--spacing-4);
        }

        .status-banner-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .action-required-alert,
        .receipt-upload-alert {
            padding: var(--spacing-4);
        }

        .action-required-content {
            flex-direction: column;
        }

        .action-required-buttons {
            flex-wrap: wrap;
        }

        .action-required-buttons .btn {
            flex: 1;
            min-width: 120px;
        }

        .document-item {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-3);
        }

        .document-actions {
            width: 100%;
        }

        .document-actions .btn {
            flex: 1;
        }

        .timeline {
            padding-left: var(--spacing-5);
        }

        .timeline-marker {
            width: 28px;
            height: 28px;
            left: -34px;
        }

        .modal-dialog,
        .modal-dialog.modal-lg {
            max-width: 100%;
        }

        #previewContent {
            height: 50vh !important;
        }

        .items-table {
            font-size: var(--font-size-sm);
        }

        .items-table thead th,
        .items-table tbody td {
            padding: var(--spacing-2) var(--spacing-3);
        }

        .content-actions {
            display: flex !important;
            justify-content: flex-end !important;
            gap: 0.5rem;
            flex-wrap: wrap;
            white-space: nowrap !important;
        }

        .content-actions .btn {
            flex: 0 1 auto !important;
            white-space: nowrap;
        }
    }
</style>


<!-- Content Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="content-title">
                <i class="fas fa-file-alt me-2"></i>
                Requisition <?php echo htmlspecialchars($reqData['requisition_number']); ?>
                <?php if ($reqData['is_draft']): ?>
                    <span class="badge badge-secondary ms-2">DRAFT</span>
                <?php endif; ?>
            </h1>
            <!-- <nav class="content-breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?php echo BASE_URL; ?>dashboard/" class="breadcrumb-link">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="list.php" class="breadcrumb-link">Requisitions</a>
                        </li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($reqData['requisition_number']); ?></li>
                    </ol>
                </nav> -->
            <p class="content-description">Created on <?php echo format_date($reqData['created_at'], 'F d, Y \a\t h:i A'); ?></p>
        </div>
        <div class="content-actions">
            <a href="list.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                <span>Back to List</span>
            </a>
            <?php if ($canEdit): ?>
                <a href="edit.php?id=<?php echo $reqData['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>
                    <span>Edit</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Success!</div>
            <div class="alert-message"><?php echo htmlspecialchars($successMessage); ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Error</div>
            <div class="alert-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Approval Actions Alert -->
<?php if ($canApprove): ?>
    <div class="action-required-alert">
        <div class="action-required-content">
            <i class="fas fa-exclamation-triangle action-required-icon"></i>
            <div class="action-required-text">
                <h5>Action Required</h5>
                <p>This requisition is awaiting your approval. Please review the details below and take action.</p>
                <div class="action-required-buttons">
                    <button type="button" class="btn btn-danger" onclick="showRejectModal()">
                        <i class="fas fa-times-circle me-2"></i>Reject
                    </button>
                    <button type="button" class="btn btn-light btn-outline-primary" onclick="showApproveModal()">
                        <i class="fas fa-check-circle me-2"></i>Approve
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Receipt Upload Alert -->
<?php if ($reqData['status'] == 'paid' && empty($reqData['receipt_file_path']) && $reqData['user_id'] == $_SESSION['user_id']): ?>
    <div class="receipt-upload-alert" style="border: 1px solid var(--success); border-radius: var(--border-radius-lg); padding: var(--spacing-5); margin-bottom: var(--spacing-5);">

        <div style="display: flex; align-items: flex-start; gap: 1rem;">
            <!-- Success Icon -->
            <i class="fas fa-check-circle"
                style="font-size: 2.2rem; color: var(--success); flex-shrink: 0; margin-top: 0.2rem;"></i>

            <!-- Content + Button (button at far right) -->
            <div style="flex: 1; display: flex; justify-content: space-between; align-items: flex-end; gap: 2rem; min-width: 0;">

                <!-- Left: Text Content -->
                <div style="flex: 1;">
                    <h5 style="margin: 0 0 var(--spacing-2) 0; font-weight: var(--font-weight-semibold); color: white;">
                        Payment Completed - Receipt Required
                    </h5>
                    <p style="margin: 0 0 var(--spacing-3) 0; opacity: 0.9; color: white;">
                        Your requisition has been paid! Please upload your receipt as proof of purchase to complete this requisition.
                    </p>

                    <?php if ($reqData['payment_date']): ?>
                        <p style="margin: 0; opacity: 0.9; font-size: var(--font-size-sm); color: rgba(255,255,255,0.9);">
                            <i class="fas fa-calendar-check me-2"></i>
                            Paid on <?php echo format_date($reqData['payment_date'], 'M d, Y \a\t h:i A'); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Right: Button - always at the far end -->
                <div style="flex-shrink: 0;">
                    <a href="upload-receipt.php?id=<?php echo $reqData['id']; ?>"
                        class="btn btn-success"
                        style="white-space: nowrap; font-weight: 600; padding: 0.65rem 1.25rem;">
                        <i class="fas fa-cloud-upload-alt me-2"></i>
                        Upload Receipt
                    </a>
                </div>
            </div>
        </div>

    </div>
<?php endif; ?>

<!-- Status Banner -->
<div class="status-banner <?php echo $reqData['status']; ?>">
    <div class="status-banner-header">
        <div class="status-info">
            <h5>
                <?php echo get_status_badge($reqData['status']); ?>
            </h5>

            <?php if ($reqData['rejection_reason']): ?>
                <div class="rejection-reason">
                    <div class="rejection-reason-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Rejection Reason</span>
                    </div>
                    <p class="rejection-reason-text"><?php echo htmlspecialchars($reqData['rejection_reason']); ?></p>
                    <?php if ($reqData['rejected_by_first_name']): ?>
                        <div class="rejection-metadata">
                            Rejected by <?php echo htmlspecialchars($reqData['rejected_by_first_name'] . ' ' . $reqData['rejected_by_last_name']); ?>
                            on <?php echo format_date($reqData['rejected_at'], 'M d, Y \a\t h:i A'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($canCancel): ?>
            <div>
                <button type="button" class="btn btn-danger" onclick="confirmCancel()">
                    <i class="fas fa-times-circle me-2"></i>Cancel Requisition
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Main Content Grid -->
<div class="requisition-view-grid">
    <!-- Main Content Section -->
    <div class="requisition-main-content">
        <!-- Requisition Details Card -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-header-content">
                    <div class="section-icon primary">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="section-title">
                        <h5>Requisition Details</h5>
                        <p>Basic information about this requisition</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label class="detail-label">Requester</label>
                        <div class="detail-value">
                            <?php echo htmlspecialchars($reqData['first_name'] . ' ' . $reqData['last_name']); ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <label class="detail-label">Department</label>
                        <div class="detail-value">
                            <?php echo htmlspecialchars($reqData['department_name']); ?>
                            <span class="detail-value-muted">
                                (<?php echo htmlspecialchars($reqData['department_code']); ?>)
                            </span>
                        </div>
                    </div>

                    <?php if ($reqData['submitted_at']): ?>
                        <div class="detail-item">
                            <label class="detail-label">Submitted</label>
                            <div class="detail-value">
                                <?php echo format_date($reqData['submitted_at'], 'M d, Y \a\t h:i A'); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (in_array($reqData['status'], ['paid', 'completed'])): ?>
                        <div class="detail-item">
                            <label>Processed by</label>
                            <div class="detail-value">
                                <?= htmlspecialchars($reqData['paid_by_first_name'] . ' ' . $reqData['paid_by_last_name']) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($reqData['current_approver_id'] && !is_final_status($reqData['status'])): ?>
                        <div class="detail-item">
                            <label class="detail-label">Current Approver</label>
                            <div class="detail-value">
                                <?php echo htmlspecialchars($reqData['approver_first_name'] . ' ' . $reqData['approver_last_name']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="purpose-display">
                    <label>Purpose/Category</label>
                    <p><?php echo htmlspecialchars($reqData['purpose']); ?></p>
                </div>

                <?php if (!empty($reqData['description'])): ?>
                    <div class="purpose-display" style="margin-top: var(--spacing-3);">
                        <label>Additional Details</label>
                        <p><?php echo nl2br(htmlspecialchars($reqData['description'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Items Card -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-header-content">
                    <div class="section-icon success">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="section-title">
                        <h5>Items (<?php echo count($reqData['items']); ?>)</h5>
                        <p>Itemized list of requested items</p>
                    </div>
                </div>
                <div class="summary-value highlight">₦<?php echo number_format((float)$reqData['total_amount'], 2); ?>
                </div>
            </div>
            <div class="section-body" style="padding: 0;">
                <div class="items-table-container">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th>Description</th>
                                <th style="width: 12%;">Qty</th>
                                <th style="width: 15%;" class="text-right">Unit Price</th>
                                <th style="width: 15%;" class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reqData['items'] as $index => $item): ?>
                                <tr>
                                    <td class="item-number1"><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($item['item_description']); ?></td>
                                    <td><?php echo number_format($item['quantity']); ?></td>
                                    <td class="text-right">₦<?php echo number_format((float)$item['unit_price'], 2); ?></td>
                                    <td class="text-right subtotal">₦<?php echo number_format((float)$item['subtotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right">Total Amount:</td>
                                <td class="text-right total-amount">₦<?php echo number_format((float)$reqData['total_amount'], 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Attachments Card -->
        <?php if (!empty($reqData['documents'])): ?>
            <div class="section-card" id="attachmentsCard">
                <div class="section-header">
                    <div class="section-header-content">
                        <div class="section-icon info">
                            <i class="fas fa-paperclip"></i>
                        </div>
                        <div class="section-title">
                            <h5>Attachments (<?php echo count($reqData['documents']); ?>)</h5>
                            <p>Supporting documents and files</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <?php foreach ($reqData['documents'] as $doc): ?>
                        <div class="document-item">
                            <div class="document-info">
                                <div class="document-icon">
                                    <i class="fas <?php echo FileUpload::getFileIcon($doc['file_name']); ?>"></i>
                                </div>
                                <div class="document-details">
                                    <div class="document-name">
                                        <?php echo htmlspecialchars($doc['file_name']); ?>
                                        <?php if (isPreviewable($doc['file_name'])): ?>
                                            <span class="previewable-badge">
                                                <i class="fas fa-eye"></i>Previewable
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="document-meta">
                                        <span><?php echo format_file_size($doc['file_size']); ?></span>
                                        <span class="meta-separator">•</span>
                                        <span>Uploaded by <?php echo htmlspecialchars($doc['uploaded_by_name']); ?></span>
                                        <span class="meta-separator">•</span>
                                        <span><?php echo format_date($doc['uploaded_at'], 'M d, Y'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="document-actions">
                                <?php if (isPreviewable($doc['file_name'])): ?>
                                    <button type="button" class="btn btn-sm btn-secondary"
                                        onclick="previewFile('<?php echo $doc['id']; ?>', '<?php echo htmlspecialchars($doc['file_name'], ENT_QUOTES); ?>', '<?php echo getPreviewType($doc['file_name']); ?>')">
                                        <i class="fas fa-eye me-1"></i>Preview
                                    </button>
                                <?php endif; ?>
                                <a href="../api/download-file.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="fas fa-download me-1"></i>Download
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar Section -->
    <div class="requisition-sidebar">
        <!-- Summary Card -->
        <div class="sidebar-card">
            <div class="sidebar-card-header">
                <i class="fas fa-calculator"></i>
                <h6>Summary</h6>
            </div>
            <div class="sidebar-card-body">
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-hashtag"></i>Requisition No.
                    </span>
                    <span class="summary-value"><?php echo htmlspecialchars($reqData['requisition_number']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-box"></i>Total Items
                    </span>
                    <span class="summary-value"><?php echo count($reqData['items']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-money-bill-wave"></i>Total Amount
                    </span>
                    <span class="summary-value highlight">₦<?php echo number_format((float)$reqData['total_amount'], 2); ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-info-circle"></i>Status
                    </span>
                    <span class="summary-value"><?php echo get_status_badge($reqData['status']); ?></span>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="sidebar-card">
            <div class="sidebar-card-header">
                <i class="fas fa-bolt"></i>
                <h6>Quick Actions</h6>
            </div>
            <div class="sidebar-card-body">
                <div class="quick-actions">
                    <a href="list.php" class="quick-action-btn">
                        <i class="fas fa-list"></i>
                        <span>All Requisitions</span>
                    </a>
                    <?php if ($canEdit): ?>
                        <a href="edit.php?id=<?php echo $reqData['id']; ?>" class="quick-action-btn">
                            <i class="fas fa-edit"></i>
                            <span>Edit Requisition</span>
                        </a>
                    <?php endif; ?>
                    <a href="create.php" class="quick-action-btn">
                        <i class="fas fa-plus"></i>
                        <span>Create New</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Approval History Card -->
        <div class="sidebar-card" id="approvalHistoryCard">
            <div class="sidebar-card-header">
                <i class="fas fa-history"></i>
                <h6>Approval History</h6>
            </div>
            <div class="sidebar-card-body">
                <?php if (empty($reqData['approvals'])): ?>
                    <div class="empty-state" style="padding: var(--spacing-4);">
                        <i class="fas fa-info-circle" style="font-size: 2rem;"></i>
                        <p style="margin: var(--spacing-2) 0 0 0;">No approvals yet</p>
                    </div>
                <?php else: ?>
                    <?php
                    // Sort approvals by newest first
                    usort($reqData['approvals'], function ($a, $b) {
                        return strtotime($b['created_at']) - strtotime($a['created_at']);
                    });
                    ?>
                    <div class="timeline">
                        <?php foreach ($reqData['approvals'] as $approval): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker <?php echo $approval['action'] == 'approved' ? 'success' : 'danger'; ?>">
                                    <i class="fas <?php echo $approval['action'] == 'approved' ? 'fa-check' : 'fa-times'; ?>"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <strong><?php echo htmlspecialchars($approval['approver_name']); ?></strong>
                                        <span class="badge badge-<?php echo $approval['action'] == 'approved' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($approval['action']); ?>
                                        </span>
                                    </div>
                                    <div class="timeline-role">
                                        <i class="fas fa-user-tag"></i>
                                        <?php echo htmlspecialchars($approval['role_name']); ?>
                                    </div>
                                    <?php if ($approval['comments']): ?>
                                        <div class="timeline-comment">
                                            <i class="fas fa-comment-alt"></i>
                                            <?php echo htmlspecialchars($approval['comments']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="timeline-time">
                                        <i class="fas fa-clock"></i>
                                        <?php echo format_date($approval['created_at'], 'M d, Y \a\t h:i A'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>

<!-- File Preview Modal -->
<div id="previewModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="section-card">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i><span id="previewFileName">File Preview</span>
                </h5>
                <button type="button" class="btn btn-ghost btn-sm" onclick="closePreviewModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <div class="preview-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Loading preview...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePreviewModal()">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <a id="previewDownloadBtn" href="#" class="btn btn-primary" target="_blank">
                    <i class="fas fa-download me-2"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog">
        <div class="section-card">
            <div class="modal-header bg-success">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Approve Requisition
                </h5>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve this requisition?</p>
                <p><strong>Requisition:</strong> <?php echo htmlspecialchars($reqData['requisition_number']); ?><br>
                    <strong>Amount:</strong>₦<?php echo number_format((float)$reqData['total_amount'], 2); ?>
                </p>

                <form method="POST" action="approve.php" id="approveForm">
                    <?php echo Session::csrfField(); ?>
                    <input type="hidden" name="requisition_id" value="<?php echo $reqData['id']; ?>">

                    <div class="form-group">
                        <label for="approve_comments" class="form-label">Comments (Optional)</label>
                        <textarea name="comments" id="approve_comments" class="form-control" rows="3"
                            placeholder="Add any comments about this approval..."></textarea>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary" onclick="closeApproveModal()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle me-2"></i>Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog">
        <div class="section-card">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle"></i> Reject Requisition
                </h5>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reject this requisition?</p>
                <p><strong>Requisition:</strong> <?php echo htmlspecialchars($reqData['requisition_number']); ?></p>

                <form method="POST" action="reject.php" id="rejectForm">
                    <?php echo Session::csrfField(); ?>
                    <input type="hidden" name="requisition_id" value="<?php echo $reqData['id']; ?>">

                    <div class="form-group">
                        <label for="reject_reason" class="form-label">
                            Rejection Reason <span class="text-danger">*</span>
                        </label>
                        <textarea name="reason" id="reject_reason" class="form-control" rows="4"
                            placeholder="Please provide a clear reason for rejection..." required></textarea>
                        <div class="form-text">This will be visible to the requester.</div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times-circle me-2"></i>Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<?php if ($canCancel): ?>
    <div id="cancelModal" class="modal-overlay" style="display: none;">
        <div class="modal-dialog">
            <div class="section-card">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i> Cancel Requisition
                    </h5>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this requisition? This action cannot be undone.</p>
                    <form method="POST" action="cancel.php">
                        <?php echo Session::csrfField(); ?>
                        <input type="hidden" name="requisition_id" value="<?php echo $reqData['id']; ?>">
                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">
                                No, Keep It
                            </button>
                            <button type="submit" class="btn btn-danger">
                                Yes, Cancel Requisition
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Isolated JavaScript -->
<script>
    // Namespace to avoid conflicts
    const RequisitionViewer = {
        init() {
            this.initializeEventListeners();
        },

        initializeEventListeners() {
            // Close modals on outside click
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            // ESC key closes all modals
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay').forEach(modal => {
                        modal.style.display = 'none';
                    });
                }
            });

            // Form validation for reject
            const rejectForm = document.getElementById('rejectForm');
            if (rejectForm) {
                rejectForm.addEventListener('submit', (e) => {
                    const reason = document.getElementById('reject_reason').value.trim();
                    if (reason.length < 10) {
                        e.preventDefault();
                        alert('Please provide a detailed reason for rejection (at least 10 characters).');
                        return false;
                    }
                });
            }
        },

        previewFile(fileId, fileName, type) {
            const modal = document.getElementById('previewModal');
            const content = document.getElementById('previewContent');
            const fileNameEl = document.getElementById('previewFileName');
            const downloadBtn = document.getElementById('previewDownloadBtn');

            // Set file name
            fileNameEl.textContent = fileName;

            // Set download link
            downloadBtn.href = '../api/download-file.php?id=' + fileId;

            // Show modal
            modal.style.display = 'flex';

            // Show loading
            content.innerHTML = '<div class="preview-spinner"><i class="fas fa-spinner fa-spin"></i><p>Loading preview...</p></div>';

            // Build preview URL
            const previewUrl = '../api/download-file.php?id=' + fileId;

            // Load content based on type
            if (type === 'image') {
                content.innerHTML = '<img src="' + previewUrl + '" alt="' + fileName + '" style="max-width: 100%; max-height: 70vh; object-fit: contain;">';
            } else if (type === 'pdf') {
                content.innerHTML = '<iframe src="' + previewUrl + '" style="width: 100%; height: 70vh; border: none;"></iframe>';
            }
        },

        closePreviewModal() {
            document.getElementById('previewModal').style.display = 'none';
            document.getElementById('previewContent').innerHTML = '';
        },

        showApproveModal() {
            document.getElementById('approveModal').style.display = 'flex';
        },

        closeApproveModal() {
            document.getElementById('approveModal').style.display = 'none';
        },

        showRejectModal() {
            document.getElementById('rejectModal').style.display = 'flex';
        },

        closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        },

        confirmCancel() {
            document.getElementById('cancelModal').style.display = 'flex';
        },

        closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }
    };

    // Global function wrappers for onclick handlers
    function previewFile(fileId, fileName, type) {
        RequisitionViewer.previewFile(fileId, fileName, type);
    }

    function closePreviewModal() {
        RequisitionViewer.closePreviewModal();
    }

    function showApproveModal() {
        RequisitionViewer.showApproveModal();
    }

    function closeApproveModal() {
        RequisitionViewer.closeApproveModal();
    }

    function showRejectModal() {
        RequisitionViewer.showRejectModal();
    }

    function closeRejectModal() {
        RequisitionViewer.closeRejectModal();
    }

    function confirmCancel() {
        RequisitionViewer.confirmCancel();
    }

    function closeCancelModal() {
        RequisitionViewer.closeCancelModal();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => RequisitionViewer.init());
    } else {
        RequisitionViewer.init();
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
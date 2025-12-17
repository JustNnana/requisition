<?php

/**
 * GateWey Requisition Management System
 * Audit Log
 * 
 * File: admin/audit-log.php
 * Purpose: View system audit log with filtering and search
 */

// Define access level
define('APP_ACCESS', true);
define('ADMIN_ACCESS', true);

// Include necessary files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../helpers/permissions.php';

// Start session
Session::start();

// Check authentication and authorization
require_once __DIR__ . '/../middleware/auth-check.php';
require_once __DIR__ . '/../middleware/role-check.php';

// Initialize database
$db = Database::getInstance();

// Initialize variables
$errors = [];
$logs = [];
$totalRecords = 0;

// Get filter parameters
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterUser = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$filterAction = isset($_GET['action_type']) ? trim($_GET['action_type']) : '';
$filterDateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$filterDateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

// Build query
$whereClauses = [];
$params = [];

if (!empty($searchQuery)) {
  $whereClauses[] = "(al.description LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
  $searchParam = '%' . $searchQuery . '%';
  $params[] = $searchParam;
  $params[] = $searchParam;
  $params[] = $searchParam;
}

if ($filterUser > 0) {
  $whereClauses[] = "al.user_id = ?";
  $params[] = $filterUser;
}

if (!empty($filterAction)) {
  $whereClauses[] = "al.action = ?";
  $params[] = $filterAction;
}

if (!empty($filterDateFrom)) {
  $whereClauses[] = "DATE(al.created_at) >= ?";
  $params[] = $filterDateFrom;
}

if (!empty($filterDateTo)) {
  $whereClauses[] = "DATE(al.created_at) <= ?";
  $params[] = $filterDateTo;
}

$whereSQL = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Get total count
$countSql = "SELECT COUNT(*) as total 
             FROM audit_log al
             LEFT JOIN users u ON al.user_id = u.id
             $whereSQL";
$countResult = $db->fetchOne($countSql, $params);
$totalRecords = (int) ($countResult['total'] ?? 0);

// Get all logs (no pagination)
$logSql = "SELECT 
               al.id as audit_id,
               al.user_id,
               al.action as action_type,
               al.description as action_description,
               al.ip_address,
               al.created_at,
               u.first_name,
               u.last_name,
               u.email,
               r.role_name
           FROM audit_log al
           LEFT JOIN users u ON al.user_id = u.id
           LEFT JOIN roles r ON u.role_id = r.id
           $whereSQL
           ORDER BY al.created_at DESC";

$logs = $db->fetchAll($logSql, $params);

// Get all users for filter dropdown
$usersSql = "SELECT DISTINCT u.id as user_id, u.first_name, u.last_name, u.email
             FROM audit_log al
             INNER JOIN users u ON al.user_id = u.id
             ORDER BY u.first_name, u.last_name";
$allUsers = $db->fetchAll($usersSql);

// Get all action types for filter dropdown
$actionsSql = "SELECT DISTINCT action as action_type FROM audit_log ORDER BY action";
$allActions = $db->fetchAll($actionsSql);

// Function to get action badge color
function getActionBadgeColor($actionType)
{
  $actionType = strtolower($actionType);

  if (strpos($actionType, 'login') !== false || strpos($actionType, 'created') !== false) {
    return 'success';
  } elseif (strpos($actionType, 'logout') !== false || strpos($actionType, 'deleted') !== false) {
    return 'danger';
  } elseif (strpos($actionType, 'updated') !== false || strpos($actionType, 'approved') !== false) {
    return 'info';
  } elseif (strpos($actionType, 'rejected') !== false || strpos($actionType, 'failed') !== false) {
    return 'warning';
  } else {
    return 'secondary';
  }
}

// Page title
$pageTitle = 'Audit Log';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Enhanced Styles -->
<style>
  /* Page Layout */
  .audit-container {
    max-width: 1400px;
    margin: 0 auto;
  }

  /* Filter Card */
  .filter-card {
    background: transparent;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    margin-bottom: var(--spacing-6);
  }

  .filter-header {
    padding: var(--spacing-4);
    background: var(--bg-subtle);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .filter-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    margin: 0;
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
  }

  .filter-icon {
    color: var(--primary);
  }

  .filter-toggle {
    background: none;
    border: none;
    color: var(--primary);
    cursor: pointer;
    font-size: var(--font-size-base);
    padding: var(--spacing-2);
    transition: var(--theme-transition);
  }

  .filter-toggle:hover {
    color: var(--primary-dark);
  }

  .filter-body {
    padding: var(--spacing-5);
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-4);
    transition: all 0.3s ease;
  }

  .filter-body.collapsed {
    display: none !important;
  }

  /* Form Group */
  .form-group {
    margin-bottom: 0;
  }

  .form-label {
    display: block;
    margin-bottom: var(--spacing-2);
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
    font-size: var(--font-size-sm);
  }

  .form-control {
    width: 100%;
    padding: var(--spacing-3) var(--spacing-4);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: var(--font-size-sm);
    transition: var(--theme-transition);
  }

  .form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
  }

  .filter-actions {
    grid-column: 1 / -1;
    display: flex;
    gap: var(--spacing-3);
    justify-content: flex-end;
    margin-top: var(--spacing-2);
  }

  /* Stats Bar */
  .stats-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-4);
    background: var(--bg-subtle);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing-4);
  }

  .stats-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
  }

  .stats-info i {
    color: var(--primary);
  }

  .stats-count {
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
  }

  /* Table Card */
  .table-card {
    background: transparent;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
  }

  .table-container {
    overflow-x: auto;
    max-height: 70vh;
    overflow-y: auto;
  }

  /* Audit Table */
  .audit-table {
    width: 100%;
    border-collapse: collapse;
    font-size: var(--font-size-sm);
  }

  .audit-table thead {
    background: var(--bg-subtle);
    border-bottom: 2px solid var(--border-color);
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .audit-table th {
    padding: var(--spacing-4);
    text-align: left;
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    white-space: nowrap;
    background: var(--bg-subtle);
  }

  .audit-table tbody tr {
    border-bottom: 1px solid var(--border-color);
    transition: var(--theme-transition);
  }

  .audit-table tbody tr:hover {
    background: var(--bg-subtle);
  }

  .audit-table tbody tr:last-child {
    border-bottom: none;
  }

  .audit-table td {
    padding: var(--spacing-4);
    color: var(--text-secondary);
    vertical-align: top;
  }

  /* User Info */
  .user-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
  }

  .user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: var(--font-weight-semibold);
    font-size: var(--font-size-sm);
    flex-shrink: 0;
  }

  .user-details {
    flex: 1;
    min-width: 0;
  }

  .user-name {
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
    margin-bottom: 2px;
  }

  .user-email {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
  }

  /* Action Badge */
  .action-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-2);
    padding: var(--spacing-1) var(--spacing-3);
    border-radius: var(--border-radius);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    white-space: nowrap;
  }

  .action-badge.badge-success {
    background: rgba(var(--success-rgb), 0.1);
    color: var(--success);
  }

  .action-badge.badge-danger {
    background: rgba(var(--danger-rgb), 0.1);
    color: var(--danger);
  }

  .action-badge.badge-info {
    background: rgba(var(--info-rgb), 0.1);
    color: var(--info);
  }

  .action-badge.badge-warning {
    background: rgba(var(--warning-rgb), 0.1);
    color: var(--warning);
  }

  .action-badge.badge-secondary {
    background: var(--bg-subtle);
    color: var(--text-secondary);
  }

  /* Description */
  .log-description {
    color: var(--text-secondary);
    line-height: 1.5;
  }

  /* Timestamp */
  .log-timestamp {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .log-date {
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
  }

  .log-time {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
  }

  /* IP Address */
  .ip-address {
    font-family: 'Courier New', monospace;
    background: var(--bg-subtle);
    padding: var(--spacing-1) var(--spacing-2);
    border-radius: var(--border-radius-sm);
    font-size: var(--font-size-xs);
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: var(--spacing-8);
    color: var(--text-muted);
  }

  .empty-state-icon {
    font-size: 4rem;
    opacity: 0.3;
    margin-bottom: var(--spacing-4);
  }

  .empty-state-text {
    font-size: var(--font-size-base);
    color: var(--text-secondary);
    margin-bottom: var(--spacing-2);
  }

  .empty-state-hint {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
  }

  /* Breadcrumb Styling */
  .content-breadcrumb {
    margin-top: var(--spacing-2);
  }

  .breadcrumb {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: var(--spacing-1);
    align-items: center;
  }

  .breadcrumb-item {
    display: flex;
    align-items: center;
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
  }

  .breadcrumb-item a {
    color: var(--primary);
    text-decoration: none;
    transition: var(--theme-transition);
  }

  .breadcrumb-item a:hover {
    color: var(--primary-dark);
    text-decoration: underline;
  }

  .breadcrumb-item.active {
    color: var(--text-primary);
  }

  .breadcrumb-item+.breadcrumb-item::before {
    content: "/";
    margin: 0 var(--spacing-2);
    color: var(--text-muted);
  }

  /* Content Header */
  .content-header {
    margin-bottom: var(--spacing-6);
  }

  /* Responsive Adjustments */
  @media (max-width: 992px) {
    .filter-body {
      grid-template-columns: 1fr;
    }

    .stats-bar {
      flex-direction: column;
      gap: var(--spacing-3);
      align-items: flex-start;
    }
  }

  @media (max-width: 768px) {
    .user-info {
      flex-direction: column;
      align-items: flex-start;
    }

    .audit-table th,
    .audit-table td {
      padding: var(--spacing-3);
    }

    .content-header {
      flex-direction: column !important;
    }

    .content-actions {
      display: flex !important;
      flex-direction: column !important;
      gap: 0.5rem !important;
    }
  }
</style>

<!-- Content Header -->
<div class="content-header">
  <div class="d-flex justify-content-between align-items-start">
    <div>
      <h1 class="content-title">Audit Log</h1>
      <!--<nav class="content-breadcrumb">-->
      <!--  <ol class="breadcrumb">-->
      <!--    <li class="breadcrumb-item">-->
      <!--      <a href="<?php echo BASE_URL; ?>/" class="breadcrumb-link">Dashboard</a>-->
      <!--    </li>-->
      <!--    <li class="breadcrumb-item">-->
      <!--      <a href="index.php">Admin</a>-->
      <!--    </li>-->
      <!--    <li class="breadcrumb-item active">Audit Log</li>-->
      <!--  </ol>-->
      <!--</nav>-->
    </div>
    <div class="content-actions">
      <button type="button" class="btn btn-success" onclick="exportToExcel()">
        <i class="fas fa-file-excel"></i>
        <span>Export to Excel</span>
      </button>
      <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Dashboard</span>
      </a>
    </div>
  </div>
</div>

<div class="audit-container">
  <!-- Filter Card -->
  <div class="filter-card">
    <div class="filter-header">
      <h3 class="filter-title">
        <i class="fas fa-filter filter-icon"></i>
        Filters
      </h3>
      <button type="button" class="filter-toggle" id="filterToggle" aria-expanded="true">
        <i class="fas fa-chevron-up"></i>
      </button>
    </div>
    <form method="GET" action="" id="filterForm">
      <div class="filter-body" id="filterBody">
        <div class="form-group">
          <label for="search" class="form-label">Search</label>
          <input type="text"
            id="search"
            name="search"
            class="form-control"
            placeholder="Search descriptions..."
            value="<?php echo htmlspecialchars($searchQuery); ?>">
        </div>

        <div class="form-group">
          <label for="user_id" class="form-label">User</label>
          <select id="user_id" name="user_id" class="form-control">
            <option value="">All Users</option>
            <?php foreach ($allUsers as $user): ?>
              <option value="<?php echo $user['user_id']; ?>"
                <?php echo ($filterUser == $user['user_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="action_type" class="form-label">Action Type</label>
          <select id="action_type" name="action_type" class="form-control">
            <option value="">All Actions</option>
            <?php foreach ($allActions as $action): ?>
              <option value="<?php echo htmlspecialchars($action['action_type']); ?>"
                <?php echo ($filterAction == $action['action_type']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $action['action_type']))); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="date_from" class="form-label">Date From</label>
          <input type="date"
            id="date_from"
            name="date_from"
            class="form-control"
            value="<?php echo htmlspecialchars($filterDateFrom); ?>">
        </div>

        <div class="form-group">
          <label for="date_to" class="form-label">Date To</label>
          <input type="date"
            id="date_to"
            name="date_to"
            class="form-control"
            value="<?php echo htmlspecialchars($filterDateTo); ?>">
        </div>

        <div class="filter-actions">
          <a href="audit-log.php" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Reset
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Apply Filters
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- Stats Bar -->
  <div class="stats-bar">
    <div class="stats-info">
      <i class="fas fa-list"></i>
      <span>Showing <span class="stats-count"><?php echo number_format($totalRecords); ?></span> total records</span>
    </div>
    <?php if ($totalRecords > 50): ?>
      <div class="stats-info">
        <i class="fas fa-info-circle"></i>
        <span class="text-muted">Use filters to narrow down results</span>
      </div>
    <?php endif; ?>
  </div>

  <!-- Audit Log Table -->
  <div class="table-card">
    <div class="table-container">
      <?php if (empty($logs)): ?>
        <div class="empty-state">
          <div class="empty-state-icon">
            <i class="fas fa-clipboard-list"></i>
          </div>
          <p class="empty-state-text">No audit log entries found</p>
          <p class="empty-state-hint">Try adjusting your filters or search criteria</p>
        </div>
      <?php else: ?>
        <table class="audit-table">
          <thead>
            <tr>
              <th style="width: 15%;">Timestamp</th>
              <th style="width: 20%;">User</th>
              <th style="width: 15%;">Action</th>
              <th style="width: 40%;">Description</th>
              <?php if (defined('LOG_IP_ADDRESS') && LOG_IP_ADDRESS): ?>
                <th style="width: 10%;">IP Address</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $log): ?>
              <tr>
                <td>
                  <div class="log-timestamp">
                    <span class="log-date">
                      <?php echo date('M d, Y', strtotime($log['created_at'])); ?>
                    </span>
                    <span class="log-time">
                      <?php echo date('h:i A', strtotime($log['created_at'])); ?>
                    </span>
                  </div>
                </td>
                <td>
                  <div class="user-info">
                    <div class="user-avatar">
                      <?php
                      $initials = strtoupper(
                        substr($log['first_name'] ?? 'U', 0, 1) .
                          substr($log['last_name'] ?? 'N', 0, 1)
                      );
                      echo htmlspecialchars($initials);
                      ?>
                    </div>
                    <div class="user-details">
                      <div class="user-name">
                        <?php echo htmlspecialchars(($log['first_name'] ?? 'Unknown') . ' ' . ($log['last_name'] ?? 'User')); ?>
                      </div>
                      <div class="user-email">
                        <?php echo htmlspecialchars($log['email'] ?? 'N/A'); ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <?php
                  $badgeColor = getActionBadgeColor($log['action_type']);
                  $actionDisplay = ucwords(str_replace('_', ' ', $log['action_type']));
                  ?>
                  <span class="action-badge badge-<?php echo $badgeColor; ?>">
                    <i class="fas fa-circle" style="font-size: 6px;"></i>
                    <?php echo htmlspecialchars($actionDisplay); ?>
                  </span>
                </td>
                <td>
                  <div class="log-description">
                    <?php echo htmlspecialchars($log['action_description']); ?>
                  </div>
                </td>
                <?php if (defined('LOG_IP_ADDRESS') && LOG_IP_ADDRESS): ?>
                  <td>
                    <?php if (!empty($log['ip_address'])): ?>
                      <span class="ip-address">
                        <?php echo htmlspecialchars($log['ip_address']); ?>
                      </span>
                    <?php else: ?>
                      <span class="text-muted">N/A</span>
                    <?php endif; ?>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('📋 Initializing Audit Log...');

    // Filter toggle functionality
    const filterToggle = document.getElementById('filterToggle');
    const filterBody = document.getElementById('filterBody');

    if (filterToggle && filterBody) {
      console.log('✅ Filter elements found');

      filterToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        console.log('🔄 Toggle clicked');

        // Toggle the collapsed class
        const isCollapsing = !filterBody.classList.contains('collapsed');

        if (isCollapsing) {
          filterBody.classList.add('collapsed');
          console.log('📦 Filter collapsed');
        } else {
          filterBody.classList.remove('collapsed');
          console.log('📂 Filter expanded');
        }

        // Update icon
        const icon = filterToggle.querySelector('i');
        if (icon) {
          if (isCollapsing) {
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
            filterToggle.setAttribute('aria-expanded', 'false');
          } else {
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
            filterToggle.setAttribute('aria-expanded', 'true');
          }
        }
      });

      console.log('✅ Filter toggle event listener attached');
    } else {
      console.error('❌ Filter elements not found');
    }

    console.log('✅ Audit Log initialized successfully');
  });

  // Export to Excel function
  function exportToExcel() {
    // Get current filter parameters
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');

    // Redirect to export script
    window.location.href = 'export-audit-log.php?' + params.toString();
  }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
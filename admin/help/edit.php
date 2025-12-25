<?php
/**
 * GateWey Requisition Management System
 * Create/Edit Help Item
 *
 * File: admin/help/create.php
 * Purpose: Form to create or edit help content
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../../middleware/auth-check.php';

// Check if user is super admin (role_id = 1)
if (Session::getUserRoleId() != 1) {
    Session::setFlash('error', 'Unauthorized access.');
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

// Initialize model
$helpModel = new HelpSupport();

// Check if editing
$isEdit = false;
$item = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $item = $helpModel->getById($id);
    if ($item) {
        $isEdit = true;
    } else {
        Session::setFlash('error', 'Help item not found.');
        header('Location: index.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Sanitize and validate input
        $data = [
            'title' => Sanitizer::string($_POST['title'] ?? ''),
            'description' => Sanitizer::string($_POST['description'] ?? ''),
            'type' => Sanitizer::string($_POST['type'] ?? 'tip'),
            'video_url' => Sanitizer::string($_POST['video_url'] ?? ''),
            'category' => Sanitizer::string($_POST['category'] ?? ''),
            'icon' => Sanitizer::string($_POST['icon'] ?? 'fa-info-circle'),
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Validate required fields
        if (empty($data['title'])) {
            $error = 'Title is required.';
        } elseif (empty($data['description'])) {
            $error = 'Description is required.';
        } elseif (empty($data['category'])) {
            $error = 'Category is required.';
        } elseif ($data['type'] === 'video' && empty($data['video_url'])) {
            $error = 'Video URL is required for video type.';
        } else {
            // Save data
            if ($isEdit) {
                $result = $helpModel->update($id, $data);
            } else {
                $result = $helpModel->create($data);
            }

            if ($result['success']) {
                Session::setFlash('success', $result['message']);
                header('Location: index.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Prepare form data
$formData = $isEdit ? $item : [
    'title' => '',
    'description' => '',
    'type' => 'tip',
    'video_url' => '',
    'category' => '',
    'icon' => 'fa-info-circle',
    'display_order' => 0,
    'is_active' => 1
];

// Override with POST data if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = array_merge($formData, $_POST);
    $formData['is_active'] = isset($_POST['is_active']) ? 1 : 0;
}

$pageTitle = $isEdit ? 'Edit Help Item' : 'Create Help Item';
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="page-wrapper">
    <div class="page-header">
        <div class="page-header-content">
            <div class="page-header-text">
                <h1><i class="fas <?php echo $isEdit ? 'fa-edit' : 'fa-plus'; ?>"></i> <?php echo $pageTitle; ?></h1>
                <p><?php echo $isEdit ? 'Update help content information' : 'Create new help article, tip, or video tutorial'; ?></p>
            </div>
        </div>
        <div class="page-actions">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error alert-dismissible">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <form method="POST" class="form-layout">
        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCsrfToken(); ?>">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    Basic Information
                </h3>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="form-group col-span-2">
                        <label for="title" class="form-label required">Title</label>
                        <input type="text"
                               id="title"
                               name="title"
                               class="form-control"
                               value="<?php echo htmlspecialchars($formData['title']); ?>"
                               placeholder="e.g., How to Create a Requisition"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="type" class="form-label required">Content Type</label>
                        <select id="type" name="type" class="form-control" required onchange="toggleVideoField()">
                            <option value="tip" <?php echo $formData['type'] === 'tip' ? 'selected' : ''; ?>>Tip</option>
                            <option value="article" <?php echo $formData['type'] === 'article' ? 'selected' : ''; ?>>Article</option>
                            <option value="video" <?php echo $formData['type'] === 'video' ? 'selected' : ''; ?>>Video Tutorial</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category" class="form-label required">Category</label>
                        <input type="text"
                               id="category"
                               name="category"
                               class="form-control"
                               value="<?php echo htmlspecialchars($formData['category']); ?>"
                               placeholder="e.g., Requisitions, Approvals, Payments"
                               list="category-suggestions"
                               required>
                        <datalist id="category-suggestions">
                            <option value="Requisitions">
                            <option value="Approvals">
                            <option value="Payments">
                            <option value="Reports">
                            <option value="Settings">
                            <option value="Getting Started">
                        </datalist>
                    </div>

                    <div class="form-group">
                        <label for="icon" class="form-label">Icon (FontAwesome class)</label>
                        <input type="text"
                               id="icon"
                               name="icon"
                               class="form-control"
                               value="<?php echo htmlspecialchars($formData['icon']); ?>"
                               placeholder="e.g., fa-info-circle">
                        <small class="form-text text-muted">
                            Browse icons at <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com</a>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="display_order" class="form-label">Display Order</label>
                        <input type="number"
                               id="display_order"
                               name="display_order"
                               class="form-control"
                               value="<?php echo htmlspecialchars($formData['display_order']); ?>"
                               min="0">
                        <small class="form-text text-muted">Lower numbers appear first</small>
                    </div>

                    <div class="form-group col-span-2" id="video-url-group" style="display: <?php echo $formData['type'] === 'video' ? 'block' : 'none'; ?>;">
                        <label for="video_url" class="form-label">Video URL</label>
                        <input type="url"
                               id="video_url"
                               name="video_url"
                               class="form-control"
                               value="<?php echo htmlspecialchars($formData['video_url']); ?>"
                               placeholder="https://www.youtube.com/watch?v=... or Google Drive/OneDrive link">
                        <small class="form-text text-muted">
                            Supports YouTube, Google Drive, OneDrive, Vimeo, and direct video files (MP4). Video will be embedded in the page.
                        </small>
                    </div>

                    <div class="form-group col-span-2">
                        <label for="description" class="form-label required">Description / Content</label>
                        <textarea id="description"
                                  name="description"
                                  class="form-control"
                                  rows="6"
                                  placeholder="Enter detailed description or article content..."
                                  required><?php echo htmlspecialchars($formData['description']); ?></textarea>
                    </div>

                    <div class="form-group col-span-2">
                        <label class="form-checkbox">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   <?php echo $formData['is_active'] ? 'checked' : ''; ?>>
                            <span>Active (visible to users)</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?php echo $isEdit ? 'Update Help Item' : 'Create Help Item'; ?>
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Cancel
            </a>
        </div>
    </form>
</div>

<style>
    .form-layout {
        max-width: 100%;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-4);
    }

    .col-span-2 {
        grid-column: span 2;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-label.required::after {
        content: '*';
        color: var(--danger);
        margin-left: var(--spacing-1);
    }

    .form-checkbox {
        display: flex;
        align-items: center;
        gap: var(--spacing-2);
        cursor: pointer;
    }

    .form-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .form-actions {
        display: flex;
        gap: var(--spacing-3);
        margin-top: var(--spacing-6);
    }
</style>

<script>
function toggleVideoField() {
    const type = document.getElementById('type').value;
    const videoGroup = document.getElementById('video-url-group');
    const videoInput = document.getElementById('video_url');

    if (type === 'video') {
        videoGroup.style.display = 'block';
        videoInput.required = true;
    } else {
        videoGroup.style.display = 'none';
        videoInput.required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleVideoField();
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

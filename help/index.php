<?php
/**
 * GateWey Requisition Management System
 * Help & Support Page
 *
 * File: help/index.php
 * Purpose: Display help articles, tips, and video tutorials for users
 */

// Define access level
define('APP_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Session::start();

// Check authentication
require_once __DIR__ . '/../middleware/auth-check.php';

// Initialize model
$helpModel = new HelpSupport();

// Get filter parameters
$typeFilter = isset($_GET['type']) ? Sanitizer::string($_GET['type']) : '';
$categoryFilter = isset($_GET['category']) ? Sanitizer::string($_GET['category']) : '';

// Build filters array
$filters = ['is_active' => 1];
if (!empty($typeFilter)) {
    $filters['type'] = $typeFilter;
}
if (!empty($categoryFilter)) {
    $filters['category'] = $categoryFilter;
}

// Get help items
$helpItems = $helpModel->getAll($filters);

// Get all categories for filter dropdown
$allCategories = $helpModel->getCategories();

$pageTitle = 'Help & Support';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-wrapper">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <div class="page-header-text">
                <h1><i class="fas fa-question-circle"></i> Help & Support</h1>
                <p>Find helpful tips, tutorials, and guides to use the system effectively</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card filters-card">
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="type" class="form-label">
                            <i class="fas fa-filter"></i>
                            Content Type
                        </label>
                        <select name="type" id="type" class="form-control" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="tip" <?php echo $typeFilter === 'tip' ? 'selected' : ''; ?>>
                                <i class="fas fa-lightbulb"></i> Tips
                            </option>
                            <option value="article" <?php echo $typeFilter === 'article' ? 'selected' : ''; ?>>
                                <i class="fas fa-file-alt"></i> Articles
                            </option>
                            <option value="video" <?php echo $typeFilter === 'video' ? 'selected' : ''; ?>>
                                <i class="fas fa-video"></i> Video Tutorials
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category" class="form-label">
                            <i class="fas fa-tag"></i>
                            Category
                        </label>
                        <select name="category" id="category" class="form-control" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($allCategories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $categoryFilter === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group filter-actions">
                        <?php if (!empty($typeFilter) || !empty($categoryFilter)): ?>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Clear Filters
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Help Items -->
    <?php if (empty($helpItems)): ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Help Content Found</h3>
                    <p>There are no help items matching your filters. Try adjusting your search criteria.</p>
                    <?php if (!empty($typeFilter) || !empty($categoryFilter)): ?>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-times"></i>
                            Clear Filters
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="help-items-grid">
            <?php foreach ($helpItems as $item): ?>
                <div class="help-item-card <?php echo $item['type']; ?>-card">
                    <!-- Card Header -->
                    <div class="help-item-header">
                        <div class="help-item-icon">
                            <i class="fas <?php echo htmlspecialchars($item['icon']); ?>"></i>
                        </div>
                        <div class="help-item-meta">
                            <?php
                            $typeColors = [
                                'tip' => 'info',
                                'video' => 'danger',
                                'article' => 'success'
                            ];
                            $typeIcons = [
                                'tip' => 'fa-lightbulb',
                                'video' => 'fa-video',
                                'article' => 'fa-file-alt'
                            ];
                            $typeColor = $typeColors[$item['type']] ?? 'secondary';
                            $typeIcon = $typeIcons[$item['type']] ?? 'fa-info-circle';
                            ?>
                            <span class="badge badge-<?php echo $typeColor; ?>">
                                <i class="fas <?php echo $typeIcon; ?>"></i>
                                <?php echo ucfirst($item['type']); ?>
                            </span>
                            <span class="badge badge-outline-primary">
                                <?php echo htmlspecialchars($item['category']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Card Title -->
                    <h3 class="help-item-title">
                        <?php echo htmlspecialchars($item['title']); ?>
                    </h3>

                    <!-- Video Embed (if video type) -->
                    <?php if ($item['type'] === 'video' && !empty($item['video_url'])): ?>
                        <?php
                        $videoInfo = $helpModel->getVideoEmbedInfo($item['video_url']);
                        if ($videoInfo['embed_url']):
                        ?>
                            <div class="video-wrapper">
                                <?php if ($videoInfo['platform'] === 'direct'): ?>
                                    <video controls>
                                        <source src="<?php echo htmlspecialchars($videoInfo['embed_url']); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php else: ?>
                                    <iframe src="<?php echo htmlspecialchars($videoInfo['embed_url']); ?>"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen>
                                    </iframe>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Card Description -->
                    <div class="help-item-description">
                        <?php
                        $description = htmlspecialchars($item['description']);
                        $maxLength = 200; // Maximum characters to show

                        if (strlen($description) > $maxLength) {
                            $truncated = substr($description, 0, $maxLength);
                            $truncated = substr($truncated, 0, strrpos($truncated, ' ')); // Cut at last word
                            echo nl2br($truncated) . '...';
                        } else {
                            echo nl2br($description);
                        }
                        ?>
                    </div>

                    <!-- Card Footer -->
                    <div class="help-item-footer">
                        <div class="help-item-footer-left">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i>
                                Added on <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                            </small>
                        </div>
                        <?php if (strlen($item['description']) > $maxLength): ?>
                            <div class="help-item-footer-right">
                                <a href="view.php?id=<?php echo UrlEncryption::encryptId($item['id']); ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-book-open"></i>
                                    Read More
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .filters-card {
        margin-bottom: var(--spacing-5);
    }

    .filter-form {
        margin: 0;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: var(--spacing-4);
        align-items: end;
    }

    .filter-actions {
        margin-bottom: 0;
    }

    .help-items-grid {
        column-count: 3;
        column-gap: var(--spacing-4);
    }

    .help-item-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        transition: var(--transition-normal);
        display: flex;
        flex-direction: column;
        break-inside: avoid;
        margin-bottom: var(--spacing-4);
    }

    .help-item-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .help-item-header {
        padding: var(--spacing-5);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: var(--spacing-3);
    }

    .help-item-icon {
        width: 48px;
        height: 48px;
        background: var(--primary);
        color: white;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .tip-card .help-item-icon {
        background: var(--info);
    }

    .video-card .help-item-icon {
        background: var(--danger);
    }

    .article-card .help-item-icon {
        background: var(--success);
    }

    .help-item-meta {
        display: flex;
        gap: var(--spacing-2);
        flex-wrap: wrap;
    }

    .help-item-title {
        padding: var(--spacing-4) var(--spacing-5);
        margin: 0;
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        line-height: 1.4;
    }

    .video-wrapper {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        height: 0;
        overflow: hidden;
        margin: 0 var(--spacing-5) var(--spacing-4);
        border-radius: var(--border-radius);
        background: var(--bg-subtle);
    }

    .video-wrapper iframe,
    .video-wrapper video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: var(--border-radius);
    }

    .help-item-description {
        padding: var(--spacing-4) var(--spacing-5);
        color: var(--text-secondary);
        line-height: 1.6;
        flex: 1;
    }

    .help-item-footer {
        padding: var(--spacing-4) var(--spacing-5);
        border-top: 1px solid var(--border-color);
        background: var(--bg-subtle);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: var(--spacing-3);
    }

    .help-item-footer-left {
        flex: 1;
    }

    .help-item-footer-right {
        flex-shrink: 0;
    }

    .empty-state {
        text-align: center;
        padding: var(--spacing-8) var(--spacing-4);
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--text-muted);
        margin-bottom: var(--spacing-4);
    }

    .empty-state h3 {
        color: var(--text-primary);
        margin-bottom: var(--spacing-2);
    }

    .empty-state p {
        color: var(--text-secondary);
        margin-bottom: var(--spacing-4);
    }

    @media (max-width: 1200px) {
        .help-items-grid {
            column-count: 2;
        }
    }

    @media (max-width: 768px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }

        .help-items-grid {
            column-count: 1;
        }
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>

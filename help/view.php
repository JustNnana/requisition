<?php
/**
 * GateWey Requisition Management System
 * Help & Support Detail View
 *
 * File: help/view.php
 * Purpose: Display full details of a help item
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

// Get help item ID (decrypt from URL)
if (!isset($_GET['id']) || empty($_GET['id'])) {
    Session::setFlash('error', 'Invalid help item.');
    header('Location: index.php');
    exit;
}

$id = UrlEncryption::decryptId($_GET['id']);

// Check if decryption was successful
if ($id === false) {
    Session::setFlash('error', 'Invalid help item ID.');
    header('Location: index.php');
    exit;
}

$item = $helpModel->getById($id);

// Check if item exists and is active
if (!$item) {
    Session::setFlash('error', 'Help item not found.');
    header('Location: index.php');
    exit;
}

if (!$item['is_active']) {
    Session::setFlash('error', 'This help item is not available.');
    header('Location: index.php');
    exit;
}

$pageTitle = $item['title'];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-wrapper">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <div class="page-icon">
                <i class="fas <?php echo htmlspecialchars($item['icon']); ?>"></i>
            </div>
            <div class="page-header-text">
                <h1><?php echo htmlspecialchars($item['title']); ?></h1>
                <p>
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
                </p>
            </div>
        </div>
        <div class="page-actions">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Help
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
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

            <!-- Full Content -->
            <div class="help-content">
                <?php echo nl2br(htmlspecialchars($item['description'])); ?>
            </div>

            <!-- Footer Info -->
            <div class="help-meta">
                <small class="text-muted">
                    <i class="fas fa-clock"></i>
                    Added on <?php echo date('F d, Y', strtotime($item['created_at'])); ?>
                </small>
            </div>
        </div>
    </div>
</div>

<style>
    .video-wrapper {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        height: 0;
        overflow: hidden;
        margin-bottom: var(--spacing-6);
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

    .help-content {
        font-size: var(--font-size-base);
        line-height: 1.8;
        color: var(--text-primary);
        margin-bottom: var(--spacing-6);
    }

    .help-meta {
        padding-top: var(--spacing-4);
        border-top: 1px solid var(--border-color);
    }

    .page-header p {
        display: flex;
        gap: var(--spacing-2);
        align-items: center;
        flex-wrap: wrap;
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>

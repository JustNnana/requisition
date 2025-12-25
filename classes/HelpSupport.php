<?php
/**
 * GateWey Requisition Management System
 * Help and Support Model Class
 *
 * File: classes/HelpSupport.php
 * Purpose: Handle help articles, tips, and video tutorials
 */

class HelpSupport
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all help items with optional filtering
     *
     * @param array $filters Optional filters (type, category, is_active)
     * @return array Array of help items
     */
    public function getAll($filters = [])
    {
        $sql = "SELECT * FROM help_support WHERE 1=1";
        $params = [];

        if (isset($filters['type']) && !empty($filters['type'])) {
            $sql .= " AND type = ?";
            $params[] = $filters['type'];
        }

        if (isset($filters['category']) && !empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }

        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = ?";
            $params[] = $filters['is_active'];
        }

        $sql .= " ORDER BY display_order ASC, created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get single help item by ID
     *
     * @param int $id Help item ID
     * @return array|null Help item data or null
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM help_support WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Get all unique categories
     *
     * @return array Array of categories
     */
    public function getCategories()
    {
        $sql = "SELECT DISTINCT category FROM help_support WHERE is_active = 1 ORDER BY category ASC";
        $results = $this->db->fetchAll($sql, []);

        $categories = [];
        foreach ($results as $row) {
            $categories[] = $row['category'];
        }

        return $categories;
    }

    /**
     * Create new help item
     *
     * @param array $data Help item data
     * @return array Result with success status and ID
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO help_support (
                title, description, type, video_url, category, icon,
                display_order, is_active, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $data['title'],
                $data['description'],
                $data['type'],
                $data['video_url'] ?? null,
                $data['category'],
                $data['icon'] ?? 'fa-info-circle',
                $data['display_order'] ?? 0,
                $data['is_active'] ?? 1,
                Session::getUserId()
            ];

            $this->db->execute($sql, $params);
            $id = $this->db->getConnection()->lastInsertId();

            // Log action
            if (ENABLE_AUDIT_LOG) {
                $auditLog = new AuditLog();
                $auditLog->log(
                    Session::getUserId(),
                    'help_created',
                    "Created help item: {$data['title']}"
                );
            }

            return ['success' => true, 'id' => $id, 'message' => 'Help item created successfully.'];
        } catch (Exception $e) {
            error_log("Help creation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create help item.'];
        }
    }

    /**
     * Update help item
     *
     * @param int $id Help item ID
     * @param array $data Updated data
     * @return array Result with success status
     */
    public function update($id, $data)
    {
        try {
            $sql = "UPDATE help_support SET
                title = ?,
                description = ?,
                type = ?,
                video_url = ?,
                category = ?,
                icon = ?,
                display_order = ?,
                is_active = ?
                WHERE id = ?";

            $params = [
                $data['title'],
                $data['description'],
                $data['type'],
                $data['video_url'] ?? null,
                $data['category'],
                $data['icon'] ?? 'fa-info-circle',
                $data['display_order'] ?? 0,
                $data['is_active'] ?? 1,
                $id
            ];

            $this->db->execute($sql, $params);

            // Log action
            if (ENABLE_AUDIT_LOG) {
                $auditLog = new AuditLog();
                $auditLog->log(
                    Session::getUserId(),
                    'help_updated',
                    "Updated help item ID: {$id}"
                );
            }

            return ['success' => true, 'message' => 'Help item updated successfully.'];
        } catch (Exception $e) {
            error_log("Help update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update help item.'];
        }
    }

    /**
     * Delete help item
     *
     * @param int $id Help item ID
     * @return array Result with success status
     */
    public function delete($id)
    {
        try {
            $item = $this->getById($id);
            if (!$item) {
                return ['success' => false, 'message' => 'Help item not found.'];
            }

            // Use direct execution to avoid prepared statement cache issues
            $conn = $this->db->getConnection();
            $escapedId = (int)$id;
            $conn->exec("DELETE FROM help_support WHERE id = {$escapedId}");

            // Log action
            if (ENABLE_AUDIT_LOG) {
                $auditLog = new AuditLog();
                $auditLog->log(
                    Session::getUserId(),
                    'help_deleted',
                    "Deleted help item: {$item['title']}"
                );
            }

            return ['success' => true, 'message' => 'Help item deleted successfully.'];
        } catch (Exception $e) {
            error_log("Help deletion error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete help item.'];
        }
    }

    /**
     * Extract YouTube video ID from URL
     *
     * @param string $url YouTube URL
     * @return string|null Video ID or null
     */
    public function extractYouTubeId($url)
    {
        if (empty($url)) {
            return null;
        }

        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get YouTube embed URL from video ID
     *
     * @param string $videoId YouTube video ID
     * @return string Embed URL
     */
    public function getYouTubeEmbedUrl($videoId)
    {
        return "https://www.youtube.com/embed/{$videoId}";
    }

    /**
     * Detect video platform and get embed URL
     *
     * @param string $url Video URL (YouTube, Google Drive, OneDrive, etc.)
     * @return array Array with 'platform' and 'embed_url'
     */
    public function getVideoEmbedInfo($url)
    {
        if (empty($url)) {
            return ['platform' => 'unknown', 'embed_url' => null];
        }

        // YouTube
        if (preg_match('/(?:youtube\.com|youtu\.be)/i', $url)) {
            $videoId = $this->extractYouTubeId($url);
            if ($videoId) {
                return [
                    'platform' => 'youtube',
                    'embed_url' => "https://www.youtube.com/embed/{$videoId}"
                ];
            }
        }

        // Google Drive
        if (preg_match('/drive\.google\.com\/file\/d\/([^\/]+)/i', $url, $matches)) {
            $fileId = $matches[1];
            return [
                'platform' => 'google_drive',
                'embed_url' => "https://drive.google.com/file/d/{$fileId}/preview"
            ];
        }

        // Google Drive (alternative format)
        if (preg_match('/drive\.google\.com\/open\?id=([^&]+)/i', $url, $matches)) {
            $fileId = $matches[1];
            return [
                'platform' => 'google_drive',
                'embed_url' => "https://drive.google.com/file/d/{$fileId}/preview"
            ];
        }

        // OneDrive (embed link format)
        if (preg_match('/1drv\.ms|onedrive\.live\.com/i', $url)) {
            // OneDrive requires embed parameter in URL
            if (strpos($url, 'embed') !== false) {
                return [
                    'platform' => 'onedrive',
                    'embed_url' => $url
                ];
            } else {
                // Try to convert to embed URL
                $embedUrl = str_replace('view.aspx', 'embed.aspx', $url);
                return [
                    'platform' => 'onedrive',
                    'embed_url' => $embedUrl
                ];
            }
        }

        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/i', $url, $matches)) {
            $videoId = $matches[1];
            return [
                'platform' => 'vimeo',
                'embed_url' => "https://player.vimeo.com/video/{$videoId}"
            ];
        }

        // Direct video file (mp4, webm, etc.)
        if (preg_match('/\.(mp4|webm|ogg)(\?|$)/i', $url)) {
            return [
                'platform' => 'direct',
                'embed_url' => $url
            ];
        }

        // Unknown platform - return original URL
        return [
            'platform' => 'unknown',
            'embed_url' => $url
        ];
    }
}

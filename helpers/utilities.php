<?php
/**
 * GateWey Requisition Management System
 * Additional Helper Functions
 * 
 * File: helpers/utilities.php
 * Purpose: Additional utility functions for the application
 */

if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

/**
 * Format file size in human-readable format
 * 
 * @param int $bytes File size in bytes
 * @param int $precision Decimal precision
 * @return string Formatted file size
 */
function format_file_size($bytes, $precision = 2) {
    if ($bytes <= 0) {
        return '0 Bytes';
    }
    
    $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Get file extension from filename
 * 
 * @param string $filename Filename
 * @return string File extension
 */
function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file extension is allowed
 * 
 * @param string $filename Filename
 * @return bool True if allowed
 */
function is_allowed_file_extension($filename) {
    $extension = get_file_extension($filename);
    return in_array($extension, ALLOWED_FILE_EXTENSIONS);
}

/**
 * Generate unique filename
 * 
 * @param string $originalFilename Original filename
 * @return string Unique filename
 */
function generate_unique_filename($originalFilename) {
    $extension = get_file_extension($originalFilename);
    return uniqid() . '_' . time() . '.' . $extension;
}

/**
 * Sanitize filename
 * 
 * @param string $filename Filename
 * @return string Sanitized filename
 */
function sanitize_filename($filename) {
    // Remove any path information
    $filename = basename($filename);
    
    // Replace spaces with underscores
    $filename = str_replace(' ', '_', $filename);
    
    // Remove special characters except dots, underscores, and hyphens
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    
    return $filename;
}

/**
 * Get MIME type from filename
 * 
 * @param string $filename Filename
 * @return string MIME type
 */
function get_mime_type_from_filename($filename) {
    $extension = get_file_extension($filename);
    
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];
    
    return $mimeTypes[$extension] ?? 'application/octet-stream';
}

/**
 * Create directory if it doesn't exist
 * 
 * @param string $directory Directory path
 * @param int $permissions Directory permissions
 * @return bool Success status
 */
function ensure_directory_exists($directory, $permissions = 0755) {
    if (file_exists($directory)) {
        return is_dir($directory);
    }
    
    return mkdir($directory, $permissions, true);
}

/**
 * Get client IP address
 * 
 * @return string IP address
 */
function get_client_ip() {
    $ipKeys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER)) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
}

/**
 * Get user agent
 * 
 * @return string User agent
 */
function get_user_agent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

/**
 * Truncate text with ellipsis
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to add (default: '...')
 * @return string Truncated text
 */
function truncate_text($text, $length = 50, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length - strlen($suffix)) . $suffix;
}

/**
 * Generate random string
 * 
 * @param int $length Length of string
 * @return string Random string
 */
function generate_random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Check if request is AJAX
 * 
 * @return bool True if AJAX request
 */
function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * JSON response helper
 * 
 * @param array $data Response data
 * @param int $statusCode HTTP status code
 */
function json_response($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Redirect helper
 * 
 * @param string $url URL to redirect to
 * @param int $statusCode HTTP status code
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Get pagination data
 * 
 * @param int $totalRecords Total number of records
 * @param int $currentPage Current page number
 * @param int $perPage Records per page
 * @return array Pagination data
 */
function get_pagination_data($totalRecords, $currentPage = 1, $perPage = 15) {
    $totalPages = ceil($totalRecords / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'per_page' => $perPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Build query string from array
 * 
 * @param array $params Parameters array
 * @return string Query string
 */
function build_query_string($params) {
    $filtered = array_filter($params, function($value) {
        return $value !== null && $value !== '';
    });
    
    return !empty($filtered) ? '?' . http_build_query($filtered) : '';
}

/**
 * Parse name into first and last name
 * 
 * @param string $fullName Full name
 * @return array ['first_name' => '...', 'last_name' => '...']
 */
function parse_full_name($fullName) {
    $parts = explode(' ', trim($fullName), 2);
    
    return [
        'first_name' => $parts[0] ?? '',
        'last_name' => $parts[1] ?? ''
    ];
}

/**
 * Format phone number
 * 
 * @param string $phone Phone number
 * @return string Formatted phone number
 */
function format_phone_number($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Format based on length
    if (strlen($phone) == 11) {
        return substr($phone, 0, 4) . ' ' . substr($phone, 4, 3) . ' ' . substr($phone, 7);
    }
    
    return $phone;
}

/**
 * Check if date is valid
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return bool True if valid
 */
function is_valid_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Get days difference between two dates
 * 
 * @param string $date1 First date
 * @param string $date2 Second date (default: today)
 * @return int Number of days
 */
function get_days_difference($date1, $date2 = null) {
    $d1 = new DateTime($date1);
    $d2 = $date2 ? new DateTime($date2) : new DateTime();
    
    $diff = $d1->diff($d2);
    return $diff->days;
}

/**
 * Get time ago in human-readable format
 * 
 * @param string $datetime Datetime string
 * @return string Time ago (e.g., "2 hours ago")
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    $periods = [
        'year' => 31536000,
        'month' => 2592000,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1
    ];
    
    foreach ($periods as $key => $value) {
        $count = floor($difference / $value);
        
        if ($count > 0) {
            $plural = ($count > 1) ? 's' : '';
            return $count . ' ' . $key . $plural . ' ago';
        }
    }
    
    return 'just now';
}
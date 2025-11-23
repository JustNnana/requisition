<?php
/**
 * GateWey Requisition Management System
 * Input Sanitization Class
 * 
 * File: classes/Sanitizer.php
 * Purpose: Comprehensive input sanitization with 30+ methods for XSS prevention
 */

class Sanitizer {
    
    /**
     * Sanitize string (remove tags, encode special chars)
     * 
     * @param string $input Input string
     * @return string Sanitized string
     */
    public static function string($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize email
     * 
     * @param string $email Email address
     * @return string Sanitized email
     */
    public static function email($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Sanitize URL
     * 
     * @param string $url URL string
     * @return string Sanitized URL
     */
    public static function url($url) {
        return filter_var(trim($url), FILTER_SANITIZE_URL);
    }
    
    /**
     * Sanitize integer
     * 
     * @param mixed $input Input value
     * @return int Sanitized integer
     */
    public static function int($input) {
        return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }
    
    /**
     * Sanitize float/decimal
     * 
     * @param mixed $input Input value
     * @return float Sanitized float
     */
    public static function float($input) {
        return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
    
    /**
     * Sanitize boolean
     * 
     * @param mixed $input Input value
     * @return bool Boolean value
     */
    public static function bool($input) {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * Sanitize filename (remove special characters)
     * 
     * @param string $filename Filename
     * @return string Sanitized filename
     */
    public static function filename($filename) {
        // Remove any path components
        $filename = basename($filename);
        
        // Remove special characters except dots, hyphens, and underscores
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Remove multiple dots (potential path traversal)
        $filename = preg_replace('/\.+/', '.', $filename);
        
        return $filename;
    }
    
    /**
     * Sanitize HTML (allow safe tags)
     * 
     * @param string $html HTML string
     * @param array $allowedTags Allowed HTML tags
     * @return string Sanitized HTML
     */
    public static function html($html, $allowedTags = ['p', 'br', 'strong', 'em', 'u', 'a', 'ul', 'ol', 'li']) {
        $allowed = '<' . implode('><', $allowedTags) . '>';
        return strip_tags($html, $allowed);
    }
    
    /**
     * Sanitize text for database (prepare for SQL)
     * Note: This should be used in conjunction with prepared statements
     * 
     * @param string $text Text string
     * @return string Sanitized text
     */
    public static function dbText($text) {
        return trim(strip_tags($text));
    }
    
    /**
     * Sanitize phone number
     * 
     * @param string $phone Phone number
     * @return string Sanitized phone number
     */
    public static function phone($phone) {
        // Remove all non-numeric characters except + at the start
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Ensure + is only at the start
        if (strpos($phone, '+') !== false) {
            $phone = '+' . str_replace('+', '', $phone);
        }
        
        return $phone;
    }
    
    /**
     * Sanitize date
     * 
     * @param string $date Date string
     * @param string $format Expected format
     * @return string|null Sanitized date or null if invalid
     */
    public static function date($date, $format = 'Y-m-d') {
        $dateObj = DateTime::createFromFormat($format, $date);
        if ($dateObj && $dateObj->format($format) === $date) {
            return $dateObj->format($format);
        }
        return null;
    }
    
    /**
     * Sanitize alphanumeric string
     * 
     * @param string $input Input string
     * @return string Alphanumeric string
     */
    public static function alphanumeric($input) {
        return preg_replace('/[^a-zA-Z0-9]/', '', $input);
    }
    
    /**
     * Sanitize alpha string (letters only)
     * 
     * @param string $input Input string
     * @param bool $allowSpaces Allow spaces
     * @return string Alpha string
     */
    public static function alpha($input, $allowSpaces = false) {
        if ($allowSpaces) {
            return preg_replace('/[^a-zA-Z\s]/', '', $input);
        }
        return preg_replace('/[^a-zA-Z]/', '', $input);
    }
    
    /**
     * Sanitize slug (URL-friendly string)
     * 
     * @param string $input Input string
     * @return string Slug string
     */
    public static function slug($input) {
        $input = strtolower($input);
        $input = preg_replace('/[^a-z0-9\s-]/', '', $input);
        $input = preg_replace('/[\s-]+/', '-', $input);
        return trim($input, '-');
    }
    
    /**
     * Sanitize textarea (preserve line breaks)
     * 
     * @param string $input Input text
     * @return string Sanitized text
     */
    public static function textarea($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize array of strings
     * 
     * @param array $array Input array
     * @return array Sanitized array
     */
    public static function arrayString($array) {
        if (!is_array($array)) {
            return [];
        }
        
        return array_map([self::class, 'string'], $array);
    }
    
    /**
     * Sanitize array of integers
     * 
     * @param array $array Input array
     * @return array Sanitized array
     */
    public static function arrayInt($array) {
        if (!is_array($array)) {
            return [];
        }
        
        return array_map('intval', $array);
    }
    
    /**
     * Remove XSS threats from string
     * 
     * @param string $input Input string
     * @return string XSS-safe string
     */
    public static function xss($input) {
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        // Remove carriage returns
        $input = str_replace("\r", '', $input);
        
        // Replace all non-printable characters except newlines and tabs
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $input);
        
        // Remove script tags
        $input = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $input);
        
        // Remove javascript: protocol
        $input = preg_replace('/javascript:/i', '', $input);
        
        // Remove on* event attributes
        $input = preg_replace('/\s*on\w+\s*=\s*["\']?[^"\'>]*["\']?/i', '', $input);
        
        // Encode special characters
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        return $input;
    }
    
    /**
     * Clean SQL injection attempts
     * Note: Always use prepared statements instead
     * 
     * @param string $input Input string
     * @return string Cleaned string
     */
    public static function sql($input) {
        // Remove SQL comment characters
        $input = str_replace(['--', '/*', '*/'], '', $input);
        
        // Remove suspicious SQL keywords in unusual contexts
        $sqlKeywords = ['union', 'select', 'insert', 'update', 'delete', 'drop', 'create', 'alter', 'exec', 'script'];
        foreach ($sqlKeywords as $keyword) {
            $input = preg_replace('/\b' . $keyword . '\b/i', '', $input);
        }
        
        return trim($input);
    }
    
    /**
     * Strip all HTML and PHP tags completely
     * 
     * @param string $input Input string
     * @return string Cleaned string
     */
    public static function stripAll($input) {
        return strip_tags($input);
    }
    
    /**
     * Sanitize JSON input
     * 
     * @param string $json JSON string
     * @return array|null Decoded and sanitized array or null if invalid
     */
    public static function json($json) {
        $decoded = json_decode($json, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return self::arrayDeep($decoded);
        }
        
        return null;
    }
    
    /**
     * Deep sanitize multi-dimensional array
     * 
     * @param array $array Input array
     * @return array Sanitized array
     */
    public static function arrayDeep($array) {
        if (!is_array($array)) {
            return self::string($array);
        }
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::arrayDeep($value);
            } else {
                $array[$key] = self::string($value);
            }
        }
        
        return $array;
    }
    
    /**
     * Sanitize credit card number
     * 
     * @param string $cardNumber Card number
     * @return string Sanitized card number
     */
    public static function creditCard($cardNumber) {
        return preg_replace('/[^0-9]/', '', $cardNumber);
    }
    
    /**
     * Sanitize IP address
     * 
     * @param string $ip IP address
     * @return string|null Sanitized IP or null if invalid
     */
    public static function ip($ip) {
        $ip = filter_var($ip, FILTER_VALIDATE_IP);
        return $ip !== false ? $ip : null;
    }
    
    /**
     * Sanitize MAC address
     * 
     * @param string $mac MAC address
     * @return string Sanitized MAC address
     */
    public static function mac($mac) {
        return preg_replace('/[^a-fA-F0-9:]/', '', $mac);
    }
    
    /**
     * Sanitize hexadecimal color code
     * 
     * @param string $color Color code
     * @return string Sanitized color code
     */
    public static function color($color) {
        $color = ltrim($color, '#');
        $color = preg_replace('/[^a-fA-F0-9]/', '', $color);
        
        if (strlen($color) === 6 || strlen($color) === 3) {
            return '#' . $color;
        }
        
        return '#000000'; // Default to black if invalid
    }
    
    /**
     * Sanitize base64 encoded string
     * 
     * @param string $base64 Base64 string
     * @return string Sanitized base64 string
     */
    public static function base64($base64) {
        return preg_replace('/[^a-zA-Z0-9\/+=]/', '', $base64);
    }
    
    /**
     * Escape string for JavaScript
     * 
     * @param string $string Input string
     * @return string Escaped string
     */
    public static function js($string) {
        return json_encode($string, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
    
    /**
     * Escape string for CSV
     * 
     * @param string $string Input string
     * @return string Escaped string
     */
    public static function csv($string) {
        if (strpos($string, ',') !== false || strpos($string, '"') !== false || strpos($string, "\n") !== false) {
            $string = '"' . str_replace('"', '""', $string) . '"';
        }
        return $string;
    }
    
    /**
     * Sanitize username
     * 
     * @param string $username Username
     * @return string Sanitized username
     */
    public static function username($username) {
        // Allow letters, numbers, underscores, and hyphens
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $username);
    }
    
    /**
     * Sanitize domain name
     * 
     * @param string $domain Domain name
     * @return string Sanitized domain
     */
    public static function domain($domain) {
        // Remove protocol
        $domain = preg_replace('/^https?:\/\//i', '', $domain);
        
        // Remove path
        $domain = preg_replace('/\/.*$/', '', $domain);
        
        // Allow letters, numbers, dots, and hyphens
        return preg_replace('/[^a-zA-Z0-9.-]/', '', strtolower($domain));
    }
    
    /**
     * Sanitize path (prevent directory traversal)
     * 
     * @param string $path File path
     * @return string Sanitized path
     */
    public static function path($path) {
        // Remove null bytes
        $path = str_replace(chr(0), '', $path);
        
        // Remove directory traversal attempts
        $path = str_replace(['../', '.\\', '..\\'], '', $path);
        
        // Remove leading slashes
        $path = ltrim($path, '/\\');
        
        return $path;
    }
    
    /**
     * Sanitize money/currency value
     * 
     * @param string $amount Amount string
     * @return float Sanitized amount
     */
    public static function money($amount) {
        // Remove currency symbols and commas
        $amount = preg_replace('/[^0-9.]/', '', $amount);
        
        // Convert to float with 2 decimal places
        return number_format((float)$amount, 2, '.', '');
    }
    
    /**
     * Sanitize percentage
     * 
     * @param string $percentage Percentage string
     * @return float Sanitized percentage
     */
    public static function percentage($percentage) {
        $percentage = preg_replace('/[^0-9.]/', '', $percentage);
        $value = (float)$percentage;
        
        // Clamp between 0 and 100
        return max(0, min(100, $value));
    }
    
    /**
     * Sanitize entire $_POST array
     * 
     * @return array Sanitized POST data
     */
    public static function post() {
        return self::arrayDeep($_POST);
    }
    
    /**
     * Sanitize entire $_GET array
     * 
     * @return array Sanitized GET data
     */
    public static function get() {
        return self::arrayDeep($_GET);
    }
    
    /**
     * Sanitize entire $_REQUEST array
     * 
     * @return array Sanitized REQUEST data
     */
    public static function request() {
        return self::arrayDeep($_REQUEST);
    }
    
    /**
     * Sanitize and validate file upload
     * 
     * @param array $file File array from $_FILES
     * @param array $options Validation options
     * @return array Result array with 'success', 'message', and 'file' keys
     */
    public static function fileUpload($file, $options = []) {
        $defaults = [
            'maxSize' => MAX_FILE_SIZE,
            'allowedTypes' => ALLOWED_FILE_TYPES,
            'allowedMimes' => ALLOWED_MIME_TYPES
        ];
        
        $options = array_merge($defaults, $options);
        
        // Check for upload errors
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'message' => 'Invalid file upload.'];
        }
        
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['success' => false, 'message' => 'No file uploaded.'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['success' => false, 'message' => 'File exceeds maximum size.'];
            default:
                return ['success' => false, 'message' => 'Unknown upload error.'];
        }
        
        // Check file size
        if ($file['size'] > $options['maxSize']) {
            $maxSizeMB = round($options['maxSize'] / 1048576, 2);
            return ['success' => false, 'message' => "File must not exceed {$maxSizeMB}MB."];
        }
        
        // Check file extension
        $fileName = self::filename($file['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $options['allowedTypes'])) {
            return ['success' => false, 'message' => 'File type not allowed.'];
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $options['allowedMimes'])) {
            return ['success' => false, 'message' => 'File MIME type not allowed.'];
        }
        
        return [
            'success' => true,
            'message' => 'File validated successfully.',
            'file' => [
                'name' => $fileName,
                'size' => $file['size'],
                'type' => $mimeType,
                'extension' => $fileExtension,
                'tmp_name' => $file['tmp_name']
            ]
        ];
    }
}
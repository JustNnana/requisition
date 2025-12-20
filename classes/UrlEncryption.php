<?php

class UrlEncryption {
    private static $cipher = 'AES-256-CBC';
    private static $key;

    /**
     * Initialize encryption key from config
     */
    private static function init() {
        if (self::$key === null) {
            self::$key = hash('sha256', ENCRYPTION_KEY, true);
        }
    }

    /**
     * Encrypt an ID for use in URLs
     *
     * @param int|string $id The ID to encrypt
     * @return string URL-safe encrypted string
     */
    public static function encryptId($id) {
        self::init();

        // Generate initialization vector
        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);

        // Encrypt the ID
        $encrypted = openssl_encrypt(
            (string)$id,
            self::$cipher,
            self::$key,
            OPENSSL_RAW_DATA,
            $iv
        );

        // Combine IV and encrypted data
        $combined = $iv . $encrypted;

        // Make it URL-safe
        return rtrim(strtr(base64_encode($combined), '+/', '-_'), '=');
    }

    /**
     * Decrypt an ID from URL
     *
     * @param string $encryptedId The encrypted ID from URL
     * @return int|false The original ID or false on failure
     */
    public static function decryptId($encryptedId) {
        self::init();

        try {
            // Convert from URL-safe base64
            $decoded = base64_decode(strtr($encryptedId, '-_', '+/'));

            if ($decoded === false) {
                return false;
            }

            // Extract IV and encrypted data
            $ivLength = openssl_cipher_iv_length(self::$cipher);
            $iv = substr($decoded, 0, $ivLength);
            $encrypted = substr($decoded, $ivLength);

            // Decrypt
            $decrypted = openssl_decrypt(
                $encrypted,
                self::$cipher,
                self::$key,
                OPENSSL_RAW_DATA,
                $iv
            );

            // Return as integer if numeric
            if ($decrypted !== false && is_numeric($decrypted)) {
                return (int)$decrypted;
            }

            return $decrypted;
        } catch (Exception $e) {
            error_log("URL Decryption Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Build a URL with encrypted ID parameter
     *
     * @param string $baseUrl The base URL
     * @param int|string $id The ID to encrypt
     * @param string $paramName The parameter name (default: 'id')
     * @param array $additionalParams Additional query parameters
     * @return string Complete URL with encrypted ID
     */
    public static function buildUrl($baseUrl, $id, $paramName = 'id', $additionalParams = []) {
        $encryptedId = self::encryptId($id);
        $params = array_merge([$paramName => $encryptedId], $additionalParams);

        $queryString = http_build_query($params);
        $separator = strpos($baseUrl, '?') !== false ? '&' : '?';

        return $baseUrl . $separator . $queryString;
    }

    /**
     * Get decrypted ID from current request
     *
     * @param string $paramName The parameter name (default: 'id')
     * @param string $method Request method (default: 'GET')
     * @return int|false The decrypted ID or false on failure
     */
    public static function getIdFromRequest($paramName = 'id', $method = 'GET') {
        $source = $method === 'POST' ? $_POST : $_GET;

        if (!isset($source[$paramName])) {
            return false;
        }

        return self::decryptId($source[$paramName]);
    }
}

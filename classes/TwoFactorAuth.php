<?php
/**
 * GateWey Requisition Management System
 * Two-Factor Authentication Class
 *
 * File: classes/TwoFactorAuth.php
 * Purpose: Handle TOTP-based two-factor authentication
 */

class TwoFactorAuth
{
    private $db;
    private $codeLength = 6;
    private $period = 30; // Time period in seconds

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate a random secret key for TOTP
     *
     * @return string Base32 encoded secret
     */
    public function generateSecret()
    {
        $secret = '';
        $validChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 alphabet

        // Generate 16 character secret (128 bits of entropy)
        for ($i = 0; $i < 16; $i++) {
            $secret .= $validChars[random_int(0, 31)];
        }

        return $secret;
    }

    /**
     * Generate QR Code data URI for authenticator apps
     *
     * @param string $secret The secret key
     * @param string $email User's email
     * @param string $issuer Application name
     * @return string QR code data URI or otpauth URL as fallback
     */
    public function getQRCodeUrl($secret, $email, $issuer = 'GateWey Requisition')
    {
        // otpauth URL format for TOTP
        $otpauthUrl = "otpauth://totp/{$issuer}:{$email}?secret={$secret}&issuer={$issuer}";

        try {
            // Check if QR code library is available
            if (class_exists('Endroid\QrCode\QrCode')) {
                $qrCode = new \Endroid\QrCode\QrCode($otpauthUrl);
                $writer = new \Endroid\QrCode\Writer\PngWriter();
                $result = $writer->write($qrCode);

                // Return as data URI
                return $result->getDataUri();
            }
        } catch (Exception $e) {
            error_log("QR Code generation error: " . $e->getMessage());
        }

        // Fallback: return otpauth URL (will fail to load as image, triggering error handler)
        return $otpauthUrl;
    }

    /**
     * Verify a TOTP code
     *
     * @param string $secret The secret key
     * @param string $code The code to verify
     * @param int $window Time window to check (default: 1 = ±30 seconds)
     * @return bool True if code is valid
     */
    public function verifyCode($secret, $code, $window = 1)
    {
        $currentTime = time();

        // Check current time slot and adjacent slots (for clock drift)
        for ($i = -$window; $i <= $window; $i++) {
            $testTime = $currentTime + ($i * $this->period);
            $testCode = $this->generateCode($secret, $testTime);

            if ($testCode === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a TOTP code for a given time
     *
     * @param string $secret The secret key
     * @param int|null $time Unix timestamp (null = current time)
     * @return string The generated code
     */
    public function generateCode($secret, $time = null)
    {
        if ($time === null) {
            $time = time();
        }

        // Calculate time counter
        $counter = floor($time / $this->period);

        // Decode base32 secret
        $secretKey = $this->base32Decode($secret);

        // Generate HMAC
        $hash = hash_hmac('sha1', $this->intToBytes($counter), $secretKey, true);

        // Dynamic truncation
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $truncatedHash = substr($hash, $offset, 4);

        // Convert to integer
        $value = unpack('N', $truncatedHash)[1];
        $value = $value & 0x7FFFFFFF;

        // Generate code
        $code = str_pad($value % pow(10, $this->codeLength), $this->codeLength, '0', STR_PAD_LEFT);

        return $code;
    }

    /**
     * Enable 2FA for a user
     *
     * @param int $userId User ID
     * @param string $secret The secret key
     * @return bool Success status
     */
    public function enable2FA($userId, $secret)
    {
        try {
            // Use direct PDO connection to bypass prepared statement cache issues
            $conn = $this->db->getConnection();

            // Manually escape values for security
            $escapedSecret = $conn->quote($secret);
            $escapedUserId = (int)$userId; // Cast to int for safety

            // Build direct SQL query
            $sql = "UPDATE users
                    SET twofa_secret = {$escapedSecret},
                        twofa_enabled = 1,
                        twofa_verified_at = NOW()
                    WHERE id = {$escapedUserId}";

            // Execute directly without prepared statement
            $affectedRows = $conn->exec($sql);

            // Check if update actually affected any rows
            if ($affectedRows === 0) {
                error_log("2FA Enable Error: No rows affected for user ID: " . $userId);
                return false;
            }

            // Log action
            if (ENABLE_AUDIT_LOG) {
                $auditLog = new AuditLog();
                $auditLog->log(
                    $userId,
                    'twofa_enabled',
                    'Two-factor authentication enabled'
                );
            }

            return true;
        } catch (Exception $e) {
            error_log("2FA Enable Error: " . $e->getMessage() . " | User ID: " . $userId);
            return false;
        }
    }

    /**
     * Disable/Reset 2FA for a user
     *
     * @param int $userId User ID
     * @param int|null $adminId Admin performing the reset
     * @return bool Success status
     */
    public function disable2FA($userId, $adminId = null)
    {
        try {
            // Use direct PDO connection to bypass prepared statement cache issues
            $conn = $this->db->getConnection();

            // Cast to int for safety
            $escapedUserId = (int)$userId;

            // Build direct SQL query
            $sql = "UPDATE users
                    SET twofa_secret = NULL,
                        twofa_enabled = 0,
                        twofa_verified_at = NULL
                    WHERE id = {$escapedUserId}";

            // Execute directly without prepared statement
            $conn->exec($sql);

            // Log action
            if (ENABLE_AUDIT_LOG) {
                $description = $adminId
                    ? "Two-factor authentication reset by admin (ID: {$adminId})"
                    : "Two-factor authentication disabled";

                $auditLog = new AuditLog();
                $auditLog->log(
                    $userId,
                    'twofa_disabled',
                    $description
                );
            }

            return true;
        } catch (Exception $e) {
            error_log("2FA Disable Error: " . $e->getMessage() . " | User ID: " . $userId);
            return false;
        }
    }

    /**
     * Check if user has 2FA enabled
     *
     * @param int $userId User ID
     * @return array User 2FA status
     */
    public function get2FAStatus($userId)
    {
        $sql = "SELECT twofa_secret, twofa_enabled, twofa_verified_at
                FROM users
                WHERE id = ?";

        $result = $this->db->fetchOne($sql, [$userId]);

        return [
            'has_secret' => !empty($result['twofa_secret']),
            'enabled' => (bool)$result['twofa_enabled'],
            'verified_at' => $result['twofa_verified_at'],
            'secret' => $result['twofa_secret'] ?? null
        ];
    }

    /**
     * Convert integer to bytes (big-endian)
     *
     * @param int $int Integer to convert
     * @return string Byte string
     */
    private function intToBytes($int)
    {
        $result = [];
        while ($int != 0) {
            $result[] = chr($int & 0xFF);
            $int >>= 8;
        }
        return str_pad(implode('', array_reverse($result)), 8, "\000", STR_PAD_LEFT);
    }

    /**
     * Decode Base32 string
     *
     * @param string $secret Base32 encoded string
     * @return string Decoded binary string
     */
    private function base32Decode($secret)
    {
        $secret = strtoupper($secret);
        $lut = [
            'A' => 0,  'B' => 1,  'C' => 2,  'D' => 3,  'E' => 4,  'F' => 5,  'G' => 6,  'H' => 7,
            'I' => 8,  'J' => 9,  'K' => 10, 'L' => 11, 'M' => 12, 'N' => 13, 'O' => 14, 'P' => 15,
            'Q' => 16, 'R' => 17, 'S' => 18, 'T' => 19, 'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23,
            'Y' => 24, 'Z' => 25, '2' => 26, '3' => 27, '4' => 28, '5' => 29, '6' => 30, '7' => 31
        ];

        $binary = '';

        for ($i = 0; $i < strlen($secret); $i++) {
            $binary .= str_pad(decbin($lut[$secret[$i]]), 5, '0', STR_PAD_LEFT);
        }

        $result = '';
        for ($i = 0; $i < strlen($binary); $i += 8) {
            $chunk = substr($binary, $i, 8);
            if (strlen($chunk) == 8) {
                $result .= chr(bindec($chunk));
            }
        }

        return $result;
    }
}

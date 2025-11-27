<?php
/**
 * GateWey Requisition Management System
 * Input Validation Class - SAFE VERSION WITH LOOP PROTECTION
 * 
 * File: classes/Validator.php
 * Purpose: Comprehensive input validation with 20+ validation rules
 */

class Validator {
    
    private $data = [];
    private $errors = [];
    private $rules = [];
    private $validationDepth = 0; // ADDED: Track recursion depth
    private const MAX_DEPTH = 100; // ADDED: Maximum recursion depth
    
    /**
     * Constructor
     * 
     * @param array $data Data to validate
     */
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    /**
     * Set data to validate
     * 
     * @param array $data Data array
     * @return self
     */
    public function setData($data) {
        $this->data = $data;
        return $this;
    }
    
    /**
     * Add validation rules
     * 
     * @param array $rules Validation rules
     * @return self
     */
    public function setRules($rules) {
        $this->rules = $rules;
        return $this;
    }
    
    /**
     * Validate data against rules
     * 
     * @return bool Validation result
     */
    public function validate() {
        $this->errors = [];
        $this->validationDepth = 0; // ADDED: Reset depth counter
        
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;
            
            foreach ($rules as $rule) {
                // ADDED: Safety check
                if ($this->validationDepth > self::MAX_DEPTH) {
                    error_log("VALIDATION LOOP DETECTED: Exceeded max depth for field: {$field}, rule: {$rule}");
                    $this->errors['_system'][] = "Validation loop detected. Please contact administrator.";
                    return false;
                }
                
                $this->applyRule($field, $value, $rule);
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Apply a single validation rule
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $rule Rule string
     */
    private function applyRule($field, $value, $rule) {
        // ADDED: Increment depth counter
        $this->validationDepth++;
        
        // ADDED: Safety check before processing
        if ($this->validationDepth > self::MAX_DEPTH) {
            error_log("VALIDATION LOOP: Depth exceeded at field: {$field}, rule: {$rule}");
            return;
        }
        
        // ADDED: Skip empty rules (for optional fields)
        if (trim($rule) === '') {
            $this->validationDepth--;
            return;
        }
        
        // Parse rule and parameters
        if (strpos($rule, ':') !== false) {
            list($ruleName, $params) = explode(':', $rule, 2);
            $params = explode(',', $params);
        } else {
            $ruleName = $rule;
            $params = [];
        }
        
        $methodName = 'validate' . str_replace('_', '', ucwords($ruleName, '_'));
        
        // ADDED: Log what we're about to call
        error_log("Validator calling: {$methodName} for field: {$field} (depth: {$this->validationDepth})");
        
        if (method_exists($this, $methodName)) {
            try {
                $result = call_user_func_array([$this, $methodName], array_merge([$field, $value], $params));
                
                if ($result !== true) {
                    $this->errors[$field][] = $result;
                }
            } catch (Exception $e) {
                error_log("Validation exception for {$field}: " . $e->getMessage());
                $this->errors[$field][] = "Validation error occurred.";
            }
        }
        
        // ADDED: Decrement depth counter
        $this->validationDepth--;
    }
    
    /**
     * Get validation errors
     * 
     * @return array Errors array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get errors for a specific field
     * 
     * @param string $field Field name
     * @return array Field errors
     */
    public function getError($field) {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * Get first error for a field
     * 
     * @param string $field Field name
     * @return string|null First error message
     */
    public function getFirstError($field) {
        return $this->errors[$field][0] ?? null;
    }
    
    /**
     * Check if field has errors
     * 
     * @param string $field Field name
     * @return bool
     */
    public function hasError($field) {
        return isset($this->errors[$field]);
    }
    
    /* ===== VALIDATION RULES ===== */
    
    /**
     * Rule: Required field
     */
    protected function validateRequired($field, $value) {
        if (is_null($value) || $value === '' || (is_array($value) && empty($value))) {
            return ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
        return true;
    }
    
    /**
     * Rule: Email validation
     */
    protected function validateEmail($field, $value) {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return ucfirst(str_replace('_', ' ', $field)) . ' must be a valid email address.';
        }
        return true;
    }
    
    /**
     * Rule: Minimum length
     */
    protected function validateMin($field, $value, $min) {
        if ($value && strlen($value) < $min) {
            return ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.";
        }
        return true;
    }
    
    /**
     * Rule: Maximum length
     */
    protected function validateMax($field, $value, $max) {
        if ($value && strlen($value) > $max) {
            return ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$max} characters.";
        }
        return true;
    }
    
    /**
     * Rule: Exact length
     */
    protected function validateLength($field, $value, $length) {
        if ($value && strlen($value) !== (int)$length) {
            return ucfirst(str_replace('_', ' ', $field)) . " must be exactly {$length} characters.";
        }
        return true;
    }
    
    /**
     * Rule: Numeric validation
     */
    protected function validateNumeric($field, $value) {
        if ($value && !is_numeric($value)) {
            return ucfirst(str_replace('_', ' ', $field)) . ' must be a number.';
        }
        return true;
    }
    
    /**
     * Rule: Integer validation
     */
    protected function validateInteger($field, $value) {
        if ($value && !filter_var($value, FILTER_VALIDATE_INT)) {
            return ucfirst(str_replace('_', ' ', $field)) . ' must be an integer.';
        }
        return true;
    }
    
    /**
     * Rule: Decimal validation
     */
    protected function validateDecimal($field, $value, $decimals = null) {
        if ($value && !is_numeric($value)) {
            return ucfirst(str_replace('_', ' ', $field)) . ' must be a decimal number.';
        }
        
        if ($decimals && $value) {
            $pattern = '/^\d+(\.\d{1,' . $decimals . '})?$/';
            if (!preg_match($pattern, $value)) {
                return ucfirst(str_replace('_', ' ', $field)) . " must have at most {$decimals} decimal places.";
            }
        }
        
        return true;
    }
    
    /**
     * Rule: Minimum value
     */
    protected function validateMinValue($field, $value, $min) {
        if ($value && is_numeric($value) && $value < $min) {
            return ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min}.";
        }
        return true;
    }
    
    /**
     * Rule: Maximum value
     */
    protected function validateMaxValue($field, $value, $max) {
        if ($value && is_numeric($value) && $value > $max) {
            return ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$max}.";
        }
        return true;
    }
    
    /**
     * Rule: Between values
     */
    protected function validateBetween($field, $value, $min, $max) {
        if ($value && is_numeric($value) && ($value < $min || $value > $max)) {
            return ucfirst(str_replace('_', ' ', $field)) . " must be between {$min} and {$max}.";
        }
        return true;
    }
    
    /**
     * Rule: Alpha characters only
     */
    protected function validateAlpha($field, $value) {
        if ($value && !ctype_alpha(str_replace(' ', '', $value))) {
            return ucfirst(str_replace('_', ' ', $field)) . ' must contain only letters.';
        }
        return true;
    }
    
    /**
     * Rule: Alphanumeric characters only
     */
    protected function validateAlphanumeric($field, $value) {
        if ($value && !ctype_alnum(str_replace(' ', '', $value))) {
            return ucfirst(str_replace('_', ' ', $field)) . ' must contain only letters and numbers.';
        }
        return true;
    }
    
    /**
     * Rule: Match another field
     */
    protected function validateMatch($field, $value, $matchField) {
        $matchValue = $this->data[$matchField] ?? null;
        if ($value !== $matchValue) {
            return ucfirst(str_replace('_', ' ', $field)) . ' must match ' . str_replace('_', ' ', $matchField) . '.';
        }
        return true;
    }
    
    /**
     * Rule: Different from another field
     */
    protected function validateDifferent($field, $value, $differentField) {
        $differentValue = $this->data[$differentField] ?? null;
        if ($value === $differentValue) {
            return ucfirst(str_replace('_', ' ', $field)) . ' must be different from ' . str_replace('_', ' ', $differentField) . '.';
        }
        return true;
    }
    
    /**
     * Rule: In array (enumeration)
     */
    protected function validateIn($field, $value, ...$options) {
        if ($value && !in_array($value, $options)) {
            return ucfirst(str_replace('_', ' ', $field)) . ' must be one of: ' . implode(', ', $options) . '.';
        }
        return true;
    }
    
    /**
     * Rule: Not in array
     */
    protected function validateNotIn($field, $value, ...$options) {
        if ($value && in_array($value, $options)) {
            return ucfirst(str_replace('_', ' ', $field)) . ' must not be one of: ' . implode(', ', $options) . '.';
        }
        return true;
    }
    
    /**
     * Rule: URL validation
     */
    protected function validateUrl($field, $value) {
        if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
            return ucfirst(str_replace('_', ' ', $field)) . ' must be a valid URL.';
        }
        return true;
    }
    
    /**
     * Rule: IP address validation
     */
    protected function validateIp($field, $value) {
        if ($value && !filter_var($value, FILTER_VALIDATE_IP)) {
            return ucfirst(str_replace('_', ' ', $field)) . ' must be a valid IP address.';
        }
        return true;
    }
    
    /**
     * Rule: Date validation
     */
    protected function validateDate($field, $value) {
        if ($value && !strtotime($value)) {
            return ucfirst(str_replace('_', ' ', $field)) . ' must be a valid date.';
        }
        return true;
    }
    
    /**
     * Rule: Date format validation
     */
    protected function validateDateFormat($field, $value, $format) {
        if ($value) {
            $date = DateTime::createFromFormat($format, $value);
            if (!$date || $date->format($format) !== $value) {
                return ucfirst(str_replace('_', ' ', $field)) . " must be in format {$format}.";
            }
        }
        return true;
    }
    
    /**
     * Rule: After date
     */
    protected function validateAfter($field, $value, $afterDate) {
        if ($value && strtotime($value) <= strtotime($afterDate)) {
            return ucfirst(str_replace('_', ' ', $field)) . " must be after {$afterDate}.";
        }
        return true;
    }
    
    /**
     * Rule: Before date
     */
    protected function validateBefore($field, $value, $beforeDate) {
        if ($value && strtotime($value) >= strtotime($beforeDate)) {
            return ucfirst(str_replace('_', ' ', $field)) . " must be before {$beforeDate}.";
        }
        return true;
    }
    
    /**
     * Rule: Regular expression pattern
     */
    protected function validateRegex($field, $value, $pattern) {
        if ($value && !preg_match($pattern, $value)) {
            return ucfirst(str_replace('_', ' ', $field)) . ' format is invalid.';
        }
        return true;
    }
    
    /**
     * Rule: File upload validation
     */
    protected function validateFile($field, $file) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return true; // No file uploaded, use 'required' rule separately if needed
        }
        
        if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            return 'File upload failed for ' . str_replace('_', ' ', $field) . '.';
        }
        
        return true;
    }
    
    /**
     * Rule: File size validation
     */
    protected function validateFileSize($field, $file, $maxSize) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        
        if ($_FILES[$field]['size'] > $maxSize) {
            $maxSizeMB = round($maxSize / 1048576, 2);
            return ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$maxSizeMB}MB.";
        }
        
        return true;
    }
    
    /**
     * Rule: File extension validation
     */
    protected function validateFileExtension($field, $file, ...$extensions) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        
        $fileName = $_FILES[$field]['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $extensions)) {
            return ucfirst(str_replace('_', ' ', $field)) . ' must be one of: ' . implode(', ', $extensions) . '.';
        }
        
        return true;
    }
    
    /**
     * Rule: File MIME type validation
     */
    protected function validateFileMime($field, $file, ...$mimeTypes) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        
        $fileMime = mime_content_type($_FILES[$field]['tmp_name']);
        
        if (!in_array($fileMime, $mimeTypes)) {
            return ucfirst(str_replace('_', ' ', $field)) . ' file type is not allowed.';
        }
        
        return true;
    }
    
    /**
     * Rule: Phone number validation
     */
    protected function validatePhone($field, $value) {
        if ($value) {
            // Simple phone validation (digits, spaces, dashes, parentheses, plus sign)
            if (!preg_match('/^[\d\s\-\(\)\+]+$/', $value)) {
                return ucfirst(str_replace('_', ' ', $field)) . ' must be a valid phone number.';
            }
        }
        return true;
    }
    
    /**
     * Rule: Strong password validation
     */
    protected function validateStrongPassword($field, $value) {
        if ($value) {
            $errors = [];
            
            $minLength = defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8;
            $requireUpper = defined('PASSWORD_REQUIRE_UPPERCASE') ? PASSWORD_REQUIRE_UPPERCASE : true;
            $requireNumber = defined('PASSWORD_REQUIRE_NUMBER') ? PASSWORD_REQUIRE_NUMBER : true;
            $requireSpecial = defined('PASSWORD_REQUIRE_SPECIAL_CHAR') ? PASSWORD_REQUIRE_SPECIAL_CHAR : true;
            
            if (strlen($value) < $minLength) {
                $errors[] = "at least " . $minLength . " characters";
            }
            
            if ($requireUpper && !preg_match('/[A-Z]/', $value)) {
                $errors[] = "one uppercase letter";
            }
            
            if ($requireNumber && !preg_match('/[0-9]/', $value)) {
                $errors[] = "one number";
            }
            
            if ($requireSpecial && !preg_match('/[^A-Za-z0-9]/', $value)) {
                $errors[] = "one special character";
            }
            
            if (!empty($errors)) {
                return 'Password must contain ' . implode(', ', $errors) . '.';
            }
        }
        
        return true;
    }
    
    /**
     * Rule: Unique in database (requires database instance)
     */
    protected function validateUnique($field, $value, $table, $column = null, $exceptId = null) {
        if (!$value) {
            return true;
        }
        
        $column = $column ?: $field;
        
        try {
            $db = Database::getInstance();
            $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
            $params = [$value];
            
            if ($exceptId) {
                $sql .= " AND id != ?";
                $params[] = $exceptId;
            }
            
            $result = $db->fetchOne($sql, $params);
            
            if ($result['count'] > 0) {
                return ucfirst(str_replace('_', ' ', $field)) . ' already exists.';
            }
        } catch (Exception $e) {
            error_log("Unique validation error: " . $e->getMessage());
            return 'Database validation error.';
        }
        
        return true;
    }
    
    /**
     * Rule: Exists in database (requires database instance)
     */
    protected function validateExists($field, $value, $table, $column = null) {
        if (!$value) {
            return true;
        }
        
        $column = $column ?: $field;
        
        try {
            $db = Database::getInstance();
            $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
            $result = $db->fetchOne($sql, [$value]);
            
            if ($result['count'] === 0) {
                return ucfirst(str_replace('_', ' ', $field)) . ' does not exist.';
            }
        } catch (Exception $e) {
            error_log("Exists validation error: " . $e->getMessage());
            return 'Database validation error.';
        }
        
        return true;
    }
}
<?php
/**
 * GateWey Requisition Management System
 * Database Configuration & Connection Class
 * 
 * File: config/database.php
 * Purpose: Database connection settings, constants, and PDO database class
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

/**
 * ========================================
 * DATABASE CONFIGURATION CONSTANTS
 * ========================================
 */
define('DB_HOST', 'localhost');           // Database host
define('DB_NAME', 'gateweyc_requisition');         // Database name
define('DB_USER', 'gateweyc_request_admin');                // Database username
define('DB_PASS', '{6Reg-0n-UG=&YVU');                    // Database password
define('DB_CHARSET', 'utf8mb4');          // Character set
define('DB_COLLATION', 'utf8mb4_unicode_ci'); // Collation

/**
 * PDO DATABASE CONNECTION SETTINGS
 */
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET);

// PDO Options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATION
]);

/**
 * DATABASE CONNECTION POOL SETTINGS
 */
define('DB_PERSISTENT', false); // Use persistent connections
define('DB_TIMEOUT', 30);       // Connection timeout in seconds

/**
 * DATABASE TABLE PREFIX (if needed)
 */
define('DB_PREFIX', ''); // Leave empty if not using prefix

/**
 * TABLE NAMES
 * Define table names as constants for easy reference
 */
define('TABLE_USERS', DB_PREFIX . 'users');
define('TABLE_ROLES', DB_PREFIX . 'roles');
define('TABLE_DEPARTMENTS', DB_PREFIX . 'departments');
define('TABLE_REQUISITIONS', DB_PREFIX . 'requisitions');
define('TABLE_REQUISITION_ITEMS', DB_PREFIX . 'requisition_items');
define('TABLE_REQUISITION_APPROVALS', DB_PREFIX . 'requisition_approvals');
define('TABLE_REQUISITION_DOCUMENTS', DB_PREFIX . 'requisition_documents');
define('TABLE_AUDIT_LOG', DB_PREFIX . 'audit_log');
define('TABLE_NOTIFICATIONS', DB_PREFIX . 'notifications');
define('TABLE_REMEMBER_TOKENS', DB_PREFIX . 'remember_tokens');

/**
 * DATABASE BACKUP SETTINGS
 */
define('DB_BACKUP_DIR', ROOT_PATH . '/backups');
define('DB_BACKUP_ENABLED', true);
define('DB_BACKUP_AUTO', false); // Enable automatic backups
define('DB_BACKUP_FREQUENCY', 'daily'); // daily, weekly, monthly
define('DB_BACKUP_RETENTION', 30); // Days to keep backups

/**
 * ========================================
 * DATABASE CLASS
 * ========================================
 * Singleton pattern for database connection
 */
class Database {
    
    // Singleton instance
    private static $instance = null;
    private $connection = null;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        try {
            $dsn = DB_DSN;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => DB_PERSISTENT,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Log successful connection (optional - only in development)
            if (defined('APP_ENV') && APP_ENV === 'development' && APP_DEBUG) {
                error_log("Database connection established successfully");
            }
            
        } catch (PDOException $e) {
            // Log the error
            error_log("Database Connection Error: " . $e->getMessage());
            
            // Display user-friendly error
            if (defined('APP_ENV') && APP_ENV === 'development' && APP_DEBUG) {
                die("Database Connection Failed: " . $e->getMessage());
            } else {
                die("A database error occurred. Please contact the administrator.");
            }
        }
    }
    
    /**
     * Get singleton instance of Database
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Prevent cloning of instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Execute a prepared statement with parameters
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return PDOStatement
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query Execution Error: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }
    
    /**
     * Fetch single row
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array|false
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Fetch single column value
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return mixed
     */
    public function fetchColumn($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Insert data into a table
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int Last insert ID
     */
    public function insert($table, $data) {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $this->execute($sql, array_values($data));
        return $this->lastInsertId();
    }
    
    /**
     * Update data in a table
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param string $where WHERE clause (with placeholders)
     * @param array $whereParams Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "$column = ?";
        }
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $table,
            implode(', ', $setParts),
            $where
        );
        
        $params = array_merge(array_values($data), $whereParams);
        $stmt = $this->execute($sql, $params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Delete data from a table
     * 
     * @param string $table Table name
     * @param string $where WHERE clause (with placeholders)
     * @param array $params Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function delete($table, $where, $params = []) {
        $sql = sprintf("DELETE FROM %s WHERE %s", $table, $where);
        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Count rows in a table
     * 
     * @param string $table Table name
     * @param string $where WHERE clause (optional)
     * @param array $params Parameters for WHERE clause
     * @return int Row count
     */
    public function count($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) FROM $table";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }
        return (int) $this->fetchColumn($sql, $params);
    }
    
    /**
     * Check if a record exists
     * 
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @return bool
     */
    public function exists($table, $where, $params = []) {
        return $this->count($table, $where, $params) > 0;
    }
    
    /**
     * Get last insert ID
     * 
     * @return string
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begin transaction
     * 
     * @return bool
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     * 
     * @return bool
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     * 
     * @return bool
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
    
    /**
     * Check if we're in a transaction
     * 
     * @return bool
     */
    public function inTransaction() {
        return $this->connection->inTransaction();
    }
    
    /**
     * Get table name with prefix
     * 
     * @param string $table Base table name
     * @return string Table name with prefix
     */
    public static function table($table) {
        return DB_PREFIX . $table;
    }
    
    /**
     * Quote a string for safe use in SQL
     * 
     * @param string $string String to quote
     * @return string Quoted string
     */
    public function quote($string) {
        return $this->connection->quote($string);
    }
    
    /**
     * Get database name
     * 
     * @return string
     */
    public static function getDatabaseName() {
        return DB_NAME;
    }
    
    /**
     * Get database host
     * 
     * @return string
     */
    public static function getDatabaseHost() {
        return DB_HOST;
    }
    
    /**
     * Get database username
     * 
     * @return string
     */
    public static function getDatabaseUser() {
        return DB_USER;
    }
    
    /**
     * Get database charset
     * 
     * @return string
     */
    public static function getDatabaseCharset() {
        return DB_CHARSET;
    }
    
    /**
     * Test database connection
     * 
     * @return array Connection status
     */
    public static function testConnection() {
        try {
            $db = self::getInstance();
            $result = $db->fetchOne("SELECT 1 as test");
            
            return [
                'success' => true,
                'message' => 'Database connection successful',
                'database' => DB_NAME,
                'host' => DB_HOST,
                'charset' => DB_CHARSET
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get database version
     * 
     * @return string
     */
    public function getVersion() {
        try {
            return $this->fetchColumn("SELECT VERSION()");
        } catch (Exception $e) {
            return 'Unknown';
        }
    }
    
    /**
     * Get list of tables in database
     * 
     * @return array
     */
    public function getTables() {
        try {
            $tables = $this->fetchAll("SHOW TABLES");
            return array_column($tables, 'Tables_in_' . DB_NAME);
        } catch (Exception $e) {
            error_log("Error getting tables: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Optimize all tables in database
     * 
     * @return bool
     */
    public function optimizeTables() {
        try {
            $tables = $this->getTables();
            foreach ($tables as $table) {
                $this->execute("OPTIMIZE TABLE `$table`");
            }
            return true;
        } catch (Exception $e) {
            error_log("Error optimizing tables: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Backup database to SQL file
     * 
     * @param string $filename Backup filename (optional)
     * @return array Result with success status and message
     */
    public static function backupDatabase($filename = null) {
        try {
            // Create backup directory if it doesn't exist
            if (!is_dir(DB_BACKUP_DIR)) {
                mkdir(DB_BACKUP_DIR, 0755, true);
                // Create .htaccess to prevent direct access
                file_put_contents(DB_BACKUP_DIR . '/.htaccess', "Deny from all");
            }
            
            // Generate filename if not provided
            if (!$filename) {
                $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            }
            
            $filepath = DB_BACKUP_DIR . '/' . $filename;
            
            // MySQL dump command
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s 2>&1',
                escapeshellarg(DB_USER),
                escapeshellarg(DB_PASS),
                escapeshellarg(DB_HOST),
                escapeshellarg(DB_NAME),
                escapeshellarg($filepath)
            );
            
            exec($command, $output, $returnCode);
            
            // Check if backup was successful
            if ($returnCode === 0 && file_exists($filepath) && filesize($filepath) > 0) {
                return [
                    'success' => true,
                    'message' => 'Database backup created successfully',
                    'filename' => $filename,
                    'filepath' => $filepath,
                    'size' => filesize($filepath)
                ];
            } else {
                // Clean up failed backup file
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
                
                return [
                    'success' => false,
                    'message' => 'Failed to create database backup',
                    'error' => !empty($output) ? implode("\n", $output) : 'Unknown error'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Backup error: ' . $e->getMessage()
            ];
        }
    }
}

/**
 * Test database connection on configuration load (development only)
 */
if (defined('APP_ENV') && APP_ENV === 'development' && defined('APP_DEBUG') && APP_DEBUG) {
    try {
        $testConnection = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
        // Connection successful
        $testConnection = null;
    } catch (PDOException $e) {
        die("Database Connection Error: " . $e->getMessage());
    }
}
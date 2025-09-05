<?php
class Database {
    private $host = "localhost";
    private $dbname = "traffic_dashboard";
    private $user = "root";
    private $pass = "";
    private $conn;
    private $stmt;
    private static $instance = null;
    
    // Configuration
    private $charset = "utf8mb4";
    private $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    
    // Singleton pattern - ek hi connection throughout application
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Private constructor to prevent direct instantiation
    private function __construct() {
        $this->connect();
    }
    
    // Database connection
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $this->conn = new PDO($dsn, $this->user, $this->pass, $this->options);
            
            // Log successful connection
            $this->logActivity("Database connected successfully");
            
        } catch (PDOException $e) {
            $this->logError("Connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    // Get PDO connection for raw queries
    public function getConnection() {
        return $this->conn;
    }
    
    // Prepare and execute query
    public function query($query, $params = []) {
        try {
            $this->stmt = $this->conn->prepare($query);
            $this->stmt->execute($params);
            return $this;
        } catch (PDOException $e) {
            $this->logError("Query failed: " . $e->getMessage() . " | Query: " . $query);
            throw new Exception("Database query failed");
        }
    }
    
    // Fetch single row
    public function fetch() {
        return $this->stmt->fetch();
    }
    
    // Fetch all rows
    public function fetchAll() {
        return $this->stmt->fetchAll();
    }
    
    // Fetch single column value
    public function fetchColumn() {
        return $this->stmt->fetchColumn();
    }
    
    // Get row count
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    // Get last inserted ID
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    // Begin transaction
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    // Commit transaction
    public function commit() {
        return $this->conn->commit();
    }
    
    // Rollback transaction
    public function rollback() {
        return $this->conn->rollBack();
    }
    
    // Insert data with validation
    public function insert($table, $data) {
        try {
            // Sanitize table name
            $table = $this->sanitizeTableName($table);
            
            $columns = implode(',', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            
            $this->query($query, $data);
            $insertId = $this->lastInsertId();
            
            $this->logActivity("Insert operation", "Table: {$table}, ID: {$insertId}");
            return $insertId;
            
        } catch (Exception $e) {
            $this->logError("Insert failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Update data with validation
    public function update($table, $data, $where, $whereParams = []) {
        try {
            $table = $this->sanitizeTableName($table);
            
            $setParts = [];
            foreach (array_keys($data) as $key) {
                $setParts[] = "{$key} = :{$key}";
            }
            $setClause = implode(', ', $setParts);
            
            $query = "UPDATE {$table} SET {$setClause} WHERE {$where}";
            $params = array_merge($data, $whereParams);
            
            $this->query($query, $params);
            $affectedRows = $this->rowCount();
            
            $this->logActivity("Update operation", "Table: {$table}, Affected rows: {$affectedRows}");
            return $affectedRows;
            
        } catch (Exception $e) {
            $this->logError("Update failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Delete data with validation
    public function delete($table, $where, $whereParams = []) {
        try {
            $table = $this->sanitizeTableName($table);
            
            $query = "DELETE FROM {$table} WHERE {$where}";
            $this->query($query, $whereParams);
            $affectedRows = $this->rowCount();
            
            $this->logActivity("Delete operation", "Table: {$table}, Affected rows: {$affectedRows}");
            return $affectedRows;
            
        } catch (Exception $e) {
            $this->logError("Delete failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Select data with pagination
    public function select($table, $columns = '*', $where = '', $whereParams = [], $orderBy = '', $limit = '', $offset = 0) {
        try {
            $table = $this->sanitizeTableName($table);
            
            $query = "SELECT {$columns} FROM {$table}";
            
            if (!empty($where)) {
                $query .= " WHERE {$where}";
            }
            
            if (!empty($orderBy)) {
                $query .= " ORDER BY {$orderBy}";
            }
            
            if (!empty($limit)) {
                $query .= " LIMIT {$offset}, {$limit}";
            }
            
            return $this->query($query, $whereParams)->fetchAll();
            
        } catch (Exception $e) {
            $this->logError("Select failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Check if record exists
    public function exists($table, $where, $whereParams = []) {
        $table = $this->sanitizeTableName($table);
        $query = "SELECT 1 FROM {$table} WHERE {$where} LIMIT 1";
        return $this->query($query, $whereParams)->fetch() !== false;
    }
    
    // Count records
    public function count($table, $where = '', $whereParams = []) {
        $table = $this->sanitizeTableName($table);
        $query = "SELECT COUNT(*) FROM {$table}";
        
        if (!empty($where)) {
            $query .= " WHERE {$where}";
        }
        
        return $this->query($query, $whereParams)->fetchColumn();
    }
    
    // Sanitize table name (prevent SQL injection)
    private function sanitizeTableName($table) {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    }
    
    // Log database activities
    private function logActivity($action, $details = '') {
        $log_file = '../logs/database.log';
        $log_dir = dirname($log_file);
        
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $action";
        
        if (!empty($details)) {
            $log_entry .= " - $details";
        }
        
        $log_entry .= PHP_EOL;
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    // Log database errors
    private function logError($error) {
        $log_file = '../logs/database_errors.log';
        $log_dir = dirname($log_file);
        
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] ERROR: $error" . PHP_EOL;
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    // Close connection
    public function close() {
        $this->conn = null;
        $this->stmt = null;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Initialize database instance
$db = Database::getInstance();

// Legacy mysqli connection for backward compatibility
$host = "localhost";
$dbname = "traffic_dashboard";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    error_log("Legacy connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Helper functions for legacy code
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}
?>
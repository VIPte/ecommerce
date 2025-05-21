<?php
require_once 'config.php';

// Database connection class
class Database {
    private $conn;
    
    // Constructor - establish database connection
    public function __construct() {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8");
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    // Get database connection
    public function getConnection() {
        return $this->conn;
    }
    
    // Execute query
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    // Prepare statement
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    // Get last inserted ID
    public function lastInsertId() {
        return $this->conn->insert_id;
    }
    
    // Escape string
    public function escapeString($string) {
        return $this->conn->real_escape_string($string);
    }
    
    // Close connection
    public function close() {
        $this->conn->close();
    }
}

// Create database instance
$db = new Database();
?>
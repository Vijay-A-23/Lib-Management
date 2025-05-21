<?php
/**
 * Database connection using PDO
 */

require_once 'config.php';

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Execute a query with parameters
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return PDOStatement|false The PDOStatement object or false on failure
     */
    public function query($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            echo "Query Error: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Get a single row from a query
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return array|false Associative array of data or false if no record found
     */
    public function getRow($query, $params = []) {
        $stmt = $this->query($query, $params);
        if ($stmt) {
            return $stmt->fetch();
        }
        return false;
    }
    
    /**
     * Get all rows from a query
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return array Array of associative arrays of data
     */
    public function getAll($query, $params = []) {
        $stmt = $this->query($query, $params);
        if ($stmt) {
            return $stmt->fetchAll();
        }
        return [];
    }
    
    /**
     * Get the last inserted ID
     * 
     * @return string The last inserted ID
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}
?>

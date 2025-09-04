<?php
// includes/db_connect.php
require_once 'config.php';


class Database
{
    private static $instance = null;
    private $conn;

    private function __construct()
    {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }

            $this->conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function prepare($sql)
    {
        return $this->conn->prepare($sql);
    }

    public function query($sql)
    {
        return $this->conn->query($sql);
    }

    public function escape($string)
    {
        return $this->conn->real_escape_string($string);
    }

    public function getLastId()
    {
        return $this->conn->insert_id;
    }

    public function close()
    {
        $this->conn->close();
    }
}

// Create global database instance
$db = Database::getInstance()->getConnection();
$conn = $db;
<?php

/**
 * Database Connection Class
 * Secure PDO connection with prepared statements
 */

require_once 'config.php';

class Database
{
    private $host;
    private $dbName;
    private $username;
    private $password;
    private $charset;
    private $pdo;

    public function __construct()
    {
        $this->host = DB_HOST;
        $this->dbName = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = DB_CHARSET;
    }

    /**
     * Create PDO connection
     */
    public function connect()
    {
        if ($this->pdo === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}",
                PDO::ATTR_PERSISTENT => false
            ];

            try {
                $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw new Exception("Connection failed. Please try again later.");
            }
        }

        return $this->pdo;
    }

    /**
     * Execute a prepared statement
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            throw new Exception("Database operation failed. Please try again.");
        }
    }

    /**
     * Get single row
     */
    public function fetch($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Get all rows
     */
    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Get last inserted ID
     */
    public function lastInsertId()
    {
        return $this->connect()->lastInsertId();
    }

    /**
     * Get row count
     */
    public function rowCount($sql, $params = [])
    {
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->connect()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->connect()->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->connect()->rollback();
    }

    /**
     * Close connection
     */
    public function close()
    {
        $this->pdo = null;
    }
}

// Global database instance
$db = new Database();

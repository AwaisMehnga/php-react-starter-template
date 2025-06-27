<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database Connection Singleton
 */
class Database
{
    private static $instance = null;
    private $connection;
    private $host;
    private $dbname;
    private $username;
    private $password;

    private function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';

        $this->host = $config['host'] ?? 'localhost';
        $this->dbname = $config['dbname'] ?? '';
        $this->username = $config['username'] ?? 'root';
        $this->password = $config['password'] ?? '';

        $this->connect();
    }

    /**
     * Get singleton instance
     * 
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance->connection;
    }

    /**
     * Create database connection
     */
    private function connect(): void
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // For development, show error. In production, log it instead.
            if (($_ENV['APP_ENV'] ?? 'prod') === 'dev') {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed");
            }
        }
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
    }
}

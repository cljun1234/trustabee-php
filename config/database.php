<?php

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        // In a real environment, use environment variables
        // For this sandbox, we'll default to sqlite if environment variables are not set

        $host = getenv('DB_HOST');
        $db   = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        $driver = getenv('DB_DRIVER');

        if (!$driver && !$host) {
            $driver = 'sqlite';
        }

        $driver = $driver ?: 'mysql';

        try {
            if ($driver === 'sqlite') {
                // For sandbox testing without MySQL
                $this->pdo = new PDO("sqlite:" . __DIR__ . "/../database/trustabee.sqlite");
            } else {
                $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
                $this->pdo = new PDO($dsn, $user, $pass);
            }

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // In production, log this, don't echo
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}

<?php

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        // In a real environment, use environment variables
        // For this sandbox, we'll default to localhost if not set, or a sqlite file for fallback testing

        $host = getenv('DB_HOST') ?: 'localhost';
        $db   = getenv('DB_NAME') ?: 'u118256295_stg_trustabee';
        $user = getenv('DB_USER') ?: 'u118256295_trustabee';
        $pass = getenv('DB_PASS') ?: 'Trustabee123!';
        $driver = getenv('DB_DRIVER') ?: 'mysql';

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
            // Log the error details
            error_log("Database connection failed: " . $e->getMessage());

            // If debug mode is enabled, show the error
            if (getenv('APP_DEBUG') === 'true') {
                 die("Database connection failed: " . $e->getMessage());
            }

            // In production, show a generic message
            http_response_code(500);
            die("Service temporarily unavailable. Please try again later.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}

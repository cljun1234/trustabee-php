<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getInstance();
    $stmt = $pdo->query("DESCRIBE socials");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('position', $columns)) {
        echo "Adding position column...\n";
        $pdo->exec("ALTER TABLE socials ADD COLUMN position VARCHAR(50) DEFAULT 'bottom-right'");
        echo "Column added.\n";
    } else {
        echo "Column 'position' already exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

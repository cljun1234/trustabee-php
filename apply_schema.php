<?php
// Load environment variables if needed, but Config/Database usually handles it.
// Assuming this is run from the root.

require_once 'config/database.php';

try {
    $pdo = Database::getInstance();

    $sql = file_get_contents('database/schema.sql');

    // Split into individual statements because PDO::exec sometimes has trouble with multiple statements depending on driver
    // However, the stored procedure part uses DELIMITER which PHP PDO doesn't parse natively usually.
    // But since this is a controlled environment, let's try to see if we can execute it.

    // Actually, simpler approach for the Stored Proc:
    // 1. Run the CREATE TABLEs first.
    // 2. Run the Stored Procedure block.

    // Regex to split by semicolon, but ignore semicolons inside BEGIN...END blocks of procedures is hard.
    // Let's rely on the fact that `database/schema.sql` is well formatted.

    // Alternative: Just run the ALTER statements directly here for the specific task updates,
    // to be 100% sure, then rely on schema.sql for future.

    // BUT, the plan said "Apply Database Changes" using schema.sql.
    // Let's try to run it. If it fails due to DELIMITER, I'll manually run the ALTERS.

    // NOTE: PHP PDO doesn't support 'DELIMITER //'. That's a MySQL client command.
    // So I need to parse the file or just run the specific needed commands.

    echo "Applying schema updates...\n";

    // 1. Create Partner Tokens Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS partner_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        token VARCHAR(64) NOT NULL UNIQUE,
        allowed_domains TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Checked/Created partner_tokens table.\n";

    // 2. Add display_name to plans
    try {
        $pdo->exec("ALTER TABLE plans ADD COLUMN display_name VARCHAR(255) DEFAULT NULL");
        echo "Added display_name to plans.\n";
    } catch (PDOException $e) {
        // Ignore if exists
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
             echo "Column display_name already exists.\n";
        } else {
             // For SQLite, ALTER TABLE ADD COLUMN is supported but 'IF NOT EXISTS' logic is different.
             // If we are in MySQL (prod), we check information_schema or catch error.
             echo "Note: " . $e->getMessage() . "\n";
        }
    }

    // 3. Insert Sponsored Plan
    // Using INSERT IGNORE or checking first
    $stmt = $pdo->prepare("SELECT id FROM plans WHERE id = 5");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $sql = "INSERT INTO plans (id, name, display_name, monthly_price, visit_limit, domain_limit, features) VALUES
        (5, 'Sponsored', 'Unlimited', 0.00, -1, -1, '{\"remove_branding\": true, \"coupons\": true, \"notifications\": true, \"videos\": true, \"newsletters\": true, \"socials\": true, \"reviews\": true, \"live_visitor\": true, \"live_conversion\": true}')";
        $pdo->exec($sql);
        echo "Inserted Sponsored plan.\n";
    } else {
        echo "Sponsored plan already exists.\n";
    }

    echo "Database update complete.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

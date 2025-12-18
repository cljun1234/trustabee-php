# Database Migration Instructions

To support the new **Sponsored Plans** and **Partner Tokens** features, the following database changes must be applied.

## 1. Execute SQL Commands

Run the following SQL commands in your MySQL database (via phpMyAdmin, CLI, or other tool):

```sql
-- 1. Create Partner Tokens Table
CREATE TABLE IF NOT EXISTS partner_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    allowed_domains TEXT, -- JSON or comma separated
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Add Display Name to Plans
-- Note: Check if column exists first if not using the stored procedure logic
ALTER TABLE plans ADD COLUMN display_name VARCHAR(255) DEFAULT NULL;

-- 3. Insert Sponsored Plan (ID=5)
INSERT IGNORE INTO plans (id, name, display_name, monthly_price, visit_limit, domain_limit, features) VALUES
(5, 'Sponsored', 'Unlimited', 0.00, -1, -1, '{\"remove_branding\": true, \"coupons\": true, \"notifications\": true, \"videos\": true, \"newsletters\": true, \"socials\": true, \"reviews\": true, \"live_visitor\": true, \"live_conversion\": true}');
```

## 2. Verify

Ensure that:
1. The `partner_tokens` table exists.
2. The `plans` table has a `display_name` column.
3. The `plans` table has a row with ID 5 (Sponsored).

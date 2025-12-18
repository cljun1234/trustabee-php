-- ==========================================
-- TRUSTABEE DATABASE SCHEMA
-- Handles Fresh Installs & Updates
-- ==========================================

-- 1. PLANS (New Table)
CREATE TABLE IF NOT EXISTS plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    display_name VARCHAR(255) DEFAULT NULL, -- Public facing name
    monthly_price DECIMAL(10, 2) DEFAULT 0.00,
    visit_limit INT DEFAULT 1000, -- -1 for unlimited
    domain_limit INT DEFAULT 1, -- -1 for unlimited
    features TEXT, -- JSON
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Plans (Safe Insert)
INSERT IGNORE INTO plans (id, name, display_name, monthly_price, visit_limit, domain_limit, features) VALUES
(1, 'Free', 'Free Plan', 0.00, 1000, 1, '{"remove_branding": false, "coupons": true, "notifications": true, "videos": false}'),
(2, 'Basic', 'Basic Plan', 9.00, 10000, 3, '{"remove_branding": true, "coupons": true, "notifications": true, "videos": true}'),
(3, 'Pro', 'Pro Plan', 29.00, 50000, 10, '{"remove_branding": true, "coupons": true, "notifications": true, "videos": true}'),
(4, 'Unlimited', 'Unlimited Plan', 99.00, -1, -1, '{"remove_branding": true, "coupons": true, "notifications": true, "videos": true}'),
(5, 'Sponsored', 'Unlimited', 0.00, -1, -1, '{"remove_branding": true, "coupons": true, "notifications": true, "videos": true, "newsletters": true, "socials": true, "reviews": true, "live_visitor": true, "live_conversion": true}');

-- 2. USERS
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user', -- 'user', 'owner'
    plan_id INT DEFAULT 1,
    subscription_start DATE DEFAULT NULL,
    verified BOOLEAN DEFAULT 0,
    last_login TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. WIDGETS
CREATE TABLE IF NOT EXISTS widgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    domain VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    magical_detection BOOLEAN DEFAULT 1,
    live_visitor_enabled BOOLEAN DEFAULT 0,
    live_visitor_config TEXT DEFAULT NULL,
    live_conversion_enabled BOOLEAN DEFAULT 0,
    use_real_conversion BOOLEAN DEFAULT 1,
    use_simulated_conversion BOOLEAN DEFAULT 1,
    timezone VARCHAR(50) DEFAULT 'UTC',
    allowed_domains TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. USAGE TRACKING
CREATE TABLE IF NOT EXISTS widget_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    month_year VARCHAR(7) NOT NULL, -- 'YYYY-MM'
    muv_count INT DEFAULT 0,
    session_count INT DEFAULT 0,
    impression_count INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (widget_id, month_year),
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS visitor_monthly_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    visitor_id VARCHAR(255) NOT NULL,
    month_year VARCHAR(7) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (widget_id, visitor_id, month_year),
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

-- 5. NOTIFICATIONS
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    action_text VARCHAR(255) NOT NULL,
    location VARCHAR(255) DEFAULT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

-- 6. LIVE VISITORS
CREATE TABLE IF NOT EXISTS live_visitors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    visitor_id VARCHAR(255) NOT NULL,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    url VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE,
    INDEX (last_seen)
);

-- 7. EVENTS
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    payload TEXT DEFAULT NULL,
    visitor_id VARCHAR(255) DEFAULT NULL,
    page_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

-- 8. TRAFFIC SNAPSHOTS
CREATE TABLE IF NOT EXISTS traffic_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    visitor_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

-- 9. COUPONS
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    title VARCHAR(255) DEFAULT 'Special Offer',
    description TEXT,
    coupon_code VARCHAR(100) NOT NULL,
    button_text VARCHAR(100) DEFAULT 'Copy Code',
    bg_color VARCHAR(50) DEFAULT '#ffffff',
    text_color VARCHAR(50) DEFAULT '#333333',
    trigger_type VARCHAR(50) DEFAULT 'delay',
    trigger_delay INT DEFAULT 0,
    frequency VARCHAR(50) DEFAULT 'every_load',
    match_url VARCHAR(255) DEFAULT NULL,
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    image_url VARCHAR(255) DEFAULT NULL,
    image_style VARCHAR(50) DEFAULT 'top',
    remove_branding BOOLEAN DEFAULT 0,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS coupon_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE
);

-- 10. ANNOUNCEMENTS
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    title VARCHAR(255) DEFAULT 'Announcement',
    message TEXT,
    image_url VARCHAR(255) DEFAULT NULL,
    image_style VARCHAR(50) DEFAULT 'top',
    btn_text VARCHAR(100) DEFAULT 'Learn More',
    btn_action VARCHAR(50) DEFAULT 'link',
    btn_link VARCHAR(255) DEFAULT NULL,
    remove_branding BOOLEAN DEFAULT 0,
    bg_color VARCHAR(50) DEFAULT '#ffffff',
    text_color VARCHAR(50) DEFAULT '#333333',
    trigger_type VARCHAR(50) DEFAULT 'delay',
    trigger_delay INT DEFAULT 0,
    frequency VARCHAR(50) DEFAULT 'every_load',
    match_url VARCHAR(255) DEFAULT NULL,
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS announcement_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    announcement_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE
);

-- 11. VIDEOS
CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    title VARCHAR(255) DEFAULT 'Check this out',
    message TEXT,
    video_url VARCHAR(255) DEFAULT NULL,
    btn_text VARCHAR(100) DEFAULT 'Learn More',
    btn_action VARCHAR(50) DEFAULT 'link',
    btn_link VARCHAR(255) DEFAULT NULL,
    remove_branding BOOLEAN DEFAULT 0,
    bg_color VARCHAR(50) DEFAULT '#ffffff',
    text_color VARCHAR(50) DEFAULT '#333333',
    trigger_type VARCHAR(50) DEFAULT 'delay',
    trigger_delay INT DEFAULT 0,
    frequency VARCHAR(50) DEFAULT 'every_load',
    match_url VARCHAR(255) DEFAULT NULL,
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS video_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);

-- 12. NEWSLETTERS
CREATE TABLE IF NOT EXISTS newsletters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    title VARCHAR(255) DEFAULT 'Join Our Newsletter',
    description TEXT,
    image_url VARCHAR(255) DEFAULT NULL,
    image_style VARCHAR(50) DEFAULT 'top',
    bg_color VARCHAR(50) DEFAULT '#ffffff',
    text_color VARCHAR(50) DEFAULT '#333333',
    btn_text VARCHAR(100) DEFAULT 'Subscribe',
    allow_name BOOLEAN DEFAULT 0,
    allow_phone BOOLEAN DEFAULT 0,
    webhook_url VARCHAR(255) DEFAULT NULL,
    success_action VARCHAR(50) DEFAULT 'message',
    success_message TEXT,
    redirect_url VARCHAR(255) DEFAULT NULL,
    trigger_type VARCHAR(50) DEFAULT 'delay',
    trigger_delay INT DEFAULT 0,
    frequency VARCHAR(50) DEFAULT 'every_load',
    match_url VARCHAR(255) DEFAULT NULL,
    remove_branding BOOLEAN DEFAULT 0,
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS newsletter_leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    newsletter_id INT NOT NULL,
    widget_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (newsletter_id) REFERENCES newsletters(id) ON DELETE CASCADE,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS newsletter_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    newsletter_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (newsletter_id) REFERENCES newsletters(id) ON DELETE CASCADE
);

-- 13. SOCIALS
CREATE TABLE IF NOT EXISTS socials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    active BOOLEAN DEFAULT 1,
    title VARCHAR(255) DEFAULT 'Follow Us',
    subtitle VARCHAR(255) DEFAULT 'Feel free to follow and connect with us at our social networks',
    remove_branding BOOLEAN DEFAULT 0,
    position VARCHAR(50) DEFAULT 'bottom-right',
    trigger_type VARCHAR(50) DEFAULT 'delay',
    trigger_delay INT DEFAULT 0,
    frequency VARCHAR(50) DEFAULT 'session',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS social_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    social_id INT NOT NULL,
    platform VARCHAR(50) NOT NULL,
    url TEXT,
    label_text VARCHAR(255),
    is_active BOOLEAN DEFAULT 0,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (social_id) REFERENCES socials(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS social_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    social_id INT NOT NULL,
    link_id INT DEFAULT NULL,
    event_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (social_id) REFERENCES socials(id) ON DELETE CASCADE,
    FOREIGN KEY (link_id) REFERENCES social_links(id) ON DELETE SET NULL
);

-- 14. REVIEWS
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    popup_title VARCHAR(255) DEFAULT 'Rate your experience',
    popup_description TEXT,
    google_review_link VARCHAR(255) DEFAULT NULL,
    facebook_review_link VARCHAR(255) DEFAULT NULL,
    high_star_action VARCHAR(50) DEFAULT 'thank_you',
    high_star_message TEXT,
    high_star_coupon_id INT DEFAULT NULL,
    high_star_redirect_url VARCHAR(255) DEFAULT NULL,
    low_star_action VARCHAR(50) DEFAULT 'thank_you',
    low_star_message TEXT,
    low_star_coupon_id INT DEFAULT NULL,
    low_star_redirect_url VARCHAR(255) DEFAULT NULL,
    trigger_type VARCHAR(50) DEFAULT 'delay',
    trigger_delay INT DEFAULT 0,
    frequency VARCHAR(50) DEFAULT 'every_load',
    match_url VARCHAR(255) DEFAULT NULL,
    show_reviews_widget BOOLEAN DEFAULT 0,
    widget_position VARCHAR(50) DEFAULT 'bottom-right',
    remove_branding BOOLEAN DEFAULT 0,
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS review_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    review_text TEXT,
    rating INT DEFAULT 5,
    image_url VARCHAR(255) DEFAULT NULL,
    source VARCHAR(50) DEFAULT 'custom',
    source_link VARCHAR(255) DEFAULT NULL,
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS review_feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    widget_id INT NOT NULL,
    rating INT NOT NULL,
    name VARCHAR(255),
    email VARCHAR(255),
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS review_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
);

-- 15. SYSTEM SETTINGS
CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
);

-- 16. LOGIN OTPS
CREATE TABLE IF NOT EXISTS login_otps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    otp_code VARCHAR(10),
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 17. PASSWORD RESETS
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (email),
    INDEX (token)
);

-- 18. USER DEVICES (Trusted Devices)
CREATE TABLE IF NOT EXISTS user_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_token VARCHAR(255) NOT NULL,
    last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (device_token)
);

-- 19. PARTNER TOKENS (Sponsored Plans)
CREATE TABLE IF NOT EXISTS partner_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    allowed_domains TEXT, -- JSON or comma separated
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =================================================================
-- UPDATER STORED PROCEDURE (SAFE ADD COLUMNS)
-- =================================================================
DROP PROCEDURE IF EXISTS upgrade_trustabee_db;
DELIMITER //
CREATE PROCEDURE upgrade_trustabee_db()
BEGIN
    -- 1. USERS: Add role, plan_id, subscription_start, verified, last_login
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='role') THEN
        ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'user';
    END IF;
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='plan_id') THEN
        ALTER TABLE users ADD COLUMN plan_id INT DEFAULT 1;
    END IF;
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='subscription_start') THEN
        ALTER TABLE users ADD COLUMN subscription_start DATE DEFAULT NULL;
    END IF;
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='verified') THEN
        ALTER TABLE users ADD COLUMN verified BOOLEAN DEFAULT 0;
    END IF;
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='last_login') THEN
        ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL;
    END IF;

    -- 2. WIDGETS: Add allowed_domains, timezone, and live feature flags
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='widgets' AND COLUMN_NAME='magical_detection') THEN
        ALTER TABLE widgets ADD COLUMN magical_detection BOOLEAN DEFAULT 1;
    END IF;
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='widgets' AND COLUMN_NAME='live_visitor_enabled') THEN
        ALTER TABLE widgets ADD COLUMN live_visitor_enabled BOOLEAN DEFAULT 0;
    END IF;
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='widgets' AND COLUMN_NAME='live_visitor_config') THEN
        ALTER TABLE widgets ADD COLUMN live_visitor_config TEXT DEFAULT NULL;
    END IF;
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='widgets' AND COLUMN_NAME='live_conversion_enabled') THEN
        ALTER TABLE widgets ADD COLUMN live_conversion_enabled BOOLEAN DEFAULT 0;
    END IF;
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='widgets' AND COLUMN_NAME='use_real_conversion') THEN
        ALTER TABLE widgets ADD COLUMN use_real_conversion BOOLEAN DEFAULT 1;
    END IF;
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='widgets' AND COLUMN_NAME='use_simulated_conversion') THEN
        ALTER TABLE widgets ADD COLUMN use_simulated_conversion BOOLEAN DEFAULT 1;
    END IF;
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='widgets' AND COLUMN_NAME='timezone') THEN
        ALTER TABLE widgets ADD COLUMN timezone VARCHAR(50) DEFAULT 'UTC';
    END IF;
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='widgets' AND COLUMN_NAME='allowed_domains') THEN
        ALTER TABLE widgets ADD COLUMN allowed_domains TEXT DEFAULT NULL;
    END IF;

    -- 3. SOCIALS: Add position
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='socials' AND COLUMN_NAME='position') THEN
        ALTER TABLE socials ADD COLUMN position VARCHAR(50) DEFAULT 'bottom-right';
    END IF;

    -- 4. PLANS: Add display_name
    IF NOT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='plans' AND COLUMN_NAME='display_name') THEN
        ALTER TABLE plans ADD COLUMN display_name VARCHAR(255) DEFAULT NULL;
    END IF;

    -- 5. PARTNER TOKENS: Ensure table exists (handled by CREATE TABLE IF NOT EXISTS above, but good for completeness if we were doing pure alters)

END//
DELIMITER ;

-- Execute Upgrade
CALL upgrade_trustabee_db();

-- Clean up
DROP PROCEDURE upgrade_trustabee_db;

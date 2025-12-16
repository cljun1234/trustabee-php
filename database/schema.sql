-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Widgets (Configuration for a domain)
CREATE TABLE IF NOT EXISTS widgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    domain VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    magical_detection BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Notifications (Simulated data)
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

-- Live Visitors (Heartbeats)
CREATE TABLE IF NOT EXISTS live_visitors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    visitor_id VARCHAR(255) NOT NULL,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    url VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE,
    INDEX (last_seen)
);

-- Events (Real Conversions)
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    type VARCHAR(50) NOT NULL, -- 'form_submit', etc.
    payload TEXT DEFAULT NULL, -- JSON data
    visitor_id VARCHAR(255) DEFAULT NULL,
    page_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);
-- Add Traffic Snapshots table
CREATE TABLE IF NOT EXISTS traffic_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    visitor_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

-- Add Live Visitor Configuration to widgets
ALTER TABLE widgets ADD COLUMN live_visitor_enabled BOOLEAN DEFAULT 0;
ALTER TABLE widgets ADD COLUMN live_visitor_config TEXT DEFAULT NULL; -- JSON string for styles

-- Live Conversion Settings
ALTER TABLE widgets ADD COLUMN live_conversion_enabled BOOLEAN DEFAULT 0;
ALTER TABLE widgets ADD COLUMN use_real_conversion BOOLEAN DEFAULT 1;
ALTER TABLE widgets ADD COLUMN use_simulated_conversion BOOLEAN DEFAULT 1;
-- Add Timezone to widgets
ALTER TABLE widgets ADD COLUMN timezone VARCHAR(50) DEFAULT 'UTC';

-- Coupons Table
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    title VARCHAR(255) DEFAULT 'Special Offer',
    description TEXT,
    coupon_code VARCHAR(100) NOT NULL,
    button_text VARCHAR(100) DEFAULT 'Copy Code',
    bg_color VARCHAR(50) DEFAULT '#ffffff',
    text_color VARCHAR(50) DEFAULT '#333333',
    trigger_type VARCHAR(50) DEFAULT 'delay', -- 'delay' or 'exit_intent'
    trigger_delay INT DEFAULT 0, -- Seconds
    frequency VARCHAR(50) DEFAULT 'every_load', -- 'every_load' or 'session'
    match_url VARCHAR(255) DEFAULT NULL, -- URL pattern to match, NULL = all
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    image_url VARCHAR(255) DEFAULT NULL,
    image_style VARCHAR(50) DEFAULT 'top', -- 'top', 'left', 'right', 'background'
    remove_branding BOOLEAN DEFAULT 0,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

-- Coupon Analytics
CREATE TABLE IF NOT EXISTS coupon_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL, -- 'view', 'click'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE
);

-- Announcements Table
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    title VARCHAR(255) DEFAULT 'Announcement',
    message TEXT,
    image_url VARCHAR(255) DEFAULT NULL,
    image_style VARCHAR(50) DEFAULT 'top', -- 'top', 'left', 'right', 'background'
    btn_text VARCHAR(100) DEFAULT 'Learn More',
    btn_action VARCHAR(50) DEFAULT 'link', -- 'link' or 'close'
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

-- Announcement Analytics
CREATE TABLE IF NOT EXISTS announcement_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    announcement_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL, -- 'view', 'click'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE
);

-- Videos Table
CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    title VARCHAR(255) DEFAULT 'Check this out',
    message TEXT,
    video_url VARCHAR(255) DEFAULT NULL,
    btn_text VARCHAR(100) DEFAULT 'Learn More',
    btn_action VARCHAR(50) DEFAULT 'link', -- 'link' or 'close'
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

-- Video Analytics
CREATE TABLE IF NOT EXISTS video_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL, -- 'view', 'click'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);

-- Newsletters Table
CREATE TABLE IF NOT EXISTS newsletters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,
    title VARCHAR(255) DEFAULT 'Join Our Newsletter',
    description TEXT,
    image_url VARCHAR(255) DEFAULT NULL,
    image_style VARCHAR(50) DEFAULT 'top', -- 'top', 'left', 'right', 'background'
    bg_color VARCHAR(50) DEFAULT '#ffffff',
    text_color VARCHAR(50) DEFAULT '#333333',
    btn_text VARCHAR(100) DEFAULT 'Subscribe',
    allow_name BOOLEAN DEFAULT 0,
    allow_phone BOOLEAN DEFAULT 0,
    webhook_url VARCHAR(255) DEFAULT NULL,
    success_action VARCHAR(50) DEFAULT 'message', -- 'message', 'redirect', 'close'
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

-- Newsletter Leads
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

-- Newsletter Analytics
CREATE TABLE IF NOT EXISTS newsletter_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    newsletter_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL, -- 'view', 'submit'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (newsletter_id) REFERENCES newsletters(id) ON DELETE CASCADE
);

-- Social Widgets
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

-- Social Links
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

-- Social Analytics
CREATE TABLE IF NOT EXISTS social_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    social_id INT NOT NULL,
    link_id INT DEFAULT NULL,
    event_type VARCHAR(50) NOT NULL, -- 'view', 'click'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (social_id) REFERENCES socials(id) ON DELETE CASCADE,
    FOREIGN KEY (link_id) REFERENCES social_links(id) ON DELETE SET NULL
);

-- Reviews Configuration (One per widget, similar to Newsletters but handling both widget/popup config)
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_id INT NOT NULL,

    -- Popup Configuration
    popup_title VARCHAR(255) DEFAULT 'Rate your experience',
    popup_description TEXT,
    google_review_link VARCHAR(255) DEFAULT NULL,
    facebook_review_link VARCHAR(255) DEFAULT NULL,

    -- Post Action Logic (4-5 stars)
    high_star_action VARCHAR(50) DEFAULT 'thank_you', -- 'thank_you', 'coupon', 'redirect', 'close'
    high_star_message TEXT, -- For 'thank_you'
    high_star_coupon_id INT DEFAULT NULL,
    high_star_redirect_url VARCHAR(255) DEFAULT NULL,

    -- Post Action Logic (1-3 stars)
    low_star_action VARCHAR(50) DEFAULT 'thank_you',
    low_star_message TEXT,
    low_star_coupon_id INT DEFAULT NULL,
    low_star_redirect_url VARCHAR(255) DEFAULT NULL,

    -- Triggers (for Popup)
    trigger_type VARCHAR(50) DEFAULT 'delay',
    trigger_delay INT DEFAULT 0,
    frequency VARCHAR(50) DEFAULT 'every_load',
    match_url VARCHAR(255) DEFAULT NULL,

    -- Widget (Toaster) Configuration
    show_reviews_widget BOOLEAN DEFAULT 0, -- Toggle for the toaster widget
    widget_position VARCHAR(50) DEFAULT 'bottom-right',

    remove_branding BOOLEAN DEFAULT 0,
    active BOOLEAN DEFAULT 1, -- Global toggle for the feature
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

-- Manual Review Items (for the Toaster Widget)
CREATE TABLE IF NOT EXISTS review_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    review_text TEXT,
    rating INT DEFAULT 5,
    image_url VARCHAR(255) DEFAULT NULL,
    source VARCHAR(50) DEFAULT 'custom', -- 'facebook', 'google', 'custom'
    source_link VARCHAR(255) DEFAULT NULL,
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
);

-- Feedback Submissions (1-3 stars)
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

-- Analytics
CREATE TABLE IF NOT EXISTS review_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL, -- 'view_popup', 'click_star_1'...'click_star_5'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
);

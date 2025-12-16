SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE users;
TRUNCATE TABLE widgets;
TRUNCATE TABLE notifications;
TRUNCATE TABLE live_visitors;
TRUNCATE TABLE events;
TRUNCATE TABLE traffic_snapshots;
TRUNCATE TABLE coupons;
TRUNCATE TABLE coupon_analytics;
TRUNCATE TABLE announcements;
TRUNCATE TABLE announcement_analytics;
TRUNCATE TABLE videos;
TRUNCATE TABLE video_analytics;
TRUNCATE TABLE newsletters;
TRUNCATE TABLE newsletter_leads;
TRUNCATE TABLE newsletter_analytics;
TRUNCATE TABLE socials;
TRUNCATE TABLE social_links;
TRUNCATE TABLE social_analytics;
TRUNCATE TABLE reviews;
TRUNCATE TABLE review_items;
TRUNCATE TABLE review_feedbacks;
TRUNCATE TABLE review_analytics;

SET FOREIGN_KEY_CHECKS = 1;

-- Insert default admin user
-- Password is 'password'
INSERT INTO users (email, password) VALUES ('admin@trustabee.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert default widget for admin (assumes user_id is 1 after truncate)
INSERT INTO widgets (user_id, domain, name) VALUES (1, 'example.com', 'My First Widget');

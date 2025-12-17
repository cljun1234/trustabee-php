<?php
session_start();

// Error Reporting Configuration
if (getenv('APP_DEBUG') === 'true') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
}

require_once __DIR__ . '/../src/AltoRouter.php';
require_once __DIR__ . '/../config/database.php';

// Autoload Controllers & Helpers
spl_autoload_register(function ($class) {
    if (strpos($class, 'Controller') !== false) {
        require_once __DIR__ . '/../src/Controllers/' . $class . '.php';
    } elseif ($class === 'PlanManager') {
        require_once __DIR__ . '/../src/Helpers/PlanManager.php';
    }
});

$router = new AltoRouter();

// Define Routes

// Auth
$router->map('GET', '/login', 'AuthController#showLogin', 'login');
$router->map('POST', '/login', 'AuthController#processLogin', 'login_post');
$router->map('GET', '/logout', 'AuthController#logout', 'logout');
$router->map('GET', '/register', 'AuthController#showRegister', 'register');
$router->map('POST', '/register', 'AuthController#processRegister', 'register_post');

// Dashboard
$router->map('GET', '/', 'DashboardController#index', 'dashboard');
$router->map('GET', '/billing', 'BillingController#index', 'billing');
$router->map('GET', '/settings', 'DashboardController#settings', 'settings');
$router->map('POST', '/widget/save', 'DashboardController#saveConfig', 'save_config');
$router->map('POST', '/api/toggle-feature', 'DashboardController#toggleFeature', 'api_toggle_feature');

// Admin
$router->map('GET', '/admin/plans', 'AdminController#plans', 'admin_plans');
$router->map('POST', '/admin/plans/save', 'AdminController#savePlan', 'admin_plans_save');
$router->map('GET', '/admin/users', 'AdminController#users', 'admin_users');
$router->map('POST', '/admin/users/plan', 'AdminController#updateUserPlan', 'admin_users_plan');
$router->map('POST', '/admin/users/role', 'AdminController#toggleUserRole', 'admin_users_role');


// Live Visitors
$router->map('GET', '/campaigns/live-visitors', 'LiveVisitorController#index', 'live_visitors');
$router->map('POST', '/save-live-visitor-config', 'LiveVisitorController#saveConfig', 'save_live_visitor');

// Live Conversions
$router->map('GET', '/campaigns/live-conversion', 'LiveConversionController#index', 'live_conversion');
$router->map('POST', '/notification/add', 'LiveConversionController#addNotification', 'add_notification');
$router->map('GET', '/notification/delete/[i:id]', 'LiveConversionController#deleteNotification', 'delete_notification');
$router->map('GET', '/event/delete/[i:id]', 'LiveConversionController#deleteEvent', 'delete_event');


// Coupons
$router->map('GET', '/campaigns/coupon', 'CouponController#index', 'coupon');
$router->map('POST', '/campaigns/coupon/save', 'CouponController#save', 'coupon_save');
$router->map('GET', '/campaigns/coupon/delete/[i:id]', 'CouponController#delete', 'coupon_delete');

// Announcements
$router->map('GET', '/campaigns/announcement', 'AnnouncementController#index', 'announcement');
$router->map('POST', '/campaigns/announcement/save', 'AnnouncementController#save', 'announcement_save');
$router->map('GET', '/campaigns/announcement/delete/[i:id]', 'AnnouncementController#delete', 'announcement_delete');

// Videos
$router->map('GET', '/campaigns/video', 'VideoController#index', 'video');
$router->map('POST', '/campaigns/video/save', 'VideoController#save', 'video_save');
$router->map('GET', '/campaigns/video/delete/[i:id]', 'VideoController#delete', 'video_delete');

// Newsletters
$router->map('GET', '/campaigns/newsletter', 'NewsletterController#index', 'newsletter');
$router->map('POST', '/campaigns/newsletter/save', 'NewsletterController#save', 'newsletter_save');
$router->map('GET', '/campaigns/newsletter/delete/[i:id]', 'NewsletterController#delete', 'newsletter_delete');
$router->map('GET', '/campaigns/newsletter/leads/[i:id]', 'NewsletterController#leads', 'newsletter_leads');
$router->map('GET', '/campaigns/newsletter/export/[i:id]', 'NewsletterController#export_leads', 'newsletter_export');

// Social
$router->map('GET', '/campaigns/social', 'SocialController#index', 'social');
$router->map('POST', '/api/social/save', 'SocialController#save', 'social_save');
$router->map('POST', '/api/social/toggle', 'SocialController#toggle', 'social_toggle');

// Reviews
$router->map('GET', '/campaigns/reviews', 'ReviewController#index', 'reviews');
$router->map('POST', '/campaigns/review/save', 'ReviewController#save', 'review_save');
$router->map('POST', '/campaigns/review/item/save', 'ReviewController#saveItem', 'review_item_save');
$router->map('GET', '/campaigns/review/item/delete/[i:id]', 'ReviewController#deleteItem', 'review_item_delete');

// Fallback for other campaigns (e.g. Coming Soon)
$router->map('GET', '/campaigns/[*:type]', 'DashboardController#campaigns', 'campaigns');

// API / Widget
$router->map('GET', '/api/widget.js', 'WidgetController#serveScript', 'widget_js');
$router->map('GET', '/api/data', 'WidgetController#getData', 'widget_data');
$router->map('POST', '/api/heartbeat', 'WidgetController#heartbeat', 'widget_heartbeat');
$router->map('POST', '/api/track', 'WidgetController#trackEvent', 'widget_track');
$router->map('POST', '/api/track-coupon', 'WidgetController#trackCoupon', 'widget_track_coupon');
$router->map('POST', '/api/track-announcement', 'WidgetController#trackAnnouncement', 'widget_track_announcement');
$router->map('POST', '/api/track-video', 'WidgetController#trackVideo', 'widget_track_video');
$router->map('POST', '/api/track-newsletter', 'WidgetController#trackNewsletter', 'widget_track_newsletter');
$router->map('POST', '/api/track-social', 'WidgetController#trackSocial', 'widget_track_social');
$router->map('POST', '/api/track-review', 'WidgetController#trackReview', 'widget_track_review');
$router->map('POST', '/api/submit-review', 'WidgetController#submitReview', 'widget_submit_review');
$router->map('POST', '/api/submit-newsletter', 'WidgetController#submitNewsletter', 'widget_submit_newsletter');

// Match request
$match = $router->match();

if ($match && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
} elseif ($match) {
    list($controller, $action) = explode('#', $match['target']);
    if (class_exists($controller) && method_exists($controller, $action)) {
        $obj = new $controller();
        call_user_func_array([$obj, $action], $match['params']);
    } else {
        // Handle error: controller or method not found
        header($_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error');
        echo "Error: Controller or action not found.";
    }
} else {
    // 404
    header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    echo "404 Not Found";
}

<?php
session_start();

require_once __DIR__ . '/../src/AltoRouter.php';
require_once __DIR__ . '/../config/database.php';

// Autoload Controllers
spl_autoload_register(function ($class) {
    if (strpos($class, 'Controller') !== false) {
        require_once __DIR__ . '/../src/Controllers/' . $class . '.php';
    }
});

$router = new AltoRouter();

// Define Routes

// Auth
$router->map('GET', '/login', 'AuthController#showLogin', 'login');
$router->map('POST', '/login', 'AuthController#processLogin', 'login_post');
$router->map('GET', '/logout', 'AuthController#logout', 'logout');

// Dashboard
$router->map('GET', '/', 'DashboardController#index', 'dashboard');
$router->map('GET', '/settings', 'DashboardController#settings', 'settings');
$router->map('GET', '/campaigns/[*:type]', 'DashboardController#campaigns', 'campaigns');

$router->map('POST', '/widget/save', 'DashboardController#saveConfig', 'save_config');
$router->map('POST', '/save-live-visitor-config', 'DashboardController#saveLiveVisitorConfig', 'save_live_visitor');
$router->map('POST', '/api/toggle-feature', 'DashboardController#toggleFeature', 'api_toggle_feature');

$router->map('POST', '/notification/add', 'DashboardController#addNotification', 'add_notification');
$router->map('GET', '/notification/delete/[i:id]', 'DashboardController#deleteNotification', 'delete_notification');
$router->map('GET', '/event/delete/[i:id]', 'DashboardController#deleteEvent', 'delete_event');

// Coupons
$router->map('POST', '/campaigns/coupon/save', 'CouponController#save', 'coupon_save');
$router->map('GET', '/campaigns/coupon/delete/[i:id]', 'CouponController#delete', 'coupon_delete');

// Announcements
$router->map('POST', '/campaigns/announcement/save', 'AnnouncementController#save', 'announcement_save');
$router->map('GET', '/campaigns/announcement/delete/[i:id]', 'AnnouncementController#delete', 'announcement_delete');

// Videos
$router->map('POST', '/campaigns/video/save', 'VideoController#save', 'video_save');
$router->map('GET', '/campaigns/video/delete/[i:id]', 'VideoController#delete', 'video_delete');

// Newsletters
$router->map('POST', '/campaigns/newsletter/save', 'NewsletterController#save', 'newsletter_save');
$router->map('GET', '/campaigns/newsletter/delete/[i:id]', 'NewsletterController#delete', 'newsletter_delete');
$router->map('GET', '/campaigns/newsletter/leads/[i:id]', 'NewsletterController#leads', 'newsletter_leads');
$router->map('GET', '/campaigns/newsletter/export/[i:id]', 'NewsletterController#export_leads', 'newsletter_export');

// Social Popup
$router->map('POST', '/api/social/save', 'SocialController#save', 'social_save');
$router->map('POST', '/api/social/toggle', 'SocialController#toggle', 'social_toggle');

// Reviews
$router->map('POST', '/campaigns/review/save', 'ReviewController#save', 'review_save');
$router->map('POST', '/campaigns/review/item/save', 'ReviewController#saveItem', 'review_item_save');
$router->map('GET', '/campaigns/review/item/delete/[i:id]', 'ReviewController#deleteItem', 'review_item_delete');

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

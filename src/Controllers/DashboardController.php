<?php

class DashboardController {

    private function getWidgetAndUser() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $pdo = Database::getInstance();
        $user_id = $_SESSION['user_id'];

        // Fetch widget
        $stmt = $pdo->prepare("SELECT * FROM widgets WHERE user_id = ? LIMIT 1");
        $stmt->execute([$user_id]);
        $widget = $stmt->fetch();

        if (!$widget) {
            echo "No widget found.";
            exit;
        }

        return [$pdo, $user_id, $widget];
    }

    public function index() {
        list($pdo, $user_id, $widget) = $this->getWidgetAndUser();

        // 1. Current Live Count (Active in last 30 mins)
        // This matches the WidgetController logic
        $cutoff = date('Y-m-d H:i:s', strtotime('-30 minutes'));
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT visitor_id) as count FROM live_visitors WHERE widget_id = ? AND last_seen > ?");
        $stmt->execute([$widget['id'], $cutoff]);
        $current_live = $stmt->fetch()['count'];

        // 2. Historical Graph Data (Last 24 hours)
        // Fetch raw data
        $stmt = $pdo->prepare("SELECT visitor_count, created_at FROM traffic_snapshots WHERE widget_id = ? AND created_at > (NOW() - INTERVAL 24 HOUR) ORDER BY created_at ASC");
        $stmt->execute([$widget['id']]);
        $raw_data = $stmt->fetchAll();

        // Process data to fill gaps and create hourly peaks
        // We want a full 24-hour range on the chart
        $timezone = $widget['timezone'] ?? 'UTC';
        try {
            $tz = new DateTimeZone($timezone);
        } catch (Exception $e) {
            $tz = new DateTimeZone('UTC');
        }

        $hourly_buckets = [];
        $now = new DateTime('now', $tz);

        // Generate buckets for the last 24 hours relative to the USER'S timezone
        for ($i = 23; $i >= 0; $i--) {
             $dt = clone $now;
             $dt->modify("-$i hour");
             $h = $dt->format('H:00');
             $hourly_buckets[$h] = 0;
        }

        // Server time is likely UTC or system default.
        // We assume 'created_at' comes out as server time string.
        // We need to convert it to the user's timezone.
        $serverTz = new DateTimeZone(date_default_timezone_get());

        foreach ($raw_data as $row) {
            // Map the timestamp to its hour bucket
            $dt = new DateTime($row['created_at'], $serverTz);
            $dt->setTimezone($tz);
            $h = $dt->format('H:00');

            if (isset($hourly_buckets[$h])) {
                // Use the max visitor count recorded in that hour
                $hourly_buckets[$h] = max($hourly_buckets[$h], $row['visitor_count']);
            }
        }

        $labels = array_keys($hourly_buckets);
        $counts = array_values($hourly_buckets);

        require_once __DIR__ . '/../../views/pages/home.php';
    }

    public function settings() {
        list($pdo, $user_id, $widget) = $this->getWidgetAndUser();
        $plan = PlanManager::getUserPlan($user_id); // Fetch plan for limits
        require_once __DIR__ . '/../../views/pages/settings.php';
    }

    public function campaigns($type) {
        list($pdo, $user_id, $widget) = $this->getWidgetAndUser();

        // Feature Locking Check
        $featureMap = [
            'reviews' => 'reviews',
            'social' => 'socials',
            'live-visitors' => 'live_visitor',
            'coupon' => 'coupons',
            'announcement' => 'notifications',
            'video' => 'videos',
            'newsletter' => 'newsletters',
            'live-conversion' => 'live_conversion'
        ];

        if (isset($featureMap[$type])) {
            require_once __DIR__ . '/../Helpers/PlanManager.php';
            $plan = PlanManager::getUserPlan($user_id);
            if (empty($plan['features'][$featureMap[$type]])) {
                // Feature Locked
                $activePage = 'campaigns';
                $activeSubPage = $type;
                $pageTitle = 'Locked';
                require_once __DIR__ . '/../../views/locked.php';
                return;
            }
        }

        $controllerName = null;

        switch ($type) {
            case 'reviews':
                $controllerName = 'ReviewController';
                break;
            case 'social':
                $controllerName = 'SocialController';
                break;
            case 'live-visitors':
                $controllerName = 'LiveVisitorController';
                break;
            case 'coupon':
                $controllerName = 'CouponController';
                break;
            case 'announcement':
                $controllerName = 'AnnouncementController';
                break;
            case 'video':
                $controllerName = 'VideoController';
                break;
            case 'newsletter':
                $controllerName = 'NewsletterController';
                break;
            case 'live-conversion':
                $controllerName = 'LiveConversionController';
                break;
            case 'low-stock':
                $controllerName = 'LowStockController';
                break;
        }

        if ($controllerName) {
            // Load and instantiate the controller
            $controllerFile = __DIR__ . '/' . $controllerName . '.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    if (method_exists($controller, 'index')) {
                        $controller->index($widget['id']);
                        return;
                    }
                }
            }
        }

        // Generic placeholder for undefined types
        $campaignType = $type;
        require_once __DIR__ . '/../../views/campaigns/placeholder.php';
    }

    public function toggleFeature() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $pdo = Database::getInstance();
        $user_id = $_SESSION['user_id'];

        // Parse JSON input
        $raw_input = file_get_contents('php://input');
        $input = json_decode($raw_input, true);

        if (!$input) {
             http_response_code(400);
             echo json_encode(['error' => 'Invalid JSON input', 'raw' => $raw_input]);
             exit;
        }

        $feature = $input['feature'] ?? '';
        $enabled = !empty($input['enabled']); // boolean true/false
        $widget_id = $input['widget_id'] ?? 0;

        // Map feature name to column name
        $column = '';
        if ($feature === 'live_visitor') $column = 'live_visitor_enabled';
        elseif ($feature === 'live_conversion') $column = 'live_conversion_enabled';
        elseif ($feature === 'magical_detection') $column = 'magical_detection';
        elseif ($feature === 'use_real_conversion') $column = 'use_real_conversion';
        elseif ($feature === 'use_simulated_conversion') $column = 'use_simulated_conversion';

        if (!$column) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid feature: ' . $feature]);
            exit;
        }

        try {
            // Update
            // Ensure the widget belongs to the user
            $stmt = $pdo->prepare("UPDATE widgets SET $column = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$enabled ? 1 : 0, $widget_id, $user_id]);

            echo json_encode(['success' => true, 'feature' => $feature, 'enabled' => $enabled]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error', 'details' => $e->getMessage()]);
        }
        exit;
    }

    public function saveConfig() {
         if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $pdo = Database::getInstance();
        $user_id = $_SESSION['user_id'];

        $magical = isset($_POST['magical_detection']) ? 1 : 0;
        $timezone = $_POST['timezone'] ?? 'UTC';
        $allowed_domains = $_POST['allowed_domains'] ?? '';

        // Validate Domain Limit
        require_once __DIR__ . '/../Helpers/PlanManager.php';
        $plan = PlanManager::getUserPlan($user_id);

        $domains = array_filter(explode(',', $allowed_domains), function($d) { return !empty(trim($d)); });

        if ($plan['domain_limit'] != -1 && count($domains) > $plan['domain_limit']) {
            // Truncate to limit
            $domains = array_slice($domains, 0, $plan['domain_limit']);
            $allowed_domains = implode(',', $domains);
        }

        // Update first widget found
        $stmt = $pdo->prepare("UPDATE widgets SET magical_detection = ?, timezone = ?, allowed_domains = ? WHERE user_id = ?");
        $stmt->execute([$magical, $timezone, $allowed_domains, $user_id]);

        header('Location: /settings');
    }
}

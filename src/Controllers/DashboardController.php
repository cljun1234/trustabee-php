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
        require_once __DIR__ . '/../../views/pages/settings.php';
    }

    public function campaigns($type) {
        list($pdo, $user_id, $widget) = $this->getWidgetAndUser();

        if ($type === 'live-conversion') {
            // Fetch notifications (Simulated)
            $stmt = $pdo->prepare("SELECT * FROM notifications WHERE widget_id = ? ORDER BY created_at DESC");
            $stmt->execute([$widget['id']]);
            $notifications = $stmt->fetchAll();

            // Fetch Real Events (Form Submits)
            $stmt = $pdo->prepare("SELECT * FROM events WHERE widget_id = ? AND type='form_submit' ORDER BY created_at DESC LIMIT 50");
            $stmt->execute([$widget['id']]);
            $real_events = $stmt->fetchAll();

            require_once __DIR__ . '/../../views/campaigns/live_conversion.php';
        } elseif ($type === 'live-visitors') {

            // Config
            $config = [
                'enabled' => (bool)($widget['live_visitor_enabled'] ?? false),
                'settings' => json_decode($widget['live_visitor_config'] ?? '{}', true)
            ];

            require_once __DIR__ . '/../../views/campaigns/live_visitors.php';
        } elseif ($type === 'coupon') {
             // Delegate to CouponController or handle here?
             // Since I created CouponController::index, I should forward it.
             // But existing routing calls DashboardController#campaigns for /campaigns/[type]

             // I'll instantiate the specific controller logic here or redirect logic.
             // Given the architecture, I'll call the CouponController manually here.
             require_once __DIR__ . '/CouponController.php';
             $cc = new CouponController();
             $cc->index($widget['id']);
             // The CouponController::index loads the view and exits?
             // No, it requires the view. So we are good.

        } elseif ($type === 'announcement') {
             require_once __DIR__ . '/AnnouncementController.php';
             $ac = new AnnouncementController();
             $ac->index($widget['id']);

        } elseif ($type === 'video') {
             require_once __DIR__ . '/VideoController.php';
             $vc = new VideoController();
             $vc->index($widget['id']);

        } elseif ($type === 'newsletter') {
             require_once __DIR__ . '/NewsletterController.php';
             $nc = new NewsletterController();
             $nc->index($widget['id']);

        } elseif ($type === 'social') {
             require_once __DIR__ . '/SocialController.php';
             $sc = new SocialController();
             $sc->index($widget['id']);

        } elseif ($type === 'reviews') {
             require_once __DIR__ . '/ReviewController.php';
             $rc = new ReviewController();
             $rc->index($widget['id']);

        } else {
            // Generic placeholder
            $campaignType = $type;
            require_once __DIR__ . '/../../views/campaigns/placeholder.php';
        }
    }

    public function saveLiveVisitorConfig() {
        if (!isset($_SESSION['user_id'])) {
           header('Location: /login');
           exit;
       }

       $pdo = Database::getInstance();
       $user_id = $_SESSION['user_id'];
       $widget_id = $_POST['widget_id'];

       // Ownership check
       $stmt = $pdo->prepare("SELECT id FROM widgets WHERE id = ? AND user_id = ?");
       $stmt->execute([$widget_id, $user_id]);
       if (!$stmt->fetch()) { die("Unauthorized"); }

       // We only save config here (AJAX handles enabled toggle)
       $config = [
           'position' => $_POST['position'] ?? 'bottom-left',
           'bg_color' => $_POST['bg_color'] ?? '#ffffff',
           'text_color' => $_POST['text_color'] ?? '#333333'
       ];

       $stmt = $pdo->prepare("UPDATE widgets SET live_visitor_config = ? WHERE id = ?");
       $stmt->execute([json_encode($config), $widget_id]);

       header('Location: /campaigns/live-visitors');
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

        // Update first widget found
        $stmt = $pdo->prepare("UPDATE widgets SET magical_detection = ?, timezone = ? WHERE user_id = ?");
        $stmt->execute([$magical, $timezone, $user_id]);

        header('Location: /settings');
    }

    public function addNotification() {
         if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $pdo = Database::getInstance();
        $user_id = $_SESSION['user_id'];

        $name = $_POST['name'];
        $action = $_POST['action_text'];

        // Get widget id
        $stmt = $pdo->prepare("SELECT id FROM widgets WHERE user_id = ? LIMIT 1");
        $stmt->execute([$user_id]);
        $widget = $stmt->fetch();

        if ($widget) {
            $stmt = $pdo->prepare("INSERT INTO notifications (widget_id, name, action_text) VALUES (?, ?, ?)");
            $stmt->execute([$widget['id'], $name, $action]);
        }

        header('Location: /campaigns/live-conversion');
    }

    public function deleteNotification($id) {
         if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $pdo = Database::getInstance();
        $user_id = $_SESSION['user_id'];

        // Verify ownership via widget
        $stmt = $pdo->prepare("DELETE n FROM notifications n JOIN widgets w ON n.widget_id = w.id WHERE n.id = ? AND w.user_id = ?");
        $stmt->execute([$id, $user_id]);

        header('Location: /campaigns/live-conversion');
    }

    public function deleteEvent($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $pdo = Database::getInstance();
        $user_id = $_SESSION['user_id'];

        // Verify ownership via widget join
        $stmt = $pdo->prepare("DELETE e FROM events e JOIN widgets w ON e.widget_id = w.id WHERE e.id = ? AND w.user_id = ?");
        $stmt->execute([$id, $user_id]);

        header('Location: /campaigns/live-conversion');
    }
}

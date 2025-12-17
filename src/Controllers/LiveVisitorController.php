<?php

class LiveVisitorController {

    private function getWidgetAndUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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

    public function index($widget_id = null) {
        if ($widget_id === null) {
            list($pdo, $user_id, $widget) = $this->getWidgetAndUser();
            $widget_id = $widget['id'];
        } else {
             $pdo = Database::getInstance();
             $stmt = $pdo->prepare("SELECT * FROM widgets WHERE id = ?");
             $stmt->execute([$widget_id]);
             $widget = $stmt->fetch();
        }

        // Config
        $config = [
            'enabled' => (bool)($widget['live_visitor_enabled'] ?? false),
            'settings' => json_decode($widget['live_visitor_config'] ?? '{}', true)
        ];

        require_once __DIR__ . '/../../views/campaigns/live_visitors.php';
    }

    public function saveConfig() {
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
}

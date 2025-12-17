<?php

class LiveConversionController {

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

        // Fetch notifications (Simulated)
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE widget_id = ? ORDER BY created_at DESC");
        $stmt->execute([$widget_id]);
        $notifications = $stmt->fetchAll();

        // Fetch Real Events (Form Submits)
        $stmt = $pdo->prepare("SELECT * FROM events WHERE widget_id = ? AND type='form_submit' ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([$widget_id]);
        $real_events = $stmt->fetchAll();

        require_once __DIR__ . '/../../views/campaigns/live_conversion.php';
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
